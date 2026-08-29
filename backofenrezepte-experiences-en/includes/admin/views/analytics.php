<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var array|null $aggregates */
/** @var WP_Post|null $post */
/** @var int $recipe_id */
?>
<div class="wrap br-exp-wrap">
	<h1>Statistics per Recipe</h1>

	<form method="get">
		<input type="hidden" name="page" value="backofenrezepte-experience-analytics" />
		<label for="recipe_id">Recipe ID:</label>
		<input type="number" name="recipe_id" id="recipe_id" value="<?php echo esc_attr( $recipe_id ?: '' ); ?>" />
		<button class="button">Show</button>
	</form>

	<?php if ( $aggregates ) : ?>
		<h2><?php echo $post ? esc_html( get_the_title( $post ) ) : 'Recipe deleted (ID ' . (int) $recipe_id . ')'; ?></h2>

		<h3>Status</h3>
		<table class="widefat" style="max-width:400px;">
			<?php foreach ( $aggregates['status_counts'] as $row ) : ?>
				<tr><td><?php echo esc_html( $row['status'] ); ?></td><td><?php echo (int) $row['c']; ?></td></tr>
			<?php endforeach; ?>
		</table>

		<?php foreach ( array( 'result' => 'Results', 'problem' => 'Problems', 'oven_type' => 'Oven Types' ) as $dim => $label ) : ?>
			<h3><?php echo esc_html( $label ); ?></h3>
			<table class="widefat" style="max-width:400px;">
				<?php foreach ( $aggregates['breakdowns'][ $dim ] as $row ) : ?>
					<tr><td><?php echo esc_html( $row['v'] ?: '—' ); ?></td><td><?php echo (int) $row['c']; ?></td></tr>
				<?php endforeach; ?>
			</table>
		<?php endforeach; ?>

		<p class="description">
			Note: These figures are descriptive, user-reported experience values (only approved entries are included in the breakdowns).
			They do not represent a scientifically verified statement and are not labeled "proven" just because several users reported the same thing.
		</p>
	<?php endif; ?>
</div>
