<?php

/**
 * @package Geslib
 */
namespace Inc\Geslib\Base;

 class Deactivate {
  public static function deactivate() {
    flush_rewrite_rules();
    global $wpdb;

    // Replace these with your actual table names
    $geslib_log = $wpdb->prefix . 'geslib_log';
    $geslib_lines = $wpdb->prefix . 'geslib_lines';

    // Replace 'fk_constraint_name' with the actual foreign key constraint name
    //$fk_constraint_name = 'fk_constraint_name';

    // SQL queries to drop the foreign key constraint and tables
    //$sql_drop_fk = "ALTER TABLE {$geslib_lines} DROP FOREIGN KEY {$fk_constraint_name};";

    // SQL queries to drop the tables
    $sql_log = "DROP TABLE IF EXISTS {$geslib_log};";
    $sql_lines = "DROP TABLE IF EXISTS {$geslib_lines};";

    // Execute the queries
    //$wpdb->query($sql_drop_fk);
    $wpdb->query($sql_lines);
    $wpdb->query($sql_log);
    
  }
 }