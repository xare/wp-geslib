<?php
/**
 * WP-CLI Commands for managing geslib_queue
 */

namespace Inc\Geslib\Commands;

use WP_CLI;

class GeslibQueueCommand {

    /**
     * Register WP-CLI commands
     */
    public function register() {
        if (class_exists('WP_CLI')) {
            WP_CLI::add_command('geslib clear_queue', [$this, 'clearQueue']);
        }
    }

    /**
     * Clear tasks from geslib_queue
     *
     * ## OPTIONS
     *
     * [<type>]
     * : The type of the task to remove. If not provided, all tasks will be removed.
     *
     * ## EXAMPLES
     *
     *     wp geslib clear_queue
     *     wp geslib clear_queue store_lines
     *
     * @when after_wp_load
     */
    public function clearQueue($args, $assoc_args) {
        $queue = get_option('geslib_queue', []);

        $type = isset($args[0]) ? $args[0] : null;  // Get the type from the command arguments
        $newQueue = [];

        if ($type) {
            foreach ($queue as $task) {
                if ($task['type'] !== $type) {
                    $newQueue[] = $task;
                }
            }
             // Update the queue with remaining tasks
            update_option('geslib_queue', $newQueue);
            WP_CLI::success("Removed all tasks of type '{$type}' from the queue.");
        } else {
             // Update the queue with remaining tasks
            update_option('geslib_queue', $newQueue);
            WP_CLI::success('Removed all tasks from the queue.');
        }


    }
}
