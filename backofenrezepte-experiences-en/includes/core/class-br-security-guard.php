<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Layered anonymous-submission protection.
 * All checks fail closed with a generic error; no distinct "spam
 * detected" signal is ever returned to the client.
 */
class BR_Security_Guard {

	/**
	 * Runs all pre-storage checks. Returns true or WP_Error.
	 * $result_meta is filled with flags for security_meta storage.
	 */
	public static function check( array $raw_input, array $clean_data, array &$result_meta ) {
		$settings    = BR_Helpers::settings();
		$fingerprint = BR_Helpers::request_fingerprint();
		$result_meta = array( 'flags' => array() );

		// 1. Honeypot.
		if ( ! empty( $raw_input['hp_field'] ) ) {
			self::register_abuse( $fingerprint, 5 );
			BR_Logger::log( 'warning', 'Honeypot triggered', array( 'fingerprint' => substr( $fingerprint, 0, 12 ) ) );
			// Silent discard: looks like success to the caller, nothing is stored.
			return new WP_Error( 'silent_discard', 'honeypot' );
		}

		// 2. Timing check.
		$min_seconds = isset( $settings['min_fill_seconds'] ) ? (int) $settings['min_fill_seconds'] : 3;
		if ( ! empty( $raw_input['ts'] ) && is_numeric( $raw_input['ts'] ) ) {
			$elapsed = time() - (int) ( $raw_input['ts'] / 1000 > time() ? $raw_input['ts'] / 1000 : $raw_input['ts'] );
			if ( $elapsed >= 0 && $elapsed < $min_seconds ) {
				self::register_abuse( $fingerprint, 3 );
				return new WP_Error( 'validation_error', BR_Helpers::generic_error_message() );
			}
		}

		// 3. Abuse score / temporary block.
		if ( self::is_blocked( $fingerprint ) ) {
			return new WP_Error( 'rate_limited', BR_Helpers::generic_error_message() );
		}

		// 4. Rate limiting.
		$limit  = isset( $settings['rate_limit_per_hour'] ) ? (int) $settings['rate_limit_per_hour'] : 5;
		$count  = self::increment_rate_counter( $fingerprint );
		if ( $count > $limit ) {
			self::register_abuse( $fingerprint, 2 );
			return new WP_Error( 'rate_limited', BR_Helpers::generic_error_message() );
		}

		// 5. Duplicate detection (layered heuristic, see architecture doc).
		$signature = BR_Helpers::generate_normalized_signature( $clean_data );
		$dup_key   = 'br_sig_' . substr( hash( 'sha256', $fingerprint . $signature ), 0, 40 );

		if ( false !== get_transient( $dup_key ) ) {
			// Same fingerprint, identical payload, short window -> likely accidental double-submit.
			return new WP_Error( 'duplicate', BR_Helpers::generic_error_message() );
		}
		set_transient( $dup_key, 1, 10 * MINUTE_IN_SECONDS );

		$broad_key = 'br_seen_' . substr( hash( 'sha256', $fingerprint ), 0, 40 );
		if ( false !== get_transient( $broad_key ) ) {
			// Same fingerprint submitted again within 24h with a different payload:
			// not blocked, just flagged for a human moderator.
			$result_meta['flags'][] = 'possible_repeat';
		}
		set_transient( $broad_key, 1, DAY_IN_SECONDS );

		$result_meta['fingerprint_hash'] = substr( $fingerprint, 0, 16 ); // truncated, transient-scope reference only.

		return true;
	}

	private static function increment_rate_counter( $fingerprint ) {
		$key   = 'br_rate_' . substr( hash( 'sha256', $fingerprint ), 0, 40 );
		$count = (int) get_transient( $key );
		$count++;
		set_transient( $key, $count, HOUR_IN_SECONDS );
		return $count;
	}

	private static function register_abuse( $fingerprint, $points ) {
		$key   = 'br_abuse_' . substr( hash( 'sha256', $fingerprint ), 0, 40 );
		$score = (int) get_transient( $key );
		$score += $points;
		set_transient( $key, $score, HOUR_IN_SECONDS );

		if ( $score >= 10 ) {
			set_transient( 'br_block_' . substr( hash( 'sha256', $fingerprint ), 0, 40 ), 1, 30 * MINUTE_IN_SECONDS );
		}
	}

	private static function is_blocked( $fingerprint ) {
		return false !== get_transient( 'br_block_' . substr( hash( 'sha256', $fingerprint ), 0, 40 ) );
	}
}
