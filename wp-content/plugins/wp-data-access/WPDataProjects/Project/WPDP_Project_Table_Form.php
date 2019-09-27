<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;
	use WPDataAccess\Utilities\WPDA_Message_Box;

	/**
	 * Class WPDP_Project_Table_Form
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Table_Form {

		/**
		 * @var null
		 */
		protected $page = null;

		/**
		 * @var null
		 */
		protected $wpda_table_name = null;
		/**
		 * @var array|null
		 */
		protected $table_structure = null;

		/**
		 * @var null|WPDA_Dictionary_Exist
		 */
		protected $wpda_data_dictionary = null;
		/**
		 * @var null|WPDP_List_Columns
		 */
		protected $wpda_list_columns = null;

		/**
		 * @var null
		 */
		protected $action  = null;
		/**
		 * @var null|string
		 */
		protected $action2 = null;

		/**
		 * @var null|boolean
		 */
		protected $has_primary_key = null;

		/**
		 * WPDP_Project_Table_Form constructor.
		 */
		public function __construct() {
			if ( isset( $_REQUEST['page'] ) ) {
				$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
			} else {
				wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			if ( isset( $_REQUEST['action'] ) ) {
				$this->action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			}

			if ( isset( $_REQUEST['action2'] ) ) {
				$this->action2 = sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
			}

			if ( isset( $_REQUEST['wpda_table_name'] ) && ! is_array( $_REQUEST['wpda_table_name'] ) ) {
				$wpda_project_design_table_model = new WPDP_Project_Design_Table_Model();
				if ( 'reconcile' === $this->action ) {
					$wpda_table_name_re       = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) ); // input var okay.
					$wpda_reverse_engineering = new \WPDataAccess\Utilities\WPDA_Reverse_Engineering( $wpda_table_name_re );
					$table_structure          = $wpda_reverse_engineering->get_designer_format( 'advanced' );
					if ( isset( $_REQUEST['keep_options'] ) ) {
						$param_keep_options = sanitize_text_field( wp_unslash( $_REQUEST['keep_options'] ) );
					} else {
						$param_keep_options = 'off';
					}
					$result_update = $wpda_project_design_table_model->reconcile( $table_structure, $param_keep_options );
					if ( false === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => __( 'Update failed', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}
					if ( 0 === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Nothing to save', 'wp-data-access' ),
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Succesfully saved changes to database', 'wp-data-access' ),
							]
						);
						$msg->box();
					}
				} elseif ( 'reverse_engineering' === $this->action ) {
					if ( isset( $_REQUEST['wpda_table_name'] ) ) {
						$wpda_table_name_re       = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) ); // input var okay.
						$wpda_reverse_engineering = new \WPDataAccess\Utilities\WPDA_Reverse_Engineering( $wpda_table_name_re );
						$table_structure          = $wpda_reverse_engineering->get_designer_format( 'advanced' );
						if ( count( $table_structure ) > 0 ) {
							$this->wpda_table_name   = $wpda_table_name_re;
							$this->wpda_table_design = $table_structure;
						} else {
							wp_die( __( 'ERROR: Reverse engineering table failed', 'wp-data-access' ) );
						}
						if ( ! WPDP_Project_Design_Table_Model::insert_reverse_engineered( $this->wpda_table_name, $this->wpda_table_design ) ) {
							wp_die( __( 'ERROR: Reverse engineering table failed', 'wp-data-access' ) );
						} else {
							// Convert named array to object (needed to display structure).
							$this->wpda_table_design = json_decode( json_encode( $table_structure ) );
						}
						$this->action2 = 'edit';
						$msg           = new WPDA_Message_Box(
							[
								'message_text' => __( 'Table added to respository', 'wp-data-access' ),
							]
						);
						$msg->box();
					} else {
						wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
					}
				} elseif ( null !== $this->action2 ) {
					$result_update = $wpda_project_design_table_model->update();
					if ( false === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => __( 'Update failed', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}
					if ( 0 === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Nothing to save', 'wp-data-access' ),
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Succesfully saved changes to database', 'wp-data-access' ),
							]
						);
						$msg->box();
					}
				}

				$wpda_project_design_table_model->query();
				$structure_messages = $wpda_project_design_table_model->validate();

				foreach ( $structure_messages as $messages ) {
					if ( 'ERR' === $messages[0] ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => $messages[1],
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => $messages[1],
							]
						);
						$msg->box();
					}
				}

				$this->table_structure      = $wpda_project_design_table_model->get_table_design();
				$this->wpda_table_name      = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) );
				$this->wpda_data_dictionary = new WPDA_Dictionary_Exist( '', $this->wpda_table_name );

				if ( $this->wpda_data_dictionary->table_exists() ) {
					$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( '', $this->wpda_table_name, 'tableform' );
					if ( 0 === count( $this->wpda_list_columns->get_table_primary_key() ) ) {
						$this->has_primary_key = false;
					} else {
						$this->has_primary_key = true;
					}
				} else {
					wp_die( __( 'ERROR: Invalid table name or not authorized', 'wp-data-access' ) );
				}
			} else {
				wp_die( __( 'ERROR: Argument wpda_table_name not found', 'wp-data-access' ) );
			}
		}

		/**
		 *
		 */
		public function show() {
			?>
			<div class="wrap">
				<h1>
					<a
							href="javascript:void(0)"
							onclick="javascript:location.href='?page=wpdp&tab=tables'"
							style="display: inline-block; vertical-align: unset;"
							class="dashicons dashicons-arrow-left-alt"
							title="<?php echo __( 'Back to table list', 'wp-data-access' ); ?>"
					></a>&nbsp;
					<?php echo __( 'Back to table list', 'wp-data-access' ); ?>
				</h1>
				<form
						method="post"
						action="?page=<?php echo esc_attr( $this->page ); ?>&tab=tables"
						style="display: inline-block; vertical-align: unset;"
				>
					<table cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td>
								<input type="hidden" name="action" value="reconcile">
								<input type="hidden" name="wpda_table_name"
									   value="<?php echo esc_attr( $this->wpda_table_name ); ?>">
								<input type="submit"
									   value="<?php echo esc_html__( 'Reconcile Table', 'wp-data-access' ); ?>"
									   class="page-title-action">
								&nbsp;
							</td>
							<td style="vertical-align: top;">
								<label><input type="checkbox" name="keep_options">Keep options?</label>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<?php
			$this->show_table_info();
			if ( true === $this->has_primary_key ) {
				echo '<br/>';
				$this->show_relations();
			}
			echo '<br/>';
			$this->show_list_table();
			if ( true === $this->has_primary_key ) {
				echo '<br/>';
				$this->show_table_form();
			}
			?>
			<script language="JavaScript">
				jQuery(document).ready(function () {
					jQuery('.dashicons-arrow-down').click(function (event) {
						var curr_id = event.target.parentNode.parentNode.id;
						jQuery('#' + curr_id).closest('tr').next().insertBefore(jQuery("#" + curr_id));
					});
					jQuery('.dashicons-arrow-up').click(function (event) {
						var curr_id = event.target.parentNode.parentNode.id;
						jQuery('#' + curr_id).closest('tr').prev().insertAfter(jQuery("#" + curr_id));
					});
				});
			</script>
			<?php
		}

		/**
		 *
		 */
		protected function show_table_info() {
			if ( isset( $this->table_structure->tableinfo ) && isset( $this->table_structure->tableinfo->tab_label ) ) {
				$tab_label = $this->table_structure->tableinfo->tab_label;
			} else {
				$tab_label = '';
			}
			?>
			<form id="wpdp_form_table_info" method="post">
				<table class="wpda-table-structure">
					<thead>
					<tr>
						<td colspan="2" class="wpda-table-structure-first-column-left" style="text-align:left;">
							<label style="font-weight: normal;">
								<?php echo __( 'Manage info for table', 'wp-data-access' ); ?>
							</label>
							<label>
								<?php echo esc_attr( $this->wpda_table_name ); ?>
							</label>
						</td>
					</tr>
					</thead>
					<tbody id="wpda_table_info">
					<tr>
						<td style="text-align: right; padding-right: 5px; width: 120px;">
							<label>
								<?php echo __( 'Tab label', 'wp-data-access' ) ?>
							</label>
						</td>
						<td>
							<input type="text" name="tab_label" value="<?php echo esc_attr( $tab_label ); ?>"/>
						</td>
					</tr>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="2">
							<input type="hidden" name="tab" value="tables"/>
							<input type="hidden" name="wpda_table_name"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<input type="hidden" name="action" value="edit"/>
							<input type="hidden" name="action2" value="tableinfo"/>
							<input type="submit"
								   class="button button-primary"
								   value="<?php echo __( 'Save table info', 'wp-data-access' ); ?>"/>
						</td>
					</tr>
					</tfoot>
				</table>
			</form>
			<?php
		}

		/**
		 *
		 */
		protected function show_relations() {
			$source_table_columns = $this->wpda_list_columns->get_table_columns();
			$target_table_names   = WPDA_Dictionary_Lists::get_tables( true );
			$i                    = 0;
			?>
			<script language="JavaScript">
				var row_num = 0;
				var col_num = [];

				function wpdp_get_columns(item, index, target_id, selected_value = '') {
					table_name = jQuery(item).val();
					var url = location.pathname + '?action=get_columns';
					var data = {table_name: table_name};
					jQuery.post(
						url,
						data,
						function (data) {
							jQuery(target_id).find('option').remove();
							var jsonData = JSON.parse(data);
							for (i = 0; i < jsonData.length; i++) {
								if (jsonData[i]['column_name'] === selected_value) {
									jQuery(target_id).append(
										jQuery("<option></option>")
										.attr("value", jsonData[i]['column_name'])
										.text(jsonData[i]['column_name'])
										.attr("selected", true)
									);
								} else {
									jQuery(target_id).append(
										jQuery("<option></option>")
										.attr("value", jsonData[i]['column_name'])
										.text(jsonData[i]['column_name'])
									);
								}
							}
						}
					);
				}

				function add_row(relation_type, source_column_name, target_table_name, target_column_name, relation_table_name) {
					if (relation_type === undefined || relation_type === '') {
						relation_type = '1n';
					}
					if (source_column_name === undefined || source_column_name === '') {
						source_column_name = [''];
					}
					if (relation_table_name === undefined || relation_table_name === '') {
						relation_table_name = '';
					}
					var new_row = `
                        <tr id="relation_${row_num}" style="vertical-align:top;">
                            <td class="wpda-table-structure-first-column-left">
                                <a href="javascript:void(0)" class="dashicons dashicons-arrow-down"></a>
                                <a href="javascript:void(0)" class="dashicons dashicons-arrow-up"></a>
                                <a href="javascript:void(0)" class="dashicons dashicons-trash" onclick="rem_row(event)"></a>
                                <input type="hidden" name="row_num[]" value="${row_num}" />
                            </td>
                            <td>
                                <select name="relation_type[]" onclick="change_relationship(event, ${row_num})">
                                    <option value="1n"${ relation_type === '1n' ? ' selected' : '' }>1:n</option>
                                    <option value="nm"${ relation_type === 'nm' ? ' selected' : '' }>n:m</option>
                                    <option value="lookup"${ relation_type === 'lookup' ? ' selected' : '' }>lookup</option>
                                </select>
                            </td>
                            <td>
                                <select name="source_column_name[]" id="source_column_name_${row_num}">
                                    <?php
						foreach ( $source_table_columns as $column ) {
						?>
                                        <option value="<?php echo esc_attr( $column['column_name'] ); ?>"${ source_column_name[0] === "<?php echo esc_attr( $column['column_name'] ); ?>" ? " selected" : "" }>
                                            <?php echo $column['column_name']; ?>
                                        </option>
                                    <?php
						}
						?>
                                </select>
                                <input type="hidden" name="num_source_column_name[]" id="num_source_column_name_${row_num}" value="0" />
                            </td>
                            <td>
                                <a href="javascript:void(0)"
                                   style="vertical-align:-webkit-baseline-middle;"
                                   class="dashicons dashicons-plus"
                                   title="Add column"
                                   onclick="add_column(${row_num})"
                                   id="remove_column_names_${row_num}"
                                ></a>
                            </td>
                            <td>
                                <select id="target_table_name_${row_num}" name="target_table_name[]" onclick="wpdp_get_columns(this, ${row_num}, '#target_column_name_${row_num}')">
                                    <option value=""></option>
                                    <?php
						foreach ( $target_table_names as $target_table_name ) {
						?>
                                        <option value="<?php echo esc_attr( $target_table_name['table_name'] ); ?>"${ target_table_name === "<?php echo esc_attr( $target_table_name['table_name'] ); ?>" ? " selected" : "" }>
                                            <?php echo esc_attr( $target_table_name['table_name'] ); ?>
                                        </option>
                                    <?php
						}
						?>
                                </select>
                            </td>
                            <td>
                                <select name="target_column_name[]" id="target_column_name_${row_num}"></select>
                            </td>
                            <td class="wpda-table-structure-last-column">
                                <select name="relation_table_name_${row_num}" id="relation_table_name_${row_num}"></select>
                            </td>
                        </tr>
                    `;
					if (jQuery("#wpda_table_structure tr").length === 0) {
						jQuery("#wpda_table_structure").append(new_row);
					} else {
						jQuery("#wpda_table_structure tr:last").after(new_row);
					}
					col_num[row_num] = 0;
					if (jQuery('#target_table_name_' + row_num + ' option:selected').val() !== '') {
						wpdp_get_columns(jQuery('#target_table_name_' + row_num), row_num, '#target_column_name_' + row_num, target_column_name[0]);
						for (i = 1; i < target_column_name.length; i++) {
							add_column(row_num, target_column_name[i]);
							jQuery('#source_column_name_' + row_num + '_' + i).val(source_column_name[i]);
						}
					}
					jQuery('#relation_table_name_' + row_num).append("<option value=''></option>");
					<?php
					foreach ( $target_table_names as $target_table_name ) {
					$option =
						"<option value='" . esc_attr( $target_table_name['table_name'] ) . "'>" .
						esc_attr( $target_table_name['table_name'] ) .
						"</option>";
					?>
					jQuery('#relation_table_name_' + row_num).append("<?php echo $option; ?>");
					jQuery('#relation_table_name_' + row_num).val(relation_table_name);
					<?php
					}
					?>
					if (relation_type === '1n' || relation_type === 'lookup') {
						jQuery('#relation_table_name_' + row_num).hide();
					}
					row_num++;
				}

				function change_relationship(e, index) {
					if (jQuery(e.target).val() === 'nm') {
						jQuery('#relation_table_name_' + index).show();
					} else {
						jQuery('#relation_table_name_' + index).hide();
					}
				}

				function rem_row(e) {
					var curr_id = e.target.parentNode.parentNode.id;
					if (confirm("Delete relationship?")) {
						jQuery("#" + curr_id).remove();
					}
				}

				function add_column(index, selected_value = '') {
					var source_column_name_list =
						`
                        <br id="br_source_column_name_${index}_${col_num[index] + 1}"/>
                        <select name="source_column_name_${index}_${col_num[index] + 1}" id="source_column_name_${index}_${col_num[index] + 1}">
                            <?php
							foreach ( $source_table_columns as $column ) {
							?>
                                <option value="<?php echo esc_attr( $column['column_name'] ); ?>">
                                    <?php echo $column['column_name']; ?>
                                </option>
                            <?php
							}
							?>
                        </select>
                        `;
					//jQuery(source_column_name_list).insertAfter('#source_column_name_' + index);
					jQuery(source_column_name_list).insertAfter(jQuery('#source_column_name_' + index).parent().children().last());

					var remove_column_names_list =
						`
                        <div id="remove_column_names_${index}_${col_num[index] + 1}" style="padding-top:4px;">
                            <a href="javascript:void(0)"
                               style="vertical-align:-webkit-baseline-middle;"
                               class="dashicons dashicons-minus"
                               title="Remove column"
                               onclick="rem_column(${index},${col_num[index] + 1})"
                            ></a>
                        </div>
                        `;
					jQuery(remove_column_names_list).insertAfter(jQuery('#remove_column_names_' + index).parent().children().last());

					var target_column_name_list =
						`
                        <br id="br_target_column_name_${index}_${col_num[index] + 1}"/>
                        <select name="target_column_name_${index}_${col_num[index] + 1}" id="target_column_name_${index}_${col_num[index] + 1}">
                        </select>
                        `;
					jQuery(target_column_name_list).insertAfter(jQuery('#target_column_name_' + index).parent().children().last());
					wpdp_get_columns(jQuery('#target_table_name_' + index), index, '#target_column_name_' + index + '_' + eval(col_num[index] + 1), selected_value);

					col_num[index] += 1;
					jQuery('#num_source_column_name_' + index).val(col_num[index]);
				}

				function rem_column(index, seq) {
					console.log('#source_column_name_' + index + '_' + seq);
					jQuery('#br_source_column_name_' + index + '_' + seq).remove();
					jQuery('#source_column_name_' + index + '_' + seq).remove();
					jQuery('#remove_column_names_' + index + '_' + seq).remove();
					jQuery('#br_target_column_name_' + index + '_' + seq).remove();
					jQuery('#target_column_name_' + index + '_' + seq).remove();
				}
			</script>
			<form id="wpdp_form_relations" method="post">
				<table class="wpda-table-structure">
					<thead>
					<tr>
						<td colspan="5" class="wpda-table-structure-first-column-left" style="text-align:left;">
							<label style="font-weight: normal;">
								<?php echo __( 'Manage relationships for table', 'wp-data-access' ); ?>
							</label>
							<label>
								<?php echo esc_attr( $this->wpda_table_name ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th class="wpda-table-structure-first-column-left" style="text-align:right;">
							<a href="javascript:void(0)"
							   style="vertical-align:-webkit-baseline-middle;"
							   class="dashicons dashicons-plus add-row"
							   onclick="add_row()"
							></a>
						</th>
						<th>
							<?php echo __( 'Type', 'wp-data-access' ) ?>
						</th>
						<th>
							<?php echo __( 'Source column name', 'wp-data-access' ) ?>
						</th>
						<th style="width:20px;"></th>
						<th>
							<?php echo __( 'Target table name', 'wp-data-access' ) ?>
						</th>
						<th>
							<?php echo __( 'Target column name', 'wp-data-access' ) ?>
						</th>
						<th>
							<?php echo __( 'Relation table name (only n:m)', 'wp-data-access' ) ?>
							<span
									class="dashicons dashicons-info"
									title="<?php echo __( 'Table shown on the other end of the n:m relationship (instead of target table shown for 1:n relationships). Not available for 1:n relationships.', 'wp-data-access' ) ?>"
									style="cursor:pointer;"
							></span>
						</th>
					</tr>
					</thead>
					<tbody id="wpda_table_structure">
					</tbody>
					<tfoot>
					<tr>
						<td colspan="7">
							<input type="hidden" name="tab" value="tables"/>
							<input type="hidden" name="wpda_table_name"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<input type="hidden" name="action" value="edit"/>
							<input type="hidden" name="action2" value="relation"/>
							<input type="submit"
								   class="button button-primary"
								   value="<?php echo __( 'Save relationships', 'wp-data-access' ); ?>"/>
						</td>
					</tr>
					</tfoot>
				</table>
			</form>
			<?php
			if ( isset( $this->table_structure->relationships ) ) {
				$relationships = $this->table_structure->relationships;
				if ( 0 < count( $relationships ) ) {
					foreach ( $relationships as $relationship ) {
						?>
						<script>
							<?php
							if ( ! is_array( $relationship->source_column_name ) ) {
								$source_column_name_array = '[""]';
							} else {
								$source_column_name_array = wp_json_encode( $relationship->source_column_name );
							}
							echo "var source_column_name_array = " . $source_column_name_array . ";\n";

							if ( ! is_array( $relationship->target_column_name ) ) {
								$target_column_name_array = '[""]';
							} else {
								$target_column_name_array = wp_json_encode( $relationship->target_column_name );
							}
							echo "var target_column_name_array = " . $target_column_name_array . ";\n";
							if ( isset( $relationship->relation_table_name ) ) {
								$relation_table_name = $relationship->relation_table_name;
							} else {
								$relation_table_name = '';
							}
							?>
							add_row(
								'<?php echo esc_attr( $relationship->relation_type ); ?>',
								source_column_name_array,
								'<?php echo esc_attr( $relationship->target_table_name ); ?>',
								target_column_name_array,
								'<?php echo esc_attr( $relation_table_name ); ?>'
							);
						</script>
						<?php
					}
				} else {
					?>
					<script>
						add_row('', '', '', '');
					</script>
					<?php
				}
			} else {
				?>
				<script>
					add_row('', '', '', '');
				</script>
				<?php
			}
		}

		/**
		 * @return mixed
		 */
		protected function show_list_table() {
			if ( null === $this->table_structure ) {
				return __( 'Invalid structure', 'wp-data-access' );
			}
			?>
			<form id="wpdp_form_labels" method="post">
				<table class="wpda-table-structure">
					<thead>
					<tr>
						<td colspan="8" class="wpda-table-structure-first-column-left" style="text-align:left;">
							<label style="font-weight: normal;">
								<?php echo __( 'Manage columns for list table of table', 'wp-data-access' ); ?>
							</label>
							<label>
								<?php echo esc_attr( $this->wpda_table_name ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th class="wpda-table-structure-first-column-left"></th>
						<th>
							<?php echo __( 'Column name', 'wp-data-access' ) ?>
						</th>
						<th>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</th>
						<th>
							<?php echo __( 'Data type', 'wp-data-access' ) ?>
						</th>
						<th>
							<?php echo __( 'Key?', 'wp-data-access' ) ?>
						</th>
						<th>
							<?php echo __( 'Mandatory?', 'wp-data-access' ) ?>
						</th>
						<th></th>
						<th>
							<?php echo __( 'List label (uncheck to hide column)', 'wp-data-access' ) ?>
						</th>
						<th></th>
						<th>
							<?php echo __( 'Lookup', 'wp-data-access' ) ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
					$table_structure = [];
					if ( isset( $this->table_structure->listtable_column_options ) ) {
						$structure = $this->table_structure->listtable_column_options;
						foreach ( $this->table_structure->table as $column ) {
							$table_structure[ $column->column_name ] = $column;
						}
					} else {
						$structure = $this->table_structure->table;
					}
					$i = 0;
					foreach ( $structure as $column ) {
						$column_name = $column->column_name;
						if ( isset( $this->table_structure->listtable_column_options ) && isset ( $table_structure[ $column_name ] ) ) {
							$data_type      = $table_structure[ $column_name ]->data_type;
							$type_attribute = $table_structure[ $column_name ]->type_attribute;
							$key            = $table_structure[ $column_name ]->key;
							$mandatory      = $table_structure[ $column_name ]->mandatory;
							$max_length     = $table_structure[ $column_name ]->max_length;
						} else {
							$msg = new WPDA_Message_Box(
								[
									'message_text'           => __( "Column $column_name not found for list table", 'wp-data-access' ),
									'message_type'           => 'error',
									'message_is_dismissible' => false,
								]
							);
							$msg->box();
							break;
						}
						if ( '' === $max_length ) {
							$data_type = $data_type . ' ' . $type_attribute;
						} else {
							$data_type = $data_type . ' (' . $max_length . ') ' . $type_attribute;
						}
						if ( isset( $this->table_structure->listtable_column_options ) ) {
							$show_in_list   = 'on' === $column->show ? 'checked' : '';
							$label_in_list  = $column->label;
							$lookup_in_list = isset( $column->lookup ) ? $column->lookup : '';
						} else {
							$show_in_list   = 'checked';
							$label_in_list  = ucfirst( str_replace( '_', ' ', $column_name ) );
							$lookup_in_list = '';
						}
						$i++;
						?>
						<tr id="listtable_<?php echo esc_attr( $i ); ?>">
							<td class="wpda-table-structure-first-column-left">
								<a href="javascript:void(0)" class="dashicons dashicons-arrow-down"></a>
								<a href="javascript:void(0)" class="dashicons dashicons-arrow-up"></a>
							</td>
							<td>
								<?php echo esc_attr( $column_name ); ?>
								<input type="hidden" name="list_item_name[]"
									   value="<?php echo esc_attr( $column_name ); ?>"/>
							</td>
							<td></td>
							<td>
								<?php echo esc_attr( $data_type ); ?>
							</td>
							<td>
								<?php echo esc_attr( $key ); ?>
							</td>
							<td>
								<?php echo esc_attr( $mandatory ); ?>
							</td>
							<td style="text-align:right;width:16px;">
								<input type="checkbox"
									   name="<?php echo esc_attr( $column_name ); ?>_show"
									<?php echo esc_attr( $show_in_list ); ?>
									   style="vertical-align:middle;width:16px;height:16px;"
								/>
							</td>
							<td>
								<input type="text"
									   name="<?php echo esc_attr( $column_name ); ?>"
									   value="<?php echo esc_attr( $label_in_list ); ?>"
									   style="vertical-align:middle;"
								/>
							</td>
							<td></td>
							<td class="wpda-table-structure-last-column">
								<?php
								$has_lookup = false;
								if ( isset( $this->table_structure->relationships ) ) {
									foreach ( $this->table_structure->relationships as $relationship ) {
										if ( $column_name === $relationship->source_column_name[0] && 'lookup' === $relationship->relation_type ) {
											$lookup_column_list = WPDA_Dictionary_Lists::get_table_columns( $relationship->target_table_name );
											?>
											<select name="<?php echo esc_attr( $column_name ); ?>_lookup">
											<?php
											foreach ( $lookup_column_list as $lookup_column ) {
												?>
												<option value="<?php echo esc_attr( $lookup_column['column_name'] ); ?>"
													<?php if ( $lookup_in_list === $lookup_column['column_name'] ) { echo 'selected'; } ?>
												>
                                    				<?php echo $lookup_column['column_name']; ?>
                                				</option>
												<?php
											}
											?>
											</select>
											<?php
											$has_lookup = true;
										}
									}
								}
								if ( ! $has_lookup ) {
									echo '--';
								}
								?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="8">
							<input type="hidden" name="tab" value="tables"/>
							<input type="hidden" name="wpda_table_name"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<input type="hidden" name="action" value="edit"/>
							<input type="hidden" name="action2" value="listtable"/>
							<input type="submit"
								   class="button button-primary"
								   value="<?php echo __( 'Save list table columns', 'wp-data-access' ); ?>"/>
						</td>
					</tr>
					</tfoot>
				</table>
			</form>
			<?php
		}

		/**
		 * @return mixed
		 */
		protected function show_table_form() {
			if ( null === $this->table_structure ) {
				return __( 'Invalid structure', 'wp-data-access' );
			}
			?>
			<form id="wpdp_form_labels" method="post">
				<table class="wpda-table-structure">
					<thead>
					<tr>
						<td colspan="8" class="wpda-table-structure-first-column-left" style="text-align:left;">
							<label style="font-weight: normal;">
								<?php echo __( 'Manage columns for data entry form for table', 'wp-data-access' ); ?>
							</label>
							<label>
								<?php echo esc_attr( $this->wpda_table_name ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th class="wpda-table-structure-first-column-left"></th>
						<th>
							<?php echo __( 'Column name', 'wp-data-access' ) ?>
						</th>
						<th>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</th>
						<th>
							<?php echo __( 'Data type', 'wp-data-access' ) ?>
						</th>
						<th>
							<?php echo __( 'Key?', 'wp-data-access' ) ?>
						</th>
						<th>
							<?php echo __( 'Mandatory?', 'wp-data-access' ) ?>
						</th>
						<th></th>
						<th>
							<?php echo __( 'Form label (uncheck to hide column)', 'wp-data-access' ) ?>
						</th>
						<th></th>
						<th>
							<?php echo __( 'Lookup', 'wp-data-access' ) ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
					$table_structure = [];
					if ( isset( $this->table_structure->tableform_column_options ) ) {
						$structure = $this->table_structure->tableform_column_options;
						foreach ( $this->table_structure->table as $column ) {
							$table_structure[ $column->column_name ] = $column;
						}
					} else {
						$structure = $this->table_structure->table;
					}
					$i = 0;
					foreach ( $structure as $column ) {
						$column_name = $column->column_name;
						if ( isset( $this->table_structure->tableform_column_options ) && isset ( $table_structure[ $column_name ] ) ) {
							$data_type      = $table_structure[ $column_name ]->data_type;
							$type_attribute = $table_structure[ $column_name ]->type_attribute;
							$key            = $table_structure[ $column_name ]->key;
							$mandatory      = $table_structure[ $column_name ]->mandatory;
							$max_length     = $table_structure[ $column_name ]->max_length;
						} else {
							$msg = new WPDA_Message_Box(
								[
									'message_text'           => __( "Column $column_name not found for data entry form", 'wp-data-access' ),
									'message_type'           => 'error',
									'message_is_dismissible' => false,
								]
							);
							$msg->box();
							break;
						}
						if ( '' === $max_length ) {
							$data_type = $data_type . ' ' . $type_attribute;
						} else {
							$data_type = $data_type . ' (' . $max_length . ') ' . $type_attribute;
						}
						if ( isset( $this->table_structure->tableform_column_options ) ) {
							$show_on_form   = 'on' === $column->show ? 'checked' : '';
							$label_on_form  = $column->label;
							$lookup_in_list = isset( $column->lookup ) ? $column->lookup : '';
						} else {
							$show_on_form   = 'checked';
							$label_on_form  = ucfirst( str_replace( '_', ' ', $column_name ) );
							$lookup_in_list = '';
						}
						if ( $this->wpda_list_columns->get_auto_increment_column_name() === $column_name ) {
							// Allow to hide auto_increment column.
							$key       = 'No';
							$mandatory = 'No';
						}
						$i++;
						?>
						<tr id="tableform_<?php echo esc_attr( $i ); ?>">
							<td class="wpda-table-structure-first-column-left">
								<a href="javascript:void(0)" class="dashicons dashicons-arrow-down"></a>
								<a href="javascript:void(0)" class="dashicons dashicons-arrow-up"></a>
							</td>
							<td>
								<?php echo esc_attr( $column_name ); ?>
								<input type="hidden" name="list_item_name[]"
									   value="<?php echo esc_attr( $column_name ); ?>"/>
							</td>
							<td></td>
							<td>
								<?php echo esc_attr( $data_type ); ?>
							</td>
							<td>
								<?php echo esc_attr( $key ); ?>
							</td>
							<td>
								<?php echo esc_attr( $mandatory ); ?>
							</td>
							<td style="text-align:right;width:16px;">
								<input type="checkbox"
									   name="<?php echo esc_attr( $column_name ); ?>_show"
									<?php echo esc_attr( $show_on_form ); ?>
									<?php if ( 'Yes' === $key || 'Yes' === $mandatory ) {
										echo ' disabled="disabled"';
									} ?>
									   style="vertical-align:middle;width:16px;height:16px;"
								/>
								<?php if ( 'Yes' === $key || 'Yes' === $mandatory ) { ?>
									<input name="<?php echo esc_attr( $column_name ); ?>_show" type="hidden"
										   value="true"/>
								<?php } ?>
							</td>
							<td>
								<input type="text"
									   name="<?php echo esc_attr( $column_name ); ?>"
									   value="<?php echo esc_attr( $label_on_form ); ?>"
									   style="vertical-align:middle;"
								/>
							</td>
							<td></td>
							<td class="wpda-table-structure-last-column">
								<?php
								$has_lookup = false;
								if ( isset( $this->table_structure->relationships ) ) {
									foreach ( $this->table_structure->relationships as $relationship ) {
										if ( $column_name === $relationship->source_column_name[0] && 'lookup' === $relationship->relation_type ) {
											$lookup_column_list = WPDA_Dictionary_Lists::get_table_columns( $relationship->target_table_name );
											?>
											<select name="<?php echo esc_attr( $column_name ); ?>_lookup">
												<?php
												foreach ( $lookup_column_list as $lookup_column ) {
													?>
													<option value="<?php echo esc_attr( $lookup_column['column_name'] ); ?>"
														<?php if ( $lookup_in_list === $lookup_column['column_name'] ) { echo 'selected'; } ?>
													>
														<?php echo $lookup_column['column_name']; ?>
													</option>
													<?php
												}
												?>
											</select>
											<?php
											$has_lookup = true;
										}
									}
								}
								if ( ! $has_lookup ) {
									echo '--';
								}
								?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="8">
							<input type="hidden" name="tab" value="tables"/>
							<input type="hidden" name="wpda_table_name"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<input type="hidden" name="action" value="edit"/>
							<input type="hidden" name="action2" value="tableform"/>
							<input type="submit"
								   class="button button-primary"
								   value="<?php echo __( 'Save data entry form columns', 'wp-data-access' ); ?>"/>
						</td>
					</tr>
					</tfoot>
				</table>
			</form>
			<?php
		}

	}

}
