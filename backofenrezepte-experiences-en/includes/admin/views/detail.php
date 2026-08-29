<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var array $item */
/** @var WP_Post|null $post */
?>
<div class="wrap br-exp-wrap">
	<h1>Experience <?php echo esc_html( $item['experience_id'] ); ?></h1>

	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success"><p>Saved.</p></div>
	<?php endif; ?>

	<div class="br-exp-recipe-context">
		<?php if ( $post ) : ?>
			<p><strong>Recipe:</strong> <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" target="_blank"><?php echo esc_html( get_the_title( $post ) ); ?></a> (ID <?php echo (int) $post->ID; ?>)</p>
		<?php else : ?>
			<p><strong>Recipe:</strong> Recipe deleted (ID <?php echo (int) $item['recipe_id']; ?>)</p>
		<?php endif; ?>
		<p>
			<strong>Created:</strong> <?php echo esc_html( $item['created_at'] ); ?> ·
			<strong>Updated:</strong> <?php echo esc_html( $item['updated_at'] ); ?> ·
			<strong>Approved:</strong> <?php echo esc_html( $item['approved_at'] ? $item['approved_at'] : '—' ); ?>
		</p>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="br_exp_save_detail" />
		<input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>" />
		<?php wp_nonce_field( 'br_exp_detail_save' ); ?>

		<table class="form-table">
			<tr>
				<th><label for="oven_type">Oven</label></th>
				<td>
					<select name="oven_type" id="oven_type">
						<?php foreach ( BR_Vocabulary::oven_types() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item['oven_type'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="temperature">Temperature (°C)</label></th>
				<td><input type="number" name="temperature" id="temperature" value="<?php echo esc_attr( $item['temperature'] ); ?>" min="30" max="350" /></td>
			</tr>
			<tr>
				<th><label for="time_minutes">Time (minutes)</label></th>
				<td><input type="number" name="time_minutes" id="time_minutes" value="<?php echo esc_attr( $item['time_minutes'] ); ?>" min="1" max="1440" /></td>
			</tr>
			<tr>
				<th><label for="form">Baking Form</label></th>
				<td>
					<select name="form" id="form">
						<option value="">—</option>
						<?php foreach ( BR_Vocabulary::forms() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item['form'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="quantity">Quantity</label></th>
				<td><input type="text" name="quantity" id="quantity" class="regular-text" value="<?php echo esc_attr( $item['quantity'] ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="result">Result</label></th>
				<td>
					<select name="result" id="result">
						<?php foreach ( BR_Vocabulary::results() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item['result'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="problem">Problem</label></th>
				<td>
					<select name="problem" id="problem">
						<option value="">—</option>
						<?php foreach ( BR_Vocabulary::problems() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item['problem'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="changes">Changes Made</label></th>
				<td><textarea name="changes" id="changes" rows="3" class="large-text"><?php echo esc_textarea( $item['changes'] ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="comment">Comment</label></th>
				<td><textarea name="comment" id="comment" rows="4" class="large-text"><?php echo esc_textarea( $item['comment'] ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="status">Status</label></th>
				<td>
					<select name="status" id="status">
						<option value="pending" <?php selected( $item['status'], 'pending' ); ?>>Pending</option>
						<option value="approved" <?php selected( $item['status'], 'approved' ); ?>>Approved</option>
						<option value="rejected" <?php selected( $item['status'], 'rejected' ); ?>>Rejected</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="admin_note">Internal Note</label></th>
				<td>
					<textarea name="admin_note" id="admin_note" rows="3" class="large-text"><?php echo esc_textarea( $item['admin_note'] ); ?></textarea>
					<p class="description">Visible internally only. Never output via a public API or the (default) CSV export.</p>
				</td>
			</tr>
		</table>

		<?php submit_button( 'Save' ); ?>
	</form>

	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=backofenrezepte-experiences' ) ); ?>">&larr; Back to overview</a>
	</p>
</div>
