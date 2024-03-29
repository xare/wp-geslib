<?php

namespace Inc\Geslib\Api;

use WP_Query;
use WC_Product_Simple;
use Inc\Geslib\Api\GeslibApiDbLoggerManager;

class GeslibApiDbProductsManager extends GeslibApiDbManager {
	protected $geslibApiDbLoggerManager;

	public function __construct(){
		$this->geslibApiDbLoggerManager = new GeslibApiDbLoggerManager;
	}

    /**
     * storeProducts
     *
     * @return void
     */
    public function storeProducts() {
		global $wpdb;
        $geslibApiDbQueueManager = new GeslibApiDbQueueManager;
		$geslibQueuesTable = $wpdb->prefix.self::GESLIB_QUEUES_TABLE;

		// Create a queue for storing products.
        $actions = [
            'A', // Add
			'M', // Modify
            'B'  // Delete
        ];

		foreach ( $actions as $actionSet ) {
			$query = $wpdb->prepare(
				"SELECT * FROM {$geslibQueuesTable}
				WHERE action='%s'
				AND entity='%s'
				AND type='%s'",
				[ $actionSet, 'product', 'build_content']
			);
			$lines = $wpdb->get_results( $query );
			$batch_size = 3000; // Choose a reasonable batch size
			$batch = [];
			foreach ( $lines as $line ) {
				$item = [
					'geslib_id' => $line->geslib_id,
					'log_id' => $line->log_id,
					'data' => $line->data,
					'type' => 'store_products'  // type to identify the task in processQueue
				];
				if(isset($line->data->action)) {
					$item['action'] = $line->data->action;
				}
				$batch[] = $item;
				if ( count( $batch ) >= $batch_size ) {
					$geslibApiDbQueueManager->insertProductsIntoQueue( $batch );
					$batch = [];
				}
			}
			// Don't forget the last batch
			if ( !empty( $batch ) ) {
				$geslibApiDbQueueManager->insertProductsIntoQueue( $batch );
			}
		}
	}

    /**
     * storeProduct
     *
     * @param  int $geslib_id
     * @param  string $content
     * @return int
     */
    public function storeProduct( int $geslib_id, string $content ): int {
		// Check if product already exists
		$content = json_decode( $content, true );
		$ean = isset($content['ean']) ? $content['ean'] : '';
		$author = isset($content['author']) ? $content['author'] : '';
		$num_paginas = isset($content['num_paginas'])? $content['num_paginas'] : 0;
		$editorial_geslib_id = isset($content['editorial']) ? $content['editorial']:'';
		$book_name = isset($content['description']) ? $content['description']:'';;
		$peso = isset($content['peso']) ? $content['peso']/1000 : 0;
		$book_description = '';
		$stock = isset($content['stock']) ? $content['stock'] : 0;

		if ( isset( $content['sinopsis'] ) )
			$book_description = isset( $content['sinopsis']) ? $content['sinopsis'] : '';

			$book_price = ( isset( $content['pvp'] ) && $content['pvp'] != null ) ? floatval(str_replace(',', '.', $content['pvp'])) : 0.00;
			$existing_product = null;
			$args = [
				'post_type'      => 'product',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'title'          => $book_name
			];

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
				$product = new WC_Product_Simple;
				$product->set_name($book_name); // name is only set for new products
			}

			// Set or update product data

			$product->set_description($book_description);
			$product->set_status("publish");  // can also be 'draft' or 'pending'
			$product->set_catalog_visibility('visible');  // or 'hidden'
			$product->set_price($book_price);
			$product->set_regular_price($book_price);
			$product->set_weight($peso);
			$product->set_manage_stock( true );
			$product->set_stock_quantity($stock);

			// ... Set other product properties

			// Save the product to the database and get its ID
			try {
				$product_id = $product->save();
			} catch(\Exception $exception) {
				error_log('Failed to store product'. $exception->getMessage());
				$this->geslibApiDbLoggerManager->geslibLogger(0, $geslib_id, 'error', 'store_product', 'woocommerce_product', [
                    'message' => "Product has NOT been queued: ".$exception->getMessage(),
                    'file' => basename(__FILE__),
                    'class' => __CLASS__,
                    'function' => __METHOD__,
                    'line' => __LINE__,
                ]);
			}

