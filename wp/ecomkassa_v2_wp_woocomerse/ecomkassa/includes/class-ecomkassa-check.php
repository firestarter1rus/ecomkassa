<?php


final class EcomKassa_Check {

	const CHECK_TABLE_NAME = 'ecomkassa_receipts';
	const REPORT_TABLE_NAME = 'ecomkassa_reports';


	public function save( $response_data ) {
		global $wpdb;

		$report_table_name = $wpdb->prefix . self::REPORT_TABLE_NAME;
		$check_table_name  = $wpdb->prefix . self::CHECK_TABLE_NAME;

		$report = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$report_table_name} WHERE `report_id` = %d LIMIT 1;", intval( $response_data['uuid'] ) ) );

		$internal_check_id = strval(json_decode( $report->request_data, true )['external_id']);

		$wpdb->insert(
			$wpdb->prefix . self::CHECK_TABLE_NAME,
			array(
				'internal_id'               => $internal_check_id,
				'external_id'               => intval( $report->report_id ),
				'order_id'                  => intval( $report->order_id ),
				'operation'                 => strval($report->operation),
				'datetime'                  => strval(json_decode( $report->report_data, true )['payload']['receipt_datetime']),
				'total'                     => floatval( json_decode( $report->report_data, true )['payload']['total'] ),
				'ecr_registration_number'   => strval(json_decode( $report->report_data, true )['payload']['ecr_registration_number']),
				'fiscal_document_number'    => strval(json_decode( $report->report_data, true )['payload']['fiscal_document_number']),
				'fiscal_document_attribute' => strval(json_decode( $report->report_data, true )['payload']['fiscal_document_attribute']),
			),
			array( '%s', '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s' )
		);

		$order = wc_get_order( intval( $report->order_id ) );
		$order->add_order_note( "Чек #{$internal_check_id} был зарегистрирован" );
	}

	public function get_receipts( $order_id ) {
		global $wpdb;

		$check_table_name  = $wpdb->prefix . self::CHECK_TABLE_NAME;

		$checks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$check_table_name} WHERE `order_id` = %d",
			intval( $order_id ) ) );

		return $checks;
	}
}