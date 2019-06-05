<?php
require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/sanitization.php';
require get_template_directory() . '/inc/functions.php';
require get_template_directory() . '/inc/helpers-post-meta.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/customizer-styles.php';
require get_template_directory() . '/inc/wp-job-manager.php';
require get_template_directory() . '/inc/custom-fields-page.php';

/**
 * Common theme features.
 */
require_once get_theme_file_path( '/common/common.php' );

add_action( 'after_setup_theme', 'specialty_content_width', 0 );
function specialty_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'specialty_content_width', 750 );
}

add_action( 'after_setup_theme', 'specialty_setup' );
if ( ! function_exists( 'specialty_setup' ) ) :
	function specialty_setup() {

		if ( ! defined( 'SPECIALTY_NAME' ) ) {
			define( 'SPECIALTY_NAME', 'specialty' );
		}
		if ( ! defined( 'SPECIALTY_WHITELABEL' ) ) {
			// Set the following to true, if you want to remove any user-facing CSSIgniter traces.
			define( 'SPECIALTY_WHITELABEL', false );
		}

		load_theme_textdomain( 'specialty', get_template_directory() . '/languages' );

		/*
		 * Theme supports.
		 */
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		add_theme_support( 'custom-logo', array(
			'height'      => 30,
			'width'       => 185,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		add_theme_support( 'custom-background' );

		/*
		 * Image sizes.
		 */
		set_post_thumbnail_size( 850, 450, true );
		add_image_size( 'specialty_entry_item', 264, 140, true );
		add_image_size( 'specialty_fullwidth', 1140, 550, true );
		add_image_size( 'specialty_fullwidth_narrow', 950, 450, true );
		add_image_size( 'specialty_wpjm_company_logo', 200, 0, false );
		add_image_size( 'specialty_hero', 1920, 500, true );

		/*
		 * Navigation menus.
		 */
		register_nav_menus( array(
			'main_menu' => esc_html__( 'Main Menu', 'specialty' ),
		) );


		/*
		 * Default hooks
		 */
		// Prints the inline JS scripts that are registered for printing, and removes them from the queue.
		add_action( 'admin_footer', 'specialty_print_inline_js' );
		add_action( 'wp_footer', 'specialty_print_inline_js' );

		// Handle the dismissible sample content notice.
		add_action( 'admin_notices', 'specialty_admin_notice_sample_content' );
		add_action( 'wp_ajax_specialty_dismiss_sample_content', 'specialty_ajax_dismiss_sample_content' );

		// Wraps post counts in span.ci-count
		// Needed for the default widgets, however more appropriate filters don't exist.
		add_filter( 'get_archives_link', 'specialty_wrap_archive_widget_post_counts_in_span', 10, 2 );
		add_filter( 'wp_list_categories', 'specialty_wrap_category_widget_post_counts_in_span', 10, 2 );
	}
endif;



add_action( 'wp_enqueue_scripts', 'specialty_enqueue_scripts' );
function specialty_enqueue_scripts() {

	/*
	 * Styles
	 */
	$theme = wp_get_theme();

	$font_url = '';
	/* translators: If there are characters in your language that are not supported by Lato, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Lato font: on or off', 'specialty' ) ) {
		$font_url = add_query_arg( 'family', 'Lato:300,400,400i,700', '//fonts.googleapis.com/css' );
	}
	wp_register_style( 'specialty-google-font', esc_url( $font_url ) );

	wp_register_style( 'specialty-base', get_template_directory_uri() . '/css/base.css', array(), $theme->get( 'Version' ) );
	wp_register_style( 'mmenu', get_template_directory_uri() . '/css/mmenu.css', array(), '5.5.3' );
	wp_register_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome.css', array(), '4.7.0' );
	wp_register_style( 'magnific-popup', get_template_directory_uri() . '/css/magnific.css', array(), '1.0.0' );
	wp_register_style( 'jquery-mCustomScrollbar', get_template_directory_uri() . '/css/jquery.mCustomScrollbar.min.css', array(), '3.1.13' );

	wp_register_style( 'specialty-dependencies', false, array(
		'specialty-google-font',
		'specialty-base',
		'specialty-common',
		'mmenu',
		'font-awesome',
		'magnific-popup',
		'jquery-mCustomScrollbar',
	), $theme->get( 'Version' ) );

	if ( is_child_theme() ) {
		wp_enqueue_style( 'specialty-style-parent', get_template_directory_uri() . '/style.css', array(
			'specialty-dependencies',
		), $theme->get( 'Version' ) );
	}

	wp_enqueue_style( 'specialty-style', get_stylesheet_uri(), array(
		'specialty-dependencies',
	), $theme->get( 'Version' ) );

	wp_add_inline_style( 'specialty-style', specialty_get_hero_styles() );

	wp_add_inline_style( 'specialty-style', specialty_get_customizer_css() );


	/*
	 * Scripts
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}


	wp_register_script( 'mmenu', get_template_directory_uri() . '/js/jquery.mmenu.min.all.js', array( 'jquery' ), '5.5.3', true );
	wp_register_script( 'fitVids', get_template_directory_uri() . '/js/jquery.fitvids.js', array( 'jquery' ), '1.1', true );
	wp_register_script( 'magnific-popup', get_template_directory_uri() . '/js/jquery.magnific-popup.js', array( 'jquery' ), '1.0.0', true );
	wp_register_script( 'match-height', get_template_directory_uri() . '/js/jquery.matchHeight.js', array( 'jquery' ), '0.7.0', true );
	wp_register_script( 'jquery-mCustomScrollbar', get_template_directory_uri() . '/js/jquery.mCustomScrollbar.concat.min.js', array( 'jquery' ), '3.1.13', true );

	/*
	 * Enqueue
	 */
	wp_enqueue_script( 'specialty-front-scripts', get_template_directory_uri() . '/js/scripts.js', array(
		'jquery',
		'mmenu',
		'fitVids',
		'magnific-popup',
		'match-height',
		'jquery-mCustomScrollbar',
	), $theme->get( 'Version' ), true );

}

add_action( 'admin_enqueue_scripts', 'specialty_admin_enqueue_scripts' );
function specialty_admin_enqueue_scripts( $hook ) {
	$theme = wp_get_theme();

	/*
	 * Styles
	 */
	wp_register_style( 'alpha-color-picker', get_template_directory_uri() . '/css/admin/alpha-color-picker.css', array(
		'wp-color-picker',
	), '1.0.0' );
	wp_register_style( 'alpha-color-picker-customizer', get_template_directory_uri() . '/inc/customizer-controls/alpha-color-picker/alpha-color-picker.css', array(
		'wp-color-picker',
	), '1.0.0' );

	wp_register_style( 'specialty-post-edit', false, array(
		'alpha-color-picker',
	), $theme->get( 'Version' ) );


	/*
	 * Scripts
	 */
	wp_register_script( 'alpha-color-picker', get_template_directory_uri() . '/js/admin/alpha-color-picker.js', array(
		'jquery',
		'wp-color-picker',
	), '1.0.0', true );
	wp_register_script( 'alpha-color-picker-customizer', get_template_directory_uri() . '/inc/customizer-controls/alpha-color-picker/alpha-color-picker.js', array(
		'jquery',
		'wp-color-picker',
	), '1.0.0', true );

	wp_register_script( 'specialty-post-edit', get_template_directory_uri() . '/js/admin/post-edit.js', array(
		'jquery',
		'alpha-color-picker',
	), $theme->get( 'Version' ), true );


	/*
	 * Enqueue
	 */
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_media();
		wp_enqueue_style( 'specialty-post-meta' );
		wp_enqueue_script( 'specialty-post-meta' );

		wp_enqueue_style( 'specialty-post-edit' );
		wp_enqueue_script( 'specialty-post-edit' );

		wp_enqueue_style( 'wp-color-picker' );
	}

	if ( in_array( $hook, array( 'widgets.php', 'customize.php' ), true ) ) {
		wp_enqueue_media();
		wp_enqueue_style( 'specialty-post-meta' );
		wp_enqueue_script( 'specialty-post-meta' );

		wp_enqueue_style( 'alpha-color-picker-customizer' );
		wp_enqueue_script( 'alpha-color-picker-customizer' );
	}

}


add_action( 'widgets_init', 'specialty_widgets_init' );
function specialty_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html_x( 'Blog', 'widget area', 'specialty' ),
		'id'            => 'blog',
		'description'   => esc_html__( 'This sidebar appears on your blog posts.', 'specialty' ),
		'before_widget' => '<aside id="%1$s" class="widget group %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html_x( 'Pages', 'widget area', 'specialty' ),
		'id'            => 'page',
		'description'   => esc_html__( "This sidebar appears on the site's static pages.", 'specialty' ),
		'before_widget' => '<aside id="%1$s" class="widget group %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html_x( 'Jobs', 'widget area', 'specialty' ),
		'id'            => 'jobs',
		'description'   => esc_html__( 'This sidebar appears on job-related pages. Requires the WP Job Manager plugin.', 'specialty' ),
		'before_widget' => '<aside id="%1$s" class="widget group %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html_x( 'Footer - Column 1', 'widget area', 'specialty' ),
		'id'            => 'footer-1',
		'description'   => esc_html__( 'First column on footer.', 'specialty' ),
		'before_widget' => '<aside id="%1$s" class="widget group %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html_x( 'Footer - Column 2', 'widget area', 'specialty' ),
		'id'            => 'footer-2',
		'description'   => esc_html__( 'Second column on footer.', 'specialty' ),
		'before_widget' => '<aside id="%1$s" class="widget group %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html_x( 'Footer - Column 3', 'widget area', 'specialty' ),
		'id'            => 'footer-3',
		'description'   => esc_html__( 'Third column on footer.', 'specialty' ),
		'before_widget' => '<aside id="%1$s" class="widget group %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html_x( 'Footer - Column 4', 'widget area', 'specialty' ),
		'id'            => 'footer-4',
		'description'   => esc_html__( 'Fourth column on footer.', 'specialty' ),
		'before_widget' => '<aside id="%1$s" class="widget group %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}


add_action( 'widgets_init', 'specialty_load_widgets' );
function specialty_load_widgets() {
	require get_template_directory() . '/inc/widgets/socials.php';
	require get_template_directory() . '/inc/widgets/callout.php';
	require get_template_directory() . '/inc/widgets/related-jobs.php';
}


add_action( 'wp_head', 'specialty_pingback_header' );
if ( ! function_exists( 'specialty_pingback_header' ) ) :
	/**
	 * Add a pingback url auto-discovery header for singularly identifiable articles.
	 */
	function specialty_pingback_header() {
		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
		}
	}
