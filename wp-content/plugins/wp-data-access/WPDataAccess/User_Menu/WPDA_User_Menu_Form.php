<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\User_Menu
 */

namespace WPDataAccess\User_Menu {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_User_Menu_Form
	 *
	 * An extention of class WPDA_Simple_Form that implements a data entry form for plugin table wp_wpda_menu_items.
	 *
	 * @package WPDataAccess\User_Menu
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_User_Menu_Form extends WPDA_Simple_Form {

		/**
		 * WPDA_User_Menu_Form constructor
		 *
		 * Overwrites messages (success and failure).
		 *
		 * @since   1.0.0
		 *
		 * @param string                                          $schema_name Database schema name.
		 * @param string                                          $table_name Database table name.
		 * @param \WPDataAccess\Data_Dictionary\WPDA_List_Columns $wpda_list_columns Database column list.
		 */
		public function __construct(
			$schema_name,
			$table_name,
			&$wpda_list_columns
		) {
			$args = [
				'wpda_success_msg' => __( 'Succesfully saved changes to database (you might need to refresh your menu)', 'wp-data-access' ),
				'wpda_failure_msg' => __( 'Saving changes to database failed', 'wp-data-access' ),
			];

			$this->check_table_type = false;
			$this->title            = 'Back';

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );
		}

		/**
		 * Overwrite method.
		 *
		 * @param bool   $allow_save
		 * @param string $add_param
		 */
		public function show( $allow_save = true, $add_param = '' ) {
			parent::show( $allow_save, '&tab=menus' );
			?>
			<script language="JavaScript">
				jQuery(document).ready(function () {
					jQuery("#<?php echo esc_attr( $this->current_form_id ); ?> input.wpda_primary_key").prop("readonly", true);
					jQuery("#<?php echo esc_attr( $this->current_form_id ); ?> select.wpda_primary_key").prop("disabled", true);
				});
			</script>
			<?php
		}

