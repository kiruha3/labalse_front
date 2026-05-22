<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	define( 'DEF_CODEPAGE' , 'cp1251' );
	define( 'DB_CODEPAGE' , DEF_CODEPAGE );

	if ( isset( $_SERVER[ 'orgIndex_vrcse' ] ) ) {
		define( 'ORG_INDEX_VRCSE' , $_SERVER[ 'orgIndex_vrcse' ] );
	} else {
		define( 'ORG_INDEX_VRCSE' , '{VALUE:ORG-INDEX-VRCSE}' );
	}

	if ( isset( $_SERVER[ 'dbHost' ] ) ) {
		$dbHost = $_SERVER[ 'dbHost' ];
	} else {
		$dbHost = '{VALUE:DB-HOST}' ;
	}

	if ( isset( $_SERVER[ 'dbUser' ] ) ) {
		$dbUser = $_SERVER[ 'dbUser' ];
	} else {
		$dbUser = '{VALUE:DB-USER-NAME}' ;
	}

	if ( isset( $_SERVER[ 'dbPass' ] ) ) {
		$dbPassword = $_SERVER[ 'dbPass' ];
		unset( $_SERVER[ 'dbPass' ] );
	} else {
		$dbPassword = '{VALUE:DB-PASSWORD}' ;
	}

	$dbDatabase = '{VALUE:DB-DATABASE}' ;
	if ( isset( $_SERVER[ 'alterDB' ] ) ) {
		$dbDatabase = $_SERVER[ 'alterDB' ];
	}

	$ErrorStyle = "color: #f00; font: bold 12pt;" ;
	$RequiredParameter = "<span style=\"color: #f00\">*</span>" ;

	$locale["Database"]["ConnectES"]	= '<span style="'.$ErrorStyle.'">Ошибка подключения к серверу баз данных: ' ;
	$locale["Database"]["ConnectEE"]	= '</span></span>' ;
	$locale["Database"]["SelectDBES"]	= '<span style="'.$ErrorStyle.'">Ошибка выбора базы данных: ' ;
	$locale["Database"]["SelectDBEE"]	= '</span></span>' ;
	$locale["Database"]["QueryES"]		= '<span style="'.$ErrorStyle.'">Ошибка выполнения запроса: ' ;
	$locale["Database"]["QueryEE"]		= '</span></span>' ;

	$locale[100]= "Необходино заполнить поле <b>Имя учетной записи</b>";
	$locale[101]= "Необходино заполнить поле <b>Пароль учетной записи</b>";
	$locale[102]= "Пользователь не зарегистрирован в системе<br>Укажите имя и пароль вашей зарегистрированной учетной записи";
	$locale[103]= "Пароль указан не верно";
	$locale[104]= "Пользователю нельзя работать с текущего IP адресса." ;
	$locale[105]= "Пользователь с указанным Именем учетной записи уже зарегистрирован в системе<br>Укажите другое Имя учетной записи" ;
	$locale[106]= "Необходино заполнить поле <b>Фамилия Имя Отчество</b>" ;
	$locale[107]= "Необходино заполнить поле <b>Оформление</b>" ;


