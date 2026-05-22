/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	$.windowOnLoad.push( function() {
		//alert( $.typeOfAgency );
		var fel = document.getElementById( "i_case_category" );

		upd( $.typeOfAgency );

		var sel1 = document.getElementById( "i_from_agency_list" );
		var j = -1 ;
		for ( var i = 0 ; i < sel1.options.length ; i++ ) {
			if ( sel1.options[ i ].value == $.agencyId ) {
				j = i ;
				break ;
			}
		}

		sel1.selectedIndex = j ;

		var sel2 = document.getElementById( "i_from_agent_list" );
		j = -1 ;
		for ( var i = 0 ; i < sel2.options.length ; i++ ) {
			if ( sel2.options[i].value == $.agentId ) {
				j = i ;
				break ;
			}
		}

		sel2.selectedIndex = j ;
		
		var addMark = function ( dlg , el , tmpl ) {
			if ( tmpl != null ) {
				var markImg = document.createElement( "div" );
				markImg.className = "tmpl-dlg-mark-execute" ;
				markImg.alt = markImg.title = "Нажмите * в полее ввода чтобы выбрать шаблон" ;
				markImg.onclick = el.onkeypress ;
			} else {
				var markImg = document.createElement( "div" );
				markImg.className = "tmpl-dlg-mark-add" ;
				markImg.alt = markImg.title = "Нажмите здесь чтобы добавить шаблон" ;
				markImg.onclick = function( x ) {
					return function( event ) {
						dlg.create( x );
					};
				}( el );
			}
	
			while ( el.tagName != "TR" ) {
				el = el.parentNode ;
			}
			
			el = el.insertCell( -1 );
			el.vAlign = "middle" ;
			el.appendChild( markImg );
		};
		
		var changeMark = function ( dlg , el , tmpl ) {
			if ( tmpl != null ) {
				var markImg = document.createElement( "div" );
				markImg.className = "tmpl-dlg-mark-execute" ;
				markImg.alt = markImg.title = "Нажмите * в полее ввода чтобы выбрать шаблон" ;
				markImg.onclick = el.onkeypress ;
			}
	
			while ( el.tagName != "TR" ) {
				el = el.parentNode ;
			}
			
			el = el.cells[ el.cells.length - 1 ];
			el.replaceChild( markImg , el.childNodes[ 0 ] );
		};
		
		var dlg = new $.TDLGInputTemplate(
			$.tmpl ,
			{
				autoAssign : false ,
				variables : $.tmplVar ,
				assignCallback : addMark ,
				reAssignCallback : changeMark
			}
		);
		
		for( var i = 0 ; i < $.tmplTargets.length ; i++ ) {
			dlg.assign( $.tmplTargets[ i ] );
		}
				
		fel.focus();
		
		/*$.agentDLG = new $.TDLGAgentSelect( { link : {
			type : "i_from_type_of_agency" ,
			agency : "i_from_agency" ,
			agencyList : "i_from_agency_list" ,
			agent : "i_from_agent" ,
			agentList : "i_from_agent_list"
		} } , { type : $.typeOfAgency } );*/
	} );

	function __sendXML( data , async , addr , aParam ) {
		if ( typeof addr === "undefined" ) {
			addr = "level1card.ajax.php" ;
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

	function upd( toa ) {
		var sel1 = document.getElementById( "i_from_type_of_agency" );
		var text1 = document.getElementById( "i_from_agency" );

		if ( toa == undefined ) {
			toa = sel1.options[ sel1.selectedIndex ].value ;
		}

		loadAgencyList( "toa=" + toa );
	}

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

	function srch() {
		var text1 = document.getElementById( "i_from_agency" );

		if ( text1.value != "" ) {
			var st = text1.value.toUpperCase();
			var sel1 = document.getElementById( "i_from_agency_list" );
			var count = sel1.options.length ;
			var j = -1 ;
			for ( var i = 0 ; i < count ; i++ ) {
				if ( sel1.options[ i ].text.toUpperCase().indexOf( st ) == 0 ) {
					j = i ;
					break ;
				}
			}

			sel1.selectedIndex = j ;
		}
	}

	function srch2() {
		var text2 = document.getElementById( "i_from_agent" );
		if ( text2.value != "" ) {
			var st = text2.value.toUpperCase();
			var sel2 = document.getElementById( "i_from_agent_list" );
			var count = sel2.options.length ;
			var j = -1 ;
			for ( var i = 0 ; i < count ; i++ ) {
				if ( sel2.options[ i ].text.toUpperCase().indexOf( st ) == 0 ) {
					j = i ;
					break ;
				}
			}

			sel2.selectedIndex = j ;
		}
	}

	function agency_select() {
		var sel1 = document.getElementById( "i_from_agency_list" );
		var text1 = document.getElementById( "i_from_agency" );
		if ( sel1.selectedIndex > -1 ) {
			text1.value = sel1.options[ sel1.selectedIndex ].text ;
			var agency = sel1.options[ sel1.selectedIndex ].value ;
		} else {
			//text1.value = "" ;
			var agency = "-1" ;
		}

		loadAgentList( "agency=" + agency );
	}

	function agent_select() {
		var sel2 = document.getElementById( "i_from_agent_list" );
		var text2 = document.getElementById( "i_from_agent" );
		if ( sel2.selectedIndex > -1 ) {
			text2.value = sel2.options[ sel2.selectedIndex ].text ;
		} else {
			//text2.value = "" ;
		}
	}

	function tc( id ) {
		var el = document.getElementById( "tcel" + id );
		var im = document.getElementById( "tcimg" + id );
		if ( el.style.display == "none" ) {
			el.style.display = "" ;
			im.src = "themes/" + $.userThemeLoc + "/col.bmp" ;
		} else {
			el.style.display = "none" ;
			im.src= "themes/" + $.userThemeLoc + "/exp.bmp" ;
		}
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
			req.open( "POST" , "level1card.from.php" , false );
			req.setRequestHeader( "Accept-Charset" , "windows-1251" );
			req.setRequestHeader( "Accept-Language" , "ru,en" );
			req.setRequestHeader( "Connection" , "close" );
			req.setRequestHeader( "Content-length" , sd.length );
			req.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );
			req.setRequestHeader( "Content-Encoding" , "utf-8" );
			req.send( sd );

			return req.responseText ;
		}
	}

	function loadAgencyList( url ) {
  		var tcel1 = document.getElementById( "tcel1" );
		tcel1.innerHTML = doPOST( url );
	}

	function loadAgentList( url ) {
  		var tcel2 = document.getElementById( "tcel2" );
		tcel2.innerHTML = doPOST( url );
	}
	
	function doUnlink( id ) {
		var res = prompt( "Для подтверждения операции введите слово П О Д Т В Е Р Ж Д А Ю без пробелов" );
		if ( res == "ПОДТВЕРЖДАЮ" ) {
			sendXML( "<unlink id=\"" + id + "\" />" , false , "level1card.ajax.php" );
			window.location.reload();
		} else {
			alert( "Не принято" );
		}
	}
	
	function mkNewLink( src ) {
		var res = prompt( "Укажите номер экспертизы и год в формате xxxxx/yyyy (например: 12345/2012)" );
		if ( res == null ) {
			return ;
		}
		
		var m = res.match( /^\d{1,6}\/\d{4}$/ );
		if ( m == null ) {
			alert( "Не верный формат" );
			return ;
		}
		
		res = res.split( "/" );
		
		var doc = sendXML( "<mklink-get n=\"" + res[ 0 ] + "\" y=\"" + res[ 1 ] + "\" id=\"" + src + "\" />" , false , "level1card.ajax.php" );
		if ( doc.getAttribute( "state" ) == "ok" ) {
			var id = doc.getAttribute( "id" );
			var tid = doc.getAttribute( "tid" );
			var idSum = parseInt( id.substr( -6 ) , 10 ) + parseInt( tid.substr( -6 ) , 10 );
			var res = prompt( "Для подтвержения привязки текущей\r\nкарточки к указанной введите " + idSum + "\r\n" + getXMLNodeValue( doc ) );
			if ( res !== null && parseInt( res , 10 ) == idSum ) {
				var doc = sendXML( "<mklink-do id=\"" + id + "\" tid=\"" + tid + "\" />" , false , "level1card.ajax.php" );
			} else {
				alert( "Не подтверждено" );
			}
		} else {
			alert( "Ошибка" );
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
	
	function lvl1CardSubmit() {
		var cf = document.getElementById( "lvl1CardForm" );
		
		var cc = document.getElementById( "i_case_category" );
		
		if ( cc.value == "" ) {
			alert( "Не указана категория дела !" );
			cc.focus();
			blinkElement( cc.parentNode.parentNode );
			return ;
		}
		
		for( var i = 0 ; i < enia.length ; i++ ) {
			var eni = enia[ i ];
			
			if ( eni.date.value == "" && eni.descr.value == "" ) {
			} else {
				if ( !isValidDate( eni.date.value ) ) {
					alert( "Не верная дата" );
					eni.date.focus();
					return ;
				}
				
				if ( eni.descr.value.length < 4 ) {
					alert( "Слишком короткое описание" );
					eni.descr.focus();
					return ;
				}
			}
		}
		
		cf.submit();
	}


	
	function calcControlDigit() {
		var src = "394009 " ;
		while ( true ) {
			var src = prompt( "Введите часть ШПИ ( 13 знаков )" , src );
			if ( src != null ) {
				var res = src.replace( /\s+/g , "" );
			} else {
				return null ;
			}
			
			if ( res.length == 13 ) {
				break ;
			}
		}
		
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
		
		return res + s ;
	}
	
	var enia = [];
	
	function doAddRow() {
		var tab = document.getElementById( "evtab" );
		var eni = {};
		
		var enid = ( new Date() ).getTime();
		
		var r = tab.insertRow( -1 );
		r.className = "dr-d" ;
		
			var c = r.insertCell( -1 );
			c.id = "" ;
			c.className = "dr-d et-states" ;
			
			var c = r.insertCell( -1 );
			c.id = "" ;
			c.className = "dr-d et-states" ;
			
			var c = r.insertCell( -1 );
			c.id = "" ;
			c.className = "dr-d td" ;
			
				var tmp = document.createElement( "input" );
				tmp.type = "text" ;
				tmp.name = "en_date[" + enid + "]" ;
				tmp.className = "nrr-i-date" ;
			c.appendChild( tmp );
			eni.date = tmp ;
			
			var c = r.insertCell( -1 );
			c.id = "" ;
			c.className = "dr-d tSf" ;
			
				var tmp = document.createElement( "textarea" );
				tmp.className = "nrr-i-descr" ;
				tmp.name = "en_descr[" + enid + "]" ;
			c.appendChild( tmp );
			eni.descr = tmp ;
			
			enia.push( eni );
			
		/*var addBtn = document.getElementById( "evtab-add-btn" );
		addBtn.disabled = true ;*/
	}
	
	function doChangeState( eid , cState ) {
		//alert( eid );
		var text = new Date();
		text = str_pad( text.getDate() , 2 , "0" , STR_PAD_LEFT ) + "-" + str_pad( ( text.getMonth() + 1 ) , 2 , "0" , STR_PAD_LEFT ) + "-" + text.getFullYear() + ", ";
		if ( cState == 1 ) {
			text = prompt( "Укажите данные о выдаче" , text );
			if ( text == null ) {
				return ;
			}
		}
		var doc = sendXML( "<next-evidence-state id=\"" + eid + "\">" + toCDATA( text ) + "</next-evidence-state>" , false , "level1card.ajax.php" );
		if ( doc.getAttribute( "state" ) == "ok" ) {
			var si = document.getElementById( "et-state-icon-" + eid );
			var es = parseInt( doc.getAttribute( "ns" ) , 10 );
			switch( es ) {
				case -2 :
					var sb = "e" ;
					break ;
				case -1 :
					var sb = "w0" ;
					break ;
				case 0 :
					var sb = "w1" ;
					break ;
				case 1 :
					var sb = "r" ;
					break ;
				case 2 :
					var sb = "f" ;
					break ;
			}
			
			var etr = document.getElementById( "evtab-dr" + eid );
			if ( es == 2 ) {
				var c = etr.insertCell( -1 );
				c.id = "" ;
				c.className = "dr-d tSf" ;
					var tmp = document.createElement( "span" );
					tmp.className = "et-state-date" ;
					tmp.appendChild( document.createTextNode( "[ " + doc.getAttribute( "nsd" ) + " ]" ) ); 
				c.appendChild( tmp );
				c.appendChild( document.createTextNode( getXMLNodeValue( doc ) ) );
			} else {
				while ( etr.cells.length > 4 ) {
					etr.deleteCell( -1 );
				}
			}
			
			si.className = "et-state-" + sb ;
			si.onclick = function( x , y ) {
				return function() {
					doChangeState( x , y );
				};
			}( eid , es );
		}
		
	}