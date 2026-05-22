/**
 * 
 */

	$.TDLGGlobalSearch = function( parent , options ) {
		this.www = 800 ;
		this.OT = "" ;
		this.ONT = null ;
		this.rlt = null ;
		this.srcList = [];
		this.srclSE = false ;
		this.searchScope = [];
		this.reqID = 0 ;
		this.checkbox = false ;
		this.options = options ;

		this.searchScopeAllowed = {};
		options[ 'search-scope-allowed' ].split( ',' ).forEach( function( v ) {
			this.searchScopeAllowed[ v ] = true ;
		} , this );

		
		this.scopeMap = {
			exp : {
				cap : "база"
			} ,
			bills : {
				cap : "счета"
			} ,
			pay : {
				cap : "оплата"
			} ,
			timet : {
				cap : "выезды"
			} ,
			subpoena : {
				cap : "повестки"
			} ,
			portal : {
				cap : "ресурс"
			} ,
			cor_t1 : {
				cap : "журнал"
			} ,
			cor_t2 : {
				cap : "журнал"
			} ,
			cor_t3 : {
				cap : "журнал"
			} ,
			cor_t4 : {
				cap : "журнал"
			} ,
			all : {
				cap : "все"
			}
		};

		this.state = "hidden" ;
		
		if ( parent != null ) {
			this.embedded = true ;
		} else {
			parent = document.body ;
			this.embedded = false ;
		}
		this.parent = parent ;
		
		var dlg = document.createElement( "div" );
		dlg.className = "tdlg-global-search" ;
		
			var sp = document.createElement( "div" );
			sp.className = "tdlg-global-search-panel" ;
		
				var wer1 = document.createElement( "div" );
				wer1.id = "wer-1" ;
				wer1.className = "wer-1" ;
				
					var werss = document.createElement( "div" );
					werss.className = "wer-ss" ;
						werss.appendChild( document.createTextNode( "все" ) );
					wer1.appendChild( werss );

					var wersl = document.createElement( 'div' );
					wersl.className = 'wer-sl' ;
					wersl.style.display = 'none' ;
					for( var csi in this.scopeMap  ) {
						if ( this.searchScopeAllowed[ csi ] ) {
							this.searchScope.push( csi );
							var wersli = document.createElement( 'label' );
							wersli.className = 'wer-sli' ;
								var wleS = document.createElement( 'div' );
								wleS.className = 'wle-s-' + csi ;
									var wleCap = document.createElement( 'span' );
									wleCap.className = 'wle-cap' ;
									wleCap.appendChild( document.createTextNode( this.scopeMap[ csi ].cap ) );
									wleS.appendChild( wleCap );
									var sscb = document.createElement( 'input' );
									sscb.type = 'checkbox' ;
									sscb.checked = true ;
									sscb.onchange = sscb.onpropertychange = function( x , y , z ) {
										return function() {
											var pos = z.searchScope.indexOf( y );
											if ( z.searchScopeAllowed[ y ] && x.checked ) {
												if ( pos == -1 ) {
													z.searchScope.push( y );
												}
											} else {
												if ( pos > -1 ) {
													z.searchScope.splice( pos , 1 );
												}
											}
											z.reloadList( true );
										};
									} ( sscb , csi , this );
									wleS.appendChild( sscb );
								wersli.appendChild( wleS );
							wersl.appendChild( wersli );
						}
					}

					werss.onclick = function( x ) {
						return function() {
							if ( x.style.display == 'none' ) {
								x.style.display = '' ;
							} else {
								x.style.display = 'none' ;
							}
						}
					} ( wersl );


					wer1.appendChild( wersl );

	
					var werw = document.createElement( "div" );
					werw.className = "wer-w" ;
					
						var wer21 = document.createElement( "input" );
						wer21.id = "wer-2-1" ;
						wer21.type = "text" ;
						wer21.className = "wer-2-1" ;
						werw.appendChild( wer21 );
						
						var wer22 = document.createElement( "input" );
						wer22.id = "wer-2-2" ;
						wer22.type = "text" ;
						wer22.className = "wer-2-2" ;
						werw.appendChild( wer22 );
						
					wer1.appendChild( werw );
						
					var werl = document.createElement( "div" );
					werl.id = "wer-l" ;
					werl.className = "wer-l" ;
					werl.style.display = "none" ;
					wer1.appendChild( werl );
						
					var dbg = document.createElement( "div" );
					dbg.id = "dbg" ;
					dbg.style.display = "none" ;
					wer1.appendChild( dbg );
				
				sp.appendChild( wer1 );
			
			dlg.appendChild( sp );
				
		parent.appendChild( dlg );
		
		this.hideList = function ( x ) {
			return function( evt ) {
				setTimeout( function() {
					x.style.display = "none" ;
				} , 300 );
			};
		}( werl );
		
		this.showList = function ( x ) {
			return function( evt ) {
				x.style.display = "" ;
			};
		}( werl );
		
		this.keyDown = function( x , srci , srcb , srcl ) {
			return function ( evt ) {
				evt = evt || window.event ;				
				if ( ( evt.keyCode == 39 || evt.keyCode == 35 ) && ( srci.selectionStart == srci.value.length || srci.selectionEnd == srci.value.length ) ) {					
					if ( x.ONT != null ) {
						srcb.value = srci.value = x.ONT ;
					}
				}
				
				if ( evt.keyCode == 40 && x.srcList.length > 0 ) {
					if ( x.srclSE === false ) {
						x.srclSE = 0 ;
					} else {
						x.srclSE++ ;
						if ( x.srclSE >= x.srcList.length ) {
							x.srclSE = 0 ;
						}
					}
					
					x.hlListEl( x.srclSE );
				}
				
				if ( evt.keyCode == 38 && x.srcList.length > 0 ) {
					if ( x.srclSE === false ) {
						x.srclSE = x.srcList.length - 1 ;
					} else {
						x.srclSE-- ;
						if ( x.srclSE < 0 ) {
							x.srclSE = x.srcList.length - 1 ;
						}
					}
					
					x.hlListEl( x.srclSE );
				}
				
				if ( evt.keyCode == 13 && x.srcList.length > 0 && x.srclSE !== false ) {
					if ( x.srclSE >= 0 && x.srclSE < x.srcList.length ) {
						x.srcList[ x.srclSE ].onclick();
					}
				}
				
				//document.getElementById( "wer-1" ).style.width = ( www-- ) + "px" ;
			};
		}( this , wer22 , wer21 , werl );

		//this.keyUp = function( x , srci , srcb , srcl ) {
		this.reloadList = function( x , srci , srcb , srcl ) {
			return function ( forceReload ) {
				if ( srci.value == "" ) {
					srcb.value = "" ;
					srcl.style.display = "none" ;
				} else
				if ( ( srci.value != x.OT ) || forceReload ) {
					if ( ( srci.value.toLowerCase() == x.OT.toLowerCase() ) && !forceReload ) {
						srcb.value = srci.value ;
						x.OT = srci.value ;
					} else {			
						if ( x.rlt != null ) {
							clearTimeout( x.rlt );
						}
						
						if ( x.ONT == null || srci.value.toLowerCase() != x.ONT.substr( 0 , srci.value.length ).toLowerCase() ) {
							//setText( srcb , srci.value );
							srcb.value = srci.value ;
						}
						//x.rlt = setTimeout( x.updList , 500 );
						x.reqID++ ;
						sendXML(
							"<getList searchScope=\"" + x.searchScope.join( "," ) + "\">" + toCDATA( srci.value ) + "</getList>" ,
							true , "/search.ajax.php" , "" , false ,
							function ( y , z ) {
								return function( xhr ) {
									y.updList( xhr , z );
								};
							}( x , x.reqID ) );
					}
				}
			};
		}( this , wer22 , wer21 , werl );

		this.keyUp = function( t ) {
			return function ( event ) {
				t.reloadList( false );
			};
		}( this );
		
		this.hlListEl = function( x , srci , srcb , srcl ) {
			return function ( se ) {
				if ( x.srcList.length == 0 ) {
					return ;
				}
				
				for( var i = 0 ; i < x.srcList.length ; i++ ) {
					x.srcList[ i ].className = "wle" ;
				}
				
				if ( se < 0 || se >= x.srcList.length  ) {
					return ;
				}
				
				var cse = x.srcList[ se ];
				
				cse.className = "wle-hl" ;
				x.srclSE = se ;
				
				if ( cse.offsetTop < srcl.scrollTop ) {
					srcl.scrollTop = cse.offsetTop ;
				} else
				if ( cse.offsetTop + cse.offsetHeight > srcl.scrollTop + srcl.offsetHeight ) {
					srcl.scrollTop = cse.offsetTop + cse.offsetHeight - srcl.offsetHeight ;
				}
			};
		}( this , wer22 , wer21 , werl );
		
		this.updList = function( x , srci , srcb , srcl ) {
			return function ( xhr , reqid ) {
				if ( x.reqID !== reqid ) {
					return ;
				}
				
				//var doc = sendXML( "<getList searchScope=\"" + x.searchScope.join( "," ) + "\">" + toCDATA( srci.value ) + "</getList>" , false , "/search.ajax.php" );
				var doc = xhr.responseXML.documentElement ;
				
				var e0 = null ;
				srcl.innerHTML = "" ;
				var stdClickFunc = function( t , st ) {
					return function() {
						srci.value = srcb.value = t ;
						srcl.style.display = "none" ;
						if ( typeof st === "undefined" ) {
							st = [];
						}
						x.searchScope = st ;
					};
				};
				var stdClickFuncRedir = function( base , params ) {
					return function() {
						window.open( base + "?" + params );
					};
				};
				
				x.srcList = [];
				x.srclSE = false ;
				var e0a = true ;
								
				for( var i = 0 ; i < doc.childNodes.length ; i++ ) {
					var le = doc.childNodes[ i ];
					if ( le.nodeName == "r" ) {
						var let = le.getAttribute( "t" );
						var resType = le.getAttribute( "rt" );
						var les = le.getAttribute( "s" );
						var tmpdT = getXMLNodeValue( le );
						var tmpd = document.createElement( "div" );
						tmpd.className = "wle";

						var tmpid = document.createElement( "div" );
						tmpid.className = "wle-s-" + les;
						tmpid.innerHTML = (x.checkbox ? "<label><input type=\"checkbox\">" : "") + "<span class=\"wle-cap\">" + x.scopeMap[ les ].cap + "</span> " +
							tmpdT
								.replace( /\[\/[b]\]/gi , "</span>" )
								.replace( /\[b\]/gi , "<span class=\"wle-b\">" ) + (x.checkbox ? "</label>" : "");


						var data1 = le.getAttribute( "data1" );

						if ( resType == "lnk" ) {
							switch ( let ) {
								case "exp1" :
								case "expc" :
									tmpd.onclick = stdClickFuncRedir( "/maindb/main.php" , data1 );
									break;

								case "bill1" :
									tmpd.onclick = stdClickFuncRedir( "/bills/bill.print.php" , data1 );
									break;

								case "billc" :
									tmpd.onclick = stdClickFuncRedir( "/bills/list.php" , data1 );
									break;

								case "payn" :
									tmpd.onclick = function () {
									};
									break;
								case "pay1" :
									tmpd.onclick = stdClickFuncRedir( "/maindb/writ-of-execution.php" , data1 );
									break;
								case "pay2" :
									tmpd.onclick = stdClickFuncRedir( this.options[ 'payment-addr' ] ? this.options[ 'payment-addr' ] : '/maindb/payments.php' , data1 );
									break;
								case "payc" :
									tmpd.onclick = stdClickFuncRedir( "/maindb/writ-of-execution.list.php" , data1 );
									break;

								case "timet1" :
								case "timetc" :
									tmpd.onclick = stdClickFuncRedir( "/time_table/main.php" , data1 );
									break;

								case "subpoena1" :
								case "subpoenac" :
									tmpd.onclick = stdClickFuncRedir( "/maindb/subpoenas.php" , data1 );
									break;

								case "portal-adm-1" :
								case "portal-adm-c" :
									tmpd.onclick = stdClickFuncRedir( "/adminka/accounts.php" , data1 );
									break;

								case "cor1-t1" :
								case "cor1-t1c" :
								case "cor1-t2" :
								case "cor1-t2c" :
								case "cor1-t3" :
								case "cor1-t3c" :
								case "cor1-t4" :
								case "cor1-t4c" :
									tmpd.onclick = stdClickFuncRedir( "/maindb/correspondence.php" , data1 );
									break;

								case "words" :
									if ( e0a && e0 === null ) {
										e0 = data1;
									}
									tmpd.onclick = stdClickFunc( data1 , [] );
									break;

								case "all" :
								default :
									//tmpid.innerHTML += tmpdT ;
									tmpd.onclick = stdClickFunc( tmpdT , [] );
									break;
							}
						} else if ( resType == "search" ) {
							tmpd.onclick = stdClickFunc( data1 , [] );
						}

						e0a = false;

						/*if ( doc.childNodes[ i ].getAttribute( "e" ) == "1" ) {
							tmpd.style.color = "#ff0000" ;
						}
						tmpd.appendChild( document.createTextNode( tmpdT ) );
						*/

						tmpd.onmouseover = tmpd.onmouseenter = function ( y , i ) {
							return function () {
								y.hlListEl( i );
							};
						}( x , x.srcList.length );
						tmpd.onmouseout = tmpd.onmouseleave = function ( y , i ) {
							return function () {
								y.hlListEl( -1 );
							};
						}( x , x.srcList.length );

						tmpd.appendChild( tmpid );
						srcl.appendChild( tmpd );
						x.srcList.push( tmpd );
					} else {
					}
				}
				
				if ( x.srcList.length > 0 ) {
					if ( e0 != null ) {
						//setText( srcb , e0 );
						srcb.value = srci.value + e0.substr( srci.value.length );
					}
					
					srcl.style.display = "" ;
				}
				
				x.ONT = e0 ;
				x.OT = srci.value ;
				x.rlt = null ;
			};
		}( this , wer22 , wer21 , werl );



		
		wer22.onkeydown = function ( x ) {
			return function( event ) {
				x.keyDown( event );
			};
		}( this );
		wer22.onkeyup = function ( x ) {
			return function( event ) {
				x.keyUp( event );
			};
		}( this );
		wer22.onblur = function ( x ) {
			return function( event ) {
				x.hideList( event );
			};
		}( this );
		wer22.onfocus = function ( x ) {
			return function ( event ) {
				x.showList( event );
			};
		}( this );
		
		this.form = dlg ;
		this.input = wer22 ;
		
		if ( !this.embedded ) {
			//  sdaf - search dialog activation function
			var sdaf = function ( x , y ) {
				return function ( a ) {
					if ( isUndefined( a ) ) {
						a = "toggle" ;
					}
					if ( x.state == "hidden" && ( a == "show" || a == "toggle" ) ) {
						x.form.style.top = "10cm" ;
						x.state = "visible" ;
						x.input.focus();
					} else
					if ( x.state != "hidden" && ( a == "hide" || a == "toggle" ) ) {
						y.style.display = "none" ;
						x.input.blur();
						x.form.style.top = "-5cm" ;
						x.state = "hidden" ;
					}
				};
			}( this , werl );
			
			if ( "onhelp" in window ) {
				window.onhelp = function() {
					sdaf();
					return false ;
				};
				document.onkeydown = function ( x , y ) {
					return function( evt ) {
						x.cancelKeypress = ( evt.keyCode == 27 );
						if ( x.cancelKeypress ) {
							y( "hide" );
							return false ;
						}
					};
				}( this , sdaf );	
			} else {
				document.onkeydown = function ( x , y ) {
					return function( evt ) {
						x.cancelKeypress = ( evt.keyCode == 112 || evt.keyCode == 27 );
						if ( x.cancelKeypress ) {
							y( evt.keyCode == 27 ? "hide" : "toggle" );
							return false ;
						}
					};
				}( this , sdaf );
				
				document.onkeypress = function( x ) {
					return function ( evt ) {
						if ( x.cancelKeypress ) {
							return false ;
						}
					};
				}( this );
			}
		}

	};

	$.windowOnLoad.push( function () {
		var opt = sendXML(
			'<get-opt />' ,
			false , '/search.ajax.php' ,
			'get_opt=1' , false ,
		);

		var ocn = opt.childNodes ;
		var dlgOpt = {};
		for( var i = 0 ; i < ocn.length ; i++ ) {
			dlgOpt[ ocn[ i ].nodeName ] = getXMLNodeValue( ocn[ i ] );
		}

		$.searchDlg = new $.TDLGGlobalSearch( document.getElementById( "search-frame-reserver" ) , dlgOpt );
	} );

	$.Table = function( p , c , o ) {
		this.parent = p ;
		this.columns = c ;
		this.options = o ;
		
		this.mainDiv = document.createElement( "div" );
	};
	