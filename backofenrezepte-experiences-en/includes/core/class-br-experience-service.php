<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business logic layer. Orchestrates validation, security, and
 * storage. Never touches $wpdb directly.
 */
class BR_Experience_Service {

	private $repo;

	public function __construct() {
		$this->repo = new BR_Experience_Repository();
	}

	/**
	 * Handles a full public submission.
	 *
	 * @param array $raw_input Raw decoded JSON body from the request.
	 * @return array|WP_Error  array( 'experience_id' => ... ) or WP_Error.
	 */
	public function submit( array $raw_input ) {
		// Validate structural/business rules first.
		$valid = BR_Validator::validate( $raw_input );
		if ( is_wp_error( $valid ) ) {
			BR_Logger::log( 'notice', 'Validation failed: ' . $valid->get_error_code() );
			return new WP_Error( 'validation_error', BR_Helpers::generic_error_message() );
		}

		$clean = BR_Sanitizer::sanitize( $raw_input );

		$security_meta = array();
		$security_result = BR_Security_Guard::check( $raw_input, $clean, $security_meta );

		if ( is_wp_error( $security_result ) ) {
			if ( 'silent_discard' === $security_result->get_error_code() ) {
				// Bot-facing response looks like success; nothing is stored.
				return array( 'experience_id' => BR_Id_Generator::fake_preview() );
			}
			return $security_result;
		}

		$post = get_post( $clean['recipe_id'] );

		$row = $clean;
		$row['experience_id']    = BR_Id_Generator::next();
		$row['recipe_slug']      = $post ? $post->post_name : null;
		$row['original_payload'] = $clean;
		$row['security_meta']    = $security_meta;

		$new_id = $this->repo->insert( $row );

		if ( is_wp_error( $new_id ) ) {
			BR_Logger::log( 'error', 'DB insert failed for new experience.' );
			return new WP_Error( 'storage_error', BR_Helpers::generic_error_message() );
		}

		return array( 'experience_id' => $row['experience_id'] );
	}

	public function change_status( $id, $new_status ) {
		$allowed = array( 'pending', 'approved', 'rejected' );
		if ( ! in_array( $new_status, $allowed, true ) ) {
			return new WP_Error( 'invalid_status', 'Invalid status value.' );
		}

		$fields = array( 'status' => $new_status );
		$fields['approved_at'] = ( 'approved' === $new_status ) ? BR_Helpers::now_mysql() : null;

		return $this->repo->update( $id, $fields );
	}

	public function update_fields( $id, array $fields ) {
		$allowed_keys = array(
			'oven_type', 'temperature', 'time_minutes', 'form', 'quantity',
			'result', 'problem', 'changes', 'comment', 'admin_note', 'status',
		);

		$update = array();
		foreach ( $fields as $key => $val ) {
			if ( in_array( $key, $allowed_keys, true ) ) {
				$update[ $key ] = $val;
			}
		}

		if ( isset( $update['status'] ) ) {
			$update['approved_at'] = ( 'approved' === $update['status'] ) ? BR_Helpers::now_mysql() : null;
		}

		return $this->repo->update( $id, $update );
	}

	public function delete( $id ) {
		return $this->repo->delete( $id );
	}

	public function get( $id ) {
		return $this->repo->get( $id );
	}

	public function query( array $args ) {
		return $this->repo->query( $args );
	}

	public function overview() {
		return $this->repo->counts_overview();
	}

	public function recipe_aggregates( $recipe_id ) {
		return $this->repo->recipe_aggregates( $recipe_id );
	}

	public function public_summary( $recipe_id ) {
		return $this->repo->approved_public_summary( $recipe_id );
	}
}
