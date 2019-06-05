<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Dictionary
 */

namespace WPDataAccess\Data_Dictionary {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Dictionary_Lists
	 *
	 * @package WPDataAccess\Data_Dictionary
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Dictionary_Lists {

		/**
		 * List of tables for setting pages
		 *
		 * Returns an array including all tables and views in the WordPress database.
		 *
		 * Do NOT use table access control here! This list is used in the settings forms and must show ALL tables in
		 * the WordPress database.
		 *
		 * @since   1.0.0
		 *
		 * @param boolean $show_views TRUE = show views, FALSE = hide views.
		 * @return array List of database tables (and views).
		 */
		public static function get_tables( $show_views = true ) {

			global $wpdb;

			if ( false === $show_views ) {
				$and = " and table_type != 'VIEW' ";
			} else {
				$and = '';
			}

			$query = "
				select table_name,
					   create_time,
					   table_rows
				  from information_schema.tables
				 where table_schema = '$wpdb->dbname'
				 $and
				 order by table_name
			";

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

        /**
         * List of columns for a specific table
         *
         * jQuery usage: action=get_columns
         *
         * @since   1.6.10
         *
         * @return array List of column for a specific table.
         */
        public static function get_columns() {

            if ( isset( $_REQUEST['table_name'] ) ) {
                $table_name = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.
            } else {
                return 'ERROR_NO_TABLE';
            }

            global $wpdb;

            $query = $wpdb->prepare('
				  SELECT column_name
					FROM information_schema.columns 
				   WHERE table_schema = %s
					 AND table_name   = %s
				',
                [
                    $wpdb->dbname,
                    $table_name,
                ]
            );
            $columns = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

            echo json_encode( $columns );

        }

		/**
		 * List of columns for a specific table
		 *
		 * @since   2.0.10
		 *
		 * @param $table_name
		 * @return Column in $table_name
		 */
		public static function get_table_columns( $table_name ) {

			global $wpdb;

			$query = $wpdb->prepare('
				  SELECT column_name
					FROM information_schema.columns 
				   WHERE table_schema = %s
					 AND table_name   = %s
				',
				[
					$wpdb->dbname,
					$table_name,
				]
			);

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

        /**
		 * List of tables for visual editor
		 *
		 * Returns a list of all available tables to support the visual editor wizard. The front-end settings are
		 * reflected in the list, preventing users to query tables to which no access is granted.
		 *
		 * @since   1.0.0
		 */
		public function get_tables_tinymce_listbox() {

			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			global $wpdb;

			$table_access          = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS );
			$table_access_selected = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS_SELECTED );

			$where = '';
			if ( 'hide' === $table_access ) {

				// Access to WordPress tables is denied.
				$where = " table_name not in ('" . implode( "','", $wpdb->tables( 'all', true ) ) . "')";

			} elseif ( 'select' === $table_access ) {

				// Only access to selected tables and views (wpda be/fe settings).
				$tables_selected = '';
				if ( '' !== $table_access_selected ) {
					foreach ( $table_access_selected as $key => $value ) {
						$tables_selected .= "'$value',";
					}
				}

				if ( '' !== $tables_selected ) {
					$where = ' table_name in (' . substr( $tables_selected, 0, -1 ) . ')';
				} else {
					// No access to tables and views.
					$where = ' 1=2';
				}
			}
			if ( '' !== $where ) {
				$where = " and $where";
			}

			$query = "
				select table_name
				  from information_schema.tables
				 where table_schema = '$wpdb->dbname'
				 $where
				 order by table_name
			";

			$rows = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			$listbox = [];
			foreach ( $rows as $row ) {
				$listbox[] = [
					'text'  => $row['table_name'],
					'value' => $row['table_name'],
				];
			}

			wp_send_json( $listbox );

			wp_die();

		}

		/**
		 * List of columns in a specified table for visual editor
		 *
		 * Returns the list of columns for a specified table to support the visual editor wizard. The front-end
		 * settings are reflected in the list. If a user is somehow able to hack the table name and requests columns
		 * for a table to which no access is granted, an empty array is returned.
		 *
		 * @since   1.0.0
		 */
		public function get_columns_tinymce_listbox() {

			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			if ( ! isset( $_REQUEST['table_name'] ) ) { // input var okay.

				wp_die();

			}

			$table_name = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.

			// Check for SQL injection and authorisation.
			$wpda_dictionary_checks = new WPDA_Dictionary_Exist( '', $table_name );
			if ( ! $wpda_dictionary_checks->table_exists( true, false ) ) {
				wp_die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
			}

			global $wpdb;

			$query = $wpdb->prepare('
				  SELECT column_name
					FROM information_schema.columns 
				   WHERE table_schema = %s
					 AND table_name   = %s
				',
				[
					$wpdb->dbname,
					$table_name,
				]
			);

			$rows = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			$listbox = [];
			foreach ( $rows as $row ) {
				$listbox[] = [
					'text'  => $row['column_name'],
					'value' => $row['column_name'],
				];
			}

			wp_send_json( $listbox );

			wp_die();

		}

		/**
		 * List of database schemas available to user
		 *
		 * @since   1.6.0
		 */
		public function get_db_schemas() {

			global $wpdb;

			$query = '
				  SELECT schema_name
					FROM information_schema.schemata 
				   ORDER BY schema_name
			';

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

		/**
		 * List of available engines
		 *
		 * @since   1.6.0
		 */
		public static function get_engines() {

			global $wpdb;

			$query = '
				  SELECT engine,
				         support
					FROM information_schema.engines
				   ORDER BY engine
			';

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

		/**
		 * List of available collations
		 *
		 * @since   1.6.0
		 */
		public static function get_collations() {

			global $wpdb;

			$query = '
				SELECT character_set_name, 
				       collation_name 
				FROM   information_schema.collations
				ORDER BY character_set_name, collation_name
			';

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

		/**
		 * Returns default collation
		 *
		 * @since   1.6.0
		 */
		public static function get_default_collation() {

			global $wpdb;

			$query = "
				SELECT default_character_set_name,
				       default_collation_name
				FROM   information_schema.schemata
				WHERE  schema_name = '$wpdb->dbname'
				GROUP BY schema_name
			";

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

	}

}
