
	$.TDLGInputTemplate = function( data , opt ) {

		this.appendMode = false ;
		this.mode ;
		if ( opt && opt.base64 ) {
			data = JSON.parse( unescape( atob( data ) ) );
		}
		this.data = data ;
		var dataMap = remap( data , 'e' );
		this.dataMap = dataMap ;
		this.extVar = [];
		if ( opt && opt.variables ) {
			this.extVar = opt.variables ;
		}
		this.extFunc = [];
		if ( opt && opt.functions ) {
			this.extFunc = opt.functions ;
		}
		this.options = opt ;
		this.currentTmpl = null ;
		this.oldTmpl = null ;
		this.target = null ;
		this.assigned = {};
		this.params = {};

		/* ok */
		this.show = function() {
			var dgl = this.dlg ;
			dlg.style.display = '' ;
			dlg.focus();
		};
		/* ok */
		this.hide = function() {
			var dgl = this.dlg ;
			dlg.blur();
			dlg.style.display = 'none' ;
		};

		this.doItemSelect = function( item , target ) {
			var state = true ;
			var wPromises = [];
			var y = item.pv.replace( /%#([^%]+)%/g , function( m , fn ) {
				var rfn ;
				var rfcp = '' ;
				var fc = fn.match( /^([^()]+)((?:\([^)]*\))?)$/ );
				if ( fc ) {
					rfn = fc[ 1 ];
					rfcp = fc[ 2 ];
				} else {
					rfn = fn ;
				}
				var res = functionByName( rfn , window , fn , rfcp , m );
				if ( res && res.then && typeof res.then == 'function' ) {
					wPromises.push( res );
					return m ;
				} else {
					if ( res == null ) {
						state = false ;
					} else {
						return res ;
					}
				}
			} );
			if ( !state ) {
				return ;
			}
			var newValueObject = { nv : y };
			var allDone = function( o , tgt , nvo ) {
				return function() {
					if ( o.appendMode && ( tgt.value.trim().length > 0  ) ) {
						tgt.value+= " ; " + nvo.nv ;
					} else {
						tgt.value = nvo.nv ;
					}
					o.close();
				};
			} ( this , target , newValueObject );
			if ( wPromises.length > 0 ) {
				Promise.allSettled( wPromises ).then( function( nvo , adf ) {
					return function( results ) {
						for( var i = 0 ; i < results.length ; i++ ) {
							var cr = results[ i ];
							nvo.nv = nvo.nv.replace( cr.value.om , cr.value.value );
						}
						adf();
					}
				}( newValueObject , allDone ) );
			} else {
				allDone();
			}
		};

		/* ok? */
		this.processInput = function ( event ) {
			if ( this.mode && this.mode == 'config' ) {
				return true ;
			}

			event = event || window.event ;
			if ( event.type == 'keyup' ) {
				if ( event.keyCode == 27 ) {
					this.close();
				}
				return false ;
			}

			var target = this.target ;

			if ( event.type == 'keypress' ) {
				var b = getChar( event );
				if ( b == "*" ) {
					this.close();
					target.value+= "*" ;
					return false ;
				}

				if ( b == "+" ) {
					this.toggleAppendMode();
				}

				var l = this.currentTmpl.l ;
				for( var i = 0 ; i < l.length ; i++ ) {
					var li = l[ i ];
					if ( li.k == b ) {
						this.doItemSelect( li , target );
						break ;
					}
				}
			}

			return false ;
		};

		/* ok */
		this.makeList = function () {
			var l = this.currentTmpl.l ;
			if ( this.params[ this.currentTmpl.e ] ) {
				var param = this.params[ this.currentTmpl.e ];
			} else {
				var param = [];
			}
			if ( param.listLoader ) {
				l = l.concat( param.listLoader() );
			}
			var ll = l.length ;
			var tab = this.tab ;
			var ev = this.extVar ;
			var evl = ev.length ;
			var ef = this.extFunc ;
			var efl = ef.length ;
			for( var i = 0 ; i < ll ; ++i ) {
				var li = l[ i ];
				var te = tab.insertRow( -1 ).insertCell( -1 );
				te.parentNode.className = 'tdlg-input-template-tab-row' ;
				var a = document.createElement( 'a' );
				a.className = 'tdlg-input-template-lnk' ;
				var k = document.createElement( 'span' );
				k.className = 'tdlg-input-template-lnk-l' ;
				k.appendChild( document.createTextNode( li.k ) );
				a.appendChild( k );

				var cap = li.v ;
				for( var j = 0 ; j < efl ; j++ ) {
					var efj = ef[ j ];
					cap = cap.replace( new RegExp( "%#" + efj.k + "%" , 'g' ) , efj.l );
				};

				var t = li.v ;
				for( var j = 0 ; j < evl ; j++ ) {
					var evj = ev[ j ];
					t = t.replace( new RegExp( "%" + evj.k + "%" , 'g' ) , evj.v );
					cap = cap.replace( new RegExp( "%" + evj.k + "%" , 'g' ) , evj.v );
				};
				a.appendChild( document.createTextNode( " " + cap ) );
				li.pv = t ;
				a.onclick = function( dlg , item ) {
					return function() {
						dlg.doItemSelect( item , dlg.target );
					};
				}( this , li );
				te.appendChild( a );
			}
		};

		/* ok? */
		this.makeListCfgMode = function () {
			var l = this.currentTmpl.l ;
			var ll = l.length ;
			var tab = this.tab ;
			var ev = this.extVar ;
			var evl = ev.length ;
			for( var i = 0 ; i < ll ; i++ ) {
				var li = l[ i ];
				li.ve = null ;
				var te = tab.insertRow( -1 ).insertCell( -1 );
				te.parentNode.className = 'tdlg-input-template-tab-row' ;
				var a = document.createElement( 'a' );
				a.className = 'tdlg-input-template-lnk' ;
				var tmp = document.createElement( 'span' );
				tmp.className = 'tdlg-input-template-lnk-l' ;
				tmp.appendChild( document.createTextNode( li.k ) );
				a.appendChild( tmp );
				var st = ( " " + li.v ).split( /([%][a-z0-9_.]+[%])/i );
				for( var j = 0 ; j < st.length ; j++ ) {
					var stj = st[ j ];
					if ( stj.substr( 0 , 1 ) == "%" ) {
						for( var k = 0 ; k < evl ; k++ ) {
							var evk = ev[ k ];
							if ( stj == "%" + evk.k + "%" ) {
								var tmp = document.createElement( 'span' );
								setText( tmp , stj );
								tmp.title = evk.d ;
								tmp.className = 'tdlg-input-template-lnk-var' ;
								a.appendChild( tmp );
							}
						}
					} else {
						a.appendChild( document.createTextNode( stj ) );
					}
				};
				a.onclick = function( x , y , z ) {
					return function() {
						var w = document.createElement( 'div' );
						w.className = 'tdlg-input-template-e-w' ;
						var ke = document.createElement( 'input' );
						ke.value = z.k ;
						ke.className = 'tdlg-input-template-e-l' ;
						ke.onkeypress = function( x , y ) {
							return function( event ) {
								event = event || window.event ;
								var b = getChar( event );
								x.value = y.k = b ;
								return false ;
							};
						}( ke , z );
						w.appendChild( ke );
						w.appendChild( document.createTextNode( " " ) );
						var ve = document.createElement( 'input' );
						ve.value = z.v ;
						ve.className = 'tdlg-input-template-e-v' ;
						z.ve = ve ;
						w.appendChild( ve );
						x.replaceChild( w , y );
						//x.value = y ;
						//tmplDlgClose();
					};
				}( te , a , li );
				te.appendChild( a );
			}
		};

		/* ok */
		this.toggleAppendMode = function () {
			this.appendMode = !this.appendMode ;
			var m = this.appendModeSign ;
			var ms = m.style ;
			if ( this.appendMode ) {
				ms.backgroundColor = "#0f0" ;
				ms.color = "#000" ;
				setText( m , "+" );
			} else {
				ms.backgroundColor = '' ;
				ms.color = '' ;
				setText( m , '-' );
			}
		};

		/* ok */
		this.create = function ( el , param ) {
			var tn = el.id ;
			if ( this.dataMap[ tn ] ) {
				return ;
			}

			this.data.push( { e : tn , l : [] } );
			this.dataMap = remap( this.data , 'e' );

			this.assign( tn , param );
			this.configApply();
		};


		/* ok */
		this.config = function () {
			var tt = this.currentTmpl ;
			this.oldTmpl = JSON.parse( JSON.stringify( tt ) );
			var dlg = this.dlg ;
			clearTab( this.tab );
			this.makeListCfgMode();
			dlg.replaceChild( this.tbcm , this.tb );
			this.mode = 'config' ;
			dlg.focus();
		};

		/* ok */
		this.configApply = function () {
			var data = this.data ;
			var datal = data.length ;
			var opt = this.options ;
			var params = this.params ;
			var ed = [];
			for( var i = 0 ; i < datal ; i++ ) {
				var dataie = data[ i ].e ;
				if ( params[ dataie ] && params[ dataie ].temporary ) {
					ed.push( i );
					continue ;
				}
				var ctl = data[ i ].l ;
				var ctll = ctl.length ;
				var ek = [];
				for( var j = 0 ; j < ctll ; j++ ) {
					var ctlj = ctl[ j ];
					delete ctlj.pv ;
					if ( ctlj.ve != null ) {
						ctlj.v = ctlj.ve.value ;
					}
					delete ctlj.ve ;

					if ( ctlj.v.trim() == '' ) {
						ek.push( j );
					}
				}

				ek.sort();
				ek.reverse();
				for( var j = 0 ; j < ek.length ; j++ ) {
					ctl.splice( ek[ j ] , 1 );
				}
			}

			ed.sort();
			ed.reverse();
			for( var i = 0 ; i < ed.length ; i++ ) {
				data.splice( ed[ i ] , 1 );
			}

			var tt = JSON.stringify( data );
			if ( opt.updateURL ) {
				sendXML( "<updTmpl>" + toCDATA( tt ) + "</updTmpl>" , false , opt.updateURL );
			} else {
				sendXML( "<updTmpl>" + toCDATA( tt ) + "</updTmpl>" , false );
			}
		};

		/* ok */
		this.configCancel = function () {
			this.currentTmpl.l = JSON.parse( JSON.stringify( this.oldTmpl.l ) );
		};

		/* ok */
		this.configHlp = function () {
			var msg = [];
			var ev = this.extVar ;
			for( var i = 0 ; i < ev.length ; i++ ) {
				var evi = ev[ i ];
				msg.push( "%" + evi.k + "% - " + evi.d );
			}
			alert( "Параметры замены:\n\n" + msg.join( "\n\n" ) );
		};

		/* ok */
		this.close = function () {
			var target = this.target ;

			this.appendMode = false ;
			this.toggleAppendMode();

			this.hide();
			target.focus();
			target.selectionEnd = target.selectionStart = target.value.length ;
		};

		/* ok */
		this.cfgModeClose = function () {
			delete this.oldTmpl ;
			delete this.mode ;
			this.dlg.replaceChild( this.tb , this.tbcm );
			this.close();
		};

		/* ok */
		this.add = function () {
			var z = { k : '' , v : '' };
			this.currentTmpl.l.push( z );

			var te = this.tab.insertRow( -1 ).insertCell( -1 );
			te.parentNode.className = 'tmpl-dlg-tab-row' ;

			var w = document.createElement( 'div' );
			w.className = 'tdlg-input-template-e-w' ;
			var ke = document.createElement( 'input' );
			ke.value = z.k ;
			ke.className = 'tdlg-input-template-e-l' ;
			ke.onkeypress = function( x , y ) {
				return function( event ) {
					event = event || window.event ;
					var b = getChar( event );
					x.value = y.k = b ;
					return false ;
				};
			}( ke , z );
			w.appendChild( ke );
			w.appendChild( document.createTextNode( " " ) );
			var ve = document.createElement( 'input' );
			ve.value = z.v ;
			ve.className = 'tdlg-input-template-e-v' ;
			z.ve = ve ;
			w.appendChild( ve );
			te.appendChild( w );
		};

		/* ok */
		this.assign = function( en , param ) {
			var tmp = document.getElementById( en );
			var dm = this.dataMap ;
			var opt = this.options ;
			var assigned = this.assigned ;
			if ( tmp != null ) {
				if ( typeof dm[ en ] !== 'undefined' ) {
					var dmen = dm[ en ];
					tmp.onkeypress = function( o , x , y ) {
						return function( event ) {
							return o.processFieldInput( event , x , y );
						};
					}( this , tmp , dmen );
				} else {
					dmen = null ;
				}
				if ( assigned[ en ] ) {
					if ( typeof opt.reAssignCallback !== 'undefined' ) {
						opt.reAssignCallback( this , tmp , dmen );
					}
				} else {
					if ( typeof opt.assignCallback !== 'undefined' ) {
						opt.assignCallback( this , tmp , dmen );
						assigned[ en ] = true ;
					}
				}

				if ( param ) {
					this.params[ en ] = param ;
				}
			}
		};

		/* ok */
		this.processFieldInput = function ( event , element , template ) {
			event = event || window.event ;
			var b = getChar( event );
			if ( event.type == 'click' || ( event.type == 'keypress' && b == "*" ) ) {
				this.target = element ;
				this.currentTmpl = template ;

				clearTab( this.tab );
				this.makeList();
				this.show();
				return false ;
			}
		};



		var dlg = document.createElement( 'div' );
		dlg.className = 'tdlg-input-template' ;
		dlg.style.display = 'none' ;
		dlg.tabIndex = 0 ;
		dlg.onkeypress = dlg.onkeyup = function( o ) {
			return function ( event ) {
				return o.processInput( event );
			};
		}( this );
		dlg.onblur = function () {
		};

		var cap = document.createElement( 'div' );
		cap.className = 'tdlg-input-template-cap' ;
		cap.appendChild( document.createTextNode( "Выберите шаблон" ) );
		var closeBtn = document.createElement( 'div' );
		closeBtn.className = 'tdlg-input-template-close-btn' ;
		closeBtn.onclick = function ( dlg ) {
			return function () {
				if ( dlg.mode && dlg.mode == 'config' ) {
					dlg.configCancel();
					dlg.cfgModeClose();
				} else {
					dlg.close();
				}
			};
		}( this );
		cap.appendChild( closeBtn );
		dlg.appendChild( cap );

		var tabWrapper = document.createElement( 'div' );
		tabWrapper.className = 'tdlg-input-template-tab-wrapper' ;

		var tab = document.createElement( 'table' );
		tab.className = 'tdlg-input-template-tab' ;
		tabWrapper.appendChild( tab );

		dlg.appendChild( tabWrapper );

		var toolbar = document.createElement( 'div' );
		toolbar.className = 'tdlg-input-template-tool-bar' ;
		toolbar.appendChild( document.createTextNode( "<Esc> - Закрыть окно , * - Закрыть окно и ввести символ <*>" ) );
		var tmp = document.createElement( 'a' );
		tmp.className = 'tdlg-input-template-tool-bar-lnk' ;
		tmp.style.cssFloat = tmp.style.styleFloat = 'right' ;
		tmp.style.marginLeft = '8px' ;
		tmp.onclick = function( o ) {
			return function() {
				o.config();
			};
		}( this );
		setText( tmp , "Настройка" );
		toolbar.appendChild( tmp );

		var tmp = document.createElement( 'span' );
		tmp.className = 'tdlg-input-template-append-mode' ;
		tmp.style.cssFloat = tmp.style.styleFloat = 'right' ;
		tmp.onclick = function ( o ) {
			return function () {
				o.toggleAppendMode();
			};
		}( this );
		toolbar.appendChild( tmp );

		var appendModeSign = tmp ;

		dlg.appendChild( toolbar );

		var toolbarCfgMode = toolbar.cloneNode( false );
		var tmp = document.createElement( 'a' );
		tmp.className = 'tdlg-input-template-tool-bar-lnk' ;
		tmp.onclick = function( o ) {
			return function() {
				o.add();
			};
		}( this );
		setText( tmp , "Добавить шаблон" );
		toolbarCfgMode.appendChild( tmp );

		var tmp = document.createElement( 'a' );
		tmp.className = 'tdlg-input-template-tool-bar-lnk' ;
		tmp.style.marginLeft = '8px' ;
		tmp.style.cssFloat = tmp.style.styleFloat = 'right' ;
		tmp.style.color = "#800" ;
		tmp.onclick = function( o ) {
			return function() {
				o.configCancel();
				o.cfgModeClose();
			};
		}( this );
		setText( tmp , "Отмена" );
		toolbarCfgMode.appendChild( tmp );

		var tmp = document.createElement( 'a' );
		tmp.className = 'tdlg-input-template-tool-bar-lnk' ;
		tmp.style.marginLeft = '64px' ;
		tmp.style.cssFloat = tmp.style.styleFloat = 'right' ;
		tmp.style.color = "#080" ;
		tmp.style.fontSize = '9pt' ;
		tmp.onclick = function( o ) {
			return function() {
				o.configApply();
				o.cfgModeClose();
			};
		}( this );
		setText( tmp , "Принять" );
		toolbarCfgMode.appendChild( tmp );

		var tmp = document.createElement( 'a' );
		tmp.className = 'tdlg-input-template-tool-bar-lnk' ;
		tmp.style.marginLeft = '8px' ;
		tmp.style.cssFloat = tmp.style.styleFloat = 'right' ;
		tmp.onclick = function( o ) {
			return function() {
				o.configHlp();
			};
		}( this );
		setText( tmp , "Подсказка" );
		toolbarCfgMode.appendChild( tmp );

		//dlg.appendChild( toolbarCfgMode );

		this.dlg = dlg ;
		this.tab = tab ;
		this.tb = toolbar ;
		this.tbcm = toolbarCfgMode ;
		this.appendModeSign = appendModeSign ;

		document.body.appendChild( dlg );

		if ( opt && opt.autoAssign ) {
			for( var i = 0 ; i < data.length ; i++ ) {
				this.assign( data[ i ].e );
			}
		}

		this.toggleAppendMode();
	};