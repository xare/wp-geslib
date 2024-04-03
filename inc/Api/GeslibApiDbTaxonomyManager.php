<?php

namespace Inc\Geslib\Api;

class GeslibApiDbTaxonomyManager extends GeslibApiDbManager {

    public function storeEditorials( $editorial ) {
		$term_name = $editorial->content;
		$term_slug = $this->_create_slug( $term_name );
		$term_description = $term_name;
		$term = term_exists( $term_name, 'editorials' ); // check if term already exists
		if ( 0 !== $term && null !== $term ) {
			// If the term exists, update it
			$term_data = wp_update_term( $term['term_id'], 'editorials', [
				'name' => $term_name,
				'slug' => $term_slug,
				'description' => $term_description,
			]);
    	} else {
        	// Otherwise, insert a new term
        	$term_data = wp_insert_term(
							$term_name,   // the term
							'editorials', // the taxonomy
							[
								'description'=> $term_description,
								'slug' => $term_slug,
							]);
    	}

		add_term_meta($term_data['term_id'],'editorial_geslib_id', $editorial->geslib_id);
		$editorial_geslib_id = get_term_meta( $term_data['term_id'], 'editorial_geslib_id', true );

        // Check for errors
        if ( is_wp_error($term_data) ) {
            // Handle the error here
            echo $term_data->get_error_message();
        }
		return get_term($term_data['term_id'], 'editorials');
    }

