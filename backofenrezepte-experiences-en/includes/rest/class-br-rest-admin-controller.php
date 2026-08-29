<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin-only REST actions. All require the manage_options capability
 * and (for state-changing routes) a valid wp_rest nonce, which is the
 * correct/expected use of that nonce for authenticated admin users.
 */
class BR_REST_Admin_Controller {

	const NAMESPACE_ = 'backofenrezepte/v1';

	public function register_routes() {
		register_rest_route(
			self::NAMESPACE_,
			'/admin/experiences',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'list_experiences' ),
				'permission_callback' => array( $this, 'can_manage' ),
			)
		);

		register_rest_route(
			self::NAMESPACE_,
			'/admin/experiences/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_experience' ),
					'permission_callback' => array( $this, 'can_manage' ),
				),
				array(
					'methods'             => 'PATCH',
					'callback'            => array( $this, 'update_experience' ),
					'permission_callback' => array( $this, 'can_manage' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_experience' ),
					'permission_callback' => array( $this, 'can_manage' ),
				),
			)
		);
	}

	public function can_manage() {
		return current_user_can( 'manage_options' );
	}

	public function list_experiences( WP_REST_Request $request ) {
		$service = new BR_Experience_Service();
		$args    = array(
			'recipe_id' => (int) $request->get_param( 'recipe_id' ),
			'status'    => sanitize_key( (string) $request->get_param( 'status' ) ),
			'result'    => sanitize_key( (string) $request->get_param( 'result' ) ),
			'problem'   => sanitize_key( (string) $request->get_param( 'problem' ) ),
			'oven_type' => sanitize_key( (string) $request->get_param( 'oven_type' ) ),
			'date_from' => sanitize_text_field( (string) $request->get_param( 'date_from' ) ),
			'date_to'   => sanitize_text_field( (string) $request->get_param( 'date_to' ) ),
			'search'    => sanitize_text_field( (string) $request->get_param( 'search' ) ),
			'page'      => max( 1, (int) $request->get_param( 'page' ) ),
			'per_page'  => (int) ( $request->get_param( 'per_page' ) ?: 20 ),
		);

		return new WP_REST_Response( $service->query( $args ), 200 );
	}

	public function get_experience( WP_REST_Request $request ) {
		$service = new BR_Experience_Service();
		$row     = $service->get( (int) $request->get_param( 'id' ) );

		if ( ! $row ) {
			return new WP_REST_Response( array( 'message' => 'Not found.' ), 404 );
		}

		return new WP_REST_Response( $row, 200 );
	}

	public function update_experience( WP_REST_Request $request ) {
		if ( ! $this->verify_nonce( $request ) ) {
			return new WP_REST_Response( array( 'message' => 'Invalid nonce.' ), 403 );
		}

		$service = new BR_Experience_Service();
		$id      = (int) $request->get_param( 'id' );
		$body    = $request->get_json_params();

		if ( ! is_array( $body ) ) {
			return new WP_REST_Response( array( 'message' => 'Invalid body.' ), 400 );
		}

		$sanitized = array();
		foreach ( $body as $key => $val ) {
			$sanitized[ sanitize_key( $key ) ] = is_string( $val ) ? sanitize_textarea_field( wp_strip_all_tags( $val ) ) : $val;
		}

		$ok = $service->update_fields( $id, $sanitized );

		if ( ! $ok ) {
			return new WP_REST_Response( array( 'message' => 'Update failed.' ), 500 );
		}

		return new WP_REST_Response( array( 'success' => true, 'item' => $service->get( $id ) ), 200 );
	}

	public function delete_experience( WP_REST_Request $request ) {
		if ( ! $this->verify_nonce( $request ) ) {
			return new WP_REST_Response( array( 'message' => 'Invalid nonce.' ), 403 );
		}

		$service = new BR_Experience_Service();
		$ok = $service->delete( (int) $request->get_param( 'id' ) );

		return new WP_REST_Response( array( 'success' => (bool) $ok ), $ok ? 200 : 500 );
	}

	private function verify_nonce( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		return $nonce && wp_verify_nonce( $nonce, 'wp_rest' );
	}
}
