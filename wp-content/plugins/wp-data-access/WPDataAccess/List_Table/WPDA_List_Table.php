<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\List_Table
 */

namespace WPDataAccess\List_Table {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns;
	use WPDataAccess\Utilities\WPDA_Import;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\Wordpress_Original;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_List_Table
	 *
	 * This class extends WordPress class WP_List_Table. The WordPress WP_List_Table is contained in the package as
	 * advised by WordPress and might be updated in future releases.
	 *
	 * @package WPDataAccess\List_Table
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_List_Table extends Wordpress_Original\WP_List_Table {

		/**
		 * Base table for list of all tables
		 */
		const LIST_BASE_TABLE = 'information_schema.tables';

		/**
		 * Default value bulk actions enabled
		 */
		const DEFAULT_BULK_ACTIONS_ENABLED = true;

		/**
		 * Default value bulk export enabled
		 */
		const DEFAULT_BULK_EXPORT_ENABLED = true;

		/**
		 * Default value bulk search enabled
		 */
		const DEFAULT_SEARCH_BOX_ENABLED = true;

		/**
		 * Table row number within the current response
		 *
		 * @var int
		 */
		static protected $list_number = 0;

		/**
		 * Menu slug of the current page
		 *
		 * @var string
		 */
		protected $page;

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
		protected $subtitle = '';

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name = '';

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name = '';

		/**
		 * Where clause
		 *
		 * @var string
		 */
		protected $where = '';

		/**
		 * Columns in query
		 *
		 * @var array
		 */
		protected $columns_queried;

		/**
		 * Number of rows displayed in list box
		 *
		 * @var int
		 */
		protected $items_per_page;

		/**
		 * Page number
		 *
		 * @var int
		 */
		protected $current_page = 1;

		/**
		 * Add search box?
		 *
		 * @var bool
		 */
		protected $search_box_enabled = WPDA_List_Table::DEFAULT_SEARCH_BOX_ENABLED;

		/**
		 * Enable bulk actions?
		 *
		 * @var bool
		 */
		protected $bulk_actions_enabled = WPDA_List_Table::DEFAULT_BULK_ACTIONS_ENABLED;

		/**
		 * Enable exports?
		 *
		 * @var bool
		 */
		protected $bulk_export_enabled = WPDA_List_Table::DEFAULT_BULK_EXPORT_ENABLED;

		/**
		 * Show view link?
		 *
		 * @var string on|off
		 */
		protected $show_view_link = null;

		/**
		 * Allow inserts?
		 *
		 * @var string on|off
		 */
		protected $allow_insert = null;

		/**
		 * Allow updates?
		 *
		 * @var string on|off
		 */
		protected $allow_update = null;

		/**
		 * Allow deletes?
		 *
		 * @var string on|off
		 */
		protected $allow_delete = null;

		/**
		 * Hides tablenav
		 *
		 * @var bool
		 */
		protected $hide_navigation = false;

		/**
		 * Reference to column list
		 *
		 * @var WPDA_List_Columns
		 */
		protected $wpda_list_columns;

		/**
		 * Reference to dictionary object
		 *
		 * @var WPDA_Dictionary_Exist
		 */
		protected $wpda_data_dictionary;

		/**
		 * Column headers (labels)
		 *
		 * @var array
		 */
		protected $column_headers;

		/**
		 * Reference to import object
		 *
		 * @var WPDA_Import
		 */
		protected $wpda_import = null;

		/**
		 * Child tab clicked (used for parent child relationships only)
		 *
		 * @var null
		 */
		protected $child_tab = '';

		/**
		 * Search string (entered by user or taken from cookie)
		 *
		 * @var null|string
		 */
		protected $search_value = null;

		/**
		 * Previous search string
		 *
		 * @var null|string
		 */
		protected $search_value_old = null;

		/**
		 * @var string
		 */
		protected $page_number_item_name = 'page_number';

		/**
		 * @var null
		 */
		protected $page_number_link = '';

		/**
		 * @var null
		 */
		protected $page_number_item = '';

		/**
		 * @var string Name of search item (WP default = 's')
		 */
		protected $search_item_name = 's';

		/**
		 * WPDA_List_Table constructor
		 *
		 * A table name must be provided in the constructor call. The table must be a valid MySQL database table to
		 * which acces (to the back-end) is granted. If no table name is provided, the table does not exist or access
		 * to the back-end for the given table is not granted, processing is stopped. It makes no sense to continue
		 * without a valid table. A check for table existence is performed to prevent SQL injection.
		 *
		 * There are two types of tables to build a list table on:
		 * + List of tables in the WordPress database schema
		 * + List of rows in a specific table
		 *
		 * To build a list of tables in the WordPress database schema, we query table 'information_schema.tables'
		 * (which is in fact a view). This is the only query allowed outside the WordPress database schema. The
		 * table/view name is stored in constant WPDA_List_Table::LIST_BASE_TABLE for validation purposes.
		 *
		 * A list of rows for a specific table is based on WordPress class WP_List_Table.
		 *
		 * WPDA_List_Table can be used to build list tables for views as well. View based list tables however, do not
		 * support insert, update, delete, import and export actions.
		 *
		 * A table name is not the only thing we need to build a list table. We also need to have access to the
		 * table columns. If no table columns are provided execution is stopped as well.
		 *
		 * @since   1.0.0
		 *
		 * @param array $args [
		 *
		 * 'table_name'              => (string) Database table name
		 *
		 * 'wpda_list_columns'       => (object) Reference to a WPDA_List_Columns object
		 *
		 * 'singular'                => (string) Singular object label
		 *
		 * 'plural'                  => (string) lural object label
		 *
		 * 'ajax'                    => (string) TRUE = list table support Ajax
		 *
		 * 'column_headers'          => (array|string) Column headers
		 *
		 * 'title'                   => (string) Page title
		 *
		 * 'subtitle'                => (string) Page subtitle
		 *
		 * 'bulk_export_enabled' => (boolean)
		 *
		 * 'search_box_enabled'      => (boolean)
		 *
		 * 'bulk_actions_enabled'    => (boolean)
		 *
		 * 'show_view_link'          => (string) on|off
		 *
		 * 'allow_insert'            => (string) on|off
		 *
		 * 'allow_update'            => (string) on|off
		 *
		 * 'allow_delete'            => (string) on|off
		 *
		 * 'allow_import'            => (string) on|off
		 *
		 * 'hide_navigation'         => (boolean)
		 *
		 * ].
		 */
		public function __construct( $args = [] ) {

			if ( ! isset( $args['table_name'] ) ) {
				// Calling WPDA_List_Table without a table_name doesn't make sense.
				wp_die( esc_html__( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			if ( ! isset( $args['wpda_list_columns'] ) ) {
				// Calling WPDA_List_Table without a column list is not allowed.
				wp_die( esc_html__( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			parent::__construct(
				[
					'singular' => isset( $args['singular'] ) ? $args['singular'] : __( 'Row', 'wp-data-access' ),
					'plural'   => isset( $args['plural'] ) ? $args['plural'] : __( 'Rows', 'wp-data-access' ),
					'ajax'     => isset( $args['ajax'] ) ? $args['ajax'] : false,
				]
			);

			// Set schema name is available.
			if ( isset( $args['schema_name'] ) ) {
				$this->schema_name = $args['schema_name'];
			}

			// Set table name (availability already checked).
			$this->table_name = $args['table_name'];

			if ( $this->table_name !== self::LIST_BASE_TABLE ) {
				// Whenever a table name is provided through a URL we are risking an SQL injection attack. Our
				// defence mechanism here is to check whether the table name provided exists in our database.
				// If it does not we'll terminate the process with an error.
				// Although this check is only needed when a table name is provided through the URL we will perform
				// it in all situations. It is a fast query which makes our application much more safe and reliable.
				$this->wpda_data_dictionary = new WPDA_Dictionary_Exist( $this->schema_name, $this->table_name );
				if ( ! $this->wpda_data_dictionary->table_exists() ) {
					wp_die( esc_html__( 'ERROR: Invalid table name or not authorized', 'wp-data-access' ) );
				}
			}

			// Get menu slag of current page.
			if ( isset( $_REQUEST['page'] ) ) {
				$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
			} else {
				// In order to show a list table we need a page.
				wp_die( esc_html__( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			// Use column list: argument wpda_list_columns (availability already checked).
			$this->wpda_list_columns = &$args['wpda_list_columns'];

			// Set pagination.
			$this->items_per_page = WPDA::get_option( WPDA::OPTION_BE_PAGINATION );

			// Overwrite column header text if column headers were provided.
			$this->column_headers = isset( $args['column_headers'] ) ? $args['column_headers'] : '';

			// Set title.
			if ( isset( $args['title'] ) ) {
				$this->title = $args['title'];
			} else {
				$this->title = __( 'Table', 'wp-data-access' ) . ' ' . strtoupper( $this->table_name );
			}

			// Set subtitle.
			if ( isset( $args['subtitle'] ) ) {
				$this->subtitle = $args['subtitle'];
			} else {
				global $wpdb;
				$wp_tables = $wpdb->tables( 'all', true );

				if ( isset( $wp_tables[ substr( $this->table_name, strlen( $wpdb->prefix ) ) ] ) ) {
					$this->subtitle = '<span class="dashicons dashicons-warning"></span> ' . WPDA::get_table_type_text( WPDA::TABLE_TYPE_WP );
				} elseif ( WPDA::is_wpda_table( $this->table_name ) ) {
					$this->subtitle = '<span class="dashicons dashicons-warning"></span> ' . WPDA::get_table_type_text( WPDA::TABLE_TYPE_WPDA );
				}
			}

			if ( ! ( isset( $args['allow_import'] ) && 'off' === $args['allow_import'] ) ) {
				try {
					// Instantiate WPDA_Import.
					$this->wpda_import = new WPDA_Import(
						"?page={$this->page}&schema_name={$this->schema_name}&table_name={$this->table_name}",
						$this->schema_name,
						$this->table_name
					);
				} catch ( \Exception $e ) {
					// If import is turned off instantition will fail. Handle is set to null (check in future calls).
					$this->wpda_import = null;
				}
			}

			if ( isset( $args['bulk_export_enabled'] ) ) {
				$this->bulk_export_enabled = $args['bulk_export_enabled'];
			}

			if ( isset( $args['search_box_enabled'] ) ) {
				$this->search_box_enabled = $args['search_box_enabled'];
			}

			if ( isset( $args['bulk_actions_enabled'] ) ) {
				$this->bulk_actions_enabled = $args['bulk_actions_enabled'];
			}

			if ( isset( $args['show_view_link'] ) ) {
				$this->show_view_link = $args['show_view_link'];
			}

			if ( isset( $args['allow_insert'] ) ) {
				$this->allow_insert = $args['allow_insert'];
			}

			if ( isset( $args['allow_update'] ) ) {
				$this->allow_update = $args['allow_update'];
			}

			if ( isset( $args['allow_delete'] ) ) {
				$this->allow_delete = $args['allow_delete'];
			}

			if ( isset( $args['hide_navigation'] ) ) {
				$this->hide_navigation = $args['hide_navigation'];
			}

			$this->search_value = $this->get_search_value();
			if ( isset( $_REQUEST["{$this->search_item_name}_old_value"] ) ) {
				$this->search_value_old = sanitize_text_field( wp_unslash( $_REQUEST["{$this->search_item_name}_old_value"] ) ); // input var okay.
			}

			// Get page number(s).
			if ( 'page_number' !== $this->page_number_item_name ) {
				if ( isset( $_REQUEST['page_number'] ) ) {
					$requested_page_number  = sanitize_text_field( wp_unslash( $_REQUEST['page_number'] ) ); // input var okay.
					$this->page_number_link = "&page_number=" . $requested_page_number;
					$this->page_number_item = "<input type='hidden' name='page_number' value='" . $requested_page_number . "' />";
				}
			}
			$this->page_number_link .= "&paged=" . $this->get_pagenum();
			$this->page_number_item .= "<input type='hidden' name='" . $this->page_number_item_name . "' value='" . $this->get_pagenum() . "' />";

		}

		/**
		 * Set columns to be queries
		 *
		 * Set columns to be queried and shown in the list table.
		 * By default all columns in the table will be displayed.
		 *
		 * @since   1.0.0
		 *
		 * @param mixed $columns_queried Column list.
		 */
		public function set_columns_queried( $columns_queried ) {

			$this->columns_queried = $columns_queried;

		}

		/**
		 * Enable or disable bulk actions
		 *
		 * @since   1.0.0
		 *
		 * @param boolean $bulk_actions_enabled TRUE = allowed, FALSE = not allowed.
		 */
		public function set_bulk_actions_enabled( $bulk_actions_enabled ) {

			$this->bulk_actions_enabled = $bulk_actions_enabled;

		}

		/**
		 * Enable or disable bulk export options
		 *
		 * @since   1.0.0
		 *
		 * @param boolean $bulk_export_enabled TRUE = allowed, FALSE = not allowed.
		 */
		public function set_bulk_export_enabled( $bulk_export_enabled ) {

			$this->bulk_export_enabled = $bulk_export_enabled;

		}

		/**
		 * Show or hide search box
		 *
		 * @since   1.0.0
		 *
		 * @param boolean $search_box_enabled TRUE = show search box, FALSE = do not show search bow.
		 */
		public function set_search_box_enabled( $search_box_enabled ) {

			$this->search_box_enabled = $search_box_enabled;

		}

		/**
		 * Set page title
		 *
		 * @since   1.0.0
		 *
		 * @param string $title Page title.
		 */
		public function set_title( $title ) {

			$this->title = $title;

		}

		/**
		 * Set page subtitle
		 *
		 * @since   1.0.0
		 *
		 * @param string $subtitle Page subtitle.
		 */
		public function set_subtitle( $subtitle ) {

			$this->subtitle = $subtitle;

		}

		/**
		 * I18n text displayed when no data found
		 *
		 * @since   1.0.0
		 */
		public function no_items() {

			echo esc_html__( 'No data found', 'wp-data-access' );

		}

		/**
		 * Use this method to build parent child relationships.
		 *
		 * Overwrite this function if you want to use the list table as a child list table related to some parent
		 * form. You can add parent arguments to calls to make sure you get back to the right parent.
		 *
		 * @since   1.5.0
		 *
		 * @return string String contains parent arguments.
		 */
		protected function add_parent_args_as_string() {
			return '';
		}

		/**
		 * Render columns
		 *
		 * Three links are provided for every list table row:
		 * + 'View' => Link to view form (readonly data entry form)
		 * + 'Edit' => Link to data entry form (updatable)
		 * + 'Delete' => Link to delete record from database table
		 *
		 * Links to view, edit and delete records are only available if a primary key for the table is found. Without
		 * a primary key unique identification of records is not possible.
		 *
		 * Links to action are offered through hidden forms to prevent long URL's and problem when refreshing pages
		 * manually. More information about how and why is provided in source code at place where I thoug the
		 * information could be helpful.
		 *
		 * @since   1.0.0
		 *
		 * @param array  $item Column info.
		 * @param string $column_name Column name.
		 *
		 * @return mixed Value display in list table column
		 */
		public function column_default( $item, $column_name ) {

			if ( $this->wpda_list_columns->get_table_columns()[0]['column_name'] === $column_name ) {
				// First column: add row actions.
				$count = count( $this->wpda_list_columns->get_table_primary_key() );
				if ( 0 === $count ) {
					// No actions without a primary key!
					// This automatically covers view processing correctly.
					return $this->render_column_content( $item[ $column_name ] );
				} else {
					// Check rights.
					if ( null !== $this->show_view_link ) {
						$allow_view = $this->show_view_link;
					} else {
						$allow_view = WPDA::get_option( WPDA::OPTION_BE_VIEW_LINK );
					}
					if ( null !== $this->allow_update ) {
						$allow_update = $this->allow_update;
					} else {
						$allow_update = WPDA::get_option( WPDA::OPTION_BE_ALLOW_UPDATE );
					}
					if ( null !== $this->allow_delete ) {
						$allow_delete = $this->allow_delete;
					} else {
						$allow_delete = WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE );
					}

					if ( 'off' === $allow_view && 'off' === $allow_update && 'off' === $allow_delete ) {
						// No rights!
						$actions = [];
						$this->column_default_add_action( $item, $column_name, $actions );
						if ( is_array( $actions ) && count( $actions ) > 0 ) {
							return sprintf( '%1$s %2$s', $this->render_column_content( $item[ $column_name ] ), $this->row_actions( $actions ) );
						} else {
							return sprintf( '%1$s', $this->render_column_content( $item[ $column_name ] ) );
						}
					}

					// To prevent our URLs containing many arguments, we'll use post to submit row actions. Since our
					// list table is build within a form and we cannot use nested forms we'll use a container (id =
					// wpda_invisible_container) defined outside the list table, and add our forms to that container.
					// We'll use jQuery to add our forms to the container. From the links in our rows we can then just
					// submit any form in that container with jQuery as well.
					$form_id       = '_' . self::$list_number++;
					$wp_nonce_keys = '';
					// We need to add keys and values for multi column primary keys.
					foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
						$wp_nonce_keys .= "-{$item[$key]}";
					}

					// Prepare argument schema name.
					if ( '' === $this->schema_name ) {
						$schema_name = '';
					} else {
						$schema_name = '&schema_name=' . esc_attr( $this->schema_name );
					}

					// Prepare argument page.
					$page = esc_attr( $this->page );

					// Prepare argument table name.
					$table_name = esc_attr( $this->table_name );

					if ( 'on' === $allow_view ) {
						// Build the row action.
						// Use jQuery to add form to container.
						$view_form =
							"<form" .
								" id='view_form$form_id'" .
								" action='?page=$page$schema_name&table_name=$table_name'" .
								" method='post'>" .
								$this->get_key_input_fields( $item ) .
								$this->add_parent_args_as_string() .
								"<input type='hidden' name='action' value='view' />" .
								$this->page_number_item .
							"</form>"
						?>

						<script language="JavaScript">
							jQuery("#wpda_invisible_container").append("<?php echo $view_form; ?>");
						</script>

						<?php

						// Add link to submit form.
						$actions['view'] = sprintf(
							'<a href="javascript:void(0)" 
                                    class="edit"  
                                    onclick="jQuery(\'#%s\').submit()">
                                    View
                                </a>
                                ',
							"view_form$form_id"
						);
					}

					if ( 'on' === $allow_update ) {
						// Build the row action.
						// Use jQuery to add form to container.
						$edit_form =
							"<form" .
								" id='edit_form$form_id'" .
								" action='?page=$page$schema_name&table_name=$table_name'" .
								" method='post'>" .
								$this->get_key_input_fields( $item ) .
								$this->add_parent_args_as_string() .
								"<input type='hidden' name='action' value='edit' />" .
								$this->page_number_item .
							"</form>";
						?>

						<script language="JavaScript">
							jQuery("#wpda_invisible_container").append("<?php echo $edit_form; ?>");
						</script>

						<?php

						// Add link to submit form.
						$actions['edit'] = sprintf(
							'<a href="javascript:void(0)" 
                                    class="edit"  
                                    onclick="jQuery(\'#%s\').submit()">
                                    Edit
                                </a>
                                ',
							"edit_form$form_id"
						);
					}

					if ( 'on' === $allow_delete ) {
						// Build the row action.
						$wp_nonce_action = "wpda-delete-{$this->table_name}$wp_nonce_keys";
						$wp_nonce        = esc_attr( wp_create_nonce( $wp_nonce_action ) );
						// Use jQuery to add form to container.
						$delete_form =
							"<form" .
								" id='delete_form$form_id'" .
								" action='?page=$page$schema_name&table_name=$table_name'" .
								" method='post'>" .
								$this->get_key_input_fields( $item ) .
								$this->add_parent_args_as_string() .
								"<input type='hidden' name='action' value='delete' />" .
								"<input type='hidden' name='_wpnonce' value='$wp_nonce'>" .
								$this->page_number_item .
							"</form>";
						?>

						<script language="JavaScript">
							jQuery("#wpda_invisible_container").append("<?php echo $delete_form; ?>");
						</script>

						<?php

						// Add link to submit form.
						$actions['delete'] = sprintf(
							'<a href="javascript:void(0)" 
                                    class="delete"  
                                    onclick="if (showNotice.warn()) jQuery(\'#%s\').submit()">
                                    Delete
                                </a>
                                ',
							"delete_form$form_id"
						);
					}

					// Developers can add actions by adding their own implementation of following method.
					$this->column_default_add_action( $item, $column_name, $actions );

					// Array $actions must have at least one element, otherwise we wouldn't be here. Skip IDE message!
					return sprintf( '%1$s %2$s', $this->render_column_content( $item[ $column_name ] ), $this->row_actions( $actions ) );
				}
			} else {
				return $this->render_column_content( $item[ $column_name ] );
			}

		}

		protected function render_column_content( $column_content ) {
			if ( 'off' === WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP_SWITCH ) &&
				WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP ) < strlen( $column_content )
			) {
				$title = sprintf(
					__( 'Output limited to %1$s characters', 'wp-data-access' ),
					WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP )
				);
				// return stripslashes( substr( $column_content, 0, WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP ) ) ) .
				//	' <a href="javascript:void(0)" title="' . $title . '">&bull;&bull;&bull;</a>';
				return substr( esc_html( str_replace( '&', '&amp;', $column_content ) ), 0, WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP ) ) .
					' <a href="javascript:void(0)" title="' . $title . '">&bull;&bull;&bull;</a>';
			} else {
				// return stripslashes( $column_content );
				return esc_html( str_replace( '&', '&amp;', $column_content ) );
			}
		}

		/**
		 * Generate hidden fields for keys
		 *
		 * @param  array $item Column info.
		 *
		 * @return string String containg key items and values.
		 */
		protected function get_key_input_fields( $item ) {

			$key_input_fields = '';

			foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
				$key_name  = esc_attr( $key );
				$key_value = esc_attr( $item[ $key ] );
				$key_input_fields .= "<input type='hidden' name='$key_name' value='$key_value' />";
			}

			return $key_input_fields;

		}

		/**
		 * Override this method to add actions to first column of row
		 *
		 * @param array  $item Item information.
		 * @param string $column_name Column name.
		 * @param array  $actions Array of actions to be added to row.
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) {}

		/**
		 * Render bulk edit checkbox
		 *
		 * @since   1.0.0
		 *
		 * @param array $item Column list.
		 *
		 * @return string Content for checkbox column.
		 */
		public function column_cb( $item ) {

			if ( ! $this->bulk_actions_enabled ) {
				// Bulk actions disabled.
				return '';
			}

			if ( empty( $this->wpda_list_columns->get_table_primary_key() ) ) {
				// Table has no primary key: no bulk actions allowed!
				// Primary key is used to ensure uniqueness!
				return '';
			}

			// Build CB value: Use json format for multi column primary keys.
			$cb_value = (object) null;
			foreach ( $this->wpda_list_columns->get_table_primary_key() as $primary_key_column ) {
				// JSON string key and values will be double quoted. Therefor a slash must be added to double quotes in values.
				$cb_value->$primary_key_column = esc_attr( str_replace( '"', '\"', $item[ $primary_key_column ] ) );
			}

			return "<input type='checkbox' name='bulk-selected[]' value='" . json_encode( $cb_value ) . "' />";

		}

		/**
		 * Show list table page
		 *
		 * Inside the list table page, the list table is shown. The necessary functionality to show the list table
		 * specifically is found in method {@see WPDA_List_Table::display()}.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::display()
		 */
		public function show() {

			// Check for import requested.
			if ( null !== $this->wpda_import ) {
				$this->wpda_import->check_post();
			}

			// Prepare list table items.
			$this->prepare_items();

			// Show list table.
			?>

			<div class="wrap">
				<h1 class="wp-heading-inline">

					<?php

					if ( self::LIST_BASE_TABLE !== $this->table_name &&
						( \WP_Data_Access_Admin::PAGE_MAIN === $this->page ) ) {

						?>

						<a
								href="?page=<?php echo esc_attr( $this->page ); ?>"
								style="display: inline-block; vertical-align: unset;"
								class="dashicons dashicons-arrow-left-alt"
								title="<?php echo \WP_Data_Access_Admin::PAGE_MAIN === $this->page ? esc_html__( 'Back to Data Explorer', 'wp-data-access' ) : esc_html__( 'Back to Favourites', 'wp-data-access' ); ?>"
						></a>

						<?php

					}

					?>

					<span><?php echo esc_attr( $this->title ); ?></span>

				</h1>

				<?php

				$this->add_header_button();

				?>

				<div><strong><?php echo wp_kses( $this->subtitle, [ 'span' => [ 'class' => [] ] ] ); ?></strong></div>
				<iframe id="stealth_mode" style="display:none;"></iframe>
				<div id="wpda_invisible_container" style="display:none;"></div>

				<?php

				// Add import container.
				if ( null !== $this->wpda_import ) {
					$this->wpda_import->add_container();
				}

				?>

				<form
						id="wpda_main_form"
						method="post"
						action="?page=<?php echo esc_attr( $this->page ); ?><?php echo self::LIST_BASE_TABLE === $this->table_name ? '' : ( '' === $this->schema_name ? '' : '&schema_name=' . esc_attr( $this->schema_name ) ) . '&table_name=' . esc_attr( $this->table_name ); ?>"
				>

					<?php

					if ( $this->search_box_enabled ) {
						$this->search_box( __( 'search', 'wp-data-access' ), 'search_id' );
					}

					$this->display();

					if ( '' === $this->get_bulk_actions() ) {
						// Add action item containing valie -1. This will allow sorting if no bulk action listbox is displayed.
						?>
						<input type="hidden" name="action" value="-1"/>
						<?php
					}

					?>

					<input id="wpda_main_form_orderby" type="hidden" name="orderby"
						value="<?php echo ( isset( $_REQUEST['orderby'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_form_order" type="hidden" name="order"
						value="<?php echo ( isset( $_REQUEST['order'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_form_post_mime_type" type="hidden" name="post_mime_type"
						value="<?php echo ( isset( $_REQUEST['post_mime_type'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['post_mime_type'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_form_detached" type="hidden" name="detached"
						value="<?php echo ( isset( $_REQUEST['detached'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['detached'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_db_schema" type="hidden" name="wpda_main_db_schema"
						value="<?php echo ( isset( $_REQUEST['wpda_main_db_schema'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['wpda_main_db_schema'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_favourites" type="hidden" name="wpda_main_favourites"
						   value="<?php echo ( isset( $_REQUEST['wpda_main_favourites'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['wpda_main_favourites'] ) ) ) : ''; // input var okay. ?>"/>
					<?php wp_nonce_field( 'wpda-export-*', '_wpnonce', false ); ?>
					<?php wp_nonce_field( 'wpda-delete-*', '_wpnonce2', false ); ?>
					<?php wp_nonce_field( 'wpda-drop-*', '_wpnonce3', false ); ?>
					<?php wp_nonce_field( 'wpda-truncate-*', '_wpnonce4', false ); ?>
				</form>
			</div>
            <?php $this->bind_action_buttons(); ?>

			<?php

		}

        /**
         * Bind javascript code to action buttons
         *
         * @since   1.6.2
         */
        protected function bind_action_buttons() {
            ?>
            <script language="JavaScript">
                jQuery(document).ready(function() {
                    jQuery("#doaction").click(function() {
                        return wpda_action_button();
                    });
                    jQuery("#doaction2").click(function() {
                        return wpda_action_button();
                    });
                });
           </script>
            <?php
        }

		/**
		 * Add button to page header
		 *
		 * By default "add new" and "import" buttons are added (depending on the settings). Overwrite this method to
		 * add your own buttons.
		 *
		 * @since   1.0.1
		 *
		 * @param string $add_param Parameter to be added to form action.
		 */
		protected function add_header_button( $add_param = '' ) {

			if ( 'off' === $this->allow_insert ) {
				if ( null !== $this->wpda_import ) {
					$this->wpda_import->add_button();
				}
			} else {
				if ( WPDA::is_wpda_table( $this->table_name ) ||
					( 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_INSERT ) &&
						count( $this->wpda_list_columns->get_table_primary_key() ) ) > 0
				) {

					?>

					<form
							method="post"
							action="?page=<?php echo esc_attr( $this->page ); ?><?php echo '' === $this->schema_name ? '' : '&schema_name=' . esc_attr( $this->schema_name ); ?>&table_name=<?php echo esc_attr( $this->table_name ); ?><?php echo esc_attr( $add_param ); ?>"
							style="display: inline-block; vertical-align: unset;"
					>
						<div>
							<input type="hidden" name="action" value="new">
							<input type="submit" value="<?php echo esc_html__( 'Add New', 'wp-data-access' ); ?>"
								   class="page-title-action">

							<?php

							// Add import button to title.
							if ( null !== $this->wpda_import ) {
								$this->wpda_import->add_button();
							}

							?>

						</div>
					</form>

					<?php

				} else {

					// Add import button to title.
					if ( null !== $this->wpda_import ) {
						$this->wpda_import->add_button();
					}
				}
			}

		}

		/**
		 * Prepares the list of items for displaying
		 *
		 * Overwrites WP_List_Table::prepare_items()
		 *
		 * @since   1.0.0
		 */
		public function prepare_items() {

			// Construct where clause with search values provided in the search box.
			// Result (where clause) is written to $this->where.
			$this->construct_where_clause();

			$this->process_bulk_action();

			$option               = 'wpda_rows_per_page_' . str_replace( '.', '_', $this->table_name );
			$this->items_per_page = $this->get_items_per_page(
				$option,
				WPDA::get_option( WPDA::OPTION_BE_PAGINATION )
			);
			$this->current_page   = $this->get_pagenum();
			$total_items          = $this->record_count();
			$total_pages          = ceil( $total_items / $this->items_per_page );
			if ( $this->search_value != $this->search_value_old ) {
				$this->current_page = 1;
			}
			if ( $this->current_page > $total_pages ) {
				$this->current_page = $total_pages;
			}
			$this->set_pagination_args(
				[
					'total_items' => $total_items,
					'total_pages' => $total_pages,
					'per_page'    => $this->items_per_page,
				]
			);

			$this->get_rows(); // Written to $this->items in base class (WP_List_Table).

			$columns = $this->get_columns();

			$hidden = $this->get_hidden_columns();
			if ( false === $hidden ) {
				// Hidden column should be return by: $this->get_hidden_columns()
				// In some cases I kept getting a bool(false) however. Calling get_user_option directly mostly works if
				// I first check the return value, which is bool(false) if the meta_key is not found in wp_usermeta.
				$hidden_check = get_user_option( 'manage' . get_current_screen()->id . 'columnshidden' );
				if ( is_array( $hidden_check ) ) {
					$hidden = $hidden_check;
				}
				if ( false === $hidden ) {
					// We cannot continue with $hidden=false as we'll run into an error later.
					$hidden = [];
				}
			}

			$sortable              = $this->get_sortable_columns();
			$primary               = $this->get_primary_column();
			$this->_column_headers = [ $columns, $hidden, $sortable, $primary ];

		}

		/**
		 * Build where clause
		 *
		 * Arguments might come from the URL so we need to check for SQL injection and use prepare. The resulting
		 * where clause is written directly to member $this->where.
		 *
		 * @since   1.0.0
		 */
		protected function construct_where_clause() {

			global $wpdb;

			if ( null !== $this->search_value && '' !== $this->search_value ) {
				$nowhere = ( '' === $this->where );
				$search_values = '%' . esc_attr( $this->search_value ) . '%';
				$where_current = '';
				$where_first = true;
				foreach ( $this->wpda_list_columns->get_table_columns() as $column ) {
					if ( 'varchar' === $column['data_type'] || 'enum' === $column['data_type'] ) {
						$where_current_prepare = "`{$column['column_name']}` like %s";
						if ( ! $where_first ) {
							$where_current .= ' or ';
						}
						$where_current .= $wpdb->prepare( $where_current_prepare, $search_values ); // WPCS: unprepared SQL OK.
						$where_first = false;
					}
				}
				if ( '' !== $where_current ) {
					$this->where = $nowhere ? " where $where_current " : " {$this->where} and ($where_current) ";
				}
				if ( '' === $this->where ) {
					// No search columns. Must result in no rows.
					$this->where = ' where 1=2 ';
				}
			}

		}

		/**
		 * Process bulk actions
		 *
		 * Delete and bulk-delete actions use the primary key list as a reference. Before performing the actual delete
		 * statement(s) we'll check the provided column names against the data dictionary. This will protect us against
		 * SQL injection attacks.
		 *
		 * For export actions table names will not be checked here. They are handed over WPDA_Export where the validity
		 * and access rights are checked anyway (to be safe).
		 *
		 * All actions can be processed only with a valid wpnonce value.
		 *
		 * To perform actions the user must have the appropriate rights.
		 *
		 * @since   1.0.0
		 */
		public function process_bulk_action() {

			switch ( $this->current_action() ) {
				case 'delete':
					// Check access rights.
					if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE ) ) {
						// Deleting records from list table is not allowed.
						wp_die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					// Prepare wp_nonce action security check.
					$wp_nonce_action = "wpda-delete-{$this->table_name}";

					$row_to_be_deleted = []; // Gonna hold the row to be deleted.
					$i                 = 0; // Index, necessary for multi column keys.

					// Check all key columns.
					foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
						// Check if key is available.
						if ( ! isset( $_REQUEST[ $key ] ) ) { // input var okay.
							wp_die( esc_html__( 'ERROR: Invalid URL', 'wp-data-access' ) );
						}

						// Write key value pair to array.
						$row_to_be_deleted[ $i ]['key']   = $key;
						$row_to_be_deleted[ $i ]['value'] = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); // input var okay.
						$i++;

						// Add key values to wp_nonce action.
						$wp_nonce_action .= '-' . sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); // input var okay.
					}

