<?php

/**
 * @package Geslib
 */

namespace Inc\Geslib\Base;

 class Activate {
  public static function activate() {
    global $wpdb;
    flush_rewrite_rules();

    $default = [];

    if ( !get_option('geslib_settings')) {
      update_option('geslib_settings', $default);
    }

    /* CREATE A DATABASE TABLE CALLED geslib_log */
    /* id int autoincrement */
    /* filename string */
    /* start_date datetime */
    /* end_date datetime */
    /* status string read|queued|processed */
    /* lines int */

    /* CREATE A DATABASE TABLE CALLED geslib_queues */
    /* id int autoincrement */
    /* log_id string one log id may have many geslib_lines.id s */
    /* geslib_id */
    /* type string */
    /* action string */
    /* data string json*/

    /* CREATE A DATABASE TABLE CALLED geslib_logger */
    /* id int autoincrement */
    /* log_id string one log id may have many geslib_lines.id s */
    /* geslib_id */
    /* entity string */
    /* action string */
    /* metadata string json*/

    wp_mkdir_p( WP_CONTENT_DIR . '/uploads/geslib' );

    $charset_collate = $wpdb->get_charset_collate();
    $log_table_name = $wpdb->prefix . 'geslib_log';
    $queue_table_name = $wpdb->prefix. 'geslib_queues';
    $logger_table_name = $wpdb->prefix. 'geslib_logger';

    $log_sql = "CREATE TABLE $log_table_name (
      id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
      filename text NOT NULL,
      start_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
      end_date datetime DEFAULT NULL,
      status text NOT NULL,
      lines_count int(11) NOT NULL,
      PRIMARY KEY (id)
    ) $charset_collate;";

    $queue_sql = "CREATE TABLE $queue_table_name (
        id int(11) unsigned NOT NULL AUTO_INCREMENT,
        log_id mediumint(9) unsigned,
        geslib_id text,
        type varchar(255) NOT NULL,
        data text,
        PRIMARY KEY (id)
      ) $charset_collate;";

    $logger_sql = "CREATE TABLE $logger_table_name(
        id int(11) unsigned NOT NULL AUTO_INCREMENT,
        log_id mediumint(9) unsigned,
        geslib_id text,
        action varchar(255) NOT NULL,
        entity varchar(255) NOT NULL,
        metadata text,
        date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (`id`)
      ) $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $log_sql );
      dbDelta( $queue_sql );
      dbDelta( $logger_sql );
  }
 }