endif;


add_filter( 'excerpt_length', 'specialty_excerpt_length' );
if ( ! function_exists( 'specialty_excerpt_length' ) ) :
	function specialty_excerpt_length( $length ) {
		return get_theme_mod( 'excerpt_length', 55 );
	}
endif;


add_filter( 'the_content', 'specialty_lightbox_rel', 12 );
add_filter( 'get_comment_text', 'specialty_lightbox_rel' );
if ( ! function_exists( 'specialty_lightbox_rel' ) ) :
	function specialty_lightbox_rel( $content ) {
		global $post;
		$pattern     = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>(.*?)<\/a>/i";
		$replacement = '<a$1href=$2$3.$4$5 data-lightbox="gal[' . $post->ID . ']"$6>$7</a>';
		$content     = preg_replace( $pattern, $replacement, $content );

		return $content;
	}
endif;

if ( ! function_exists( 'specialty_get_social_networks' ) ) :
	function specialty_get_social_networks() {
		return apply_filters( 'specialty_social_networks', array(
			array(
				'name'  => 'facebook',
				'label' => esc_html__( 'Facebook', 'specialty' ),
				'icon'  => 'fa-facebook',
			),
			array(
				'name'  => 'twitter',
				'label' => esc_html__( 'Twitter', 'specialty' ),
				'icon'  => 'fa-twitter',
			),
			array(
				'name'  => 'pinterest',
				'label' => esc_html__( 'Pinterest', 'specialty' ),
				'icon'  => 'fa-pinterest',
			),
			array(
				'name'  => 'instagram',
				'label' => esc_html__( 'Instagram', 'specialty' ),
				'icon'  => 'fa-instagram',
			),
			array(
				'name'  => 'gplus',
				'label' => esc_html__( 'Google Plus', 'specialty' ),
				'icon'  => 'fa-google-plus',
			),
			array(
				'name'  => 'linkedin',
				'label' => esc_html__( 'LinkedIn', 'specialty' ),
				'icon'  => 'fa-linkedin',
			),
			array(
				'name'  => 'tumblr',
				'label' => esc_html__( 'Tumblr', 'specialty' ),
				'icon'  => 'fa-tumblr',
			),
			array(
				'name'  => 'flickr',
				'label' => esc_html__( 'Flickr', 'specialty' ),
				'icon'  => 'fa-flickr',
			),
			array(
				'name'  => 'bloglovin',
				'label' => esc_html__( 'Bloglovin', 'specialty' ),
				'icon'  => 'fa-heart',
			),
			array(
				'name'  => 'youtube',
				'label' => esc_html__( 'YouTube', 'specialty' ),
				'icon'  => 'fa-youtube',
			),
			array(
				'name'  => 'vimeo',
				'label' => esc_html__( 'Vimeo', 'specialty' ),
				'icon'  => 'fa-vimeo',
			),
			array(
				'name'  => 'dribbble',
				'label' => esc_html__( 'Dribbble', 'specialty' ),
				'icon'  => 'fa-dribbble',
			),
			array(
				'name'  => 'wordpress',
				'label' => esc_html__( 'WordPress', 'specialty' ),
				'icon'  => 'fa-wordpress',
			),
			array(
				'name'  => '500px',
				'label' => esc_html__( '500px', 'specialty' ),
				'icon'  => 'fa-500px',
			),
			array(
				'name'  => 'soundcloud',
				'label' => esc_html__( 'Soundcloud', 'specialty' ),
				'icon'  => 'fa-soundcloud',
			),
			array(
				'name'  => 'spotify',
				'label' => esc_html__( 'Spotify', 'specialty' ),
				'icon'  => 'fa-spotify',
			),
			array(
				'name'  => 'vine',
				'label' => esc_html__( 'Vine', 'specialty' ),
				'icon'  => 'fa-vine',
			),
		) );
	}
