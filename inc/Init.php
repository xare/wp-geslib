<?php

namespace Inc\Geslib;
use Inc\Geslib\Commands\GeslibHelloCommand;
use Inc\Geslib\Commands\GeslibDeleteAllProductsCommand;
use Inc\Geslib\Commands\GeslibDeleteAllTermsCommand;

final class Init
{
  /**
   * Store all the classes inside an array
   *
   * @return array Full list of classes
   */
  public static function get_services():Array {
    return [
      GeslibHelloCommand::class,
	    GeslibDeleteAllProductsCommand::class,
	    GeslibDeleteAllTermsCommand::class
    ];
  }

  /**
   * Loop through the classes, initialize them
   * and call the register() method if it exists
   *
   * @return void
   */
  public static function register_services() {
    foreach(self::get_services() as $class){
      $service = self::instantiate( $class );
      if(method_exists($service,'register')) {
          $service->register();
      }
    }
  }
  /**
   * Initialize the class
   *
   * @param [type] $class class from the services array
   * @return class instance new instance of the class
   */
  private static function instantiate( $class ) {
    return new $class();
  }
}
