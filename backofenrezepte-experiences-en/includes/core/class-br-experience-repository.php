<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sole owner of SQL for the br_experiences table.
 * No other class in this plugin may query this table directly.
 */
class BR_Experience_Repository {

	private function table() {
		global $wpdb;
		return $wpdb->prefix . BR_EXP_TABLE_EXPERIENCES;
	}

	public function insert( array $row ) {
		global $wpdb;

		$now = BR_Helpers::now_mysql();

		$data = array(
			'experience_id'     => $row['experience_id'],
			'recipe_id'         => $row['recipe_id'],
			'recipe_slug'       => $row['recipe_slug'],
			'oven_type'         => $row['oven_type'],
			'temperature'       => $row['temperature'],
			'time_minutes'      => $row['time_minutes'],
			'form'              => $row['form'],
			'quantity'          => $row['quantity'],
			'result'            => $row['result'],
			'problem'           => $row['problem'],
			'changes'           => $row['changes'],
			'comment'           => $row['comment'],
			'status'            => 'pending',
			'admin_note'        => null,
			'original_payload'  => wp_json_encode( $row['original_payload'] ),
			'security_meta'     => wp_json_encode( $row['security_meta'] ),
			'created_at'        => $now,
			'updated_at'        => $now,
			'approved_at'       => null,
		);

		$formats = array(
			'%s', '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s',
			'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
		);

		$ok = $wpdb->insert( $this->table(), $data, $formats );

		if ( false === $ok ) {
			return new WP_Error( 'db_insert_failed', 'Insert failed.' );
		}

		return (int) $wpdb->insert_id;
	}

	public function get( $id ) {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table()} WHERE id = %d", $id ),
			ARRAY_A
		);
		return $row ?: null;
	}

	public function update( $id, array $fields ) {
		global $wpdb;

		$fields['updated_at'] = BR_Helpers::now_mysql();

		$ok = $wpdb->update( $this->table(), $fields, array( 'id' => (int) $id ) );

		return false !== $ok;
	}

	public function delete( $id ) {
		global $wpdb;
		return false !== $wpdb->delete( $this->table(), array( 'id' => (int) $id ), array( '%d' ) );
	}

	/**
	 * Flexible listing with filters, pagination, sorting.
	 * $args keys: recipe_id, status, result, problem, oven_type,
	 * date_from, date_to, search, orderby, order, page, per_page.
	 */
	public function query( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'recipe_id'  => 0,
			'status'     => '',
			'result'     => '',
			'problem'    => '',
			'oven_type'  => '',
			'date_from'  => '',
			'date_to'    => '',
			'search'     => '',
			'orderby'    => 'created_at',
			'order'      => 'DESC',
			'page'       => 1,
			'per_page'   => 20,
		);
		$args = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['recipe_id'] ) ) {
			$where[]  = 'recipe_id = %d';
			$params[] = (int) $args['recipe_id'];
		}
		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['result'] ) ) {
			$where[]  = 'result = %s';
			$params[] = $args['result'];
		}
		if ( ! empty( $args['problem'] ) ) {
			$where[]  = 'problem = %s';
			$params[] = $args['problem'];
		}
		if ( ! empty( $args['oven_type'] ) ) {
			$where[]  = 'oven_type = %s';
			$params[] = $args['oven_type'];
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at >= %s';
			$params[] = $args['date_from'] . ' 00:00:00';
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at <= %s';
			$params[] = $args['date_to'] . ' 23:59:59';
		}
		if ( ! empty( $args['search'] ) ) {
			$where[]  = '(experience_id LIKE %s OR comment LIKE %s OR changes LIKE %s)';
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$allowed_orderby = array( 'created_at', 'updated_at', 'status', 'recipe_id', 'result' );
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$per_page = max( 1, min( 200, (int) $args['per_page'] ) );
		$page     = max( 1, (int) $args['page'] );
		$offset   = ( $page - 1 ) * $per_page;

		$where_sql = implode( ' AND ', $where );

		$sql = "SELECT * FROM {$this->table()} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$params[] = $per_page;
		$params[] = $offset;

		$prepared = $wpdb->prepare( $sql, $params );
		$rows     = $wpdb->get_results( $prepared, ARRAY_A );

		$count_sql = "SELECT COUNT(*) FROM {$this->table()} WHERE {$where_sql}";
		$count_params = array_slice( $params, 0, -2 );
		$total = $count_params
			? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $count_params ) )
			: (int) $wpdb->get_var( $count_sql );

		return array(
			'items' => $rows,
			'total' => $total,
			'pages' => (int) ceil( $total / $per_page ),
		);
	}

	public function counts_overview() {
		global $wpdb;
		$table = $this->table();

		$rows = $wpdb->get_results( "SELECT status, COUNT(*) as c FROM {$table} GROUP BY status", ARRAY_A );

		$out = array( 'total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0 );
		foreach ( $rows as $r ) {
			$out[ $r['status'] ] = (int) $r['c'];
			$out['total']       += (int) $r['c'];
		}
		return $out;
	}

	public function recipe_aggregates( $recipe_id ) {
		global $wpdb;
		$table = $this->table();

		$status_counts = $wpdb->get_results(
			$wpdb->prepare( "SELECT status, COUNT(*) as c FROM {$table} WHERE recipe_id = %d GROUP BY status", $recipe_id ),
			ARRAY_A
		);

		$dims = array( 'result', 'problem', 'oven_type' );
		$breakdowns = array();
		foreach ( $dims as $dim ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT {$dim} as v, COUNT(*) as c FROM {$table} WHERE recipe_id = %d AND status = 'approved' GROUP BY {$dim}", $recipe_id ),
				ARRAY_A
			);
			$breakdowns[ $dim ] = $rows;
		}

		return array(
			'status_counts' => $status_counts,
			'breakdowns'    => $breakdowns,
		);
	}

	public function approved_public_summary( $recipe_id ) {
		global $wpdb;
		$table = $this->table();

		$count = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE recipe_id = %d AND status = 'approved'", $recipe_id )
		);

		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT result, COUNT(*) as c FROM {$table} WHERE recipe_id = %d AND status = 'approved' GROUP BY result", $recipe_id ),
			ARRAY_A
		);

		return array( 'count' => $count, 'results' => $results );
	}
}
