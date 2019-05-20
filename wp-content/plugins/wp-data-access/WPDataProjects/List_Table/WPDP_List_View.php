<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\List_Table {

	use WPDataAccess\List_Table\WPDA_List_View;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataProjects\Project\WPDP_Project;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;

	/**
	 * Class WPDP_List_View
	 *
	 * @package WPDataProjects\List_Table
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_List_View extends WPDA_List_View {

		/**
		 * @var null|string
		 */
		protected $project_id = null;
		/**
		 * @var null
		 */
		protected $page_id = null;

		/**
		 * @var null
		 */
		protected $title;
		/**
		 * @var null
		 */
		protected $subtitle;

		/**
		 * @var
		 */
		protected $mode;

		/**
		 * @var null
		 */
		protected $where_clause = null;

		/**
		 * @var string
		 */
		protected $label_type = 'listtable';

		/**
		 * WPDP_List_View constructor.
		 *
		 * @param array $args
		 */
		public function __construct( array $args = [] ) {
			if ( isset( $args['project_id'] ) ) {
				$this->project_id = sanitize_text_field( wp_unslash( $args['project_id'] ) );
			} elseif ( isset( $_REQUEST['tab'] ) && 'tables' === $_REQUEST['tab'] ) {
				$this->project_id = 'wpda_sys_tables';
			}
			if ( isset( $args['page_id'] ) ) {
				$this->page_id = sanitize_text_field( wp_unslash( $args['page_id'] ) );
			}

			$this->project  = new WPDP_Project( $this->project_id, $this->page_id );
			$this->title    = $this->project->get_title();
			$this->subtitle = $this->project->get_subtitle();
			$this->mode     = $this->project->get_mode();

			$args['title']    = ( null === $this->title || '' === $this->title ) ? null : $this->title;
			$args['subtitle'] = $this->subtitle;

			parent::__construct( $args );

			if (
				'edit' === $this->action ||
				'new'  === $this->action ||
				'view' === $this->action
			) {
				$this->label_type = 'tableform';
			}

			// Overwrite column header text.
			$this->column_headers = isset( $args['column_headers'] ) ? $args['column_headers'] : '';

			if ( isset( $args['where_clause'] ) && '' !== $args['where_clause'] ) {
				$this->where_clause = $args['where_clause'];
			}
		}

		/**
		 * Overwrite method.
		 */
		public function show() {
			// Prepare columns for list table. Needed in get_column_headers() and handed over to list table to prevent
			// processing the same queries multiple times.
			if ( null === $this->wpda_list_columns ) {
				$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name, $this->label_type );
			}

			$wpda_repository = new WPDA_Repository();
			$wpda_repository->inform_user();

			switch ( $this->action ) {

				case 'new':  // Show edit form in editing mode to create new records.
				case 'edit': // Show edit form in editing mode to update records.
				case 'view': // Show edit form in view mode to view records.
					$this->display_edit_form();
					break;

				case 'create_table': // Show form to create new table.
					$this->display_design_menu();
					break;

				default: // Show list (default).
					$this->display_list_table();

			}
		}

		/**
		 * Overwrite method.
		 */
		protected function display_list_table() {
			$this->wpda_list_table = new WPDP_List_Table(
				[
					'schema_name'       => $this->schema_name,
					'table_name'        => $this->table_name,
					'wpda_list_columns' => $this->wpda_list_columns,
					'column_headers'    => $this->column_headers,
					'title'				=> $this->title,
					'subtitle'			=> $this->subtitle,
					'mode'				=> $this->mode,
					'where_clause'		=> $this->where_clause,
				]
			);

			$this->wpda_list_table->show();
		}

		/**
		 * Overwrite method.
		 */
		public function get_column_headers() {
			if ( null === $this->wpda_list_columns ) {
				$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name, $this->label_type );
			}
			return $this->wpda_list_columns->get_table_column_headers();
		}

	}

}