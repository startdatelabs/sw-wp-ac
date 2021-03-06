<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Uninstallation steps:
 * + Check if plugin is called correcly
 * + Check if user has the appropriate rights
 * + Drop plugin tables (unless settings indicate not to)
 * + Delete plugin options from $wpdb->options (unless settings indicate not to)
 *
 * For multisite installation steps 3 and 4 are performed for every blog.
 *
 * @package     plugin
 * @author      Peter Schulz
 * @since       1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	exit();
}

/**
 * Uninstall blog
 *
 * This functions is called when the plugin is uninstalled. The following actions are performed:
 * + Drop plugin tables (unless settings indicate not to)
 * + Delete plugin options from $wpdb->options (unless settings indicate not to)
 *
 * Actions are processed on the current blog and are repeated for every blog on a multisite installation. Must be
 * called from the dashboard (WP_UNINSTALL_PLUGIN defined). User must have the proper privileges (activate_plugins).
 *
 * @author      Peter Schulz
 * @since       1.0.0
 */
function wpda_uninstall_blog() {

	global $wpdb;

	$drop_tables = get_option( 'wpda_uninstall_tables' );
	if ( ! $drop_tables || 'on' === $drop_tables ) {
		// Drop repository.
		$wpdb->query(
			"
				DROP TABLE IF EXISTS 
				{$wpdb->prefix}wpda_logging
			"
		); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		$wpdb->query(
			"
				DROP TABLE IF EXISTS 
				{$wpdb->prefix}wpda_menu_items
			"
		); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		$wpdb->query(
			"
				DROP TABLE IF EXISTS 
				{$wpdb->prefix}wpda_table_design
			"
		); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		$wpdb->query(
			"
				DROP TABLE IF EXISTS 
				{$wpdb->prefix}wpdp_project
			"
		); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		$wpdb->query(
			"
				DROP TABLE IF EXISTS 
				{$wpdb->prefix}wpdp_page
			"
		); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		$wpdb->query(
			"
				DROP TABLE IF EXISTS 
				{$wpdb->prefix}wpdp_table
			"
		); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
	}

	$delete_options = get_option( 'wpda_uninstall_options' );
	if ( ! $delete_options || 'on' === $delete_options ) {
		// Delete all options from wp_options.
		$wpdb->query(
			"
				DELETE FROM {$wpdb->options}
				WHERE option_name LIKE 'wpda_%'
			"
		); // db call ok; no-cache ok.
	}

}

if ( is_multisite() ) {

	global $wpdb;

	// Uninstall plugin for alll blogs one by one (will fail silently for blogs having no plugin tables/options).
	$blogids = $wpdb->get_col( "select blog_id from $wpdb->blogs" ); // db call ok; no-cache ok.
	foreach ( $blogids as $blog_id ) {

		// Uninstall blog.
		switch_to_blog( $blog_id );
		wpda_uninstall_blog();
		restore_current_blog();

	}
} else {

	// Uninstall on single site installation.
	wpda_uninstall_blog();

}
