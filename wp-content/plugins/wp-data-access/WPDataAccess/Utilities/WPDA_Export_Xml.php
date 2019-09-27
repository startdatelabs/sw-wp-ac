<?php

namespace WPDataAccess\Utilities {

	class WPDA_Export_Xml extends WPDA_Export_Formatted {

		protected function header() {
			header( "Content-type: text/xml; charset=utf-8" );
			header( "Content-Disposition: attachment; filename={$this->table_names}.xml" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			echo "<?xml version=\"1.0\" ?>";
			echo "<resultset statement=\"{$this->statement}>\"";
			echo " time=\"" . gmdate("Y-m-d\TH:i:s\Z") . "\"";
			echo " xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">";
		}

		protected function row( $row ) {
			echo "<row>";
			foreach ( $row as $column_name => $column_value ) {
				echo "<field name=\"$column_name\">$column_value</field>";
			}
			echo "</row>";
		}

		protected function footer() {
			echo "</resultset>";
		}

	}

}