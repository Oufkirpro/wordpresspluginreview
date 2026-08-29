<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BR_Admin_Detail {

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$id      = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$service = new BR_Experience_Service();
		$item    = $service->get( $id );

		if ( ! $item ) {
			echo '<div class="wrap"><h1>Experience not found</h1></div>';
			return;
		}

		$post = get_post( (int) $item['recipe_id'] );

		include BR_EXP_PLUGIN_DIR . 'includes/admin/views/detail.php';
	}
}
