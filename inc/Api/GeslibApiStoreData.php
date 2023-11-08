<?php

namespace Inc\Geslib\Api;

class GeslibApiStoreData {
    private $db;

    public function __construct() {
        $this->db = new GeslibApiDbManager();
    }

    public function storeProductCategories() {
        global $wpdb;
        $queueTable = $wpdb->prefix . 'geslib_queues';
        $product_categories = $this->db->getProductCategoriesFromGeslibLines();
        foreach($product_categories as $product_category) {
            $item = [
                'product_category' => $product_category,
                'type' => 'store_categories',
            ];
            // Directly insert into the database queue
            $wpdb->insert($queueTable, $item);
        }
        // Return a status message indicating success or failure and/or count of categories added.
        $totalAdded = count($product_categories);
        return "Added $totalAdded product categories to the queue.";
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