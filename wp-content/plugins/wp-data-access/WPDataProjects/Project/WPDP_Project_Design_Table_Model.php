<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use WPDataAccess\Design_Table\WPDA_Design_Table_Model;
	use WPDataAccess\Utilities\WPDA_Message_Box;

	/**
	 * Class WPDP_Project_Design_Table_Model
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Design_Table_Model extends WPDA_Design_Table_Model {

		/**
		 * @var
		 */
		protected $action2;

		/**
		 * WPDP_Project_Design_Table_Model constructor.
		 */
		public function __construct() {
			parent::__construct();

			$this->wpda_table_name_original = $this->wpda_table_name;
			if ( isset( $_REQUEST['action2'] ) ) {
				$this->action2 = sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
			}
		}

		/**
		 * @return array
		 */
		public function validate() {
			$structure_messages = parent::validate();

			if ( isset( $this->wpda_table_design->tableform_column_options ) ) {
				if ( count( $this->wpda_table_design->table ) !== count( $this->wpda_table_design->tableform_column_options ) ) {
					$structure_messages[] = [ 'ERR', 'Invalid structure (run reconcile)' ];
				}
			}

			return $structure_messages;
		}

		/**
		 *
		 */
		public function prepare_insert() {
			parent::prepare_insert();
		}

		/**
		 *
		 */
		public function prepare_update() {
			parent::prepare_update();

			switch ( $this->action2 ) {
				case 'relation':
					$this->prepare_update_relation();
					break;
				case 'listtable':
					$this->prepare_update_listtable();
					break;
				case 'tableform':
					$this->prepare_update_tableform();
					break;
				case 'tableinfo':
					$this->prepare_update_tableinfo();
			}
		}

		/**
		 *
		 */
		protected function prepare_update_relation() {
			unset( $this->wpda_table_design->relationships );
			if ( isset( $_REQUEST['row_num'] ) ) {
				$no_columns = count( $_REQUEST['row_num'] );
				if (
					isset( $_REQUEST['relation_type'] ) &&
					$no_columns === count( $_REQUEST['relation_type'] ) &&
					isset( $_REQUEST['source_column_name'] ) &&
					$no_columns === count( $_REQUEST['source_column_name'] ) &&
					isset( $_REQUEST['target_table_name'] ) &&
					$no_columns === count( $_REQUEST['target_table_name'] ) &&
					isset( $_REQUEST['target_column_name'] ) &&
					$no_columns === count( $_REQUEST['target_column_name'] )
				) {
					for ( $i = 0; $i < $no_columns; $i++ ) {
						$relation_type      = sanitize_text_field( wp_unslash( $_REQUEST['relation_type'][ $i ] ) );
						$source_column_name = sanitize_text_field( wp_unslash( $_REQUEST['source_column_name'][ $i ] ) );
						$target_table_name  = sanitize_text_field( wp_unslash( $_REQUEST['target_table_name'][ $i ] ) );
						$target_column_name = sanitize_text_field( wp_unslash( $_REQUEST['target_column_name'][ $i ] ) );

						if ( 'nm' === $relation_type ) {
							$relation_table_name = sanitize_text_field( wp_unslash( $_REQUEST[ 'relation_table_name_' . $i ] ) );
							if ( trim( $relation_table_name ) === '' ) {
								$msg = new WPDA_Message_Box(
									[
										'message_text'           => __( 'Invalid array: missing required fields', 'wp-data-access' ),
										'message_type'           => 'error',
										'message_is_dismissible' => false,
									]
								);
								$msg->box();
								return;
							}
						}

						if (
							trim( $relation_type ) !== '' &&
							trim( $source_column_name ) !== '' &&
							trim( $target_table_name ) !== '' &&
							trim( $target_column_name ) !== ''
						) {
							$source_column_name_array = [];
							$target_column_name_array = [];

							array_push( $source_column_name_array, $source_column_name );
							array_push( $target_column_name_array, $target_column_name );

							if ( isset( $_REQUEST['num_source_column_name'][ $i ] ) ) {
								$num_source_column_name = sanitize_text_field( wp_unslash( $_REQUEST['num_source_column_name'][ $i ] ) );
								if ( is_numeric( $num_source_column_name ) ) {
									for ( $j = 1; $j <= $num_source_column_name; $j++ ) {
										if (
											isset( $_REQUEST[ 'source_column_name_' . $i . '_' . $j ] ) &&
											isset( $_REQUEST[ 'target_column_name_' . $i . '_' . $j ] )
										) {
											array_push( $source_column_name_array, sanitize_text_field( wp_unslash( $_REQUEST[ 'source_column_name_' . $i . '_' . $j ] ) ) );
											array_push( $target_column_name_array, sanitize_text_field( wp_unslash( $_REQUEST[ 'target_column_name_' . $i . '_' . $j ] ) ) );
										}
									}
								}
							}

							$this->wpda_table_design->relationships[ $i ]['relation_type']      = $relation_type;
							$this->wpda_table_design->relationships[ $i ]['source_column_name'] = $source_column_name_array;
							$this->wpda_table_design->relationships[ $i ]['target_table_name']  = $target_table_name;
							$this->wpda_table_design->relationships[ $i ]['target_column_name'] = $target_column_name_array;
							if ( 'nm' === $relation_type ) {
								$this->wpda_table_design->relationships[ $i ]['relation_table_name'] = $relation_table_name;
							}
						}
					}
				} else {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'Invalid array: missing required fields', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
				}
			}
		}

		/**
		 *
		 */
		protected function prepare_update_listtable() {
			$column_options = $this->get_column_options_from_request();
			if ( null !== $column_options ) {
				$this->wpda_table_design->listtable_column_options = $column_options;
			}
		}

		/**
		 *
		 */
		protected function prepare_update_tableform() {
			$column_options = $this->get_column_options_from_request();
			if ( null !== $column_options ) {
				$this->wpda_table_design->tableform_column_options = $column_options;
			}
		}

		/**
		 *
		 */
		protected function prepare_update_tableinfo() {
			if ( isset( $_REQUEST['tab_label'] ) ) {
				$this->wpda_table_design->tableinfo = [
					'tab_label' => sanitize_text_field( wp_unslash( $_REQUEST['tab_label'] ) ),
				];
			}
		}

		/**
		 * @return array|null
		 */
		protected function get_column_options_from_request() {
			if ( isset( $_REQUEST['list_item_name'] ) ) {
				$tableform_column_options = [];
				foreach ( $_REQUEST['list_item_name'] as $column_name ) {
					$tableform_column_options[] = [
						'column_name' => $column_name,
						'label'       => isset( $_REQUEST[ $column_name ] ) ?
												sanitize_text_field( wp_unslash( $_REQUEST[ $column_name ] ) ) :
												ucfirst( str_replace( '_', ' ', $column_name ) ),
						'show'        => isset( $_REQUEST["{$column_name}_show"] ) ? 'on' : 'off',
						'lookup'      => isset( $_REQUEST["{$column_name}_lookup"] ) ?
												sanitize_text_field( wp_unslash( $_REQUEST["{$column_name}_lookup" ] ) ) :
												false,
					];
				}
				return $tableform_column_options;
			} else {
				return null;
			}
		}

		/**
		 * @param $table_structure
		 * @param $param_keep_options
		 * @return mixed
		 */
		public function reconcile(
			$table_structure,
			$param_keep_options
		) {
			if ( 1 === $this->query( $this->wpda_table_name_original === $this->wpda_table_name ? null : $this->wpda_table_name_original ) ) {
				// Table definition.
				$this->wpda_table_design->design_mode = $table_structure['design_mode'];
				$this->wpda_table_design->engine      = $table_structure['engine'];
				$this->wpda_table_design->collation   = $table_structure['collation'];
				// Table columns.
				$this->wpda_table_design->table = $table_structure['table'];
				// Table indexes.
				$this->wpda_table_design->indexes = $table_structure['indexes'];

				if ( 'on' !== $param_keep_options ) {
					// Clear arrays.
					$this->wpda_table_design->listtable_column_options = [];
					$this->wpda_table_design->tableform_column_options = [];
					$this->wpda_table_design->tableinfo                = [];
				} else {
					// Remove non existing columns from arrays.
					$new_listtable_column_options = [];
					foreach ( $this->wpda_table_design->listtable_column_options as $listtable_column_option ) {
						$column_found = false;
						foreach ( $this->wpda_table_design->table as $table_column ) {
							if ( $table_column->column_name === $listtable_column_option->column_name ) {
								$column_found = true;
							}
						}
						if ( $column_found ) {
							// Add only column to array that were found in the table definition.
							array_push( $new_listtable_column_options, $listtable_column_option );
						}
					}
					$this->wpda_table_design->listtable_column_options = $new_listtable_column_options;
					$new_tableform_column_options                      = [];
					foreach ( $this->wpda_table_design->tableform_column_options as $tableform_column_option ) {
						$column_found = false;
						foreach ( $this->wpda_table_design->table as $table_column ) {
							if ( $table_column->column_name === $tableform_column_option->column_name ) {
								$column_found = true;
							}
						}
						if ( $column_found ) {
							// Add only column to array that were found in the table definition.
							array_push( $new_tableform_column_options, $tableform_column_option );
						}
					}
					$this->wpda_table_design->tableform_column_options = $new_tableform_column_options;
				}

				foreach ( $this->wpda_table_design->table as $column ) {
					if ( 'on' !== $param_keep_options ) {
						// Every column must be added to array.
						$this->reconcile_add_column( $column );
					} else {
						// Add only new columns to array.
						$column_found = false;
						foreach ( $this->wpda_table_design->listtable_column_options as $listtable_column_option ) {
							if ( isset( $column->column_name ) && isset( $listtable_column_option->column_name ) ) {
								if ( $column->column_name === $listtable_column_option->column_name ) {
									$column_found = true;
									break;
								}
							}
						}
						if ( ! $column_found ) {
							// Add column to both arrays.
							$this->reconcile_add_column( $column );
						}
					}
				}
			}
			global $wpdb;
			return
				$wpdb->update(
					$this->table_name,
					[
						'wpda_table_name'   => $this->wpda_table_name,
						'wpda_table_design' => json_encode( $this->wpda_table_design ),
					],
					[
						'wpda_table_name' => $this->wpda_table_name_original === $this->wpda_table_name ? $this->wpda_table_name : $this->wpda_table_name_original,
					]
				);
		}

		/**
		 * @param $column
		 */
		private function reconcile_add_column( $column ) {
			$this->wpda_table_design->listtable_column_options[] =
				[
					"column_name" => $column->column_name,
					"label"       => static::get_column_label( $column->column_name ),
					"show"        => "on",
				];
			$this->wpda_table_design->tableform_column_options[] =
				[
					"column_name" => $column->column_name,
					"label"       => static::get_column_label( $column->column_name ),
					"show"        => "on",
				];
		}

		/**
		 * @return string
		 */
		public static function get_design_table_name() {
			global $wpdb;

			return $wpdb->prefix . 'wpdp_table';
		}

		/**
		 * @param $table_name
		 * @param $label_type
		 * @return array|null
		 */
		public static function get_column_options( $table_name, $label_type ) {
			global $wpdb;

			$query      = $wpdb->prepare(
				'
              SELECT wpda_table_design
                FROM ' . static::get_design_table_name() . '
               WHERE wpda_table_name = %s
            ',
				[
					$table_name,
				]
			);
			$table_json = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			if ( 1 === $wpdb->num_rows ) {
				if ( isset( $table_json[0]['wpda_table_design'] ) ) {
					$table_obj = json_decode( $table_json[0]['wpda_table_design'] );
					if ( 'tableform' === $label_type && isset( $table_obj->tableform_column_options ) ) {
						return $table_obj->tableform_column_options;
					}
					if ( 'listtable' === $label_type && isset( $table_obj->listtable_column_options ) ) {
						return $table_obj->listtable_column_options;
					}
					if ( 'relationships' === $label_type && isset( $table_obj->relationships ) ) {
						return [
							'table'         => $table_obj->table,
							'tableinfo'     => isset( $table_obj->tableinfo ) ? $table_obj->tableinfo : null,
							'relationships' => $table_obj->relationships,
						];
					}
					if ( 'tableinfo' === $label_type ) {
						return isset( $table_obj->tableinfo ) ? $table_obj->tableinfo : null;
					}
					return null;
				} else {
					return null;
				}
			} else {
				return null;
			}

		}

		/**
		 * @param $wpda_table_name
		 * @param $wpda_table_design
		 * @return bool
		 */
		public static function insert_reverse_engineered( $wpda_table_name, $wpda_table_design ) {
			global $wpdb;

			$table_name = static::get_design_table_name();

			$wpda_table_design['table_type'] = SELF::get_table_type( $wpda_table_name );

			$wpda_table_design['listtable_column_options'] = [];
			$wpda_table_design['tableform_column_options'] = [];

			foreach ( $wpda_table_design['table'] as $column ) {
				$wpda_table_design['listtable_column_options'][] =
					[
						"column_name" => $column->column_name,
						"label"       => static::get_column_label( $column->column_name ),
						"show"        => "on",
					];
				$wpda_table_design['tableform_column_options'][] =
					[
						"column_name" => $column->column_name,
						"label"       => static::get_column_label( $column->column_name ),
						"show"        => "on",
					];
			}

			return
				(
					1 === $wpdb->insert(
						$table_name,
						[
							'wpda_table_name'   => $wpda_table_name,
							'wpda_table_design' => json_encode( $wpda_table_design ),
						]
					)
				);
		}

		/**
		 * @param $column_name
		 * @return string
		 */
		protected static function get_column_label( $column_name ) {
			return ucfirst( str_replace( '_', ' ', $column_name ) );
		}

	}

}
