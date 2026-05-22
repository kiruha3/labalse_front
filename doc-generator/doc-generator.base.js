
	class TDocGeneratorEngine {
		static #__data = null ;
		static get #__dge_data() {
			if ( !this.#__data ) {
				this.init();
			}
			return this.#__data ;
		}
		static init() {
			if ( this.#__data ) {
				return ;
			}

			let changed = false ;

			if ( !localStorage.DocGenerator ) {
				localStorage.setItem( 'DocGenerator' , JSON.stringify( {} ) );
			}
			let data = JSON.parse( localStorage.DocGenerator );

			if ( !data.VERSION || ( data.VERSION && $.VERSIONS && $.VERSIONS[ 'doc-templates' ] && data.VERSION != $.VERSIONS[ 'doc-templates' ] ) ) {
				data = {
					VERSION : $.VERSIONS[ 'doc-templates' ]
				};
				changed = true ;
			}

			if ( !data.tmplData ) {
				data.tmplData = {};
				changed = true ;
			}

			if ( !data.collectionsData ) {
				data.collectionsData = {};
				changed = true ;
			}
			TDocGenerator_DataCollection.init( data.collectionsData );

			this.#__data = data ;

			if ( changed ) {
				this.saveData();
			}
		}

		static get tmplData() {
			const data = this.#__dge_data ;
			return data.tmplData ;
		}

		static get collectionsData() {
			const data = this.#__dge_data ;
			return data.collectionsData ;
		}

		static saveData() {
			const data = this.#__dge_data ;
			localStorage.setItem( 'DocGenerator' , JSON.stringify( data ) );
		}
	}

	function readClasses( xml , classes ) {
		for( const cn of xml.querySelectorAll( ':scope class' ) ) {
			let tmpClass = new TDGClass( cn );
			classes[ tmpClass.id ] = tmpClass ;
		}

		//console.log( classes );
	}

	function getTmplData( tmplID ) {
		let changed = false ;
		/*if ( !localStorage.DocGenerator ) {
			localStorage.setItem( 'DocGenerator' , JSON.stringify( {} ) );
		}
		let DocGenerator = JSON.parse( localStorage.DocGenerator );
		//DocGenerator.tmplData - templateData
		if ( !DocGenerator.tmplData ) {
			DocGenerator.tmplData = {};
			changed = true ;
		}*/

		let tmplIDKey = 'tmpl-' + tmplID ;
		const tmplData = TDocGeneratorEngine.tmplData ;
		//if ( !DocGenerator.tmplData[ tmplIDKey ] ) {
		if ( !tmplData[ tmplIDKey ] ) {
			let tmplDataXML = sendXML( '<get-tmpl-data tmpl="' + tmplID + '" />' , false , '/doc-generator/template-data.php' );

			/**
			 * @description Current Template Data. /result
			 * @type {{fileName: string, code: string, name: string, downloadMode: (number|number), shortName: string, triggers: {}, previewMode: (number|number)}}
			 */
			let ctd = {
				proceeding    : tmplDataXML.hasAttribute( 'proceeding'  )    ? parseInt( tmplDataXML.getAttribute( 'proceeding'  ) )    : 0 ,
				previewMode   : tmplDataXML.hasAttribute( 'preview-mode'  )  ? parseInt( tmplDataXML.getAttribute( 'preview-mode'  ) )  : 1 ,
				downloadMode  : tmplDataXML.hasAttribute( 'download-mode' )  ? parseInt( tmplDataXML.getAttribute( 'download-mode' ) )  : 1 ,
				downloadTypes : tmplDataXML.hasAttribute( 'download-types' ) ? tmplDataXML.getAttribute( 'download-types' )             : '' ,
				name          : '' ,
				shortName     : '' ,
				fileName      : '' ,
				code          : '' ,
				triggers      : {} ,
				classes       : {}
			};
			
			//debugger ;

			for ( const ctdXcn of tmplDataXML.children ) { /** @description Current Template Data Xml Child Node */
				switch( ctdXcn.nodeName ) {
					case 'name' :
					case 'short-name' :
					case 'file-name' :
					case 'code' :
					case 'ext-var' : /** @description Param Name */
						let pn = ctdXcn.nodeName.replace( /-([a-z])/ , function ( m , p ) {
							return p.toUpperCase();
						} );
						ctd[ pn ] = getXMLNodeValue( ctdXcn );
						break ;

					case 'triggers' :
						for( const ctdtcXcn of ctdXcn.querySelectorAll( ':scope > trigger-chain' ) ) { /** @description Current Template Data Trigger-Chain Xml Child Node */
							/** @description Current Trigger-Chain Name */
							let ctcName = ctdtcXcn.getAttribute( 'event' ).replace( /-([a-z])/ , function ( m , p ) {
								return p.toUpperCase();
							} );

							/** @description Current Trigger-Chain triggers list */
							let ctc = [];
							ctd.triggers[ ctcName ] = ctc ;

							for ( const ctdtctXcn of ctdtcXcn.querySelectorAll( ':scope > trigger' ) ) { /** @description Current Template Data Trigger-Chain Trigger Xml Child Node */
								/** @description Current Trigger-Chain Trigger */
								var ctct = {
									type : ctdtctXcn.getAttribute( 'type' )
								};

								switch ( ctct.type ) {
									case 'ask-user' :
										ctct.functionName = ctdtctXcn.getAttribute( 'function' )
										break ;
								}

								ctc.push( ctct );
							}
						}
						break ;
					case 'classes' :
						readClasses( ctdXcn , ctd.classes );
						break ;
				}
			}

			if ( ctd.extVar ) {
				ctd.extVar = JSON.parse( ctd.extVar );
			} else {
				ctd.extVar = [];
			}

			//DocGenerator.tmplData[ tmplIDKey ] = ctd ;
			tmplData[ tmplIDKey ] = ctd ;

			changed = true ;
		}

		if ( changed ) {
			TDocGeneratorEngine.saveData();
		}

		const ctd = tmplData[ tmplIDKey ];
		for( let cid in ctd.classes ) {
			let cc = ctd.classes[ cid ];
			if ( !( cc instanceof TDGClass ) ) {
				ctd.classes[ cid ] = new TDGClass( cc );
			}
		}
		return ctd ;
	}

	class TDGVariableUnits {
		static #__data ;
		static get data() {
			if ( this.#__data ) {
				return this.#__data ;
			}

			this.#__data = {
				'm'     : { short : 'm'     , base : 'm'     , 'descr-short-line' : 'м'     , factor : 1 } ,
				'nm'    : { short : 'nm'    , base : 'm'     , 'descr-short-line' : 'нм'    , factor : 0.000000001 } ,
				'um'    : { short : 'um'    , base : 'm'     , 'descr-short-line' : 'мкм'   , factor : 0.000001 } ,
				'mm'    : { short : 'mm'    , base : 'm'     , 'descr-short-line' : 'мм'    , factor : 0.001 } ,
				'cm'    : { short : 'cm'    , base : 'm'     , 'descr-short-line' : 'см'    , factor : 0.01 } ,
				'dm'    : { short : 'dm'    , base : 'm'     , 'descr-short-line' : 'дм'    , factor : 0.1 } ,
				'dam'   : { short : 'dam'   , base : 'm'     , 'descr-short-line' : 'дам'   , factor : 10 } ,
				'hm'    : { short : 'hm'    , base : 'm'     , 'descr-short-line' : 'гм'    , factor : 100 } ,
				'km'    : { short : 'km'    , base : 'm'     , 'descr-short-line' : 'км'    , factor : 1000 } ,
				'inch'  : { short : 'inch'  , base : 'm'     , 'descr-short-line' : 'in'    , factor : 0.0254 } ,

				'sq-m'  : { short : 'sq-m'  , base : 'sq-m'  , 'descr-short-line' : 'кв.м'  , factor : 1 } ,
				'sq-mm' : { short : 'sq-mm' , base : 'sq-m'  , 'descr-short-line' : 'кв.мм' , factor : 0.000001 } ,
				'sq-cm' : { short : 'sq-cm' , base : 'sq-m'  , 'descr-short-line' : 'кв.см' , factor : 0.0001 } ,
				'sq-dm' : { short : 'sq-dm' , base : 'sq-m'  , 'descr-short-line' : 'кв.дм' , factor : 0.01 } ,
				'a'     : { short : 'a'     , base : 'sq-m'  , 'descr-short-line' : 'а'     , factor : 100 } ,
				'ha'    : { short : 'ha'    , base : 'sq-m'  , 'descr-short-line' : 'га'    , factor : 10000 } ,

				'ruble' : { short : 'ruble' , base : 'ruble' , 'descr-short-line' : 'руб'  , factor : 1 } ,
			};
		}
	}

	class TDGVariable {
		static get LAYOUT_LIST() { return 'list'    ; } // Просто набор div'ов по одному для каждого элемента
		static get LAYOUT_ALIGNED_LIST() { return 'aligned-list'    ; } // Таблица 2 столбца, слева названия, справа поле ввода, по 1 строке на элемент
		static get LAYOUT_COLUMNS() { return 'columns' ; } // Таблица 1 строка, по 1 столбцу отдельно на каждое название и поле ввода на каждый элемент
		static get LAYOUT_LINE() { return 'line' ; } // 1 строка для всех элементов
		static get LAYOUT_ONE_LINE() { return 'one-line' ; } // 1 строка для заголовка и всех элементов
		static get LAYOUT_EXTERNAL() { return 'external' ; } // внешнее управление
		static get LAYOUT_DOCKED_GRID() { return 'docked-grid' ; } // сетка со стыковкой однотипных элементов в таблицу
		
		static get defaultLayout() { return TDGVariable.LAYOUT_LIST ; };

		static get TYPE_STRING    () { return 'string'    ; }
		static get TYPE_NUMBER    () { return 'number'    ; }
		static get TYPE_PRICE     () { return 'price'     ; }
		static get TYPE_DATE_TIME () { return 'date-time' ; }
		static get TYPE_OPTIONS   () { return 'options'   ; }
		static get TYPE_VARIANT   () { return 'variant'   ; }
		static get TYPE_ARRAY     () { return 'array'     ; }
		static get TYPE_STRUCTURE () { return 'structure' ; }
		static get TYPE_CLASS     () { return 'class'     ; }
		static get TYPE_IMAGE     () { return 'image'     ; }
		static get TYPE_ADDRESS   () { return 'address'   ; }

		static get SPECIAL_TYPE_FORM_DATA () { return '@form-data'   ; }

		static get hasChilds() { return false ; };
		static get fbp_SkipCount() { return 0 ; };

		#__uid ;
		#__parent ;
		#__exData ;
		#__name ;
		#__type ;
		#__description ;
		#__definition ;
		#__value ;
		#__newValue ;
		#__defaultValue ;
		#__path ;

		#__docLinks ;
		
		#__layout ;
		
		

		dom ;

		constructor( parent , name , type , description , definition , exData ) {
			this.#__uid = 'var--' + generateGUID();
			this.#__parent = parent ;
			this.#__exData = exData ;
			this.#__name = name ;
			this.#__type = type ;
			this.#__description = description ;
			this.#__definition = definition ;
			this.#__defaultValue = definition.default ?? null ;
			this.#__value = this.#__defaultValue ;
			this.#__newValue = null ;
			this.#__path = '[' + name + ']' ;
			if ( parent ) {
				this.#__path = parent.path + ( exData[ 'path-prefix' ] ? exData[ 'path-prefix' ] : '' ) + this.#__path ;
			}

			this.#__docLinks = [];
			
			this.#__layout = definition.layout ?? this.constructor.defaultLayout ;

			this.dom = {
				stylePrefixCommon : 'dg--ev-visual--VARIABLE--' ,
				stylePrefix : 'dg--ev-visual--' + type + '--'
			};
		}

		static #__copy( s ) {
			if ( s instanceof Array ) {
				return s.slice();
			} else
			if ( s instanceof Set ) {
				return new Set( s );
			} else
			if ( s instanceof Map ) {
				return new Map( s );
			} else {
			if ( ( s instanceof Object ) ) {
				return Object.assign( {} , s );
			} else
				return s ;
			}
		}

		get uid() {
			return this.#__uid ;
		}

		get parent() {
			return this.#__parent ;
		}

		get exData() {
			return this.#__exData ;
		}

		get name() {
			return this.#__name ;
		}

		get type() {
			return this.#__type ;
		}

		get description() {
			return this.#__description ;
		}

		get definition() {
			return this.#__definition ;
		}

		get value() {
			return this.#__value ;
		}

		set value( v ) {
			this.setNewValueRaw( v );
		}

		get newValue() {
			return this.#__newValue ;
		}

		set newValue( v ) {
			this.setNewValueRaw( v );
		}

		setValueRaw( v ) {
			this.#__value = TDGVariable.#__copy( v );
		}
		setNewValueRaw( v ) {
			this.#__newValue = TDGVariable.#__copy( v );
		}
		get defaultValue() {
			return this.#__defaultValue ;
		}
		
		applyChanges() {
			this.#__value = TDGVariable.#__copy( this.#__newValue );
			if ( this.constructor.hasChilds ) {
				const entries = this.#__value.entries ? this.#__value.entries() : Object.entries( this.#__value );
				for( const el of entries ) {
					el[ 1 ].applyChanges();
				}
			}
		}

		reset() {
			this.#__newValue = TDGVariable.#__copy( this.#__value );
			if ( this.constructor.hasChilds ) {
				const entries = this.#__value.entries ? this.#__value.entries() : Object.entries( this.#__value );
				for( const el of entries ) {
					el[ 1 ].reset();
				}
			}
		}

		get path() {
			return this.#__path ;
		}

		get docLinks() {
			return this.#__docLinks ;
		}

		addDocLink( docElement ) {
			this.#__docLinks.push( docElement );
		}
		
		get layout() {
			return this.#__layout ;
		}

		read( src ) {}

		write() {}

		generateDomPrepare( params , localParams ) {
			const res = {};
			const dom = this.dom ;
			const setClasses = function( sp , spc , p ) {
				return function( el , s ) {
					el.classList.add( spc + s );
					el.classList.add( sp + s );
					el.classList.add( 'level-' + p.level );
				};
			} ( dom.stylePrefix , dom.stylePrefixCommon , res );
			if ( params instanceof HTMLElement ) {
				res.parent = params ;
			} else {
				Object.assign( res , params );
			}

			if ( !localParams ) {
				localParams = {};
			}
			Object.assign( res , localParams );

			if ( !res.level ) {
				res.level = 1 ;
			}

			let area ;
			if ( res.area ) {
				if ( typeof res.area === "function" ) {
					area = res.area();
				} else {
					area = res.area ;
				}
			} else {
				area = document.createElement( 'div' );
				setClasses( area , 'area' );
				res.parent.appendChild( area );
				res.area = area ;
			}
			dom.area = area ;

			if ( !res.noLabel ) {
				if ( !res.labelArea ) {
					res.labelArea = area ;
				}

				let label ;
				if ( res.labelComplex ) {
					label = document.createElement( 'div' );
					const labelText = res.labelText = dom.labelText = document.createElement( 'span' );
					setClasses( labelText , 'label-text' );
					labelText.appendChild( document.createTextNode( this.description ) );
					label.appendChild( labelText );
					res.labelArea.appendChild( label );
				} else {
					label = document.createElement( 'label' );
					label.appendChild( document.createTextNode( this.description ) );
					res.labelArea.appendChild( label );
					dom.labelText = res.labelText = label ;
				}
				setClasses( label , 'label' );
				dom.label = res.label = label ;
				if ( res.hookup ) {
					res.labelArea.insertBefore( res.hookup , label );
				}
			}

			if ( !res.valueArea ) {
				res.valueArea = area ;
			}

			return res ;
		}

		generateDom( params ) {
			const p = this.generateDomPrepare( params );
			p.area.className = 'dg--ev-visual--ERROR--area' ;
			p.valueArea.appendChild( document.createTextNode( this.name + ' : ' + this.type ) );
		}

		clearDom() {

		}

		findByPath( tgtPath ) {
			if ( tgtPath.length > 0 && tgtPath[ 0 ] == this.name ) {
				if ( tgtPath.length === 1 ) {
					return this ;
				} else {
					if ( this.constructor.hasChilds ) {
						const index = tgtPath[ 1 ];
						if ( this.newValue[ index ] ) {
							return this.newValue[ index ].findByPath( tgtPath.slice( 1 + this.constructor.fbp_SkipCount ) );
						} else {
							return null ;
						}
					}
				}
			}
			return null ;
		}

		static fromDef( parent , def , exData ) {

			const MAP = {
				[ TDGVariable.TYPE_STRING    ] : TDGVariableString ,
				[ TDGVariable.TYPE_NUMBER    ] : TDGVariableNumber ,
				[ TDGVariable.TYPE_PRICE     ] : TDGVariablePrice ,
				[ TDGVariable.TYPE_DATE_TIME ] : TDGVariableDateTime ,
				[ TDGVariable.TYPE_OPTIONS   ] : TDGVariableOptions ,
				[ TDGVariable.TYPE_VARIANT   ] : TDGVariableVariant ,
				[ TDGVariable.TYPE_ARRAY     ] : TDGVariableArray ,
				[ TDGVariable.TYPE_STRUCTURE ] : TDGVariableStructure ,
				[ TDGVariable.TYPE_CLASS     ] : TDGVariableClass ,
				[ TDGVariable.TYPE_IMAGE     ] : TDGVariableImage ,
				[ TDGVariable.TYPE_ADDRESS   ] : TDGVariableAddress ,
			};

			if ( def.type && MAP[ def.type ] ) {
				const td = MAP[ def.type ];
				return new td( parent , def.name , def.type , def.descr , def , exData );
			} else
			if ( def.type === TDGVariable.SPECIAL_TYPE_FORM_DATA ) {
				return new TDLGFormData( parent , def , exData );
			} else {
				//console.log( def );
				return new TDGVariable( parent , def.name , def.type , def.descr , def , exData );
			}
		}
	}

	class TDGVariableString extends TDGVariable {
		#__lines ;
		#__multiForm ;

		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			this.setValueRaw( this.defaultValue );
			this.#__lines = definition.lines ?? 1 ;
			this.#__multiForm = definition.multiForm ?? false ;
		}

		get lines() {
			return this.#__lines ;
		}

		get multiForm() {
			return this.#__multiForm ;
		}

		read( src ) {
			const v = '' + src ;
			this.setValueRaw( v );
		}

		write() {
			return this.value ;
		}

		generateDom( params ) {
			const p = this.generateDomPrepare( params );
			const inputID = 'i--' + this.uid ;

			if ( !p.noLabel ) {
				let label = p.label ;
				label.htmlFor = inputID ;
			}

			let valueInput ;
			if ( this.#__lines == 1 ) {
				valueInput = this.dom.valueInput = document.createElement( 'input' );
				valueInput.type = 'text' ;
			} else {
				valueInput = this.dom.valueInput = document.createElement( 'textarea' );
				valueInput.rows = this.#__lines ;
			}
			valueInput.id = inputID ;
			valueInput.className = this.dom.stylePrefix + 'value' ;
			p.valueArea.appendChild( valueInput );

			valueInput.value = this.newValue ;

			valueInput.oninput = function( o  , e ) {
				return function() {
					o.newValue = e.value ;
				};
			}( this , valueInput );
		}
	}

	class TDGVariableAddress extends TDGVariableString {
	
		#__helperTimer = false ;
		#__helperSelectedItem = 0 ;
		#__helperItemsCount = 0 ;
	
		generateDom( params ) {
			const dom = this.dom ;
			const valueWrapper = dom.valueWrapper = document.createElement( 'div' );
			valueWrapper.className = dom.stylePrefix + 'wrapper' ;
			const p = this.generateDomPrepare( params );
			p.valueArea.appendChild( valueWrapper );
			
			const inputID = 'i--' + this.uid ;
			
			if ( !p.noLabel ) {
				let label = p.label ;
				label.htmlFor = inputID ;
			}
			
			let valueInput ;
			if ( this.lines == 1 ) {
				valueInput = dom.valueInput = document.createElement( 'input' );
				valueInput.type = 'text' ;
			} else {
				valueInput = dom.valueInput = document.createElement( 'textarea' );
				valueInput.rows = this.lines ;
			}
			valueInput.id = inputID ;
			valueInput.className = dom.stylePrefix + 'value' ;
			valueWrapper.appendChild( valueInput );
			
			valueInput.value = this.newValue ;
			
			const helperList = dom.helperList = document.createElement( 'div' );
			helperList.className = dom.stylePrefix + 'helper-list' ;
			valueWrapper.appendChild( helperList );
			this.hideHelperList();
			
			dom.helperListItems = [];
			this.#__helperSelectedItem = false ;
			this.#__helperItemsCount = 0 ;
			
			valueInput.oninput = function( o  , e ) {
				return function() {
					o.newValue = e.value ;
					o.helperListUpdate();
				};
			}( this , valueInput );
			
			valueInput.onblur = function( o  , e ) {
				return function() {
					o.hideHelperList();
				};
			}( this , valueInput );
			
			valueInput.onkeydown = function( o ) {
				return function( e ) {
					o.keyDown( e );
				};
			}( this );
		}
		
		showHelperList() {
			setTimeout( function( o ) {
				return function() {
					const dom = o.dom ;
					dom.helperList.style.display = '' ;
				}
			} ( this ) , 100 );
		}
		
		hideHelperList() {
			setTimeout( function( o ) {
				return function() {
					const dom = o.dom ;
					dom.helperList.style.display = 'none' ;
					
					const hlia = dom.helperListItems ;
					for( let i = 0 ; i < hlia.length ; i++  ) {
						hlia[ i ].style.display = 'none' ;
					}
				}
			} ( this ) , 100 );
		}
		
		helperListUpdate() {
			if ( this.#__helperTimer !== false ) {
				clearTimeout( this.#__helperTimer );
				this.#__helperTimer = false ;
			}
			
			this.#__helperTimer = setTimeout( function( o ) {
				return function() {
					o.#__helperTimer = false ;
					o.loadHelperList();
				}
			} ( this ) , 250 );
		}
		
		loadHelperList() {
			if ( this.newValue.length < 5 ) {
				return ;
			}
			const req = {
				UserName: "mto@vrcse.ru" ,
				Address : this.newValue
			};
			const data = sendJSONExt(
				JSON.stringify( req ) , true ,
				'https://api.gc-enisey.ru/address/ParsingAddress/' ,
				{ Authorization: 'Bearer 8C=U-k4ut?0EC61Bs4IT9N-Dru7d870LNOPTC8yf93FMYKnpVUI7kIcoDv9g!1czfZHSOA4Qi=IJUusM2oZ-HxNyM6d5wqLCS3ySPKT-DOSdEQWEXg=giIXUvMkErLAk68hoPnKUifnU0HN4wnELWZO3==2dOoFLC37yOlC3ePOcHZ0=K7NaTqKrcwVYOl0?zQU!P=hO8pxz0lqREDDzLgLtgbPrM7anfwP-h5K3uc7YhHAXia8PU32bhpWF5hTi' } ,
				false ,
				function( o ) {
					return function( req ) {
						o.loadHelperListReady( req );
					}
				} ( this )
			);
			
			/*const req = {
				query : this.newValue
			};
			const data = sendJSONExt( JSON.stringify( req ) , false , 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address' , { Authorization : 'Token  31d620b632bb6cf493543596b6166769bccbc4d5' } );*/
			
		}
		
		loadHelperListReady( req ) {
			const data = JSON.parse( req.responseText );
			if ( data && data.length > 0 ) {
				
				if ( data.length > 0 ) {
					this.showHelperList();
				}
				
				const dom = this.dom ;
				const stylePrefix = dom.stylePrefix ;
				
				console.log( data );
				const hlia = dom.helperListItems ;
				for( let i = 0 ; i < hlia.length ; i++  ) {
					hlia[ i ].style.display = ( i < data.length ? '' : 'none' );
				}
				for( let i = hlia.length ; i < data.length ; i++  ) {
					const chli = document.createElement( 'div' );
					chli.className = stylePrefix + 'helper-list-item' ;
					dom.helperList.appendChild( chli );
					hlia.push( chli );
				}
				for ( let i = 0 ; i < data.length ; i++ ) {
					const chli = hlia[ i ];
					const d = data[ i ];
					chli.innerHTML = '' ;
					
					chli.onclick = function( o  , t ) {
						return function() {
							o.newValue = t ;
							o.dom.valueInput.value = t ;
							o.hideHelperList();
						};
					} ( this , d.addressToDisplay );
					
					chli.onmouseover = chli.onmouseenter = function ( y , index ) {
						return function () {
							y.highlightHelperItem( index );
						};
					} ( this , i );
					chli.onmouseout = chli.onmouseleave = function ( y ) {
						return function () {
							y.highlightHelperItem( -1 );
						};
					} ( this );
					
					
					chli.appendChild( document.createTextNode( d.addressToDisplay ) );
				}
				
				this.#__helperItemsCount = data.length ;
			}
		}
		
		keyDown( evt ) {
			evt = evt || window.event ;
			/*if ( ( evt.keyCode == 39 || evt.keyCode == 35 ) && ( srci.selectionStart == srci.value.length || srci.selectionEnd == srci.value.length ) ) {
				if ( x.ONT != null ) {
					srcb.value = srci.value = x.ONT ;
				}
			}*/
			
			const dom = this.dom ;
			const hli = dom.helperListItems ;
			
			if ( evt.keyCode == 40 && this.#__helperItemsCount > 0 ) {
				if ( this.#__helperSelectedItem === false ) {
					this.#__helperSelectedItem = 0 ;
				} else {
					this.#__helperSelectedItem++ ;
					if ( this.#__helperSelectedItem >= this.#__helperItemsCount ) {
						this.#__helperSelectedItem = 0 ;
					}
				}
				
				this.highlightHelperItem( this.#__helperSelectedItem );
			}
			
			if ( evt.keyCode == 38 && this.#__helperItemsCount > 0 ) {
				if ( this.#__helperSelectedItem === false ) {
					this.#__helperSelectedItem = this.#__helperItemsCount - 1 ;
				} else {
					this.#__helperSelectedItem-- ;
					if ( this.#__helperSelectedItem < 0 ) {
						this.#__helperSelectedItem = this.#__helperItemsCount - 1 ;
					}
				}
				
				this.highlightHelperItem( this.#__helperSelectedItem );
			}
			
			if ( evt.keyCode == 13 && this.#__helperItemsCount > 0 && this.#__helperSelectedItem !== false ) {
				if ( this.#__helperSelectedItem >= 0 && this.#__helperSelectedItem < this.#__helperItemsCount ) {
					hli[ this.#__helperSelectedItem ].onclick();
				}
			}
		}
		
		highlightHelperItem( se ) {
			if ( this.#__helperItemsCount == 0 ) {
				return ;
			}
			
			const dom = this.dom ;
			const hl = dom.helperList ;
			const hli = dom.helperListItems ;
			const setClasses = function( sp ) {
				return function( el , s ) {
					el.className = sp + s ;
				};
			} ( dom.stylePrefix );
			
			
			for( let i = 0 ; i < this.#__helperItemsCount ; i++ ) {
				setClasses( hli[ i ] , 'helper-list-item' );
			}
			
			if ( se < 0 || se >= this.#__helperItemsCount  ) {
				return ;
			}
			
			const cse = hli[ se ];
			
			setClasses( cse , 'helper-list-item-selected' );
			this.#__helperSelectedItem = se ;
			
			if ( cse.offsetTop < hl.scrollTop ) {
				hl.scrollTop = cse.offsetTop ;
			} else
			if ( cse.offsetTop + cse.offsetHeight > hl.scrollTop + hl.offsetHeight ) {
				hl.scrollTop = cse.offsetTop + cse.offsetHeight - hl.offsetHeight ;
			}
		}
		
	}

	class TDGVariableNumber extends TDGVariable {
		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			this.setValueRaw( this.defaultValue );
		}

		read( src ) {
			const v = Number( ( '' + src ).replace( ',' , '.' ) );
			this.setValueRaw( v );
		}

		write() {
			return this.value ;
		}

		generateDom( params ) {
			const p = this.generateDomPrepare( params );
			const inputID = 'i--' + this.uid ;

			if ( !p.noLabel ) {
				let label = p.label ;
				label.htmlFor = inputID ;
			}

			const valueInput = this.dom.valueInput = document.createElement( 'input' );
			valueInput.type = 'text' ;
			valueInput.id = inputID ;
			valueInput.className = this.dom.stylePrefix + 'value' ;
			p.valueArea.appendChild( valueInput );

			valueInput.value = this.newValue ;

			valueInput.oninput = function( o  , e ) {
				return function() {
					o.newValue = Number( ( '' + e.value ).replace( ',' , '.' ) );
				};
			}( this , valueInput );
		}
	}

	class TDGVariablePrice extends TDGVariableNumber {
	}

	class TDGVariableDateTime extends TDGVariable {
		static get PART_DATE()      { return 'date'      ; }
		static get PART_TIME()      { return 'time'      ; }
		static get PART_DATE_TIME() { return 'date-time' ; }
		#__parts ;

		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			this.setValueRaw( this.defaultValue );
			this.#__parts  = definition.part ? definition.part : TDGVariableDateTime.PART_DATE_TIME ;
		}

		get parts() {
			return this.#__parts ;
		}

		read( src ) {
			this.setValueRaw( parseInt( src ) );
		}

		write() {
			return this.value ;
		}

		generateDom( params ) {
			const p = this.generateDomPrepare( params );
			let label ;
			if ( !p.noLabel ) {
				label = p.label ;
			}

			const ieh = function( o  , e ) {
				return function() {
					const dom = o.dom ;
					if ( o.parts === TDGVariableDateTime.PART_DATE_TIME ) {
						const e1 = dom.value1Input ;
						const e2 = dom.value2Input ;
						if ( e1.hasAttribute( 'valueAsNumber' ) && e2.hasAttribute( 'valueAsNumber' ) ) {
							const D1 = new Date( e1.valueAsNumber );
							const D2 = new Date( e1.valueAsNumber );
							o.setNewValueRaw( ( new Date( D1.getFullYear() , D1.getMonth() , D1.getDate() , D2.getHours() , D2.getMinutes() , D2.getSeconds() ) ).getTime() );
						} else {
							const D = new Date( e1.value.replace( /(\d{2})\.(\d{2})\.(\d{4})/ , '$3-$2-$1' ) + 'T' + e2.value );
							o.setNewValueRaw( ( new Date( D.getFullYear() , D.getMonth() , D.getDate() , D.getHours() , D.getMinutes() , D.getSeconds() ) ).getTime() );
						}
					} else {
						const e = dom.valueInput ;
						if ( e.hasAttribute( 'valueAsNumber' ) ) {
							o.newValue = e.valueAsNumber ;
						} else {
							o.newValue = Date.parse( e.value );
						}
					}
				};
			}( this );

			if ( this.#__parts != TDGVariableDateTime.PART_DATE_TIME ) {
				const inputID = 'i--' + this.uid ;
				if ( label ) {
					label.htmlFor = inputID ;
				}

				const valueInput = this.dom.valueInput = document.createElement( 'input' );
				valueInput.type = this.#__parts === TDGVariableDateTime.PART_DATE ? 'date' : 'time' ;
				if ( valueInput.hasAttribute( 'valueAsNumber' ) ) {
					valueInput.valueAsNumber = this.newValue ;
				} else {
					valueInput.value = formatDate( this.newValue , this.#__parts === TDGVariableDateTime.PART_DATE ? '{Y}-{m}-{d}' : '{H}:{i}:{s}' ) ;
				}
				valueInput.id = inputID ;
				valueInput.required = true ;
				valueInput.className = this.dom.stylePrefix + 'value' ;
				p.valueArea.appendChild( valueInput );

				valueInput.oninput = ieh ;
			} else {
				const input1ID = 'i1--' + this.uid ;
				const input2ID = 'i2--' + this.uid ;
				if ( label ) {
					label.htmlFor = input1ID ;
				}

				const value1Input = this.dom.value1Input = document.createElement( 'input' );
				value1Input.type = 'date' ;
				value1Input.valueAsNumber = this.newValue ;
				value1Input.id = input1ID ;
				value1Input.required = true ;
				value1Input.className = this.dom.stylePrefix + 'value' ;
				p.valueArea.appendChild( value1Input );
				value1Input.oninput = ieh ;

				const value2Input = this.dom.value2Input = document.createElement( 'input' );
				value2Input.type = 'time' ;
				value2Input.valueAsNumber = this.newValue ;
				value2Input.id = input1ID ;
				value2Input.required = true ;
				value2Input.className = this.dom.stylePrefix + 'value' ;
				p.valueArea.appendChild( value2Input );
				value2Input.oninput = ieh ;
			}
		}
	}

	class TDGVariableOptions extends TDGVariable {
		#__options ;

		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			const opts = this.#__options = {};
			const od = definition[ 'options' ];
			for( let id in od ) {
				opts[ id ] = {
					id ,
					descr : od[ id ] ,
					selected : false
				};
			}

			let value = new Set();
			if ( definition[ 'default' ] ) {
				for( const v of definition[ 'default' ] ) {
					if ( opts[ v ] ) {
						value.add( v );
					}
				}
			}
			this.setValueRaw( value );
		}

		reset() {
			super.reset();
			const opts = this.#__options ;
			const val = this.newValue ;
			for( let id in opts ) {
				opts[ id ].selected = val.has( id );
			}
		}

		read( src ) {
			if ( !src ) {
				src = [];
			}

			const res = new Set();

			const opts = this.#__options ;
			for( let id in opts ) {
				opts[ id ].selected = false ;
			}

			for( const id of src ) {
				if ( opts[ id ] ) {
					opts[ id ].selected = true ;
					res.add( id );
				}
			}

			this.setValueRaw( res );
		}

		write() {
			return [ ...this.value ];
		}

		generateDom( params ) {
			const dom = this.dom ;
			const stylePrefix = dom.stylePrefix ;

			const p = this.generateDomPrepare( params , { labelComplex : 1 } );

			const optionsArea = dom.optionsArea = document.createElement( 'div' );
			optionsArea.className = stylePrefix + 'options-area' ;
			p.valueArea.appendChild( optionsArea );

			const optionsDom = dom.options = {};
			const opt = this.#__options ;
			for( let id in opt ) {
				const cod = {};
				let oid = id + '@' + this.uid ;
				const optionArea = cod.area = document.createElement( 'div' );
				optionArea.className = stylePrefix + 'option-area' ;
				const optionCheckbox = cod.checkbox = document.createElement( 'input' );
				optionCheckbox.type = 'checkbox' ;
				optionCheckbox.id = oid ;
				optionCheckbox.className = stylePrefix + 'option-checkbox' ;
				optionArea.appendChild( optionCheckbox );

				optionCheckbox.checked = opt[ id ].selected ;

				optionCheckbox.onchange = function( o , d , e ) {
					return function() {
						o.setElement( d , e.checked );
					};
				}( this , id , optionCheckbox );

				const optionLabel = cod.label = document.createElement( 'label' );
				optionLabel.className = stylePrefix + 'option-label' ;
				optionLabel.htmlFor = oid ;
				optionLabel.appendChild( document.createTextNode( opt[ id ].descr ) );
				optionArea.appendChild( optionLabel );
				optionsArea.appendChild( optionArea );
				optionsDom[ id ] = cod ;
			}
		}

		setElement( id , state ) {
			if ( this.#__options[ id ] ) {
				this.#__options[ id ].selected = state ;
				this.newValue[ state ? 'add' : 'delete' ]( id );
			}
		}
	}

	class TDGVariableVariant extends TDGVariable {
		#__items ;

		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			const items = this.#__items = {};
			for( let cItem of definition.items ) {
				items[ cItem.id ] = cItem ;
			}

			let value = null ;
			if ( definition[ 'default' ] ) {
				if ( items[ definition[ 'default' ] ] ) {
					value = definition[ 'default' ];
				}
			}
			this.setValueRaw( value );
		}

		read( src ) {
			const v = '' + src ;
			if ( this.#__items[ v ] ) {
				this.setValueRaw( v );
			}
		}

		write() {
			return this.value ;
		}

		generateDom( params ) {
			const dom = this.dom ;
			const stylePrefix = dom.stylePrefix ;
			const p = this.generateDomPrepare( params , { labelComplex : 0 } );

			const inputID = 'i--' + this.uid ;

			if ( !p.noLabel ) {
				let label = p.label ;
				label.htmlFor = inputID ;
			}

			let valueSelect ;
			valueSelect = dom.valueSelect = document.createElement( 'select' );
			valueSelect.id = inputID ;
			valueSelect.className = stylePrefix + 'select' ;
			p.valueArea.appendChild( valueSelect );

			const itemsDom = dom.items = {};
			const items = this.#__items ;
			for( let cItemID in items ) {
				const elItem = itemsDom[ cItemID ] = new Option( items[ cItemID ].descr , cItemID );
				valueSelect.appendChild( elItem );
			}

			valueSelect.value = this.newValue ;

			valueSelect.onselect = valueSelect.onchange = function( o ) {
				return function() {
					o.selectElement();
				};
			}( this );
		}

		selectElement() {
			const sel = this.dom.valueSelect ;
			if ( this.#__items[ sel.value ] ) {
				this.setNewValueRaw( sel.value );
			}
		}
		
		showValue( tgt ) {
			if ( this.#__items[ tgt ] ) {
				return this.#__items[ tgt ].descr ;
			} else {
				return '< не выбрано >' ;
			}
		}
	}

	class TDGVariableArray extends TDGVariable {
		static get hasChilds() { return true };
		static get fbp_SkipCount() { return 1 };

		#__elementDefinition ;
		#__maxLength ;

		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			this.#__elementDefinition = [];
			for( const ed of definition[ 'array-element-definition' ] ) {
				this.#__elementDefinition.push( ed );
			}
			this.#__maxLength = definition[ 'max-length' ] ? definition[ 'max-length' ] : 0 ;
			
			this.setValueRaw( this.defaultValue ?? [] );
		}

		read( src ) {
			if ( !src ) {
				src = [];
			}

			const elDefMap = {};
			for( const d of this.#__elementDefinition ) {
				elDefMap[ d.name ] = d ;
			}
			for( let i = 0 ; i < src.length ; i++ ) {
				const srcEl = src[ i ];
				const name = Object.keys( srcEl )[ 0 ];
				const cDef = elDefMap[ name ];
				const cVal = this.addElementTgt( cDef , this.value );
				cVal.read( srcEl[ name ] );
			}
		}

		write() {
			const res = [];
			const v = this.value ;
			for( let i = 0 ; i < v.length ; i++ ) {
				res[ i ] = {
					[ v[ i ].name ] : v[ i ].write()
				};
			}
			return res ;
		}

		generateDom( params ) {
			const dom = this.dom ;
			const stylePrefix = dom.stylePrefix ;
			let p ;
			let buttons ;
			let gdp ;
			console.log( 'TDGVariableArray.generateDom: params' , params );
			switch ( this.layout ) {
				case TDGVariable.LAYOUT_EXTERNAL :
					
					p = this.generateDomPrepare( params , {
						area : params.area ,
						noLabel : true
					} );
					
					const btnArea = params.reqAreaFn( null , null , 'btn' );
					buttons = this.dom.buttons = {};
					gdp = Object.assign( {} , params , {
						level : p.level + 1
					} );
					
					console.log( 'TDGVariableArray.generateDom: gdp' , gdp );
					
					for( const ed of this.#__elementDefinition ) {
						const btn = document.createElement( 'a' );
						btn.classList.add( stylePrefix + 'btn-add' );
						btn.classList.add( 'btn1' );
						btn.appendChild( document.createTextNode( '+ ' + ed.descr ) );
						btn.onclick = function( o , d , p ) {
							return function() {
								const e = o.addElement( d );
								if ( e ) {
									e.reset();
									e.generateDom( Object.assign( {} , p , {
										level : p.level + 1
									} ) );
								}
							};
						} ( this , ed , gdp );
						btnArea.appendChild( btn );
						buttons[ ed.name ] = btn ;
						
						console.log( 'DBG : EXTERNAL-CONTROLS' , ed );
						
						if ( ed[ 'external-controls' ] ) {
							const defEC = ed[ 'external-controls' ];
							for( const cecd of defEC ) {
								console.log( 'DBG : EXTERNAL-CONTROLS-ITEM' , cecd );
								switch ( cecd.type ) {
									case 'button' :
										const btn = document.createElement( 'a' );
										btn.classList.add( stylePrefix + 'btn-add' );
										btn.classList.add( 'btn1' );
										btn.appendChild( document.createTextNode( '+ ' + cecd.descr ) );
										btn.onclick = function( o , d , p , cbn ) {
											return function() {
												window[ cbn ]( o , d , p );
											};
										}( this , ed , gdp , cecd.onclick );
										btnArea.appendChild( btn );
										buttons[ ed.name + '[button:' + cecd.onclick + ']' ] = btn ;
										break ;
								}
							}
						}
					}
					
					this.controlMaxLength();
					
					for( const val of this.newValue ) {
						val.generateDom( gdp );
					}
					
					break ;
					
				default :
					//console.log( 'array params: ' , params );
					p = this.generateDomPrepare( params , { labelComplex : 1 } );
					
					if ( p.noLabel || p.alienLabel ) {
						p.buttonsArea = dom.buttonsArea = document.createElement( 'div' );
						p.buttonsArea.classList.add( 'buttons-area' );
						p.valueArea.appendChild( p.buttonsArea );
					} else {
						p.buttonsArea = dom.buttonsArea = p.label ;
					}
					
					const valuesWrapper = dom.valuesWrapper = document.createElement( 'div' );
					valuesWrapper.className = stylePrefix + 'elements-wrapper' ;
					p.valueArea.appendChild( valuesWrapper );
					
					buttons = this.dom.buttons = {};
					for( const ed of this.#__elementDefinition ) {
						const btn = document.createElement( 'a' );
						//btn.classList.add( stylePrefix + 'btn-add' );
						btn.classList.add( 'btn1' );
						btn.appendChild( document.createTextNode( '+ ' + ed.descr ) );
						btn.onclick = function( o , d , p ) {
							return function() {
								const e = o.addElement( d );
								if ( e ) {
									e.reset();
									e.generateDom( {
										parent : o.dom.valuesWrapper ,
										level : p.level + 1
									} );
								}
							};
						}( this , ed , p );
						p.buttonsArea.appendChild( btn );
						buttons[ ed.name ] = btn ;
					}
					
					if ( this.definition[ 'external-controls' ] ) {
						/*const btn = document.createElement( 'a' );
						btn.classList.add( 'btn1' );
						btn.appendChild( document.createTextNode( '+ ' + ed.descr ) );
						btn.onclick = function( o , d , p ) {
							return function() {
								const e = o.addElement( d );
								if ( e ) {
									e.reset();
									e.generateDom( {
										parent : o.dom.valuesWrapper ,
										level : p.level + 1
									} );
								}
							};
						}( this , ed , p );
						p.buttonsArea.appendChild( btn );
						buttons[ ed.name ] = btn ;*/
						console.log( this.definition[ 'external-controls' ] );
					}
					
					this.controlMaxLength();
					
					gdp = {
						parent : this.dom.valuesWrapper ,
						level : p.level + 1
					};
					
					for( const val of this.newValue ) {
						val.generateDom( gdp );
					}
					break;
			}
		}

		clearDom() {
			super.clearDom();
		}

		// true when length >= maxLength
		controlMaxLength() {
			const dis = this.#__maxLength != 0 && this.newValue.length >= this.#__maxLength ;
			if ( this.dom && this.dom.buttons ) {
				const btnList = this.dom.buttons ;
				for( let i in btnList ) {
					btnList[ i ].classList.toggle( 'disabled' , dis );
				}
			}

			return dis ;
		}

		addElementTgt( definition , tgt ) {
			const newIndex = tgt.length ;
			const element = TDGVariable.fromDef( this , definition , Object.assign( {} , this.exData , {
				'path-prefix' : '[' + newIndex + ']'
			} ) );
			tgt.push( element );
			return element ;
		}

		addElement( definition ) {
			if ( this.controlMaxLength() ) {
				return null ;
			}

			const element = this.addElementTgt( definition , this.newValue );
			this.controlMaxLength();
			return element ;
		}
	}

	class TDGVariableStructure extends TDGVariable {
		static get hasChilds() { return true ; };
		static get defaultLayout() { return TDGVariable.LAYOUT_ALIGNED_LIST ; };

		#__elementDefinition ;

		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			const value = {};
			const elDef = this.#__elementDefinition = [];
			const childExData = Object.assign( {} , exData );
			delete childExData[ 'path-prefix' ];
			for( const ed of definition[ 'structure-definition' ] ) {
				value[ ed.name ] = TDGVariable.fromDef( this , ed , childExData );
				elDef.push( ed );
			}
			this.setValueRaw( value );
		}

		read( src ) {
			if ( !src ) {
				src = {};
			}

			const val = this.value ;
			for( const elDef of this.#__elementDefinition ) {
				const elName = elDef.name ;
				if ( val[ elName ] && src[ elName ] ) {
					val[ elName ].read( src[ elName ] );
				}
			}
		}

		write() {
			const res = {};
			const value = this.value ;
			const elDef = this.#__elementDefinition ;
			for( const ed of elDef ) {
				const name = ed.name ;
				res[ name ] = value[ name ].write();
			}
			return res ;
		}

		generateDom( params ) {
			const dom = this.dom ;
			const stylePrefix = dom.stylePrefix ;
			let valuesWrapper ;
			console.log( params );
			switch( this.layout ) {
				case TDGVariable.LAYOUT_LIST :
					valuesWrapper = dom.valuesWrapper = document.createElement( 'div' );
					valuesWrapper.classList.add( stylePrefix + 'elements-area' );
					break ;

				case TDGVariable.LAYOUT_ALIGNED_LIST :
				case TDGVariable.LAYOUT_COLUMNS :
					valuesWrapper = dom.valuesWrapper = document.createElement( 'table' );
					valuesWrapper.classList.add( stylePrefix + 'elements-area' );
					valuesWrapper.classList.add( 'layout--' + this.layout );
					break ;


				case TDGVariable.LAYOUT_LINE :
				case TDGVariable.LAYOUT_ONE_LINE :
				case TDGVariable.LAYOUT_DOCKED_GRID :
					valuesWrapper = dom.valuesWrapper = document.createElement( 'div' );
					valuesWrapper.classList.add( stylePrefix + 'elements-area' );
					valuesWrapper.classList.add( 'layout--' + this.layout );
					break ;
					
				/*case TDGVariable.LAYOUT_EXTERNAL :
					valuesWrapper = dom.valuesWrapper = document.createElement( 'div' );
					valuesWrapper.classList.add( stylePrefix + 'elements-area' );
					valuesWrapper.classList.add( 'layout--' + this.layout );
					break ;*/
			}

			const p = this.generateDomPrepare( params , { valueArea : valuesWrapper , labelComplex : 1 } );
			
			console.log( p );

			if ( this.layout === TDGVariable.LAYOUT_ONE_LINE ) {
				p.area.classList.add( 'layout--one-line' );
			}

			const value = this.newValue ;
			const elDef = this.#__elementDefinition ;
			p.area.appendChild( valuesWrapper );

			switch( this.layout ) {
				case TDGVariable.LAYOUT_LIST :
				case TDGVariable.LAYOUT_LINE :
				case TDGVariable.LAYOUT_ONE_LINE :
				case TDGVariable.LAYOUT_EXTERNAL :
					for( const ed of elDef ) {
						const name = ed.name ;
						value[ name ].generateDom( {
							area : valuesWrapper ,
							level : p.level + 1
						} );
					}
					break ;

				case TDGVariable.LAYOUT_ALIGNED_LIST :
					for( const ed of elDef ) {
						const name = ed.name ;
						const row = valuesWrapper.insertRow();
						const lc = row.insertCell();
						const vc = row.insertCell();
						value[ name ].generateDom( {
							area : row ,
							labelArea : lc ,
							valueArea : vc ,
							level : p.level + 1
						} );
					}
					break ;

				case TDGVariable.LAYOUT_COLUMNS :
					const row = valuesWrapper.insertRow();
					for( const ed of elDef ) {
						const name = ed.name ;
						const lc = row.insertCell();
						const vc = row.insertCell();
						value[ name ].generateDom( {
							area : row ,
							labelArea : lc ,
							valueArea : vc ,
							level : p.level + 1
						} );
					}
					break ;
					
				case TDGVariable.LAYOUT_DOCKED_GRID :
				/*case TDGVariable.LAYOUT_EXTERNAL :*/
					const dockInfo = {
						cols : 1 ,
						rows : 1 ,
						vars : [] ,
						fields : []
					};
					const reqArea = function( vw , di , parent , p ) {
						return function( vid , fid , type ) {
							if ( type == 'btn' ) {
								return p.label ;
							}
							
							const dom = parent.dom ;
							const setClasses = function( sp , spc ) {
								return function( el , s ) {
									el.classList.add( 'docked-grid--' + s );
								};
							} ( dom.stylePrefix , dom.stylePrefixCommon );
							
							
							let res ;
							let cvd ;
							if ( !di.vars[ vid ] ) {
								cvd = {
									index : di.cols
								};
								di.vars[ vid ] = cvd ;
								di.cols += 1 ;
								vw.style.gridTemplateColumns = ( parent.definition[ 'layout.param-name-max-width' ] ?? 'auto' ) + ' repeat( ' + ( di.cols - 1 ) + ' , ' + ( parent.definition[ 'layout.element-max-width' ] ?? 'auto' ) + ' )' ;
							} else {
								cvd = di.vars[ vid ];
							}
							
							let cfd ;
							
							if ( type == 'description' ) {
								if ( fid == null ) {
									res = document.createElement( 'div' );
									setClasses( res , 'caption' );
									res.style.gridColumnStart = ( cvd.index + 1 ) + '' ;
									res.style.gridColumnEnd = ( cvd.index + 2 ) + '' ;
									res.style.gridRowStart = '1' ;
									res.style.gridRowEnd = '2' ;
									vw.appendChild( res );
									return res ;
								} else {
									if ( !di.fields[ fid ] ) {
										cfd = {
											index : di.rows
										};
										di.fields[ fid ] = cfd ;
										di.rows += 1 ;
										vw.style.gridTemplateRows = 'repeat( ' + di.rows + ' , auto )' ;
										
										res = document.createElement( 'div' );
										setClasses( res , 'label' );
										res.style.gridColumnStart = '1' ;
										res.style.gridColumnEnd = '2' ;
										res.style.gridRowStart = ( cfd.index + 1 ) + '' ;
										res.style.gridRowEnd = ( cfd.index + 2 ) + '' ;
										vw.appendChild( res );
										return res ;
									} else {
										return false ;
									}
								}
							} else {
								if ( !di.fields[ fid ] ) {
									return false ;
								} else {
									cfd = di.fields[ fid ];
									res = document.createElement( 'div' );
									setClasses( res , 'value-area' );
									res.style.gridColumnStart = ( cvd.index + 1 ) + '' ;
									res.style.gridColumnEnd = ( cvd.index + 2 ) + '' ;
									res.style.gridRowStart = ( cfd.index + 1 ) + '' ;
									res.style.gridRowEnd = ( cfd.index + 2 ) + '' ;
									vw.appendChild( res );
									return res ;
								}
							}
						};
					} ( valuesWrapper , dockInfo , this , p );
					
					console.log( this.definition );
					
					const corner = document.createElement( 'div' );
					corner.className = stylePrefix + 'elements-area-corner' ;
					valuesWrapper.appendChild( corner );
					
					for( const ed of elDef ) {
						const name = ed.name ;
						value[ name ].generateDom( {
							area : valuesWrapper ,
							level : p.level + 1 ,
							alienLabel : true ,
							reqAreaFn : reqArea
						} );
					}
					break ;
				
			}
		}
	}

	class TDGVariableClass extends TDGVariable {
		static get hasChilds() { return true };

		#__classID ;
		#__classDefinition ;

		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			const value = {};
			
			console.log( 'TDGVariableClass.constructor: definition' , definition );
			console.log( 'TDGVariableClass.constructor: exData' , exData );
			
			
			const classID = this.#__classID = ( definition[ 'class-id' ] ?? null );
			if ( !classID ) {
				return ;
			}

			if ( !exData.classes[ classID ] ) {
				return ;
			}

			const childExData = Object.assign( {} , exData );
			delete childExData[ 'path-prefix' ];
			const currentClass = this.#__classDefinition = exData.classes[ classID ];
			for( let fID in currentClass.fields ) {
				let f = currentClass.fields[ fID ];
				let vd = f.getVariableDeinition();
				value[ f.id ] = TDGVariable.fromDef( this , vd , childExData );
			}

			this.setValueRaw( value );
		}

		read( src ) {
			if ( !src ) {
				src = {};
			}

			const val = this.value ;
			const currentClass = this.#__classDefinition ;
			for( let fID in currentClass.fields ) {
				let f = currentClass.fields[ fID ];
				let vd = f.getVariableDeinition();
				if ( val[ f.id ] && src[ f.id ] ) {
					val[ f.id ].read( src[ f.id ] );
				}
			}
		}

		write() {
			const res = {};
			const fDef = this.#__classDefinition.fields ;
			for( let fID in fDef ) {
				res[ fID ] = this.value[ fID ].write();
			}
			return res ;
		}

		generateDom( params ) {
			const dom = this.dom ;
			const stylePrefix = dom.stylePrefix ;
			let valuesArea ;
			console.log( 'TDGVariableClass.generateDom: params ' , params );
			let p ;
			let fDef ;
			switch( this.layout ) {
				case TDGVariable.LAYOUT_EXTERNAL :
					const labelArea = params.reqAreaFn( this.uid , null , 'description' );
					
					p = this.generateDomPrepare( params , {
						area : params.area ,
						labelArea : labelArea
					} );
					
					fDef = this.#__classDefinition.fields ;
					for( let fID in fDef ) {
						const lc = params.reqAreaFn( this.uid , fID , 'description' );
						const vc = params.reqAreaFn( this.uid , fID , 'value' );
						const gdp = {
							area : params.area ,
							valueArea : vc ,
							level : p.level + 1 ,
							reqAreaFn : params.reqAreaFn
						};
						if ( params.alienLabel ) {
							gdp.alienLabel = true ;
						}
						if ( lc !== false ) {
							gdp.labelArea = lc ;
						} else {
							gdp.noLabel = true ;
						}
						this.newValue[ fID ].generateDom( gdp );
					}
					
					break ;
					
				default :
					valuesArea = dom.valuesArea = document.createElement( 'table' );
					p = this.generateDomPrepare( params , {
						valueArea : valuesArea
					} );
					
					valuesArea.className = 'dg--ev-visual--class--elements-area' ;
					p.area.appendChild( valuesArea );
					
					fDef = this.#__classDefinition.fields ;
					for( let fID in fDef ) {
						const row = valuesArea.insertRow();
						const lc = row.insertCell();
						const vc = row.insertCell();
						this.newValue[ fID ].generateDom( {
							area : row ,
							labelArea : lc ,
							valueArea : vc ,
							level : p.level + 1
						} );
					}
					break ;
			}
			
			//const p = this.generateDomPrepare( params , { valueArea : valuesArea , labelComplex : 1 } );
			
			
			
			
			/*
				const dom = this.dom ;
				const stylePrefix = dom.stylePrefix ;
				let valuesWrapper ;
				console.log( params );
				switch( this.#__layout ) {
					case TDGVariable.LAYOUT_LIST :
						valuesWrapper = dom.valuesWrapper = document.createElement( 'div' );
						valuesWrapper.classList.add( stylePrefix + 'elements-area' );
						break ;
	
					case TDGVariable.LAYOUT_ALIGNED_LIST :
					case TDGVariable.LAYOUT_COLUMNS :
						valuesWrapper = dom.valuesWrapper = document.createElement( 'table' );
						valuesWrapper.classList.add( stylePrefix + 'elements-area' );
						valuesWrapper.classList.add( 'layout--' + this.#__layout );
						break ;
	
	
					case TDGVariable.LAYOUT_LINE :
					case TDGVariable.LAYOUT_ONE_LINE :
					case TDGVariable.LAYOUT_DOCKED_GRID :
						valuesWrapper = dom.valuesWrapper = document.createElement( 'div' );
						valuesWrapper.classList.add( stylePrefix + 'elements-area' );
						valuesWrapper.classList.add( 'layout--' + this.#__layout );
						break ;
				}
				
				const p = this.generateDomPrepare( params , { valueArea : valuesWrapper , labelComplex : 1 } );
				
				console.log( p );
				
				if ( this.#__layout === TDGVariable.LAYOUT_ONE_LINE ) {
					p.area.classList.add( 'layout--one-line' );
				}
				
				const value = this.newValue ;
				const elDef = this.#__elementDefinition ;
				p.area.appendChild( valuesWrapper );
				
				switch( this.#__layout ) {
					case TDGVariable.LAYOUT_LIST :
					case TDGVariable.LAYOUT_LINE :
					case TDGVariable.LAYOUT_ONE_LINE :
					case TDGVariable.LAYOUT_EXTERNAL :
						for( const ed of elDef ) {
							const name = ed.name ;
							value[ name ].generateDom( {
								area : valuesWrapper ,
								level : p.level + 1
							} );
						}
						break ;
					
					case TDGVariable.LAYOUT_ALIGNED_LIST :
						for( const ed of elDef ) {
							const name = ed.name ;
							const row = valuesWrapper.insertRow();
							const lc = row.insertCell();
							const vc = row.insertCell();
							value[ name ].generateDom( {
								area : row ,
								labelArea : lc ,
								valueArea : vc ,
								level : p.level + 1
							} );
						}
						break ;
					
					case TDGVariable.LAYOUT_COLUMNS :
						const row = valuesWrapper.insertRow();
						for( const ed of elDef ) {
							const name = ed.name ;
							const lc = row.insertCell();
							const vc = row.insertCell();
							value[ name ].generateDom( {
								area : row ,
								labelArea : lc ,
								valueArea : vc ,
								level : p.level + 1
							} );
						}
						break ;
					
					case TDGVariable.LAYOUT_DOCKED_GRID :
						const docInfo = {};
						const reqArea = function( di ) {
							return function( id ) {
								if ( di[ id ] ) {
								} else {
								}
							};
						}( docInfo );
						for( const ed of elDef ) {
							const name = ed.name ;
							value[ name ].generateDom( {
								area : valuesWrapper ,
								level : p.level + 1 ,
								reqAreaFn : reqArea
							} );
						}
						break ;
					
				}
		*/
		}
	}

	class TDGVariableImage extends TDGVariable {
		#__imageData ;
		#__transforms ;
		#__cache ;
		#__originalSize ;
		#__thumbnailSize ;

		constructor( parent , name , type , description , definition , exData ) {
			super( parent , name , type , description , definition , exData );
			this.#__thumbnailSize = definition[ 'thumbnail-size' ] ? definition[ 'thumbnail-size' ] : { width : 192 , height : 128 };
			this.#__transforms = [];
			this.#__originalSize = null ;
		}

		get thumbnailSize() {
			return this.#__thumbnailSize ;
		}

		get originalSize() {
			return this.#__originalSize ;
		}

		get value() {
			return this.#__cache ;
		}

		read( src ) {
			if ( !src || !src.data ) {
				return ;
			}

			this.#__transforms = [];
			if ( src.transforms ) {
				const st = src.transforms ;
				const dt = this.#__transforms ;
				for( let i = 0 ; i < st.length ; i++ ) {
					dt[ i ] = Object.assign( {} , st[ i ] );
				}
			}

			this.LoadImageFromURL( src.data );
		}

		write() {
			const res = {
				data : this.#__imageData ,
				transforms : this.#__transforms
			};

			return res ;
		}


		generateDom( params ) {
			const dom = this.dom ;
			const stylePrefix = dom.stylePrefix ;

			const p = this.generateDomPrepare( params );

			const thumbnailArea = dom.thumbnailArea = document.createElement( 'div' );

			thumbnailArea.className = stylePrefix + 'thumbnail' ;
			thumbnailArea.style.width  = this.#__thumbnailSize.width  + 'px' ;
			thumbnailArea.style.height = this.#__thumbnailSize.height + 'px' ;
			thumbnailArea.style.maxWidth  = this.#__thumbnailSize.width  + 'px' ;
			thumbnailArea.style.maxHeight = this.#__thumbnailSize.height + 'px' ;
			p.valueArea.appendChild( thumbnailArea );

			const toolbar = document.createElement( 'div' );
			toolbar.className = stylePrefix + 'toolbar' ;
			thumbnailArea.appendChild( toolbar );

			const btnFromFile = document.createElement( 'div' );
			btnFromFile.className = stylePrefix + 'btn-from-file' ;
			btnFromFile.append( 'Ф' );
			toolbar.appendChild( btnFromFile );

			btnFromFile.onclick = function( o ) {
				return function( evt ){
					o.onLoadFromFile( evt );
				};
			}( this );

			const btnFromClipboard = document.createElement( 'div' );
			btnFromClipboard.className = stylePrefix + 'btn-from-clipboard' ;
			btnFromClipboard.append( 'Б' );
			toolbar.appendChild( btnFromClipboard );

			btnFromClipboard.onclick = function( o ) {
				return function() {
					o.onPasteFromClipboard();
				};
			}( this );

			const btnRemoveImage = document.createElement( 'div' );
			btnRemoveImage.className = stylePrefix + 'btn-remove-image' ;
			thumbnailArea.appendChild( btnRemoveImage );

			btnRemoveImage.onclick = function( o ) {
				return function( evt ) {
					o.onDeleteImage();
				};
			}( this );
		}

		onLoadFromFile( evt ) {
			const fileInput = document.createElement( 'input' );
			fileInput.type = 'file' ;
			fileInput.onchange = function( t ) {
				return function( evt ){
					t.onFileSelect( evt );
				};
			}( this );
			this.dom.fileInput = fileInput ;
			fileInput.click();
		}

		onFileSelect( evt ) {
			const files = evt.target.files ;
			const f = files[ 0 ];
			console.log( files );

			if ( !f.type.match( /image\/.+/ ) ) {
				alert( "Не верный тип документа!" );
				return ;
			}

			const reader = new FileReader();
			reader.onload = ( function( o , x ) {
				return function( e ) {
					const data = e.target.result ;
					if ( x.type.match( /image\/.+/ ) ) {
						//o.LoadImageFromURL( data );
						console.log( x );
						o.PreloadImageFromURL( data , x.type , x.size );
					}
				};
			} )( this , f );
			reader.readAsDataURL( f );
		}

		async onPasteFromClipboard() {
			try {
				const permission = await navigator.permissions.query( {
					name : 'clipboard-read' ,
				} );
				if ( permission.state === 'denied' ) {
					throw new Error( 'Чтение из буфера не разрешено' );
				}
				const clipboardContents = await navigator.clipboard.read();
				for ( const item of clipboardContents ) {
					let blob ;
					if ( item.types.includes( 'image/png' ) ) {
						blob = await item.getType( 'image/png' );
					} else
					if ( item.types.includes( 'image/jpeg' ) ) {
						blob = await item.getType( 'image/jpeg' );
					} else {
						return ;
					}
					const fileReader = new FileReader();
					fileReader.onloadend = function( r , o , t , s ) {
						return function(){
							console.log( 'image from buffer base64 size : ' + r.result.length );
							o.PreloadImageFromURL( r.result , t , s );
						};
					}( fileReader , this , blob.type , blob.size );
					fileReader.readAsDataURL( blob );
				}
			} catch ( error ) {
				console.error( error.message );
			}
		}

		PreloadImageFromURL( data , type , size ) {
			if ( type === 'image/bmp' || type === 'image/png' ) {
				console.log( 'image preload of type : ' + type + ' original size : ' + size );
				const fmt = type.substring( 'image/'.length );
				let res = confirm(
					'Изображения формата ' + fmt.toUpperCase() + ' могут занимать больше памяти чем изображения в формате JPEG.' +
					' Хотите преобразовать ваше изображение к формату JPEG?'
				);
				if ( res ) {
					this.ConvertImage( data , 'image/jpeg' , size );
					return ;
				}
			}

			this.LoadImageFromURL( data );
		}

		ConvertImage( data , toType , refSize ) {
			const img = new Image();
			img.onload = function( i , t , it , rs , od ) {
				return function() {
					const canvas = document.createElement( 'canvas' );
					canvas.width = i.width ;
					canvas.height = i.height ;

					const c = canvas.getContext( '2d' );
					c.drawImage( i , 0 , 0 );

					const td = canvas.toDataURL( it , 1.0 );
					if ( td.length >= rs ) {
						console.log( 'image NOT converted to type : ' + it + ' new size : ' + td.length );
						t.LoadImageFromURL( od );
					} else {
						console.log( 'image converted to type : ' + it + ' new size : ' + td.length );
						t.LoadImageFromURL( td );
					}
				};
			}( img , this , toType , refSize , data );
			img.src = data ;
		}

		LoadImageFromURL( srcURL ) {
			this.#__imageData = srcURL ;
			const dom = this.dom ;
			const origSize = this.#__originalSize = {};

			const image = this.#__cache = new Image();
			image.onload = function( i , t , s ) {
				return function() {
					const dom = t.dom ;
					s.width = i.width ;
					s.height = i.height ;

					const ts = t.thumbnailSize ;
					const ns = {};

					const canvas = document.createElement( 'canvas' );
					ns.h = Math.round( i.height * ts.width / i.width );
					if ( ns.h <= ts.height ) {
						ns.w = ts.width ;
					} else {
						ns.w = Math.round( i.width * ts.height / i.height );
						ns.h = ts.height ;
					}
					canvas.width = ns.w ;
					canvas.height = ns.h ;

					const c = canvas.getContext( '2d' );
					c.drawImage( i , 0 , 0 , ns.w , ns.h );

					const td = canvas.toDataURL( 'image/jpeg', 1.0 );
					const thumbnailImage = new Image();
					thumbnailImage.src = td ;
					const st = dom.thumbnailArea.style ;
					st.width = '' ;
					st.height = '' ;

					if ( dom.thumbnailImage ) {
						dom.thumbnailArea.replaceChild( thumbnailImage , dom.thumbnailImage );
					} else {
						dom.thumbnailArea.prepend( thumbnailImage );
					}
					dom.thumbnailImage = thumbnailImage ;

					thumbnailImage.onclick = function( o ) {
						return function() {
							o.onClickImage();
						};
					}( t );

				};
			}( image , this , origSize );
			image.src = srcURL ;
		}

		onDeleteImage() {
			const dom = this.dom ;
			const ta = dom.thumbnailArea ;
			ta.removeChild( dom.thumbnailImage );
			dom.thumbnailImage = null ;
			dom.fileInput = null ;
			ta.style.width = this.#__thumbnailSize.width + 'px' ;
			ta.style.height = this.#__thumbnailSize.height + 'px' ;
			this.#__imageData = null ;
		}



		onClickImage() {
			const dom = this.dom ;
			const form = new TDLGForm();

			Object.assign( form , {
				caption : 'Работа с изображением' ,
				width : 1200 ,
				height : 800 ,
				maxHeight : 960 ,
				flowDirection : TDLGComponent.DIRECTION_TOP_BOTTOM
			} );

			const btnPanel = document.createElement( 'div' );
			btnPanel.className = 'dg--dlg-form----btn-panel' ;
			form.dom.dlgArea.appendChild( btnPanel );

			const btnApply = document.createElement( 'a' );
			btnApply.className = 'btn3' ;
			btnApply.appendChild( document.createTextNode( 'Сохранить и продолжить' ) );
			/*btnApply.onclick = function( res , f, data , ev ) {
				return function() {
					f.hide();
					for( let n in ev ) {
						data[ n ] = ev[ n ].write();
					}
					console.log( data );
					res();
				};
			}( resolve , form , DGData.collectedData , extVar );*/
			btnPanel.appendChild( btnApply );

			/*form.onCLose = function( rej ) {
				return function() {
					let res = confirm( 'Все несохраненные данные будут потеряны. Продолжить ?' );
					if ( res ) {
						rej();
						return true ;
					} else {
						return false ;
					}
				};
			}( reject );*/

			form.show();
			form.onCLose = function( t , f ){
				return function(){
					return true ;
				};
			}( this , form );
		}
	}



	function generateReqID( DGData ) {
		let prefix = ( new Date() ).getTime() + ':' + DGData.tmplID + ':' + DGData.rootID ;
		return prefix + ':' + generateGUID();
	}


	class TDGClassField {
		id ;
		name ;
		type ;
		description ;
		unit ;
		defaultValue ;

		constructor( def ) {
			if ( def instanceof Node ) {
				for ( const p of [ 'id' , 'type' ] ) {
					if ( def.hasAttribute( p ) ) {
						this[ p ] = def.getAttribute( p );
					} else {
						throw new Error( 'field ' + p.toUpperCase() + ' not set' );
					}
				}

				let elements = {};
				for( const cn of def.children ) {
					elements[ cn.nodeName ] = cn ;
				}

				if ( elements[ 'name' ] ) {
					this.name = getXMLNodeValue( elements[ 'name' ] );
				} else {
					throw new Error( 'field NAME not set' );
				}
				
				if ( elements[ 'default' ] ) {
					this.defaultValue = getXMLNodeValue( elements[ 'default' ] );
				}
			} else {
				for( let prop in this ) {
					this[ prop ] = def[ prop ];
				}
			}
		}

		static fromDef( def ) {
			const MAP = {
				'variant' : TDGClassFieldVariant ,
				'array'   : TDGClassFieldArray
			};

			let type ;

			if ( def instanceof Node ) {
				if ( def.hasAttribute( 'type' ) ) {
					type = def.getAttribute( 'type' );
				} else {
					throw new Error( 'field ' + p.toUpperCase() + ' not set' );
					return ;
				}
			} else {
				type = def.type ;
			}

			if ( MAP[ type ] ) {
				const cd = MAP[ type ];
				return new cd( def );
			} else {
				return new TDGClassField( def );
			}
		}

		getVariableDeinition() {
			const res = {
				name  : this.id ,
				type  : this.type ,
				descr : this.name
			};
			if ( this.defaultValue ) {
				res.default = this.defaultValue ;
			}
			return res ;
		}
	}

	class TDGClassFieldVariant extends TDGClassField {
		items ;

		constructor( def ) {
			super( def );
			this.items = [];

			if ( def instanceof Node ) {
				let elements = {};
				for( const cn of def.children ) {
					elements[ cn.nodeName ] = cn ;
				}

				if ( elements[ 'items' ] ) {
					for( const cn of elements[ 'items' ].querySelectorAll( ':scope > item' ) ) {
						const cItem = {
							id : cn.getAttribute( 'id' ) ?? null ,
							descr : getXMLNodeValue( cn )
						};
						this.items.push( cItem );
					}
				} else {
					throw new Error( 'items not found' );
				}
			} else {
				const items = def.items ?? [];
				for( let cItem of items ) {
					this.items.push( cItem );
				}
			}
		}

		getVariableDeinition() {
			const res = super.getVariableDeinition();
			res.items = this.items ;
			return res ;
		}
	}

	class TDGClassFieldArray extends TDGClassField {
		elementsDefinition ;

		constructor( def ) {
			super( def );
			const elDef = this.elementsDefinition = [];

			if ( def instanceof Node ) {
				let elements = {};
				for( const cn of def.children ) {
					elements[ cn.nodeName ] = cn ;
				}

				if ( elements[ 'elements' ] ) {
					for( const cn of elements[ 'elements' ].querySelectorAll( ':scope > element' ) ) {
						const cItem = TDGClassField.fromDef( cn );
						elDef.push( cItem );
					}
				} else {
					throw new Error( 'element definition not found' );
				}
			} else {
				const elements = def.elementsDefinition ?? [];
				for( let cDef of elements ) {
					const cItem = TDGClassField.fromDef( cDef );
					elDef.push( cItem );
				}
			}
		}

		getVariableDeinition() {
			const res = super.getVariableDeinition();
			const elements = this.elementsDefinition ?? [];
			res[ 'array-element-definition' ] = [];
			for( let cDef of elements ) {
				res[ 'array-element-definition' ].push( cDef.getVariableDeinition() );
			}
			return res ;
		}
	}

	class TDGClass {
		id ;
		parentID ;
		name ;
		fields ;
		constructor( def ) {
			this.fields = {};

			if ( def instanceof Node ) {
				if ( def.hasAttribute( 'id' ) ) {
					this.id = def.getAttribute( 'id' );
				} else {
					throw new Error( 'Class ID not set' );
				}

				this.parentID = def.hasAttribute( 'parent-class-id' ) ? def.getAttribute( 'parent-class-id' ) : null ;

				let elements = {};
				for( const cn of def.children ) {
					elements[ cn.nodeName ] = cn ;
				}

				if ( elements[ 'name' ] ) {
					this.name = getXMLNodeValue( elements[ 'name' ] );
				} else {
					throw new Error( 'Class NAME not set' );
				}

				if ( !elements[ 'fields' ] ) {
					throw new Error( 'Class Fields not set' );
				}

				for( const cn of elements[ 'fields' ].querySelectorAll( ':scope > field' ) ) {
					let tmpField = TDGClassField.fromDef( cn ); //new TDGClassField( cn );
					this.fields[ tmpField.id ] = tmpField ;
				}
			} else {
				for( let prop in this ) {
					if ( prop === 'fields' ) {
						let fields = def.fields ;
						for( let fid in fields ) {
							this.fields[ fid ] = TDGClassField.fromDef( fields[ fid ] );
						}
					} else {
						this[ prop ] = def[ prop ];
					}
				}
			}
		}
	}

	class TDocGenerator_DataCollection {
		static get LIST_SIZE__SMALL()   { return 'small' };
		static get LIST_SIZE__BIGGEST() { return 'biggest' };
		
		static #__collectionsList = null ;
		static init( list ) {
			this.#__collectionsList = list ;
			for( let id in list ) {
				const c = new TDocGenerator_DataCollection( id , list[ id ] );
			}
		}
		
		static getCollection( id ) {
			const cl = TDocGenerator_DataCollection.#__collectionsList ;
			if ( cl[ id ] && ( cl[ id ] instanceof TDocGenerator_DataCollection ) ) {
				return cl[ id ];
			} else {
				return new TDocGenerator_DataCollection( id );
			}
		}

		#__id ;
		#__elements ;
		#__name ;
		#__description ;
		#__typeData ;
		#__listSize ;

		constructor( id , def ) {
			this.#__id = id ;
			this.#__elements = [];
			
			let changed = false ;
			let needLoadList = true ;
			if ( def ) {
				this.#__name        = def.name ;
				this.#__description = def.description ;
				this.#__typeData    = def.typeData ;
				this.#__listSize    = def.listSize ;
				if ( this.listSize == TDocGenerator_DataCollection.LIST_SIZE__SMALL ) {
					this.read( def.elements );
					needLoadList = false ;
				}
			} else {
				let collectionDataXML = sendXML( '<get-collection-data collection="' + id + '" />' , false , '/doc-generator/collection-data.php' );
				
				/**
				 * @description Current Collection Data. /result
				 * @type {{listSize: string, name: string, description: string, typeData: {}}}
				 */
				
				this.#__listSize = collectionDataXML.hasAttribute( 'list-size'  ) ? collectionDataXML.getAttribute( 'list-size'  ) : TDocGenerator_DataCollection.LIST_SIZE__BIGGEST ;
				
				debugger ;
				
				for ( const ccdXcn of collectionDataXML.children ) { /** @description Current Collection Data Xml Child Node */
					switch ( ccdXcn.nodeName ) {
						case 'name' :
							this.#__name = getXMLNodeValue( ccdXcn );
							break ;
						case 'description' :
							this.#__description = getXMLNodeValue( ccdXcn );
							break;
						case 'type-data' :
							this.#__typeData = JSON.parse( getXMLNodeValue( ccdXcn ) );
							break;
					}
				}
				
				changed = true ;
			}

			if ( needLoadList ) {
				//
			}
			
			TDocGenerator_DataCollection.#__collectionsList[ id ] = this ;
			if ( changed ) {
				TDocGeneratorEngine.saveData();
			}
		}
		
		get id() {
			return this.#__id ;
		}
		
		get name() {
			return this.#__name ;
		}
		
		get description() {
			return this.#__description ;
		}
		
		get typeData() {
			return this.#__typeData ;
		}
		
		get listSize() {
			return this.#__listSize ;
		}
		
		toJSON() {
			return {
				id          : this.id ,
				name        : this.name ,
				description : this.description ,
				typeData    : this.typeData ,
				listSize    : this.listSize ,
				elements    : this.listSize == TDocGenerator_DataCollection.LIST_SIZE__SMALL ? this.#__elements : []
			};
		}
		
		read( data ) {
			const elements = this.#__elements ;
			const def = this.#__typeData ;
			for( const el of data ) {
				const v = TDGVariable.fromDef( null , def , {} );
				v.read( el );
				elements.push( v );
			}
		}
	}
	
	function mkExternalCtrl( o , d , p ) {
		let sid = prompt( 'Введите ID на источнике' );
		if ( sid ) {
			const req = {
				'address' : {
					regionId : 'b756fe6b-bbd3-44d5-9302-5bfcc740f46e'
				} ,
				IdOnSource : sid ,
				maxResultCount : 50
			};
			
			const data = sendJSONExt(
				JSON.stringify( req ) , false ,
				//'https://xn--80aafmncowhr9cp5b.xn--p1ai/api/services/app/Analogue/GetApiItems' ,
				'/doc-generator/virt-ext/GetApiItems' ,
				{ Authorization: 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IjM1NDAiLCJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoiRkJVTVUiLCJBc3BOZXQuSWRlbnRpdHkuU2VjdXJpdHlTdGFtcCI6IlUyWlRTSTU2UVhaTk5JQllQMjdVQkVNU0tNWko3VkwyIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjpbIkN1c3RvbWVyIiwiVXNlciJdLCJBcHBsaWNhdGlvbl9Vc2VyRW1haWwiOiJtdG9AdnJjc2UucnUiLCJzdWIiOiIzNTQwIiwianRpIjoiZjgzNDk1OWMtZmEyNy00ZGE4LWFlNmYtZmIyZmIwODgzNTM4IiwiaWF0IjoxNzM0MDkyNTczLCJuYmYiOjE3MzQwOTI1NzMsImV4cCI6MTc2NTYyODU3MywiaXNzIjoiVG9vbHMiLCJhdWQiOiJUb29scyJ9.BANffmO5P46yPqGPNjYkUPonR0dEqDqhB-4xEK5pGw8' }
			);
			
			if ( data && data.success && data.result ) {
				const rr = data.result ;
				if ( rr.responseInfo && rr.responseInfo.status ) {
					if ( rr.totalCount > 0 ) {
						const re = rr.items[ 0 ];
						const e = o.addElement( d );
						if ( e ) {
							e.reset();
							const ov = e.write();
							console.log( 'orig object: ' , ov );
							console.log( 'retreived object: ' , re );
							
							houseBuildingTypeMap = {
								'панельный' : 'panel'
							};
							
							ov[ '2917d021-98a2-43b4-9b96-0fc2cb4d5032' ] = re.areaTotal ;
							ov[ '0fcfb57b-2036-4593-a6c5-7e2d85516e42' ] = re.floorLocation ;
							ov[ 'c70205ac-7f02-4612-adba-d605674de8cc' ] = re.houseBuildingYear ;
							ov[ 'ca37bdd5-7964-45b4-8e03-b1967dcfafe1' ] = houseBuildingTypeMap[ re.houseBuildingType ];
							ov[ '96d564aa-a23f-4bad-8c39-6f7c3393ad05' ] = re.price ;
							ov[ 'dba144af-8c96-40cd-976f-ba03c73da808' ] = re.roomsCount ;
							ov[ 'e4f7021b-3f7e-4b12-ab23-6ae99f5c6ffe' ] = re.url ;
							ov[ '1ce0b57b-f535-48e2-a696-ebdda5e70506' ] = re.addressOrigin ;
							ov[ 'f90a8ee1-c577-46fe-8ec2-1e8708be3711' ] = ( re.toiletsSeparatedCount && re.toiletsSeparatedCount ) > 0 ? 'separate' : null ;
							/*ov[ '' ] = re. ;
							ov[ '' ] = re. ;
							ov[ '' ] = re. ;
							ov[ '' ] = re. ;*/
							
							debugger ;
							
							fetch(
								'https://api.gc-enisey.ru/screen/realty/details/' + re.id ,
								{ headers : {
									Authorization : 'Bearer 8C=U-k4ut?0EC61Bs4IT9N-Dru7d870LNOPTC8yf93FMYKnpVUI7kIcoDv9g!1czfZHSOA4Qi=IJUusM2oZ-HxNyM6d5wqLCS3ySPKT-DOSdEQWEXg=giIXUvMkErLAk68hoPnKUifnU0HN4wnELWZO3==2dOoFLC37yOlC3ePOcHZ0=K7NaTqKrcwVYOl0?zQU!P=hO8pxz0lqREDDzLgLtgbPrM7anfwP-h5K3uc7YhHAXia8PU32bhpWF5hTi'
								} }
							).then( function( x , y , z ) {
								return function( r ) {
									r.blob().then( function( a , b , c ) {
										return function( rb ) {
											const fr = new FileReader();
											fr.onloadend = function( ov , e , p ) {
												return function( evt ) {
													const data = evt.target.result ;
													ov[ '9efef702-a92a-49dc-a8e4-1f382b20d8d7' ] = [ {
														img : {
															data : data ,
															transforms : []
														}
													} ]
													
													console.log( 'new object: ' , ov );
													e.read( ov );
													e.reset();
													e.generateDom( Object.assign( {} , p , {
														level : p.level + 1
													} ) );
												}
											} ( a , b , c );
											fr.readAsDataURL( rb );
										}
									} ( x , y , z ) );
								};
							} ( ov , e , p ) );
						}
					} else {
						alert( 'Объявлений с таким "ID на источнике" нет' );
					}
				} else {
					alert( 'Запрос потерпел неудачу' );
				}
			} else {
				alert( 'Запрос не выполнен' );
			}
		}
	}
