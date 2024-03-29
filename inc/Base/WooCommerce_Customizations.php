<?php

namespace Inc\Geslib\Base;

class WooCommerce_Customizations {

    public function __construct() {
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_geslib_id_field' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_geslib_id_field' ] );
    }

    public function add_geslib_id_field() {
        woocommerce_wp_text_input(
            [
                'id'          => 'geslib_id',
                'label'       => __( 'Geslib ID', 'woocommerce' ),
                'placeholder' => '',
                'desc_tip'    => 'true',
                'description' => __( 'Enter the Geslib ID here.', 'woocommerce' ),
                'type'        => 'number',
            ]
        );
        woocommerce_wp_text_input(
            [
                'id'          => 'dilve_url',
                'label'       => __( 'DILVE URL', 'woocommerce' ),
                'placeholder' => '',
                'desc_tip'    => 'true',
                'description' => __( 'Enter the DILVE URL here.', 'woocommerce' ),
                'type'        => 'text', // Assuming it's a URL, the input type is text
            ]
        );
        woocommerce_wp_text_input(
            [
                'id'          => 'cegal_url',
                'label'       => __( 'CEGAL URL', 'woocommerce' ),
                'placeholder' => '',
                'desc_tip'    => 'true',
                'description' => __( 'Enter the CEGAL URL here.', 'woocommerce' ),
                'type'        => 'text', // Assuming it's a URL, the input type is text
            ]
        );
    }

    public function save_geslib_id_field( $post_id ) {
        // Save Geslib ID
        $geslib_id = isset( $_POST['geslib_id'] ) ? $_POST['geslib_id'] : 0;
        update_post_meta( $post_id, 'geslib_id', $geslib_id );
        // Save Dilve URL
        $dilve_url = isset( $_POST['dilve_url'] ) ? $_POST['dilve_url'] : '';
        update_post_meta( $post_id, 'dilve_url', $dilve_url );
        // Save Cegal URL
        $cegal_url = isset( $_POST['cegal_url'] ) ? $_POST['cegal_url'] : '';
        update_post_meta( $post_id, 'cegal_url', $cegal_url );
    }

}
