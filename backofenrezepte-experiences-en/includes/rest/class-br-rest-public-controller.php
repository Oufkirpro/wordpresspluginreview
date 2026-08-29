<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET /wp-json/backofenrezepte/v1/experiences/{recipe_id}
 * Public, read-only, approved-data-only aggregate endpoint.
 * Can be disabled entirely via Settings.
 */
class BR_REST_Public_Controller {

	const NAMESPACE_ = 'backofenrezepte/v1';

	public function register_routes() {
		register_rest_route(
			self::NAMESPACE_,
			'/experiences/(?P<recipe_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_get' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'recipe_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);
	}

	public function handle_get( WP_REST_Request $request ) {
		$settings = BR_Helpers::settings();

		if ( empty( $settings['public_endpoint_enabled'] ) ) {
			return new WP_REST_Response( array( 'message' => 'Not available.' ), 404 );
		}

		$recipe_id = (int) $request->get_param( 'recipe_id' );

		$cache_key = 'br_public_summary_' . $recipe_id;
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return new WP_REST_Response( $cached, 200 );
		}

		$service = new BR_Experience_Service();
		$summary = $service->public_summary( $recipe_id );

		$results_breakdown = array();
		foreach ( $summary['results'] as $row ) {
			$results_breakdown[ $row['result'] ] = (int) $row['c'];
		}

		$response = array(
			'recipe_id'        => $recipe_id,
			'approved_count'   => (int) $summary['count'],
			'results_breakdown' => $results_breakdown,
		);

		set_transient( $cache_key, $response, 15 * MINUTE_IN_SECONDS );

		return new WP_REST_Response( $response, 200 );
	}
}