			if( isset($ean) ){
				update_post_meta($product_id, '_ean', $ean);
				update_post_meta($product_id, '_num_paginas', $num_paginas);
			}
			if( isset($author) ) {
				update_post_meta($product_id, '_author', $author);
			}
			update_post_meta($product_id, 'geslib_id', $geslib_id);
			// Get the integer value from the content array
			$editorial_id = (isset($content['editorial'])) ? intval($content['editorial']) : 0;

			// Get terms
			$args = [
				'taxonomy' => 'editorials', // the taxonomy for the term
				'hide_empty' => false, // also retrieve terms which are not used yet
				'meta_query' => [
						['key'       => 'editorial_geslib_id', // your meta key
						'value'     => $editorial_id, // your meta value
						'compare'   => '='],
					],
				];
			$terms = get_terms($args);
			// Check if any term found
			if (!empty($terms) && !is_wp_error($terms)) {
				// Terms found, get the first term
				$editorial_term = $terms[0]->term_id;
				// Assign the product to the editorial taxonomy term
				try {
					wp_set_object_terms($product_id, $editorial_term, 'editorials', true);
				} catch(\Exception $exception) {
					error_log('Term was not properly assigned to product: '. $exception->getMessage());
					$this->geslibApiDbLoggerManager->geslibLogger(0, $geslib_id, 'error', 'store_product', 'woocommerce_product', [
						'message' => "Term was not properly assigned to product: ".$exception->getMessage(),
						'file' => basename(__FILE__),
						'class' => __CLASS__,
						'function' => __METHOD__,
						'line' => __LINE__,
					]);
				}
			}

