/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	function doGenLogin() {
		var login = generatePwd( 'login' , 0 );
		document.getElementById( 'euLogin' ).value = login ;
	};
	
	function doGenPWD() {
		var login = generatePwd( 'passP' , 0 , '._-' );
		document.getElementById( 'euPassword' ).value = login ;
	};
