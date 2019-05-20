<?php

namespace WPDataAccess\Utilities {

    use WPDataAccess\WPDA;
    use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;

    class WPDA_Table_Actions {

        protected $schema_name;
        protected $table_name;
        protected $table_structure;
        protected $create_table_stmt_orig;
        protected $create_table_stmt;
        protected $indexes;
        protected $is_wp_table;
        protected $dbo_type;

        public function show() {
            if ( ! isset( $_REQUEST['table_name'] ) || ! isset( $_REQUEST['schema_name'] ) ) {
                wp_die( esc_html__( 'ERROR: Wrong arguments', 'wp-data-access' ) );
            } else {
                $this->schema_name = sanitize_text_field( wp_unslash( $_REQUEST['schema_name'] ) ); // input var okay.
                $this->table_name  = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.

                $wpda_data_dictionary = new WPDA_Dictionary_Exist( $this->schema_name, $this->table_name );
                if ( ! $wpda_data_dictionary->table_exists() ) {
                    echo '<div>' . esc_html__( 'ERROR: Invalid table name or not authorized', 'wp-data-access' ) . '</div>';
                    return;
                }

                $wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
                if ( ! wp_verify_nonce( $wp_nonce, "wpda-actions-{$this->table_name}" ) ) {
                    echo '<div>' . esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) . '</div>';
                    return;
                }

                $this->dbo_type = isset( $_REQUEST['dbo_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['dbo_type'] ) ) : null; // input var okay.

                $this->is_wp_table = WPDA::is_wp_table( $this->table_name );

                global $wpdb;
                $query           = "show full columns from `{$this->schema_name}`.`{$this->table_name}`";
                $this->table_structure = $wpdb->get_results( $query, 'ARRAY_A' );

                if ( strpos( strtoupper( $this->dbo_type ), 'TABLE') !== false ) {
                    $this->dbo_type     = 'Table';
                    $query              = "show create table `{$this->schema_name}`.`{$this->table_name}`";
                    $create_table       = $wpdb->get_results( $query, 'ARRAY_A' );
                    if ( isset( $create_table[0]['Create Table'] ) ) {
                        $this->create_table_stmt_orig = $create_table[0]['Create Table'];
                        $this->create_table_stmt      = preg_replace( "/\(/", "<br/>(", $this->create_table_stmt_orig, 1 );
                        $this->create_table_stmt      = preg_replace( '/\,\s\s\s/', '<br/>,   ', $this->create_table_stmt );
                        $pos                          = strrpos( $this->create_table_stmt, ')' );
                        if ( false !== $pos ) {
                            $this->create_table_stmt =
                                substr( $this->create_table_stmt, 0, $pos - 1 ) .
                                "<br/>)" .
                                substr( $this->create_table_stmt, $pos + 1 );
                        }

                        $query   = "show indexes from `{$this->schema_name}`.`{$this->table_name}`";
                        $this->indexes = $wpdb->get_results( $query, 'ARRAY_A' );
                    } else {
                        $this->create_table_stmt = 'Error reading create table statement';
                    }
                } elseif ( strtoupper( $this->dbo_type ) === 'VIEW') {
                    $this->dbo_type     = 'View';
                    $query              = "show create view `{$this->schema_name}`.`{$this->table_name}`";
                    $create_table       = $wpdb->get_results( $query, 'ARRAY_A' );
                    if ( isset( $create_table[0]['Create View'] ) ) {
                        $this->create_table_stmt_orig = $create_table[0]['Create View'];
                        $this->create_table_stmt      = str_replace( "AS select", "AS<br/>select", $this->create_table_stmt_orig );
                        $this->create_table_stmt      = str_replace( "from", "<br/>from", $this->create_table_stmt );
                    }
                } else {
                    $this->dbo_type = '';
                }
				?>
                <div id="<?php echo esc_attr( $this->table_name ); ?>-tabs" style="border:1px solid #e5e5e5;">
					<div class="nav-tab-wrapper">
						<?php
						if ( '' !== $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->table_name ) . '-sel-1" class="nav-tab nav-tab-active' .
								'" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->table_name ) . '\', \'1\');" 
								style="font-size:inherit;">' .
								__( 'Actions', 'wp-data-access' ) .
								'</a>';
						}
						echo '<a id="' . esc_attr( $this->table_name ) . '-sel-2" class="nav-tab' .
							'" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->table_name ) . '\', \'2\');" 
							style="font-size:inherit;">' .
							__( 'Structure', 'wp-data-access' ) .
							'</a>';
						if ( 'Table' === $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->table_name ) . '-sel-3" class="nav-tab' .
								'" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->table_name ) . '\', \'3\');" 
								style="font-size:inherit;">' .
								__( 'Indexes', 'wp-data-access' ) .
								'</a>';
						}
						if ( '' !== $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->table_name ) . '-sel-4" class="nav-tab' .
								'" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->table_name ) . '\', \'4\');" 
								style="font-size:inherit;">' .
								__( 'SQL', 'wp-data-access' ) .
								'</a>';
						}
						?>
					</div>
					<?php
					if ( '' !== $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->table_name ); ?>-tab-1" style="padding:3px;">
							<?php $this->tab_actions(); ?>
						</div>
						<?php
					}
					?>
                    <div id="<?php echo esc_attr( $this->table_name ); ?>-tab-2" style="padding:3px;display:none;">
                        <?php $this->tab_structure(); ?>
                    </div>
					<?php
					if ( 'Table' === $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->table_name ); ?>-tab-3" style="padding:3px;display:none;">
							<?php $this->tab_index(); ?>
						</div>
						<?php
					}
					if ( '' !== $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->table_name ); ?>-tab-4" style="padding:3px;display:none;">
							<?php $this->tab_sql(); ?>
						</div>
						<?php
					}
					?>
                </div>
                <script language="JavaScript">
					function settab(table_name, tab) {
						for (i=1; i<=4; i++) {
							jQuery("#" + table_name + "-sel-" + i.toString()).removeClass('nav-tab-active');
							jQuery("#" + table_name + "-tab-" + i.toString()).hide();
						}
						jQuery("#" + table_name + "-sel-" + tab).addClass('nav-tab-active');
						jQuery("#" + table_name + "-tab-" + tab).show();
					}
					jQuery(document).ready(function () {
						var sql_to_clipboard = new ClipboardJS("#button-copy-clipboard-<?php echo esc_attr( $this->table_name ); ?>");
                        sql_to_clipboard.on('success', function(e) {
                            alert('SQL successfully copied to clipboard!');
                        });
                        sql_to_clipboard.on('error', function(e) {
                            console.log('Could not copy SQL to clipboard!');
                        });
                        jQuery("#rename-table-from-<?php echo esc_attr( $this->table_name ); ?>").on('keyup paste', function () {
                            this.value = this.value.replace(/[^\w\$\_]/g, '');
                        });
                        jQuery("#copy-table-from-<?php echo esc_attr( $this->table_name ); ?>").on('keyup paste', function () {
                            this.value = this.value.replace(/[^\w\$\_]/g, '');
                        });
                    });
                </script>
                <?php
            }
        }

        protected function tab_structure() {
            ?>
            <table class="widefat striped rows wpda-structure-table">
                <tr>
                    <th><strong><nobr>Column Name</nobr></strong></th>
                    <th><strong><nobr>Data Type</nobr></strong></th>
                    <th><strong>Collation</strong></th>
                    <th><strong>Null?</strong></th>
                    <th><strong>Key?</strong></th>
                    <th><strong><nobr>Default Value</nobr></strong></th>
                    <th style="width:80%;"><strong>Extra</strong></th>
                </tr>
                <?php
                foreach ( $this->table_structure as $column ) {
                    ?>
                    <tr>
                        <td>
                            <nobr><?php echo esc_attr( $column['Field'] ); ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo esc_attr( $column['Type'] ); ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo esc_attr( $column['Collation'] ); ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo esc_attr( $column['Null'] ); ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo esc_attr( $column['Key'] ); ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo esc_attr( $column['Default'] ); ?></nobr>
                        </td>
                        <td><?php echo esc_attr( $column['Extra'] ); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }

        protected function tab_index() {
            ?>
            <table class="widefat striped rows wpda-structure-table">
                <tr>
                    <th><strong><nobr>Index Name</nobr></strong></th>
                    <th><strong>Unique?</strong></th>
                    <th><strong>#</strong></th>
                    <th><strong><nobr>Column Name</nobr></strong></th>
                    <th><strong>Collation</strong></th>
                    <th><strong><nobr>Index Prefix?</nobr></strong></th>
                    <th><strong>Null?</strong></th>
                    <th style="width:80%;"><strong><nobr>Index Type</nobr></strong></th>
                </tr>
                <?php
                if ( 0 === count( $this->indexes ) ) {
                    echo '<tr><td colspan="8">' . __( 'No indexes defined for this table', 'wp-data-access' ) . '</td></tr>';
                }
                $current_index_name = '';
                foreach ( $this->indexes as $index ) {
                    if ( $current_index_name !== $index['Key_name'] ) {
                        $current_index_name = esc_attr( $index['Key_name'] );
                        $new_index          = true;
                    } else {
                        $new_index = false;
                    }
                    ?>
                    <tr>
                        <td>
                            <nobr><?php if ( $new_index ) { echo esc_attr( $index['Key_name'] ); } ?></nobr>
                        </td>
                        <td>
                            <nobr><?php if ( $new_index ) { echo '0' === $index['Non_unique'] ? 'Yes' : 'No'; } ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo esc_attr( $index['Seq_in_index'] ); ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo esc_attr( $index['Column_name'] ); ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo 'A' === $index['Collation'] ? 'Ascending' : 'Not sorted'; ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo esc_attr( $index['Sub_part'] ); ?></nobr>
                        </td>
                        <td>
                            <nobr><?php echo '' === $index['Null'] ? 'NO' : esc_attr( $index['Null'] ); ?></nobr>
                        </td>
                        <td><?php echo esc_attr( $index['Index_type'] ); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }

        protected function tab_sql() {
            ?>
			<table class="widefat striped rows wpda-structure-table">
				<tr>
					<td>
						<?php echo wp_kses( $this->create_table_stmt, [ 'br' => [] ] ); ?>
					</td>
					<td style="text-align: right;">
						<a id="button-copy-clipboard-<?php echo esc_attr( $this->table_name ); ?>"
						   href="javascript:void(0)"
						   class="button button-primary"
						   data-clipboard-text="<?php echo $this->create_table_stmt_orig; ?>"
						>
							Copy to clipboard
						</a>
					</td>
				</tr>
			</table>
            <?php
        }

        protected function tab_actions() {
            ?>
			<table class="widefat striped rows wpda-structure-table">
				<?php
				if ( 'Table' === $this->dbo_type && 'on' === WPDA::get_option( WPDA::OPTION_BE_EXPORT_TABLES ) ) {
					$this->tab_export();
				}
				if ( $this->is_wp_table === false && 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_RENAME ) ) {
					$this->tab_rename();
				}
				if ( 'Table' === $this->dbo_type && $this->is_wp_table === false && 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_COPY ) ) {
					$this->tab_copy();
				}
				if ( 'Table' === $this->dbo_type && $this->is_wp_table === false && 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_TRUNCATE ) ) {
					$this->tab_truncate();
				}
				if ( '' !== $this->dbo_type && $this->is_wp_table === false && 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_DROP ) ) {
					$this->tab_drop();
				}
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_optimize();
				}
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_alter();
				}
				?>
			</table>
            <?php
        }

        protected function tab_export() {
            $check_export_access = 'true';
            if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_CONFIRM_EXPORT ) ) {
                $check_export_access = "confirm('Export table $this->table_name?')";
            }
            $wp_nonce_action   = 'wpda-export-*';
            $wp_nonce          = wp_create_nonce( $wp_nonce_action );
            $src               = "?action=wpda_export&type=table&schema_name={$this->schema_name}&table_names={$this->table_name}&_wpnonce=$wp_nonce&format_type=";

            global $wpdb;
			$export_variable_prefix = false;
            if ( strpos( $this->table_name, $wpdb->prefix ) === 0) {
				// Offer an extra SQL option: SQL (add variable WP prefix)
				$export_variable_prefix = true;
			}
            $export_variable_prefix_option = ( 'on' === WPDA::get_option( WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX ) );
            ?>
            <tr>
                <td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
                    <a href="javascript:void(0)"
                       class="button button-primary"
                       onclick="if (<?php echo esc_attr( $check_export_access ); ?>) jQuery('#stealth_mode').attr('src','<?php echo esc_attr( $src ); ?>' + jQuery('#format_type_<?php echo esc_attr( $this->table_name ); ?>').val())"
                       style="display:block;"
                    >
                        EXPORT
                    </a>
                </td>
                <td style="vertical-align:middle;">
					<span>Export <strong>table `<?php echo esc_attr( $this->table_name ); ?>`</strong> to: </span>
					<select id="format_type_<?php echo esc_attr( $this->table_name ); ?>" name="format_type">
						<option value="sql" <?php echo $export_variable_prefix_option ? '' : 'selected'; ?>>SQL</option>
						<?php if ( $export_variable_prefix ) { ?>
						<option value="sqlpre" <?php echo $export_variable_prefix_option ? 'selected' : ''; ?>>SQL (add variable WP prefix)</option>
						<?php } ?>
						<option value="xml">XML</option>
						<option value="json">JSON</option>
						<option value="excel">Excel</option>
						<option value="csv">CSV</option>
					</select>
					<span> (file download)</span>
                </td>
            </tr>
            <?php
        }

        protected function tab_rename() {
            $wp_nonce_action_rename = "wpda-rename-{$this->table_name}";
            $wp_nonce_rename        = wp_create_nonce( $wp_nonce_action_rename );
            $rename_table_form_id   = 'rename_table_form_' . esc_attr( $this->table_name );
            $rename_table_form      =
				"<form" .
					" id='" . $rename_table_form_id . "'" .
					" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
					" method='post'>" .
					"<input type='hidden' name='action' value='rename-table' />" .
					"<input type='hidden' name='rename_table_name_old' value='" . esc_attr( $this->table_name ) . "' />" .
					"<input type='hidden' name='rename_table_name_new' id='rename_table_name_" . esc_attr( $this->table_name ) . "' value='' />" .
					"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_rename ) . "' />" .
				"</form>";
            ?>
            <tr>
                <td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
                    <script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $rename_table_form; ?>");
                    </script>
                    <a href="javascript:void(0)"
                       class="button button-primary"
                       onclick="if (jQuery('#rename-table-from-<?php echo esc_attr( $this->table_name ); ?>').val()==='') { alert('Please enter a valid table name'); return false; } if (confirm('<?php echo __( 'Rename' ,'wp-data-access' ) . ' ' . esc_attr( strtolower( $this->dbo_type ) ) . '?'; ?>')) { jQuery('#rename_table_name_<?php echo esc_attr( $this->table_name ); ?>').val(jQuery('#rename-table-from-<?php echo esc_attr( $this->table_name ); ?>').val()); jQuery('#<?php echo $rename_table_form_id; ?>').submit(); }"
                       style="display:block;"
                    >
                        RENAME
                    </a>
                </td>
                <td style="vertical-align:middle;">
                    Rename <strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?> `<?php echo esc_attr( $this->table_name ); ?>`</strong> to:
                    <input type="text" id="rename-table-from-<?php echo esc_attr( $this->table_name ); ?>" value="">
                </td>
            </tr>
            <?php
        }

        protected function tab_copy() {
            $wp_nonce_action_copy = "wpda-copy-{$this->table_name}";
            $wp_nonce_copy        = wp_create_nonce( $wp_nonce_action_copy );
            $copy_table_form_id   = 'copy_table_form_' . esc_attr( $this->table_name );
			$copy_table_form      =
				"<form" .
					" id='$copy_table_form_id'" .
					" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
					" method='post'>" .
					"<input type='hidden' name='action' value='copy-table' />" .
					"<input type='hidden' name='copy_table_name_src' value='" . esc_attr( $this->table_name ) . "' />" .
					"<input type='hidden' name='copy_table_name_dst' id='copy_table_name_" . esc_attr( $this->table_name ) . "' value='' />" .
					"<input type='checkbox' name='copy-table-data' id='copy_table_data_" . esc_attr( $this->table_name ) . "' checked />" .
					"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_copy ) . "' />" .
				"</form>";
            ?>
            <tr>
                <td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
                    <script language="JavaScript">
                        jQuery("#wpda_invisible_container").append("<?php echo $copy_table_form; ?>");
                    </script>
                    <a href="javascript:void(0)"
                       class="button button-primary"
                       onclick="if (jQuery('#copy-table-from-<?php echo esc_attr( $this->table_name ); ?>').val()==='') { alert('Please enter a valid table name'); return false; } if (confirm('<?php echo __( 'Copy' ,'wp-data-access' ) . ' ' . esc_attr( strtolower( $this->dbo_type ) ) . '?'; ?>')) { jQuery('#copy_table_name_<?php echo esc_attr( $this->table_name ); ?>').val(jQuery('#copy-table-from-<?php echo esc_attr( $this->table_name ); ?>').val()); jQuery('#<?php echo $copy_table_form_id; ?>').submit(); }"
                       style="display:block;"
                    >
                        COPY
                    </a>
                </td>
                <td style="vertical-align:middle;">
                    Copy <strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?> `<?php echo esc_attr( $this->table_name ); ?>`</strong> to:
                    <input type="text" id="copy-table-from-<?php echo esc_attr( $this->table_name ); ?>" value="">
                    <label style="vertical-align:baseline"><input type="checkbox" checked onclick="jQuery('#copy_table_data_<?php echo esc_attr( $this->table_name ); ?>').prop('checked', jQuery(this).is(':checked'));"><?php echo __( 'Copy data', 'wp-data-access'); ?></label>
                </td>
            </tr>
            <?php
        }

        protected function tab_truncate() {
            $wp_nonce_action_truncate = "wpda-truncate-{$this->table_name}";
            $wp_nonce_truncate        = wp_create_nonce( $wp_nonce_action_truncate );
            $truncate_table_form_id   = 'truncate_table_form_' . esc_attr( $this->table_name );
			$truncate_table_form      =
				"<form" .
					" id='$truncate_table_form_id'" .
					" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
					" method='post'>" .
					"<input type='hidden' name='action' value='truncate' />" .
					"<input type='hidden' name='truncate_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
					"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_truncate ) . "' />" .
				"</form>";
            ?>
            <tr>
                <td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
                    <script language="JavaScript">
                        jQuery("#wpda_invisible_container").append("<?php echo $truncate_table_form; ?>");
                    </script>
                <a href="javascript:void(0)"
                   class="button button-primary"
                   onclick="if (confirm('<?php echo __( 'Truncate table?', 'wp-data-access' ); ?>')) { jQuery('#<?php echo $truncate_table_form_id; ?>').submit(); }"
                   style="display:block;"
                >
                    TRUNCATE
                </a>
                </td>
                <td style="vertical-align:middle;">
                    Permanently delete all data from
                    <strong><?php echo esc_attr(strtolower($this->dbo_type)); ?>
                    `<?php echo esc_attr( $this->table_name ); ?>`</strong>
                    .<br/>
                    <strong>This action cannot be undone!</strong>
                </td>
            </tr>
            <?php
        }

        protected function tab_drop() {
            $wp_nonce_action_drop = "wpda-drop-{$this->table_name}";
            $wp_nonce_drop        = wp_create_nonce( $wp_nonce_action_drop );
            if ( 'View' === $this->dbo_type ) {
                $msg_drop = __( 'Drop view?', 'wp-data-access' );
            } else {
                $msg_drop = __( 'Drop table?', 'wp-data-access' );
            }
            $drop_table_form_id = 'drop_table_form_' . esc_attr( $this->table_name );
			$drop_table_form    =
				"<form" .
					" id='$drop_table_form_id'" .
					" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
					" method='post'>" .
					"<input type='hidden' name='action' value='drop' />" .
					"<input type='hidden' name='drop_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
					"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_drop ) . "' />" .
				"</form>";
            ?>
            <tr>
                <td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
                    <script language="JavaScript">
                        jQuery("#wpda_invisible_container").append("<?php echo $drop_table_form; ?>");
                    </script>
                    <a href="javascript:void(0)"
                       class="button button-primary"
                       onclick="if (confirm('<?php echo $msg_drop; ?>')) { jQuery('#<?php echo $drop_table_form_id; ?>').submit(); }"
                       style="display:block;"
                    >
                        DROP
                    </a>
                </td>
                <td style="vertical-align:middle;">
                    Permanently delete <strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
                    `<?php echo esc_attr( $this->table_name ); ?>`</strong>
                    and all table data from the database.<br/>
                    <strong>This action cannot be undone!</strong>
                </td>
            </tr>
            <?php
        }

		// Data_length
		// Index_length
		// Data_free
		protected function tab_optimize() {
			global $wpdb;

			$table_structure             = $wpdb->get_row( $wpdb->prepare( 'show table status like %s', $this->table_name ) );
			$query_innodb_file_per_table = $wpdb->get_row("show session variables like 'innodb_file_per_table'");

			if ( ! empty( $query_innodb_file_per_table ) ) {
				$innodb_file_per_table = ( 'ON' === $query_innodb_file_per_table->Value );
			} else {
				$innodb_file_per_table = true;
			}

			if ( 'InnoDB' === $table_structure->Engine && ! $innodb_file_per_table ) {
				return;
			}

			$consider_optimize =
				$table_structure->Data_free > 0 && $table_structure->Data_length > 0 &&
				( $table_structure->Data_free / $table_structure->Data_length > 0.2 );

			$wp_nonce_action_optimize    = "wpda-optimize-{$this->table_name}";
			$wp_nonce_optimize           = wp_create_nonce( $wp_nonce_action_optimize );
			$optimize_table_form_id      = 'optimize_table_form_' . esc_attr( $this->table_name );
			$optimize_table_form         =
				"<form" .
					" id='$optimize_table_form_id'" .
					" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
					" method='post'>" .
					"<input type='hidden' name='action' value='optimize-table' />" .
					"<input type='hidden' name='optimize_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
					"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_optimize ). "' />" .
				"</form>";
			$msg_optimize                = __( 'Optimize table?', 'wp-data-access' );
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $optimize_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo $msg_optimize; ?>')) { jQuery('#<?php echo $optimize_table_form_id; ?>').submit(); }"
					   style="display:block;<?php if ( ! $consider_optimize ) { echo 'opacity:0.5;'; } ?>"
					>
						OPTIMIZE
					</a>
				</td>
				<td style="vertical-align:middle;<?php if ( ! $consider_optimize ) { echo 'opacity:0.5;'; } ?>">
					Optimize <strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>.<br/>
					<?php
					if ( $consider_optimize ) {
						?>
						<strong>MySQL locks the table during the time OPTIMIZE TABLE is running!</strong>
						<?php
					} else {
					?>
						<strong>Table optimization not considered useful! But you can...</strong>
					<?php
					}
					?>
				</td>
			</tr>
			<?php
		}

		protected function tab_alter() {
			$wp_nonce_action_alter = "wpda-alter-{$this->table_name}";
			$wp_nonce_alter        = wp_create_nonce( $wp_nonce_action_alter );
			$alter_table_form_id   = 'alter_table_form_' . esc_attr( $this->table_name );
			$alter_table_form      =
				"<form" .
				" id='$alter_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_DESIGNER ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='edit' />" .
				"<input type='hidden' name='action2' value='init' />" .
				"<input type='hidden' name='wpda_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='wpda_table_name_re' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_alter ) . "' />" .
				"<input type='hidden' name='page_number' value='1' />" .
				"<input type='hidden' name='caller' value='dataexplorer' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script language="JavaScript">
						jQuery("#wpda_invisible_container").append("<?php echo $alter_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo __( 'Alter table?', 'wp-data-access' ); ?>')) { jQuery('#<?php echo $alter_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						ALTER
					</a>
				</td>
				<td style="vertical-align:middle;">
					Loads
					<strong><?php echo esc_attr(strtolower($this->dbo_type)); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>
					into the Data Designer.
				</td>
			</tr>
			<?php
		}

	}

}
