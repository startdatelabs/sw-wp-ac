<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\User_Menu
 */

namespace WPDataAccess\User_Menu {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_User_Menu_Model
	 *
	 * @package WPDataAccess\User_Menu
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_User_Menu_Model {

		/**
		 * Base table name (without prefix)
		 */
		const WPDA_TABLE_NAME = 'menu_items';

        /**
         * Get data menu table name
         *
         * @return string Data menu table name
         */
        public static function get_menu_table_name() {

            global $wpdb;

            return $wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) . self::WPDA_TABLE_NAME;

        }

        /**
         * Check if table menu_items exists
         *
         * @since   1.0.0
         *
         * @return bool TRUE = table found
         */
        public static function table_menu_items_exists() {

            $wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', self::get_menu_table_name() );

            return $wpda_dictionary_exist->table_exists( false );

        }

        /**
		 * List of external menu items
		 *
		 * Used in {@see \WP_Data_Access_Admin::add_menu_my_tables()} to build user defined menus.
		 *
		 * Returns all external menu items. These menus are below a user defined menu.
		 *
		 * @since   1.0.0
		 *
		 * @return array List of menu items
		 */
		public static function list_external_menus() {

			global $wpdb;

			if ( self::table_menu_items_exists() ) {

				return $wpdb->get_results('
					select * 
					from   ' . self::get_menu_table_name() . ' 
					order by menu_name
				'); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			} else {

				return [];

			}

		}

		/**
		 * Check if before insert trigger exists
		 *
		 * @since   1.0.0
		 *
		 * @return bool TRUE = trigger found
		 */
		public static function triggers_menu_items_before_insert_exists() {

			global $wpdb;

			$schema_name  = $wpdb->dbname;
			$trigger_name =
				$wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) .
				self::WPDA_TABLE_NAME . '_before_insert';

            $wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', self::get_menu_table_name() );


            return $wpda_dictionary_exist->trigger_exists( $schema_name, $trigger_name );

		}

		/**
		 * Check if before update trigger exists
		 *
		 * @since   1.0.0
		 *
		 * @return bool TRUE = trigger found
		 */
		public static function triggers_menu_items_before_update_exists() {

			global $wpdb;

			$schema_name  = $wpdb->dbname;
			$trigger_name =
				$wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) .
				self::WPDA_TABLE_NAME . '_before_update';

            $wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', self::get_menu_table_name() );

            return $wpda_dictionary_exist->trigger_exists( $schema_name, $trigger_name );

		}

		/**
		 * Return number of menu items in repository
		 *
		 * @since   1.0.0
		 *
		 * @return int
		 */
		public static function count_menu_items_stored() {

			if ( ! self::table_menu_items_exists() ) {
				return 0;
			}

			global $wpdb;

			$query = 'SELECT count(*) AS noitems FROM ' . self::get_menu_table_name();

			$result = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			if ( 1 === $wpdb->num_rows ) {
				return $result[0]['noitems'];
			} else {
				return 0;
			}

		}

	}

}