endif;

if ( ! function_exists( 'specialty_get_columns_classes' ) ) :
	function specialty_get_columns_classes( $columns ) {
		switch ( intval( $columns ) ) {
			case 1:
				$classes = 'col-xs-12';
				break;
			case 2:
				$classes = 'col-md-6 col-xs-12';
				break;
			case 3:
				$classes = 'col-xl-4 col-xs-12';
				break;
			case 4:
			default:
				$classes = 'col-lg-3 col-md-4 col-md-6 col-xs-12';
				break;
		}

		return apply_filters( 'specialty_get_columns_classes', $classes, $columns );
	}
endif;


add_filter( 'wp_page_menu', 'specialty_wp_page_menu', 10, 2 );
if ( ! function_exists( 'specialty_wp_page_menu' ) ) :
	function specialty_wp_page_menu( $menu, $args ) {
		preg_match( '#^<div class="(.*?)">(?:.*?)</div>$#', $menu, $matches );
		$menu = preg_replace( '#^<div class=".*?">#', '', $menu, 1 );
		$menu = preg_replace( '#</div>$#', '', $menu, 1 );
		$menu = preg_replace( '#^<ul>#', '<ul class="' . esc_attr( $args['menu_class'] ) . '">', $menu, 1 );

		return $menu;
	}
