<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Data_Dictionary {

	use \WPDataAccess\Data_Dictionary\WPDA_List_Columns;
	use \WPDataProjects\Project\WPDP_Project_Design_Table_Model;

	/**
	 * Class WPDP_List_Columns
	 *
	 * Taken from WPDA_List_Columns. This class adds extra functionality for Data Projects. Column headers
	 * defined in 'Manage Table Options' are taken into account in this class.
	 *
	 * @package WPDataProjects\Data_Dictionary
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_List_Columns extends WPDA_List_Columns {

		/**
		 * @var label_type
		 */
		protected $label_type;

		/**
		 * @var wpdp_project_design_table_model
		 */
		protected $wpdp_project_design_table_model;

		/**
		 * WPDP_List_Columns constructor.
		 *
		 * @param $schema_name
		 * @param $table_name
		 * @param $label_type
		 */
		public function __construct( $schema_name, $table_name, $label_type ) {
			$this->label_type                      = $label_type;
			$this->wpdp_project_design_table_model = new WPDP_Project_Design_Table_Model();

			parent::__construct( $schema_name, $table_name );
		}

		/**
		 * @param string $column_name
		 * @return string
		 */
		public function get_column_label( $column_name ) {
			if ( isset( $this->table_column_headers[ $column_name ] ) ) {
				return $this->table_column_headers[ $column_name ];
			} else {
				return parent::get_column_label( $column_name );
			}
		}

		/**
		 *
		 */
		protected function set_table_columns() {
			parent::set_table_columns();

			// Reorder table columns according to sequence defined by user.
			$table_columns_sorted = [];
			if ( ! isset( $this->table_columns ) ) {
				wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			$column_options = $this->wpdp_project_design_table_model->get_column_options( $this->table_name, $this->label_type );
			if ( null !== $column_options ) {
				foreach ( $column_options as $column_option ) {
					foreach ( $this->table_columns as $table_column ) {
						if ( $table_column['column_name'] === $column_option->column_name ) {
							if ( 'on' === $column_option->show ) {
								$table_columns_sorted[] = $table_column;
							} elseif ( 'tableform' === $this->label_type ) {
								$table_column['show']   = false;
								$table_columns_sorted[] = $table_column;
							}
						}
					}
				}
				$this->table_columns = $table_columns_sorted;
			}
		}

		/**
		 *
		 */
		protected function set_table_column_headers() {
			if ( ! isset( $this->table_columns ) ) {
				wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			$column_options = $this->wpdp_project_design_table_model->get_column_options( $this->table_name, $this->label_type );
			if ( null === $column_options ) {
				parent::set_table_column_headers();
			} else {
				$primary_nr                 = 0;
				$this->table_column_headers = [];

				foreach ( $this->table_columns as $table_column ) {
					$index = $this->get_array_index( $column_options, $table_column['column_name'] );

					if ( isset( $column_options[ $index ] ) && isset( $column_options[ $index ]->label ) ) {
						$label = $column_options[ $index ]->label;
					} else {
						$label = $this->get_column_label( $table_column['column_name'] );
					}

					if ( $this->is_primary_key_column( $table_column['column_name'] ) ) {
						$key_text = __( 'key', 'wp-data-access' );
						if ( count( $this->table_primary_key ) > 1 ) {
							$label .= " ($key_text #" . ( ++$primary_nr ) . ')';
						} else {
							$label .= " ($key_text)";
						}
					}

					if ( isset( $column_options[ $index ] ) && isset( $column_options[ $index ]->show ) ) {
						if ( 'on' === $column_options[ $index ]->show || 'tableform' === $this->label_type ) {
							$this->table_column_headers[ $table_column['column_name'] ] = $label;
						}
					}
				}
			}
		}

		/**
		 * @param $column_options
		 * @param $column_name
		 * @return int
		 */
		private function get_array_index( $column_options, $column_name ) {
			$index = 0;
			foreach ( $column_options as $column_option ) {
				if ( isset( $column_option->column_name ) && $column_option->column_name === $column_name ) {
					return $index;
				}
				$index++;
			}
		}

	}

}