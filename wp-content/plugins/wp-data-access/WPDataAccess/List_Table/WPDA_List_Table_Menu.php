<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\List_Table
 */

namespace WPDataAccess\List_Table {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Utilities\WPDA_Favourites;
	use WPDataAccess\Utilities\WPDA_Import_Multi;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_List_Table_Menu
	 *
	 * This class implements the Data Explorer shown in the plugin menu.
	 *
	 * WPDA_List_Table_Menu extends WPDA_List_Table. Although both list tables basically offer the same functionality
	 * WPDA_List_Table_Menu selects data from MySQL view 'information_schema.tables', where WPDA_List_Table selects
	 * data from tables that are located in the WordPress database schema. The view that serves as the 'base table'
	 * for WPDA_List_Table_Menu is not updatable. A data entry form is therefor not available for WPDA_List_Table_Menu.
	 *
	 * Export functionality word WPDA_List_Table_Menu differs from WPDA_List_Table as well. WPDA_List_Table_Menu allows
	 * to export single tables, as well as multiple tables at once as s bulk action.
	 *
	 * When the user clicks on 'view', a list table for the selected table of view is shown.
	 *
	 * @package WPDataAccess\List_Table
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_List_Table_Menu extends WPDA_List_Table {

		const LOADING = 'Loading...';

		protected $favourites            = null;
        protected $wpda_main_favourites  = null;
        protected $innodb_file_per_table = true;

		/**
		 * WPDA_List_Table_Menu constructor
		 *
		 * Constructor calls constructor of WPDA_List_Table. Before calling constructor of WPDA_List_Table the
		 * table name is set to {@see WPDA_List_Table::LIST_BASE_TABLE} which gives us the base table for the data
		 * explorer.
		 *
		 * Column headers  and columns queried are defined harcoded as this class handles only one base table and it's
		 * columns (table specific implementation).
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table
		 *
		 * @param array $args See {@see WPDA_List_Table::__construct()}.
		 */
		public function __construct( $args = [] ) {

			global $wpdb;

			$args['table_name'] = WPDA_List_Table::LIST_BASE_TABLE;

			// Add column labels.
			$args['column_headers'] = self::column_headers_labels();

			$args['title'] = __( 'Data Explorer', 'wp-data-access' );

			$args['subtitle'] = ''; // Define an empty subtitle to prevent WPDA_List_Table from adding one.

			parent::__construct( $args );

			$this->set_columns_queried(
				[
					'table_name',
					'if (find_in_set(table_name,\'' . implode( ',', WPDA::get_wp_tables() ) . '\')
						, \'' . WPDA::get_table_type_text( WPDA::TABLE_TYPE_WP ) . '\'
						, if (find_in_set(table_name,\'' . implode( ',', WPDA::get_wpda_tables() ) . '\') 
							, \'' . WPDA::get_table_type_text( WPDA::TABLE_TYPE_WPDA ) . '\'
							, lower(table_type)
						)
					) as table_type',
					'create_time',
					'table_rows',
					'auto_increment',
                    'engine',
					'data_length as data_size',
					'index_length as index_size',
					'data_free as overhead',
                    'table_collation',
				]
			);

			$this->favourites = get_option( WPDA_Favourites::FAVOURITES_OPTION_NAME );

			$this->schema_name = $this->get_schema_name();
			if ( null === $this->schema_name || '' === $this->schema_name ) {
				$this->schema_name = $wpdb->dbname;
			}

			$this->wpda_main_favourites = $this->get_favourites();

			// Instantiate WPDA_Import.
			$this->wpda_import = new WPDA_Import_Multi( "?page={$this->page}", $this->schema_name );

            if (
                'on' !== WPDA::get_option( WPDA::OPTION_BE_EXPORT_TABLES ) &&
                'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_DROP ) &&
                'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_TRUNCATE )
            ){
                $this->bulk_actions_enabled = false;
            }

			$result = $wpdb->get_row("show session variables like 'innodb_file_per_table'");
			if ( ! empty( $result ) ) {
				$this->innodb_file_per_table = ( 'ON' === $result->Value );
			}

		}

		/**
		 * Overwrite method to add structure to the row below.
		 *
		 * @since   1.5.0
		 *
		 * @param object $item Iten info.
		 */
		public function single_row( $item ) {

			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
			$column_not_showns = sizeof($hidden);

			echo '<tr id="rownum_' . self::$list_number . '">';
			$this->single_row_columns( $item );
			echo '</tr>';

            if ( $this->bulk_actions_enabled ) {
                echo '<tr style="height:0;"><td id="rownum_' . self::$list_number . '_x1" colspan="' . (12-$column_not_showns) . '" style="padding:0;"></tr>'; // Fake! Maintain odd/even colors.
                echo '<tr><td style="padding:0;"></td><td id="rownum_' . self::$list_number . '_x2" colspan="' . (11-$column_not_showns) . '" style="padding:0 10px 10px 0;">';
            } else {
                echo '<tr style="height:0;"><td id="rownum_' . self::$list_number . '_x1" colspan="' . (11-$column_not_showns) . '" style="padding:0;"></tr>'; // Fake! Maintain odd/even colors.
                echo '<tr><td id="rownum_' . self::$list_number . '_x2" colspan="' . (11-$column_not_showns) . '" style="padding:0 10px 10px 10px;">';
            }

            echo '<div id="wpda_admin_menu_actions_' . $item['table_name'] . '" style="width:100%;display:none;padding-right:20px;">' . self::LOADING . '</div>';
			echo '</td></tr>';

		}


		/**
		 * Override column_default
		 *
		 * We need to override this method as our table is in fact a view and has no real columns:
		 * $this->wpda_list_columns->get_table_columns() returns no results
		 *
		 * We'll use jquery to write html forms to a container to make them accessible inside our list table.
		 * See WPDA_List_Table->column_default for further explanation.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::column_default()
		 *
		 * @param array  $item Item info.
		 * @param string $column_name Column name.
		 * @return mixed Actions for the current row.
		 */
		public function column_default( $item, $column_name ) {

			if ( 'icons' === $column_name ) {
				// Dummy column where icons are shown (favourites and row admin menu).
				$table_name      = $item['table_name'];
				$favourite_class = 'dashicons-star-empty';
				$favourite_title = 'Add to favourites';
				if ( null !== $this->favourites && false !== $this->favourites ) {
					$favourites_array = $this->favourites;
					if ( isset( $favourites_array[ $table_name ] ) ) {
						$favourite_class = 'dashicons-star-filled';
						$favourite_title = 'Remove from favourites';
					}
				}
				$favourites_menu =
					"<a href=\"javascript:void( 0 )\" title=\"$favourite_title\" onclick=\"wpda_list_table_favourite( '{$this->schema_name}', '$table_name' )\">
						<span id=\"span_favourites_$table_name\" class=\"dashicons $favourite_class\"></span>
					</a>";

				return $favourites_menu;

			}

			if ( 'table_rows' === $column_name ) {
				if ( 'InnoDB' === $item['engine'] ) {
					$innodb_count = WPDA::get_option( WPDA::OPTION_BE_INNODB_COUNT );
					if ( $item[ $column_name ] < $innodb_count ) {
						// Count rows in InnoDB table.
						return $this->count_rows( $item['table_name'] );
					} else {
						$msg = __('This is an estimation! Click to read more...', 'wp-data-access');
						$approx = " <a href='/wp-admin/admin.php?page=wpda_help&docid=rows_estimation' target='_blank' title='$msg' style'cursor:pointer;'><span style='font-size:15px;' class='dashicons dashicons-warning'></span></a>";
						return stripslashes( $item[ $column_name ] ) . $approx;
					}
				} else {
					return stripslashes( $item[ $column_name ] );
				}
			}

			if ( 'data_size' === $column_name || 'index_size' === $column_name ) {
				if ( '' === stripslashes( $item[ $column_name ] ) ) {
					return stripslashes( $item[ $column_name ] );
				} else {
					if ( $item[ $column_name ] / (1024*1024) > 1 ) {
						return number_format( stripslashes( $item[ $column_name ] / (1024*1024) ), 2, '.', ',') . ' MB';
					} elseif ( $item[ $column_name ] / 1024 > 1 ) {
						return number_format( ( $item[ $column_name ] / 1024 ), 2, '.', ',') . ' KB';
					}
					return number_format( stripslashes( $item[ $column_name ] ), 2, '.', ',') . ' bytes';
				}
			}

			if ( 'overhead' === $column_name ) {
				if ( 'InnoDB' === $item['engine'] && ! $this->innodb_file_per_table ) {
					return '-';
				} else {
					if ( '' === stripslashes( $item[ $column_name ] ) ) {
						return '';
					} else {
						if ( $item[ $column_name ] > 0 && $item[ 'data_size' ] > 0 && ( $item[ $column_name ] / $item[ 'data_size' ] > 0.2 ) ) {
							$msg = __('Fragmentation for this table is high. Consider: Manage>Actions>Optimize', 'wp-data-access');
							$approx = " <span style='font-size:15px;' class='dashicons dashicons-warning' title='$msg' style'cursor:pointer;'></span>";
						} else {
							$approx = '';
						}

						if ( $item[ $column_name ] / (1024*1024) > 1 ) {
							return number_format( stripslashes( $item[ $column_name ] / (1024*1024) ), 2, '.', ',') . ' MB' . $approx;
						} elseif ( $item[ $column_name ] / 1024 > 1 ) {
							return number_format( stripslashes( $item[ $column_name ] / 1024 ), 2, '.', ',') . ' KB' . $approx;
						}
						return number_format( stripslashes( $item[ $column_name ] ), 2, '.', ',') . ' bytes' . $approx;
					}
				}
			}

			if ( 'table_name' === $column_name ) {
				// Get table name of the current row (= key).
				$table_name    = $item[ $column_name ];
				$admin_actions = []; // Array containing admin actions.

				// Add manage table/view line.
                $wp_nonce_action_table_actions = "wpda-actions-$table_name";
                $wp_nonce_table_actions        = wp_create_nonce( $wp_nonce_action_table_actions );
				$actions['manage']             =
                    "<a href=\"javascript:void( 0 )\" onclick=\"wpda_show_table_actions( '{$this->schema_name}', '$table_name', '" . self::$list_number++ . "', '$wp_nonce_table_actions', '{$item['table_type']}', '" . self::LOADING . "' ); this.blur();\">" .
						__( 'Manage', 'wp-data-access' ) .
					"</a>";

				// Prepare type checking for editing.
				$check_view_access = 'true';
				if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_CONFIRM_VIEW ) ) {
					if ( 'VIEW' !== strtoupper( $item['table_type'] ) && 'SYSTEM VIEW' !== strtoupper( $item['table_type'] ) ) {
						if ( WPDA::TABLE_TYPE_WP === $item['table_type'] ) {
							// WordPress table.
							$msg = __(
								'You are about to edit a WordPress table! Changing this table might result in corrupting the WordPress database. Are you sure you want to continue?', 'wp-data-access'
							);
						} elseif ( WPDA::TABLE_TYPE_WPDA === $item['table_type'] ) {
							// Plugin table.
							$msg = __(
								'You are about to edit a plugin table! Changing this table might result in corrupting the WP Data Access database. Are you sure you want to continue?', 'wp-data-access'
							);
						} else {
							// User table (other than WordPress or plugin).
							$msg = __(
								'You are about to edit a table of an external application! Changing this table might result in corrupting the external database. Are you sure you want to continue?', 'wp-data-access'
							);
						}
						$check_view_access = 'confirm(\'' . $msg . '\')';
					}
				}
				if ( 'InnoDB' === $item['engine'] &&
					$item['table_rows'] > WPDA::get_option( WPDA::OPTION_BE_INNODB_COUNT )
					) {
					$msg               = __('You are about to edit a table containing a large number of rows. This might slow down your system! Are you sure you want to continue?', 'wp-data-access');
					$check_view_access = 'confirm(\'' . $msg . '\')';
				}
				global $wpdb;
				if ( $this->schema_name === $wpdb->dbname ) {
					$schema_name = '';
				} else {
					$schema_name = "&schema_name=$this->schema_name";
				}
				$action_view          = __( 'Explore', 'wp-data-access' );
				$actions['listtable'] =
					sprintf(
						'<a href="javascript:void(0)" 
                               class="view"  
                               onclick="if (%s) location.href=\'?page=%s%s&table_name=%s&action=listtable\'">
                               %s
                            </a>',
						$check_view_access,
						$this->page,
						$schema_name,
						$table_name,
						$action_view
					);

				if ( WPDA::is_wp_table( $table_name ) ) {
					?>
					<script language="JavaScript">
						wpda_bulk.push('<?php echo esc_attr( $table_name ); ?>');
					</script>
					<?php
				}

				return sprintf(
					'%1$s %2$s %3$s',
					stripslashes( $item[ $column_name ] ),
					"<nobr>{$this->row_actions( $actions )}</nobr>",
					"<span id=\"span_admin_menu_$table_name\" style=\"display:none;width:auto;float:clear;\"><nobr>{$this->row_actions( $admin_actions )}</nobr></span>"
				);

			} else {

				return stripslashes( $item[ $column_name ] );

			}

		}

		/**
		 * Count the number of rows in a table.
		 *
		 * This method is used to get the number of rows in an InnoDB table as the table_rows column of
		 * information_schema.tables only return an estimate.
		 *
		 * @since   1.5.1
		 *
		 * @param string $table_name Database table name.
		 *
		 * @return integer|null Number of rows in $table_name.
		 */
		protected function count_rows( $table_name ) {

			global $wpdb;

			if ( '' === $this->schema_name ) {
				$query = "
					select count(*) 
					from `$table_name`
				";
			} else {
				$query = "
					select count(*) 
					from `{$this->schema_name}`.`$table_name`
				";
			}

			return $wpdb->get_var( $query ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.


		}

		/**
		 * Override get_columns
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::get_columns()
		 *
		 * @return array
		 */
		public function get_columns() {

			$columns = [];

			if ( $this->bulk_actions_enabled ) {
				$columns = [ 'cb' => '<input type="checkbox" />' ];
			}

			return array_merge( $columns, $this->column_headers );

		}

		/**
		 * Override get_sortable_columns()
		 *
		 * Type is not sortable as it is not in the view.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::get_sortable_columns()
		 *
		 * @return array
		 */
		public function get_sortable_columns() {

			$columns = [];

			$columns['table_name']      = [ 'table_name', false ];
			$columns['table_type']      = [ 'table_type', false ];
			$columns['engine']          = [ 'engine', false ];
			$columns['create_time']     = [ 'create_time', false ];
			$columns['table_rows']      = [ 'table_rows', false ];
			$columns['auto_increment']  = [ 'auto_increment', false ];
			$columns['data_size']       = [ 'data_size', false ];
			$columns['index_size']      = [ 'index_size', false ];
			$columns['overhead']        = [ 'overhead', false ];
			$columns['table_collation'] = [ 'table_collation', false ];

			return $columns;

		}

		/**
		 * Override column_cb
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::column_cb()
		 *
		 * @param array $item Column info.
		 * @return string
		 */
		public function column_cb( $item ) {

			if ( ! $this->bulk_actions_enabled ) {
				// Bulk actions disabled.
				return '';
			}

			if ( 'VIEW' === $item['table_type'] ) {
				// Disabled checkbox for views (do not allow export).
				return "<input type='checkbox' name='bulk-selected[]' disabled />";
			} else {
				return "<input type='checkbox' name='bulk-selected[]' value='{$item['table_name']}' />";
			}

		}

		/**
		 * Override get_bulk_actions
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::get_bulk_actions()
		 *
		 * @return array
		 */
		public function get_bulk_actions() {

			if ( ! $this->bulk_actions_enabled ) {
				// Bulk actions disabled.
				return '';
			}

			$actions = [];

			if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_EXPORT_TABLES ) ) {
			    $actions['bulk-export'] = __( 'Export Table(s)', 'wp-data-access' );
            }

            if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_DROP ) ) {
                $actions['bulk-drop'] = __( 'Drop Table(s)/View(s) (does not drop WordPress tables)', 'wp-data-access' );
            }

            if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_TRUNCATE ) ) {
                $actions['bulk-truncate'] = __( 'Truncate Table(s) (does not truncate WordPress tables)', 'wp-data-access' );
            }

			return $actions;

		}

		/**
		 * Override process_bulk_action()
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::process_bulk_action()
		 */
		public function process_bulk_action() {

			switch ( $this->current_action() ) {
				case 'bulk-export':
					$this->process_bulk_action_bulk_export();
					break;
				case 'bulk-drop':
					$this->process_bulk_action_bulk_drop();
					break;
				case 'bulk-truncate':
					$this->process_bulk_action_bulk_truncate();
					break;
				case 'drop':
					$this->process_bulk_action_drop();
					break;
				case 'truncate':
					$this->process_bulk_action_truncate();
                    break;
                case 'rename-table':
                    $this->process_bulk_action_rename_table();
                    break;
                case 'copy-table':
                    $this->process_bulk_action_copy_table();
                    break;
				case 'optimize-table':
					$this->process_bulk_action_optimize_table();
					break;
			}

		}

		protected function process_bulk_action_optimize_table() {

			if ( isset( $_REQUEST['optimize_table_name'] ) ) {
				$optimize_table_name = sanitize_text_field( wp_unslash( $_REQUEST['optimize_table_name'] ) ); // input var okay.

				if ( $this->process_bulk_action_check_wpnonce( "wpda-optimize-$optimize_table_name", '_wpnonce' ) ) {
					$dbo_type = $this->get_dbo_type( $optimize_table_name );
					if ( false === $dbo_type || 'BASE TABLE' !== $dbo_type ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => sprintf( __( 'Cannot optimize `%s`', 'wp-data-access' ), $optimize_table_name ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					} else {
						// Optimize table.
						global $wpdb;
						if ( '' === $this->schema_name || $wpdb->dbname === $this->schema_name ) {
							// Optimize table in WordPress schema.
							$wpdb->query( "optimize table `$optimize_table_name`" ); // db call ok; no-cache ok.
						} else {
							// Optimize table in other schema.
							$db = new \wpdb( DB_USER, DB_PASSWORD, $this->schema_name, DB_HOST );
							$db->query( "optimize table `$optimize_table_name`" ); // db call ok; no-cache ok.
							$db->close();

						}
						$msg = new WPDA_Message_Box(
							[
								'message_text' => sprintf( __( 'Table %s optimized', 'wp-data-access' ), $optimize_table_name ),
							]
						);
						$msg->box();
					}
				}
			}

		}

        /**
         * Performs rename table/view.
         *
         * @since   1.6.6
         */
        protected function process_bulk_action_rename_table() {

            // Check access rights.
            if ( $this->process_bulk_action_check_option( WPDA::OPTION_BE_ALLOW_RENAME ) ) {
                // Check arguments.
                if ( ! $this->process_bulk_action_check_action(
                    'rename_table_name_old',
                    __( 'Missing old table name', 'wp-data-access' )
                ) ) {
                    return;
                }
                if ( $this->process_bulk_action_check_action(
                    'rename_table_name_new',
                    __( 'Missing new table name', 'wp-data-access' )
                ) ) {
                    // Rename table is not allowed for WordPress tables (double check).
                    $rename_table_name_old = sanitize_text_field( wp_unslash( $_REQUEST['rename_table_name_old'] ) ); // input var okay.
                    $rename_table_name_new = sanitize_text_field( wp_unslash( $_REQUEST['rename_table_name_new'] ) ); // input var okay.
                    $err_txt               = sprintf( __( ' (cannot rename WordPress table `%s`)', 'wp-data-access' ), $rename_table_name_old );
                    if ( '' === $rename_table_name_old ) {
                        $msg = new WPDA_Message_Box(
                            [
                                'message_text' => __( 'Missing old table name value', 'wp-data-access' ),
                            ]
                        );
                        $msg->box();
                        return;
                    }
                    if ( '' === $rename_table_name_new ) {
                        $msg = new WPDA_Message_Box(
                            [
                                'message_text' => __( 'Missing new table name value', 'wp-data-access' ),
                            ]
                        );
                        $msg->box();
                        return;
                    }
                    if ( $this->process_bulk_action_check_is_wp_table( $rename_table_name_old, $err_txt ) ) {
                        // Check if table exists.
                        if ( $this->process_bulk_action_check_table_exists( $rename_table_name_old ) ) {
                            // Check if rename is allowed.
                            if ( $this->process_bulk_action_check_wpnonce( "wpda-rename-$rename_table_name_old", '_wpnonce' ) ) {
                                $dbo_type = $this->get_dbo_type( $rename_table_name_old );
                                if ( false === $dbo_type || 'SYSTEM VIEW' === $dbo_type ) {
                                    $msg = new WPDA_Message_Box(
                                        [
                                            'message_text'           => sprintf( __( 'Cannot rename `%s`', 'wp-data-access' ), $rename_table_name_old ),
                                            'message_type'           => 'error',
                                            'message_is_dismissible' => false,
                                        ]
                                    );
                                    $msg->box();
                                } else {
                                    // Rename table/view.
                                    if ( false === $this->rename_table( $rename_table_name_old, $rename_table_name_new ) ) {
                                        $msg = new WPDA_Message_Box(
                                            [
                                                'message_text'           => __( 'Cannot rename', 'wp-data-access' ) . ' ' . strtolower( $dbo_type ) . ' `' . $rename_table_name_old . '`',
                                                'message_type'           => 'error',
                                                'message_is_dismissible' => false,
                                            ]
                                        );
                                        $msg->box();
                                    } else {
                                        $msg = new WPDA_Message_Box(
                                            [
                                                'message_text' =>
                                                    strtoupper( substr( $dbo_type, 0, 1 ) ) . strtolower( substr( $dbo_type, 1 ) ) .
                                                    ' `' . $rename_table_name_old . '` ' . __( 'renamed to', 'wp-data-access' ) .
                                                    ' `' . $rename_table_name_new . '` ',
                                            ]
                                        );
                                        $msg->box();
                                    }
                                }
                            }
                        }
                    }
                }
            }

        }

        /**
         * Rename table/view.
         *
         * @since   1.6.6
         *
         * @param string $rename_table_name_old Old table name.
         * @param string $rename_table_name_new New table name.
         * @return false|int
         */
        protected function rename_table( $rename_table_name_old, $rename_table_name_new ) {

            global $wpdb;

            if ( WPDA::is_wp_table( $rename_table_name_old ) ) {
                // Never ever allow renaming a WordPress table!
                $msg = new WPDA_Message_Box(
                    [
                        'message_text'           => __( 'Not authorized', 'wp-data-access' ),
                        'message_type'           => 'error',
                        'message_is_dismissible' => false,
                    ]
                );
                $msg->box();
                return false;
            }

            if ( '' === $this->schema_name || $wpdb->dbname === $this->schema_name ) {
                // Rename table in WordPress schema.
                return $wpdb->query( "rename table `$rename_table_name_old` to `$rename_table_name_new`" ); // db call ok; no-cache ok.
            } else {
                // Rename table in other schema.
                $db = new \wpdb( DB_USER, DB_PASSWORD, $this->schema_name, DB_HOST );
                $result = $db->query( "rename table `$rename_table_name_old` to `$rename_table_name_new`" ); // db call ok; no-cache ok.
                $db->close();
                return $result;

            }

        }

        /**
         * Performs copying a table.
         *
         * @since   1.6.6
         */
        protected function process_bulk_action_copy_table() {

            // Check access rights.
            if ( $this->process_bulk_action_check_option( WPDA::OPTION_BE_ALLOW_COPY ) ) {
                // Check arguments.
                if ( ! $this->process_bulk_action_check_action(
                    'copy_table_name_src',
                    __( 'Missing source table name', 'wp-data-access' )
                ) ) {
                    return;
                }
                if ( $this->process_bulk_action_check_action(
                    'copy_table_name_dst',
                    __( 'Missing destination table name', 'wp-data-access' )
                ) ) {
                    // copy table is not allowed for WordPress tables (double check).
                    $copy_table_name_src = sanitize_text_field( wp_unslash( $_REQUEST['copy_table_name_src'] ) ); // input var okay.
                    $copy_table_name_dst = sanitize_text_field( wp_unslash( $_REQUEST['copy_table_name_dst'] ) ); // input var okay.
                    if ( '' === $copy_table_name_src ) {
                        $msg = new WPDA_Message_Box(
                            [
                                'message_text'           => __( 'Missing source table name value', 'wp-data-access' ),
                            ]
                        );
                        $msg->box();
                        return;
                    }
                    if ( '' === $copy_table_name_dst ) {
                        $msg = new WPDA_Message_Box(
                            [
                                'message_text'           => __( 'Missing destination table name value', 'wp-data-access' ),
                            ]
                        );
                        $msg->box();
                        return;
                    }
                    // Check if table exists.
                    if ( $this->process_bulk_action_check_table_exists( $copy_table_name_src ) ) {
                        // Check if copy is allowed.
                        if ( $this->process_bulk_action_check_wpnonce( "wpda-copy-$copy_table_name_src", '_wpnonce' ) ) {
                            $dbo_type = $this->get_dbo_type( $copy_table_name_src );
                            if ( false === $dbo_type || 'SYSTEM VIEW' === $dbo_type ) {
                                $msg = new WPDA_Message_Box(
                                    [
                                        'message_text'           => sprintf( __( 'Cannot copy %s', 'wp-data-access' ), $copy_table_name_src ),
                                        'message_type'           => 'error',
                                        'message_is_dismissible' => false,
                                    ]
                                );
                                $msg->box();
                            } else {
                                $include_data = isset( $_REQUEST['copy-table-data'] ) ? 'on' : 'off';
                                // copy table/view.
                                if ( false === $this->copy_table( $copy_table_name_src, $copy_table_name_dst, $include_data ) ) {
                                    $msg = new WPDA_Message_Box(
                                        [
                                            'message_text'           => __( 'Cannot copy', 'wp-data-access' ) . ' ' . strtolower( $dbo_type ) . ' ' . $copy_table_name_src,
                                            'message_type'           => 'error',
                                            'message_is_dismissible' => false,
                                        ]
                                    );
                                    $msg->box();
                                } else {
                                    $msg = new WPDA_Message_Box(
                                        [
                                            'message_text' =>
                                                strtoupper( substr( $dbo_type, 0, 1 ) ) . strtolower( substr( $dbo_type, 1 ) ) .
                                                ' `' . $copy_table_name_src . '` ' . __( 'copied to', 'wp-data-access' ) .
                                                ' `' . $copy_table_name_dst . '` ',
                                        ]
                                    );
                                    $msg->box();
                                }
                            }
                        }
                    }
                }
            }

        }

        /**
         * Copy table.
         *
         * @since   1.6.6
         *
         * @param string $copy_table_name_src Source table name.
         * @param string $copy_table_name_dst Destination table name.
         * @param string $include_data 'on' = include data.
         * @return false|int
         */
        protected function copy_table($copy_table_name_src, $copy_table_name_dst, $include_data ) {

            global $wpdb;

            if ( '' === $this->schema_name || $wpdb->dbname === $this->schema_name ) {
                // Copy table in WordPress schema.
                if ( 'on' === $include_data ) {
                    $result = $wpdb->query( "create table `$copy_table_name_dst` like `$copy_table_name_src`" ); // db call ok; no-cache ok.
                    if ( false === $result ) {
                        return false;
                    } else {
                        return $wpdb->query( "insert `$copy_table_name_dst` select * from `$copy_table_name_src`" ); // db call ok; no-cache ok.
                    }
                } else {
                    return $wpdb->query( "create table `$copy_table_name_dst` like `$copy_table_name_src`" ); // db call ok; no-cache ok.
                }
            } else {
                // Copy table in other schema.
                $db = new \wpdb( DB_USER, DB_PASSWORD, $this->schema_name, DB_HOST );
                $result = $db->query( "create table `$copy_table_name_dst` like `$copy_table_name_src`" ); // db call ok; no-cache ok.
                if ( false === $result ) {
                    $db->close();
                    return false;
                }
                $result = $db->query( "insert `$copy_table_name_dst` select * from `$copy_table_name_src`" ); // db call ok; no-cache ok.
                $db->close();
                return $result;

            }

        }

        /**
		 * Performs bulk export.
		 *
		 * @since   1.5.0
		 */
		protected function process_bulk_action_bulk_export() {

			// Check access rights.
			if ( $this->process_bulk_action_check_option( WPDA::OPTION_BE_EXPORT_TABLES ) ) {
				// Check is there is anything to export.
				if ( $this->process_bulk_action_check_action(
					'bulk-selected',
					__( 'Empty bulk selected', 'wp-data-access' )
				) ) {
					// Check if export is allowed.
					if ( $this->process_bulk_action_check_wpnonce( 'wpda-export-*', '_wpnonce' ) ) {
						// Get arguments.
						$bulk_tabs   = isset( $_REQUEST['bulk-selected'] ) ? $_REQUEST['bulk-selected'] : ''; // input var okay; sanitization okay.
						$wp_nonce    = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
						$querystring = "?action=wpda_export&type=table&_wpnonce=$wp_nonce&schema_name={$this->schema_name}";

						$cnt = 0;
						foreach ( $bulk_tabs as $table_name ) {
							$export_table_name = sanitize_text_field( wp_unslash( $table_name ) ); // input var okay.
							$err_txt           = sprintf( __( ' (table %s)', 'wp-data-access' ), $export_table_name );
							if ( $this->process_bulk_action_check_table_exists( $export_table_name, $err_txt ) ) {
								$dbo_type = $this->get_dbo_type( $export_table_name );
								if ( false === $dbo_type || 'VIEW' === $dbo_type  || 'SYSTEM VIEW' === $dbo_type ) {
									$msg = new WPDA_Message_Box(
										[
											'message_text'           => sprintf( __( 'Cannot export %s', 'wp-data-access' ), $export_table_name ),
											'message_type'           => 'error',
											'message_is_dismissible' => false,
										]
									);
									$msg->box();
								} else {
									$querystring .= "&table_names[]=$export_table_name";
									$cnt++;
								}
							}
						}

						if ( 0 < $cnt ) {
							// Export tables.
							?>
							<script>
								jQuery(document).ready(function () {
									jQuery("#stealth_mode").attr("src", "<?php echo esc_sql( $querystring ); ?>");
								});
							</script>
							<?php

							$msg = new WPDA_Message_Box(
								[
									'message_text' => sprintf( __( '%d tables exported', 'wp-data-access' ), $cnt ),
								]
							);
							$msg->box();
						}
					}
				}
			}

		}

		/**
		 * Checks if action is allowed [allowed: option===on].
		 *
		 * @since   1.5.0
		 *
		 * @param array $option_name Option name.
		 * @return bool TRUE = option===on.
		 */
		protected function process_bulk_action_check_option( $option_name ) {

			if ( 'on' !== WPDA::get_option( $option_name ) ) {
				// Exporting tables from list table is not allowed.
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => __( 'Not authorized', 'wp-data-access' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();

				return false;
			}

			return true;

		}

		/**
		 * Checks request argument needed for (bulk) action to be performed.
		 *
		 * @since   1.5.0
		 *
		 * @param string $argument_name Request argument name.
		 * @param string $msg Message on failure.
		 * @return bool TRUE = argument found in request.
		 */
		protected function process_bulk_action_check_action( $argument_name, $msg ) {

			if ( ! isset( $_REQUEST[ $argument_name ] ) ) {
				// Nothing export.
				$msg = new WPDA_Message_Box(
					[
						'message_text' => $msg,
					]
				);
				$msg->box();

				return false;
			}

			return true;

		}

		/**
		 * Checks wpnonce against a specific action.
		 *
		 * @since   1.5.0
		 *
		 * @param string $wp_nonce_action Nonce action.
		 * @param string $wp_nonce_arg Nonce argument.
		 * @return bool TRUE = action allowed.
		 */
		protected function process_bulk_action_check_wpnonce( $wp_nonce_action, $wp_nonce_arg ) {

			$wp_nonce = isset( $_REQUEST[ $wp_nonce_arg ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $wp_nonce_arg ] ) ) : ''; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, $wp_nonce_action ) ) {
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => __( 'Not authorized', 'wp-data-access' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();

				return false;
			}

			return true;

		}

		/**
		 * Checks if table exists.
		 *
		 * @since   1.5.0
		 *
		 * @param string $table_name Database table name.
		 * @param string $err_txt Additional error text/info.
		 * @return bool TRUE = table exists.
		 */
		protected function process_bulk_action_check_table_exists( $table_name, $err_txt = '' ) {

			$wpda_dictionary = new WPDA_Dictionary_Exist( $this->schema_name, $table_name );
			if ( ! $wpda_dictionary->table_exists() ) {
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => __( 'Not authorized', 'wp-data-access' ) . $err_txt,
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();

				return false;
			}

			return true;

		}

		/**
		 * Get database object type (VIEW, BASE_TABLE, SYSTEM VIEW).
		 *
		 * @since   1.5.0
		 *
		 * @param string $dbo_name Table or view name.
		 * @return string|boolean Database object type or false.
		 */
		protected function get_dbo_type( $dbo_name ) {

			global $wpdb;

			$query  =
				$wpdb->prepare(
					'
							SELECT table_type
							  FROM information_schema.tables
							 WHERE table_schema = %s
							   AND table_name   = %s
						',
					[
						$this->schema_name,
						$dbo_name,
					]
				); // db call ok; no-cache ok.
			$result = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			if ( 1 === $wpdb->num_rows ) {
				return $result[0]['table_type'];
			} else {
				return false;
			}

		}

		protected function process_bulk_action_bulk_drop() {

			// Check access rights.
			if ( $this->process_bulk_action_check_option( WPDA::OPTION_BE_ALLOW_DROP ) ) {
				// Check is there is anything to drop.
				if ( $this->process_bulk_action_check_action(
					'bulk-selected',
					__( 'Empty bulk selected', 'wp-data-access' )
				) ) {
					// Check if drop is allowed.
					if ( $this->process_bulk_action_check_wpnonce( 'wpda-drop-*', '_wpnonce3' ) ) {
						$bulk_tabs = isset( $_REQUEST['bulk-selected'] ) ? $_REQUEST['bulk-selected'] : ''; // input var okay; sanitization okay.
						foreach ( $bulk_tabs as $table_name ) {
							// Drop table is not allowed for WordPress tables (double check).
							$drop_table_name = sanitize_text_field( wp_unslash( $table_name ) ); // input var okay.
							$err_txt         = sprintf( __( ' (cannot drop WordPress table `%s`)', 'wp-data-access' ), $drop_table_name );
							if ( $this->process_bulk_action_check_is_wp_table( $drop_table_name, $err_txt ) ) {
								// Check if table exists.
								$err_txt = sprintf( __( ' (table %s)', 'wp-data-access' ), $drop_table_name );
								if ( $this->process_bulk_action_check_table_exists( $drop_table_name, $err_txt ) ) {
									$dbo_type = $this->get_dbo_type( $drop_table_name );
									if ( false === $dbo_type || 'SYSTEM VIEW' === $dbo_type ) {
										$msg = new WPDA_Message_Box(
											[
												'message_text'           => sprintf( __( 'Cannot drop `%s`', 'wp-data-access' ), $drop_table_name ),
												'message_type'           => 'error',
												'message_is_dismissible' => false,
											]
										);
										$msg->box();
									} else {
										if ( 'VIEW' === $dbo_type ) {
											// Drop view.
											if ( $this->drop_view( $drop_table_name ) ) {
												$msg = new WPDA_Message_Box(
													[
														'message_text' => sprintf( __( 'View `%s` dropped', 'wp-data-access' ), $drop_table_name ),
													]
												);
												$msg->box();
											}
										} else {
											// Drop table.
											if ( $this->drop_table( $drop_table_name ) ) {
												$msg = new WPDA_Message_Box(
													[
														'message_text' => sprintf( __( 'Table `%s` dropped', 'wp-data-access' ), $drop_table_name ),
													]
												);
												$msg->box();
											}
										}
									}
								}
							}
						}
					}
				}
			}

		}

		/**
		 * Checks if table is a WordPress table.
		 *
		 * @since   1.5.0
		 *
		 * @param string $table_name Database table name.
		 * @param string $err_txt Additional error text/info.
		 * @return bool TRUE = table is WordPress table.
		 */
		protected function process_bulk_action_check_is_wp_table( $table_name, $err_txt = '' ) {

			if ( WPDA::is_wp_table( $table_name ) ) {
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => __( 'Not authorized', 'wp-data-access' ) . $err_txt,
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();

				return false;
			}

			return true;

		}

		/**
		 * Performs drop view.
		 *
		 * @since   1.5.0
		 *
		 * @param string $view_name Database view name.
		 * @return bool TRUE = view dropped.
		 */
		protected function drop_view( $view_name ) {

			global $wpdb;

			return $wpdb->query( "drop view $view_name" ); // db call ok; no-cache ok.

		}

		/**
		 * Performs drop table.
		 *
		 * @since   1.5.0
		 *
		 * @param string $table_name Database table name.
		 * @return bool TRUE = table dropped.
		 */
		protected function drop_table( $table_name ) {

			global $wpdb;

			if ( WPDA::is_wp_table( $table_name ) ) {
				// Never ever allow dropping a WordPress table!
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => __( 'Not authorized', 'wp-data-access' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();
				return false;
			}

			if ( '' === $this->schema_name || $wpdb->dbname === $this->schema_name ) {
				// Drop table in WordPress schema.
				return $wpdb->query( "drop table `$table_name`" ); // db call ok; no-cache ok.
			} else {
				// Drop table in other schema.
				$db = new \wpdb( DB_USER, DB_PASSWORD, $this->schema_name, DB_HOST );
				$result = $db->query( "drop table `$table_name`" ); // db call ok; no-cache ok.
				$db->close();
				return $result;

			}

		}

		protected function process_bulk_action_bulk_truncate() {

			// Check access rights.
			if ( $this->process_bulk_action_check_option( WPDA::OPTION_BE_ALLOW_TRUNCATE ) ) {
				// Check is there is anything to truncate.
				if ( $this->process_bulk_action_check_action(
					'bulk-selected',
					__( 'No table defined', 'wp-data-access' )
				) ) {
					// Check if truncate is allowed.
					if ( $this->process_bulk_action_check_wpnonce( 'wpda-truncate-*', '_wpnonce4' ) ) {
						$bulk_tabs = isset( $_REQUEST['bulk-selected'] ) ? $_REQUEST['bulk-selected'] : ''; // input var okay; sanitization okay.
						foreach ( $bulk_tabs as $table_name ) {
							// Truncate table is not allowed for WordPress tables (double check).
							$truncate_table_name = sanitize_text_field( wp_unslash( $table_name ) ); // input var okay.
							$err_txt             = sprintf( __( ' (cannot truncate WordPress table `%s`)', 'wp-data-access' ), $truncate_table_name );
							if ( $this->process_bulk_action_check_is_wp_table( $truncate_table_name, $err_txt ) ) {
								// Check if table exists.
								$err_txt = sprintf( __( ' (table %s)', 'wp-data-access' ), $truncate_table_name );
								if ( $this->process_bulk_action_check_table_exists( $truncate_table_name, $err_txt ) ) {
									$dbo_type = $this->get_dbo_type( $truncate_table_name );
									if ( false === $dbo_type || 'VIEW' === $dbo_type || 'SYSTEM VIEW' === $dbo_type ) {
										$msg = new WPDA_Message_Box(
											[
												'message_text'           => sprintf( __( 'Cannot truncate `%s`', 'wp-data-access' ), $truncate_table_name ),
												'message_type'           => 'error',
												'message_is_dismissible' => false,
											]
										);
										$msg->box();
									} else {
										// Truncate table.
										if ( $this->truncate_table( $truncate_table_name ) ) {
											$msg = new WPDA_Message_Box(
												[
													'message_text' => sprintf( __( 'Table `%s` truncated', 'wp-data-access' ), $truncate_table_name ),
												]
											);
											$msg->box();
										}
									}
								}
							}
						}
					}
				}
			}

		}

		/**
		 * Performs truncate table.
		 *
		 * @since   1.5.0
		 *
		 * @param string $table_name Database table name.
		 * @return bool TRUE = table truncated.
		 */
		protected function truncate_table( $table_name ) {

			global $wpdb;

			if ( WPDA::is_wp_table( $table_name ) ) {
				// Never ever allow truncating a WordPress table!
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => __( 'Not authorized', 'wp-data-access' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();
				return false;
			}

			if ( '' === $this->schema_name || $wpdb->dbname === $this->schema_name ) {
				// Truncate table in WordPress schema.
				return $wpdb->query( "truncate table `$table_name`" ); // db call ok; no-cache ok.
			} else {
				// Truncate table in other schema.
				$db = new \wpdb( DB_USER, DB_PASSWORD, $this->schema_name, DB_HOST );
				$result = $db->query( "truncate table `$table_name`" ); // db call ok; no-cache ok.
				$db->close();
				return $result;
			}

		}

		/**
		 * Processes drop table request.
		 *
		 * @since   1.5.0
		 */
		protected function process_bulk_action_drop() {

			// Check access rights.
			if ( $this->process_bulk_action_check_option( WPDA::OPTION_BE_ALLOW_DROP ) ) {
				// Check is there is anything to drop.
				if ( $this->process_bulk_action_check_action(
					'drop_table_name',
					__( 'No table defined', 'wp-data-access' )
				) ) {
					// Drop table is not allowed for WordPress tables (double check).
					$drop_table_name = sanitize_text_field( wp_unslash( $_REQUEST['drop_table_name'] ) ); // input var okay.
					$err_txt         = sprintf( __( ' (cannot drop WordPress table `%s`)', 'wp-data-access' ), $drop_table_name );
					if ( $this->process_bulk_action_check_is_wp_table( $drop_table_name, $err_txt ) ) {
						// Check if table exists.
						if ( $this->process_bulk_action_check_table_exists( $drop_table_name ) ) {
							// Check if drop is allowed.
							if ( $this->process_bulk_action_check_wpnonce( "wpda-drop-$drop_table_name", '_wpnonce' ) ) {
								$dbo_type = $this->get_dbo_type( $drop_table_name );
								if ( false === $dbo_type || 'SYSTEM VIEW' === $dbo_type ) {
									$msg = new WPDA_Message_Box(
										[
											'message_text'           => sprintf( __( 'Cannot drop `%s`', 'wp-data-access' ), $drop_table_name ),
											'message_type'           => 'error',
											'message_is_dismissible' => false,
										]
									);
									$msg->box();
								} else {
									if ( 'VIEW' === $dbo_type ) {
										// Drop view.
										if ( $this->drop_view( $drop_table_name ) ) {
											$msg = new WPDA_Message_Box(
												[
													'message_text' => sprintf( __( 'View `%s` dropped', 'wp-data-access' ), $drop_table_name ),
												]
											);
											$msg->box();
										}
									} else {
										// Drop table.
										if ( $this->drop_table( $drop_table_name ) ) {
											$msg = new WPDA_Message_Box(
												[
													'message_text' => sprintf( __( 'Table `%s` dropped', 'wp-data-access' ), $drop_table_name ),
												]
											);
											$msg->box();
										}
									}
								}
							}
						}
					}
				}
			}

		}

		/**
		 * Processes truncate table request.
		 *
		 * @since   1.5.0
		 */
		protected function process_bulk_action_truncate() {

			// Check access rights.
			if ( $this->process_bulk_action_check_option( WPDA::OPTION_BE_ALLOW_TRUNCATE ) ) {
				// Check is there is anything to truncate.
				if ( $this->process_bulk_action_check_action(
					'truncate_table_name',
					__( 'No table defined', 'wp-data-access' )
				) ) {
					// Truncate table is not allowed for WordPress tables (double check).
					$truncate_table_name = sanitize_text_field( wp_unslash( $_REQUEST['truncate_table_name'] ) ); // input var okay.
					$err_txt             = sprintf( __( ' (cannot truncate WordPress table `%s`)', 'wp-data-access' ), $truncate_table_name );
					if ( $this->process_bulk_action_check_is_wp_table( $truncate_table_name, $err_txt ) ) {
						// Check if table exists.
						if ( $this->process_bulk_action_check_table_exists( $truncate_table_name ) ) {
							// Check if truncate is allowed.
							if ( $this->process_bulk_action_check_wpnonce( "wpda-truncate-$truncate_table_name", '_wpnonce' ) ) {
								// Truncate table.
								if ( $this->truncate_table( $truncate_table_name ) ) {
									$msg = new WPDA_Message_Box(
										[
											'message_text' => sprintf( __( 'Table `%s` truncated', 'wp-data-access' ), $truncate_table_name ),
										]
									);
									$msg->box();
								}
							}
						}
					}
				}
			}

		}

		/**
		 * Overwrite method: add bulk array to check ddl allowed
		 */
		public function show() {

            ?>
            <script language="JavaScript">
                var wpda_bulk = [];

                function wpda_bulk_valid() {
                    var wpda_bulk_selected_valid = true;
                    jQuery("input[name='bulk-selected[]']:checked").each(function () {
                        var wpda_bulk_selected = jQuery(this).val();
                        wpda_bulk.every(function (item) {
                            if (item === wpda_bulk_selected) {
                                alert("Action not allowed on WordPress tables!");
                                wpda_bulk_selected_valid = false;
                            }
                        });
                        if (wpda_bulk_selected_valid === false) {
                            return false;
                        }
                    });
                    return wpda_bulk_selected_valid;
                }

                function wpda_check_bulk() {
                    action = jQuery("select[name='action']").val();
                    action2 = jQuery("select[name='action2']").val();
                    if (action === '-1') {
                        if (action2 === 'bulk-drop' || action2 === 'bulk-truncate') {
                            return wpda_bulk_valid();
                        } else {
                            return true;
                        }
                    } else {
                        if (action === 'bulk-drop' || action === 'bulk-truncate') {
                            return wpda_bulk_valid();
                        } else {
                            return true;
                        }
                    }
                }
            </script>
            <?php

            parent::show();

            ?>
			<script language="JavaScript">
				function show_hide_column(show) {
					for (i=0; i<<?php echo self::$list_number; ?>; i++) {
						if (show) {
							jQuery('#rownum_' + i + '_x1').attr('colspan', parseInt(jQuery('#rownum_' + i + '_x1').attr('colspan'))+1);
							jQuery('#rownum_' + i + '_x2').attr('colspan', parseInt(jQuery('#rownum_' + i + '_x2').attr('colspan'))+1);
						} else {
							jQuery('#rownum_' + i + '_x1').attr('colspan', parseInt(jQuery('#rownum_' + i + '_x1').attr('colspan'))-1);
							jQuery('#rownum_' + i + '_x2').attr('colspan', parseInt(jQuery('#rownum_' + i + '_x2').attr('colspan'))-1);
						}
					}
					jQuery('.wp-list-table').removeClass('fixed');
				}
				jQuery(document).ready(function () {
                    jQuery("#doaction").off();
					jQuery("#doaction").bind("click", function (e) {
                        return wpda_check_bulk();
					});
                    jQuery("#doaction2").off();
                    jQuery("#doaction2").bind("click", function (e) {
						return wpda_check_bulk();
					});
					jQuery('#table_name-hide').bind("click", function (e) {
						show_hide_column(jQuery('#table_name-hide').is(":checked"));
					});
					jQuery('#table_type-hide').bind("click", function (e) {
						show_hide_column(jQuery('#table_type-hide').is(":checked"));
					});
					jQuery('#create_time-hide').bind("click", function (e) {
						show_hide_column(jQuery('#create_time-hide').is(":checked"));
					});
					jQuery('#table_rows-hide').bind("click", function (e) {
						show_hide_column(jQuery('#table_rows-hide').is(":checked"));
					});
					jQuery('#auto_increment-hide').bind("click", function (e) {
						show_hide_column(jQuery('#auto_increment-hide').is(":checked"));
					});
					jQuery('#engine-hide').bind("click", function (e) {
						show_hide_column(jQuery('#engine-hide').is(":checked"));
					});
					jQuery('#data_size-hide').bind("click", function (e) {
						show_hide_column(jQuery('#data_size-hide').is(":checked"));
					});
					jQuery('#index_size-hide').bind("click", function (e) {
						show_hide_column(jQuery('#index_size-hide').is(":checked"));
					});
					jQuery('#overhead-hide').bind("click", function (e) {
						show_hide_column(jQuery('#overhead-hide').is(":checked"));
					});
					jQuery('#table_collation-hide').bind("click", function (e) {
						show_hide_column(jQuery('#table_collation-hide').is(":checked"));
					});
				});
			</script>
			<?php

		}

		/**
		 * Overwrite construct_where_clause()
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::construct_where_clause()
		 */
		protected function construct_where_clause() {

			global $wpdb;

			// Make sure we're selecting only tables that are in the WordPress database.
			$where_or_and = '' === $this->where ? ' where ' : ' and ';
			$this->where  .= $wpdb->prepare( " $where_or_and table_schema = %s ", $this->schema_name );

			// Since we are using a view, the default behaviour of the parent will not work for us. We have to
			// define our where clause manually.
			if ( null !== $this->search_value && '' !== $this->search_value ) {

				// A search argument was provided. Let's use it to search for table names.
				$search_values = '%' . esc_attr( $this->search_value ) . '%';
				$this->where   .= $wpdb->prepare( ' and table_name like %s', $search_values );

			}

			$table_access = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS );
			if ( 'hide' === $table_access ) {

				// No access to WordPress tables: filter WordPress table.
				$this->where .= " and table_name not in ('" . implode( "','", $wpdb->tables( 'all', true ) ) . "')";

			} elseif ( 'select' === $table_access ) {

				$option = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );
				if ( '' !== $option ) {
					// Allow only access to selected tables.
					$this->where .= " and table_name in ('" . implode( "','", $option ) . "')";
				} else {
					// No tables selected: no access.
					$this->where .= ' and 1=2';
				}
			}

			if ( null != $this->wpda_main_favourites ) {
				if ( false === $this->favourites && 'show' === $this->wpda_main_favourites ) {
					$where_or_and = '' === $this->where ? ' where ' : ' and ';
					$this->where .= " $where_or_and 1=2 ";
				} else if ( is_array( $this->favourites ) ) {
					if ( 0 < count( $this->favourites ) ) {
						$where_or_and = '' === $this->where ? ' where ' : ' and ';
						$in_or_not_in = 'show' === $this->wpda_main_favourites ? 'in' : 'not in';
						$this->where .= " $where_or_and table_name $in_or_not_in ('" . implode($this->favourites, "','") . "') ";
					}
				}
			}

		}

		/**
		 * Overwrite method: add button to design a table
		 */
		protected function add_header_button( $add_param = '' ) {

			?>

			<form
					method="post"
					action="?page=<?php echo esc_attr( \WP_Data_Access_Admin::PAGE_DESIGNER ); ?>"
					style="display: inline-block; vertical-align: unset;"
			>
				<div>
					<input type="hidden" name="action" value="create_table">
					<input type="hidden" name="caller" value="dataexplorer">
					<input type="submit" value="<?php echo __( 'Design new table', 'wp-data-access' ); ?>"
						   class="page-title-action">
				</div>
			</form>

			<?php
			$this->wpda_import->add_button();
			?>

			<a href="?page=wpda_backup" class="page-title-action"><?php echo __('Scheduled Exports (Data Backup)'); ?></a>

			<?php

		}

		/**
		 * Display the search box
		 *
		 * @since   1.6.0
		 *
		 * @param string $text The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			$input_id = $input_id . '-search-input';

			$wpda_dictionary_lists = new WPDA_Dictionary_Lists();
			$schemas = $wpda_dictionary_lists->get_db_schemas();

			?>
			<div style="padding-top:10px;padding-bottom:0;">
				<?php
				if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_SCHEMAS ) ) {
					?>
					<select id="wpda_main_db_schema_list">
					<?php
					foreach ( $schemas as $schema ) {
						if ( $this->schema_name === $schema['schema_name'] ) {
							$selected = ' selected';
						} else {
							$selected = '';
						}
						?>
						<option value="<?php echo esc_attr( $schema['schema_name'] ); ?>" <?php echo esc_attr( $selected ); ?>>
							<?php echo esc_attr( $schema['schema_name'] ); ?>
						</option>
						<?php
					}
					?>
					</select>
					<?php
				}
				?>
				<select id="wpda_main_favourites_list">
					<option value="" <?php echo '' === $this->wpda_main_favourites ? 'selected' : ''; ?>>Show all</option>
					<option value="show" <?php echo 'show' === $this->wpda_main_favourites ? 'selected' : ''; ?>>Show favourites only</option>
					<option value="hide" <?php echo 'hide' === $this->wpda_main_favourites ? 'selected' : ''; ?>>Hide favourites</option>
				</select>
				<?php
				if ( ! ( null === $this->search_value && ! $this->has_items() ) ) {
					?>
					<p class="search-box">
						<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $this->search_item_name ); ?>"
							   value="<?php echo esc_attr( $this->search_value ); ?>"/>
						<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
						<input type="hidden" name="<?php echo esc_attr( $this->search_item_name ); ?>_old_value" value="<?php echo esc_attr( $this->search_value ); ?>"/>
					</p>
					<?php
				}
				?>
			</div>
			<script language="JavaScript">
				jQuery(document).ready(function() {
					jQuery("#wpda_main_db_schema_list").bind("change", function () {
						jQuery("#wpda_main_db_schema").val(jQuery(this).val());
                        jQuery("#wpda_main_form :input[name='action']").val('-1');
                        jQuery("#wpda_main_form :input[name='action2']").val('-1');
						jQuery("#wpda_main_form").submit();
					});
					jQuery("#wpda_main_favourites_list").bind("change", function () {
						jQuery("#wpda_main_favourites").val(jQuery(this).val());
                        jQuery("#wpda_main_form :input[name='action']").val('-1');
                        jQuery("#wpda_main_form :input[name='action2']").val('-1');
						jQuery("#wpda_main_form").submit();
					});
				});
			</script>
			<?php
		}

		/**
		 * Get schema name from cookie or list
		 *
		 * @since   1.6.0
		 *
		 * @return null|string
		 */
		protected function get_schema_name() {
			$cookie_name = $this->page . '_schema_name';

			if ( isset( $_REQUEST['wpda_main_db_schema'] ) && '' !== $_REQUEST['wpda_main_db_schema'] ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['wpda_main_db_schema'] ) ); // input var okay.
			} elseif ( isset( $_COOKIE[ $cookie_name ] ) ) {
				return $_COOKIE[ $cookie_name ];
			} else {
				return null;
			}
		}

		/**
		 * Get favourite selection from cookie or list
		 *
		 * @since   1.6.0
		 *
		 * @return null|string
		 */
		protected function get_favourites() {
			$cookie_name = $this->page . '_favourites';

			if ( isset( $_REQUEST['wpda_main_favourites'] ) ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['wpda_main_favourites'] ) ); // input var okay.
			} elseif ( isset( $_COOKIE[ $cookie_name ] ) ) {
				return $_COOKIE[ $cookie_name ];
			} else {
				return null;
			}

		}

		/**
		 * Add labels to static function to make it available to class WPDA_List _View. Allowing to hide columns in
		 * Data Explorer main page.
		 *
		 * @since	2.0.3
		 *
		 * @return array
		 */
		public static function column_headers_labels() {
			return [
				'table_name'      => __( 'Name', 'wp-data-access' ),
				'icons'           => '',
				'table_type'      => __( 'Type', 'wp-data-access' ),
				'create_time'     => __( 'Creation Date', 'wp-data-access' ),
				'table_rows'      => __( '#Rows', 'wp-data-access' ),
				'auto_increment'  => __( 'Increment', 'wp-data-access' ),
				'engine'          => __( 'Engine', 'wp-data-access' ),
				'data_size'       => __( 'Data Size', 'wp-data-access' ),
				'index_size'      => __( 'Index Size', 'wp-data-access' ),
				'overhead'        => __( 'Overhead', 'wp-data-access' ),
				'table_collation' => __( 'Collation', 'wp-data-access' ),
			];
		}

	}

}
