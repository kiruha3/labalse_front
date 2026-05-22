/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	/**
	 * @var {object} $
	 */
	/**
	 * @var {string} $.userThemeLoc
	 */
	/**
	 * @var $.tmpl
	 */
	/**
	 * @var $.tmplVar
	 */
	/**
	 * @var $.tmplTargets
	 */
	/**
	 * @var $.typeOfAgency
	 */
	/**
	 * @var {number} $.agencyId
	 */
	/**
	 * @var {number} $.agentId
	 */
	/**
	 * @var {number} $.userId
	 */

	$.windowOnLoad.push( function() {
		var addMark = function ( dlg , el , tmpl ) {
			var markImg ;
			if ( tmpl != null ) {
				markImg = document.createElement( "div" );
				markImg.className = "tmpl-dlg-mark-execute" ;
				markImg.alt = markImg.title = "Нажмите * в полее ввода чтобы выбрать шаблон" ;
				markImg.onclick = el.onkeypress ;
			} else {
				markImg = document.createElement( "div" );
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
				reAssignCallback : changeMark ,
				updateURL : "level1card.ajax.php"
			}
		);
		
		for( var i = 0 ; i < $.tmplTargets.length ; i++ ) {
			dlg.assign( $.tmplTargets[ i ] );
		}
		
		var fel = document.getElementById( "i_case_category" );
		fel.focus();
		
		var aDlgLink = {
			type : "i_from_type_of_agency" ,
			agency : "i_from_agency" ,
			agencyList : "i_from_agency_list" ,
			agent : "i_from_agent" ,
			agentList : "i_from_agent_list"
		};
		var o = $.agentDLG_Data ;
		for( var i in o.link ) {
			aDlgLink[ i ] = o.link[ i ];
		}
		
		$.agentDLG = new $.TDLGAgentSelect( { link : aDlgLink , 'contacts-name-prefix' : 'i_' , 'html-form-post' : true , 'no-select-checkbox' : 1 } , { type : $.typeOfAgency , agencyList : $.agencyId , agentList : $.agentId } );
		
		$.fileUploadDLG = new $.TDLGFileUpload( { 
			tabInputName : "file-upload-tabs" , 
			redirect : false , 
			docTypeList : [
				{ v : "0110" , n : "Заключение" } ,
				{ v : "1010" , n : "Пост/Опред" } ,
				{ v : "0420" , n : "Карт.движ.мат" } ,
				{ v : "0430" , n : "Карт.вещ.док" } ,
				{ v : "0610" , n : "Ход. объект" } ,
				{ v : "0620" , n : "Ход. срок" } ,
				{ v : "0600" , n : "Ход. другое" } ,
				{ v : "1410" , n : "Доп.мат" } ,
				{ v : "0520" , n : "увед. о сроках" } ,
				{ v : "0500" , n : "увед. другое" } ,
				{ v : "0890" , n : "Прочие рапорты" } ,
				{ v : "0910" , n : "Отчет факса" } ,
				{ v : "0990" , n : "Прочие отчеты" } ,
				{ v : "3110" , n : "Счет" },
				{ v : "3114" , n : "Квитанция" },
				{ v : "3190" , n : "прочие документы по оплате" }
			]
		} );
		
		$.searchDlg.checkbox = true ;
	} );

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

	/*function srch() {
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
	}*/

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
		var res ;
		res = prompt( "Укажите номер экспертизы и год в формате xxxxx/yyyy (например: 12345/2012)" );
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
			res = prompt( "Для подтвержения привязки текущей\r\nкарточки к указанной введите " + idSum + "\r\n" + getXMLNodeValue( doc ) );
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
		var cf = document.getElementById( 'lvl1CardForm' );

		var date = document.getElementById( 'i_date' );
		if ( !date.value.match( /^\s*\d{2}[,.-]\d{2}[,.-](\d{2}|\d{4})\s*$/ ) ) {
			alert( 'Заполните поле "Дата поступления материалов" корректно' );
			date.focus();
			blinkElement( date.parentNode.parentNode );
			return ;
		}

		var cc = document.getElementById( 'i_case_category' );
		if ( cc.value == "" ) {
			alert( 'Не указана категория дела !' );
			cc.focus();
			blinkElement( cc.parentNode.parentNode );
			return ;
		}

		var ay = document.getElementById( 'i_from_agency' );
		var ayV = ay.value.trim();
		if ( ayV == '' || ay.length < 3 ) {
			alert( 'Заполните поле "название органа"' );
			ay.focus();
			blinkElement( ay.parentNode.parentNode );
			return ;
		}

		var at = document.getElementById( 'i_from_agent' );
		var atV = at.value.trim();
		if ( atV == '' || at.length < 3 ) {
			alert( 'Заполните поле "Назначивший"' );
			at.focus();
			blinkElement( at.parentNode.parentNode );
			return ;
		}


		for( var i = 0 ; i < enia.length ; i++ ) {
			var eni = enia[ i ];
			
			if ( eni.date.value == "" && eni.descr.value == "" ) {
			} else {
				if ( !isValidDate( eni.date.value ) ) {
					alert( 'Не верная дата' );
					eni.date.focus();
					return ;
				}
				
				if ( eni.descr.value.length < 4 ) {
					alert( 'Слишком короткое описание' );
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
		var tmp ;
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
			
				tmp = document.createElement( "input" );
				tmp.type = "text" ;
				tmp.name = "en_date[" + enid + "]" ;
				tmp.className = "nrr-i-date" ;
			c.appendChild( tmp );
			eni.date = tmp ;
			
			var c = r.insertCell( -1 );
			c.id = "" ;
			c.className = "dr-d tSf" ;
			
				tmp = document.createElement( "textarea" );
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
			var sb ;
			switch( es ) {
				case -2 :
					sb = "e" ;
					break ;
				case -1 :
					sb = "w0" ;
					break ;
				case 0 :
					sb = "w1" ;
					break ;
				case 1 :
					sb = "r" ;
					break ;
				case 2 :
					sb = "f" ;
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
	
	function showFUDlg( id , type ) {
		$.fileUploadDLG.show( id , type );
	}