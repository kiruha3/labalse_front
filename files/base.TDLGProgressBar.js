
	$.TDLGProgressBar = function( levels , opt ) {
		var dom = this.dom = {};
		this.levels = {};
		this.options = Object.assign( {
			title : ''
		} , opt );

		var mkElem = mk_makeElement( 'std-progress-bar-' );
		var mkDiv = mkElem ;

		this.show = function() {
			var dom = this.dom ;
			dom.wrapper.style.display = '' ;
		};



		this.close = function() {
			var dom = this.dom ;
			dom.wrapper.style.display = 'none' ;
		};

		var lvlMkFuncList = {
			'big-wo-num' : function( wrapper , param ) {
				var cnt = mkDiv( '-bar big-wo-num' );
				wrapper.appendChild( cnt );

				var bg = mkDiv( '-bar-layer1 big-wo-num' );
				cnt.appendChild( bg );

				var fg1 = mkDiv( '-bar-layer2 big-wo-num' );
				bg.appendChild( fg1 );
				var fg2 = mkDiv( '-bar-layer3 big-wo-num' );
				bg.appendChild( fg2 );

				param.setProgress = function( o , b1 , b2 , p ) {
					return function( v ) {
						o.dataset.progress = v + '%' ;
						b1.style.width = b2.style.width = v + '%' ;
						p.progress = v ;
					};
				}( bg , fg1 , fg2 , param );
				param.setProgress( 0 );
			}
		};

		var defMkFunc = lvlMkFuncList[ 'big-wo-num' ];

		this.addLevel = function( param ) {
			var dom = this.dom ;
			var tmp = mkDiv( 'level' );
			if ( param.lvlMkFunc ) {
				param.lvlMkFunc( tmp , param );
			} else {
				var style = param.style ;
				if ( lvlMkFuncList[ style ] ) {
					lvlMkFuncList[ style ].call( this , tmp , param );
				} else {
					defMkFunc( tmp , param );
				}
			}
			dom.lvlArea.appendChild( tmp );
			dom.levels.push( { dom : tmp , data : param } );
		};

		dom.wrapper = mkDiv( 'dlg-wrapper' );
		dom.wrapper.style.display = 'none' ;

		dom.bg = mkDiv( 'dlg-bg' );
		dom.bg.onscroll = function() {
			return false ;
		};
		dom.wrapper.appendChild( dom.bg );

		dom.dlg = mkDiv( 'dlg' );
		dom.cap = mkDiv( 'dlg-cap' );
		dom.cap.appendChild( document.createTextNode( this.options.title ) );

		dom.closeBtn = mkDiv( 'dlg-close-btn' );
		dom.closeBtn.onclick = function( o ) {
			return function() {
				o.close();
			};
		}( this );
		dom.cap.appendChild( dom.closeBtn );

		dom.dlg.appendChild( dom.cap );

		dom.lvlArea = mkDiv( 'area' );
		dom.levels = [];

		for( var i = 0 ; i < levels.length ; i++ ) {
			levels[ i ].owner = this ;
			this.addLevel( levels[ i ] );
		}

		/*dom.list.appendChild( dom.listData );*/

		dom.dlg.appendChild( dom.lvlArea );

		/*dom.toolbar = mkDiv( 'toolbar' );

		dom.toolbarAttacheBtn = document.createElement( 'a' );
		dom.toolbarAttacheBtn.className = 'btn3' ;
		dom.toolbarAttacheBtn.appendChild( document.createTextNode( "Прикрепить" ) );
	dom.toolbar.appendChild( dom.toolbarAttacheBtn );
	dom.dlg.appendChild( dom.toolbar );*/


		dom.wrapper.appendChild( dom.dlg );

		document.body.appendChild( dom.wrapper );
	};

