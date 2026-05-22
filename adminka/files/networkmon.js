/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	var ha = new Array();

	function scanPorts() {
		for( var i = 0 ; i < ha.length ; i++ ) {
			getData( "?portData=" + encodeURI( ha[ i ] ) , portData_processReqChange );
		}
	}

	function getData( url , func ) {
		url = url + "&random=" + ( new Date() ).getTime() ;
		var req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( "Microsoft.XMLHTTP" );
		}

		if ( req ) {
			req.onreadystatechange = function ( x ){ return function() { func( x ); } }( req );
			req.open( "GET" , url , true );
			req.send( null );
		}
	}

	function portData_processReqChange( req ) {
		if ( req.readyState == 4 ) {
			if ( req.status == 200 ) {
				//alert( req.responseText );
				var timeStart2 = ( new Date().getTime() ) / 1000 ;

				var hi = req.responseXML.documentElement ;

				var href = hi.getAttribute( "ref" );
				var hip = hi.getAttribute( "ip" );
				hip = hip.replace( /\./gi , "_" );

				var d = document.getElementById( "div_" + hip );
				var img = document.getElementById( "wi_" + hip );
				d.removeChild( img );

				if ( hi.childNodes.length > 0 ) {
					for( var i = 0 ; i < hi.childNodes.length ; i++ ) {
						var pid = hi.childNodes[ i ].getAttribute( "id" );
						var sn = hi.childNodes[ i ].getAttribute( "svc" );

						var a = document.createElement( "a" );
						a.href = sn + "://" + href + "/" ;
						a.className = "lnk-seedomain" ;
						a.innerText = sn ;
						a.target = "_blank" ;
						d.appendChild( a );
						var p = document.createTextNode( " " );
						d.appendChild( p );
					}
				}

			}
		}
	}

