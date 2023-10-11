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
        WP_CLI::add_command( 'geslib storeProducts', [ $this, 'execute' ] );
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
    * wp geslib storedata
    *
    * @when after_wp_load
    */
    public function execute( $args, $assoc_args ) {
      $this->db->storeProducts();
      $geslibApiLiones = new GeslibApiLines;
      $this->db->setLogStatus( $log_id, 'processed' );
      $this->db->truncateGeslibLines();
      WP_CLI::line( "Store products" );
    }

}