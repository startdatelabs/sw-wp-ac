<?php get_header(); ?>

<?php get_template_part( 'part-hero', get_post_type() ); ?>

<main class="main main-elevated">
	<div class="container">
		<div class="row">
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="col-xl-9 col-lg-8 col-xs-12">
					<div class="content-wrap">
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<figure class="entry-thumb">
									<a class="ci-lightbox" href="<?php echo esc_url( wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ); ?>">
										<?php the_post_thumbnail(); ?>
									</a>
								</figure>
							<?php endif; ?>

							<div class="entry-content">
								<?php the_content(); ?>
								<?php wp_link_pages(); ?>
							</div>
						</article>
					</div>

					<div class="content-wrap-footer">
						<div class="row">
							<div class="col-md-8 col-xs-12">
								<?php get_template_part( 'part-social-sharing' ); ?>
							</div>
						</div>
					</div>

					<?php comments_template(); ?>
				</div>
			<?php endwhile; ?>

			<div class="col-xl-3 col-lg-4 col-xs-12">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>
</main>

<?php get_footer();
