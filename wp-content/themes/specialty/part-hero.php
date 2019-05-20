<?php
	$title    = '';
	$subtitle = '';

	if ( is_home() || is_singular( 'post' ) ) {
		$title = get_theme_mod( 'title_blog', esc_html__( 'From the blog', 'specialty' ) );
	} elseif ( is_page() ) {
		$title    = single_post_title( '', false );
		$subtitle = get_post_meta( get_queried_object_id(), 'specialty_subtitle', true );
	} elseif ( is_singular() ) {
		$title = single_post_title( '', false );
	} elseif ( is_archive() ) {
		$title = get_the_archive_title();
	} elseif ( is_search() ) {
		$title = get_theme_mod( 'title_search', esc_html__( 'Search results', 'specialty' ) );
	} elseif ( is_404() ) {
		$title = get_theme_mod( 'title_404', esc_html__( 'Page not found', 'specialty' ) );
	} else {
		$title = '';
	}

	$title = apply_filters( 'specialty_hero_title', $title );

	$layout_class = 'col-xs-12';
	if ( is_page_template( array( 'template-nosidebar.php', 'template-login.php' ) ) || is_404() ) {
		$layout_class = 'col-xl-10 offset-xl-1 col-lg-10 offset-lg-1 col-xs-12';
	}
?>
	<div class="page-hero">
		<?php if ( ! empty( $title ) && ! is_page_template( 'template-builder.php' ) ) : ?>
		<div class="container">
			<div class="row">
				<div class="<?php echo esc_attr( $layout_class ); ?>">
					<div class="page-hero-content">
						<h1 class="page-title"><?php echo wp_kses( $title, specialty_get_allowed_tags() ); ?></h1>

						<?php if ( ! empty( $subtitle ) ) : ?>
							<p class="page-subtitle">
								<?php echo wp_kses( $subtitle, specialty_get_allowed_tags( 'guide' ) ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( is_page_template( array( 'template-listing-jobs.php' ) ) ) {
			get_template_part( 'part-job-filters' );
		} ?>

	</div>
