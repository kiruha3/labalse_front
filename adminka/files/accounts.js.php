<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	if ( isset( $_GET[ "ut" ] ) ) {
		$ut = $_GET[ "ut" ];
	} else {
		$ut = "st0" ;
	}

	echo "
	function tc( n ) {
		r = document.getElementById( n );
		i = document.getElementById( \"img\" + n );
		if ( r.style.display == \"none\" ) {
			r.style.display = \"\" ;
			i.src= \"themes/".$ut."/exp.gif\" ;
		} else {
			r.style.display = \"none\" ;
			i.src= \"themes/".$ut."/col.gif\" ;
		}
	}
	function doListUsers() {
		var d = document.getElementById( \"dep\" );
		d = d.value ;
		var t = prompt( \"Формат имени: \" , \"%F1 %i.%o.\" );
		window.location = \"list-users.php?dep=\" + d + \"&tmp1=\" + encodeURI( t );
	}" ;
?>