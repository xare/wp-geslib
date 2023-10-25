<?php

/**
 * @package geslib
 */

namespace Inc\Geslib\Base;

use Inc\Geslib\Base\BaseController;

class Enqueue extends BaseController {
  public function register(){
    if (is_admin() && $_GET['page'] === 'geslib')
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
      }
}