endif;

add_filter( 'login_url', 'specialty_redirect_login_page', 10, 2 );
if ( ! function_exists( 'specialty_redirect_login_page' ) ) :
	function specialty_redirect_login_page( $login_url, $redirect ) {
		$page_id = get_theme_mod( 'login_page' );
		if ( empty( $page_id ) || intval( $page_id ) <= 0 ) {
			return $login_url;
		}

		$page_id = intval( $page_id );
		$page = get_post( $page_id );

		if ( is_null( $page ) || ! is_object( $page ) || empty( $page ) ) {
			return $login_url;
		}

		if ( 'template-login.php' !== get_page_template_slug( $page ) ) {
			return $login_url;
		}

		$login_url = get_permalink( $page );
		if ( ! empty( $redirect ) ) {
			$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
		}

		return $login_url;
	}
endif;


if ( ! function_exists( 'specialty_get_default_footer_text' ) ) :
	function specialty_get_default_footer_text( $position = 'left' ) {
		if ( 'right' === $position ) {
			if ( ! defined( 'SPECIALTY_WHITELABEL' ) || ! SPECIALTY_WHITELABEL ) {
				$text = sprintf( __( '<a href="%s">Specialty</a> &ndash; Job Board WordPress Theme', 'specialty' ),
					esc_url( 'https://www.cssigniter.com/ignite/themes/specialty/' )
				);
			} else {
				$text = sprintf( __( '<a href="%1$s">%2$s</a>', 'specialty' ),
					esc_url( home_url( '/' ) ),
					get_bloginfo( 'name' )
				);
			}
		} else {
			if ( ! defined( 'SPECIALTY_WHITELABEL' ) || ! SPECIALTY_WHITELABEL ) {
				$text = sprintf( __( 'Powered by <a href="%s">CSSIgniter.com</a>', 'specialty' ),
					esc_url( 'https://www.cssigniter.com/' )
				);
			} else {
				$text = sprintf( __( 'Powered by <a href="%s">WordPress</a>', 'specialty' ),
					esc_url( 'https://wordpress.org/' )
				);
			}
		}

		return $text;
	}
