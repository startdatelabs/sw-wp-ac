<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use WPDataAccess\List_Table\WPDA_List_Table;

	/**
	 * Class WPDP_Project_Table_List
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Table_List extends WPDA_List_Table {

		/**
		 * @var array|null
		 */
		protected $all_tables    = null;
		/**
		 * @var null
		 */
		protected $sorted_tables = null;

		/**
		 * WPDP_Project_Table_List constructor.
		 *
		 * @param array $args
		 */
		public function __construct( array $args = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'wpda_table_name'     => __( 'Table Name', 'wp-data-access' ),
				'table_found'         => __( 'Table Created', 'wp-data-access' ),
				'table_has_relations' => __( 'Table Has Relations', 'wp-data-access' ),
				'table_rows'          => __( '#Rows', 'wp-data-access' ),
			];
			$args['title']          = '';
			$args['allow_import']   = 'off';
			$args['allow_insert']   = 'off';

			$this->all_tables = \WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists::get_tables( true ); // Get all tables first.
			foreach ( $this->all_tables as $table) { // Sort tables for quick access to details.
				$this->sorted_tables[$table['table_name']] = [
					'create_time' => $table['create_time'],
					'table_rows'  => $table['table_rows'],
				];
			}

			parent::__construct( $args );
		}

		/**
		 *
		 */
		protected function add_header_button( $add_param = '' ) {
			?>
			<form
					method="post"
					action="?page=<?php echo esc_attr( $this->page ); ?>&tab=tables"
					style="display: inline-block; vertical-align: unset;"
			>
				<input type="hidden" name="action" value="reverse_engineering">
				<div id="no_repository_buttons" style="display:block">
					<button class="page-title-action"
							onclick="jQuery('#no_repository_buttons').hide(); jQuery('#add_table_to_repository').show(); return false;">
						<?php echo esc_html__( 'Add Table To Repository', 'wp-data-access' ); ?>
					</button>
				</div>
				<div id="add_table_to_repository" style="display:none">
					<select name="wpda_table_name">
						<?php
						foreach ( $this->all_tables as $key => $value ) {
							?>
							<option value="<?php echo esc_attr( $value['table_name'] ); ?>"><?php echo esc_attr( $value['table_name'] ); ?></option>
							<?php
						}
						?>
					</select>
					<input type="submit"
						   value="<?php echo esc_html__( 'Add Selected Table To Repository', 'wp-data-access' ); ?>"
						   class="button button-secondary">
					<button class="button button-secondary"
							onclick="jQuery('#no_repository_buttons').show(); jQuery('#add_table_to_repository').hide(); return false;">
						<?php echo esc_html__( 'Cancel', 'wp-data-access' ); ?>
					</button>
				</div>
			</form>
			<?php
		}

		/**
		 * @return array
		 */
		public function get_columns() {
			$columns = [];

			if ( $this->bulk_actions_enabled ) {
				$columns = [ 'cb' => '<input type="checkbox" />' ];
			}

			return array_merge( $columns, $this->column_headers );
		}

		/**
		 * @param array  $item
		 * @param string $column_name
		 * @return mixed|string
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'table_found':
					if ( isset( $this->sorted_tables[ $item['wpda_table_name'] ] ) ) {
						return $this->sorted_tables[ $item['wpda_table_name'] ]['create_time'];
					} else {
						return esc_html__( '--- NOT FOUND ---', 'wp-data-access' );
					}
					break;
				case 'table_has_relations':
					$table_design = json_decode( $item['wpda_table_design'] );
					if ( isset( $table_design->relationships ) ) {
						return 'Yes';
					} else {
						return 'No';
					}
					break;
				case 'table_rows':
					if ( isset( $this->sorted_tables[ $item['wpda_table_name'] ] ) ) {
						return $this->sorted_tables[ $item['wpda_table_name'] ]['table_rows'];
					} else {
						return '-';
					}
					break;
				default:
					return parent::column_default( $item, $column_name );
			}
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
				jQuery("#view_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="tab" value="tables">');
				jQuery("#edit_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="tab" value="tables">');
				jQuery("#delete_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="tab" value="tables">');
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
				var wpda_main_form_action = jQuery('#wpda_main_form').attr('action') + '&tab=tables';
				jQuery('#wpda_main_form').attr('action', wpda_main_form_action);
			</script>
			<?php
		}

	}

}
