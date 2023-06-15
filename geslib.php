<?php
    /*
    Plugin Name: Geslib for WP plugin
    Description: Geslib plugin for WordPress
    Version: 1.0
    Author: xare
    */

defined( 'ABSPATH' ) or die ( 'Acceso prohibido');

// Require once the Composer Autoload
if( file_exists( dirname( __FILE__).'/vendor/autoload.php' ) ){
  require_once dirname( __FILE__).'/vendor/autoload.php';
}

/**
 * The code that runs during plugin Activation
 *
 * @return void
 */
function activate_geslib(){
  Inc\Geslib\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'activate_geslib');

/**
 * The code that runs during plugin Deactivation
 *
 * @return void
 */
function deactivate_geslib(){
  Inc\Geslib\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_geslib');

if(class_exists( 'Inc\\Geslib\\Init' )) {
  Inc\Geslib\Init::register_services();
}