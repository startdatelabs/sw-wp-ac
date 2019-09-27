<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\List_Table
 */

namespace WPDataAccess\List_Table {

	use WPDataAccess\Data_Dictionary\WPDA_List_Columns;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Design_Table\WPDA_Design_Table_Form;
	use WPDataAccess\User_Menu\WPDA_User_Menu_Form;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_List_View
	 *
	 * A list view is an object that consists of a WordPress list table and it's screen options (displayed in the top
	 * right corner). The list view combines these options by identifying the different stages that apply to building
	 * a page containing both.
	 *
	 * Stages:
	 * + Screen options are added in the constructor
	 * + The list table is created in {@see WPDA_List_View::display_list_table()}
	 *
	 * To make sure that screen options are displayed with the list table, the constructor is called in the
	 * 'admin_menu' hook {@see \WP_Data_Access::define_admin_hooks()}. A object reference is stored in the class
	 * and used later when the list table is created on the page displayed.
	 *
	 * @see WPDA_List_View::display_list_table()
	 * @see \WP_Data_Access::define_admin_hooks()
	 *
	 * @package WPDataAccess\List_Table
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_List_View {

		/**
		 * Page hook suffix
		 *
		 * @var object|boolean Reference to (sub) menu or false
		 */
		protected $page_hook_suffix;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name;

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * Indicates if bulk actions are allow
		 *
		 * @var boolean
		 */
		protected $bulk_actions_enabled;

		/**
		 * Indicates if a search box is shown
		 *
		 * @var boolean
		 */
		protected $search_box_enabled;

		/**
		 * Classname of list table
		 *
		 * @var string
		 */
		protected $list_table_class;

		/**
		 * Classname of data entry form
		 *
		 * @var string
		 */
		protected $edit_form_class;

		/**
		 * Reference to list table
		 *
		 * @var WPDA_List_Table|WPDA_List_Table_Menu
		 */
		protected $wpda_list_table;

		/**
		 * Reference to list columns
		 *
		 * @var WPDA_List_Columns
		 */
		protected $wpda_list_columns = null;

		/**
		 * Page title
		 *
		 * @var string
		 */
		protected $title;

		/**
		 * Page subtitle
		 *
		 * @var string
		 */
		protected $subtitle;

		/**
		 * Action (taken from $_REQUEST)
		 *
		 * @var string
		 */
		protected $action = '';

		/**
		 * Column headers (labels)
		 *
		 * @var string
		 */
		protected $column_headers;

		/**
		 * WPDA_List_View constructor
		 *
		 * Page hook suffix
		 *
		 * We first check if we have a page hook suffix. This is the reference to the sub menu to which we want to
		 * add the list view. If no page hook suffix is provided, the list table might be displayed as expected, the
		 * screen options in the top right corner however will not be shown.
		 *
		 * Database table usage
		 * + The constructor can be called with or without a table name. If a table name is provided, a list table is
		 * generated for that database table.
		 * + If no table name is provided, we need to checked if a table name argument was given in the request. If a
		 * table name was provided with the request, a list table is generated for that table.
		 * + If no table name is provided (neither as an argument nor with the request) a list of all tables available
		 * in the WordPress database schema is generated.
		 *
		 * Table names are always checked! We need to check:
		 * + if a table exists in our database schema and
		 * + if we have access to that table.
		 *
		 * These checks are performed to prevent SQL injection and misuse of our WordPress database. These checks
		 * however are not performed in this class. They are performed in class {@see WPDA_List_Table} as we do not
		 * perform any queries in this class. We do perform queries on the given tables in {@see WPDA_List_Table}.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table
		 *
		 * @param array $args [
		 *
		 * 'page_hook_suffix'     => (string|boolean) Page hook suffix of false (default = false)
		 *
		 * 'schema_name'          => (string) Database schema name (default = '')
		 *
		 * 'table_name'           => (string) Database table name (default = '')
		 *
		 * 'bulk_actions_enabled' => (boolean) Allow bulk actions? (default = TRUE)
		 *
		 * 'search_box_enabled'   => (boolean) Show search box? (default = TRUE)
		 *
		 * 'list_table_class'     => (string) Class providing list table functionality
		 *
		 * 'edit_form_class'      => (string) Class providing data entry functionality
		 *
		 * 'column_headers'       => (array|string) Column headers (default = '' : headers taken from data dictionary)
		 *
		 * 'title'                => (string) Page title (default = null)
		 *
		 * 'subtitle'             => (string) Page subtitle (default = null)
		 *
		 * ].
		 */
		public function __construct( $args = [] ) {

			$args = wp_parse_args(
				$args, [
					'page_hook_suffix'     => false,
					'schema_name'          => '',
					'table_name'           => '',
					'bulk_actions_enabled' => WPDA_List_Table::DEFAULT_BULK_ACTIONS_ENABLED,
					'search_box_enabled'   => WPDA_List_Table::DEFAULT_SEARCH_BOX_ENABLED,
					'list_table_class'     => 'WPDataAccess\\List_Table\\WPDA_List_Table',
					'edit_form_class'      => 'WPDataAccess\\Simple_Form\\WPDA_Simple_Form',
					'column_headers'       => '',
					'title'                => null,
					'subtitle'             => null,
				]
			);

			// If page_hook_suffix = false WordPress will not be able to continue. The standard WordPress exception will
			// be displayed. We leave this to be handled by WordPress.
			$this->page_hook_suffix = $args['page_hook_suffix'];

			$this->schema_name = $args['schema_name'];
			if ( '' === $this->schema_name ) {
				// No pre defined schema_name!
				if ( isset( $_REQUEST['schema_name'] ) ) {
					// Get schema name from URL.
					$this->schema_name = sanitize_text_field( wp_unslash( $_REQUEST['schema_name'] ) ); // input var okay.
				}
			}

			$this->table_name = $args['table_name'];
			if ( '' === $this->table_name ) {
				// No pre defined table_name!
				if ( isset( $_REQUEST['table_name'] ) ) {
					// Get table name from URL. (later we'll check if the table exists in the WordPress database to
					// protect ourselves against SQL injection).
					$this->table_name = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.
				}
			}

			// Set class to provide list table functionality.
			$this->list_table_class = $args['list_table_class'];

			// Set class for data entry form support (used for new, edit and view actions).
			$this->edit_form_class = $args['edit_form_class'];

			// Set page title.
			$this->title = $args['title'];

			// Set page subtitle.
			$this->subtitle = $args['subtitle'];

			$this->bulk_actions_enabled = $args['bulk_actions_enabled'];

			$this->search_box_enabled = $args['search_box_enabled'];

			if ( isset( $_REQUEST['action'] ) ) {
				$this->action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay; sanitization okay.
			}

			if ( ! ( 'new' === $this->action ||
				'edit' === $this->action ||
				'view' === $this->action ||
				'user_menu' === $this->action
			) ) {
				// Add screen options.
				add_action( 'load-' . $this->page_hook_suffix, [ $this, 'page_screen_options' ] );
				add_filter( 'set-screen-option', [ $this, 'set_screen_option' ], 10, 3 );
			}

			// Overwrite column header text.
			$this->column_headers = isset( $args['column_headers'] ) ? $args['column_headers'] : '';

		}

		/**
		 * Set columns to be queried
		 *
		 * If not set all column (*) will be selected/set/queried.
		 *
		 * @since   1.0.0
		 *
		 * @param mixed $columns_queried Column array, '' or '*'.
		 */
		public function set_columns_queried( $columns_queried ) {

			$this->wpda_list_table->set_columns_queried( $columns_queried );

		}

		/**
		 * Enable or disable bulk actions
		 *
		 * If enabled user can perform actions on multiple rows at once.
		 *
		 * @since   1.0.0
		 *
		 * @param boolean $bulk_actions_enabled TRUE = allow bulk actions, FALSE = no bulk actions.
		 */
		public function set_bulk_actions_enabled( $bulk_actions_enabled ) {

			$this->bulk_actions_enabled = $bulk_actions_enabled;

		}

		/**
		 * Enable search box
		 *
		 * Shows a search box if enabled. In WP Data Access only columns with data type varchar or enum are searched.
		 *
		 * @since   1.0.0
		 *
		 * @param boolean $search_box_enabled TRUE = show search box, FALSE = no search box.
		 */
		public function set_search_box_enabled( $search_box_enabled ) {

			$this->search_box_enabled = $search_box_enabled;

		}

		/**
		 * Display page
		 *
		 * Page types to be displayed:
		 * + List table
		 * + View form
		 * + Data entry form (add new)
		 * + Data entry form (update)
		 *
		 * The type of table displayed depend on the value of the action argument provided in the request. The value
		 * of argument action is stored in $this->action in the constructor.
		 *
		 * @since   1.0.0
		 */
		public function show() {

			// Prepare columns for list table. Needed in get_column_headers() and handed over to list table to prevent
			// processing the same queries multiple times.
			if ( null === $this->wpda_list_columns ) {
				$this->wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name );
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
		 * Display data entry form
		 *
		 * Called when action is:
		 * + 'new' to add a new record to the table
		 * + 'edit' to update a record
		 * + 'view" to show a record (readonly)
		 *
		 * Class WPDA_Simple_Form is the default class used to generate data entry forms. This class provides dynamic
		 * generation of data entry forms for any table, as long as the table has a primary key. The primary key is
		 * necessary to perform updates (unique identification of records).
		 *
		 * For more specific data entry forms WPDA_Simple_Form can be extended. These classes need to implement some
		 * methods to work properly. Check out {@see \WPDataAccess\Simple_Form\WPDA_Simple_Form} for more information.
		 * Or see {@see \WPDataAccess\User_Menu\WPDA_User_Menu_Form} for an implemetation which might serve as an
		 * example.
		 *
		 * @since   1.0.0
		 *
		 * @see \WPDataAccess\Simple_Form\WPDA_Simple_Form
		 * @see \WPDataAccess\User_Menu\WPDA_User_Menu_Form
		 */
		protected function display_edit_form() {

			$form = new $this->edit_form_class(
				$this->schema_name,
				$this->table_name,
				$this->wpda_list_columns
			);
			$form->show();

		}

		/**
		 * Create sub menu
		 *
		 * Called when action is:
		 * + 'user_menu' to create a sub menu containing a list table
		 *
		 * Calls class WPDA_User_Menu_Form to add a new record to plugin table 'wp_wpda_menu_items'. Class
		 * WPDA_User_Menu_Form entends class WPDA_Simple_Form.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_User_Menu_Form
		 */
		protected function display_user_menu() {

			$form = new WPDA_User_Menu_Form(
				$this->table_name,
				$this->wpda_list_columns
			);
			$form->show();

		}

		protected function display_design_menu() {
			$form = new WPDA_Design_Table_Form;
			$form->show();
		}

		/**
		 * Display list table
		 *
		 * There are two type of list tables here:
		 * + List of tables in the WordPress database schema
		 * + List of rows in a specific table
		 *
		 * A list of tables in the WordPress database schema is in fact a list of rows as well. The MySQL base table
		 * (which is in fact a view) used to show this information is 'information_schema.tables'. The list of tables
		 * contains a link to a list table for every table.
		 *
		 * The list of rows is provided by class {@see WPDA_List_Table}. WPDA_List_Table extends Wordprees class
		 * WP_List_Table.
		 *
		 * The list of tables is provided by class {@see WPDA_List_Table_Menu}. WPDA_List_Table_Menu extends class
		 * WPDA_List_Table.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table
		 * @see WPDA_List_Table_Menu
		 */
		protected function display_list_table() {

			if ( '' === $this->table_name ) {
				// List all tables in the database.
				$this->list_table_class = 'WPDataAccess\\List_Table\\WPDA_List_Table_Menu';
			}

			$this->wpda_list_table = new $this->list_table_class(
				[
					'schema_name'       => $this->schema_name,
					'table_name'        => $this->table_name,
					'wpda_list_columns' => $this->wpda_list_columns,
					'column_headers'    => $this->column_headers,
				]
			);

			// $this->wpda_list_table->set_bulk_actions_enabled( $this->bulk_actions_enabled );
			// $this->wpda_list_table->set_search_box_enabled( $this->search_box_enabled );

			// Reset page title and subtitle to allow empty titles and subtitles as well.
			if ( null !== $this->title ) {
				$this->wpda_list_table->set_title( $this->title );
			}
			if ( null !== $this->subtitle ) {
				$this->wpda_list_table->set_subtitle( $this->subtitle );
			}

			$this->wpda_list_table->show();

		}

		/**
		 * Set page screen options
		 *
		 * Provided are column selection (enable/disable) and rows per page. The table name is included in the meta_key
		 * to save screen options per table.
		 *
		 * @since   1.0.0
		 */
		public function page_screen_options() {

			set_screen_options();

			$screen = get_current_screen();

			if ( is_object( $screen ) && $screen->id === $this->page_hook_suffix ) {

				$table_name = $this->table_name;
				if ( '' === $table_name ) {
					// The WordPress Database Table List doesn't have a table_name at this stage. Use the base table
					// defined in WPDA_List_Table_Menu instead.
					$table_name = str_replace( '.', '_', WPDA_List_Table::LIST_BASE_TABLE );
				}

				add_filter( "manage_{$screen->id}_columns", [ $this, 'get_column_headers' ] );

				$args = [
					'label'   => __( 'Number of items per page', 'wp-data-access' ),
					'default' => WPDA::get_option( WPDA::OPTION_BE_PAGINATION ),
					'option'  => 'wpda_rows_per_page_' . str_replace( '.', '_', $table_name ),
				];
				add_screen_option( 'per_page', $args );

			}

		}

		/**
		 * Get column headers
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_column_headers() {

			if ( '' === $this->table_name ) {
				// We're on the Data Explorer main page. Use user defined labels.
				return WPDA_List_Table_Menu::column_headers_labels();
			} else {
				// We're on the Data Explorer table page. Use table column labels.
				if ( null === $this->wpda_list_columns ) {
					$this->wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name );
				}
				return $this->wpda_list_columns->get_table_column_headers();
			}

		}

		/**
		 * Callback for set-screen-option filter
		 *
		 * @since   1.0.0
		 *
		 * @param string $status Not used.
		 * @param string $option string Not used.
		 * @param mixed  $value Option value.
		 * @return mixed
		 */
		public function set_screen_option( $status, $option, $value ) {

			return $value;

		}

	}

}
