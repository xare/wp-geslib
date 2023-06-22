<?php

namespace Inc\Geslib;

use Inc\Geslib\Base\GeslibEditorialController;
use Inc\Geslib\Base\GeslibProductCatController;
use Inc\Geslib\Base\GeslibProductController;
use Inc\Geslib\Commands\GeslibStoreProductCategoriesCommand;
use Inc\Geslib\Commands\GeslibStoreEditorialsCommand;
use Inc\Geslib\Commands\GeslibHelloCommand;
use Inc\Geslib\Commands\GeslibShowTablesCommand;
use Inc\Geslib\Commands\GeslibLogCommand;
use Inc\Geslib\Commands\GeslibDeleteAllProductsCommand;
use Inc\Geslib\Commands\GeslibDeleteAllTermsCommand;
use Inc\Geslib\Commands\GeslibDeleteProductCategoriesCommand;
use Inc\Geslib\Commands\GeslibDeleteEditorialsCommand;
use Inc\Geslib\Commands\GeslibLinesCommand;
use Inc\Geslib\Commands\GeslibStoreProductsCommand;
use Inc\Geslib\Commands\GeslibStoreDataCommand;
use Inc\Geslib\Commands\GeslibTruncateTableCommand;
use Inc\Geslib\Pages\Dashboard;

final class Init
{
  /**
   * Store all the classes inside an array
   *
   * @return array Full list of classes
   */
  public static function get_services():Array {
    return [
      GeslibDeleteProductCategoriesCommand::class,
      GeslibDeleteEditorialsCommand::class,
      GeslibStoreEditorialsCommand::class,
      GeslibHelloCommand::class,
      GeslibShowTablesCommand::class,
      GeslibTruncateTableCommand::class,
      GeslibLogCommand::class,
      GeslibLinesCommand::class,
	    GeslibDeleteAllProductsCommand::class,
	    GeslibDeleteAllTermsCommand::class,
      GeslibStoreProductCategoriesCommand::class,
      GeslibStoreProductsCommand::class,
      GeslibProductCatController::class,
      GeslibProductController::class,
      GeslibEditorialController::class,
      Dashboard::class
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
