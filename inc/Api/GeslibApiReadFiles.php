<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiDbManager;
use ZipArchive;

// This file contains all the functions necessary to read the contents in the geslib folder and store them to the logs table

class GeslibApiReadFiles {
	private $mainFolderPath;
    private $histoFolderPath;
	private $geslibSettings;
    private $db;

    public function __construct() {
		$this->geslibSettings = get_option('geslib_settings');
        $this->mainFolderPath = WP_CONTENT_DIR . '/uploads/' . $this->geslibSettings['geslib_folder_index'].'/';
        $this->histoFolderPath = $this->mainFolderPath . 'HISTO/';
        $this->db = new GeslibApiDbManager();
    }

	public function readFolder(){

		$files = glob($this->mainFolderPath . 'INTER*');

		foreach($files as $file) {
			if (is_file($file)) {
				$filename = basename($file);
				var_dump($filename);
                $linesCount = count(file($file));
				// Check if the filename already exists in the database
                if (!$this->db->isFilenameExists($filename)) {
					$this->db->insertLogData($filename, 'logged', $linesCount);
				}
			}
		}

		$this->processZipFiles();
	}

	public function listFilesInFolder() {
		$justFileNames = array_map( 'basename', glob( $this->mainFolderPath . 'INTER*' ) );
		$geslibApiDbManager = new GeslibApiDbManager;
		return $geslibApiDbManager->fetchLoggedFilesFromDb();
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

		$zipFiles = glob($this->mainFolderPath . 'INTER*.zip');

		// Iterate through each ZIP file
		foreach ($zipFiles as $zipFile) {
			var_dump($zipFile);
			$this->processZipFile($zipFile);
		}
    }

	/**
     * Process a ZIP file: uncompress, read its contents, compress again, and insert data into the database.
     *
     * @param string $zipFilePath Path to the ZIP file.
     */
    public function processZipFile($zipFilePath) {
        // Uncompress the ZIP file to a temporary directory
        $tempDir = wp_tempnam();
        $zip = new ZipArchive();
        if ( $zip->open( $zipFilePath ) === true ) {
            $zip->extractTo( dirname( $zipFilePath ) );
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

	// Function to check if the file is already unzipped
	public function isUnzipped($zipPath, $unzipDir) {
		$zip = new ZipArchive;
		if ($zip->open($zipPath) === TRUE) {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$filename = $zip->getNameIndex($i);
				if (file_exists($unzipDir . $filename)) {
					return true;
				}
			}
		}
		return false;
	}

	public function unzipFile(string $path) {
		// Check if the ZIP file is already unzipped
		if (!$this->isUnzipped($path, $this->mainFolderPath)) {
			// Initialize the ZipArchive class
			$zip = new ZipArchive;
			$res = $zip->open($path);
			if ($res === TRUE) {
				// Extract the ZIP file to the same folder
				$zip->extractTo($this->mainFolderPath);
				$extractedFilename = $zip->getNameIndex(0);
				$zip->close();
			} else {
				// Handle error
				echo 'Could not open the ZIP file.';
			}
		}

		// Update the filename to the extracted file (if you know the name)
		// $filename = 'your_extracted_file_name_here';

		// Or you can programmatically find the extracted file name
		// if it follows a specific pattern or if it's the only file in the ZIP
		return $extractedFilename;
	}

	/* private function insertLogData($filename, $line_count) {
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
	} */

	public function countLines( $filename ) {
		// Check if the file exists
		if( file_exists( $filename ) )
			return count( file( $filename ) );
		else
			return false; // Return false if file not found
	}

	public function countLinesWithGP4($filename) {
		// Check if the file exists
		if (!file_exists($filename)) {
			return false; // Return false if file not found
		}

		// Initialize the counts to 0
		$counts = [
			'total' => 0,
			'GP4A' => 0,
			'GP4M' => 0,
			'GP4B' => 0
		];

		$handle = fopen($filename, "r"); // Open the file for reading
		// Read line by line
		while (($line = fgets($handle)) !== false) {
			// Check if the line starts with "GP4"
			if (substr($line, 0, 3) === "GP4") {
				$counts['total']++; // Increment total GP4 lines count

				// Check the second part after "GP4|"
				$parts = explode('|', $line);
				if (count($parts) > 1) {
					switch ($parts[1]) {
						case 'A':
							$counts['GP4A']++;
							break;
						case 'M':
							$counts['GP4M']++;
							break;
						case 'B':
							$counts['GP4B']++;
							break;
					}
				}
			}
		}

		fclose($handle); // Close the file handle

		return $counts; // Return the counts
	}


}