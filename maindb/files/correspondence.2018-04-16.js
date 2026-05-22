	
	$.windowOnLoad.push( function() {
		//upd();
		
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
				markImg.onclick = function( x ,y ) {
					return function( event ) {
						dlg.create( x , y );
						/*return processInput( event , x );*/
					};
				}( el , markImg );
			}
			
			var tmp = document.createElement( "div" );
			tmp.className = "nrr-field-wrapper" ;
			el.parentNode.replaceChild( tmp , el );
			
			var tmp2 = document.createElement( "div" );
			tmp2.appendChild( el );
			
			tmp.appendChild( tmp2 );
			tmp.appendChild( markImg );
		};
		
		var changeMark = function ( dlg , el , tmpl ) {
			if ( tmpl != null ) {
				var markImg = document.createElement( "div" );
				markImg.className = "tmpl-dlg-mark-execute" ;
				markImg.alt = markImg.title = "Нажмите * в полее ввода чтобы выбрать шаблон" ;
				markImg.onclick = el.onkeypress ;
			}
			
			var pel = el.parentNode ;
			pel.parentNode.replaceChild( markImg , pel.nextElementSibling );
		};
		
		var dlg = new $.TDLGInputTemplate(
			$.tmpl ,
			{
				autoAssign : false ,
				variables : $.tmplVar ,
				assignCallback : addMark ,
				reAssignCallback : changeMark ,
				updateURL : "correspondence.php?view=" + $.tmplUpdateURL
			}
		);
		
		for( var i = 0 ; i < $.tmplTargets.length ; i++ ) {
			dlg.assign( $.tmplTargets[ i ] );
		}
		
		$.dlgTmpl = dlg ;
		
		
		PDFJS.workerSrc = '/ext-lib/pdf.js/build/pdf.worker.js' ;
		var ppo = {
			dlg : document.getElementById( "fu-dlg" ) ,
			tab1i : document.getElementById( "fu-tab-1" ) ,
			tab2i : document.getElementById( "fu-tab-2" ) ,
			lal : document.getElementById( "fu-tlal" ) ,
			tgt : document.getElementById( "fu-ppa" ),
			tl : document.getElementById( "fu-tl" ) ,
			fsf : document.getElementById( "fu-file-select-form" ) ,
			fcid : document.getElementById( "fu-cor-id" ),
			fcy : document.getElementById( "fu-cor-y" ),
			fcn : document.getElementById( "fu-cor-n" ),
			ab : document.getElementById( "fu-attache-btn" ) ,
			tc : []
		};
		
		var fuDlgBG = document.getElementById( "fu-dlg-bg" );
		fuDlgBG.onscroll = function() {
			return false ;
		};
		
		$.pdfPreview = ppo ;
		document.getElementById( "fu-pa-sizer" ).style.marginRight = ( $.scrollbarSize.w + 1 ) + "px" ;
		
		$.agentDLG = new $.TDLGAgentSelect( { link : {
			type : "nrr-toa" ,
			agency : "nrr-from-agency" ,
			agencyList : "nrr-agency-sel" ,
			agent : "nrr-from-agent" ,
			agentList : "nrr-agent-sel" ,
			//agencyAddress : "agencyAddress" ,
			contactsList : "nrr-addressee-contacts-tab" ,
			contactsListCtrl1 : "nrr-addressee-contacts-tab-ctrl1" ,
			contactsListCtrl2 : "nrr-addressee-contacts-tab-ctrl2" ,
			contactsListCtrl3 : "nrr-addressee-contacts-tab-ctrl3" ,
			contactsListCtrl4 : "nrr-addressee-contacts-tab-ctrl4" ,
			contactsListCtrl5 : "nrr-addressee-contacts-tab-ctrl5"
			//contactsListCtrlPanel : "contactsListCtrlPanel"
		} } /*, { type : $.typeOfAgency , agencyList : $.agencyId , agentList : $.agentId } */);
		
		//var dlg = new $.TDLGAgentSelect();
	} );
	
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
			req.open( "POST" , "correspondence.from.php" , false );
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
	
	function doPOSTretXML( params ) {
		var sd = params + "&random=" + ( new Date() ).getTime() + ( Math.random() * 1000 );
		var req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( "Microsoft.XMLHTTP" );
		}

		if ( req ) {
			req.open( "POST" , "correspondence.from.php" , false );
			req.setRequestHeader( "Accept-Charset" , "windows-1251" );
			req.setRequestHeader( "Accept-Language" , "ru,en" );
			req.setRequestHeader( "Connection" , "close" );
			req.setRequestHeader( "Content-length" , sd.length );
			req.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );
			req.setRequestHeader( "Content-Encoding" , "utf-8" );
			req.send( sd );
			//alert( req.responseText );
			return req.responseXML.documentElement ;
		}
	}

	var agencyAllContacts = [];
	function loadAgencyContacts( url ) {
		agencyAllContacts = [];
		var cl = doPOSTretXML( url );
		for( var i = 0 ; i < cl.childNodes.length ; i++ ) {
			var cc = cl.childNodes[ i ];
			
			agencyAllContacts.push( {
				k : "" ,
				v : getXMLNodeValue( cc )
			} );
		}
	}
	
	function getAgencyAddress( url ) {
		/*var aa = document.getElementById( "nrr-from-agency-address" );
		aa.innerHTML = doPOST( url );*/
	}
	
	function getAgentContacts( url ) {
		var act = document.getElementById( "nrr-addressee-contacts-tab" );
		while( act.rows.length > 0 ) {
			act.deleteRow( 0 );
		}
		
		var cl = doPOSTretXML( url );
		for( var i = 0 ; i < cl.childNodes.length ; i++ ) {
			var cc = cl.childNodes[ i ];
			var ccTag = doAddContact( cc.getAttribute( "t" ) , getXMLNodeValue( cc ) );
			if ( cc.getAttribute( "a" ) != 1 ) {
				ccTag.enabled = false ;
			}
		}
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
				m = el.value.match( /^\s*([0-2]\d|3[0-1])[.,-](0\d|1[0-2])[.,-](?:20)?(1\d)\s*$/ );
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
			case "e" :
				
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
	
	function blinkElement( f ) {
		blinkTimer = setInterval( function( x ){
			var blinkTimer = null ;
			var blinkFase = 0 ;
			var blinkIterations = 10 ;

			return function() {
				if ( isArray( x ) ) {
					for( var i = 0 ; i < x.length ; i++ ) {
						if ( blinkFase < blinkIterations ) {
							if ( blinkFase % 2 == 0 ) {
								x[ i ].style.backgroundColor = "" ;
							} else {
								x[ i ].style.backgroundColor = "#f00" ;
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
							x.style.backgroundColor = "#f00" ;
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
	
	function showAddNRRDlg( viewName ) {
		var dlg = document.getElementById( "nrr-dlg" );
		
		var d = document.getElementById( "nrr-date" );
		d.value = d.defaultValue ;
		var td = document.getElementById( "nrr-ext-date" );
		td.value = td.defaultValue ;
		var tn = document.getElementById( "nrr-ext-num" );
		tn.value = tn.defaultValue ;
		
		var cn = document.getElementById( "nrr-name" );
		cn.value = cn.defaultValue ;
		var cd = document.getElementById( "nrr-desc" );
		cd.value = cd.defaultValue ;

		
		//var toa = document.getElementById( "nrr-toa" );
		$.agentDLG.listSelect( 1 , "type" );
		//toa.value = upd();
		
		
		var fa = document.getElementById( "nrr-from-agency" );
		fa.value = fa.defaultValue ;
		var fa = document.getElementById( "nrr-from-agent" );
		fa.value = fa.defaultValue ;
		
		addresseeList = [];
		var alt = document.getElementById( "nrr-addressee-list-tab" );
		while( alt.rows.length > 0 ) {
			alt.deleteRow( -1 );
		}
		
		var cba = document.getElementsByName( "nrr-marks[]" );
		checkByList( cba , [] );
		
		var act = document.getElementById( "nrr-addressee-contacts-tab" );
		while( act.rows.length > 0 ) {
			act.deleteRow( -1 );
		}
				
		var cba = document.getElementsByName( "nrren[]" );
		checkByList( cba , [] );

		
		var act = document.getElementById( "nrr-addressee-contacts-tab" );
		while( act.rows.length > 0 ) {
			act.deleteRow( -1 );
		}
				
		var btn = document.getElementById( "nrr-lnk-ok" );
		btn.onclick = function( x ) {
			return function() {
				doStoreNRR( "add" , x );
			};
		} ( viewName );
		btn.style.display = "" ;

		dlg.style.display = "" ;
	}
	
	function checkByList( cb , l ) {
		var cbarm = {};
		for( var i = 0 ; i < cb.length ; i++ ) {
			cbarm[ "e" + cb[ i ].value ] = cb[ i ];
			cb[ i ].checked = false ;
		}
		
		for( var i = 0 ; i < l.length ; i++ ) {
			if ( ( "e" + l[ i ] ) in cbarm ) {
				cbarm[ "e" + l[ i ] ].checked = true ;
			}
		}
	}
	
	function showEditNRRDlg( id , viewName , copy ) {
		if ( typeof copy === "undefined" ) {
			copy = false ;
		}
		
		var dlg = document.getElementById( "nrr-dlg" );
		dlg.style.display = "none" ;
		
		var doc = sendXML( "<get-correspondence id=\"" + id + "\" />" , false , "correspondence.php" , "view=" + viewName );
		
		var t = document.getElementById( "nrr-type" );
		selectItemByValue( t , doc.getAttribute( "t" ) );
		
		var nodes = {};
		for( var i = 0 ; i < doc.childNodes.length ; i++ ) {
			var cdn = doc.childNodes[ i ];
			nodes[ cdn.nodeName ] = cdn ;
		}
		
		var d = document.getElementById( "nrr-date" );
		d.value = nodes[ "num" ].getAttribute( "d" );
		var num = document.getElementById( "nrr-num" );
		setText( num , getXMLNodeValue( nodes[ "num" ] ) );
		
		var eDate = document.getElementById( "nrr-ext-date" );
		eDate.value = nodes[ "ext" ].getAttribute( "d" );
		var eNum = document.getElementById( "nrr-ext-num" );
		eNum.value = getXMLNodeValue( nodes[ "ext" ] );
		
		var name = document.getElementById( "nrr-name" );
		name.value = getXMLNodeValue( nodes[ "name" ] );
		var desc = document.getElementById( "nrr-desc" );
		desc.value = getXMLNodeValue( nodes[ "desc" ] );
		
		var pn = document.getElementById( "nrr-page-num" );
		pn.value = doc.getAttribute( "pn" );
		var an = document.getElementById( "nrr-att-num" );
		an.value = doc.getAttribute( "an" );
		
		addresseeList = [];
		var alt = document.getElementById( "nrr-addressee-list-tab" );
		while( alt.rows.length > 0 ) {
			alt.deleteRow( 0 );
		}
		
		var alcn = nodes[ "addressee-list" ].childNodes ;
		for( var i = 0 ; i < alcn.length ; i++ ) {
		
			var addresseeData = {
				uid : generateGUID() ,
				agencyType : 1 ,
				orgName : "" ,
				name : "" ,
				contacts : [] ,
				contactsMap : {}
			};
			var tcl = [];
			
			for( var j = 0 ; j < alcn[ i ].childNodes.length ; j++ ) {
				var acn = alcn[ i ].childNodes[ j ];
				switch ( acn.nodeName ) {
					case "agency" :
						addresseeData.agencyType = acn.getAttribute( "toa" );
						addresseeData.orgName = getXMLNodeValue( acn );
						break ;
					case "agent" :
						addresseeData.name = getXMLNodeValue( acn );
						break ;
					case "contacts" :
						for( var k = 0 ; k < acn.childNodes.length ; k++ ) {
							var ccn = acn.childNodes[ k ];
							cc = {
								id : "nrr-contact-" + generateGUID() ,
								type : ccn.getAttribute( "t" ) ,
								value : getXMLNodeValue( ccn ) ,
								useForReply : ccn.getAttribute( "ufr" ) ,
								state : ccn.getAttribute( "s" ) ,
								stateDate : ccn.getAttribute( "sd" )
							};
							
							if ( copy ) {
								cc.state =  0 ;
								cc.stateDate = "" ;
							} else {
								cc.state = ccn.getAttribute( "s" );
								cc.stateDate = ccn.getAttribute( "sd" );
							}
							
							addresseeData.contacts.push( cc );
							addresseeData.contactsMap[ cc.id ] = cc ;
							
							if ( cc.useForReply == 1 ) {
								tcl.push( cc.value );
							}
						}
						break ;
				}
			}
			
			mkAddresseeRow( addresseeData , tcl );
			addresseeList.push( addresseeData );
		}
		
		var cba = document.getElementsByName( "nrr-marks[]" );
		var el = doc.getAttribute( "marks" ).split( "," );
		checkByList( cba , el );
		
		var act = document.getElementById( "nrr-addressee-contacts-tab" );
		while( act.rows.length > 0 ) {
			act.deleteRow( 0 );
		}
				
		var cba = document.getElementsByName( "nrren[]" );
		var el = doc.getAttribute( "te" ).split( "," );
		checkByList( cba , el );		

		
		var btn = document.getElementById( "nrr-lnk-ok" );
		if ( 1 ) {
			
			if ( copy ) {
				btn.onclick = function( x ) {
					return function() {
						doStoreNRR( "add" , x );
					};
				} ( viewName );		
			} else {
				btn.onclick = function( x , y ) {
					return function() {
						doStoreNRR( "edit" , x , y );
					};
				} ( viewName , id );
			}
			btn.style.display = "" ;
		} else {
			alert( "Нельзя изменить повестку если эксперт(ы) уже указали стоимость выхода в суд." );
			btn.style.display = "none" ;
		}
				
		dlg.style.display = "" ;
		
		return ;
		
		/*var btn = document.getElementById( "nrr-lnk-ok" );
		if ( doc.getAttribute( "mc" ) == "1" ) {
			btn.onclick = function( x , y ) {
				return function() {
					doStoreNRR( "edit" , x , y );
				};
			} ( viewName , id );
			btn.style.display = "" ;
		} else {
			alert( "Нельзя изменить повестку если эксперт(ы) уже указали стоимость выхода в суд." );
			btn.style.display = "none" ;
		}*/
		
	}
	
	
	function hideAddNRRDlg() {
		var dlg = document.getElementById( "nrr-dlg" );
		dlg.style.display = "none" ;
	}
	
	function doStoreNRR( mode , viewName , sid ) {
		var cba = document.getElementsByName( "nrren[]" );
		var te = [];
		for( var i = 0 ; i < cba.length ; i++ ) {
			if ( cba[ i ].checked ) {
				te.push( cba[ i ].value );
			}
		}
		
		var cba = document.getElementsByName( "nrr-marks[]" );
		var marks = [];
		for( var i = 0 ; i < cba.length ; i++ ) {
			if ( cba[ i ].checked ) {
				marks.push( cba[ i ].value );
			}
		}
		
		var t = document.getElementById( "nrr-type" );
		var d = document.getElementById( "nrr-date" );
		var eDate = document.getElementById( "nrr-ext-date" );
		var eNum = document.getElementById( "nrr-ext-num" );
		var name = document.getElementById( "nrr-name" );
		var desc = document.getElementById( "nrr-desc" );
		var pn = document.getElementById( "nrr-page-num" );
		var an = document.getElementById( "nrr-att-num" );
		var elt = document.getElementById( "nrr-elt" );
		
		var cs = true ;
		
		var ca = [ [ d , "d" ] , [ eDate , "d" ] , [ eNum , "V" , /^\s*$/ , "Не указан номер" ] ];
		var cr = true ;
		for( var i = 0 ; i < ca.length ; i++ ) {
			cr = cr && checkInput.apply( null , ca[ i ] );
			if ( !cr ) {
				return ;
			}
		}
		
		if ( addresseeList.length < 1 ) {
			alert( "Выберите адресатов" );
			var al = document.getElementById( "nrr-addressee-list-area" );
			blinkElement( al );
			cs = false ;
			return ;
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
		
		switch( mode ) {
			case "add" :
				var xml = 
					"<add-correspondence t=\"" + t.value + "\" d=\"" + d.value + "\" te=\"" + te.join( "," ) + "\" pn=\"" + pn.value + "\" an=\"" + an.value + "\" marks=\"" + marks.join( "," ) + "\">" +
						"<ext d=\"" + eDate.value + "\">" + toCDATA( eNum.value ) + "</ext>" + 
						"<name>" + toCDATA( name.value ) + "</name>" +
						"<desc>" + toCDATA( desc.value ) + "</desc>" +
						"<addressee-list>" ;
					for ( var i = 0 ; i < addresseeList.length ; i++ ) {
						var ca = addresseeList[ i ];
						xml += "<addressee><agency toa=\"" + ca.agencyType + "\">" + toCDATA( ca.orgName ) + "</agency><agent>" + toCDATA( ca.name ) + "</agent><contacts>" ;
						for ( var j = 0 ; j < ca.contacts.length ; j++ ) {
							var cc = ca.contacts[ j ];
							xml += "<contact t=\"" + cc.type + "\" ufr=\"" + cc.useForReply + "\" s=\"" + cc.state + "\" sd=\"" + cc.stateDate + "\">" + toCDATA( cc.value ) + "</contact>" ;
						}
						xml += "</contacts></addressee>" ;
					}
					xml += "</addressee-list></add-correspondence>" ;
					var doc = sendXML( xml , false , "correspondence.php" , "view=" + viewName );
				break ;
				
			case "edit" :
				var xml = 
					"<change-correspondence id=\"" + sid + "\" t=\"" + t.value + "\" d=\"" + d.value + "\" te=\"" + te.join( "," ) + "\" pn=\"" + pn.value + "\" an=\"" + an.value + "\" marks=\"" + marks.join( "," ) + "\">" +
						"<ext d=\"" + eDate.value + "\">" + toCDATA( eNum.value ) + "</ext>" + 
						"<name>" + toCDATA( name.value ) + "</name>" +
						"<desc>" + toCDATA( desc.value ) + "</desc>" +
						"<addressee-list>" ;
					for ( var i = 0 ; i < addresseeList.length ; i++ ) {
						var ca = addresseeList[ i ];
						xml += "<addressee><agency toa=\"" + ca.agencyType + "\">" + toCDATA( ca.orgName ) + "</agency><agent>" + toCDATA( ca.name ) + "</agent><contacts>" ;
						for ( var j = 0 ; j < ca.contacts.length ; j++ ) {
							var cc = ca.contacts[ j ];
							xml += "<contact t=\"" + cc.type + "\" ufr=\"" + cc.useForReply + "\" s=\"" + cc.state + "\" sd=\"" + cc.stateDate + "\">" + toCDATA( cc.value ) + "</contact>" ;
						}
						xml += "</contacts></addressee>" ;
					}
					xml += "</addressee-list></change-correspondence>" ;
					var doc = sendXML( xml , false , "correspondence.php" , "view=" + viewName );
				break ;
		}
		
		hideAddNRRDlg();
		window.location.reload( true );
	}
	
	function doAddContact( ct , cv , cc ) {		
		if ( typeof cc == "undefined" ) {
			cc = false ;
		}
		
		if ( typeof cv == "undefined" ) {
			cv = "" ;
		}
		
		var contactData = [
			{
				label : "адрес" ,
				tag : "textarea" ,
				placeHolder : "394000, г.Воронеж, ..."
			} ,
			{
				label : "e-mail" ,
				tag : "input" ,
				tagType : "text" , 
				placeHolder : "васяпупки@mail.ru"
			} ,
			{
				label : "факс" ,
				tag : "input" ,
				tagType : "text" ,
				placeHolder : "(473) 200-00-00 или 200-00-00"
			} ,
			{
				label : "мобильный" ,
				tag : "input" ,
				tagType : "text" ,
				placeHolder : "8-920-200-00-00"
			} ,
			{
				label : "на руки" ,
				tag : "input" ,
				tagType : "text" ,
				placeHolder : "Иванов Иван Иванович ..."
			}			
		];
		
		var inpID = "nrr-contact-" + generateGUID();
		
		var ctab = document.getElementById( "nrr-addressee-contacts-tab" );
		var r = ctab.insertRow( -1 );
		r.className = "nrr-act-row" ;
			var c = r.insertCell( -1 );
			c.className = "nrr-act-name" ;
				var tmp = document.createElement( "label" );
				tmp.className = "nrr-inline-label" ;
				setText( tmp , contactData[ ct - 1 ].label );
				tmp.htmlFor = inpID ;
			c.appendChild( tmp );
			
			var c = r.insertCell( -1 );
			c.className = "nrr-act-value" ;
				var tmp = document.createElement( contactData[ ct - 1 ].tag );
				if ( contactData[ ct - 1 ].tagType ) {
					tmp.type = contactData[ ct - 1 ].tagType ;
				}
				tmp.id = inpID ;
				tmp.name = "nrr-contacts[]" ;
				tmp.setAttribute( "data-contact-type" , ct );
				tmp.className = "nrr-contact" ;
				tmp.placeholder = contactData[ ct - 1 ].placeHolder ;
				if ( cv != "" ) {
					tmp.value = cv ;
				}
			c.appendChild( tmp );
			tmp.focus();
			var inpTag = tmp ;
			
			var c = r.insertCell( -1 );
			c.className = "nrr-act-repl" ;
				var tmp = document.createElement( "input" );
				tmp.type = "checkbox" ;
				tmp.name = "nrr-contacts-cb[]" ;
				tmp.setAttribute( "data-for-contact" , inpID );
				if ( cc ) {
					tmp.checked = true ;
				}
			c.appendChild( tmp );
			
			var c = r.insertCell( -1 );
			c.className = "nrr-act-btns" ;
				var tmp = document.createElement( "div" );
				tmp.className = "nrr-act-dcb" ;
			c.appendChild( tmp );
			
		$.dlgTmpl.create( inpTag , { temporary : true , listLoader : contactsLoader } );
		return inpTag ;
	}
	
	function contactsLoader() {
		//alert( $.dlgTmpl );
		return agencyAllContacts ;
	}
	
	var addresseeList = [];
	
	function mkAddresseeRow( addresseeData ) {
		var al = document.getElementById( "nrr-addressee-list-tab" );
		var r = al.insertRow( -1 );
		r.className = "nrr-alt-row" ;
			var c = r.insertCell( -1 );
			c.className = "nrr-alt-name" ;
			addText( c , addresseeData.orgName + ", " + addresseeData.name );
			
			var c = r.insertCell( -1 );
			c.className = "nrr-alt-cnt" ;
			var tcl = addresseeData.contacts ;
			for( var i = 0 ; i < tcl.length ; i++ ) {
				if ( tcl[ i ].useForReply == 1 ) {
					var tmp = document.createElement( "div" );
					var tmp2 = document.createElement( "div" );
					tmp2.className = tcl[ i ].state == 1 ? "nrr-alt-ss-ok" : "nrr-alt-ss-wait" ;
					tmp2.onclick = function ( x , y ) {
						return function() {
							changeSendState( x , y ); 
						};
					}( tmp2 , tcl[ i ] );
					tmp.appendChild( tmp2 );
					addText( tmp , tcl[ i ].value );
					tmp.title = tcl[ i ].value ;
					c.appendChild( tmp );
				}
			}
			
			var c = r.insertCell( -1 );
			c.className = "nrr-alt-btns" ;
				var tmp = document.createElement( "div" );
				tmp.className = "nrr-alt-dab" ;
				tmp.onclick = function ( x ) {
					return function () {
						deleteAddresseeRow( x );
					};
				}( addresseeData );
			c.appendChild( tmp );
			
			addresseeData.row = r ;
			
		return r ;
	}
	
	function changeSendState( icon , cont ) {
		var d = new Date();
		var ns = 1 - cont.state ;
		
		if ( ns == 1 ) {
			d = str_pad( d.getDate() , 2 , "0" , STR_PAD_LEFT ) + "-" + str_pad( d.getMonth() + 1 , 2 , "0" , STR_PAD_LEFT ) + "-" + d.getFullYear();
			var res = prompt( "Укажите дату доставки" , d );
			if ( res === null ) {
				return ;
			}
						
			cont.stateDate = res ;
		} else {
			if ( !confirm( "Вы действительно хотите удалить отметку о доставке ?" ) ) {
				return ;
			}
		}
		
		
		cont.state = ns ;
		if ( cont.state == 1 ) {
			icon.className = "nrr-alt-ss-ok" ;
		} else {
			icon.className = "nrr-alt-ss-wait" ;
		}
		
	}
	
	function deleteAddresseeRow( ad ) {
		var adi = -1 ;
		for( var i = 0 ; i < addresseeList.length ; i++ ) {
			if ( addresseeList[ i ].uid == ad.uid ) {
				adi = i ;
				break ;
			}
		}
		
		if ( adi == -1 ) {
			return ;
		}
		
		addresseeList.splice( adi , 1 );
		ad.row.parentNode.removeChild( ad.row );
	}
	
	function doAddAddressee() {
		var addresseeData = {
			uid : generateGUID() ,
			agencyType : 1 ,
			orgName : "" ,
			name : "" ,
			contacts : [] ,
			contactsMap : {}
		};
		var tcl = [];
		var contactsList = document.getElementsByName( "nrr-contacts[]" );
		for( var i = 0 ; i < contactsList.length ; i++ ) {
			cc = {
				id : contactsList[ i ].id ,
				type : contactsList[ i ].getAttribute( "data-contact-type" ),
				value : contactsList[ i ].value.trim() ,
				useForReply : 0 ,
				state : 0 ,
				stateDate : ""
			};
			
			addresseeData.contacts.push( cc );
			addresseeData.contactsMap[ cc.id ] = cc ;
		}
		
		var contactsCbList = document.getElementsByName( "nrr-contacts-cb[]" );
		for( var i = 0 ; i < contactsCbList.length ; i++ ) {
			if ( contactsCbList[ i ].checked ) {
				var ccid = contactsCbList[ i ].getAttribute( "data-for-contact" );
				addresseeData.contactsMap[ ccid ].useForReply = 1 ;
				tcl.push( addresseeData.contactsMap[ ccid ].value );
			}
		}
		
		var toa = document.getElementById( "nrr-toa" );
		var ay = document.getElementById( "nrr-from-agency" );
		var at = document.getElementById( "nrr-from-agent" );
		addresseeData.agencyType = toa.value ;
		addresseeData.orgName = ay.value ;
		addresseeData.name = at.value ;
		
		mkAddresseeRow( addresseeData , tcl );
		
		addresseeList.push( addresseeData );
	}
	
	
	
	
	
	
	
	/*function doMove() {
		var ppo = $.pdfPreview ;
		if ( !ppo.en.value.match( /^\s*\d{1,5}\s*$/ ) ) {
			alert( "Не верный номер, укажите только цифры" );
			return ;
		}
		$n = /(\d+)/.exec( ppo.en.value );
		
		var doc = sendXML( "<move y=\"" + ppo.ey.value + "\" n=\"" + $n[ 1 ] + "\">" + toCDATA( $.pdfPreview.fn ) + "</move>" );
		window.location.reload( true );
	}
	
	function doDelete() {
		var doc = sendXML( "<delete>" + toCDATA( $.pdfPreview.fn ) + "</delete>" );
		window.location.reload( true );
	}*/
	
	function doAttacheFile( fn , id , viewName ) {
		var ppo = $.pdfPreview ;
		
		var doc = sendXML( "<get-cor-ny id=\"" + id + "\" />" , false , "correspondence.php" , "view=" + viewName );
		if ( doc.getAttribute( "state" ) == "error" ) {
			return ;
		}
		
		var y = parseInt( doc.getAttribute( "y" ) , 10 );
		var n = parseInt( doc.getAttribute( "n" ) , 10 );
		
		/*if ( ppo.tab1i.checked ) {
			if ( fn == null ) {
				alert( "Файл не выбран" );
				return ;				
			}
			var doc = sendXML( "<link-file id=\"" + id + "\">" + toCDATA( fn ) + "</link-file>" , false , null , "view=" + viewName );
			if ( doc.getAttribute( "state" ) == "error" ) {
				alert( getXMLNodeValue( doc ) );
				return ;
			}
		} else*/
		if ( ppo.tab2i.checked ) {
			ppo.fcid.value = id ;
			ppo.fcy.value = y ;
			ppo.fcn.value = n ;
			ppo.fsf.submit();
			return ;
		}
						
		hideFUDlg();
		window.location.reload( true );
	}
	
	function showFUDlg( id , viewName ) {
		var ppo = $.pdfPreview ;
		var tl = ppo.tl ;
		var tll = tl.childNodes ;
		var tlll = tll.length ;
		
		var doc = sendXML( "<get-files-to-upload />" , false , "correspondence.php" , "view=" + viewName );
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
			clnk.onclick = function( x , y , z ) {
				return function() {
					var ppo = $.pdfPreview ;
					
					showPDF( x , y , z );
				};
			}( clnkT , id , viewName );
		}
		
		ppo.ab.onclick = function( x , y , z ) {
			return function() {
				doAttacheFile( x , y , z );
			};
		}( null , id , viewName );
		
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
	
	function showPDF( fn , id , viewName ){
		var ppo = $.pdfPreview ;
		ppo.fn = fn ;
		ppo.ab.onclick = function( x , y , z ) {
			return function() {
				doAttacheFile( x , y , z );
			};
		}( fn , id , viewName );
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
