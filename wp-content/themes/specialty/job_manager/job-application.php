<?php if ( $apply = get_the_job_application_method() ) :
	wp_enqueue_script( 'wp-job-manager-job-application' );
	?>
	<div id="job_application_<?php the_ID(); ?>" class="job_application application">
		<?php do_action( 'job_application_start', $apply ); ?>
		
		<input type="button" class="application_button button btn btn-block btn-apply-content" value="<?php esc_attr_e( 'Apply for this job', 'specialty' ); ?>" />
		
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


<?php if ( $apply = get_the_job_application_method() ) : ?>
<a href="<?php echo esc_url( $apply->url ); ?>" class="application_button button btn btn-block"><?php esc_html_e( 'Apply for this job', 'specialty' ); ?></a>

<?php endif; ?>