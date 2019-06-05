<?php if ( $apply = get_the_job_application_method() ) : ?>
	<?php if ( 'url' === $apply->type ) : ?>
		<div class="job_application application">
			<a href="<?php echo esc_url( $apply->url ); ?>" class="application_button button" target="_blank" rel="nofollow">Apply Now</a>
		</div>
	<?php else : ?>
		<?php wp_enqueue_script( 'wp-job-manager-job-application' ); ?>
		<div class="job_application application">
			<?php do_action( 'job_application_start', $apply ); ?>
		
			<input type="button" class="application_button button" value="<?php _e( 'Apply for job', 'wp-job-manager' ); ?>" />
		
			<div class="application_details">
				<?php
				/**
				 * job_manager_application_details_email or job_manager_application_details_url hook
				 */
				do_action( 'job_manager_application_details_' . $apply->type, $apply );
				?>
			</div>
			<?php do_action( 'job_application_end', $apply ); ?>
		</div>
	<?php endif; ?>
<?php endif; ?>	