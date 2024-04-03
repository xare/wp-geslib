<?php

namespace Inc\Geslib\Api;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;

class GeslibApiDbLogManager extends GeslibApiDbManager {

    /**
	 * insertLogData
	 * Called by
	 * GeslibApiRead Files _insert2geslibLog
	 *
	 * @param  string $filename
	 * @param  string $status
	 * @param  int $linesCount
	 * @return bool
	 */
	public function insertLogData( string $filename, string $status, int $linesCount ): bool {
		global $wpdb;
		$geslibLogValues = [
			$filename,
			date('Y-m-d H:i:s'),
			null,
			$status,
			$linesCount,
		];
		$insertArray = array_combine(self::$geslibLogKeys, $geslibLogValues);
		try {
			$wpdb->insert($wpdb->prefix . self::GESLIB_LOG_TABLE,
						$insertArray,
						['%s', '%s', '%s', '%s', '%d']);
			return true;
		} catch (\Exception $e) {
			error_log("This file has not been properly inserted into the database due to an error: ".$e->getMessage());
			return false;
		}
	}

    /**
	 * isFilenameExists
     * Check if the filename exists in the wpgeslib_log table.
	 * Called by:
	 * - GeslibApiReadFiles function _insert2geslibLog
     *
     * @param string $filename
     * @return bool
     */
    public function isFilenameExists( string $filename ): bool {
        global $wpdb;
		$query = $wpdb->prepare(
            "SELECT COUNT(*)
			FROM $wpdb->prefix.self::GESLIB_LOG_TABLE
			WHERE filename = %s",
            $filename
        );
        return (bool) $wpdb->get_var($query) > 0;
    }

    /**
	 * getLogQueuedFile
	 * Called by GeslibApiLog.php function getQueuedFile()
	 *
	 * @return string
	 */
	public function getLogQueuedFile(): string {
		global $wpdb;
		$query = $wpdb->prepare(
            "SELECT filename
			FROM $wpdb->prefix.self::GESLIB_LOG_TABLE
			WHERE status = '%s'",
            'queued'
        );
        return (string) $wpdb->get_var($query);
	}

    /**
	 * getGeslibLoggedId
	 *
	 * Returns $log_id from the lowest row with status "logged".
	 * Called by:
	 * - Cron.php
	 * - GeslibStoreProductsFormController.php
	 * - GeslibLinesCommand.php
	 * - GeslibProcessAllCommand.php
	 * - GeslibStoreProductsCommand.php
	 *
	 * @return int
	 */
	public function getGeslibLoggedId(): int {
		global $wpdb;
		$query = $wpdb->prepare("SELECT
									id
								FROM '$wpdb->prefix.self::GESLIB_LOG_TABLE'
								WHERE status='%s'
								ORDER BY id ASC
								LIMIT %d",
								'logged', 1);
		return (int) $wpdb->get_var($query);
	}

    /**
	 * getGeslibLoggedFilename
	 * Called by:
	 * - GeslibApiLines.php
	 *
	 * @param int
	 * @return string
	 */
	public function getGeslibLoggedFilename( int $log_id ): string {
		global $wpdb;
		$query = $wpdb->prepare("SELECT
									filename
								FROM $wpdb->prefix.self::GESLIB_LOG_TABLE
								WHERE id='%d'",
								$log_id);
		return (string) $wpdb->get_var($query);
	}

