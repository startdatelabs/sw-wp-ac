<?php

namespace WPDataAccess\Utilities {

	use WPDataAccess\WPDA;

	class WPDA_Export {

		/**
		 * Main method to start specific export type
		 *
		 * @since   2.0.13
		 */
		public function export() {

			// Check access rights.
			if ( WPDA::get_option( WPDA::OPTION_BE_EXPORT_TABLES ) !== 'on' ) {
				// Exporting tables is not allowed.
				wp_die();
			}

			$export_class = 'WPDataAccess\\Utilities\\WPDA_Export_Sql'; // Default export class exports to SQL.

			if ( isset( $_REQUEST['format_type'] ) ) {
				$format_type  = sanitize_text_field( wp_unslash( $_REQUEST['format_type'] ) ); // input var okay.
				switch ( $format_type ) {
					case 'excel':
						$export_class = 'WPDataAccess\\Utilities\\WPDA_Export_Excel';
						break;
					case 'json':
						$export_class = 'WPDataAccess\\Utilities\\WPDA_Export_Json';
						break;
					case 'xml':
						$export_class = 'WPDataAccess\\Utilities\\WPDA_Export_Xml';
						break;
					case 'csv':
						$export_class = 'WPDataAccess\\Utilities\\WPDA_Export_Csv';
						break;
				}
			}

			$export = new $export_class();
			$export->export();
		}

	}

}
