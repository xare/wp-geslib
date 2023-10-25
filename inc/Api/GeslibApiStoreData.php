<?php

namespace Inc\Geslib\Api;

class GeslibApiStoreData {
    private $db;

    public function __construct() {
        $this->db = new GeslibApiDbManager();
    }

    public function storeProductCategories() {
        $product_categories = $this->db->getProductCategoriesFromGeslibLines();
        $queue = get_option('geslib_queue', []);
        foreach($product_categories as $product_category) {
            $item = [
                'product_category' => $product_category,
                'type' => store_categories,
            ];
            $queue[] = $item;
        }
        update_option('geslib_queue', $queue);
        //$this->db->storeProductCategory($product_category);
    }


    public function storeEditorials() {
        $editorials = $this->db->getEditorialsFromGeslibLines();
        foreach($editorials as $editorial) {
            $this->db->storeEditorials($editorial);
        }
    }

    public function storeAuthors() {
        $editorials = $this->db->getAuthorsFromGeslibLines();
        foreach($editorials as $editorial) {
            $this->db->storeAuthors($editorial);
        }
    }


}