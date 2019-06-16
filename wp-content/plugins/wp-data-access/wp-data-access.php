<?php
/**
 * Plugin Name:       WP Data Access
 * Plugin URI:        https://wpdataaccess.com/
 * Description:       A WordPress data administration and publication tool that helps you to manage your Wordpress data and database and build data driven WordPress apps that run in the WordPress dashboard and add dynamic WordPress tables to your website.
 * Version:           2.0.14
 * Author:            Peter Schulz
 * Author URI:        https://www.linkedin.com/in/peterschulznl/
 * Text Domain:       wp-data-access
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 * @package plugin
 * @author  Peter Schulz
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load WPDataAccess namespace.
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Activate plugin
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
function activate_wp_data_access() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access-switch.php';
	WP_Data_Access_Switch::activate();
}
register_activation_hook( __FILE__, 'activate_wp_data_access' );

/**
 * Deactivate plugin
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
function deactivate_wp_data_access() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access-switch.php';
	WP_Data_Access_Switch::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_wp_data_access' );

/**
 * Check if database needs to be updated
 *
 * @author  Peter Schulz
 * @since   1.5.2
 */
function wpda_update_db_check() {
	if ( WPDataAccess\WPDA::OPTION_WPDA_VERSION[1] !== get_option( WPDataAccess\WPDA::OPTION_WPDA_VERSION[0] ) ) {
		activate_wp_data_access();
	}
}
add_action( 'plugins_loaded', 'wpda_update_db_check' );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access.php';
/**
 * Start plugin
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
function run_wp_data_access() {
	$wpdataaccess = new WP_Data_Access();
	$wpdataaccess->run();
}

run_wp_data_access();
