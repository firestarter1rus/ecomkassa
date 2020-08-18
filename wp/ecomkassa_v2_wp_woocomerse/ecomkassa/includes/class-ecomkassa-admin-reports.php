<?php

final class EcomKassa_AdminReports {

    public static function out() {
        global $reports_list;
        $option = 'per_page';
		$args   = [
			'label'   => 'Customers',
			'default' => 5,
			'option'  => 'customers_per_page'
		];

		add_screen_option( $option, $args );
        $reports_list = new EcomKassa_ReportsList();
        include(ECOMKASSA_ABSPATH_VIEWS . 'html-admin-reports.php');
    }
}
