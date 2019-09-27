<?php

namespace WPDataAccess\Utilities {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Import_Multi
	 *
	 * Imports a script file that might contain multiple SQL statement including create table and insert into
	 * statements.
	 *
	 * @package WPDataAccess\Utilities
	 * @author  Peter Schulz
	 * @since   1.6.0
	 */
	class WPDA_Import_Multi {

	    const SOLUTIONS = '(<a href="/wp-admin/admin.php?page=wpda_help&docid=import_file_too_large">see solutions</a>)';

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
		 * Pointer to import file
		 *
		 * @var string
		 */
		protected $file_pointer;

		/**
         * Content of import file
         *
         * @var string
         */
        protected $file_content;

		/**
		 * Text to inform user (line 1).
		 * @var string
		 */
		protected $info_text_l1 = null;

		/**
		 * Text to inform user (line 2).
		 * @var string
		 */
		protected $info_text_l2 = null;

		/**
		 * WPDA_Import constructor
		 *
		 * Checks if imports are allowed. Throws an exception if imports are not allowed.
		 *
		 * @since   1.6.0
		 *
		 * @param string $page Page where to post data (url).
		 * @param string $schema_name Database schema name.
		 * @param array  $args Extra arguments.
		 * @throws \Exception Throws exception if export is disabled.
		 */
		public function __construct( $page, $schema_name, $args = null ) {

			if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_IMPORTS ) ) {
				// Prevent import object being created: exception must be handled in calling method.
				throw new \Exception( __( 'ERROR: Not authorized', 'wp-data-access' ) );
			}

			$this->url         = $page;
			$this->schema_name = $schema_name;

			if ( ! is_null( $args ) && isset( $args[0] ) ) {
				$this->info_text_l1 = $args[0];
			} else {
				$this->info_text_l1 = __(
					'Imports files that have been exported using the export feature of this plugin from any page. Supports multiple table imports. Data is imported immediately after file upload.',
					'wp-data-access'
				);
			}

