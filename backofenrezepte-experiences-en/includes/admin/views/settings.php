<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var array $settings */
?>
<div class="wrap br-exp-wrap">
	<h1>Settings</h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success"><p>Settings saved.</p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="br_exp_save_settings" />
		<?php wp_nonce_field( 'br_exp_settings_save' ); ?>

		<table class="form-table">
			<tr>
				<th><label for="rate_limit_per_hour">Rate limit (submissions/hour)</label></th>
				<td><input type="number" name="rate_limit_per_hour" id="rate_limit_per_hour" value="<?php echo esc_attr( $settings['rate_limit_per_hour'] ); ?>" min="1" max="100" /></td>
			</tr>
			<tr>
				<th><label for="min_fill_seconds">Minimum fill time (seconds)</label></th>
				<td><input type="number" name="min_fill_seconds" id="min_fill_seconds" value="<?php echo esc_attr( $settings['min_fill_seconds'] ); ?>" min="0" max="60" /></td>
			</tr>
			<tr>
				<th><label for="public_endpoint_enabled">Public API enabled</label></th>
				<td><label><input type="checkbox" name="public_endpoint_enabled" id="public_endpoint_enabled" value="1" <?php checked( $settings['public_endpoint_enabled'], 1 ); ?> /> Make aggregated approved data publicly available</label></td>
			</tr>
			<tr>
				<th><label for="log_level">Log level</label></th>
				<td>
					<select name="log_level" id="log_level">
						<option value="off" <?php selected( $settings['log_level'], 'off' ); ?>>Off</option>
						<option value="basic" <?php selected( $settings['log_level'], 'basic' ); ?>>Basic</option>
						<option value="verbose" <?php selected( $settings['log_level'], 'verbose' ); ?>>Verbose</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="delete_data_on_uninstall">Delete data on uninstall</label></th>
				<td>
					<label><input type="checkbox" name="delete_data_on_uninstall" id="delete_data_on_uninstall" value="1" <?php checked( $settings['delete_data_on_uninstall'], 1 ); ?> /> Warning: irreversibly deletes all experience data on uninstall</label>
				</td>
			</tr>
		</table>

		<?php submit_button( 'Save Settings' ); ?>
	</form>

	<hr />

	<h2 id="export">CSV Export</h2>
	<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="br_exp_export" />
		<?php wp_nonce_field( 'br_exp_export' ); ?>
		<table class="form-table">
			<tr>
				<th><label for="export_status">Status</label></th>
				<td>
					<select name="status" id="export_status">
						<option value="">All</option>
						<option value="pending">Pending</option>
						<option value="approved">Approved</option>
						<option value="rejected">Rejected</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="export_recipe">Recipe ID (optional)</label></th>
				<td><input type="number" name="recipe_id" id="export_recipe" /></td>
			</tr>
			<tr>
				<th>Fields</th>
				<td>
					<label><input type="checkbox" name="include_admin_note" value="1" /> Include internal note (not recommended)</label>
				</td>
			</tr>
		</table>
		<?php submit_button( 'Download CSV', 'secondary' ); ?>
	</form>
</div>
