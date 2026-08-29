<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BR_List_Table extends WP_List_Table {

	private $service;

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'experience',
				'plural'   => 'experiences',
				'ajax'     => false,
			)
		);
		$this->service = new BR_Experience_Service();
	}

	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'experience_id' => 'Experience ID',
			'recipe'        => 'Recipe',
			'result'        => 'Result',
			'problem'       => 'Problem',
			'oven_type'     => 'Oven',
			'temperature'   => 'Temperature',
			'time_minutes'  => 'Time',
			'status'        => 'Status',
			'created_at'    => 'Date',
		);
	}

	public function get_sortable_columns() {
		return array(
			'created_at' => array( 'created_at', true ),
			'status'     => array( 'status', false ),
			'recipe'     => array( 'recipe_id', false ),
		);
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="ids[]" value="%d" />', $item['id'] );
	}

	public function column_recipe( $item ) {
		$post = get_post( (int) $item['recipe_id'] );
		if ( $post ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( get_edit_post_link( $post->ID ) ), esc_html( get_the_title( $post ) ) );
		}
		return sprintf( 'Recipe deleted (ID %d)', (int) $item['recipe_id'] );
	}

	public function column_status( $item ) {
		$labels = array( 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected' );
		$label  = $labels[ $item['status'] ] ?? $item['status'];
		return sprintf( '<span class="br-status br-status-%s">%s</span>', esc_attr( $item['status'] ), esc_html( $label ) );
	}

	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '—';
	}

	protected function get_bulk_actions() {
		return array(
			'approve' => 'Approve',
			'reject'  => 'Reject',
			'delete'  => 'Delete',
		);
	}

	public function column_experience_id( $item ) {
		$detail_url = admin_url( 'admin.php?page=backofenrezepte-experience-detail&id=' . $item['id'] );
		$actions    = array(
			'view'    => sprintf( '<a href="%s">View</a>', esc_url( $detail_url ) ),
			'approve' => $this->row_status_link( $item['id'], 'approved', 'Approve' ),
			'reject'  => $this->row_status_link( $item['id'], 'rejected', 'Reject' ),
			'delete'  => $this->row_delete_link( $item['id'] ),
		);
		return sprintf( '<a href="%s"><strong>%s</strong></a>%s', esc_url( $detail_url ), esc_html( $item['experience_id'] ), $this->row_actions( $actions ) );
	}

	private function row_status_link( $id, $status, $label ) {
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=br_exp_save_status&id=' . $id . '&status=' . $status ),
			'br_exp_status_change'
		);
		return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $label ) );
	}

	private function row_delete_link( $id ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=br_exp_delete&id=' . $id ), 'br_exp_delete' );
		return sprintf( '<a href="%s" onclick="return confirm(\'Really delete?\')">Delete</a>', esc_url( $url ) );
	}

	public function prepare_items() {
		$per_page = 20;
		$current_page = $this->get_pagenum();

		$args = array(
			'recipe_id' => isset( $_GET['filter_recipe'] ) ? (int) $_GET['filter_recipe'] : 0,
			'status'    => isset( $_GET['filter_status'] ) ? sanitize_key( wp_unslash( $_GET['filter_status'] ) ) : '',
			'result'    => isset( $_GET['filter_result'] ) ? sanitize_key( wp_unslash( $_GET['filter_result'] ) ) : '',
			'problem'   => isset( $_GET['filter_problem'] ) ? sanitize_key( wp_unslash( $_GET['filter_problem'] ) ) : '',
			'oven_type' => isset( $_GET['filter_oven'] ) ? sanitize_key( wp_unslash( $_GET['filter_oven'] ) ) : '',
			'date_from' => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
			'date_to'   => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
			'search'    => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
			'orderby'   => isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
			'order'     => isset( $_GET['order'] ) ? sanitize_key( wp_unslash( $_GET['order'] ) ) : 'desc',
			'page'      => $current_page,
			'per_page'  => $per_page,
		);

		$result = $this->service->query( $args );

		$this->items = $result['items'];

		$this->set_pagination_args(
			array(
				'total_items' => $result['total'],
				'per_page'    => $per_page,
				'total_pages' => $result['pages'],
			)
		);

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
	}
}
