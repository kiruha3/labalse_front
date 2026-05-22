<?php
	include_once ( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 */

	require_once ( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( isset( $_REQUEST[ "eq" ] ) ) {
		$eqID = intval( $_REQUEST[ "eq" ] );
	} else {
		$eqID = -1 ;
	}

	$tabWorkers = $portalDB->table( "workers" , "id" );
	foreach ( $tabWorkers as &$w ) {
		$w[ "name" ] = NAMES_Format( NAMES_parse( $w[ "name" ] ) );
	} unset( $w );

	$tabEquipment = $portalDB->query( "select `t1`.* , `t2`.`id` as `exp-eq-id` from `equipment` as `t1` , `exp-equipment` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t1`.`id` = ? )" , "exp-eq-id" , "i" , $eqID );
	if ( count( $tabEquipment ) < 1 ) {
		exit();
	};

	$tabSpec = $portalDB->query( "select `t1`.`index` , `t2`.* from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id` = `t2`.`group` )" , "id" );

	$expEqIDL = array_keys( $tabEquipment );

	$tabEQUE = $portalDB->query(
		"select `t1`.`id` , `t1`.`exp_type` , `t2`.`dep_id` , `t3`.`exp_id` , `t3`.`id` as `expertize` , `t3`.`spec_id` , `t4`.`eq_id` , `t4`.`start` , `t4`.`finish`
		 from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` , `exp-equipment-usage` as `t4`
		 where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `t3`.`id` = `t4`.`ext_id` ) and ( `t4`.`eq_id` in ( ?* ) )
		 order by `t4`.`start` asc" , false , "*i" , $expEqIDL );

	$totalTime = 0 ;
	foreach ( $tabEQUE as $ctEQUE ) {
		$totalTime+= $ctEQUE[ "finish" ] - $ctEQUE[ "start" ] ;
	}

	$totalTime = round( $totalTime / 3600 );

	$flt = makeSimpleTable_init_filter();
	$flt[ "matid" ] = function ( &$r , $c , $v ) {
		//return "<a class=\"mat-id\" href=\"expertize.php?edit=".$r[ "expertize" ]."\" target=\"_blank\">".matincomingNumber( $v )." / ".matincomingYear( $v )."</a>" ;
		$num = matincomingNumberFullParts( $v , $r[ 'dep_id' ] , $r[ 'exp_type' ] );
		if ( !isset( $num[ 'year' ] ) ) {
			$num = implode( $num ).' ('.matincomingYear( $v ).')' ;
		} else {
			$num = implode( $num );
		}
		return '<a class="mat-id" href="expertize.php?edit='.$r[ 'expertize' ].'" target="_blank">'.$num.'</a>' ;
	};
	$flt[ "expert" ] = function ( &$r , $c , $v ) {
		global $tabWorkers ;
		return $tabWorkers[ $v ][ "name" ];
	};
	$flt[ "equipment" ] = function ( &$r , $c , $v ) {
		global $tabEquipment ;
		$ce = $tabEquipment[ $v ];
		return $ce[ "label" ]." (".$ce[ "reg-number" ].")" ;
	};

	$flt[ "time-diff" ] = function ( &$r , $c , $v ) {
		$td = ( $r[ "finish" ] - $r[ "start" ] );
		if ( $td > 86400 ) {
			return round( $td / 86400 , 2 )." дн" ;
		} else
		if ( $td > 3600 ) {
			return round( $td / 3600 , 2 )." ч" ;
		} else
		if ( $td > 60 ) {
			return round( $td / 60 , 2 )." мин" ;
		} else {
			return $td." сек" ;
		}
	};

	$flt[ "spec" ] = function ( &$r , $c , $v ) {
		global $tabSpec , $UserID ;
		$cs = $tabSpec[ $v ];
		return "<span class=\"spec\" title=\"".( $UserID == 1 ? '['.$v.'] ' : '' ).htmlspecialchars( $cs[ "desc" ] )."\">".$cs[ "index" ].".".$cs[ "num" ]."</span>" ;
	};

	MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" , "%UT/log.equipment.css" ) , array() , "hlp/no_access.html" );

	$eqData = reset( $tabEquipment );

	echo '<div class="header">Журнал использования оборудования</div>
	<div class="header"><a href="/equipment.form.php?edit='.$eqID.'">'.$eqData[ 'name' ].( strlen( $eqData[ 'reg-number' ].'' ) > 0 ? ' ('.$eqData[ 'reg-number' ].')' : '' ).'</a></div>'.makeSimpleTable( '[]' , '[ { "t" : 2 } ]' ,
		'['.
		'  { "n" : "start"   , "t" : "dt" , "h" : [ { "d" : "Начало" } ] } ,'.
		'  { "n" : "finish"  , "t" : "dt" , "h" : [ { "d" : "Конец" } ] } ,'.
		'  { "n" : "id"      , "t" : "ss" , "h" : [ { "d" : "Экспертиза" } ] , "f" : "matid" } ,'.
		'  { "n" : "spec_id" , "t" : "ss" , "h" : [ { "d" : "Специальность" } ] , "f" : "spec" } ,'.
		'  { "n" : "exp_id"  , "t" : "ss" , "h" : [ { "d" : "Эксперт" } ] , "f" : "expert" } ,'.
		'  { "n" : "id"      , "t" : "ss" , "h" : [ { "d" : "Время использования" } ] , "f" : "time-diff" }'.
		']' ,
		$tabEQUE , false , $flt
	).'<div class="total">Всего записей: '.count( $tabEQUE ).'</div>
	<div class="total">Всего наработано: '.$totalTime.' ч</div>' ;

	closeHtml();
