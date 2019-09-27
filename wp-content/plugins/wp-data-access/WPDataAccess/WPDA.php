<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess
 */

namespace WPDataAccess {

	/**
	 * Class WPDA
	 *
	 * Plugin default values and settings are managed through this class. Every plugin option has a default value
	 * which is maintained in an array together with the option name. Options are only saved in $wpdb->options when
	 * they are changed. Otherwise the default values are used. After reading option values from $wpdb->options the
	 * values are cached as many of them are used in multiple
	 * places during the processing of a request.
	 *
	 * @package WPDataAccess
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA {

		/**
		 * Label for WordPress tables
		 */
		const TABLE_TYPE_WP = 'wordpress table';
		/**
		 * Label for WPDA plugin tables
		 */
		const TABLE_TYPE_WPDA = 'plugin table';

		// Options are stored in arrays (OPTION_ARRAYS):
		// [0] = option name (as saved in wp_options).
		// [1] = default value (used if option is not available in wp_options).
		// Application options.
		/**
		 * Option wpda_name and it's default value
		 */
		const OPTION_WPDA_NAME = [ 'wpda_name', 'WP Data Access' ];
		/**
		 * Option wpda_version and it's default value
		 */
		const OPTION_WPDA_VERSION = [ 'wpda_version', '2.0.13' ];
		/**
		 * Option wpda_prefix and it's default value
		 */
		const OPTION_WPDA_PREFIX = [ 'wpda_prefix', 'wpda_' ];
		/**
		 * Option wpda_setup_error and it's default value
		 */
		const OPTION_WPDA_SETUP_ERROR = [ 'wpda_setup_error', '' ]; // Values: off = do not display setup errors, other = show.
		/**
		 * Option wpda_show_whats_new and it's default value
		 */
		const OPTION_WPDA_SHOW_WHATS_NEW = [ 'wpda_show_whats_new', 'off' ]; // Values: off = hide link to what's new page, other = show.
		/**
		 * Option wpda_uninstall_tables and it's default value
		 */
		const OPTION_WPDA_UNINSTALL_TABLES = [ 'wpda_uninstall_tables', 'on' ]; // On uninstall drop WPDA tables.
		/**
		 * Option wpda_uninstall_options and it's default value
		 */
		const OPTION_WPDA_UNINSTALL_OPTIONS = [ 'wpda_uninstall_options', 'on' ]; // On uninstall delety WPDA options.
		/**
		 * Option wpda_bootstrap_version and it's default value
		 */
		const OPTION_WPDA_BOOTSTRAP_VERSION = [ 'wpda_bootstrap_version', '3.3.7' ];
		/**
		 * Option wpda_datatables_version and it's default value
		 */
		const OPTION_WPDA_DATATABLES_VERSION = [ 'wpda_datatables_version', '1.10.16' ];
		/**
		 * Option wpda_datatables_responsive_version and it's default value
		 */
		const OPTION_WPDA_DATATABLES_RESPONSIVE_VERSION = [ 'wpda_datatables_responsive_version', '2.1.1' ];