		/**
		 * Set item attributes
		 *
		 * Overrides WPDA_Simple_Form::prepare_items().
		 *
		 * @since   1.0.0
		 *
		 * @param boolean $set_back_form_values Set back values entered by user.
		 */
		protected function prepare_items( $set_back_form_values = false ) {

			// Allow keys to be changed for this form.
			$this->set_update_keys( true );

			// Build a list with tables.
			$all_tables_init = WPDA_Dictionary_Lists::get_tables(); // Get all tables first.
			$all_tables      = [];
			foreach ( $all_tables_init as $key => $value ) {
				$all_tables[ $value['table_name'] ] = true;
			}

			// Check table access.
			$table_access = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS );
			if ( 'hide' === $table_access ) {

				// No access to WordPress tables: filter WordPress table.
				global $wpdb;
				foreach ( $wpdb->tables( 'all', true ) as $wp_table ) {
					unset( $all_tables[ $wp_table ] );
				}
			} elseif ( 'select' === $table_access ) {

				$option = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );
				if ( '' !== $option ) {
					// Allow only access to selected tables.
					unset( $all_tables );
					foreach ( $option as $table_name ) {
						$all_tables[ $table_name ] = true;
					}
				} else {
					// No tables selected: no access.
					unset( $all_tables );
					die( '<p>Adding menus is currently not available!<p/><p>Select at least one table in the Plugin/Back-end Settings to continue.</p>' );
				}
			}

			global $wp_roles;
			$all_roles        = $wp_roles->get_names(); // Get all available roles.
			$wp_roles_array   = ( (array) $wp_roles ); // Write roles to array.
			$all_capabilities = $wp_roles_array['roles']; // Get all available capabilities per role.

			// Array holding roles and their capabilities.
			// Used to build listbox of all available capabilities.
			$roles_capabilities_array = [];
			// Array holding all capalities (not role related).
			// Used to show which roles are granted access when a capability is selected (JS).
			$capabilities_array = [];

			foreach ( $all_roles as $role ) {
				foreach ( $all_capabilities[ strtolower( $role ) ] as $role_capabilities ) {
					$roles_capabilities_array[ $role ] = [];
					if ( is_array( $role_capabilities ) ) {
						foreach ( $role_capabilities as $capability => $value ) {
							$roles_capabilities_array[ $role ][] = $capability;
							$capabilities_array[ $capability ]   = true;
						}
					}
				}
			}
			ksort( $capabilities_array ); // Sort array to show values in ascending order in listbox.

			// JS array used to check roles and capabilities.
			$js_roles_capabilities_array = json_encode( $roles_capabilities_array );

			// Add dummy item to column list.
			$this->add_dummy_column( 'dummy_roles_granted' );

			// Reorder columns (menu_slug should be the first column te be displayed).
			$this->order_and_filter_columns(
				[
					'menu_id',
					'menu_slug',
					'menu_name',
					'menu_table_name',
					'menu_capability',
					'dummy_roles_granted',
				]
			);

			$form_item_labels = [
				'menu_id'             => [
					'label' => __( 'Menu ID', 'wp-data-access' ),
				],
				'menu_slug'           => [
					'label' => __( 'Menu Slug', 'wp-data-access' ),
				],
				'menu_name'           => [
					'label' => __( 'Menu Name', 'wp-data-access' ),
				],
				'menu_table_name'     => [
					'label' => __( 'Table Name', 'wp-data-access' ),
				],
				'menu_capability'     => [
					'label' => __( 'Capability', 'wp-data-access' ),
				],
				'dummy_roles_granted' => [
					'label' => __( 'Roles Authorized', 'wp-data-access' ),
				],
			];

			$count_cols = count( $this->table_columns );
			for ( $i = 0; $i < $count_cols; $i++ ) {
				// Process column.
				$column_name = $this->table_columns[ $i ]['column_name'];
				$item_event  = '';
				$item_js     = '';

				// Build a listbox for tables.
				if ( 'menu_table_name' === $column_name ) {
					// Check if table is still available.
					if ( isset( $this->row ) ) {
						if ( ! isset( $all_tables[ $this->row[0][ $column_name ] ] ) ) {
							// Table access is turned of in back-end settings.
							// We'll add the table_name to the list and show a message to inform the user.
							$all_tables[ $this->row[0][ $column_name ] ] = true;
							$msg = new WPDA_Message_Box(
								[
									'message_text' => __( 'Access to table is disabled (menu page will not be accessible)', 'wp-data-access' ),
									'message_type' => 'error',
									'message_is_dismissible' => false,
								]
							);
							$msg->box();
							$msg = new WPDA_Message_Box(
								[
									'message_text' => __( 'Add table in back-end settings, delete menu item or select another table', 'wp-data-access' ),
									'message_type' => 'action',
								]
							);
							$msg->box();
						}
					}
					$this->table_columns[ $i ]['data_type']   = 'enum'; // Set type to enum to show listbox.
					$this->table_columns[ $i ]['column_type'] =
						"enum('" . implode( "','", array_keys( $all_tables ) ) . "')";
				}

				// Build a listbox for column capability.
				if ( 'menu_capability' === $column_name ) {
					$this->table_columns[ $i ]['data_type']   = 'enum'; // Set type to enum to show listbox.
					$this->table_columns[ $i ]['column_type'] =
						"enum('" . implode( "','", array_keys( $capabilities_array ) ) . "')";
					// Build JS code in 3 steps (loop through array in step 2).
					$item_js = '
                    var capabilities_array = ' . $js_roles_capabilities_array . ';
                    var roles_matched = [];
                    function check() {
                        capability = jQuery("select[name=\'menu_capability\']").val();
                        roles_matched = [];
                ';
					foreach ( $all_roles as $role ) {
						$item_js .= '
                        capabilities_array["' . $role . '"].forEach(function (role_capability) {
                            if (role_capability == capability) {
                                roles_matched.push("' . $role . '");
                            }
                        });
                ';
					}
					$item_js .= '
                        jQuery("input[name=\'dummy_roles_granted\']").val(roles_matched.join());
                    }
                    jQuery(document).ready(function() {
                        check();
                        jQuery("select[name=\'menu_capability\']").on("change", function (e) {
                            check();
                        });
                        jQuery("input[name=\'reset_button\']").on("click", function (e) {
                            check();
                        });
                    });
                ';
				}

				if ( 'dummy_roles_granted' === $column_name ) {
					// Set values for dummy item: dummy_roles_granted.
					$item_data_type   = 'dummy';
					$item_value       = '';
					$item_extra       = '';
					$item_enum        = '';
					$item_column_type = '';
					$item_hide_icon   = true;
					$item_js          = '
                    jQuery("input[name=\'dummy_roles_granted\']").prop("readonly", true);
                    jQuery("input[name=\'dummy_roles_granted\']").prop("style", "border: none; background: transparent");
                ';
					$item_class       = 'wpda_user_menu_input';
				} else {
					// Set values for database columns.
					$item_data_type = $this->table_columns[ $i ]['data_type'];
					if ( $set_back_form_values ) {
						// Set value back to what user entered.
						if ( $this->is_key_column( $column_name ) ) {
							// This situation appears after a data entry error. If the column is a key column, values must
							// be reversed. Otherwise the key value would point to a non existing record. In this special
							// case we'll get the value from the database to maintain consistency.
							$item_value = isset( $this->row ) ? $this->row[0][ $column_name ] : '';
						} else {
							// Get post data.
							$item_value = $this->get_new_value( $column_name );
						}
					} else {
						// Get value from database.
						$item_value = isset( $this->row ) ? $this->row[0][ $column_name ] : '';
					}
					if ( 'menu_capability' === $column_name && '' === $item_value ) {
						$item_value = 'manage_options'; // Default value.
					}
					$item_extra = $this->table_columns[ $i ]['extra'];
					$item_enum  = '';
					if ( 'enum' === $item_data_type ) {
						$item_enum = $this->table_columns[ $i ]['column_type'];
					}
					$item_column_type = $this->table_columns[ $i ]['column_type'];
					$item_hide_icon   = false;
					if ('menu_table_name' === $column_name ||
						'menu_capability' === $column_name
					) {
						$item_class = '';
					} else {
						$item_class = 'wpda_user_menu_input';
					}
				}
				$item_label = $form_item_labels[ $column_name ]['label'];

				// Set all item specific properties.
				$item = new WPDA_Simple_Form_Item(
					[
						'item_name'      => $column_name,
						'data_type'      => $item_data_type,
						'item_label'     => $item_label,
						'item_value'     => $item_value,
						'item_extra'     => $item_extra,
						'item_enum'      => $item_enum,
						'column_type'    => $item_column_type,
						'item_event'     => $item_event,
						'item_js'        => $item_js,
						'item_hide_icon' => $item_hide_icon,
						'item_class'     => $item_class,
					]
				);

				$this->add_form_item( $i, $item );
			}

		}

		/**
		 * Validate form data
		 *
		 * @since   1.0.0
		 *
		 * @return bool TRUE = data is valid
		 */
		protected function validate() {

			if ( $this->get_new_value( 'menu_name' ) === '' ) {
				$this->set_message( '1', __( 'Menu name must be entered', 'wp-data-access' ) );
				return false;
			}

			if ( $this->get_new_value( 'menu_table_name' ) === '' ) {
				$this->set_message( '1', __( 'Table name must be entered', 'wp-data-access' ) );
				return false;
			}

			if ( $this->get_new_value( 'menu_capability' ) === '' ) {
				$this->set_message( '1', __( 'Capability must be entered', 'wp-data-access' ) );
				return false;
			}

			return parent::validate();

		}

	}

}
