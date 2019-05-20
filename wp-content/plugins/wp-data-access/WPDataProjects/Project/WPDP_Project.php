<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use WPDataAccess\WPDA;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;

	/**
	 * Class WPDP_Project
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project {

		/**
		 * @var
		 */
		protected $project;

		/**
		 * @var null
		 */
		protected $project_id = null;
		/**
		 * @var null
		 */
		protected $page_id = null;

		/**
		 * WPDP_Project constructor.
		 *
		 * @param null $project_id
		 * @param null $page_id
		 */
		public function __construct( $project_id = null, $page_id = null ) {
			$this->project_id = $project_id;
			$this->page_id    = $page_id;

			if ( null === $this->project_id ) {
				$this->init_self();
			} elseif ( 'wpda_sys_tables' === $this->project_id ) {
				$this->init_self_tables();
			} else {
				$this->init_project_page( $this->project_id, $this->page_id );
			}
		}

		/**
		 *
		 */
		protected function init_self() {
			global $wpdb;
			$this->project =
				[
					'mode'     => 'edit',
					'title'    => '',
					'subtitle' => '',
					'parent'   =>
						[
							'key'       => [ 'project_id' ],
							'data_type' => [ 'number' ],
						],
					'children' =>
						[
							[
								'table_name'  => $wpdb->prefix . 'wpdp_page',
								'tab_label'   => 'Pages',
								'relation_1n' =>
									[
										'child_key' => [ 'project_id' ],
										'data_type' => [ 'number' ],
									],
							],
						],
				];
		}

		/**
		 *
		 */
		protected function init_self_tables() {
			$this->project =
				[
					'mode'      => 'edit',
					'title'     => '',
					'subtitle'  => '',
					'parent'    =>
						[
							'key'       => [ 'wpda_table_name' ],
							'data_type' => [ 'varchar' ],
						],
					'children.' =>
						[],
				];
		}

		/**
		 * @param $project_id
		 * @param $page_id
		 */
		protected function init_project_page( $project_id, $page_id ) {
			global $wpdb;
			$query = $wpdb->prepare(
				"
                    select * from {$wpdb->prefix}wpdp_page 
                    where project_id = %d 
                      and page_id    = %d
                ",
				[
					$project_id,
					$page_id,
				]
			);
			$project_page = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			$table_name    = $project_page[0]['page_table_name'];
			$relationships = WPDP_Project_Design_Table_Model::get_column_options( $table_name, 'relationships' );

			$wpda_list_columns            = WPDA_List_Columns_Cache::get_list_columns( '', $table_name );
			$parent_primary_key           = $wpda_list_columns->get_table_primary_key();
			$parent_primary_key_data_type = [];
			foreach ( $parent_primary_key as $pk ) {
				if ( isset( $relationships['table'] ) && null !== $relationships['table'] ) {
					foreach ( $relationships['table'] as $column ) {
						if ( $column->column_name === $pk ) {
							array_push( $parent_primary_key_data_type, WPDA::get_type( $column->data_type ) );
							break;
						}
					}
				}
			}
			$parent = [
				'key'       => $parent_primary_key,
				'data_type' => $parent_primary_key_data_type,
			];

			$children = [];
			if ( isset( $relationships['relationships'] ) && null !== $relationships['relationships'] ) {
				foreach ( $relationships['relationships'] as $relationship ) {
					$child_key_data_type = [];
					if ( '1n' === $relationship->relation_type || 'lookup' === $relationship->relation_type ) {
						$n_relationship = WPDP_Project_Design_Table_Model::get_column_options( $relationship->target_table_name, 'tableinfo' );

						if ( isset( $n_relationship->tab_label ) ) {
							$tab_label = $n_relationship->tab_label;
						} else {
							$tab_label = '';
						}

						foreach ( $relationships['table'] as $column ) {
							foreach ( $relationship->target_column_name as $target_column_name ) {
								if ( $column->column_name === $target_column_name ) {
									array_push( $child_key_data_type, WPDA::get_type( $column->data_type ) );
									break;
								}
							}
						}
						$child = [
							'table_name'                               => $relationship->target_table_name,
							'tab_label'                                => $tab_label === '' ? $relationship->target_table_name : $tab_label,
							'relation_' . $relationship->relation_type => [
								'child_key' => $relationship->target_column_name,
								'data_type' => $child_key_data_type,
							]
						];
						array_push( $children, $child );
					} elseif ( 'nm' === $relationship->relation_type ) {
						$nm_relationships = WPDP_Project_Design_Table_Model::get_column_options( $relationship->relation_table_name, 'relationships' );

						if ( isset( $nm_relationships['tableinfo'] ) && isset( $nm_relationships['tableinfo']->tab_label ) ) {
							$tab_label = $nm_relationships['tableinfo']->tab_label;
						} else {
							$tab_label = '';
						}

						if ( null !== $nm_relationships['relationships'] ) {
							$nm_relationship_found = null;
							foreach ( $nm_relationships['relationships'] as $nm_relationship ) {
								if ( $nm_relationship->target_table_name === $relationship->target_table_name ) {
									$nm_relationship_found = $nm_relationship;
									break;
								}
							}
							foreach ( $nm_relationship_found->source_column_name as $source_column_name ) {
								foreach ( $nm_relationships['table'] as $column ) {
									if ( $column->column_name === $source_column_name ) {
										array_push( $child_key_data_type, WPDA::get_type( $column->data_type ) );
										break;
									}
								}
							}
							$child = [
								'table_name'  => $relationship->relation_table_name,
								'tab_label'   => $tab_label === '' ? $relationship->relation_table_name : $tab_label,
								'relation_nm' => [
									'child_table'        => $relationship->target_table_name,
									'parent_key'         => $nm_relationship->source_column_name,
									'child_table_select' => $nm_relationship->target_column_name,
									'child_table_where'  => $relationship->target_column_name,
									'data_type'          => $child_key_data_type,
								]
							];
							array_push( $children, $child );
						}
					}
				}
			}

			$this->project =
				[
					'mode'     => $project_page[0]['page_mode'],
					'title'    => $project_page[0]['page_title'],
					'subtitle' => $project_page[0]['page_subtitle'],
					'parent'   => $parent,
					'children' => $children,
				];

		}


		/**
		 * @return mixed
		 */
		public function get_project() {
			return $this->project;
		}

		/**
		 * @return null
		 */
		public function get_mode() {
			if ( isset( $this->project['mode'] ) ) {
				return $this->project['mode'];
			} else {
				return null;
			}
		}

		/**
		 * @return null
		 */
		public function get_title() {
			if ( isset( $this->project['title'] ) ) {
				return $this->project['title'];
			} else {
				return null;
			}
		}

		/**
		 * @return null
		 */
		public function get_subtitle() {
			if ( isset( $this->project['subtitle'] ) ) {
				return $this->project['subtitle'];
			} else {
				return null;
			}
		}

		/**
		 * @return null
		 */
		public function get_parent() {
			if ( isset( $this->project['parent'] ) ) {
				return $this->project['parent'];
			} else {
				return null;
			}
		}

		/**
		 * @return null
		 */
		public function get_children() {
			if ( isset( $this->project['children'] ) ) {
				return $this->project['children'];
			} else {
				return null;
			}
		}

	}

}
