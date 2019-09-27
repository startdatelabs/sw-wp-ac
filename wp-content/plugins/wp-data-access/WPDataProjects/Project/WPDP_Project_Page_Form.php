<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */

namespace WPDataProjects\Project {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataProjects\Parent_Child\WPDP_Child_Form;

	/**
	 * Class WPDP_Project_Page_Form
	 *
	 * @package WPDataProjects\Project
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Page_Form extends WPDP_Child_Form {

		/**
		 * WPDP_Project_Page_Form constructor.
		 *
		 * @param       $schema_name
		 * @param       $table_name
		 * @param       $wpda_list_columns
		 * @param array $args
		 */
		public function __construct( $schema_name, $table_name, $wpda_list_columns, array $args = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'project_id'        => __( 'Project ID', 'wp-data-access' ),
				'page_id'           => __( 'Page ID', 'wp-data-access' ),
				'add_to_menu'       => __( 'Add To Menu', 'wp-data-access' ),
				'page_name'         => __( 'Menu Name', 'wp-data-access' ),
				'page_type'         => __( 'Type', 'wp-data-access' ),
				'page_table_name'   => __( 'Table Name', 'wp-data-access' ),
				'page_mode'         => __( 'Mode', 'wp-data-access' ),
				'page_allow_insert' => __( 'Allow insert?', 'wp-data-access' ),
				'page_allow_delete' => __( 'Allow delete?', 'wp-data-access' ),
				'page_content'      => __( 'Post', 'wp-data-access' ),
				'page_title'        => __( 'Title', 'wp-data-access' ),
				'page_subtitle'     => __( 'Subtitle', 'wp-data-access' ),
				'page_role'         => __( 'Role', 'wp-data-access' ),
				'page_where'        => __( 'WHERE Clause', 'wp-data-access' ),
				'page_sequence'     => __( 'Seq#', 'wp-data-access' ),
			];

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );
		}

		/**
		 * @param bool $set_back_form_values
		 */
		protected function prepare_items( $set_back_form_values = false ) {

			parent::prepare_items( $set_back_form_values );

			foreach ( $this->form_items as $item ) {
				if ( 'page_type' === $item->get_item_name() ) {
					$item_js =
						'function set_item_visibility(page_type) { ' .
						'  if (page_type===\'static\') { ' .
						'     jQuery(\'[name="page_content"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_table_name"]\').parent().parent().hide(); ' .
						'     jQuery(\'[name="page_mode"]\').parent().parent().hide(); ' .
						'     jQuery(\'[name="page_allow_insert"]\').parent().parent().hide(); ' .
						'     jQuery(\'[name="page_allow_delete"]\').parent().parent().hide(); ' .
						'  } else { ' .
						'     jQuery(\'[name="page_table_name"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_mode"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_allow_insert"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_allow_delete"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_content"]\').parent().parent().hide(); ' .
						'  } ' .
						'} ' .
						'jQuery(document).ready(function () { ' .
						'  jQuery(\'[name="page_type"]\').change(function() { ' .
						'    set_item_visibility(jQuery(this).val()); ' .
						'  }); ' .
						'  set_item_visibility(jQuery(\'[name="page_type"]\').val()); ' .
						'});';
					$item->set_item_js( $item_js );
				} elseif ( 'page_content' === $item->get_item_name() ) {
					$posts = get_posts(
						[
							'post_status' => '%',
							'orderby'     => 'ID',
						]
					);

					$lov         = [];
					$lov_options = [];
					// For some reason get_posts always sorts DESC on ID: reverse array.
					$posts_reverse = array_reverse( $posts );
					// Set first element to blank.
					array_push( $lov, '' );
					array_push( $lov_options, '0' );
					foreach ( $posts_reverse as $post ) {
						$post_element = $post->post_title . ' (ID=' . $post->ID . ')';
						array_push( $lov, $post_element );
						array_push( $lov_options, $post->ID );
					}

					$item->set_data_type( 'enum' );
					$item->set_enum( $lov );
					$item->set_enum_options( $lov_options );
					$item->set_item_hide_icon( true );
				} elseif ( 'page_table_name' === $item->get_item_name() ) {
					$wpda_dictionary_lists = WPDA_Dictionary_Lists::get_tables();
					$lov                   = [];
					array_push( $lov, '' );
					foreach ( $wpda_dictionary_lists as $dictionary_list ) {
						array_push( $lov, $dictionary_list['table_name'] );
					}
					$item->set_data_type( 'enum' );
					$item->set_enum( $lov );
				} elseif ( 'page_role' === $item->get_item_name() ) {
					global $wp_roles;
					$lov = [];
					foreach ( $wp_roles->roles as $role => $val ) {
						array_push( $lov, $role );
					}
					$item->set_data_type( 'set' );
					$item->set_enum( $lov );
					$item->set_item_hide_icon( true );
				}
			}

		}

	}

}