<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbLinesManager;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;
use Inc\Geslib\Api\GeslibApiDbLogManager;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbProductsManager;
use Inc\Geslib\Api\GeslibApiDbQueueManager;
use Inc\Geslib\Api\GeslibApiLines;
use Inc\Geslib\Api\GeslibApiReadFiles;
use Inc\Geslib\Api\GeslibApiStoreData;
use Inc\Dilve\Api\DilveApi;
use WP_CLI;

class GeslibStoreProductsCommand {

  private $db;

  public function __construct() {
    $this->db = new GeslibApiDbManager();
  }

  public function register() {
    if ( class_exists( 'WP_CLI' ) ) {
        WP_CLI::add_command( 'geslib storeProducts', [ $this, 'execute' ], [
          'synopsis' => [
              [
                  'type'        => 'flag',
                  'name'        => 'process-store-products',
                  'description' => 'Process the queue for store_products tasks.',
                  'optional'    => true,
              ],
          ],
      ] );
    }
  }

   /**
    * Say hello
    *
    * ## OPTIONS
    *
    * [--name=<name>]
    * : The name of the person to greet.
    *
    * ## EXAMPLES
    *
    * wp geslib storeProducts
    * wp geslib storeProducts --process-store-products
    * @when after_wp_load
    */
    public function execute( $args, $assoc_args ) {
      if ( isset( $assoc_args[ 'process-store-products' ] ) ) {
        $this->processStoreProducts();
        return;
      }
      $geslibApiReadFiles = new GeslibApiReadFiles();
        $geslibApiLines = new GeslibApiLines();
        $geslibApiDbManager = new GeslibApiDbManager();
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
        $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
        $geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
        $geslibApiStoreData = new GeslibApiStoreData;
        $dilveApi = new DilveApi();
        while( $loggedStatus = $geslibApiDbLogManager->checkLoggedStatus() ) {
            $geslibApiReadFiles->readFolder();
            $log_id = $geslibApiDbLogManager->getGeslibLoggedId();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Current Log_id', 'geslib_log', [
                'message' => 'Current log_id '.$log_id. ' to be queued.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'START', 'geslib_log', [
                'message' => 'We start the full PROCESS',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            if ( !$geslibApiDbLogManager->isQueued() ){
                $geslibApiDbLogManager->setLogStatus( $log_id, 'queued' );
                $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Set to queued', 'geslib_log', [
                    'message' => 'Log '.$log_id. ' has been queued.',
                    'file' => basename(__FILE__),
                    'class' => __CLASS__,
                    'function' => __METHOD__,
                    'line' => __LINE__,
                ]);
            } else {
                $geslibApiDbQueueManager->deleteItemsFromQueue( 'store_lines' );
                $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Reset store_lines', 'geslib_log', [
                    'message' => 'Log '.$log_id. ' is already queued we delete store_lines from queue to start again.',
                    'file' => basename(__FILE__),
                    'class' => __CLASS__,
                    'function' => __METHOD__,
                    'line' => __LINE__,
                ]);
            }
            $geslibApiLines->storeToLines();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to queue', 'geslib_queue', [
                'message' => 'We are moving data from files to geslib_queued(store_lines).',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbQueueManager->processFromQueue('store_lines');
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to lines', 'geslib_lines', [
                'message' => 'We are moving data from geslib_queued(store_lines) to geslib_lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiStoreData->storeAuthors();
            $geslibApiDbLoggerManager->geslibLogger( $log_id, 0, 'info', 'Store to Terms', 'authors', [
                'message' => 'Saving Authors.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiStoreData->storeEditorials();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Store to Terms', 'editorials', [
                'message' => 'Saving Editorials.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbProductsManager->storeProducts();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to Products 1', 'geslib_queues', [
                'message' => 'Saving Product data to geslib_queues.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);

            $geslibApiDbQueueManager->processFromQueue( 'store_editorials' );
            $geslibApiDbQueueManager->processFromQueue( 'store_autors' );
            $geslibApiDbQueueManager->processFromQueue( 'store_categories' );
            $geslibApiDbQueueManager->processFromQueue( 'store_products' );
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info','Store to Products 1', 'products', [
                'message' => 'Saving to woocommerce Products from geslib_queues.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLinesManager->truncateGeslibLines();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'truncate geslib_lines', 'geslib_lines', [
                'message' => 'Empty the table geslib_lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLogManager->setLogStatus( $log_id, 'processed');
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info','set log to processed', 'geslib_log', [
                'message' => 'Set log '.$log_id.' to processed',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
        }
    }

    /**
     * processStoreProducts
     *
     * @return void
     */
    public function processStoreProducts() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'geslib_queues';

      $geslibApiDbManager = new GeslibApiDbManager;
      $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
      $geslibApiDbQueueManager = new GeslibApiDbQueueManager;

      // Select tasks of type 'store_products' that are pending
      $pending_products = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM `$table_name` WHERE `type` = %s",
            'store_products'
        )
      );
      foreach ($pending_products as $index => $task) {
        if ($task->type === 'store_products') {
          WP_CLI::line("Processing product for Geslib ID: {$task->geslib_id}");
          //if($task['action'] != 'B')
            $geslibApiDbProductsManager->storeProduct( $task->geslib_id, $task->data );
            $geslibApiDbQueueManager->deleteItemFromQueue( $task->type,$task->log_id, (int) $task->geslib_id );
            //delete from queue

          //else {
          //  $geslibApiDbbProductsManager->deleteProduct($task['geslib_id']);
          //}
          WP_CLI::line("Processed product with geslib_id: {$task->geslib_id}");
        }
      }
    }

}