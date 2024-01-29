<?php

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbLinesManager;
use Inc\Geslib\Api\GeslibApiDbManager;
use WP_CLI;

/**
 * Class GeslibTruncateTableCommand
 */
class GeslibTruncateTableCommand {

	public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib truncateTable', [$this, 'execute'] );
        }
    }
    /**
     * Empties a table
     *
     * ## OPTIONS
     *
     *
     * ## EXAMPLES
     *
     *     wp geslib truncateTable
     *
     * @when after_wp_load
     */
    public function execute( $args, $assoc_args ) {
        $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
        $geslibApiDbLinesManager->truncateGeslibLines();
        WP_CLI::line( 'Geslib lines has been emptied. ');
    }
}

