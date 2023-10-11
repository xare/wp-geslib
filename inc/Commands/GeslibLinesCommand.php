<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\Encoding;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiLog;
use Inc\Geslib\Api\GeslibApiLines;
use Inc\Geslib\Api\GeslibApiReadFiles;
use WC_Product_Simple;
use WP_CLI;

class GeslibLinesCommand {

    public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib lines', [$this, 'execute'] );
        }
    }
    /**
    * Store filename data in geslib_lines
    *
    *
    * ## EXAMPLES
    *
    * wp geslib lines
    *
    * @when after_wp_load
    */

    public function execute( $args, $assoc_args ) {
        $geslibApiLog = new GeslibApiLog();
        $geslibApiLines = new GeslibApiLines();
        $geslibApiDb = new GeslibApiDbManager();
        $log_id = $geslibApiDb->getGeslibLoggedId();
        $geslibApiLines->storeToLines();
        $geslibApiDb->setLogStatus($log_id, 'queued');
        WP_CLI::line( 'Data has not been saved to geslib_lines!' );
    }
}