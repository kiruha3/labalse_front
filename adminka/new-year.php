<?php
	/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	include_once( '../core.php' );
	/**
	 * @var TDB $portalDB
	 * @var boolean $LoginOk
	 * @var array $UserRights
	 */
	include_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */
	
	//$portalDB->dbgMode = true ;
	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}
	
	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'NEW-YEAR' , $Rights ) ) {
			$mayNewYear = in_array( 'NEW-YEAR' , $Rights[ 'NEW-YEAR' ] );
			$GoOut = !$mayNewYear ;
		} else {
			$mayNewYear = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}
	
	if ( $GoOut ) {
		ErrorMessage( 403 );
	}
	
	$cY = intval( date( 'Y' ) , 10 );
	$newYearMID = ( $cY - 2000 ) * 1000000 ;
	
	$pref = VERSION_CHAR_ID.'.'.ORG_INDEX_VRCSE.'.0110.20' ;
	
	$lastYearMID = $portalDB->row( "select max( `_id` ) as `max_id` from `indexes` where ( `_index_prefix` = ? ) and ( `_id` < ? )" , 'si' , $pref , $newYearMID );
	if ( $lastYearMID !== false ) {
		$portalDB->noResult( "delete from `indexes` where ( `_index_prefix` = ? ) and ( `_id` < ? )" , 'si' , $pref , $lastYearMID[ 'max_id' ] );
	}
	$lastIndex = $portalDB->row( "select max( `_id` ) as `max_id` from `indexes` where ( `_index_prefix` = ? )" , 's' , $pref );
	if ( $lastIndex === false || $lastIndex[ 'max_id' ] < $newYearMID ) {
		$portalDB->noResult( "insert into `indexes` ( `_index_prefix` , `_id` ) values ( ? , ? )" , 'si' , $pref , $newYearMID );
	}
	$portalDB->noResult( "alter table `subpoena` AUTO_INCREMENT = ".( $cY * 1000000 + 1 ) );
	
	MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
	echo '<br><br><br><br><br>' ;
	MessageForm( 'Год "'.$cY.'" начат!' );
	closeHtml();
