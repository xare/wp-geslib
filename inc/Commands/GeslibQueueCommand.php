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
        global $wpdb;
        $queueTable = $wpdb->prefix . 'geslib_queues'; // Ensure to replace with your actual table name

        $type = isset($args[0]) ? $args[0] : null; // Get the type from the command arguments

        if ($type) {
            // Delete tasks of a specific type
            $result = $wpdb->delete($queueTable, ['type' => $type]);
            if ($result !== false) {
                WP_CLI::success("Removed all tasks of type '{$type}' from the queue.");
            } else {
                WP_CLI::error("An error occurred while trying to remove tasks of type '{$type}' from the queue.");
            }
        } else {
             // Delete all tasks
            $result = $wpdb->query("DELETE FROM `{$queueTable}`");
            if ($result !== false) {
                WP_CLI::success('Removed all tasks from the queue.');
            } else {
                WP_CLI::error('An error occurred while trying to remove all tasks from the queue.');
            }
        }


    }
}