    /**
	 * Checks if there is at least one "logged" status in geslib_log table.
	 * Called by:
	 * - Cron.php
	 * - GeslibStoreProductsFormController
	 * - GeslibProcessAllCommand
	 * - GeslibStoreProductsCommand
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return bool
	 *   Returns true if there is at least one row with status "logged",
	 *   false otherwise.
	 */
	function checkLoggedStatus(): bool {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT COUNT(*)
								FROM $wpdb->prefix . self::GESLIB_LOG_TABLE
								WHERE status = %s
								ORDER BY id='%s'",
								'logged', 'ASC' );
		return (bool) $wpdb->get_var( $sql ) > 0;
	}

    /**
     * getLogQueuedFilename
     *
     * @return string
     */
    public function getLogQueuedFilename(): string {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT filename
			FROM $wpdb->prefix . self::GESLIB_LOG_TABLE
            WHERE status = %s LIMIT %d",
			['queued', 1 ]
		);
		return (string) ($wpdb->get_var($sql) == null) ? 'No file' : $wpdb->get_var($sql);
	}

    /**
	 * setLogTableToLogged
	 * Sets the status of all rows in the geslib_log table to "logged".
	 * Called by:
	 * - GeslibStoreProductsFormController.php
	 *
	 * @return bool
	 */
	public function setLogTableToLogged(): bool {
		global $wpdb;

		// SQL to update the status
		$sql = "UPDATE $wpdb->prefix . self::GESLIB_LOG_TABLE SET status = 'logged'";

		// Execute the query
		try {
			$wpdb->query($sql);
			return true;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

    /**
	 * countGeslibLogStatus
	 * Called by:
	 * - GeslibUpdateValuesController
	 * - tab1_content.php
	 *
	 * @param  mixed $status
	 * @return int
	 */
	public function countGeslibLogStatus( string $status ) :int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*)
								FROM $wpdb->prefix.self::GESLIB_LOG_TABLE
								WHERE status='".$status."'");
	}

    /**
	 * countGeslibLog
	 * Called by:
	 * - GeslibUpdateValuesController
	 * - tab1_content.php
	 *
	 * @return int
	 */
	public function countGeslibLog(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*)
								FROM ".$wpdb->prefix.self::GESLIB_LOG_TABLE);
	}

    /**
	 * fetchLoggedFilesFromDb
	 * Called by:
	 * - GeslibStoreProductsFormController
	 *
	 * @return array
	 */
	public function fetchLoggedFilesFromDb(): array {
		global $wpdb;
		return (array) $wpdb->get_results( "SELECT filename, status
											FROM ".$wpdb->prefix.self::GESLIB_LOG_TABLE );
	}

    /**
     * truncateGeslibLogs
	 * Truncates geslib_log table
	 * Called by:
	 * - GeslibProductsFormController
     *
     * @return bool
     */
    public function truncateGeslibLogs(): bool {
		global $wpdb;
		try {
			$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . self::GESLIB_LINES_TABLE . ' WHERE log_id IN (SELECT id FROM '.$wpdb->prefix.self::GESLIB_LOG_TABLE.')');
			$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . self::GESLIB_LINES_TABLE . ' AUTO_INCREMENT = 1' );
			$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0;' );
			$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . self::GESLIB_LOG_TABLE );
			$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1;' );
			if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
				global $wpdb;
				if ( ! empty( $wpdb->queries ) ) {
					foreach ( $wpdb->queries as $query ) {
						// Log each query to the debug log
						error_log( $query[0] );
					}
				}
			}
			return true;
		} catch( \Exception $exception ) {
			error_log( 'Unable to truncate geslib_lines table' . $exception->getMessage() );
			return false;
		}
	}

    /**
	 * updateLogStatus
	 * Called by:
	 * - GeslibApiLog
	 * - Cron
	 * - GeslibStoreProductsFormController
	 * - GeslibLinesCommand
	 * - GeslibProcessAllCommand
	 * - GeslibStoreProdcutsCommand
	 *
	 * @param  int $log_id
	 * @param  string $status
	 * @return bool
	 */
	public function setLogStatus( int $log_id, string $status ): bool {
		global $wpdb;
		$table_name = $wpdb->prefix.self::GESLIB_LOG_TABLE; // Replace with your actual table name if different
		$data = [ 'status' => $status ];
		if( $status == 'processed' ) {
			$data[ 'end_date' ] = date('Y-m-d H:i:s');
		}
		$where = ['id' => $log_id];
		$format = ['%s']; // string format
		$where_format = ['%d']; // integer format
		try {
			$wpdb->update( $table_name, $data, $where, $format, $where_format);
			return true;
		} catch( \Exception $exception ) {
			error_log('Unable to update the row.'.$exception->getMessage());
			return false;
		}
	}

	/**
	 * getQueuedLogId
	 *
	 * @return mixed
	 */
	public function getQueuedLogId(): mixed {
		global $wpdb;
		try {
			$query = $wpdb->prepare( "SELECT id
										FROM $wpdb->prefix.self::GESLIB_LOG_TABLE
										WHERE status=%s",'queued' );
			return (int) $wpdb->get_var($query);
		} catch ( \Exception $exception) {
			error_log('ERROR on getQueuedLogId: '. $exception->getMessage());
			return false;
		}

	}

    /**
	* isQueued
	*
	* Checks if there are any queued items in the database.
	* Called by:
	* - Cron
	* - GeslibStoreProductsFormController
	* - GeslibProcessAllCommand
	* - GeslibStoreProductsCommand
	*
	* @global wpdb $wpdb WordPress database abstraction object.
	* @return bool True if there are queued items, false otherwise.
	*/
    public function isQueued(): bool {
        global $wpdb;

        // Prepare the SQL query. Ensure your column names are correct.
        $query = $wpdb->prepare( "SELECT id
									FROM $wpdb->prefix . self::GESLIB_LOG_TABLE
									WHERE status = %s LIMIT %d", 'queued', 1 );

        // Return true if a result is found, false otherwise.
        return !is_null( $wpdb->get_var( $query ) );
    }

}