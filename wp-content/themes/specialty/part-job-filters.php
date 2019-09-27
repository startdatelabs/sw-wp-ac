<?php
	if ( ! class_exists( 'WP_Job_Manager' ) ) {
		return;
	}

	$keywords          = '';
	$location          = '';
	$selected_category = array();

	if ( ! empty( $_GET['search_keywords'] ) ) {
		$keywords = sanitize_text_field( $_GET['search_keywords'] );
	}
	if ( ! empty( $_GET['search_location'] ) ) {
		$location = sanitize_text_field( $_GET['search_location'] );
	}
	if ( ! empty( $_GET['search_categories'] ) ) {
		if ( is_array( $_GET['search_categories'] ) ) {
			$selected_category = absint( reset( $_GET['search_categories'] ) );
		} else {
			$selected_category = absint( $_GET['search_categories'] );
		}
		$_GET['search_categories'] = $selected_category;
	}

	if ( is_tax( 'job_listing_category' ) ) {
		$term = get_queried_object();

		$_GET['search_categories'] = $selected_category = array( $term->term_id );
	}
?>
<div class="form-filter">
	<div class="form-filter-header">
		<a href="#" class="form-filter-dismiss">&times;<span class="sr-only"> <?php esc_html_e( 'Dismiss filters', 'specialty' ); ?></span></a>
	</div>

	<?php
		$has_categories = true;

		$count = wp_count_terms( 'job_listing_category', array(
			'hide_empty' => 1, // Hide empty, as they are not displayed by the dropdown anyway.
		) );

		if ( is_wp_error( $count ) || 0 === intval( $count ) || is_tax( 'job_listing_category' ) ) {
			$has_categories = false;
		}

		$col_classes = array(
			'keywords' => 'col-lg-3 col-xs-12',
			'location' => 'col-lg-3 col-xs-12',
			'category' => 'col-lg-3 col-xs-12',
			'button'   => 'col-lg-3 col-xs-12',
		);

		if ( ! get_option( 'job_manager_enable_categories' ) || ! $has_categories ) {
			$col_classes = array(
				'keywords' => 'col-lg-4 col-xs-12',
				'location' => 'col-lg-4 col-xs-12',
				'category' => '',
				'button'   => 'col-lg-3 push-lg-1 col-xs-12',
			);
		}
	?>
	<div class="container">
		<div class="row">
			<div class="<?php echo esc_attr( $col_classes['keywords'] ); ?>">
				<label for="job-keywords" class="sr-only"><?php esc_html_e( 'Job Keywords', 'specialty' ); ?></label>
				<input type="text" id="job-keywords" name="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'specialty' ); ?>" value="<?php echo esc_attr( $keywords ); ?>">
			</div>
			<div class="<?php echo esc_attr( $col_classes['location'] ); ?>">
				<label for="job-location" class="sr-only"><?php esc_html_e( 'Job Location', 'specialty' ); ?></label>
				<input type="text" id="job-location" name="search_location" placeholder="<?php esc_attr_e( 'Location', 'specialty' ); ?>" value="<?php echo esc_attr( $location ); ?>">
			</div>
			<?php if ( get_option( 'job_manager_enable_categories' ) && $has_categories ) : ?>
				<div class="<?php echo esc_attr( $col_classes['category'] ); ?>">
					<label for="job-category" class="sr-only"><?php esc_html_e( 'Job Category', 'specialty' ); ?></label>
					<div class="ci-select">
						<?php
							$multiple = get_option( 'job_manager_enable_default_category_multiselect' );

							job_manager_dropdown_categories( array(
								'taxonomy'        => 'job_listing_category',
								'hierarchical'    => 1,
								'show_option_all' => $multiple ? false : esc_html__( 'Any category', 'specialty' ),
								'name'            => 'search_categories',
								'orderby'         => 'name',
								'selected'        => $selected_category,
								'multiple'        => $multiple,
							) );
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="<?php echo esc_attr( $col_classes['button'] ); ?>">
				<button class="btn btn-block btn-jobs-filter" type="submit"><?php esc_html_e( 'Search', 'specialty' ); ?></button>
			</div>
		</div>
	</div>
</div>
