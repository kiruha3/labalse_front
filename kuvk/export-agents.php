<?php
	header( "content-type: application/json;charset=utf-8" );

	require_once( "../../core.php" );

	$res = array( "success" => true );

	$toal = $portalDB->table( "type-of-agency" );
	foreach( $toal as &$i ) {
		$i[ "name" ] = iconv( "cp1251" , "utf8" , inForm( $i[ "name" ] , 1 ) );
		unset( $i[ "_fr" ] );
		unset( $i[ "fmt" ] );
		unset( $i[ "for-test" ] );
	} unset( $i );

	$res[ "type_of_agency" ] = &$toal ;
	$res[ "agency" ] = array();


	$tatl = $portalDB->query( "select `from_agent` as `atid` from `matincoming` where `date` >= '2012-01-01' group by `atid`" );
	$tatidl = array();
	foreach( $tatl as $at ) {
		$tatidl[]= $at[ "atid" ];
	}

	$tatl = $portalDB->query( "select * from `agent` where `id` in ( ?* ) order by `name`" , "id" , "*i" , $tatidl );
	$tayidl = array();
	foreach( $tatl as &$at ) {
		$tayidl[]= $at[ "ext_id" ];
		$at[ "contacts" ] = array();
	} unset( $at );


	$tatcl = $portalDB->query( "select * from `agent-contacts` where `ext_id` in ( ?* )" , false , "*i" , $tatidl );
	foreach ( $tatcl as &$atc ) {
		$atid = $atc[ "ext_id" ];
		$tatl[ $atid ][ "contacts" ][]= &$atc ;
		$atc[ "value" ] = iconv( "cp1251" , "utf8" , $atc[ "value" ] );
		unset( $atc[ "ext_id" ] );
		unset( $atc[ "actual" ] );
	} unset( $atc );

	$tayidl = array_unique( $tayidl );

	$tayl = $portalDB->query( "select * from `agency` where `id` in ( ?* ) order by `name`" , "id" , "*i" , $tayidl );
	foreach( $tayl as &$ay ) {
		$ay[ "name" ] = iconv( "cp1251" , "utf8" , clearText( $ay[ "name" ] ) );
		$ay[ "destination" ] = iconv( "cp1251" , "utf8" , clearText( $ay[ "destination" ] ) );
		unset( $ay[ "_fr" ] );
		unset( $ay[ "for-test" ] );
		unset( $ay[ "group_id" ] );
		unset( $ay[ "actual" ] );
		$ay[ "agents" ] = array();
	} unset( $ay );

	//print_r_html( $tatl , true );

	foreach ( $tatl as &$at ) {
		$ayid = $at[ "ext_id" ];
		unset( $at[ "_fr" ] );
		unset( $at[ "fmt" ] );
		unset( $at[ "for-test" ] );
		unset( $at[ "ext_id" ] );
		$tayl[ $ayid ][ "agents" ][]= &$at ;
		$at[ "name" ] = iconv( "cp1251" , "utf8" , $at[ "name" ] );
	} unset( $at );

	foreach( $tayl as &$ay ) {
		$res[ "agency" ][]= $ay ;
	} unset( $ay );

	$res = json_encode( $res , JSON_UNESCAPED_UNICODE );
	echo $res ;
?>