		// Back-end options.
		/**
		 * Option wpda_be_table_access and it's default value
		 */
		const OPTION_BE_TABLE_ACCESS = [ 'wpda_be_table_access', 'show' ]; // Values: show, hide and select.
		/**
		 * Option wpda_be_table_access_selected and it's default value
		 */
		const OPTION_BE_TABLE_ACCESS_SELECTED = [ 'wpda_be_table_access_selected', '' ]; // Tables authorized in WPDA.
		/**
		 * Option wpda_be_allow_structure and it's default value
		 */
		const OPTION_BE_ALLOW_STRUCTURE = [ 'wpda_be_allow_structure', 'on' ]; // Show table structure link in list table.
		/**
		 * Option wpda_be_allow_schemas and it's default value
		 */
		const OPTION_BE_ALLOW_SCHEMAS = [ 'wpda_be_allow_schemas', 'on' ]; // Allow accessing other schemas.
		/**
		 * Option wpda_be_allow_drop and it's default value
		 */
		const OPTION_BE_ALLOW_DROP = [ 'wpda_be_allow_drop', 'on' ]; // Show drop table link in list table.
		/**
		 * Option wpda_be_allow_truncate and it's default value
		 */
		const OPTION_BE_ALLOW_TRUNCATE = [ 'wpda_be_allow_truncate', 'on' ]; // Show truncate table link in list table.
        /**
         * Option wpda_be_allow_drop_index and it's default value
         */
        const OPTION_BE_ALLOW_DROP_INDEX = [ 'wpda_be_allow_drop_index', 'on' ]; // Show drop index link in list table.
        /**
         * Option wpda_be_allow_rename and it's default value
         */
        const OPTION_BE_ALLOW_RENAME = [ 'wpda_be_allow_rename', 'on' ]; // Allow rename table/view.
        /**
         * Option wpda_be_allow_copy and it's default value
         */
        const OPTION_BE_ALLOW_COPY = [ 'wpda_be_allow_copy', 'on' ]; // Allow copy table/view.
		/**
		 * Option wpda_be_view_link and it's default value
		 */
		const OPTION_BE_VIEW_LINK = [ 'wpda_be_view_link', 'on' ]; // Show view link in list table.
		/**
		 * Option wpda_be_allow_insert and it's default value
		 */
		const OPTION_BE_ALLOW_INSERT = [ 'wpda_be_allow_insert', 'on' ]; // Show insert link in list table (simple form).
		/**
		 * Option wpda_be_allow_update and it's default value
		 */
		const OPTION_BE_ALLOW_UPDATE = [ 'wpda_be_allow_update', 'on' ]; // Show update link in list table (simple form).
		/**
		 * Option wpda_be_allow_delete and it's default value
		 */
		const OPTION_BE_ALLOW_DELETE = [ 'wpda_be_allow_delete', 'on' ]; // Show delete link in list table.
		/**
		 * Option wpda_be_wpda_export_tables and it's default value
		 */
		const OPTION_BE_EXPORT_TABLES = [ 'wpda_be_wpda_export_tables', 'on' ]; // Show export link in list table (main only).
		/**
		 * Option wpda_be_wpda_export_rows and it's default value
		 */
		const OPTION_BE_EXPORT_ROWS = [ 'wpda_be_wpda_export_rows', 'on' ]; // Show export link in list table (row export).
		/**
		 * Option wpda_be_wpda_export_variable_prefix and it's default value
		 */
		const OPTION_BE_EXPORT_VARIABLE_PREFIX = [ 'wpda_be_wpda_export_variable_prefix', 'on' ]; // Allows to import into repository with different wpdb prefix.
		/**
		 * Option wpda_be_wpda_allow_imports and it's default value
		 */
		const OPTION_BE_ALLOW_IMPORTS = [ 'wpda_be_wpda_allow_imports', 'on' ]; // Allow to import data (main only).
		/**
		 * Option wpda_be_confirm_export and it's default value
		 */
		const OPTION_BE_CONFIRM_EXPORT = [ 'wpda_be_confirm_export', 'on' ]; // Ask for confirmation before exporting.
		/**
		 * Option wpda_be_confirm_view and it's default value
		 */
		const OPTION_BE_CONFIRM_VIEW = [ 'wpda_be_confirm_view', 'on' ]; // Ask for confirmation before viewing.
		/**
		 * Option wpda_be_pagination and it's default value
		 */
		const OPTION_BE_PAGINATION = [ 'wpda_be_pagination', '10' ];
		/**
		 * Option wpda_be_remember_search and it's default value
		 */
		const OPTION_BE_REMEMBER_SEARCH = [ 'wpda_be_remember_search', 'on' ];
		/**
		 * Option wpda_be_innodb_count and it's default value
		 */
		const OPTION_BE_INNODB_COUNT = [ 'wpda_be_innodb_count', 100000 ];
		/**
		 * Option wpda_be_design_mode and it's default value
		 */
		const OPTION_BE_DESIGN_MODE = [ 'wpda_be_design_mode', 'advanced' ]; // Default design mode (basic/advanced).
		/**
		 * Option wpda_be_text_wrap_switch and it's default value
		 */
		const OPTION_BE_TEXT_WRAP_SWITCH = [ 'wpda_be_text_wrap_switch', 'off' ];
		/**
		 * Option wpda_be_text_wrap and it's default value
		 */
		const OPTION_BE_TEXT_WRAP = [ 'wpda_be_text_wrap', 400 ];

