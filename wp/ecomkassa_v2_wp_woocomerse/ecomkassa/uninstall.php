<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

include_once(dirname( __FILE__ ).'/includes/class-ecomkassa-install.php');
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ecomkassa_reports");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ecomkassa_receipts");
delete_option(EcomKassa_Install::OPTION_DB_VERSION_KEY);
