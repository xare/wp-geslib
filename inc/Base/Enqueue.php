<?php

/**
 * @package geslib
 */

namespace Inc\Geslib\Base;

use Inc\Geslib\Base\BaseController;

class Enqueue extends BaseController {
  public function register(){
    $page = filter_input(INPUT_GET, 'page', FILTER_DEFAULT);
    if (is_admin() && ($page === 'geslib' || $page === 'geslib_logger') )
      add_action ( 'admin_enqueue_scripts', [$this, 'enqueue_admin']);
    //add_action ( 'enqueue_scripts', [$this, 'enqueue']);
  }
function enqueue() {
        //enqueue all our scripts

        wp_enqueue_script('media_upload');
        wp_enqueue_media();
        wp_enqueue_style('GeslibStyle', $this->plugin_url . 'dist/css/geslib.min.css');
        wp_enqueue_script('GeslibScript', $this->plugin_url . 'dist/js/geslib.min.js');

      }
  function enqueue_admin() {
      // enqueue all our scripts
      wp_enqueue_style('GeslibAdminStyle', $this->plugin_url .'dist/css/geslibAdmin.min.css');
      wp_enqueue_script('GeslibAdminScript', $this->plugin_url .'dist/js/geslibAdmin.min.js');
      wp_enqueue_script('GeslibPagination', $this->plugin_url .'dist/js/pagination.min.js',['jquery'], '1.0', true);

      wp_enqueue_script('GeslibUpdateValues', $this->plugin_url . 'dist/js/geslibUpdateValues.min.js', ['jquery'], '1.0', true);
      wp_localize_script('GeslibUpdateValues', 'ajax_object', array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'nonce' => wp_create_nonce('my_ajax_nonce')
      ));
  }

}