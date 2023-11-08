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

    /* CREATE A DATABASE TABLE CALLED geslib_lines */
    /* id int autoincrement */
    /* log_id string one log id may have many geslib_lines.id s */
    /* geslib_id */
    /* entity string */
    /* action string */
    /* content string json*/
    /* status string read|queued|processed */
    /* lines int */


    wp_mkdir_p( WP_CONTENT_DIR . '/uploads/geslib' );

    $charset_collate = $wpdb->get_charset_collate();
    $log_table_name = $wpdb->prefix . 'geslib_log';
    $lines_table_name = $wpdb->prefix . 'geslib_lines';
    $queue_table_name = $wpdb->prefix. 'geslib_queues';

    $log_sql = "CREATE TABLE $log_table_name (
      id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
      filename text NOT NULL,
      start_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
      end_date datetime DEFAULT NULL,
      status text NOT NULL,
      `lines_count` int(11) NOT NULL,
      PRIMARY KEY (id)
    ) $charset_collate;";

    $lines_sql = "CREATE TABLE $lines_table_name (
      id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
      log_id mediumint(9) unsigned NOT NULL,
      geslib_id text NOT NULL,
      entity text NOT NULL,
      action text NOT NULL,
      content text NOT NULL,
      queued boolean,
      PRIMARY KEY (id),
      FOREIGN KEY (log_id) REFERENCES $log_table_name(id)
          ) $charset_collate;";

      $queue_sql = "CREATE TABLE $queue_table_name (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `log_id` mediumint(9) unsigned,
        `geslib_id` text,
        `type` varchar(255) NOT NULL,
        `data` text,
        PRIMARY KEY (`id`)
      ) $charset_collate;
      ";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $log_sql );
      dbDelta( $lines_sql );
      dbDelta( $queue_sql );
  }
 }