endif;

if ( ! function_exists( 'specialty_sanitize_footer_text' ) ) :
	function specialty_sanitize_footer_text( $text ) {
		return wp_kses( $text, specialty_get_allowed_tags( 'guide' ) );
	}
endif;

if ( ! function_exists( 'specialty_get_job_listing_layout_choices' ) ) :
	function specialty_get_job_listing_layout_choices() {
		return apply_filters( 'specialty_job_listing_layout_choices', array(
			''     => esc_html__( 'Right sidebar', 'specialty' ),
			'left' => esc_html__( 'Left sidebar', 'specialty' ),
			'full' => esc_html__( 'No sidebar', 'specialty' ),
		) );
	}
endif;

if ( ! function_exists( 'specialty_sanitize_job_listing_layout_choices' ) ) :
	function specialty_sanitize_job_listing_layout_choices( $value ) {
		$choices = specialty_get_job_listing_layout_choices();
		if ( array_key_exists( $value, $choices ) ) {
			return $value;
		}

		return apply_filters( 'specialty_sanitize_listing_layout_choices_default', '' );
	}
endif;

if ( ! function_exists( 'specialty_sanitize_metabox_tab_hero' ) ) :
	function specialty_sanitize_metabox_tab_hero( $post_id ) {
		// Ignore phpcs issues. nonce validation happens inside specialty_can_save_meta(), from the caller of this function.
		// @codingStandardsIgnoreStart
		update_post_meta( $post_id, 'specialty_title', wp_kses( $_POST['specialty_title'], specialty_get_allowed_tags() ) );
		update_post_meta( $post_id, 'specialty_subtitle', wp_kses( $_POST['specialty_subtitle'], specialty_get_allowed_tags() ) );

		update_post_meta( $post_id, 'header_image_id', specialty_sanitize_intval_or_empty( $_POST['header_image_id'] ) );
		update_post_meta( $post_id, 'header_bg_color', specialty_sanitize_rgba_color( $_POST['header_bg_color'] ) );
		update_post_meta( $post_id, 'header_text_color', specialty_sanitize_hex_color( $_POST['header_text_color'] ) );
		update_post_meta( $post_id, 'header_image_repeat', specialty_sanitize_image_repeat( $_POST['header_image_repeat'] ) );
		update_post_meta( $post_id, 'header_image_position_x', specialty_sanitize_image_position_x( $_POST['header_image_position_x'] ) );
		update_post_meta( $post_id, 'header_image_position_y', specialty_sanitize_image_position_y( $_POST['header_image_position_y'] ) );
		update_post_meta( $post_id, 'header_image_attachment', specialty_sanitize_image_attachment( $_POST['header_image_attachment'] ) );
		update_post_meta( $post_id, 'header_image_cover', specialty_sanitize_checkbox_ref( $_POST['header_image_cover'] ) );
		// @codingStandardsIgnoreEnd
	}
endif;

