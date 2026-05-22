	function getText( c ) {
		if ( typeof( c.textContent ) == "undefined" ) {
			return c.text ;
		} else {
			return c.textContent ;
		}
	}

	var newPayersID = 0 ;
	
	window.onload = function() {
		if ( !( typeof closePage === "undefined" ) ) {
			window.close();
			return ;
		}
		
		upd();
		
		if ( agencyID != null ) {
			var al = document.getElementById( "woe_agency_sel" );
			al.value = agencyID ;
			var op = al.options ;
			var ol = op.length ;
			for( var i = 0 ; i < ol ; ++i ) {
				if ( op[ i ].value == agencyID ) {
					al.selectedIndex = i ;
					break ;
				}
			}
			
			agency_select();
			
			if ( agentID != null ) {
				var al = document.getElementById( "woe_agent_sel" );
				al.value = agentID ;
				var op = al.options ;
				var ol = op.length ;
				for( var i = 0 ; i < ol ; ++i ) {
					if ( op[ i ].value == agentID ) {
						al.selectedIndex = i ;
						break ;
					}
				}
			}
		}
		
		var elem = document.getElementsByTagName( "input" );
		for( var i = 0 ; i < elem.length ; i++ ) {
			if ( elem[ i ].type.match( /text/i ) ) {
				elem[ i ].readOnly = true ;
			} else
			if ( elem[ i ].type.match( /button/i ) ) {
				elem[ i ].disabled = true ;
			}
		}
		
		var elem = document.getElementsByTagName( "textarea" );
		for( var i = 0 ; i < elem.length ; i++ ) {
			elem[ i ].readOnly = true ;			
		}
		
		var elem = document.getElementsByTagName( "select" );
		for( var i = 0 ; i < elem.length ; i++ ) {
			elem[ i ].style.display = "none" ;			
		}
		
		elem = [];
		elem.push( document.getElementById( "woe_agency_alt_addr_cont" ) );
		elem.push( document.getElementById( "pm-ap-lnk" ) );
		for( var i = 0 ; i < elem.length ; i++ ) {
			elem[ i ].style.display = "none" ;			
		}
	};
	
	// tgt : agency sel
	// src : type of agency sel
	function upd( tgt , src ) {
		if ( typeof src === "undefined" ) {
			var toa = 1 ;
		} else {
			src = document.getElementById( src );
			var toa = src.value ;
		}
		
		if ( typeof tgt === "undefined" ) {
			tgt = "woe_agency_sel" ;
		}
		
		tgt = document.getElementById( tgt );
		
		loadAgencyList( "toa=" + toa , tgt );
	}

	function sendXML( data , async ) {
		var sd = "random=" + ( new Date() ).getTime() + ( Math.random() * 1000 ) + "&mode=ajax&data=" + encodeURIComponent( "<?xml version=\"1.0\" encoding=\"utf-8\" ?>" + data );
		var req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( "Microsoft.XMLHTTP" );
		}
		
		if ( typeof async === "undefined" ) {
			async = false ;
		}

		if ( req ) {
			req.open( "POST" , "writ-of-execution.php" , async );
			req.setRequestHeader( "Accept-Charset" , "windows-1251" );
			req.setRequestHeader( "Accept-Language" , "ru,en" );
			req.setRequestHeader( "Connection" , "close" );
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


	function doPOST( params ) {
		var sd = params + "&random=" + ( new Date() ).getTime() + ( Math.random() * 1000 );
		var req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( "Microsoft.XMLHTTP" );
		}

		if ( req ) {
			req.open( "POST" , "writ-of-execution.from.php" , false );
			req.setRequestHeader( "Accept-Charset" , "windows-1251" );
			req.setRequestHeader( "Accept-Language" , "ru,en" );
			req.setRequestHeader( "Connection" , "close" );
			req.setRequestHeader( "Content-length" , sd.length );
			req.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );
			req.setRequestHeader( "Content-Encoding" , "utf-8" );
			req.send( sd );
			//alert( req.responseText );
			return req.responseText ;
		}
	}

	// url : "toa=n"
	// tgt : agency sel
	function loadAgencyList( url , tgt ) {
		if ( typeof tgt === "undefined" ) {
			var al = document.getElementById( "woe_agency_sel" );
		} else {
			var al = tgt ;
		}
		
		al.innerHTML = doPOST( url );
	}
	
	// url : "agency=n"
	// tgt : agent sel
	function loadAgentList( url , tgt ) {
		if ( typeof tgt === "undefined" ) {
			var al = document.getElementById( "woe_agent_sel" );
		} else {
			var al = tgt ;
		}
		
		al.innerHTML = doPOST( url );
	}

	// tgt : agency sel
	// tgt2 : agency txt
	// tgt3 : agent sel or ''
	function agency_select( tgt , tgt2 , tgt3 , tgt4 ) {
		if ( typeof tgt3 === "undefined" ) {
			var tgt3 = "woe_agent_sel" ;
		}
		
		if ( typeof tgt2 === "undefined" ) {
			var tgt2 = "woe_agency_ta" ;
		}
		
		if ( typeof tgt === "undefined" ) {
			var tgt = "woe_agency_sel" ;
		}
		
		var al = document.getElementById( tgt );
		var at = document.getElementById( tgt2 );
		if ( al.selectedIndex > -1 ) {
			at.value = al.options[ al.selectedIndex ].text ;
			var agency = al.options[ al.selectedIndex ].value ;
		} else {
			var agency = "-1" ;
		}

		if ( tgt3 != "" ) {
			loadAgentList( "agency=" + agency , document.getElementById( tgt3 ) );
		}
		
		if ( typeof tgt4 !== "undefined" ) {
			getAgencyAddress( "aa=" + agency );
		}
	}
	
	function agent_select() {
		var al = document.getElementById( "woe_agent_sel" );
		var at = document.getElementById( "woe_agent_ta" );
		if ( al.selectedIndex > -1 ) {
			at.value = al.options[ al.selectedIndex ].text ;
		} else {
			//at.value = "" ;
		}
	}


	function srch( tgt , tgt2 ) {
		if ( typeof tgt2 === "undefined" ) {
			var tgt2 = "woe_agency_ta" ;
		}
		
		if ( typeof tgt === "undefined" ) {
			var tgt = "woe_agency_sel" ;
		}
		
		var at = document.getElementById( tgt2 );

		if ( at.value != "" ) {
			var st = at.value.toUpperCase();
			var al = document.getElementById( tgt );
			var count = al.options.length ;
			var j = -1 ;
			for ( var i = 0 ; i < count ; i++ ) {
				if ( al.options[ i ].text.toUpperCase().indexOf( st ) >= 0 ) {
					j = i ;
					break ;
				}
			}

			al.selectedIndex = j ;
		}
	}

	function srch2() {
		var at = document.getElementById( "woe_agent_ta" );

		if ( at.value != "" ) {
			var st = at.value.toUpperCase();
			var al = document.getElementById( "woe_agent_sel" );
			var count = al.options.length ;
			var j = -1 ;
			for ( var i = 0 ; i < count ; i++ ) {
				if ( al.options[ i ].text.toUpperCase().indexOf( st ) >= 0 ) {
					j = i ;
					break ;
				}
			}

			al.selectedIndex = j ;
		}
	}




	
	function getAgencyAddress( url ) {
		var aa = document.getElementById( "woe_agency_alt_addr" );
		aa.innerHTML = doPOST( url );
	}
	
	function fillAddress() {
		var aa = document.getElementById( "woe_agency_alt_addr" );
		var ata = document.getElementById( "woe_agency_addr_ta" );
		ata.value = aa.innerHTML ;
	}
		
	function setText( c , t ) {
		if ( typeof( c.innerText ) == "undefined" ) {
			c.textContent = t ;
		} else {
			c.innerText = t ;
		}
	}

	function addText( c , t ) {
		c.appendChild( document.createTextNode( t ) );
	}


	function showPayerMenu( pid ) {
		var pm = document.getElementById( "payer-menu" );
		var doc = sendXML( "<get-payments id=\"" + pid + "\" />" );
		var payerNode = null ;
		var paymentsNode = null ;
		for( var i = 0 ; i < doc.childNodes.length ; ++i ) {
			switch( doc.childNodes[ i ].nodeName ) {
				case "payer" :
					payerNode = doc.childNodes[ i ];
					break ;
				case "payments" :
					paymentsNode = doc.childNodes[ i ].childNodes ;
					break ;
			}
		}
		
		var pn = document.getElementById( "pm-pn-label" );
		setText( pn , getXMLNodeValue( payerNode ) );
		
		var pmpt = document.getElementById( "pm-pt" );
		while( pmpt.rows.length > 0 ) {
			pmpt.deleteRow( -1 );
		}
		
		if ( paymentsNode.length > 0 ) {
			
			var r = pmpt.insertRow( -1 );
			r.className = "pm-pt-h-row" ;
				var c = r.insertCell( -1 );
				c.className = "pm-pt-h-d" ;
				addText( c , "Дата ПП" );
				
				var c = r.insertCell( -1 );
				c.className = "pm-pt-h-pa" ;
				addText( c , "Плательщик" );
				
				var c = r.insertCell( -1 );
				c.className = "pm-pt-h-p" ;
				addText( c , "Сумма" );
				
				var c = r.insertCell( -1 );
				c.className = "pm-pt-h-n" ;
				addText( c , "№ ПП" );
		
			for( var i = 0 ; i < paymentsNode.length ; ++i ) {
				var cp = paymentsNode[ i ];
				var r = pmpt.insertRow( -1 );
				r.className = "pm-pt-d-row" ;
					var c = r.insertCell( -1 );
					c.className = "pm-pt-d-d" ;
					addText( c , cp.getAttribute( "d" ) );
					
					var c = r.insertCell( -1 );
					c.className = "pm-pt-d-pa" ;
					addText( c , getXMLNodeValue( cp ) );
					
					var c = r.insertCell( -1 );
					c.className = "pm-pt-d-p" ;
					addText( c , cp.getAttribute( "p" ) );
					
					var c = r.insertCell( -1 );
					c.className = "pm-pt-d-n" ;
					addText( c , cp.getAttribute( "n" ) );
			}
		}
		
		var pmtv = document.getElementById( "pm-total-v" );
		setText( pmtv , doc.getAttribute( "p" ) );
		
		pm.style.display = "" ;
	}
	
	function findCorr() {
		var ni = document.getElementById( "i_num" );
		var num = ni.value ;
		var num1 = num.match( /^\s*([А-Я]{2})\s*№\s*(\d{9})\s*$/i );
		var num2 = num.match( /^\s*(\d{2}RS\d{4}#\d{1,2}-\d{1,5}\/\d{4}#\d)\s*$/i );
		if ( num1 == null && num2 == null ) {
			ni.focus();
			alert( "Некорректный номер" );
			blinkElement( ni.parentNode );
			return ;
		}
		
		if ( num1 != null ) {
			var w1 = window.open( "correspondence.php?view=any&text=" + num1[ 2 ] , "_blank" );
		} else {
			var w1 = window.open( "correspondence.php?view=any&text=" + num , "_blank" );
		}
	}
