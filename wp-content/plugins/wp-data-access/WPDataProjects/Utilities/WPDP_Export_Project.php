<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Utilities {

	use WPDataAccess\WPDA;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;

	/**
	 * Class WPDP_Export_Project
	 *
	 * @package WPDataProjects\Utilities
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Export_Project {

		/**
		 * @var null
		 */
		protected $project_id = null;

		/**
		 * Main method to start export
		 *
		 * This method checks arguments and starts the export according to the arguments provided.
		 *
		 * @since   1.0.0
		 */
		public function export() {

			// Check access rights.
			if ( WPDA::get_option( WPDA::OPTION_BE_EXPORT_TABLES ) !== 'on' ) {
				// Exporting tables is not allowed.
				wp_die();
			}


			// Get arguments.
			$this->project_id = isset( $_REQUEST['project_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['project_id'] ) ) : null; // input var okay.

			if ( null !== $this->project_id ) {
				// Check if export is allowed.
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpdp-export-project-' . $this->project_id ) ) {
					wp_die();
				}
				$this->export_tables();
			} else {
				$this->wrong_arguments();
			}

		}

		/**
		 * Export project tables.
		 *
		 * @since   2.0.0
		 */
		protected function export_tables() {
			$this->header( $this->project_id );
			$this->db_begin();

			global $wpdb;
			$this->insert_rows( $wpdb->prefix . 'wpdp_project', 'where project_id = ' . $this->project_id );
			$this->insert_rows( $wpdb->prefix . 'wpdp_page', 'where project_id = ' . $this->project_id );
			// For now table options have to be exported seperately.
			// $this->insert_rows( $wpdb->prefix . 'wpdp_table' );

			$this->db_end();
		}

		/**
		 * Set export header (filename)
		 *
		 * @since   2.0.0
		 */
		protected function header( $project_id ) {
			header( 'Content-type: text/plain; charset=utf-8' );
			header( "Content-Disposition: attachment; filename=wpdp_project_$project_id.sql" );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
		}

		/**
		 * Set MySQL environment
		 *
		 * @since   2.0.0
		 */
		protected function db_begin() {
			global $wpdb;

			echo "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
			echo "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
			echo "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
			echo '/*!40101 SET NAMES ' . esc_attr( $wpdb->charset ) . " */;\n\n";
		}

		/**
		 * Write insert into statement
		 *
		 * @since   2.0.0
		 *
		 * @param string  $table_name Database table name.
		 * @param string  $where SQL where clause.
		 */
		public function insert_rows( $table_name, $where = '' ) {

			global $wpdb;

			$save_suppress_errors  = $wpdb->suppress_errors;
			$wpdb->suppress_errors = true;

			$query = "select * from $table_name $where";
			$rows  = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			if ( $wpdb->num_rows > 0 ) {
				// Prepare row export: get column names and data types.
				$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( '', $table_name );
				$table_columns     = $wpda_list_columns->get_table_columns();

				// Create array for fast column_name based access.
				$column_data_types = $this->column_data_types( $table_columns );

				// Exports rows.
				echo "--\n";
				echo '-- Export table `' . esc_attr( $table_name ) . "`\n";
				echo "--\n";

				echo 'INSERT INTO `' . esc_attr( $table_name ) . '` ';

				$process_first_row = true;
				foreach ( $rows[0] as $column_name => $column_value ) {
					if (
						! ( $wpdb->prefix . 'wpdp_project' === $table_name && 'project_id' === $column_name ) &&
						! ( $wpdb->prefix . 'wpdp_page' === $table_name && 'page_id' === $column_name )
					) {
						echo $process_first_row ? '(' : ', ';
						echo '`' . esc_attr( $column_name ) . '`';

						$process_first_row = false;
					}
				}

				echo ') VALUES ';

				$first_row = true;
				foreach ( $rows as $row ) {
					if ( ! $first_row ) {
						echo ',';
					} else {
						$first_row = false;
					}
					echo "\n(";

					$keys        = array_keys( $row );
					$last_column = end( $keys );
					foreach ( $row as $column_name => $column_value ) {
						if (
							! ( $wpdb->prefix . 'wpdp_project' === $table_name && 'project_id' === $column_name ) &&
							! ( $wpdb->prefix . 'wpdp_page' === $table_name && 'page_id' === $column_name )
						) {
							if ( $this->is_numeric( $column_data_types[ $column_name ] ) ) {
								if ( $wpdb->prefix . 'wpdp_page' === $table_name && 'project_id' === $column_name ) {
									echo '@PROJECT_ID';
								} else {
									echo esc_attr( $column_value );
								}
							} else {
								echo "'" . esc_sql( $column_value ) . "'";
							}
							if ( $column_name !== $last_column ) {
								echo ',';
							}
						}
					}

					echo ')';
				}

				echo ';';
				if ( $wpdb->prefix . 'wpdp_project' === $table_name ) {
					echo "\n";
					echo 'SET @PROJECT_ID = LAST_INSERT_ID();';
				}
				echo "\n\n";
			} else {
				// Empty table, nothing to export.
				echo "--\n";
				echo '-- No rows to export from empty table `' . esc_attr( $table_name ) . "`\n";
				echo "--\n\n";
			}

			$wpdb->suppress_errors = $save_suppress_errors;

		}

		/**
		 * Save column data types
		 *
		 * This method creates a named array for all column names of a table in form:
		 * 'column_name' => 'data_type'
		 *
		 * Argument $table_columns can be retrieved from WPDA_List_Columns->set_table_columns(). It must be prepared
		 * however with the idea that the instance of WPDA_List_Columns can be reused for best performance.
		 *
		 * In fact this is just an array conversion.
		 *
		 * @since   2.0.0
		 *
		 * @param array $table_columns Column_names and data_types of a table (table name not used here).
		 * @return array Named array 'column_name' => 'data_type' for all columns in the table.
		 */
		protected function column_data_types( $table_columns ) {

			$column_data_types = [];

			foreach ( $table_columns as $column_value ) {
				$column_data_types[ $column_value['column_name'] ] = $column_value['data_type'];
			}

			return $column_data_types;

		}

		/**
		 * Check if data type is numeric
		 *
		 * @since   2.0.0
		 *
		 * @param string $data_type Data type (simple).
		 * @return bool
		 */
		protected function is_numeric( $data_type ) {

			return ( 'number' === WPDA::get_type( $data_type ) );

		}

		/**
		 * Set back MySQL environment
		 *
		 * @since   2.0.0
		 */
		protected function db_end() {

			echo "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
			echo "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
			echo '/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;';

		}

		/**
		 * Processing on invalid arguments
		 *
		 * @since   2.0.0
		 */
		protected function wrong_arguments() {

			wp_die();

		}

	}

}
