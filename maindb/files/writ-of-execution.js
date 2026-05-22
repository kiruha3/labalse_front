	function getText( c ) {
		if ( typeof( c.textContent ) == "undefined" ) {
			return c.text ;
		} else {
			return c.textContent ;
		}
	}

	var newPayersID = 0 ;
	
	$.windowOnLoad.push( function() {
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
		
		
		$.fileUploadDLG = new $.TDLGFileUpload( { 
			tabInputName : "file-upload-tabs" , 
			redirect : false , 
			test : 0 ,
			docTypeList : [
				{ v : "3400" , n : "Исполнительный лист" }
			]
		} );
	} );
	
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

	function sendXMLLocal( data , async ) {
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
	
	function addPayer() {
		var pt = document.getElementById( "woe_payers_table" );
		var r = pt.insertRow( -1 );
		r.id = "woe_pt_new_row_" + newPayersID ;
		
		var c = r.insertCell( -1 );
		c.className = "woe-pt-d-0" ;
			var tmp = document.createElement( "a" );
			tmp.className = "woe-pt-i-0" ;
			tmp.onclick = function( x ) {
				return function() {
					deletePayer( x , 1 );
				};
			}( newPayersID );
				var tmp2 = document.createElement( "img" );
				tmp2.src = "themes/" + UserThemeLoc + "/btn_del.bmp" ;
				tmp.appendChild( tmp2 );
		c.appendChild( tmp );
		
		var c = r.insertCell( -1 );
		c.className = "woe-pt-d-n" ;
			var w = document.createElement( "div" );
			w.className = "woe-pt-d-wrapper" ;
				var tmp = document.createElement( "input" );
				tmp.type = "text" ;
				tmp.name = "i_new_payers_name[" + newPayersID + "]" ;
				tmp.value = "" ;
				tmp.className = "woe-pt-i-n" ;
		var inpn = tmp ;
			w.appendChild( tmp );
		c.appendChild( w );
		
		var c = r.insertCell( -1 );
		c.className = "woe-pt-d-p" ;
			var w = document.createElement( "div" );
			w.className = "woe-pt-d-wrapper" ;
				var tmp = document.createElement( "input" );
				tmp.type = "text" ;
				tmp.name = "i_new_prices[" + newPayersID + "]" ;
				tmp.value = "" ;
				tmp.className = "woe-pt-i-p" ;
			w.appendChild( tmp );
		c.appendChild( w );
		
		inpn.focus();
		
		newPayersID++ ;
	}
	
	function deletePayer( id , t ) {
		switch( t ) {
			case 0 :
				prompt( "Для удаления этой строки обратитесь к Администратору и сообщите ему указанное ниже число" , id );
				break ;
				
			case 1 :
				var pt = document.getElementById( "woe_payers_table" );
				var r = document.getElementById( "woe_pt_new_row_" + id );
				pt.deleteRow( r.rowIndex );
				break ;
			case 2 :
				var pt = document.getElementById( "woe_payers_table" );
				var r = document.getElementById( "woe_pt_row_" + id );
				pt.deleteRow( r.rowIndex );
				break ;
		}
	}
	
	
	var blinkTimer = null ;
	var blinkFase = 0 ;
	
	function blinkElement( f ) {
		blinkTimer = setInterval( function( x ){
			blinkFase = 0 ;
			return function() {
				if ( isArray( x ) ) {
					for( var i = 0 ; i < x.length ; i++ ) {
						if ( blinkFase < 20 ) {
							if ( blinkFase % 2 == 0 ) {
								x[ i ].style.backgroundColor = "" ;
							} else {
								x[ i ].style.backgroundColor = "#ff0000" ;
							}
						} else {
							x[ i ].style.backgroundColor = "" ;
							clearInterval( blinkTimer );
							blinkTimer = null ;
						}
					}
					
					blinkFase++ ;
				} else {
					if ( blinkFase++ < 20 ) {
						if ( blinkFase % 2 == 0 ) {
							x.style.backgroundColor = "" ;
						} else {
							x.style.backgroundColor = "#ff0000" ;
						}
					} else {
						x.style.backgroundColor = "" ;
						clearInterval( blinkTimer );
						blinkTimer = null ;
					}
				}
			};
		}( f ) , 100 );
	}
	
	function checkYearIsLeap( y ) {
		return ( y % 400 == 0 ? true : ( y % 100 == 0 ? false : ( y % 4 == 0 ) ) );
	}
	
	function daysInMonth( y , m ) {
		var dc = {
			n : [ 31 , 28 , 31 , 30 , 31 , 30 , 31 , 31 , 30 , 31 , 30 , 31 ] ,
			l : [ 31 , 29 , 31 , 30 , 31 , 30 , 31 , 31 , 30 , 31 , 30 , 31 ]
		};
		
		y = checkYearIsLeap( y ) ? "l" : "n" ;
		return dc[ y ][ m - 1 ];
	}
	
	function checkDate( di , n ) {
		if ( typeof n === "undefined" ) {
			n = false ;
		}
		
		var dv = di.value ;
		dv = dv.trim();
		if ( !n && dv.length == 0 ) {
			return true ;
		}
			
		var m = dv.match( /^\s*([0-2]\d|3[01])[-.,](0\d|1[0-2])[-.,](\d{4})\s*$/ );
		if ( m == null ) {
			return false ;
		}
		
		for( var i = 1 ; i <= 3 ; ++i ) {
			m[ i ] = parseInt( m[ i ] , 10 );
		}
		
		var dc = daysInMonth( m[ 3 ] , m[ 2 ] );
		return m[ 1 ] >= 1 && m[ 1 ] <= dc ;
	}
	
	function checkForm() {
		var form = document.getElementById( "woe_form" );
		
		var di = document.getElementById( "i_date" );
		if ( !checkDate( di ) ) {
			di.focus();
			alert( "Некорректная дата" );
			blinkElement( di.parentNode.parentNode );
			return ;
		}
		
		var di = document.getElementById( "i_issue_date" );
		if ( !checkDate( di ) ) {
			di.focus();
			alert( "Некорректная дата" );
			blinkElement( di.parentNode.parentNode );
			return ;
		}
		
		var di = document.getElementById( "i_incoming_date" );
		if ( !checkDate( di ) ) {
			di.focus();
			alert( "Некорректная дата" );
			blinkElement( di.parentNode.parentNode );
			return ;
		}
		
		
		var iel = document.getElementsByTagName( "input" );
		for( var i = 0 ; i < iel.length ; ++i ) {
			var inp = iel[ i ];
			var inpName = inp.name ;
			var m = inpName.match( /^i_(?:new_)?payers_name\[\d+\]$/ );
			if ( m != null ) {
				if ( inp.value.trim().length == 0 ) {
					inp.focus();
					alert( "Некорректное Ф.И.О. или наименование плательщика" );
					blinkElement( inp.parentNode.parentNode );
					return ;
				} else {
					continue ;
				}
			}
			
			var m = inpName.match( /^i_(?:new_)?prices\[\d+\]$/ );
			if ( m != null ) {
				m = inp.value.match( /^\d{1,11}(?:[.,]\d{1,2})?$/ );
				if ( m == null ) {
					inp.focus();
					alert( "Некорректная сумма" );
					blinkElement( inp.parentNode.parentNode );
					return ;
				} else {
					continue ;
				}
			}
		}
		
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
		
		form.submit();
	}
	
	function showPayerMenu( pid ) {
		var pm = document.getElementById( "payer-menu" );
		var doc = sendXMLLocal( "<get-payments id=\"" + pid + "\" />" );
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
				c.className = "pm-pt-h-t" ;
				addText( c , "" );
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
					c.className = "pm-pt-d-t" ;
					var tmp = document.createElement( "a" );
					tmp.className = "pm-pt-d-t--del" ;
					tmp.onclick = function( x ) {
						return function() {
							doDelPayment( x );
						};
					}( cp.getAttribute( "id" ) );
					c.appendChild( tmp );
					//addText( c , "" );
					
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
		
		var pmapl = document.getElementById( "pm-ap-lnk" );
		pmapl.onclick = function( x ) {
			return function() {
				addPayment( x );
			};
		}( pid );
		
		pm.style.display = "" ;
	}
	
	function addPayment( pid ) {
		var apd = document.getElementById( "add-payment-dlg" );
		var d = document.getElementById( "ap-date" );
		d.value = "" ;
		var p = document.getElementById( "ap-price" );
		p.value = "" ;
		var n = document.getElementById( "ap-num" );
		n.value = "" ;
		
		var l = document.getElementById( "ap-lnk-ok" );
		l.onclick = function( x ) {
			return function() {
				doAddPayment( x );
			};
		}( pid );
		
		var al = document.getElementById( "ap-agency-list" );
		
		upd( "ap_agency_sel" , "ap-toa" );
		
		apd.style.display = "" ;
		d.focus();
	}
	
	function doAddPayment( pid ) {
		var t = document.getElementById( "ap-toa" );
		
		var d = document.getElementById( "ap-date" );
		d.value = d.value.trim();
		if ( !checkDate( d , true ) ) {
			d.focus();
			alert( "Некорректная дата" );
			blinkElement( d.parentNode.parentNode );
			return ;
		}
		
		var p = document.getElementById( "ap-price" );
		p.value = p.value.trim();
		var m = p.value.match( /^\d{1,11}(?:[.,]\d{1,2})?$/ );
		if ( m == null ) {
			p.focus();
			alert( "Некорректная сумма" );
			blinkElement( p.parentNode.parentNode );
			return ;
		}

		var n = document.getElementById( "ap-num" );
		n.value = n.value.trim();
		var m = n.value.match( /^\d{1,8}$/ );
		if ( m == null ) {
			n.focus();
			alert( "Некорректный № ПП" );
			blinkElement( n.parentNode.parentNode );
			return ;
		}
		
		var a = document.getElementById( "ap_from_agency" );
		
		var res = sendXMLLocal( "<add-payment pid=\"" + pid + "\" t=\"" + t.value + "\" d=\"" + d.value + "\" p=\"" + p.value + "\" n=\"" + n.value + "\">" + toCDATA( a.value ) + "</add-payment>" );
		if ( res.getAttribute( "state" ) == "ok" ) {
			alert( "ПП добавлено успешно" );
			closeAPDlg();
			showPayerMenu( pid );
		} else {
			alert( "Ошибка добавления ПП" );
		}
	}
	
	function doDelPayment( pid ) {
		var res = prompt( "Для подтверждения удаления введите текст '" + pid + " УДАЛИТЬ' без кавычек и пробелов" ); 
		if ( res != pid + "УДАЛИТЬ" ) {
			alert( "Удаление не подтверждено" );
			return ;
		}
		var res = sendXMLLocal( "<del-payment pid=\"" + pid + "\" />" );
		if ( res.getAttribute( "state" ) == "ok" ) {
			alert( "ПП удалено успешно" );
		} else {
			alert( "Ошибка удаления ПП" );
		}
	}
	

	
	function closeAPDlg() {
		var apd = document.getElementById( "add-payment-dlg" );
		apd.style.display = "none" ;
	}
	
	function closeWOE( id ) {
		var res = prompt( "Для подтверждения закрытия введите текст '" + id + " ЗАКРЫТЬ' без кавычек и пробелов" ); 
		if ( res != id + "ЗАКРЫТЬ" ) {
			alert( "Закрытие не подтверждено" );
			return ;
		}
		var res = sendXMLLocal( "<close id=\"" + id + "\" />" );
		if ( res.getAttribute( "state" ) == "ok" ) {
			alert( "И/Л закрыт" );
			window.close();
		} else {
			alert( "Ошибка!" );
		}
	}
	
	function uncloseWOE( id ) {
		var res = prompt( "Для подтверждения отмены закрытия введите текст '" + id + " ОТМЕНА ЗАКРЫТИЯ' без кавычек и пробелов" ); 
		if ( res != id + "ОТМЕНАЗАКРЫТИЯ" ) {
			alert( "Отмена закрытия не подтверждена" );
			return ;
		}
		var res = sendXMLLocal( "<unclose id=\"" + id + "\" />" );
		if ( res.getAttribute( "state" ) == "ok" ) {
			alert( "И/Л снова открыт" );
			window.close();
		} else {
			alert( "Ошибка!" );
		}
	}


	function copyWOE( id ) {
		var res = prompt( "Для подтверждения создания копии введите текст '" + id + " КОПИЯ' без кавычек и пробелов" ); 
		if ( res != id + "КОПИЯ" ) {
			alert( "Создание копии не подтверждено" );
			return ;
		}
		var res = prompt( "Укажите серию нового И/Л" ); 
		if ( res == null ) {
			return ;
		}
		
		var ni = document.getElementById( "i_num" );
		var num = ni.value ;
		var num1 = num.match( /^\s*([А-Я]{2})\s*№\s*(\d{9})\s*$/i );
		var num2 = num.match( /^\s*(\d{2}RS\d{4}#\d{1,2}-\d{1,5}\/\d{4}#\d)\s*$/i );
		if ( num1 == null && num2 == null ) {
			alert( "Некорректный номер" );
			return ;
		}
		
		var res = sendXMLLocal( "<copy id=\"" + id + "\">" + toCDATA( res ) + "</copy>" );
		if ( res.getAttribute( "state" ) == "ok" ) {
			alert( "копия И/Л создана" );
			location.replace( "?edit=" + res.getAttribute( "id" ) );
		} else {
			alert( "Ошибка!" );
		}
	}

	function deleteWOE( id ) {
		var res = prompt( "Для подтверждения удаления введите текст '" + id + " УДАЛИТЬ' без кавычек и пробелов" ); 
		if ( res != id + "УДАЛИТЬ" ) {
			alert( "Удаление не подтверждено" );
			return ;
		}
		var res = sendXMLLocal( "<delete id=\"" + id + "\" />" );
		if ( res.getAttribute( "state" ) == "ok" ) {
			alert( "И/Л удален" );
			window.close();
		} else {
			alert( "Ошибка!" );
		}
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


	function showFUDlg( id , type ) {
		$.fileUploadDLG.show( id , type );
	}