		// Front-end options.
		/**
		 * Option wpda_fe_load_bootstrap and it's default value
		 */
		const OPTION_FE_LOAD_BOOTSTRAP = [ 'wpda_fe_load_bootstrap', 'off' ];
		/**
		 * Option wpda_fe_load_datatables and it's default value
		 */
		const OPTION_FE_LOAD_DATATABLES = [ 'wpda_fe_load_datatables', 'on' ];
		/**
		 * Option wpda_fe_load_datatables_response and it's default value
		 */
		const OPTION_FE_LOAD_DATATABLES_RESPONSE = [ 'wpda_fe_load_datatables_response', 'on' ];
		/**
		 * Option wpda_fe_table_access and it's default value
		 */
		const OPTION_FE_TABLE_ACCESS = [ 'wpda_fe_table_access', 'select' ]; // Values: hide, show, select.
		/**
		 * Option wpda_fe_table_access_selected and it's default value
		 */
		const OPTION_FE_TABLE_ACCESS_SELECTED = [ 'wpda_fe_table_access_selected', '' ]; // Tables authorized in WPDA.

		// Manage Repository options.
		/**
		 * Option wpda_mr_keep_backup_tables and it's default value
		 */
		const OPTION_MR_KEEP_BACKUP_TABLES = [ 'wpda_mr_keep_backup_tables', 'on' ];

		// Data Backup options.
		/**
		 * Option wpda_db_local_path and it's default value
		 */
		const OPTION_DB_LOCAL_PATH = [ 'wpda_db_local_path', '' ];
		/**
		 * Option wpda_db_dropbox_path and it's default value
		 */
		const OPTION_DB_DROPBOX_PATH = [ 'wpda_db_dropbox_path', '/wp-data-access/' ];

		/**
		 * List of plugin tables
		 */
		const WPDA_TABLES = [
			'menu_items'   => true,
			'table_design' => true,
		];
		const WPDP_TABLES = [
			'wpdp_project' => true,
			'wpdp_page'    => true,
			'wpdp_table'   => true,
		];

		// Option values once queried from wp_options are stored in an array for re-use during request to prevent
		// executing same query on wp_options multiple times.
		/**
		 * Options cache array
		 *
		 * @var array
		 */
		static protected $option_cache = [];

		/**
		 * List containing all WordPress tables
		 *
		 * @var array
		 */
		static protected $wp_tables = [];

		/**
		 * Translated table label
		 *
		 * Returns a translated table label. The label depends on the type of table. Provided through a function to
		 * support internationalization.
		 *
		 * @since   1.0.0
		 *
		 * @param string $table_type Table type (use WPDA constants).
		 * @return string Translated table type.
		 */
		public static function get_table_type_text( $table_type ) {

			switch ( $table_type ) {

				case self::TABLE_TYPE_WP:
					return __( 'WordPress table', 'wp-data-access' );

				case self::TABLE_TYPE_WPDA:
					return __( 'plugin table', 'wp-data-access' );

				default:
					return $table_type;

			}

		}

		/**
		 * Get plugin option values
		 *
		 * Get value for pluginoption saved in wp_options. If option is not found in $wpdb->options the default value
		 * for that option is returned. Option values once taken from $wpdb->options are cached to prevent execution
		 * of the same query multiple times during teh processing of a request.
		 *
		 * @since   1.0.0
		 *
		 * @param array $option OPTION_ARRAY (use class constants).
		 * @return mixed Value for OPTION_ARRAY ($option): (1) cached value (2) wp_options value (3) default value.
		 */
		public static function get_option( $option ) {

			if ( isset( self::$option_cache[ $option[0] ] ) ) {
				return self::$option_cache[ $option[0] ]; // Re-use cached value.
			}

			$option_value = get_option( $option[0] );
			if ( ! $option_value ) {
				// Option not found in wp_options: save default value for re-use.
				self::$option_cache[ $option[0] ] = $option[1];
			} else {
				// Option found in wp_options: save for re-use.
				self::$option_cache[ $option[0] ] = $option_value;
			}

			return self::$option_cache[ $option[0] ]; // Return saved value.

		}

		/**
		 * Delete all plugin options from table wp_options
		 *
		 * @since   1.0.0
		 */
		public static function clear_all_options() {

			global $wpdb;

			$wpdb->query(
				"
				DELETE FROM wp_options
				WHERE option_name LIKE 'wpda_%'
			"
			); // db call ok; no-cache ok.

			self::$option_cache = []; // Reset cache.

		}

