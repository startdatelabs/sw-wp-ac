<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use WPDataProjects\Parent_Child\WPDP_Child_List_Table;

	/**
	 * Class WPDP_Project_Page_List
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Page_List extends WPDP_Child_List_Table {

		/**
		 * WPDP_Project_Page_List constructor.
		 *
		 * @param array $args
		 */
		public function __construct( array $args = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'project_id'        => __( 'Project ID', 'wp-data-access' ),
				'page_id'           => __( 'Page ID', 'wp-data-access' ),
				'add_to_menu'       => __( 'Add To Menu', 'wp-data-access' ),
				'page_name'         => __( 'Menu Name', 'wp-data-access' ),
				'page_type'         => __( 'Type', 'wp-data-access' ),
				'page_table_name'   => __( 'Table Name', 'wp-data-access' ),
				'page_mode'         => __( 'Mode', 'wp-data-access' ),
				'page_allow_insert' => __( 'Allow insert?', 'wp-data-access' ),
				'page_allow_delete' => __( 'Allow delete?', 'wp-data-access' ),
				'page_content'      => __( 'Post', 'wp-data-access' ),
				'page_title'        => __( 'Title', 'wp-data-access' ),
				'page_subtitle'     => __( 'Subtitle', 'wp-data-access' ),
				'page_role'         => __( 'Role', 'wp-data-access' ),
				'page_sequence'     => __( 'Seq#', 'wp-data-access' ),
			];

			parent::__construct( $args );
		}

		/**
		 * @return array
		 */
		public function get_hidden_columns() {

			return [
				'page_allow_insert',
				'page_allow_delete',
				'page_subtitle',
				'page_where',
			];

		}

		/**
		 * @param array  $item
		 * @param string $column_name
		 * @return mixed|string
		 */
		public function column_default( $item, $column_name ) {
			if (
				'static' === $item['page_type'] &&
				(
					'page_table_name' === $column_name ||
					'page_mode' === $column_name ||
					'page_allow_insert' === $column_name ||
					'page_allow_delete' === $column_name
				)
			) {
				return '';
			} else {
				if (
					'static' !== $item['page_type'] &&
					(
						'page_content' === $column_name
					)
				) {
					return '';
				} else {
					return parent::column_default( $item, $column_name );
				}
			}
		}

	}

}