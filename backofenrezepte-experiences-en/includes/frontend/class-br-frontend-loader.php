<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backofenrezepte Experience Frontend Loader
 *
 * Loads the Experience Card CSS/JS automatically
 * and exposes the recipe-specific REST configuration.
 */
class BR_Frontend_Loader {

	public static function init() {

		add_action(
			'wp_enqueue_scripts',
			array( __CLASS__, 'enqueue_assets' )
		);

	}


	public static function enqueue_assets() {

		if ( ! is_singular() ) {
			return;
		}


		global $post;

		if ( ! $post ) {
			return;
		}


		/**
		 * Allow the site to restrict the Experience Card
		 * to specific post types if needed.
		 */
		$should_load = apply_filters(
			'br_exp_should_localize',
			true,
			$post
		);


		if ( ! $should_load ) {
			return;
		}


		/**
		 * ----------------------------------------------------
		 * CSS
		 * ----------------------------------------------------
		 */

		wp_enqueue_style(
			'br-experience',
			BR_EXP_PLUGIN_URL . 'assets/css/experience.css',
			array(),
			BR_EXP_VERSION
		);


		/**
		 * ----------------------------------------------------
		 * JavaScript
		 * ----------------------------------------------------
		 */

		wp_enqueue_script(
			'br-experience',
			BR_EXP_PLUGIN_URL . 'assets/js/experience.js',
			array(),
			BR_EXP_VERSION,
			true
		);


		/**
		 * ----------------------------------------------------
		 * Frontend configuration
		 * ----------------------------------------------------
		 */

		$settings = BR_Helpers::settings();


		wp_localize_script(
			'br-experience',
			'BackofenRezepteExperience',
			array(

				'recipeId' =>
					(int) $post->ID,

				'restUrl' =>
					esc_url_raw(
						rest_url(
							'backofenrezepte/v1/experiences'
						)
					),

				'nonce' =>
					wp_create_nonce(
						'wp_rest'
					),

				'vocabulary' => array(

					'ovenTypes' =>
						BR_Vocabulary::oven_types(),

					'results' =>
						BR_Vocabulary::results(),

					'problems' =>
						BR_Vocabulary::problems(),

					'forms' =>
						BR_Vocabulary::forms(),

				),

				'i18n' => array(

					'success' =>
						BR_Helpers::success_message(),

					'error' =>
						BR_Helpers::generic_error_message(),

				),

			)
		);

	}

}
