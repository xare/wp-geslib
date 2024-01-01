<?php

/**
 * @package geslib
 */
namespace Inc\Geslib\Base;

use Inc\Dilve\Api\DilveApi;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiLines;
use Inc\Geslib\Api\GeslibApiReadFiles;

class Cron extends BaseController {

    public function register() {
        if ( ! wp_next_scheduled( 'geslib_cron_event' ) ) {
            wp_schedule_event( time(), 'daily', 'geslib_cron_event' );
        }
        add_action( 'geslib_cron_event', [ $this, 'geslib_cron_function' ] );
    }
    function geslib_cron_function() {
        $geslibApiReadFiles = new GeslibApiReadFiles();
        $geslibApiLines = new GeslibApiLines();
        $geslibApiDbManager = new GeslibApiDbManager();
        $dilveApi = new DilveApi();
        //while( $loggedStatus = $geslibApiDbManager->checkLoggedStatus() ) {
            //var_dump($loggedStatus);
            $geslibApiReadFiles->readFolder();
            $log_id = $geslibApiLines->storeToLines();
            $geslibApiDbManager->processFromQueue('store_lines');
            $geslibApiDbManager->storeProducts();
            $geslibApiDbManager->processFromQueue('store_products');
            $geslibApiDbManager->truncateGeslibLines();
            $geslibApiDbManager->setLogStatus( $log_id, 'processed');
        //}
        $dilveApi->scanProducts();
    }
}
