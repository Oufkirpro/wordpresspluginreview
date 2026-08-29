<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BR_Admin_Settings {

	public static function get_defaults() {
		return array(
			'rate_limit_per_hour'      => 5,
			'min_fill_seconds'         => 3,
			'public_endpoint_enabled'  => 1,
			'log_level'                => 'basic', // off | basic | verbose
			'delete_data_on_uninstall' => 0,
			'vocabulary'               => array(
				'oven_type' => array(),
				'result'    => array(),
				'problem'   => array(),
				'form'      => array(),
			),
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$settings = BR_Helpers::settings();
		include BR_EXP_PLUGIN_DIR . 'includes/admin/views/settings.php';
	}

	public static function handle_save() {
		check_admin_referer( 'br_exp_settings_save' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$settings = self::get_defaults();

		$settings['rate_limit_per_hour']     = isset( $_POST['rate_limit_per_hour'] ) ? max( 1, (int) $_POST['rate_limit_per_hour'] ) : 5;
		$settings['min_fill_seconds']        = isset( $_POST['min_fill_seconds'] ) ? max( 0, (int) $_POST['min_fill_seconds'] ) : 3;
		$settings['public_endpoint_enabled'] = ! empty( $_POST['public_endpoint_enabled'] ) ? 1 : 0;
		$settings['log_level']               = isset( $_POST['log_level'] ) ? sanitize_key( wp_unslash( $_POST['log_level'] ) ) : 'basic';
		$settings['delete_data_on_uninstall'] = ! empty( $_POST['delete_data_on_uninstall'] ) ? 1 : 0;

		update_option( 'br_exp_settings', $settings );

		wp_safe_redirect( admin_url( 'admin.php?page=backofenrezepte-experience-settings&saved=1' ) );
		exit;
	}
}
