
		// event.type должен быть keypress
	function getChar( event ) {
		if ( event.which == null ) {  // IE
			if ( event.keyCode < 32 ) {
				return null ; // спец. символ
			}
			return String.fromCharCode( event.keyCode );
		}

		if ( event.which != 0 && event.charCode != 0 ) { // все кроме IE
			if ( event.which < 32 ) {
				return null ; // спец. символ
			}
			return String.fromCharCode( event.which ); // остальные
		}

		return null ; // спец. символ
	}

	function sendXML( data , async , addr , aParam ) {
		if ( typeof addr === "undefined" ) {
			addr = "all.php" ;
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

		if ( async ) {
			async = true ;
		} else {
			async = false ;
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

	function setText( c , t ) {
		if ( typeof( c.innerText ) == "undefined" ) {
			c.textContent = t ;
		} else {
			c.innerText = t ;
		}
	}
	
	var dlgCalendarMonths = [
		"Январь" , "Февраль" , "Март" ,
		"Апрель" , "Май" , "Июнь" ,
		"Июль" , "Август" , "Сентябрь" ,
		"Октябрь" , "Ноябрь" , "Декабрь"
	];
	
	var dlgCalendarSelectedMonth = {
		m : 0 ,
		y : 0 ,
		el : null ,
		ss : 0 ,
		se : 0
	};
	
	function dateFormat( d , m , y ) {
		return ( parseInt( d , 10 ) < 10 ? "0" : "" ) + d + "." + ( m < 10 ? 0 : "" ) + m + "." + y ;
	}
	
	function dlgCalendar( f , v1 , v2 , v3 ) {
		
		switch ( f ) {
			case 0 :
				var mn = document.getElementById( "dlgCalendarMonthName" );
				setText( mn , dlgCalendarMonths[ dlgCalendarSelectedMonth.m ] + " " + dlgCalendarSelectedMonth.y );
				var dc = ( new Date( dlgCalendarSelectedMonth.y , dlgCalendarSelectedMonth.m + 1 , 0 ) ).getDate();
				var fd = dlgCalendar( 4 , ( new Date( dlgCalendarSelectedMonth.y , dlgCalendarSelectedMonth.m , 1 ) ).getDay() );
				var t = document.getElementById( "dlgCalendarTable" );
				while ( t.rows.length > 1 ) {
					t.deleteRow( 1 );
				}
				
				var r = t.insertRow( -1 );
				for( var i = 0 ; i < fd ; i++ ) {
					var c = r.insertCell( -1 );
					c.className = "dlg-calendar-empty" ;
				}
				
				var cwd = fd ;
				var cd = 1 ;
				var today = new Date();
				while ( cd <= dc ) {
					if ( cwd == 7 ) {
						var r = t.insertRow( -1 );
						cwd = 0 ;
					}
					var c = r.insertCell( -1 );
					c.className = "dlg-calendar-date-" + ( cwd++ < 5 ? 0 : 1 ) + ( cd == today.getDate() && dlgCalendarSelectedMonth.m == today.getMonth() && dlgCalendarSelectedMonth.y == today.getFullYear() ? " dlg-calendar-today" : "" );
					c.onclick = function( d , m , y ) {
						return function() {
							dlgCalendar( 5 , d , m , y );
						};
					}( cd , dlgCalendarSelectedMonth.m + 1 , dlgCalendarSelectedMonth.y );
					setText( c , cd++ );
				}
				
				for( var i = cwd ; i < 7 ; i++ ) {
					var c = r.insertCell( -1 );
					c.className = "dlg-calendar-empty";
				}
				
				break ;
				
			case 1 :
				if ( typeof v1 === "undefined" ) {
					var v1 = new Date();
					dlgCalendarSelectedMonth.m = v1.getMonth();
					dlgCalendarSelectedMonth.y = v1.getFullYear();
				} else {
					dlgCalendarSelectedMonth.m = v1 - 1 ;
					dlgCalendarSelectedMonth.y = v2 ;
				}
				break ;
				
			case 2 :
				dlgCalendarSelectedMonth.m-- ;
				if ( dlgCalendarSelectedMonth.m < 0 ) {
					dlgCalendarSelectedMonth.m = 11 ;
					dlgCalendarSelectedMonth.y-- ;
				}
				dlgCalendar( 0 );
				break ;
				
			case 3 :
				dlgCalendarSelectedMonth.m++ ;
				if ( dlgCalendarSelectedMonth.m > 11 ) {
					dlgCalendarSelectedMonth.m = 0 ;
					dlgCalendarSelectedMonth.y++ ;
				}
				dlgCalendar( 0 );
				break ;
				
			case 4 :
				if ( v1 == 0 ) {
					v1 = 7 ;
				}
				v1-- ;
				return v1 ;
				break ;
				
			case 5 :
				var dlg = document.getElementById( "dlgCalendar" );
				var tgt = dlgCalendarSelectedMonth.el ;
				var ss = dlgCalendarSelectedMonth.ss ;
				var se = dlgCalendarSelectedMonth.se ;
				tgt.value = tgt.value.substring( 0 , ss ) + dateFormat( v1 , v2 , v3 ) + tgt.value.substring( se , tgt.value.length );
				dlg.style.display = "none" ;
				var blockator = document.getElementById( "blockator" );
				blockator.style.display = "none" ;
				break ;
				
			case 6 :
				dlgCalendarSelectedMonth.y-- ;
				dlgCalendar( 0 );
				break ;
				
			case 7 :
				dlgCalendarSelectedMonth.y++ ;
				dlgCalendar( 0 );
				break ;
		}
		
	}
	
	
	function dlgCalendarShow( event , id ) {
		event = event || window.event ;
		var b = getChar( event );
		if ( b == "*" ) {
			var blockator = document.getElementById( "blockator" );
			blockator.style.display = "" ;
			
			var dlg = document.getElementById( "dlgCalendar" );
			var tgt = document.getElementById( "flt_" + id );
			dlgCalendarSelectedMonth.el = tgt ;
			dlgCalendarSelectedMonth.ss = tgt.selectionStart ;
			dlgCalendarSelectedMonth.se = tgt.selectionEnd ;
			dlg.style.display = "" ;
			return false ;
		}
	}
	
	var dlgListFieldID = null ;
	var dlgListItems = null ;
	
	function dlgListShow( event , id , tabn , fld ) {
		var blockator = document.getElementById( "blockator" );
		blockator.style.display = "" ;
		
		dlgListFieldID = id ;
		
		var dlg = document.getElementById( "dlgList" );
		var tbl = document.getElementById( "dlg_list_table" );
		var tgt = document.getElementById( "flt_" + id );
		
		while( tbl.rows.length > 0 ) {
			tbl.deleteRow( 0 );
		}
		
		var list = sendXML( "<get-list-items tabn=\"" + tabn + "\" fld=\"" + fld + "\"  />" );
		list = list.childNodes ;
		var li = [];
		tgt = tgt.value.split( "," );
		dlgListItems = [];
		for( i = 0 ; i < list.length ; i++ ) {
			var cbid = list[ i ].getAttribute( "id" ); 
			var itemDesc = getXMLNodeValue( list[ i ] );
			var r = tbl.insertRow( -1 );
			var c = r.insertCell( -1 );
			c.className = "dlg-list-item-cb-area" ;
				var cb = document.createElement( "input" );
				cb.name = "dlg_list_cb" ;
				cb.type = "checkbox" ;
				cb.value = cbid ;
				cb.checked = tgt.indexOf( cbid ) > -1 ;
			c.appendChild( cb );
			
			dlgListItems.push( {
				id : cbid ,
				desc : itemDesc
			} );
			
			var c = r.insertCell( -1 );
			c.className = "dlg-list-item-desc-area" ;
			setText( c , itemDesc );
		}
		
		dlg.style.display = "" ;
	}
	
	function dlgListApply() {
		var dlg = document.getElementById( "dlgList" );
		var tgt = document.getElementById( "flt_" + dlgListFieldID );
		var ref = document.getElementById( "flt_a_" + dlgListFieldID );
		var boxes = document.getElementsByName( "dlg_list_cb" );
		
		var checked = [];
		var txt = [];
		for( var i = 0 ; i < boxes.length ; i++ ) {
			if ( boxes[ i ].checked ) {
				checked.push( boxes[ i ].value );
				for( var j = 0 ; j < dlgListItems.length ; j++ ) {
					if ( boxes[ i ].value == dlgListItems[ j ].id ) {
						txt.push( dlgListItems[ j ].desc );
					}
				}
			}
		}
		
		var txt2 ;
		if ( txt.length > 0 && checked.length > 0 ) {
			txt = txt.join( ", " );
			if ( txt.length > 29 ) {
				txt2 = txt.substr( 0 , 26 ) + "..." ;
			} else {
				txt2 = txt ;
			}
		} else {
			txt = "< выбрать >" ;
			txt2 = txt ;
		}
		
		setText( ref , txt2 + " *" );
		ref.title = txt ;
		
		if ( checked.length > 0 ) {
			tgt.value = checked.join( "," );
		} else {
			tgt.value = "" ;
		}
		
		dlg.style.display = "none" ;
		
		var blockator = document.getElementById( "blockator" );
		blockator.style.display = "none" ;
	}
	
	function dlgListCancel() {
		var dlg = document.getElementById( "dlgList" );
		dlg.style.display = "none" ;
		
		var blockator = document.getElementById( "blockator" );
		blockator.style.display = "none" ;
	}
	
	function dlgListClear() {
		var boxes = document.getElementsByName( "dlg_list_cb" );
		
		for( var i = 0 ; i < boxes.length ; i++ ) {
			boxes[ i ].checked = false ;
		}
	}
	
	window.onload = function() {
		dlgCalendar( 1 );
		dlgCalendar( 0 );
	};
	
	