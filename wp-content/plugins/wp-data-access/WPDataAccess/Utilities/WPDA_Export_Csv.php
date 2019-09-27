<?php

namespace WPDataAccess\Utilities {

	use WPDataAccess\WPDA;

	class WPDA_Export_Csv extends WPDA_Export_Formatted {

		protected function header() {
			header( "Content-type: text/csv" );
			header( "Content-Disposition: attachment; filename={$this->table_names}.csv" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			$first_col = true;
			foreach ( $this->rows[0] as $column_name => $column_value ) {
				if ( $first_col ) {
					$first_col = false;
				} else {
					echo ", ";
				}
				echo $column_name;
			}
			echo "\n";
		}

		protected function row( $row ) {
			$first_col = true;
			foreach ( $row as $column_name => $column_value ) {
				if ( $first_col ) {
					$first_col = false;
				} else {
					echo ", ";
				}
				$is_string = 'number' === WPDA::get_type( $this->data_types[ $column_name ] ) ? "" : "\"";
				echo $is_string . str_replace('\"', '', $column_value) . $is_string;
			}
			echo "\n";
		}

	}

}
