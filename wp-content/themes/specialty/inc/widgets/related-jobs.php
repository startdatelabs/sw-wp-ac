<?php
if ( ! class_exists( 'CI_Widget_Related_Jobs' ) ) :
	class CI_Widget_Related_Jobs extends WP_Widget {

		protected $defaults = array(
			'title' => '',
			'count' => 3,
		);

		public function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Displays related jobs. Only works on single Job pages. Requires Job Categories to be enabled.', 'specialty' ) );
			$control_ops = array();
			parent::__construct( 'ci-related-jobs', esc_html__( 'Theme - Related Jobs', 'specialty' ), $widget_ops, $control_ops );
		}

		public function widget( $args, $instance ) {

			if ( ! class_exists( 'WP_Job_Manager' ) ) {
				return;
			}

			if ( ! is_singular( 'job_listing' ) ) {
				return;
			}

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title   = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			$count   = $instance['count'];
			$related = self::get_related_jobs( get_the_ID(), $count );

			if ( ! empty( $related ) && is_object( $related ) && $related->have_posts() ) {
				echo $args['before_widget'];

				if ( $title ) {
					echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
				}

				?>
				<ul class="job_listings">
					<?php while ( $related->have_posts() ) : $related->the_post(); ?>
						<?php get_job_manager_template_part( 'content-widget', 'job_listing' ); ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</ul>
				<?php

				echo $args['after_widget'];
			}
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array();

			$instance['title'] = sanitize_text_field( $new_instance['title'] );
			$instance['count'] = absint( $new_instance['count'] );

			return $instance;
		}

		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title = $instance['title'];
			$count = $instance['count'];
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'specialty' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat"/></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number of jobs:', 'specialty' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>" class="widefat"/></p>
			<?php
		}

		public static function get_related_jobs( $post_id, $count ) {
			$post_id   = absint( $post_id );
			$count     = absint( $count );
			$post      = get_post( $post_id );
			$term_list = array();

			$taxonomies = get_object_taxonomies( $post, 'names' );
			$taxonomy   = 'job_listing_category';

			if ( ! in_array( $taxonomy, $taxonomies, true ) ) {
				return false;
			}

			$terms = get_the_terms( $post_id, $taxonomy );
			if ( is_array( $terms ) && count( $terms ) > 0 ) {
				$term_list = wp_list_pluck( $terms, 'slug' );
				$term_list = array_values( $term_list );
			}

			if ( empty( $term_list ) ) {
				return false;
			}

			// WPJM wrongly reconstructs the cached query and causes problems. Disable caching.
			add_filter( 'get_job_listings_cache_results', '__return_false' );

			$related = get_job_listings( array(
				'posts_per_page'    => $count + 1, // One more, because we'll need to remove the current job if it's in the results.
				'orderby'           => 'rand',
				'search_categories' => $term_list,
				'fields'            => 'ids',
			) );

			// WPJM wrongly reconstructs the cached query and causes problems. Re-enable caching.
			remove_filter( 'get_job_listings_cache_results', '__return_false' );

			if ( empty( $related->posts ) ) {
				return false;
			}

			$related_ids = $related->posts;
			unset( $related );

			$found = array_search( $post_id, $related_ids, true );
			if ( false !== $found ) {
				unset( $related_ids[ $found ] );
			}

			$related_ids = array_slice( $related_ids, 0, $count );

			$related = new WP_Query( apply_filters( 'specialty_get_related_jobs_query_args', array(
				'post_type'           => 'any',
				'post__in'            => $related_ids,
				'posts_per_page'      => $count,
				'ignore_sticky_posts' => true,
				'orderby'             => 'post__in',
			) ) );

			return $related;
		}
	}


	register_widget( 'CI_Widget_Related_Jobs' );

endif;
