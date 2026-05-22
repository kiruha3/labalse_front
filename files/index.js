	$.windowOnLoad.push( function() {
		PDFJS.workerSrc = '/ext-lib/pdf.js/build/pdf.worker.js' ;
		var docList = document.querySelectorAll( '.pdf-preview-wrapper' );
		var ppoList = [];
		for( var i = 0 ; i < docList.length ; i++ ) {
			var ppw = docList[ i ];
			var ppa = ppw.parentNode ;
			var ppo = {
				ppw : ppw ,
				ppa : ppa ,
				url : ppw.dataset.link ,
				tc : []
			};


			var lnkTypes = {
				'type1' : /^http(?:s)?:\/\/base\.vrcse\.ru\/file_store\/download\.php\?id=(\d+)$/ ,
				'type2' : /^http(?:s)?:\/\/base\.vrcse\.ru\/documents\.php\?download=(\d+)$/
			};
			var docCookieID = null ;
			for( var type in lnkTypes ) {
				var pat = lnkTypes[ type ];
				var m = ppo.url.match( pat );
				if ( m ) {
					docCookieID = 'infoDoc_' + type + '_' + m[ 1 ];
					break ;
				}
			}
			if ( docCookieID ) {
				var drc = getCookie( docCookieID );
				if ( drc ) {
					if ( drc < 2 ) {
						drc++ ;
					} else {
						drc = 10 ;
					}
				} else {
					drc = 0 ;
				}
				setCookie( docCookieID , drc , 1000 );
				if ( drc == 10 ) {
					ppo.ppa.style.display = 'none' ;
					continue ;
				}
			}
			showPDF( ppo );
		}
		$.pdfPreview = ppoList ;
	} );

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

	function drawPageFinished( ppo , pn , n , pdf ) {
		var tc = ppo.tc ;
		if ( tc[ n ] ) {
			tc[ n ].r = true ;
		}

		var all = true ;
		for( var i = 0 ; i < pn ; i++ ) {
			all = all & tc[ i ].r ;
		}

		if ( all ) {
			pdf.cleanup();
			pdf.destroy();
		}
	}

	function showPDF( ppo ){
		PDFJS.getDocument( ppo.url ).then( function ( pdf ) {
			var pn = pdf.numPages ;
			var tc = ppo.tc ;
			for( var i = tc.length + 1 ; i <= pn ; i++ ) {
				var cd = document.createElement( "div" );
				var cc = document.createElement( "canvas" );
				cd.appendChild( cc );
				cd.style.marginRight = ( $.scrollbarSize.w + 1 ) + "px" ;
				ppo.ppw.appendChild( cd );
				tc.push( {
					c : cc ,
					d : cd
				} );
			}
			for( var i = 0 ; i < tc.length ; i++ ) {
				tc[ i ].d.style.display = "none" ;
			}

			var toolBar = document.createElement( 'div' );
			toolBar.className = 'pdf-preview-area-toolbar' ;

			ppo.ppw.appendChild( toolBar );

			var closeBtn = document.createElement( 'div' );
			closeBtn.className = 'btn3' ;
			closeBtn.onclick = function( o ) {
				return function( ) {
					o.ppa.style.display = 'none' ;
					var showLnk = o.ppa.parentNode ;
					setTimeout( function( lnk , s ) {
						return function() {
							lnk.onclick = function() {
								s.display = 'block' ;
								lnk.onclick = undefined ;
							};
						};
					}( showLnk , o.ppa.style ) , 1000 );
				};
			}( ppo );
			closeBtn.appendChild( document.createTextNode( 'закрыть' ) );
			toolBar.appendChild( closeBtn );

			ppo.ppa.style.display = 'block' ;


			var pWidth = ppo.ppw.clientWidth - $.scrollbarSize.w ;

			for( var i = 0 ; i < pn ; i++ ) {
				tc[ i ].d.style.display = "" ;
				tc[ i ].r = false ;
				drawPDFPage( pdf , i + 1 , pWidth , tc[ i ].c , function( w , x , y , z ) {
					return function() {
						drawPageFinished( w , x , y , z );
					};
				}( ppo , pn , i , pdf ) );
			}

			ppo.ppw.scrollTop = 0 ;
		} );
	};
