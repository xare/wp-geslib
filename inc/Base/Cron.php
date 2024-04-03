<?php

/**
 * @package geslib
 */
namespace Inc\Geslib\Base;

use Inc\Dilve\Api\DilveApi;
use Inc\Geslib\Api\GeslibApiDbLinesManager;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;
use Inc\Geslib\Api\GeslibApiDbLogManager;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbProductsManager;
use Inc\Geslib\Api\GeslibApiDbQueueManager;
use Inc\Geslib\Api\GeslibApiLines;
use Inc\Geslib\Api\GeslibApiReadFiles;
use Inc\Geslib\Api\GeslibApiStoreData;

class Cron extends BaseController {

    public function register() {
        if ( ! wp_next_scheduled( 'geslib_cron_event' ) ) {
            wp_schedule_event( time(), 'daily', 'geslib_cron_event' );
        }
        add_action( 'geslib_cron_event', [ $this, 'geslib_cron_function' ] );
    }

    /**
     * geslib_cron_function
     *
     * @return void
     */
    function geslib_cron_function() {
        $geslibApiReadFiles = new GeslibApiReadFiles();
        $geslibApiLines = new GeslibApiLines();
        $geslibApiDbManager = new GeslibApiDbManager();
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
        $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
        $geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
        $geslibApiStoreData = new GeslibApiStoreData;
        $geslibApiReadFiles->readFolder();
        // Purge queues
        // Former calls to the cron may have stopped for some reason, before opening the next file.
        // Make sure the queues are processed before starting parsing more files.
        $queuetypes = ['store_products', 'build_content', 'store_autors', 'store_categories', 'store_editorials', 'store_lines', ];
        foreach( $queuetypes as $queuetype ) {
            $geslibApiDbQueueManager->processFromQueue( $queuetype );
        }
        while( $geslibApiDbLogManager->checkLoggedStatus() ) {
            $log_id = $geslibApiDbLogManager->getGeslibLoggedId();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Current Log_id', 'geslib_log', [
                'message' => 'Current log_id '.$log_id. ' to be queued.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'START', 'geslib_log', [
                'message' => 'We start the full PROCESS',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            if ( !$geslibApiDbLogManager->isQueued() ){
                $geslibApiDbLogManager->setLogStatus( $log_id, 'queued' );
                $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Set log to queued', 'geslib_log', [
                    'message' => 'Log '.$log_id. ' has been queued.',
                    'file' => basename(__FILE__),
                    'class' => __CLASS__,
                    'function' => __METHOD__,
                    'line' => __LINE__,
                ]);
            } else {
                $geslibApiDbQueueManager->deleteItemsFromQueue( 'store_lines' );
                $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Reset store_lines', 'geslib_log', [
                    'message' => 'Log '.$log_id. ' is already queued we delete store_lines from queue to start again.',
                    'file' => basename(__FILE__),
                    'class' => __CLASS__,
                    'function' => __METHOD__,
                    'line' => __LINE__,
                ]);
            }
            $geslibApiLines->storeToLines($log_id);
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to queue', 'geslib_queue', [
                'message' => 'We are moving data from files to geslib_queued(store_lines).',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbQueueManager->processFromQueue('store_lines');
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to lines', 'geslib_lines', [
                'message' => 'We are moving data from geslib_queued(store_lines) to geslib_lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbQueueManager->processFromQueue('build_content');
            //$geslibApiStoreData->storeAuthors();
            $geslibApiDbLoggerManager->geslibLogger( $log_id, 0, 'info', 'Store to Terms', 'autors', [
                'message' => 'Saving Authors.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            //$geslibApiStoreData->storeEditorials();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Store to Terms', 'editorials', [
                'message' => 'Saving Editorials.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            //$geslibApiDbProductsManager->storeProducts();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to Products 1', 'geslib_queues', [
                'message' => 'Saving Product data to geslib_queues.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);

            $geslibApiDbQueueManager->processFromQueue( 'store_editorials' );
            $geslibApiDbQueueManager->processFromQueue( 'store_autors' );
            $geslibApiDbQueueManager->processFromQueue( 'store_categories' );
            $geslibApiDbQueueManager->processFromQueue( 'store_products' );
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info','Store to Products 2', 'products', [
                'message' => 'Saving to woocommerce Products from geslib_queues.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLinesManager->truncateGeslibLines();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'truncate geslib_lines', 'geslib_lines', [
                'message' => 'Empty the table geslib_lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbLogManager->setLogStatus( $log_id, 'processed');
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info','set log to processed', 'geslib_log', [
                'message' => 'Set log '.$log_id.' to processed',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
        }
    }
}
