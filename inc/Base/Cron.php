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
        $geslibApiDb = new GeslibApiDbManager();
        $geslibApiDbManager = new GeslibApiDbManager();
        $dilveApi = new DilveApi();

        $geslibApiReadFiles->readFolder();
        $log_id = $geslibApiLines->storeToLines();
        $geslibApiDb->processFromQueue('store_lines');
        $geslibApiDbManager->storeProducts();
        // Assuming you have a method to get queued tasks
        foreach ($geslibApiDbManager->getQueuedTasks('store_products') as $task) {
            $geslibApiDbManager->storeProduct($task->geslib_id, $task->data);
        }
        $geslibApiDbManager->setLogStatus( $log_id, 'processed');
        $dilveApi->scanProducts();
    }
}