		/**
		 * Load all WordPress tables
		 *
		 * @since   1.1.0
		 */
		public static function load_wp_tables() {

			if ( 0 === count( self::$wp_tables ) ) {
				try {
					global $wpdb;

					if ( ! is_multisite() ) {
						foreach ( $wpdb->tables( 'all', true ) as $table ) {
							self::$wp_tables[ $table ] = $table;
						}
					} else {
						$query = "select blog_id from {$wpdb->blogs}";
						$blogs = $wpdb->get_results( $query, 'ARRAY_N' );
						foreach ( $blogs as $blog ) {
							foreach ( $wpdb->tables( $blog === reset( $blogs ) ? 'all' : 'blog', true, $blog[0] ) as $table ) {
								self::$wp_tables[ $table ] = $table;
							}
						}
					}

					return true;
				} catch ( \Exception $e ) {
					wp_die( 'ERROR: ' . $e->getMessage() );
				}
			}

		}

		/**
		 * Checks if a table is a WordPress table
		 *
		 * @since   1.1.0
		 *
		 * @param string $table_name Table name.
		 * @return bool TRUE = WordPress table
		 */
		public static function is_wp_table( $table_name ) {

			self::load_wp_tables();

			if ( 0 === count( self::$wp_tables ) ) {
				return false;
			}

			return isset( self::$wp_tables[ $table_name ] );

		}

		/**
		 * List containing all WordPress tables
		 *
		 * @since   1.1.0
		 *
		 * @return array
		 */
		public static function get_wp_tables() {

			self::load_wp_tables();

			if ( 0 === count( self::$wp_tables ) ) {
				wp_die( 'ERROR: No WordPress table found???' );
			}

			return self::$wp_tables;

		}

		/**
		 * Returns a list of all plugin tables
		 *
		 * @since   1.0.0
		 *
		 * @return array List of plugin tables
		 */
		public static function get_wpda_tables() {

			global $wpdb;
			$wpda_tables = [];

			foreach ( self::WPDA_TABLES as $key => $value ) {
				$wpda_tables[] = $wpdb->prefix . self::get_option( self::OPTION_WPDA_PREFIX ) . $key;
			}

			foreach ( self::WPDP_TABLES as $key => $value ) {
				$wpda_tables[] = $wpdb->prefix . $key;
			}

			return $wpda_tables;

		}

		/**
		 * Return the default value for a plugin option
		 *
		 * @since   1.0.0
		 *
		 * @param array $option OPTION_ARRAY (use class constants).
		 * @return mixed Default value for OPTION_ARRAY ($option).
		 */
		public static function get_option_default( $option ) {

			return $option[1]; // Default option value.

		}

		/**
		 * Checks if table is plugin table
		 *
		 * NOTE
		 * Variable $phpdoc_supported_solution is a temporary variable that does not add any functionality to this
		 * function. It only serves the purpose to get class WPDA in the documentation!!! If the isset statement in
		 * the return is performed directly on self::WPDA_TABLES, class WPDA will not appear in the phpdoc generated
		 * documentation. To avoid class WPDA to be undocumented, we use $phpdoc_supported_solution.
		 *
		 * @since   1.0.0
		 *
		 * @param string $real_table_name Table name to be checked.
		 * @return bool TRUE = $table_name is a WPDA table, FALSE = $table_name is not a WPDA table.
		 */
		public static function is_wpda_table( $real_table_name ) {

			if ( null === $real_table_name ) {
				return false;
			}

			global $wpdb;

			$phpdoc_supported_solution      = self::WPDA_TABLES; // DO NOT DELETE THIS TO MAKE THE CODE SIMPLER!!! (read).
			$phpdoc_supported_solution_wpdp = self::WPDP_TABLES; // DO NOT DELETE THIS TO MAKE THE CODE SIMPLER!!! (read).

			return
				(
					isset(
						$phpdoc_supported_solution[ substr(
							$real_table_name,
							strlen( $wpdb->prefix . self::get_option( self::OPTION_WPDA_PREFIX ) )
						) ]
					) &&
					(
						$wpdb->prefix . self::get_option( self::OPTION_WPDA_PREFIX ) ===
						substr( $real_table_name, 0, strlen( $wpdb->prefix . self::get_option( self::OPTION_WPDA_PREFIX ) ) )
					)
				) ||
				(
					isset(
						$phpdoc_supported_solution_wpdp[ substr(
							$real_table_name,
							strlen( $wpdb->prefix )
						) ]
					) &&
					(
						$wpdb->prefix === substr( $real_table_name, 0, strlen( $wpdb->prefix ) )
					)
				);

		}

