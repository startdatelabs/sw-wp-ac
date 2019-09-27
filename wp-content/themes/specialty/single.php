<?php get_header(); ?>

<?php get_template_part( 'part-hero', get_post_type() ); ?>

<main class="main main-elevated">
	<div class="container">
		<div class="row">
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="col-xl-9 col-lg-8 col-xs-12">
					<div class="content-wrap">
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
							<?php if ( has_post_thumbnail() && get_theme_mod( 'single_featured', 1 ) ) : ?>
								<figure class="entry-thumb">
									<a class="ci-lightbox" href="<?php echo esc_url( wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ); ?>">
										<?php the_post_thumbnail(); ?>
									</a>
								</figure>
							<?php endif; ?>

							<?php if ( get_theme_mod( 'single_date', 1 ) || get_theme_mod( 'single_categories', 1 ) ) : ?>
								<div class="entry-meta">
									<?php if ( get_theme_mod( 'single_date', 1 ) ) : ?>
										<time class="entry-time" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo get_the_date(); ?></time>
									<?php endif; ?>

									<?php if ( get_theme_mod( 'single_categories', 1 ) ) : ?>
										<span class="entry-categories">
											<?php the_category( ', ' ); ?>
										</span>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<h1 class="entry-title"><?php the_title(); ?></h1>

							<div class="entry-content">
								<?php the_content(); ?>
								<?php wp_link_pages(); ?>
							</div>
						</article>
					</div>

					<?php if ( get_theme_mod( 'single_social_sharing', 1 ) ) : ?>
						<div class="content-wrap-footer">
							<div class="row">
								<div class="col-md-8 col-xs-12">
									<?php get_template_part( 'part-social-sharing' ); ?>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( get_theme_mod( 'single_related', 1 ) ) {
						get_template_part( 'part-related' );
					} ?>

					<?php if ( get_theme_mod( 'single_comments', 1 ) ) {
						comments_template();
					} ?>
				</div>
			<?php endwhile; ?>

			<div class="col-xl-3 col-lg-4 col-xs-12">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>
</main>

<?php get_footer();
