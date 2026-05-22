<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	use Marks\updateMarks ;

	include_once( '../core.php' );
	/**
	 * @var TDB $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserAllWorkers
	 * @var $UserID
	 * @var $dbConfig
	 */
	require_once( 'lconfig.php' );
	require_once( '../cores/core.maindb.php' );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/data-bank.php' );
	require_once( 'request.core.php' );
	require_once( '../equipment.core.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		exit ;
	}

	$mayExpertizeEDIT = false ;
	$mayExpertizeCORRECT = false ;
	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'EXPERTIZE' , $Rights ) ) {
			$mayExpertizeEDIT = in_array( 'EDIT' , $Rights[ 'EXPERTIZE' ] );
			$mayExpertizeCORRECT = in_array( 'CORRECT_AFTER_CLOSE' , $Rights[ 'EXPERTIZE' ] );
		}
	}
	
	$access = ( isset( $_REQUEST[ 'edit' ] ) || isset( $_REQUEST[ 'mode' ] ) ) && $mayExpertizeEDIT ;
	$modeAJAX = isset( $_REQUEST[ 'mode' ] );

	if ( !$access || !$modeAJAX ) {
		exit ;
	}

	header( 'Content-Type: text/xml' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Expires: '.date( 'r' ) );
	header( 'Expires: -1' , false );

	echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

	$DD = new DomDocument();
	$DD->loadXML( $_REQUEST[ 'data' ] );

	$data = $DD->documentElement ;

	switch ( $data->nodeName ) {
		case 'add-equipment' :
			$tabUsableEq = $portalDB->query( "select `t2`.`id` from `equipment` as `t1` , `exp-equipment` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( not ( `t2`.`state` <=> -1 ) )" , "id" );

			$extID = intval( $data->getAttribute( 'extid' ) );
			$eqID = intval( $data->getAttribute( 'eqid' ) );
			$ds = $data->getAttribute( 'ds' );
			$ts = $data->getAttribute( 'ts' );
			$de = $data->getAttribute( 'de' );
			$te = $data->getAttribute( 'te' );

			$ta = trim( iconv( 'utf8' , 'cp1251' , $data->nodeValue ) );

			$m = array();
			$n = preg_match( '/^\s*([0-2]\d|3[0-1])[.,-](0\d|1[0-2])[.,-](?:20)?(\d{2})\s*/' , $ds , $m );
			$cds = false ;
			if ( $n == 1 ) {
				$m[ 1 ] = intval( $m[ 1 ] );
				$m[ 2 ] = intval( $m[ 2 ] );
				$m[ 3 ] = intval( $m[ 3 ] );
				if ( $m[ 3 ] >= 0 && $m[ 3 ] <= 99 ) {
					$m[ 3 ]+= 2000 ;
				}

				$dc = intval( date( 't' , mktime( 0 , 0 , 0 , $m[ 2 ] , 1 , $m[ 3 ] ) ) );
				if ( $dc >= $m[ 1 ] ) {
					$cds = true ;
					$ds = $m ;
				}
			}

			$m = array();
			$n = preg_match( '/^\s*([0-1]\d|2[0-3])[-.,:]([0-5]\d)\s*/' , $ts , $m );
			$cts = false ;
			if ( $n == 1 ) {
				$m[ 1 ] = intval( $m[ 1 ] );
				$m[ 2 ] = intval( $m[ 2 ] );
				$cts = true ;
				$ts = $m ;
			}

			$m = array();
			$n = preg_match( '/^\s*([0-2]\d|3[0-1])[.,-](0\d|1[0-2])[.,-](?:20)?(\d{2})\s*/' , $de , $m );
			$cde = false ;
			if ( $n == 1 ) {
				$m[ 1 ] = intval( $m[ 1 ] );
				$m[ 2 ] = intval( $m[ 2 ] );
				$m[ 3 ] = intval( $m[ 3 ] );
				if ( $m[ 3 ] >= 0 && $m[ 3 ] <= 99 ) {
					$m[ 3 ]+= 2000 ;
				}

				$dc = intval( date( "t" , mktime( 0 , 0 , 0 , $m[ 2 ] , 1 , $m[ 3 ] ) ) );
				if ( $dc >= $m[ 1 ] ) {
					$cde = true ;
					$de = $m ;
				}
			}

			$m = array();
			$n = preg_match( '/^\s*([0-1]\d|2[0-3])[-.,:]([0-5]\d)\s*/' , $te , $m );
			$cte = false ;
			if ( $n == 1 ) {
				$m[ 1 ] = intval( $m[ 1 ] );
				$m[ 2 ] = intval( $m[ 2 ] );
				$cte = true ;
				$te = $m ;
			}

			if ( !isset( $tabUsableEq[ $eqID ] ) ) {
				echo '<result state="err">Оборудование не готово к использованию</result>' ;
			} else
			if ( $cds && $cts && $cde && $cte ) {
				$euStart = mktime( $ts[ 1 ] , $ts[ 2 ] , 0 , $ds[ 2 ] , $ds[ 1 ] , $ds[ 3 ] );
				$euFinish = mktime( $te[ 1 ] , $te[ 2 ] , 0 , $de[ 2 ] , $de[ 1 ] , $de[ 3 ] );
				if ( $euStart > $euFinish ) {
					$tmp = $euStart ;
					$euStart = $euFinish ;
					$euFinish = $tmp ;
				}
				$portalDB->noResult( "insert into `exp-equipment-usage` ( `ext_id` , `eq_id` , `start` , `finish` , `comment` ) values ( ? , ? , ? , ? , ? )" , "iiiis" , $extID , $eqID , $euStart , $euFinish , $ta );
				$rowID = $portalDB->lastInsertID();
				echo '<result state="ok" row-id="'.$rowID.'" ds="'.date( 'd-m-Y' , $euStart ).'" ts="'.date( 'H:i' , $euStart ).'" de="'.date( 'd-m-Y' , $euFinish ).'" te="'.date( 'H:i' , $euFinish ).'">'.toCDATA( $ta ).'</result>' ;
			} else {
				echo '<result state="err">Неверный формат даты</result>' ;
			}

			break ;

		case 'substances-norms' :
			$res = $portalDB->query( "select `t2`.* , `t1`.`unit` , `t3`.`id` as `sis_id` from `substances` as `t1` , `substances-norms` as `t2` , `substances-in-stock` as `t3` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t1`.`id` = `t3`.`ext_id` ) and ( not ( `t3`.`state` <=> -1 ) )" );
			if ( $res !== false && count( $res ) > 0 ) {
				echo '<result>' ;
				foreach( $res as $sn ) {
					echo '<n id="'.$sn[ 'id' ].'" ei="'.$sn[ 'ext_id' ].'" si="'.$sn[ 'sis_id' ].'" v="'.$sn[ 'norm' ].'" u="'.$sn[ 'unit' ].'">'.toCDATA( $sn[ 'name' ] ).'</n>' ;
				}
				echo '</result>' ;
			} else {
				echo '<result />' ;
			}
			break ;

		case 'add-substance' :
			$tabUsableMaterials = $portalDB->query( "select `t2`.`id` , `t1`.`unit` from `substances` as `t1` , `substances-in-stock` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( not ( `t2`.`state` <=> -1 ) );" , 'id' );

			$extID = intval( $data->getAttribute( 'extid' ) );
			$sID = intval( $data->getAttribute( 'sid' ) );
			$ds = $data->getAttribute( 'ds' );
			$ts = $data->getAttribute( 'ts' );
			$ml = $data->getAttribute( 'sid' );
			$dd2l = intval( $data->getAttribute( 'nid' ) );
			$mCount = $data->getAttribute( 'c' );

			if ( $dd2l == -1 ) {
				$dd2ta = trim( iconv( 'utf8' , 'cp1251' , $data->nodeValue ) );
			} else {
				$dd2ta = '' ;
			}

			$m = array();
			$n = preg_match( '/^\s*([0-2]\d|3[0-1])[.,-](0\d|1[0-2])[.,-](?:20)?(\d{2})\s*/' , $ds , $m );
			$cds = false ;
			if ( $n == 1 ) {
				$m[ 1 ] = intval( $m[ 1 ] );
				$m[ 2 ] = intval( $m[ 2 ] );
				$m[ 3 ] = intval( $m[ 3 ] );
				if ( $m[ 3 ] >= 0 && $m[ 3 ] <= 99 ) {
					$m[ 3 ]+= 2000 ;
				}

				$dc = intval( date( 't' , mktime( 0 , 0 , 0 , $m[ 2 ] , 1 , $m[ 3 ] ) ) );
				if ( $dc >= $m[ 1 ] ) {
					$cds = true ;
					$ds = $m ;
				}
			}

			$m = array();
			$n = preg_match( '/^\s*([0-1]\d|2[0-3])[.,-\:]([0-5]\d)\s*/' , $ts , $m );
			$cts = false ;
			if ( $n == 1 ) {
				$m[ 1 ] = intval( $m[ 1 ] );
				$m[ 2 ] = intval( $m[ 2 ] );
				$cts = true ;
				$ts = $m ;
			}

			if ( !isset( $tabUsableMaterials[ $sID ] ) ) {
				echo '<result state="err">Материал не готов к использованию</result>' ;
			} else
			if ( $cds && $cts ) {
				$mDate = mktime( $ts[ 1 ] , $ts[ 2 ] , 0 , $ds[ 2 ] , $ds[ 1 ] , $ds[ 3 ] );
				$portalDB->noResult( "insert into `exp-substances-usage` ( `ext_id` , `s_id` , `date` , `n_id` , `count` , `comment` ) values ( ? , ? , ? , ? , ? , ? )" , 'iiiiis' , $extID , $sID , $mDate , $dd2l , $mCount , $dd2ta );
				$rowID = $portalDB->lastInsertID();
				echo '<result state="ok" row-id="'.$rowID.'" ds="'.date( 'd-m-Y' , $mDate ).'" ts="'.date( 'H:i' , $mDate ).'" u="'.$tabUsableMaterials[ $sID ][ 'unit' ].'">'.toCDATA( $dd2ta ).'</result>' ;
			} else {
				echo '<result state="err">Неверный формат даты</result>' ;
			}

			break ;

		case "add-subpoena-addressee" :
			//$tabUsableMaterials = $portalDB->query( "select `t2`.`id` , `t1`.`unit` from `substances` as `t1` , `substances-in-stock` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( not ( `t2`.`state` <=> -1 ) );" , "id" );

			$extID = intval( $data->getAttribute( "extid" ) );
			$sID = intval( $data->getAttribute( "s" ) );
			$pr = $data->getAttribute( "pr" );

			$pa = "" ;
			$cmt = "" ;
			foreach ( $data->childNodes as $cn ) {
				switch( $cn->nodeName ) {
					case "pa" :
					case "cmt" :
						$vn = $cn->nodeName ;
						$$vn = iconv( "utf8" , "cp1251" , $cn->nodeValue );
						break ;
				}
			}

			$pr = str_replace( "," , "." , trim( $pr ) );
			$m = array();
			$n = preg_match( "/(\\d+)(?:\\.(\\d+))?/" , $pr , $m );
			if ( $n == 1 ) {
				$pr = intval( $m[ 1 ] ) * 100 ;
				if ( count( $m ) == 3  ) {
					$pr+= intval( $m[ 2 ] );
				}
			} else {
				$pr = 0 ;
			}

			$portalDB->noResult( "insert into `payments` ( `expertize_id` , `state` , `create_date` , `type` , `check_date` ) values ( ? , 0 , ? , 1 , 0 )" , "ii" , $extID , time() );
			$lid = $portalDB->lastInsertID();
			$portalDB->noResult( "insert into `subpoena-addressee` ( `s_id` , `p_id` , `payer` , `comment` , `price` ) values ( ? , ? , ? , ? , ? )" , "iissi" , $sID , $lid , $pa , $cmt , $pr );
			echo "<result state=\"ok\" pr=\"".money_format( "%!i" , round( $pr / 100.0 , 2 ) )."\" />" ;

			break ;

		case "change-subpoena-addressee" :
			//$tabUsableMaterials = $portalDB->query( "select `t2`.`id` , `t1`.`unit` from `substances` as `t1` , `substances-in-stock` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( not ( `t2`.`state` <=> -1 ) );" , "id" );

			$extID = intval( $data->getAttribute( "extid" ) );
			$sID = intval( $data->getAttribute( "s" ) );
			$pr = $data->getAttribute( "pr" );

			$pa = "" ;
			$cmt = "" ;
			foreach ( $data->childNodes as $cn ) {
				switch( $cn->nodeName ) {
					case "pa" :
					case "cmt" :
						$vn = $cn->nodeName ;
						$$vn = iconv( "utf8" , "cp1251" , $cn->nodeValue );
						break ;
				}
			}

			$pr = str_replace( "," , "." , trim( $pr ) );
			$m = array();
			$n = preg_match( "/(\\d+)(?:\\.(\\d+))?/" , $pr , $m );
			if ( $n == 1 ) {
				$pr = intval( $m[ 1 ] ) * 100 ;
				if ( count( $m ) == 3  ) {
					$pr+= intval( $m[ 2 ] );
				}
			} else {
				$pr = 0 ;
			}

			$portalDB->noResult( "insert into `payments` ( `expertize_id` , `state` , `create_date` , `type` , `check_date` ) values ( ? , 0 , ? , 1 , 0 )" , "ii" , $extID , time() );
			$lid = $portalDB->lastInsertID();
			$portalDB->noResult( "insert into `subpoena-addressee` ( `s_id` , `p_id` , `payer` , `comment` , `price` ) values ( ? , ? , ? , ? , ? )" , "iissi" , $sID , $lid , $pa , $cmt , $pr );
			echo "<result state=\"ok\" pr=\"".money_format( "%!i" , round( $pr / 100.0 , 2 ) )."\" />" ;

			break ;

		case 'delete-item' :
			$tabNameMap = array(
				'eul' => 'exp-equipment-usage' ,
				//"ml" => "exp-substances-usage" ,
				'pl' => 'subpoena-addressee'
			);
			$tabName = $data->getAttribute( 'dlgName' );

			if ( !isset( $tabNameMap[ $tabName ] ) ) {
				echo '<result state="err"></result>' ;
				break ;
			}

			$itemID = intval( $data->getAttribute( 'id' ) );

			$tabDBName = $tabNameMap[ $tabName ];

			$itemData = $portalDB->simpleRow( $tabDBName , $itemID );
			switch( $tabName ) {
				case 'pl' :
					$paymentsData = $portalDB->simpleRow( 'payments', $itemData[ 'p_id' ] );
					if ( $paymentsData === false ) {
						echo '<result state="err"></result>' ;
						break ;
					}

					if ( $paymentsData[ 'state' ] == 1 || $paymentsData[ 'type' ] != 1 ) {
						echo '<result state="err"></result>' ;
						break ;
					}

					$portalDB->deleteRow( 'payments' , $paymentsData[ 'id' ] );
					$portalDB->deleteRow( $tabDBName , $itemID );
					echo '<result state="ok"></result>' ;
					break ;

				case 'eul' :
					$portalDB->deleteRow( $tabDBName , $itemID );
					echo '<result state="ok"></result>' ;
					break ;

				default :
					break ;
			}
			break ;
	}
