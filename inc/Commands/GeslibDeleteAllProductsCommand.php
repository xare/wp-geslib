<?php

namespace Inc\Geslib\Commands;

use Inc\Geslib\Api\GeslibApiDbManager;
use Inc\Geslib\Api\GeslibApiDbProductsManager;
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
        $geslibApiDbProductsManager = new GeslibApiDbProductsManager;
        $geslibApiDbProductsManager->deleteAllProducts();

        WP_CLI::success( 'All products have been deleted.' );
    }
}
