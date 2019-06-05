<?php
if ( ! function_exists( 'specialty_get_customizer_css' ) ) :
	function specialty_get_customizer_css() {
		ob_start();

		//
		// Logo
		//
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( get_theme_mod( 'limit_logo_size' ) && ! empty( $custom_logo_id ) ) {
			$image_metadata = wp_get_attachment_metadata( $custom_logo_id );
			$max_width      = floor( $image_metadata['width'] / 2 );
			?>
			.site-logo img {
				max-width: <?php echo intval( $max_width ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'logo_padding_top' ) || get_theme_mod( 'logo_padding_bottom' ) ) {
			?>
			.site-logo {
				<?php if ( get_theme_mod( 'logo_padding_top' ) ) : ?>
					padding-top: <?php echo intval( get_theme_mod( 'logo_padding_top' ) ); ?>px;
				<?php endif; ?>
				<?php if ( get_theme_mod( 'logo_padding_bottom' ) ) : ?>
					padding-bottom: <?php echo intval( get_theme_mod( 'logo_padding_bottom' ) ); ?>px;
				<?php endif; ?>
			}
			<?php
		}


		//
		// Global Colors
		//
		if ( get_theme_mod( 'site_accent_color' ) ) {
			$accent_color = get_theme_mod( 'site_accent_color' );
			?>
			a, a:hover,
			.text-theme,
			.navigation-main li li:hover > a,
			.navigation-main li li > a:focus,
			.navigation-main li .current-menu-item > a,
			.navigation-main li .current-menu-parent > a,
			.navigation-main li .current-menu-ancestor > a,
			.widget_meta li a:hover,
			.widget_pages li a:hover,
			.widget_categories li a:hover,
			.widget_archive li a:hover,
			.widget_nav_menu li a:hover,
			.widget_recent_entries li a:hover,
			.btn-white.btn-transparent:hover {
				color: <?php echo sanitize_hex_color( $accent_color ); ?>;
			}

			.chosen-container .chosen-results li.highlighted,
			.item-color,
			.item-badge,
			.list-item-callout,
			.list-item-secondary-wrap,
			.item-filter-tag-bg,
			.checkbox-filter:checked + label::before,
			.navigation a:hover,
			.navigation .current,
			.social-icon:hover,
			.navigation-main > li.menu-item-btn > a:hover,
			.select2-container .select2-results .select2-results__option--highlighted {
				background-color: <?php echo sanitize_hex_color( $accent_color ); ?>;
			}

			input:hover,
			input:focus,
			textarea:hover,
			textarea:focus,
			.social-icon:hover,
			.navigation-main > li.menu-item-btn > a {
				border-color: <?php echo sanitize_hex_color( $accent_color ); ?>;
			}

			.item-listing::after,
			ul.job_listings::after {
				border-color: <?php echo specialty_hex2rgba( $accent_color, .35 ); ?>;
			}

			.item-listing::after,
			ul.job_listings::after {
				border-top-color: <?php echo specialty_hex2rgba( $accent_color, .875 ); ?>;
			}

			.navigation-main > li:hover > a,
			.navigation-main > li > a:focus,
			.navigation-main > .current-menu-item > a,
			.navigation-main > .current-menu-parent > a,
			.navigation-main > .current-menu-ancestor > a {
				border-bottom-color: <?php echo sanitize_hex_color( $accent_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'site_text_on_accent_color' ) ) {
			$text_on_accent = get_theme_mod( 'site_text_on_accent_color' );
			?>
			.item-badge,
			.item-badge:hover,
			.list-item-callout .list-item-title,
			.list-item-callout .list-item-time,
			.list-item-callout .list-item-company,
			.checkbox-filter-label::after,
			.navigation a:hover,
			.navigation .current {
				color: <?php echo sanitize_hex_color( $text_on_accent ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'site_text_color' ) ) {
			$text_color       = get_theme_mod( 'site_text_color' );
			$text_color_light = specialty_color_luminance( $text_color, .2 );
			$text_color_dark  = specialty_color_luminance( $text_color, -.2 );
			?>
			body,
			input,
			textarea,
			select,
			.ci-select::after,
			.widget .instagram-pics li a,
			.chosen-container,
			.chosen-container .chosen-single,
			.chosen-container-multi .chosen-choices,
			.section-title-compliment a,
			.entry-share,
			.navigation a,
			.navigation span,
			.ci-select .select2-container .select2-selection.select2-selection--multiple select2-selection_choice {
				color: <?php echo sanitize_hex_color( $text_color ); ?>;
			}

			blockquote cite,
			.ci-select select,
			.mobile-trigger:hover,
			.mobile-trigger:focus,
			.list-item-time,
			.list-item-company,
			.checkbox-filter-label,
			.social-icon,
			.widget_meta li a,
			.widget_pages li a,
			.widget_categories li a,
			.widget_archive li a,
			.widget_nav_menu li a,
			.widget_recent_entries li a,
			.text-secondary,
			.comment-author,
			.footer-copy,
			.content-wrap-footer,
			.entry-meta,
			.navigation a,
			.navigation span {
				color: <?php echo sanitize_hex_color( $text_color_light ); ?>;
			}

			.social-icon {
				border-color: <?php echo sanitize_hex_color( $text_color_light ); ?>;
			}

			.list-item-location,
			.widget-title {
				color: <?php echo sanitize_hex_color( $text_color_dark ); ?>;
			}

			@media (max-width: 991px) {
				form-filter {
					color: <?php echo sanitize_hex_color( $text_color ); ?>;
				}
			}
			<?php
		}

		if ( get_theme_mod( 'site_border_color' ) ) {
			$border_color      = get_theme_mod( 'site_border_color' );
			$border_color_dark = specialty_color_luminance( $border_color, -0.2 );
			?>
			input,
			textarea,
			select,
			.widget select,
			.ci-select select,
			.chosen-container .chosen-single,
			.chosen-drop,
			.chosen-container-single .chosen-search input[type="text"],
			.chosen-container-multi .chosen-choices,
			.chosen-container-active.chosen-with-drop .chosen-single
			{
				border-color: <?php echo sanitize_hex_color( $border_color ); ?>;
			}

			input:hover,
			input:focus,
			textarea:hover,
			textarea:focus {
				border-color: <?php echo $border_color_dark; ?>;
			}

			.footer-copy,
			.mobile-triggers {
				border-top-color: <?php echo sanitize_hex_color( $border_color ); ?>;
			}

			.widget_recent_comments li,
			.company_video,
			.widget ul.job_listings li.job_listing,
			.content-wrap,
			ul.job_listings li.list-item.job_listing,
			.list-item-secondary-wrap:last-child,
			.item-filter,
			.search-notice,
			.entry-item,
			.navigation a,
			.navigation span,
			.card-info-thumb,
			.callout-wrapper {
				border-bottom-color: <?php echo sanitize_hex_color( $border_color ); ?>;
			}

			blockquote {
				border-left-color: <?php echo sanitize_hex_color( $border_color ); ?>;
			}

			.mobile-trigger:first-child {
				border-right-color: <?php echo sanitize_hex_color( $border_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'site_element_bg_color' ) ) {
			$element_bg = get_theme_mod( 'site_element_bg_color' );
			?>
			.company_video,
			.widget ul.job_listings li.job_listing,
			.content-wrap,
			.list-item:not(.list-item-callout),
			.no_job_listings_found,
			.item-filter,
			.entry-item,
			.navigation a,
			.navigation span,
			.card-info-thumb,
			.callout-wrapper {
				background-color: <?php echo sanitize_hex_color( $element_bg ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'button_bg_color' ) ) {
			$button_bg_color = get_theme_mod( 'button_bg_color' );
			?>
			.btn,
			.button,
			.comment-reply-link,
			input[type="submit"],
			input[type="reset"],
			button {
				background-color: <?php echo sanitize_hex_color( $button_bg_color ); ?>;
			}

			.btn-transparent {
				background-color: transparent;
				color: <?php echo sanitize_hex_color( $button_bg_color ); ?>;
				border-color: <?php echo sanitize_hex_color( $button_bg_color ); ?>;
			}

			.btn-transparent:hover {
				background-color: <?php echo sanitize_hex_color( $button_bg_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'button_hover_bg_color' ) ) {
			$button_hover_bg_color = get_theme_mod( 'button_hover_bg_color' );
			?>
			.btn:hover,
			.button:hover,
			.comment-reply-link:hover,
			input[type="submit"]:hover,
			input[type="reset"]:hover,
			button:hover {
				background-color: <?php echo sanitize_hex_color( $button_hover_bg_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'button_text_color' ) ) {
			$button_text_color = get_theme_mod( 'button_text_color' );
			?>
			.btn,
			.button,
			.comment-reply-link,
			input[type="submit"],
			input[type="reset"],
			button {
				color: <?php echo sanitize_hex_color( $button_text_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'button_hover_text_color' ) ) {
			$button_hover_text_color = get_theme_mod( 'button_hover_text_color' );
			?>
			.btn:hover,
			.button:hover,
			.comment-reply-link:hover,
			input[type="submit"]:hover,
			input[type="reset"]:hover,
			button:hover {
				color: <?php echo sanitize_hex_color( $button_hover_text_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'footer_background_color' ) ) {
			$footer_background_color = get_theme_mod( 'footer_background_color' );
			?>
			.footer {
				background-color: <?php echo sanitize_hex_color( $footer_background_color ); ?>;
			}
			<?php
		}


		//
		// Header Colors
		//
		if ( get_theme_mod( 'head_text_color' ) ) {
			$head_text_color = get_theme_mod( 'head_text_color' );
			?>
			.header,
			.site-logo a,
			.site-tagline,
			.navigation-main > li > a {
				color: <?php echo sanitize_hex_color( $head_text_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'head_text_hover_color' ) ) {
			$head_text_hover_color = get_theme_mod( 'head_text_hover_color' );
			?>
			.site-logo a:hover,
			.navigation-main > li:hover > a,
			.navigation-main > li > a:focus,
			.navigation-main > .current-menu-item > a,
			.navigation-main > .current-menu-parent > a,
			.navigation-main > .current-menu-ancestor > a {
				color: <?php echo sanitize_hex_color( $head_text_hover_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'head_subnav_bg_color' ) ) {
			$head_subnav_bg_color = get_theme_mod( 'head_subnav_bg_color' );
			?>
			.navigation-main ul {
				background-color: <?php echo sanitize_hex_color( $head_subnav_bg_color ); ?>;
			}

			.navigation-main > li > ul::before {
				border-bottom-color: <?php echo sanitize_hex_color( $head_subnav_bg_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'head_subnav_text_color' ) ) {
			$head_subnav_text_color = get_theme_mod( 'head_subnav_text_color' );
			?>
			.navigation-main li li a {
				color: <?php echo sanitize_hex_color( $head_subnav_text_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'head_subnav_text_hover_color' ) ) {
			$head_subnav_text_hover_color = get_theme_mod( 'head_subnav_text_hover_color' );
			?>
			.navigation-main li li:hover > a,
			.navigation-main li li > a:focus,
			.navigation-main li .current-menu-item > a,
			.navigation-main li .current-menu-parent > a,
			.navigation-main li .current-menu-ancestor > a	{
				color: <?php echo sanitize_hex_color( $head_subnav_text_hover_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'head_subnav_border_color' ) ) {
			$head_subnav_border_color = get_theme_mod( 'head_subnav_border_color' );
			?>
			.navigation-main li li a {
				border-color: <?php echo sanitize_hex_color( $head_subnav_border_color ); ?>;
			}

			.navigation-main ul {
				border-bottom-color: <?php echo sanitize_hex_color( $head_subnav_border_color ); ?>;
			}
			<?php
		}


		//
		// Sidebar Colors
		//
		if ( get_theme_mod( 'sidebar_text_color' ) ) {
			$sidebar_text_color = get_theme_mod( 'sidebar_text_color' );
			?>
			.sidebar,
			.sidebar .widget-title {
				color: <?php echo sanitize_hex_color( $sidebar_text_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'sidebar_link_color' ) ) {
			$sidebar_link_color = get_theme_mod( 'sidebar_link_color' );
			?>
			.sidebar a:not(.btn),
			.sidebar .widget_meta li a,
			.sidebar .widget_pages li a,
			.sidebar .widget_categories li a,
			.sidebar .widget_archive li a,
			.sidebar .widget_nav_menu li a,
			.sidebar .widget_recent_entries li a,
			.sidebar .widget_product_categories li a,
			.sidebar .widget_layered_nav li a,
			.sidebar .widget_rating_filter li a,
			.sidebar .sidebar .product_list_widget .product-title {
				color: <?php echo sanitize_hex_color( $sidebar_link_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'sidebar_link_hover_color' ) ) {
			$sidebar_link_hover_color = get_theme_mod( 'sidebar_link_hover_color' );
			?>
			.sidebar a:not(.btn):hover,
			.sidebar .widget_meta li a:hover,
			.sidebar .widget_pages li a:hover,
			.sidebar .widget_categories li a:hover,
			.sidebar .widget_archive li a:hover,
			.sidebar .widget_nav_menu li a:hover,
			.sidebar .widget_recent_entries li a:hover,
			.sidebar .widget_product_categories li a:hover,
			.sidebar .widget_layered_nav li a:hover,
			.sidebar .widget_rating_filter li a:hover,
			.sidebar .product_list_widget .product-title:hover {
				color: <?php echo sanitize_hex_color( $sidebar_link_hover_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'sidebar_border_color' ) ) {
			$sidebar_border_color = get_theme_mod( 'sidebar_border_color' );
			?>

			.sidebar .widget select {
				border-color: <?php echo sanitize_hex_color( $sidebar_border_color ); ?>;
			}

			.sidebar .widget_recent_comments li,
			.sidebar .widget_rss li {
				border-bottom-color: <?php echo sanitize_hex_color( $sidebar_border_color ); ?>;
			}
			<?php
		}

		if ( get_theme_mod( 'sidebar_widget_title_color' ) ) {
			$sidebar_widget_title_color = get_theme_mod( 'sidebar_widget_title_color' );
			?>
			.sidebar .widget-title {
				color: <?php echo sanitize_hex_color( $sidebar_widget_title_color ); ?>;
			}
			<?php
		}


		//
		// Typography
		//
		if ( get_theme_mod( 'h1_size' ) ) {
			?>
			.entry-content h1,
			.entry-title {
				font-size: <?php echo intval( get_theme_mod( 'h1_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'h2_size' ) ) {
			?>
			.entry-content h2 {
				font-size: <?php echo intval( get_theme_mod( 'h2_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'h3_size' ) ) {
			?>
			.entry-content h3 {
				font-size: <?php echo intval( get_theme_mod( 'h3_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'h4_size' ) ) {
			?>
			.entry-content h4 {
				font-size: <?php echo intval( get_theme_mod( 'h4_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'h5_size' ) ) {
			?>
			.entry-content h5 {
				font-size: <?php echo intval( get_theme_mod( 'h5_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'h6_size' ) ) {
			?>
			.entry-content h6 {
				font-size: <?php echo intval( get_theme_mod( 'h6_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'body_text_size' ) ) {
			?>
			.entry-content {
				font-size: <?php echo intval( get_theme_mod( 'body_text_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'widgets_text_size' ) ) {
			?>
			.sidebar .widget,
			.footer .widget,
			.sidebar .widget_meta li,
			.sidebar .widget_pages li,
			.sidebar .widget_categories li,
			.sidebar .widget_archive li,
			.sidebar .widget_nav_menu li,
			.sidebar .widget_recent_entries li,
			.footer .widget_meta li,
			.footer .widget_pages li,
			.footer .widget_categories li,
			.footer .widget_archive li,
			.footer .widget_nav_menu li,
			.footer .widget_recent_entries li {
				font-size: <?php echo intval( get_theme_mod( 'widgets_text_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'widgets_title_size' ) ) {
			?>
			.widget-title {
				font-size: <?php echo intval( get_theme_mod( 'widgets_title_size' ) ); ?>px;
			}
			<?php
		}

		if ( get_theme_mod( 'lowercase_widget_titles' ) ) {
			?>
			.widget-title {
				text-transform: none;
			}
			<?php
		}

		if ( get_theme_mod( 'uppercase_content_titles' ) ) {
			?>
			.entry-content h1,
			.entry-content h2,
			.entry-content h3,
			.entry-content h4,
			.entry-content h5,
			.entry-content h6 {
				text-transform: uppercase;
			}
			<?php
		}

		// TODO: Remove this after two versions from when the option was removed.
		if ( get_theme_mod( 'custom_css' ) ) {
			echo get_theme_mod( 'custom_css' );
		}

		$css = ob_get_clean();
		return apply_filters( 'specialty_customizer_css', $css );
	}
endif;


if ( ! function_exists( 'specialty_get_hero_styles' ) ) :
	function specialty_get_hero_styles() {
		$bg_color         = get_theme_mod( 'hero_bg_color', 'rgba( 47, 48, 67, 0.82 )' );
		$text_color       = get_theme_mod( 'hero_text_color' );
		$image            = get_theme_mod( 'hero_image' );
		$image_repeat     = get_theme_mod( 'hero_image_repeat', 'no-repeat' );
		$image_position_x = get_theme_mod( 'hero_image_position_x', 'center' );
		$image_position_y = get_theme_mod( 'hero_image_position_y', 'center' );
		$image_attachment = get_theme_mod( 'hero_image_attachment', 'scroll' );
		$image_cover      = get_theme_mod( 'hero_image_cover', 1 );

		if ( is_singular() ) {
			$single_image_id = get_post_meta( get_queried_object_id(), 'header_image_id', true );
			$single_bg_color = get_post_meta( get_queried_object_id(), 'header_bg_color', true );

			if ( $single_image_id || $single_bg_color ) {
				if ( $single_image_id ) {
					$image = specialty_get_image_src( $single_image_id, 'specialty_hero' );
				} else {
					$image = '';
				}

				$bg_color         = $single_bg_color;
				$text_color       = get_post_meta( get_queried_object_id(), 'header_text_color', true );
				$image_repeat     = get_post_meta( get_queried_object_id(), 'header_image_repeat', true );
				$image_position_x = get_post_meta( get_queried_object_id(), 'header_image_position_x', true );
				$image_position_y = get_post_meta( get_queried_object_id(), 'header_image_position_y', true );
				$image_attachment = get_post_meta( get_queried_object_id(), 'header_image_attachment', true );
				$image_cover      = get_post_meta( get_queried_object_id(), 'header_image_cover', true );
			}
		}

		$style = '';

		if ( $bg_color ) {
			$style .= '.page-hero::before { ';
			$style .= sprintf( 'background-color: %s; ',
				$bg_color
			);
			$style .= '}' . PHP_EOL;
		}

		if ( $text_color || $image ) {
			$style .= '.page-hero { ';

			if ( $text_color ) {
				$style .= sprintf( 'color: %s; ',
					$text_color
				);
			}

			if ( $image ) {
				$style .= sprintf( 'background-image: url(%s); ',
					$image
				);

				if ( $image_repeat ) {
					$style .= sprintf( 'background-repeat: %s; ',
						$image_repeat
					);
				}

				if ( $image_position_x && $image_position_y ) {
					$style .= sprintf( 'background-position: %s %s; ',
						$image_position_x,
						$image_position_y
					);
				}

				if ( $image_attachment ) {
					$style .= sprintf( 'background-attachment: %s; ',
						$image_attachment
					);
				}

				if ( $image_cover ) {
					$style .= 'background-size: cover; ';
				}
			}

			$style .= '}';
		}

		return $style;
	}
endif;