					// Check if delete is allowed.
					$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, $wp_nonce_action ) ) {
						wp_die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					// All key column values available: delete record.
					// Prepare named array for delete operation.
					$next_row_to_be_deleted = [];
					$count_rows             = count( $row_to_be_deleted );
					for ( $i = 0; $i < $count_rows; $i++ ) {
						$next_row_to_be_deleted[ $row_to_be_deleted[ $i ]['key'] ] = $row_to_be_deleted[ $i ]['value'];
					}

					if ( $this->delete_row( $next_row_to_be_deleted ) ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Row deleted', 'wp-data-access' ),
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => __( 'Could not delete row', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}

					break;
				case 'bulk-delete':
					// Check access rights.
					if ( WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE ) !== 'on' ) {
						// Deleting records from list table is not allowed.
						die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					// We first need to check if all the necessary information is available.
					if ( ! isset( $_REQUEST['bulk-selected'] ) ) { // input var okay.
						// Nothing to delete.
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Nothing to delete', 'wp-data-access' ),
							]
						);
						$msg->box();

						return;
					}

					// Check if delete is allowed.
					$wp_nonce_action = 'wpda-delete-*';
					$wp_nonce        = isset( $_REQUEST['_wpnonce2'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce2'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, $wp_nonce_action ) ) {
						die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					$bulk_rows = $_REQUEST['bulk-selected'];
					$no_rows   = count( $bulk_rows ); // # rows to be deleted.

					$rows_to_be_deleted = []; // Gonna hold rows to be deleted.

					for ( $i = 0; $i < $no_rows; $i++ ) {
						// Write "json" to named array. Need to strip slashes twice. Once for the normal conversion
						// and once extra for the pre-conversion of double quotes in method column_cb().
						$row_object = json_decode( stripslashes( stripslashes( $bulk_rows[ $i ] ) ), true );
						if ( $row_object ) {
							$j = 0; // Index used to build array.

							// Check all key columns.
							foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
								// Check if key is available.
								if ( ! isset( $row_object[ $key ] ) ) {
									wp_die( esc_html__( 'ERROR: Invalid URL', 'wp-data-access' ) );
								}

								// Write key value pair to array.
								$rows_to_be_deleted[ $i ][ $j ]['key']   = $key;
								$rows_to_be_deleted[ $i ][ $j ]['value'] = $row_object[ $key ];
								$j++;

							}
						}
					}

					// Looks like eveything is there. Delete records from table...
					$no_key_cols              = count( $this->wpda_list_columns->get_table_primary_key() );
					$rows_succesfully_deleted = 0; // Number of rows succesfully deleted.
					$rows_with_errors         = 0; // Number of rows that could not be deleted.
					for ( $i = 0; $i < $no_rows; $i++ ) {
						// Prepare named array for delete operation.
						$next_row_to_be_deleted = [];

						$row_found = true;
						for ( $j = 0; $j < $no_key_cols; $j++ ) {
							if ( isset( $rows_to_be_deleted[ $i ][ $j ]['key'] ) ) {
								$next_row_to_be_deleted[ $rows_to_be_deleted[ $i ][ $j ]['key'] ] = $rows_to_be_deleted[ $i ][ $j ]['value'];
							} else {
								$row_found = false;
							}
						}

						if ( $row_found ) {
							if ( $this->delete_row( $next_row_to_be_deleted ) ) {
								// Row(s) succesfully deleted.
								$rows_succesfully_deleted++;
							} else {
								// An error occured during the delete operation: increase error count.
								$rows_with_errors++;
							}
						} else {
							// An error occured during the delete operation: increase error count.
							$rows_with_errors++;
						}
					}

					// Inform user about the results of the operation.
					$message = '';

					if ( 1 === $rows_succesfully_deleted ) {
						$message = __( 'Row deleted', 'wp-data-access' );
					} elseif ( $rows_succesfully_deleted > 1 ) {
						$message = "$rows_succesfully_deleted " . __( 'rows deleted', 'wp-data-access' );
					}

					if ( '' !== $message ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => $message,
							]
						);
						$msg->box();
					}

					$message = '';

					if ( $rows_with_errors > 0 ) {
						$message = __( 'Not all rows have been deleted', 'wp-data-access' );
					}

					if ( '' !== $message ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => $message,
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}

					break;
				case 'bulk-export':
				case 'bulk-export-xml':
				case 'bulk-export-json':
				case 'bulk-export-excel':
				case 'bulk-export-csv':
					// Check access rights.
					if ( ! WPDA::is_wpda_table( $this->table_name ) ) {
						if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_EXPORT_ROWS ) ) {
							// Exporting rows from list table is not allowed.
							die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
						}
					}

					// We first need to check if all the necessary information is available.
					if ( ! isset( $_REQUEST['bulk-selected'] ) ) { // input var okay.
						// Nothing to export.
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Nothing to export', 'wp-data-access' ),
							]
						);
						$msg->box();

						return;
					}

					// Check if export is allowed.
					$wp_nonce_action = 'wpda-export-*';
					$wp_nonce        = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, $wp_nonce_action ) ) {
						die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					$bulk_rows = $_REQUEST['bulk-selected'];
					$no_rows   = count( $bulk_rows ); // # rows to be exported.

					$format_type = '';
					switch ( $this->current_action() ) {
						case 'bulk-export-xml':
							$format_type = 'xml';
							break;
						case 'bulk-export-json':
							$format_type = 'json';
							break;
						case 'bulk-export-excel':
							$format_type = 'excel';
							break;
						case 'bulk-export-csv':
							$format_type = 'csv';
					}

					$querystring = "?action=wpda_export&type=row&mysql_set=off&show_create=off&show_comments=off&schema_name={$this->schema_name}&table_names={$this->table_name}&_wpnonce=$wp_nonce&format_type=$format_type";

					$j = 0;
					for ( $i = 0; $i < $no_rows; $i++ ) {
						// Write "json" to named array. Need to strip slashes twice. Once for the normal conversion
						// and once extra for the pre-conversion of double quotes in method column_cb().
						$row_object = json_decode( stripslashes( stripslashes( $bulk_rows[ $i ] ) ), true );
						if ( $row_object ) {
							// Check all key columns.
							foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
								// Check if key is available.
								if ( ! isset( $row_object[ $key ] ) ) {
									wp_die( esc_html__( 'ERROR: Invalid URL', 'wp-data-access' ) );
								}

								// Write key value pair to array.
								$querystring .= "&{$key}[{$j}]=" . urlencode( $row_object[ $key ] );
							}
							$j++;
						}
					}

					// Export rows.
					echo '
						<script>
							jQuery(document).ready(function() {
								jQuery("#stealth_mode").attr("src","' . $querystring . '");
							});
						</script>
					';
			}

		}

		/**
		 * Delete record from database table
		 *
		 * The table must have a primary key and the values for all primary key colummns must be provided in the
		 * request. The where clause must be a named array in format: ['column_name'] = 'value'
		 *
		 * @since   1.0.0
		 *
		 * @param string $where Where clause.
		 * @return mixed
		 */
		public function delete_row( $where ) {

			global $wpdb;

			if ( '' === $this->schema_name || $wpdb->dbname === $this->schema_name ) {
				// Table is located in WordPress schema.
				return $wpdb->delete( $this->table_name, $where ); // db call ok; no-cache ok.
			} else {
				// Table is located in another schema.
				$db = new \wpdb( DB_USER, DB_PASSWORD, $this->schema_name, DB_HOST );
				$result = $db->delete( $this->table_name, $where ); // db call ok; no-cache ok.
				$db->close();
				return $result;
			}

		}

		/**
		 * Number of records in database table
		 *
		 * Returns the number of records in the database table. Where clause must be prepared in advance and written
		 * to $this->where ({@see WPDA_List_Table::construct_where_clause()})
		 *
		 * @since   1.0.0
		 *
		 * @return null|string Number of rows in the current table
		 */
		public function record_count() {

			global $wpdb;

			if ( '' === $this->schema_name ) {
				$query = "
					select count(*) 
					from `{$this->table_name}`
					{$this->where}
				";
			} else {
				if ( $this->table_name === self::LIST_BASE_TABLE ) {
					$query = "
						select count(*) 
						from {$this->table_name}
						{$this->where}
					";
				} else {
					$query = "
						select count(*) 
						from `{$this->schema_name}`.`{$this->table_name}`
						{$this->where}
					";
				}
			}

			return $wpdb->get_var( $query ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

		/**
		 * Perform query to retrieve rows from database
		 *
		 * Where clause must be prepared in advance and written to $this->where
		 * ({@see WPDA_List_Table::construct_where_clause()}).
		 *
		 * No return value. Result is directly written to $this->items (base class member).
		 *
		 * @since   1.0.0
		 */
		public function get_rows() {

			global $wpdb;

			// Selected columns cannot be changed by the user at this time. No check for SQL injection needed now.
			// This might change in the future when users are allowed to change or set this value. A method named
			// column_exists() in WPDA_Dictionary_Checks is already available for this purpose.
			$selected_columns = '*';
			if ( isset( $this->columns_queried ) ) {

				$selected_columns = implode( ',', $this->columns_queried );

			}

			if ( '' === $this->schema_name ) {
				$query = "
					select $selected_columns 
					from `{$this->table_name}`
					{$this->where}
				";
			} else {
				if ( $this->table_name === self::LIST_BASE_TABLE ) {
					$query = "
						select $selected_columns 
						from {$this->table_name}
						{$this->where}
					";
				} else {
					$query = "
						select $selected_columns 
						from `{$this->schema_name}`.`{$this->table_name}`
						{$this->where}
					";
				}
			}

			$orderby = '';
			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$orderby_arg = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // input var okay.

				if ( ! empty( $_REQUEST['order'] ) ) {
					$order_arg = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ); // input var okay.
				} else {
					$order_arg = '';
				}

				$columns = $this->get_sortable_columns();

				// Check column name for SQL injection.
				if ( isset( $columns[ $orderby_arg ] ) || $this->wpda_data_dictionary->column_exists( $orderby_arg ) ) {

					// Column name exists in current table, safely continue...
					$orderby = " order by $orderby_arg";

					// Prevent SQL injection for order. If 'desc' is found result wille be ordered desc. In all other
					// cases we'll order asc.
					$orderby .= strtolower( trim( $order_arg ) ) === 'desc' ? ' desc' : ' asc';

				} else {

					// The user provided a column name which is not in the table. Most probably the result of a
					// SQL injection attack, so let's terminate.
					wp_die( esc_html__( 'ERROR: Invalid URL', 'wp-data-access' ) );

				}
			}
			$query .= $orderby;
			$query .= " limit {$this->items_per_page}";
			$offset = ( $this->current_page - 1 ) * $this->items_per_page;
			if ( $offset > 0 ) {
				$query .= " offset $offset";
			}

			$this->items = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

		}

		/**
		 * Return an associative array of columns
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_columns() {

			$columns = [];

			if ( $this->bulk_actions_enabled ) {
				if ( ! empty( $this->wpda_list_columns->get_table_primary_key() ) ) {
					// Tables has primary key: bulk actions allowed!
					// Primary key is used to ensure uniqueness.
					$columns = [ 'cb' => '<input type="checkbox" />' ];
				}
			}

			$columnlist = $this->wpda_list_columns->get_table_column_headers();
			foreach ( $columnlist as $key => $value ) {
				$columns[ $key ] = $value;
				// Check for alternative column header.
				if ( isset( $this->column_headers[ $key ] ) ) {
					// Alternative header found: use it.
					$columns[ $key ] = $this->column_headers[ $key ];
				} else {
					// Default behaviour: get column header from generated label.
					$columns[ $key ] = $this->wpda_list_columns->get_column_label( $key );
				}
			}

			return $columns;

		}

		/**
		 * List of columns to make sortable
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_sortable_columns() {

			$columns = [];

			// Get column names from result set.
			if ( $this->items ) {
				foreach ( $this->items[0] as $key => $value ) {

					$columns[ $key ] = [ $key, false ];

				}
			}

			return $columns;

		}

		/**
		 * Display the search box
		 *
		 * @since   1.0.0
		 *
		 * @param string $text The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			if ( null === $this->search_value && ! $this->has_items() ) {
				return;
			}

			$input_id = $input_id . '-search-input';

			?>
			<p class="search-box">
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $this->search_item_name ); ?>"
					value="<?php echo esc_attr( $this->search_value ); ?>"/>
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
				<input type="hidden" name="<?php echo esc_attr( $this->search_item_name ); ?>_old_value" value="<?php echo esc_attr( $this->search_value ); ?>"/>
			</p>
			<?php
		}

		/**
		 * Get search value (entered by the user or taken from cookie).
		 *
		 * @since   1.5.0
		 *
		 * @return string
		 */
		protected function get_search_value() {
			if ( 'off' === WPDA::get_option( WPDA::OPTION_BE_REMEMBER_SEARCH ) ) {
				if ( isset( $_REQUEST[ $this->search_item_name ] ) ) {
					return sanitize_text_field( wp_unslash( $_REQUEST[ $this->search_item_name ] ) ); // input var okay.
				}
			}

			$cookie_name = $this->page . '_search_' . str_replace('.', '_', $this->table_name);
			if ( isset( $_REQUEST[ $this->search_item_name ] ) && '' !== $_REQUEST[ $this->search_item_name ] ) { // input var okay.
				return sanitize_text_field( wp_unslash( $_REQUEST[ $this->search_item_name ] ) ); // input var okay.
			} elseif ( isset( $_COOKIE[ $cookie_name ] ) ) {
				return $_COOKIE[ $cookie_name ];
			} else {
				return null;
			}
		}

		/**
		 * Print column headers
		 *
		 * Overriding original method print_column_headers to support post instead of get.
		 * Changes are marked!
		 *
		 * @since   1.0.0
		 *
		 * @staticvar $cb_counter int
		 *
		 * @param boolean $with_id Whether to set the id attribute or not.
		 */
		public function print_column_headers( $with_id = true ) {
			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

			// *********************
			// *** BEGIN CHANGES ***
			// *********************
			// Code removed.
			// *******************
			// *** END CHANGES ***
			// *******************
			if ( isset( $_REQUEST['orderby'] ) ) {
				$current_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // input var okay.
			} else {
				$current_orderby = '';
			}

			if ( isset( $_REQUEST['order'] ) && 'desc' === sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) { // input var okay.
				$current_order = 'desc';
			} else {
				$current_order = 'asc';
			}

			if ( ! empty( $columns['cb'] ) ) {
				static $cb_counter = 1;
				$columns['cb']     = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . esc_html__( 'Select All' ) . '</label>'
					. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
				$cb_counter++;
			}

			foreach ( $columns as $column_key => $column_display_name ) {
				$class = [ 'manage-column', "column-$column_key" ];

				if ( in_array( $column_key, $hidden ) ) {
					$class[] = 'hidden';
				}

				if ( 'cb' === $column_key ) {
					$class[] = 'check-column';
				} elseif ( in_array( $column_key, [ 'posts', 'comments', 'links' ] ) ) {
					$class[] = 'num';
				}

				if ( $column_key === $primary ) {
					$class[] = 'column-primary';
				}

				if ( isset( $sortable[ $column_key ] ) ) {
					list( $orderby, $desc_first ) = $sortable[ $column_key ];

					if ( $current_orderby === $orderby ) {
						$order   = 'asc' === $current_order ? 'desc' : 'asc';
						$class[] = 'sorted';
						$class[] = $current_order;
					} else {
						$order   = $desc_first ? 'desc' : 'asc';
						$class[] = 'sortable';
						$class[] = $desc_first ? 'asc' : 'desc';
					}

					// *********************
					// *** BEGIN CHANGES ***
					// *********************
					// Code removed.
					// *******************
					// *** END CHANGES ***
					// *******************
				}

				$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
				$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
				$id    = $with_id ? "id='$column_key'" : '';

				if ( ! empty( $class ) ) {
					$class = "class='" . join( ' ', $class ) . "'";
				}

				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				echo '<' . esc_attr( $tag ) . ' ' . wp_kses_data( $scope ) . ' ' . wp_kses_data( $id ) . ' ' .
					wp_kses_data( $class ) . '>';

				if ( isset( $sortable[ $column_key ] ) ) {

				?>
					<a href="javascript:void(0)"
						onclick="jQuery('#wpda_main_form_orderby').val('<?php echo esc_attr( $orderby ); ?>'); jQuery('#wpda_main_form_order').val('<?php echo esc_attr( $order ); ?>'); jQuery('#wpda_main_form').submit();">
						<span><?php echo wp_kses_data( $column_display_name ); ?></span><span class="sorting-indicator"></span>
					</a>
				<?php

				} else {
					echo wp_kses(
						$column_display_name,
						[
							'input' => [
								'id'   => [],
								'type' => [],
							],
							'label' => [
								'class' => [],
								'for'   => [],
							],
						]
					);
				}

				echo '</' . esc_attr( $tag ) . '>';

				// *******************
				// *** END CHANGES ***
				// *******************
			}
		}

		/**
		 * Returns an associative array containing the bulk action
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_bulk_actions() {

			if ( ! $this->bulk_actions_enabled ) {
				// Bulk actions disabled.
				return '';
			}

			if ( empty( $this->wpda_list_columns->get_table_primary_key() ) ) {
				// Tables has no primary key: no bulk actions allowed!
				// Primary key is neccesary to ensure uniqueness.
				return '';
			}

			$actions = [];

			if ( WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE ) === 'on' ) {
				$actions = [
					'bulk-delete' => __( 'Delete Permanently', 'wp-data-access' ),
				];
			}

			if (
				$this->bulk_export_enabled && (
					WPDA::is_wpda_table( $this->table_name ) ||
					WPDA::get_option( WPDA::OPTION_BE_EXPORT_ROWS ) === 'on'
				)
			) {
				$actions['bulk-export'] = __( 'Export to SQL', 'wp-data-access' );
				$actions['bulk-export-xml'] = __( 'Export to XML', 'wp-data-access' );
				$actions['bulk-export-json'] = __( 'Export to JSON', 'wp-data-access' );
				$actions['bulk-export-excel'] = __( 'Export to Excel', 'wp-data-access' );
				$actions['bulk-export-csv'] = __( 'Export to CSV', 'wp-data-access' );
			}

			return $actions;

		}

		/**
		 * Generates the table navigation
		 *
		 * Generates the table navigation above or bellow the table and removes the
		 * _wp_http_referrer and _wpnonce because it generates a error about URL too large.
		 *
		 * @since   1.0.0
		 *
		 * @param string $which CSS Class name.
		 * @return void
		 */
		protected function display_tablenav( $which ) {
			if ( ! $this->hide_navigation && $this->items ) {

				?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<div class="alignleft actions">
						<?php $this->bulk_actions(); ?>
					</div>
					<?php
					$this->extra_tablenav( $which );
					$this->pagination( $which );
					?>
					<br class="clear"/>
				</div>
				<?php
			} else {
				?>
				<br class="clear"/>
				<?php
			}

		}

		/**
		 * Display the pagination
		 *
		 * Overriding original method pagination to support post instead of get.
		 * Changes are marked!
		 *
		 * @since   1.0.0
		 *
		 * @param string $which CSS Class name.
		 */
		protected function pagination( $which ) {
			if ( empty( $this->_pagination_args ) ) {
				return;
			}

			$total_items     = $this->_pagination_args['total_items'];
			$total_pages     = $this->_pagination_args['total_pages'];
			$infinite_scroll = false;
			if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
				$infinite_scroll = $this->_pagination_args['infinite_scroll'];
			}

			if ( 'top' === $which && $total_pages > 1 ) {
				$this->screen->render_screen_reader_content( 'heading_pagination' );
			}

			/* translators: %s: number of items (2x) */
			$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

			$current = $this->get_pagenum();
			if ( $this->search_value != $this->search_value_old ) {
				$current = 1;
			}

			// *********************
			// *** BEGIN CHANGES ***
			// *********************
			// Code removed.
			// *******************
			// *** END CHANGES ***
			// *******************
			$page_links = array();

			$total_pages_before = '<span class="paging-input">';
			$total_pages_after  = '</span></span>';

			$disable_first = $disable_last = $disable_prev = $disable_next = false;

			if ( 1 === (int) $current ) {
				$disable_first = true;
				$disable_prev  = true;
			}
			if ( 2 === (int) $current ) {
				$disable_first = true;
			}
			if ( (int) $current === (int) $total_pages ) {
				$disable_last = true;
				$disable_next = true;
			}
			if ( (int) $current === (int) $total_pages - 1 ) {
				$disable_last = true;
			}

			// *********************
			// *** BEGIN CHANGES ***
			// *********************
			$link_with_post_support = "
            <a class='%s' 
                href='javascript:void(0)' 
                onclick='jQuery(\"#current-page-selector\").val(\"%s\"); jQuery(\"#wpda_main_form\").submit();'>
                <span class='screen-reader-text'>%s</span>
                <span aria-hidden='true'>%s</span>
            </a>";
			// *******************
			// *** END CHANGES ***
			// *******************
			if ( $disable_first ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
			} else {
				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				$page_links[] = sprintf(
					$link_with_post_support,
					'first-page button',
					'',
					esc_html__( 'First page' ),
					'&laquo;'
				);
				// *******************
				// *** END CHANGES ***
				// *******************
			}

			if ( $disable_prev ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
			} else {
				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				$page_links[] = sprintf(
					$link_with_post_support,
					'prev-page button',
					max( 1, $current - 1 ),
					esc_html__( 'Previous page' ),
					'&lsaquo;'
				);
				// *******************
				// *** END CHANGES ***
				// *******************
			}

			if ( 'bottom' === $which ) {
				$html_current_page  = $current;
				$total_pages_before = '<span class="screen-reader-text">' . esc_html__( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
			} else {
				$html_current_page = sprintf(
					"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
					'<label for="current-page-selector" class="screen-reader-text">' . esc_html__( 'Current Page' ) . '</label>',
					$current,
					strlen( $total_pages )
				);
			}
			$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
			/* translators: %s: current page/total pages */
			$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

			if ( $disable_next ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
			} else {
				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				$page_links[] = sprintf(
					$link_with_post_support,
					'next-page button',
					min( $total_pages, $current + 1 ),
					esc_html__( 'Next page' ),
					'&rsaquo;'
				);
				// *******************
				// *** END CHANGES ***
				// *******************
			}

			if ( $disable_last ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
			} else {
				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				$page_links[] = sprintf(
					$link_with_post_support,
					'last-page button',
					$total_pages,
					esc_html__( 'Last page' ),
					'&raquo;'
				);
				// *******************
				// *** END CHANGES ***
				// *******************
			}

			$pagination_links_class = 'pagination-links';
			if ( ! empty( $infinite_scroll ) ) {
				$pagination_links_class = ' hide-if-js';
			}
			$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

			if ( $total_pages ) {
				$page_class = $total_pages < 2 ? ' one-page' : '';
			} else {
				$page_class = ' no-pages';
			}
			$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

			echo $this->_pagination;
		}

	}

}
