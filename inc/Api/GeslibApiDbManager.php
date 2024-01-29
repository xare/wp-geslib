<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiSanitize;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;
use WC_Product_Simple;
use WP_Query;

class GeslibApiDbManager {
	const GESLIB_LINES_TABLE = 'geslib_lines';
	const GESLIB_LOG_TABLE = 'geslib_log';
	const GESLIB_QUEUES_TABLE = 'geslib_queues';
	const GESLIB_LOGGER_TABLE = 'geslib_logger';
	static $geslibLinesKeys = [
		'log_id', // int relation oneToMany with geslib_log
		'geslib_id', // int
		'filename', // string inter000
		'action', // string insert|update|delete
		'entity', // string product | category | author | publisher
		'content', // json
		'queued' // boolean 0|1
	];
	static $geslibLogKeys = [
		'filename', // string inter000
		'start_date', // date
		'end_date', // date
		'status', // string waiting | enqueued | processed
		'lines_count', // int number of lines
	];
	static $geslibLoggerKeys = [
		'log_id', // int
		'geslib_id', // int
		'type', // string
		'action', // string
		'entity', // string log | lines | store_lines | store_products
		'metadata', // json
	];

	private $geslibApiSanitize;

	public function __construct() {
		$this->geslibApiSanitize = new GeslibApiSanitize();
	}

	/**
	 * countRows
	 * Count the number of rows in the geslib_log and geslib_lines tables
	 *
	 * @param  mixed $table
	 * @return int
	 */
	public function countRows( string $table ): int {
		global $wpdb;
		$table = $wpdb->prefix.'geslib_'.$table;
		return $wpdb->get_var( "SELECT COUNT(id) FROM $table" );
	}



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
		$term = term_exists( $term_name, 'autores' ); // check if term already exists
		if ( 0 !== $term && null !== $term ) {
			// If the term exists, update it
			$term_data = wp_update_term( $term['term_id'], 'autores', [
				'name' => $term_name,
				'slug' => $term_slug,
				'description' => $term_description,
			]);
    	} else {
        	// Otherwise, insert a new term
        	$term_data = wp_insert_term(
							$term_name,   // the term
							'autores', // the taxonomy
							[
								'description'=> $term_description,
								'slug' => $term_slug,
							]);
							$geslibApiDbLoggerManager->geslibLogger( 0, $author->geslib_id, 'info', 'store authors', 'author', [
								'message' => 'Function storeAuthors: ' . $term_name.' Desc.: '.$term_description.' Slug '. $term_slug ,
								'file' => basename(__FILE__),
								'class' => __CLASS__,
								'function' => __METHOD__,
								'line' => __LINE__,
							]);
    	}

		add_term_meta($term_data['term_id'],'autor_geslib_id', $author->geslib_id);

