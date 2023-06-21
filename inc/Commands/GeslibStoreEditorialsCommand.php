<?php

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiStoreData;
use WP_CLI;

class GeslibStoreEditorialsCommand {
    private $db;
    private $geslibApiStoreData;

    public function __construct(){
        $this->db = new GeslibApiDbManager();
        $this->geslibApiStoreData = new GeslibApiStoreData();
    }

    public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib storeEditorials', [$this, 'execute'] );
        }
    }

    /**
     * Send categories from geslib_lines to product_cat
     *
     * ## OPTIONS
     *
     * 
     * ## EXAMPLES
     *
     *     wp geslib storeEditorials
     *
     * @when after_wp_load
     */
    public function execute() {
        $this->geslibApiStoreData->storeEditorials();
        
        WP_CLI::line( 'Geslib lines categories have been transfered.');
    }



}