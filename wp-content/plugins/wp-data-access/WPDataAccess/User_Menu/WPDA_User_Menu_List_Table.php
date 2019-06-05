<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataAccess\User_Menu {

	use WPDataAccess\List_Table\WPDA_List_Table;

	class WPDA_User_Menu_List_Table extends WPDA_List_Table {

		public function __construct( array $args = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'menu_id'         => __( 'Menu ID', 'wp-data-access' ),
				'menu_name'       => __( 'Menu Name', 'wp-data-access' ),
				'menu_table_name' => __( 'Table Name', 'wp-data-access' ),
				'menu_cpability'  => __( 'Menu Cpability', 'wp-data-access' ),
				'menu_slug'       => __( 'Menu Slug', 'wp-data-access' ),
			];

			parent::__construct( $args );
		}

		/**
		 * Overwrite method, add tab argument.
		 *
		 * @param string $add_param
		 */
		protected function add_header_button( $add_param = '' ) {
			parent::add_header_button( '&tab=menus' );
		}

		/**
		 * Overwrite method, add tab argument.
		 *
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) {
			parent::column_default_add_action( $item, $column_name, $actions );
			?>
			<script language="JavaScript">
				jQuery("#view_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="tab" value="menus">');
				jQuery("#edit_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="tab" value="menus">');
				jQuery("#delete_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="tab" value="menus">');
			</script>
			<?php
		}

		/**
		 * Overwrite method, add tab argument.
		 */
		public function show() {
			parent::show();
			?>
			<script language="JavaScript">
				jQuery(document).ready(function () {
					var wpda_main_form_action = jQuery('#wpda_main_form').attr('action') + '&tab=menus';
					jQuery('#wpda_main_form').attr('action', wpda_main_form_action);
					var wpda_main_form_action = jQuery('#form_import_table').attr('action') + '&tab=menus';
					jQuery('#form_import_table').attr('action', wpda_main_form_action);
				});
			</script>
			<?php
		}

	}

}