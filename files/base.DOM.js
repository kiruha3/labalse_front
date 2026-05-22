	
	function clearTab( t , rc ) {
		if ( typeof rc === 'undefined' ) {
			rc = 0 ;
		}
	
		for( let i = t.rows.length ; i > rc ; --i ) {
			t.deleteRow( -1 );
		}
	}
	
	function toCDATA( s ) {
		return '<![CDATA[' + s.replace( ']]>' , ']]]]><![CDATA[>' ) + ']]>' ;
	}
	
	function getXMLNodeValue( n ) {
		return ( n.text || n.textContent );
	}
	
	function setText( c , t ) {
		/*if ( typeof( c.innerText ) == 'undefined' ) {
			c.textContent = t ;
		} else {
			c.innerText = t ;
		}*/
		c.textContent = t ;
	}
	
	function getText( o ) {
		if ( 'innerText' in o ) {
			return o.innerText ;
		} else {
			return o.textContent ;
		}
	}
	
	function addText( c , t ) {
		c.appendChild( document.createTextNode( t ) );
	}
	
	function selectItemByValue( s , v ) {
		const so = s.options ;
		const sol = so.length ;
		for( let i = 0 ; i < sol ; i++ ) {
			if ( so[ i ].value == v ) {
				s.selectedIndex = i ;
				break ;
			}
		}
	}
	
	function mk_makeElement( makeElement_localPrefix ) {
		return function() {
			let al = arguments.length ;
			while ( al > 0 && isUndefined( arguments[ al - 1 ] ) ) {
				al-- ;
			}
	
			let className ;
			let el ;
			if ( al === 0 ) {
				className = null ;
				el = 'div' ;
			} else
			if ( al === 1 ) {
				className = makeElement_localPrefix + arguments[ 0 ];
				el = 'div' ;
			} else {
				className = makeElement_localPrefix + arguments[ 1 ];
				el = arguments[ 0 ];
			}
	
			let id = null ;
			if ( al >= 3 ) {
				id = arguments[ 2 ];
			}
	
			/*let opt = {};
			if ( al >= 4 ) {
				opt = arguments[ 3 ];
			}*/
	
			const tmp = document.createElement( el );
			if ( className != null ) {
				tmp.className = className ;
			}
	
			if ( id != null ) {
				tmp.id = id ;
			}
	
			return tmp ;
		}
	}
	
	function mkRadio( name , checked ) {
		const radioInput = document.createElement( 'input' );
		radioInput.type = 'radio' ;
		radioInput.name = name ;
		radioInput.checked = checked == true ;
		return radioInput ;
	}
