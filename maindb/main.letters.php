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

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	header( 'Content-Type: text/xml' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Expires: '.date( 'r' ) );
	header( 'Expires: -1' , false );

	echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

	if ( count( $UserRights ) != 1 ) {
		echo '<result/>' ;
		exit();
	}

	$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

	if ( array_key_exists( 'EXTENTIONS' , $Rights ) ) {
		$mayPrintAddressLabel = in_array( 'PRINT-ADDRESS-LABEL' , $Rights[ 'EXTENTIONS' ] );
	} else {
		$mayPrintAddressLabel = false ;
	}

	if ( !$mayPrintAddressLabel ) {
		echo '<result status="error"/>' ;
		exit();
	}

	function getAddr( $code , $ap2 , $av = false ) {
		global $con2 ;

		$res = array();

		if ( strlen( $code ) != 17 || $code == "00000000000000000" ) {
			return "" ;
		}

		$sd = RowAsArray( $con2 , "select * from `street` where `CODE` = ".Str2SQL( $code )." ;" );
		$res[]= trim( $sd[ "SOCR" ] ).". ".trim( $sd[ "NAME" ] );
		$postIndex = trim( $sd[ "INDEX" ] );

		$kc = array( substr( $code , 0 , 2 ) , substr( $code , 2 , 3 ) , substr( $code , 5 , 3 ) , substr( $code , 8 , 3 ) );
		$kcm = array( "00" , "000" , "000" , "000" );
		$p = 3 ;
		while ( $p >= 0 && $kc[ $p ] == $kcm[ $p ] ) {
			$p-= 1 ;
		}

		while ( $p >= 0 ) {
			$klo = RowAsArray( $con2 , "select * from `kladr` where `CODE` = ".Str2SQL( implode( $kc )."00" )." ;" );
			$res[]= trim( $klo[ "SOCR" ] ).". ".trim( $klo[ "NAME" ] );
			$kc[ $p ] = $kcm[ $p ];
			$p-= 1 ;
			while ( $p >= 0 && $kc[ $p ] == $kcm[ $p ] ) {
				$p-= 1 ;
			}
		}

		$res = array_reverse( $res );

		if ( !$av ) {
			return ( $postIndex != "" ? $postIndex.", " : "" ).implode( ", " , $res ).", ".$ap2 ;
		} else {
			return array( "index" => $postIndex , "addr" => ( implode( ", " , $res ).", ".$ap2 ) );
		}


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
	$DD->loadXML( $_REQUEST[ 'data' ] );

	$data = $DD->documentElement ;

	switch ( $data->nodeName ) {

		case 'get-address-list' :
			$pcid = getCharID( $data->getAttribute( 'id' ) , DOCTYPE_MATINCOMING );
			echo '<result>' ;

			$ad = $portalDB->query( "select `t4`.`id` , `t1`.`ext_id` as `type_of_agency` , `t1`.`name` as `agency_name` , `t2`.`name` as `agent_name` , `t4`.`value` as `destination` from `agency` as `t1` , `agent` as `t2` , `matincoming` as `t3` , `agent-contacts` as `t4` where ( `t1`.`id` = `t3`.`from_agency` ) and ( `t2`.`id` = `t3`.`from_agent` ) and ( `t4`.`ext_id` = `t2`.`id` ) and ( `t4`.`type` = 1 ) and ( `t4`.`actual` = 1 ) and ( `t3`.`id` = ? )" , false ,'s' , $pcid );
			foreach( $ad as &$i ) {
				$i[ 'destination' ] = processAddress( $i[ 'destination' ] );
				echo '<ai type="from-contacts" id="'.$i[ 'id' ].'"><addressee>'.toCDATA( ( $i[ 'type_of_agency' ] != '11' ? $i[ 'agency_name' ].', ' : '' ).$i[ 'agent_name' ] ).'</addressee><destination>'.toCDATA( $i[ 'destination' ] ).'</destination></ai>' ;
			} unset( $i );

			$ad = $portalDB->row( "select `t1`.`ext_id` as `type_of_agency` , `t1`.`name` as `agency_name` , `t2`.`name` as `agent_name` , `t1`.`destination` from `agency` as `t1` , `agent` as `t2` , `matincoming` as `t3` where ( `t1`.`id` = `t3`.`from_agency` ) and ( `t2`.`id` = `t3`.`from_agent` ) and ( `t3`.`id` = ? )" , 's' , $pcid );
			echo '<ai type="from-base"><addressee>'.toCDATA( ( $ad[ 'type_of_agency' ] != '11' ? $ad[ 'agency_name' ].', ' : '' ).$ad[ 'agent_name' ] ).'</addressee><destination>'.toCDATA( $ad[ 'destination' ] ).'</destination></ai>' ;

			$ad = $portalDB->query( "select * from `addresses` where ( `mat_id` = ? )" , false , 's' , $pcid );
			foreach( $ad as &$i ) {
				$i[ 'destination' ] = processAddress( $i[ 'destination' ] );
				echo '<ai type="from-addresses" id="'.$i[ 'id' ].'"><addressee>'.toCDATA( $i[ 'addressee' ] ).'</addressee><destination>'.toCDATA( $i[ 'destination' ] ).'</destination></ai>' ;
			} unset( $i );

			echo '</result>' ;
			break ;

		case 'get-workers-list' :
			$pcid = getCharID( $data->getAttribute( 'id' ) , DOCTYPE_MATINCOMING );

			$ad = $portalDB->query( "select `t1`.`exp_id` from `expertize` as `t1` , `matincominglvl2` as `t2` where ( `t1`.`ext_id` = `t2`.`id` ) and ( `t2`.`mat_id` = ? ) group by `t1`.`exp_id`" , false , 's' , $pcid );
			if ( $ad === false || count( $ad ) == 0 ) {
				echo '<result status="ok" count="0"/>' ;
			} else {
				foreach( $ad as &$i ) {
					$i = $i[ 'exp_id' ];
				} unset( $i );
				$ad = $portalDB->query( "select `first_id` from `workers` where ( `id` in ( ?* ) ) group by `first_id`" , false , '*i' , $ad );
				foreach( $ad as &$i ) {
					$i = $i[ 'first_id' ];
				} unset( $i );
				$ad = $portalDB->query( "select * from `workers` where ( `first_id` in ( ?* ) ) and ( `actual` = 1 )" , false , '*i' , $ad );

				echo '<result status="ok" count="' , count( $ad ) , '">' ;

				foreach( $ad as &$i ) {
					echo '<w id="' , $i[ 'first_id' ] , '">'.toCDATA( NAMES_Format( NAMES_parse( $i[ 'name' ] ) , '%F1 %I1 %O1' ) ).'</w>' ;
				}
				unset( $i );

				echo '</result>' ;
			}
			break ;

		case 'register-letter' :
			$pcid = $data->getAttribute( 'mat-id' );
			$ad = array();
			foreach( $data->childNodes as $cn ) {
				if ( $cn->nodeType == XML_ELEMENT_NODE ) {
					switch ( $cn->nodeName ) {
						case 'addressee' :
						case 'destination' :
							$ad[ $cn->nodeName ] = rcvt( $cn->nodeValue );
							break ;
					}
				}
			}
			$expNum = 'эксп '.matincomingNumber( $pcid ).' от '.matincomingYear( $pcid ).'г.' ;

			$r = processAddress( $ad[ 'destination' ] , true );
			$ad[ 'destination' ] = $r[ 'address' ];
			$di = $r[ 'index' ];
			echo '<result index="'.$di.'"><addressee>'.toCDATA( $ad[ 'addressee' ] ).'</addressee><destination>'.toCDATA( $ad[ 'destination' ] ).'</destination></result>' ;

			$p1 = floatval( $data->getAttribute( 'p1' ) );
			$p2 = floatval( $data->getAttribute( 'p2' ) );
			$w = intval( round( $data->getAttribute( 'w' ) * 1000.0 ) );

			$portalDB->noResult( "insert into `register-correspondence` ( `comment` , `date` , `destination` , `addressee` , `price` , `add_price` , `weight` , `ext_type` , `ext_id` ) values ( ? , ? , ? , ? , ? , ? , ? , \"matincoming\" , ? )" , 'sissddis' , $expNum , time() , $di.', '.$ad[ 'destination' ] , $ad[ 'addressee' ] , $p1 , $p2 , $w , $pcid );

			break ;
	}
