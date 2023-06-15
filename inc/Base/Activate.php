<?php

/**
 * @package Geslib
 */

namespace Inc\Geslib\Base;

 class Activate {
  public static function activate() {
    flush_rewrite_rules();

    $default = [];

    if ( !get_option('geslib')) {
      update_option('geslib', $default);
    }
    
    wp_mkdir_p( WP_CONTENT_DIR . '/uploads/geslib' );

  }
 }