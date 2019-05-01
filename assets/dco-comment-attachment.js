( function( $ ) {
	$( document ).ready( function() {

		// This is a dirty method, but there is no hook in WordPress to add attributes to the commenting form.
		$( '#respond' ).children( 'form' ).attr( 'enctype', 'multipart/form-data' );
	});
}( jQuery ) );