	public function storeAuthors( $author ) {
		$geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
		$term_name = $author->content;
		$term_slug = $this->_create_slug( $term_name );
		$term_description = $term_name;
		$term = term_exists( $term_name, 'autors' ); // check if term already exists
		if ( 0 !== $term && null !== $term ) {
			// If the term exists, update it
			$term_data = wp_update_term( $term['term_id'], 'autors', [
				'name' => $term_name,
				'slug' => $term_slug,
				'description' => $term_description,
			]);
    	} else {
        	// Otherwise, insert a new term
        	$term_data = wp_insert_term(
							$term_name,   // the term
							'autors', // the taxonomy
							[
								'description'=> $term_description,
								'slug' => $term_slug,
							]);
    	}

		add_term_meta($term_data['term_id'],'autor_geslib_id', $author->geslib_id);

        // Check for errors
        if ( is_wp_error($term_data) ) {
            // Handle the error here
            error_log($term_data->get_error_message());
			$geslibApiDbLoggerManager->geslibLogger( 0, $author->geslib_id, 'error', 'store authors', 'author', [
				'message' => 'Function storeAuthors: ' . $term_data->get_error_message() ,
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
			return false;
        }
		return get_term($term_data['term_id'], 'autors');
    }

    /**
     * storeCategory
     *
     * @param  int $geslib_id
     * @param  mixed $content
     * @return mixed
     */
    public function storeCategory( int $geslib_id, $content): mixed{
		$product_category = json_decode( $content );
		$geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
		$term_name = $this->geslibApiSanitize->utf8_encode($product_category->name);
		$term_slug = $this->_create_slug( $term_name );
		$term_description = $term_name;
		if( !term_exists( $term_name, 'product_cat' )) {
			// Create the category
			// TODO: No asume bien contenidos con acentos y signos raros.
			$result = wp_insert_term(
				$term_name, // the term
				'product_cat', // the taxonomy
				[
					'description' => 'Imported category',
					'slug'        => $term_slug
					// you can add other properties here as per your needs
				]
			);
			add_term_meta($result['term_id'],'category_geslib_id', $product_category->geslib_id);
			$category_geslib_id = get_term_meta( $result['term_id'], 'category_geslib_id', true );

			// Check for errors
			if (is_wp_error($result)) {
				// Handle error here
				error_log($result->get_error_message());
				return false;
			}

			// Return the created category
			return get_term($result['term_id'], 'product_cat');
		} else {
			error_log("Category already exists");
			return false;
		}
	}
	public function storeEditorial( $geslib_id, $content){
		$editorial = json_decode( $content);
		$geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
        $geslibApiSanitize = new GeslibApiSanitize();
		$term_name = $geslibApiSanitize->utf8_encode($editorial->name);
		$term_slug = $this->_create_slug( $term_name );
		$term_description = $term_name;
		$term = term_exists( $term_name, 'editorials' ); // check if term already exists
		if ( 0 !== $term && null !== $term ) {
			// If the term exists, update it
			$term_data = wp_update_term( $term['term_id'], 'editorials', [
				'name' => $term_name,
				'slug' => $term_slug,
				'description' => $term_description,
			]);
			update_term_meta($term_data['term_id'], 'editorial_geslib_id', $geslib_id);
    	} else {
        	// Otherwise, insert a new term
        	$term_data = wp_insert_term(
							$term_name,   // the term
							'editorials', // the taxonomy
							[
								'description'=> $term_description,
								'slug' => $term_slug,
							]);
			add_term_meta($term_data['term_id'],'editorial_geslib_id', $editorial->geslib_id);
    	}
		if ( is_wp_error($term_data) ) {
            // Handle the error here
            error_log($term_data->get_error_message());
			return false;
		}
		return get_term($term_data['term_id'], 'editorials');
	}
    /**
     * storeAuthor
     *
     * @param  int $geslib_id
     * @param  string $content
     * @return mixed
     */
    public function storeAuthor( int $geslib_id, string $content): mixed {
		$author = json_decode( $content );
		$geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
		$geslibApiSanitize = new GeslibApiSanitize;
		$term_name = $geslibApiSanitize->utf8_encode($author->name);
		$term_slug = $this->_create_slug( $term_name );
		$term_description = $term_name;
		$term = term_exists( $term_name, 'autors' ); // check if term already exists
		if ( 0 !== $term && null !== $term ) {
			// If the term exists, update it
			try {
				$term_data = wp_update_term( $term['term_id'], 'autors', [
					'name' => $term_name,
					'slug' => $term_slug,
					'description' => $term_description,
				]);
				if (is_wp_error($term_data)) {
					error_log($term_data->get_error_message());
					return false;
				}
				$term_meta = update_term_meta($term_data['term_id'], 'author_geslib_id', $geslib_id);
                return true;
			} catch (\Exception $exception) {
				error_log($exception->getMessage());
				return false;
			}
    	} else {
        	// Otherwise, insert a new term
        	$term_data = wp_insert_term(
							$term_name,   // the term
							'autors', // the taxonomy
							[
								'description'=> $term_description,
								'slug' => $term_slug
							]);
			add_term_meta($term_data['term_id'],'author_geslib_id', $geslib_id);
    	}
        // Check for errors
        if ( is_wp_error($term_data) ) {
            // Handle the error here
            error_log( $term_data->get_error_message());
			$geslibApiDbLoggerManager->geslibLogger( 0, $author->geslib_id, 'error', 'store authors', 'author', [
				'message' => 'Function storeAuthors: ' . $term_data->get_error_message() ,
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
			return false;
        }
		return get_term($term_data['term_id'], 'autors');
	}

    public function storeProductCategory($product_category): mixed {
        $geslibApiSanitize = new GeslibApiSanitize;
		// Make sure the category doesn't already exist
		if( !term_exists( $geslibApiSanitize->utf8_encode( $product_category->content ), 'product_cat' )) {
			// Create the category
			// TODO: No asume bien contenidos con acentos y signos raros.
			$result = wp_insert_term(
				$geslibApiSanitize->utf8_encode( $product_category->content ), // the term
				'product_cat', // the taxonomy
				[
					'description' => 'Imported category',
					'slug'        => sanitize_title($product_category->content)
					// you can add other properties here as per your needs
				]
			);
			add_term_meta($result['term_id'],'category_geslib_id', $product_category->geslib_id);
			$category_geslib_id = get_term_meta( $result['term_id'], 'category_geslib_id', true );

			// Check for errors
			if (is_wp_error($result)) {
				// Handle error here
				echo $result->get_error_message();
				return false;
			}

			// Return the created category
			return get_term($result['term_id'], 'product_cat');
		} else {
			error_log("Category already exists");
			return false;
		}
	}

	public function reorganizeProductCategories() {
		$terms = get_terms([
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		]);

		foreach ($terms as $term) {

			// Get the custom field value
			$category_geslib_id = get_term_meta($term->term_id, 'category_geslib_id', true);

			// Extract the parent category's geslib_id
			$parent_category_geslib_id = substr($category_geslib_id, 0, -2);
			// Find the parent term based on category_geslib_id
			if ( $parent_category_geslib_id != '' ) {
				$args = [
							'taxonomy' => 'product_cat',
							'hide_empty' => false,
							'meta_query' => [
												[
													'key' => 'category_geslib_id',
													'value' => $parent_category_geslib_id,
													'compare' => '='
												]
							]
						];
				$parent_terms = get_terms($args);

				if (!empty($parent_terms) && !is_wp_error($parent_terms)) {
					foreach($parent_terms as $parent_term) {
						if ( $parent_term != '' ) {
							// Update the term's parent with the parent term
							wp_update_term( $term->term_id, 'product_cat', [ 'parent' => $parent_term->term_id ] );
						}
					}
				} else {
					error_log('No terms were found or there was an error');
					continue;
				}
			}
		}
	}

	public function removeUncategorizedCategory() {
		// Get all products
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'fields' => 'ids',
		);

		// Loop for each product
		// For each product get all the categories.
		// If category === 'uncategorized' remove it.

	}
}