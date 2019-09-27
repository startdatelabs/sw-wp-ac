<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Parent_Child {

	/**
	 * Class WPDP_Child_List_Table_View
	 *
	 * @package WPDataProjects\Parent_Child
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Child_List_Table_View extends WPDP_Child_List_Table {

		/**
		 * WPDP_Child_List_Table_View constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			$args['allow_update'] = 'off';

			global $wpdb;
			$args['schema_name'] = $wpdb->dbname;

			parent::__construct( $args );

			$this->mode = 'view';
		}

		/**
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) { }

	}

}
