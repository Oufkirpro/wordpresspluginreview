( function ( $ ) {
	'use strict';

	$( function () {
		$( '.br-exp-wrap' ).on( 'click', '#cb-select-all-1, #cb-select-all-2', function () {
			var checked = $( this ).prop( 'checked' );
			$( 'input[name="ids[]"]' ).prop( 'checked', checked );
		} );
	} );
} )( jQuery );
