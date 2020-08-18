<?php

if (!defined('ABSPATH')) {
    exit;
}

function ecomkassa_0001_create_tables() {
    global $wpdb;

    $collate = $wpdb->get_charset_collate();

    $table_reports = "
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ecomkassa_reports (
    report_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT '0000-00-00',
    updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE now(),
    order_id BIGINT UNSIGNED NOT NULL,
    operation varchar(16) NOT NULL,
    status varchar(16) NOT NULL DEFAULT 'new',
    request_data TEXT DEFAULT NULL,
    response_data TEXT DEFAULT NULL,
    report_data TEXT DEFAULT NULL,
    error TEXT DEFAULT NULL,
    PRIMARY KEY (report_id)
) $collate";
 

	$table_receipts = "
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ecomkassa_receipts (
  `internal_id` varchar(37) NOT NULL,
  `external_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `operation` varchar(16) NOT NULL,
  `datetime` varchar(19) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `ecr_registration_number` varchar(20) NOT NULL,
  `fiscal_document_number` varchar(20) NOT NULL,
  `fiscal_document_attribute` varchar(20) NOT NULL,
    PRIMARY KEY (internal_id, external_id)
) $collate";

    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    dbDelta($table_reports);
 
	dbDelta($table_receipts);
}
