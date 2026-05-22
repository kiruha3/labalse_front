	
	$.windowOnLoad.push( function() {
		upd();
		
		PDFJS.workerSrc = '/ext-lib/pdf.js/build/pdf.worker.js';
		var ppo = {
			dlg : document.getElementById( "fu-dlg" ) ,
			tab1i : document.getElementById( "fu-tab-1" ) ,
			tab2i : document.getElementById( "fu-tab-2" ) ,
			lal : document.getElementById( "fu-tlal" ) ,
			tgt : document.getElementById( "fu-ppa" ),
			tl : document.getElementById( "fu-tl" ) ,
			fsf : document.getElementById( "fu-file-select-form" ) ,
			fcid : document.getElementById( "fu-cor-id" ),
			ab : document.getElementById( "fu-attache-btn" ) ,
			tc : []
		};
		
		var fuDlgBG = document.getElementById( "fu-dlg-bg" );
		fuDlgBG.onscroll = function() {
			return false ;
		};
		
		$.pdfPreview = ppo ;
		document.getElementById( "fu-pa-sizer" ).style.marginRight = ( $.scrollbarSize.w + 1 ) + "px" ;
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
			tgt = "nrr-agency-sel" ;
		}
		
		tgt = document.getElementById( tgt );
		
		loadAgencyList( "toa=" + toa , tgt );
		
		return toa ;
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
			req.open( "POST" , "subpoenas.from.php" , false );
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
			var al = document.getElementById( "nrr-agency-sel" );
		} else {
			var al = tgt ;
		}
		
		al.innerHTML = doPOST( url );
	}
	
	// url : "agency=n"
	// tgt : agent sel
	function loadAgentList( url , tgt ) {
		if ( typeof tgt === "undefined" ) {
			var al = document.getElementById( "nrr_agent_sel" );
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
			var tgt3 = "nrr-agent-sel" ;
		}
		
		if ( typeof tgt2 === "undefined" ) {
			var tgt2 = "nrr-agency-ta" ;
		}
		
		if ( typeof tgt === "undefined" ) {
			var tgt = "nrr-agency-sel" ;
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
			//getAgencyAddress( "aa=" + agency );
			getAgencyAddress( "aaa=" + agency );
		}
	}
	
	function agent_select() {
		var al = document.getElementById( "nrr_agent_sel" );
		var at = document.getElementById( "nrr_agent_ta" );
		if ( al.selectedIndex > -1 ) {
			at.value = al.options[ al.selectedIndex ].text ;
		} else {
			//at.value = "" ;
		}
	}


	function srch( tgt , tgt2 ) {
		if ( typeof tgt2 === "undefined" ) {
			var tgt2 = "nrr-agency-ta" ;
		}
		
		if ( typeof tgt === "undefined" ) {
			var tgt = "nrr-agency-sel" ;
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
		var at = document.getElementById( "nrr_agent_ta" );

		if ( at.value != "" ) {
			var st = at.value.toUpperCase();
			var al = document.getElementById( "nrr_agent_sel" );
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
		var aa = document.getElementById( "nrr-from-agency-address" );

		var aList = doPOST( url );
		aList = aList.split( "\r\n" );
		aa.innerHTML = aList[ 0 ];
	}
	
	function fillAddress() {
		var aa = document.getElementById( "nrr-from-agency-address" );
		var ata = document.getElementById( "nrr-from-agency-alt-address" );
		ata.value = aa.innerHTML ;
	}
	
	function checkInput( el , t , v , mt ) {
		var m = true ;
		var msg = "" ;
		switch ( t ) {
			case "i" :
				m = el.value.match( /^\s*(\d+)\s*$/ );
				m = ( m == null || m.length != 2 );
				msg = "Неверный формат числа" ;
				break ;
			case "d" :
				m = el.value.match( /^\s*([0-2]\d|3[0-1])[.,-](0\d|1[0-2])[.,-](?:20)?(\d{2})\s*$/ );
				m = ( m == null || m.length != 4 );
				msg = "Неверный формат даты" ;
				break ;
			case "t" :
				m = el.value.match( /^\s*([0-1]\d|2[0-3])[.,-\:]([0-5]\d)\s*$/ );
				m = ( m == null || m.length != 3 );
				msg = "Неверный формат времени" ;
				break ;
			case "v" :
				if ( v instanceof RegExp ) {
					m = el.value.match( v );
					m = ( m == null );
				} else {
					m = !( el.value == v );
				}
				msg = mt ;
				break ;
			case "V" :
				if ( v instanceof RegExp ) {
					m = el.value.match( v );
					m = !( m == null );
				} else {
					m = ( el.value == v );
				}
				msg = mt ;
				break ;
		}
		
		if ( m ) {
			alert( msg );
			el.scrollIntoView();
			blinkElement( [ el ] );
			el.focus();
			return false ;
		} else {
			if ( t == "i" || t == "d" || t == "t" ) {
				el.value = el.value.trim();
			}
			return true ;
		}
	}
	
	function isArray( o ) {
		if( Object.prototype.toString.call( o ) === "[object Array]" ) {
			return true ;
		} else {
			return false ;
		}
	}
	
	function blinkElement( f ) {
		blinkTimer = setInterval( function( x ){
			var blinkTimer = null ;
			var blinkFase = 0 ;
			var blinkIterations = 18 ;

			return function() {
				if ( isArray( x ) ) {
					for( var i = 0 ; i < x.length ; i++ ) {
						if ( blinkFase < blinkIterations ) {
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
					if ( blinkFase++ < blinkIterations ) {
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
		}( f ) , 50 );
	}
	
	function showAddNRRDlg() {
		var dlg = document.getElementById( "nrr-dlg" );
		
		var d = document.getElementById( "nrr-date" );
		d.value = d.defaultValue ;
		var td = document.getElementById( "nrr-to-date" );
		td.value = td.defaultValue ;
		var tt = document.getElementById( "nrr-to-time" );
		tt.value = tt.defaultValue ;
		
		var toa = document.getElementById( "nrr-toa" );
		toa.value = upd();
		
		
		var fa = document.getElementById( "nrr-from-agency" );
		fa.value = fa.defaultValue ;
		
		var aa = document.getElementById( "nrr-from-agency-address" );
		aa.innerHTML = "" ;
		var ata = document.getElementById( "nrr-from-agency-alt-address" );
		ata.value = ata.defaultValue ;
		
		var btn = document.getElementById( "nrr-lnk-ok" );
		btn.onclick = function() {
			return function() {
				doStoreNRR( "add" );
			};
		} ();
		btn.style.display = "" ;

		dlg.style.display = "" ;
	}
	
	function showEditNRRDlg( id ) {
		var dlg = document.getElementById( "nrr-dlg" );
		dlg.style.display = "none" ;
		
		var doc = sendXML( "<get-subpoena id=\"" + id + "\" />" , false , 'subpoenas.php' );
		
		var ops = [];
		for( var i = 0 ; i < doc.childNodes.length ; i++ ) {
			var chn = doc.childNodes[ i ];
			switch( chn.nodeName ) {
				default :
					ops[ chn.nodeName ] = getXMLNodeValue( chn );
					break ;
			}
		}
		
		var num = document.getElementById( "nrr-num" );
		num.innerHTML = doc.getAttribute( "num" );
		var d = document.getElementById( "nrr-date" );
		d.value = doc.getAttribute( "d" );
		var td = document.getElementById( "nrr-to-date" );
		td.value = doc.getAttribute( "td" );
		var tt = document.getElementById( "nrr-to-time" );
		tt.value = doc.getAttribute( "tt" );
		
		var t = document.getElementById( "nrr-type" );
		t.value = doc.getAttribute( "t" );
		
		var toa = document.getElementById( "nrr-toa" );
		toa.value = doc.getAttribute( "toa" );
		
		upd( "nrr-agency-sel" , "nrr-toa" );
		
		
		
		var fa = document.getElementById( "nrr-from-agency" );
		var as = document.getElementById( "nrr-agency-sel" );
		selectItemByValue( as , doc.getAttribute( "ay" ) );
		fa.value = ops[ "agency" ];
		
		var aa = document.getElementById( "nrr-from-agency-address" );
		aa.innerHTML = "" ;
		var ata = document.getElementById( "nrr-from-agency-alt-address" );
		ata.value = ops[ "addr" ];
		
		var cba = document.getElementsByName( "nnren[]" );
		var cbarm = {};
		for( var i = 0 ; i < cba.length ; i++ ) {
			cbarm[ "e" + cba[ i ].value ] = cba[ i ];
			cba[ i ].checked = false ;
		}
		
		var el = doc.getAttribute( "exp" ).split( "," );
		for( var i = 0 ; i < el.length ; i++ ) {
			cbarm[ "e" + el[ i ] ].checked = true ;
		}
		
		var btn = document.getElementById( "nrr-lnk-ok" );
		if ( doc.getAttribute( "mc" ) == "1" ) {
			btn.onclick = function( x ) {
				return function() {
					doStoreNRR( "edit" , x );
				};
			} ( id );
			btn.style.display = "" ;
		} else {
			alert( "Нельзя изменить повестку если эксперт(ы) уже указали стоимость выхода в суд." );
			btn.style.display = "none" ;
		}
		
		
		dlg.style.display = "" ;
	}
	
	
	function hideAddNRRDlg() {
		var dlg = document.getElementById( "nrr-dlg" );
		dlg.style.display = "none" ;
	}
	
	/*function doAddNRR() {
		var cba = document.getElementsByName( "nnren[]" );
		var te = [];
		for( var i = 0 ; i < cba.length ; i++ ) {
			if ( cba[ i ].checked ) {
				te.push( cba[ i ].value );
			}
		}
		
		var d = document.getElementById( "nrr-date" );
		var td = document.getElementById( "nrr-to-date" );
		var tt = document.getElementById( "nrr-to-time" );
		var dt = document.getElementById( "nrr-type" );
		var elt = document.getElementById( "nrr-elt" );
		var fa = document.getElementById( "nrr-from-agency" );
		var as = document.getElementById( "nrr-agency-sel" );
		var aa = document.getElementById( "nrr-from-agency-alt-address" );
		
		var cs = true ;
		
		var ca = [ [ d , "d" ] , [ td , "d" ] , [ tt , "t" ] , [ fa , "V" , /^\s*$/ , "Не указан орган" ] , [ as , "V" , /^\s*$/ , "Орган не выбран из списка" ] , [ aa , "V" , /^\s*$/ , "Не указан адрес" ] ];
		var cr = true ;
		for( var i = 0 ; i < ca.length ; i++ ) {
			cr = cr && checkInput.apply( null , ca[ i ] );
			if ( !cr ) {
				return ;
			}
		}		
				
		if ( te.length < 1 ) {
			alert( "Выберите эксперта" );
			blinkElement( elt );
			cs = false ;
			return ;
		}
		
		if ( te.length > 5 ) {
			alert( "Слишком много экспертов" );
			blinkElement( elt );
			cs = false ;
			return ;
		}
		
		if ( as.selectedIndex == -1 ) {
			alert( "Орган не выбран из списка" );
			blinkElement( as );
			cs = false ;
			return ;
		}
		
		var doc = sendXML( "<add-subpoena d=\"" + d.value + "\" td=\"" + td.value + "\" tt=\"" + tt.value + "\" dt=\"" + dt.value + "\" as=\"" + as.value + "\" te=\"" + te.join( "," ) + "\">" + toCDATA( aa.value ) + "</add-subpoena>" , false );
		
		hideAddNRRDlg();
		window.location.reload( true );
	}*/
	
	
	function doStoreNRR( mode , sid ) {
		var cba = document.getElementsByName( "nnren[]" );
		var te = [];
		for( var i = 0 ; i < cba.length ; i++ ) {
			if ( cba[ i ].checked ) {
				te.push( cba[ i ].value );
			}
		}
		
		var d = document.getElementById( "nrr-date" );
		var td = document.getElementById( "nrr-to-date" );
		var tt = document.getElementById( "nrr-to-time" );
		var dt = document.getElementById( "nrr-type" );
		var elt = document.getElementById( "nrr-elt" );
		var fa = document.getElementById( "nrr-from-agency" );
		var as = document.getElementById( "nrr-agency-sel" );
		var aa = document.getElementById( "nrr-from-agency-alt-address" );
		
		var cs = true ;
		
		var ca = [ [ d , "d" ] , [ td , "d" ] , [ tt , "t" ] , [ fa , "V" , /^\s*$/ , "Не указан орган" ] , [ as , "V" , /^\s*$/ , "Орган не выбран из списка" ] , [ aa , "V" , /^\s*$/ , "Не указан адрес" ] ];
		var cr = true ;
		for( var i = 0 ; i < ca.length ; i++ ) {
			cr = cr && checkInput.apply( null , ca[ i ] );
			if ( !cr ) {
				return ;
			}
		}		
				
		if ( te.length < 1 ) {
			alert( "Выберите эксперта" );
			blinkElement( elt );
			cs = false ;
			return ;
		}
		
		if ( te.length > 5 ) {
			alert( "Слишком много экспертов" );
			blinkElement( elt );
			cs = false ;
			return ;
		}
		
		if ( as.selectedIndex == -1 ) {
			alert( "Орган не выбран из списка" );
			blinkElement( as );
			cs = false ;
			return ;
		}
		
		switch( mode ) {
			case "add" :
				var doc = sendXML( "<add-subpoena d=\"" + d.value + "\" td=\"" + td.value + "\" tt=\"" + tt.value + "\" dt=\"" + dt.value + "\" as=\"" + as.value + "\" te=\"" + te.join( "," ) + "\">" + toCDATA( aa.value ) + "</add-subpoena>"  , false , 'subpoenas.php' );
				window.location.reload( true );
				break ;
				
			case "edit" :
				var doc = sendXML( "<change-subpoena id=\"" + sid + "\" d=\"" + d.value + "\" td=\"" + td.value + "\" tt=\"" + tt.value + "\" dt=\"" + dt.value + "\" as=\"" + as.value + "\" te=\"" + te.join( "," ) + "\">" + toCDATA( aa.value ) + "</change-subpoena>" , false , 'subpoenas.php' );
				window.location.reload( true );
				break ;
		}
		
		hideAddNRRDlg();

	}
	
	
	
	
	
	
	
	
	
	
	
	function doAttacheFile( fn , id ) {
		var ppo = $.pdfPreview ;
		
		if ( ppo.tab1i.checked ) {
			if ( fn == null ) {
				alert( "Файл не выбран" );
				return ;				
			}
			var doc = sendXML( "<link-file id=\"" + id + "\">" + toCDATA( fn ) + "</link-file>"  , false , 'subpoenas.php' );
			if ( doc.getAttribute( "state" ) == "error" ) {
				alert( getXMLNodeValue( doc ) );
				return ;
			}
		} else
		if ( ppo.tab2i.checked ) {
			ppo.fcid.value = id ;
			ppo.fsf.submit();
			return ;
		}
						
		hideFUDlg();
		window.location.reload( true );
	}
	
	function showFUDlg( id ) {
		var ppo = $.pdfPreview ;
		var tl = ppo.tl ;
		var tll = tl.childNodes ;
		var tlll = tll.length ;
		
		var doc = sendXML( "<get-files-to-upload />" , false , 'subpoenas.php' );
		var fnl = doc.childNodes ;
		var fnll = fnl.length ;
		
		for( var i = tlll ; i < fnll ; i++ ) {
			var nlnk = document.createElement( "a" );
			nlnk.className = "fu-tgt-lnk" ;
			tl.appendChild( nlnk );
		}
		
		var tlll = tll.length ;
		for( var i = fnll ; i < tlll ; i++ ) {
			tll[ i ].style.display = "none" ;
		}		
		
		for( var i = 0 ; i < fnll ; i++ ) {
			var clnk = tll[ i ];
			clnk.style.display = "" ;
			var clnkT = getXMLNodeValue( fnl[ i ] );
			setText( clnk , clnkT );
			clnk.onclick = function( x , y ) {
				return function() {
					var ppo = $.pdfPreview ;
					showPDF( x , y );
				};
			}( clnkT , id );
		}
		
		ppo.ab.onclick = function( x , y ) {
			return function() {
				doAttacheFile( x , y );
			};
		}( null , id );
		
		ppo.dlg.style.display = "" ;
		
		/*document.onmousewheel=document.onwheel=function(){ 
			return false;
		};
		document.addEventListener("MozMousePixelScroll",function(){return false;},false);
		document.onkeydown=function(e) {
			if (e.keyCode>=33&&e.keyCode<=40) return false;
		};*/
	}
	
	function hideFUDlg() {
		var ppo = $.pdfPreview ;
		ppo.dlg.style.display = "none" ;
	}
	
	function drawPDFPage( pdf , pn , pw , c , f ) {
		if ( pdf.numPages >= pn ) {
			pdf.getPage( pn ).then(
				function ( pageWidth , canvas , func ) {
					return function ( page ) {
						var viewport = page.getViewport( 1.0 );
						var scale = pageWidth / viewport.width ;
						var viewport = page.getViewport( scale );
						
						var context = canvas.getContext( "2d" );
						canvas.width = viewport.width ;
						canvas.height = viewport.height ;
						context.clearRect( 0 , 0 , canvas.width , canvas.height );
						
						var renderContext = {
							canvasContext : context ,
							viewport : viewport
						};
						
						if ( func != null ) {
							page.render( renderContext ).then( func );	
						} else {
							page.render( renderContext );
						}
					};
				}( pw , c , f )
			);
		}
	}
	
	function drawPageFinished( pn , n , pdf ) {
		var ppo = $.pdfPreview ;
		var tc = ppo.tc ;
		if ( tc[ n ] ) {
			tc[ n ].r = true ;
		}
		
		var all = true ;
		for( var i = 0 ; i < pn ; i++ ) {
			all = all & tc[ i ].r ;
		}
		
		if ( all ) {
			ppo.lal.style.display = "none" ;
			pdf.cleanup();
			pdf.destroy();
		} else {
			
		}
	}
	
	function showPDF( fn , id ){
		var ppo = $.pdfPreview ;
		ppo.fn = fn ;
		ppo.ab.onclick = function( x , y ) {
			return function() {
				doAttacheFile( x , y );
			};
		}( fn , id );
		//fn = atob( fn );
		ppo.lal.style.display = "" ;
		PDFJS.getDocument( "/maindb/ED/NP_/" + fn ).then( function ( pdf ) {
			var pn = pdf.numPages ;
			var tc = ppo.tc ;
			for( var i = tc.length + 1 ; i <= pn ; i++ ) {
				var cd = document.createElement( "div" );
				var cc = document.createElement( "canvas" );
				cd.appendChild( cc );
				cd.style.marginRight = ( $.scrollbarSize.w + 1 ) + "px" ;
				ppo.tgt.appendChild( cd );
				tc.push( {
					c : cc ,
					d : cd
				} );
			}
			for( var i = 0 ; i < tc.length ; i++ ) {
				tc[ i ].d.style.display = "none" ;
			}
			
			for( var i = 0 ; i < pn ; i++ ) {
				tc[ i ].d.style.display = "" ;
				tc[ i ].r = false ;
				drawPDFPage( pdf , i + 1 , 720 , tc[ i ].c , function( x , y , z ) {
					return function() {
						drawPageFinished( x , y , z );
					};
				}( pn , i , pdf ) );
			}
			
			ppo.tgt.scrollTop = 0 ;			
		} );
	};


	var letter_dlg__selected_mat_id = null ;

	function showLetterDlg( event , id ) {
		event = window.event ? window.event : event ;
		event.target = event.target ? event.target : event.srcElement ;
		if( event.stopPropagation ) {
			event.stopPropagation();
		} else {
			event.cancelBubble = true ;
		}

		letter_dlg__selected_mat_id = id ;

		var lf = getCookie( 'labelFormat' );
		var labelFormat = document.getElementById( 'labelFormat' );
		labelFormat.value = lf ;

		var letterDlg = document.getElementById( 'letter_dlg' );

		letterDlg.style.display = 'none' ;

		var ldt = document.getElementById( 'letter_dlg_tab' );
		while ( ldt.rows.length > 0 ) {
			ldt.deleteRow( 0 );
		}

		var r = ldt.insertRow( -1 );
		addTabCell( r , 'ldt-cap' );
		addTabCell( r , 'ldt-cap' , 'Кому' );
		addTabCell( r , 'ldt-cap' , 'Куда' );

		var doc = sendXML( "<get-subpoena id=\"" + id + "\" />" , false , 'subpoenas.php' );
		var ops = [];
		for( var i = 0 ; i < doc.childNodes.length ; i++ ) {
			var chn = doc.childNodes[ i ];
			switch( chn.nodeName ) {
				default :
					ops[ chn.nodeName ] = getXMLNodeValue( chn );
					break ;
			}
		}

		var r = ldt.insertRow( -1 );
		var c = addTabCell( r , 'ldt-prna-btn' );
		var inp = document.createElement( 'a' );
		inp.className = '' ;
		inp.onclick = function( id , ct ) {
			return function() {
				printLetterLabel( id );
			};
		} ( id );
		var img = document.createElement( 'div' );
		img.className = 'ldt-prna-img' ;
		inp.appendChild( img );
		c.appendChild( inp );
		addTabCell( r , 'ldt-addressee' , ops[ "agency" ] );
		addTabCell( r , 'ldt-destination' , ops[ "addr" ] );

		var w = document.getElementById( 'new-weight' );
		w.value = '' + ( wbp1 / 1000 ).toFixed( 3 );
		var p = document.getElementById( 'new-price' );
		p.value = bp1.toFixed( 2 ) + ' + 0.00' ;


		letterDlg.style.display = '' ;
	}

	function hideLetterDlg() {
		var ld = document.getElementById( 'letter_dlg' );
		ld.style.display = 'none' ;
	}

	function labelFormatChange() {
		var lf = document.getElementById( 'labelFormat' );
		setCookie( 'labelFormat' , lf.value , 1000  );
	}

	function printLetterLabel( id ) {
		var w = document.getElementById( 'new-weight' );
		var lt = document.getElementById( 'new-letter-type' );
		var pr = getPrices( w.value , lt.checked );


		var lData = sendXML( '<get-letter-data id="' + id + '" p1="' + pr.p1.toFixed( 2 ) + '" p2="' + pr.p2.toFixed( 2 ) + '" w="' + pr.w + '"/>' , false , 'subpoenas.letters.php' );

		var lAddresseeNode = null ;
		var lDestinationNode = null ;

		for( var j = 0 ; j < lData.childNodes.length ; j++ ) {
			switch ( lData.childNodes[ j ].nodeName ) {
				case 'addressee' :
					lAddresseeNode = lData.childNodes[ j ];
					break ;
				case 'destination' :
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

	var bp1 = 17.00 ;
	var bp2 = [ [ 17.00 , 37.00 ] , [ 35.00 , 50.00 ] ];

	var ws = 20 ;
	var wbp1 = 20 ;
	var wlt = 100 ;

	var ps = 2.0 ;

	function getPrices( w , t ) {
		w = '' + w ;
		var wm = w.match( /^\d*(?:[.,]\d{0,3})?$/ );
		if ( wm == null ) {
			return {
				s : 1
			};
		}

		wm = w.match( /^\d*[.,]$/ );
		if ( wm != null ) {
			w = w + '0' ;
		}

		wm = w.match( /^[.,]\d{0,3}$/ );
		if ( wm != null ) {
			w = '0' + w ;
		}

		w = Math.round( parseFloat( w.replace( ',' , '.' ) ) * 1000 );
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

	function changeWeight() {
		var w = document.getElementById( 'new-weight' );
		var lt = document.getElementById( 'new-letter-type' );
		var p = document.getElementById( 'new-price' );

		var pr = getPrices( w.value , lt.checked );
		if ( isNaN( pr.w ) ) {
			alert( 'Вес указан не правильно' );
			return ;
		}
		p.value = pr.p1.toFixed( 2 ) + " + " + pr.p2.toFixed( 2 );
	}

	function addTabCell( tr , cn , text ) {
		if ( typeof( text ) == 'undefined' ) {
			text = '' ;
		}

		var c = document.createElement( 'td' );
		c.className = cn ;
		setText( c , text );
		tr.appendChild( c );
		return c ;
	}
