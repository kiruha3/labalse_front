<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
?>

	rbArray = [ 0 , 0 , 0 , 0 , 0 , 0 , 0 , 0 ];

<?php
	echo "cd = \"".urldecode( $_REQUEST[ "dn" ] )."\" ;\r\n" ;
	echo "cti = ".intval( $_REQUEST[ "ti" ] )." ;\r\n" ;
?>

	function rb( i , s ) {
		rbArray[ i - 1 ]+= s ;
		if ( rbArray[ i - 1 ] < 0 ) {
			rbArray[ i - 1 ] = 0 ;
		}
		var img = document.getElementById( "gr_img_" + i );
		img.src="?imgi=" + i + "&img=" + ( new Date().getTime()  ) + "&dn=" + cd + "&ti=" + cti + "&rb=" + rbArray[ i - 1 ];
	}
