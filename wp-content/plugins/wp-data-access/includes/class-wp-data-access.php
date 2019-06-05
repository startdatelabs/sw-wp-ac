<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\includes
 */

use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
use WPDataAccess\Data_Tables\WPDA_Data_Tables;
use WPDataAccess\Utilities\WPDA_Table_Actions;
use WPDataAccess\Utilities\WPDA_Example;
use WPDataAccess\Utilities\WPDA_Export;
use WPDataAccess\Utilities\WPDA_Favourites;
use WPDataProjects\Utilities\WPDP_Export_Project;
use WPDataAccess\Backup\WPDA_Data_Export;

/**
 * Class WP_Data_Access
 *
 * Core plugin class used to define:
 * + admin specific functionality {@see WP_Data_Access_Admin}
 * + public specific functionality {@see WP_Data_Access_Public}
 * + internationalization {@see WP_Data_Access_I18n}
 * + plugin activation and deactivation {@see WP_Data_Access_Loader}
 *
 * @package plugin\includes
 * @author  Peter Schulz
 * @since   1.0.0
 *
 * @see WP_Data_Access_Admin
 * @see WP_Data_Access_Public
 * @see WP_Data_Access_I18n
 * @see WP_Data_Access_Loader
 */
class WP_Data_Access {

	/**
	 * Reference to plugin loader
	 *
	 * @var WP_Data_Access_Loader
	 *
	 * @since   1.0.0
	 *
	 * @see WP_Data_Access_Loader
	 */
	protected $loader;

	/**
	 * WP_Data_Access constructor
	 *
	 * Calls method the following methods to setup plugin:
	 * + {@see WP_Data_Access::load_dependencies()}
	 * + {@see WP_Data_Access::set_locale()}
	 * + {@see WP_Data_Access::define_admin_hooks()}
	 * + {@see WP_Data_Access::define_public_hooks()}
	 *
	 * @since   1.0.0
	 *
	 * @see WP_Data_Access::load_dependencies()
	 * @see WP_Data_Access::set_locale()
	 * @see WP_Data_Access::define_admin_hooks()
	 * @see WP_Data_Access::define_public_hooks()
	 */
	public function __construct() {

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load required dependencies
	 *
	 * Loads required plugin files and initiates the plugin loader.
	 *
	 * @since   1.0.0
	 *
	 * @see WP_Data_Access_Loader
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-data-access-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-data-access-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-data-access-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-data-access-public.php';

		$this->loader = new WP_Data_Access_Loader();

	}

	/**
	 * Set locale for internationalization
	 *
	 * @since   1.0.0
	 *
	 * @see WP_Data_Access_I18n
	 */
	private function set_locale() {

		$wpda_i18n = new WP_Data_Access_I18n();
		$this->loader->add_action( 'init', $wpda_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Add admin hooks
	 *
	 * Initiates {@see WP_Data_Access_Admin} (admin functionality), {@see WPDA_Export} (export functionality) and
	 * {@see WPDA_Example} (example plugin that demostrates the use of WP Data Access by code from another plugin).
	 * Adds the appropriate actions to the loader.
	 *
	 * @since   1.0.0
	 *
	 * @see WP_Data_Access_Admin
	 * @see WPDA_Export
	 * @see WPDA_Example
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WP_Data_Access_Admin();

		// Handle plugin cookies.
		$this->loader->add_action( 'admin_init' , $plugin_admin, 'handle_plugin_cookies' );

		// Admin menu.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_items' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_my_tables', 11 );

		// Admin scripts.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// TinyMCE listboxes (get table list).
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'tinymce_listboxes_get_tables' );

		// Export action.
		$plugin_export = new WPDA_Export();
		$this->loader->add_action( 'admin_action_wpda_export', $plugin_export, 'export' );

		// Example requested.
		$plugin_example = new WPDA_Example();
		$this->loader->add_action( 'admin_action_wpda_example', $plugin_example, 'get_example' );

		// Add/remove favourites.
		$plugin_favourites = new WPDA_Favourites();
		$this->loader->add_action( 'admin_action_add_favourite', $plugin_favourites, 'add' );
		$this->loader->add_action( 'admin_action_rem_favourite', $plugin_favourites, 'rem' );

        // Show tables actions.
        $plugin_table_actions = new WPDA_Table_Actions();
        $this->loader->add_action( 'admin_action_show_table_actions', $plugin_table_actions, 'show' );

        // Get columns for a specific table.
        $plugin_column_list = new WPDA_Dictionary_Lists();
        $this->loader->add_action( 'admin_action_get_columns', $plugin_column_list, 'get_columns' );

        // Export project.
		$plugin_export_project = new WPDP_Export_Project();
		$this->loader->add_action( 'admin_action_wpdp_export_project', $plugin_export_project, 'export' );

		// Data backup.
		$wpda_data_backup = new WPDA_Data_Export();
		$this->loader->add_action( 'wpda_data_backup', $wpda_data_backup, 'wpda_data_backup' );

	}

	/**
	 * Add public hooks
	 *
	 * Initiates {@see WP_Data_Access_Public} (public functionality), {@see WPDA_Data_Tables} (ajax call to support
	 * server side jQuery DataTables functionality) and {@see WPDA_Dictionary_Lists} (table and column list for tables
	 * to which access is granted to the front-end to support the shortcode wizard). Adds the appropriate actions to
	 * the loader.
	 *
	 * @since   1.0.0
	 *
	 * @see WP_Data_Access_Public
	 * @see WPDA_Data_Tables
	 * @see WPDA_Dictionary_Lists
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_Data_Access_Public();

		// Shortcodes.
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action( 'init', $plugin_public, 'wpdataaccess_shortcode_button_init' );

		// Public scripts.
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Ajax calls.
		$plugin_datatables = new WPDA_Data_Tables();
		$this->loader->add_action( 'wp_ajax_wpda_datatables', $plugin_datatables, 'get_data' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpda_datatables', $plugin_datatables, 'get_data' );

		// Ajax calls to dynamically load listbox content (tables and columns).
		$plugin_tinymce_listboxes = new WPDA_Dictionary_Lists();
		$this->loader->add_action( 'wp_ajax_wpda_tinymce_listbox_tables', $plugin_tinymce_listboxes, 'get_tables_tinymce_listbox' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpda_tinymce_listbox_tables', $plugin_tinymce_listboxes, 'get_tables_tinymce_listbox' );
		$this->loader->add_action( 'wp_ajax_wpda_tinymce_listbox_columns', $plugin_tinymce_listboxes, 'get_columns_tinymce_listbox' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpda_tinymce_listbox_columns', $plugin_tinymce_listboxes, 'get_columns_tinymce_listbox' );

	}

	/**
	 * Start plugin loader
	 *
	 * @since   1.0.0
	 */
	public function run() {

		$this->loader->run();

	}

}
