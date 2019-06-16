<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities;

use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
use WPDataAccess\Design_Table\WPDA_Design_Table_Model;
use WPDataAccess\User_Menu\WPDA_User_Menu_Model;
use WPDataAccess\WPDA;
use WPDataProjects\Project\WPDP_Project_Design_Table_Model;

/**
 * Class WPDA_Repository
 *
 * Recreate repository objects.
 *
 * @package WPDataAccess\Utilities
 * @author  Peter Schulz
 * @since   1.0.0
 */
class WPDA_Repository {

	const CREATE_TABLE = [
		'wpda_logging' => [
			'create_table_logging.sql'
		],
		'wpda_menu_items' => [
			'create_table_menu_items.sql', 'create_table_menu_items_alter1.sql',
			'create_trigger_menu_items_before_insert.sql', 'create_trigger_menu_items_before_update.sql'
		],
		'wpda_table_design' => [
			'create_table_table_design.sql',
			'create_table_table_design_alter1.sql', 'create_table_table_design_alter3.sql', 'create_table_table_design_alter3.sql'
		],
		'wpdp_project' => [
			'create_table_project.sql'
		],
		'wpdp_page' => [
			'create_table_page.sql'
		],
		'wpdp_table' => [
			'create_table_table.sql'
		]
	];

	const DROP_TABLE = [
		'wpda_logging' => [
			'drop_table_logging.sql'
		],
		'wpda_menu_items' => [
			'drop_table_menu_items.sql'
		],
		'wpda_table_design' => [
			'drop_table_table_design.sql'
		],
		'wpdp_project' => [
			'drop_table_project.sql'
		],
		'wpdp_page' => [
			'drop_table_page.sql'
		],
		'wpdp_table' => [
			'drop_table_table.sql'
		]
	];

	/**
	 * Recreate repository (save as much data as possible)
	 *
	 * @since   2.0.11
	 */
	public function recreate() {
		global $wpdb;

		$suppress = $wpdb->suppress_errors( true );

		$files_processed_successfully = [];
		$files_processed_with_errors  = [];

		foreach ( self::CREATE_TABLE as $key => $value ) {
			$table_name     = $wpdb->prefix . $key;
			$create_table   = true;
			$bck_postfix    = '_BACKUP_' . date('YmdHis');

			// Check if table exists
			$table_exists = new WPDA_Dictionary_Exist( $wpdb->dbname, $table_name );

			$bck_table_name  = null;
			$same_cols       = null;

			if ( $table_exists->table_exists( false ) ) {
				// Drop repository table with extention _new (in case it wasn't removed last time)
				$this->run_script( self::DROP_TABLE[ $key ][0], '_new' );

				// Create backup table
				$bck_table_name   = $wpdb->prefix . $key . $bck_postfix;
				$sql_create_table = "create table $bck_table_name as select * from $table_name";
				$wpdb->query( $sql_create_table );

				// Create new table just to check for changes
				// We only need to process the first script as this creates the table
				if ( $this->run_script( $value[0], '_new' ) ) {
					// Check structure old table
					$sql_check_table =
						"select column_name " .
						"from information_schema.columns " .
						"where table_schema = '{$wpdb->dbname}' " .
						"  and table_name   = '{$table_name}' ";
					$wpdb->get_results( $sql_check_table, 'ARRAY_A' );
					$nocols_old_table = $wpdb->num_rows;

					// Check structure new table
					$sql_check_table =
						"select column_name " .
						"from information_schema.columns " .
						"where table_schema = '{$wpdb->dbname}' " .
						"  and table_name   = '{$table_name}_new' ";
					$wpdb->get_results( $sql_check_table, 'ARRAY_A' );
					$nocols_new_table = $wpdb->num_rows;

					// Check if columns old and new are the same
					$sql_check_table =
						"select c1.column_name " .
						"from information_schema.columns c1 " .
						"where c1.table_schema = '{$wpdb->dbname}' " .
						"  and c1.table_name   = '$table_name' " .
						"  and c1.column_name in ( " .
						"		select c2.column_name " .
						"		from   information_schema.columns c2 " .
						"		where  c2.table_schema = '{$wpdb->dbname}' " .
						"		  and  c2.table_name   = '{$table_name}_new' " .
						"	 )";
					$same_cols       = $wpdb->get_results( $sql_check_table, 'ARRAY_A' );
					$nocols_both     = $wpdb->num_rows;

					// Repository tables already available with right structure, create table not necessary
					$create_table = $nocols_new_table !== $nocols_old_table || $nocols_new_table !== $nocols_both;

					// Drop check table
					$this->run_script( self::DROP_TABLE[ $key ][0], '_new' );
				}
			}

			if ( $create_table ) {
				// Drop table
				$this->run_script( self::DROP_TABLE[ $key ][0], '' );

				// Create table
				foreach ( $value as $sql_file ) {
					$this->run_script( $sql_file );
				}

				// Restore data
				if ( null !== $same_cols ) {
					$selected_columns = '';
					foreach ( $same_cols as $same_col ) {
						$selected_columns .= $same_col['column_name'] . ',';
					}
					$selected_columns = substr( $selected_columns, 0, strlen( $selected_columns ) - 1 );
					$sql_restore =
						"insert into $table_name ($selected_columns) " .
						"select $selected_columns from $bck_table_name";
					$wpdb->query( $sql_restore );

				}
			}

			if ( 'on' !== WPDA::get_option( WPDA::OPTION_MR_KEEP_BACKUP_TABLES ) ) {
				// Drop backup table
				$this->run_script( self::DROP_TABLE[ $key ][0], $bck_postfix );
			}
		}
		$wpdb->suppress_errors( $suppress );
	}

