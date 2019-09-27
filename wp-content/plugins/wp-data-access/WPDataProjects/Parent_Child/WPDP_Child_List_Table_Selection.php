<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Parent_Child {

	use WPDataAccess\Utilities\WPDA_Message_Box;

	/**
	 * Class WPDP_Child_List_Table_Selection
	 *
	 * @package WPDataProjects\Parent_Child
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Child_List_Table_Selection extends WPDP_Child_List_Table {

		/**
		 * WPDP_Child_List_Table_Selection constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			$this->page_number_item_name = 'child_selection_page_number';
			$this->search_item_name      = 'slov';

			parent::__construct( $args );

			$this->bulk_actions_enabled = true;
			$this->show_view_link       = 'off';
			$this->allow_insert         = 'off';
			$this->allow_update         = 'off';
			$this->allow_delete         = 'off';
			$this->where_in             = 'not in';
		}

		/**
		 *
		 */
		public function show() {
			parent::show();
			?>
			<div style="padding-top:5px;padding-left:3px;">
				<a
					href="javascript:void(0)"
					onclick="javascript:location.href='?page=<?php echo esc_attr( $this->page ); ?><?php echo '' === $this->schema_name ? '' : '&schema_name=' . esc_attr( $this->schema_name ); ?>&table_name=<?php echo esc_attr( $this->table_name ); ?><?php echo esc_attr( $this->add_parent_args_to_back_button() ); ?><?php echo $this->page_number_link; ?>'"
					class="button button-secondary"
				>
					<?php echo esc_html__( 'Back To List', 'wp-data-access' ); ?>
				</a>
			</div>
			<?php
		}

		protected function add_parent_args() {
			parent::add_parent_args();
			?>
			<input type='hidden' name='list_table_selection' value='TRUE'>
			<?php
		}

		/**
		 * @return string
		 */
		protected function add_parent_args_to_back_button() {
			if ( isset( $_REQUEST['child_tab'] ) ) {
				$child_tab = sanitize_text_field( wp_unslash( $_REQUEST['child_tab'] ) ); // input var okay.
			} else {
				$child_tab = '';
			}
			$args = "&action=list&mode=edit&child_request=TRUE&child_tab=$child_tab";
			foreach ( $this->parent['parent_key'] as $parent_key ) {
				$args .= '&' . esc_attr( $parent_key ) . '=' . esc_attr( $this->parent['parent_key_value'][ $parent_key ] );
			}
			return $args;
		}

		/**
		 * @return array|string
		 */
		public function get_bulk_actions() {
			$actions = [
				'bulk-add' => __( 'Add selected rows', 'wp-data-access' ),
			];

			return $actions;
		}

		/**
		 *
		 */
		public function process_bulk_action() {
			if ( 'bulk-add' === $this->current_action() ) {
				if ( ! isset( $_REQUEST['bulk-selected'] ) ) { // input var okay.
					$msg = new WPDA_Message_Box(
						[
							'message_text' => __( 'Nothing selected', 'wp-data-access' ),
						]
					);
					$msg->box();

					return;
				}

				$bulk_rows = $_REQUEST['bulk-selected'];
				$no_rows   = count( $bulk_rows ); // # rows to be added.

				$rows_to_be_added = []; // Gonna hold rows to be added.

				for ( $i = 0; $i < $no_rows; $i++ ) {
					// Write "json" to named array. Need to strip slashes twice. Once for the normal conversion
					// and once extra for the pre-conversion of double quotes in method column_cb().
					$row_object = json_decode( stripslashes( stripslashes( $bulk_rows[ $i ] ) ), true );
					if ( $row_object ) {
						$j = 0; // Index used to build array.

						// Check all key columns.
						foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
							// Check if key is available.
							if ( ! isset( $row_object[ $key ] ) ) {
								wp_die( __( 'ERROR: Invalid URL', 'wp-data-access' ) );
							}

							// Write key value pair to array.
							$rows_to_be_added[ $i ][ $j ]['key']   = $key;
							$rows_to_be_added[ $i ][ $j ]['value'] = $row_object[ $key ];
							$j++;

						}
					}
				}

				// Looks like eveything is there. Add relationship.
				$no_key_cols            = count( $this->wpda_list_columns->get_table_primary_key() );
				$rows_succesfully_added = 0; // Number of rows succesfully added.
				$rows_with_errors       = 0; // Number of rows that could not be added.
				for ( $i = 0; $i < $no_rows; $i++ ) {
					// Prepare named array for delete operation.
					$next_row_to_be_added = [];

					$row_found = true;
					for ( $j = 0; $j < $no_key_cols; $j++ ) {
						if ( isset( $rows_to_be_added[ $i ][ $j ]['key'] ) ) {
							$next_row_to_be_added[ $rows_to_be_added[ $i ][ $j ]['key'] ] = $rows_to_be_added[ $i ][ $j ]['value'];
						} else {
							$row_found = false;
						}
					}

					if ( $row_found ) {
						if ( $this->add_row( $next_row_to_be_added ) ) {
							// Row(s) succesfully added.
							$rows_succesfully_added++;
						} else {
							// An error occured during the insert operation: increase error count.
							$rows_with_errors++;
						}
					} else {
						// An error occured during the insert operation: increase error count.
						$rows_with_errors++;
					}
				}

				// Inform user about the results of the operation.
				$message = '';
				if ( 1 === $rows_succesfully_added ) {
					$message = __( 'Row added', 'wp-data-access' );
				} elseif ( $rows_succesfully_added > 1 ) {
					$message = "$rows_succesfully_added " . __( 'rows added', 'wp-data-access' );
				}
				if ( '' !== $message ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text' => $message,
						]
					);
					$msg->box();
				}

				if ( $rows_with_errors > 0 ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'Not all rows have been added', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
				}
			}
		}

		/**
		 * @param $next_row_to_be_added
		 * @return mixed
		 */
		protected function add_row( $next_row_to_be_added ) {
			global $wpdb;

			$child_columns = [];
			$index         = 0;
			foreach ( $next_row_to_be_added as $row ) {
				$child_columns[ $this->child['relation_nm']['child_table_select'][ $index ] ] =
					$row;
				$child_columns[ $this->child['relation_nm']['child_table_where'][ $index ] ]  =
					$this->parent['parent_key_value'][ $this->parent['parent_key'][ $index ] ];
				$index++;
			}

			return $wpdb->insert( $this->child['relation_nm']['child_table'], $child_columns );
		}

		/**
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) { }

	}

}
