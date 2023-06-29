<?php

namespace Inc\Geslib\Commands;

use WP_CLI;

/**
 * Class GeslibDeleteProductCategoriesCommand
 */
class GeslibDeleteProductCategoriesCommand {
	public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib deleteProductCategories', [$this, 'execute'] );
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
     *     wp geslib deleteProductCategories
     *
     * @when after_wp_load
     */
    public function execute( $args, $assoc_args ) {
        $categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, ) );

        if ( empty( $categories ) || is_wp_error( $categories ) ) {
            WP_CLI::error( 'No product categories found.' );
            return;
        }

        foreach ( $categories as $category ) {
            wp_delete_term( $category->term_id, 'product_cat' );
        }

        WP_CLI::success( 'All product categories deleted.' );
    }
}


