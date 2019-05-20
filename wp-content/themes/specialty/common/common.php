<?php
/**
 * Common theme features.
 */

/**
 * Common assets registration
 */
function specialty_register_common_assets() {
	$theme = wp_get_theme();
	wp_register_style( 'specialty-common', get_template_directory_uri() . '/common/css/global.css', array(), $theme->get( 'Version' ) );
}
add_action( 'init', 'specialty_register_common_assets', 8 );
