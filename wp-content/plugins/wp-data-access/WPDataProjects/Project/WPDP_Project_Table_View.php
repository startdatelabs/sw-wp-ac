<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use \WPDataAccess\List_Table\WPDA_List_View;

	/**
	 * Class WPDP_Project_Table_View
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Table_View extends WPDA_List_View {

		/**
		 *
		 */
		public function show() {
			if ( 'reconcile' === $this->action || 'reverse_engineering' === $this->action ) {
				$this->display_edit_form();
			} else {
				parent::show();
			}
		}

	}

}