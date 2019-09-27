<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Dictionary
 */

namespace WPDataAccess\Data_Dictionary;

class WPDA_List_Columns_Cache {

	static protected $cached_list_columns = [];

	static public function get_list_columns( $schema_name, $table_name ) {
		$index = "$schema_name.$table_name";
		if ( ! isset( self::$cached_list_columns[ $index ] ) ) {
			self::$cached_list_columns[ $index ] = new WPDA_List_Columns(  $schema_name, $table_name );
		}

		return self::$cached_list_columns[ $index ];
	}

}