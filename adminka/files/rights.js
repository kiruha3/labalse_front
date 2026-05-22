
	function rightsToGroup( t ) {
		var rtga = document.getElementById( "rights_to_group_all" );
		var rtg = document.getElementsByName( "rights_to_group[]" );
		switch ( t ) {
			case 0 :
				for( var i = 0 ; i < rtg.length ; i++ ) {
					rtg[ i ].checked = rtga.checked ;
				}
				break ;

			case 1 :
				var v = true ;
				for( var i = 0 ; i < rtg.length ; i++ ) {
					v&= rtg[ i ].checked ;
				}
				rtga.checked = v ;
		}

		return false ;
	}
