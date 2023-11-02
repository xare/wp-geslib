<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbManager;
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
      $this->db->storeProducts();
      $geslibApiLiones = new GeslibApiLines;
      $this->db->setLogStatus( $log_id, 'processed' );
      $this->db->truncateGeslibLines();
      WP_CLI::line( "Store products" );
    }

    /**
     * processStoreProducts
     *
     * @return void
     */
    public function processStoreProducts() {
      $queue = get_option('geslib_queue', []);
      $newQueue = [];
      $geslibApiDbManager = new GeslibApiDbManager;
      foreach ($queue as $index => $task) {
        if ($task['type'] === 'store_products') {
          WP_CLI::line("Processing product for Geslib ID: {$task['geslib_id']}");
          if($task['action'] != 'B')
            $geslibApiDbManager->storeProduct($task['geslib_id'], $task['content']);
          else {
            $geslibApiDbManager->deleteProduct($task['geslib_id']);
          }
          WP_CLI::line("Processed product with geslib_id: {$task['geslib_id']}");
        }
      }
    }

}