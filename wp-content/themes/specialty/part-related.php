<?php
	$count   = apply_filters( 'specialty_single_related_count', 3 );
	$columns = apply_filters( 'specialty_single_related_columns', 3 );
	$related = specialty_get_related_posts( get_the_ID(), $count );
?>
<?php if ( $related->have_posts() ) : ?>
	<div class="entry-related">
		<?php if ( get_theme_mod( 'single_related_title', __( 'Related Articles', 'specialty' ) ) ) : ?>
			<h3 class="section-title"><?php echo esc_html( get_theme_mod( 'single_related_title', __( 'Related Articles', 'specialty' ) ) ); ?></h3>
		<?php endif; ?>

		<div class="row row-equal">
			<?php while ( $related->have_posts() ) : $related->the_post(); ?>
				<div class="<?php echo esc_attr( specialty_get_columns_classes( $columns ) ); ?>">
					<?php get_template_part( 'listing-items/entry-item', get_post_type() ); ?>
				</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</div>
<?php endif;
