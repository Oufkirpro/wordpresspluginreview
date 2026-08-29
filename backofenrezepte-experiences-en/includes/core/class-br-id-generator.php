<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates unique public-facing experience IDs (EXP-000001, ...).
 * Never used as the database primary key. Uses a dedicated option
 * counter (not the DB auto-increment id) so gaps/deletions don't
 * leak internal identifiers, and is safe under moderate concurrency
 * via a direct SQL increment.
 */
class BR_Id_Generator {

	public static function next() {
		global $wpdb;

		// Atomic-ish increment at the options table level.
		$option_name = 'br_exp_id_counter';

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s",
				$option_name
			)
		);

		$next = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $option_name )
		);

		if ( 0 === $next ) {
			// Option didn't exist yet; initialize and retry once.
			add_option( $option_name, 1 );
			$next = 1;
		}

		wp_cache_delete( $option_name, 'options' );

		return sprintf( 'EXP-%06d', $next );
	}

	/**
	 * Used only for the honeypot "silent discard" success response, so a
	 * bot receives a plausible-looking ID without an actual record being
	 * created or the real counter being consumed.
	 */
	public static function fake_preview() {
		$fake = wp_rand( 1, 999999 );
		return sprintf( 'EXP-%06d', $fake );
	}
}