if ( ! function_exists( 'specialty_print_metabox_tab_hero' ) ) :
	function specialty_print_metabox_tab_hero( $object, $box ) {

		specialty_metabox_open_tab( esc_html__( 'Subtitle', 'specialty' ) );
			specialty_metabox_guide( array(
				wp_kses( __( 'You can provide an HTML version of your title, in order to format it according to your needs. If you leave it empty, the normal post/page title will be used instead. The subtitle', 'specialty' ), specialty_get_allowed_tags( 'guide' ) ),
				wp_kses( sprintf( __( 'You can wrap some text within <code>%1$s</code> and <code>%2$s</code> in order to make it stand out.', 'specialty' ), esc_html( '<span class="text-theme">' ), esc_html( '</span>' ) ), specialty_get_allowed_tags( 'guide' ) ),
			) );
			specialty_metabox_input( 'specialty_title', esc_html__( 'Page Title (overrides the normal title):', 'specialty' ) );
			specialty_metabox_input( 'specialty_subtitle', esc_html__( 'Page Subtitle:', 'specialty' ) );
		specialty_metabox_close_tab();

		specialty_metabox_open_tab( esc_html__( 'Hero section', 'specialty' ) );

			$header_image_id = get_post_meta( $object->ID, 'header_image_id', true );
			?>
			<div class="ci-field-group ci-field-input">
				<label for="header_image_id"><?php esc_html_e( 'Hero image:', 'specialty' ); ?></label>
				<div class="ci-upload-preview">
					<div class="upload-preview">
						<?php if ( ! empty( $header_image_id ) ) : ?>
							<?php
								$image_url = specialty_get_image_src( $header_image_id, 'specialty_featgal_small_thumb' );
								echo sprintf( '<img src="%s" /><a href="#" class="close media-modal-icon" title="%s"></a>',
									esc_url( $image_url ),
									esc_attr__( 'Remove image', 'specialty' )
								);
							?>
						<?php endif; ?>
					</div>
					<input name="header_image_id" type="hidden" class="ci-uploaded-id" value="<?php echo esc_attr( $header_image_id ); ?>" />
					<input id="header_image_id" type="button" class="button ci-media-button" value="<?php esc_attr_e( 'Select Image', 'specialty' ); ?>" />
				</div>
			</div>
			<?php

			?><p class="ci-field-group ci-field-input"><?php
				specialty_metabox_input( 'header_bg_color', esc_html__( 'Overlay Color:', 'specialty' ), array( 'input_class' => 'alpha-color-picker widefat', 'before' => '', 'after' => '' ) );
			?></p><?php
			?><p class="ci-field-group ci-field-input"><?php
				specialty_metabox_input( 'header_text_color', esc_html__( 'Text Color:', 'specialty' ), array( 'input_class' => 'colorpckr widefat', 'before' => '', 'after' => '' ) );
			?></p><?php

			specialty_metabox_dropdown( 'header_image_repeat', specialty_get_image_repeat_choices(), esc_html__( 'Image repeat:', 'specialty' ), array( 'default' => 'no-repeat' ) );
			specialty_metabox_dropdown( 'header_image_position_x', specialty_get_image_position_x_choices(), esc_html__( 'Image horizontal position:', 'specialty' ), array( 'default' => 'center' ) );
			specialty_metabox_dropdown( 'header_image_position_y', specialty_get_image_position_y_choices(), esc_html__( 'Image vertical position:', 'specialty' ), array( 'default' => 'center' ) );
			specialty_metabox_dropdown( 'header_image_attachment', specialty_get_image_attachment_choices(), esc_html__( 'Image attachment:', 'specialty' ), array( 'default' => 'scroll' ) );
			specialty_metabox_checkbox( 'header_image_cover', 1, esc_html__( 'Scale the image to cover its containing area.', 'specialty' ), array( 'default' => 1 ) );

		specialty_metabox_close_tab();
	}
endif;

if ( ! function_exists( 'specialty_get_image_repeat_choices' ) ) :
	function specialty_get_image_repeat_choices() {
		return apply_filters( 'specialty_image_repeat_choices', array(
			'no-repeat' => esc_html__( 'No repeat', 'specialty' ),
			'repeat'    => esc_html__( 'Tile', 'specialty' ),
			'repeat-x'  => esc_html__( 'Tile Horizontally', 'specialty' ),
			'repeat-y'  => esc_html__( 'Tile Vertically', 'specialty' ),
		) );
	}
endif;

if ( ! function_exists( 'specialty_sanitize_image_repeat' ) ) :
	function specialty_sanitize_image_repeat( $value ) {
		$choices = specialty_get_image_repeat_choices();
		if ( array_key_exists( $value, $choices ) ) {
			return $value;
		}

		return apply_filters( 'specialty_sanitize_image_repeat_default', 'no-repeat' );
	}
endif;

if ( ! function_exists( 'specialty_get_image_position_x_choices' ) ) :
	function specialty_get_image_position_x_choices() {
		return apply_filters( 'specialty_image_position_x_choices', array(
			'left'   => esc_html__( 'Left', 'specialty' ),
			'center' => esc_html__( 'Center', 'specialty' ),
			'right'  => esc_html__( 'Right', 'specialty' ),
		) );
	}
