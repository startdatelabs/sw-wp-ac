<?php
	$networks = specialty_get_social_networks();
	$global   = array();
	$used     = array();
	$has_rss  = get_theme_mod( 'rss_feed', get_bloginfo( 'rss2_url' ) ) ? true : false;

	foreach ( $networks as $network ) {
		if ( get_theme_mod( 'social_' . $network['name'] ) ) {
			$global[ $network['name'] ] = get_theme_mod( 'social_' . $network['name'] );
		}
	}

	$used = $global;

	// Set the target attribute for social icons.
	$target = '';
	if ( get_theme_mod( 'social_target', 1 ) ) {
		$target = 'target="_blank"';
	}

	if ( count( $used ) > 0 || $has_rss ) {
		?>
		<ul class="list-social-icons">
			<?php
				$template = '';
				$template = '<li><a href="%1$s" class="social-icon" %2$s><i class="fa %3$s"></i></a></li>';

				foreach ( $networks as $network ) {
					if ( ! empty( $used[ $network['name'] ] ) ) {
						echo sprintf( $template,
							esc_url( $used[ $network['name'] ] ),
							$target,
							esc_attr( $network['icon'] )
						);
					}
				}

				if ( $has_rss ) {
					echo sprintf( $template,
						esc_url( get_theme_mod( 'rss_feed', get_bloginfo( 'rss2_url' ) ) ),
						$target,
						esc_attr( 'fa-rss' )
					);
				}
			?>
		</ul>
		<?php
	}
