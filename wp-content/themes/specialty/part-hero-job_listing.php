<?php
	if ( ! class_exists( 'WP_Job_Manager' ) ) {
		return;
	}

	$title = single_post_title( '', false );
	$title = apply_filters( 'specialty_hero_title', $title );
?>
<?php if ( ! empty( $title ) ) : ?>
	<div class="page-hero">
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<div class="page-hero-content">
						<h1 class="page-title"><?php echo wp_kses( $title, specialty_get_allowed_tags() ); ?></h1>

						<div class="page-hero-details">
							<?php if ( get_option( 'job_manager_enable_types' ) && wpjm_get_the_job_types() ) : ?>
								<?php
									$types = wpjm_get_the_job_types();
									foreach ( $types as $type ) {
										echo sprintf( '<span class="item-badge job-type-%1$s">%2$s</span>',
											esc_attr( sanitize_title( $type->slug ) ),
											esc_html( $type->name )
										);
									}
								?>
							<?php endif; ?>

							<?php if ( get_the_job_location() ) : ?>
								<span class="entry-location"><?php the_job_location(); ?></span>
							<?php endif; ?>

							<?php the_company_name( '<span class="entry-company">', '</span>' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif;
