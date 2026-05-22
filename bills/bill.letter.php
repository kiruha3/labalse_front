<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once ( "../core.php" );
	require_once ( "lconfig.php" );

	header( "Content-Type: text/xml" );
	header( "Pragma: no-cache" );
	header( "Cache-Control: no-store, no-cache, must-revalidate" );
	header( "Expires: ".date( "r" ) );
	header( "Expires: -1" , false );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

	if ( count( $UserRights ) != 1 ) {
		echo "<result/>" ;
		exit();
	}

	$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

	if ( array_key_exists( "EXTENTIONS" , $Rights ) ) {
		$mayPrintAddressLabel = in_array( "PRINT-ADDRESS-LABEL" , $Rights[ "EXTENTIONS" ] );
	} else {
		$mayPrintAddressLabel = false ;
	}

	if ( !$mayPrintAddressLabel ) {
		echo "<result/>" ;
		exit();
	}

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
	$DD->loadXML( $_REQUEST[ "data" ] );

	$data = $DD->documentElement ;

	switch ( $data->nodeName ) {

		case "get-letter-data" :
			$pcid = $data->getAttribute( "id" );
			$ad = $portalDB->row( "select * from `bills` where ( `id` = ? );" , "i" , $pcid );
			$r = processAddress( $ad[ "address" ] , true );
			$ad[ "address" ] = $r[ "address" ];
			$di = $r[ "index" ];
			echo "<result index=\"".$di."\"><addressee>" , toCDATA( $ad[ "payer" ] ) , "</addressee><destination>" , toCDATA( $ad[ "address" ] ) , "</destination></result>" ;

			$portalDB->noResult(
				"insert into `register-correspondence` ( `comment` , `date` , `destination` , `addressee` , `price` , `add_price` , `weight` , `ext_type` , `ext_id` ) values ( ? , ? , ? , ? , 17.00 , 0 , 20 , \"bills\" , ? )" ,
				"sissi" ,
				"Счет №".$ad[ "number" ]." от ".date( "d.m.Y" , strtotime( $ad[ "date" ] ) ) , time() , $di.", ".$ad[ "address" ] , $ad[ "payer" ] , $pcid );
			break ;

	}

	exit();
?>