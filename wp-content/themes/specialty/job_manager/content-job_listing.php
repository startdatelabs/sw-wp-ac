<?php
	global $post;

	$classes      = 'list-item';
	$eyebrow_text = '';

	if ( is_position_featured() ) {
		$classes      = 'list-item list-item-featured';
		$eyebrow_text = esc_html__( 'Featured', 'specialty' );
	} elseif ( is_position_filled() ) {
		$eyebrow_text = esc_html__( 'Filled', 'specialty' );
	} elseif ( in_array( $post->post_status, array( 'expired' ), true ) ) {
		$eyebrow_text = esc_html__( 'Expired', 'specialty' );
	}
?>
<li <?php job_listing_class( $classes ); ?> data-longitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-latitude="<?php echo esc_attr( $post->geolocation_long ); ?>">
	<?php if ( has_post_thumbnail() && get_theme_mod( 'theme_jobs_listing_company_logo_show' ) ) : ?>
		<figure class="entry-item-thumb">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'specialty_wpjm_company_logo' ); ?>
			</a>
		</figure>
	<?php endif; ?>

	<div class="list-item-main-info">
		<?php if ( ! empty( $eyebrow_text ) ) : ?>
			<span class="list-item-title-eyebrow"><?php echo esc_html( $eyebrow_text ); ?></span>
		<?php endif; ?>

		<p class="list-item-title">
			<a href="<?php the_job_permalink(); ?>"><?php the_title(); ?></a>
		</p>

		<div class="list-item-meta">
			<?php if ( get_option( 'job_manager_enable_types' ) && wpjm_get_the_job_types() ) : ?>
				<?php
					$types = wpjm_get_the_job_types();

					// Linking job type terms to the job_type GET parameter only works for pages with the "Job Listing"
					// template assigned. It won't work for pages with the [jobs] shortcode, term archives, or widgets.
					if ( is_page() && is_page_template( 'template-listing-jobs.php' ) ) {
						foreach ( $types as $type ) {
							echo sprintf( '<span class="list-item-tag item-badge job-type-%1$s"><a href="%2$s">%3$s</a></span>',
								esc_attr( sanitize_title( $type->slug ) ),
								esc_url( add_query_arg( array( 'job_type' => $type->slug ), get_permalink( get_queried_object_id() ) ) ),
								esc_html( $type->name )
							);
						}
					} else {
						foreach ( $types as $type ) {
							echo sprintf( '<span class="list-item-tag item-badge job-type-%1$s">%2$s</span>',
								esc_attr( sanitize_title( $type->slug ) ),
								esc_html( $type->name )
							);
						}
					}
				?>
			<?php endif; ?>

			<?php the_company_name( '<span class="list-item-company">', '</span> ' ); ?>
		</div>
	</div>

	<div class="list-item-secondary-info">
		<?php do_action( 'job_listing_meta_start' ); ?>

		<p class="list-item-location"><?php the_job_location( false ); ?></p>
		<time class="list-item-time" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php the_job_publish_date(); ?></time>

		<?php do_action( 'job_listing_meta_end' ); ?>
	</div>
</li>
