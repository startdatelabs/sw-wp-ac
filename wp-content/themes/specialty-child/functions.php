<?php
add_action( 'after_setup_theme', 'specialty_child_before_setup', 5 );
function specialty_child_before_setup() {

	/*
	 * Loads the language files of the child theme (if any).
	 * If you use any of the WordPress localization functions, you will need to provide the same text domain
	 * exactly as the one mentioned below, i.e. 'specialty-child'
	 * If you change this value, then you'll need to modify your function calls accordingly.
	 * Localization functions reference: https://codex.wordpress.org/L10n
	 */
	load_child_theme_textdomain( 'specialty-child', get_stylesheet_directory() . '/languages' );

	/*
	 * Code added here will execute BEFORE the respective specialty_setup function of the parent theme.
	 */


}


add_action( 'after_setup_theme', 'specialty_child_after_setup', 15 );
function specialty_child_after_setup() {

	/*
	 * Code added here will execute AFTER the respective specialty_setup function of the parent theme.
	 */


}


add_action( 'wp_enqueue_scripts', 'specialty_child_enqueue_scripts', 15 );
function specialty_child_enqueue_scripts() {
	$theme = wp_get_theme();

	/*
	 * The parent theme's stylesheet, as well as this child's stylesheet, are loaded by default by the
	 * parent theme itself. This means you can easily add you CSS customizations by just adding them
	 * inside this child's style.css file.
	 */

	/*
	 * If you DON'T want the parent stylesheet to load, then uncomment the following line.
	 */
	//wp_dequeue_style( 'specialty-style-parent' );


	/*
	 * Note that the wp_dequeue_style() call above, does not dequeue the rest of the stylesheets.
	 * If you need the rest of the stylesheets not to load, then uncomment the following lines.
	 */
	//wp_deregister_style( 'specialty-dependencies' );
	//wp_register_style( 'specialty-dependencies', false, array(), $theme->get( 'Version' ) );


}
