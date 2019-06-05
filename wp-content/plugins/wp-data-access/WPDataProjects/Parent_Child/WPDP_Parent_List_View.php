<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Parent_Child {

	use WPDataAccess\List_Table\WPDA_List_View;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataProjects\Project\WPDP_Project;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;

	/**
	 * Class WPDP_Parent_List_View
	 *
	 * @package WPDataProjects\Parent_Child
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Parent_List_View extends WPDA_List_View {

		/**
		 * @var null|string
		 */
		protected $project_id = null;
		/**
		 * @var null
		 */
		protected $page_id = null;

		/**
		 * @var WPDP_Project
		 */
		protected $project;
		/**
		 * @var
		 */
		protected $project_structure;

		/**
		 * @var null
		 */
		protected $parent;

		/**
		 * @var null
		 */
		protected $children;

		/**
		 * @var null
		 */
		protected $title;
		/**
		 * @var null
		 */
		protected $subtitle;

		/**
		 * @var bool
		 */
		protected $child_request;

		/**
		 * @var
		 */
		protected $mode = null;

		/**
		 * @var mixed|null
		 */
		protected $parent_edit_form_class = null;

		/**
		 * @var null
		 */
		protected $where_clause = null;

		/**
		 * WPDP_Parent_List_View constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			if ( isset( $args['project_id'] ) ) {
				$this->project_id = sanitize_text_field( wp_unslash( $args['project_id'] ) );
			} elseif ( isset( $_REQUEST['tab'] ) && 'tables' === $_REQUEST['tab'] ) {
				$this->project_id = 'wpda_sys_tables';
			}
			if ( isset( $args['page_id'] ) ) {
				$this->page_id = sanitize_text_field( wp_unslash( $args['page_id'] ) );
			}

			global $wpdb;
			$args['schema_name'] = $wpdb->dbname;
			$args['title']       = ( null === $this->title || '' === $this->title ) ? null : $this->title;
			$args['subtitle']    = $this->subtitle;

			parent::__construct( $args );

			$this->child_request = (
				isset( $_REQUEST['child_request'] ) &&
				'TRUE' === sanitize_text_field( wp_unslash( $_REQUEST['child_request'] ) )
			);

			if ( isset( $_REQUEST['mode'] ) ) {
				$this->mode = sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ); // input var okay.
			}

			if ( isset( $args['parent_edit_form_class'] ) ) {
				$this->parent_edit_form_class = $args['parent_edit_form_class']; // input var okay.
			}

			if ( isset( $args['where_clause'] ) && '' !== $args['where_clause'] ) {
				$this->where_clause = $args['where_clause'];
			}
		}

		/**
		 *
		 */
		public function show() {
			$this->project  = new WPDP_Project( $this->project_id, $this->page_id );
			$this->title    = $this->project->get_title();
			if ( null === $this->mode ) {
				$this->mode = $this->project->get_mode();
			}
			$this->subtitle = $this->project->get_subtitle();
			$this->parent   = $this->project->get_parent();
			$this->children = $this->project->get_children();

			$wpda_repository = new WPDA_Repository();
			$wpda_repository->inform_user();

			if ( $this->child_request ) {
				$this->display_edit_form();
			} else {
				switch ( $this->action ) {
					case 'new':
					case 'view':
					case 'edit':
						$this->display_edit_form();
						break;
					default:
						$this->display_list_table();
				}
			}
		}

		/**
		 *
		 */
		protected function display_edit_form() {
			if ( 'view' === $this->mode || ( ! $this->child_request && 'view' === $this->action ) ) {
				$edit_form_class = 'WPDataProjects\\Parent_Child\\WPDP_Parent_Form_View';
				$mode            = 'view';
			} else {
				$edit_form_class = 'WPDataProjects\\Parent_Child\\WPDP_Parent_Form';
				$mode            = 'edit';
			}
			if ( null !== $this->parent_edit_form_class ) {
				$edit_form_class = $this->parent_edit_form_class;
			}
			$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name, 'tableform' );
			$form                    = new $edit_form_class(
				$this->schema_name,
				$this->table_name,
				$this->wpda_list_columns,
				[
					'title'               => null === $this->title ? __( 'Back', 'wp-data-access' ) : $this->title,
					'subtitle'            => $this->subtitle,
					'add_action_to_title' => 'FALSE',
					'mode'                => $mode,
					'child_request'       => $this->child_request,
				],
				[
					'parent'   => $this->parent,
					'children' => $this->children,
				]
			);
			$form->show();
		}

		/**
		 *
		 */
		protected function display_list_table() {

			$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name, 'listtable' );
			$this->wpda_list_table   = new $this->list_table_class(
				[
					'schema_name'       => $this->schema_name,
					'table_name'        => $this->table_name,
					'wpda_list_columns' => $this->wpda_list_columns,
					'column_headers'    => $this->column_headers,
					'project'           => $this->project,
					'where_clause'		=> $this->where_clause,
//					'allow_insert'      => 'off',
				]
			);

			$this->wpda_list_table->set_bulk_actions_enabled( $this->bulk_actions_enabled );
			$this->wpda_list_table->set_search_box_enabled( $this->search_box_enabled );

			if ( null !== $this->title ) {
				$this->wpda_list_table->set_title( $this->title );
			}
			if ( null !== $this->subtitle ) {
				$this->wpda_list_table->set_subtitle( $this->subtitle );
			}

			$this->wpda_list_table->show();

		}

	}

}
