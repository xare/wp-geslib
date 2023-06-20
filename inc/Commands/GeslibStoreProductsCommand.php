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
        WP_CLI::add_command( 'geslib storeProducts', [$this, 'execute'] );
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
      WP_CLI::line("Store products");
    }

    /* public function _readGeslibLinesTable(){
      global $wpdb;
      $table_name = $wpdb->prefix . 'geslib_lines';
      $query = $wpdb->prepare( "SELECT * FROM {$table_name}" );
      $results = $wpdb->get_results($query);
      
      foreach ($results as $result) {
        $this->_storeData($result->type, $result->id, $result->content);
      }
    } */

    
}

/* \WP_CLI::add_command( 'geslib storedata', 'Inc\Geslib\Commands\GeslibStoreDataCommand' ); */