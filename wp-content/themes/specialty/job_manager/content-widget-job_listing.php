<?php
/**
 * Single job listing widget content.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-widget-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @version     1.31.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$eyebrow_text = '';

if ( is_position_featured() ) {
	$classes      = 'list-item list-item-featured';
	$eyebrow_text = esc_html__( 'Featured', 'specialty' );
} elseif ( is_position_filled() ) {
	$eyebrow_text = esc_html__( 'Filled', 'specialty' );
} elseif ( in_array( get_post_status(), array( 'expired' ), true ) ) {
	$eyebrow_text = esc_html__( 'Expired', 'specialty' );
}

?>
<li <?php job_listing_class( 'list-item list-item-sm' ); ?>>
	<div class="list-item-main-info">
		<?php if ( ! empty( $eyebrow_text ) ) : ?>
			<span class="list-item-title-eyebrow"><?php echo esc_html( $eyebrow_text ); ?></span>
		<?php endif; ?>

		<p class="list-item-title">
			<a href="<?php the_job_permalink(); ?>"><?php the_title(); ?></a>
		</p>
		<?php if ( get_option( 'job_manager_enable_types' ) ) : ?>
			<?php foreach ( wpjm_get_the_job_types() as $job_type ) : ?>
				<span class="list-item-tag item-badge job-type-<?php echo esc_attr( $job_type->slug ); ?>"><?php echo esc_html( $job_type->name ); ?></span>
			<?php endforeach; ?>
		<?php endif; ?>
		<span class="list-item-company"><?php the_company_name(); ?></span>
	</div>

	<div class="list-item-secondary-info">
		<p class="list-item-location"><?php the_job_location( false ); ?></p>
		<time class="list-item-time" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php the_job_publish_date(); ?></time>
	</div>
</li>
