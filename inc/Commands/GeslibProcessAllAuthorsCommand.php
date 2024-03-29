<?php
/**
* WP-CLI Commands
*/

namespace Inc\Geslib\Commands;
use WP_CLI;

class GeslibProcessAllCommand {
    public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib processAllAuthors', [ $this, 'execute' ], [
              'synopsis' => [
                    [
                        'type'        => 'flag',
                        'name'        => 'process-all-authors',
                        'description' => 'Realise all the process for authors.',
                        'optional'    => true,
                    ],
                ],
            ]);
        }
    }

    /**
    * Process all authors.
    *
    * ## OPTIONS
    *
    * [--name=<name>]
    * : The name of the person to greet.
    *
    * ## EXAMPLES
    *
    * wp geslib processAllAuthors
    * @when after_wp_load
    */
    public function execute ($args, $assoc_args) {

    }
}
