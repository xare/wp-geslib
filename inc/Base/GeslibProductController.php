<?php 

namespace Inc\Geslib\Base;

use Inc\Geslib\Base\BaseController;

class GeslibProductController extends BaseController {

    function register() {
        add_action('manage_product_posts_custom_column',[ $this,  'display_ean'], 10, 2);
        add_filter('manage_edit-product_columns', [ $this, 'add_ean_column'], 10, 1);
        add_action('woocommerce_product_options_sku', [ $this, 'woocommerce_product_options_ean']);
        add_action('woocommerce_admin_process_product_object', [ $this, 'save_product_ean'], 10, 1);
        
        // Register new columns and fields
        add_action('manage_product_posts_custom_column',[ $this,  'display_author'], 10, 2);
        add_filter('manage_edit-product_columns', [ $this, 'add_author_column'], 10, 1);
        add_action('woocommerce_product_options_sku', [ $this, 'woocommerce_product_options_author']);
        add_action('woocommerce_admin_process_product_object', [ $this, 'save_product_author'], 10, 1);

        add_action('manage_product_posts_custom_column',[ $this,  'display_num_paginas'], 10, 2);
        add_filter('manage_edit-product_columns', [ $this, 'add_num_paginas_column'], 10, 1);
        add_action('woocommerce_product_options_sku', [ $this, 'woocommerce_product_options_num_paginas']);
        add_action('woocommerce_admin_process_product_object', [ $this, 'save_product_num_paginas'], 10, 1);
    
    }

    function add_author_column( $columns ) {
        // Similar to add_ean_column function but for author
        $new_columns = array();
        foreach ($columns as $column_name => $column_info) {
            $new_columns[$column_name] = $column_info;
            if ('author' === $column_name) {
                $new_columns['author'] = __('author');
            }
        }
        return $new_columns;
    }

    function display_author($column, $post_id) {
        if ('author' === $column) {
            echo get_post_meta($post_id, '_author', true);
        }
    }

    function woocommerce_product_options_author() {
        woocommerce_wp_text_input([
            'id' => '_author',
            'label' => __('AUTHOR', 'woocommerce'),
            'description' => __('Enter the author here.', 'woocommerce'),
            'desc_tip' => 'true',
            'placeholder' => __('AUTHOR', 'woocommerce'),
        ]);
    }

    function save_product_author($product) {
        $author = isset($_POST['_author']) ? $_POST['_author'] : '';
        $product->update_meta_data('_author', sanitize_text_field($author));
    }

    function add_num_paginas_column( $columns ) {
        $new_columns = array();
        foreach ($columns as $column_name => $column_info) {
            $new_columns[$column_name] = $column_info;
            if ('num_paginas' === $column_name) {
                $new_columns['num_paginas'] = __('Núm. páginas');
            }
        }
        return $new_columns;
    }

    function display_num_paginas($column, $post_id) {
        if ('num_paginas' === $column) {
            echo get_post_meta($post_id, '_num_paginas', true);
        }
    }

    function woocommerce_product_options_num_paginas() {
        woocommerce_wp_text_input([
            'id' => '_num_paginas',
            'label' => __('Nº Pág.', 'woocommerce'),
            'description' => __('Introduce el nº de páginas.', 'woocommerce'),
            'desc_tip' => 'true',
            'placeholder' => __('Nº Pág.', 'woocommerce'),
        ]);
    }

    function save_product_num_paginas($product) {
        $num_paginas = isset($_POST['_num_paginas']) ? $_POST['_num_paginas'] : '';
        $product->update_meta_data('_num_paginas', sanitize_text_field($num_paginas));
    }

    function add_ean_column( $columns ) {
        $new_columns = array();
        foreach ($columns as $column_name => $column_info) {
            $new_columns[$column_name] = $column_info;
            if ('sku' === $column_name) {
                $new_columns['ean'] = __('EAN');
            }
        }
        return $new_columns;
    }

    function display_ean($column, $post_id) {
        if ('ean' === $column) {
            echo get_post_meta($post_id, '_ean', true);
        }
    }

    function woocommerce_product_options_ean() {
        woocommerce_wp_text_input([
            'id' => '_ean',
            'label' => __('EAN', 'woocommerce'),
            'description' => __('Enter the EAN here.', 'woocommerce'),
            'desc_tip' => 'true',
            'placeholder' => __('EAN', 'woocommerce'),
        ]);
    }

    function save_product_ean($product) {
        $ean = isset($_POST['_ean']) ? $_POST['_ean'] : '';
        $product->update_meta_data('_ean', sanitize_text_field($ean));
    }

}