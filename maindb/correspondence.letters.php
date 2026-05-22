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
	 * @var TDB $portalDB
	 */
	require_once ( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

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
			$ccid = intval( $data->getAttribute( 'id' ) , 10 );
			echo '<result>' ;

			$ad = $portalDB->query( "select `t1`.`ext_id` as `type_of_agency` , `t1`.`name` as `agency_name` , `t2`.`name` as `agent_name` , `t1`.`destination` , `t2`.`id` as `agent_id` from `agency` as `t1` , `agent` as `t2` , `correspondence-target` as `t3` where ( `t2`.`id` = `t3`.`tgt` ) and ( `t2`.`ext_id` = `t1`.`id` ) and ( `t3`.`ext_id` = ? )" , 'agent_id' , 'i' , $ccid );
			$atIDs = array_keys( $ad );
			$atIDs = array_combine( $atIDs , $atIDs );
			$ad2 = $portalDB->query( "select * from `agent-contacts` where ( `ext_id` in ( ?* ) ) and ( `type` = 1 )" , false , "*i" , $atIDs );
			foreach( $ad2 as $adr ) {
				$cid = $adr[ 'ext_id' ];
				if ( !is_array( $atIDs[ $cid ] ) ) {
					$atIDs[ $cid ] = array();
				}
				$atIDs[ $cid ][]= $adr ;
			}
			foreach ( $ad as $adr ) {
				$cid = $adr[ 'agent_id' ];
				foreach ( $atIDs[ $cid ] as $adr2 ) {
					if ( trim( $adr2[ 'value' ] ) != '' ) {
						echo '<ai id="'.$adr2[ 'id' ].'" type="atc"><addressee><![CDATA[', ($adr['type_of_agency'] != '11' ? $adr['agency_name'] . ', ' : ''), $adr['agent_name'], ']]></addressee><destination><![CDATA[', $adr2[ 'value' ] , ']]></destination></ai>' ;
					}
				}
			}
			foreach ( $ad as $adr ) {
				$cid = $adr[ 'agent_id' ];
				if ( trim( $adr[ 'destination' ] ) != '' ) {
					echo '<ai id="'.$cid.'" type="at"><addressee><![CDATA[' , ( $adr[ 'type_of_agency' ] != '11' ? $adr[ 'agency_name' ].', ' : '' ) , $adr[ 'agent_name' ] , ']]></addressee><destination><![CDATA[' , $adr[ 'destination' ] , ']]></destination></ai>' ;
				}
			}
			echo '</result>' ;
			break ;

		case 'get-letter-data' :
			$docID = intval( $data->getAttribute( 'docid' ) , 10 );
			$docInfo = $portalDB->row( "select * from `correspondence-main` where `id` = ?" , 'i' , $docID );
			$expNum =  'по журналу №'.$docInfo[ 'num' ].' от '.date( 'Y' , $docInfo[ 'date' ] ).'г.' ;
			$aiID = intval( $data->getAttribute( 'id' ) , 10 );

			$aiType = $data->getAttribute( 'type' );
			$ad = array();
			switch( $aiType ) {
				case 'at' :
					$atInfo = $portalDB->row(
						"select
							`t1`.`ext_id` as `type_of_agency` , 
       						`t1`.`name` as `agency_name` , 
       						`t2`.`name` as `agent_name` , 
       						`t1`.`destination` 
						from 
						    `agency` as `t1` , 
						    `agent` as `t2` 
						where 
						    ( `t1`.`id` = `t2`.`ext_id` ) and
						    ( `t2`.`id` = ? )" ,
						'i' , $aiID
					);
					break ;

				case 'atc' :
					$atInfo = $portalDB->row(
						"select
							`t1`.`ext_id` as `type_of_agency` , 
       						`t1`.`name` as `agency_name` , 
       						`t2`.`name` as `agent_name` , 
       						`t3`.`value` as `destination` 
						from 
						    `agency` as `t1` , 
						    `agent` as `t2` , 
						    `agent-contacts` as `t3` 
						where 
						    ( `t1`.`id` = `t2`.`ext_id` ) and
						    ( `t2`.`id` = `t3`.`ext_id` ) and
						    ( `t3`.`id` = ? )" ,
						'i' , $aiID
					);

					break ;

				default :
					$atInfo = array();
			}

			$atInfo[ 'addressee' ] = ( $atInfo[ 'type_of_agency' ] != '11' ? $atInfo[ 'agency_name' ].', ' : '' ).$atInfo[ 'agent_name' ];

			$r = processAddress( $atInfo[ 'destination' ] , true );
			$atInfo[ 'destination' ] = $r[ 'address' ];
			$di = $r[ 'index' ];
			echo '<result index="'.$di.'"><addressee><![CDATA[' , $atInfo[ 'addressee' ] , ']]></addressee><destination><![CDATA[' , $atInfo[ 'destination' ] , ']]></destination></result>' ;

			$p1 = floatval( $data->getAttribute( 'p1' ) );
			$p2 = floatval( $data->getAttribute( 'p2' ) );
			$w = intval( round( $data->getAttribute( 'w' ) * 1000.0 ) , 10 );

			$portalDB->noResult( "insert into `register-correspondence` ( `comment` , `date` , `destination` , `addressee` , `price` , `add_price` , `weight` , `ext_type` , `ext_id` ) values ( ? , ? , ? , ? , ? , ? , ? , 'correspondence' , ? )" , 'sissddis' , $expNum , time() , $di.', '.$atInfo[ 'destination' ] , $atInfo[ 'addressee' ] , $p1 , $p2 , $w , $docID );

			break ;
	}
