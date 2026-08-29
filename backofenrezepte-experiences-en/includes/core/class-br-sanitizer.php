<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes a validated payload into a clean array ready for storage.
 * No HTML is ever allowed in this content type — everything is
 * plain text, tags stripped entirely.
 */
class BR_Sanitizer {

	public static function sanitize( array $data ) {
		$clean = array();

		$clean['recipe_id']    = (int) $data['recipe_id'];
		$clean['oven_type']    = sanitize_key( $data['oven_type'] );
		$clean['result']       = sanitize_key( $data['result'] );
		$clean['problem']      = ! empty( $data['problem'] ) ? sanitize_key( $data['problem'] ) : null;
		$clean['form']         = ! empty( $data['form'] ) ? sanitize_key( $data['form'] ) : null;

		$clean['temperature']  = ( isset( $data['temperature'] ) && '' !== $data['temperature'] )
			? (int) $data['temperature'] : null;

		$clean['time_minutes'] = ( isset( $data['time_minutes'] ) && '' !== $data['time_minutes'] )
			? (int) $data['time_minutes'] : null;

		$clean['quantity'] = ! empty( $data['quantity'] )
			? sanitize_text_field( wp_strip_all_tags( (string) $data['quantity'] ) ) : null;

		$clean['changes'] = ! empty( $data['changes'] )
			? sanitize_textarea_field( wp_strip_all_tags( (string) $data['changes'] ) ) : null;

		$clean['comment'] = ! empty( $data['comment'] )
			? sanitize_textarea_field( wp_strip_all_tags( (string) $data['comment'] ) ) : null;

		return $clean;
	}
}
