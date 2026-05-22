/**
 * 
 */

	function setVal( a , v ) {
		for( var i = 0 ; i < a.length ; i++ ) {
			setText( a[ i ] , v );
		}
	}
	
	function checkDate( v ) {
		var m = v.match( /(\d{2})[,-.](\d{2})[,-.](\d{4})/ );
		if ( m == null ) {
			return { s : "err" , t : "f" , res : v };
		}
		m[ 4 ] = m[ 1 ] + "." + m[ 2 ] + "." + m[ 3 ];
		for( var i = 1 ; i <= 3 ; i++  ) {
			m[ i ] = parseInt( m[ i ] , 10 );
		}
		
		if ( m[ 2 ] < 1 || m[ 2 ] > 12 ) {
			return { s : "err" , t : "moor" , res : m[ 4 ] };
		}
		
		var cd = new Date();
		if ( m[ 3 ] < cd.getFullYear() ) {
			return { s : "err" , t : "yltc" , res : m[ 4 ] };
		}
		
		var dc = ( new Date( m[ 3 ] , m[ 2 ] , 0 ) ).getDate();
		if ( m[ 1 ] < 1 || m[ 1 ] > dc ) {
			return { s : "err" , t : "door" , res : m[ 4 ] };
		}
		
		return { s : "ok" , t : "ok" , res : m[ 4 ] };					
	}

	function changeField( eo ) {
		var cr = checkDate( eo.tdi.value );
		if ( cr.s != "ok" ) {
			eo.tdi.style.color = "#f44" ;
		} else {
			eo.tdi.style.color = "" ;
		}
		setVal( eo.td , cr.res + " г." );
		setVal( eo.tt , eo.tti.value );
		setVal( eo.to , eo.toi.value );		
	}
	
	function setEvt( o , eo ) {
		o.onkeydown = function( x ) {
			return function() {
				changeField( x );
			};
		}( eo );
		o.onkeypress = function( x ) {
			return function() {
				changeField( x );
			};
		}( eo );
		o.onkeyup = function( x ) {
			return function() {
				changeField( x );
			};
		}( eo );
	}
	
	function cp() {
		var mt = document.getElementById( "main-text" );
		//mt.
	}
	
	$.windowOnLoad.push( function() {
		
		var doia = document.getElementById( "tgt-displayable-ia" );
		
		var eo = {
			tdi : document.getElementById( "tgt-date-i" ),
			tti : document.getElementById( "tgt-time-i" ),
			toi : document.getElementById( "tgt-object-i" ),
			odi : [],
			td : document.getElementsByClassName( "tgt-date" ),
			tt : document.getElementsByClassName( "tgt-time" ),
			to : document.getElementsByClassName( "tgt-object" ),
			od : document.getElementsByClassName( "tgt-displayable" )
		};
		
		/*for( var i = 0 ; i < eo.od.length ; i++ ) {
			var cdo = eo.od[ i ];
			var cdoh = cdo.offsetHeight ;
			var tmp = document.createElement( "div" );
			var tmp2 = document.createElement( "input" );
			tmp2.type = "checkbox" ;
			tmp2.checked = true ;
			tmp2.onchange = function( s , x , y ) {
				return function() {
					if ( s.checked ) {
						x.style.height = y + "px" ;
						x.style.opacity = "1.0" ;
					} else {
						x.style.height = "0px" ;
						x.style.opacity = "0.0" ;
					}
				};
			}( tmp2 , cdo , cdoh );
			cdo.style.height = cdoh + "px" ;
			tmp.appendChild( tmp2 );
			tmp.appendChild( document.createTextNode( cdo.title ) );
			
			doia.appendChild( tmp );
		}*/
		
		setEvt( eo.tdi , eo );
		setEvt( eo.tti , eo );
		setEvt( eo.toi , eo );
		
		
		
		changeField( eo );
		
		var doc = sendXML( "<docVar />" , false , "" , "tmpl=" + docTemplateID + "&id=" + expertizeID );
		$.docVar = JSON.parse( getXMLNodeValue( doc ) );
	} );
	
	function doEnum() {
		var mt = document.getElementById( "main-text" );
		for( var i = 0 ; i < mt.childNodes.length ; i++ ) {
			var ce = mt.childNodes[ i ];
			alert( ce );
		}
	}
	
	function doPasteTest() {
		var s = window.getSelection();
		var r = s.getRangeAt( 0 );
		var t = document.createElement( "span" );
		t.className = "thl" ;
		t.appendChild( document.createTextNode( ( new Date() ) ) );
		r.insertNode( t );
	}
	
	function doSelectVarVariant( v , t , n , i ) {
		v.setAttribute( "data-form" , i );
		t.data = inForm( $.docVar[ n ].value , i );
	}
	
	function doPasteVariable( n ) {
		var s = window.getSelection();
		var r = s.getRangeAt( 0 );
		//var sc = r.startContainer ;
		var t = document.createElement( "span" );
		t.className = "thli" ;
		t.title = $.docVar[ n ].desc ;
		t.setAttribute( "data-var-name" , n );
		t.setAttribute( "data-var-hl" , "i" );
		if ( $.docVar[ n ].mf ) {
			t.setAttribute( "data-form" , 1 );
			var ttn = document.createTextNode( inForm( $.docVar[ n ].value ) );
			t.appendChild( ttn );
			var lst = document.createElement( "div" );
			lst.className = "thli-list" ;
			for( var i = 1 ; i <= 6 ; i++ ) {
				var tmp = document.createElement( "div" );
				tmp.appendChild( document.createTextNode( inForm( $.docVar[ n ].value , i ) ) );
				tmp.className = "thli-list-item" ;
				tmp.unselectable = "on" ;
				tmp.onselectstart = tmp.onmousedown = function() { return false ; };
				tmp.onclick = function( v , t , n , i ) {
					return function() {
						doSelectVarVariant( v , t , n , i );
					};
				}( t , ttn , n , i );
				lst.appendChild( tmp );
			}
			t.appendChild( lst );
		} else {
			t.appendChild( document.createTextNode( $.docVar[ n ].value ) );
		}
		t.unselectable = "on" ;
		t.onselectstart = t.onmousedown = function() { return false ; };
		
		var ins = document.createDocumentFragment();
		ins.appendChild( t );
		ins.appendChild( document.createTextNode( " " ) );
		
		r.deleteContents();
		r.insertNode( ins );
	}
	
	function doEditVariable( n ) {
		var vv = $.docVar[ n ].value ;
		var nvv = prompt( "¬ведите новое значение дл€ \r\n[ " + $.docVar[ n ].desc + " ]" , vv );
		if ( nvv === null ) {
			return ;
		}
		var el = document.querySelectorAll( "[data-var-name='" + n + "']" );
		for( var i = 0 ; i < el.length ; i++ ) {
			var tmp = el[ i ].cloneNode( false );
			tmp.className = "thln" ;
			tmp.setAttribute( "data-var-hl" , "n" );
			tmp.appendChild( document.createTextNode( nvv ) );
			el[ i ].parentNode.replaceChild( tmp , el[ i ] );
		}
		var ed = document.querySelectorAll( "[data-editor-var-name='" + n + "']" );
		for( var i = 0 ; i < ed.length ; i++ ) {
			var tmp = ed[ i ].cloneNode( false );
			tmp.className = "thln" ;
			tmp.appendChild( document.createTextNode( nvv ) );
			ed[ i ].parentNode.replaceChild( tmp , ed[ i ] );
		}
		$.docVar[ n ].value = nvv ;
		
		//alert( nvv );
		return ;
		var s = window.getSelection();
		var r = s.getRangeAt( 0 );
		//var sc = r.startContainer ;
		var t = document.createElement( "span" );
		t.className = "thli" ;
		t.title = $.docVar[ n ].desc ;
		t.setAttribute( "data-var-name" , n );
		t.setAttribute( "data-var-hl" , "i" );
		if ( $.docVar[ n ].mf ) {
			t.setAttribute( "data-form" , 1 );
			var ttn = document.createTextNode( inForm( $.docVar[ n ].value ) );
			t.appendChild( ttn );
			var lst = document.createElement( "div" );
			lst.className = "thli-list" ;
			for( var i = 1 ; i <= 6 ; i++ ) {
				var tmp = document.createElement( "div" );
				tmp.appendChild( document.createTextNode( inForm( $.docVar[ n ].value , i ) ) );
				tmp.className = "thli-list-item" ;
				tmp.unselectable = "on" ;
				tmp.onselectstart = tmp.onmousedown = function() { return false ; };
				tmp.onclick = function( v , t , n , i ) {
					return function() {
						doSelectVarVariant( v , t , n , i );
					};
				}( t , ttn , n , i );
				lst.appendChild( tmp );
			}
			t.appendChild( lst );
		} else {
			t.appendChild( document.createTextNode( $.docVar[ n ].value ) );
		}
		t.unselectable = "on" ;
		t.onselectstart = t.onmousedown = function() { return false ; };
		
		var ins = document.createDocumentFragment();
		ins.appendChild( t );
		ins.appendChild( document.createTextNode( " " ) );
		
		r.deleteContents();
		r.insertNode( ins );
	}
	
	function doSave( tid ) {
		
		function readTextXML( n ) {
			var res = "" ;
			for( var i = 0 ; i < n.childNodes.length ; i++ ) {
				var cn = n.childNodes[ i ];
				if ( cn.nodeType == 3 ) {
					res+= cn.data ;
				} else
				if ( cn.hasAttribute( "data-var-name" ) ) {
					var vn = cn.getAttribute( "data-var-name" );
					var vf = $.docVar[ vn ].mf && cn.hasAttribute( "data-form" ) ? " form=\"" + cn.getAttribute( "data-form" ) + "\"" : "" ;
					var vh = cn.hasAttribute( "data-var-hl" ) ? " hl=\"" + cn.getAttribute( "data-var-hl" ) + "\"" : "" ;
					res += "<var name=\"" + vn + "\"" + vf + vh + "/>" ; 
				} else {
					res += "<" + cn.nodeName.toLowerCase() + ">" + readTextXML( cn ) + "</" + cn.nodeName.toLowerCase() + ">" ;					
				}
			}					
			return res ;
		}
		
		var res = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><template version=\"100\" format=\"" + ps + "\" header=\"1\" prepend-text=\"1\" title=\"1\">" ;
		
		var pt = document.getElementById( "prepend-text" );
		res+= "<prepend-text signature=\"boss\"><text>" + readTextXML( pt ) + "</text></prepend-text>" ;
		
		res+= "<title type=\"caps\"><var name=\"tmpl-name-short\" /></title>" ;
		
		var mt = document.getElementById( "main-text" );
		res+= "<main-text>" + readTextXML( mt ) + "</main-text>" ;
		
		res+= "</template>" ;
		
		alert( "<save id=\"" + tid + "\">" + toCDATA( res ) + "</save>" );
		
		var doc = sendXML( "<save id=\"" + tid + "\">" + toCDATA( res ) + "</save>" , false , "" , "tmpl=" + docTemplateID + "&id=" + expertizeID );
	}
	
	function doGetRTF( t , e ) {
		var variables = {};
		variables = JSON.stringify( variables );
		var idbg = document.getElementById( "req-dbg-mode" );
		window.location.assign( "request.rtf.php?tmpl=" + t + "&type=expertize&&id=" + e + ( idbg != null && idbg.checked ? "&dbg=1" : "" ) );
	}
	
	function getPagesCount() {
		
	}

	
	//function
	
	//onkeydown=\"changeField()\" onkeypress=\"changeField()\" onkeyup=\"changeField()\"