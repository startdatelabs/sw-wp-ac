<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Dictionary
 */

namespace WPDataAccess\Data_Dictionary {

	/**
	 * Class WPDA_List_Columns
	 *
	 * @package WPDataAccess\Data_Dictionary
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_List_Columns {

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name;

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * Columns of $this->table_name
		 *
		 * @var array
		 */
		protected $table_columns = [];

		/**
		 * Primary key columns of $this->table_name
		 *
		 * @var array
		 */
		protected $table_primary_key = [];

		/**
		 * Primary key columns of $this->table_name (named)
		 *
		 * @var array
		 */
		protected $table_primary_key_check = [];

		/**
		 * Auto increment column name of $this->table_name or false
		 *
		 * @var bool|string
		 */
		protected $auto_increment_column_name = false;

		/**
		 * Column headers for $this->table_name
		 *
		 * @var array
		 */
		protected $table_column_headers = [];

		/**
		 * WPDA_List_Columns constructor
		 *
		 * @since   1.0.0
		 *
		 * @param string $schema_name Database schema name.
		 * @param string $table_name Database table name.
		 */
		public function __construct( $schema_name, $table_name ) {

		    // Set schema and table name.
			if ( '' === $schema_name ) {
				global $wpdb;
				$this->schema_name = $wpdb->dbname;
			} else {
                $this->schema_name = $schema_name;
            }
			$this->table_name = $table_name;

			if ( '' !== $table_name ) {
				// Get dictionary information.
				$this->set_table_columns();
				$this->set_table_primary_key();
				$this->set_table_column_headers();
			}

		}

		/**
		 * Get label of specified column
		 *
		 * Returns the label of a column according to a pre defined format. Call must contain column name.
		 * Column must be in $this->table_name.
		 *
		 * @since   1.0.0
		 *
		 * @param string $column_name Column name as defined in the data dictionary.
		 * @return string Label for $column_name.
		 */
		public function get_column_label( $column_name ) {

            return ucfirst( str_replace( '_', ' ', $column_name ) );

		}

		/**
		 * Check if column is part of primary key
		 *
		 * @since   1.0.0
		 *
		 * @param string $column_name Column name as defined in the data dictionary.
		 * @return bool TRUE = column is part of primary key, FALSE = column is not part of primary key.
		 */
		public function is_primary_key_column( $column_name ) {

			return ( isset( $this->table_primary_key_check[ $column_name ] ) );

		}

		/**
		 * Get columns
		 *
		 * @since   1.0.0
		 *
		 * @return array Column of $this->table_name.
		 */
		public function get_table_columns() {

			return $this->table_columns;

		}

		/**
		 * Set table columns
		 *
		 * Column info is taken from the MySQL data dictionary. For each column in $this->table_name the following
		 * column info is stored:
		 * + Column name
		 * + Data type (MySQL data type)
		 * + Extra (needed to find auto increment columns)
		 * + Column type (needed for columns with data type enum: column type holds allowed values)
		 * + Null values allowed?
		 *
		 * @since   1.0.0
		 */
		protected function set_table_columns() {

			global $wpdb;

			$query = $wpdb->prepare(
				'
              SELECT column_name, 
                     data_type,
                     extra,
                     column_type,
                     is_nullable,
                     column_default
                FROM information_schema.columns 
               WHERE table_schema = %s
                 AND table_name   = %s
            ',
				[
					$this->schema_name,
					$this->table_name,
				]
			);

			$this->table_columns = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

		/**
		 * Get primary key columns
		 *
		 * @since   1.0.0
		 *
		 * @return array Primary key columns of $this->table_name.
		 */
		public function get_table_primary_key() {

			return $this->table_primary_key;

		}

		/**
		 * Set primary key columns
		 *
		 * Primary key columns are taken from the MySQL data dictionary.
		 *
		 * @since   1.0.0
		 */
		protected function set_table_primary_key() {

			global $wpdb;

			$query = $wpdb->prepare(
				"
              SELECT kcu.column_name
                FROM information_schema.table_constraints tc
                JOIN information_schema.key_column_usage kcu
               USING (constraint_name, table_schema, table_name)
               WHERE tc.constraint_type = 'PRIMARY KEY'
                 AND tc.table_schema    = %s
                 AND tc.table_name      = %s
              ORDER BY kcu.ordinal_position
            ",
				[
					$this->schema_name,
					$this->table_name,
				]
			);

			$result = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			$this->table_primary_key       = []; // Write result in simple array for loops.
			$this->table_primary_key_check = []; // Write result in named array for quick checks.
			foreach ( $result as $row ) {
				$this->table_primary_key[]                            = $row['column_name'];
				$this->table_primary_key_check[ $row['column_name'] ] = true;

				foreach ( $this->table_columns as $table_column ) {
					if ( $table_column['column_name'] === $row['column_name'] &&
						'auto_increment' === $table_column['extra']
					) {
						// Save auto_increment column name.
						$this->auto_increment_column_name = $row['column_name'];
					}
				}
			}

		}

		/**
		 * Get column headers
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_table_column_headers() {

			return $this->table_column_headers;

		}

		/**
		 * Set column headers info
		 *
		 * For now column headers are defined equal to their names. If a column is part of the primary key, this is
		 * reflected in the column header.
		 *
		 * @since   1.0.0
		 */
		protected function set_table_column_headers() {

			if ( ! isset( $this->table_columns ) ) {
				wp_die( esc_html__( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			$primary_nr                 = 0;
			$this->table_column_headers = [];
			foreach ( $this->table_columns as $key => $value ) {

                $label = $this->get_column_label( $value['column_name'] );

                if ( $this->is_primary_key_column( $value['column_name'] ) ) {
                    $key_text = __( 'key', 'wp-data-access' );
                    if ( count( $this->table_primary_key ) > 1 ) {
                        $label .= " ($key_text #" . ( ++$primary_nr ) . ')';
                    } else {
                        $label .= " ($key_text)";
                    }
                }

				$this->table_column_headers[ $value['column_name'] ] = $label;
			}

		}

		/**
		 * Get name of auto increment column
		 *
		 * @since   1.0.0
		 *
		 * @return bool|string Name of auto increment column or false if no auto increment column exists
		 */
		public function get_auto_increment_column_name() {

			return $this->auto_increment_column_name;

		}

	}

}
