<?php
foreach ( $post_types as $_type_id => $_type ) {

	if ( $type_label_counts[ $_type->label ] < 2 ) {
		$_label = $_type->label;
	} else {
		$_label = sprintf( '%s (%s)', $_type->label, $_type_id );
	}
	?>
	<label style="margin-right: 1em;"><input type="checkbox" disabled="disabled"><?php esc_html_e( $_label ); ?></label><?php
}