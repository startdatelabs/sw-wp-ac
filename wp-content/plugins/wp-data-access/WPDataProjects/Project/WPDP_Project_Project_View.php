<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use WPDataProjects\Parent_Child\WPDP_Parent_List_View;

	/**
	 * Class WPDP_Project_Project_View
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Project_View extends WPDP_Parent_List_View {

		/**
		 * WPDP_Project_Project_View constructor.
		 *
		 * @param array $args
		 */
		public function __construct( array $args = [] ) {
			$args['parent_edit_form_class'] = 'WPDataProjects\\Project\\WPDP_Project_Project_Form';

			parent::__construct( $args );
		}

	}

}