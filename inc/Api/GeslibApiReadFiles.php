<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiDbManager;
use ZipArchive;

// This file contains all the functions necessary to read the contents in the geslib folder and store them to the logs table

class GeslibApiReadFiles {
	private $mainFolderPath;
    private $histoFolderPath;
    private $db;

    public function __construct() {
        $this->mainFolderPath = WP_CONTENT_DIR . '/uploads/geslib/';
        $this->histoFolderPath = $this->mainFolderPath . 'HISTO/';
        $this->db = new GeslibApiDbManager();
    }

	public function readFolder(){

		$files = glob($this->mainFolderPath . 'INTER*');
		
		foreach($files as $file) {
			if (is_file($file)) {
				$filename = basename($file);
                $linesCount = count(file($file));
				// Check if the filename already exists in the database
                if (!$this->db->isFilenameExists($filename)) { 
					$this->db->insertLogData($filename, 'logged', $linesCount);
				}
			}
		}

		$this->processZipFiles();
	}

	/**
     * Process ZIP files in the HISTO folder: uncompress, read, compress, and insert data into the database.
     */
    public function processZipFiles() {
        // Check if the "HISTO" folder exists
        if (is_dir($this->histoFolderPath)) {
            // Get all ZIP files in the "HISTO" folder
            $zipFiles = glob($this->histoFolderPath . 'INTER*.zip');

            // Iterate through each ZIP file
            foreach ($zipFiles as $zipFile) {
                $this->processZipFile($zipFile);
            }
        }
    }

	/**
     * Process a ZIP file: uncompress, read its contents, compress again, and insert data into the database.
     *
     * @param string $zipFilePath Path to the ZIP file.
     */
    private function processZipFile($zipFilePath) {
        // Uncompress the ZIP file to a temporary directory
        $tempDir = wp_tempnam();
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
        }

        // Get the name of the uncompressed file
        $uncompressedFileName = basename($zipFilePath, '.zip');

        // Read the contents of the uncompressed file
        $uncompressedFilePath = $tempDir . '/' . $uncompressedFileName;
        $lines = file($uncompressedFilePath);
        $linesCount = count($lines);

        // Compress the file again and overwrite the original ZIP file
        $newZipPath = $this->histoFolderPath . $uncompressedFileName . '.zip';
        $newZip = new ZipArchive();
        if ($newZip->open($newZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($lines as $line) {
                $newZip->addFromString($uncompressedFileName, $line);
            }
            $newZip->close();
        }
		// Remove the temporary directory
		if (is_dir($tempDir)) {
			$filesToRemove = glob($tempDir . '/*');
			foreach ($filesToRemove as $fileToRemove) {
				if (is_file($fileToRemove)) {
					unlink($fileToRemove);
				}
			}
			rmdir($tempDir);
		}

	// Insert data into the database table for the compressed file
	$startDate = date('Y-m-d H:i:s');

	// Check if the filename already exists in the database
	if (!$this->db->isFilenameExists($uncompressedFileName)) {
		// Insert data into the database table
		$this->db->insertLogData($uncompressedFileName, 'logged', $linesCount);
	}
	}
	private function insertLogData($filename, $line_count) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'geslib_log';
	
		$result = $wpdb->insert(
			$table_name,
			array(
				'start_date' => current_time('mysql', 1),
				'imported_file' => $filename,
				'processed_lines' => $this->countLines($filename),
				'status' => 'logged'
			),
			array(
				'%s', // for start_date (formatted as a string)
				'%s', // for imported_file
				'%d', // for processed_lines
			)
		);
	
		if (false === $result) {
			error_log('Insert failed: ' . $wpdb->last_error);
		}
	}
	
	public function countLines( $filename ) {
		// Check if the file exists
		if( file_exists( $filename ) )
			return count( file( $filename ) );
		else 
			return false; // Return false if file not found
	}
}