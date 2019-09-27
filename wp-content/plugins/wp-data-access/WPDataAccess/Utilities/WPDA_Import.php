<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\List_Table\WPDA_List_Table;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Import
	 *
	 * Imports a script file that contains exactly one insert into statement (can insert multiple records). Only
	 * insert statements are allowed. Insert is only allowed into the table name provided in constructor. Subqueries
	 * are not allowed (checked with explain).
	 *
	 * @package WPDataAccess\Utilities
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Import {

		/**
		 * URL where to post data
		 *
		 * @var string
		 */
		protected $url;

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
		 * Indicates where imports are allowed
		 *
		 * @var string 'on' or 'off'
		 */
		protected $allow_imports;

		/**
		 * WPDA_Import constructor
		 *
		 * Checks if imports are allowed. Throws an exception if imports are not allowed.
		 *
		 * @since   1.0.0
		 *
		 * @param string $page Page where to post data (url).
		 * @param string $schema_name Database schema name.
		 * @param string $table_name Database table name.
		 * @throws \Exception Throws exception if export is disabled.
		 */
		public function __construct( $page, $schema_name, $table_name ) {

			if ( ! WPDA::is_wpda_table( $table_name ) ) {
				// Check access rights for non WPDA tables.
				if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_IMPORTS ) ) {
					// Prevent import object being created: exception must be handled in calling method.
					throw new \Exception( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}
				// Disable import for views.
				$wpda_dictionary_exists = new WPDA_Dictionary_Exist( $schema_name, $table_name );
				if ( $wpda_dictionary_exists->is_view() ) {
					// Prevent import object being created: exception must be handled in calling method.
					throw new \Exception( __( 'ERROR: Import not allowed on views', 'wp-data-access' ) );
				}
			}

			$this->url         = $page;
			$this->schema_name = $schema_name;
			$this->table_name  = $table_name;

		}

		/**
		 * Checks if request is valid and allowed
		 *
		 * If the requested import is valid and allowed, the import file is loaded and its content imported.
		 *
		 * @since   1.0.0
		 */
		public function check_post() {

			// Check if import was requested.
			// Import is not possible for WPDA_List_Table::LIST_BASE_TABLE (view in mysql information_schema).
			if ( WPDA_List_Table::LIST_BASE_TABLE !== $this->table_name &&
				isset( $_REQUEST['action'] ) && 'import' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) // input var okay.
			) {
				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, "wpda-import-{$this->table_name}" ) ) {
					wp_die( esc_html__( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( isset( $_FILES['filename'] ) ) {

					if ( UPLOAD_ERR_OK === $_FILES['filename']['error']
						&& is_uploaded_file( $_FILES['filename']['tmp_name'] )
					) {
						// Get file content.
						$wpda_import = new WPDA_Import_File( $_FILES['filename']['tmp_name'] );

						// Check if errors should be shown.
						$hide_errors = isset( $_REQUEST['hide_errors'] ) ? $_REQUEST['hide_errors'] : 'off';

						// Process file content.
						$wpda_import->import( $this->schema_name, $this->table_name, $hide_errors );
					}
				} else {
					// File upload failed: inform user.
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'File upload failed', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
				}
			}

		}

		/**
		 * Adds an import button
		 *
		 * @since   1.0.0
		 *
		 * @param string $label Button label.
		 * @param string $class Button CSS class.
		 */
		public function add_button( $label = '', $class = 'page-title-action' ) {

			if ( '' === $label ) {
				$label = __( 'Import', 'wp-data-access' );
			}

			?>

			<a href="javascript:void(0)"
				onclick="jQuery('#upload_file_container').show()"
				class="<?php echo esc_attr( $class ); ?>">
				<?php echo esc_attr( $label ); ?></a>

			<?php

		}

		/**
		 * Adds an import container
		 *
		 * The container contains an upload form. The container is hidden by default. When the button created in
		 * {@see WPDA_Import::add_button()} is clicked, the container is shown.
		 *
		 * @since   1.0.0
		 */
		public function add_container() {

			$file_uploads_enabled = @ini_get('file_uploads');

			?>

			<script language="JavaScript">
				function before_submit_upload() {
					if (jQuery('#filename').val() == '') {
						alert('<?php echo __( 'No file to import!', 'wp-data-access' ); ?>');
						return false;
					}
					if (!(jQuery('#filename')[0].files[0].size < <?php echo WPDA::convert_memory_to_decimal( @ini_get('upload_max_filesize') ); ?>)) {
						alert("File exceeds maximum size of <?php echo @ini_get('upload_max_filesize'); ?>!");
						return false;
					}
				}
			</script>

			<div id="upload_file_container" style="display: none">
				<div>&nbsp;</div>
				<div class="wpda_upload">
					<?php if ( $file_uploads_enabled ) { ?>
						<p>
							<strong><?php echo esc_html__( 'INFO', 'wp-data-access' ); ?></strong>
						</p>
						<p class="wpda_list_indent">
							<?php
							/* translators: %s: table name */
							$text = __(
								'Supports only data imports for table %s. To import data into another table navigate to that table from the Data Explorer and start the import from there or perform the import from the Data Explorer main page. Data is imported immediately after file upload.',
								'wp-data-access'
							);
							echo sprintf( esc_attr( $text ), esc_attr( $this->table_name ) );
							?>
						</p>
						<form id="form_import_table" method="post" action="<?php echo esc_attr( $this->url ); ?>"
							enctype="multipart/form-data">
							<input type="file" name="filename" id="filename" accept=".sql">
							<?php echo __('Max file size: ') . @ini_get('upload_max_filesize'); ?>
							<input type="submit" value="<?php echo esc_html__( 'Import file', 'wp-data-access' ); ?>"
								class="button button-secondary"
								onclick="return before_submit_upload()">
							<a href="javascript:void(0)"
								onclick="jQuery('#upload_file_container').hide()"
								class="button button-secondary"><?php echo esc_html__( 'Cancel', 'wp-data-access' ); ?></a>
							<label style="vertical-align:baseline;"><input type="checkbox" name="hide_errors" style="vertical-align:sub;" checked>Hide errors</label>
							<input type="hidden" name="action" value="import">
							<?php wp_nonce_field( "wpda-import-{$this->table_name}", '_wpnonce', false ); ?>
						</form>
					<?php } else { ?>
						<p>
							<strong><?php echo __( 'ERROR', 'wp-data-access' ); ?></strong>
						</p>
						<p class="wpda_list_indent">
							<?php
							echo sprintf( __( 'Your configuration does not allow file uploads! Set <strong>file_uploads</strong> to <strong>On</strong> (<a href="/wp-admin/admin.php?page=wpda_help&docid=import_file_too_large">see documentation</a>).' ) );
							?>
						</p>
					<?php } ?>
				</div>
				<div>&nbsp;</div>
			</div>

			<?php

		}

	}

}
