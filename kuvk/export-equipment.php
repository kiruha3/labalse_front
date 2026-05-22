<?php
	require_once( 'core.export.php' );
	/**
	 * @var TDB $portalDB
	 * @var array $dbConfig
	 */

	$res = array( 'success' => true );



	$eqList = $portalDB->query(
		"select `t1`.* , `t7`.`kuvk-guid` , `t2`.`test_type` , `t2`.`test_period` from `equipment` as `t1`
		inner join `exp-equipment` as `t2` on ( `t2`.`ext_id` = `t1`.`id` )
		left join `kuvk-links` as `t7` on ( ( `t1`.`id` = `t7`.`ext_id` ) and ( `t7`.`ext_type` = 'equipment' ) )
		where ( `t2`.`id` in (
		    select distinct
				`t3`.`eq_id`
			from
				`exp-equipment-usage` as `t3` ,
				`expertize` as `t4` ,
				`matincominglvl2` as `t5` ,
				`kuvk-links` as `t6`
			where
				( `t3`.`ext_id` = `t4`.`id` ) and
				( `t4`.`ext_id` = `t5`.`id` ) and
				( ( `t5`.`mat_id` = `t6`.`ext_id` ) and
				  ( `t6`.`ext_type` = 'matincoming' ) )
			)
		)
		group by `t1`.`id`"  );

	cvtArr( $eqList , array( 'name' , 'label' , 'reg-number' , 'mop' , 'mop-comment' , 'manufacture-number' , 'mi-type-number' , 'mi-type-title' , 'mi-type-type' , 'mi-modification' ) );
	foreach( $eqList as &$cc ) {
		$cc[ 'startup-date' ] = date( DATE_ATOM , $cc[ 'startup-date' ] );
		$cc[ 'test_period' ] = preg_replace( array( '/(\d+)y/' ) , array( '$1' ) , $cc[ 'test_period' ] );
	} unset( $cc );
	cvtArr( $eqList , 'test_period' );
	$res[ 'eq-list' ] = $eqList ;



	sendResult( $res );
