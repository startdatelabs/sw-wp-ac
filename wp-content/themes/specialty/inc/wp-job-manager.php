<?php
	if ( ! class_exists( 'WP_Job_Manager' ) ) {
		return;
	}

	add_action( 'after_setup_theme', 'specialty_wpjm_setup' );
	if ( ! function_exists( 'specialty_wpjm_setup' ) ) {
		function specialty_wpjm_setup() {
			add_theme_support( 'job-manager-templates' );
		}
	}

	add_action( 'init', 'specialty_wpjm_hooks' );
	if ( ! function_exists( 'specialty_wpjm_hooks' ) ) {
		function specialty_wpjm_hooks() {
			// Remove meta and company info from the main content. We'll get the templates directly where needed.
			remove_action( 'single_job_listing_start', 'job_listing_meta_display', 20 );
			remove_action( 'single_job_listing_start', 'job_listing_company_display', 30 );

		}
	}

	add_filter( 'job_manager_enhanced_select_enabled', 'specialty_wpjm_enable_enhanced_select' );
	if ( ! function_exists( 'specialty_wpjm_enable_enhanced_select' ) ) {
		function specialty_wpjm_enable_enhanced_select( $enhanced_select_used_on_page ) {
			if ( is_singular() && is_page_template( 'template-listing-jobs.php' ) ) {
				$enhanced_select_used_on_page = true;
			}

			return $enhanced_select_used_on_page;
		}
	}

	add_filter( 'the_job_location_map_link', 'specialty_wpjm_the_job_location_map_link', 10, 3 );
	if ( ! function_exists( 'specialty_wpjm_the_job_location_map_link' ) ) {
		function specialty_wpjm_the_job_location_map_link( $link, $location, $post ) {
			$link = str_replace( 'http://maps.google.com', 'https://maps.google.com', $link );
			return $link;
		}
	}

	add_filter( 'submit_job_form_fields', 'specialty_wpjm_add_extra_submit_fields' );
	if ( ! function_exists( 'specialty_wpjm_add_extra_submit_fields' ) ) {
		function specialty_wpjm_add_extra_submit_fields( $fields ) {
			$fields['company']['company_facebook'] = array(
				'label'       => esc_html__( 'Facebook URL', 'specialty' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'https://', 'specialty' ),
				'priority'    => 5,
			);
			$fields['company']['company_linkedin'] = array(
				'label'       => esc_html__( 'LinkedIn URL', 'specialty' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'https://', 'specialty' ),
				'priority'    => 5,
			);

			$fields['job']['job_salary'] = array(
				'label'       => esc_html__( 'Salary (no currency, commas or dots)', 'specialty' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 20000', 'specialty' ),
				'priority'    => 7,
			);

			return $fields;
		}
	}

	add_filter( 'job_manager_job_listing_data_fields', 'specialty_wpjm_admin_add_extra_fields' );
	if ( ! function_exists( 'specialty_wpjm_admin_add_extra_fields' ) ) {
		function specialty_wpjm_admin_add_extra_fields( $fields ) {
			$fields['_company_facebook'] = array(
				'label'       => esc_html__( 'Facebook URL', 'specialty' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'https://', 'specialty' ),
				'description' => '',
				'priority'    => 7,
			);
			$fields['_company_linkedin'] = array(
				'label'       => esc_html__( 'LinkedIn URL', 'specialty' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'https://', 'specialty' ),
				'description' => '',
				'priority'    => 7,
			);

			$fields['_job_salary'] = array(
				'label'       => esc_html__( 'Salary (no currency, commas or dots)', 'specialty' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'e.g. 20000', 'specialty' ),
				'description' => '',
				'priority'    => 7,
			);

			return $fields;
		}
	}

	if ( ! function_exists( 'specialty_wpjm_get_salary_ranges' ) ) {
		function specialty_wpjm_get_salary_ranges() {
			/*
			 * Ranges are in the format min-max (whole numbers only).
			 * Ranges are *inclusive* therefore the next min number should be 1 higher than the previous max.
			 */
			return apply_filters( 'specialty_wpjm_salary_ranges', array(
				'0-0'          => __( 'Unknown', 'specialty' ),
				'0-50000'      => __( 'Under $50,000', 'specialty' ),
				'50001-75000'  => __( '$50,000 - $75,000', 'specialty' ),
				'75001-100000' => __( '$75,001 - $100,000', 'specialty' ),
				'100001-0'     => __( 'Over $100,000', 'specialty' ),
			) );
		}
	}

	if ( ! function_exists( 'specialty_wpjb_sanitize_salary_range' ) ) {
		function specialty_wpjb_sanitize_salary_range( $range ) {
			preg_match( '/^\d+-\d+$/', $range, $matches );
			if ( isset( $matches[0] ) ) {
				return $matches[0];
			}

			return '';
		}
	}

	// Return number of found jobs in ajax json.
	add_filter( 'job_manager_get_listings_result', 'specialty_wpjb_filter_ajax_json', 10, 2 );
	if ( ! function_exists( 'specialty_wpjb_filter_ajax_json' ) ) {
		function specialty_wpjb_filter_ajax_json( $result, $jobs ) {
			$result['jobs_number']       = $jobs->have_posts() ? intval( $jobs->found_posts ) : 0;
			/* translators: %d is a number of jobs found. */
			$result['jobs_number_found'] = sprintf( _n( '%d job found', '%d jobs found', $jobs->found_posts, 'specialty' ), $jobs->found_posts );

			return $result;
		}
	}

	add_filter( 'job_manager_get_listings_args', 'specialty_wpjb_add_salary_range_filter_listing_arg' );
	if ( ! function_exists( 'specialty_wpjb_add_salary_range_filter_listing_arg' ) ) {
		function specialty_wpjb_add_salary_range_filter_listing_arg( $args ) {
			if ( ! empty( $_REQUEST['salary_range'] ) && is_array( $_REQUEST['salary_range'] ) ) {
				$tmp_ranges = array_map( 'specialty_wpjb_sanitize_salary_range', $_REQUEST['salary_range'] );
				$tmp_ranges = array_filter( $tmp_ranges );

				$args['salary_range'] = $tmp_ranges;
			}

			return $args;
		}
	}

	// Handle salary ranges.
	add_filter( 'job_manager_get_listings', 'specialty_wpjb_filter_by_salary_range', 10, 2 );
	if ( ! function_exists( 'specialty_wpjb_filter_by_salary_range' ) ) {
		function specialty_wpjb_filter_by_salary_range( $query_args, $args ) {

			if ( empty( $args['salary_range'] ) || ! is_array( $args['salary_range'] ) ) {
				return $query_args;
			}

			$tmp_ranges = array_map( 'specialty_wpjb_sanitize_salary_range', $args['salary_range'] );
			$tmp_ranges = array_filter( $tmp_ranges );

			$meta_query = array();

			$ranges = array();
			foreach ( $tmp_ranges as $range ) {
				$minmax   = explode( '-', $range );
				$minmax   = array_map( 'absint', $minmax );
				$ranges[] = array(
					'min' => absint( $minmax[0] ),
					'max' => absint( $minmax[1] ),
				);
			}

			foreach ( $ranges as $range ) {
				if ( $range['min'] > 0 && $range['max'] > 0 ) {
					$meta_query[] = array(
						'key'     => '_job_salary',
						'value'   => $range,
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC',
					);
				} elseif ( $range['min'] > 0 ) {
					$meta_query[] = array(
						'key'     => '_job_salary',
						'value'   => $range['min'],
						'compare' => '>=',
						'type'    => 'NUMERIC',
					);
				} elseif ( $range['max'] > 0 ) {
					$meta_query[] = array(
						'relation' => 'AND',
						array(
							'key'     => '_job_salary',
							'value'   => 0,
							'compare' => '>',
							'type'    => 'NUMERIC',
						),
						array(
							'key'     => '_job_salary',
							'value'   => $range['max'],
							'compare' => '<=',
							'type'    => 'NUMERIC',
						),
					);
				} else {
					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => '_job_salary',
							'value'   => 0,
							'compare' => '=',
							'type'    => 'NUMERIC',
						),
						array(
							'key'     => '_job_salary',
							'compare' => 'NOT EXISTS',
						),
					);
				}
			}

			if ( ! empty( $meta_query ) ) {
				$meta_query['relation']     = 'OR';
				$query_args['meta_query'][] = $meta_query;
			}

			return $query_args;
		}
	}


	add_action( 'pre_get_posts', 'specialty_wpjm_exclude_jobs_from_search' );
	if ( ! function_exists( 'specialty_wpjm_exclude_jobs_from_search' ) ) :
	function specialty_wpjm_exclude_jobs_from_search( $q ) {
		if ( $q->is_search ) {
			$pt = $q->get( 'post_type' );

			if ( ! empty( $pt ) ) {
				// Post type is not empty, therefore it's an explicit search we shouldn't mess with.
				return;
			}

			$post_types = get_post_types( array( 'exclude_from_search' => false ), 'names' );
			if ( isset( $post_types['job_listing'] ) ) {
				unset( $post_types['job_listing'] );

				$post_types = array_values( $post_types );
				$q->set( 'post_type', $post_types );
			}
		}
	}
	endif;
