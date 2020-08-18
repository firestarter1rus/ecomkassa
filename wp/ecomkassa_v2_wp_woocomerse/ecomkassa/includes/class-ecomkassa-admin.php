<?php

final class EcomKassa_Admin {

    public static function init() {
        add_action('admin_head', array(__CLASS__, 'menu_correction'));
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
        add_action('admin_menu', array(__CLASS__, 'add_menu_settings'));
        add_action('admin_menu', array(__CLASS__, 'add_menu_reports'));

        require_once(ECOMKASSA_ABSPATH . 'includes/class-ecomkassa-admin-settings.php');
        require_once(ECOMKASSA_ABSPATH . 'includes/class-ecomkassa-admin-reports.php');
    }

    public static function add_menu() {
        add_menu_page(
            'ECOM Касса',
            'ECOM Касса',
            'manage_options',
            'ecomkassa',
            array(__CLASS__, 'settings_page'),
            null,
            '56.2'
        );
    }

    public static function add_menu_settings() {
        add_submenu_page(
            'ecomkassa',
            'ECOM Касса - Настройки',
            'Настройки',
            'manage_options',
            'ecomkassa-settings',
            array(__CLASS__, 'settings_page')
        );
    }

    public static function add_menu_reports() {
        add_submenu_page(
            'ecomkassa',
            'ECOM Касса - Заявки',
            'Заявки',
            'manage_options',
            'ecomkassa-reports',
            array(__CLASS__, 'reports_page')
        );
    }

    public static function menu_correction() {
        global $submenu;

        if (isset($submenu['ecomkassa'])) {
            unset($submenu['ecomkassa'][0]);
        }
    }

    public static function settings_page() {
        EcomKassa_AdminSettings::out();
    }

    public static function reports_page() {
        EcomKassa_AdminReports::out();
    }
}
