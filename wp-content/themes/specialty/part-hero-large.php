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

	if ( is_singular() ) {
		$alt_title = get_post_meta( get_queried_object_id(), 'specialty_title', true );
		if ( ! empty( $alt_title ) ) {
			$title = $alt_title;
		}
	}

	$title = apply_filters( 'specialty_hero_title', $title );
?>
<?php if ( ! empty( $title ) ) : ?>
	<div class="page-hero page-hero-lg">
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
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

		<?php if ( is_post_type_archive( 'job_listing' ) || is_tax( array( 'job_listing_category', 'job_listing_type' ) ) || is_page_template( array( 'template-listing-jobs.php' ) ) ) {
			get_template_part( 'part-job-filters' );
		} ?>
	</div>
<?php endif;
