<?php

namespace WPDataAccess\Design_Table {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\Utilities\WPDA_Reverse_Engineering;
	use WPDataAccess\WPDA;

	class WPDA_Design_Table_Form {

		protected $page                   = null;
		protected $action                 = null;
		protected $action2                = null;
		protected $model                  = null;
		protected $wpda_table_name        = null;
		protected $wpda_table_design      = null;
		protected $table_exists           = null;
		protected $is_wp_table            = false;
		protected $create_table_statement = null;
		protected $create_table_succeeded = null;
		protected $create_index_failed    = null;
		protected $design_mode            = null;
		protected $wpdb_error             = null;

		public function __construct() {

			if ( isset( $_REQUEST['page'] ) ) {
				$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
			} else {
				wp_die( esc_html__( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			if ( isset( $_REQUEST['action'] ) ) {
				$this->action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.
			}

			if ( isset( $_REQUEST['action2'] ) ) {
				$this->action2 = sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ); // input var okay.
			}

			if ( isset( $_REQUEST['design_mode'] ) ) {
				$this->design_mode = sanitize_text_field( wp_unslash( $_REQUEST['design_mode'] ) ); // input var okay.
			} else {
				$this->design_mode = WPDA::get_option( WPDA::OPTION_BE_DESIGN_MODE ); // Default design mode.
			}

			if ( 'wpda_reverse_engineering' === $this->action2 ) {
				if ( isset( $_REQUEST['wpda_table_name_re'] ) ) {
					$wpda_table_name_re = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name_re'] ) );
					// Start reverse engineering tabel.
					$wpda_reverse_engineering = new WPDA_Reverse_Engineering( $wpda_table_name_re );
					$this->design_mode        = isset( $_REQUEST['design_mode_re'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['design_mode_re'] ) ) : $this->design_mode; // input var okay.
					$table_structure          = $wpda_reverse_engineering->get_designer_format( $this->design_mode );
					if ( count( $table_structure ) > 0 ) {
						if ( isset( $_REQUEST['wpda_table_name'] ) && '' !== trim( $_REQUEST['wpda_table_name'] ) ) {
							$this->wpda_table_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) );
						} else {
							$this->wpda_table_name = $wpda_table_name_re;
						}
						$this->wpda_table_design = $table_structure;
					} else {
                        wp_die( esc_html__( 'ERROR: Reverse engineering table failed', 'wp-data-access' ) );
                    }
					if ( ! WPDA_Design_Table_Model::insert_reverse_engineered( $this->wpda_table_name, $this->wpda_table_design ) ) {
						wp_die( esc_html__( 'ERROR: Reverse engineering table failed', 'wp-data-access' ) );
					} else {
						// Convert named array to object (needed to display structure).
						$this->wpda_table_design = json_decode( json_encode( $table_structure ) );
					}
					$this->action2 = 'edit';
				} else {
					wp_die( esc_html__( 'ERROR: Wrong arguments', 'wp-data-access' ) );
				}
			} elseif ( isset( $_REQUEST['wpda_table_name'] ) ) {
				$this->wpda_table_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) );
				$this->model           = new WPDA_Design_Table_Model();

