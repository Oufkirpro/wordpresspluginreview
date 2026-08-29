<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server-side controlled vocabularies. Never trust frontend-supplied
 * values for these fields; always validate against this list.
 *
 * Values are stored partly in settings (admin-editable) so they can be
 * expanded later without touching the database schema, and are also
 * filterable for developers.
 */
class BR_Vocabulary {

	public static function oven_types() {
		$defaults = array(
			'umluft'       => 'Fan / Convection',
			'ober_unter'   => 'Top/Bottom Heat',
			'heissluft'    => 'Hot Air (Forced Air)',
			'unterhitze'   => 'Bottom Heat Only',
			'grill'        => 'Grill/Broil',
			'dampfgarer'   => 'Steam Oven',
			'sonstiges'    => 'Other',
		);

		return self::merged( 'oven_type', $defaults );
	}

	public static function results() {
		$defaults = array(
			'gelungen'            => 'Turned out well',
			'teilweise_gelungen'  => 'Partially successful',
			'nicht_gelungen'      => 'Did not turn out',
		);

		return self::merged( 'result', $defaults );
	}

	public static function problems() {
		$defaults = array(
			'keins'          => 'No problem',
			'zu_trocken'     => 'Too dry',
			'nicht_durch'    => 'Not baked through',
			'zu_dunkel'      => 'Too dark / burnt',
			'zu_hell'        => 'Too pale / not browned',
			'zusammengefallen' => 'Collapsed / sank',
			'zu_suess'       => 'Too sweet',
			'zu_trocken_teig' => 'Dough too dry',
			'sonstiges'      => 'Other',
		);

		return self::merged( 'problem', $defaults );
	}

	public static function forms() {
		$defaults = array(
			'kastenform'  => 'Loaf Pan',
			'springform'  => 'Springform Pan',
			'blech'       => 'Baking Sheet',
			'gugelhupf'   => 'Bundt Pan',
			'muffinform'  => 'Muffin Tin',
			'sonstiges'   => 'Other',
		);

		return self::merged( 'form', $defaults );
	}

	/**
	 * Merges settings-defined additions on top of code defaults, then
	 * applies a developer filter as the final override point.
	 */
	private static function merged( $key, $defaults ) {
		$settings = get_option( 'br_exp_settings', array() );
		$custom   = isset( $settings['vocabulary'][ $key ] ) && is_array( $settings['vocabulary'][ $key ] )
			? $settings['vocabulary'][ $key ]
			: array();

		$merged = array_merge( $defaults, $custom );

		/**
		 * Filters a controlled vocabulary list.
		 *
		 * @param array  $merged Machine key => label.
		 * @param string $key    Which vocabulary ('oven_type', 'result', 'problem', 'form').
		 */
		return apply_filters( 'br_exp_vocabulary_' . $key, $merged, $key );
	}

	public static function is_valid( $key, $value ) {
		switch ( $key ) {
			case 'oven_type':
				return array_key_exists( $value, self::oven_types() );
			case 'result':
				return array_key_exists( $value, self::results() );
			case 'problem':
				return array_key_exists( $value, self::problems() );
			case 'form':
				return array_key_exists( $value, self::forms() );
			default:
				return false;
		}
	}
}