        // Check for errors
        if ( is_wp_error($term_data) ) {
            // Handle the error here
            echo $term_data->get_error_message();
			$geslibApiDbLoggerManager->geslibLogger( 0, $author->geslib_id, 'error', 'store authors', 'author', [
				'message' => 'Function storeAuthors: ' . $term_data->get_error_message() ,
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
        }
		return get_term($term_data['term_id'], 'authors');
    }

    public function storeCategory( $geslib_id, $content){
		$product_category = json_decode( $content );
		$geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
		$term_name = $product_category->content;
		$term_slug = $this->_create_slug( $term_name );
		$term_description = $term_name;
		if( !term_exists( $this->geslibApiSanitize->utf8_encode( $product_category->content ), 'product_cat' )) {
			// Create the category
			// TODO: No asume bien contenidos con acentos y signos raros.
			$result = wp_insert_term(
				$this->geslibApiSanitize->utf8_encode( $product_category->content ), // the term
				'product_cat', // the taxonomy
				[
					'description' => 'Imported category',
					'slug'        => sanitize_title($product_category->content)
					// you can add other properties here as per your needs
				]
			);
			$geslibApiDbLoggerManager->geslibLogger( 0, $product_category->geslib_id, 'info', 'store category', 'category', [
				'message' => 'Function storecategory: ' . $product_category->content.' Desc.: '.$term_description.' Slug '. $term_slug ,
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
			add_term_meta($result['term_id'],'category_geslib_id', $product_category->geslib_id);
			$category_geslib_id = get_term_meta( $result['term_id'], 'category_geslib_id', true );

			// Check for errors
			if (is_wp_error($result)) {
				// Handle error here
				echo $result->get_error_message();
				return null;
			}

			// Return the created category
			return get_term($result['term_id'], 'product_cat');
		} else {
			echo "Category already exists";
			return null;
		}
	}
	public function storeEditorial( $geslib_id, $content){
		$editorial = json_decode( $content);
		$geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
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
							$geslibApiDbLoggerManager->geslibLogger( 0, $editorial->geslib_id, 'info', 'store editorial', 'editorial', [
								'message' => 'Function storeeditorial: ' . $term_name.' Desc.: '.$term_description.' Slug '. $term_slug ,
								'file' => basename(__FILE__),
								'class' => __CLASS__,
								'function' => __METHOD__,
								'line' => __LINE__,
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
    public function storeAuthor( $geslib_id, $content){
		$author = json_decode( $content, true );
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
							$geslibApiDbLoggerManager->geslibLogger( 0, $author->geslib_id, 'info', 'store author', 'author', [
								'message' => 'Function storeAuthor: ' . $term_name.' Desc.: '.$term_description.' Slug '. $term_slug ,
								'file' => basename(__FILE__),
								'class' => __CLASS__,
								'function' => __METHOD__,
								'line' => __LINE__,
							]);
    	}

		add_term_meta($term_data['term_id'],'autor_geslib_id', $author->geslib_id);

        // Check for errors
        if ( is_wp_error($term_data) ) {
            // Handle the error here
            echo $term_data->get_error_message();
			$geslibApiDbLoggerManager->geslibLogger( 0, $author->geslib_id, 'error', 'store authors', 'author', [
				'message' => 'Function storeAuthors: ' . $term_data->get_error_message() ,
				'file' => basename(__FILE__),
				'class' => __CLASS__,
				'function' => __METHOD__,
				'line' => __LINE__,
			]);
        }
		return get_term($term_data['term_id'], 'autors');
	}

    private function _create_slug($term_name) {
		// convert to lowercase
		$term_name = strtolower($term_name);

		// remove punctuation
		$term_name = preg_replace("/[.,:;!?(){}[\]<>%$#@^*+=|~`]/", "", $term_name);

		// replace spaces with underscores
		$term_slug = str_replace(" ", "_", $term_name);

		return $term_slug;
  	}


	// FROM HERE WE PRESENT FUNCTIONS THAT WILL STORE GESLIB LINES ROWS TO WORDPRESS

	public function getEditorialsFromGeslibLines() {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LINES_TABLE;
		$query = $wpdb->prepare( "SELECT * FROM {$table} WHERE entity=%s",'editorial');
		return $wpdb->get_results($query);
	}

	public function getProductCategoriesFromGeslibLines() {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LINES_TABLE;
		$query = $wpdb->prepare ("SELECT * FROM {$table} WHERE entity=%s",'product_cat');

		return $wpdb->get_results($query);
	}

	public function storeProductCategory($product_category) {

		// Make sure the category doesn't already exist
		if( !term_exists( $this->geslibApiSanitize->utf8_encode( $product_category->content ), 'product_cat' )) {
			// Create the category
			// TODO: No asume bien contenidos con acentos y signos raros.
			$result = wp_insert_term(
				$this->geslibApiSanitize->utf8_encode( $product_category->content ), // the term
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
				return null;
			}

			// Return the created category
			return get_term($result['term_id'], 'product_cat');
		} else {
			echo "Category already exists";
			return null;
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
					// No terms found or there was an error
				}
			}
		}
	}

	public function getGeslibTable( $table_suffix ) {
		global $wpdb;
		$table_name = $wpdb->prefix .'geslib_'. $table_suffix;
		return $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
    }

}
