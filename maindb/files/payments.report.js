/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	function showRP() {
		var rpa = document.getElementById( "rpa" );
		var rpaa = document.getElementById( "rpaa" );
		if ( rpa.style.display == "none" ) {
			rpa.style.display = "" ;
		} else {
			rpa.style.display = "none" ;
		}
	}

	function sfkp( event ) {
		if ( columnToFilter == null ) {
			return ;
		}
		event = window.event ? window.event : event ;
		if ( event.keyCode == 13 ) {
			var sf = document.getElementById( "search-form" );
			var fi = document.getElementById( "i_search" );
			var fd = document.getElementById( "i_flt__" + columnToFilter );
			fd.value = fi.value ;
			sf.submit();
		}
	}

	function sfsf( v ) {
		if ( columnToFilter == null ) {
			return ;
		}
		var sf = document.getElementById( "search-form" );
		var fi = document.getElementById( "i_search" );
		var fd = document.getElementById( "i_flt__" + columnToFilter );
		fd.value = ( v == 1 ? fi.value : "" );
		sf.submit();
	}

	window.onload = function () {
		var dlg = document.getElementById( "filter_dialog" );
		if ( dlg != null ) {
			dlg.parentNode.removeChild( dlg );
			document.body.appendChild( dlg );
		}
	};

	function getText( o ) {
		if ( "innerText" in o ) {
			return o.innerText ;
		} else {
			return o.textContent ;
		}
	}

	function setText( o , t ) {
		if ( "innerText" in o ) {
			o.innerText = t ;
		} else {
			o.textContent = t ;
		}
	}

	function gmp( o ) {
		for( var px = 0 , py = 0 ; o.offsetParent ; o = o.offsetParent ) {
			px+= o.offsetLeft ;
			py+= o.offsetTop ;
		}
		return [ px , py ];
	}

	var columnToFilter = null ;

	function fdc( fld , event ) {
		event = window.event ? window.event : event ;
		event.target = event.target ? event.target : event.srcElement ;

		var dlg = document.getElementById( "filter_dialog" );
		var fi = document.getElementById( "i_search" );
		var fd = document.getElementById( "i_flt__" + fld );

		var dlgPos = gmp( event.target.parentNode );
		dlg.style.left = dlgPos[ 0 ] + "px" ;
		dlg.style.top = ( dlgPos[ 1 ] + event.target.parentNode.offsetHeight - 4 ) + "px" ;
		fi.style.width = ( event.target.parentNode.offsetWidth - 13 ) + "px" ;

		fi.value = fd.value ;

		if ( dlg.style.display == "none" ) {
			dlg.style.display = "" ;
			columnToFilter = fld ;
			fi.focus();
		} else {
			columnToFilter = null ;
			dlg.style.display = "none" ;
		}
	}

	function fdb() {
		var dlg = document.getElementById( "filter_dialog" );
		dlg.style.display = "none" ;
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

	function makeMarkList( ml ) {
		/*var mrllt = document.getElementById( "mrl_lb" );

		while ( mrllt.childNodes.length > 0 ) {
			mrllt.removeChild( mrllt.childNodes[ 0 ] );
		}*/

		/*var mrlab = document.getElementById( "mrl_ab" );
		if ( ml.length > 0 ) {
			mrlab.style.display = "" ;
			var res = sendXML( "<marklist id=\"" + ml.join( "," ) + "\"/>" , false );
			var doc = res.documentElement ;

			for( var i = 0 ; i < doc.childNodes.length ; i++ ) {
				var d = document.createElement( "div" );

				var a = document.createElement( "a" );
				a.className = "mrl-lti" ;
				a.href = "#tr_cid_" + doc.childNodes[ i ].getAttribute( "id" );
				setText( a , ( doc.childNodes[ i ].getAttribute( "number" ) ) + " - " + ( doc.childNodes[ i ].textContent || doc.childNodes[ i ].text ) );
				d.appendChild( a );

				mrllt.appendChild( d );
			}

		} else {
			mrlab.style.display = "none" ;
		}*/
	}
	
	var rowMarkColors = [
 		[ "" , "" , "" ] ,
 		//[ "#ffc0c0" , "#000000" , "#ffc0c0" ] ,
 		[ "#c0ffc0" , "#000000" , "#c0ffc0" ] ,
 		//[ "#c0c0ff" , "#000000" , "#c0c0ff" ]
 	];

	function trs( event , pcid ) {
		event = window.event ? window.event : event ;
		event.target = event.target || event.srcElement ;
		
		if ( event.target.nodeName == "TD" ) {
			var ca = event.currentTarget.childNodes ;

			var sc = getCookie( "payments_mark" );
			if ( sc == null || sc == "" ) {
				sc = [];
			} else {
				sc = sc.split( "," );
			}
			ml = [];
			for ( var i = 0 ; i < sc.length ; i++ ) {
				var p = sc[ i ].split( "/" );

				if ( p.length == 1 ) {
					p.push( 1 );
				} else
				if ( p.length == 2 ) {
					p[ 1 ] = parseInt( p[ 1 ] , 10 );
				}

				if ( p[ 0 ] == "" + pcid ) {
					sc.splice( i , 1 );
					break ;
				}
				p = null ;
			}
			if ( p == null ) {
				p = [ pcid , 0 ];
			}
			p[ 1 ]++ ;
			if ( p[ 1 ] >= rowMarkColors.length ) {
				p[ 1 ] = 0 ;
			}
			ca[ 0 ].style.backgroundColor = rowMarkColors[ p[ 1 ] ][ 0 ];
			ca[ 0 ].style.color = rowMarkColors[ p[ 1 ] ][ 1 ];
			for ( var i = 1 ; i < ca.length ; i++ ) {
				ca[ i ].style.backgroundColor = rowMarkColors[ p[ 1 ] ][ 2 ];
				ca[ i ].style.color = rowMarkColors[ p[ 1 ] ][ 1 ];
			}

			if ( p[ 1 ] != 0 ) {
				sc.push( p.join( "/" ) );
			}

			for ( var i = 0 ; i < sc.length ; i++ ) {
				var p = sc[ i ].split( "/" );
				ml.push( p[ 0 ] );
			}

			setCookie( "payments_mark" , sc.join( "," ) , 0 );

			makeMarkList( ml );

			/*if( event.stopPropagation ) {
				event.stopPropagation();
			} else {
				event.cancelBubble = true ;
			}*/
		}

	}

	var currentEditComment = null ;
	var editCommentInput = document.createElement( "textarea" );
	editCommentInput.className = "elt-d-com-ta" ;
	editCommentInput.onkeypress = takp ;
	editCommentInput.onblur = tab ;


	function getText( o ) {
		if ( "innerText" in o ) {
			return o.innerText ;
		} else {
			return o.textContent ;
		}
	}

	function setText( o , t ) {
		if ( "innerText" in o ) {
			o.innerText = t ;
		} else {
			o.textContent = t ;
		}
	}

	var ccth = null ;

	function updComment() {
		if ( currentEditComment != null && currentEditComment.ct != editCommentInput.value ) {
			var res = sendXML( "<comment id=\"" + currentEditComment.pcid + "\" eid=\"" + currentEditComment.eid + "\">" + toCDATA( editCommentInput.value ) + "</comment>" , true );
			currentEditComment.ct = editCommentInput.value ;
		}
		ccth = null ;
	}

	function takp () {
		if ( ccth != null ) {
			clearTimeout( ccth );
		}

		ccth = setTimeout( "updComment();" , 500 );
	}

	function tab () {
		if ( currentEditComment != null ) {
			if ( ccth != null ) {
				clearTimeout( ccth );
			}
			updComment();
			setText( currentEditComment.cts , currentEditComment.ct );
			currentEditComment.pr.replaceChild( currentEditComment.cts , editCommentInput );
			currentEditComment = null ;
		}
	}

	function editComment ( rid , pcid , eid ) {
		if ( currentEditComment != null && currentEditComment.rid == rid ) {
			return ;
		}

		if ( currentEditComment != null ) {
			if ( ccth != null ) {
				clearTimeout( ccth );
			}
			updComment();
			setText( currentEditComment.cts , currentEditComment.ct );
			currentEditComment.pr.replaceChild( currentEditComment.cts , editCommentInput );
		}

		var pr = document.getElementById( "pr_" + rid + "_com" );
		var cts = document.getElementById( "pcc_" + rid );
		var ct = getText( cts );

		editCommentInput.value = ct ;
		pr.replaceChild( editCommentInput , cts );

		currentEditComment = {
			rid : rid ,
			pcid : pcid ,
			pr : pr ,
			cts : cts ,
			ct : ct ,
			eid : eid
		};

		editCommentInput.focus();
	}

	function sendXML( data , async , target ) {
		
		var sd = "random=" + ( new Date() ).getTime() + ( Math.random() * 1000 ) + "&mode=ajax&data=" + encodeURIComponent( "<?xml version=\"1.0\" encoding=\"utf-8\" ?>" + data );
		var req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( "Microsoft.XMLHTTP" );
		}
		
		if ( typeof target == "undefined" ) {
			target = "payments.php" ;
		}

		if ( req ) {
			req.open( "POST" , target , async );
			req.setRequestHeader( "Accept-Charset" , "windows-1251" );
			req.setRequestHeader( "Accept-Language" , "ru,en" );
			req.setRequestHeader( "Connection" , "close" );
			req.setRequestHeader( "Content-length" , sd.length );
			req.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );
			req.setRequestHeader( "Content-Encoding" , "utf-8" );
			req.send( sd );

			if ( !async ) {
				//alert( req.responseText );
				return req.responseXML ;
			} else {
				return ;
			}
		}
	}
