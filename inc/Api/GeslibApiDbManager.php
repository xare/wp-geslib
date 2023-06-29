<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiSanitize;
use WC_Product_Simple;
use WP_Query;

class GeslibApiDbManager {
	const GESLIB_LINES_TABLE = 'geslib_lines';
	const GESLIB_LOG_TABLE = 'geslib_log';
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
		'lines_count', // int number of lines 
		'status', // string waiting | enqueued | processed
	];

	private $geslibApiSanitize;

	public function __construct() {
		$this->geslibApiSanitize = new GeslibApiSanitize();
	}
	
	public function insertLogData( $filename, $status, $linesCount  ) {
		global $wpdb;
		$geslibLogValues = [
			$filename,
			date('Y-m-d H:i:s'),
			null,
			$linesCount,
			$status
		];
		$insertArray = array_combine(self::$geslibLogKeys, $geslibLogValues);
		$wpdb->insert($wpdb->prefix . self::GESLIB_LOG_TABLE,
						$insertArray,
						['%s', '%s', '%s', '%d', '%s']);
	}

	/** 
	 * Count the number of rows in the geslib_row and geslib_lines tables
	 */

	public function countRows($table){
		global $wpdb;
		$table = $wpdb->prefix.'geslib_'.$table;
		return $wpdb->get_var( "SELECT COUNT(id) FROM $table" );
	}
	/**
     * Check if the filename exists in the wpgeslib_log table.
     *
     * @param string $filename
     * @return bool
     */
    public function isFilenameExists($filename) {
        global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LOG_TABLE;
		$query = $wpdb->prepare(
            "SELECT COUNT(*) 
			FROM {$table}
			WHERE filename = %s",
            $filename
        );
        $count = $wpdb->get_var($query);

        return $count > 0;
    }

	public function getLogQueuedFile() {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LOG_TABLE;
		
		$query = $wpdb->prepare(
            "SELECT filename 
			FROM {$table}
			WHERE status = '%s'",
            'logged'
        );

        return $wpdb->get_var($query);
	}

	public function insert2geslibLines( $data_array ) {
		//var_dump($data_array);
		global $wpdb;
		$wpdb->insert( $wpdb->prefix.self::GESLIB_LINES_TABLE, $data_array);
	}
	
	
  
  	public function _readGeslibLinesTable() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::GESLIB_LINES_TABLE;
		$query = $wpdb->prepare( "SELECT * FROM {$table_name}" );
		$results = $wpdb->get_results($query);
		
		foreach($results as $result) {
			$this->_storeData($result->type, $result->id, $result->content);
		}
    }

	public function updateGeslibLines( $geslib_id, $type, $content){
		global $wpdb;
		//var_dump($content);
		$wpdb->update(
			$wpdb->prefix.self::GESLIB_LINES_TABLE,
			['content' => $content],
			[
				'geslib_id' => $geslib_id,
				'entity' => $type
			],
			'%s',
			['%d','%s']
		);
	}

	public function insertGeslibLines( $geslib_id, $log_id, $content ){
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix.self::GESLIB_LINES_TABLE,
			[
				'geslib_id' => $geslib_id,
				'content' => $content
			],
		);
	}

	public function insertProductData($content_array, $action, $log_id) {
		
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix.self::GESLIB_LINES_TABLE,
			[
				'log_id' => $log_id,
				'geslib_id' => $content_array['geslib_id'],
				'action' => $action,
				'entity' => 'product',
				'content' => json_encode($content_array),
				'queued' => 1
			],
		);
	}

	public function getLogId($filename){
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LOG_TABLE;
		$query = $wpdb->prepare("SELECT 
									id 
								FROM $table 
								WHERE filename='%s'", 
								$filename);
		return $wpdb->get_var($query);

	}
	
	private function _storeData( $type, $geslib_id, $content ) {
		$store_data=[];
		$function_name = 'store'.$type[0];
		if (method_exists($this, $function_name)) {
			$store_data[] = $this->{$function_name}($geslib_id,$content);
		} else {
			$store_data[] = 'EMPTY';
		}
      
      	return $store_data;
    }

    public function storeEditorials( $editorial ) {
		//$content = json_decode( $editorial->content, true );
		//$content = json_decode( $editorial->content, true );
		//$term_name = $content['editorial'];
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

	public function storeProducts() {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LINES_TABLE;

		$query = $wpdb->prepare("SELECT * FROM {$table} WHERE action=%s and entity=%s",['A','product']);
		$product_geslib_lines = $wpdb->get_results($query);
		foreach($product_geslib_lines as $product_geslib_line) {
			$this->storeProduct($product_geslib_line->geslib_id, $product_geslib_line->content);
		}
	}

    public function storeProduct($geslib_id, $content){
		// Check if product already exists
		$content = json_decode($content, true);
		$ean = $content['ean'];
		$author = $content['author'];
		$num_paginas = $content['num_paginas'];
		$editorial_geslib_id = $content['editorial'];
		$book_name = $content['description'];
		var_dump($content);
		if ( isset( $content['sinopsis'] ) )
			$book_description = $content['sinopsis'];
		$book_price = floatval(str_replace(',', '.', $content['pvp']));
		$existing_product = null;
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'title'          => $book_name
		);
		
		$products = new WP_Query($args);
		
		if($products->have_posts()) {
			while ($products->have_posts()) {
				$products->the_post();
				$existing_product = $products->post;
			}
			wp_reset_postdata();
		}
		
		if ($existing_product) {
			// If product exists, get an instance of WC_Product for the existing product
			$product = wc_get_product($existing_product->ID);
		} else {
			// If product does not exist, create a new instance of WC_Product_Simple
			$product = new WC_Product_Simple();
			$product->set_name($book_name); // name is only set for new products
		}

		// Set or update product data
		
		$product->set_description($book_description);
		$product->set_status("publish");  // can also be 'draft' or 'pending'
		$product->set_catalog_visibility('visible');  // or 'hidden'
		$product->set_price($book_price);
		$product->set_regular_price($book_price);
		// ... Set other product properties

		// Save the product to the database and get its ID
		$product_id = $product->save();
		if(isset($ean))
			update_post_meta($product_id, '_ean', $ean);
		if(isset($author))
			update_post_meta($product_id, '_author', $author);
		if(isset($ean))
			update_post_meta($product_id, '_num_paginas', $num_paginas);

		// Get the integer value from the content array
		$editorial_id = intval($content['editorial']); 

		// Get terms
		$args = array(
			'taxonomy' => 'editorials', // the taxonomy for the term
			'hide_empty' => false, // also retrieve terms which are not used yet
			'meta_query' => [
					['key'       => 'editorial_geslib_id', // your meta key
					'value'     => $editorial_id, // your meta value
					'compare'   => '='],
				],
			);

		$terms = get_terms($args);
		// Check if any term found
		if (!empty($terms) && !is_wp_error($terms)) {
    		// Terms found, get the first term
    		$editorial_term = $terms[0]->term_id;
			// Assign the product to the editorial taxonomy term
			wp_set_object_terms($product_id, $editorial_term, 'editorials', true);
		}

		// APPEND CATEGORIES

		if( $content['categories'] != null && count( $content['categories']) > 0 ) {
			foreach ( $content['categories'] as $key => $value ) {
				$category_id = intval($key);
				// Get terms
				$cat_args = [
					'taxonomy' => 'product_cat', // the taxonomy for the term
					'hide_empty' => false, // also retrieve terms which are not used yet
					'meta_query' => [
							['key'       => 'category_geslib_id', // your meta key
							'value'     => $category_id, // your meta value
							'compare'   => '='],
						],
					];

					$categories = get_terms($cat_args);
					// Check if any term found
					if (!empty($categories) && !is_wp_error($categories)) {
						// Terms found, get the first term
						$category_term = $categories[0]->term_id;
						// Assign the product to the editorial taxonomy term
						wp_set_object_terms($product_id, $category_term, 'product_cat', true);
					}
			}
		}
		return $product_id;
    }
    public function storecategory($geslib_id,$content){}
    public function storeauthor($geslib_id,$content){}

    private function _create_slug($term_name) {
		// convert to lowercase
		$term_name = strtolower($term_name);
	  
		// remove punctuation
		$term_name = preg_replace("/[.,:;!?(){}[\]<>%$#@^*+=|~`]/", "", $term_name);
	  
		// replace spaces with underscores
		$term_slug = str_replace(" ", "_", $term_name);
	  
		return $term_slug;
  	}

	public function fetchContent($geslib_id, $type) {
		global $wpdb;
		$table = $wpdb->prefix.self::GESLIB_LINES_TABLE;

		$query = $wpdb->prepare( 
						"SELECT 
							content 
						FROM $table
						WHERE 
							geslib_id = '%d'
						AND 
							entity = '%s'",
						$geslib_id, $type);
		return $wpdb->get_var( $query );
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
		var_dump($wpdb->get_results($query));
		return $wpdb->get_results($query);
	}

	public function storeProductCategories($product_category) {
		var_dump($product_category->geslib_id);
	
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

	
	
	
}
