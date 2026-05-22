/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	function tci( num ) {
		var ttl = document.getElementById( "item" + num );
		var tti = document.getElementById( "ii" + num );
		if ( ttl.style.display == "none" ) {
			ttl.style.display = "" ;
			tti.src = "themes/" + UserThemeLoc + "/col.bmp" ;
		} else {
			ttl.style.display = "none" ;
			tti.src = "themes/" + UserThemeLoc + "/exp.bmp" ;
		}
	}

	function getCookie( n ) {
		ca = document.cookie.split( "; " );
		for( var i = 0 ; i < ca.length ; i++ ) {
			var ep = ca[ i ].indexOf( "=" );
			if ( ca[ i ].substr( 0 , ep ) == n ) {
				return unescape( ca[ i ].substr( ep + 1 ) );
			}
		}

		return null ;
	}

	function printLetterLabel( id ) {
		var fb = 0 ;
		if ( id == "from-base" ) {
			id = letter_dlg__selected_mat_id ;
			fb = 1 ;
		}

		var lData = sendXML( "<get-letter-data id=\"" + id + "\" fb=\"" + fb + "\" />" , false );

		var lAddresseeNode = null ;
		var lDestinationNode = null ;

		for( var j = 0 ; j < lData.childNodes.length ; j++ ) {
			switch ( lData.childNodes[ j ].nodeName ) {
				case "addressee" :
					lAddresseeNode = lData.childNodes[ j ];
					break ;
				case "destination" :
					lDestinationNode = lData.childNodes[ j ];
					break ;
			}
		}

		DoPrintLetterLabel(
			getXMLNodeValue( lAddresseeNode ) ,
			getXMLNodeValue( lDestinationNode ) ,
			lData.getAttribute( "index" )
		);
	}

	function sendXML( data , async , addr , aParam ) {
		if ( typeof addr === "undefined" ) {
			addr = "bill.letter.php" ;
		}

		if ( typeof aParam === "undefined" ) {
			aParam = "" ;
		} else {
			aParam+= "&" ;
		}

		var sd = aParam + "mode=ajax&data=" + encodeURIComponent( "<?xml version=\"1.0\" encoding=\"utf-8\" ?>" + data );
		var req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( "Microsoft.XMLHTTP" );
		}

		if ( req ) {
			req.open( "POST" , addr , async );
			req.setRequestHeader( "Accept-Charset" , "windows-1251" );
			req.setRequestHeader( "Accept-Language" , "ru,en" );
			req.setRequestHeader( "Content-length" , sd.length );
			req.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );
			req.setRequestHeader( "Content-Encoding" , "utf-8" );
			req.send( sd );

			if ( !async ) {
				//alert( req.responseText );
				return req.responseXML.documentElement ;
			} else {
				return ;
			}
		}
	}

	function getXMLNodeValue( n ) {
		return ( n.text || n.textContent );
	}
	
	function doCombine() {
		var bl = document.getElementsByName( "bill[]" );
		var sbl = [];
		for ( var i = 0 ; i < bl.length ; i++  ) {
			if ( bl[ i ].checked ) {
				sbl.push( bl[ i ].value );
			}
		}
		
		if ( sbl.length < 2 ) {
			alert( "Необходимо выбрать не менее 2 счетов" );
			return ;
		}
		
		window.location = "bill.php?invoice&combine=" + sbl.join( "," );
	}

