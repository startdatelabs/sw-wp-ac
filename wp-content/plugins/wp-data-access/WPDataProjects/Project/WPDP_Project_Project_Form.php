<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use WPDataProjects\Parent_Child\WPDP_Parent_Form;

	/**
	 * Class WPDP_Project_Project_Form
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Project_Form extends WPDP_Parent_Form {

		/**
		 * WPDP_Project_Project_Form constructor.
		 *
		 * @param       $schema_name
		 * @param       $table_name
		 * @param       $wpda_list_columns
		 * @param array $args
		 * @param array $relationship
		 */
		public function __construct( $schema_name, $table_name, $wpda_list_columns, array $args = [], array $relationship = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'project_id'          => __( 'Project ID', 'wp-data-access' ),
				'project_name'        => __( 'Project Name', 'wp-data-access' ),
				'project_description' => __( 'Project Description', 'wp-data-access' ),
				'add_to_menu'         => __( 'Add To Menu', 'wp-data-access' ),
				'menu_name'           => __( 'Menu Name', 'wp-data-access' ),
				'project_sequence'    => __( 'Seq#', 'wp-data-access' ),
			];

			$args['edit_form_class'] = 'WPDataProjects\\Project\\WPDP_Project_Page_Form';

			if ( isset( $args['mode'] ) ) {
				$mode = $args['mode'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			if ( 'view' === $mode ) {
				$args['list_form_class'] = 'WPDataProjects\\Project\\WPDP_Project_Page_List_View';
			} else {
				$args['list_form_class'] = 'WPDataProjects\\Project\\WPDP_Project_Page_List';
			}

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args, $relationship );
		}

	}

}