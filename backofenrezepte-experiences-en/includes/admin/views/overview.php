<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var array $overview */
/** @var BR_List_Table $list_table */
?>
<div class="wrap br-exp-wrap">
	<h1>Backofenrezepte Experiences</h1>

	<div class="br-exp-cards">
		<div class="br-exp-card">
			<span class="br-exp-card-number"><?php echo (int) $overview['total']; ?></span>
			<span class="br-exp-card-label">Total</span>
		</div>
		<div class="br-exp-card br-exp-card-pending">
			<span class="br-exp-card-number"><?php echo (int) $overview['pending']; ?></span>
			<span class="br-exp-card-label">Pending</span>
		</div>
		<div class="br-exp-card br-exp-card-approved">
			<span class="br-exp-card-number"><?php echo (int) $overview['approved']; ?></span>
			<span class="br-exp-card-label">Approved</span>
		</div>
		<div class="br-exp-card br-exp-card-rejected">
			<span class="br-exp-card-number"><?php echo (int) $overview['rejected']; ?></span>
			<span class="br-exp-card-label">Rejected</span>
		</div>
	</div>

	<form method="get">
		<input type="hidden" name="page" value="backofenrezepte-experiences" />
		<div class="br-exp-filters">
			<input type="text" name="s" placeholder="Search…" value="<?php echo esc_attr( isset( $_GET['s'] ) ? wp_unslash( $_GET['s'] ) : '' ); ?>" />

			<select name="filter_status">
				<option value="">All statuses</option>
				<option value="pending" <?php selected( isset( $_GET['filter_status'] ) ? wp_unslash( $_GET['filter_status'] ) : '', 'pending' ); ?>>Pending</option>
				<option value="approved" <?php selected( isset( $_GET['filter_status'] ) ? wp_unslash( $_GET['filter_status'] ) : '', 'approved' ); ?>>Approved</option>
				<option value="rejected" <?php selected( isset( $_GET['filter_status'] ) ? wp_unslash( $_GET['filter_status'] ) : '', 'rejected' ); ?>>Rejected</option>
			</select>

			<select name="filter_result">
				<option value="">All results</option>
				<?php foreach ( BR_Vocabulary::results() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $_GET['filter_result'] ) ? wp_unslash( $_GET['filter_result'] ) : '', $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>

			<select name="filter_problem">
				<option value="">All problems</option>
				<?php foreach ( BR_Vocabulary::problems() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $_GET['filter_problem'] ) ? wp_unslash( $_GET['filter_problem'] ) : '', $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>

			<select name="filter_oven">
				<option value="">All oven types</option>
				<?php foreach ( BR_Vocabulary::oven_types() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $_GET['filter_oven'] ) ? wp_unslash( $_GET['filter_oven'] ) : '', $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>

			<input type="date" name="date_from" value="<?php echo esc_attr( isset( $_GET['date_from'] ) ? wp_unslash( $_GET['date_from'] ) : '' ); ?>" />
			<input type="date" name="date_to" value="<?php echo esc_attr( isset( $_GET['date_to'] ) ? wp_unslash( $_GET['date_to'] ) : '' ); ?>" />

			<button class="button">Filter</button>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=backofenrezepte-experiences' ) ); ?>">Reset</a>

			<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=backofenrezepte-experience-settings' ) ); ?>#export">CSV Export</a>
		</div>
	</form>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="br_exp_bulk" />
		<?php wp_nonce_field( 'br_exp_bulk' ); ?>
		<div class="br-exp-bulk-row">
			<select name="bulk_action">
				<option value="">Bulk action</option>
				<option value="approve">Approve</option>
				<option value="reject">Reject</option>
				<option value="delete">Delete</option>
			</select>
			<button class="button" onclick="return confirm('Really run this bulk action?')">Apply</button>
		</div>
		<?php $list_table->display(); ?>
	</form>
</div>
