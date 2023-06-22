<?php 

namespace Inc\Geslib\Base;

use Inc\Geslib\Base\BaseController;

class GeslibProductController extends BaseController {

    function register() {
        add_action('manage_product_posts_custom_column',[ $this,  'display_ean'], 10, 2);
        add_filter('manage_edit-product_columns', [ $this, 'add_ean_column'], 10, 1);
        add_action('woocommerce_product_options_sku', [ $this, 'woocommerce_product_options_ean']);
        add_action('woocommerce_admin_process_product_object', [ $this, 'save_product_ean'], 10, 1);
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