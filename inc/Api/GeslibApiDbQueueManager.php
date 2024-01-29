<?php

namespace Inc\Geslib\Api;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;

class GeslibApiDbQueueManager extends GeslibApiDbManager {

    /**
     * insertLinesIntoQueue
	 * Inserts each line from INTER*** to the store_lines queue
     *
     * @param  array $batch
     * @return mixed
     */
    public function insertLinesIntoQueue( array $batch ) {
		global $wpdb;
		foreach ($batch as $item) {
			try {
				$wpdb->insert($wpdb->prefix . self::GESLIB_QUEUES_TABLE, $item);
			} catch( \Exception $exception ) {
				error_log( $exception->getMessage() );
            }
		}
	}
    /**
     * insertProductsIntoQueue
     *
     * @param  array $batch
     * @return void
     */
    public function insertProductsIntoQueue( array $batch ) {
		global $wpdb;

		foreach ( $batch as $item ) {
			try{
				$wpdb->insert( $wpdb->prefix . self::GESLIB_QUEUES_TABLE, $item );
				try {
					$wpdb->delete(
						$wpdb->prefix . self::GESLIB_LINES_TABLE,
						[
							'geslib_id' => $item['geslib_id'],
							'log_id' => $item['log_id'],
							'entity' => 'product'
						],['%d','%d','%s']
					);
				} catch( \Exception $exception ) { echo $exception->getMessage(); }
			} catch( \Exception $exception ) { echo $exception->getMessage(); }
		}
	}

	public function insertAuthorsIntoQueue( array $batch ) {
		global $wpdb;

		foreach ( $batch as $item ) {
			try{
				$wpdb->insert( $wpdb->prefix . self::GESLIB_QUEUES_TABLE, $item );
				try {
					$wpdb->delete(
						$wpdb->prefix . self::GESLIB_LINES_TABLE,
						[
							'geslib_id' => $item['geslib_id'],
							'log_id' => $item['log_id'],
							'entity' => 'author'
						],['%d','%d','%s']
					);
				} catch( \Exception $exception ) { echo $exception->getMessage(); }
			} catch( \Exception $exception ) { echo $exception->getMessage(); }
		}
	}

	public function insertEditorialsIntoQueue( array $batch ) {
		global $wpdb;

		foreach ( $batch as $item ) {
			try{
				$wpdb->insert( $wpdb->prefix . self::GESLIB_QUEUES_TABLE, $item );
				try {
					$wpdb->delete(
						$wpdb->prefix . self::GESLIB_LINES_TABLE,
						[
							'geslib_id' => $item['geslib_id'],
							'log_id' => $item['log_id'],
							'entity' => 'author'
						],['%d','%d','%s']
					);
				} catch( \Exception $exception ) { echo $exception->getMessage(); }
			} catch( \Exception $exception ) { echo $exception->getMessage(); }
		}
	}

	public function insertCategoriesIntoQueue( array $batch ) {
		global $wpdb;

		foreach ( $batch as $item ) {
			try{
				$wpdb->insert( $wpdb->prefix . self::GESLIB_QUEUES_TABLE, $item );
				try {
					$wpdb->delete(
						$wpdb->prefix . self::GESLIB_LINES_TABLE,
						[
							'geslib_id' => $item['geslib_id'],
							'log_id' => $item['log_id'],
							'entity' => 'author'
						],['%d','%d','%s']
					);
				} catch( \Exception $exception ) { echo $exception->getMessage(); }
			} catch( \Exception $exception ) { echo $exception->getMessage(); }
		}
	}

    /**
	 * deleteItemFromQueue
	 *
	 * @param  string $type
	 * @param  int $log_id
	 * @param  int $geslib_id
	 * @return void
	 */
	public function deleteItemFromQueue( string $type, int $log_id, int $geslib_id ) {
		global $wpdb;
		$geslib_id = ( $geslib_id == null )? 0 : $geslib_id;
		try {
			$wpdb->delete(
				$wpdb->prefix . self::GESLIB_QUEUES_TABLE,
				[
					'type' => $type,
					'geslib_id' => $geslib_id,
					'log_id' => $log_id
				],
				[
					'%s', // placeholder for 'type'
					'%d', // placeholder for 'geslib_id', assuming it's an integer
					'%d'  // placeholder for 'log_id', assuming it's an integer
				]
			);
			error_log("Deleted task: Type {$type}, Geslib ID {$geslib_id}, Log ID {$log_id}");
		} catch(\Exception $exception) {
			error_log("Failed to delete task: Type {$type}, Geslib ID {$geslib_id}, Log ID {$log_id} :".$exception->getMessage());
		}
	}

	 /**
     * deleteItemsFromQueue
     *
     * @param  string $type
     * @return bool
     */
    public function deleteItemsFromQueue( string $type ): bool {
		global $wpdb;
        try {
            // Delete query using Drupal's Database API
			$wpdb->delete(
				$wpdb->prefix . self::GESLIB_QUEUES_TABLE,
				[
					'type' => $type,
				],
				[
					'%s', // placeholder for 'type'
				]
			);

            return TRUE;
        } catch (\Exception $exception) {
            error_log("Failed to delete queue: {$type}:".$exception->getMessage());
            return FALSE;
        }
    }

