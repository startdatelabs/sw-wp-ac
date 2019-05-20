<div class="sidebar">

	<aside class="widget widget_ci-apply-button-widget">
		<?php if ( $apply = get_the_job_application_method() ) : ?>
<a href="<?php echo esc_url( $apply->url ); ?>" target = "blank" class="application_button button btn btn-block"><?php esc_html_e( 'Apply for this job', 'specialty' ); ?></a>

<?php endif; ?>
	</aside>

	<?php if ( get_the_company_name() ) : ?>
		<aside class="widget widget_ci-company-info-widget">
			<h3 class="widget-title"><?php esc_html_e( 'Company Information', 'specialty' ); ?></h3>
			<div class="card-info">
				<div class="card-info-media">
					<figure class="card-info-thumb">
						<?php the_company_logo( 'specialty_wpjm_company_logo' ); ?>
					</figure>
					<div class="card-info-details">
						<?php the_company_name( '<p class="card-info-title">', '</p>' ); ?>

						<?php if ( get_the_company_website() ) : ?>
							<p class="card-info-link">
								<a href="<?php echo esc_url( get_the_company_website() ); ?>"><?php echo esc_html( get_the_company_website() ); ?></a>
							</p>
						<?php endif; ?>

						<div class="card-info-socials">
							<?php $twitter = get_the_company_twitter(); ?>
							<?php if ( $twitter ) : ?>
								<?php $url = sprintf( 'https://twitter.com/%s', $twitter ); ?>
								<a href="<?php echo esc_url( $url ); ?>" target="_blank">
									<i class="fa fa-twitter"></i><span class="sr-only"><?php esc_html_e( 'Twitter', 'specialty' ); ?></span>
								</a>
							<?php endif; ?>

							<?php $post = get_post(); ?>
							<?php if ( ! empty( $post->_company_facebook ) ) : ?>
								<a href="<?php echo esc_url( $post->_company_facebook ); ?>" target="_blank">
									<i class="fa fa-facebook"></i><span class="sr-only"><?php esc_html_e( 'Facebook', 'specialty' ); ?></span>
								</a>
							<?php endif; ?>

							<?php if ( ! empty( $post->_company_linkedin ) ) : ?>
								<a href="<?php echo esc_url( $post->_company_linkedin ); ?>" target="_blank">
									<i class="fa fa-linkedin"></i><span class="sr-only"><?php esc_html_e( 'LinkedIn', 'specialty' ); ?></span>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php the_company_tagline( '<div class="card-info-description"><p>', '</p></div>' ); ?>

				<?php the_company_video(); ?>
			</div>
		</aside>
	<?php endif; ?>

	<?php dynamic_sidebar( 'jobs' ); ?>
</div>
