<?php
if(!defined('WP_UNINSTALL_PLUGIN'))
    exit;
/**
 * Uninstalls the plugin and deletes the table and options
 */
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ghazale_inquiry_c");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ghazale_inquiry_q");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ghazale_inquiry_%'" );