	/**
	 * Create repository
	 *
	 * @since   1.0.0
	 */
	public function create() {
		foreach ( self::CREATE_TABLE as $key => $value ) {
			foreach ( $value as $sql_file ) {
				$this->run_script( $sql_file );
			}
		}
	}

	/**
	 * Drop repository
	 *
	 * @since   1.0.0
	 */
	public function drop() {
		foreach ( self::DROP_TABLE as $key => $value ) {
			foreach ( $value as $sql_file ) {
				$this->run_script( $sql_file );
			}
		}
	}

	protected function run_script( $sql_file, $wpda_postfix = '' ) {
		$sql_repository_file   = plugin_dir_path( dirname( __FILE__ ) ) .
			'../admin/repository/' . $sql_file;
		$sql_repository_handle = fopen( $sql_repository_file, 'r' );

		if ( $sql_repository_handle ) {
			// Read file content and close handle.
			$sql_repository_file_content = fread( $sql_repository_handle, filesize( $sql_repository_file ) );
			fclose( $sql_repository_handle );

			global $wpdb;

			// Replace WP prefix and WPDA prefix.
			$sql_repository_file_content = str_replace( '{wp_prefix}', $wpdb->prefix, $sql_repository_file_content );
			$sql_repository_file_content = str_replace( '{wpda_prefix}', WPDA::get_option( WPDA::OPTION_WPDA_PREFIX ), $sql_repository_file_content );
			$sql_repository_file_content = str_replace( '{wpda_postfix}', $wpda_postfix, $sql_repository_file_content );

			// Run script.
			return $wpdb->query( $sql_repository_file_content ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}
	}


	/**
	 * Inform user if repository is invalid
	 *
	 * Repository is valid if:
	 * + Table menu_items exists
	 * + Before insert trigger exists
	 * + Before update trigger exists
	 * + Table table_design exists
	 *
	 * Repository is invalid if:
	 * + Table menu_items does not exist
	 * + Table table_design does not exist
	 *
	 * Repository is incomplete but working:
	 * + Table menu_items exists
	 * + Triggers do not exists
	 *
	 * If the triggers do not exist but the table is available, inserts and updates are possible. The dbms however
	 * will not validate any data on insert and update. When users use WPDA_User_Menu_Form, data validation is
	 * perform on the server. If users use WPDA_Simple_Form data validation will be insufficient.
	 *
	 * @since   1.0.0
	 */
	public function inform_user() {
		if ( isset( $_REQUEST['setup_error'] ) && 'off' === $_REQUEST['setup_error'] ) {
			// Turn off menu management not available message.
			WPDA::set_option( WPDA::OPTION_WPDA_SETUP_ERROR, 'off' );
		} else {
			if ( 'off' !== WPDA::get_option( WPDA::OPTION_WPDA_SETUP_ERROR ) ) {
				global $wpdb;
				$project_table_name = $wpdb->prefix . 'wpdp_project';
				$page_table_name    = $wpdb->prefix . 'wpdp_page';

				// Check if repository tables exist.
				$wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', $project_table_name );
				$project_table_exists  = $wpda_dictionary_exist->table_exists( false );

				$wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', $page_table_name );
				$page_table_exists     = $wpda_dictionary_exist->table_exists( false );

				if ( ! WPDA_User_Menu_Model::table_menu_items_exists() ||
					! WPDA_Design_Table_Model::table_table_design_exists() ||
					! WPDP_Project_Design_Table_Model::table_table_design_exists() ||
					! $project_table_exists ||
					! $page_table_exists
				) {
					$msg = new WPDA_Message_Box(
						[
							'message_text' =>
								__( 'Some features of WP Data Access are currently not available.', 'wp-data-access' ) .
								' ' .
								__( 'ACTION', 'wp-data-access' ) .
								': ' .
								'<a href="?page=wpda_settings&tab=repository">' . __( 'Recreate repository', 'wp-data-access' ) . '</a>' .
								' ' .
								__( 'to to solve this problem.', 'wp-data-access' ) .
								' [' .
								'<a href="?' . $_SERVER['QUERY_STRING'] . '&setup_error=off">' . __( 'do not show this message again', 'wp-data-access' ) . '</a>' .
								']',
						]
					);

					$msg->box();
				}
			}
		}

		if ( isset( $_REQUEST['whats_new'] ) && 'off' === $_REQUEST['whats_new'] ) {
			// Turn off what's new message.
			WPDA::set_option( WPDA::OPTION_WPDA_SHOW_WHATS_NEW, 'off' );
		}
		if ( 'off' !== WPDA::get_option( WPDA::OPTION_WPDA_SHOW_WHATS_NEW ) ) {
			$msg = new WPDA_Message_Box(
				[
					'message_text' =>
						__( 'See the ', 'wp-data-access' ) .
						' ' .
						'<a href="?page=wpda_help&docid=whats_new">' . __( 'what\'s new', 'wp-data-access' ) . '</a>' .
						' ' .
						__( 'page for new features added to WP Data Access.', 'wp-data-access' ) .
						' [' .
						'<a href="?' . $_SERVER['QUERY_STRING'] . '&whats_new=off">' . __( 'do not show this message again', 'wp-data-access' ) . '</a>' .
						']',
				]
			);

			$msg->box();
		}
	}

}