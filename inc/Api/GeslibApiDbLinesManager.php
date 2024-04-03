<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;

class GeslibApiDbLinesManager extends GeslibApiDbManager {

	protected $geslibApiDbLoggerManager;

	public function __construct(){
		$this->geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
	}
    /**
	 * countGeslibLines
	 *
	 * @return int
	 */
	public function countGeslibLines() :int {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix.self::GESLIB_QUEUES_TABLE." WHERE type='build_content'");
	}

    /**
     * truncateGeslibLines
     *
     * @return bool
     */
    public function truncateGeslibLines(): bool {
		global $wpdb;

		try {
        	$wpdb->delete( $wpdb->prefix.self::GESLIB_QUEUES_TABLE, ['type' => 'store_lines'], ['%s'] );
			return true;
		} catch( \Exception $exception ) {
            error_log('Failed to remove store_lines from queue');
			$this->geslibApiDbLoggerManager->geslibLogger(0, 0, 'error', 'truncate', 'geslib_lines', [
				'message' => "Unable to truncate geslib_lines table ".$exception->getMessage() ,
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
			return false;
		}
	}


    public function updateGeslibLines( int $geslib_id, string $entity, mixed $content){
		global $wpdb;

		try {
			$wpdb->update(
				$wpdb->prefix.self::GESLIB_QUEUES_TABLE,
				['data' => $content],
				[
					'geslib_id' => $geslib_id,
					'entity' => $entity,
                    'type' => 'build_content'
				],
				'%s',
				['%d','%s','%s']
			);
			return true;

		} catch( \Exception $exception ) {
            error_log('failed to update gesli_lines table'. $exception->getMessage());
			$this->geslibApiDbLoggerManager->geslibLogger(0, 0, 'error', 'update', 'geslib_lines', [
				'message' => "Unable to update geslib_lines table ".$exception->getMessage() ,
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
		}
	}

    /**
	 * insertData
	 *
	 * @param  mixed $content_array
	 * @param  string $action
	 * @param  int $log_id
	 * @param  string $entity
	 * @return bool
	 */
	public function insertData( mixed $content_array, string $action, int $log_id, string $entity ): bool {
		global $wpdb;
		try {
			$wpdb->insert(
				$wpdb->prefix.self::GESLIB_QUEUES_TABLE,
				[
					'log_id' => $log_id,
					'geslib_id' => $content_array['geslib_id'],
                    'type' => 'build_content',
					'action' => $action,
					'entity' => $entity,
					'data' => json_encode($content_array),
				],
			);
            return true;
		} catch (\Exception $e) {
            error_log("The $entity data was NOT successfully inserted to geslib lines ".$e->getMessage());
            $this->geslibApiDbLoggerManager->geslibLogger( $log_id, $content_array['geslib_id'], 'error', 'Store to lines', $entity, [
                'message' => "The $entity data was NOT successfully inserted to geslib lines ".$e->getMessage(),
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            return false;
		}
	}

    /**
     * fetchContent
     *
     * @param  int $geslib_id
     * @param  string $entity
     * @return ?string
     */
    public function fetchContent( int $geslib_id, string $entity ): ?string {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_QUEUES_TABLE;

		$query = $wpdb->prepare(
							"SELECT
								data
							FROM {$table}
							WHERE
								geslib_id = '%d'
							AND
								entity = '%s'
                            AND
                                type = '%s'",
						$geslib_id, $entity, 'build_content');
		return $wpdb->get_var( $query );
	}
    /**
     * getAuthorsFromGeslibLines
     * Get Authors from Geslib Lines
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     * @return mixed Array of authors or false on failure.
     */
    public function getAuthorsFromGeslibLines(): mixed {
        global $wpdb;
        $queue_table = $wpdb->prefix . self::GESLIB_QUEUES_TABLE;
        // Prepare the SQL query. Ensure your column names are correct.

        $query = "SELECT * FROM {$queue_table} WHERE entity = '%s' AND TYPE='%s'";
        $prepared_query = $wpdb->prepare($query, 'autors', 'store_autors');

        try {
            // Execute the query and get the results.
			// ARRAY_A returns the result as an associative array.
            $results = $wpdb->get_results($prepared_query, ARRAY_A);
            return $results;
        } catch (\Exception $exception) {
            // Log the error to WordPress debug log.
            error_log('Function getAuthorsFromGeslibLines: ' . $exception->getMessage());
			$this->geslibApiDbLoggerManager->geslibLogger( 0, 0, 'error', 'get authors', 'author', [
                'message' => 'Function getAuthorsFromGeslibLines: ' . $exception->getMessage() ,
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            return false;
        }
    }

	/**
     * getAuthorsFromGeslibLines
     * Get Authors from Geslib Lines
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     * @return mixed Array of authors or false on failure.
     */
    public function getEditorialsFromGeslibLines(): mixed {
        global $wpdb;
        $queue_table = $wpdb->prefix . self::GESLIB_QUEUES_TABLE;
        // Prepare the SQL query. Ensure your column names are correct.
        $query = "SELECT * FROM {$queue_table} WHERE type='store_lines' AND entity = '%s' AND TYPE='%s'";
        $prepared_query = $wpdb->prepare($query, 'editorial', 'store_editorial');

        try {
            // Execute the query and get the results.
			// ARRAY_A returns the result as an associative array.
            $results = $wpdb->get_results($prepared_query, ARRAY_A);
            return $results;
        } catch (\Exception $exception) {
            // Log the error to WordPress debug log.
            error_log('Function getEditorialsFromGeslibLines: ' . $exception->getMessage());
			$this->geslibApiDbLoggerManager->geslibLogger( 0, 0, 'error', 'get editorials', 'editorial', [
                'message' => 'Function getEditorialsFromGeslibLines: ' . $exception->getMessage() ,
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            return false;
        }
    }

	/**
     * getAuthorsFromGeslibLines
     * Get Authors from Geslib Lines
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     * @return mixed Array of categories or false on failure.
     */
    public function getCategoriesFromGeslibLines(): mixed {
        global $wpdb;
        $queue_table = $wpdb->prefix . self::GESLIB_QUEUES_TABLE;
        // Prepare the SQL query. Ensure your column names are correct.
        $query = "SELECT * FROM {$queue_table} WHERE  entity = '%s' AND type='%s'";
        $prepared_query = $wpdb->prepare($query, 'product_cat', 'store_categories');

        try {
            // Execute the query and get the results.
			// ARRAY_A returns the result as an associative array.
            $results = $wpdb->get_results($prepared_query, ARRAY_A);
            return $results;
        } catch (\Exception $exception) {
            // Log the error to WordPress debug log.
			$this->geslibApiDbLoggerManager->geslibLogger( 0, 0, 'error', 'get categories', 'product_cat', [
                'message' => 'Function getCategoriesFromGeslibLines: ' . $exception->getMessage() ,
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            error_log('Function getCategoriesFromGeslibLines: ' . $exception->getMessage());
            return false;
        }
    }
}