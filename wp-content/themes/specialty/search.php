<?php get_header(); ?>

<?php get_template_part( 'part-hero', get_post_type() ); ?>

<main class="main main-elevated">
	<div class="container">
		<div class="row">
			<div class="col-xl-9 col-lg-8 col-xs-12">
				<div class="content-wrap">
					<?php
						global $wp_query;

						$found = $wp_query->found_posts;
						$none  = esc_html__( 'No results found. Please broaden your terms and search again.', 'specialty' );
						$one   = esc_html__( 'Just one result found. We either nailed it, or you might want to broaden your terms and search again.', 'specialty' );
						/* translators: %d is a number of search results. */
						$many = sprintf( _n( '%d result found.', '%d results found.', $found, 'specialty' ), $found );
					?>
					<div class="search-notice">
						<div class="entry-content">
							<p><?php specialty_e_inflect( $found, $none, $one, $many ); ?></p>
							<?php if ( $found < 2 ) {
								get_search_form();
							} ?>
						</div>
					</div>

					<?php while ( have_posts() ) : the_post();  ?>
						<?php get_template_part( 'listing-items/article', get_post_type() ); ?>
					<?php endwhile; ?>
				</div>

				<?php specialty_posts_pagination(); ?>
			</div>

			<div class="col-xl-3 col-lg-4 col-xs-12">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>
</main>

<?php get_footer();
