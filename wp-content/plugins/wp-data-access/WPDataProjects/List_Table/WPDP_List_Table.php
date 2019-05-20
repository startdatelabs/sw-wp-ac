<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\List_Table {

	class WPDP_List_Table extends WPDP_List_Table_Lookup {

		public function __construct( array $args = [] ) {
			if ( isset( $args['mode'] ) && 'edit' !== $args['mode'] ) {
				$args['allow_insert'] = 'off';
				$args['allow_update'] = 'off';
				$args['allow_delete'] = 'off';
				$args['allow_import'] = 'off';
			}

			if ( isset( $args['where_clause'] ) && '' !== $args['where_clause'] ) {
				$this->where = $args['where_clause'];
			}

			parent::__construct( $args );
		}

	}

}