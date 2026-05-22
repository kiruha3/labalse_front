
	$.windowOnLoad.push( function() {
		const fld = document.body.querySelector( 'textarea.i_search_string[name="i_search_string"]' );
		fld.focus();
		const form = document.body.querySelector( '#main-form' );
		fld.addEventListener( 'keydown' , function( f ){
			return function( e ){
				if ( e.keyCode == 13 && e.ctrlKey ) {
					f.submit();
				}
			};
		}( form ) );
	});