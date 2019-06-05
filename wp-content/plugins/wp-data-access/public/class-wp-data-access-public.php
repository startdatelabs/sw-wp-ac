<?php
/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\public
 */

use WPDataAccess\Data_Tables\WPDA_Data_Tables;
use WPDataAccess\WPDA;

/**
 * Class WP_Data_Access_Public
 *
 * Defines public specific functionality for plugin WP Data Access.
 *
 * @package plugin\public
 * @author  Peter Schulz
 * @since   1.0.0
 */
class WP_Data_Access_Public {

	/**
	 * Add stylesheets to front-end
	 *
	 * The following stylesheets are added:
	 * + Bootstrap stylesheet (version is set in class WPDA)
	 * + jQuery DataTables stylesheet (version is set in class WPDA)
	 * + jQuery DataTables responsive stylesheet (version is set in class WPDA)
	 *
	 * Stylesheets are used to style the front-end tables. Whether stylesheets should be loaded or not can be set in
	 * the front-end settings (menu: Manage Plugin). Sites that already have some of these stylesheets loaded, can turn
	 * off loading in the front-end settings to prevent double loading.
	 *
	 * @since   1.0.0
	 *
	 * @see WPDA
	 */
	public function enqueue_styles() {

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_BOOTSTRAP ) === 'on' ) {

			wp_register_style(
				'prefix_bootstrap',
				'//maxcdn.bootstrapcdn.com/bootstrap/' .
				WPDA::get_option( WPDA::OPTION_WPDA_BOOTSTRAP_VERSION ) .
				'/css/bootstrap.min.css',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_style( 'prefix_bootstrap' );

		}

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES ) === 'on' ) {

			wp_register_style(
				'jquery_datatables', '//cdn.datatables.net/' .
				WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_VERSION ) .
				'/css/jquery.dataTables.min.css',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_style( 'jquery_datatables' );

		}

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES_RESPONSE ) === 'on' ) {

			wp_register_style(
				'jquery_datatables_responsive',
				'//cdn.datatables.net/responsive/' .
				WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_RESPONSIVE_VERSION ) .
				'/css/responsive.dataTables.min.css',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_style( 'jquery_datatables_responsive' );

		}

	}

	/**
	 * Add scripts to back-end
	 *
	 * The following script files are added:
	 * + jQuery (just enqueue, registered by default)
	 * + Bootstrap (version is set in class WPDA)
	 * + jQuery DataTables (version is set in class WPDA)
	 * + jQuery DataTables responsive (version is set in class WPDA)
	 * + WP Data Access DataTables server implementation (ajax)
	 *
	 * Scripts are used to build front-end tables and support searching and pagination. Whether the scripts for
	 * Bootstrap, jQuery DataTables and/or jQuery DataTables responsice should be loaded or not can be set in the
	 * front-end settings (menu: Manage Plugin). Sites that already have some of these script files loaded, can
	 * turn off loading in the front-end settings to prevent double loading.
	 *
	 * @since   1.0.0
	 *
	 * @see WPDA
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'jquery' ); // Just enqueue: jquery is already registered.

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_BOOTSTRAP ) === 'on' ) {

			wp_register_script(
				'prefix_bootstrap',
				'//maxcdn.bootstrapcdn.com/bootstrap/' .
				WPDA::get_option( WPDA::OPTION_WPDA_BOOTSTRAP_VERSION ) .
				'/js/bootstrap.min.js'
			);
			wp_enqueue_script( 'prefix_bootstrap' );

		}

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES ) === 'on' ) {

			wp_register_script(
				'jquery_datatables',
				'//cdn.datatables.net/' .
				WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_VERSION ) .
				'/js/jquery.dataTables.min.js',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_script( 'jquery_datatables' );

		}

		if ( WPDA::get_option( WPDA::OPTION_FE_LOAD_DATATABLES_RESPONSE ) === 'on' ) {

			wp_register_script(
				'jquery_datatables_responsive',
				'//cdn.datatables.net/responsive/' .
				WPDA::get_option( WPDA::OPTION_WPDA_DATATABLES_RESPONSIVE_VERSION ) .
				'/js/dataTables.responsive.min.js',
				[],
				WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
			);
			wp_enqueue_script( 'jquery_datatables_responsive' );

		}

		// Register purl external library.
		wp_register_script( 'purl', plugins_url( '../assets/js/purl.js', __FILE__ ), [ 'jquery' ] );
		wp_enqueue_script( 'purl' );

		// Ajax call to WPDA datables implementation.
		$details      = __( 'Row details', 'wp-data-access' ); // Set title of modal window here to support i18n.
		$query_string = str_replace( ' ', '+', "?details=$details" );
		wp_register_script(
			'wpda_datatables',
			plugins_url( '../assets/js/wpda_datatables.js' . $query_string, __FILE__ ), [ 'jquery' ],
			[],
			WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
		);
		wp_localize_script( 'wpda_datatables', 'wpda_ajax', [ 'wpda_ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
		wp_enqueue_script( 'wpda_datatables' );

	}

	/**
	 * Register shortcode 'wpdataaccess'
	 *
	 * @since   1.0.0
	 */
	public function register_shortcodes() {

		add_shortcode( 'wpdataaccess', [ $this, 'wpdataaccess' ] );

	}

	/**
	 * Initialize shortcode button for visual editor
	 *
	 * Consists of two steps:
	 * + Register tinymce button {@see WP_Data_Access_Public::wpdataaccess_register_tinymce_plugin()}
	 * + Add tinymce button {@see WP_Data_Access_Public::wpdataaccess_add_tinymce_button()}
	 *
	 * @since   1.0.0
	 *
	 * @see WP_Data_Access_Public::wpdataaccess_register_tinymce_plugin()
	 * @see WP_Data_Access_Public::wpdataaccess_add_tinymce_button()
	 */
	public function wpdataaccess_shortcode_button_init() {

		if ( get_user_option( 'rich_editing' ) === 'true' &&
			current_user_can( 'edit_posts' ) &&
			current_user_can( 'edit_pages' )

		) {

			add_filter( 'mce_external_plugins', [ $this, 'wpdataaccess_register_tinymce_plugin' ] ); // Register button.
			add_filter( 'mce_buttons', [ $this, 'wpdataaccess_add_tinymce_button' ] ); // Add button.

		}

	}

	/**
	 * Register tinymce button
	 *
	 * Register tinymce plugin button to support the addition of the plugin shortcode through a wizard that can be
	 * started from this button. Tekst used in the wizard is translated upfront and added to the call through a
	 * querystring.
	 *
	 * @since   1.0.0
	 *
	 * @param array $plugin_array Array holding registrations.
	 * @return array Initial plugin array (argument $plugin_array) + wpda button element.
	 */
	public function wpdataaccess_register_tinymce_plugin( $plugin_array ) {

		$tab     = __( 'Table', 'wp-data-access' );
		$cols    = __( 'Columns', 'wp-data-access' );
		$selcols = __( 'SELECT COLUMNS', 'wp-data-access' );
		$clksel  = __( 'Click to select. Columns are displayed in the order as listed.', 'wp-data-access' );
		$curcel  = __( 'Current selection', 'wp-data-access' );
		$shwcols = __( 'SHOW ALL COLUMNS', 'wp-data-access' );
		$resp    = __( 'Responsive mode', 'wp-data-access' );
		$yes     = __( 'Yes', 'wp-data-access' );
		$no      = __( 'No', 'wp-data-access' );
		$rescols = __( 'Number of columns in responsive header', 'wp-data-access' );
		$icon    = __( 'Show expand icon', 'wp-data-access' );
		$details = __( 'Show details', 'wp-data-access' );
		$col     = __( 'Collapsed', 'wp-data-access' );
		$mod     = __( 'In modal window', 'wp-data-access' );
		$exp     = __( 'Expanded', 'wp-data-access' );
		$war     = __( 'WARNING', 'wp-data-access' );
		$wartxt  = __( 'You currently have no access to any database tables and views', 'wp-data-access' );
		$act     = __( 'ACTION', 'wp-data-access' );
		$acttxt  = __( 'Grant access to desired tables and views in front-end settings', 'wp-data-access' );

		$query_string =
			str_replace(
				' ',
				'+',
				"?tab=$tab" .
				"&cols=$cols" .
				"&selcols=$selcols" .
				"&clksel=$clksel" .
				"&curcel=$curcel" .
				"&shwcols=$shwcols" .
				"&resp=$resp" .
				"&yes=$yes" .
				"&no=$no" .
				"&rescols=$rescols" .
				"&icon=$icon" .
				"&details=$details" .
				"&col=$col" .
				"&mod=$mod" .
				"&exp=$exp" .
				"&war=$war" .
				"&wartxt=$wartxt" .
				"&act=$act" .
				"&acttxt=$acttxt"
			);

		$plugin_array['wpdataaccess_button'] =
			plugins_url( '../assets/js/wpda_shortcode_button.js' . $query_string, __FILE__ );

		return $plugin_array;

	}

	/**
	 * Add tinymce button
	 *
	 * @since   1.0.0
	 *
	 * @param array $buttons Array holding available tinymce buttons.
	 * @return array Initial array (argument $buttons) + wpda button element.
	 */
	public function wpdataaccess_add_tinymce_button( $buttons ) {

		array_push( $buttons, '|', 'wpdataaccess_button' );
		return $buttons;

	}

	/**
	 * Implementation of shortcode 'wpdataaccess'
	 *
	 * Checks the values entered on validity (as far as possible) and builds the table based on the given table name,
	 * column names and other arguments. Tables is build with class {@see WPDA_Data_Tables}.
	 *
	 * @since   1.0.0
	 *
	 * @see WPDA_Data_Tables
	 *
	 * @param array $atts Arguments applied with the shortcode.
	 */
	public function wpdataaccess( $atts ) {

		$atts    = array_change_key_case( (array) $atts, CASE_LOWER );
		$wp_atts = shortcode_atts(
			[
				'table'           => '',
				'columns'         => '*',
				'responsive'      => 'no',
				'responsive_cols' => '0',           // > 1 or no effect.
				'responsive_type' => 'collapsed',   // modal,expanded,collapsed.
				'responsive_icon' => 'yes',         // yes,no.
			], $atts
		);

		if ( '' === $wp_atts['table'] ) {

			echo '<p>ERROR: Missing argument table in shortcode!</p>';

		} else {

			$wpda_data_tables = new WPDA_Data_Tables();
			$wpda_data_tables->show(
				$wp_atts['table'],
				str_replace( ' ', '', $wp_atts['columns'] ),
				$wp_atts['responsive'],
				$wp_atts['responsive_cols'],
				$wp_atts['responsive_type'],
				$wp_atts['responsive_icon']
			);

		}

	}

}