endif;

if ( ! function_exists( 'specialty_sanitize_image_position_x' ) ) :
	function specialty_sanitize_image_position_x( $value ) {
		$choices = specialty_get_image_position_x_choices();
		if ( array_key_exists( $value, $choices ) ) {
			return $value;
		}

		return apply_filters( 'specialty_sanitize_image_position_x_default', 'center' );
	}
endif;

if ( ! function_exists( 'specialty_get_image_position_y_choices' ) ) :
	function specialty_get_image_position_y_choices() {
		return apply_filters( 'specialty_image_position_y_choices', array(
			'top'    => esc_html__( 'Top', 'specialty' ),
			'center' => esc_html__( 'Center', 'specialty' ),
			'bottom' => esc_html__( 'Bottom', 'specialty' ),
		) );
	}
endif;

if ( ! function_exists( 'specialty_sanitize_image_position_y' ) ) :
	function specialty_sanitize_image_position_y( $value ) {
		$choices = specialty_get_image_position_y_choices();
		if ( array_key_exists( $value, $choices ) ) {
			return $value;
		}

		return apply_filters( 'specialty_sanitize_image_position_y_default', 'center' );
	}
endif;

if ( ! function_exists( 'specialty_get_image_attachment_choices' ) ) :
	function specialty_get_image_attachment_choices() {
		return apply_filters( 'specialty_image_attachment_choices', array(
			'scroll' => esc_html__( 'Scroll', 'specialty' ),
			'fixed'  => esc_html__( 'Fixed', 'specialty' ),
		) );
	}
endif;

if ( ! function_exists( 'specialty_sanitize_image_attachment' ) ) :
	function specialty_sanitize_image_attachment( $value ) {
		$choices = specialty_get_image_attachment_choices();
		if ( array_key_exists( $value, $choices ) ) {
			return $value;
		}

		return apply_filters( 'specialty_sanitize_image_attachment_default', 'scroll' );
	}
endif;

