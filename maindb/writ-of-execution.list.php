<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	/**
	 * @var TDB $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $dbConfig
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

	if ( isset( $_REQUEST[ 'dbg' ] ) && 1 ) {
		$portalDB->dbgMode = true ;
	}

	/*$queryTables = array(
		"`writ-of-execution-list` as `t1` , `matincoming` as `t2` "
	);*/

	$portalDB->noResult( "create temporary table `0.idList` ( `id` int(11) NOT NULL );" );
	$portalDB->noResult( "create temporary table `0.mList` ( `id` int(11) NOT NULL );" );
	$portalDB->noResult( "create temporary table `0.sList` ( `id` int(11) NULL );" );

	$queryTables = array(
		"`2.result` as `t1` , `matincoming` as `t2` "
	);

	$queryCondition = array(
		"( `t2`.`id` = `t1`.`ext_id` )"
	);
	$queryGroup = false ;
	$queryOrder = "`num` asc" ;

	$resIDList = false ;
	if ( isset( $_REQUEST[ "idlist" ] ) ) {
		$resIDList = array();
		$idlist = explode( "," , $_REQUEST[ "idlist" ] );

		foreach( $idlist as &$idl ) {
			$idl = trim( $idl );
			if ( strlen( $idl ) > 0 ) {
				$idl = intval( $idl );
				$resIDList[ $idl ] = $idl ;
			}
		} unset( $idl );
		if ( count( $resIDList ) > 0 ) {
			//$queryCondition[]= "( `t1`.`id` in ( ".implode( "," , $resIDList )." ) )" ;
			$portalDB->noResult( "insert into `0.idList` values (".implode( "),(", $resIDList ).");" );
		} else {
			$queryCondition[]= "( 0 )" ;
			$portalDB->noResult( "insert into `0.idList` values ( -1 );" );
		}
	} else {
		$portalDB->noResult( "insert into `0.idList` select `id` from `writ-of-execution`;" );
	}

	if ( isset( $_REQUEST[ "marks" ] ) ) {
		if ( is_string( $_REQUEST[ "marks" ] ) ) {
			$marks = explode( "," , $_REQUEST[ "marks" ] );
		} else {
			$marks = $_REQUEST[ "marks" ];
		}

		$marksOpAnd = isset( $_REQUEST[ "marks-op" ] ) && strtolower( $_REQUEST[ "marks-op" ] ) == "and" ;

		if ( count( $marks ) > 1 && $marksOpAnd ) {
			$mol = Marks\getObjectsIDAll( $marks , "woe" );
			$marksOpAnd = 'and' ;
		} else {
			$mol = Marks\getObjectsIDAny( $marks , "woe" );
			$marksOpAnd = 'or' ;
		}

		/*if ( count( $mol ) > 0 ) {
			//$queryCondition[]= "( `t1`.`id` in ( ".implode( "," , $mol )." ) )" ;
			$portalDB->noResult( "insert into `0.mList` values (".implode( "),(", $mol ).");" );
		} else {
			$queryCondition[]= "( 0 )" ;
			$portalDB->noResult( "insert into `0.mList` values ( -1 );" );
		}*/

		if ( count( $marks ) > 0 ) {
			//$queryCondition[]= "( `t1`.`id` in ( ".implode( "," , $mol )." ) )" ;
			$portalDB->noResult( "insert into `0.mList` values (".implode( "),(", $marks ).");" );
		} else {
			$queryCondition[]= "( 0 )" ;
			$portalDB->noResult( "insert into `0.mList` values ( -1 );" );
		}

		/*$mflt = array();
		foreach( $marks as $mid ) {
			$mflt[]= "( instr( concat( ',' , `t1`.`marks` , ',' ) ,  ',".trim( $mid ).",' ) > 0 )" ;
		}

		if ( count( $mflt ) > 0 ) {
			$queryCondition[]= "( ".implode( " or " , $mflt )." )" ;
		}*/
	} else {
		$marks = array();
		$marksOpAnd = false ;
	}

	if ( !isset( $_REQUEST[ "closed" ] ) && !isset( $_REQUEST[ "opened" ] ) ) {
		$fltOpened = $fltClosed = true ;
	} else {
		$fltOpened = isset( $_REQUEST[ "opened" ] ) && $_REQUEST[ "opened" ] == 1 ;
		$fltClosed = isset( $_REQUEST[ "closed" ] ) && $_REQUEST[ "closed" ] == 1 ;
	}

	if ( $fltClosed && $fltOpened ) {
		$portalDB->noResult( "insert into `0.sList` values ( null ),( 0 ),( 1 );" );
	} else
	if ( $fltClosed ) {
		$portalDB->noResult( "insert into `0.sList` values ( 1 );" );
	} else
	if ( $fltOpened ) {
		$portalDB->noResult( "insert into `0.sList` values ( null ),( 0 );" );
	} else {
		$queryCondition[]= "( 0 )" ;
	}

	//$portalDB->noResult( "call `select-woe-marks`( 'woe' );" );
	$portalDB->noResult( "alter table `0.idList` ADD PRIMARY KEY ( `id` );" );
	$portalDB->noResult( "alter table `0.mList` ADD PRIMARY KEY ( `id` );" );
	$portalDB->noResult( "alter table `0.sList` ADD UNIQUE ( `id` );" );
	$portalDB->noResult( "call `select-woe-marks-2`( ".( $marksOpAnd === false ? "'none'" : "'".$marksOpAnd."'" )." );" );
	$res = $portalDB->query( "select `t1`.* , ifnull( `t2`.`group_id` , 0 ) as `group_id` from ".implode( " , " , $queryTables )." where ".implode( " and " , $queryCondition ).( $queryOrder !== false ? " order by ".$queryOrder : "" ) , "id" );
	$payers = $portalDB->query( "select * from `writ-of-execution-payers` where ( `ext_id` in ( ?* ) )" , "id" , "*s" , array_keys( $res ) );
	$woeShortNum = array();
	$woeShortNumMap = array();
	foreach ( $res as &$cw ) {
		$wsn = substr( $cw[ 'num' ] , -9 );
		$woeShortNum[]= $wsn ;
		$woeShortNumMap[ $wsn ] = $cw[ 'id' ];
	} unset( $cw );
	//( MATCH( `t1`.`description`,`t1`.`name` ) against( '036961838 036961985' ) ) and ( `t2`.`ext_id` = `t1`.`id` ) and ( `t3`.`id` = `t2`.`tgt` ) and ( `t3`.`ext_id` = `t4`.`id` )
	$corr = $portalDB->query( "SELECT `t1`.* , `t4`.`name` as `agency` , `t3`.`name` as `agent` FROM `correspondence-main` as `t1` , `correspondence-target` as `t2` , `agent` as `t3` , `agency` as `t4` WHERE  ( MATCH( `t1`.`name` , `t1`.`description` ) AGAINST( '".implode( ' ' , $woeShortNum )."' ) ) and ( `t1`.`type` in ( 2 , 8 ) ) and ( `t2`.`ext_id` = `t1`.`id` ) and ( `t3`.`id` = `t2`.`tgt` ) and ( `t3`.`ext_id` = `t4`.`id` )" );
	foreach ( $corr as &$ccorr ) {
		$n1 = preg_match_all( '/\D(\d{9})(?:\D|$)/' , $ccorr[ 'name' ] , $m1 );
		$n2 = preg_match_all( '/\D(\d{9})(?:\D|$)/' , $ccorr[ 'description' ] , $m2 );
		$woeID = false ;
		$arr = array();

		if ( count( $m1[ 1 ] ) > 0 ) {
			$arr = array_merge( $arr , $m1[ 1 ] );
		}
		if ( count( $m2[ 1 ] ) > 0 ) {
			$arr = array_merge( $arr , $m2[ 1 ] );
		}

		foreach ( $arr as $wsn ) {
			if ( isset( $woeShortNumMap[ $wsn ] ) ) {
				$woeID = $woeShortNumMap[ $wsn ];
				if ( $woeID !== false ) {
					if ( !isset( $res[ $woeID ][ '___corr' ] ) ) {
						$res[ $woeID ][ '___corr' ] = $ccorr;
					} else {
						if ( $res[ $woeID ][ '___corr' ][ 'date' ] < $ccorr[ 'date' ] ) {
							$res[ $woeID ][ '___corr' ] = $ccorr;
						}
					}
				}
			}
		}

	} unset( $ccorr );

	//print_r_html( $res );


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

	if ( count( $l1grl ) > 0 ) {
		$l1res = $portalDB->query( "select * from `matincoming` where ( `group_id` in ( ?* ) ) or ( `id` in ( ?* ) ) order by `id` desc" , "id" , "*i*s" , $l1grl , $l1idl );
	} else {
		$l1res = $portalDB->query( "select * from `matincoming` where ( `id` in ( ?* ) ) order by `id` desc" , "id" , "*s" , $l1idl );
	}
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

	MainHead_L2( "База" , "<a href=\"./\">База</a> - Исполнительный Лист" , array( "../%UT/buttons.css" , "%UT/writ-of-execution.list.css" ) , array( "files/writ-of-execution.list.js" ) , "hlp/main.html" );

	echo "<div><a href=\"writ-of-execution.list.wrk.php\" class=\"btn3\">ИЛ по экспертам</a> <a href=\"utils/woe-stat.php\" class=\"btn3\">стат ИЛ</a> <a href=\"utils/woe-check.php\" class=\"btn3\">Обработка выгрузки от приставов</a></div>" ;

	echo "<form id=\"search-form\" action=\"writ-of-execution.list.php".( $resIDList !== false ? '?idlist='.implode( ',' , $resIDList ) : '' )."\" method=\"post\">
		<div class=\"tools-panel\">
			<div class=\"rp\">
				<label for=\"rpa-tcb\" id=\"rpaa\" class=\"rpaa\" onclick=\"showRP()\">Фильтры</label>
				<input id=\"rpa-tcb\" type=\"checkbox\">
				<div id=\"rpa\" class=\"rpa\">
					<table>
						<tr>
							<td>
								<div>
									<label class=\"mo-scbl\"><input id=\"marks-op\" name=\"marks-op\" type=\"checkbox\" value=\"and\"".( $marksOpAnd ? " checked=\"checked\"" : "" )." class=\"mo-scb\"><div class=\"mo-sv v1\">Одновременно</div><div class=\"mo-sel\"></div><div class=\"mo-sv v2\">Независимо</div></label>
								</div>
								<div class=\"tp-marks-area\">".Marks\integrate( array( $dbConfig[ CFG_MARK_GROUP_WOE ] ) , array( "mode" => "edit" , "mark-name-attr" => "marks" , "checked-id-only" => true , "add-date-range-editor" => true , "integrate-date-range-w-id" => true ) , $marks )."</div>
							</td>
							<td>
								<label>Открытые <input name=\"opened\" type=\"checkbox\" value=\"1\"".( $fltOpened ? " checked=\"checked\"" : "" )."></label><br>
								<label>Закрытые <input name=\"closed\" type=\"checkbox\" value=\"1\"".( $fltClosed ? " checked=\"checked\"" : "" )."></label>
							</td>
						</tr>
					</table>
					<div><button class=\"btn3\" onclick=\"doFilter()\">Применить</button> ".( 1 == 1 ? "<button class=\"btn3\" onclick='doXLSXTable()'>таблица Excel</button>" : "" )."</div>
				</div>
			</div>
		</div>
	</form>" ;


	$flt = makeSimpleTable_init_filter();

	function makeExpLnk( $id ) {
		global $l1res ;
		$l1c = $l1res[ $id ];
		//$num = matincomingNumberFullParts(  )
		return "<a href=\"main.php?idlist=".$id."\" class=\"woe-exp-lnk\" target=\"_blank\">".matincomingNumber( $l1c[ "id" ] )." / ".matincomingYear( $l1c[ "id" ] )."</a>" ;
	}

	function makeWrkLnk( $id ) {
		return "" ;
	}

	$flt[ "marks" ] = function( &$r , $c , $v ) use( $tabMarksCatalog ) {
		if ( !is_null( $v ) && $v != '' ) {
			return Marks\integrate( explode( "," , $v ) , array( "mode" => "label" , "id-combined" => true , 'show-timestamp' => true ) , $tabMarksCatalog );
		} else {
			return '' ;
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

	$flt[ "outgoingCorrPayments" ] = function( &$r , $c , $v ) {
		if ( isset( $r[ '___corr' ] ) ) {
			$ccorr = $r[ '___corr' ];
			return '<a href="correspondence.php?view=any&idlist='.$ccorr[ 'id' ].'" class="woe-exp-lnk" target="_blank">'.date( 'd-m-Y' , $ccorr[ 'date' ] ).' '.$ccorr[ 'agency' ].', '.$ccorr[ 'agent' ].'</a>' ;
		} else {
			return '' ;
		}
	};

	echo "<div>Всего : ".count( $res )."</div>" ;

	echo makeSimpleTable(
		'[]' ,
		'[ { "t" : 1 } ]' ,
		'[ { "n" : "marks" , "t" : "S64" , "h" : [ { "d" : "" } ] , "f" : "marks" , "s" : "std-marks-col" } ,'
		.' { "n" : "num" , "t" : "ss" , "h" : [ { "d" : "Серия" } ] , "f" : "woe_lnk" , "s" : "woe-num" } ,'
		.' { "n" : "state" , "t" : "n" , "h" : [ { "d" : "" } ] , "f" : "woe_state" , "s" : "woe-state" } ,'
		.' { "n" : "ext_id" , "t" : "Ss" , "h" : [ { "d" : "По экспертизе" } ] , "f" : "exp_lnk" , "s" : "woe-exp" } ,'
		.' { "n" : "ext_id" , "t" : "Ss" , "h" : [ { "d" : "Эксперты" } ] , "f" : "wrk_lnk" } ,'
		.' { "n" : "from" , "t" : "Sl" , "h" : [ { "d" : "Выписал" } ] } ,'
		.' { "n" : "date" , "t" : "d" , "h" : [ { "d" : "Дата" } ] } ,'
		.' { "n" : "id" , "t" : "Sf" , "h" : [ { "d" : "Плательщики и сумма" } ] , "f" : "payers" } ,'
		.' { "n" : "id" , "t" : "p" , "h" : [ { "d" : "Общая сумма по И/Л" } ] , "f" : "price" } ,'
		.' { "n" : "id" , "t" : "Sl" , "h" : [ { "d" : "Последний исходящий" } ] , "f" : "outgoingCorrPayments" } ]' ,
		$res , array( "dr" => "dr-d" ) , $flt
	);

	//fixTimerData( "core" );

	closeHtml();
