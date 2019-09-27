<?php get_header(); ?>

<?php get_template_part( 'part-hero', get_post_type() ); ?>

<main class="main main-elevated">
	<div class="container">
		<div class="row">
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="col-xl-9 col-lg-8 col-xs-12">
					<div class="content-wrap">
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
							<div class="entry-content">
								<?php the_content(); ?>
								<?php wp_link_pages(); ?>
							</div>

							<?php job_listing_meta_display(); ?>
						</article>
					</div>

					<div class="content-wrap-footer">
						<div class="row">
							<div class="col-md-8 col-xs-12">
								<?php get_template_part( 'part-social-sharing' ); ?>
							</div>

							<?php if ( get_theme_mod( 'theme_jobs_report_email' ) &&
								(
									( get_theme_mod( 'theme_jobs_report_email_logged_in', 1 ) && is_user_logged_in() )
									||
									! get_theme_mod( 'theme_jobs_report_email_logged_in', 1 )
								)
							) : ?>
								<div class="col-md-4 col-xs-12 text-right">
									<?php
										/* translators: %s is a job title. */
										$subject_text = __( 'Job Reported: %s', 'specialty' );
										$subject      = sprintf( $subject_text, get_the_title() );
										$subject      = apply_filters( 'specialty_wpjm_report_email_subject', $subject, $subject_text, get_the_ID() );
										$subject      = wp_kses( $subject, array() );

										/* translators: %1$s is new-line character. */
										$body_text = __( 'Job Listing URL: %2$s%1$sJob Listing Title: %3$s%1$s%1$sPlease provide the reason for reporting this job listing, to help us best understand the problem.%1$s', 'specialty' );
										$body      = sprintf( $body_text,
											'%0D%0A',
											get_the_permalink(),
											get_the_title()
										);
										$body      = apply_filters( 'specialty_wpjm_report_email_body', $body, $body_text, get_the_ID() );
										$body      = wp_kses( $body, specialty_get_allowed_tags() );

										$mailto = '';
										$mailto = add_query_arg( array(
											'subject' => $subject,
											'body'    => $body,
										), $mailto );
										$mailto = 'mailto:' . antispambot( get_theme_mod( 'theme_jobs_report_email' ) ) . $mailto;

										printf( '<a href="%s">%s</a>',
											esc_url( $mailto ),
											esc_html__( 'Report this listing', 'specialty' )
										);
									?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<?php comments_template(); ?>
				</div>
			<?php endwhile; ?>

			<div class="col-xl-3 col-lg-4 col-xs-12">
				<?php get_sidebar( get_post_type() ); ?>
			</div>
		</div>
	</div>
</main>

<?php get_footer();
