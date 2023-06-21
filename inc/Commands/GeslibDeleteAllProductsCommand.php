<?php

namespace Inc\Geslib\Commands;

use WP_CLI;
use WP_Query;

/**
 * Class for a custom WP-CLI command to delete all WooCommerce products.
 */
class GeslibDeleteAllProductsCommand {
	
	public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib deleteAllProducts', [$this, 'execute'] );
        }
    }
	
    /**
     * Delete all WooCommerce products.
     *
     * ## EXAMPLES
     *
     *     wp geslib deleteAllProducts
     *
     */
    public function execute( $args, $assoc_args ) {
        // Query for all products
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );
        $query = new WP_Query( $args );

        // Loop through all products and delete
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();
            wp_delete_post( $id, true );
        }

        // Reset query data
        wp_reset_postdata();

        WP_CLI::success( 'All products have been deleted.' );
    }
}
