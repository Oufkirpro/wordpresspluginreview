<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates a submitted payload. Returns WP_Error on first failure,
 * or true if everything passes. Never trusts client-side validation.
 */
class BR_Validator {

	const MAX_PAYLOAD_BYTES   = 20480; // 20 KB
	const MAX_SHORT_TEXT_LEN  = 100;
	const MAX_CHANGES_LEN     = 1000;
	const MAX_COMMENT_LEN     = 2000;
	const MIN_TEMPERATURE     = 30;
	const MAX_TEMPERATURE     = 350;
	const MIN_TIME_MINUTES    = 1;
	const MAX_TIME_MINUTES    = 1440;

	public static function validate_payload_size( $raw_body ) {
		if ( strlen( $raw_body ) > self::MAX_PAYLOAD_BYTES ) {
			return new WP_Error( 'payload_too_large', 'Payload exceeds maximum allowed size.' );
		}
		return true;
	}

	public static function validate( array $data ) {
		// Required: recipe_id.
		if ( empty( $data['recipe_id'] ) || ! is_numeric( $data['recipe_id'] ) ) {
			return new WP_Error( 'invalid_recipe_id', 'Missing or invalid recipe_id.' );
		}

		$post = get_post( (int) $data['recipe_id'] );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return new WP_Error( 'unknown_recipe', 'Recipe does not exist or is not published.' );
		}

		// Required: oven_type.
		if ( empty( $data['oven_type'] ) || ! BR_Vocabulary::is_valid( 'oven_type', $data['oven_type'] ) ) {
			return new WP_Error( 'invalid_oven_type', 'Invalid oven_type.' );
		}

		// Required: result.
		if ( empty( $data['result'] ) || ! BR_Vocabulary::is_valid( 'result', $data['result'] ) ) {
			return new WP_Error( 'invalid_result', 'Invalid result.' );
		}

		// Optional: problem.
		if ( ! empty( $data['problem'] ) && ! BR_Vocabulary::is_valid( 'problem', $data['problem'] ) ) {
			return new WP_Error( 'invalid_problem', 'Invalid problem value.' );
		}

		// Optional: form.
		if ( ! empty( $data['form'] ) && ! BR_Vocabulary::is_valid( 'form', $data['form'] ) ) {
			return new WP_Error( 'invalid_form', 'Invalid form value.' );
		}

		// Optional: temperature range.
		if ( isset( $data['temperature'] ) && '' !== $data['temperature'] && null !== $data['temperature'] ) {
			if ( ! is_numeric( $data['temperature'] )
				|| $data['temperature'] < self::MIN_TEMPERATURE
				|| $data['temperature'] > self::MAX_TEMPERATURE ) {
				return new WP_Error( 'invalid_temperature', 'Temperature out of allowed range.' );
			}
		}

		// Optional: time_minutes range.
		if ( isset( $data['time_minutes'] ) && '' !== $data['time_minutes'] && null !== $data['time_minutes'] ) {
			if ( ! is_numeric( $data['time_minutes'] )
				|| $data['time_minutes'] < self::MIN_TIME_MINUTES
				|| $data['time_minutes'] > self::MAX_TIME_MINUTES ) {
				return new WP_Error( 'invalid_time_minutes', 'time_minutes out of allowed range.' );
			}
		}

		// Optional: quantity length.
		if ( ! empty( $data['quantity'] ) && strlen( (string) $data['quantity'] ) > self::MAX_SHORT_TEXT_LEN ) {
			return new WP_Error( 'quantity_too_long', 'quantity exceeds max length.' );
		}

		// Optional: changes length.
		if ( ! empty( $data['changes'] ) && strlen( (string) $data['changes'] ) > self::MAX_CHANGES_LEN ) {
			return new WP_Error( 'changes_too_long', 'changes exceeds max length.' );
		}

		// Optional: comment length.
		if ( ! empty( $data['comment'] ) && strlen( (string) $data['comment'] ) > self::MAX_COMMENT_LEN ) {
			return new WP_Error( 'comment_too_long', 'comment exceeds max length.' );
		}

		return true;
	}
}