if ( ! function_exists( 'specialty_sanitize_rgba_color' ) ) :
	function specialty_sanitize_rgba_color( $str, $return_hash = true, $return_fail = '' ) {
		if ( false === $str || empty( $str ) || 'false' === $str ) {
			return $return_fail;
		}

		// Allow keywords and predefined colors
		if ( in_array( $str, array( 'transparent', 'initial', 'inherit', 'black', 'silver', 'gray', 'grey', 'white', 'maroon', 'red', 'purple', 'fuchsia', 'green', 'lime', 'olive', 'yellow', 'navy', 'blue', 'teal', 'aqua', 'orange', 'aliceblue', 'antiquewhite', 'aquamarine', 'azure', 'beige', 'bisque', 'blanchedalmond', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgrey', 'darkgreen', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray', 'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'greenyellow', 'grey', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgray', 'lightgreen', 'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue', 'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow', 'limegreen', 'linen', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'oldlace', 'olivedrab', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'skyblue', 'slateblue', 'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'whitesmoke', 'yellowgreen', 'rebeccapurple' ), true ) ) {
			return $str;
		}

		preg_match( '/rgba\(\s*(\d{1,3}\.?\d*\%?)\s*,\s*(\d{1,3}\.?\d*\%?)\s*,\s*(\d{1,3}\.?\d*\%?)\s*,\s*(\d{1}\.?\d*\%?)\s*\)/', $str, $rgba_matches );
		if ( ! empty( $rgba_matches ) && 5 === count( $rgba_matches ) ) {
			for ( $i = 1; $i < 4; $i++ ) {
				if ( strpos( $rgba_matches[ $i ], '%' ) !== false ) {
					$rgba_matches[ $i ] = specialty_sanitize_0_100_percent( $rgba_matches[ $i ] );
				} else {
					$rgba_matches[ $i ] = specialty_sanitize_0_255( $rgba_matches[ $i ] );
				}
			}
			$rgba_matches[4] = specialty_sanitize_0_1_opacity( $rgba_matches[ $i ] );
			return sprintf( 'rgba(%s, %s, %s, %s)', $rgba_matches[1], $rgba_matches[2], $rgba_matches[3], $rgba_matches[4] );
		}

		// Not a color function either. Let's see if it's a hex color.

		// Include the hash if not there.
		// The regex below depends on in.
		if ( substr( $str, 0, 1 ) !== '#' ) {
			$str = '#' . $str;
		}

		preg_match( '/(#)([0-9a-fA-F]{6})/', $str, $matches );

		if ( 3 === count( $matches ) ) {
			if ( $return_hash ) {
				return $matches[1] . $matches[2];
			} else {
				return $matches[2];
			}
		}

		return $return_fail;
	}
endif;

if ( ! function_exists( 'specialty_sanitize_0_100_percent' ) ) :
	function specialty_sanitize_0_100_percent( $val ) {
		$val = str_replace( '%', '', $val );
		if ( floatval( $val ) > 100 ) {
			$val = 100;
		} elseif ( floatval( $val ) < 0 ) {
			$val = 0;
		}

		return floatval( $val ) . '%';
	}
endif;

if ( ! function_exists( 'specialty_sanitize_0_255' ) ) :
	function specialty_sanitize_0_255( $val ) {
		if ( intval( $val ) > 255 ) {
			$val = 255;
		} elseif ( intval( $val ) < 0 ) {
			$val = 0;
		}

		return intval( $val );
	}
endif;

if ( ! function_exists( 'specialty_sanitize_0_1_opacity' ) ) :
	function specialty_sanitize_0_1_opacity( $val ) {
		if ( floatval( $val ) > 1 ) {
			$val = 1;
		} elseif ( floatval( $val ) < 0 ) {
			$val = 0;
		}

		return floatval( $val );
	}
endif;


if ( ! defined( 'SPECIALTY_WHITELABEL' ) || false === (bool) SPECIALTY_WHITELABEL ) {
	add_filter( 'pt-ocdi/import_files', 'specialty_ocdi_import_files' );
	add_action( 'pt-ocdi/after_import', 'specialty_ocdi_after_import_setup' );
}

function specialty_ocdi_import_files( $files ) {
	if ( ! defined( 'SPECIALTY_NAME' ) ) {
		define( 'SPECIALTY_NAME', 'specialty' );
	}

	$demo_dir_url = untrailingslashit( apply_filters( 'specialty_ocdi_demo_dir_url', 'https://www.cssigniter.com/sample_content/' . SPECIALTY_NAME ) );

	// When having more that one predefined imports, set a preview image, preview URL, and categories for isotope-style filtering.
	$new_files = array(
		array(
			'import_file_name'           => esc_html__( 'Demo Import', 'specialty' ),
			'import_file_url'            => $demo_dir_url . '/content.xml',
			'import_widget_file_url'     => $demo_dir_url . '/widgets.wie',
			'import_customizer_file_url' => $demo_dir_url . '/customizer.dat',
		),
	);

	return array_merge( $files, $new_files );
}

function specialty_ocdi_after_import_setup() {
	// Set up nav menus.
	$main_menu = get_term_by( 'name', 'Menu 1', 'nav_menu' );

	set_theme_mod( 'nav_menu_locations', array(
		'main_menu' => $main_menu->term_id,
	) );

	// Set up home and blog pages.
	$front_page_id = get_page_by_title( 'Home' );
	$blog_page_id  = get_page_by_title( 'Blog' );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $front_page_id->ID );
	update_option( 'page_for_posts', $blog_page_id->ID );
}

add_action( 'init', 'specialty_migrate_custom_css_to_customizer' );
function specialty_migrate_custom_css_to_customizer() {
	if ( ! is_admin() || wp_doing_ajax() || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}

	remove_theme_mod( 'custom_css_migrated' );

	$migrated = get_theme_mod( 'custom_css_migrated', false );

	if ( $migrated || ! function_exists( 'wp_update_custom_css_post' ) ) {
		return;
	}

	// Migrate any existing theme CSS to the core option added in WordPress 4.7.
	$css = get_theme_mod( 'custom_css', '' );
	if ( $css ) {
		// Preserve any CSS already added to the core option.
		$core_css = wp_get_custom_css();

		$return = wp_update_custom_css_post( $core_css .
			PHP_EOL . PHP_EOL .
			"/* Migrated CSS from the theme's old custom CSS setting. */" .
			PHP_EOL .
			html_entity_decode( $css )
		);

		if ( ! is_wp_error( $return ) ) {
			// Remove the old option, so that the CSS is stored in only one place moving forward.
			set_theme_mod( 'custom_css', '' );
			set_theme_mod( 'custom_css_migrated', true );
		}
	}
}
