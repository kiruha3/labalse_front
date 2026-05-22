
	class TBCESlidingPanel extends HTMLElement {
		static #__template = false ;
		static get TEMPLATE() {
			if ( !this.#__template ) {
				this.#__template = document.getElementById( 'template--SlidingPanel' );
			}
			return this.#__template ;
		}
		
		static get SIDE_TOP()    { return 'top'    ; }
		static get SIDE_RIGHT()  { return 'right'  ; }
		static get SIDE_BOTTOM() { return 'bottom' ; }
		static get SIDE_LEFT()   { return 'left'   ; }
	
		#__dom ;
		#__shadowRoot ;
		#__created ;
		#__caption ;
		#__side ;
		#__internals ;
		
		#__contentOldWidth ;
		#__contentOldHeight ;
		
		constructor() {
			super();
			
			this.#__dom = {};
			this.#__shadowRoot = false ;
			this.#__created = false ;
			this.#__caption = '' ;
			this.#__side = TBCESlidingPanel.SIDE_LEFT ;
			this.#__internals = false ;
		}
		
		
		get caption() {
			return this.#__caption ;
		}
		
		set caption( newValue ) {
			const oldValue = this.#__caption ;
			this.#__caption = newValue ;
			
			if ( oldValue != newValue && this.#__created ) {
				setText( this.#__dom.label , newValue );
			}
		}
		
		get side() {
			return this.#__side ;
		}
		
		set side( newValue ) {
			const oldValue = this.#__side ;
			const allSides = {
				[TBCESlidingPanel.SIDE_TOP] : 1 ,
				[TBCESlidingPanel.SIDE_RIGHT] : 1 ,
				[TBCESlidingPanel.SIDE_BOTTOM] : 1 ,
				[TBCESlidingPanel.SIDE_LEFT] : 1
			};
			if ( !allSides[ newValue ] ) {
				return ;
			}
			
			if ( oldValue != newValue ) {
				this.#__side = newValue ;
			}
		}
		
		get opened() {
			//return this.#__internals.states.has( '--opened' );
			return this.dataset.opened === 'opened' ;
		}
		
		set opened( flag ) {
			if ( flag ) {
				//this.#__internals.states.add( '--opened' );
				this.dataset.opened = 'opened' ;
			} else {
				//this.#__internals.states.delete( '--opened' );
				this.dataset.opened = 'closed' ;
			}
		}
		
		render() {
			const dom = this.#__dom ;
			
			if ( !this.#__shadowRoot ) {
				this.#__shadowRoot = this.attachShadow( { mode : 'closed' } );
			}
			const shadowRoot = this.#__shadowRoot ;
			
			if ( !this.#__created ) {
				this.caption = this.getAttribute( 'caption' );
				this.side = this.getAttribute( 'side' );
				shadowRoot.appendChild( TBCESlidingPanel.TEMPLATE.content.cloneNode( true ) );
				this.#__internals = this.attachInternals();
				
				dom.label = shadowRoot.querySelector( '#label' );
				dom.label.addEventListener( 'click' , this.__onClick.bind( this ) );

				const label = dom.label ;
				setText( label , this.caption );
				
				dom.area = shadowRoot.querySelector( '#area' );
				
				dom.scrollerShr = shadowRoot.querySelector( '#scroller-shr' );
				this.__setScrollInitial( dom.scrollerShr );
				dom.scrollerShr.addEventListener( 'scroll' , this.__onContentResize.bind( this ) );
				
				dom.scrollerExp = shadowRoot.querySelector( '#scroller-exp' );
				this.__setScrollInitial( dom.scrollerExp );
				dom.scrollerExp.addEventListener( 'scroll' , this.__onContentResize.bind( this ) );
				
				dom.variables = shadowRoot.querySelector( '#variables' );
				
				this.#__created = true ;
			}
		}
		
		connectedCallback() {
			if ( !this.rendered ) {
				this.render();
				this.rendered = true ;
			}
		}
		
		disconnectedCallback() {
		}
		
		static get observedAttributes() {
			return [ 'caption' , 'side' ];
		}
		
		attributeChangedCallback( name , oldValue , newValue ) {
			switch ( name ) {
				case 'caption' :
					this.caption = newValue ;
					break ;
				case 'side' :
					this.side = newValue ;
					break ;
			}
		}
		
		adoptedCallback() {
		}
		
		__onClick ( event ) {
			this.opened = !this.opened ;
		}
		
		__onContentResize() {
			if ( !this.#__dom ) {
				return ;
			}
			
			const dom = this.#__dom ;
			if ( dom.area && dom.variables ) {
				dom.variables.innerHTML = ':host {' +
					'--client-width : ' + dom.area.clientWidth + 'px ;' +
					'transition : all 0.5s ;' +
				'}' ;
			}
			
			this.__setScrollInitial( dom.scrollerShr );
			this.__setScrollInitial( dom.scrollerExp );
		}
		
		__setScrollInitial( e ) {
			if ( e ) {
				e.scrollTop = 10000000 ;
				e.scrollLeft = 10000000 ;
			}
		}
	}
	
	customElements.define( 'vrcse-sliding-panel' , TBCESlidingPanel );