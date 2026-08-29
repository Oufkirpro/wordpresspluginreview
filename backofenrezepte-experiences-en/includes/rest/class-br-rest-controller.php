<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST /wp-json/backofenrezepte/v1/experiences
 * Public, anonymous submission endpoint.
 */
class BR_REST_Controller {

	const NAMESPACE_ = 'backofenrezepte/v1';

	public function register_routes() {
		register_rest_route(
			self::NAMESPACE_,
			'/experiences',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_submit' ),
				'permission_callback' => '__return_true', // Intentionally public; layered security in the service.
				'args'                => array(),
			)
		);
	}

	public function handle_submit( WP_REST_Request $request ) {
		$raw_body = $request->get_body();

		$size_check = BR_Validator::validate_payload_size( $raw_body );
		if ( is_wp_error( $size_check ) ) {
			return $this->error_response();
		}

		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			return $this->error_response();
		}

		// Nonce is checked as an anti-CSRF signal only, never as authentication.
		// Missing/invalid nonce does not hard-block the request (public endpoint),
		// but is not required for functioning — logged only if present and invalid.
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( $nonce && ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			BR_Logger::log( 'notice', 'Invalid nonce on public submission (non-blocking).' );
		}

		$service = new BR_Experience_Service();
		$result  = $service->submit( $params );

		if ( is_wp_error( $result ) ) {
			return $this->error_response();
		}

		return new WP_REST_Response(
			array(
				'success'       => true,
				'experience_id' => $result['experience_id'],
				'message'       => BR_Helpers::success_message(),
			),
			201
		);
	}

	private function error_response() {
		return new WP_REST_Response(
			array(
				'success' => false,
				'code'    => 'validation_error',
				'message' => BR_Helpers::generic_error_message(),
			),
			400
		);
	}
}
