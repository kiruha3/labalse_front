/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	function tbps( n ) {
		var cb = document.getElementById( "bp_" + n + "_i" );
		var bp = document.getElementById( "bp_" + n );
		if ( cb.checked ) {
			bp.style.display = "" ;
		} else {
			bp.style.display = "none" ;
		}
	}

	function tbpde( rv ) {
		var cb = document.getElementById( "bill-date-ex-cb" );
		var bd = document.getElementById( "bill-date" );
		var bde = document.getElementById( "bill-date-ex" );
		if ( cb.checked ) {
			bd.innerHTML = bde.value ;
		} else {
			bd.innerHTML = rv ;
		}
	}
	
	function tbpte() {
		var cb = document.getElementById( "bill-tgt-ex-cb" );
		var tgt = document.getElementById( "bill-tgt" );
		var tgte = document.getElementById( "bill-tgt-ex" );
		if ( cb.checked ) {
			tgt.innerHTML = " " + tgte.value ;
		} else {
			tgt.innerHTML = "" ;
		}
	}
	
	function tbppwVAT() {
		var cb = document.getElementById( "bill-price-wVAT-cb" );
		var it = document.getElementById( "items-table" );
		if ( cb.checked ) {
			it.className = "it-wVAT" ;
		} else {
			it.className = "" ;
		}
	}
	
	function ts( i ) {
		var sr = document.getElementById( "row" + i );
		if ( sr.style.backgroundColor != "" ) {
			sr.style.backgroundColor = "" ;
		} else {
			sr.style.backgroundColor = "#fcc" ;
		}
	}
	
	function doSearchBill( id ) {
		var doc = sendXML( "<search-bills id=\"" + id + "\" />" );
		var lnk = document.getElementById( "search-bill-lnk-" + id );
		var p = lnk.parentNode ;
		p.removeChild( lnk );
		var cn = doc.childNodes ;
		for( var i = 0 ; i < cn.length ; i++ ) {
			var tmp = document.createElement( "a" );
			tmp.className = "" ;
			tmp.appendChild( document.createTextNode( getXMLNodeValue( cn[ i ] ) ) );
			tmp.href = "bill.print.php?id=" + cn[ i ].getAttribute( "id" );
			tmp.target = "_blank" ;
			if ( i > 0 ) {
				p.appendChild( document.createElement( "br" ) );
			}
			p.appendChild( tmp );
		}
	}
	
	function printLetterLabel( id ) {
		var fb = 0 ;
		if ( id == "from-base" ) {
			id = letter_dlg__selected_mat_id ;
			fb = 1 ;
		}

		var lData = sendXML( "<get-letter-data id=\"" + id + "\" fb=\"" + fb + "\" />" , false , "bill.letter.php" );

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