	/**
	 * processFromQueue
	 *
	 * @param  mixed $type
	 * @return void
	 */
	public function processFromQueue( string $type ) {
		global $wpdb;
        $table_name = $wpdb->prefix . self::GESLIB_QUEUES_TABLE;
		if( $type == 'store_lines' ) {
			do {
				$this->processBatchStoreLines( 10 );
				// Get the count of remaining items in the queue
				$queue_count = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name` WHERE `type` = '$type'" );
				//echo 'queue count '.$queue_count.'/n' ;
			} while ( $queue_count > 0 );
		}
		if( $type == 'store_products' ) {
			// Select tasks of type 'store_products' that are pending
			do{
				$this->processBatchStoreProducts( 10 );
				$queue_count = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name` WHERE `type` = '$type'" );
			} while( $queue_count > 0);
		}
		if( $type == 'store_authors' ) {
			// Select tasks of type 'store_products' that are pending
			do{
				$this->processBatchStoreAuthors( 10 );
				$queue_count = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name` WHERE `type` = '$type'" );
			} while( $queue_count > 0);
		}
		if( $type == 'store_editorials' ) {
			// Select tasks of type 'store_products' that are pending
			do{
				$this->processBatchStoreEditorials( 10 );
				$queue_count = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name` WHERE `type` = '$type'" );
			} while( $queue_count > 0);
		}
		if( $type == 'store_categories' ) {
			// Select tasks of type 'store_products' that are pending
			do{
				$this->processBatchStoreCategories( 10 );
				$queue_count = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name` WHERE `type` = '$type'" );
			} while( $queue_count > 0);
		}
	}
	/**
	 * processBatchStoreLines
	 *
	 * @param  int $batchSize
	 * @return void
	 */
	public function processBatchStoreLines( int $batchSize = 10 ) {
        $queue = $this->getBatchFromQueue( $batchSize, 'store_lines' );
        // If there are no tasks, exit the function.
        $geslibApiLines = new GeslibApiLines();
        foreach ($queue as $task) {
            $geslibApiLines->readLine( $task->data, $task->log_id );
        }
    }

	/**
	 * processBatchStoreProducts
	 *
	 * @param  int $batchSize
	 * @return void
	 */
	public function processBatchStoreProducts( int $batchSize = 10 ) {
		$geslibApiDbProductsManager = new GeslibApiDbProductsManager();
		$queue = $this->getBatchFromQueue( $batchSize, 'store_products' );
		foreach ( $queue as $task ) {
			if( $task->action == 'stock') {
				$geslibApiDbProductsManager->stockProduct($task->geslib_id, $task->data);
			} else if( $task->action == 'B') {
				$geslibApiDbProductsManager->deleteProduct( $task->geslib_id );
			} else {
				$geslibApiDbProductsManager->storeProduct( $task->geslib_id, $task->data );
			}
			$this->deleteItemFromQueue( $task->type, $task->log_id, (int) $task->geslib_id );
		}
	}

	public function processBatchStoreAuthors( int $batchSize = 10 ) {
		$geslibApiDbManager = new GeslibApiDbManager();
		$queue = $this->getBatchFromQueue( $batchSize, 'store_authors' );
		foreach ( $queue as $task ) {
			if( $task->action == 'B') {
				$geslibApiDbManager->deleteAuthor( $task->geslib_id );
			} else {
				$geslibApiDbManager->storeAuthor( $task->geslib_id, $task->data );
			}
			$this->deleteItemFromQueue( $task->type, $task->log_id, (int) $task->geslib_id );
		}
	}
	public function processBatchStoreEditorials( int $batchSize = 10 ) {
		$geslibApiDbManager = new GeslibApiDbManager();
		$queue = $this->getBatchFromQueue( $batchSize, 'store_editorials' );
		foreach ( $queue as $task ) {
			if( $task->action == 'B') {
				$geslibApiDbManager->deleteProduct( $task->geslib_id );
			} else {
				$geslibApiDbManager->storeEditorial( $task->geslib_id, $task->data );
			}
			$this->deleteItemFromQueue( $task->type, $task->log_id, (int) $task->geslib_id );
		}
	}
	public function processBatchStoreCategories( int $batchSize = 10 ) {
		$geslibApiDbManager = new GeslibApiDbManager();
		$queue = $this->getBatchFromQueue( $batchSize, 'store_categories' );
		foreach ( $queue as $task ) {
			if ( $task->action == 'B') {
				$geslibApiDbManager->deleteCategory( $task->geslib_id );
			} else {
				$geslibApiDbManager->storeCategory( $task->geslib_id, $task->data );
			}
			$this->deleteItemFromQueue( $task->type, $task->log_id, (int) $task->geslib_id );
		}
	}

	/**
	 * getBatchFromQueue
	 *
	 * @param  int $batchSize
	 * @param  string $type
	 * @return array
	 */
	public function getBatchFromQueue( int $batchSize, string $type ): array {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT * FROM ". $wpdb->prefix . self::GESLIB_QUEUES_TABLE ."
            WHERE type=%s LIMIT %d",
            $type, $batchSize );
        return $wpdb->get_results( $query );
    }

	/**
	 * getQueuedTasks
	 *
	 * @param  string $type
	 * @return array
	 */
	public function getQueuedTasks( string $type) :array {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT * FROM ". $wpdb->prefix . self::GESLIB_QUEUES_TABLE ." WHERE type=%s", $type );
        return $wpdb->get_results( $query );
	}

    /**
	 * countGeslibQueue
	 *
	 * @param  string $type
	 * @return mixed
	 */
	public function countGeslibQueue( string $type ): mixed {
		global $wpdb;
		$queueTable = $wpdb->prefix . self::GESLIB_QUEUES_TABLE; // Replace with your actual table name
		// Prepare SQL to count the number of each type of task
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$queueTable} WHERE type='%s'", $type);
		return $wpdb->get_var($sql);
	}

}