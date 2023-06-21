<?php

namespace Inc\Geslib\Api;

class GeslibApiStoreData {
    private $db;

    public function __construct() {
        $this->db = new GeslibApiDbManager();
    }

    public function storeProductCategories() {
        $product_categories = $this->db->getProductCategoriesFromGeslibLines();
        foreach($product_categories as $product_category) {
            $this->db->storeProductCategories($product_category);
        }
    }

    public function storeEditorials() {
        $editorials = $this->db->getEditorialsFromGeslibLines();
        foreach($editorials as $editorial) {
            $this->db->storeEditorials($editorial);
        }
    }


}