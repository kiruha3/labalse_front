<?php
	require_once( 'core.export.php' );
    /**
     * @var TDB $portalDB
     * @var array $dbConfig
     */

    $res = array( 'success' => true );

	$mai = $portalDB->simpleRow( 'kuvk-params' , array( 'name' => 'matincoming.add.first' ) );
	if ( $mai !== false ) {
		$mai = json_decode( $mai[ 'value' ] , true );
	} else {
		sendResult( array( 'success' => false ) );
		exit ;
	}

	/*$ccl = $portalDB->table( 'casecategory' );
	cvtArr( $ccl , 'name' );
	unsetArr( $ccl , array( 'short_name' , 'order-151-246' ) );
	$res[ 'casecategory' ] = &$ccl ;*/

	switch ( $mai[ 't' ] ) {
		case 'd' :
			$td = Date2SQL( $mai[ 'd' ] );
			$minID = $portalDB->row( "select min( `id` ) as `minID` from `matincoming` where `date` >= ".$td );
			$minID = $minID[ 'minID' ];
			break ;

		case 'id' :
			$minID = $mai[ 'd' ];
			break ;

		default :
			$td = Date2SQL( '01.01.'.date( 'Y' , time() ) );
			$minID = $portalDB->row( "select min( `id` ) as `minID` from `matincoming` where `date` >= ".$td );
			$minID = $minID[ 'minID' ];
			break ;
	}

	$maCount = 1000 ;
	$muCount = 1000 ;
	$mnLa = $portalDB->query( "select `t1`.`id` from `matincoming` as `t1` left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'matincoming' ) ) where ( `t1`.`id` >= ? ) and ( `t2`.`id` is null ) order by `t1`.`id` limit ?" , false , 'si' , $minID , $maCount );
	$mnLu = $portalDB->query( "select `t1`.`ext_id` as `id` from `kuvk-sync` as `t1` where ( ( `t1`.`ext_type` = 'matincoming' ) and ( `t1`.`processed` <> 1 ) ) order by `t1`.`ext_id` limit ?" , false , 'i' , $muCount );
	$mnIDL = array();
	foreach ( $mnLa as &$mn ) {
		$mnIDL[]= $mn[ 'id' ];
	} unset( $mn );
	foreach ( $mnLu as &$mn ) {
		$mnIDL[]= $mn[ 'id' ];
	} unset( $mn );
	$mnIDL = array_unique( $mnIDL );

	$lvl1c = $portalDB->query(
		"select
			`t1`.`id` , `t1`.`date` , `t1`.`exp_type` , `t1`.`ex_data_3` , `t1`.`ex_data_4` ,
			`t1`.`state` , `t1`.`group_id` , `t1`.`from_agent` , `t2`.`kuvk-guid`
		from `matincoming` as `t1`
		left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'matincoming' ) )
		where ( `t1`.`id` in ( ?* ) ) order by `t1`.`id`" ,
		'id' , '*s' , $mnIDL
	);

	$lvl1cg = $portalDB->query(
		"select
			`t1`.`group_id` , count( * ) as `cnt`
		from
			`matincoming` as `t1`
		group by `t1`.`group_id`
		having ( `cnt` > 1 ) and ( `cnt` < 100 )" ,
		'group_id' );

	$L = array();
	$mnAtL = array();
	cvtArr( $lvl1c , array( 'ex_data_3' , 'ex_data_4' ) );
	foreach( $lvl1c as &$cc ) {
		$cc[ 'number' ] = matincomingNumber( $cc[ 'id' ] );
		$cc[ 'date' ] = date( DATE_ATOM , strtotime( $cc[ 'date' ] ) );
		//$cc[ 'ex_data_3' ] = iconv( 'cp1251' , 'utf8' , $cc[ 'ex_data_3' ] );
		//$cc[ 'ex_data_4' ] = iconv( 'cp1251' , 'utf8' , $cc[ 'ex_data_4' ] );
		$cc[ 'evidence' ] = array();
		$cc[ 'lvl23' ] = array();
		$cc[ 'marks' ] = array();
		$cc[ 'kompl' ] = isset( $lvl1cg[ $cc[ 'group_id' ] ] ) ? 1 : 0 ;
		unset( $cc[ 'group_id' ] );
		$L[]= &$cc ;

		$mnAtL[]= $cc[ 'from_agent' ];
	} unset( $cc );

	$mnAtL = array_unique( $mnAtL );
	$atL = $portalDB->query(
		"select `t1`.`id` , `t1`.`ext_id` , `t1`.`name` , `t2`.`kuvk-guid` from `agent` as `t1`
		left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'agent' ) )
		where ( `t1`.`id` in ( ?* ) )" , 'id' , '*i' , $mnAtL );

	$mnAyL = array();
	cvtArr( $atL , 'name' );
	foreach( $atL as &$ci ) {
		//$ci[ 'name' ] = iconv( 'cp1251' , 'utf8' , $ci[ 'name' ] );
		$mnAyL[]= $ci[ 'ext_id' ];
	} unset( $ci );
	$res[ 'agent' ] = $atL ;

	$mnAyL = array_unique( $mnAyL );

	$ayL = $portalDB->query(
		"select `t1`.`id` , `t1`.`ext_id` , `t1`.`name` , `t2`.`kuvk-guid` from `agency` as `t1`
		left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'agency' ) )
		where ( `t1`.`id` in ( ?* ) )" , 'id' , '*i' , $mnAyL );

	cvtArr( $ayL , 'name' );
	/*foreach( $ayL as &$ci ) {
		$ci[ 'name' ] = iconv( 'cp1251' , 'utf8' , $ci[ 'name' ] );
	} unset( $ci );*/
	$res[ 'agency' ] = $ayL ;

	$lvl1cIDL = array_keys( $lvl1c );
	$evl = $portalDB->query( "select * from `evidence` where ( `ext_id` in ( ?* ) )" , 'id' , '*s' , $lvl1cIDL );

	cvtArr( $evl , array( 'descr' , 'out_comment' ) );
	foreach( $evl as &$ev ) {
		$eid = $ev[ 'ext_id' ];
		//$ev[ 'descr' ] = iconv( 'cp1251' , 'utf8' , $ev[ 'descr' ] );
		//$ev[ 'out_comment' ] = iconv( 'cp1251' , 'utf8' , $ev[ 'out_comment' ] );
		$lvl1c[ $eid ][ 'evidence' ][]= &$ev ;
		$ev[ 'inc_date' ] = date( DATE_ATOM , $ev[ 'inc_date' ] );
		unset( $ev[ 'ext_id' ] );
	} unset( $ev );

	$lvl23c = $portalDB->query(
		"select `t2`.`id` , `t2`.`mat_id` , `t2`.`kat_slognost` , ".implode( " , " , strexp( "`t3`.`{{spec,exp}_id,state,fin_date,conclusion{,_1,_2{,_1,_2,_3},_3},sndz,reason_{1,2}}`" ) )."
		from `matincominglvl2` as `t2` , `expertize` as `t3` , `specialities` as `t4`
		where ( `t2`.`mat_id` in ( ?* ) ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `t4`.`id` = `t3`.`spec_id` ) and ( `t4`.`original` = 1 ) and ( `t3`.`use_in_stat` = 1 )" , 'id' , "*s" , $lvl1cIDL );
	$wIDL = array();
	$specIDL = array();
	foreach( $lvl23c as &$cc ) {
		$mid = $cc[ 'mat_id' ];
		$lvl1c[ $mid ][ 'lvl23' ][]= &$cc ;
		unset( $cc[ 'mat_id' ] );
		$wIDL[]= $cc[ 'exp_id' ];
		$specIDL[]= $cc[ 'spec_id' ];
		$cc[ 'fin_date' ] = date( DATE_ATOM , strtotime( $cc[ 'fin_date' ] ) );
	} unset( $cc );

	$wIDL = array_unique( $wIDL );
	$specIDL = array_unique( $specIDL );

	$marksNoPay =  $portalDB->query( "select * from `marks-objects` where ( `ext_id` in ( ?* ) ) and ( `ext_type` = 'matincoming' ) and ( `mark_id` = ? )" , 'ext_id' , '*si' , $lvl1cIDL , $dbConfig[ CFG_MATINCOMING_MARK_NOPAY ] );

	foreach( $marksNoPay as &$mark ) {
		$eid = $mark[ 'ext_id' ];
		$lvl1c[ $eid ][ 'marks' ][ 'noPay' ] = 1 ;
	} unset( $marks );

	$res[ 'matincoming' ] = $L ;//$lvl1c ;

	sendResult( $res );