		// APPEND AUTHORS
		if (isset($content['authors']) && is_array($content['authors']) && count($content['authors']) > 0) {
			$author_ids = [];
			foreach ($content['authors'] as $key => $value) {
				$author_id = intval($key);
				$aut_args = [
					'taxonomy' => 'autors', // the taxonomy for the term
					'hide_empty' => false, // also retrieve terms which are not used yet
					'meta_query' => [
							['key'   => 'author_geslib_id', // your meta key
							'value'  => $author_id, // your meta value
							'compare'=> '='],
						],
					];
				$authors = get_terms($aut_args);
				if (!empty($authors) && !is_wp_error($authors)) {
					// Terms found, get the first term
					$author_term = $authors[0]->term_id;
					// Assign the product to the editorial taxonomy term
					try {
						wp_set_object_terms( $product_id, $author_term, 'autors', true );
					} catch( \Exception $exception ){
						error_log( 'Failed to store author with id'. $author_term.' to product with id'. $product_id.': '.$exception->getMessage() );
						continue;
					}
				}
			}
		}
		// APPEND CATEGORIES
		if( isset($content['categories']) && is_array($content['categories']) && count( $content['categories']) > 0 ) {
			foreach ( $content['categories'] as $key => $value ) {
				$category_id = intval($key);
				// Get terms
				$cat_args = [
					'taxonomy' => 'product_cat', // the taxonomy for the term
					'hide_empty' => false, // also retrieve terms which are not used yet
					'meta_query' => [
							'key'   => 'category_geslib_id', // your meta key
							'value'  => $category_id, // your meta value
							'compare'=> '=',
						],
					];

				$categories = get_terms($cat_args);
				// Check if any term found
				// Example for categories
				if (!empty($categories) && !is_wp_error($categories)) {
					$category_terms = wp_list_pluck($categories, 'term_id');

					// Remove 'Uncategorized' category if other categories exist
					$uncategorized_term_id = get_term_by('slug', 'uncategorized', 'product_cat')->term_id;
					if (in_array($uncategorized_term_id, $category_terms)) {
						if (count($category_terms) > 1) { // Check if there are other categories assigned
							$key = array_search($uncategorized_term_id, $category_terms);
							unset($category_terms[$key]);
						}
					}

					foreach ($category_terms as $category_term) {
						// Assign each category to the product, excluding 'Uncategorized' if applicable
						try {
							wp_set_object_terms($product_id, $category_term, 'product_cat', true);
						} catch(\Exception $exception) {
							error_log('Term was not properly assigned to product: '. $exception->getMessage());
							$this->geslibApiDbLoggerManager->geslibLogger(0, $geslib_id, 'error', 'store_product', 'woocommerce_product', [
								'message' => "Term was not properly assigned to product: ".$exception->getMessage(),
								'file' => basename(__FILE__),
								'class' => __CLASS__,
								'function' => __METHOD__,
								'line' => __LINE__,
							]);
						}
					}
				}
			}
		}
		return $product_id;
    }

    /**
     * stockProduct
     *
     * @param  mixed $geslib_id
     * @param  $data
     * @return void
     */
    public function stockProduct( int $geslib_id, $data ): void {
		$data = json_decode($data);
        $stock = $data->stock;
        if($stock == null || $stock == 0) return;
        // Ensure that WooCommerce is active
        if ( ! function_exists( 'wc_get_product' ) ) {
           return;
       }

       // Args for the WP_Query
       $args = [
           'post_type'      => 'product',
           'posts_per_page' => 1,
           'meta_query'     => [
               [
                   'key'   => 'geslib_id',
                   'value' => $geslib_id,
               ],
           ],
       ];

       // Get the product
       $query = new WP_Query( $args );

       if ( $query->have_posts() ) {
           while ( $query->have_posts() ) {
               $query->the_post();
               $product_id = get_the_ID();
               $product = wc_get_product( $product_id );

               if ( $product ) {
                   // Update the stock
                   $product->set_stock_quantity( $stock );
                   try {
						$product->save();
				   } catch( \Exception $exception ) {
						error_log('Product stock for geslib_id '.$geslib_id.' has NOT been updated:'. $exception->getMessage());
						$this->geslibApiDbLoggerManager->geslibLogger(0, $geslib_id, 'error', 'stock_product', 'woocommerce_product', [
							'message' => 'Product stock for geslib_id '.$geslib_id.' has NOT been updated:'. $exception->getMessage(),
							'file' => basename(__FILE__),
							'class' => __CLASS__,
							'function' => __METHOD__,
							'line' => __LINE__,
						]);
				   }
               }
           }
       }

       // Reset the global post data. This restores the $post global to the current post in the main query.
       wp_reset_postdata();
   }

   /**
	 * getTotalNumberOfProducts
	 *
	 * @return int
	 */
	public function getTotalNumberOfProducts(): int {
		global $wpdb;

		// Get the total number of products (excluding variations)
		$total_products = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'" );

		// Get the total number of product variations (if needed)
		$total_variations = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product_variation' AND post_status = 'publish'" );

		// Sum the products and variations if variations should be included in the total
		$total = $total_products + $total_variations;

		return $total;
	}

    /**
	 * deleteAllProducts
	 *
	 * @return mixed
	 */
	public function deleteAllProducts(): mixed {
		// Query for all products
		$batch_size = (!isset($_POST['batch_size']) || $_POST['batch_size'] == null) ? -1 : $_POST['batch_size'];
		$offset = (!isset($_POST['offset']) || $_POST['offset'] == null) ? 0 : $_POST['offset'];

		$args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $batch_size,
		];

		$query = new WP_Query( $args );
		$totalLines = $query->found_posts;
		$processedLines = 0;
		$hasMore = !empty($product_geslib_lines);

		// If no posts are returned, we're done
		if ( !$query->have_posts() ) {
			return null;
		}
		$loop = 0;
		$response = [];
		// Loop through all products and delete
		while ( $query->have_posts() ) {
			$query->the_post();
			$id = get_the_ID();
			wp_delete_post( $id, true );
			$processedLines++;
			$progress = ($processedLines / $totalLines) * 100;
			update_option('geslib_delete_product_progress', $progress);
			if ( $loop == 0 ) {
				$response['title'] = "DELETING PRODUCTS";
			}
			$loop++;
		}

		// Reset query data
		wp_reset_postdata();
		$response['hasMore'] = $hasMore;
		$response['progress'] = $progress;
		$response['totalLines'] = $totalLines;
		$response['message'] = "Processed {$processedLines} products.";
		return json_encode( $response );
	}

    /**
     * deleteProduct
     *
     * @param  int $geslib_id
     * @return bool
     */
    public function deleteProduct( int $geslib_id ): bool {
		$args = [
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'   => 'geslib_id',
					'value' => $geslib_id,
				],
			],
		];
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				// Using WooCommerce CRUD functions to delete product
				$product = wc_get_product( $post_id );
				try{
					$product->delete( true );
				} catch(\Exception $exception) {
					error_log($exception->getMessage());
				}
			}
			wp_reset_postdata();
			return true;
		} else {
			error_log('No products found with the given geslib_id '.$geslib_id);
			return false;
		}
	}
}