<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\Encoding;
use WC_Product_Simple;
use WP_CLI_Command;

class GeslibStoreDataCommand extends WP_CLI_Command { 
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
    public function __invoke( $args, $assoc_args ) {
      //1. Read geslib_logs table
      $this->_readGeslibLinesTable();
      //2. open the file
      //3. send the contents to the geslib_lines
      return "geslibLines";
    }

    public function _readGeslibLinesTable(){
      global $wpdb;
      $table_name = $wpdb->prefix . 'geslib_lines';
      $query = $wpdb->prepare( "SELECT * FROM {$table_name}" );
      $results = $wpdb->get_results($query);
      
      foreach ($results as $result) {
        $this->_storeData($result->type, $result->id, $result->content);
      }
    }

    
}

\WP_CLI::add_command( 'geslib storedata', 'Inc\Geslib\Commands\GeslibStoreDataCommand' );