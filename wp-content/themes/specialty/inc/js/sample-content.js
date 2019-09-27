(function($) {
	$(window).load( function() {
		$( '.specialty-sample-content-notice' ).parents( '.is-dismissible' ).on( 'click', 'button', function( e ) {
			console.log( $(this) );
			$.ajax( {
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'specialty_dismiss_sample_content',
					nonce: specialty_SampleContent.dismiss_nonce,
					dismissed: true
				},
				dataType: 'text',
				success: function( response ) {
					// console.log( response );
				}
			} );
		});
	});
})(jQuery);
