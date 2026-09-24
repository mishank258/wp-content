<?php
if (!defined('ABSPATH')) {
    exit;
}

// The full table name, including the site's prefix, e.g. "wp_kalinga_enquiries"
function kalinga_tours_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'kalinga_enquiries';
}

// Create or update the enquiries table
function kalinga_tours_create_tables() {
    global $wpdb;

    $table           = kalinga_tours_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  tour_id bigint(20) unsigned NOT NULL DEFAULT 0,
  name varchar(100) NOT NULL,
  email varchar(100) NOT NULL,
  phone varchar(30) NOT NULL DEFAULT '',
  travel_date date DEFAULT NULL,
  travellers smallint(5) unsigned NOT NULL DEFAULT 1,
  message text NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'new',
  created_at datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY tour_id (tour_id),
  KEY status_created (status, created_at)
) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('kalinga_tours_db_version', KALINGA_TOURS_DB_VERSION);
}

// If the saved version doesn't match the code's version, update the table
function kalinga_tours_maybe_upgrade() {
    if (get_option('kalinga_tours_db_version') !== KALINGA_TOURS_DB_VERSION) {
        kalinga_tours_create_tables();
    }
}
add_action('plugins_loaded', 'kalinga_tours_maybe_upgrade');