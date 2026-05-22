	function showRep( y ) {
		var d = document.getElementById( "d" );
		if ( d.value == "-1" ) {
			alert( "Не выбран отдел !" );
			return ;
		}
		if ( d.value == "allByDep" ) {
			for( var i = 0 ; i < d.options.length ; i++ ) {
				var o = d.options[ i ];
				if ( !o.disabled && o.value != "-1" && o.value != "all" && o.value != "allByDep" ) {
					window.open( window.location.href + "?" + encodeURI( "ds=" + y + "/01/01&de=" + y + "/12/31&d=" + o.value ) );
				}
			}
		} else {
			window.open( window.location.href + "?" + encodeURI( "ds=" + y + "/01/01&de=" + y + "/12/31&d=" + d.value ) );
		}
		//window.location.href = "?" + encodeURI( "ds=" + y + "/" + m + "/1&de=" + y + "/" + m + "/" + dc + "&d=" + d.value );

		//window.open(optionalArg1, optionalArg2, optionalArg3, optionalArg4);
	}
