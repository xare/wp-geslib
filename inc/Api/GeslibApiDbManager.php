<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiLog;

class GeslibApiDbManager {
	const GESLIB_LINES_TABLE = 'geslib_lines';
	const GESLIB_LOG_TABLE = 'geslib_log';
	static $geslibLinesKeys = [
		'log_id', // int relation oneToMany with geslib_log
		'geslib_id', // int 
		'filename', // string inter000
		'action', // string insert|update|delete
		'type', // string product | category | author | publisher
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

	public function store2geslibLines($data_array) {
		global $wpdb;
		
		$geslibLinesValues = [
			$data_array['geslib_id'],
			$filename,
			'product',
			json_encode($data_array),
			1
		];
	}
	
	
  
  public function _readGeslibLinesTable(){
      global $wpdb;
      $table_name = $wpdb->prefix . 'geslib_lines';
      $query = $wpdb->prepare( "SELECT * FROM {$table_name}" );
      $results = $wpdb->get_results($query);
      
      foreach ($results as $result) {
        $this->_storeData($result->type, $result->id, $result->content);
      }
    }
	
	private function _storeData($type, $geslib_id, $content){
      $store_data=[];
      $function_name = 'store'.$type[0];
      if (method_exists($this, $function_name)) {
        $store_data[] = $this->{$function_name}($geslib_id,$content);
      } else {
        $store_data[] = 'EMPTY';
      }
      
      return $store_data;
    }

    public function storepublisher( $geslib_id, $content ){
      $content = json_decode( $content );
      $term_name = $content['publisher'];
      $term_slug = $this->_create_slug( $term_name );
      $term_description = $term_name;
      $term = term_exists( $term_name, 'Editorials' ); // check if term already exists
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

        // Check for errors
        if ( is_wp_error($term_data) ) {
            // Handle the error here
            echo $term_data->get_error_message();
        }
    }
    public function storeproduct($geslib_id,$content){
		// Check if product already exists
		$content = json_decode($content);
		$book_name = $content['titulo'];
		$book_description = $content['sinopsis'];
		$book_price = floatval(str_replace(',', '.', $content['pvp']));
		$existing_product = get_page_by_title($book_name, OBJECT, 'product');
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

		// Assign the product to the editorial taxonomy term
		//wp_set_object_terms($product_id, $editorial_term, 'editorials', true);

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


}
