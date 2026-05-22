
	function getText( o ) {
		if ( "innerText" in o ) {
			return o.innerText ;
		} else {
			return o.textContent ;
		}
	}

	var hel = [];

	function thl( r , c , t , cc ) {
		switch( t ) {
			case 0 :
				for( var i = 0 ; i < hel.length ; i++ ) {
					hel[ i ].style.backgroundColor = "" ;
					hel[ i ].style.color = "" ;
				}
				break ;

			case 1 :
				var he = document.getElementById( "d_r" + r + "c" + c );
				he.style.backgroundColor = "#383" ;
				he.style.color = "#fff" ;
				hel.push( he );
				for( var i = 1 ; i <= cc ; i++ ) {
					var he = document.getElementById( "d_r" + r + "c" + ( c - ( cc + 1 ) * 5 + i * 5 ) );
					var tmp = getText( he );
					if ( tmp != "0" ) {
						he.style.backgroundColor = "#6c6" ;
					} else {
						he.style.color = "#6c6" ;
						he.style.backgroundColor = "#cfc" ;
					}
					hel.push( he );
				}
				break ;

			case 2 :
				var he = document.getElementById( "d_r" + r + "c" + c );
				he.style.backgroundColor = "#383" ;
				he.style.color = "#fff" ;
				hel.push( he );
				for( var i = 1 ; i <= 4 ; i++ ) {
					var he = document.getElementById( "d_r" + r + "c" + ( c + i ) );
					var tmp = getText( he );
					if ( tmp != "0" ) {
						he.style.backgroundColor = "#6c6" ;
					} else {
						he.style.color = "#6c6" ;
						he.style.backgroundColor = "#cfc" ;
					}
					hel.push( he );
				}
				break ;

			case 3 :
				var he = document.getElementById( "d_r" + r + "c" + c );
				he.style.backgroundColor = "#383" ;
				he.style.color = "#fff" ;
				hel.push( he );

				var he = document.getElementById( "d_r" + r + "c" + ( c - 1 ) );
				var tmp = getText( he );
				if ( tmp != "0" ) {
					he.style.backgroundColor = "#6c6" ;
				} else {
					he.style.color = "#6c6" ;
					he.style.backgroundColor = "#cfc" ;
				}
				hel.push( he );

				var he = document.getElementById( "d_r" + r + "c" + ( c - 6 ) );
				var tmp = getText( he );
				if ( tmp != "0" ) {
					he.style.backgroundColor = "#6c6" ;
				} else {
					he.style.color = "#6c6" ;
					he.style.backgroundColor = "#cfc" ;
				}
				hel.push( he );
				break ;

			case 4 :
				var he = document.getElementById( "d_r" + r + "cs" );
				he.style.backgroundColor = "#44c" ;
				he.style.color = "#fff" ;
				hel.push( he );
				for( var i = 0 ; i <= 5 ; i++ ) {
					var he = document.getElementById( "d_r" + r + "c" + i );
					var tmp = getText( he );
					if ( tmp != "0" ) {
						he.style.backgroundColor = "#88f" ;
					} else {
						he.style.color = "#88f" ;
						he.style.backgroundColor = "#ccf" ;
					}
					hel.push( he );
				}
				break ;
		}
	}

	function showRep( y , m , dc ) {
		var d = document.getElementById( "d" );
		if ( d.value == "-1" ) {
			alert( "Не выбран отдел !" );
			return ;
		}
		if ( d.value == "allByDep" ) {
			for( var i = 0 ; i < d.options.length ; i++ ) {
				var o = d.options[ i ];
				if ( !o.disabled && o.value != "-1" && o.value != "all" && o.value != "allByDep" ) {
					window.open( window.location.href + "?" + encodeURI( "ds=" + y + "/" + m + "/1&de=" + y + "/" + m + "/" + dc + "&d=" + o.value ) );
				}
			}
		} else {
			window.open( window.location.href + "?" + encodeURI( "ds=" + y + "/" + m + "/1&de=" + y + "/" + m + "/" + dc + "&d=" + d.value ) );
		}
		//window.location.href = "?" + encodeURI( "ds=" + y + "/" + m + "/1&de=" + y + "/" + m + "/" + dc + "&d=" + d.value );

		//window.open(optionalArg1, optionalArg2, optionalArg3, optionalArg4);
	}
