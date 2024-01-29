<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbLinesManager;
use Inc\Geslib\Api\GeslibApiDbLogManager;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbProductsManager;
use Inc\Geslib\Api\GeslibApiDbQueueManager;
use Inc\Geslib\Api\GeslibApiLines;
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
      $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
      $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
      $geslibApiDbLogManager = new GeslibApiDbLogManager;
      $geslibApiDbProductsManager->storeProducts();
      $geslibApiLines = new GeslibApiLines;
      $geslibApiDbLogManager->setLogStatus( $log_id, 'processed' );
      $geslibApiDbLinesManager->truncateGeslibLines();
      WP_CLI::line( "Store products" );
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