<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Tables
 */

namespace WPDataAccess\Data_Tables {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Data_Tables
	 *
	 * @package WPDataAccess\Data_Tables
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Data_Tables {

		/**
		 * Generate jQuery DataTable code
		 *
		 * Table and column names provided are checked for existency and access to prevent hacking the DataTable code
		 * and SQL injection.
		 *
		 * @since   1.0.0
		 *
		 * @param string $table_name Database table name.
		 * @param string $column_names Comma seperated list of column names.
		 * @param string $responsive Yes = responsive mode, No = No responsive mode.
		 * @param int    $responsive_cols Number of columns to be displayd in responsive mode.
		 * @param string $responsive_type Modal, Collaped or Expanded (only if $responsive = Yes).
		 * @param string $responsive_icon Yes = show icon, No = do not show icon (only if $responsive = Yes).
		 */
		public function show( $table_name, $column_names, $responsive, $responsive_cols, $responsive_type, $responsive_icon ) {
			if ( ! is_numeric( $responsive_cols ) ) {
				$responsive_cols = 0;
			}

			if ( strpos( $table_name, '.' ) ) {
				// Querying tables in other schema's is not allowed!
				echo '<p>' . esc_html__( 'ERROR: Table not found', 'wp-data-access' ) . '</p>';
				return;
			}

			// Check if table exists (prevent sql injection).
			$wpda_dictionary_checks = new WPDA_Dictionary_Exist( '', $table_name );
			if ( ! $wpda_dictionary_checks->table_exists( true, false ) ) {
				// Table not found.
				echo '<p>' . esc_html__( 'ERROR: Invalid table name or not authorized', 'wp-data-access' ) . '</p>';
				return;
			}

			// Set columns to be queried.
			if ( '*' === $column_names ) {
				// Get all column names from table.
				$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( '', $table_name );
				$columns           = []; // Create column ARRAY ***.
				foreach ( $wpda_list_columns->get_table_columns() as $column ) {
					$columns[] = $column['column_name'];
				}
			} else {
				$columns = explode( ',', $column_names ); // Create column ARRAY.
				// Check if columns exist (prevent sql injection).
				foreach ( $columns as $column ) {
					if ( ! $wpda_dictionary_checks->column_exists( $column ) ) {
						// Column not found.
						echo esc_html__( 'ERROR: Column', 'wp-data-access' ) . ' ' . esc_attr( $column ) . ' ' . esc_html__( 'not found', 'wp-data-access' );
						return;
					}
				}
			}

			?>

			<table id="<?php echo esc_attr( $table_name ); ?>" class="display nowrap" cellspacing="0">
				<thead>
				<?php $this->show_header( $columns, $responsive, $responsive_cols ); ?>
				</thead>
				<tfoot>
				<?php $this->show_header( $columns, $responsive, $responsive_cols ); ?>
				</tfoot>
			</table>

			<script language="javascript">
				jQuery(document).ready(function () {
					wpda_datatables_ajax_call(
						"<?php echo esc_attr( $table_name ); ?>",
						"<?php echo esc_attr( $column_names ); ?>",
						"<?php echo esc_attr( $responsive ); ?>",
						"<?php echo esc_attr( $responsive_type ); ?>",
						"<?php echo esc_attr( $responsive_icon ); ?>"
					);
				});
			</script>

			<?php

		}

		/**
		 * Show table header (footer as well)
		 *
		 * @param string $columns Comma seperated list of column names.
		 * @param string $responsive Yes = responsive mode, No = No responsive mode.
		 * @param int    $responsive_cols Number of columns to be displayd in responsive mode.
		 */
		protected function show_header( $columns, $responsive, $responsive_cols ) {

			$count = 0;
			foreach ( $columns as $column ) {
				if ( 'yes' !== $responsive ) {
					$class = '';
				} else {
					if ( $count >= 1 && $count >= $responsive_cols ) {
						$class = 'none';
					} else {
						$class = 'all';
					}
				}

				?>

				<th class="<?php echo esc_attr( $class ); ?>"><?php echo esc_attr( $column ); ?></th>

				<?php

				$count++;
			}

		}

		/**
		 * Performs jQuery DataTable query
		 *
		 * Once a jQuery DataTable is build using {@see WPDA_Data_Tables::show()}, the DataTable is filled according
		 * to the search criteria and pagination settings on the Datable. The query is performed through this function.
		 * The query result is returned (echo) in JSON format. Table and column names are checked for existence and
		 * access to prevent hacking the DataTable code and SQL injection.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_Data_Tables::show()
		 */
		public function get_data() {

			global $wpdb;

			if ( ! isset( $_REQUEST['table_name'] ) ) { // input var okay.
				// Table name must be set!
				wp_die();
			} else {
				// Set table name.
				$table_name = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.

				if ( strpos( $table_name, '.' ) ) {
					// Querying tables in other schema's is not allowed!
					wp_die();
				}

				// Check if table exists (prevent sql injection).
				$wpda_dictionary_checks = new WPDA_Dictionary_Exist( '', $table_name );
				if ( ! $wpda_dictionary_checks->table_exists( true, false ) ) {
					// Table not found.
					wp_die();
				}

				// Get all column names from table (must be comma seperated string).
				$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( '', $table_name );
				$table_columns     = $wpda_list_columns->get_table_columns();

				// Set columns to be queried.
				$columns = '*';
				if ( isset( $_REQUEST['columns'] ) ) {
					// Use columns from shortcode arguments.
					$columns = str_replace( ' ', '', sanitize_text_field( wp_unslash( $_REQUEST['columns'] ) ) ); // input var okay.
				}

				if ( '*' === $columns ) {
					// Get all column names from table (must be comma seperated string).
					$column_array = [];
					foreach ( $table_columns as $column ) {
						$column_array[] = $column['column_name'];
					}
					$columns = implode( ',', $column_array );
				} else {
					// Check if columns exist (prevent sql injection).
					$wpda_dictionary_checks = new WPDA_Dictionary_Exist( '', $table_name );
					$column_array           = explode( ',', $columns );
					foreach ( $column_array as $column ) {
						if ( ! $wpda_dictionary_checks->column_exists( $column ) ) {
							// Column not found.
							wp_die();
						}
					}
				}

				// Set pagination values.
				$offset = 0;
				if ( isset( $_REQUEST['start'] ) ) {
					$offset = sanitize_text_field( wp_unslash( $_REQUEST['start'] ) ); // input var okay.
				}
				$limit = 10; // jQuery Datatables default.
				if ( isset( $_REQUEST['length'] ) ) {
					$limit = sanitize_text_field( wp_unslash( $_REQUEST['length'] ) ); // input var okay.
				}

				// Set order by.
				$orderby = '';
				if ( isset( $_REQUEST['order'] ) && is_array( $_REQUEST['order'] ) ) { // input var okay.
					$orderby_columns = [];
					$orderby_args = [];
					// Sanitize argument array and write result to termporary sanatizes array for processing:
					foreach ( $_REQUEST['order'] as $order_column ) { // input var okay.
						$orderby_args[] = [
							'column' => sanitize_text_field( wp_unslash( $order_column['column'] ) ),
							'dir'	 => sanitize_text_field( wp_unslash( $order_column['dir'] ) ),
						];
					}
					foreach ( $orderby_args as $order_column ) { // input var okay.
						$column_index      = $order_column['column'];
						$column_name       = $column_array[ $column_index ];
						$column_dir        = $order_column['dir'];
						$orderby_columns[] = "`$column_name` $column_dir";
					}
					$orderby = 'order by ' . implode( ',', $orderby_columns );
				}

				// Add search criteria.
				$where = '';
				if ( isset( $_REQUEST['search'] ) && '' !== sanitize_text_field( wp_unslash( $_REQUEST['search']['value'] ) ) ) { // input var okay.
					$search_value  = '%' . sanitize_text_field( wp_unslash( $_REQUEST['search']['value'] ) ) . '%'; // input var okay.
					$where_columns = [];
					foreach ( $table_columns as $column ) {
						if ( WPDA::get_type( $column['data_type'] ) === 'string' ) {
							// Search is only performed on column of type string.
							// Use prepare to prevent SQL injection.
							$where_columns[] = $wpdb->prepare( "`{$column['column_name']}` like '%s'", $search_value ); // WPCS: unprepared SQL OK.
						}
					}
					$where = 'where ' . implode( ' or ', $where_columns );
				}

				// Execute query.
				$column_array      = explode( ',', $columns );
				$columns_backticks = '`' . implode( '`,`', $column_array ) . '`';
				$query = "select $columns_backticks from `$table_name` $where $orderby limit $limit offset $offset";
				$rows  = $wpdb->get_results( $query, 'ARRAY_N' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

				// Count rows in table.
				$query       = "select count(*) from `$table_name`";
				$count_rows  = $wpdb->get_results( $query, 'ARRAY_N' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				$count_table = $count_rows[0][0]; // Number of rows in table.

				if ( '' !== $where ) {
					// Count rows in selection (only necessary if a search criteria was entered).
					$query                = "select count(*) from `$table_name` $where";
					$count_rows_filtered  = $wpdb->get_results( $query, 'ARRAY_N' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
					$count_table_filtered = $count_rows_filtered[0][0]; // Number of rows in table.
				} else {
					// No search criteria entered: # filtered rows = # table rows.
					$count_table_filtered = $count_table;
				}

				// Convert query result to jQuery Datatables object.
				$obj                  = (object) null;
				$obj->draw            = isset( $_REQUEST['draw'] ) ? intval( $_REQUEST['draw'] ) : 0;
				$obj->recordsTotal    = $count_table;
				$obj->recordsFiltered = $count_table_filtered;
				$obj->data            = $rows;

				// Convert object to json. jQuery Datatables needs json format.
				echo json_encode( $obj );
			}

			wp_die();

		}

	}

}
