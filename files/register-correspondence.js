
	var bp1 = 17.00 ;
	var bp2 = [ [ 17.00 , 37.00 ] , [ 35.00 , 50.00 ] ];

	var ws = 20 ;
	var wbp1 = 20 ;
	var wlt = 100 ;
	
	var ps = 2.0 ;

	function sendXML( data , async , addr , aParam ) {
		if ( typeof addr === "undefined" ) {
			addr = "register-correspondence.php" ;
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

	function setText( c , t ) {
		if ( typeof( c.innerText ) == "undefined" ) {
			c.textContent = t ;
		} else {
			c.innerText = t ;
		}
	}

	function getText( o ) {
		if ( "innerText" in o ) {
			return o.innerText ;
		} else {
			return o.textContent ;
		}
	}



	function generateRegistry2( addr ) {
		var ra = document.getElementsByName( "i_row[]" );
		var ria = [];
		for( var i = 0 ; i < ra.length ; i++ ) {
			var tmpv = ra[ i ].value ;
			var m = tmpv.match( /^\d+$/ );
			if ( m.length == 1 && ra[ i ].checked ) {
				ria.push( parseInt( ra[ i ].value , 10 ) );
			}
		} 

		if ( ria.length == 0 ) {
			alert( "Нет выделенных строк" );
		} else {
			var mini = ria[ 0 ];
			var maxi = mini ;
			
			ria = ria.sort();
			
			var ranges = [];
			for( var i = 1 ; i < ria.length ; i++ ) {
				if ( ria[ i ] > maxi + 1 ) {
					if ( mini == maxi ) {
						ranges.push( mini );
					} else {
						ranges.push( mini + "-" + maxi );
					}
					mini = maxi = ria[ i ];
				} else {
					maxi++ ;
				}
			}
			if ( mini == maxi ) {
				ranges.push( mini );
			} else {
				ranges.push( mini + "-" + maxi );
			}
			
			window.open( addr + ranges.join( "," ) , "_blank" );
		}
	}
	
	var currentEditField = null ;

	function updField( r ) {

		if ( typeof r == "undefined" ) {
			r = false ;
		}

		if ( currentEditField != null && ( ( currentEditField.oldText != currentEditField.input.value ) || r ) ) {
			var res = sendXML( "<" + fieldsData[ currentEditField.name ].xmlReqString + " id=\"" + currentEditField.rid + "\">" + toCDATA( currentEditField.input.value ) + "</" + fieldsData[ currentEditField.name ].xmlReqString + ">" , !r );
			if ( r ) {
				currentEditField.input.value = getXMLNodeValue( res );
			}
			currentEditField.oldText = currentEditField.input.value ;
		}

		cfth = null ;
	}

	var cfth = null ;

	function fikp( event ) {
		event = event || window.event ;
		var a = event.keyCode || event.charCode ;

		if ( cfth != null ) {
			clearTimeout( cfth );
		}

		if ( a == 13 ) {
			currentEditField.input.blur();
			return false ;
		} else {
			cfth = setTimeout( "updField();" , 500 );
			return true ;
		}
	}

	function fib() {
		if ( currentEditField != null ) {
			if ( cfth != null ) {
				clearTimeout( cfth );
			}
			updField( true );
			currentEditField.parentCell.removeChild( currentEditField.input );
			var cfs = document.createElement( "span" );
			cfs.id = "rc_" + fieldsData[ currentEditField.name ].idString + "_s_" + currentEditField.rid ;
			setText( cfs , currentEditField.oldText );
			currentEditField.parentCell.appendChild( cfs );
			currentEditField = null ;
		}
	}

	var fieldsData = [];
	fieldsData[ "price" ] = {
		idString : "p" ,
		xmlReqString : "price"
	};

	fieldsData[ "weight" ] = {
		idString : "w" ,
		xmlReqString : "weight"
	};

	function editField ( fn , rid ) {
		if ( currentEditField != null && currentEditField.rid == rid ) {
			return ;
		}

		if ( currentEditField != null ) {
			if ( cfth != null ) {
				clearTimeout( cfth );
			}
			updField( true );
			currentEditField.parentCell.removeChild( currentEditField.input );
			var cfs = document.createElement( "span" );
			cfs.id = "rc_" + fieldsData[ currentEditField.name ].idString + "_s_" + currentEditField.rid ;
			setText( cfs , currentEditField.oldText );
			currentEditField.parentCell.appendChild( cfs );
		}

		var pc = document.getElementById( "rc_" + fieldsData[ fn ].idString + "_" + rid );
		var cfs = document.getElementById( "rc_" + fieldsData[ fn ].idString + "_s_" + rid );
		var ot = getText( cfs );

		pc.removeChild( cfs );
		var ci = document.createElement( "input" );
		ci.className = "mt-d-" + fieldsData[ fn ].idString + "-i" ;
		ci.value = ot ;
		ci.onkeypress = fikp ;
		ci.onblur = fib ;
		pc.appendChild( ci );

		currentEditField = {
			rid : rid ,
			parentCell : pc ,
			input : ci ,
			oldText : ot ,
			name : fn
		};

		ci.focus();
		ci.select();
	}

	function doDelete() {
		var res = prompt( "Для подтверждения удаления записей реестра напечатайте в нижней строке слово \"У Д А Л Е Н И Е\" без пробелов" , "" );
		if ( res == "УДАЛЕНИЕ" ) {
			var ra = document.getElementsByName( "i_row[]" );
			var ria = "" ;
			for( var i = 0 ; i < ra.length ; i++ ) {
				var cr = ra[ i ];
				if ( cr.checked ) {
					ria+= "<e id=\"" + cr.value + "\" />" ;
				}
			}

			sendXML( "<delete>" + ria + "</delete>" , false );
			window.location.reload();

		} else {
			alert( "Удаление не подтверждено" );
		}

	}

	function selectRows( mark ) {
		var ra = document.getElementsByName( "i_row[]" );
		if ( typeof mark == "undefined" ) {
			for( var i = 0 ; i < ra.length ; i++ ) {
				ra[ i ].checked = true ;
			}
		} else {
			for( var i = 0 ; i < ra.length ; i++ ) {
				ra[ i ].checked = ( ra[ i ].getAttribute( "data-mark" ) == mark );
			}
		}
	}

	function setMark( mark ) {
		if ( typeof mark == "undefined" ) {
			mark = "" ;
		}

		var ra = document.getElementsByName( "i_row[]" );
		var cmc = 0 ;
		for( var i = 0 ; i < ra.length ; i++ ) {
			var cr = ra[ i ];
			if ( cr.checked ) {
				var tmpMark = cr.getAttribute( "data-mark" );
				if ( tmpMark != "" && tmpMark != mark ) {
					cmc++ ;
				}
			}
		}

		if ( cmc > 0 ) {
			var res = confirm( "Среди выделенных записей присутствуют записи с метками, отличающимися от устанавливаемой. Продолжение операции приведет к замене установленных ранее меток на новые. Заменить ?" );
			if ( !res ) {
				return false ;
			}
		}


		var ria = [];
		for( var i = 0 ; i < ra.length ; i++ ) {
			var cr = ra[ i ];
			if ( cr.checked ) {
				ria.push( cr.value );
				cr.setAttribute( "data-mark" , mark );
				var cmc = document.getElementById( "i_mark_" + cr.value );
				if ( mark == "" ) {
					cmc.style.backgroundColor = "" ;
				} else {
					cmc.style.backgroundColor = "#" + markColors[ mark ];
				}
				setText( cmc , mark );
			}
		}

		sendXML( "<set-mark id=\"" + ria.join( "," ) + "\" mark=\"" + mark + "\"/>" , false );
	}

	function deselectRows() {
		var ra = document.getElementsByName( "i_row[]" );
		for( var i = 0 ; i < ra.length ; i++ ) {
			ra[ i ].checked = false ;
		}
	}

	function showAddressesFillDlg( event , id ) {
		event = window.event ? window.event : event ;
		event.target = event.target ? event.target : event.srcElement ;
		if( event.stopPropagation ) {
			event.stopPropagation();
		} else {
			event.cancelBubble = true ;
		}

		var addressesFillDlg = document.getElementById( "addresses_fill_dlg" );

		var w = document.getElementById( "new-weight" );
		w.value = "" + ( wbp1 / 1000 ).toFixed( 3 );
		var p = document.getElementById( "new-price" );
		p.value = bp1.toFixed( 2 ) + " + 0.00" ;

		var lf = getCookie( "labelFormat" );
		var labelFormat = document.getElementById( "labelFormat" );
		labelFormat.value = lf ;


		addressesFillDlg.style.display = "" ;
	}

	function hideAddressesFillDlg() {
		var afd = document.getElementById( "addresses_fill_dlg" );
		afd.style.display = "none" ;
	}

	function processAddress( addr ) {
		var sdi = addr.match( /(?:[,. ]+(\d{6})[,. ]*)/ );
		if ( sdi != null && sdi.length == 2 ) {
			var res = [];
			res[ "index" ] = sdi[ 1 ] ;
			res[ "address" ] = addr.replace( /(?:[,. ]+(\d{6})[,. ]*)/ , "" );
			return res ;
		} else {
			sdi = addr.match( /(?:[,. ]*(\d{6})[,. ]+)/ );
			if ( sdi != null && sdi.length == 2 ) {
				var res = [];
				res[ "index" ] = sdi[ 1 ];
				res[ "address" ] = addr.replace( /(?:[,. ]*(\d{6})[,. ]+)/ , "" );
				return res ;
			} else {
				var res = [];
				res[ "index" ] = "" ;
				res[ "address" ] = addr ;
				return res ;
			}
		}
	}

	function setCookie( n , v , t ) {
		if ( t == null || t == 0 ) {
			var ed = "" ;
		} else {
			var ed = new Date();
			ed.setDate( ed.getDate() + t );
			ed = "; expires=" + ed.toUTCString() ;
		}
		var c_value= escape( v ) + ed + "; path=/" ;
		document.cookie = n + "=" + c_value ;
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

	function labelFormatChange() {
		var lf = document.getElementById( "labelFormat" );
		setCookie( "labelFormat" , lf.value , 1000  );
	}

	function getPrices( w , t ) {
		w = "" + w ;
		var wm = w.match( /^\d*(?:[.,]\d{0,3})?$/ );
		if ( wm == null ) {
			return {
				s : 1
			};
		}
		
		wm = w.match( /^\d*[.,]$/ );
		if ( wm != null ) {
			w = w + "0" ;
		}
		
		wm = w.match( /^[.,]\d{0,3}$/ );
		if ( wm != null ) {
			w = "0" + w ;
		}
		
		w = Math.round( parseFloat( w.replace( "," , "." ) ) * 1000 );
		var wSave = w ;
		if ( w % 20 > 0 ) {
			w = w - w % 20 + 20 ;
		}
		
		w = Math.max( 1 , w );
		if ( isNaN( w ) ) {
			return {
				s : 1
			};
		}

		var p1 = 0.00 ;
		var p2 = 0.00 ;

		if ( !t && w <= wbp1 ) {
			p1 = bp1 ;
		} else {
			if ( w <= wlt ) {
				p2 = bp2[ t ? 1 : 0 ][ 0 ] + Math.round( ( w - ws ) / ws ) * ps ;
			} else {
				p2 = bp2[ t ? 1 : 0 ][ 1 ] + Math.round( ( w - wlt - ws ) / ws ) * ps ;
			}
		}

		return {
			w : wSave / 1000 ,
			p1 : p1 ,
			p2 : p2
		};
	}

	async function printLabelAndSave() {
		var c = document.getElementById( "comment" );
		var a = document.getElementById( "addressee" );
		var d = document.getElementById( "destination" );

		var r = processAddress( d.value );


		var w = document.getElementById( "new-weight" );
		var lt = document.getElementById( "new-letter-type" );
		var pr = getPrices( w.value , lt.checked );

		var lData = sendXML( "<add-label p1=\"" + pr.p1.toFixed( 2 ) + "\" p2=\"" + pr.p2.toFixed( 2 ) + "\" w=\"" + pr.w + "\"><comment>" + toCDATA( c.value ) + "</comment><addressee>" + toCDATA( a.value ) + "</addressee><destination>" + toCDATA( d.value ) + "</destination></add-label>" , false );
		await DoPrintLetterLabel( a.value , r[ "address" ] , r[ "index" ] );
		window.location.reload();
	}

	function changeWeight() {
		var w = document.getElementById( "new-weight" );
		var lt = document.getElementById( "new-letter-type" );
		var p = document.getElementById( "new-price" );

		var pr = getPrices( w.value , lt.checked );
		if ( isNaN( pr.w ) ) {
			alert( "Вес указан не правильно" );
			return ;
		}
		p.value = pr.p1.toFixed( 2 ) + " + " + pr.p2.toFixed( 2 );
	}
	
	function calcControlDigit() {
		var src = prompt( "Введите часть ШПИ ( 13 знаков )" , "394009" );
		if ( src != null ) {
			var res = src.replace( /\s+/g , "" );
			var s = 0 ; 
			for ( var i = 0 ; i < res.length ; i+= 2 ) {
				s+= parseInt( res.charAt( i ) , 10 );
			}
			s*= 3 ;
			for ( var i = 1 ; i < res.length ; i+= 2 ) {
				s+= parseInt( res.charAt( i ) , 10 );
			}
			s = s % 10 ;
			if ( s > 0 ) {
				s = 10 - s ;
			}
			
			alert( "Последний знак : " + s + "\r\n" + src + "" + s );
		}
	}


