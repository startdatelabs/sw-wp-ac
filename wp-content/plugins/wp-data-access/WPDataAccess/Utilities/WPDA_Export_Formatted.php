<?php

namespace WPDataAccess\Utilities {

	use WPDataAccess\WPDA;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;

	class WPDA_Export_Formatted {

		protected $statement   = '';

		protected $schema_name = '';
		protected $table_names = '';

		protected $rows        = null;
		protected $row_count   = 0;

		protected $columns     = null;
		protected $data_types  = [];

		/**
		 * WPDA_Export_Formatted constructor.
		 *
		 * @since	2.0.13
		 */
		public function __construct() {
			if ( defined('WP_MAX_MEMORY_LIMIT') ) {
				$wp_memory_limit      = WP_MAX_MEMORY_LIMIT;
				$current_memory_limit = @ini_set('memory_limit');
				if ( false === $current_memory_limit ||
					WPDA::convert_memory_to_decimal( $current_memory_limit ) < WPDA::convert_memory_to_decimal( $wp_memory_limit )
				) {
					@ini_set( 'memory_limit', $wp_memory_limit );
				}
			}
		}

		/**
		 * Main method to get arguments and start export.
		 *
		 * @since   2.0.13
		 */
		public function export() {
			// Check access rights.
			if ( WPDA::get_option( WPDA::OPTION_BE_EXPORT_TABLES ) !== 'on' ) {
				// Exporting tables is not allowed.
				wp_die();
			}

			// Check if export is allowed.
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, 'wpda-export-*' ) ) {
				wp_die();
			}

			if ( isset( $_REQUEST['schema_name'] ) ) {
				$this->schema_name = sanitize_text_field( wp_unslash( $_REQUEST['schema_name'] ) ); // input var okay.
			}

			if ( isset( $_REQUEST['table_names'] ) ) {
				$this->table_names = sanitize_text_field( wp_unslash( $_REQUEST['table_names'] ) ); // input var okay.
			} else {
				// No table to export.
				wp_die();
			}

			// Check if table exists and access is granted.
			$wpda_list_columns_cache = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_names );
			$this->columns = $wpda_list_columns_cache->get_table_columns();
			foreach ( $this->columns as $column ) {
				$this->data_types[$column['column_name']] = $column['data_type'];
			}

			$this->get_rows();
			if ( false !== $this->rows ) {
				$this->send_export_file();
			}
		}

		protected function get_rows() {
			global $wpdb;

			if ( '' === $this->schema_name) {
				$this->statement = "select * from `{$this->table_names}`";
			} else {
				$this->statement = "select * from `{$this->schema_name}`.`{$this->table_names}`";
			}
			$this->rows      = $wpdb->get_results( $this->statement, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			$this->row_count = $wpdb->num_rows;
		}

		protected function send_export_file() {
			$this->header();
			foreach ( $this->rows as $row ) {
				$this->row( $row );
			}
			$this->footer();
		}

		protected function header() {}

		protected function row( $row ) {}

		protected function footer() {}

	}

}
