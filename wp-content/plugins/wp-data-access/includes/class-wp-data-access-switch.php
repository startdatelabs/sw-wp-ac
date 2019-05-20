<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\includes
 */

use WPDataAccess\Utilities\WPDA_Repository;
use WPDataAccess\WPDA;

/**
 * Class WP_Data_Access_Switch
 *
 * Switch to:
 * + activate plugin {@see WP_Data_Access_Switch::activate()}
 * + deactive plugin {@see WP_Data_Access_Switch::deactivate()}
 *
 * @package plugin\includes
 * @author  Peter Schulz
 * @since   1.0.0
 *
 * @see WP_Data_Access_Switch::activate()
 * @see WP_Data_Access_Switch::deactivate()
 */
class WP_Data_Access_Switch {

	/**
	 * Activate plugin WP Data Access
	 *
	 * The user must have the appropriate privileges to perform this operation.
	 *
	 * For single site installation {@see WP_Data_Access_Switch::activate_blog()} will be called. For multi site
	 * installations {@see WP_Data_Access_Switch::activate_blog()} must be called for every blog.
	 *
	 * IMPORTANT!!!
	 *
	 * For blogs installed on multi site installations after activation of the plugin, activation of the plugin for
	 * that blog will not be performed if the plugin is network activated. In that case the admin user of the blog
	 * will receive a message when viewing a plugin page with an option to follow these steps manually.
	 *
	 * @since   1.0.0
	 *
	 * @see WP_Data_Access_Switch::activate_blog()
	 */
	public static function activate() {
		if ( current_user_can( 'activate_plugins' ) ) {
			// Activate plugin.
			if ( is_multisite() ) {
				global $wpdb;
				// Multisite installation.
				$blogids = $wpdb->get_col( "select blog_id from $wpdb->blogs" ); // db call ok; no-cache ok.
				foreach ( $blogids as $blog_id ) {
					// Uninstall blog.
					switch_to_blog( $blog_id );
					self::activate_blog();
					restore_current_blog();
				}
			} else {
				// Single site installation.
				self::activate_blog();
			}
		} else {
			// Is this blocking the site on unattended plugin update? (support topic 11472418 - tjgorman)
			// wp_die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
		}
	}

	/**
	 * Activate blog
	 *
	 * The user must have the appropriate privileges to perform this operation.
	 *
	 * On activation this method checks whether there has previously been a version of the plugin installed. For this
	 * purpose the wp_options table read directly (usually done via class WPDA). If a value is found, this method
	 * checks if the version number in wp_options is the same as the plugin version. If these are equal, no action is
	 * needed. If they are not equal, this method will check if there is an upgrade or downgrade for the delta
	 * between these releases.
	 *
	 * This action is performed on the 'active WordPress blog'. On single site there is only one blog. On multisite
	 * installations it must be executed for every blog.
	 *
	 * On a fresh installation the following actions are performed:
	 * + save plugin version number in wp_options {@see WPDA::set_option()}
	 * + (re)create plugin repository {@see WPDA_Repository::recreate()}
	 *
	 * @since   1.0.0
	 *
	 * @see WPDA::set_option()
	 * @see WPDA_Repository::create()
	 */
	protected static function activate_blog() {
		if ( current_user_can( 'activate_plugins' ) ) {
			// (re)create plugin repository
			// If no repository is found, a new one is created
			// If a repository is found, the table structures are update and the data transferred
			$wpda_repository = new WPDA_Repository();
			$wpda_repository->recreate();

			// Save plugin version
			WPDA::set_option( WPDA::OPTION_WPDA_VERSION );

			// Show link to "What's New?" page on plugin pages in WordPress dashboard.
			WPDA::set_option( WPDA::OPTION_WPDA_SHOW_WHATS_NEW, 'on' );
		} else {
			// Is this blocking the site on unattended plugin update? (support topic 11472418 - tjgorman)
			// wp_die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
		}
	}

	/**
	 * Deactivate plugin WP Data Access
	 *
	 * On deactivation we leave the repository and options as they are in case the user wants to reactivate the
	 * plugin later again. Tables and options are deleted when the plugin is uninstalled. To keep tables and options
	 * on uninstall change plugin settings (see uninstall settings).
	 *
	 * @since   1.0.0
	 */
	public static function deactivate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			// Is this blocking the site on unattended plugin update? (support topic 11472418 - tjgorman)
			// wp_die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
		}
	}

}
