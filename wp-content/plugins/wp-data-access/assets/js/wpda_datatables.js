/**
 * Javascript code needed to build tables in WordPress with jQuery DataTables.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */

var wpda_details = 'Row details'; // Default label value.

wpda_script_url = document.currentScript.src; // Save script url.
jQuery( document.currentScript ).ready(
	function() {
		// Check for translations.
		if (jQuery.url( wpda_script_url ).data.param.query.details) {
			wpda_details = jQuery.url( wpda_script_url ).data.param.query.details;
		}
	}
);

function wpda_datatables_ajax_call(table_name, columns, responsive, responsive_type, responsive_icon) {

	/*
	* display possible values:
	* childrow = user toggled
	* childrowimmediate = show
	* modal = show details in modal window
	*/

	/*
	* type possible values:
	* column = no control element
	* inline = show control element
	*/

	var responsive_control_type = "inline";
	if (responsive_icon !== "yes") {
		responsive_control_type = "column";
	}

	var childrow =
		{
			details: {
				display: jQuery.fn.dataTable.Responsive.display.childRow,
				type: responsive_control_type
			}
	};

	var childrowimmediate =
		{
			details: {
				display: jQuery.fn.dataTable.Responsive.display.childRowImmediate,
				type: responsive_control_type
			}
	};

	var modal =
		{
			details: {
				display: jQuery.fn.dataTable.Responsive.display.modal(
					{
						header: function (row) {
							return wpda_details;
						}
					}
				),
			renderer: function (api, rowIdx, columns) {
				var data = jQuery.map(
					columns, function (col, i) {
							return '<tr>' +
							'<td>' + col.title + '</td>' +
							'<td><strong>' + col.data + '</strong></td>' +
							'</tr>';
					}
				).join( '' );
				var datatable = '<table width="100%" class="display dataTable">' + data + '</table>';
				var footer    = '<tr><td style="padding-top:10px; text-align: center"><div>' +
					'<input type="button" value="Close" class="button dtr-modal-close" onclick="jQuery(\'.dtr-modal\').remove()"/>' +
					'</div></td></tr>';
				var table     = '<tr><td>' + datatable + '</td></tr>' + footer;

				return jQuery( '<table width="100%"/>' ).append( table );
			},
				type: responsive_control_type
			}

	};

	var responsive_value = false;
	if (responsive === 'yes') {
		switch (responsive_type) {
			case "modal":
				responsive_value = modal;
				break;
			case "expanded":
				responsive_value = childrowimmediate;
				break;
			default:
				/* collaped */
				responsive_value = childrow;
		}
	}

	jQuery( "#" + table_name ).DataTable(
		{
			responsive: responsive_value,
			processing: true,
			serverSide: true,
			ajax: {
				url: wpda_ajax.wpda_ajaxurl,
				data: {
					action: "wpda_datatables",
					table_name: table_name,
					columns: columns
				}
			}
		}
	);

}
