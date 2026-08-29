<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BR_Activator {

	public static function activate() {
		self::create_table();
		self::maybe_generate_salt();
		self::maybe_init_settings();

		update_option( 'br_exp_db_schema_version', BR_EXP_DB_SCHEMA_VERSION );
		update_option( 'br_exp_plugin_version', BR_EXP_VERSION );

		if ( false === get_option( 'br_exp_id_counter', false ) ) {
			add_option( 'br_exp_id_counter', 0 );
		}
	}

	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = $wpdb->prefix . BR_EXP_TABLE_EXPERIENCES;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			experience_id VARCHAR(20) NOT NULL,
			recipe_id BIGINT UNSIGNED NOT NULL,
			recipe_slug VARCHAR(200) NULL,
			oven_type VARCHAR(50) NOT NULL,
			temperature SMALLINT UNSIGNED NULL,
			time_minutes SMALLINT UNSIGNED NULL,
			form VARCHAR(50) NULL,
			quantity VARCHAR(100) NULL,
			result VARCHAR(50) NOT NULL,
			problem VARCHAR(50) NULL,
			changes TEXT NULL,
			comment TEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			admin_note TEXT NULL,
			original_payload LONGTEXT NULL,
			security_meta TEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			approved_at DATETIME NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY experience_id (experience_id),
			KEY recipe_id (recipe_id),
			KEY status (status),
			KEY created_at (created_at),
			KEY recipe_status (recipe_id, status)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	public static function maybe_generate_salt() {
		if ( false === get_option( 'br_exp_site_salt', false ) ) {
			add_option( 'br_exp_site_salt', wp_generate_password( 64, true, true ) );
		}
	}

	public static function maybe_init_settings() {
		if ( false === get_option( 'br_exp_settings', false ) ) {
			add_option( 'br_exp_settings', BR_Admin_Settings::get_defaults() );
		}
	}
}
