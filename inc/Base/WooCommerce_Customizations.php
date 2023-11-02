<?php

namespace Inc\Geslib\Base;

class WooCommerce_Customizations {

    public function __construct() {
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_geslib_id_field' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_geslib_id_field' ] );
    }

    public function add_geslib_id_field() {
        woocommerce_wp_text_input(
            array(
                'id'          => 'geslib_id',
                'label'       => __( 'Geslib ID', 'woocommerce' ),
                'placeholder' => '',
                'desc_tip'    => 'true',
                'description' => __( 'Enter the Geslib ID here.', 'woocommerce' ),
                'type'        => 'number',
            )
        );
    }

    public function save_geslib_id_field( $post_id ) {
        $geslib_id = isset( $_POST['geslib_id'] ) ? $_POST['geslib_id'] : '';
        $geslib_id = sanitize_text_field( $geslib_id );
        update_post_meta( $post_id, 'geslib_id', esc_attr( $geslib_id ) );
    }

}
