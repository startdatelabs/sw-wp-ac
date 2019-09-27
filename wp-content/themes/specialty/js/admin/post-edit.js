jQuery(document).ready(function($) {

	$( '.colorpckr' ).each( function() {
		$( this ).wpColorPicker();
	} );

	jQuery( '.alpha-color-picker' ).alphaColorPicker();

	/*
	 * Hide Hero options on builder template.
	 */
	// var template_box = $( '#page_template' );
	// var specialty_page_hero_metabox = $( '#specialty-page-layout' );
	// if ( template_box.length > 0 ) {
	// 	var specialty_page_hero_metabox_template = [ 'template-builder.php' ];
	//
	// 	specialty_page_hero_metabox.show();
	// 	if ( $.inArray( template_box.val(), specialty_page_hero_metabox_template ) > -1 ) {
	// 		specialty_page_hero_metabox.hide();
	// 	}
	//
	// 	template_box.change( function() {
	// 		if ( $.inArray( template_box.val(), specialty_page_hero_metabox_template ) > -1 ) {
	// 			specialty_page_hero_metabox.hide();
	// 			if ( typeof google === 'object' && typeof google.maps === 'object' ) {
	// 				if ( specialty_page_hero_metabox.find( '.gllpLatlonPicker' ).length > 0 ) {
	// 					google.maps.event.trigger( window, 'resize', {} );
	// 				}
	// 			}
	// 		} else {
	// 			specialty_page_hero_metabox.show();
	// 		}
	// 	} );
	// } else {
	// 	specialty_page_hero_metabox.show();
	// }

});
