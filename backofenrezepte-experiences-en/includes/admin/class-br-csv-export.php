<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BR_Csv_Export {

	public static function handle_export_request() {
		check_admin_referer( 'br_exp_export' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not authorized.' );
		}

		$status    = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$recipe_id = isset( $_GET['recipe_id'] ) ? (int) $_GET['recipe_id'] : 0;
		$include_admin_note = ! empty( $_GET['include_admin_note'] );

		$service = new BR_Experience_Service();
		$result  = $service->query(
			array(
				'status'    => $status,
				'recipe_id' => $recipe_id,
				'page'      => 1,
				'per_page'  => 100000, // export is capability-gated; full set is intentional.
			)
		);

		$filename = 'backofenrezepte-experiences-' . gmdate( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$output = fopen( 'php://output', 'w' );

		// UTF-8 BOM for Excel compatibility.
		fwrite( $output, "\xEF\xBB\xBF" );

		$columns = array(
			'experience_id', 'recipe_id', 'recipe_slug', 'oven_type', 'temperature',
			'time_minutes', 'form', 'quantity', 'result', 'problem', 'changes',
			'comment', 'status', 'created_at', 'updated_at', 'approved_at',
		);
		if ( $include_admin_note ) {
			$columns[] = 'admin_note';
		}

		fputcsv( $output, $columns, ';' );

		foreach ( $result['items'] as $row ) {
			$line = array();
			foreach ( $columns as $col ) {
				$line[] = isset( $row[ $col ] ) ? $row[ $col ] : '';
			}
			fputcsv( $output, $line, ';' );
		}

		fclose( $output );
		exit;
	}
}
