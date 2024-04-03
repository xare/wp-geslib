<?php

namespace Inc\Geslib\Api;

use Exception;
use Inc\Geslib\Api\GeslibApiSanitize;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;
use WC_Product_Simple;
use WP_Query;

class GeslibApiDbManager {
	const GESLIB_LINES_TABLE = 'geslib_lines';
	const GESLIB_LOG_TABLE = 'geslib_log';
	const GESLIB_QUEUES_TABLE = 'geslib_queues';
	const GESLIB_LOGGER_TABLE = 'geslib_logger';
	static $geslibLinesKeys = [
		'log_id', // int relation oneToMany with geslib_log
		'geslib_id', // int
		'filename', // string inter000
		'action', // string insert|update|delete
		'entity', // string product | category | author | publisher
		'content', // json
		'queued' // boolean 0|1
	];
	static $geslibLogKeys = [
		'filename', // string inter000
		'start_date', // date
		'end_date', // date
		'status', // string waiting | enqueued | processed
		'lines_count', // int number of lines
	];
	static $geslibLoggerKeys = [
		'log_id', // int
		'geslib_id', // int
		'type', // string store_lines | store_products | build_content
		'action', // string
		'entity', // string log | lines |
		'metadata', // json
	];

	/**
	 * countRows
	 * Count the number of rows in the geslib_log and geslib_lines tables
	 *
	 * @param  mixed $table
	 * @return int
	 */
	public function countRows( string $table ): int {
		global $wpdb;
		$table = $wpdb->prefix.'geslib_'.$table;
		return $wpdb->get_var( "SELECT COUNT(id) FROM $table" );
	}

    /**
     * _create_slug
	 * Called by GeslibApiDbTaxonomyManager
     *
     * @param  string $term_name
     * @return string
     */
    protected function _create_slug(string $term_name): string {
		// convert to lowercase
		$term_name = strtolower($term_name);

		// remove punctuation
		$term_name = preg_replace("/[.,:;!?(){}[\]<>%$#@^*+=|~`]/", "", $term_name);

		// replace spaces with underscores
		$term_slug = str_replace(" ", "_", $term_name);

		return (string) $term_slug;
  	}

	/**
	 * getGeslibTable
	 * Called by GeslibHelloController
	 *
	 * @param  string $table_suffix
	 * @return array
	 */
	public function getGeslibTable( string $table_suffix ): array {
		global $wpdb;
		$table_name = $wpdb->prefix .'geslib_'. $table_suffix;
		return $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
    }

	/**
	 * deleteTerm
	 * Called by GeslibApiDbQueueManager
	 *
	 * @param  int $geslib_id
	 * @param  string $taxonomy_name
	 * @return bool
	 */
	public function deleteTerm( int $geslib_id, string $taxonomy_name ): bool {
		$term = get_term_by('geslib_id', $geslib_id, $taxonomy_name);
		try {
			 wp_delete_term($term->ID, $taxonomy_name);
			 return true;
		} catch (Exception $e) {
			error_log('function deleteTerm : '.$e->getMessage());
			return false;
		}
	}
}
