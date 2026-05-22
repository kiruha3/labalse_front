<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once ( "../../core.php" );
	/**
	 * @var $LoginOk
	 * @var TDB $portalDB
	 */
	require_once ( "../lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once ( '../../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	MainHead_L2( "" , "" , array( "../../%UT/buttons.css" , "../../%UT/forms.css" , "logic-control-2.css" ) , array() , "hlp/no_access.html" );

	//$portalDB->dbgMode = true ;

	$specList = $portalDB->query( "select `t2`.`id` , `t1`.`index` as `spec_g` , `t2`.`num` as `spec_num` from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t2`.`group` = `t1`.`id` )" , "id" );

	$cy = intval( date( 'Y' , time() ) , 10 );

	$controlMID = mkCharID( array(
		'v' => 10 ,
		'o' => ORG_INDEX_VRCSE ,
		't' => DOCTYPE_MATINCOMING ,
		'y' => $cy - 3 ,
		'n' => '000000'
	) );

	$lvl2a3 = $portalDB->query( "select `t2`.`mat_id` , `t3`.`spec_id` , `t4`.`index` as `spec_g` , `t5`.`num` as `spec_num` from `matincominglvl2` as `t2` , `expertize` as `t3` , `specialities-groups` as `t4` , `specialities` as `t5` where ( `t3`.`ext_id` = `t2`.`id` ) and ( `t3`.`spec_id` = `t5`.`id` ) and ( `t5`.`group` = `t4`.`id` ) and ( `t3`.`use_in_stat` = 1 ) and ( `t5`.`use_in_stat` = 1 ) and ( `t2`.`mat_id` >= ? )" , false , 's' , $controlMID );

	$lvl1 = array();

	foreach( $lvl2a3 as &$c2a3 ) {
		$mid = $c2a3[ "mat_id" ];
		$sid = $c2a3[ "spec_id" ];

		if ( !isset( $lvl1[ $mid ] ) ) {
			$lvl1[ $mid ] = array();
		}

		if ( !isset( $lvl1[ $mid ][ $sid ] ) ) {
			$lvl1[ $mid ][ $sid ] = 0 ;
		}

		$lvl1[ $mid ][ $sid ]++ ;
	} unset( $c2a3 );

	$tc = 0 ;
	$cnt = 0 ;
	$res_1spec_mw = array();
	$res_mspec = array();
	$res_spec_g22 = array();

	ksort( $lvl1 , SORT_STRING );

	foreach( $lvl1 as $c1id => &$c1 ) {

		$c1_c = count( $c1 );

		$ec = 0 ;

		$sl = array();

		$h_spec_g22 = false ;
		foreach( $c1 as $c1sid => $cc ) {
			$ec+= $cc ;
			$sl[]= $specList[ $c1sid ][ "spec_g" ].".".$specList[ $c1sid ][ "spec_num" ];
			if ( $specList[ $c1sid ][ "spec_g" ] == 22 ) {
				$h_spec_g22 = true ;
			}
		}

		/*if ( $c1_c == 1 && $ec > 1 ) {
			$res_1spec_mw[]= array( "id" => $c1id , "count" => $ec , "specs" => array_unique( $sl ) );
		}*/

		if ( $c1_c > 1 ) {
			$res_mspec[]= array( "id" => $c1id , "count" => $ec , "specs" => array_unique( $sl ) );
		}

		if ( $h_spec_g22 ) {
			$res_spec_g22[]= array( "id" => $c1id , "count" => $ec , "specs" => array_unique( $sl ) );
		}

		/*if ( $ec > 1 ) {
			echo "<tr><td>" , $c1id , " - " , $ec , "</td></tr>" ;
		}*/
	}

	function show( $a ) {
		global $portalDB ;
		$ccMap = array(
			0 => "444" ,
			1 => "888" ,
			2 => "fff" ,
			3 => "8cf" ,
			4 => "48f" ,
			5 => "fc8" ,
			6 => "f84" ,
			7 => "f00"
		);

		$ids = array();
		foreach( $a as &$r ) {
			$ids[]= $r[ "id" ];
		}

		//$portalDB->dbgMode = true ;
		$res = $portalDB->query( "select * , YEAR( `date` ) as `year` from `matincoming` where `id` in ( ?* )" , "id" , "*s" , $ids );

		echo "<table class=\"res-tab\" align=\"center\">" ;
		foreach( $a as &$r ) {
			$rr = $res[ $r[ "id" ] ];
			echo "<tr class=\"rtr\"><td class=\"rtc-lnk-a\"><a href=\"../main.php?singlerow&y=" , $rr[ "year" ] , "&n=" , matincomingNumber( $r[ "id" ] ) , "\" class=\"rtc-link\" target=\"_blank\">" , matincomingNumber( $r[ "id" ] ) , " / * - " , $rr[ "exp_type" ] , "</a></td><td class=\"rtc-y\">" , $rr[ "year" ] , "</td><td class=\"rtc-c\" style=\"background-color: #".$ccMap[ $r[ "count" ] ]."\">" , $r[ "count" ] , "</td><td class=\"rtc-w\">" , substr( $rr[ "ex_data_6" ] , 0 , 30 ) , "</td><td class=\"rtc-s\">".implode( " , " , $r[ "specs" ] )."</td></tr>" ;
		} unset( $r );
		echo "</table>" ;
	}

	flush();

	//echo "<div class=\"nav\"><a href=\"#res_1spec_mw\">Одинаковые специальности</a><br><a href=\"#res_mspec\">Разные специальности</a><br><a href=\"#res_spec_g22\">Специальности 22</a></div>" ;

	/*if ( count( $res_1spec_mw ) > 0 ) {
		echo "<div class=\"desc-a\" id=\"res_1spec_mw\">Одинаковые специальности</div>" ;
		show( $res_1spec_mw );
	}*/
	echo "<div class=\"desc-a\" id=\"res_mspec\">Разные специальности</div>" ;
	show( $res_mspec );

	if ( count( $res_spec_g22 ) > 0 ) {
		echo "<div class=\"desc-a\" id=\"res_spec_g22\">Специальности 22</div>" ;
		show( $res_spec_g22 );
	}


	//$lvl1 = $portalDB->query( "select `t1`.* from `matincoming` as `t1` where ( matincomingYear( `t1`.`id` ) >= ? ) and ( `t1`.`group_id` > 0 )" , 'id' , 'i' , $cy - 1 );
	$expWG = array();
	$lvl2a3 = $portalDB->query( "select `t2`.`mat_id` , `t3`.* from `matincominglvl2` as `t2` , `expertize` as `t3` , `specialities-groups` as `t4` , `specialities` as `t5` where ( `t3`.`ext_id` = `t2`.`id` ) and ( `t3`.`spec_id` = `t5`.`id` ) and ( `t5`.`group` = `t4`.`id` ) and ( `t3`.`use_in_stat` = 1 ) and ( `t5`.`use_in_stat` = 1 ) and ( `t2`.`mat_id` >= ? )" , false , 's' , $controlMID );

	foreach( $lvl2a3 as &$c2a3 ) {
		$mid = $c2a3[ "mat_id" ];
		$state = $c2a3[ "state" ];
		$sndz = $c2a3[ "sndz" ] == 1 ? 1 : 0 ;
		//$expGID = isset( $lvl1[ $mid ] ) ? 'g-'.$lvl1[ $mid ][ 'group_id' ] : 'm-'.$mid ;
		$expGID = $mid ;

		if ( !isset( $expWG[ $expGID ] ) ) {
			$expWG[ $expGID ] = array( 'exp-all' => array() , 'states' => array() , 'sndz' => array() );
		}

		$expWG[ $expGID ][ 'exp-all' ][]= &$c2a3 ;
		$expWG[ $expGID ][ 'states' ][ $state ]= $state ;
		$expWG[ $expGID ][ 'sndz' ][ $sndz ]= $sndz ;

	} unset( $c2a3 );

	echo '<div class="desc-a" id="res_mspec">Всего проверено : '.count( $expWG ).'</div>' ;

	$diffStates = array();
	$diffSNDZ = array();

	$delID = array();
	foreach ( $expWG as $expGID => $expGData ) {
		if ( count( $expGData[ 'exp-all' ] ) < 2 ) {
			$delID[]= $expGID ;
		}

		if ( count( $expGData[ 'states' ] ) >= 2 ) {
			$diffStates[]= array( 'id' => $expGID , 'count' => count( $expGData[ 'states' ] ) , 'specs' => array() );
		}

		if ( count( $expGData[ 'sndz' ] ) >= 2 ) {
			$diffSNDZ[]= array( 'id' => $expGID , 'count' => count( $expGData[ 'sndz' ] ) , 'specs' => array() );
		}
	}

	/*foreach ( $delID as $did ) {
		unset( $expWG[ $did ] );
	}*/

	echo '<div class="desc-a" id="res_mspec">Различие в состоянии экспертизы (В производстве/Окончена/Без производства): '.count( $diffStates ).'</div>' ;
	show( $diffStates );

	echo '<div class="desc-a" id="res_mspec">Различие в отметке СНДЗ : '.count( $diffSNDZ ).'</div>' ;
	show( $diffSNDZ );

	/*foreach ( $expWG as $expGID => $expGData ) {
		if ( count( $expGData ) < 2 ) {
			$delID[]= $expGID ;
		}
	}*/

	closeHtml();
