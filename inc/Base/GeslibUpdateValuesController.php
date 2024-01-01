<?php

namespace Inc\Geslib\Base;

use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiReadFiles;
use Inc\Geslib\Base\BaseController;

class GeslibUpdateValuesController extends BaseController {
    function register() {
        add_action('wp_ajax_get_geslib_statistics', [ $this, 'getGeslibStatistics'] );
        add_action('wp_ajax_nopriv_get_geslib_statistics', [ $this, 'getGeslibStatistics'] );
    }

    function getGeslibStatistics() {
        $geslibApiDbManager = new GeslibApiDbManager;
        $geslibApiReadFiles = new GeslibApiReadFiles;
        // Get data
        $data = [
            'total-products' => $geslibApiDbManager->get_total_number_of_products(),
            'total-files' => $geslibApiReadFiles->countFilesInFolder(),
            'total-logs' => $geslibApiDbManager->countGeslibLog(),
            'total-lines' => $geslibApiDbManager->countGeslibLines(),
            'total-lines-queue'=>  $geslibApiDbManager->countGeslibQueue('store_lines'),
            'total-products-queue' => $geslibApiDbManager->countGeslibQueue('store_products'),
            'queued-filename'=> $geslibApiDbManager->getLogQueuedFilename(),
            'geslib-log-logged' => $geslibApiDbManager->countGeslibLogStatus('logged'),
            'geslib-log-queued' => $geslibApiDbManager->countGeslibLogStatus('queued'),
            'geslib-log-processed' => $geslibApiDbManager->countGeslibLogStatus('processed'),
        ];
        // Send JSON response
        wp_send_json_success($data);
    }
}