<?php

namespace Inc\Geslib\Api;


// This file contains all the functions necessary to read the contents in the geslib folder and store them to the logs table

class GeslibApiReadFiles {

	private function storeToLog($file_name, $line_count) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'geslib_log';
	
		$result = $wpdb->insert(
			$table_name,
			array(
				'start_date' => current_time('mysql', 1),
				'imported_file' => $file_name,
				'processed_lines' => $line_count,
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