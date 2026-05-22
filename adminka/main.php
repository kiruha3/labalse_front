<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 */
	include_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) != 1 ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit();
	}

	$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

	$accountsEDIT = false ;
	if ( isset( $Rights[ 'ACCOUNTS' ] ) ) {
		$accountsEDIT = in_array( 'EDIT' , $Rights[ 'ACCOUNTS' ] );
	}

	$documentsADD = false ;
	if ( isset( $Rights[ 'DOCUMENTS' ] ) ) {
		$documentsADD = in_array( 'ADD' , $Rights[ 'DOCUMENTS' ] );
	}

	$networkMonVIEW = false ;
	if ( isset( $Rights[ 'NETWORKMON' ] ) ) {
		$networkMonVIEW = in_array( 'VIEW' , $Rights[ 'NETWORKMON' ] );
	}

	$licensesVIEW = false ;
	if ( isset( $Rights[ 'LICENSES' ] ) ) {
		$licensesVIEW = in_array( 'VIEW' , $Rights[ 'LICENSES' ] );
	}

	$mayNewYear = false ;
	if ( isset( $Rights[ 'NEW-YEAR' ] ) ) {
		$mayNewYear = in_array( 'NEW-YEAR' , $Rights[ 'NEW-YEAR' ] );
	}

	$maySetPlan = false ;
	if ( isset( $Rights[ 'SET-PLAN' ] ) ) {
		$maySetPlan = in_array( 'SET-PLAN' , $Rights[ 'SET-PLAN' ] );
	}

	$mayMarksEdit = isset( $Rights[ RIGHTS_GR__ADMINKA__MARKS ] );

	MainHead_L2( 'Админка' , 'Админка' , array( '../%UT/buttons.css' ) , array() , 'hlp/main.html' );

	if ( $accountsEDIT ) {
		echo '<a href="accounts.php" class="btn3">Аккаунты</a> ' ;
	}

	if ( $documentsADD ) {
		echo '<a href="documents.php" class="btn3">документы</a> ' ;
	}

	if ( $mayNewYear ) {
		echo '<a href="new-year.php" class="btn3">Начать новый год :)</a> ' ;
	}

	if ( $maySetPlan ) {
		echo '<a href="gov-plan.php" class="btn3">Установить план</a> ' ;
	}

	echo '<br/><br/>' ;
	/* TODO */
	if ( /*$mayMarksEdit*/true ) {
		echo '<a href="marks.php" class="btn3">Отметки</a> ' ;
	}

	closeHtml();
