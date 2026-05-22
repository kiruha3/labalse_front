/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	$.Tooltip = new function( tmid ) {
		var d = document ;
		this.tmid = tmid ;
		this.offsetFromCursorY = 15 ;
		this.ie = d.all && !window.opera ;
		this.ns6 = d.getElementById && !d.all ;
		this.tipobj = null ;
		this.op = null ;

		this.tooltip = function( el , id ) {
			this.tipobj = document.getElementById( this.tmid );

			var req = "<getWOEData id=\"" + id + "\" />" ;
			var res = sendXML( req , false , "writ-of-execution.php" );
			var doc = res ;
			var txt = "<table align=\"center\" class=\"woe-tab\"><tr class=\"woe-hr\"><td class=\"woe-hc1\">Плательщик</td><td class=\"woe-hc2\">Сумма</td></tr>" ;
			var dcnl = doc.childNodes.length ;
			
			for( var i = 0 ; i < dcnl ; i++ ) {
				var data = doc.childNodes[ i ];
				var price = data.getAttribute( "price" );
				var payd = data.getAttribute( "payd" );
				var payer = getText( data );
				txt+= "<tr class=\"woe-dr\"><td class=\"woe-dc1\">" + payer + "</td><td class=\"woe-dc2\">оплачено: <span class=\"woe-p\">" + payd + "</span><br>всего: <span class=\"woe-p2\">" + price + "</span></td></tr>" ;
			}
			
			txt+= "</table>" ;

			this.tipobj.innerHTML = txt ;
			this.op = 0.1 ;
			this.tipobj.style.opacity = this.op ;
			this.tipobj.style.display = "" ;
			el.onmousemove = function( o ) {
				return function( evt ) {
					o.positiontip( evt );
				};
			}( this );
			this.appear();
		};

		this.hide_info = function( el ) {
			document.getElementById( this.tmid ).style.display = "none" ;
			el.onmousemove = "" ;
		};

		this.ietruebody = function() {
			return ( document.compatMode && document.compatMode != "BackCompat" ) ? document.documentElement : document.body ;
		};

		this.positiontip = function( e ) {
			var curX = ( this.ns6 ) ? e.pageX : event.clientX + this.ietruebody().scrollLeft ;
			var curY = ( this.ns6 ) ? e.pageY : event.clientY + this.ietruebody().scrollTop ;
			var winwidth = this.ie ? this.ietruebody().clientWidth : window.innerWidth - 20 ;
			var winheight = this.ie ? this.ietruebody().clientHeight : window.innerHeight - 20 ;

			var rightedge = this.ie ? winwidth - event.clientX : winwidth - e.clientX ;
			var bottomedge = this.ie ? winheight - event.clientY - this.offsetFromCursorY : winheight - e.clientY - this.offsetFromCursorY ;

			if ( rightedge < this.tipobj.offsetWidth ) {
				this.tipobj.style.left = curX - this.tipobj.offsetWidth + "px" ;
			} else {
				this.tipobj.style.left = curX + "px" ;
			}

			if ( bottomedge < this.tipobj.offsetHeight ) {
				this.tipobj.style.top = curY - this.tipobj.offsetHeight - this.offsetFromCursorY + "px" ;
			} else {
				this.tipobj.style.top = curY + this.offsetFromCursorY + "px" ;
			}
		};

		this.appear = function() {
			if( this.op < 1 ) {
				this.op += 0.1 ;
				this.tipobj.style.opacity = this.op ;
				this.tipobj.style.filter = "alpha(opacity='+op*100+')" ;
				t = setTimeout( function( o ) { return function() { o.appear.call( o ); }; }( this ) , 30 );
			}
		};
	}( "woeDataTooltip" );

	function sfkp( event ) {
		event = window.event ? window.event : event ;
		if ( event.keyCode == 13 ) {
			var sf = document.getElementById( "search-form" );
			sf.submit();
		}
	}

	var rowMarkColors = [
		[ "" , "" , "" ] ,
		//[ "#ff0000" , "#ffffff" , "#602020" ] ,
		[ "#00c000" , "#ffffff" , "#206020" ] ,
		//[ "#0000ff" , "#ffffff" , "#202060" ] ,
		//[ "#ff00ff" , "#ffffff" , "#602060" ] ,
		//[ "#ff8000" , "#ffffff" , "#604020" ] ,
		//[ "#00c0ff" , "#ffffff" , "#005060" ]
	];

	function trs( event , pcid ) {
		event = window.event ? window.event : event ;
		event.target = event.target || event.srcElement ;
		//alert( event.target.nodeName );
		//event.currentTarget = event.target ;
		
		//alert( event.currentTarget );
		
		/*while ( event.currentTarget.nodeName != "TR" ) {
			event.currentTarget = event.currentTarget.parentNode ;
		}*/

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
			
			var mtc = document.getElementById( "main-tab-container" );
			var mtim = document.getElementById( "main-tab-item-menu" );
			mtc.style.paddingLeft = ( ml.length != 1 ? 0 : 208 ) + "px" ;
			mtim.style.left = ( -204 + ( ml.length != 1 ? 0 : 208 ) ) + "px" ;

			/*if( event.stopPropagation ) {
				event.stopPropagation();
			} else {
				event.cancelBubble = true ;
			}*/
		}

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

	$.windowOnLoad.push( function () {
		
		var ttmd = document.createElement( "div" );
		ttmd.id = $.Tooltip.tmid ;
		ttmd.style.display = "none" ;
		document.body.appendChild( ttmd ); 
		
		var dlg = document.getElementById( "filter_dialog" );
		if ( dlg != null ) {
			dlg.parentNode.removeChild( dlg );
			document.body.appendChild( dlg );
		}

		var sc = getCookie( "payments_mark" );
		if ( sc == null ) {
			sc = "" ;
		}

		//alert( "Для Петра : " + sc );
		sc = sc.split( "," );
		var ml = [];
		for ( var i = 0 ; i < sc.length ; i++ ) {
			var p = sc[ i ].split( "/" );
			if ( p.length == 1 ) {
				p.push( 1 );
			} else
			if ( p.length == 2 ) {
				p[ 1 ] = parseInt( p[ 1 ] , 10 );
			}
			var tr = document.getElementById( "tr_cid_" + p[ 0 ] );
			if ( tr == null || p[ 1 ] == 0 ) {
				sc.splice( i , 1 );
				i-- ;
			} else {
				sc[ i ] = p.join( "/" );
				ml.push( p[ 0 ] );
				var ca = tr.childNodes ;
				ca[ 0 ].style.backgroundColor = rowMarkColors[ p[ 1 ] ][ 0 ];
				ca[ 0 ].style.color = rowMarkColors[ p[ 1 ] ][ 1 ];
				for ( var j = 1 ; j < ca.length ; j++ ) {
					ca[ j ].style.backgroundColor = rowMarkColors[ p[ 1 ] ][ 2 ];
				}
			}
		}

		setCookie( "payments_mark" , sc.join( "," ) , 0 );
		makeMarkList( ml );

		setInterval( updateVisibilityState , 500 );
		setInterval( updateYearsState , 500 );

		var ps = getCookie( "pagePos" );
		if ( ps != null && ps != "" ) {
			ps = JSON.parse( ps );
			window.scrollTo( ps.left , ps.top );
			setCookie( "pagePos" , "" , 0 );
		}
	} );

	var getPageScroll = ( window.pageXOffset != undefined ) ? function() {
		return {
			left : pageXOffset ,
			top : pageYOffset
		};
	} : function() {
		var html = document.documentElement ;
		var body = document.body ;

		var top = html.scrollTop || body && body.scrollTop || 0 ;
		top -= html.clientTop ;

		var left = html.scrollLeft || body && body.scrollLeft || 0 ;
		left -= html.clientLeft ;

		return {
			top : top ,
			left : left
		};
	};

	function checkPayment( event , pcid ) {
		event = window.event ? window.event : event ;
		event.target = event.target ? event.target : event.srcElement ;
		if( event.stopPropagation ) {
			event.stopPropagation();
		} else {
			event.cancelBubble = true ;
		}

		var pc = document.getElementById( "pcid_" + pcid );
		if ( pc.checked ) {
			var res = sendXML( "<check id=\"" + pcid + "\"/>" , false , "payments.php" );
			var ps = getPageScroll();
			setCookie( "pagePos" , JSON.stringify( ps ) , 0 );
			window.location.reload();
		} else {
			var res = prompt( "Для подтверждения операции введите слово П О Д Т В Е Р Ж Д А Ю без пробелов" );
			if ( res == "ПОДТВЕРЖДАЮ" ) {
				var res = sendXML( "<uncheck id=\"" + pcid + "\"/>" , false , "payments.php" );
				window.location.reload();
			} else
			if ( res == null ) {
				pc.checked = true ;
			} else {
				alert( "Не принято" );
				pc.checked = true ;
			}
		}
	}

	var currentEditComment = null ;
	var editCommentInput = document.createElement( "textarea" );
	editCommentInput.className = "elt-d-com-ta" ;
	editCommentInput.onkeypress = takp ;
	editCommentInput.onblur = tab ;

	var ccth = null ;

	function updComment() {
		if ( currentEditComment != null && currentEditComment.ct != editCommentInput.value ) {
			var res = sendXML( "<comment id=\"" + currentEditComment.pcid + "\" eid=\"" + currentEditComment.eid + "\">" + toCDATA( editCommentInput.value ) + "</comment>" , true , "payments.php" );
			currentEditComment.ct = editCommentInput.value ;
		}
		ccth = null ;
	}
	
	function updSubpoenaPrice( pid , rid ) {
		var sppA = document.getElementById( "spp-" + pid );
		if ( sppA == null ) {
			return ;
		}
		
		var dv = sppA.dataset[ "price" ];
		while ( true ) {
			var np = prompt( "Укажите новую цену" , dv );
			if ( np == null ) {
				return ;
			}
			
			var m = np.match( /^\s*(\d+(?:[.,]\d{2})?)\s*$/ );
			m = ( m == null || m.length != 2 );
			if ( m ) {
				alert( "Неверный формат числа" );
				dv = np ;
				continue ;
			}
			
			break ;
		}
		
		var res = sendXML( "<update-spp id=\"" + pid + "\">" + toCDATA( np ) + "</update-spp>" , false , "payments.php" );
		if ( res.getAttribute( "state" ) == "ok" ) {
			sppA.dataset[ "price" ] = res.getAttribute( "pe" );
			setText( sppA , res.getAttribute( "pp" ) );
			var cid = res.getAttribute( "cid" );
			var comment = getXMLNodeValue( res );
			var cct = document.getElementById( "comment-text-" + cid );
			if ( cct == null ) {
				var pcc = document.getElementById( "pcc_" + rid );
				if ( pcc == null ) {
					alert( "Обновите страницу!" );
					return ;
				}
				var ucComment = document.createElement( "div" );
				ucComment.className = "uc-comment" ;
					cct = document.createElement( "span" );
					cct.className = "uc-text" ;
					cct.id = "comment-text-" + cid ;
					ucComment.appendChild( cct );
					
					var tmp = document.createElement( "span" );
					tmp.className = "uc-author" ;
					tmp.appendChild( document.createTextNode( res.getAttribute( "ca" ) ) );
					ucComment.appendChild( tmp );
					
					var tmp = document.createElement( "div" );
					tmp.style.clear = "both" ;
					ucComment.appendChild( tmp );
					
				pcc.parentNode.insertBefore( ucComment , pcc ); 
			}
			
			setText( cct , comment );
		} else {
			alert( getXMLNodeValue( res ) );
		}
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

	function gmp( o ) {
		for( var px = 0 , py = 0 ; o.offsetParent ; o = o.offsetParent ) {
			px+= o.offsetLeft ;
			py+= o.offsetTop ;
		}
		return [ px , py ];
	}


	function fdc( fld , event ) {
		event = window.event ? window.event : event ;
		event.target = event.target ? event.target : event.srcElement ;

		var dlg = document.getElementById( "filter_dialog" );
		var fi = document.getElementById( "i_search" );
		var fd = document.getElementById( "i_flt__" + fld );
		var ft = document.getElementById( "i_flt_ft" );
		ft.value = fld ;

		var dlgPos = gmp( event.target.parentNode );
		dlg.style.left = dlgPos[ 0 ] + "px" ;
		dlg.style.top = ( dlgPos[ 1 ] + event.target.parentNode.offsetHeight - 4 ) + "px" ;
		fi.style.width = ( event.target.parentNode.offsetWidth - 13 ) + "px" ;

		fi.value = fd.value ;

		if ( dlg.style.display == "none" ) {
			dlg.style.display = "" ;
		}

		fi.focus();
	}

	function fdb() {
		var dlg = document.getElementById( "filter_dialog" );
		dlg.style.display = "none" ;
	}

	function toggleVisibility() {
		var su = document.getElementById( "i_show_unchecked" );
		var sc = document.getElementById( "i_show_checked" );

		var c = document.getElementById( "i_contracts" );
		var n = document.getElementById( "i_no_contracts" );
		var bso = document.getElementById( "i_by_subpoenas_only" );

		var gb = document.getElementById( "i_group_by" );

		setCookie( "hideUnChecked" , ( su.checked ? "0" : "1" ) , 0 );
		setCookie( "showChecked" , ( sc.checked ? "1" : "0" ) , 0 );

		if ( c.checked && n.checked ) {
			setCookie( "contractFilter" , "all" , 0 );
		} else
		if ( c.checked && !n.checked ) {
			setCookie( "contractFilter" , "contract" , 0 );
		} else
		if ( !c.checked && n.checked ) {
			setCookie( "contractFilter" , "nocontract" , 0 );
		} else {
			setCookie( "contractFilter" , "none" , 0 );
		}
		
		setCookie( "bySubpoenasOnly" , ( bso.checked ? "1" : "0" ) , 0 );

		setCookie( "groupBy" , gb.value , 0 );


		window.location.reload();
	}
	
	function toggleResultLimit() {
		var rl = document.getElementById( "i_result_limit" );

		setCookie( "resultLimit" , ( rl.checked ? "1" : "0" ) , 0 );

		window.location.reload();
	}



	function updateVisibility( e , s ) {
		if ( e.checked != s ) {
			e.parentNode.style.backgroundColor = "#ff0000" ;
		} else {
			e.parentNode.style.backgroundColor = "" ;
		}
	}

	function updateVisibilityState() {
		var su = document.getElementById( "i_show_unchecked" );
		var sc = document.getElementById( "i_show_checked" );

		var nsus = getCookie( "hideUnChecked" ) != "1" ;
		var nscs = getCookie( "showChecked" ) == "1" ;

		updateVisibility( su , nsus );
		updateVisibility( sc , nscs );

		var c = document.getElementById( "i_contracts" );
		var n = document.getElementById( "i_no_contracts" );
		var bso = document.getElementById( "i_by_subpoenas_only" );

		var ncns = getCookie( "contractFilter" );
		switch ( ncns ) {
			case "all" :
				var ncs = true ;
				var nns = true ;
				break ;
			case "contract" :
				var ncs = true ;
				var nns = false ;
				break ;
			case "nocontract" :
				var ncs = false ;
				var nns = true ;
				break ;
			case "none" :
				var ncs = false ;
				var nns = false ;
				break ;
		}

		updateVisibility( c , ncs );
		updateVisibility( n , nns );
		
		var nbsos = getCookie( "bySubpoenasOnly" );
		updateVisibility( bso , nbsos );

		var gb = document.getElementById( "i_group_by" );
		var ngbs = getCookie( "groupBy" );
		var e = gb.parentNode ;
		if ( gb.value != ngbs ) {
			e.style.backgroundColor = "#ff0000" ;
		} else {
			e.style.backgroundColor = "" ;
		}

	}

	function toggleYears() {
		var my = ( new Date() ).getFullYear();
		for ( var i = 2010 ; i <= my ; i++ ) {
			var sy = document.getElementById( "i_show_year_" + i );
			if ( sy != null ) {
				setCookie( "showYear_" + i , ( sy.checked ? "1" : "0" ) , 0 );
			}
		}
		window.location.reload();
	}

	function updateYearsState() {
		var my = ( new Date() ).getFullYear();
		for ( var i = 2010 ; i <= my ; i++ ) {
			var sy = document.getElementById( "i_show_year_" + i );
			if ( sy != null ) {
				var nsys = getCookie( "showYear_" + i ) == "1" ;
	
				var e = sy.parentNode ;
	
				if ( sy.checked != nsys ) {
					e.style.backgroundColor = "#ff0000" ;
				} else {
					e.style.backgroundColor = "" ;
				}
			}
		}
	}
	
	function setCheckDate( id , date ) {
		while ( true ) {
			var d = prompt( "Укажите дату в формате dd-mm-YYYY" , date );
			if ( d == null ) {
				return ;
			}
			
			var m = d.match( /^\s*([0-2]\d|3[0-1])[.,-](0\d|1[0-2])[.,-](?:20)?(\d{2})\s*$/ );
			if ( m == null || m.length > 4 ) {
				alert( "Неверный формат даты" );
				date = d.trim();
				continue ;
			} else {
				date = d.trim();
				break ;
			}
		}
		
		var doc = sendXML( "<setCheckDate id=\"" + id + "\">" + date + "</setCheckDate>" );
		if ( ( doc.documentElement.getAttribute( "state" ) ) != "ok" ) {
			alert( "Ошибка" );
		} else {
			window.location.reload();
		}
	}

	function marksMenu( rID , pID ) {
		var f = mk_makeElement( "marks--" );

		var smd = new $.TDLGSimpleMenu( JSON.parse( marksCatalog ) , { title : 'test' , itemMkFunc : function( mkElem , mkDiv , rowID , paymentID ) {
				return function( item ) {
					item.pid = paymentID ;
					item.rid = rowID ;
					var res = mkDiv( "item" );
					res.appendChild( document.createTextNode( item.name ) );
					return res ;
				};
			}( f , f , rID , pID ) } );

		smd.show().then( function( item ) {
			var req = '<toggle-mark id="' + item.id + '" pid="' + item.pid + '" />' ;
			var res = sendXML( req , false , "payments.full.php" );
			if ( res.getAttribute( 'state' ) == 'ok' ) {
				var action = res.getAttribute( 'action' );
				switch( action ) {
					case 'created' :
						var container = document.getElementById( 'pr_' + item.rid + '_mark' );
						var me = document.createElement( 'div' );
						me.className = 'std-marks-' + item.style ;
						me.style.display = 'inline-block' ;
						me.dataset.markCoid = item.id + ':' + item.pid ;
						var tc = document.createElement( 'div' );
						tc.className = 'std-marks-text-container' ;
						tc.appendChild( document.createTextNode( item.name ) );
						me.appendChild( tc );
						container.appendChild( me );
						break ;

					case 'deleted' :
						var itemList = document.querySelectorAll( '[data-mark-coid="' + item.id + ':' + item.pid + '"]' );
						if ( itemList.length > 0 ) {
							itemList[ 0 ].parentElement.removeChild( itemList[ 0 ] );
						}
						break ;
				}
			}
		} );

		window.event.returnValue = false ;
	}
	