<?php

namespace Inc\Geslib\Commands;

use WP_CLI;

/**
 * Class GeslibHelloCommand
 */
class GeslibHelloCommand {
	public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib hello', [$this, 'execute'] );
        }
    }
    /**
     * Prints a hello world message
     *
     * ## OPTIONS
     *
     * 
     * ## EXAMPLES
     *
     *     wp geslib hello
     *
     * @when after_wp_load
     */
    public function execute( $args, $assoc_args ) {
        WP_CLI::line( 'Hello, World!' );
    }
}

