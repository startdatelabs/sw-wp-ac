<?php get_header(); ?>

<form action="" class="listing-filters-form">

	<?php get_template_part( 'part-hero-large' ); ?>

	<main class="main">
		<div class="container">
			<div class="row">
				<?php if ( class_exists( 'WP_Job_Manager' ) ) : ?>
					<?php
						global $wp_query;
						$content_classes = '';
						$sidebar_classes = '';

						$sidebar         = 'full';
						$content_classes = 'col-xs-12';
						$sidebar_classes = 'col-xl-3 col-lg-4 col-xs-12';

						wp_enqueue_script( 'wp-job-manager-ajax-filters' );
					?>
					<div class="<?php echo esc_attr( $content_classes ); ?>">
						<?php
							ob_start();

							$per_page          = get_option( 'posts_per_page' );
							$show_more         = true;
							$show_pagination   = false;
							$orderby           = 'featured';
							$order             = 'DESC';
							$keywords          = '';
							$location          = '';
							$selected_category = '';
							$selected_types    = array();
							$salary_ranges     = array();

							if ( ! empty( $_GET['search_keywords'] ) ) {
								$keywords = sanitize_text_field( $_GET['search_keywords'] );
							}
							if ( ! empty( $_GET['search_location'] ) ) {
								$location = sanitize_text_field( $_GET['search_location'] );
							}
							if ( ! empty( $_GET['search_category'] ) ) {
								$selected_category = absint( $_GET['search_category'] );
							}

							if ( is_tax( 'job_listing_type' ) ) {
								$term = get_queried_object();

								$_GET['job_type'] = $selected_types = array( $term->slug );
							}
							if ( ! empty( $_GET['job_type'] ) ) {
								$selected_types = $_GET['job_type'];
								if ( ! is_array( $selected_types ) ) {
									$selected_types = array( $selected_types );
								}
								$selected_types = array_map( 'sanitize_title', $selected_types );
							}


							if ( ! empty( $_GET['salary_range'] ) ) {
								$salary_ranges = $_GET['salary_range'];
								if ( ! is_array( $salary_ranges ) ) {
									$salary_ranges = array( $salary_ranges );
								}
								$salary_ranges = array_map( 'specialty_wpjb_sanitize_salary_range', $salary_ranges );
							}

							if ( have_posts() ) : ?>
								<?php
									$title = sprintf( '<h3 class="section-title"><span class="jobs-found-no">%s</span></h3>',
										esc_html( sprintf( _n( '%d job found', '%d jobs found', $wp_query->found_posts, 'specialty' ), $wp_query->found_posts ) )
									);
								?>

								<?php if ( 'full' === $sidebar ) : ?>
									<div class="section-title-wrap">
										<?php echo wp_kses_post( $title ); ?>

										<span class="section-title-compliment">
											<a href="#" class="sidebar-wrap-trigger">
												<i class="fa fa-navicon"></i> <?php esc_html_e( 'Filters', 'specialty' ); ?>
											</a>
										</span>
									</div>
								<?php else : ?>
									<?php echo wp_kses_post( $title ); ?>
								<?php endif; ?>


								<?php get_job_manager_template( 'job-listings-start.php' ); ?>

								<?php while ( have_posts() ) : the_post(); ?>
									<?php get_job_manager_template_part( 'content', 'job_listing' ); ?>
								<?php endwhile; ?>

								<?php get_job_manager_template( 'job-listings-end.php' ); ?>


								<?php if ( $wp_query->found_posts > $per_page && $show_more ) : ?>
									<?php // This whole if block was originally right after get_job_manager_template( 'job-listings-end.php' ); ?>

									<?php if ( $show_pagination ) : ?>
										<?php echo get_job_listing_pagination( $wp_query->max_num_pages ); ?>
									<?php else : ?>
										<div class="list-item-secondary-wrap">
											<button type="button" class="btn btn-round btn-white btn-transparent btn-load-jobs">
												<?php esc_html_e( 'Load More Jobs', 'specialty' ); ?>
											</button>
										</div>
									<?php endif; ?>

								<?php endif; ?>
							<?php else :
								do_action( 'job_manager_output_jobs_no_results' );
							endif;

							$data_attributes_string = '';
							$data_attributes        = array(
								'location'        => $location,
								'keywords'        => $keywords,
								'show_pagination' => $show_pagination ? 'true' : 'false',
								'per_page'        => $per_page,
								'orderby'         => $orderby,
								'order'           => $order,
								'categories'      => $selected_category,
							);

							foreach ( $data_attributes as $key => $value ) {
								$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
							}

							$job_listings_output = apply_filters( 'job_manager_job_listings_output', ob_get_clean() );

							echo '<div class="job-listing-container" ' . $data_attributes_string . '>' . $job_listings_output . '</div>';
						?>
					</div>

					<div class="<?php echo esc_attr( $sidebar_classes ); ?>">
						<div class="sidebar-wrap <?php echo esc_attr( 'full' === $sidebar ? 'sidebar-fixed-default' : '' ); ?>">
							<div class="sidebar-wrap-header">
								<a href="#" class="sidebar-wrap-dismiss">&times;</a>
							</div>

							<div class="sidebar">

								<?php $job_types = get_job_listing_types(); ?>
								<?php if ( ! empty( $job_types ) && ! is_wp_error( $job_types ) && ! is_tax( 'job_listing_type' ) ) : ?>
									<div class="widget widget_ci-filters-widget">
										<h3 class="widget-title"><?php esc_html_e( 'Job Type', 'specialty' ); ?></h3>
										<ul class="item-filters-array">
											<?php foreach ( $job_types as $job_type ) : ?>
												<?php
													// WP Job Manager does it this way instead of utilizing the slug. We do the same for consistency.
													$class = 'job-type-' . sanitize_title( $job_type->name );
												?>
												<li class="item-filter">
													<input type="checkbox" id="job-type-<?php echo esc_attr( $job_type->term_id ); ?>" class="checkbox-filter" name="job_type[]" value="<?php echo esc_attr( $job_type->slug ); ?>" <?php checked( 1, in_array( $job_type->slug, $selected_types, true ) ); ?>>
													<label class="checkbox-filter-label" for="job-type-<?php echo esc_attr( $job_type->term_id ); ?>">
														<span class="item-filter-tag item-filter-tag-badge">
															<?php echo esc_html( $job_type->name ); ?>

															<span class="item-filter-tag-bg <?php echo esc_attr( $class ); ?>"></span>
														</span>
													</label>
												</li>
											<?php endforeach; ?>

										</ul>
									</div>
								<?php endif; ?>

								<div class="widget widget_ci-filters-widget">
									<h3 class="widget-title"><?php esc_html_e( 'Compensation', 'specialty' ); ?></h3>
									<ul class="item-filters-array">
										<?php $ranges = specialty_wpjm_get_salary_ranges(); ?>
										<?php foreach ( $ranges as $range => $text ) : ?>
											<li class="item-filter">
												<input type="checkbox" id="filter-salary-<?php echo esc_attr( $range ); ?>" class="checkbox-filter" name="salary_range[]" value="<?php echo esc_attr( $range ); ?>" <?php checked( 1, in_array( $range, $salary_ranges, true ) ); ?>>
												<label class="checkbox-filter-label" for="filter-salary-<?php echo esc_attr( $range ); ?>">
													<span class="item-filter-tag"><?php echo esc_html( $text ); ?></span>
												</label>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>

								<?php dynamic_sidebar( 'jobs' ); ?>
							</div>
						</div>
					</div>
				<?php endif; // class_exists 'WP_Job_Manager' ?>
			</div>
		</div>
	</main>

	<div class="mobile-triggers">
		<a href="#" class="mobile-trigger form-filter-trigger">
			<i class="fa fa-search"></i> <?php esc_html_e( 'Search', 'specialty' ); ?>
		</a>

		<a href="#" class="mobile-trigger sidebar-wrap-trigger">
			<i class="fa fa-navicon"></i> <?php esc_html_e( 'Filters', 'specialty' ); ?>
		</a>
	</div>

</form>

<?php get_footer();
