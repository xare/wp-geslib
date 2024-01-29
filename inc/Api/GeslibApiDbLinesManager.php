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
		return $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix.self::GESLIB_LINES_TABLE);
	}

    /**
     * truncateGeslibLines
     *
     * @return bool
     */
    public function truncateGeslibLines(): bool {
		global $wpdb;

		try {
        	$wpdb->query( 'TRUNCATE TABLE '.$wpdb->prefix.self::GESLIB_LINES_TABLE );
			$this->geslibApiDbLoggerManager->geslibLogger(0, 0, 'info', 'truncate', 'geslib_lines', [
				'message' => 'Geslib_lines got truncated.',
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
			return true;
		} catch( \Exception $exception ) {
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


    public function updateGeslibLines( int $geslib_id, string $type, mixed $content){
		global $wpdb;

		try {
			$wpdb->update(
				$wpdb->prefix.self::GESLIB_LINES_TABLE,
				['content' => $content],
				[
					'geslib_id' => $geslib_id,
					'entity' => $type
				],
				'%s',
				['%d','%s']
			);
			$this->geslibApiDbLoggerManager->geslibLogger(0, 0, 'info', 'update', 'geslib_lines', [
				'message' => "Update geslib_lines table for entity ".$type." and for geslib_id: ".$geslib_id. " and content " . $content,
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
			return true;

		} catch( \Exception $exception ) {
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
				$wpdb->prefix.self::GESLIB_LINES_TABLE,
				[
					'log_id' => $log_id,
					'geslib_id' => $content_array['geslib_id'],
					'action' => $action,
					'entity' => $entity,
					'content' => json_encode($content_array),
					'queued' => 1
				],
			);
            $this->geslibApiDbLoggerManager->geslibLogger( $log_id, $content_array['geslib_id'], 'info', 'Set to queued', $entity, [
                'message' => 'The ' . $entity .' data was successfully inserted to geslib lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            return true;
		} catch (\Exception $e) {
            $this->geslibApiDbLoggerManager->geslibLogger( $log_id, $content_array['geslib_id'], 'error', 'Set to queued', $entity, [
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
     * @param  string $type
     * @return ?string
     */
    public function fetchContent( int $geslib_id, string $type ): ?string {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LINES_TABLE;

		$query = $wpdb->prepare(
							"SELECT
								content
							FROM $wpdb->prefix.self::GESLIB_LINES_TABLE
							WHERE
								geslib_id = '%d'
							AND
								entity = '%s'",
						$geslib_id, $type);
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

        // Prepare the SQL query. Ensure your column names are correct.
        $query = "SELECT * FROM $wpdb->prefix . self::GESLIB_QUEUE_TABLE WHERE type='store_lines' AND entity = '%s'";
        $prepared_query = $wpdb->prepare($query, 'autor');

        try {
            // Execute the query and get the results.
			// ARRAY_A returns the result as an associative array.
            $results = $wpdb->get_results($prepared_query);
            return $results;
        } catch (\Exception $exception) {
            // Log the error to WordPress debug log.
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

        // Prepare the SQL query. Ensure your column names are correct.
        $query = "SELECT * FROM $wpdb->prefix . self::GESLIB_QUEUE_TABLE WHERE type='store_lines' AND entity = '%s'";
        $prepared_query = $wpdb->prepare($query, 'editorial');

        try {
            // Execute the query and get the results.
			// ARRAY_A returns the result as an associative array.
            $results = $wpdb->get_results($prepared_query);
            return $results;
        } catch (\Exception $exception) {
            // Log the error to WordPress debug log.
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
     * @return mixed Array of authors or false on failure.
     */
    public function getCategoriesFromGeslibLines(): mixed {
        global $wpdb;

        // Prepare the SQL query. Ensure your column names are correct.
        $query = "SELECT * FROM $wpdb->prefix . self::GESLIB_QUEUE_TABLE WHERE type='store_lines' AND entity = '%s'";
        $prepared_query = $wpdb->prepare($query, 'product_cat');

        try {
            // Execute the query and get the results.
			// ARRAY_A returns the result as an associative array.
            $results = $wpdb->get_results($prepared_query);
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
            return false;
        }
    }
}