<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once ( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $portalDB
	 */
	require_once ( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	require_once( '../cores/core.maindb.php' );

	$modeAJAX = isset( $_REQUEST[ "mode" ] );
	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			Redirect( "../auth.php" );
		}
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "SUBPOENAS" , $Rights ) ) {
			$subpoenaAdd = in_array( "ADD" , $Rights[ "SUBPOENAS" ] );
			$subpoenaEdit = in_array( "EDIT" , $Rights[ "SUBPOENAS" ] );
			$subpoenaAccess = strtolower( array_key_exists( "SUBPOENAS-ACCESS" , $Rights ) ? $Rights[ "SUBPOENAS-ACCESS" ][ 0 ] : "Expert" );
			$GoOut = !$subpoenaAdd ;
		} else {
			$subpoenaAdd = $subpoenaEdit = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			ErrorMessage( 403 );
		}
	}

	header( 'Content-Type: text/xml' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Expires: '.date( 'r' ) );
	header( 'Expires: -1' , false );

	echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

	function processAddress( $addr , $splited = false ) {
		$sdi = array();
		if ( preg_match( "/(?:[,. ]+(\\d{6})[,. ]*)/" , $addr , $sdi ) == 1 ) {
			if ( $splited ) {
				return array( "index" => $sdi[ 1 ] , "address" => preg_replace( "/(?:[,. ]+(\\d{6})[,. ]*)/" , "" , $addr ) );
			} else {
				return $sdi[ 1 ].", ".preg_replace( "/(?:[,. ]+(\\d{6})[,. ]*)/" , "" , $addr );
			}
		} else
		if ( preg_match( "/(?:[,. ]*(\\d{6})[,. ]+)/" , $addr , $sdi ) == 1 ) {
			if ( $splited ) {
				return array( "index" => $sdi[ 1 ] , "address" => preg_replace( "/(?:[,. ]*(\\d{6})[,. ]+)/" , "" , $addr ) );
			} else {
				return $sdi[ 1 ].", ".preg_replace( "/(?:[,. ]*(\\d{6})[,. ]+)/" , "" , $addr );
			}
		} else {
			if ( $splited ) {
				return array( "index" => "" , "address" => $addr );
			} else {
				return $addr ;
			}
		}
	}

	$DD = new DomDocument();
	$DD->loadXML( $_REQUEST[ 'data' ] );

	$data = $DD->documentElement ;

	switch ( $data->nodeName ) {

		case 'get-letter-data' :
			$subID = intval( $data->getAttribute( 'id' ) , 10 );
			$subInfo = $portalDB->simpleRow( 'subpoena' , $subID );
			$comment =  'ответ на повестку №'.subpoenaNumber( $subInfo[ 'id' ] ).' за '.date( 'Y' , $subInfo[ 'date' ] ).'г.' ;
			$ayID = $subInfo[ 'agency_id' ];
			$ayInfo = $portalDB->simpleRow( 'agency' , $ayID );

			$r = processAddress( $subInfo[ 'address' ] , true );
			$address = $r[ 'address' ];
			$di = $r[ 'index' ];
			echo '<result index="'.$di.'"><addressee><![CDATA['.$ayInfo[ 'name' ].']]></addressee><destination><![CDATA['.$address.']]></destination></result>' ;

			$p1 = floatval( $data->getAttribute( 'p1' ) );
			$p2 = floatval( $data->getAttribute( 'p2' ) );
			$w = intval( round( $data->getAttribute( 'w' ) * 1000.0 ) , 10 );

			$portalDB->noResult( "insert into `register-correspondence` ( `comment` , `date` , `destination` , `addressee` , `price` , `add_price` , `weight` , `ext_type` , `ext_id` ) values ( ? , ? , ? , ? , ? , ? , ? , 'subpoena' , ? )" , 'sissddis' , $comment , time() , $di.', '.$address , $ayInfo[ 'name' ] , $p1 , $p2 , $w , $subID );

			break ;
	}