				if ( 'new' === $this->action2 ) {
					if ( $this->model->insert() < 1 ) {
						wp_die( esc_html__( 'ERROR: Insert failed', 'wp-data-access' ) );
					}
					$this->action2 = 'edit'; // Show saved records and allow editing.
				} elseif ( 'edit' === $this->action2 ) {
					$result_update = $this->model->update();
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

				$this->model->query();
                $structure_messages = $this->model->validate();
                foreach ( $structure_messages as $messages ) {
                    if ( 'ERR' === $messages[0] ) {
                        $msg = new WPDA_Message_Box(
                            [
                                'message_text' => $messages[1],
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
				$this->wpda_table_design = $this->model->get_table_design();
				$this->design_mode       = $this->wpda_table_design->design_mode;

				if ( 'create_table' === $this->action2 || 'create_table_and_index' === $this->action2 ) {
					// Perform create table statement.
					$this->create_table();
				} elseif ( 'create_table_index' === $this->action2 ) {
					// Perform create index(es) statement.
					$this->create_index();
				}

                $this->action2 = 'edit'; // Editing mode.
			} else {
				$this->action2 = 'new'; // Design new table from scratch.
			}

			if ( null !== $this->wpda_table_name ) {
				// Check if table name already exists in database.
				global $wpdb;
				$wp_tables = $wpdb->tables( 'all', true );
				if ( isset( $wp_tables[ substr( $this->wpda_table_name, strlen( $wpdb->prefix ) ) ] ) ) {
					$this->is_wp_table  = true;
					$this->table_exists = true;
				} else {
					$wpda_dictionary_exists = new WPDA_Dictionary_Exist( '', $this->wpda_table_name );
					$this->table_exists     = $wpda_dictionary_exists->plain_table_exists();
				}
			}

		}

		protected function create_table() {

			// Perform create table statement.
			$new_line                     = '<br/>';
			$this->create_table_statement = "CREATE TABLE {$this->wpda_table_name}" . $new_line;

			$create_keys = [];
			foreach ( $this->wpda_table_design->table as $row ) {
				$this->create_table_statement .= $row === reset( $this->wpda_table_design->table ) ? '(' : ',';
				$this->create_table_statement .= $row->column_name;
				$this->create_table_statement .= ' ';
				$this->create_table_statement .= $row->data_type;
				if ( '' !== $row->max_length ) {
					$this->create_table_statement .= "($row->max_length)";
				}
				if ( 'enum' === $row->data_type || 'set' === $row->data_type ) {
					$this->create_table_statement .= '(' . $row->list . ')';
				}
				$this->create_table_statement .= ' ';
				$this->create_table_statement .= 'Yes' === $row->mandatory ? 'NOT NULL' : 'NULL';
				if ( '' !== $row->default ) {
					$this->create_table_statement .= " DEFAULT {$row->default}";
				}
				if ( '' !== $row->extra ) {
					$this->create_table_statement .= ' ';
					$this->create_table_statement .= $row->extra;
				}
				if ( 'Yes' === $row->key ) {
					$create_keys[] = $row->column_name;
				}
				$this->create_table_statement .= $new_line;
			}
			if ( 0 < count( $create_keys ) ) {
				$this->create_table_statement .= ',PRIMARY KEY ';
				foreach ( $create_keys as $key ) {
					$this->create_table_statement .= $key === reset( $create_keys ) ? '(' : ',';
					$this->create_table_statement .= $key;
				}
				$this->create_table_statement .= ')';
				$this->create_table_statement .= $new_line;
			}
			$this->create_table_statement .= ')';
			$this->create_table_statement .= $new_line;

			global $wpdb;

			$suppress = $wpdb->suppress_errors( true );

			$this->create_table_succeeded =
				$wpdb->query( str_replace( $new_line, '', $this->create_table_statement ) );

			if ( $this->create_table_succeeded ) {
				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Table created', 'wp-data-access' ),
					]
				);
				$msg->box();

				if ( 'create_table_and_index' === $this->action2 ) {
					// Create index(es).
					$this->create_index();
				}

			} else {
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => __( 'CREATE TABLE failed', 'wp-data-access' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();
				$this->wpdb_error = $wpdb->last_error;
			}

			$wpdb->suppress_errors( $suppress );

		}

		public function show() {

			?>
			<script>
				var row_num = 1;
				var index_num = 1;

				var table_updated = false;
				var index_updated = false;

				const no_cols_selected = 'no column(s) selected';

				function disable_page() {
					jQuery(".wpda_view").prop("readonly", true).prop("disabled", true).addClass("disabled");
					disable_table();
					disable_index();
				}

				function disable_table() {
					jQuery(".wpda_view_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
				}

				function disable_index() {
					jQuery(".wpda_view_index").prop("readonly", true).prop("disabled", true).addClass("disabled");
				}

				function disable_create_buttons() {
					jQuery("#button_create_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery("#button_create_index").prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery("#button_create_table_and_index").prop("readonly", true).prop("disabled", true).addClass("disabled");
				}

				function row_down(e) {
					var curr_id = e.target.parentNode.parentNode.id;
					jQuery('#' + curr_id).closest('tr').next().insertBefore(jQuery("#" + curr_id));
				}

				function row_up(e) {
					var curr_id = e.target.parentNode.parentNode.id;
					jQuery('#' + curr_id).closest('tr').prev().insertAfter(jQuery("#" + curr_id));
				}

				function rem_row(e) {
					var curr_id = e.target.parentNode.parentNode.id;
					if (confirm("Delete column?")) {
						jQuery("#" + curr_id).remove();
						updated_table();
					}
				}

				function rem_index(e) {
					var curr_id = e.target.parentNode.parentNode.id;
					if (confirm("Delete index?")) {
						jQuery("#" + curr_id).remove();
						updated_indexes();
					}
				}

				function updated_table() {
					disable_create_buttons();
					disable_index();
					table_updated = true;
				}

				function updated_indexes() {
					disable_create_buttons();
					disable_table();
					index_updated = true;
				}

				function check_basic_data_type(row_num) {
					switch (jQuery('#basic_data_type_' + row_num).val()) {
						case 'Text':
                            if (jQuery('#max_length_' + row_num).val() === '') {
                                jQuery('#data_type_' + row_num).val('text');
                            } else {
                                jQuery('#data_type_' + row_num).val('varchar');
                            }
							break;
						case 'Integer':
                            jQuery('#data_type_' + row_num).val('int');
							break;
						case 'Real':
                            jQuery('#data_type_' + row_num).val('float');
							break;
						case 'List':
                            jQuery('#data_type_' + row_num).val('enum');
                            jQuery('#max_length_' + row_num).val('');
							break;
						case 'Boolean':
                            jQuery('#data_type_' + row_num).val('tinyint');
                            jQuery('#max_length_' + row_num).val('1');
							break;
						case 'Datetime':
                            jQuery('#data_type_' + row_num).val('datetime');
                            jQuery('#max_length_' + row_num).val('');
							break;
						case 'Binary':
                            if (jQuery('#max_length_' + row_num).val() === '') {
                                jQuery('#data_type_' + row_num).val('blob');
                            } else {
                                jQuery('#data_type_' + row_num).val('varbinary');
                            }
							break;
						case '*ID':
                            jQuery('#data_type_' + row_num).val('int');
                            jQuery('#key_' + row_num).val('Yes');
                            jQuery('#mandatory_' + row_num).val('Yes');
                            jQuery('#max_length_' + row_num).val('');
                            jQuery('#extra_' + row_num).val('AUTO_INCREMENT');
                            jQuery('#default_' + row_num).val('');
                            jQuery('#list_' + row_num).val('');
							break;
						case '*TimestampC':
                            jQuery('#data_type_' + row_num).val('timestamp');
                            jQuery('#key_' + row_num).val('No');
                            jQuery('#mandatory_' + row_num).val('No');
                            jQuery('#max_length_' + row_num).val('');
                            jQuery('#extra_' + row_num).val('');
                            jQuery('#default_' + row_num).val('CURRENT_TIMESTAMP');
                            jQuery('#list_' + row_num).val('');
							break;
						case '*TimestampU':
                            jQuery('#data_type_' + row_num).val('timestamp');
                            jQuery('#key_' + row_num).val('No');
                            jQuery('#mandatory_' + row_num).val('No');
                            jQuery('#max_length_' + row_num).val('');
                            jQuery('#extra_' + row_num).val('ON UPDATE CURRENT_TIMESTAMP');
                            jQuery('#default_' + row_num).val('CURRENT_TIMESTAMP');
                            jQuery('#list_' + row_num).val('');
							break;
					}
					check_numeric_items(jQuery('#basic_data_type_' + row_num).val(), row_num);
				}

				function check_data_type(row_num) {
					check_numeric_items(jQuery('#data_type_' + row_num + ' :selected').parent().attr('label'), row_num);
				}

				function check_numeric_items(data_type, row_num) {
					check_numeric_items_off();
					switch (data_type) {
						case 'Real':
							jQuery('#max_length_' + row_num).attr('class', 'wpda_real wpda_view_table');
							break;
						case 'List':
						case 'Datetime':
						case 'Blob':
							jQuery('#max_length_' + row_num).attr('class', 'wpda_nodataentryallowed wpda_view_table');
							break;
						default:
							jQuery('#max_length_' + row_num).attr('class', 'wpda_digits_only wpda_view_table');
					}
					check_numeric_items_on();
				}

				function check_numeric_items_off() {
					jQuery('.wpda_digits_only').off('keyup paste');
					jQuery('.wpda_real').off('keyup paste');
					jQuery('.wpda_nodataentryallowed').off('keyup paste');
				}

				function check_numeric_items_on() {
					jQuery('.wpda_digits_only').on('keyup paste', function () {
						this.value = this.value.replace(/[^\d]/g, '');
					});
					jQuery('.wpda_real').on('keyup paste', function () {
						this.value = this.value.replace(/[^0-9,]/g, '');
					});
					jQuery('.wpda_nodataentryallowed').on('keyup paste', function () {
						this.value = '';
					});
				}

				function pre_submit() {
					if (jQuery('#wpda_table_name').val() === '') {
						alert('Table name cannot be empty');
						return false;
					}
					if ('<?php echo esc_attr( $this->action2 ); ?>'==='new') {
						if (wpda_db_table_name[jQuery('#wpda_table_name').val()]) {
							alert('Table name already used for another table design');
							return false;
						}
					}
					var all_columns_entered = true;
					jQuery("input[name='column_name[]']").each(function () {
						if (jQuery(this).val() === '') {
							alert('Column names cannot be empty');
							all_columns_entered = false;
						}
					});
					if (!all_columns_entered) {
						return false;
					}
					// Enable all listboxes that have been disable to add arguments to request.
					jQuery("select[id^='key']").each(function () {
						jQuery(this).attr('disabled', false);
					});
					jQuery("select[id^='mandatory']").each(function () {
						jQuery(this).attr('disabled', false);
					});
					return true;
				}

				function add_row(design_mode, init = false, column_name = '', basic_data_type = '', data_type = '', type_attribute = '', key = '', mandatory = '', max_length = '', extra = '', default_value = '', list = '') {
					var basic_columns =`
						<td>
							<select name="basic_data_type[]" id="basic_data_type_${row_num}" onchange="check_basic_data_type(${row_num})" class="wpda_view_table">
								<option value="Text" ${ basic_data_type === 'Text' ? 'selected' : '' }>Text</option>
								<option value="Integer" ${ basic_data_type === 'Integer' ? 'selected' : '' }>Integer</option>
								<option value="Real" ${ basic_data_type === 'Real' ? 'selected' : '' }>Real</option>
								<option value="List" ${ basic_data_type === 'List' ? 'selected' : '' }>List</option>
								<option value="Boolean" ${ basic_data_type === 'Boolean' ? 'selected' : '' }>Boolean</option>
								<option value="Datetime" ${ basic_data_type === 'Datetime' ? 'selected' : '' }>Datetime</option>
								<option value="Binary" ${ basic_data_type === 'Binary' ? 'selected' : '' }>Binary</option>
								<option value="Blob" ${ basic_data_type === 'Blob' ? 'selected' : '' }>Blob</option>
								<option value="*ID" ${ basic_data_type === '*ID' ? 'selected' : '' }>* Numeric ID (auto increment)</option>
								<option value="*TimestampC" ${ basic_data_type === '*TimestampC' ? 'selected' : '' }>* Timestamp (date created)</option>
								<option value="*TimestampU" ${ basic_data_type === '*TimestampU' ? 'selected' : '' }>* Timestamp (last updated)</option>
							</select>
							<input type="hidden" name="data_type[]" id="data_type_${row_num}" value="${data_type}">
							<input type="hidden" name="type_attribute[]" id="type_attribute_${row_num}" value="${type_attribute}">
						</td>
					`;
					var advanced_columns =`
						<td>
							<select name="data_type[]" id="data_type_${row_num}" onchange="check_data_type(${row_num})" class="wpda_view_table">
								<option value="" ${ data_type === '' ? 'selected' : '' }></option>
								<optgroup label="Integer">
									<option value="bit" ${ data_type === 'bit' ? 'selected' : '' }>bit</option>
									<option value="tinyint" ${ data_type === 'tinyint' ? 'selected' : '' }>tinyint</option>
									<option value="smallint" ${ data_type === 'smallint' ? 'selected' : '' }>smallint</option>
									<option value="mediumint" ${ data_type === 'mediumint' ? 'selected' : '' }>mediumint</option>
									<option value="int" ${ data_type === 'int' ? 'selected' : '' }>int</option>
									<option value="bigint" ${ data_type === 'bigint' ? 'selected' : '' }>bigint</option>
								</optgroup>
								<optgroup label="Text">
									<option value="char" ${ data_type === 'char' ? 'selected' : '' }>char</option>
									<option value="varchar" ${ data_type === 'varchar' ? 'selected' : '' }>varchar</option>
									<option disabled="disabled">-</option>
									<option value="tinytext" ${ data_type === 'tinytext' ? 'selected' : '' }>tinytext</option>
									<option value="text" ${ data_type === 'text' ? 'selected' : '' }>text</option>
									<option value="mediumtext" ${ data_type === 'mediumtext' ? 'selected' : '' }>mediumtext</option>
									<option value="longtext" ${ data_type === 'longtext' ? 'selected' : '' }>longtext</option>
								</optgroup>
								<optgroup label="List">
									<option value="enum" ${ data_type === 'enum' ? 'selected' : '' }>enum</option>
									<option value="set" ${ data_type === 'set' ? 'selected' : '' }>set</option>
								</optgroup>
								<optgroup label="Date and Time">
									<option value="date" ${ data_type === 'date' ? 'selected' : '' }>date</option>
									<option value="datetime" ${ data_type === 'datetime' ? 'selected' : '' }>datetime</option>
									<option value="timestamp" ${ data_type === 'timestamp' ? 'selected' : '' }>timestamp</option>
									<option value="time" ${ data_type === 'time' ? 'selected' : '' }>time</option>
									<option value="year" ${ data_type === 'year' ? 'selected' : '' }>year</option>
								</optgroup>
								<optgroup label="Real">
									<option value="decimal" ${ data_type === 'decimal' ? 'selected' : '' }>decimal</option>
									<option value="double" ${ data_type === 'double' ? 'selected' : '' }>double</option>
									<option value="float" ${ data_type === 'float' ? 'selected' : '' }>float</option>
								</optgroup>
								<optgroup label="Binary">
									<option value="char" ${ data_type === 'char' ? 'selected' : '' }>char</option>
									<option value="varchar" ${ data_type === 'varchar' ? 'selected' : '' }>varchar</option>
								</optgroup>
								<optgroup label="Blob">
									<option value="tinyblob" ${ data_type === 'tinyblob' ? 'selected' : '' }>tinyblob</option>
									<option value="blob" ${ data_type === 'blob' ? 'selected' : '' }>blob</option>
									<option value="mediumblob" ${ data_type === 'mediumblob' ? 'selected' : '' }>mediumblob</option>
									<option value="longblob" ${ data_type === 'longblob' ? 'selected' : '' }>longblob</option>
								</optgroup>
								<optgroup label="Boolean">
									<option value="boolean" ${ data_type === 'boolean' ? 'selected' : '' }>boolean</option>
								</optgroup>
							</select>
							<input type="hidden" name="basic_data_type[]" id="basic_data_type_${row_num}" value="${basic_data_type}">
						</td>
						<td>
							<select name="type_attribute[]" id="type_attribute_${row_num}" class="wpda_view_table">
								<option value=""></option>
								<option value="unsigned" ${ type_attribute === 'unsigned' ? 'selected' : '' }>unsigned</option>
								<option value="unsigned zerofill" ${ type_attribute === 'unsigned zerofill' ? 'selected' : '' }>unsigned zerofill</option>
							</select>
						</td>
					`;
					var new_row = `<tr id="row_num_${row_num}">
						<td class="wpda-table-structure-first-column">
							<a href="javascript:void(0)" onclick="row_down(event)" class="dashicons dashicons-arrow-down wpda_view_table"></a>
							<a href="javascript:void(0)" onclick="row_up(event)" class="dashicons dashicons-arrow-up wpda_view_table"></a>
							<a href="javascript:void(0)" onclick="rem_row(event)" class="dashicons dashicons-trash wpda_view_table"></a>
						</td>
						<td>
							<input type="text" name="column_name[]" id="column_name_${row_num}"
								maxlength="64" value="${column_name}" class="wpda_mysql_names wpda_view_table">
						</td>
						${ design_mode === 'basic' ? basic_columns : advanced_columns }
						<td>
							<select name="key[]" id="key_${row_num}" class="wpda_view_table">
								<option value="No" ${ key === 'No' ? 'selected' : '' }>No</option>
								<option value="Yes" ${ key === 'Yes' ? 'selected' : '' }>Yes</option>
							</select>
						</td>
						<td>
							<select name="mandatory[]" id="mandatory_${row_num}" class="wpda_view_table">
								<option value="No" ${ mandatory === 'No' ? 'selected' : '' }>No</option>
								<option value="Yes" ${ mandatory === 'Yes' ? 'selected' : '' }>Yes</option>
							</select>
						</td>
						<td>
							<input type="text" name="max_length[]" id="max_length_${row_num}"
								value="${ max_length === '0' ? '' : max_length }" class="${ basic_data_type === 'Real' ? 'wpda_float' : 'wpda_digits_only' } wpda_view_table">
						</td>
						<td>
							<input type="text" name="extra[]" id="extra_${row_num}" value="${extra}" class="wpda_view_table">
						</td>
						<td>
							<input type="text" name="default[]" id="default_${row_num}" value="${default_value}" class="wpda_view_table">
						</td>
						<td class="wpda-table-structure-last-column">
							<input type="text" name="list[]" id="list_${row_num}" value="${list}" class="wpda_view_table">
						</td>
					</tr>
					`;
					if (jQuery("#wpda_table_structure tr").length === 0) {
						jQuery("#wpda_table_structure").append(new_row);
					} else {
						jQuery("#wpda_table_structure tr:last").after(new_row);
					}
					<?php if ( 'basic' === $this->design_mode ) { ?>
						check_basic_data_type(row_num);
					<?php } ?>
					row_num++;
					jQuery('.wpda_mysql_names').off('keyup paste');
					jQuery('.wpda_mysql_names').on('keyup paste', function () {
						this.value = this.value.replace(/[^\w\$\_]/g, '');
					});
					check_numeric_items_off();
					check_numeric_items_on();
					jQuery(".wpda_view_table").off('change paste keyup', updated_table);
					jQuery(".wpda_view_table").on('change paste keyup', updated_table);
					jQuery("a.wpda_view_table").off();
					jQuery("a.wpda_view_table").on('click', updated_table);
					if (!init) {
						updated_table();
					}
				}

				function select_available(e) {

					var option = jQuery("#columns_available option:selected");
					var add_to = jQuery("#columns_selected");

					option.remove();
					new_option = add_to.append(option);

					if (jQuery("#columns_selected option[value='']").length > 0) {
						// Remove ALL from selected list.
						jQuery("#columns_selected option[value='']").remove();
					}

					jQuery('select#columns_selected option').removeAttr("selected");

				}

				function select_selected(e) {

					var option = jQuery("#columns_selected option:selected");
					if (option[0].value === '') {
						// Cannot remove ALL.
						return;
					}

					var add_to = jQuery("#columns_available");

					option.remove();
					add_to.append(option);

					if (jQuery('select#columns_selected option').length === 0) {
						jQuery("#columns_selected").append(jQuery('<option></option>').attr('value', '').text(no_cols_selected));
					}

					jQuery('select#columns_available option').removeAttr("selected");
				}

				function show_index_dialog(e) {
					if ('<?php echo esc_attr( $this->action ); ?>' === 'view') {
						return;
					}
					var item_id = e.target.id;
					var index_row_num = item_id.substr(item_id.lastIndexOf("_") + 1);

					var columns_available = jQuery(
						'<select id="columns_available" name="columns_available[]" multiple size="8" style="width:200px" onclick="select_available()">' +
						'</select>'
					);
					jQuery("input[name='column_name[]']").each(function () {
						columns_available.append(jQuery('<option></option>').attr('value', jQuery(this).val()).text(jQuery(this).val()));
					});

					var columns_selected = jQuery(
						'<select id="columns_selected" name="columns_selected[]" multiple size="8" style="width:200px" onclick="select_selected()">' +
						'<option value="">' + no_cols_selected + '</option>' +
						'</select>'
					);

					var dialog_table = jQuery('<table style="width:410px"></table>');

					var dialog_table_row_available = dialog_table.append(jQuery('<tr></tr>').append(jQuery('<td width="50%"></td>')));
					dialog_table_row_available.append(columns_available);

					var dialog_table_row_selected = dialog_table.append(jQuery('<tr></tr>').append(jQuery('<td width="50%"></td>')));
					dialog_table_row_selected.append(columns_selected);

					var dialog_text = jQuery('<div style="width:410px"></div>');
					var dialog = jQuery('<div></div>');

					dialog.append(dialog_text);
					dialog.append(dialog_table);

					jQuery(dialog).dialog(
						{
							dialogClass: 'wp-dialog no-close',
							title: 'Add column(s) to index',
							modal: true,
							autoOpen: true,
							closeOnEscape: false,
							resizable: false,
							width: 'auto',
							buttons: {
								"Close": function () {

									var selected_columns = '';
									jQuery("#columns_selected option").each(
										function () {
											selected_columns += jQuery(this).val() + ',';
										}
									);
									if (selected_columns !== '') {
										selected_columns = selected_columns.slice(0, -1);
									}
									jQuery('#column_names_' + index_row_num).val(selected_columns);

									jQuery(this).dialog('destroy').remove();

									updated_indexes();

								},
								"Cancel": function () {

									jQuery(this).dialog('destroy').remove();

								}
							}
						}
					);
					jQuery(".ui-button-icon-only").hide();
				}

				function add_index(init = false, index_name = '', unique = '', column_names = '') {
					var new_index = `<tr id="idx_row_num_${index_num}">
						<td class="wpda-table-structure-first-column">
							<a href="javascript:void(0)" onclick="rem_index(event)" class="dashicons dashicons-trash wpda_view_index"></a>
						</td>
						<td>
							<input type="text" name="index_name[]" id="index_name_${index_num}"
								value="${index_name}" class="wpda_view_index wpda_mysql_names">
						</td>
						<td>
							<select name="unique[]" id="unique_${index_num}" class="wpda_view_index">
								<option value="No" ${ unique === 'No' ? 'selected' : '' }>No</option>
								<option value="Yes" ${ unique === 'Yes' ? 'selected' : '' }>Yes</option>
							</select>
						</td>
						<td>
							<input type="text" name="column_names[]" id="column_names_${index_num}"
								value="${column_names}" onclick="show_index_dialog(event)" readonly>
						</td>
						<td class="wpda-table-structure-last-column">
							<input type="button" name="add_columns[]" id="add_columns_${index_num}"
								value="Add column(s)" onclick="show_index_dialog(event)" class="wpda_view_index">
						</td>
					</tr>`;
					if (jQuery("#wpda_index_structure tr").length === 0) {
						jQuery("#wpda_index_structure").append(new_index);
					} else {
						jQuery("#wpda_index_structure tr:last").after(new_index);
					}
					index_num++;
					jQuery('.wpda_mysql_names').off('keyup paste');
					jQuery('.wpda_mysql_names').on('keyup paste', function () {
						this.value = this.value.replace(/[^\w\$\_]/g, '');
					});
					jQuery('#submit_indexes').attr('disabled', false);
					jQuery(".wpda_view_index").off('change paste keyup', updated_indexes);
					jQuery(".wpda_view_index").on('change paste keyup', updated_indexes);
					jQuery("a.wpda_view_index").off();
					jQuery("a.wpda_view_index").on('click', updated_indexes);
					if (!init) {
						updated_indexes();
					}
				}

				function switch_mode(e) {
					if ( 'new' === '<?php echo esc_attr( $this->action2 ); ?>' && !table_updated && !index_updated ) {
						if (e.target.value!=='<?php echo esc_attr( $this->design_mode ); ?>') {
							if (confirm('Switch to ' + e.target.value + ' mode?')) {
								jQuery('#switch_mode_form').submit();
							} else {
								return false;
							}
						}
					} else if ( 'edit' === '<?php echo esc_attr( $this->action2 ); ?>' || table_updated || index_updated ) {
						if (e.target.value!=='<?php echo esc_attr( $this->design_mode ); ?>') {
							if (confirm('Switch to ' + e.target.value + ' mode?')) {
								jQuery('#design_table_form').submit();
							} else {
								return false;
							}
						}
					}
				}

				function pre_submit_re() {
					if (wpda_db_table_name[jQuery('select[name="wpda_table_name_re"]').val()]) {
						alert('Table name already used for another table design');
						return false;
					}
					jQuery('#design_mode_re').val(jQuery('input[name="design_mode"]:checked').val());
					return true;
				}
			</script>
			<div class="wrap">
				<h1><?php echo __( 'Design new table', 'wp-data-access' ); ?></strong></h1>
				<div>
					<div style="float:left;">
						<span class="dashicons dashicons-warning"></span> <?php echo __( 'This does not (yet) create the table!', 'wp-data-access' ); ?>
						<br/>
						<span class="dashicons dashicons-warning"></span> Table design is in beta! Please leave your
						comments on the <a
								href="https://wordpress.org/support/plugin/wp-data-access/" target="_blank">support
							forum</a>.
					</div>
					<div style="float:right;">
						<a href="javascript:void(0)" onclick="jQuery('#wpda_reset_form')[0].submit();"
						   class="button wpda_view">Reset Form</a>
						<a href="javascript:location.href='?page=<?php echo esc_attr( $this->page ); ?>'"
						   class="button">Back To List</a>
					</div>
				</div>
				<br class="clear"/>
				<?php
				if ( ! $this->wpda_table_design ) {
					// Allow loading table from database into designer (reverse engineering).
					$table_list = WPDA_Dictionary_Lists::get_tables( false );
					?>
					<div id="wpda_reverse_engineering" style="display: none">
						<br class="clear"/>
						<div class="wpda_reverse_engineering">
							<form id="wpda_reverse_engineering_form"
								  action="?page=<?php echo esc_attr( $this->page ); ?>" method="post">
								<label for "table_name">Load table from database </label>
								<select name="wpda_table_name_re">
									<?php
									foreach ( $table_list as $key => $value ) {
										echo '<option value="' . esc_attr( $value['table_name'] ) . '">' . esc_attr( $value['table_name'] ) . '</option>';
									}
									?>
								</select>
								<input type="hidden" name="action" value="create_table"/>
								<input type="hidden" name="action2" value="wpda_reverse_engineering"/>
								<input type="hidden" name="wpda_table_name" id="wpda_table_name_re"
									   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
								<input type="hidden" id="design_mode_re" name="design_mode_re"/>
								<input type="submit"
									   class="button button-primary"
									   value="Start Reverse Engineering"
									   onclick="return pre_submit_re()"
								>
								<a href="javascript:void(0)" onclick="jQuery('#wpda_reverse_engineering').hide()"
								   class="button">Dismiss</a>
							</form>
						</div>
					</div>
					<?php

				}
				?>
				<br class="clear"/>
				<div class="wpda_design_table">
					<form id="design_table_form" action="?page=<?php echo esc_attr( $this->page ); ?>"
						  method="post" onsubmit="return pre_submit()">
						<table class="wpda-table-structure">
							<thead>
							<tr>
								<td class="wpda-table-structure-first-column">
									<label for "table_name">Table name </label>
								</td>
								<td>
									<input type="text" name="wpda_table_name" id="wpda_table_name" maxlength="64"
										   style="width: 100%;"
										   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"
										   onchange="jQuery('#wpda_table_name_re').val(jQuery(this).val())"
										   class="wpda_mysql_names wpda_view_table"
									/>
								</td>
								<td colspan="4">
									<?php
									if ( $this->is_wp_table ) {
										echo '&nbsp;' . '<span class="dashicons dashicons-flag"></span>' .
											__( 'You cannot use a WordPress table name', 'wp-data-access' );
									} else {
										echo $this->table_exists ? '&nbsp;' . '<span class="dashicons dashicons-warning"></span>' .
											__( 'A table with this name already exists in the database', 'wp-data-access' ) : '';
									}
									?>
									<input type="hidden" name="wpda_table_name_original"
										   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
									<?php
									if ( ! $this->wpda_table_design ) {
										?>
										<a href="javascript:void(0)"
										   onclick="jQuery('#wpda_reverse_engineering').show()"
										   class="button wpda_view_table">Load table from database (reverse
											engineering)</a>
										<?php
									}
									?>
								</td>
								<td colspan="4" class="wpda-table-structure-last-column">
									<span style="float:right;">
										<label>
											<input type="radio"
												   name="design_mode"
												   value="basic"
												   onclick="return switch_mode(event)"
												<?php echo 'basic' === $this->design_mode ? 'checked' : ''; ?>
											>
											Basic Design Mode
										</label>
										<label>
											<input type="radio"
												   name="design_mode"
												   value="advanced"
												   onclick="return switch_mode(event)"
												<?php echo 'advanced' === $this->design_mode ? 'checked' : ''; ?>
											>
											Advanced Design Mode
										</label>
									</span>
								</td>
							</tr>
							<?php if ( 'advanced' === $this->design_mode ) { ?>
								<tr>
									<td class="wpda-table-structure-first-column">
										<label for "engine">Engine </label>
									</td>
									<td>
										<select name="engine" id="engine" style="width: 100%;" class="wpda_view_table">
											<?php
											$engines = WPDA_Dictionary_Lists::get_engines();
											$engine_saved = isset ( $this->wpda_table_design->engine ) ? $this->wpda_table_design->engine : '';
											foreach ( $engines as $engine ) {
												$selected_tag = '';
												if ( '' === $engine_saved ) {
													if ( 'DEFAULT' === $engine['support'] ) {
														$selected_tag = 'selected';
													}
												} else {
													if ( $engine_saved === $engine['engine'] ) {
														$selected_tag = 'selected';
													}
												}
											?>
												<option value="<?php echo esc_attr( $engine['engine'] ); ?>" <?php echo esc_attr( $selected_tag ); ?>>
													<?php echo esc_attr( $engine['engine'] ); ?>
												</option>
											<?php
											}
											?>
										</select>
									</td>
									<td colspan="8">
									</td>
								</tr>
								<tr>
									<td class="wpda-table-structure-first-column">
										<label for "collation">Collation </label>
									</td>
									<td>
										<select name="collation" id="collation" style="width: 100%;" class="wpda_view_table">
											<?php
											$character_set_name = '';
											$default_collation = WPDA_Dictionary_Lists::get_default_collation();
											$collations        = WPDA_Dictionary_Lists::get_collations();
											$collation_saved   = isset( $this->wpda_table_design->collation ) ? $this->wpda_table_design->collation : '';
											foreach ( $collations as $collation ) {
												if ( $character_set_name !== $collation['character_set_name'] ) {
													if ( '' !== $character_set_name ) {
														echo '</optgroup>';
													}
													$character_set_name = $collation['character_set_name'];
													echo '<optgroup label="' . esc_attr( $collation['character_set_name'] ) . '">';
												}
												$selected_tag = '';
												if ( '' === $collation_saved ) {
													if ( $collation['collation_name'] === $default_collation[0]['default_collation_name'] ) {
														$selected_tag = 'selected';
													}
												} else {
													if ( $collation_saved === $collation['collation_name'] ) {
														$selected_tag = 'selected';
													}
												}
												?>
												<option value="<?php echo esc_attr( $collation['collation_name'] ); ?>" <?php echo esc_attr( $selected_tag ); ?>>
													<?php echo esc_attr( $collation['collation_name'] ); ?>
												</option>
												<?php
											}
											?>
										</select>
										<?php echo '</optgroup>'; ?>
									</td>
									<td colspan="8">
									</td>
								</tr>
                            <?php } ?>
							<tr>
								<th class="wpda-table-structure-first-column">
									<a href="javascript:void(0)" onclick="add_row('<?php echo esc_attr( $this->design_mode ); ?>')"
									   style="vertical-align:-webkit-baseline-middle;"
									   class="dashicons dashicons-plus wpda_view_table"></a>
								</th>
								<th>
									Column name
								</th>
								<th>
									Column type
								</th>
								<?php if ( 'advanced' === $this->design_mode ) { ?>
									<th>
										Type attribute
									</th>
								<?php } ?>
								<th>
									Key?
								</th>
								<th>
									Mandatory?
								</th>
								<th>
									Max length
								</th>
								<th>
									Extra
								</th>
								<th>
									Default value
								</th>
								<th class="wpda-table-structure-last-column">
									List values
								</th>
							</tr>
							</thead>
							<tbody id="wpda_table_structure"></tbody>
							<tfoot>
							<tr>
								<td colspan="<?php echo 'basic' === $this->design_mode ? '9' : '10'; ?>">
                                    <input type="hidden" name="submitted_changes" value="table"/>
									<input type="hidden" name="action" value="create_table"/>
									<input type="hidden" name="action2"
										   value="<?php echo esc_attr( $this->action2 ); ?>"
									/>
									<?php if ( 'basic' === $this->design_mode ) { ?>
										<input type="hidden" name="engine" id="engine"
											   value="<?php echo isset( $this->wpda_table_design->engine ) ? esc_attr( $this->wpda_table_design->engine ) : ''; ?>"
										/>
										<input type="hidden" name="collation" id="collation"
											   value="<?php echo isset( $this->wpda_table_design->collation ) ? esc_attr( $this->wpda_table_design->collation ) : ''; ?>"
										/>
									<?php } ?>
									<input type="submit" value="Save Table Design"
										   class="button button-primary wpda_view_table"/>
								</td>
							</tr>
							</tfoot>
						</table>
					</form>
					<form id="wpda_reset_form" action="?page=<?php echo esc_attr( $this->page ); ?>" method="post">
						<input type="hidden" name="action" value="create_table"/>
						<?php
						if ( 'new' !== $this->action2 ) {
							?>
							<input type="hidden" name="wpda_table_name"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<?php
						}
						?>
					</form>
					<form id="switch_mode_form" method="post" action="?page=<?php echo esc_attr( $this->page ); ?>&table_name=<?php global $wpdb; echo $wpdb->prefix . WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ) . 'table_design'; ?>">
						<input type="hidden" name="design_mode" value="<?php echo 'basic' === $this->design_mode ? 'advanced' : 'basic'; ?>">
						<input type="hidden" name="action" value="create_table">
					</form>
				</div>
				<br class="clear"/>
				<div class="wpda_design_table">
					<form id="design_table_form_indexes" action="?page=<?php echo esc_attr( $this->page ); ?>"
						  method="post">
						<table class="wpda-table-structure">
							<thead>
							<tr>
								<td class="wpda-table-structure-first-column" colspan="4" style="text-align: left">
									<label>Add indexes</label>
								</td>
							</tr>
							<tr>
								<th class="wpda-table-structure-first-column" style="width: 70px;">
									<a href="javascript:void(0)" onclick="add_index()"
									   style="vertical-align: -webkit-baseline-middle;"
									   class="dashicons dashicons-plus wpda_view_index"></a>
								</th>
								<th>
									Index name
								</th>
								<th>
									Unique?
								</th>
								<th>
									Column name(s)
								</th>
								<th style="width:100px"></th>
							</tr>
							</thead>
							<tbody id="wpda_index_structure">
							</tbody>
							<tfoot>
							<tr>
								<td colspan="5">
                                    <input type="hidden" name="submitted_changes" value="indexes"/>
									<input type="hidden" name="action" value="create_table"/>
									<input type="hidden" name="action2"
										   value="<?php echo esc_attr( $this->action2 ); ?>"/>
									<input type="hidden" name="wpda_table_name"
										   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
									<input type="hidden" name="wpda_table_name_original"
										   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
									<a id="submit_indexes" href="javascript:void(0)"
									   onclick="if (!jQuery(this).attr('disabled')) { jQuery('#design_table_form_indexes').submit(); } else { alert('Save table design changes first!'); }"
									   class="button button-primary wpda_view_index">Save Indexes</a>
								</td>
							</tr>
							</tfoot>
						</table>
					</form>
				</div>
				<br class="clear"/>
				<div class="wpda_design_table">
					<table class="wpda-table-structure">
						<tfoot>
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" border="0" style="margin:auto;">
									<tr>
										<td style="padding:0;">
											<form id="wpda_create_table_form"
												  action="?page=<?php echo esc_attr( $this->page ); ?>"
												  method="post">
												<input type="hidden" name="action" value="create_table"/>
												<input type="hidden" name="action2" value="create_table"/>
												<input type="hidden" name="wpda_table_name"
													   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
												<input type="submit" id="button_create_table" class="button wpda_view"
													   value="<?php echo esc_html__( 'CREATE TABLE ONLY', 'wp-data-access' ); ?>"/>
											</form>
										</td>
										<td style="padding:0;">
											<form id="wpda_create_index_form"
												  action="?page=<?php echo esc_attr( $this->page ); ?>"
												  method="post" style="margin-left:1px;margin-right:1px;">
												<input type="hidden" name="action" value="create_table"/>
												<input type="hidden" name="action2" value="create_table_index"/>
												<input type="hidden" name="wpda_table_name"
													   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
												<input type="submit" id="button_create_index" class="button wpda_view"
													   value="<?php echo esc_html__( 'CREATE INDEXES ONLY', 'wp-data-access' ); ?>"/>
											</form>
										</td>
										<td style="padding:0;">
											<form id="wpda_create_table_and_index_form"
												  action="?page=<?php echo esc_attr( $this->page ); ?>"
												  method="post">
												<input type="hidden" name="action" value="create_table"/>
												<input type="hidden" name="action2" value="create_table_and_index"/>
												<input type="hidden" name="wpda_table_name"
													   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
												<input type="submit" id="button_create_table_and_index" class="button wpda_view"
													   value="<?php echo esc_html__( 'CREATE TABLE AND INDEXES', 'wp-data-access' ); ?>"/>
											</form>
										</td>
									</tr>
								</table>
                            </td>
						</tr>
						</tfoot>
					</table>
				</div>
				<?php
				if ( null !== $this->create_table_statement && false === $this->create_table_succeeded ) {
					?>
					<br class="clear"/>
					<div class="wpda_design_table">
						<table class="wpda-table-structure">
							<tfoot>
							<tr>
								<td>
									<h3>The following CREATE TABLE statement failed</h3>
									<div>
										<div style="padding:10px; text-align: left; width: fit-content; margin: 0 auto;">
											<?php echo $this->create_table_statement; ?>
										</div>
									</div>
                                    <div>
                                        <strong><?php echo $this->wpdb_error; ?></strong>
                                    </div>
								</td>
							</tr>
							</tfoot>
						</table>
					</div>
					<?php
				}
				?>

				<?php
				if ( null !== $this->create_index_failed && 0 < count( $this->create_index_failed ) ) {
					?>
					<br class="clear"/>
					<div class="wpda_design_table">
						<table class="wpda-table-structure">
							<tfoot>
							<tr>
								<td>
									<h3>The following CREATE INDEX statement(s) failed</h3>
									<div>
										<div style="padding:10px; text-align: left; width: fit-content; margin: 0 auto;">
											<?php
											foreach ( $this->create_index_failed as $index_failed ) {
												echo "$index_failed<br/>";
											}
											?>
										</div>
									</div>
								</td>
							</tr>
							</tfoot>
						</table>
					</div>
					<?php
				}
				?>

			</div>
			<?php

			if ( $this->wpda_table_design ) {
				// Display table design.
				foreach ( $this->wpda_table_design->table as $row ) {
					?>
					<script>
						add_row(
							'<?php echo esc_attr( $this->design_mode ); ?>',
							true,
							'<?php echo esc_attr( $row->column_name ); ?>',
							'<?php echo esc_attr( WPDA_Design_Table_Model::datatype2basic( esc_attr( $row->data_type ) ) ); ?>',
							'<?php echo esc_attr( $row->data_type ); ?>',
							'<?php echo esc_attr( $row->type_attribute ); ?>',
							'<?php echo esc_attr( $row->key ); ?>',
							'<?php echo esc_attr( $row->mandatory ); ?>',
							'<?php echo esc_attr( $row->max_length ); ?>',
							'<?php echo esc_attr( $row->extra ); ?>',
							'<?php echo esc_attr( $row->default ); ?>',
							'<?php echo esc_attr( $row->list ); ?>'
						);
					</script>
					<?php
				}
				// Display indexes.
				$indexes_found = false;
				foreach ( $this->wpda_table_design->indexes as $index ) {
					?>
					<script>
						add_index(
							true,
							'<?php echo esc_attr( $index->index_name ); ?>',
							'<?php echo esc_attr( $index->unique ); ?>',
							'<?php echo esc_attr( $index->column_names ); ?>'
						);
					</script>
					<?php
					$indexes_found = true;
				}
				if ( ! $indexes_found ) {
					?>
					<script>
						add_index(true);
					</script>
					<?php
				}
			} else {
				// Display one empty row.
				?>
				<script>
					add_row('<?php echo esc_attr( $this->design_mode ); ?>', true);
					add_index(true);
					disable_index();
					disable_create_buttons();
				</script>
				<?php
			}

			if ( 'view' === $this->action ) {
				?>
				<script language="JavaScript">
					disable_page();
				</script>
				<?php
			}

			if ( $this->is_wp_table ) {
				// WP table names are not allowed: disable create table button.
				?>
				<script language="JavaScript">
					disable_create_buttons();
				</script>
				<?php
			}

			// Save all table names in array table_name check.
			?>
			<script language="JavaScript">
				var wpda_db_table_name = [];
				<?php
				$designer_table_list = WPDA_Design_Table_Model::get_designer_table_list();
				foreach ( $designer_table_list as $key => $value ) {
					echo 'wpda_db_table_name["' . esc_attr( $value['wpda_table_name'] ) . '"]=true;';
				}
				?>
			</script>
			<?php

		}

		protected function create_index() {

			global $wpdb;

			$suppress = $wpdb->suppress_errors( true );

			// Create indexes.
			foreach ( $this->wpda_table_design->indexes as $index ) {
				if ( "" === $index->index_name || '' === $index->column_names ) {
					continue;
				}
				$unique = '';
				if ( 'Yes' === $index->unique ) {
					$unique = 'UNIQUE';
				}
				$create_index_statement =
					"CREATE $unique INDEX {$index->index_name} ON {$this->wpda_table_name} ({$index->column_names})";
				if ( $wpdb->query( $create_index_statement ) ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text' => sprintf( __( 'Index %s created', 'wp-data-access' ), $index->index_name),
						]
					);
					$msg->box();
				} else {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'CREATE INDEX failed', 'wp-data-access' ) . " ({$index->index_name})",
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
					$this->create_index_failed[] = $create_index_statement;
				}
			}

			$wpdb->suppress_errors( $suppress );

		}

	}

}
