<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BR_Admin_Analytics {

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$recipe_id = isset( $_GET['recipe_id'] ) ? (int) $_GET['recipe_id'] : 0;
		$aggregates = null;
		$post = null;

		if ( $recipe_id ) {
			$service    = new BR_Experience_Service();
			$aggregates = $service->recipe_aggregates( $recipe_id );
			$post       = get_post( $recipe_id );
		}

		include BR_EXP_PLUGIN_DIR . 'includes/admin/views/analytics.php';
	}
}
