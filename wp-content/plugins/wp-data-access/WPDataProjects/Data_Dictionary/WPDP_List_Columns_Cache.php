<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Data_Dictionary;

class WPDP_List_Columns_Cache {

	static protected $cached_list_columns = [];

	static public function get_list_columns( $schema_name, $table_name, $label_type ) {
		$index = "$schema_name.$table_name.$label_type";
		if ( ! isset( self::$cached_list_columns[ $index ] ) ) {
			self::$cached_list_columns[ $index ] = new WPDP_List_Columns( $schema_name, $table_name, $label_type );
		}

		return self::$cached_list_columns[ $index ];
	}

}