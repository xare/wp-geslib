<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;
use ZipArchive;

// This file contains all the functions necessary to read the contents in the geslib folder and store them to the logs table

class GeslibApiReadFiles {
	private string $mainFolderPath;
    private string $histoFolderPath;
	private array $geslibSettings;
    private $db;

	/**
     * __construct
     * - Cast the configuration to an array.
	 * - Retrieve the real path of the public files directory and ensure it's a string.
	 * - Construct the main folder path. Consider checking if 'geslib_folder_name' exists in the settings.
	 * - Append 'HISTO/' to the mainFolderPath.
	 *
     * @return void
     */
    public function __construct() {
		$this->geslibSettings = get_option('geslib_settings');
        $this->mainFolderPath = (string) WP_CONTENT_DIR . '/uploads/' . $this->geslibSettings['geslib_folder_index'].'/';
        $this->histoFolderPath = (string) $this->mainFolderPath . 'HISTO/';
        $this->db = new GeslibApiDbManager();
    }

	/**
	 * readFolder
	 * - Create a zip folder if missing
	 * - If no files present then finish and return false
	 * - Loop each files, if it´s a zip unzip and move the .zip file to a zip folder
	 *
	 * Called by:
	 * - Cron.php
	 * - GeslibStoreProductsFromController.php
	 * - GeslibLogCommand.php
	 * - GeslibProcessAllCommand.php
	 * - GeslibStoreProductsCommand.php
	 *
	 * @return mixed
	 */
	public function readFolder(): mixed {
		$files = (array) glob( $this->mainFolderPath . 'INTER*' );
		$zipFolder = (string) $this->mainFolderPath . 'zip/';
		// Check if the zip folder exists, if not create it
		// The true parameter allows the creation of nested directories as needed
		if ( !is_dir($zipFolder) ) mkdir($zipFolder, 0755, true);
		$filenames = [];
		/** @var string $file */
		foreach( $files as $file ) {
			/**
			 * @var array $fileInfo
			 * $fileInfo is an array{ dirname:string, basename:string, extension:string, filename:string }
			 */
			$fileInfo = pathinfo( $file );
			if ( isset( $fileInfo['extension'] )) {
				//is a zip file. Will first decompress it and then take the decompressed file to the geslib log
				// Initialize the ZipArchive class
				$zip = new ZipArchive();
				if ( $zip->open($file) ) {
					// Extract the files to the mainFolderPath
					$zip->extractTo( $this->mainFolderPath );
					// Insert into geslib_log if not already
					$zip->close();
					$newLocation = (string) $zipFolder . $fileInfo['basename'];
					try {
						(bool) rename($file, $newLocation);
					} catch(\Exception $exception) {
						echo "Error while copying the file to zip folder: ".$exception->getMessage();
					}
				}
			}
			$filenames[] = $fileInfo['filename'];
			$this->_insert2geslibLog( $fileInfo['filename'] );
		}
		return $filenames;
	}
	/**
	 * insert2geslibLog
	 * Inserts a file info into geslib_log for the first time status="logged"
	 * Called by:
	 * - GeslibApiReadFiles.php function readFile()
	 *
	 * @param  string $filename
	 * @return void
	 */
	private function _insert2geslibLog( string $filename ): void {
		$geslibApiDbLogManager = new GeslibApiDbLogManager;
		if ( !$geslibApiDbLogManager->isFilenameExists( basename( $filename ))) {
			$geslibApiDbLogManager->insertLogData( basename( $filename ), 'logged', count( file( $this->mainFolderPath . $filename )));
		}
	}

	/**
	 * unzipFile
	 * Unzips a file.
	 * Called by GeslibApiLines.php
	 *
	 * @param  string $path
	 * @return string|false
	 */
	public function unzipFile(string $path): string|false {
		// Check if the ZIP file is already unzipped
		if ( !$this->_isUnzipped($path, $this->mainFolderPath) ) {
			// Initialize the ZipArchive class
			$zip = new ZipArchive;
			if ($zip->open( $path )) {
				// Extract the ZIP file to the same folder
				$zip->extractTo($this->mainFolderPath);
				$extractedFilename = $zip->getNameIndex(0);
				$zip->close();
				return (string) $extractedFilename;
			} else {
				// Handle error
				error_log('Could not open the ZIP file.');
			}
		}
		return false;
	}

	/**
	 * countFilesInFolder
	 * Called from
	 * - GeslibUpdateValuesController.php
	 * - tab1_content.php
	 *
	 * @return int
	 */
	public function countFilesInFolder(): int {
		return (int) count($this->listfilesInFolder());
	}

	/**
	 * _isUnzipped
	 * Function to check if the file is already unzipped
	 *
	 * @param  string $zipPath
	 * @param  string $unzipDir
	 * @return mixed
	 */
	private function _isUnzipped(string $zipPath, string $unzipDir): mixed {
		$zip = new ZipArchive;
		if ($zip->open($zipPath)) {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$filename = $zip->getNameIndex($i);
				return file_exists($unzipDir . $filename);
			}
		}
		return false;
	}

	/**
	 * listFilesInFolder
	 *
	 * @return array
	 */
	public function listFilesInFolder(): array {
		$justFileNames = array_map( 'basename', glob( $this->mainFolderPath . 'INTER*' ) );
		$geslibApiDbLogManager = new GeslibApiDbLogManager;
		return $geslibApiDbLogManager->fetchLoggedFilesFromDb();
	}

	/**
	 * countLines
	 * Counts the number of line of the INTER file
	 * Called from
	 * - GeslibFilesListTable
	 * - tab3_content.php
	 *
	 * @param  string $filename
	 * @return int|false
	 */
	public function countLines( string $filename ): int|false {
		// Check if the file exists
		if( file_exists( $filename ) && is_file( $filename ) )
			return (int) count( file( $filename ) );
		else
			return false; // Return false if file not found
	}

	/**
	 * countLinesWithGP4
	 * Used to show the "files" table in the admin interface.
	 * - called from GeslibFilesListTable
	 * - called from tab3_content.php
	 *
	 * @param  string $filename
	 * @param  string $type
	 * @return array|false
	 */
	public function countLinesWithGP4(string $filename, string $type='product'): array|false {
		// Check if the file exists
		$codes  = ['GP4', '1L','3'];
		if (!file_exists($filename)) {
			return false; // Return false if file not found
		}

		// Initialize the counts to 0
		$countsArray = [
			'total' => 0,
			'GP4A' => 0,
			'GP4M' => 0,
			'GP4B' => 0,
			'1LA' => 0,
			'1LM' => 0,
			'1LB' => 0,
			'3A' => 0,
			'3M' => 0,
			'3B' => 0,
		];
		if( !is_file( $filename )) {
			return false;
		}
		$handle = fopen( $filename, "r" ); // Open the file for reading
		// Read line by line
		while ( $line = fgets( $handle ) ) {
			// Check if the line starts with "GP4"
			$lineArray = explode('|', $line);
			if ( in_array($lineArray[0], $codes)) {
				$countsArray['total']++; // Increment total GP4 lines count
				if ( count( $lineArray ) > 1 ) {
					if (in_array($lineArray[1], ['A', 'M', 'B'])) {
						// i.e.: $countArray['GP4A']
						$countsArray[$lineArray[0] . $lineArray[1]]++;
					}
				}
			}
		}
		fclose( $handle ); // Close the file handle
		return (array) $countsArray; // Return the counts
	}
}