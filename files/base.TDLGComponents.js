
	function getCoords( elem ) {
		let box = elem.getBoundingClientRect();

		return {
			left  : box.x  + window.scrollX ,
			top   : box.y + window.scrollY
		};
	}

	function UCFirst( s ) {
		return s.charAt( 0 ).toUpperCase() + s.slice( 1 );
	}

	class TDLGComponent {

		static get DIRECTION_LEFT_RIGHT() { return 'left-right' ; }
		static get DIRECTION_RIGHT_LEFT() { return 'right-left' ; }
		static get DIRECTION_TOP_BOTTOM() { return 'top-bottom' ; }
		static get DIRECTION_BOTTOM_TOP() { return 'bottom-top' ; }

		#__autosize ;
		// get , set

		#__size = {
			width : 0 ,
			//get , set
			height : 0 ,
			//get , set
			minWidth : 0 ,
			//get , set
			minHeight : 0 ,
			//get , set
			maxWidth : 0 ,
			//get , set
			maxHeight : 0
			//get , set
		};
		#__onResize ;
		//get , set

		#__left ;
		//get , set
		#__top ;
		//get , set

		#__visibile ;
		//get , set
		#__onShow ;
		//get , set
		#__onHide ;
		//get , set
		//show();
		//hide();

		#__flowDirection ;
		//get , set


		#__componentAreaName ;

		dom ;

		/**
		 * @description can - componentArea Name , cas - componentArea Style (class name)
		 */
		constructor( can , cas ) {
			this.#__autosize = false ;

			this.#__componentAreaName = can ;
			this.dom = {};

			const area = document.createElement( 'div' );
			area.classList.add( cas );
			this.dom[ can ] = area ;
		}

		/* *
		 * 		SIZE & POSITION
		 * */

		get autosize() {
			return this.#__autosize ;
		}
		set autosize( v ) {
			this.#__autosize = v ;
			const can = this.#__componentAreaName ;
			this.dom[ can ].style.width = '' ;
			this.dom[ can ].style.height = '' ;
			this.dom[ can ].classList.toggle( 'autosize' );
		}

		#____setSize( size , v ) {
			const smiv = 'min' + UCFirst( size );
			const smav = 'max' + UCFirst( size );
			const ov = this.#__size[ size ];
			if ( v < 0 ) {
				v = 0
			}
			if ( this.#__size[ smiv ] && v < this.#__size[ smiv ] ) {
				v = this.#__size[ smiv ];
			}
			if ( this.#__size[ smav ] && v > this.#__size[ smav ] ) {
				v = this.#__size[ smav ];
			}
			this.#__size[ size ] = v ;
			if ( !this.#__autosize ) {
				const can = this.#__componentAreaName ;
				this.dom[ can ].style[ size ] = v + 'px' ;
			}
			if ( ov != v && this.#__onResize ) {
				this.#__onResize();
			}
		}

		#____setMinSize( size , v ) {
			if ( v < 0 ) {
				return ;
			}

			const smiv = 'min' + UCFirst( size );
			const smav = 'max' + UCFirst( size );
			const ov = this.#__size[ size ];
			const can = this.#__componentAreaName ;
			if ( this.#__size[ smav ] && v > this.#__size[ smav ] ) {
				v = this.#__size[ smav ];
			}
			this.#__size[ smiv ] = v ;
			this.dom[ can ].style[ smiv ] = v + 'px' ;
			if ( ov < v ) {
				this[ size ] = v ;
			}
		}

		#____setMaxSize( size , v ) {
			if ( v < 0 ) {
				return ;
			}

			const smiv = 'min' + UCFirst( size );
			const smav = 'max' + UCFirst( size );
			const ov = this.#__size[ size ];
			const can = this.#__componentAreaName ;
			if ( this.#__size[ smiv ] && v < this.#__size[ smiv ] ) {
				v = this.#__size[ smiv ];
			}
			this.#__size[ smav ] = v ;
			this.dom[ can ].style[ smav ] = v + 'px' ;
			if ( ov > v ) {
				this[ size ] = v ;
			}
		}

		get width() {
			return this.#__size.width ;
		}
		set width( v ) {
			this.#____setSize( 'width' , v );
		}

		get height() {
			return this.#__size.height ;
		}
		set height( v ) {
			this.#____setSize( 'height' , v );
		}

		get minWidth() {
			return this.#__size.minWidth ;
		}
		set minWidth( v ) {
			this.#____setMinSize( 'width' , v );
		}

		get minHeight() {
			return this.#__size.minHeight ;
		}
		set minHeight( v ) {
			this.#____setMinSize( 'height' , v );
		}

		get maxWidth() {
			return this.#__size.maxWidth ;
		}
		set maxWidth( v ) {
			this.#____setMaxSize( 'width' , v );
		}

		get maxHeight() {
			return this.#__size.maxHeight ;
		}
		set maxHeight( v ) {
			this.#____setMaxSize( 'height' , v );
		}

		get onResize() {
			return this.#__onResize ;
		}
		set onResize( v ) {
			this.#__onResize = v ;
		}


		get left() {
			return this.#__left ;
		}
		set left( v ) {
			this.#__left = v ;
			const can = this.#__componentAreaName ;
			this.dom[ can ].style.left = v + 'px' ;
		}

		get top() {
			return this.#__top ;
		}
		set top( v ) {
			this.#__top = v ;
			const can = this.#__componentAreaName ;
			this.dom[ can ].style.top = v + 'px' ;
		}

		get visible() {
			return this.#__visibile ;
		}

		set visible( v ) {
			if ( v ) {
				this.show();
			} else {
				this.hide();
			}
		}

		get onShow() {
			return this.#__onShow ;
		}

		set onShow( v ) {
			this.#__onShow = v ;
		}

		get onHide() {
			return this.#__onHide ;
		}

		set onHide( v ) {
			this.#__onHide = v ;
		}

		show() {
			if ( this.#__onShow ) {
				let res = this.#__onShow();
				if ( !res ) {
					return ;
				}
			}
			this.#__visibile = true ;

			const can = this.#__componentAreaName ;
			this.dom[ can ].dataset.visible = '1' ;
		}

		hide() {
			if ( this.#__onHide ) {
				let res = this.#__onHide();
				if ( !res ) {
					return ;
				}
			}
			this.#__visibile = false ;
			const can = this.#__componentAreaName ;
			this.dom[ can ].dataset.visible = '0' ;
		}

		get flowDirection() {
			return this.#__flowDirection ;
		}

		set flowDirection( v ) {
			this.#__flowDirection = v ;
			const can = this.#__componentAreaName ;
			this.dom[ can ].dataset.flowDirection = v ;
		}
	}

	class TDLGCustomButton {

	}

	class TDLGFormCaption {
		#__parent ;
		#__text ;
		#__dom ;
		constructor( Form ) {
			this.#__parent = Form ;
			this.#__text = '' ;



			let dom = this.#__dom = {};

			let mainArea = dom.mainArea = document.createElement( 'div' );
			mainArea.className = 'std-form-caption--main-area' ;

			let titleArea = dom.titleArea = document.createElement( 'div' );
			titleArea.className = 'std-form-caption--title-area' ;
			dom.titleText = document.createTextNode( '' );
			titleArea.appendChild( dom.titleText );
			mainArea.appendChild( titleArea );

			let btnArea = dom.buttonsArea = document.createElement( 'div' );
			btnArea.className = 'std-form-caption--buttons-area' ;
			mainArea.appendChild( btnArea );

			let buttonClose = dom.buttonClose = document.createElement( 'div' );
			buttonClose.className = 'std-form-caption--button-close' ;
			btnArea.appendChild( buttonClose );
			buttonClose.onclick = function( o ) {
				return function () {
					o.close();
				};
			}( Form );

			titleArea.onmousedown = function( o ) {
				return function( e ) {
					o.startMove( e );
				};
			}( this );


			Form.dom.dlgArea.appendChild( mainArea );
		}

		get parent() {
			return this.#__parent ;
		}

		get text() {
			return this.#__text ;
		}

		set text( v ) {
			this.#__text = v ;
			let newText = document.createTextNode( v );
			let dom = this.#__dom ;
			dom.titleArea.replaceChild( newText , dom.titleText );
			dom.titleText = newText ;
		}

		startMove( e ) {
			let dd = {
				c : this ,
				dm : this.#__dom.mainArea
			};

			let f = dd.c.parent ;
			let vf = dd.vf = document.createElement( 'div' );
			vf.className = 'std-form----drag-mode--vf' ;
			document.body.appendChild( vf );
			for( const s of 'left,top,width,height'.split( ',' ) ) {
				vf.style[ s ] = f[ s ] + 'px' ;
			}

			dd.dx = e.pageX - f.left ;
			dd.dy = e.pageY - f.top ;

			dd.ta = document.createElement( 'div' );
			let ta = dd.ta ;
			ta.className = 'std-form----drag-mode--ta' ;
			ta.style.cursor = 'move' ;
			document.body.appendChild( ta );

			ta.onmousemove = function ( dd ) {
				return function( e ) {
					dd.c.processMove( e , dd );
				};
			}( dd );
			ta.onmouseup = function ( dd ) {
				return function( e ) {
					dd.c.stopMove( e , dd );
				};
			}( dd );
			e.preventDefault();
		}

		processMove( e , dd ) {
			const vf = dd.vf ;
			let tmpX = e.pageX - dd.dx ;
			let tmpY = e.pageY - dd.dy ;

			vf.style.left = tmpX + 'px' ;
			vf.style.top  = tmpY + 'px' ;

			e.preventDefault();
		}


		stopMove( e , dd ) {
			//return ;
			let f = dd.c.parent ;
			let tmpX = e.pageX - dd.dx ;
			let tmpY = e.pageY - dd.dy ;
			f.left = tmpX ;
			f.top  = tmpY ;

			document.body.removeChild( dd.vf );
			document.body.removeChild( dd.ta );

			e.preventDefault();
		}
	}

	class TDLGFormBorder {
		static get BORDER_LEFT()         { return 'left'         ; }
		static get BORDER_RIGHT()        { return 'right'        ; }
		static get BORDER_TOP()          { return 'top'          ; }
		static get BORDER_BOTTOM()       { return 'bottom'       ; }
		static get BORDER_LEFT_TOP()     { return 'left-top'     ; }
		static get BORDER_LEFT_BOTTOM()  { return 'left-bottom'  ; }
		static get BORDER_RIGHT_TOP()    { return 'right-top'    ; }
		static get BORDER_RIGHT_BOTTOM() { return 'right-bottom' ; }

		static get BORDERS_ALL() { return [
			this.BORDER_LEFT ,
			this.BORDER_RIGHT ,
			this.BORDER_TOP ,
			this.BORDER_BOTTOM ,
			this.BORDER_LEFT_TOP ,
			this.BORDER_LEFT_BOTTOM ,
			this.BORDER_RIGHT_TOP ,
			this.BORDER_RIGHT_BOTTOM
		]; }

		#__parent ;
		#__dom ;
		#__borderName ;

		constructor( Form , borderName ) {
			this.#__parent = Form ;
			this.#__borderName = borderName ;

			let dom = {};
			let main = dom.main = document.createElement( 'div' );
			main.classList.add( 'std-form--border' );
			main.classList.add( borderName );

			this.#__dom = dom ;

			main.onmousedown = function ( o ) {
				return function( e ) {
					o.startResize( e );
				};
			}( this );

			Form.dom.dlgArea.appendChild( main );
		}

		get parent() {
			return this.#__parent ;
		}

		get name() {
			return this.#__borderName ;
		}

		startResize( e ) {
			/**
			 * drag data
			 */
			let dd = {
				b : this ,
				dm : this.#__dom.main ,
				map : {}
			};

			for( const cbn of TDLGFormBorder.BORDERS_ALL ) {
				dd.map[ cbn ] = [];
				if ( cbn.match( /left|right/ ) ) {
					dd.map[ cbn ].push( { c : 'x' , side : 'left' , size : 'width' , p : cbn.match( /left/ ) ? 1 : 0 } );
				}
				if ( cbn.match( /top|bottom/ ) ) {
					dd.map[ cbn ].push( { c : 'y' , side : 'top' , size : 'height' , p : cbn.match( /top/ ) ? 1 : 0 } );
				}
			}

			const p = dd.map[ dd.b.name ];
			for( const cp of p ) {
				let C = cp.c.toUpperCase();
				dd[ 'm' + cp.c ] = e[ 'page' + C ];
			}

			/** parent form */
			let f = dd.b.parent ;
			
			/** visible frame */
			let vf = dd.vf = document.createElement( 'div' );
			vf.className = 'std-form----drag-mode--vf';
			document.body.appendChild( vf );
			for( const s of [ 'left' , 'top' , 'width' , 'height' ] ) {
				vf.style[ s ] = f[ s ] + 'px' ;
			}
			
			/** top area */
			let ta = dd.ta = document.createElement( 'div' );
			ta.className = 'std-form----drag-mode--ta' ;
			ta.style.cursor = window.getComputedStyle( dd.dm ).cursor ;
			document.body.appendChild( ta );

			ta.onmousemove = function ( dd ) {
				return function( e ) {
					dd.b.processResize( e , dd );
				};
			}( dd );
			ta.onmouseup = function ( dd ) {
				return function( e ) {
					dd.b.stopResize( e , dd );
				};
			}( dd );
			e.preventDefault();
		}

		processResize( e , dd ) {
			let f = dd.b.parent;
			let vf = dd.vf;
			const p = dd.map[ dd.b.name ];
			for( const cp of p ) {
				let c = cp.c;
				let C = c.toUpperCase();

				let tmp = e[ 'page' + C ] - dd[ 'm' + c ];
				if ( cp.p ) {
					vf.style[ cp.side ] = ( f[ cp.side ] + tmp ) + 'px';
					vf.style[ cp.size ] = ( f[ cp.size ] - tmp ) + 'px';
				} else {
					vf.style[ cp.size ] = ( f[ cp.size ] + tmp ) + 'px';
				}
			}

			e.preventDefault();
		}


		stopResize( e , dd ) {
			let f = dd.b.parent ;
			const p = dd.map[ dd.b.name ];
			let nf = {};
			for( const cp of p ) {
				let c = cp.c ;
				let C = c.toUpperCase();

				let tmp =  e[ 'page' + C ] - dd[ 'm' + c ];
				if ( cp.p ) {
					nf[ cp.side ] = f[ cp.side ] + tmp ;
					nf[ cp.size ] = f[ cp.size ] - tmp ;
				} else {
					nf[ cp.size ] = f[ cp.size ] + tmp ;
				}
			}
			Object.assign( f , nf );

			document.body.removeChild( dd.vf );
			document.body.removeChild( dd.ta );

			e.preventDefault();
		}
	}

	class TDLGForm extends TDLGComponent {
		static get NEW_FORM_PARAMS() { return {
			caption : 'Form 1' ,
			width : 640 ,
			height : 480
		}; }

		#__formData ;
		//caption ;
		#__childs ;


		elements ;

		#__onClose ;

		constructor( formData ) {
			super( 'dlgArea' , 'std-form--dlg-area' );

			let privateDataArea = {
				form : this
			};
			this.#__childs = [];

			let dlgArea = this.dom[ 'dlgArea' ];

			this.elements = {
				caption : new TDLGFormCaption( this ) ,
			};

			const clientAreaWrapper = this.dom.clientAreaWrapper = document.createElement( 'div' );
			clientAreaWrapper.className = 'std-form--client-area-wrapper' ;
			dlgArea.appendChild( clientAreaWrapper );

			const clientArea = this.dom.clientArea = document.createElement( 'div' );
			clientArea.className = 'std-form--client-area' ;
			clientAreaWrapper.appendChild( clientArea );

			this.#__makeBorders();

			document.body.appendChild( dlgArea );

			Object.assign( this , TDLGForm.NEW_FORM_PARAMS );
			if ( formData && formData instanceof TDLGFormData ) {
				this.#__readFormData( formData );
			}
		}

		#__makeBorders() {
			let borders = {};
			for( const cb of TDLGFormBorder.BORDERS_ALL ) {
				borders[ cb ] = new TDLGFormBorder( this , cb );
			}

			this.elements.borders = borders ;
		}

		#__readFormData( formData ) {
			const dd = formData.direct ;
			Object.assign( this , dd );
		}

		get caption() {
			return this.elements.caption.text ;
		}

		set caption( v ) {
			this.elements.caption.text = v ;
		}

		get childs() {
			return this.#__childs ;
		}



		close() {
			if ( typeof this.#__onClose === 'function' ) {
				let result = this.#__onClose();
				if ( !result ) {
					return ;
				}
			}
			this.hide();
		}

		show() {
			super.show();
			let ax = window.innerWidth - this.width ;
			let ay = window.innerHeight - this.height ;
			this.left = ( ax - ( ax % 2 ) ) / 2 ;
			this.top  = ( ay - ( ay % 2 ) ) / 2 ;
		}

		get onClose() {
			return this.#__onClose ;
		}

		set onCLose( v ) {
			this.#__onClose = v ;
		}

		destroy() {
			let dlgArea = this.dom[ 'dlgArea' ];
			document.body.removeChild( dlgArea );
		}
	}

	class TDLGFormData {
		/*
		 */
		static get FIELD_CAPTION        () { return 'caption'    ; }
		static get FIELD_WIDTH          () { return 'width'     ; }
		static get FIELD_HEIGHT         () { return 'height'    ; }
		static get FIELD_MAX_WIDTH      () { return 'maxWidth'  ; }
		static get FIELD_MAX_HEIGHT     () { return 'maxHeight' ; }
		static get FIELD_FLOW_DIRECTION () { return 'flowDirection' ; }
		static get #__PL_DIRECT() { return [].concat(
			this.FIELD_CAPTION ,
			this.FIELD_WIDTH , this.FIELD_HEIGHT ,
			this.FIELD_MAX_WIDTH , this.FIELD_MAX_HEIGHT ,
			this.FIELD_FLOW_DIRECTION
		); }
		#__definition ;
		#__direct ;

		constructor( definition , defaults , exData ) {
			const def = this.#__definition = Object.assign( {} , definition );

			const plDirect = TDLGFormData.#__PL_DIRECT ;
			const pDirect = this.#__direct = {};
			for( let p of plDirect ) {
				if ( defaults[ p ] ) {
					pDirect[ p ] = defaults[ p ];
				}
			}
			for( let p in def ) {
				if ( plDirect.indexOf( p ) > -1 ) {
					pDirect[ p ] = def[ p ];
				}
			}
		}

		get direct() {
			return this.#__direct ;
		}

	}


