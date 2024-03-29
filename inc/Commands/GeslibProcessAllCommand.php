<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbLinesManager;
use Inc\Geslib\Api\GeslibApiDbLogManager;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbProductsManager;
use Inc\Geslib\Api\GeslibApiDbQueueManager;
use Inc\Geslib\Api\GeslibApiLines;
use Inc\Geslib\Api\GeslibApiReadFiles;
use Inc\Geslib\Api\GeslibApiStoreData;
use WP_CLI;

class GeslibProcessAllCommand {
    public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib processAll', [ $this, 'execute' ], [
              'synopsis' => [
                    [
                        'type'        => 'flag',
                        'name'        => 'process-all',
                        'description' => 'Realise all the process.',
                        'optional'    => true,
                    ],
                ],
            ]);
        }
    }

    /**
    * Process all products, categories and authors.
    *
    * ## OPTIONS
    *
    * [--name=<name>]
    * : The name of the person to greet.
    *
    * ## EXAMPLES
    *
    * wp geslib processAll
    * wp geslib processAll --process-store-products
    * @when after_wp_load
    */
    public function execute ($args, $assoc_args) {
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $geslibApiDbLinesManager = new GeslibApiDbLinesManager;
        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
        $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
        $geslibApiLines = new GeslibApiLines;
        $geslibApiStoreData = new GeslibApiStoreData;
        $geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
        $geslibApiReadFiles = new GeslibApiReadFiles;
        $geslibApiReadFiles->readFolder();
        while ( $geslibApiDbLogManager->checkLoggedStatus() ) {
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
            WP_CLI::line( 'Enviamos las lineas a la cola de proceso. ');
            $stored_lines = $geslibApiLines->storeToLines($log_id);
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to queue', 'geslib_queue', [
                'message' => 'We are moving data from files to geslib_queued(store_lines).',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            WP_CLI::line( 'Procesamos las lineas de la cola en modo store_lines. ');
            $geslibApiDbQueueManager->processFromQueue( 'store_lines' );
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to lines', 'geslib_lines', [
                'message' => 'We are moving data from geslib_queued(store_lines) to geslib_lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiStoreData->storeAuthors();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to Terms', 'authors', [
                'message' => 'Saving Authors.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiStoreData->storeEditorials();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0,'info', 'Store to Terms', 'editorials', [
                'message' => 'Saving Editorials.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            WP_CLI::line( 'Enviamos los productos a la cola de proceso. ');
            $geslibApiDbProductsManager->storeProducts();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'Store to Products 1', 'geslib_queues', [
                'message' => 'Saving Product data to geslib_queues.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            WP_CLI::line( 'Empezamos a procesar los productos y a guardarlos en la tienda. ');
            $geslibApiDbQueueManager->processFromQueue( 'store_products' );
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info','Store to Products 1', 'products', [
                'message' => 'Saving to woocommerce Products from geslib_queues.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            $geslibApiDbQueueManager->processFromQueue( 'store_editorials' );
            $geslibApiDbQueueManager->processFromQueue( 'store_autors' );
            $geslibApiDbQueueManager->processFromQueue( 'store_categories' );

            WP_CLI::line( 'Se ha terminado de procesar la cola store_products. ');
            $geslibApiDbLinesManager->truncateGeslibLines();
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info', 'truncate geslib_lines', 'geslib_lines', [
                'message' => 'Empty the table geslib_lines.',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            WP_CLI::line( 'Geslib Lines ha sido borrado. ');
            $geslibApiDbLogManager->setLogStatus( $log_id, 'processed');
            $geslibApiDbLoggerManager->geslibLogger($log_id, 0, 'info','set log to processed', 'geslib_log', [
                'message' => 'Set log '.$log_id.' to processed',
                'file' => basename(__FILE__),
                'class' => __CLASS__,
                'function' => __METHOD__,
                'line' => __LINE__,
            ]);
            WP_CLI::line( 'Archivo en geslib log marcado como procesado para log_id. '.$log_id);
        }
        WP_CLI::line( 'The process is over. ');
    }
}