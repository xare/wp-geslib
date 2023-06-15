<?php

namespace Inc\Geslib\Commands;

use WP_CLI;

/**
 * Class for a custom WP-CLI command to delete all terms in a specified taxonomy.
 */
class GeslibDeleteAllTermsCommand {
	
	public function register() {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::add_command( 'geslib deleteAllTerms', [$this, 'execute'] );
        }
    }
	
    /**
     * Delete all terms in a specified taxonomy.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : The name of the taxonomy.
     *
     * ## EXAMPLES
     *
     *     wp geslib deleteAllTerms <taxonomyName>
     *
     */
    public function execute( $args, $assoc_args ) {
        $taxonomy = $args[0];
        
        // Validate taxonomy
        if ( ! taxonomy_exists( $taxonomy ) ) {
            WP_CLI::error( 'The taxonomy does not exist.' );
            return;
        }

        // Get all terms in the taxonomy
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ) );

        // Check for errors
        if ( is_wp_error( $terms ) ) {
            WP_CLI::error( 'Failed to get terms: ' . $terms->get_error_message() );
            return;
        }

        // Delete each term
        foreach ( $terms as $term ) {
            wp_delete_term( $term->term_id, $taxonomy );
        }

        WP_CLI::success( 'All terms have been deleted.' );
    }
}

