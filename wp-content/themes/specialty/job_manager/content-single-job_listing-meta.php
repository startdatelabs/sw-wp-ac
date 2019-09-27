<?php
/**
 * Single view job meta box.
 *
 * Hooked into single_job_listing_start priority 20
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-single-job_listing-meta.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @since       1.14.0
 * @version     1.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

do_action( 'single_job_listing_meta_before' ); ?>

<ul class="job-listing-meta meta">
	<?php do_action( 'single_job_listing_meta_start' ); ?>

	<li class="date-posted"><?php the_job_publish_date(); ?></li>

	<?php if ( is_position_filled() ) : ?>
		<li class="position-filled"><?php esc_html_e( 'This position has been filled', 'specialty' ); ?></li>
	<?php elseif ( ! candidates_can_apply() && 'preview' !== $post->post_status ) : ?>
		<li class="listing-expired"><?php esc_html_e( 'Applications have closed', 'specialty' ); ?></li>
	<?php endif; ?>

	<?php do_action( 'single_job_listing_meta_end' ); ?>
</ul>

<?php do_action( 'single_job_listing_meta_after' );
