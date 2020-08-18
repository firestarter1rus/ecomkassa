<?php

final class EcomKassa_Install {

    const OPTION_DB_VERSION_KEY = 'ecomkassa_db_version';

    private static $db_migrations = array(
        '1.0.0' => 'ecomkassa_0001_create_tables'
    );

    public static function init()
    {
        add_filter('plugin_action_links_' . ECOMKASSA_BASENAME, array(__CLASS__, 'plugin_action_links'));
    }

    public static function activation()
    {
        self::db_migrations();
    }

    public static function db_migrations()
    {
        $current_db_version = get_option(self::OPTION_DB_VERSION_KEY, null);

        if (!is_null($db_version) && version_compare($db_version, max(array_keys(self::$db_migrations)), '<' ) ) {
            return;
        }

        include_once(dirname(__FILE__) . '/db-migrations.php');

        foreach (self::$db_migrations as $version => $migration) {
            if (version_compare($db_version, $version, '<') && is_callable($migration)) {
                call_user_func($migration);
            }
        }

        self::update_db_version();
    }

    public static function update_db_version($version=null) {
        delete_option(self::OPTION_DB_VERSION_KEY);
        add_option(self::OPTION_DB_VERSION_KEY, is_null($version) ? Ecom_Kassa()->version : $version);
    }

    public static function plugin_action_links($links)
    {
        $action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=ecomkassa-settings' ) . '" aria-label="Настройки">Настройки</a>',
		);

		return array_merge( $action_links, $links );
    }
}

EcomKassa_Install::init();
