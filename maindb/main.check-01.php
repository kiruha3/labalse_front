<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "../core.php" );
	/**
	 * @var TDB $portalDB
	 */
	require_once( "lconfig.php" );

	require_once( '../cores/core.maindb.php' );

	$tabDepartments = $portalDB->table( "departments" , "id" );
	$tabWorkers = $portalDB->table( "workers" , "id" );


	header( "Content-type: text/plain; charset=windows-1251" );
	header( "Cache-Control: no-store, no-cache, must-revalidate" );
	header( "Cache-Control: post-check=0, pre-check=0" , false );

	$mid = matincomingID( intval( $_REQUEST[ "n" ] ) , intval( $_REQUEST[ "y" ] ) );

	$r = $portalDB->query( "
		select
			`t1`.`state` as `state` ,
			`t1`.`fin_date` as `fin_date` ,
			`t2`.`dep_id` as `dep_id` ,
			`t1`.`exp_id` ,
			`t3`.`group_id` as `gid`
		from
			`expertize` as `t1` ,
			`matincominglvl2` as `t2` ,
			`matincoming` as `t3`
		where
			`t1`.`ext_id`=`t2`.`id` and
			`t2`.`mat_id`=`t3`.`id` and
			`t3`.`id`= ?" , false , "s" , $mid
		);

	$assigned = array();
	$wd = array();
	if ( count( $r ) > 0 ) {
		$ts = true ;
		$FD = 0 ;
		foreach( $r as $cr ) {
			$rf = $cr[ "state" ] == 1 ||  $cr[ "state" ] == 2 ;
			$ts = $ts & $rf ;
			if ( $rf ) {
				$FD = max( strtotime( $cr[ "fin_date" ] ) , $FD );
			}
			$w = $tabWorkers[ $cr[ "exp_id" ] ];
			$wd[]= $tabDepartments[ $w[ "dep" ] ][ "short_name" ]." ".NAMES_Format( NAMES_parse( $w[ "name" ] ) , "%F1 %i.%o." );
			if ( $cr[ "gid" ] != 0 ) {
				$assigned[]= $cr[ "gid" ];
			}
		}

		$assigned = array_unique( $assigned );
		if ( count( $assigned ) > 0 ) {
			$assigned = $portalDB->query( "select `id` , year( `date` ) as `y` from `matincoming` where ( `group_id` in ( ?* ) ) and ( `id` <> ? )" , false , "*is" , $assigned , $mid );
			foreach( $assigned as &$i ) {
				$i = matincomingNumber( $i[ "id" ] )." от ".$i[ "y" ];
			} unset( $i );
			$assigned = "\r\n".implode( " , " , $assigned );
		} else {
			$assigned = "" ;
		}
	} else {
		$ts = false ;
		$r = $portalDB->simpleRow( "matincoming" , $mid );


		if ( $r !== false && !is_null( $r[ "group_id" ] ) && $r[ "group_id" ] != 0 ) {
			$assigned = $portalDB->query( "select * from `matincoming` where ( `group_id` = ? ) and ( `id` <> ? )" , false , "is" , $r[ "group_id" ] , $mid );
			foreach ( $assigned as &$i ) {
				$i = matincomingNumber( $i[ "id" ] )." от ".matincomingYear( $i[ "id" ] );
			} unset( $i );
			$assigned = "\r\n".implode( " , " , $assigned );
		} else {
			$assigned = "" ;
		}
	}

	if ( !$ts && count( $wd ) > 0 ) {
		echo "UNFINISHED\r\n".implode( " , " , $wd ).$assigned ;
	} else
	if ( $ts ) {
		echo "FINISHED\r\n".date( "d-m-Y" , $FD )." , ".implode( " , " , $wd ).$assigned ;
	} else {
		echo "UNDEFINED".$assigned  ;
	}
?>