<?php

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbManager;
use WP_CLI;

/**
 * Class GeslibShowTablesCommand
 */
class GeslibShowTablesCommand {
    private $db;
    public function __construct() {
        $this->db = new GeslibApiDbManager();
    }
	public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib showTables', [$this, 'execute'] );
        }
    }
    /**
     * Prints a the number of lines of geslib log and geslib lines
     *
     * ## OPTIONS
     *
     * 
     * ## EXAMPLES
     *
     *     wp geslib showTables
     *
     * @when after_wp_load
     */
    public function execute( $args, $assoc_args ) {
        $tables = ['log', 'lines'];
        foreach ( $tables as $table ) {
            WP_CLI::line( $table .' table contains ' . $this->db->countRows($table) .' lines.');
        }
    }
}

