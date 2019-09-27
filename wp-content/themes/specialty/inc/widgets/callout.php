<?php
if ( ! class_exists( 'CI_Widget_Callout' ) ) :
	class CI_Widget_Callout extends WP_Widget {

		protected $defaults = array(
			'title'       => '',
			'image_id'    => '',
			'text'        => '',
			'subtext'     => '',
			'button_text' => '',
			'button_url'  => '',
		);

		public function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Displays an image, callout text, and a button.', 'specialty' ) );
			$control_ops = array();
			parent::__construct( 'ci-callout', esc_html__( 'Theme - Callout', 'specialty' ), $widget_ops, $control_ops );
		}

		public function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title       = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			$image_id    = $instance['image_id'];
			$text        = $instance['text'];
			$subtext     = $instance['subtext'];
			$button_text = $instance['button_text'];
			$button_url  = $instance['button_url'];

			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			echo $before_widget;

			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}

			?><div class="callout-wrapper"><?php

			if ( $image_id ) {
				echo wp_get_attachment_image( $image_id, 'specialty_wpjm_company_logo', false, array( 'class' => 'callout-thumb' ) );
			}

			if ( $text ) {
				echo sprintf( '<p><strong>%s</strong></p>',
					wp_kses( $text, specialty_get_allowed_tags( 'guide' ) )
				);
			}

			if ( $subtext ) {
				echo sprintf( '<p class="text-secondary">%s</p>',
					wp_kses( $subtext, specialty_get_allowed_tags( 'guide' ) )
				);
			}

			if ( $button_text && $button_url ) {
				echo sprintf( '<a href="%s" class="btn btn-round btn-transparent">%s</a>',
					esc_url( $button_url ),
					esc_html( $button_text )
				);
			}

			?></div><?php

			echo $after_widget;
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array();

			$instance['title'] = sanitize_text_field( $new_instance['title'] );

			$instance['text']        = wp_kses( $new_instance['text'], specialty_get_allowed_tags( 'guide' ) );
			$instance['subtext']     = wp_kses( $new_instance['subtext'], specialty_get_allowed_tags( 'guide' ) );
			$instance['button_text'] = sanitize_text_field( $new_instance['button_text'] );
			$instance['button_url']  = esc_url_raw( $new_instance['button_url'] );
			$instance['image_id']    = intval( $new_instance['image_id'] );

			return $instance;
		}

		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title       = $instance['title'];
			$image_id    = $instance['image_id'];
			$text        = $instance['text'];
			$subtext     = $instance['subtext'];
			$button_text = $instance['button_text'];
			$button_url  = $instance['button_url'];
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'specialty' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat"/></p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'image_id' ) ); ?>"><?php esc_html_e( 'Image:', 'specialty' ); ?></label>
				<div class="ci-upload-preview">
					<div class="upload-preview">
						<?php if ( ! empty( $image_id ) ) : ?>
							<?php
								$image_url = wp_get_attachment_image_url( $image_id, 'specialty_featgal_small_thumb' );
								echo sprintf( '<img src="%s" /><a href="#" class="close media-modal-icon" title="%s"></a>',
									esc_url( $image_url ),
									esc_attr__( 'Remove image', 'specialty' )
								);
							?>
						<?php endif; ?>
					</div>
					<input type="hidden" class="ci-uploaded-id" name="<?php echo esc_attr( $this->get_field_name( 'image_id' ) ); ?>" value="<?php echo esc_attr( $image_id ); ?>" />
					<input id="<?php echo esc_attr( $this->get_field_id( 'image_id' ) ); ?>" type="button" class="button ci-media-button" value="<?php esc_attr_e( 'Select Image', 'specialty' ); ?>" />
				</div>
			</p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Text (accepts HTML):', 'specialty' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>" class="widefat"/></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'subtext' ) ); ?>"><?php esc_html_e( 'Sub-text (accepts HTML):', 'specialty' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'subtext' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtext' ) ); ?>" type="text" value="<?php echo esc_attr( $subtext ); ?>" class="widefat"/></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>"><?php esc_html_e( 'Button text:', 'specialty' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" type="text" value="<?php echo esc_attr( $button_text ); ?>" class="widefat"/></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'button_url' ) ); ?>"><?php esc_html_e( 'Button URL:', 'specialty' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'button_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_url' ) ); ?>" type="text" value="<?php echo esc_url( $button_url ); ?>" class="widefat"/></p>
			<?php
		}
	}


	register_widget( 'CI_Widget_Callout' );

endif;
