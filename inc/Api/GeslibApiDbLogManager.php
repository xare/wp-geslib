<?php

namespace Inc\Geslib\Api;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;

class GeslibApiDbLogManager extends GeslibApiDbManager {

    /**
	 * insertLogData
	 *
	 * @param  string $filename
	 * @param  string $status
	 * @param  int $linesCount
	 * @return mixed
	 */
	public function insertLogData( string $filename, string $status, int $linesCount ) :mixed {
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
			return $wpdb->insert($wpdb->prefix . self::GESLIB_LOG_TABLE,
						$insertArray,
						['%s', '%s', '%s', '%s', '%d']);
		} catch (\Exception $e) {
			return "This file has not been properly inserted into the database due to an error: ".$e->getMessage();
		}
	}

    /**
	 * isFilenameExists
     * Check if the filename exists in the wpgeslib_log table.
     *
     * @param string $filename
     * @return bool
     */
    public function isFilenameExists( string $filename ): bool {
        global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LOG_TABLE;
		$query = $wpdb->prepare(
            "SELECT COUNT(*)
			FROM {$table}
			WHERE filename = %s",
            $filename
        );
        $count = $wpdb->get_var($query);

        return $count > 0;
    }

    /**
	 * getLogQueuedFile
	 *
	 * @return string
	 */
	public function getLogQueuedFile(): string {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LOG_TABLE;

		$query = $wpdb->prepare(
            "SELECT filename
			FROM {$table}
			WHERE status = '%s'",
            'queued'
        );

        return $wpdb->get_var($query);
	}

    /**
	 * getLogId
	 *
	 * @param  string $filename
	 * @return int
	 */
	public function getLogId( string $filename ): int {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LOG_TABLE;
		$query = $wpdb->prepare("SELECT
									id
								FROM $table
								WHERE filename='%s'",
								$filename);
		return $wpdb->get_var($query);
	}

    /**
	 * getGeslibLoggedId
	 *
	 * Returns $log_id from the lowest row with status "logged".
	 *
	 * @return int
	 */
	public function getGeslibLoggedId(): int {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LOG_TABLE;
		$query = $wpdb->prepare("SELECT
									id
								FROM $table
								WHERE status='%s'
								ORDER BY id ASC
								LIMIT 1",
								'logged');
		return $wpdb->get_var($query);
	}

    /**
	 * getGeslibLoggedFilename
	 *
	 * @param int
	 * @return string
	 */
	public function getGeslibLoggedFilename( int $log_id ): string {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LOG_TABLE;
		$query = $wpdb->prepare("SELECT
									filename
								FROM $table
								WHERE id='%d'",
								$log_id);
		return $wpdb->get_var($query);
	}

    /**
	 * Checks if there is at least one "logged" status in geslib_log table.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return bool
	 *   Returns true if there is at least one row with status "logged",
	 *   false otherwise.
	 */
	function checkLoggedStatus(): bool {
		global $wpdb;
		$table_name = $wpdb->prefix . self::GESLIB_LOG_TABLE;
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM
					$table_name
					WHERE status = %s
					ORDER BY id='ASC'", 'logged' );
		$count = $wpdb->get_var( $sql );
		return $count > 0;
	}

    /**
     * getLogQueuedFilename
     *
     * @return string
     */
    public function getLogQueuedFilename(): string {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT filename FROM ". $wpdb->prefix . self::GESLIB_LOG_TABLE .
            " WHERE status = %s LIMIT 1",
			'queued'
		);
		return ($wpdb->get_var($sql) == null) ? 'No file' : $wpdb->get_var($sql);
	}

    /**
	 * setLogTableToLogged
	 * Sets the status of all rows in the geslib_log table to "logged".
	 *
	 * @return bool
	 */
	public function setLogTableToLogged(): bool {
		global $wpdb;

		// Table name with the WordPress prefix
		$tableName = $wpdb->prefix . self::GESLIB_LOG_TABLE;

		// SQL to update the status
		$sql = "UPDATE $tableName SET status = 'logged'";

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
	 *
	 * @param  mixed $status
	 * @return int
	 */
	public function countGeslibLogStatus( string $status ) :int {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix.self::GESLIB_LOG_TABLE ." WHERE status='".$status."'");
	}

    /**
	 * countGeslibLog
	 *
	 * @return int
	 */
	public function countGeslibLog() :int {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix.self::GESLIB_LOG_TABLE);
	}

    /**
	 * fetchLoggedFilesFromDb
	 *
	 * @return array
	 */
	public function fetchLoggedFilesFromDb(): array {
		global $wpdb;
		return $wpdb->get_results( "SELECT filename, status FROM ".$wpdb->prefix.self::GESLIB_LOG_TABLE );
	}

    /**
     * truncateGeslibLogs
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
			wp_error( 'Unable to truncate geslib_lines table' . $exception->getMessage() );
			return false;
		}
	}

    /**
	 * updateLogStatus
	 *
	 * @param  int $log_id
	 * @param  string $status
	 * @return bool
	 */
	public function setLogStatus( int $log_id, string $status ) :bool {
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
			wp_error('Unable to update the row.'.$exception->getMessage());
			return false;
		}
	}

	public function getQueuedLogId() {
		global $wpdb;
		$table_name = $wpdb->prefix.self::GESLIB_LOG_TABLE;
		try {
			$query = $wpdb->prepare( "SELECT id
										FROM {$table_name}
										WHERE status=%s",'queued' );
			error_log($query);
			error_log('id:'. $wpdb->get_var($query));
			return $wpdb->get_var($query);
		} catch ( \Exception $exception) {
			wp_error('ERROR on getQueuedLogId: '. $exception->getMessage());
			return false;
		}

	}

    /**
	* isQueued
	*
	* Checks if there are any queued items in the database.
	*
	* @global wpdb $wpdb WordPress database abstraction object.
	* @return bool True if there are queued items, false otherwise.
	*/
    public function isQueued(): bool {
        global $wpdb;

        // Replace 'your_table_name' with the actual table name and 'status' with the appropriate column.
        $table_name = $wpdb->prefix . self::GESLIB_LOG_TABLE;

        // Prepare the SQL query. Ensure your column names are correct.
        $query = $wpdb->prepare( "SELECT id FROM $table_name WHERE status = %s LIMIT 1", 'queued' );

        // Execute the query and get the result.
        $result = $wpdb->get_var( $query );

        // Return true if a result is found, false otherwise.
        return !is_null( $result );
    }


}