<?php

namespace Inc\Geslib\Base;

use Inc\Geslib\Base\BaseController;

class GeslibEditorialController extends BaseController{
    public function register()
    {
        add_action( 'editorials_add_form_fields', [ $this, 'geslib_add_editorial_id_field' ], 10, 2);
        add_action( 'editorials_edit_form_fields', [ $this, 'geslib_edit_editorial_id_field' ], 10, 2);
        add_action( 'created_editorials', [ $this, 'geslib_save_editorial_id_field' ], 10, 2);
        add_action( 'edited_editorials', [ $this, 'geslib_save_editorial_id_field' ], 10, 2);
        add_filter( 'manage_edit-editorials_columns', [ $this, 'add_geslib_id_column' ]);
        add_filter( 'manage_editorials_custom_column', [ $this, 'add_geslib_id_column_content' ], 10, 3);
        // Make column sortable
        add_filter( 'manage_edit-editorials_sortable_columns', [ $this, 'geslib_id_column_sortable' ] );
    }

    public function geslib_add_editorial_id_field($taxonomy) {
        ?><div class="form-field term-group">
            <label for="editorial_geslib_id">
                <?php _e('Geslib Editorial ID', 'geslib'); ?>
            </label>
            <input type="number" id="editorial_geslib_id" name="editorial_geslib_id" class="postform">
        </div><?php
    }

    public function geslib_edit_editorial_id_field($term, $taxonomy) {
        $geslib_id = get_term_meta($term->term_id, 'editorial_geslib_id', true);
        ?><tr class="form-field term-group-wrap">
            <th scope="row"><label for="editorial_geslib_id"><?php _e('Geslib ID', 'geslib'); ?></label></th>
            <td><input type="number" id="editorial_geslib_id" name="editorial_geslib_id" value="<?php echo $geslib_id; ?>"></td>
        </tr><?php
    }

    function geslib_save_editorial_id_field($term_id, $tt_id) {
        if(isset($_POST['editorial_geslib_id']) && '' !== $_POST['editorial_geslib_id']){
            $group = sanitize_text_field($_POST['editorial_geslib_id']);
            update_term_meta($term_id, 'editorial_geslib_id', $group);
        }
    }

    public function add_geslib_id_column($columns) {
        $columns['editorial_geslib_id'] = __('Geslib ID', 'geslib');
        return $columns;
    }

    public function add_geslib_id_column_content($content, $column_name, $term_id) {
        if ($column_name !== 'editorial_geslib_id') {
            return $content;
        }

        $term_id = absint($term_id);
        $geslib_id = get_term_meta($term_id, 'editorial_geslib_id', true);

        if (!empty($geslib_id)) {
            $content .= esc_attr($geslib_id);
        }

        return $content;
    }

    function geslib_id_column_sortable( $sortable ) {
        $sortable[ 'editorial_geslib_id' ] = 'editorial_geslib_id';
        return $sortable;
    }
}