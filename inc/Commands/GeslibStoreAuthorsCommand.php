<?php

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiStoreData;
use WP_CLI;

class GeslibStoreAuthorsCommand {
    private $db;
    private $geslibApiStoreData;

    public function __construct(){
        $this->db = new GeslibApiDbManager();
        $this->geslibApiStoreData = new GeslibApiStoreData();
    }

    public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib storeAuthors', [$this, 'execute'] );
        }
    }

    /**
     * Send categories from geslib_lines to author
     *
     * ## OPTIONS
     *
     *
     * ## EXAMPLES
     *
     *     wp geslib storeAuthors
     *
     * @when after_wp_load
     */
    public function execute() {
        $this->geslibApiStoreData->storeAuthors();

        WP_CLI::line( 'Geslib authors categories have been transfered.');
    }



}