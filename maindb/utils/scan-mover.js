/**
 * 
 */

	$.windowOnLoad.push( function() {
		PDFJS.workerSrc = '/ext-lib/pdf.js/build/pdf.worker.js';
		var ppo = {
			lal : document.getElementById( "target-list-area-locker" ) ,
			tgt : document.getElementById( "ppa" ),
			tc : [] ,
			en : document.getElementById( "exp-num" ),
			ey : document.getElementById( "exp-year" ),
			dt : document.getElementById( "doc-type" )
		};		
		$.pdfPreview = ppo ;
		
		var doc = sendXML( "<get-list />" , false , $.docsURL + "/unknown.ajax.php" );
		var fnl = doc.childNodes ;
		var tl = document.getElementById( "target-list" );
		var allLinks = [];
		for( var i = 0 ; i < fnl.length ; i++ ) {
			var fnraw = getXMLNodeValue( fnl[ i ] );
			var fnbe = fnl[ i ].getAttribute( "code" );
			var tmp = document.createElement( "a" );
			tmp.className = "tgt-lnk" ;
			tmp.onclick = function( x, al , cl ) {
				return function() {
					for( var i = 0 ; i < al.length ; i++ ) {
						al[ i ].dataset.sel = "0" ;
					}
					cl.dataset.sel = "1" ;
					showPDF( x );
				};
			}( fnbe , allLinks , tmp );
			tmp.appendChild( document.createTextNode( fnraw ) );
			tmp.title = fnraw ;
			var tmp2 = document.createElement( "a" );
			tmp2.className = "tgt-lnk-dnld" ;
			tmp2.href = $.docsURL + "/unknown.ajax.php?show=" + fixedEncodeURIComponent( fnbe );
			tmp2.target = '_blank' ;
			tmp2.appendChild( document.createTextNode( 'd' ) );
			tmp.appendChild( tmp2 );
			tl.appendChild( tmp );
			allLinks.push( tmp );
		}

		var tc = ppo.tc ;
		var cd = document.createElement( "div" );
		var cc = document.createElement( "canvas" );
		cc.width = 720 ;
		cc.height = 720 ;
		cd.appendChild( cc );
		cd.style.marginRight = ( $.scrollbarSize.w + 1 ) + "px" ;
		ppo.tgt.appendChild( cd );
		tc.push( {
			c : cc ,
			d : cd
		} );


		//alert( $doc );
	} );
	
	function doMove() {
		var ppo = $.pdfPreview ;
		if ( !ppo.en.value.match( /^\s*\d{1,5}\s*$/ ) ) {
			alert( "Не верный номер, укажите только цифры" );
			return ;
		}
		$n = /(\d+)/.exec( ppo.en.value );
		$dt = ppo.dt.value ;
		
		var doc = sendXML( "<get-id y=\"" + ppo.ey.value + "\" n=\"" + $n[ 1 ] + "\" />" );
		 
		var doc = sendXML( "<move id=\"" + doc.getAttribute( "id" ) + "\" type=\"" + $dt + "\">" + toCDATA( $.pdfPreview.fn ) + "</move>" , false , $.docsURL + "/unknown.ajax.php" );
		if ( doc.getAttribute( "state" ) != "ok" ) {
			alert( getXMLNodeValue( doc ) );
		}
		window.location.reload( true );
	}
	
	function doDelete() {
		var doc = sendXML( "<delete>" + toCDATA( $.pdfPreview.fn ) + "</delete>" , false , $.docsURL + "/unknown.ajax.php" );
		if ( doc.getAttribute( "state" ) != "ok" ) {
			alert( getXMLNodeValue( doc ) );
		}
		window.location.reload( true );
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
	
	function showPDF( fn ){
		var ppo = $.pdfPreview ;
		ppo.fn = fn ;
		ppo.lal.style.display = "" ;
		PDFJS.getDocument( $.docsURL + "/unknown.ajax.php?show=" + fixedEncodeURIComponent( fn ) ).then( function ( pdf ) {
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
	
	var userRights = {
		l1add : 0 ,
		l1edit : 0 ,
		l1seeAll : 0 ,

		l2add : 0 ,
		l2edit : 0 ,
		l2seeAll : 0 ,
		l2delete : 0 ,
		l2deleteAny : 0 ,

		l3edit : 0 ,
		l3seeAll : 0 ,

		mayPrintAddressLabel : 0 ,
		mayEnvForPayment : 0 ,
		mayOrders : 0
	};
	
	function numInput() {
		var nInp = document.getElementById( "exp-num" );
		var yInp = document.getElementById( "exp-year" );
		var tab = document.getElementById( "exp-tab" );
		while ( tab.rows.length > 0 ) {
			tab.deleteRow( -1 );
		}
		var doc = sendXML( "<get-by-NY n=\"" + nInp.value + "\" y=\"" + yInp.value + "\" />" , false , "/maindb/main.php" );
		
		var dcn = doc.childNodes ;
		for( var i = 0 ; i < dcn.length ; i++  ) {
			//var geid = dcn[ i ].getAttribute( "id" );
			var newRow = document.createElement( "tr" );
			mkL1Row( newRow , dcn[ i ] , { showSubLVLBtn : 0 , showActionBtn : 0 } );
			tab.appendChild( newRow );
		}
	}
