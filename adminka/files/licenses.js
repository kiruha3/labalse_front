	var $ = {};

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

			var req = "<get_joined id=\"" + id + "\" />" ;
			var res = sendXML( req , false );
			var doc = res.documentElement ;
			var txt = "<table align=\"center\" class=\"cllt\"><tr><td class=\"cllch-1\">Обладатель</td><td class=\"cllch-2\">Дата</td></tr>" ;
			var dcnl = doc.childNodes.length ;
			var dcnd = 25 ;
			var dcndi = 1 ;
			for( var i = 0 ; i < dcnl ; i++ ) {
				var data = doc.childNodes[ i ];
				var licDate = data.getAttribute( "d" );
				var licUser = "" ;
				for( var j = 0 ; j < data.childNodes.length ; j++ ) {
					switch( data.childNodes[ j ].nodeName ) {
						case "user" :
							licUser = getText( data.childNodes[ j ] );
							break ;
					}
				}

				if ( dcndi - 1 == dcnd ) {
					txt+= "<table align=\"center\" class=\"cllt\"><tr><td class=\"cllch-1\">Обладатель</td><td class=\"cllch-2\">Дата</td></tr>" ;
					dcndi = 1 ;
				}

				if ( dcndi < dcnd && i + 1 < dcnl ) {
					txt+= "<tr><td class=\"cllc-1 cllc-b\">" + licUser + "</td><td class=\"cllc-2 cllc-b\">" + licDate + "</td></tr>" ;
				} else {
					txt+= "<tr><td class=\"cllc-1\">" + licUser + "</td><td class=\"cllc-2\">" + licDate + "</td></tr>" ;
				}

				dcndi++ ;
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
	}( "licenseTooltip" );

	function addText( c , t ) {
		/*if ( typeof( c.innerText ) == "undefined" ) {
			c.textContent = t ;
		} else {
			c.innerText = t ;
		}*/

		c.appendChild( document.createTextNode( t ) );
	}

	function addSpan( c , t , s ) {
		var span = document.createElement( "span" );
		if ( typeof( s ) != "undefined" ) {
			span.className = s ;
		}
		addText( span , t );
		c.appendChild( span );
	}

	function addInput( c , v , id , t , s ) {
		if ( typeof( t ) == "undefined" ) {
			t = "text" ;
		}

		if ( t == "textarea" ) {
			var i = document.createElement( "textarea" );
		} else {
			var i = document.createElement( "input" );
		}
		i.id = id ;
		i.name = id ;

		if ( typeof( s ) != "undefined" ) {
			i.className = s ;
		} else {
			i.className = id ;
		}
		i.setAttribute( "type" , t );
		if ( t == "checkbox" || t == "radio" ) {
			i.checked = ( v ? true : false );
		} else {
			i.value = v ;
		}
		c.appendChild( i );
	}



	function addBreak( c ) {
		var br = document.createElement( "br" );
		c.appendChild( br );
	}

	function addOption( s , v , t ) {
		var o = document.createElement( "option" );
		o.value = v ;
		if ( typeof( t ) == "undefined" ) {
			o.text = v ;
		} else {
			o.text = t ;
		}
		s.options.add( o );
	}

	function getText( c ) {
		if ( typeof( c.textContent ) == "undefined" ) {
			return c.text ;
		} else {
			return c.textContent ;
		}
	}

	window.onload = function() {

		var ttmd = document.createElement( "div" );
		ttmd.id = $.Tooltip.tmid ;
		ttmd.style.display = "none" ;
		document.body.appendChild( ttmd );

		var ot = document.getElementById( "orders-table" );
	};

	function sendXML( data , async ) {
		var sd = "random=" + ( new Date() ).getTime() + ( Math.random() * 1000 ) + "&mode=ajax&data=" + encodeURIComponent( "<?xml version=\"1.0\" encoding=\"utf-8\" ?>" + data );
		var req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( "Microsoft.XMLHTTP" );
		}

		if ( req ) {
			req.open( "POST" , "licenses.php" , async );
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

	function showKey( lid ) {
		var res = sendXML( "<get_license_user />" , false );
		var doc = res.documentElement ;
		var v = prompt( "Новый обладатель лицензии" , getText( doc ) );
		if ( v != null ) {
			var res = sendXML( "<get_key id=\"" + lid + "\">" + toCDATA( v ) + "</get_key>" , false );
			var doc = res.documentElement ;
			if ( doc.getAttribute( "state" ) == "ok" ) {
				prompt( "Ключ" , getText( doc ) );
				window.location.reload( true );
			} else {
				alert( "Ключ не выдан" );
			}
		}
	}

	function storeKey( lid ) {
		var res = sendXML( "<get_license_user />" , false );
		var doc = res.documentElement ;
		var v = prompt( "Новый обладатель лицензии" , getText( doc ) );
		if ( v != null ) {
			var res = sendXML( "<get_key id=\"" + lid + "\">" + toCDATA( v ) + "</get_key>" , false );
			var doc = res.documentElement ;
			if ( doc.getAttribute( "state" ) == "ok" ) {
				window.location.href = getText( doc );
			} else {
				alert( "Ключ не выдан" );
			}
		}
	}
