<?php
add_action( 'customize_register', 'specialty_customize_register', 100 );
/**
 * Registers all theme-related options to the Customizer.
 *
 * @param WP_Customize_Manager $wpc Reference to the customizer's manager object.
 */
function specialty_customize_register( $wpc ) {

	$wpc->get_panel( 'nav_menus' )->priority = 20;

	$wpc->add_section( 'layout', array(
		'title'    => esc_html_x( 'Layout Options', 'customizer section title', 'specialty' ),
		'priority' => 30,
	) );

	$wpc->add_section( 'single_post', array(
		'title'       => esc_html_x( 'Posts Options', 'customizer section title', 'specialty' ),
		'description' => esc_html__( 'These options affect your individual posts.', 'specialty' ),
		'priority'    => 40,
	) );

	$wpc->add_panel( 'theme_colors', array(
		'title'    => esc_html_x( 'Colors', 'customizer section title', 'specialty' ),
		'priority' => 50,
	) );

	// Rename & Reposition the Colors section.
	$wpc->get_section( 'colors' )->title    = esc_html_x( 'Global', 'customizer section title', 'specialty' );
	$wpc->get_section( 'colors' )->priority = 10;
	$wpc->get_section( 'colors' )->panel    = 'theme_colors';

	$wpc->add_section( 'colors_header', array(
		'title'    => esc_html_x( 'Header', 'customizer section title', 'specialty' ),
		'priority' => 20,
		'panel'    => 'theme_colors',
	) );

	$wpc->add_section( 'colors_hero', array(
		'title'    => esc_html_x( 'Hero', 'customizer section title', 'specialty' ),
		'priority' => 30,
		'panel'    => 'theme_colors',
	) );

	$wpc->add_section( 'colors_sidebar', array(
		'title'    => esc_html_x( 'Sidebar', 'customizer section title', 'specialty' ),
		'priority' => 40,
		'panel'    => 'theme_colors',
	) );

	$wpc->add_section( 'typography', array(
		'title'    => esc_html_x( 'Typography Options', 'customizer section title', 'specialty' ),
		'priority' => 60,
	) );

	// The following line doesn't work in a some PHP versions. Apparently, get_panel( 'widgets' ) returns an array,
	// therefore a cast to object is needed. http://wordpress.stackexchange.com/questions/160987/warning-creating-default-object-when-altering-customize-panels
	//$wpc->get_panel( 'widgets' )->priority = 50;
	$panel_widgets = (object) $wpc->get_panel( 'widgets' );
	$panel_widgets->priority = 70;

	$wpc->add_section( 'social', array(
		'title'       => esc_html_x( 'Social Networks', 'customizer section title', 'specialty' ),
		'description' => esc_html__( 'Enter your social network URLs. Leaving a URL empty will hide its respective icon.', 'specialty' ),
		'priority'    => 80,
	) );

	$wpc->add_section( 'titles', array(
		'title'    => esc_html_x( 'Titles', 'customizer section title', 'specialty' ),
		'priority' => 90,
	) );

	$wpc->add_section( 'theme_jobs', array(
		'title'       => esc_html_x( 'Job Listing Options', 'customizer section title', 'specialty' ),
		'description' => wp_kses( sprintf( __( 'All options require that the <a href="%s">WP Job Manager</a> plugin is active.', 'specialty' ), 'https://wordpress.org/plugins/wp-job-manager/' ), specialty_get_allowed_tags( 'guide' ) ),
		'priority'    => 100,
	) );

	$wpc->add_section( 'footer', array(
		'title'    => esc_html_x( 'Footer Options', 'customizer section title', 'specialty' ),
		'priority' => 110,
	) );

	// Section 'static_front_page' is not defined when there are no pages.
	if ( get_pages() ) {
		$wpc->get_section( 'static_front_page' )->priority = 120;
	}

	$wpc->add_section( 'other', array(
		'title'       => esc_html_x( 'Other', 'customizer section title', 'specialty' ),
		'description' => esc_html__( 'Other options affecting the whole site.', 'specialty' ),
		'priority'    => 130,
	) );


	//
	// Group options by registering the setting first, and the control right after.
	//

	// Needed by the hero image descriptions.
	global $_wp_additional_image_sizes;
	$size      = $_wp_additional_image_sizes['specialty_hero'];
	$hero_size = $size['width'] . 'x' . $size['height'];


	//
	// Layout
	//
	$wpc->add_setting( 'excerpt_length', array(
		'default'           => 55,
		'sanitize_callback' => 'absint',
	) );
	$wpc->add_control( 'excerpt_length', array(
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 10,
			'step' => 1,
		),
		'section'     => 'layout',
		'label'       => esc_html__( 'Automatically generated excerpt length (in words)', 'specialty' ),
	) );

	$wpc->add_setting( 'pagination_method', array(
		'default'           => 'numbers',
		'sanitize_callback' => 'specialty_sanitize_pagination_method',
	) );
	$wpc->add_control( 'pagination_method', array(
		'type'    => 'select',
		'section' => 'layout',
		'label'   => esc_html__( 'Pagination method', 'specialty' ),
		'choices' => array(
			'numbers' => esc_html_x( 'Numbered links', 'pagination method', 'specialty' ),
			'text'    => esc_html_x( '"Previous - Next" links', 'pagination method', 'specialty' ),
		),
	) );



	//
	// Single Post
	//
	$wpc->add_setting( 'single_featured', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'single_featured', array(
		'type'    => 'checkbox',
		'section' => 'single_post',
		'label'   => esc_html__( 'Show featured image.', 'specialty' ),
	) );

	$wpc->add_setting( 'single_date', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'single_date', array(
		'type'    => 'checkbox',
		'section' => 'single_post',
		'label'   => esc_html__( 'Show date.', 'specialty' ),
	) );

	$wpc->add_setting( 'single_categories', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'single_categories', array(
		'type'    => 'checkbox',
		'section' => 'single_post',
		'label'   => esc_html__( 'Show categories.', 'specialty' ),
	) );

	$wpc->add_setting( 'single_social_sharing', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'single_social_sharing', array(
		'type'    => 'checkbox',
		'section' => 'single_post',
		'label'   => esc_html__( 'Show social sharing icons.', 'specialty' ),
	) );

	$wpc->add_setting( 'single_related', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'single_related', array(
		'type'    => 'checkbox',
		'section' => 'single_post',
		'label'   => esc_html__( 'Show related posts.', 'specialty' ),
	) );

	$wpc->add_setting( 'single_comments', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'single_comments', array(
		'type'    => 'checkbox',
		'section' => 'single_post',
		'label'   => esc_html__( 'Show comments.', 'specialty' ),
	) );



	//
	// Global colors
	//
	$wpc->get_control( 'background_image' )->section      = 'colors';
	$wpc->get_control( 'background_repeat' )->section     = 'colors';
	$wpc->get_control( 'background_attachment' )->section = 'colors';
	if ( ! is_null( $wpc->get_control( 'background_position_x' ) ) ) {
		$wpc->get_control( 'background_position_x' )->section = 'colors';
	} else {
		$wpc->get_control( 'background_position' )->section = 'colors';
		$wpc->get_control( 'background_preset' )->section   = 'colors';
		$wpc->get_control( 'background_size' )->section     = 'colors';
	}

	$wpc->add_setting( 'site_accent_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'site_accent_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Accent color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'site_text_on_accent_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'site_text_on_accent_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Text color on accent', 'specialty' ),
	) ) );

	$wpc->add_setting( 'site_text_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'site_text_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Global text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'site_border_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'site_border_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Border color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'site_element_bg_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'site_element_bg_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Element backgrounds (content wrap, job listings, filters, etc)', 'specialty' ),
	) ) );

	$wpc->add_setting( 'button_bg_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'button_bg_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Button background color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'button_text_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'button_text_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Button text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'button_hover_bg_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'button_hover_bg_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Button hover background color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'button_hover_text_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'button_hover_text_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Button hover text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'footer_background_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'footer_background_color', array(
		'section' => 'colors',
		'label'   => esc_html__( 'Footer background color', 'specialty' ),
	) ) );



	//
	// Header colors
	//
	$wpc->add_setting( 'head_text_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'head_text_color', array(
		'section' => 'colors_header',
		'label'   => esc_html__( 'Main text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'head_text_hover_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'head_text_hover_color', array(
		'section' => 'colors_header',
		'label'   => esc_html__( 'Main hover &amp; active text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'head_subnav_text_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'head_subnav_text_color', array(
		'section' => 'colors_header',
		'label'   => esc_html__( 'Subnavigation text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'head_subnav_text_hover_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'head_subnav_text_hover_color', array(
		'section' => 'colors_header',
		'label'   => esc_html__( 'Subnavigation hover &amp; active text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'head_subnav_bg_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'head_subnav_bg_color', array(
		'section' => 'colors_header',
		'label'   => esc_html__( 'Subnavigation background', 'specialty' ),
	) ) );

	$wpc->add_setting( 'head_subnav_border_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'head_subnav_border_color', array(
		'section' => 'colors_header',
		'label'   => esc_html__( 'Subnavigation border color', 'specialty' ),
	) ) );



	//
	// Hero colors
	//
	$wpc->add_setting( 'hero_text_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'hero_text_color', array(
		'section' => 'colors_hero',
		'label'   => esc_html__( 'Text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'hero_bg_color', array(
		'default'           => 'rgba( 47, 48, 67, 0.82 )',
		'sanitize_callback' => 'specialty_sanitize_rgba_color',
	) );
	$wpc->add_control( new Customize_Alpha_Color_Control( $wpc, 'hero_bg_color', array(
		'label'        => esc_html__( 'Background Color', 'specialty' ),
		'description'  => wp_kses( __( 'Select a color for your hero section. This will be visible <strong>on top</strong> of the image, so adjust the opacity to achieve the required result.', 'specialty' ), specialty_get_allowed_tags() ),
		'section'      => 'colors_hero',
		'show_opacity' => true,
	) ) );

	$wpc->add_setting( 'hero_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wpc->add_control( new WP_Customize_Image_Control( $wpc, 'hero_image', array(
		'section'     => 'colors_hero',
		'label'       => esc_html__( 'Hero image', 'specialty' ),
		'description' => esc_html( sprintf( __( 'Recommended size of at least %s pixels.', 'specialty' ), $hero_size ) ),
	) ) );

	$wpc->add_setting( 'hero_image_repeat', array(
		'default'           => 'no-repeat',
		'sanitize_callback' => 'specialty_sanitize_image_repeat',
	) );
	$wpc->add_control( 'hero_image_repeat', array(
		'type'    => 'select',
		'section' => 'colors_hero',
		'label'   => esc_html__( 'Image repeat', 'specialty' ),
		'choices' => specialty_get_image_repeat_choices(),
	) );

	$wpc->add_setting( 'hero_image_position_x', array(
		'default'           => 'center',
		'sanitize_callback' => 'specialty_sanitize_image_position_x',
	) );
	$wpc->add_control( 'hero_image_position_x', array(
		'type'    => 'select',
		'section' => 'colors_hero',
		'label'   => esc_html__( 'Image horizontal position', 'specialty' ),
		'choices' => specialty_get_image_position_x_choices(),
	) );

	$wpc->add_setting( 'hero_image_position_y', array(
		'default'           => 'center',
		'sanitize_callback' => 'specialty_sanitize_image_position_y',
	) );
	$wpc->add_control( 'hero_image_position_y', array(
		'type'    => 'select',
		'section' => 'colors_hero',
		'label'   => esc_html__( 'Image vertical position', 'specialty' ),
		'choices' => specialty_get_image_position_y_choices(),
	) );

	$wpc->add_setting( 'hero_image_attachment', array(
		'default'           => 'scroll',
		'sanitize_callback' => 'specialty_sanitize_image_attachment',
	) );
	$wpc->add_control( 'hero_image_attachment', array(
		'type'    => 'select',
		'section' => 'colors_hero',
		'label'   => esc_html__( 'Image attachment', 'specialty' ),
		'choices' => specialty_get_image_attachment_choices(),
	) );

	$wpc->add_setting( 'hero_image_cover', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'hero_image_cover', array(
		'type'    => 'checkbox',
		'section' => 'colors_hero',
		'label'   => esc_html__( 'Scale the image to cover its containing area.', 'specialty' ),
	) );



	//
	// Sidebar colors
	//
	$wpc->add_setting( 'sidebar_text_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'sidebar_text_color', array(
		'section' => 'colors_sidebar',
		'label'   => esc_html__( 'Text color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'sidebar_link_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'sidebar_link_color', array(
		'section' => 'colors_sidebar',
		'label'   => esc_html__( 'Link color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'sidebar_link_hover_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'sidebar_link_hover_color', array(
		'section' => 'colors_sidebar',
		'label'   => esc_html__( 'Link hover color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'sidebar_border_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'sidebar_border_color', array(
		'section' => 'colors_sidebar',
		'label'   => esc_html__( 'Border color', 'specialty' ),
	) ) );

	$wpc->add_setting( 'sidebar_widget_title_color', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_hex_color',
	) );
	$wpc->add_control( new WP_Customize_Color_Control( $wpc, 'sidebar_widget_title_color', array(
		'section' => 'colors_sidebar',
		'label'   => esc_html__( 'Widget titles color', 'specialty' ),
	) ) );



	//
	// Typography
	//
	$wpc->add_setting( 'h1_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'h1_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Content H1 size', 'specialty' ),
	) );

	$wpc->add_setting( 'h2_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'h2_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Content H2 size', 'specialty' ),
	) );

	$wpc->add_setting( 'h3_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'h3_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Content H3 size', 'specialty' ),
	) );

	$wpc->add_setting( 'h4_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'h4_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Content H4 size', 'specialty' ),
	) );

	$wpc->add_setting( 'h5_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'h5_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Content H5 size', 'specialty' ),
	) );

	$wpc->add_setting( 'h6_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'h6_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Content H6 size', 'specialty' ),
	) );

	$wpc->add_setting( 'body_text_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'body_text_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Content body text size', 'specialty' ),
	) );

	$wpc->add_setting( 'widgets_title_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'widgets_title_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Widgets title size', 'specialty' ),
	) );

	$wpc->add_setting( 'widgets_text_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'widgets_text_size', array(
		'type'    => 'number',
		'section' => 'typography',
		'label'   => esc_html__( 'Widgets text size', 'specialty' ),
	) );

	$wpc->add_setting( 'lowercase_widget_titles', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'lowercase_widget_titles', array(
		'type'    => 'checkbox',
		'section' => 'typography',
		'label'   => esc_html__( 'Don\'t uppercase widget titles', 'specialty' ),
	) );

	$wpc->add_setting( 'uppercase_content_titles', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'uppercase_content_titles', array(
		'type'    => 'checkbox',
		'section' => 'typography',
		'label'   => esc_html__( 'Uppercase content titles', 'specialty' ),
	) );



	//
	// Social
	//
	$wpc->add_setting( 'social_target', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'social_target', array(
		'type'    => 'checkbox',
		'section' => 'social',
		'label'   => esc_html__( 'Open social and sharing links in a new tab.', 'specialty' ),
	) );

	$networks = specialty_get_social_networks();

	foreach ( $networks as $network ) {
		$wpc->add_setting( 'social_' . $network['name'], array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wpc->add_control( 'social_' . $network['name'], array(
			'type'    => 'url',
			'section' => 'social',
			'label'   => esc_html( sprintf( _x( '%s URL', 'social network url', 'specialty' ), $network['label'] ) ),
		) );
	}

	$wpc->add_setting( 'rss_feed', array(
		'default'           => get_bloginfo( 'rss2_url' ),
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wpc->add_control( 'rss_feed', array(
		'type'    => 'url',
		'section' => 'social',
		'label'   => esc_html__( 'RSS Feed', 'specialty' ),
	) );



	//
	// Titles
	//
	$wpc->add_setting( 'title_blog', array(
		'default'           => esc_html__( 'From the blog', 'specialty' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wpc->add_control( 'title_blog', array(
		'type'    => 'text',
		'section' => 'titles',
		'label'   => esc_html__( 'Blog section title', 'specialty' ),
	) );

	$wpc->add_setting( 'title_search', array(
		'default'           => esc_html__( 'Search results', 'specialty' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wpc->add_control( 'title_search', array(
		'type'    => 'text',
		'section' => 'titles',
		'label'   => esc_html__( 'Search title', 'specialty' ),
	) );

	$wpc->add_setting( 'title_404', array(
		'default'           => esc_html__( 'Page not found', 'specialty' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wpc->add_control( 'title_404', array(
		'type'    => 'text',
		'section' => 'titles',
		'label'   => esc_html__( '404 (not found) title', 'specialty' ),
	) );

	$wpc->add_setting( 'single_related_title', array(
		'default'           => esc_html__( 'Related Articles', 'specialty' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wpc->add_control( 'single_related_title', array(
		'type'    => 'text',
		'section' => 'single_post',
		'label'   => esc_html__( 'Related Posts section title', 'specialty' ),
	) );



	//
	// Jobs
	//
	$wpc->add_setting( 'theme_jobs_listing_create_alert', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'theme_jobs_listing_create_alert', array(
		'type'        => 'checkbox',
		'section'     => 'theme_jobs',
		'label'       => esc_html__( 'Show alert creation box on job listing.', 'specialty' ),
		'description' => esc_html__( 'This only applies on pages with the "Job Listing" template assigned.', 'specialty' ),
	) );

	$wpc->add_setting( 'theme_jobs_report_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wpc->add_control( 'theme_jobs_report_email', array(
		'type'        => 'text',
		'section'     => 'theme_jobs',
		'label'       => esc_html__( 'Report Job email address', 'specialty' ),
		'description' => esc_html__( 'When an email address is provided, a "Report this listing" link will appear on the bottom of single job pages. While anti-spam measures are being taken, it is suggested that you use a dedicated email address for this option, so that it may easily be changed in the future if needed. If you want to completely disable reporting, just leave this empty.', 'specialty' ),
	) );

	$wpc->add_setting( 'theme_jobs_report_email_logged_in', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'theme_jobs_report_email_logged_in', array(
		'type'        => 'checkbox',
		'section'     => 'theme_jobs',
		'label'       => esc_html__( 'Only logged in users can report a job.', 'specialty' ),
		'description' => esc_html__( 'When disabled, all visitors will have access to the reporting link, exposing your email address to more people.', 'specialty' ),
	) );



	//
	// Footer
	//
	$wpc->add_setting( 'footer_text_left', array(
		'default'           => specialty_get_default_footer_text( 'left' ),
		'sanitize_callback' => 'specialty_sanitize_footer_text',
	) );
	$wpc->add_control( 'footer_text_left', array(
		'type'        => 'text',
		'section'     => 'footer',
		'label'       => esc_html__( 'Left footer text', 'specialty' ),
		'description' => esc_html__( 'Allowed tags: a (href|class), img (alt|src|class), span (class), i (class), b, em, strong.', 'specialty' ),
	) );

	$wpc->add_setting( 'footer_text_right', array(
		'default'           => specialty_get_default_footer_text( 'right' ),
		'sanitize_callback' => 'specialty_sanitize_footer_text',
	) );
	$wpc->add_control( 'footer_text_right', array(
		'type'        => 'text',
		'section'     => 'footer',
		'label'       => esc_html__( 'Right footer text', 'specialty' ),
		'description' => esc_html__( 'Allowed tags: a (href|class), img (alt|src|class), span (class), i (class), b, em, strong.', 'specialty' ),
	) );



	//
	// Other
	//
	$sample_content_url = apply_filters( 'specialty_sample_content_url',
		sprintf( 'https://www.cssigniter.com/sample_content/%s.zip', SPECIALTY_NAME ),
		'https://www.cssigniter.com/sample_content/',
		SPECIALTY_NAME
	);

	if ( ! empty( $sample_content_url ) && ( ! defined( 'SPECIALTY_WHITELABEL' ) || SPECIALTY_WHITELABEL == false ) ) {
		$wpc->add_setting( 'sample_content_link', array(
			'default' => '',
		) );
		$wpc->add_control( new Specialty_Customize_Static_Text_Control( $wpc, 'sample_content_link', array(
			'section'     => 'other',
			'label'       => esc_html__( 'Resources', 'specialty' ),
			'description' => array(
				wp_kses(
					sprintf( __( '<a href="%s" target="_blank">Download the theme\'s sample content</a> to get things moving.', 'specialty' ),
						esc_url( $sample_content_url )
					),
					specialty_get_allowed_tags( 'guide' )
				),
			),
		) ) );
	}

	$wpc->add_setting( 'login_page', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'login_page', array(
		'label'       => esc_html__( 'Login page', 'specialty' ),
		'description' => wp_kses( __( 'The selected page needs to have the <strong>Login</strong> template assigned. Once an appropriate page has been selected, all login links will be redirected to that page.', 'specialty' ), specialty_get_allowed_tags( 'guide' ) ),
		'section'     => 'other',
		'type'        => 'dropdown-pages',
	) );



	//
	// title_tagline Section
	//
	$wpc->add_setting( 'limit_logo_size', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'limit_logo_size', array(
		'type'        => 'checkbox',
		'section'     => 'title_tagline',
		'priority'    => 8,
		'label'       => esc_html__( 'Limit logo size (for Retina display)', 'specialty' ),
		'description' => esc_html__( 'This option will limit the image size to half its width. You will need to upload your image in 2x the dimension you want to display it in.', 'specialty' ),
	) );

	$wpc->add_setting( 'logo_site_title', array(
		'default'           => 1,
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'logo_site_title', array(
		'type'    => 'checkbox',
		'section' => 'title_tagline',
		'label'   => esc_html__( 'Show site title below the logo.', 'specialty' ),
	) );

	$wpc->add_setting( 'logo_tagline', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_checkbox',
	) );
	$wpc->add_control( 'logo_tagline', array(
		'type'    => 'checkbox',
		'section' => 'title_tagline',
		'label'   => esc_html__( 'Show the tagline below the logo.', 'specialty' ),
	) );

	$wpc->add_setting( 'logo_padding_top', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'logo_padding_top', array(
		'type'    => 'number',
		'section' => 'title_tagline',
		'label'   => esc_html__( 'Logo top padding', 'specialty' ),
	) );

	$wpc->add_setting( 'logo_padding_bottom', array(
		'default'           => '',
		'sanitize_callback' => 'specialty_sanitize_intval_or_empty',
	) );
	$wpc->add_control( 'logo_padding_bottom', array(
		'type'    => 'number',
		'section' => 'title_tagline',
		'label'   => esc_html__( 'Logo bottom padding', 'specialty' ),
	) );
}

add_action( 'customize_register', 'specialty_customize_register_custom_controls', 9 );
/**
 * Registers custom Customizer controls.
 *
 * @param WP_Customize_Manager $wpc Reference to the customizer's manager object.
 */
function specialty_customize_register_custom_controls( $wpc ) {
	require get_template_directory() . '/inc/customizer-controls/static-text.php';
	require get_template_directory() . '/inc/customizer-controls/alpha-color-picker/alpha-color-picker.php';
}
