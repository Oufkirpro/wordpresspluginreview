<?php
/**
 * Uninstall handler.
 *
 * By default this plugin NEVER deletes experience data on uninstall.
 * Data deletion only happens if the administrator has explicitly
 * enabled "Delete data on uninstall" in Settings AND
 * confirmed it there. This file only checks that explicit flag.
 *
 * @package Backofenrezepte_Experiences
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'br_exp_settings', array() );

$delete_data = ! empty( $settings['delete_data_on_uninstall'] );

if ( true === $delete_data ) {
	global $wpdb;
	$table = $wpdb->prefix . 'br_experiences';
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name only, no user input.
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

	delete_option( 'br_exp_settings' );
	delete_option( 'br_exp_db_schema_version' );
	delete_option( 'br_exp_site_salt' );
	delete_option( 'br_exp_id_counter' );
}

// If the flag is not set, options and the table are intentionally left in place.
