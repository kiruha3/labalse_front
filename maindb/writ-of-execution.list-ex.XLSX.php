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
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );

	set_time_limit( 0 );

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
	$payments = $portalDB->query( "select * from `writ-of-execution-payments` where ( `ext_id` in ( ?* ) ) and ( `deleted` = 0 )" , "id" , "*s" , array_keys( $payers ) );
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
		$n11 = preg_match_all( '/\D(\d{9})\D/' , $ccorr[ 'name' ] , $m11 );
		$n12 = preg_match_all( '/\D(\d{9})$/' , $ccorr[ 'name' ] , $m12 );
		$n21 = preg_match_all( '/\D(\d{9})\D/' , $ccorr[ 'description' ] , $m21 );
		$n22 = preg_match_all( '/\D(\d{9})$/' , $ccorr[ 'description' ] , $m22 );
		$woeID = false ;
		$arr = array();

		foreach( array( $m11 , $m12 , $m21 , $m22 ) as $cm ) {
			if ( count( $cm[ 1 ] ) > 0 ) {
				$arr = array_merge( $arr , $cm[ 1 ] );
			}
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
	$paymentsMap = array();

	$l1idl = array();
	$l1grl = array();
	foreach( $res as &$woe ) {
		$woe[ "from" ] = htmlspecialchars( $woe[ "agency" ].", ".$woe[ "agent" ].", ".$woe[ "case_num" ] , ENT_QUOTES , "cp1251" );
		if ( $woe[ "group_id" ] != 0 ) {
			$l1grl[]= $woe[ "group_id" ];
		}
		$l1idl[]= $woe[ "ext_id" ];
		$payersMap[ $woe[ "id" ] ] = array();
		$paymentsMap[ $woe[ "id" ] ] = array();
	} unset( $woe );
	$l1idl = array_unique( $l1idl );
	$l1grl = array_unique( $l1grl );

	foreach ( $payers as &$cp ) {
		$payersMap[ $cp[ "ext_id" ] ][]= &$cp ;
	} unset( $cp );
	
	foreach ( $payments as &$cp ) {
		$cPrID = $cp[ "ext_id" ];
		$cPr = $payers[ $cPrID ];
		$cWoeID = $cPr[ 'ext_id' ];
		$paymentsMap[ $cWoeID ][]= &$cp ;
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

	$flt = makeSimpleTable_init_filter();

	function makeExpNum( $id ) {
		global $l1res ;
		$l1c = $l1res[ $id ];
		return matincomingNumber( $l1c[ "id" ] );
	}
	
	function makeExpYear( $id ) {
		global $l1res ;
		$l1c = $l1res[ $id ];
		return matincomingYear( $l1c[ "id" ] );
	}
	
	function makeWrkLnk( $id ) {
		return "" ;
	}
	
	$dateDelta = mktime( 0 , 0 , 0 , 12 , 30 , 1899 );

	$flt[ "marks" ] = function( &$r , $c , $v ) use( $tabMarksCatalog ) {
		if ( !is_null( $v ) && $v != '' ) {
			return Marks\integrate(
				explode( ',' , $v ) ,
				array(
					'mode' => 'text-quoted' ,
					'id-combined' => true ,
					'show-timestamp' => false ,
					'q-open' => '[' ,
					'q-close' => ']' ,
					'separator' => "\r\n"
				) ,
				$tabMarksCatalog
			);
		} else {
			return '' ;
		}
	};

	$flt[ 'woe_lnk' ] = function( &$r , $c , $v ) {
		return $v ;
	};
	
	$flt[ "payers" ] = function( &$r , $c , $v ) {
		global $payersMap ;
		$res = array();
		foreach ( $payersMap[ $v ] as &$p ) {
			$res[]= "<div class=\"payer-data\"><span class=\"payer-name\" title=\"".htmlspecialchars( $p[ "payer" ] , ENT_QUOTES , "cp1251" )."\">".$p[ "payer" ]."</span><span class=\"payer-price\">".money_format( "%!i" , $p[ "price" ] )."</span></div>" ;
		} unset( $p );
		return implode( "" , $res );
	};
	$flt[ "payer" ] = function( &$r , $c , $v ) {
		global $payersMap ;
		$res = array();
		foreach ( $payersMap[ $v ] as &$p ) {
			$res[]= $p[ "payer" ];
		} unset( $p );
		return implode( ', ' , $res );
	};
	$flt[ "price" ] = function( &$r , $c , $v ) {
		global $payersMap ;
		$res = 0.0 ;
		foreach ( $payersMap[ $v ] as &$p ) {
			$res += $p[ "price" ];
		} unset( $p );
		return $res ;
	};
	$flt[ "payed" ] = function( &$r , $c , $v ) {
		global $paymentsMap ;
		$res = 0.0 ;
		foreach ( $paymentsMap[ $v ] as &$p ) {
			$res += $p[ "price" ];
		} unset( $p );
		return $res ;
	};
	$flt[ "payed_c" ] = function( &$r , $c , $v ) {
		global $paymentsMap ;
		return count( $paymentsMap[ $v ] );
	};
	$flt[ "payed_last_size" ] = function( &$r , $c , $v ) {
		global $paymentsMap ;
		$payments = $paymentsMap[ $v ];
		if ( count( $payments ) > 0 ) {
			usort( $payments , function( $a , $b ) {
				return $b[ 'date' ] - $a[ 'date' ];
			} );
			return $payments[ 0 ][ 'price' ];
		} else {
			return '' ;
		}
	};
	$flt[ "payed_last_date" ] = function( &$r , $c , $v ) {
		global $paymentsMap , $dateDelta ;
		$payments = $paymentsMap[ $v ];
		if ( count( $payments ) > 0 ) {
			usort( $payments , function( $a , $b ) {
				return $b[ 'date' ] - $a[ 'date' ];
			} );
			return round( ( $payments[ 0 ][ 'date' ] - $dateDelta ) / 86400 , 0 );
		} else {
			return '' ;
		}
	};
	//$vFinDate * 86400 + $dateDelta
	$flt[ "woe_state" ] = function( &$r , $c , $v ) {
		if ( $v ) {
			return "Закрыт" ;
		} else {
			return "открыт" ;
		}
	};
	$flt[ "exp_num" ] = function( &$r , $c , $v ) use( $l1GroupMap ) {
		if ( $r[ "group_id" ] == 0 ) {
			return makeExpNum( $v );
		} else {
			$res = array();
			foreach ( $l1GroupMap[ $r[ "group_id" ] ] as $id ) {
				$res[]= makeExpNum( $id );
			}
			return implode( "\r\n" , $res );
		}
	};
	$flt[ "exp_year" ] = function( &$r , $c , $v ) use( $l1GroupMap ) {
		if ( $r[ "group_id" ] == 0 ) {
			return makeExpYear( $v );
		} else {
			$res = array();
			foreach ( $l1GroupMap[ $r[ "group_id" ] ] as $id ) {
				$res[]= makeExpYear( $id );
			}
			return implode( "\r\n" , $res );
		}
	};
	/*$flt[ "wrk_lnk" ] = function( &$r , $c , $v ) use( $l1GroupMap , $lastWorkers , $widRevMap ) {
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
	};*/

	$flt[ "outgoingCorrPayments" ] = function( &$r , $c , $v ) {
		if ( isset( $r[ '___corr' ] ) ) {
			$ccorr = $r[ '___corr' ];
			return date( 'd-m-Y' , $ccorr[ 'date' ] ).' '.$ccorr[ 'agency' ].', '.$ccorr[ 'agent' ];
		} else {
			return '' ;
		}
	};
	
	$REPORT_TIME = time();
	
	header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
	header( 'Content-Disposition: attachment;filename="Исполнительные листы выборка '.date( 'Y.m.d H-i' , $REPORT_TIME ).'.xlsx"' );
	
	$xlsxResult  = new TSimpleXLSXTemplate( './files/tmpl.writ-of-execution.list-ex.xlsx' );
	$xlsxResult->selectSheet( 'list-ex' );

	$xlsxResult->makeSimpleTable (
		'{ "first-result-row" : 2 }' ,
		'[ { "n" : "num"      , "t" : "ss"  , "c" : "A" , "f" : "woe_lnk"              } ,'
		.' { "n" : "ext_id"   , "t" : "Ss"  , "c" : "B" , "f" : "exp_num"              } ,'
		.' { "n" : "ext_id"   , "t" : "Ss"  , "c" : "C" , "f" : "exp_year"             } ,'
		.' { "n" : "case_num" , "t" : "Ss"  , "c" : "D" } ,'
		.' { "n" : "state"    , "t" : "n"   , "c" : "E" , "f" : "woe_state"            } ,'
		.' { "n" : "id"       , "t" : "Ss"  , "c" : "F" , "f" : "payer"                } ,'
		.' { "n" : "id"       , "t" : "p"   , "c" : "G" , "f" : "price"                } ,'
		.' { "n" : "id"       , "t" : "p"   , "c" : "H" , "f" : "payed"                } ,'
		.' { "n" : "id"       , "t" : "p"   , "c" : "K" , "f" : "payed_c"              } ,'
		.' { "n" : "id"       , "t" : "p"   , "c" : "L" , "f" : "payed_last_date"      } ,'
		.' { "n" : "id"       , "t" : "p"   , "c" : "M" , "f" : "payed_last_size"      } ,'
		.' { "n" : "marks"    , "t" : "S64" , "c" : "N" , "f" : "marks"                } ,'
		.' { "n" : "id"       , "t" : "Sl"  , "c" : "O" , "f" : "outgoingCorrPayments" } ,'
		.' { "n" : "ep_num"   , "t" : "Sl"  , "c" : "P" }]' ,
		$res , $flt
	);
	
	$xlsxResult->write();


	