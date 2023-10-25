<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\Encoding;
use Inc\Geslib\Api\GeslibApiDbManager;
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
        $log_id = $geslibApiDb->getGeslibLoggedId();
        $geslibApiLines->storeToLines();
        $geslibApiDb->setLogStatus($log_id, 'queued');
        WP_CLI::line( 'Data has not been saved to geslib_lines!' );
    }

    public function processStoreLines() {
        $queue = get_option('geslib_queue', []);
        //var_dump($queue);
        $newQueue = [];  // To hold tasks that are not of type 'store_lines'
        $geslibApiLines = new GeslibApiLines();
        foreach ($queue as $index => $task) {
            if ($task['type'] === 'store_lines') {
                // Process the task
                WP_CLI::line("Processsing line: {$task['line']}");
                $geslibApiLines->readLine($task['line'], $task['log_id']);
                WP_CLI::line("Processed task with log_id: {$task['log_id']}");

                // Fetch the latest queue
                $latestQueue = get_option('geslib_queue', []);
                // Remove the processed task
                unset($latestQueue[$index]);
                // Re-index array keys
                $latestQueue = array_values($latestQueue);
                // Update the WordPress option with the new queue
                update_option('geslib_queue', $latestQueue);
            } else {
                // Keep the task in the queue for later
                $newQueue[] = $task;
            }
        }

        // Update the queue with remaining tasks
        update_option('geslib_queue', $newQueue);

        WP_CLI::success('Processed all tasks of type "store_lines".');
    }
}