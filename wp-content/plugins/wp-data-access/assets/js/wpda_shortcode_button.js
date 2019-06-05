/**
 * Javascript code needed to build TinyMCE button to help user building shortcodes for WPDA tables.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */

// Default label values.
var wpda_tab     = 'Table';
var wpda_cols    = 'Columns';
var wpda_selcols = 'SELECT COLUMNS';
var wpda_clksel  = 'Click to select. Columns are displayed in the order as listed.';
var wpda_curcel  = 'Current selection';
var wpda_shwcols = 'SHOW ALL COLUMNS';
var wpda_resp    = 'Responsive mode';
var wpda_yes     = 'Yes';
var wpda_no      = 'No';
var wpda_rescols = 'Number of columns in responsive header';
var wpda_icon    = 'Show expand icon';
var wpda_details = 'Show details';
var wpda_col     = 'Collapsed';
var wpda_mod     = 'In modal window';
var wpda_exp     = 'Expanded';
var wpda_war     = 'WARNING';
var wpda_wartxt  = 'You currently have no access to any database tables and views';
var wpda_act     = 'ACTION';
var wpda_acttxt  = 'Grant access to desired tables and views in front-end settings';

function translate(txt) {
	if (jQuery.url( wpda_script_url ).data.param.query[txt]) {
		eval( 'wpda_' + txt + '= jQuery.url(wpda_script_url).data.param.query.' + txt );
	}
}

wpda_script_url = document.currentScript.src; // Save script url.
jQuery( document.currentScript ).ready(
	function() {
		// Check for translations.
		translate( 'tab' );
		translate( 'cols' );
		translate( 'selcols' );
		translate( 'clksel' );
		translate( 'curcel' );
		translate( 'shwcols' );
		translate( 'resp' );
		translate( 'yes' );
		translate( 'no' );
		translate( 'rescols' );
		translate( 'icon' );
		translate( 'details' );
		translate( 'col' );
		translate( 'mod' );
		translate( 'exp' );
		translate( 'war' );
		translate( 'wartxt' );
		translate( 'act' );
		translate( 'acttxt' );
	}
);

function select_available(e) {

	var option = jQuery( "#columns_available option:selected" );
	var add_to = jQuery( "#columns_selected" );

	option.remove();
	new_option = add_to.append( option );

	if (jQuery( "#columns_selected option[value='']" ).length > 0) {
		// Remove ALL from selected list.
		jQuery( "#columns_selected option[value='']" ).remove();
	}

	jQuery( 'select#columns_selected option' ).removeAttr( "selected" );

}

function select_selected(e) {

	var option = jQuery( "#columns_selected option:selected" );
	if (option[0].value === '') {
		// Cannot remove ALL.
		return;
	}

	var add_to = jQuery( "#columns_available" );

	option.remove();
	add_to.append( option );

	if (jQuery( 'select#columns_selected option' ).length === 0) {
		jQuery( "#columns_selected" ).append( jQuery( '<option></option>' ).attr( 'value', '' ).text( wpda_shwcols ) );
	}

	jQuery( 'select#columns_available option' ).removeAttr( "selected" );
}

