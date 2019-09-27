<?php
if ( ! function_exists( 'specialty_color_luminance' ) ) :
	/**
	 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.
	 *
	 * @see https://gist.github.com/stephenharris/5532899
	 *
	 * @param string $color Hexadecimal color value. May be 3 or 6 digits, with an optional leading # sign.
	 * @param float $percent Decimal (0.2 = lighten by 20%, -0.4 = darken by 40%)
	 *
	 * @return string Lightened/Darkened colour as hexadecimal (with hash)
	 */
	function specialty_color_luminance( $color, $percent ) {
		// Remove # if provided
		if ( '#' === $color[0] ) {
			$color = substr( $color, 1 );
		}

		// Validate hex string.
		$hex     = preg_replace( '/[^0-9a-f]/i', '', $color );
		$new_hex = '#';

		$percent = floatval( $percent );

		if ( strlen( $hex ) < 6 ) {
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}

		// Convert to decimal and change luminosity.
		for ( $i = 0; $i < 3; $i ++ ) {
			$dec      = hexdec( substr( $hex, $i * 2, 2 ) );
			$dec      = min( max( 0, $dec + $dec * $percent ), 255 );
			$new_hex .= str_pad( dechex( $dec ), 2, 0, STR_PAD_LEFT );
		}

		return $new_hex;
	}
endif;

if ( ! function_exists( 'specialty_hex2rgba' ) ) :
	/**
	 * Converts hexadecimal color value to rgb(a) format.
	 *
	 * @param string $color Hexadecimal color value. May be 3 or 6 digits, with an optional leading # sign.
	 * @param float|bool $opacity Opacity level 0-1 (decimal) or false to disable.
	 *
	 * @return string
	 */
	function specialty_hex2rgba( $color, $opacity = false ) {

		$default = 'rgb(0,0,0)';

		// Return default if no color provided
		if ( empty( $color ) ) {
			return $default;
		}

		// Remove # if provided
		$color = ltrim( $color, '#' );

		// Check if color has 6 or 3 characters and get values
		if ( strlen( $color ) === 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) === 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		$rgb = array_map( 'hexdec', $hex );

		if ( false !== $opacity ) {
			$opacity = abs( floatval( $opacity ) );
			if ( $opacity > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		return $output;
	}
endif;

if ( ! function_exists( 'specialty_posts_pagination' ) ) :
	/**
	 * Echoes pagination links if applicable. Output depends on pagination method selected from the customizer.
	 *
	 * @uses the_post_pagination()
	 * @uses previous_posts_link()
	 * @uses next_posts_link()
	 *
	 * @param array $args An array of arguments to change default behavior.
	 * @param WP_Query|null $query A WP_Query object to paginate. Defaults to null and uses the global $wp_query
	 *
	 * @return void
	 */
	function specialty_posts_pagination( $args = array(), WP_Query $query = null ) {
		$args = wp_parse_args( $args, apply_filters( 'specialty_posts_pagination_default_args', array(
			'mid_size'           => 1,
			'prev_text'          => _x( 'Previous', 'previous post', 'specialty' ),
			'next_text'          => _x( 'Next', 'next post', 'specialty' ),
			'screen_reader_text' => __( 'Posts navigation', 'specialty' ),
			'container_id'       => '',
			'container_class'    => '',
		), $query ) );

		global $wp_query;

		if ( ! is_null( $query ) ) {
			$old_wp_query = $wp_query;
			$wp_query     = $query;
		}

		$output = '';
		$method = get_theme_mod( 'pagination_method', 'numbers' );

		if ( $wp_query->max_num_pages > 1 ) {

			switch ( $method ) {
				case 'text':
					$output = get_the_posts_navigation( $args );
					break;
				case 'numbers':
				default:
					$output = get_the_posts_pagination( $args );
					break;
			}

			if ( ! empty( $args['container_id'] ) || ! empty( $args['container_class'] ) ) {
				$output = sprintf( '<div id="%2$s" class="%3$s">%1$s</div>', $output, esc_attr( $args['container_id'] ), esc_attr( $args['container_class'] ) );
			}
		}

		if ( ! is_null( $query ) ) {
			$wp_query = $old_wp_query;
		}

		// All markup is from native WordPress functions. The wrapping div is properly escaped above.
		$output_safe = $output;

		echo $output_safe;
	}
endif;