
	function propChange() {
		var f1 = document.getElementById( "1001" );
		var f2 = document.getElementById( "1002" );
		var f3 = document.getElementById( "1003" );

		if ( f1.value != "" ) {
			f2.value = parseInt( f1.value , 10 ) + 1 ;
			f3.value = parseInt( f1.value , 10 ) + 2 ;
		} else {

		}
	}


	function mnc() {
		var f1 = document.getElementById( "i_mat_number_1" );
		var f2 = document.getElementById( "i_mat_number_2" );
		var f3 = document.getElementById( "i_mat_number_3" );

		if ( f1.value != "" ) {
			f2.value = parseInt( f1.value , 10 ) + 1 ;
			f3.value = parseInt( f1.value , 10 ) + 2 ;
		} else {

		}
	}
