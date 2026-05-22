/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	function sendXML( data ) {
		var sd = "random=" + ( new Date() ).getTime() + ( Math.random() * 1000 ) + "&engineData=" + data ;
		var req = null ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject( "Microsoft.XMLHTTP" );
		}

		if ( req ) {
			req.open( "POST" , "" , false );
			req.setRequestHeader( "Accept-Charset" , "windows-1251" );
			req.setRequestHeader( "Accept-Language" , "ru,en" );
			req.setRequestHeader( "Connection" , "close" );
			req.setRequestHeader( "Content-length" , sd.length );
			req.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );
			req.send( sd );

			//alert( req.responseText );
			return req.responseXML.documentElement ;
		}
	}

	function getXMLNodeValue( n ) {
		return ( n.text || n.textContent );
	}


	var portsData = [];
	var timeLine = [];
	var maxPortDataValue = 0 ;


	function Scene() {
		this.context = null ;
		this.drawingCanvas = document.getElementById( "main-canv" );
		if ( this.drawingCanvas && this.drawingCanvas.getContext ) {
			this.context = this.drawingCanvas.getContext( "2d" );
		}

		this.colors = "ff0000,00c000,0000ff,ff8000,00c0ff,ff00ff".split( "," );

		this.redraw = function () {
			if( this.context !== null ) {
				var context = this.context ;
				var colors = this.colors ;

				var cw = this.drawingCanvas.width ;
				var ch = this.drawingCanvas.height ;

				var ga = { l : 45 , t : 30 , r : 20 , b : 30 };

				context.fillStyle = "#ffffff" ;
				context.fillRect( 0 , 0 , cw , ch );
				context.strokeStyle = "#404040" ;
				context.strokeRect( 0 , 0 , cw , ch );

				context.strokeStyle = "#ffe0e0" ;
				context.beginPath();
					for( var i = 47.5 ; i < this.drawingCanvas.width - 25 ; i+= 15 ) {
						context.moveTo( i , 32.5 );
						context.lineTo( i , this.drawingCanvas.height - 30.5 );
					}
					for( var i = 32.5 ; i < this.drawingCanvas.height - 30 ; i+= 15 ) {
						context.moveTo( 43 , i );
						context.lineTo( this.drawingCanvas.width - 20.5 , i );
					}
				context.stroke();

				context.strokeStyle = "#000000" ;
				context.beginPath();
					context.moveTo( 43 , this.drawingCanvas.height - 30.5 );
					context.lineTo( this.drawingCanvas.width - 20.5 , this.drawingCanvas.height - 30.5 );
					context.moveTo( 45.5 , this.drawingCanvas.height - 28.5 );
					context.lineTo( 45.5 , 30.5 );
				context.stroke();

				var axdl = [ 5 ];
				var axl = { lo : 50 , bo : 10 , w : 75 , mw : 10 , mh : 10 , to : 15 };

				for ( var i = 0 ; i < Math.min( colors.length , portsData.length ) ; i++ ) {
					context.fillStyle = "#" + colors[ i ];
					context.fillRect( axl.lo + i * axl.w , ch - axl.bo - axl.mh , axl.mw , axl.mh );
					context.fillStyle = "#000000" ;
					context.fillText( portsData[ i ].n , axl.lo + i * axl.w + axl.to , ch - axl.bo - 2 );
				}

				var s = ( ch - ga.b - ga.t ) / maxPortDataValue ;
				s = s / 1.1 ;

				var toa = [];
				for ( var i = 0 ; i < timeLine.length ; i++ ) {
					for( var j = 0 ; j < colors.length /*portsData.length*/ ; j++ ) {
						toa[ j ] = { v : portsData[ j ].d[ i ] * s , i : j };
					}

					/*for( var j = 0 ; j < toa.length - 1 ; j++ ) {
						for ( var k = j + 1 ; k < toa.length ; k++ ) {
							if ( toa[ j ].v > toa[ k ].v ) {
								var tmp = toa[ j ];
								toa[ j ] = toa[ k ];
								toa[ k ] = tmp ;
							}
						}
					}*/

					var sum = 0.0 ;
					for( var j = 0 ; j < toa.length ; j++ ) {
						context.fillStyle = "#" + portsData[ toa[ j ].i ].c ;
						context.fillRect( 45.5 + i * 22 , ch - 30.5 - toa[ j ].v - sum , 21 , toa[ j ].v );
						sum+= toa[ j ].v ;
					}

				}
			}
		}
	}

	var scene = null ;

	window.onload = function() {
		scene = new Scene();
		var res = sendXML( "data" );
		var metaNode = null ;
		var dataNode = null ;

		for( var i = 0 ; i < res.childNodes.length ; i++ ) {
			switch ( res.childNodes[ i ].nodeName ) {
				case "meta" :
					metaNode = res.childNodes[ i ];
					break ;
				case "data" :
					dataNode = res.childNodes[ i ];
					break ;
			}
		}

		if ( metaNode != null ) {
			var legendNode = null ;
			for ( var i = 0 ; i < metaNode.childNodes.length ; i++ ) {
				switch ( metaNode.childNodes[ i ].nodeName ) {
					case "legend" :
						legendNode = metaNode.childNodes[ i ];
						break ;
				}
			}

			if ( legendNode != null ) {
				var ind = 0 ;
				for ( var i = 0 ; i < legendNode.childNodes.length ; i++ ) {
					var lne = legendNode.childNodes[ i ];
					if ( lne.nodeType == 1 ) {
						var pda = getXMLNodeValue( lne ).split( "_" );
						var pd = {
							i : ind ,
							n : "" ,
							dn : pda[ 0 ] ,
							pn : pda[ 1 ] ,
							c : "e0e0e0" ,
							s : 0 ,
							d : []
						}

						portsData[ ind ] = pd ;
						ind++ ;
					}
				}
			}
		}

		var mv = 0 ;

		var tli = 0 ;
		for( var i = 0 ; i < dataNode.childNodes.length ; i++ ) {
			var row = dataNode.childNodes[ i ];
			if ( row.nodeType == 1 ) {
				var sum = 0 ;
				timeLine[ tli ] = parseInt( getXMLNodeValue( row.childNodes[ 0 ] ) , 10 );
				for ( var j = 1 ; j < row.childNodes.length ; j++ ) {
					var pi = parseInt( row.childNodes[ j ].nodeName.substr( 1 ) , 10 );
					var cv = parseFloat( getXMLNodeValue( row.childNodes[ j ] ) );
					portsData[ pi ].d[ tli ] = cv ;
					portsData[ pi ].s+= cv ;
					sum+= cv ;
				}
				tli++ ;

				if ( sum > mv ) {
					mv = sum ;
				}

			}
		}

		maxPortDataValue = mv ;

		for ( var i = 0 ; i < portsData.length - 1 ; i++ ) {
			for ( var j = i + 1 ; j < portsData.length ; j++ ) {
				if ( portsData[ i ].s < portsData[ j ].s ) {
					var tmp = portsData[ i ];
					portsData[ i ] = portsData[ j ];
					portsData[ j ] = tmp ;
				}
			}
		}

		for ( var i = 0 ; i < scene.colors.length ; i++ ) {
			portsData[ i ].c = scene.colors[ i ];
		}

		var res = sendXML( "names" );
		for ( var i = 0 ; i < res.childNodes.length ; i++ ) {
			var dn = res.childNodes[ i ].getAttribute( "d" );
			var pn = res.childNodes[ i ].getAttribute( "p" );
			for ( var j = 0 ; j < portsData.length ; j++ ) {
				if ( portsData[ j ].dn == dn && portsData[ j ].pn == pn ) {
					portsData[ j ].n = getXMLNodeValue( res.childNodes[ i ] );
				}
			}
		}


		scene.redraw();
	}
// 666872856