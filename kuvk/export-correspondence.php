<?php
	require_once( 'core.export.php' );
	/**
	 * @var TDB $portalDB
	 * @var array $dbConfig
	 */

	$res = array( 'success' => true );

	$mai = $portalDB->simpleRow( 'kuvk-params' , array( 'name' => 'correspondence.add.first' ) );
	if ( $mai !== false ) {
		$mai = json_decode( $mai[ 'value' ] , true );
	} else {
		sendResult( array( 'success' => false ) );
		exit ;
	}
	
	$mtCount = rand( 10 , 20 );
	$maCount = rand( 5 , $mtCount - 1 );
	$muCount = $mtCount - $maCount ;
	$maCount = 1000 ;
	$muCount = 1000 ;
	
	switch ( $mai[ 't' ] ) {
		case 'd' :
			$td = Date2Int( $mai[ 'd' ] );
			//$minID = $portalDB->row( "select min( `id` ) as `minID` from `correspondence-main` where ( `date` >= ? ) and ( `type` in ( 1 , 2 ) )" , 'i' , $td );
			//$minID = $minID[ 'minID' ];
			$mnLa = $portalDB->query( "select `t1`.`id` from `correspondence-main` as `t1` left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'correspondence' ) ) where ( `t1`.`date` >= ? ) and ( `t1`.`type` in ( 1 , 2 ) ) and ( `t2`.`id` is null ) order by `t1`.`id` limit ?" , false , 'ii' , $td , $maCount );
			$mnLu = $portalDB->query( "select `t1`.`ext_id` as `id` from `kuvk-sync` as `t1` where ( ( `t1`.`ext_type` = 'correspondence' ) and ( `t1`.`processed` <> 1 ) ) order by `t1`.`ext_id` limit ?" , false , 'i' , $muCount );
			break ;

		case 'id' :
			$minID = $mai[ 'd' ];
			$mnLa = $portalDB->query( "select `t1`.`id` from `correspondence-main` as `t1` left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'correspondence' ) ) where ( `t1`.`id` >= ? ) and ( `t1`.`type` in ( 1 , 2 ) ) and ( `t2`.`id` is null ) order by `t1`.`id` limit ?" , false , 'ii' , $minID , $maCount );
			$mnLu = $portalDB->query( "select `t1`.`ext_id` as `id` from `kuvk-sync` as `t1` where ( ( `t1`.`ext_type` = 'correspondence' ) and ( `t1`.`processed` <> 1 ) ) order by `t1`.`ext_id` limit ?" , false , 'i' , $muCount );
			break ;

		default :
			$td = Date2SQL( '01.01.'.date( 'Y' , time() ) );
			$mnLa = $portalDB->query( "select `t1`.`id` from `correspondence-main` as `t1` left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'correspondence' ) ) where ( `t1`.`date` >= ? ) and ( `t1`.`type` in ( 1 , 2 ) ) and ( `t2`.`id` is null ) order by `t1`.`id` limit ?" , false , 'ii' , $td , $maCount );
			$mnLu = $portalDB->query( "select `t1`.`ext_id` as `id` from `kuvk-sync` as `t1` where ( ( `t1`.`ext_type` = 'correspondence' ) and ( `t1`.`processed` <> 1 ) ) order by `t1`.`ext_id` limit ?" , false , 'i' , $muCount );
			break ;

	}

	/*$mtCount = rand( 10 , 20 );
	$maCount = rand( 5 , $mtCount - 1 );
	$muCount = $mtCount - $maCount ;
	$maCount = 1000 ;
	$muCount = 1000 ;

	$mnLa = $portalDB->query( "select `t1`.`id` from `correspondence-main` as `t1` left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'correspondence' ) ) where ( `t1`.`id` >= ? ) and ( `t1`.`type` in ( 1 , 2 ) ) and ( `t2`.`id` is null ) order by `t1`.`id` limit ?" , false , 'ii' , $minID , $maCount );
	$mnLu = $portalDB->query( "select `t1`.`ext_id` as `id` from `kuvk-sync` as `t1` where ( ( `t1`.`ext_type` = 'correspondence' ) and ( `t1`.`processed` <> 1 ) ) order by `t1`.`ext_id` limit ?" , false , 'i' , $muCount );*/
	$mnIDL = array();
	foreach ( $mnLa as &$mn ) {
		$mnIDL[]= $mn[ 'id' ];
	} unset( $mn );
	foreach ( $mnLu as &$mn ) {
		$mnIDL[]= $mn[ 'id' ];
	} unset( $mn );
	$mnIDL = array_unique( $mnIDL );

	$corrC = $portalDB->query(
		"select
			`t1`.`id` , `t1`.`type` , `t1`.`num` , `t1`.`date` , `t1`.`ext_num` , `t1`.`ext_date` , `t1`.`name` , `t1`.`description` ,
			`t2`.`kuvk-guid` ,
			`t3`.`tgt` ,
			`t4`.`exp`
		from `correspondence-main` as `t1`
		left join `kuvk-links` as `t2` on ( ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = 'correspondence' ) )
		left join `correspondence-target` as `t3` on ( `t1`.`id` = `t3`.`ext_id` )
		left join `correspondence-experts` as `t4` on ( `t1`.`id` = `t4`.`ext_id` )
		where ( `t1`.`id` in ( ?* ) ) and ( `t1`.`type` in ( 1 , 2 ) ) group by `t1`.`id` order by `t1`.`num`" ,
		'id' , '*i' , $mnIDL
	);

	$mnAtL = array();
	cvtArr( $corrC , array( 'ext_num' , 'name' , 'description' ) );
	foreach( $corrC as &$cc ) {
		$cc[ 'date' ] = date( DATE_ATOM , $cc[ 'date' ] );
		$cc[ 'ext_date' ] = date( DATE_ATOM , $cc[ 'ext_date' ] );
		//$cc[ 'ext_num' ] =  iconv( 'cp1251' , 'utf8' , $cc[ 'ext_num' ] );
		//$cc[ 'name' ] =  iconv( 'cp1251' , 'utf8' , $cc[ 'name' ] );
		//$cc[ 'description' ] =  iconv( 'cp1251' , 'utf8' , $cc[ 'description' ] );

		$mnAtL[]= $cc[ 'tgt' ];
	} unset( $cc );
	$res[ 'correspondence' ] = $corrC ;

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
	foreach( $ayL as &$ci ) {
		//$ci[ 'name' ] = iconv( 'cp1251' , 'utf8' , $ci[ 'name' ] );
	} unset( $ci );
	$res[ 'agency' ] = $ayL ;

	sendResult( $res );