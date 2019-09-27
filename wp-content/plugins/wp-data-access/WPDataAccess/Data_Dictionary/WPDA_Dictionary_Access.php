<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Dictionary
 */

namespace WPDataAccess\Data_Dictionary {

	use WPDataAccess\List_Table\WPDA_List_Table;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Dictionary_Access
	 *
	 * Check if access to a given table is granted. The existence of the table name (and schema name for back-end) is
	 * not checked in this class. The class presumes that the table name (and schema name for back-end) is valid.
	 *
	 * The argument $done, which is used throughout the class, indicates whether the access check confirms the
	 * existence of the table or whether no access is granted anyway and therefor no further checks are needed. In
	 * some situations this saves us a query.
	 *
	 * For example:
	 *
	 * If table $wpdb->options is provided as an argument and access to WordPress tables is allowed we are done. If
	 * only selected tables are allowed and the table provided as an argument is either in or not in the list we done
	 * as well. When calling WPDA_Dictionary_Access functions check the return value as well as $done.
	 *
	 * @package WPDataAccess\Data_Dictionary
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Dictionary_Access {

		/**
		 * Check back-end table access
		 *
		 * Checks if access to a given schema and table is granted for back-end usage.
		 *
		 * The schema name must be provided as an argument. This argument is mainly added to support a clean access
		 * check for the data explorer view which uses the view TABLES from MySQL schema INFORMATION_SCHEMA (see
		 * {@see WPDA_List_Table::LIST_BASE_TABLE}). This is the only table/view outside the WordPress schema to
		 * which access is granted.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::LIST_BASE_TABLE
		 *
		 * @param string  $schema_name Schema name in which the table or view is located.
		 * @param string  $table_name Table or view name.
		 * @param boolean $done TRUE = no futher checks needed, FALSE = still need to check table name.
		 * @return bool TRUE = access granted, FALSE = access denied.
		 */
		public static function check_table_access_backend( $schema_name, $table_name, &$done ) {

			if ( WPDA_List_Table::LIST_BASE_TABLE === $schema_name . '.' . $table_name ) {
				// Always grant access to table list.
				$done = true; // No further checks needed.
				return true;
			}

			global $wpdb;
			if ( $schema_name === $wpdb->dbname && WPDA::is_wpda_table( $table_name ) ) {
				// Always grant access to WPDA table's in the back-end.
				$done = true; // No further checks needed.
				return true;
			}

			$table_access          = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS );
			$table_access_selected = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );

			return WPDA_Dictionary_Access::check_table_access( $table_name, $table_access, $table_access_selected, $done );

		}

		/**
		 * Check tables access
		 *
		 * Checks if access to a given table is granted for back-end or front-end usage. Whether the check is performed
		 * for the back-end or front-end depends on the arguments $table_access and $table_access_selected.
		 *
		 * The schema name is not reflected in this check. It presumed that the schema name if the schema in which
		 * WordPress is installed.
		 *
		 * This function is code which is shared between {@see WPDA_Dictionary_Access::check_table_access_backend()}
		 * and {@see WPDA_Dictionary_Access::check_table_access_frontend()}.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_Dictionary_Access::check_table_access_backend()
		 * @see WPDA_Dictionary_Access::check_table_access_frontend()
		 *
		 * @param string  $table_name Table or view name.
		 * @param string  $table_access Option value for table access as stored in wp_options.
		 * @param string  $table_access_selected Option value for tables selected access as stored in wp_options.
		 * @param boolean $done TRUE = no futher checks needed, FALSE = still need to check table name.
		 * @return bool TRUE = access granted, FALSE = access denied.
		 */
		protected static function check_table_access( $table_name, $table_access, $table_access_selected, &$done ) {

			if ( 'hide' === $table_access ) {

				global $wpdb;

				// No access to WordPress tables: check if the requested table is a WordPress table.
				$wp_tables = $wpdb->tables( 'all', true );
				if ( isset( $wp_tables[ substr( $table_name, strlen( $wpdb->prefix ) ) ] ) &&
					$wp_tables[ substr( $table_name, strlen( $wpdb->prefix ) ) ] === $table_name
				) {

					// WordPress table: deny access.
					$done = true; // No further checks needed.
					return false;

				} else {

					// Non WordPress table: access granted.
					$done = false; // Still need to check if table exists.
					return true;

				}
			} elseif ( 'select' === $table_access ) {

				// Only access to selected tables and views (front-end settings).
				if ( '' !== $table_access_selected ) {
					foreach ( $table_access_selected as $key => $value ) {
						if ( $table_name === $value ) {
							// Access to this table or view is granted.
							$done = true; // No further checks needed.
							return true;
						}
					}
				}

				// No access.
				$done = true; // No further checks needed.
				return false;

			} else {

				// Access granted to all tables and views.
				$done = false; // Still need to check if table exists.
				return true;

			}

		}

		/**
		 * Check front-end table access
		 *
		 * Checks if access to a given table is granted for front-end usage.
		 *
		 * The schema name is not reflected in this check. It presumed that the schema name if the schema in which
		 * WordPress is installed.
		 *
		 * @since   1.0.0
		 *
		 * @param string  $table_name Table or view name.
		 * @param boolean $done TRUE = no futher checks needed, FALSE = still need to check table name.
		 * @return bool TRUE = access granted, FALSE = access denied.
		 */
		public static function check_table_access_frontend( $table_name, &$done ) {

			$table_access          = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS );
			$table_access_selected = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS_SELECTED );

			return WPDA_Dictionary_Access::check_table_access( $table_name, $table_access, $table_access_selected, $done );

		}

	}

}
