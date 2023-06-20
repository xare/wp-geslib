<?php 

namespace Inc\Geslib\Base;

use Inc\Geslib\Base\BaseController;

class GeslibProductCatController extends BaseController{
    public function register()
    {
        add_action( 'product_cat_add_form_fields', [ $this, 'geslib_add_category_id_field' ], 10, 2);
        add_action( 'product_cat_edit_form_fields', [ $this, 'geslib_edit_category_id_field' ], 10, 2);
        add_action( 'created_product_cat', [ $this, 'geslib_save_category_id_field' ], 10, 2);
        add_action( 'edited_product_cat', [ $this, 'geslib_save_category_id_field' ], 10, 2);
        add_filter( 'manage_edit-product_cat_columns', [ $this, 'add_geslib_id_column' ]);
        add_filter( 'manage_product_cat_custom_column', [ $this, 'add_geslib_id_column_content' ], 10, 3);
        // Make column sortable
        add_filter( 'manage_edit-product_cat_sortable_columns', [ $this, 'geslib_id_column_sortable' ] );
    }

    public function geslib_add_category_id_field($taxonomy) {
        ?><div class="form-field term-group">
            <label for="category_geslib_id"><?php _e('Geslib Category ID', 'geslib'); ?></label>
            <input type="number" id="category_geslib_id" name="category_geslib_id" class="postform">
        </div><?php
    }

    public function geslib_edit_category_id_field($term, $taxonomy) {
        $geslib_id = get_term_meta($term->term_id, 'category_geslib_id', true);
        ?><tr class="form-field term-group-wrap">
            <th scope="row"><label for="category_geslib_id"><?php _e('Geslib ID', 'geslib'); ?></label></th>
            <td><input type="number" id="category_geslib_id" name="category_geslib_id" value="<?php echo $geslib_id; ?>"></td>
        </tr><?php
    }

    function geslib_save_category_id_field($term_id, $tt_id) {
        if(isset($_POST['category_geslib_id']) && '' !== $_POST['category_geslib_id']){
            $group = sanitize_text_field($_POST['category_geslib_id']);
            update_term_meta($term_id, 'category_geslib_id', $group);
        }
    }

    public function add_geslib_id_column($columns) {
        $columns['category_geslib_id'] = __('Geslib ID', 'geslib');
        return $columns;
    }

    public function add_geslib_id_column_content($content, $column_name, $term_id) {
        if ($column_name !== 'category_geslib_id') {
            return $content;
        }
    
        $term_id = absint($term_id);
        $geslib_id = get_term_meta($term_id, 'category_geslib_id', true);
    
        if (!empty($geslib_id)) {
            $content .= esc_attr($geslib_id);
        }
    
        return $content;
    }

    function geslib_id_column_sortable( $sortable ) {
        $sortable[ 'category_geslib_id' ] = 'category_geslib_id'; 
        return $sortable;
    }
}