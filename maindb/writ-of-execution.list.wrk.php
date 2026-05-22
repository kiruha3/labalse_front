<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	$mayWOEEdit = false ;

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "PAYMENTS" , $Rights ) ) {
			$mayWOEEdit = in_array( "WOE-EDIT" , $Rights[ "PAYMENTS" ] );
			$GoOut = false ;
		} else {
			$mayWOEEdit = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	function getOut() {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}

	if ( $GoOut ) {
		getOut();
	}

	$queryTables = array(
		"`writ-of-execution-list` as `t1` , `matincoming` as `t2`"
	);
	$queryCondition = array(
		"( `t2`.`id` = `t1`.`ext_id` )"
	);
	$queryGroup = false ;
	$queryOrder = "`num` asc" ;

	if ( isset( $_REQUEST[ "idlist" ] ) ) {
		$idlist = explode( "," , $_REQUEST[ "idlist" ] );
		$resIDList = array();
		foreach( $idlist as &$idl ) {
			$idl = trim( $idl );
			if ( strlen( $idl ) > 0 ) {
				$idl = intval( $idl );
				$resIDList[ $idl ] = $idl ;
			}
		} unset( $idl );
		if ( count( $resIDList ) > 0 ) {
			$queryCondition[]= "( `t1`.`id` in ( ".implode( "," , $resIDList )." ) )" ;
		} else {
			$queryCondition[]= "( 0 )" ;
		}
	}

	$res = $portalDB->query( "select `t1`.* , ifnull( `t2`.`group_id` , 0 ) as `group_id` from ".implode( " , " , $queryTables )." where ".implode( " and " , $queryCondition ).( $queryOrder !== false ? " order by ".$queryOrder : "" ) , "id" );
	$payers = $portalDB->simpleQuery( "writ-of-execution-payers" , array( "ext_id" => array_keys( $res ) ) );

	$payersMap = array();

	$l1idl = array();
	$l1grl = array();
	foreach( $res as &$woe ) {
		$woe[ "from" ] = htmlspecialchars( $woe[ "agency" ].", ".$woe[ "agent" ].", ".$woe[ "case_num" ] , ENT_QUOTES , "cp1251" );
		if ( $woe[ "group_id" ] != 0 ) {
			$l1grl[]= $woe[ "group_id" ];
		}
		$l1idl[]= $woe[ "ext_id" ];
		$payersMap[ $woe[ "id" ] ] = array();
	} unset( $woe );
	$l1idl = array_unique( $l1idl );
	$l1grl = array_unique( $l1grl );

	foreach ( $payers as &$cp ) {
		$payersMap[ $cp[ "ext_id" ] ][]= &$cp ;
	} unset( $cp );

	$l1res = $portalDB->query( "select * from `matincoming` where ( `group_id` in ( ?* ) ) or ( `id` in ( ?* ) ) order by `id` desc" , "id" , "*i*s" , $l1grl , $l1idl );
	$l2l3res = $portalDB->query( "select `t2`.`mat_id` , `t3`.* from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t2`.`mat_id` in ( ?* ) ) and ( `t2`.`id` = `t3`.`ext_id` )" , "id" , "*s" , array_keys( $l1res ) );

	$tabWorkers = $portalDB->table( "workers" , "id" );
	$lastWorkers = $portalDB->query( "select max( `id` ) as `id` , `first_id` from `workers` group by `first_id`" , "first_id" );
	foreach ( $lastWorkers as &$cw ) {
		$pn = NAMES_parse( $tabWorkers[ $cw[ "id" ] ][ "name" ] );
		$cw[ "id.name" ] = NAMES_Format( $pn , "%F1 %I1 %O1" )."#".$cw[ "first_id" ];
		$cw[ "name" ] = NAMES_Format( $pn , "%F1 %i.%o." );
	} unset( $cw );

	$tabMarksCatalog = $portalDB->table( "marks-catalog" , "id" );

	$widMap = array();
	$widRevMap = array();
	foreach ( $l2l3res as &$l2l3c ) {
		$wid = $l2l3c[ "exp_id" ];
		$wid = $tabWorkers[ $wid ][ "first_id" ];
		if ( !isset( $widMap[ $wid ] ) ) {
			$widMap[ $wid ] = array();
		}
		$widMap[ $wid ][]= &$l2l3c ;

		$l1cid = $l2l3c[ "mat_id" ];
		if ( !isset( $widRevMap[ $l1cid ] ) ) {
			$widRevMap[ $l1cid ] = array();
		}
		$widRevMap[ $l1cid ][]= $wid ;
	} unset( $l2l3c );

	ksort( $widMap );
	//print_r_html( $widRevMap );


	$l1GroupMap = array();
	foreach( $l1res as &$l1c ) {
		$l1cg = $l1c[ "group_id" ];
		if ( !isset( $l1GroupMap[ $l1cg ] ) ) {
			$l1GroupMap[ $l1cg ] = array();
		}
		$l1GroupMap[ $l1cg ][]= $l1c[ "id" ];
	} unset( $l1c );

	$wrkResMap = array();

	foreach( $res as &$cres ) {
		if ( $cres[ "group_id" ] == 0 ) {
			$wrk = $widRevMap[ $cres[ "ext_id" ] ];
		} else {
			$wrk = array();
			foreach ( $l1GroupMap[ $cres[ "group_id" ] ] as $id ) {
				if ( isset( $widRevMap[ $id ] ) ) {
					$wrk = array_merge( $wrk , $widRevMap[ $id ] );
				}
			}
		}

		$wrk = array_unique( $wrk );
		foreach( $wrk as $wid ) {
			$wnid = $lastWorkers[ $wid ][ "id.name" ];
			if ( !isset( $wrkResMap[ $wnid ] ) ) {
				$wrkResMap[ $wnid ] = array( "id" => $wid , "list" => array() );
			}
			$wrkResMap[ $wnid ][ "list" ][ $cres[ "num" ] ]= &$cres ;
		}
	} unset( $cres );

	MainHead_L2( "База" , "<a href=\"./\">База</a> - Исполнительный Лист" , array( "../%UT/buttons.css" , "%UT/writ-of-execution.list.css" ) , array( "files/writ-of-execution.list.js" ) , "hlp/main.html" );

	$flt = makeSimpleTable_init_filter();

	function makeExpLnk( $id ) {
		global $l1res ;
		$l1c = $l1res[ $id ];
		return "<a href=\"main.php?idlist=".$id."\" class=\"woe-exp-lnk\" target=\"_blank\">".matincomingNumber( $l1c[ "id" ] )." / ".matincomingYear( $l1c[ "id" ] )."</a>" ;
	}

	function makeWrkLnk( $id ) {
		return "" ;
	}

	$flt[ "marks" ] = function( &$r , $c , $v ) use( $tabMarksCatalog ) {
		if ( !is_null( $v ) ) {
			return Marks\integrate( explode( "," , $v ) , array( "mode" => "label" ) , $tabMarksCatalog );
		} else {
			return "" ;
		}
	};

	$flt[ "woe_lnk" ] = function( &$r , $c , $v ) {
		global $mayWOEEdit ;
		if ( $mayWOEEdit ) {
			return "<a href=\"writ-of-execution.php?edit=".$r[ "id" ]."\" class=\"woe-num-lnk\" target=\"_blank\">".$v."</a>" ;
		} else {
			return "<a href=\"writ-of-execution.php?show=".$r[ "id" ]."\" class=\"woe-num-lnk\" target=\"_blank\">".$v."</a>" ;
		}
	};
	$flt[ "payers" ] = function( &$r , $c , $v ) {
		global $payersMap ;
		$res = array();
		foreach ( $payersMap[ $v ] as &$p ) {
			$res[]= "<div class=\"payer-data\"><span class=\"payer-name\" title=\"".htmlspecialchars( $p[ "payer" ] , ENT_QUOTES , "cp1251" )."\">".$p[ "payer" ]."</span><span class=\"payer-price\">".money_format( "%!i" , $p[ "price" ] )."</span></div>" ;
		} unset( $p );
		return implode( "" , $res );
	};
	$flt[ "price" ] = function( &$r , $c , $v ) {
		global $payersMap ;
		$res = 0 ;
		foreach ( $payersMap[ $v ] as &$p ) {
			$res += $p[ "price" ];
		} unset( $p );
		return money_format( "%!i" , $res );
	};
	$flt[ "woe_state" ] = function( &$r , $c , $v ) {
		if ( $v ) {
			return "<div class=\"woe-state-ok\" title=\"Закрыт ".date( "d-m-Y" , $r[ "state_date" ] )."\"></div>";
		} else {
			return "" ;
		}
	};
	$flt[ "exp_lnk" ] = function( &$r , $c , $v ) use( $l1GroupMap ) {
		if ( $r[ "group_id" ] == 0 ) {
			return makeExpLnk( $v );
		} else {
			$res = array();
			foreach ( $l1GroupMap[ $r[ "group_id" ] ] as $id ) {
				$res[]= makeExpLnk( $id );
			}
			return implode( "<br>" , $res );
		}
	};
	$flt[ "wrk_lnk" ] = function( &$r , $c , $v ) use( $l1GroupMap , $lastWorkers , $widRevMap ) {
		if ( $r[ "group_id" ] == 0 ) {
			$wrk = $widRevMap[ $v ];
		} else {
			$wrk = array();
			foreach ( $l1GroupMap[ $r[ "group_id" ] ] as $id ) {
				if ( isset( $widRevMap[ $id ] ) ) {
					$wrk = array_merge( $wrk , $widRevMap[ $id ] );
				}
			}
		}

		$wrk = array_unique( $wrk );
		$res = array();
		foreach( $wrk as $wid ) {
			$wnid = $lastWorkers[ $wid ][ "id.name" ];
			$res[ $wnid ] = $lastWorkers[ $wid ][ "name" ];
		}

		ksort( $res );
		return implode( "<br>" , $res );
		//return makeWrkLnk( $v );
	};

	ksort( $wrkResMap );

	//print_r_html( $wrkResMap );

	echo "<div class=\"wrk-woe-lists-area\">" ;
	foreach ( $wrkResMap as $cres ) {
		echo "<div class=\"wrk-woe-list-area\"><div class=\"wrk-name-woe-list\">".$lastWorkers[ $cres[ "id" ] ][ "name" ]."</div>" ;
		echo makeSimpleTable(
			'[]' ,
			'[ { "t" : 1 } ]' ,
			'[ { "n" : "marks" , "t" : "S64" , "h" : [ { "d" : "" } ] , "f" : "marks" , "s" : "std-marks-col" } ,'
			.' { "n" : "num" , "t" : "s128" , "h" : [ { "d" : "Серия" } ] , "f" : "woe_lnk" , "s" : "woe-num" } ,'
			.' { "n" : "state" , "t" : "n" , "h" : [ { "d" : "" } ] , "f" : "woe_state" , "s" : "woe-state" } ,'
			.' { "n" : "ext_id" , "t" : "S128" , "h" : [ { "d" : "По экспертизе" } ] , "f" : "exp_lnk" , "s" : "woe-exp" } ,'
			.' { "n" : "ext_id" , "t" : "S160" , "h" : [ { "d" : "Эксперты" } ] , "f" : "wrk_lnk" } ,'
			.' { "n" : "from" , "t" : "S384" , "h" : [ { "d" : "Выписал" } ] } ,'
			.' { "n" : "date" , "t" : "d" , "h" : [ { "d" : "Дата" } ] } ,'
			.' { "n" : "id" , "t" : "Sf" , "h" : [ { "d" : "Плательщики и сумма" } ] , "f" : "payers" } ,'
			.' { "n" : "id" , "t" : "p" , "h" : [ { "d" : "Общая сумма по И/Л" } ] , "f" : "price" } ]' ,
			$cres[ "list" ] , array( "dr" => "dr-d" ) , $flt
		);
		echo "</div>" ;
	}
	echo "</div>" ;

	fixTimerData( "core" );

	closeHtml();
?>