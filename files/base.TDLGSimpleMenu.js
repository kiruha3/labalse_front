
	$.TDLGSimpleMenu = function( items , opt , sel ) {
		var dom = this.dom = {};
		this.options = opt ;
		dom.items = [];

		var mkElem = mk_makeElement( 'std-simple-menu-' );
		var mkDiv = mkElem ;

		this.show = function() {
			var dom = this.dom ;
			dom.wrapper.style.display = '' ;



			var pr = new Promise( function( t , d ) {
				return function ( resolve , reject ) {
					var items = d.items ;
					for( var i = 0 ; i < items.length ; i++ ) {
						items[ i ].dom.onclick = function ( o , a , it ) {
							return function () {
								a( it );
								o.close();
							};
						}( t , resolve , items[ i ].data );
					}
				};
			}( this , dom ) );
			return pr ;


			/*document.onmousewheel = document.onwheel = function() {
				return false ;
			};
			document.addEventListener( 'MozMousePixelScroll' , function() { return false ; } , false );
			document.onkeydown = function( e ) {
				if ( e.keyCode >= 33 && e.keyCode <= 40 ) return false ;
			};*/
		};



		this.close = function() {
			var dom = this.dom ;
			dom.wrapper.style.display = 'none' ;
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
		dom.cap.appendChild( document.createTextNode( opt.title ) );

		dom.closeBtn = mkDiv( 'dlg-close-btn' );
		dom.closeBtn.onclick = function( o ) {
			return function() {
				o.close();
			};
		}( this );
		dom.cap.appendChild( dom.closeBtn );

		dom.dlg.appendChild( dom.cap );

		dom.list = mkDiv( 'list' );

		dom.listData = mkDiv( 'list-data' );

		var imf ;
		if ( opt.itemMkFunc ) {
			imf = opt.itemMkFunc ;
		} else {
			imf = function( item ) {
				return document.createTextNode( '' + item );
			};
		}

		for( var i = 0 ; i < items.length ; i++ ) {
			var tmp = mkDiv( 'item' );
			tmp.appendChild( imf( items[ i ] ) );
			dom.listData.appendChild( tmp );
			dom.items.push( { dom : tmp , data : items[ i ] } );
		}

		dom.list.appendChild( dom.listData );

		dom.dlg.appendChild( dom.list );

		/*dom.toolbar = mkDiv( 'toolbar' );

		dom.toolbarAttacheBtn = document.createElement( 'a' );
		dom.toolbarAttacheBtn.className = 'btn3' ;
		dom.toolbarAttacheBtn.appendChild( document.createTextNode( "Прикрепить" ) );
	dom.toolbar.appendChild( dom.toolbarAttacheBtn );
	dom.dlg.appendChild( dom.toolbar );*/


		dom.wrapper.appendChild( dom.dlg );

		document.body.appendChild( dom.wrapper );
	};
