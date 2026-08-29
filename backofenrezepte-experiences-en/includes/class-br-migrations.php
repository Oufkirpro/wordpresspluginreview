<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects schema version drift and applies migrations in order.
 * Never drops or truncates existing data.
 */
class BR_Migrations {

	public static function maybe_migrate() {
		$installed = get_option( 'br_exp_db_schema_version', '0' );

		if ( version_compare( $installed, BR_EXP_DB_SCHEMA_VERSION, '>=' ) ) {
			return;
		}

		// Re-running the table creation via dbDelta is safe: it only
		// adds/updates columns and indexes, never drops data.
		BR_Activator::create_table();

		/*
		 * Future migrations go here, guarded by version_compare, e.g.:
		 *
		 * if ( version_compare( $installed, '1.1.0', '<' ) ) {
		 *     self::migrate_to_1_1_0();
		 * }
		 */

		update_option( 'br_exp_db_schema_version', BR_EXP_DB_SCHEMA_VERSION );
	}
}
