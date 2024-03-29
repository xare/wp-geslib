<?php

namespace Inc\Geslib\Base;

use Inc\Geslib\Api\GeslibApiDbLinesManager;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;
use Inc\Geslib\Api\GeslibApiDbLogManager;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbProductsManager;
use Inc\Geslib\Api\GeslibApiDbQueueManager;
use Inc\Geslib\Api\GeslibApiReadFiles;
use Inc\Geslib\Base\BaseController;

class GeslibUpdateValuesController extends BaseController {
    function register() {
        add_action('wp_ajax_get_geslib_statistics', [ $this, 'getGeslibStatistics'] );
        add_action('wp_ajax_nopriv_get_geslib_statistics', [ $this, 'getGeslibStatistics'] );
    }

    function getGeslibStatistics() {
        $geslibApiDbManager = new GeslibApiDbManager;
        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
        $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
        $geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
        $geslibApiReadFiles = new GeslibApiReadFiles;
        // Get data
        $data = [
            'total-products' => $geslibApiDbProductsManager->getTotalNumberOfProducts(),
            'total-files' => $geslibApiReadFiles->countFilesInFolder(),
            'total-logs' => $geslibApiDbLogManager->countGeslibLog(),
            'total-lines' => $geslibApiDbLinesManager->countGeslibLines(),
            'total-lines-queue'=>  $geslibApiDbQueueManager->countGeslibQueue('store_lines'),
            'total-products-queue' => $geslibApiDbQueueManager->countGeslibQueue('store_products'),
            'total-authors-queue' => $geslibApiDbQueueManager->countGeslibQueue('store_autors'),
            'total-editorials-queue' => $geslibApiDbQueueManager->countGeslibQueue('store_editorials'),
            'total-categories-queue' => $geslibApiDbQueueManager->countGeslibQueue('store_categories'),
            'queued-filename'=> $geslibApiDbLogManager->getLogQueuedFilename(),
            'geslib-log-logged' => $geslibApiDbLogManager->countGeslibLogStatus('logged'),
            'geslib-log-queued' => $geslibApiDbLogManager->countGeslibLogStatus('queued'),
            'geslib-log-processed' => $geslibApiDbLogManager->countGeslibLogStatus('processed'),
            'geslib-latest-loggers' => $geslibApiDbLoggerManager->getLatestLoggers(),
        ];
        // Send JSON response
        wp_send_json_success($data);
    }
}