			if ( ! is_null( $args ) && isset( $args[0] ) ) {
				$this->info_text_l2 = $args[1];
			} else {
				$this->info_text_l2 = __(
					'Executes script files containing multiple SQL statements. Supports ZIP files containing multiple script files. Script files are executed immediately after file upload.',
					'wp-data-access'
				);
			}
		}

		/**
		 * Checks if request is valid and allowed
		 *
		 * If the requested import is valid and allowed, the import file is loaded and its content imported.
		 *
		 * @since   1.6.0
		 */
		public function check_post() {

			// Check if import was requested.
			if (
			        isset( $_REQUEST['action'] ) &&
                    'import' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) // input var okay.
			) {
				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, "wpda-import-from-data-explorer" ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( isset( $_FILES['filename'] ) ) {

					if ( 0 === $_FILES['filename']['error']
						&& is_uploaded_file( $_FILES['filename']['tmp_name'] )
					) {
					    if (
					    		'application/zip' === $_FILES['filename']['type'] ||
								'application/x-zip' === $_FILES['filename']['type'] ||
								'application/x-zip-compressed' === $_FILES['filename']['type']
						) {
					        // Process ZIP file.
                            if ( class_exists( '\ZipArchive' ) ) {
                                $zip = new \ZipArchive;
                                if ( $zip->open( $_FILES['filename']['tmp_name'] ) ) {
                                    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
										$this->file_pointer = $zip->getStream( $zip->getNameIndex( $i ) );
                                        $this->import( $zip->getNameIndex( $i ) );
                                    }
                                } else {
                                    // Error reading ZIP file.
                                    $this->import_failed( sprintf( __( 'Import failed (error reading ZIP file)', 'wp-data-access' ), $_FILES['filename']['name'] ) );
                                }
                            } else {
                                // ZipArchive not installed.
                                $this->import_failed( sprintf( __( 'Import failed - ZipArchive not installed %s', 'wp-data-access' ), SELF::SOLUTIONS ) );
                            }
                        } else {
					        // Process plain file.
                            $this->file_pointer = fopen( $_FILES['filename']['tmp_name'], 'rb' );
                            $this->import( $_FILES['filename']['name'] );
                        }
					}
				} else {
                    $this->upload_failed();
				}
			} elseif ( isset( $_REQUEST['impchk'] ) ) {
                $this->upload_failed();
            }

		}

		protected function import( $file_name ) {

			global $wpdb;

			// Check if errors should be shown.
            $hide_errors = isset( $_REQUEST['hide_errors'] ) ? $_REQUEST['hide_errors'] : 'off';

            $result = true;
            global $wpdb;

            if ( '' === $this->schema_name || $wpdb->dbname === $this->schema_name ) {
				$db = null;
				$suppress = $wpdb->suppress_errors( 'on' === $hide_errors );
            } else {
				$db = new \wpdb( DB_USER, DB_PASSWORD, $this->schema_name, DB_HOST );
				$db->suppress_errors( 'on' === $hide_errors );
            }

			if ( false !== $this->file_pointer ) {
				while ( ! feof( $this->file_pointer ) ) {
					$this->file_content .= fread( $this->file_pointer, 4096 );

					// Replace WP prefix and WPDA prefix.
					$this->file_content = str_replace( '{wp_prefix}', $wpdb->prefix, $this->file_content );
					$this->file_content = str_replace( '{wpda_prefix}', WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ), $this->file_content );

					// Find and process SQL statements.
					$sql_end_unix    = strpos( $this->file_content, ";\n");
					$sql_end_windows = strpos( $this->file_content, ";\r\n");
					while ( false !== $sql_end_unix || false !== $sql_end_windows ) {
						if ( false === $sql_end_unix ) {
							$sql_end = $sql_end_windows;
						} elseif ( false === $sql_end_windows ) {
							$sql_end = $sql_end_unix;
						} else {
							$sql_end = min( $sql_end_unix, $sql_end_windows );
						}
						$sql = rtrim( substr( $this->file_content, 0, $sql_end ) );

						$this->file_content = substr( $this->file_content, strpos( $this->file_content, $sql ) + strlen( $sql ) + 1 );

						if ( null === $db ) {
							if ( false === $wpdb->query( $sql ) ) {
								$result = false;
							}
						} else {
							if ( false === $db->query( $sql ) ) {
								$result = false;
							}
						}

						// Find next SQL statement.
						$sql_end_unix    = strpos( $this->file_content, ";\n");
						$sql_end_windows = strpos( $this->file_content, ";\r\n");
					}
				}
			}

            if ( null !== $db ) {
                $db->close();
            } else {
				$wpdb->suppress_errors( $suppress );
			}

			// Process file content.
            if ( ! $result ) {
                $this->import_failed( sprintf( __( 'Import %s failed (check import file)', 'wp-data-access' ), $file_name ) );
            } else {
                // Import succeeded.
                $msg = new WPDA_Message_Box(
                    [
						'message_text' => sprintf( __( 'Import %s completed succesfully', 'wp-data-access' ), $file_name),
                    ]
                );
                $msg->box();
            }

        }

		protected function import_failed( $msg ) {

            // An error occured: inform user.
            $msg = new WPDA_Message_Box(
                [
                    'message_text'           => $msg,
                    'message_type'           => 'error',
                    'message_is_dismissible' => false,
                ]
            );
            $msg->box();

        }

		protected function upload_failed() {

            // File upload failed: inform user.
            $msg = new WPDA_Message_Box(
                [
                    'message_text'           => sprintf( __( 'File upload failed %s', 'wp-data-access' ), SELF::SOLUTIONS),
                    'message_type'           => 'error',
                    'message_is_dismissible' => false,
                ]
            );
            $msg->box();

        }

		/**
		 * Adds an import button
		 *
		 * @since   1.6.0
		 *
		 * @param string $label Button label.
		 * @param string $class Button CSS class.
		 */
		public function add_button( $label = '', $class = 'page-title-action' ) {

			if ( '' === $label ) {
				$label = __( 'Import data/Execute script(s)', 'wp-data-access' );
			}

			?>

			<a href="javascript:void(0)"
			   onclick="jQuery('#upload_file_container_multi').show()"
			   class="<?php echo esc_attr( $class ); ?>">
				<?php echo esc_attr( $label ); ?></a>

			<?php

		}

		/**
		 * Adds an import container
		 *
		 * The container contains an upload form. The container is hidden by default. When the button created in
		 * {@see WPDA_Import_Multi::add_button()} is clicked, the container is shown.
		 *
		 * @since   1.6.0
		 */
		public function add_container() {

			$file_uploads_enabled = @ini_get('file_uploads');

		    if ( class_exists( '\ZipArchive' ) ) {
		        $file_extentions = '.sql,.zip';
            } else {
                $file_extentions = '.sql';
            }

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

			<div id="upload_file_container_multi" style="display: none">
				<div>&nbsp;</div>
				<div class="wpda_upload">
					<?php if ( $file_uploads_enabled ) { ?>
						<p>
							<strong><?php echo __( 'INFO', 'wp-data-access' ); ?></strong>
						</p>
						<p class="wpda_list_indent">
							<?php
							echo sprintf( esc_attr( $this->info_text_l1 ) );
							if ( ! is_null( $this->info_text_l2 ) ) {
								echo '<br/>';
								echo sprintf( esc_attr( $this->info_text_l2 ) );
							}
							?>
						</p>
						<form id="form_import_multi_table" method="post" action="<?php echo esc_attr( $this->url ); ?>&impchk"
							  enctype="multipart/form-data">
							<input type="file" name="filename" id="filename" accept="<?php echo esc_attr( $file_extentions ); ?>">
							<?php echo __('Max file size: ') . @ini_get('upload_max_filesize'); ?>
							<input type="submit" value="<?php echo __( 'Import file/Execute script(s)', 'wp-data-access' ); ?>"
								   class="button button-secondary"
								   onclick="return before_submit_upload()">
							<a href="javascript:void(0)"
							   onclick="jQuery('#upload_file_container_multi').hide()"
							   class="button button-secondary"><?php echo __( 'Cancel', 'wp-data-access' ); ?></a>
							<label style="vertical-align:baseline;"><input type="checkbox" name="hide_errors" style="vertical-align:sub;" checked>Hide errors</label>
							<input type="hidden" name="action" value="import">
							<?php wp_nonce_field( "wpda-import-from-data-explorer", '_wpnonce', false ); ?>
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