		/**
		 * Save plugin option
		 *
		 * Saves a plugin option in $wpdb->options.
		 *
		 * @since   1.0.0
		 *
		 * @param array $option OPTION_ARRAYS (use class constants).
		 * @param mixed $value Value to be saved for $option. If null set to default.
		 */
		public static function set_option( $option, $value = null ) {

			try {

				if ( is_null( $value ) ) {
					$option_value = $option[1]; // Set option to default.
				} else {
					$option_value = $value; // Set option value.
				}

				update_option( $option[0], $option_value ); // Save option value in wp_options.

				self::$option_cache[ $option[0] ] = $option_value; // Save for re-use.

			} catch ( \Exception $e ) {

				die( 'ERROR: ' . esc_html( $e->errorMessage() ) );

			}

		}

		/**
		 * Simplify data type for simple forms
		 *
		 * Data types used in plugin a re simplified for simple form usage.
		 *
		 * @since   1.0.0
		 *
		 * @see \WPDataAccess\Simple_Form\WPDA_Simple_Form
		 *
		 * @param string $arg Data type as known to the MySQL database.
		 * @return string Simplified data type (mainly used to recognize when to use quotes).
		 */
		public static function get_type( $arg ) {

			switch ( $arg ) {

				case 'tinyint':
				case 'smallint':
				case 'mediumint':
				case 'int':
				case 'bigint':
				case 'float':
				case 'double':
				case 'decimal':
				case 'year':
					return 'number';

				case 'date':
				case 'datetime':
				case 'timestamp':
					return 'date';

				case 'time':
					return 'time';

				case 'enum':
					return 'enum';

				case 'set':
					return 'set';

				default:
					return 'string';

			}

		}

		/**
		 * Log a message in the database
		 *
		 * Use this method to log messages to the database.
		 *
		 * NOTE Don't use $wpdb->insert! You'll miss a lot of information...
		 *
		 * @since   2.0.7
		 *
		 * @param $log_id   string Id to identify/find logged data.
		 * @param $log_type string Possible values: 'FATAL', 'ERROR', 'WARN', 'INFO', 'DEBUG', 'TRACE'
		 * @param $log_msg  string Any text (max length 4096kb).
		 */
		public static function log( $log_id, $log_type, $log_msg ) {
			global $wpdb;

			$sql =
				$wpdb->prepare(
					'INSERT INTO ' . $wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) .
					'logging (log_time, log_id, log_type, log_msg) VALUES (now(), %s, %s, %s)'
					, $log_id
					, $log_type
					, $log_msg
				);
			$wpdb->query($sql);

		}

		/**
		 * Get user role
		 *
		 * @since   2.0.8
		 *
		 * @return mixed Current user roles or FALSE if not logged in.
		 */
		public static function get_current_user_roles() {
			if( is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( ! is_array( $user->roles ) ) {
					return ( array ) $user->roles;
				} else {
					return $user->roles;
				}
			} else {
				return false;
			}
		}

		/**
		 * Get user capability
		 *
		 * @since   2.0.8
		 *
		 * @return mixed Current users first capability or FALSE if not logged in.
		 */
		public static function get_current_user_capability() {
			if( is_user_logged_in() ) {
				$user    = wp_get_current_user();
				$allcaps = [];
				foreach ( $user->allcaps as $key => $val ) {
					array_push( $allcaps, $key );
				}
				return $allcaps[0];
			} else {
				return false;
			}
		}

		/**
		 * Convert memory value to integer.
		 *
		 * @since   2.0.8
		 *
		 * @param $memory_value string Memory value (eg 128M)
		 * @return integer Converted value in decimal
		 */
		public static function convert_memory_to_decimal( $memory_value ) {
			if (preg_match('/^(\d+)(.)$/', $memory_value, $matches)) {
				if ($matches[2] == 'G') {
					return $matches[1] * 1024 * 1024 * 1024;
				} else if ($matches[2] == 'M') {
					return $matches[1] * 1024 * 1024;
				} else if ($matches[2] == 'K') {
					return $matches[1] * 1024;
				}
			}
		}

	}

}
