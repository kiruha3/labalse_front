
	$.TDLGFileUpload = function( opt ) {

		var mkElem = mk_makeElement( 'std-file-upload-' );
		var mkDiv = mkElem ;

		var tabInfo = [ { name : "С сервера" } , { name : "С компьютера" } ];

		var dom = this.dom = {};
		this.options = opt ;
		this.cache = {};
		this.pdfTargetCanvas = [];

		dom.file = [];

		this.show = function( id , type ) {
			var dom = this.dom ;
			var tl = dom.tl ;
			var tll = tl.childNodes ;
			var tlll = tll.length ;

			var doc = sendXML( "<get-list />" , false , $.docsURL + "/unknown.ajax.php" );
			var fnl = doc.childNodes ;
			var fnll = fnl.length ;
			for( var i = tlll ; i < fnll ; i++ ) {
				var nlnk = mkDiv( 'tgt-lnk' );
				tl.appendChild( nlnk );
			}

			var tlll = tll.length ;
			for( var i = fnll ; i < tlll ; i++ ) {
				tll[ i ].style.display = 'none' ;
			}

			for( var i = 0 ; i < fnll ; i++ ) {
				var clnk = tll[ i ];
				clnk.style.display = '' ;
				var clnkT = getXMLNodeValue( fnl[ i ] );
				var fnbe = fnl[ i ].getAttribute( 'code' );
				setText( clnk , clnkT );
				clnk.onclick = function( o , x , y , z ) {
					return function() {
						o.showPDF( x , y , z );
					};
				}( this , fnbe , id , type );
			}

			dom.toolbarAttacheBtn.onclick = function( o , x , y , z ) {
				return function() {
					o.doAttache( x , y , z );
				};
			}( this , null , id , type );

			dom.wrapper.style.display = '' ;

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
			var file = dom.file ;
			for( var i = file.length - 1 ; i >= 0  ; i-- ) {
				this.doDeleteFileSelector( file[ i ] );
			}
			this.doAddFileSelector();
			dom.wrapper.style.display = 'none' ;
		};

		this.drawPDFPage = function( pdf , pn , pw , c , f ) {
			if ( pdf.numPages >= pn ) {
				pdf.getPage( pn ).then(
					function ( pageWidth , canvas , func ) {
						return function ( page ) {
							var viewport = page.getViewport( 1.0 );
							var scale = pageWidth / viewport.width ;
							var viewport = page.getViewport( scale );

							var context = canvas.getContext( '2d' );
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
		};

		this.drawPageFinished = function( pn , n , pdf ) {
			var dom = this.dom ;
			var tc = this.pdfTargetCanvas ;
			if ( tc[ n ] ) {
				tc[ n ].r = true ;
			}

			var all = true ;
			for( var i = 0 ; i < pn ; i++ ) {
				all = all & tc[ i ].r ;
			}

			if ( all ) {
				dom.tlal.style.display = 'none' ;
				pdf.cleanup();
				pdf.destroy();
			} else {
			}
		};

		this.showPDF = function( fn , id , type ){
			var dom = this.dom ;
			this.selectedFileName = fn ;
			dom.toolbarAttacheBtn.onclick = function( o , x , y , z ) {
				return function() {
					o.doAttache( x , y , z );
				};
			}( this , fn , id , type );
			dom.tlal.style.display = '' ;
			PDFJS.getDocument( $.docsURL + "/unknown.ajax.php?show=" + fixedEncodeURIComponent( fn ) ).then( function ( x ) {
				return function ( pdf ) {
					var pn = pdf.numPages ;
					var tc = x.pdfTargetCanvas ;
					for( var i = tc.length + 1 ; i <= pn ; i++ ) {
						var cd = mkDiv();
						var cc = document.createElement( 'canvas' );
						cd.appendChild( cc );
						cd.style.marginRight = ( $.scrollbarSize.w + 1 ) + 'px' ;
						x.dom.pdfPW.appendChild( cd );
						tc.push( {
							c : cc ,
							d : cd
						} );
					}
					for( var i = 0 ; i < tc.length ; i++ ) {
						tc[ i ].d.style.display = 'none' ;
					}

					for( var i = 0 ; i < pn ; i++ ) {
						tc[ i ].d.style.display = '' ;
						tc[ i ].r = false ;
						x.drawPDFPage( pdf , i + 1 , 720 , tc[ i ].c , function( o , x , y , z ) {
							return function() {
								o.drawPageFinished( x , y , z );
							};
						}( x , pn , i , pdf ) );
					}

					x.dom.pdfPW.scrollTop = 0 ;
				};
			}( this ) );
		};

		this.doAddFileSelector = function() {
			var dom = this.dom ;

			var fileData = {
				img : null
			};

			var wrapper = mkDiv( 'lf-w' );

			var label = mkElem( 'label' , 'lf-pwa' );
			var preview = mkDiv( 'lf-pw' );
			label.appendChild( preview );
			var file = mkElem( 'input' , 'file' );
			file.type = 'file' ;
			file.name = dom.file.length == 0 ? 'uf' : "sf[]" ;
			file.onchange = function ( o , x , y ) {
				return function( evt ) {
					o.fileSelected( evt , x , y );
				};
			}( this , preview , fileData );
			label.appendChild( file );
			wrapper.appendChild( label );

			var btnClear = mkDiv( 'lf-pwc' );
			btnClear.style.display = 'none' ;
			btnClear.onclick = function( o , x ) {
				return function() {
					o.doDeleteFileSelector( x );
				};
			}( this , fileData );
			wrapper.appendChild( btnClear );

			dom.form.appendChild( wrapper );

			fileData.wrapper = wrapper ;
			fileData.clear = btnClear ;
			fileData.input = file ;
			fileData.label = label ;
			fileData.div = preview ;
			fileData.index = dom.file.length ;

			dom.file.push( fileData );
		};

		this.doDeleteFileSelector = function( fileData ) {
			var dom = this.dom ;
			dom.form.removeChild( fileData.wrapper );
			dom.file.splice( fileData.index , 1 );
			if ( dom.file.length > 0 ) {
				dom.file[ 0 ].input.name = 'uf' ;
			}
			for( var i = fileData.index ; i < dom.file.length ; i++ ) {
				dom.file[ i ].index = i ;
			}
		};

		this.doAttache = function ( fn , id , type ) {
			var dom = this.dom ;
			/*var doc = sendXML( "<get-cor-ny id=\"" + id + "\" />" , false , "correspondence.php" , "view=" + viewName );
			if ( doc.getAttribute( 'state' ) == 'error' ) {
				return ;
			}

			/*if ( ppo.tab1i.checked ) {
				if ( fn == null ) {
					alert( "Файл не выбран" );
					return ;
				}
				var doc = sendXML( "<link-file id=\"" + id + "\">" + toCDATA( fn ) + "</link-file>" , false , null , "view=" + viewName );
				if ( doc.getAttribute( 'state' ) == 'error' ) {
					alert( getXMLNodeValue( doc ) );
					return ;
				}
			} else*/
			if ( dom.tab1i.checked ) {
				var sfc = 0 ;
				for( var i = 0 ; i < dom.file.length ; i++ ) {
					if ( dom.file[ i ].img != null ) {
						sfc++ ;
					}
				}
				if ( sfc == 0 ) {
					alert( "Не выбран ни один файл" );
					return ;
				}

				for( var i = 0 ; i < dom.file.length ; i++ ) {
					if ( dom.file[ i ].img == null ) {
						dom.file[ i ].input.disabled = true ;
					}
				}

				dom.inputExtID.value = id ;
				dom.docType.value = dom.docTypeSel.value ;
				dom.form.submit();
				this.close();
				return ;
			}

			this.close();
			//window.location.reload( true );
		};

		this.fileSelected = function( evt , tgt , fileData ) {
			evt = evt || window.event ;

			if ( !evt.target ) {
				event.target = event.srcElement ;
			}

			var file = evt.target.files ;
			var f = file[ 0 ];

			if ( !f.type.match( /(image.*)|(text\/plain)|(application\/(pdf|msword|vnd\.ms-|vnd\.openxmlformats-))/ ) ) {
				alert( "Не верный тип документа!" );
				return ;
			}
			var reader = new FileReader();
			reader.onload = ( function( o , x , y , z ) {
				return function( e ) {
					var data = e.target.result ;
					var img = new Image();

					if ( x.type.match( /image.*/ ) ) {
						img.src = data ;
					} else
					if ( x.type.match( /application\/pdf/ ) ) {
						var pdfData = convertDataURIToBinary( data );
						PDFJS.getDocument( pdfData ).then( function ( x , y ) {
							return function ( pdf ) {
								var pn = pdf.numPages ;

								tc = document.createElement( 'canvas' );

								x.drawPDFPage( pdf , 1 , 720 , tc , function( canv , image ) {
									return function() {
										image.src = canv.toDataURL();
									};
								}( tc , y ) );
							};
						}( o , img ) );
					}

					if ( y.childNodes.length > 0 ) {
						y.replaceChild( img , y.childNodes[ 0 ] );
					} else {
						y.appendChild( img );
						o.doAddFileSelector();
					}
					z.img = img ;
					z.clear.style.display = '' ;
				};
			})( this , f , tgt , fileData );
			reader.readAsDataURL( f );
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
		dom.cap.appendChild( document.createTextNode( "Прикрепить файл" ) );

		dom.closeBtn = mkDiv( 'dlg-close-btn' );
		dom.closeBtn.onclick = function( o ) {
			return function() {
				o.close();
			};
		}( this );
		dom.cap.appendChild( dom.closeBtn );

		dom.dlg.appendChild( dom.cap );

		dom.tabs = mkDiv( 'tabs' );

		for( var i = 0 ; i < tabInfo.length ; i++ ) {
			var tmp = dom[ 'tab' + i + 'i' ] = mkRadio( opt.tabInputName , i == 1 );
			tmp.id = 'tab' + i + 'i' ;
			dom.tabs.appendChild( tmp );
		}

		for( var i = 0 ; i < tabInfo.length ; i++ ) {
			var tmp = dom[ 'tab' + i + 'l' ] = document.createElement( 'label' );
			tmp.htmlFor = 'tab' + i + 'i' ;
			tmp.appendChild( document.createTextNode( tabInfo[ i ].name ) );
			dom.tabs.appendChild( tmp );
		}

		var tmp = document.createElement( 'span' );
		dom.tabs.appendChild( tmp );

		for( var i = 0 ; i < tabInfo.length ; i++ ) {
			var tmp = dom[ 'tab' + i ] = mkDiv( tabInfo[ i ].style );
			dom.tabs.appendChild( tmp );
		}

		dom.tla = mkDiv( 'tla' );
		dom.tlal = mkDiv( 'tlal' );
		dom.tlal.style.display = 'none' ;
		dom.tla.appendChild( dom.tlal );
		dom.tl = mkDiv( 'tl' );
		dom.tla.appendChild( dom.tl );
		dom.tab0.appendChild( dom.tla );

		dom.pdfPA = mkDiv( 'pdf-pa' );
		dom.pdfPW = mkDiv( 'pdf-pw' );
		dom.paSizer = mkDiv( 'pa-sizer' );
		dom.paSizer.style.marginRight = ( $.scrollbarSize.w + 1 ) + 'px' ;
		dom.pdfPW.appendChild( dom.paSizer );
		dom.pdfPA.appendChild( dom.pdfPW );
		dom.tab0.appendChild( dom.pdfPA );

		/* ------------------- */

		dom.form = document.createElement( 'form' );
		dom.form.action = $.docsURL + "/upload-new.manual.php" + ( opt.test ? "?test_only=1" : '' ) ;
		//dom.form.action = "/tests/test2.php?test-only=1" ;
		dom.form.method = 'post' ;
		dom.form.enctype = "multipart/form-data" ;

		dom.inputExtID = document.createElement( 'input' );
		dom.inputExtID.type = 'hidden' ;
		dom.inputExtID.name = 'extId' ;
		dom.form.appendChild( dom.inputExtID );

		if ( opt[ 'redirect' ] ) {
			dom.inputRedirect = document.createElement( 'input' );
			dom.inputRedirect.type = 'hidden' ;
			dom.inputRedirect.name = 'redirect' ;
			dom.inputRedirect.value = opt[ 'redirect' ];
			dom.form.appendChild( dom.inputRedirect );
		}

		dom.docType = document.createElement( 'input' );
		dom.docType.type = 'hidden' ;
		dom.docType.name = 'docType' ;
		dom.form.appendChild( dom.docType );

		this.doAddFileSelector();

		dom.tab1.appendChild( dom.form );

		dom.dlg.appendChild( dom.tabs );

		dom.toolbar = mkDiv( 'toolbar' );
		if ( opt.docTypeList && opt.docTypeList.length > 1 ) {
			dom.docTypeSel = document.createElement( 'select' );
			for( var i = 0 ; i < opt.docTypeList.length ; i++ ) {
				var tmp1 = document.createElement( 'option' );
				var tmp2 = opt.docTypeList[ i ];
				tmp1.value = tmp2.v ;
				tmp1.text = tmp2.n ;
				dom.docTypeSel.appendChild( tmp1 );
				dom.toolbar.appendChild( dom.docTypeSel );
			}
		} else
		if ( opt.docTypeList && opt.docTypeList.length == 1 ) {
			dom.docTypeSel =  { value : opt.docTypeList[ 0 ].v };
		}

		dom.toolbarAttacheBtn = document.createElement( 'a' );  //<a id=\"fu-attache-btn\" class=\"btn3\">Прикрепить</a>
		dom.toolbarAttacheBtn.className = 'btn3' ;
		dom.toolbarAttacheBtn.appendChild( document.createTextNode( "Прикрепить" ) );
		dom.toolbar.appendChild( dom.toolbarAttacheBtn );
		dom.dlg.appendChild( dom.toolbar );

		dom.wrapper.appendChild( dom.dlg );


		PDFJS.workerSrc = '/ext-lib/pdf.js/build/pdf.worker.js' ;
		/*var ppo = {
			dlg : document.getElementById( 'fu-dlg' ) ,
			tab1i : document.getElementById( 'fu-tab-1' ) ,
			tab2i : document.getElementById( 'fu-tab-2' ) ,
			lal : document.getElementById( 'fu-tlal' ) ,
			tgt : document.getElementById( 'fu-ppa' ),
			tl : document.getElementById( 'fu-tl' ) ,
			fsf : document.getElementById( 'fu-file-select-form' ) ,
			fcid : document.getElementById( 'fu-cor-id' ),
			fcy : document.getElementById( 'fu-cor-y' ),
			fcn : document.getElementById( 'fu-cor-n' ),
			ab : document.getElementById( 'fu-attache-btn' ) ,
			tc : []
		};

		$.pdfPreview = ppo ;*/

		document.body.appendChild( dom.wrapper );
	};
