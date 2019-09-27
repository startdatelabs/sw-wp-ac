<?php
/*
 * Template Name: Login
 */
?>
<?php get_header(); ?>

<?php get_template_part( 'part-hero', get_post_type() ); ?>

<main class="main main-elevated">
	<div class="container">
		<div class="row">
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="col-xl-10 offset-xl-1 col-lg-10 offset-lg-1 col-xs-12">
					<div class="content-wrap">
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<figure class="entry-thumb">
									<a class="ci-lightbox" href="<?php echo esc_url( wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ); ?>">
										<?php the_post_thumbnail( 'specialty_fullwidth_narrow' ); ?>
									</a>
								</figure>
							<?php endif; ?>

							<div class="entry-content">
								<?php the_content(); ?>

								<?php
									$args = array();
									if ( ! empty( $_GET['redirect_to'] ) ) {
										$args['redirect'] = $_GET['redirect_to'];
									}
									wp_login_form( $args );
								?>
							</div>
						</article>
					</div>
				</div>
			<?php endwhile; ?>
		</div>
	</div>
</main>

<?php get_footer(); ?>
