<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central bootstrap. Wires REST, admin and frontend together.
 */
class BR_Plugin {

	public static function init() {
		load_plugin_textdomain( 'backofenrezepte-experiences', false, dirname( plugin_basename( BR_EXP_PLUGIN_FILE ) ) . '/languages' );

		// Run migrations if schema version changed (safe no-op if already current).
		BR_Migrations::maybe_migrate();

		// REST endpoints.
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );

		// Frontend data localization.
		BR_Frontend_Loader::init();

		// Admin UI.
		if ( is_admin() ) {
			BR_Admin_Menu::init();
		}
	}

	public static function register_rest_routes() {
		$public_submit = new BR_REST_Controller();
		$public_submit->register_routes();

		$public_read = new BR_REST_Public_Controller();
		$public_read->register_routes();

		$admin_api = new BR_REST_Admin_Controller();
		$admin_api->register_routes();
	}
}
