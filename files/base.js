
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	function generatePwd( model , l , p ) {
		const alf = {
			ll : 'abcdefghijklmnopqrstuvwxyz' ,
			lh : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' ,
			d : '0123456789' ,
			s1 : '_-.*=' ,
			s2 : '</>@#$%^&'
		};
		
		if ( p ) {
			alf.p = p ;
		}
		
		const models = {
			login : [
				{ a : 'll' , f : 2 } ,
				{ a : 'lh' , f : 3 } ,
				{ a : 'd'  , f : 3 } ,
			] ,
			
			passS1 : [
				{ a : 'll' , f : 2 } ,
				{ a : 'lh' , f : 2 } ,
				{ a : 'd'  , f : 2 } ,
				{ a : 's1' , f : 2 }
			] ,
			passS2 : [
				{ a : 'll' , f : 2 } ,
				{ a : 'lh' , f : 2 } ,
				{ a : 'd'  , f : 2 } ,
				{ a : 's1' , f : 1 } ,
				{ a : 's2' , f : 1 }
			] ,
			passP : [
				{ a : 'll' , f : 2 } ,
				{ a : 'lh' , f : 2 } ,
				{ a : 'd'  , f : 2 } ,
				{ a : 'p' , f : 2 }
				] ,
			passPF : [
				{ a : 'll' , f : 2 } ,
				{ a : 'lh' , f : 2 } ,
				{ a : 'd'  , f : 2 } ,
				{ a : 'p' , f : 5 }
			]
		};
		
		const cm = models[ model ];
		let cs = [];
		
		if ( !l ) {
			l = Math.floor( Math.random( ) * 8 ) + 8 ; 
		}
		
		let RES = '' ;
		for ( let i = 0 ; i < l ; i++ ) {
			if ( cs.length === 0 ) {
				cs = Object.assign( [] , cm );
				for ( let j = 0 ; j < cs.length ; j++ ) {
					cs[ j ].cf = 0 ;
				}
			}
			
			const ind = Math.floor( Math.random( ) * cs.length );
			const can = cs[ ind ].a ;
			const ca = alf[ can ];
			
			const ci = Math.floor( Math.random( ) * ca.length );
			
			RES = RES + ca.charAt( ci );
			
			cs[ ind ].cf++ ;
			if ( cs[ ind ].cf === cs[ ind ].f ) {
				cs[ ind ] = cs[ cs.length - 1 ];
				cs.pop();
			}
		}
		return RES ;
	}

	
	function fixedEncodeURIComponent( str ) {
		return encodeURIComponent( str ).replace( /[!'()*]/g , function( c ) {
			return '%' + c.charCodeAt( 0 ).toString( 16 );
		} );
	}


	function isUndefined( o ) {
		return typeof ( o ) === 'undefined' ;
	}
	
	function isArray( o ) {
		return Object.prototype.toString.call( o ) === '[object Array]' ;
	}
	
	function isValidDate( d ) {
		d = ( '' + d ).replace( /[,.]/ , '-' ).trim();
		if ( d.match( /^\d{2}-\d{2}-\d{2}(\d{2})?$/ ) ) {
			d = d.split( '-' );
			let y = parseInt( d[ 2 ] , 10 );
			let m = parseInt( d[ 1 ] , 10 );
			d = parseInt( d[ 0 ] , 10 );
			if ( y < 100 ) {
				y+= 2000 ;
			}
			if ( m < 1 || m > 12 ) {
				return false ;
			}
			
			const dc = ( new Date( y , m , 0 ) ).getDate();
			if ( d < 1 || d > dc ) {
				return false ;
			}
		} else {
			return false ;
		}
		
		return true ;
	}
	
	function getDateWOTime( value ) {
		let tmp ;
		if ( value instanceof Date ) {
			tmp = value.getTime();
		} else {
			tmp = value ;
		}
		
		return new Date( tmp - ( ( tmp + $.serverTimezoneOffset * 1000 ) % ( 86400 * 1000 ) ) );
	}

	function unitConvert( src , tu ) {
		const m = src.trim().match( /^(-?\d+(?:\.\d+)?)(mm|cm|in|pt|tw|px)$/ );
		if ( !m ) {
			return false ;
		}
		
		const cvt = {
			mm : { mm :         1.0 , cm :         0.1 , in :   1.0/25.4 , pt : 72.0/25.4 , tw : 1440.0/25.4 , px :   96.0/25.4 },
			cm : { mm :        10.0 , cm :         1.0 , in :   1.0/2.54 , pt : 72.0/2.54 , tw : 1440.0/2.54 , px :   96.0/2.54 },
			in : { mm :        25.4 , cm :        2.54 , in :        1.0 , pt :      72.0 , tw :      1440.0 , px :        96.0 },
			pt : { mm :   25.4/72.0 , cm :   2.54/72.0 , in :   1.0/72.0 , pt :       1.0 , tw :        20.0 , px :   96.0/72.0 },
			tw : { mm : 25.4/1440.0 , cm : 2.54/1440.0 , in : 1.0/1440.0 , pt :  1.0/20.0 , tw :         1.0 , px : 96.0/1440.0 },
			px : { mm :   25.4/96.0 , cm :   2.54/96.0 , in :   1.0/96.0 , pt : 72.0/96.0 , tw : 1440.0/96.0 , px :         1.0 }
		};
		
		if ( !cvt[ tu ] ) {
			return false ;
		}
		
		if ( !cvt[ m[ 2 ] ] ) {
			return false ;
		}
		
		return parseFloat( m[ 1 ] ) * cvt[ m[ 2 ] ][ tu ];
	}
	
	function readCssNumberProp( cssVal , tgtUnit , defVal ) {
		let result = cssVal.trim();
		result = unitConvert( result , tgtUnit );
		if ( result !== false ) {
			result = Math.round( result );
		} else {
			result = defVal ;
		}
		return result ;
	}
	
	
	
	function remap( a , k ) {
		const res = {};
		const al = a.length ;
		for( let i = al - 1 ; i >= 0 ; --i ) {
			const ai = a[ i ];
			const ak = ai[ k ];
			res[ ak ] = ai ;
		}
		return res ;
	}
	
	function generateGUID() {
		if ( !crypto || !crypto.randomUUID ) {
			return 'xxxxxxxx_xxxx_4xxx_yxxx_xxxxxxxxxxxx'.replace( /[xy]/g , function( c ) {
				const r = Math.random() * 16 | 0 ;
				const v = c === 'x' ? r : ( r & 0x3 | 0x8 );
				return v.toString( 16 );
			} );
		} else {
			return crypto.randomUUID();
		}
	}

	function setCookie( n , v , t ) {
		let ed = '' ;
		if ( t ) {
			ed = new Date();
			ed.setDate( ed.getDate() + t );
			ed = '; expires=' + ed.toUTCString();
		}
		const c_value= escape( v ) + ed + '; path=/' ;
		document.cookie = n + '=' + c_value ;
	}

	function getCookie( n ) {
		const ca = document.cookie.split( '; ' );
		for( let i = 0 ; i < ca.length ; i++ ) {
			const ep = ca[ i ].indexOf( '=' );
			if ( ca[ i ].substr( 0 , ep ) === n ) {
				return unescape( ca[ i ].substr( ep + 1 ) );
			}
		}

		return null ;
	}
	
	function functionByName( n , c ) {
		const p = [].slice.call( arguments ).splice( 2 );
		const ns = n.split( '.' );
		const fn = ns.pop();
		for( let i = 0 ; i < ns.length ; i++ ) {
			c = c[ ns[ i ] ];
		}
		
		return c[ fn ].apply( this , p );
	}

	function getXHR() {
		let req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( 'Microsoft.XMLHTTP' );
		}
		return req ;
	}
	
	function sendGET( async , addr , dbg , callback ) {
		if ( !addr ) {
			addr = location.protocol + '//' + location.host + location.pathname ;
		}
		
		if ( isUndefined( callback ) ) {
			callback = function() {};
		}
		
		const req = getXHR();
		
		if ( typeof async === 'undefined' ) {
			async = false ;
		}
		
		if ( req ) {
			req.open( 'GET' , addr , async );
			if ( async ) {
				req.onreadystatechange = function( x , y ) {
					return function() {
						if ( x.readyState !== 4 ) {
							return ;
						}
						
						if ( x.status !== 200 ) {
						} else {
							y( x );
						}
					}
				}( req , callback );
			}
			req.send();
			
			if ( !async ) {
				if ( dbg ) {
					alert( req.responseText );
				}
				return req.responseText ;
			} else {
				return ;
			}
		}
	}
	
	function sendXML( data , async , addr , aParam , dbg , callback ) {
		if ( !addr ) {
			addr = location.protocol + '//' + location.host + location.pathname ;
		}

		if ( typeof aParam === 'undefined' ) {
			aParam = '' ;
		} else {
			aParam+= '&' ;
		}
		
		if ( isUndefined( callback ) ) {
			callback = function() {};
		}

		const sd = aParam + 'random=' + ( new Date() ).getTime() + ( Math.random() * 1000 ) + '&mode=ajax&data=' + encodeURIComponent( '<?xml version="1.0" encoding="utf-8" ?>' + data );
		const req = getXHR();

		if ( typeof async === 'undefined' ) {
			async = false ;
		}

		if ( req ) {
			req.open( 'POST' , addr , async );
			req.setRequestHeader( 'Content-type' , 'application/x-www-form-urlencoded' );
			req.setRequestHeader( 'Content-Encoding' , 'utf-8' );
			if ( async ) {
				req.onreadystatechange = function( x , y ) {
					return function() {
						if ( x.readyState !== 4 ) {
							return ;
						}
						
						if ( x.status !== 200 ) {
						} else {
							y( x );
						}
					}
				}( req , callback );
			}
			req.send( sd );

			if ( !async ) {
				if ( dbg ) {
					alert( req.responseText );
				}
				return req.responseXML.documentElement ;
			} else {
				return ;
			}
		}
	}
	
	function sendJSONExt( data , async , addr , headers , dbg , callback ) {
		if ( !addr ) {
			addr = location.protocol + '//' + location.host + location.pathname ;
		}

		if ( isUndefined( callback ) ) {
			callback = function() {};
		}

		const req = getXHR();

		if ( typeof async === 'undefined' ) {
			async = false ;
		}

		if ( req ) {
			req.open( 'POST' , addr , async );
			req.setRequestHeader( 'Content-type' , 'application/json;charset=UTF-8' );
			if ( !isUndefined( headers ) ) {
				for ( const h in headers ) {
					req.setRequestHeader( h , headers[ h ] );
				}
			}
			if ( async ) {
				req.onreadystatechange = function( x , y ) {
					return function() {
						if ( x.readyState !== 4 ) {
							return ;
						}
						
						if ( x.status !== 200 ) {
						} else {
							y( x );
						}
					};
				}( req , callback );
			}
			req.send( data );

			if ( !async ) {
				if ( dbg ) {
					alert( req.responseText );
				}
				return JSON.parse( req.responseText );
			} else {
				return ;
			}
		}
	}

	function sendDataPOST( addr , data , callback ) {
		const req = getXHR();

		let fd = new FormData();
		fd.append( 'data' , JSON.stringify( data ) );

		const async = !!callback ;

		if ( req ) {
			req.open( 'POST' , addr , async );
			if ( async ) {
				req.onreadystatechange = function( x , y ) {
					return function() {
						if ( x.readyState !== 4 ) {
							return ;
						}

						if ( x.status !== 200 ) {
						} else {
							y( x );
						}
					};
				}( req , callback );
			}
			req.send( fd );

			if ( !async ) {
				return req.responseXML ;
			} else {
				return ;
			}
		}
	}
	
	// event.type должен быть keypress
	function getChar( event ) {
		if ( event.which == null ) {  // IE
			if ( event.keyCode < 32 ) {
				return null ; // спец. символ
			}
			return String.fromCharCode( event.keyCode );
		}

		if ( event.which !== 0 && event.charCode !== 0 ) { // все кроме IE
			if ( event.which < 32 ) {
				return null ; // спец. символ
			}
			return String.fromCharCode( event.which ); // остальные
		}

		return null ; // спец. символ
	}
	
	function inForm( src , form , singular ) {
		if ( typeof singular === 'undefined' ) {
			singular = true ;
		}
		if ( typeof form === 'undefined' ) {
			form = 1 ;
		}
		if ( !singular ) {
			form+= 6 ;
		}
		
		let re = '([^^|}]*)\\|' ;
		re = str_pad( '' , re.length * 6 - 2 , re );
		const repl = '$' + form ;
		src = src.replace( new RegExp( '\\{' + re + '\\}' , 'ig' ) , repl );
		src = src.replace( new RegExp( '\\{' + re + '\\^' + re + '\\}' , 'ig' ) , repl );
		
		return src ;
	}
	
	const STR_PAD_LEFT = 1 ;
	const STR_PAD_RIGHT = 2 ;
	const STR_PAD_BOTH = 3 ;

	function str_pad( str , len , pad , dir ) {
		if ( typeof( len ) == 'undefined' ) {
			len = 0 ;
		}
		if ( typeof( pad ) == 'undefined' ) {
			pad = ' ' ;
		} else {
			pad += '' ;
		}
		if ( typeof( dir ) == 'undefined' ) {
			dir = STR_PAD_RIGHT ;
		}
		
		if ( len > ( '' + str ).length ) {
			switch ( dir ) {
				case STR_PAD_LEFT :
					while ( pad.length < len ) {
						pad += pad ;
					}
					str = ( pad + str ).substr( -len );
					break ;
				case STR_PAD_BOTH :
					break ;
				
				default :
					while ( pad.length < len ) {
						pad += pad ;
					}
					str = ( pad + str ).substr( 0 , len );
					break ;
			}
		}
		
		return str ;
	}
	
	function getCharID( src , docType , region ) {
		const pDocType = typeof docType === 'undefined' || docType === false ? $.DOCTYPE_PATTERN : '' + docType ;
		const pReg = typeof region === 'undefined' || region === false ? '' + $.UserOrgIndex : ( region === '*' ? $.ORG_INDEX_PATTERN : '' + region );
		
		const pat = $.VERSION_CHAR_ID + '\.' + pReg + '\.' + pDocType + '\.' + $.OBJ_G_NUMBER_PATTERN ;
		const re = new RegExp( '/^' + pat + '$/D' );
		const n = re.test( src );
		if ( n ) {
			return src ;
		} else {
			return false ;
		}
	}

	function getCharIDStructure( src ) {
		const re = new RegExp( '^' + $.CHARID_STRUCTURE_PATTERN + '$' );
		const m = re.exec( src );
		
		if ( m !== null ) {
			return {
				v : m[ 1 ] ,
				o : m[ 2 ] ,
				t : m[ 3 ] ,
				y : m[ 4 ] ,
				n : m[ 5 ].replace( /^0+(?!\.|$)/ , '' )
			};
		} else {
			return false ;
		}
	}


	function strexp( str ) {
		let pref = '' ;
		let i = 0 ;
		let s = 'pref' ;
		const l = str.length ;
		let il = l ;
		const v = [];
		let cv = '' ;
		let bl = 0 ;
		while ( i < l && s !== 'fin' ) {
			const c = str.charAt( i );
			i++ ;
			switch ( s ) {
				case 'pref' :
					switch ( c ) {
						case '{' :
							s = 'br' ;
							cv = '' ;
							break ;

						case '\\' :
							s = 'pref.sch' ;
							break ;

						default :
							pref+= c ;
							break ;
					}
					break ;

				case 'pref.sch' :
					pref+= c ;
					s = 'pref' ;
					break ;

				case 'br' :
					switch ( c ) {
						case ',' :
							v.push( cv );
							cv = '' ;
							break ;

						case '}' :
							v.push( cv );
							il = i ;
							s = 'fin' ;
							break ;

						case '\\' :
							s = 'br.sch' ;
							break ;

						case '{' :
							cv+= c ;
							bl = 1 ;
							s = 'sbr' ;
							break ;

						default :
							cv+= c ;
							break ;
					}
					break ;

				case 'br.sch' :
					cv+= c ;
					s = 'br' ;
					break ;

				case 'sbr' :
					switch ( c ) {
						case '}' :
							cv+= c ;
							bl-- ;
							if ( bl === 0 ) {
								s = 'br' ;
							}
							break ;

						case '{' :
							cv+= c ;
							bl++ ;
							break ;

						case '\\' :
							s = 'sbr.sch' ;
							break ;

						default :
							cv+= c ;
							break ;
					}
					break ;

				case 'sbr.sch' :
					cv+= c ;
					s = 'sbr' ;
					break ;
			}
		}
		
		const suff = str.substr( il );
		let res = [];
		if ( v.length > 0 ) {
			for ( let i = 0 ; i < v.length ; i++ ) {
				res = res.concat( strexp( pref + v[ i ] + suff ) );
			}
		} else {
			res.push( pref + suff );
		}

		return res ;
	}

	function objectFillKeys( array , keys , value , copy ) {
		if ( copy ) {
			for( let i of keys ) {
				array[ i ] = Object.assign( {} , value );
			}
		} else {
			for( let i of keys ) {
				array[ i ] = value ;
			}
		}
	}

	function objectAssignKeys( a , k , v ) {
		for( let i of k ) {
			Object.assign( a[ i ] , v );
		}
	}

	function convertDataURIToBinary( dataURI ) {
		const BASE64_MARKER = ';base64,' ;
		const base64Index = dataURI.indexOf( BASE64_MARKER ) + BASE64_MARKER.length ;
		const base64 = dataURI.substring( base64Index );
		const raw = window.atob( base64 );
		const rawLength = raw.length ;
		const array = new Uint8Array( new ArrayBuffer( rawLength ) );
		
		for( let i = 0 ; i < rawLength ; i++ ) {
			array[ i ] = raw.charCodeAt( i );
		}
		
		return array ;
	}
	
	function base64DecodeStrUTF8( src ) {
		const binString = atob( src );
		return new TextDecoder().decode( Uint8Array.from( binString , ( m ) => m.codePointAt( 0 ) ) );
	}
	
	function attrToBool( x ) {
		if ( x === 'true' ) {
			return true ;
		}
		
		return typeof x === 'string' ? !!+x : !!x ;
	}

	function camelCaseToKebabCase( s ) {
		return s.replace( /([a-z])([A-Z])/g , '$1-$2' ).toLowerCase();
	}
	function kebabCaseToCamelCase( s ) {
		return s.replace( /([a-zA-Z])-([a-zA-Z])/g , function( v , m1 , m2 ) {
			return m1 + m2.toUpperCase();
		} );
	}

	function formatDate( value , format ) {
		let d ;
		if ( !( value instanceof Date ) ) {
			d = new Date( value );
		} else {
			d = value ;
		}

		if ( !format || format === '' ) {
			format = '{d}.{m}.{Y}' ;
		}

		const oneDay = 24 * 60 * 60 * 1000 ;

		const ms = d.getTime();
		const el = {
			j : d.getDate() ,
			w : d.getDay() ,
			n : d.getMonth() + 1 ,
			Y : d.getFullYear() ,
			G : d.getHours() ,
			i : d.getMinutes() ,
			s : d.getSeconds()
		};
		el.d = el.j < 10 ? '0' + el.j : el.j ;
		el.m = el.n < 10 ? '0' + el.n : el.n ;
		el.N = el.w === 0 ? 7 : el.w ;
		el.y = el.Y % 100 ;
		el.F = inForm( $.CONSTANTS.monthNames[ el.n - 1 ] , 2 );
		el.F1 = inForm( $.CONSTANTS.monthNames[ el.n - 1 ] , 1 );
		el.F2 = el.F ;
		el.l = inForm( $.CONSTANTS.daysOfWeek[ el.N ] , 1 );
		el.D = $.CONSTANTS.daysOfWeekShort[ el.N ];
		el.z = Math.floor( ( ms - ( new Date( el.Y , 0 , 1 ) ).getTime() ) / oneDay ) + 1 ;
		el.t = ( new Date( el.Y , el.n , 0 ) ).getDate();
		el.L = Math.round( ( ( new Date( el.Y + 1 , 0 , 1 ) ).getDate() - ( new Date( el.Y , 0 , 1 ) ).getDate() ) / oneDay ) === 366 ? 1 : 0 ;

		el.g = el.G < 13 ? el.G : el.G - 12 ;
		el.h = el.g < 10 ? '0' + el.g : el.g ;
		el.H = el.G < 10 ? '0' + el.G : el.G ;
		el.i = el.i < 10 ? '0' + el.i : el.i ;
		el.s = el.s < 10 ? '0' + el.s : el.s ;

		el.v = ms % 1000 ;
		el.U = ( ms - el.v ) / 1000 ;

		let result = format.replaceAll( /(?<!\\)\{([a-z]+\d*)\}/gi , function( o ) {
			return function( s , m ) {
				return m in o ? o[ m ] : s ;
			}
		} ( el ) );
		result = result.replaceAll( /(?<!\\)\\\{/gi , '{' );
		return result ;
	}


	function number_format( number , digits , fpDiv , grDiv ) {
		let tmp = 0 + number ;
		tmp = tmp.toFixed( digits );
		if ( digits != 0 ) {
			tmp = tmp.replace( /^(\d+)\D(\d+)$/ , function( r ) {
				return function( m , p1 , p2 ) {
					const p1p = p1.split( '' ).reverse().join( '' ).replaceAll( /(\d{3})(?=\d)/g , '$1{grDiv}' ).replaceAll( '{grDiv}' , grDiv ).split( '' ).reverse().join( '' );
					return p1p + r + p2 ;
				};
			} ( fpDiv ) ).replace( '{fpDiv}' , fpDiv );
		} else {
			tmp = tmp.split( '' ).reverse().join( '' ).replaceAll( /(\d{3})(?=\d)/g , '$1{grDiv}' ).replaceAll( '{grDiv}' , grDiv ).split( '' ).reverse().join( '' );
		}
		return tmp ;
	}

	function formatPrice( value , format ) {
		let res = value ;
		switch ( format ) {
			case 'price-w-text' :
				let pc = number_format( value , 2 , '.' , String.fromCharCode( 160 ) );
				res = pc.substring( 0 , pc.length - 3 ) + ' ' + ( price2word( value ) + '' ).replace( /^(.+)\s+(руб)\S+\s+(\d{2})\s+(коп)\S+$/ , '($1) $2. $3 $4.' );
				break ;
			case 'price' :
			default :
				res = number_format( value , 2 , '.' , String.fromCharCode( 160 ) );
				break ;
		}
		return res ;
	}

	function price2word( price ) {
		const kop = 'копе{йка|йки|йке|йку|йками|йке^йки|ек|йкам|йки|йками|йках}' ;
		const groups = [
			'рубл{ь|я|ю|ь|ем|е^и|ей|ям|и|ями|ях}' ,
			'тысяч{а|и|е|у|ей|е^и||ам|и|ами|ах}' ,
			'миллион{|а|у||ом|е^ы|ов|ам|ы|ами|ах}'
		];
		const num = {
			m : [ 'ноль' , 'один' , 'два' , 'три' , 'четыре' , 'пять' , 'шесть' , 'семь' , 'восемь' , 'девять' ] ,
			f : [ 'ноль' , 'одна' , 'две' , 'три' , 'четыре' , 'пять' , 'шесть' , 'семь' , 'восемь' , 'девять' ]
		};

		const num1 = [ 'десять' , 'одиннадцать' , 'двенадцать' , 'тринадцать' , 'четырнадцать' , 'пятнадцать' , 'шестнадцать' , 'семнадцать' , 'восемнадцать' , 'девятнадцать' ];
		const num2 = [ 'ноль' , 'десять' , 'двадцать' , 'тридцать' , 'сорок' , 'пятьдесят' , 'шестьдесят' , 'семьдесят' , 'восемьдесят' , 'девяносто' ];
		const num3 = [ 'ноль' , 'сто' , 'двести' , 'триста' , 'четыреста' , 'пятьсот' , 'шестьсот' , 'семьсот' , 'восемьсот' , 'девятьсот' ];

		let p = ( '' + price ).replace( '.' , ',' );
		let pos = p.indexOf( ',' );
		let tmp ;
		if ( pos > -1 ) {
			tmp = p.substring( 0 , pos );
		} else {
			tmp = p ;
		}

		let res = '' ;
		if ( parseInt( tmp , 10 ) === 0 ) {
			res = num.m[ 0 ] + ' ' + inForm( groups[ 0 ] , 2 , false ) + ' ' ;
		} else {
			for ( let j = 0 ; tmp.length > 0 ; j++ ) {
				let n = tmp.substring( tmp.length - Math.min( 3 , tmp.length ) );
				let nn = parseInt( n , 10 );

				tmp = tmp.substring( 0 , tmp.length - 3 );
				let tmp2 = '' ;
				if ( nn > 99 ) {
					let nnn = parseInt( n.substring( 0 , 1 ) , 10 );
					tmp2 = num3[ nnn ] + ' ' ;
					nn = parseInt( n.substring( 1 , 3 ) , 10 );
				}

				let k = parseInt( n.substring( n.length - 1 ) , 10 );
				let l ;
				let m ;
				if ( k === 1 ) {
					l = 1 ;
					m = true ;
				} else
				if ( k > 1 && k < 5 ) {
					l = 2 ;
					m = true ;
				} else {
					l = 2 ;
					m = false ;
				}

				let o = 'm' ;
				if ( j === 1 ) {
					o = 'f' ;
				}

				let nnn ;
				if ( nn > 9 ) {
					nnn = parseInt( n.substring( n.length - 2 , n.length - 1 ) , 10 );
				} else {
					nnn = 0 ;
				}

				if ( nnn === 1 ) {
					tmp2 += num1[ k ] + ' ' ;
					l = 2 ;
					m = false ;
				} else {
					if ( nn > 9 ) {
						tmp2 += num2[ nnn ] + ' ' ;
					}

					if ( nn > 0 && k !== 0 ) {
						tmp2 += num[ o ][ k ] + ' ' ;
					}
				}

				if ( tmp2 !== '' || j === 0 ) {
					res = tmp2 + inForm( groups[ j ] , l , m ) + ' ' + res ;
				}
			}
		}

		if ( pos > -1 ) {
			tmp = p.substring( pos + 1 );
		} else {
			tmp = '00' ;
		}

		while ( tmp.length < 2 ) {
			tmp += '0' ;
		}

		let nn = parseInt( tmp , 10 );

		if ( nn === 0 ) {
			res += '00 ' + inForm( kop , 2 , false );
		} else {
			let k = parseInt( tmp.substring( tmp.length - 1 ) , 10 );
			let l ;
			let m ;
			if ( k === 1 ) {
				l = 1 ;
				m = true ;
			} else
			if ( k > 1 && k < 5 ) {
				l = 2 ;
				m = true ;
			} else {
				l = 2 ;
				m = false ;
			}

			if ( nn > 10 && nn < 20 ) {
				l = 2 ;
				m = false ;
			}

			res += tmp + ' ' + inForm( kop , l , m );
		}

		return res ;
	}



	$.CORES = new function() {
		this.BASE = new function() {
			this.getDateFromStr = function( str , rb ) {
				if ( str.match( /^\d{2}\.\d{2}\.\d{2}(?:\d{2})?$/ ) ) {
					const tmp = str.split( '.' );
					for( let i = 0 ; i < 3 ; i++ ) {
						tmp[ i ] = parseInt( tmp[ i ] , 10 );
					}
					if ( ( '' + tmp[ 2 ] ).length === 2 ) {
						tmp[ 2 ]+= 2000 ;
					}
					const dc = ( new Date( tmp[ 2 ] , tmp[ 1 ] , 0 ) ).getDate();
					if ( tmp[ 0 ] >= 1 && tmp[ 0 ] <= dc && tmp[ 1 ] >= 1 && tmp[ 1 ] <= 12 ) {
						if ( rb ) {
							return ( new Date( tmp[ 2 ] , tmp[ 1 ] - 1 , tmp[ 0 ] , 23 , 59 , 59 , 999 ) );
						} else {
							return ( new Date( tmp[ 2 ] , tmp[ 1 ] - 1 , tmp[ 0 ] , 0 , 0 , 0 , 0 ) );
						}
					}
				}
				return null ;
			};
		}();
		
		this.marks = new function() {
			this.dateRangeChange = function( event ) {
				event = event || window.event ;
				const drai = event.currentTarget ;
				const mr = drai.parentNode.parentNode ;
				const mIDp = mr.dataset.idPrefix ;
				const mID = mr.dataset.mid ;
				const cap = mr.dataset.isCa && mr.dataset.isCa == '1' ? '-ca' : '' ;
				const drli = document.getElementById( mIDp + mID + '-drl' );
				const drhi = document.getElementById( mIDp + mID + '-drh' );
				const cai = document.getElementById( mIDp + mID + cap );
				
				let drl = drli.value + '' ;
				drli.value = drl.replace( /[,-]/g , '.' );
				drli.style.color = '#f00' ;
				drl = $.CORES.BASE.getDateFromStr( drl );
				if ( drl != null ) {
					drli.style.color = '' ;
				}
				
				let drh = drhi.value ;
				drhi.value = drh.replace( /[,-]/g , '.' );
				drhi.style.color = '#f00' ;
				drh = $.CORES.BASE.getDateFromStr( drh );
				if ( drh != null ) {
					drhi.style.color = '' ;
				}
				
				if ( drl != null && drh != null ) {
					cai.value = mID + ':' + Math.round( Math.min( drl , drh ) / 1000 ) + '-' + Math.round( Math.max( drl , drh ) / 1000 );
				}
			};
			
			this.dateRangeDelete = function( event ) {
				event = event || window.event ;
				const drai = event.currentTarget ;
				const dra = drai.parentNode ;
				const mr = dra.parentNode ;
				const mIDp = mr.dataset.idPrefix ;
				const mcp = mr.dataset.classPrefix ;
				const mID = mr.dataset.mid ;
				const cap = mr.dataset.isCa && mr.dataset.isCa == '1' ? '-ca' : '' ;
				const cai = document.getElementById( mIDp + mID + cap );
				
				const ndra = dra.cloneNode( false );
				mr.replaceChild( ndra , dra );
				
				const ab = document.createElement( 'div' );
				ab.className = mcp + 'dra-add-btn' ;
				ab.onclick = $.CORES.marks.dateRangeAdd ;
				ndra.appendChild( ab );
								
				cai.value = mID ;
			};
			
			this.dateRangeAdd = function( event ) {
				event = event || window.event ;
				const drab = event.currentTarget ;
				const dra = drab.parentNode ;
				const mr = dra.parentNode ;
				const mIDp = mr.dataset.idPrefix ;
				const mcp = mr.dataset.classPrefix ;
				const mID = mr.dataset.mid ;
				
				const ndra = dra.cloneNode( false );
				mr.replaceChild( ndra , dra );
				
				ndra.appendChild( document.createTextNode( 'с ' ) );
				
				const drli = document.createElement( 'input' );
				drli.type = 'text' ;
				drli.id = mIDp + mID + '-drl' ;
				ndra.appendChild( drli );
				
				ndra.appendChild( document.createTextNode( ' по ' ) );
				
				const drhi = document.createElement( 'input' );
				drhi.type = 'text' ;
				drhi.id = mIDp + mID + '-drh' ;
				ndra.appendChild( drhi );
				
				const db = document.createElement( 'div' );
				db.className = mcp + 'dra-delete-btn' ;
				db.onclick = $.CORES.marks.dateRangeDelete ;
				ndra.appendChild( db );
				
				drli.oninput = drhi.oninput = $.CORES.marks.dateRangeChange ;
				drli.onpropertychange = drhi.onpropertychange = $.CORES.marks.dateRangeChange ;
								
			};

		}();
	};
	
	window.onload = function() {
		/*if ( typeof TDocGeneratorEngine === 'function' ) {
			TDocGeneratorEngine.init();
		}*/
		
		if ( localStorage.DocGenerator ) {
			let DocGenerator = JSON.parse( localStorage.DocGenerator );
			if ( !DocGenerator.VERSION || ( DocGenerator.VERSION && $.VERSIONS && $.VERSIONS[ 'doc-templates' ] && DocGenerator.VERSION != $.VERSIONS[ 'doc-templates' ] ) ) {
				localStorage.setItem( 'DocGenerator' , JSON.stringify( {
					VERSION : $.VERSIONS[ 'doc-templates' ]
				} ) );
			}
		}

		$.scrollbarSize = function() {
			const outer = document.createElement( 'div' );
			outer.style.visibility = 'hidden' ;
			document.body.appendChild( outer );
			
			outer.style.width = '100px' ;
			outer.style.height = '100px' ;
			const widthNoScroll = outer.offsetWidth ;
			const heightNoScroll = outer.offsetHeight ;
			// force scrollbars
			outer.style.overflow = 'scroll' ;

			// add innerdiv
			const inner = document.createElement( 'div' );
			inner.style.width = '100%' ;
			inner.style.height = '100%' ;
			outer.appendChild( inner );

			const widthWithScroll = inner.offsetWidth ;
			const heightWithScroll = inner.offsetHeight ;

			// remove divs
			outer.parentNode.removeChild( outer );

			return {
				w : widthNoScroll - widthWithScroll ,
				h : heightNoScroll - heightWithScroll
			};
		}();

		const wol = $.windowOnLoad ;
		for( let i = 0 ; i < wol.length ; i++ ) {
			wol[ i ]();
		}
	};
