<?php

namespace Inc\Geslib\Commands;

use WP_CLI;

/**
 * Class GeslibDeleteProductCategoriesCommand
 */
class GeslibDeleteEditorialsCommand {
	public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib deleteEditorials', [$this, 'execute'] );
        }
    }
    /**
     * Deletes al terms associated with Editorials custom taxonomy
     *
     * ## OPTIONS
     *
     * 
     * ## EXAMPLES
     *
     *     wp geslib deleteProductCategories
     *
     * @when after_wp_load
     */
    public function execute( $args, $assoc_args ) {
        $editorials = get_terms( array( 'taxonomy' => 'editorials', 'hide_empty' => false, ) );

        if ( empty( $editorials ) || is_wp_error( $editorials ) ) {
            WP_CLI::error( 'No product categories found.' );
            return;
        }

        foreach ( $editorials as $editorial ) {
            wp_delete_term( $editorial->term_id, 'editorials' );
        }

        WP_CLI::success( 'All product categories deleted.' );
    }
}


