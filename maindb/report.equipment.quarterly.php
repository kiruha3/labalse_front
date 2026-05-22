<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	/**
	 * report 15-1 row order field name
	 */
	define( 'FSOF' , 'order--15-1.246--ed-129' );


	include_once( '../core.php' );
	/**
	 * @var $dbConfig
	 * @var TDB $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $TAB_CASECATEGORY
	 */
	require_once 'lconfig.php' ;
	/**
	 * @var $PlaceID
	 */

	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "EXTENTIONS" , $Rights ) ) {
			$maySTATISTICS = in_array( "STATISTICS" , $Rights[ "EXTENTIONS" ] );
		} else {
			$maySTATISTICS = false ;
		}

		$GoOut = !$maySTATISTICS ;
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit ;
	}

	$ty1 = $_REQUEST[ 'y1' ];
	$ty2 = $_REQUEST[ 'y2' ];
	$tm1 = $_REQUEST[ 'm1' ];
	$tm2 = $_REQUEST[ 'm2' ];

	$tfd = 1 ;
	$tld = date( 't' , mktime( 12 , 0 , 0 , intval( $tm2 ) , 1 , intval( $ty2 ) ) );
	$ts = mktime( 0 , 0 , 0 , $tm1 , $tfd , $ty1 );
	$te = mktime( 23 , 59 , 59 , $tm2 , $tld , $ty2 );

	$tm1 = strlen( $tm1 ) == 1 ? '0'.$tm1 : $tm1 ;
	$tm2 = strlen( $tm2 ) == 1 ? '0'.$tm2 : $tm2 ;

	$tspc = $portalDB->query( "select * from `specialities` where `use_in_stat` = 1 order by `".FSOF."` , `group` , `num`" , 'id' );
	$tspcIDArray = array_keys( $tspc );

	$col3A = $portalDB->query(
		"select
			`t1`.`id` ,
			`t1`.`date` ,
			ifnull( `t1`.`group_id` , 0 ) as `group` ,
			`t1`.`exp_type` ,
			`t3`.`spec_id` ,
			ifnull( `t3`.`state` , 0 ) as `state` ,
			`t3`.`fin_date` ,
			ifnull( `t3`.`sndz` , 0 ) as `sndz` ,
			count( `t1`.`id` ) as `ctrl_count`
		from
			`matincoming` as `t1` ,
			`matincominglvl2` as `t2` ,
			`expertize` as `t3`
		where
			( `t1`.`date` is not null ) and
			( `t1`.`state` <> -2 ) and
			( `t2`.`mat_id` = `t1`.`id` ) and
			( `t3`.`ext_id` = `t2`.`id` ) and
			( `t3`.`use_in_stat` = 1 ) and
			( `t3`.`spec_id` in ( ?* ) ) and
			( `t3`.`state` in ( 1 ) ) and
			( `t3`.`fin_date` >= ? ) and
			( `t3`.`fin_date` <= ? )
			group by `t1`.`id`" ,
		'id' , '*iss' , $tspcIDArray , date( 'Y-m-d' , $ts ) , date( 'Y-m-d' , $te ) );

	$matIDList = array_keys( $col3A );
	$markNoPayID = $dbConfig[ CFG_MATINCOMING_MARK_NOPAY ];
	$markedExp = $portalDB->query( "select * from `marks-objects` where ( `ext_type` = 'matincoming' ) and ( `mark_id` = ".$markNoPayID." )" , 'ext_id' );


	$eqTab = $portalDB->table( 'equipment' , 'id' );
	$eqTabRef = $portalDB->query( "select `t1`.`id` , `t1`.`ext_id` from `exp-equipment` as `t1` , `equipment` as `t2` where ( `t1`.`ext_id` = `t2`.`id` )" , 'id' );
	$eqUsageTab = $portalDB->query( "select `t4`.`eq_id` , `t1`.`id` as `mat-id` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` , `exp-equipment-usage` as `t4` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t3`.`ext_id` = `t2`.`id` ) and ( `t4`.`ext_id` = `t3`.`id` ) and ( `t1`.`id` in ( ?* ) ) order by null" , false , '*s' , $matIDList );

	$cdate = date( 'd-m-Y' , time() );
	$ctime = date( 'H:i:s' , time() );

	$eqMap = array();

	foreach( $eqTab as $eq ) {
		$eqID = $eq[ 'id' ];
		$eqMap[ $eqID ] = array( 'gz' => 0 , 'above-gz' => 0 );
	}

	foreach ( $eqUsageTab as $eutr ) {
		$eid = $eutr[ 'eq_id' ];
		$eid = $eqTabRef[ $eid ][ 'ext_id' ];
		$mID = $eutr[ 'mat-id' ];
		$expType = $col3A[ $mID ][ 'exp_type' ];

		if ( isCCGZ( $expType ) || isset( $markedExp[ $mID ] ) ) {
			$eqMap[ $eid ][ 'gz' ]++ ;
		} else {
			$eqMap[ $eid ][ 'above-gz' ]++ ;
		}
	}

	header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
	header( 'Content-Disposition: attachment;filename="Отчет по оборудованию ежеквартальный до 15 числа '.date( 'Y.m.d H-i' , time() ).'.xlsx"' );
	//header( 'content-type: text/plain; charset=cp1251' );
	define( 'PAGE_IN_DEMAND' , 'Востребованное' );
	define( 'PAGE_NOT_IN_DEMAND' , 'Невостребонно' );
	define( 'PAGE_NOT_READY_TO_REALLOCATE' , 'Не готовы передать' );
	define( 'PAGE_READY_TO_ACCEPT' , 'Готовы принять' );

	$xlsx = new TSimpleXLSXTemplate( './files/tmpl.report.equipment.quarterly.xlsx' );
	foreach( array( PAGE_NOT_IN_DEMAND , PAGE_NOT_READY_TO_REALLOCATE , PAGE_IN_DEMAND , PAGE_READY_TO_ACCEPT ) as $pn ) {
		$xlsx->selectSheet( $pn );
		$xlsx->setCellValue( 'A5' , $dbConfig[ CFG_ORG_NAME_SHORT ] );
	}

	$riNID = 1 ;
	$riID = 1 ;
	$riNRTR = 1 ;
	$xlsx->selectSheet( PAGE_IN_DEMAND );
	$lastSheet = PAGE_IN_DEMAND ;

	function writeEqRow( $rn , $eqData , $eqStat ) {
		global $xlsx ;
		$eqName = $eqData[ 'name' ];
		$regNumber = $eqData[ 'reg-number' ];
		$bookValue = $eqData[ 'book_value' ];
		$xlsx->setCellValue( 'A'.( $rn + 5 ) , $rn );
		$xlsx->setCellValue( 'B'.( $rn + 5 ) , str_replace( '.' , '. ' , $eqName ).' ('.$regNumber.')' );
		$xlsx->setCellValue( 'C'.( $rn + 5 ) , $bookValue );
		$xlsx->setCellValue( 'D'.( $rn + 5 ) , $eqStat[ 'gz' ] );
		$xlsx->setCellValue( 'F'.( $rn + 5 ) , $eqStat[ 'above-gz' ] );
		$xlsx->setCellValue( 'G'.( $rn + 5 ) , $eqData[ 'not_in_demand' ] == 1 ? 'невостребовано' : 'востребовано' );
		$xlsx->setCellValue( 'H'.( $rn + 5 ) , ( $eqData[ 'not_in_demand' ] == 1 ) && isset( $eqData[ 'not_in_demand_comment' ] ) ? $eqData[ 'not_in_demand_comment' ] : '' );
		$xlsx->setCellValue( 'J'.( $rn + 5 ) , isset( $eqData[ 'reallocation_comment' ] ) ? $eqData[ 'reallocation_comment' ] : '' );
	}

	foreach( $eqTab as $eq ) {
		$eqID = $eq[ 'id' ];
		$eqStat = $eqMap[ $eqID ];
		if ( $eq[ 'not_in_demand' ] == 1 ) {
			if ( $riNID <= 32 ) {
				$xlsx->selectSheet( PAGE_NOT_IN_DEMAND );
				writeEqRow( $riNID , $eq , $eqStat );
				$riNID++ ;
			}
		} else {
			if ( $eqStat[ 'gz' ] == 0  && $eqStat[ 'above-gz' ] == 0 ) {
				if ( $riNRTR <= 500 ) {
					if ( ( is_null( $eq[ 'decommissioned_date' ] ) || $eq[ 'decommissioned_date' ] == 0 ) ) {
						$xlsx->selectSheet( PAGE_NOT_READY_TO_REALLOCATE );
						writeEqRow( $riNRTR , $eq , $eqStat );
						$riNRTR++ ;
					}
				}
			} else {
				if ( $riID <= 500 ) {
					$xlsx->selectSheet( PAGE_IN_DEMAND );
					writeEqRow( $riID , $eq , $eqStat );
					$riID++ ;
				}
			}
		}
	}

	$xlsx->write();
