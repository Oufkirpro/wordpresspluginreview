<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BR_Admin_Menu {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_post_br_exp_save_status', array( __CLASS__, 'handle_status_change' ) );
		add_action( 'admin_post_br_exp_save_detail', array( __CLASS__, 'handle_detail_save' ) );
		add_action( 'admin_post_br_exp_delete', array( __CLASS__, 'handle_delete' ) );
		add_action( 'admin_post_br_exp_bulk', array( __CLASS__, 'handle_bulk' ) );
		add_action( 'admin_post_br_exp_export', array( 'BR_Csv_Export', 'handle_export_request' ) );
		add_action( 'admin_post_br_exp_save_settings', array( 'BR_Admin_Settings', 'handle_save' ) );
	}

	public static function register_menu() {
		add_menu_page(
			'Backofenrezepte Experiences',
			'Backofenrezepte',
			'manage_options',
			'backofenrezepte-experiences',
			array( __CLASS__, 'render_overview' ),
			'dashicons-food',
			26
		);

		add_submenu_page( 'backofenrezepte-experiences', 'Experiences', 'Experiences', 'manage_options', 'backofenrezepte-experiences', array( __CLASS__, 'render_overview' ) );
		add_submenu_page( 'backofenrezepte-experiences', 'Detail', 'Detail', 'manage_options', 'backofenrezepte-experience-detail', array( 'BR_Admin_Detail', 'render' ) );
		add_submenu_page( 'backofenrezepte-experiences', 'Statistics', 'Statistics', 'manage_options', 'backofenrezepte-experience-analytics', array( 'BR_Admin_Analytics', 'render' ) );
		add_submenu_page( 'backofenrezepte-experiences', 'Settings', 'Settings', 'manage_options', 'backofenrezepte-experience-settings', array( 'BR_Admin_Settings', 'render' ) );
	}

	public static function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'backofenrezepte-experience' ) === false ) {
			return;
		}
		wp_enqueue_style( 'br-exp-admin', BR_EXP_PLUGIN_URL . 'assets/admin/admin.css', array(), BR_EXP_VERSION );
		wp_enqueue_script( 'br-exp-admin', BR_EXP_PLUGIN_URL . 'assets/admin/admin.js', array( 'jquery' ), BR_EXP_VERSION, true );
	}

	public static function render_overview() {
		$service  = new BR_Experience_Service();
		$overview = $service->overview();

		$list_table = new BR_List_Table();
		$list_table->prepare_items();

		include BR_EXP_PLUGIN_DIR . 'includes/admin/views/overview.php';
	}

	public static function handle_status_change() {
		check_admin_referer( 'br_exp_status_change' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$id     = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';

		$service = new BR_Experience_Service();
		$service->change_status( $id, $status );

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=backofenrezepte-experiences' ) );
		exit;
	}

	public static function handle_detail_save() {
		check_admin_referer( 'br_exp_detail_save' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

		$fields = array();
		foreach ( array( 'oven_type', 'form', 'quantity', 'result', 'problem', 'status' ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$fields[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			}
		}
		foreach ( array( 'temperature', 'time_minutes' ) as $key ) {
			if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] ) {
				$fields[ $key ] = (int) $_POST[ $key ];
			}
		}
		foreach ( array( 'changes', 'comment', 'admin_note' ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$fields[ $key ] = sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) );
			}
		}

		$service = new BR_Experience_Service();
		$service->update_fields( $id, $fields );

		wp_safe_redirect( admin_url( 'admin.php?page=backofenrezepte-experience-detail&id=' . $id . '&updated=1' ) );
		exit;
	}

	public static function handle_delete() {
		check_admin_referer( 'br_exp_delete' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$id      = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$service = new BR_Experience_Service();
		$service->delete( $id );

		wp_safe_redirect( admin_url( 'admin.php?page=backofenrezepte-experiences&deleted=1' ) );
		exit;
	}

	public static function handle_bulk() {
		check_admin_referer( 'br_exp_bulk' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$ids    = isset( $_POST['ids'] ) ? array_map( 'intval', (array) $_POST['ids'] ) : array();
		$action = isset( $_POST['bulk_action'] ) ? sanitize_key( wp_unslash( $_POST['bulk_action'] ) ) : '';

		$service = new BR_Experience_Service();
		foreach ( $ids as $id ) {
			if ( 'approve' === $action ) {
				$service->change_status( $id, 'approved' );
			} elseif ( 'reject' === $action ) {
				$service->change_status( $id, 'rejected' );
			} elseif ( 'delete' === $action ) {
				$service->delete( $id );
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=backofenrezepte-experiences&bulk_done=1' ) );
		exit;
	}
}
