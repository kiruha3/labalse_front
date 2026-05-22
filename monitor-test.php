<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<meta http-equiv="Expires" content="-1">
		<meta http-equiv="PRAGMA" content="no-cache">
		<style type="text/css">
			* {
				padding : 0px ;
				margin : 0px ;
			}

			.fsd , .cd {
				position : fixed ;
				left : 0px ;
				top : 0px ;
			}

			.cd {
				background-position : center ;
				background-repeat : no-repeat ;
			}

		</style>
		<script type="text/javascript">
			window.onload = function() {
				var w = screen.width ;
				var h = screen.height ;

				var fsd = document.createElement( "div" );
				fsd.className = "fsd" ;
				fsd.style.width = w + "px" ;
				fsd.style.height = h + "px" ;
				this.testID = [ 0 , 0 ];

				var cd = document.createElement( "div" );
				cd.className = "cd" ;
				cd.style.width = w + "px" ;
				cd.style.height = h + "px" ;
				cd.style.backgroundImage = "url( \"files/monitor-tests/cross.png\" )" ;

				cd.onclick = function( wnd , fsd , cd ) {
					return function () {
						switch ( wnd.testID[ 0 ] ) {
							case 0 :
								var tid = wnd.testID[ 1 ]++ ;
								var colorTab = "000000,0000ff,00ff00,00ffff,ff0000,ff00ff,ffff00,ffffff,000080,008000,008080,800000,800080,808000,808080".split( "," );
								fsd.style.backgroundImage = "" ;
								fsd.style.backgroundColor = "#" + colorTab[ tid ];
								cd.style.backgroundImage = "" ;
								if ( ++tid >= colorTab.length ) {
									wnd.testID[ 0 ] = 1 ;
									wnd.testID[ 1 ] = 0 ;
								}
								break
							case 1 :
								var tid = wnd.testID[ 1 ]++ ;
								var bgImages = "bg1-1,bg1-2,bg1-3,bg2-1,bg2-2,bg2-3,bg3-1,bg3-2,bg3-3,bg3-4".split( "," );
								var cImages = "cross-inv-b,cross-inv-b,cross-inv-b,cross-b,cross-b,cross-b,cross-b,cross-b,cross-inv-b,cross-g-b".split( "," );
								//var cImages = "cross-b,cross-b,cross-b,cross-inv-b,cross-inv-b,cross-inv-b,cross-b,cross-b,cross-inv-b".split( "," );
								fsd.style.backgroundImage = "url( \"files/monitor-tests/" + bgImages[ tid ] + ".png\" )" ;
								fsd.style.backgroundColor = "" ;
								cd.style.backgroundImage = "url( \"files/monitor-tests/" + cImages[ tid ] + ".png\" )" ;
								if ( ++tid >= bgImages.length ) {
									wnd.testID[ 0 ] = 2 ;
									wnd.testID[ 1 ] = 0 ;
								}
								break
							case 2 :
								var tid = wnd.testID[ 1 ]++ ;
								var bgImages = "bg-s-1,bg-s-2,bg-s-3".split( "," );
								fsd.style.backgroundImage = "url( \"files/monitor-tests/" + bgImages[ tid ].replace( "s" , screen.width + "x" + screen.height ) + ".png\" )" ;
								fsd.style.backgroundColor = "" ;
								cd.style.backgroundImage = "" ;
								if ( ++tid >= bgImages.length ) {
									wnd.testID[ 0 ] = 0 ;
									wnd.testID[ 1 ] = 0 ;
								}
								break
						}
					}
				}( this , fsd , cd );

				cd.onclick();

				document.body.appendChild( fsd );
				document.body.appendChild( cd );
			}
		</script>

	</head>
	<body>
	</body>
</html>

