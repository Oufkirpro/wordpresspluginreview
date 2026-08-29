<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BR_Helpers {

	public static function settings() {
		$defaults = BR_Admin_Settings::get_defaults();
		$saved    = get_option( 'br_exp_settings', array() );
		return wp_parse_args( $saved, $defaults );
	}

	public static function now_mysql() {
		return current_time( 'mysql', true ); // GMT/UTC.
	}

	public static function site_salt() {
		$salt = get_option( 'br_exp_site_salt' );
		if ( empty( $salt ) ) {
			$salt = wp_generate_password( 64, true, true );
			update_option( 'br_exp_site_salt', $salt );
		}
		return $salt;
	}

	/**
	 * Builds a non-reversible fingerprint for a request, used only for
	 * transient rate-limiting / duplicate detection. Never stored
	 * permanently, never exported, never displayed.
	 */
	public static function request_fingerprint() {
		$ip = self::client_ip();
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 150 ) : '';

		return hash_hmac( 'sha256', $ip . '|' . $ua, self::site_salt() );
	}

	private static function client_ip() {
		// Best-effort IP extraction; used only in-memory for hashing, never persisted raw.
		$candidates = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' );
		foreach ( $candidates as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$val = wp_unslash( $_SERVER[ $key ] );
				// X-Forwarded-For style headers can contain a list; take the first.
				$parts = explode( ',', $val );
				return trim( $parts[0] );
			}
		}
		return '0.0.0.0';
	}

	public static function generate_normalized_signature( array $payload ) {
		$relevant = array(
			'recipe_id'     => $payload['recipe_id'] ?? '',
			'oven_type'     => $payload['oven_type'] ?? '',
			'result'        => $payload['result'] ?? '',
			'problem'       => $payload['problem'] ?? '',
			'temperature'   => $payload['temperature'] ?? '',
			'time_minutes'  => $payload['time_minutes'] ?? '',
		);

		$normalized = strtolower( wp_json_encode( $relevant ) );
		$normalized = preg_replace( '/\s+/', ' ', trim( $normalized ) );

		return hash( 'sha256', $normalized );
	}

	public static function generic_error_message() {
		return __( 'Unfortunately your experience could not be saved. Please try again later.', 'backofenrezepte-experiences' );
	}

	public static function success_message() {
		return __( 'Thank you! Your experience has been submitted and will be reviewed before publication.', 'backofenrezepte-experiences' );
	}
}
