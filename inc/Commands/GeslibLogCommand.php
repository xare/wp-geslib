<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\Encoding;
use Inc\Geslib\Api\GeslibApiLog;
use Inc\Geslib\Api\GeslibApiReadFiles;
use WC_Product_Simple;
use WP_CLI;

class GeslibLogCommand {
    
    public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib log', [$this, 'execute'] );
        }
    }
    /**
    * Store filename data in geslib_log
    *
    *
    * ## EXAMPLES
    *
    * wp geslib log
    *
    * @when after_wp_load
    */

    public function execute( $args, $assoc_args ) {
        $geslibReadFiles = new GeslibApiReadFiles();
        $geslibReadFiles->readFolder();
        WP_CLI::line( 'Data has been logged logged!' );
    }
}