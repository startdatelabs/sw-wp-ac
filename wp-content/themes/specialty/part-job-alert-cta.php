<?php if ( class_exists( 'WP_Job_Manager' ) && get_option( 'job_manager_alerts_page_id' ) && get_theme_mod( 'theme_jobs_listing_create_alert', 1 ) ) : ?>
	<?php $alert_link = get_permalink( get_option( 'job_manager_alerts_page_id' ) ); ?>
	<?php if ( $alert_link ) : ?>
		<?php $alert_link = add_query_arg( 'action', 'add_alert', $alert_link ); ?>
		<div class="list-item list-item-callout">
			<div class="list-item-main-info">
				<p class="list-item-title"><?php esc_html_e( 'Create a job alert', 'specialty' ); ?></p>

				<div class="list-item-meta">
					<span class="list-item-company"><?php esc_html_e( 'Get new jobs like these by email', 'specialty' ); ?></span>
				</div>
			</div>

			<div class="list-item-secondary-info">
				<a href="<?php echo esc_url( $alert_link ); ?>" class="btn btn-round btn-white btn-transparent"><?php esc_html_e( 'Activate Alert', 'specialty' ); ?></a>
			</div>
		</div>
	<?php endif; ?>
<?php endif;
