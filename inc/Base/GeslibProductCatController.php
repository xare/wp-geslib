<?php 

namespace Inc\Geslib\Base;

use Inc\Geslib\Base\BaseController;

class GeslibProductCatController extends BaseController{
    public function register()
    {
        add_action('product_cat_add_form_fields', [ $this, 'geslib_add_category_id_field' ], 10, 2);
        add_action('product_cat_edit_form_fields', [ $this, 'geslib_edit_category_id_field' ], 10, 2);
        add_action('created_product_cat', [ $this, 'geslib_save_category_id_field' ], 10, 2);
        add_action('edited_product_cat', [ $this, 'geslib_save_category_id_field' ], 10, 2);
    }

    public function geslib_add_category_id_field($taxonomy) {
        ?><div class="form-field term-group">
            <label for="geslib_id"><?php _e('Geslib ID', 'geslib'); ?></label>
            <input type="number" id="geslib_id" name="geslib_id" class="postform">
        </div><?php
    }

    public function geslib_edit_category_id_field($term, $taxonomy) {
        $geslib_id = get_term_meta($term->term_id, 'geslib_id', true);
        ?><tr class="form-field term-group-wrap">
            <th scope="row"><label for="geslib_id"><?php _e('Geslib ID', 'geslib'); ?></label></th>
            <td><input type="number" id="geslib_id" name="geslib_id" value="<?php echo $geslib_id; ?>"></td>
        </tr><?php
    }

    function geslib_save_category_id_field($term_id, $tt_id) {
        if(isset($_POST['geslib_id']) && '' !== $_POST['geslib_id']){
            $group = sanitize_text_field($_POST['geslib_id']);
            update_term_meta($term_id, 'geslib_id', $group);
        }
    }
}