(function () {

	tinymce.create(
		'tinymce.plugins.wpdataaccess', {

			init: function (editor, url) {

				editor.addButton(
					'wpdataaccess_button', {
						title: 'WP Data Access',
						icon: 'icon dashicons-editor-table',
						text: ' WPDA',
						cmd: 'wpdataaccess_cmd'
					}
				);

				editor.addCommand(
					'wpdataaccess_cmd', function () {
						if (editor.settings.wpda_table_list === '') {
							var msg = editor.windowManager.open(
								{
									title: "WP Data Access",
									width: 600,
									height: 300,
									body: [
									{
										type: 'label',
										label: wpda_war,
										text: wpda_wartxt
									},
									{
										type: 'label',
										label: wpda_act,
										text: wpda_acttxt
									}
									]
								}
							);
						} else {
							var win = editor.windowManager.open(
								{
									title: "WP Data Access",
									width: 600,
									height: 300,
									body: [
									{
										type: 'listbox',
										name: 'table',
										label: wpda_tab,
										values: editor.settings.wpda_table_list,
										onselect: function () {

											column_selection = win.find( "#column_selection" );
											column_selection.text( wpda_shwcols );

										}
									},
									{
										type: 'button',
										name: 'columns_button',
										label: wpda_cols,
										text: wpda_selcols,
										onclick: function () {

											// Get selected value from table list for post.
											table         = win.find( "#table" );
											var wpda_data = {
												'action': 'wpda_tinymce_listbox_columns',
												'table_name': table[0].state.data.value
											};

											// Get column list for selected table and write to listbox.
											jQuery.post(
												ajaxurl, wpda_data, function (response) {

													var columns_available =
													jQuery(
														'<select id="columns_available" name="columns_available[]" multiple size="8" style="width:200px" onclick="select_available()">' +
														'</select>'
													);

													var columns_selected =
													jQuery(
														'<select id="columns_selected" name="columns_selected[]" multiple size="8" style="width:200px" onclick="select_selected()">' +
														'<option value="">' + wpda_shwcols + '</option>' +
														'</select>'
													);

													for (var col in response) {
														columns_available.append( jQuery( '<option></option>' ).attr( 'value', response[col].text ).text( response[col].text ) );
													}

													var dialog_table = jQuery( '<table style="width:410px"></table>' );

													var dialog_table_row_available = dialog_table.append( jQuery( '<tr></tr>' ).append( jQuery( '<td width="50%"></td>' ) ) );
													dialog_table_row_available.append( columns_available );

													var dialog_table_row_selected = dialog_table.append( jQuery( '<tr></tr>' ).append( jQuery( '<td width="50%"></td>' ) ) );
													dialog_table_row_selected.append( columns_selected );

													var dialog_text = jQuery( '<div style="width:410px">' + wpda_clksel + '</div>' );
													var dialog      = jQuery( '<div></div>' );

													dialog.append( dialog_text );
													dialog.append( dialog_table );

													dialog.dialog(
														{
															dialogClass: 'wp-dialog no-close',
															title: wpda_selcols,
															modal: true,
															autoOpen: true,
															closeOnEscape: false,
															resizable: false,
															width: 'auto',
															buttons: {
																"Close": function () {

																	var selected_columns = '';
																	jQuery( "#columns_selected option" ).each(
																		function () {
																			selected_columns += jQuery( this ).val() + ',';
																		}
																	);
																	if (selected_columns !== '') {
																		selected_columns = selected_columns.slice( 0, -1 );
																	}
																	column_selection = win.find( '#column_selection' );
																	if (selected_columns === '') {
																		column_selection.text( wpda_shwcols );
																	} else {
																		column_selection.text( selected_columns );
																	}
																	jQuery( this ).dialog( 'destroy' ).remove();

																},
																"Cancel": function () {

																	jQuery( this ).dialog( 'destroy' ).remove();

																}
															}
														}
													);

													jQuery( ".ui-button-icon-only" ).hide();

												}
											);
										}
									},
									{
										type: 'label',
										name: 'column_selection',
										label: wpda_curcel,
										text: wpda_shwcols
									},
									{
										type: 'listbox',
										name: 'responsive',
										label: wpda_resp,
										values: [
										{text: wpda_yes, value: 'yes'},
										{text: wpda_no, value: 'no'}
										],
										onselect: function (e) {

											responsive_cols = win.find( "#responsive_cols" );
											responsive_icon = win.find( "#responsive_icon" );
											responsive_type = win.find( "#responsive_type" );
											if (e.target.state.data.value === 'no') {
												// Disable responsive_cols.
												responsive_cols.disabled( true );
												responsive_icon.disabled( true );
												responsive_type.disabled( true );
											} else {
												// Enable responsive_cols.
												responsive_cols.disabled( false );
												responsive_icon.disabled( false );
												responsive_type.disabled( false );
											}

										}
									},
									{
										type: 'listbox',
										name: 'responsive_cols',
										label: wpda_rescols,
										values: [
										{text: '1', value: '1'},
										{text: '2', value: '2'},
										{text: '3', value: '3'},
										{text: '4', value: '4'},
										{text: '5', value: '5'},
										{text: '6', value: '6'}
										]
									},
									{
										type: 'listbox',
										name: 'responsive_icon',
										label: wpda_icon,
										values: [
										{text: wpda_yes, value: 'yes'},
										{text: wpda_no, value: 'no'}
										]
									},
									{
										type: 'listbox',
										name: 'responsive_type',
										label: wpda_details,
										values: [
										{text: wpda_col, value: 'collapsed'},
										{text: wpda_mod, value: 'modal'},
										{text: wpda_exp, value: 'expanded'}
										]
									}
									],
									onsubmit: function (e) {

										column_selection = win.find( '#column_selection' )[0].state.data.text;
										if (column_selection === wpda_shwcols) {
											column_selection = '*';
										}
										columns    = ' columns="' + column_selection + '"';
										responsive = '';
										if (e.data.responsive === 'yes') {
											responsive = ' responsive="yes"' +
											' responsive_cols="' + e.data.responsive_cols + '"' +
											' responsive_type="' + e.data.responsive_type + '"' +
											' responsive_icon="' + e.data.responsive_icon;
										}
										editor.insertContent(
											'[wpdataaccess' +
											' table="' + e.data.table + '"' +
											columns +
											responsive +
											']'
										);

									}
								}
							);
						}
					}
				);

			}

		}
	);

	tinymce.PluginManager.add( 'wpdataaccess_button', tinymce.plugins.wpdataaccess );

})();
