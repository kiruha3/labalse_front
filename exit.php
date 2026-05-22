<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "core.php" );
	/**
	 * @var $LoginOk
	 * @var $UserThemeLoc
	 * @var $dbConfig
	 */
	
	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( "auth.php" );
	}

	$UT = $UserThemeLoc ;

	$cookieDomain = $dbConfig[ 'engine.addresses.cookieDomain' ];
	setcookie( "uLogin"    , "" , time() + 60 * 60 * 24 * 1024 , "/" , $cookieDomain , "0" );
	setcookie( "uPassword" , "" , time() + 60 * 60 * 24 * 1024 , "/" , $cookieDomain , "0" );

	MainHead_L1( "" , array( "themes/$UT/buttons.css" , "themes/$UT/forms.css" ) );
		echo "<br><br><br><br><br>" ;
		MessageForm( "Вы более не авторизованы." , "выход" , "авторизация" , "auth.php" );
	closeHtml();
