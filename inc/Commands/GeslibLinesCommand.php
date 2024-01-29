<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\Encoding;
use Inc\Geslib\Api\GeslibApiDbLogManager;
use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbQueueManager;
use Inc\Geslib\Api\GeslibApiLog;
use Inc\Geslib\Api\GeslibApiLines;
use Inc\Geslib\Api\GeslibApiReadFiles;
use WC_Product_Simple;
use WP_CLI;

class GeslibLinesCommand {

    public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib lines', [$this, 'execute'], [
                'synopsis' => [
                    [
                        'type'        => 'flag',
                        'name'        => 'process-store-lines',
                        'description' => 'Process the queue for store_lines tasks.',
                        'optional'    => true,
                    ],
                ],
            ]);
        }
    }
    /**
    * Store filename data in geslib_lines
    *
    *
    * ## EXAMPLES
    *
    * wp geslib lines
    * wp geslib lines --process-store-lines
    *
    * @when after_wp_load
    */

    public function execute( $args, $assoc_args ) {
        if ( isset( $assoc_args[ 'process-store-lines' ] ) ) {
            $this->processStoreLines();
            return;
        }
        $geslibApiLog = new GeslibApiLog();
        $geslibApiLines = new GeslibApiLines();
        $geslibApiDb = new GeslibApiDbManager();
        $geslibApiDbLogManager = new GeslibApiDbLogManager;
        $log_id = $geslibApiDbLogManager->getGeslibLoggedId();
        $geslibApiLines->storeToLines();
        $geslibApiDbLogManager->setLogStatus($log_id, 'queued');
        WP_CLI::line( 'Data has not been saved to geslib_lines!' );
    }

    public function processStoreLines( ) {
        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
        $geslibApiDbQueueManager->processFromQueue('store_lines');
    }



   /*  function deleteProcessed( $processedIds ) {
        global $wpdb;
        $tableName = $wpdb->prefix . 'geslib_queues';
        $idList = implode( ',', array_map( 'intval', $processedIds ) );
        $wpdb->query( "DELETE FROM `$tableName` WHERE `id` IN ( $idList )" );
    } */
}