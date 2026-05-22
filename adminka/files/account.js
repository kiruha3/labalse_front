/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	function doGenLogin() {
		const login = generatePwd( 'login' , 0 );
		document.getElementById( 'i_login' ).value = login ;
		const bc = document.getElementById( 'login_bc' );
		bc.style.backgroundImage =
			'url( /barcode.php?timernd=' + ( new Date() ).getTime() +
			'&dbg=1&src=' + encodeURI( login ) +
			'&type=QR&opt=' + encodeURI( '{ "EL" : "L" , "qrcode_mode" : "byte" , "pix_size" : 4 }' ) + ')' ;
		
		const cctcb = document.getElementById( 'copy-cred-to-clipboard-btn' );
		cctcb.disabled = true ;
	}
	
	function doGenPWD() {
		const pass = generatePwd( 'passP' , 0 , '._-' );
		document.getElementById( 'i_passwd' ).value = pass ;
		const bc = document.getElementById( 'passwd_bc' );
		bc.style.backgroundImage =
			'url( /barcode.php?timernd=' + ( new Date() ).getTime() +
			'&dbg=1&src=' + encodeURI( pass ) +
			'&type=QR&opt=' + encodeURI( '{ "EL" : "L" , "qrcode_mode" : "byte" , "pix_size" : 4 }' ) + ')' ;
		
		const cctcb = document.getElementById( 'copy-cred-to-clipboard-btn' );
		cctcb.disabled = true ;
	}
	
	function evtCredChanged() {
		const cctcb = document.getElementById( 'copy-cred-to-clipboard-btn' );
		cctcb.disabled = true ;
	}

	function copyCredToClipboard() {
		const login = document.getElementById( 'i_login' ).value ;
		const pass = document.getElementById( "i_passwd" ).value ;
		navigator.clipboard.writeText( 'Логин: ' + login + "\r\n" + 'Пароль: ' + pass );
	}
	