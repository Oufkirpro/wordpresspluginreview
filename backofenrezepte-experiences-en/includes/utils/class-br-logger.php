<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controlled technical logging for administrators/developers.
 * Never logs full payloads, IPs, stack traces to visitors, or secrets.
 * Caps log size to avoid uncontrolled growth.
 */
class BR_Logger {

	const MAX_ENTRIES = 300;

	public static function log( $level, $message, array $context = array() ) {
		$settings = BR_Helpers::settings();

		if ( empty( $settings['log_level'] ) || 'off' === $settings['log_level'] ) {
			return;
		}

		if ( 'basic' === $settings['log_level'] && 'debug' === $level ) {
			return;
		}

		// Defensive: never allow raw IP or full payload text into the log.
		unset( $context['ip'], $context['payload'], $context['comment'], $context['changes'] );

		$entry = array(
			'time'    => BR_Helpers::now_mysql(),
			'level'   => sanitize_key( $level ),
			'message' => sanitize_text_field( $message ),
			'context' => $context,
		);

		$log   = get_option( 'br_exp_log', array() );
		$log[] = $entry;

		if ( count( $log ) > self::MAX_ENTRIES ) {
			$log = array_slice( $log, -1 * self::MAX_ENTRIES );
		}

		update_option( 'br_exp_log', $log, false );

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( '[Backofenrezepte Experiences] %s: %s', $level, $message ) );
		}
	}

	public static function get_recent( $limit = 100 ) {
		$log = get_option( 'br_exp_log', array() );
		return array_slice( array_reverse( $log ), 0, $limit );
	}

	public static function clear() {
		delete_option( 'br_exp_log' );
	}
}
