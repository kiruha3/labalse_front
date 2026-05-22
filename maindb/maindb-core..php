<?php
	/*
	    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
	    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
	    copyright (c) Пекшев Петр Александрович, 2008
	*/

	$paymentsStyles = array(
		"unchecked" => array(
			"simple" => array( "chk_btn" , "exp_number" , "worker" , "price" , "sndz" , "pay_date" , "pay_details" , "comment" , "marks" , "application_for_issuance" ) ,
			"extended" => array( "exp_number" , "worker" , "price" , "from" , "pay_date" , "pay_details" , "comment" )
		) ,
		"checked" => array(
			"simple" => array( "check_date" , "chk_btn" , "exp_number" , "worker" , "price" , "pay_date" , "pay_details" , "comment" , "marks" , "application_for_issuance" ) ,
			"extended" => array( "exp_number" , "worker" , "price" , "from" , "pay_date" , "pay_details" , "comment" )
		)
	);

	define( "DOCTYPE_MATINCOMING" , '0110' );
	define( "DOCTYPE_PREREG"      , '0190' );
	define( "DOCTYPE_ARCHIVE" , '0010' );
	define( "PAT_MATINCOMING_ID" , '/^'.VERSION_CHAR_ID.'\.\d{3}\.'.DOCTYPE_MATINCOMING.'\.20\d{8}$/' );

	function matincomingNumber( $id ) {
		$fullID = "".$id ;
		$n = preg_match( PAT_MATINCOMING_ID , $fullID );
		if ( $n == 1 ) {
			return intval( substr( $fullID , -6 ) );
		} else {
			return false ;
		}
	}

	function matincomingYear( $id ) {
		$fullID = "".$id ;
		$n = preg_match( PAT_MATINCOMING_ID , $fullID );
		if ( $n == 1 ) {
			return intval( substr( $fullID , -10 , 4 ) );
		} else {
			return false ;
		}
	}

	function matincomingID( $number , $year , $region = false ) {
		global $UserOrgIndex ;
		if ( $region === false ) {
			$pReg = ''.$UserOrgIndex ;
		} else {
			$pReg = ''.$region ;
		}
		return VERSION_CHAR_ID.".".$pReg.".".DOCTYPE_MATINCOMING.".".( $year * 1000000 + $number );
	}

	function subpoenaNumber( $id ) {
		return intval( $id ) % 1000000 ;
	}

	function subpoenaYear( $id ) {
		return intval( $id / 1000000 );
	}

	function subpoenaID( $number , $year ) {
		return $year * 1000000 + $number ;
	}

	function subpoenaIDBase( $year ) {
		return $year * 1000000 ;
	}

	function getDocTgtSubDir( $barCode ) {
		global $dbConfig ;
		$bcta = array(
			array(
				"p" => '/^(?<type>[0-1][0-3]0)(?:(?<year>\d)(?<l1>\d{3})(?<l0>\d{3}))?$/' ,
				"f" => function( $s , $m ) {
					global $dbConfig ;
					if ( isset( $m[ "year" ] ) && isset( $m[ "l1" ] ) && isset( $m[ "l0" ] ) ) {
						return "/".$dbConfig[ "org.docEAIndex" ]."/".( intval( $m[ "year" ] ) + 2008 )."/".$m[ "l1" ]."/".$m[ "l0" ];
					} else {
						return $dbConfig[ "docEA.unknownBarCode.tgtDir" ];
					}
				}
			)
		);

		foreach( $bcta as $p ) {
			$m = array();
			$n = preg_match( $p[ "p" ] , $barCode , $m );
			if ( $n == 1 ) {
				$f = $p[ "f" ];
				return $f( $barCode , $m );
			}
		}
		return $dbConfig[ "docEA.unknownBarCode.tgtDir" ];
	}

	function storeAgentData( TDB $db , $toa , $agency , $agent = false , $contacts = array() ) {

		$agency = clearText( $agency );
		if ( $agency == "" ) {
			return false ;
		}

		if ( $agent !== false ) {
			$agent = clearText( $agent );
		}

		$res = array(
			"toa" => $toa ,
			"agency" => $agency ,
			"agency.id" => false ,
			"agent" => $agent ,
			"agent.id" => false ,
			"contacts" => array()
		);

		$ayid = $db->query( "select * from `agency` where ( `ext_id` = ? ) and ( `name` = ? )" , false , "is" , $toa , $agency );
		if ( $ayid === false || count( $ayid ) == 0 ) {
			$db->noResult( "insert into `agency` ( `ext_id` , `name` ) values ( ? , ? )" , "is" , $toa , $agency );
			$ayid = $db->lastInsertID();
		} else {
			$ayid = $ayid[ 0 ][ "id" ];
		}

		$res[ "agency.id" ] = $ayid ;

		if ( $agent === false || $agent == "" ) {
			return $res ;
		}

		$atid = $db->query( "select * from `agent` where ( `ext_id` = ? ) and ( `name` = ? )" , false , "is" , $ayid , $agent );
		if ( $atid === false || count( $atid ) == 0 ) {
			$db->noResult( "insert into `agent` ( `ext_id` , `name` , `_fr` ) values ( ? , ? , 1 )" , "is" , $ayid , $agent );
			$atid = $db->lastInsertID();
		} else {
			$atid = $atid[ 0 ][ "id" ];
		}

		$res[ "agent.id" ] = $atid ;

		if ( $contacts === false || count( $contacts ) == 0 ) {
			return $res ;
		}

		foreach( $contacts as $cc ) {
			$cc[ "value" ] = clearText( $cc[ "value" ] );
			$cid = $db->query( "select * from `agent-contacts` where ( `ext_id` = ? ) and ( `type` = ? ) and ( `value` = ? )" , false , "iis" , $atid , $cc[ "type" ] , $cc[ "value" ] );
			if ( $cid === false || count( $cid ) == 0 ) {
				$db->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) values ( ? , ? , ? , 1 )" , "iis" , $atid , $cc[ "type" ] , $cc[ "value" ] );
				$cid = $db->lastInsertID();
			} else {
				$cid = $cid[ 0 ][ "id" ];
			}

			$cc[ "id" ] = $cid ;
			$res[ "contacts" ][ $cid ]= $cc ;
		}

		return $res ;
	}

	function storeEvidenceData( $l1cid , $date , $descr ) {
		global $portalDB ;
		$en = array(); // evidence new elements
		foreach ( $date as $k => $v ) {
			if ( !isset( $en[ $k ] ) ) {
				$en[ $k ] = array();
			}
			if ( isValidDate( $v ) ) {
				$en[ $k ][ "date" ] = Date2Int( $v );
			}
		}

		foreach ( $descr as $k => $v ) {
			if ( !isset( $en[ $k ] ) ) {
				$en[ $k ] = array();
			}
			$en[ $k ][ "descr" ] = clearText( $v );
		}

		foreach ( $en as $v ) {
			if ( isset( $v[ "date" ] ) && isset( $v[ "descr" ] ) ) {
				$portalDB->noResult( "insert into `evidence` ( `ext_id` , `descr` , `inc_date` ) values ( ? , ? , ? )" , "ssi" , $l1cid , $v[ "descr" ] , $v[ "date" ] );
			}
		}
	}

	function storeExpertizeComment( $eid , $comment ) {
		global $portalDB , $UserAllWorkers , $UserWorkerID ;

		$comment = clearText( $comment , true );
		if ( strlen( $comment ) > 0 ) {
			$userComments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = 'expertize' ) and ( `ext_id` = ? ) and ( `exp_id` in ( ?* ) )" , false , "i*i" , $eid , $UserAllWorkers );
			if ( $userComments === false || count( $userComments ) == 0 ) {
				$portalDB->noResult( "insert into `expertize-comments` ( `ext_type` , `ext_id` , `date` , `exp_id` , `comment` ) values ( 'expertize' , ? , ? , ? , ? )" , "iiis" , $eid , time() , $UserWorkerID , $comment );
			} else {
				$portalDB->noResult( "update `expertize-comments` set `date` = ? , `exp_id` = ? , `comment` = ? where ( `id` = ? )" , "iisi" , time() , $UserWorkerID , $comment , $userComments[ 0 ][ "id" ] );
				if ( count( $userComments ) > 1 ) {
					$ustd = array();
					for( $i = 1 ; $i < count( $userComments ) ; $i++ ) {
						$ustd[]= $userComments[ $i ][ "id" ];
					}
					$portalDB->noResult( "delete from `expertize-comments` where ( `id` in ( ?* ) )" , "*i" , $ustd );
				}
			}
		} else {
			$portalDB->noResult( "delete from `expertize-comments` where ( `ext_type` = 'expertize' ) and ( `ext_id` = ? ) and ( `exp_id` in ( ?* ) )" , "i*i" , $eid , $UserAllWorkers );
		}
	}

	function getAllWorkersIDL( $id ) {
		global $portalDB ;
		$wd = $portalDB->row( "select * from `workers` where `id` = ?" , "i" , $id );
		if ( $wd !== false && !is_null( $wd[ "first_id" ] ) ) {
			$res = $portalDB->query( "select `id` from `workers` where `first_id` = ?" , "id" , "i" , $wd[ "first_id" ] );
			if ( $res !== false && count( $res ) > 0 ) {
                return array_keys( $res );
			} else {
				return false ;
			}
		} else {
			return false ;
		}
	}

	function getMatincomingIDGroup( $id ) {
		global $portalDB ;
		$id = getCharID( $id , DOCTYPE_MATINCOMING );
		if ( $id === false ) {
			return false ;
		}

		$r = $portalDB->simpleRow( "matincoming" , $id );
		if ( $r === false ) {
			return false ;
		}
		if ( is_null( $r[ "group_id" ] ) || $r[ "group_id" ] == 0 ) {
			return array( $id );
		}

		$r = $portalDB->simpleQuery( "matincoming" , array( "group_id" => $r[ "group_id" ] ) , "id" );
		if ( $r === false ) {
			return false ;
		}

		return array_keys( $r );
	}
?>