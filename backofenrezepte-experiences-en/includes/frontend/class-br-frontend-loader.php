<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exposes window.BackofenRezepteExperience on singular recipe pages
 * only. Does NOT enqueue or redesign the existing experience card —
 * that markup/CSS/JS is expected to already live in the theme/page
 * and simply reads this global.
 */
class BR_Frontend_Loader {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_localize' ) );
	}

	public static function maybe_localize() {
		if ( ! is_singular() ) {
			return;
		}

		global $post;
		if ( ! $post ) {
			return;
		}

		/**
		 * Filters whether the experience card data should be exposed
		 * on this post. Defaults to true for any published singular
		 * post/page; site owners can narrow this to a recipe CPT.
		 */
		$should_localize = apply_filters( 'br_exp_should_localize', true, $post );
		if ( ! $should_localize ) {
			return;
		}

		$settings = BR_Helpers::settings();

		// A tiny inline handle just to attach the localized data to;
		// does not load any additional JS/CSS file of its own.
		wp_register_script( 'br-exp-data', false, array(), BR_EXP_VERSION, true );
		wp_enqueue_script( 'br-exp-data' );

		wp_localize_script(
			'br-exp-data',
			'BackofenRezepteExperience',
			array(
				'recipeId'   => $post->ID,
				'restUrl'    => esc_url_raw( rest_url( 'backofenrezepte/v1/experiences' ) ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'vocabulary' => array(
					'ovenTypes' => BR_Vocabulary::oven_types(),
					'results'   => BR_Vocabulary::results(),
					'problems'  => BR_Vocabulary::problems(),
					'forms'     => BR_Vocabulary::forms(),
				),
				'i18n'       => array(
					'success' => BR_Helpers::success_message(),
					'error'   => BR_Helpers::generic_error_message(),
				),
			)
		);
	}
}
