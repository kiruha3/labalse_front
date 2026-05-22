<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
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

	$font_dir = "../files/fonts/" ;
	$font_name = "verdanab.ttf" ;
	$font_size = 8 ;

	function toUnicodeEntities( $text , $from = "w" ) {
		$text = convert_cyr_string( $text , $from , "i" );
		$uni = "" ;
		for ( $i = 0 , $len = strlen( $text ) ; $i < $len ; $i++ ) {
			$char = $text[ $i ];
			$code = ord( $char );
			$uni.= $code > 175 ? "&#".( 1040 + ( $code - 176 ) ).";" : $char ;
		}
		return $uni ;
	}

	if ( isset( $_GET[ "img" ] ) ) {
		header( "Content-type: image/png" );
		$br = imagettfbbox( 10 , 0 , $font_dir.$font_name ,  toUnicodeEntities( $_REQUEST[ "img" ] ) );

		$SH = $br[ 1 ] - $br[ 7 ] + 1 ;
		$SW = $br[ 2 ] - $br[ 0 ] + 1 ;

		$im = imagecreate( $SW , $SH );
		$bkColor = imagecolorallocate( $im , 255 , 255 , 255 );
		$fgColor = imagecolorallocate( $im , 0 , 0 , 0 );

		imagettftext( $im , 10 , 0 , 0 , $SH , $fgColor , $font_dir.$font_name ,  toUnicodeEntities( $_REQUEST[ "img" ] ) );
		$im = imagerotate( $im , 90 , 0 );
		imagepng( $im );
		imagedestroy( $im );
		exit();
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

	$specsTab = $portalDB->query( "select `t1`.`id` , `t2`.`index` , `t1`.`num` , `t1`.`desc` from `specialities` as `t1` , `specialities-groups` as `t2` where ( `t1`.`group` = `t2`.`id` );" , "id" );
	$eqTab = $portalDB->query( "select `t1`.* from `equipment` as `t1` , `exp-equipment` as `t2` where ( ( `t1`.`decommissioned_date` is null ) or ( `t1`.`decommissioned_date` = 0 ) or ( `t1`.`decommissioned_date` > ? ) ) and ( `t2`.`ext_id` = `t1`.`id` )" , 'id' , 'i' , $ts );
	$eqTabRef = $portalDB->query( "select `t1`.`id` , `t1`.`ext_id` from `exp-equipment` as `t1` , `equipment` as `t2` where ( ( `t2`.`decommissioned_date` is null ) or ( `t2`.`decommissioned_date` = 0 ) or ( `t2`.`decommissioned_date` > ? ) ) and ( `t1`.`ext_id` = `t2`.`id` )" , 'id' , 'i' , $ts );


	$eqUsageTab = $portalDB->query( "select `t1`.`exp_type` , `t2`.`kat_slognost` , `t3`.`spec_id` , `t4`.`eq_id` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` , `exp-equipment-usage` as `t4` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t3`.`ext_id` = `t2`.`id` ) and ( `t4`.`ext_id` = `t3`.`id` ) and ( ( ( `t4`.`start` >= ? ) and ( `t4`.`start` <= ? ) ) or ( ( `t4`.`finish` >= ? ) and ( `t4`.`finish` <= ? ) ) ) order by null" , false , "iiii" , $ts , $te , $ts , $te );

	MainHead_L2( 'База - Статистика' , "<a href='main.php'>База</a> - Статистика по оборудованию" , array( "../%UT/buttons.css" , "%UT/eq-stat.css" ) , array() , "hlp/eq-stat.html" );

	$cdate = date( 'd-m-Y' , time() );
	$ctime = date( 'H:i:s' , time() );

	echo '<center>
		<span class="stat-head">Статистика за период с '.date( 'd-m-Y' , mktime( 0 , 0 , 0 , intval( $tm1 ) , 1 , intval( $ty1 ) ) ).' по '.date( 'd-m-Y' , mktime( 0 , 0 , 0 , intval( $tm2 ) , intval( $tld ) , intval( $ty2 ) ) ).'</span><br>
		<span class="stat-head-2">составлена <span class="stat-head-2-bold">'.$cdate.'</span> в <span class="stat-head-2-bold">'.$ctime.'</span></span><br>
	</center>' ;

	$eqMap = array();

	foreach ( $eqUsageTab as $eutr ) {
		$eid = $eutr[ "eq_id" ];
		$eid = $eqTabRef[ $eid ][ "ext_id" ];

		if ( !isset( $eqMap[ $eid ] ) ) {
			$eqMap[ $eid ] = array( "total" => 0 , "task" => array( 0 , 0 , 0 , 0 , 0 ) , "task-res" => 0 , "above-exp" => 0 , "above-res" => 0 , "lost" => 0 , "spec" => array() );
		}

		$eqMap[ $eid ][ "total" ]++ ;
		$ccID = $eutr[ 'exp_type' ];
		if ( !isset( $TAB_CASECATEGORY[ $ccID ] ) ) {
			$ccID = 0 ;
		}
		$ccGroup = getCCGroup( $ccID );

		switch ( $ccGroup ) {
			case 2:
			case 3:
			case 4:
				$eqMap[ $eid ][ "above-exp" ]++ ;
				break ;

			case 0:
				$eqMap[ $eid ][ "above-res" ]++ ;
				break ;

			case 1:
			case 6:
				$eqMap[ $eid ][ "task" ][ $eutr[ "kat_slognost" ] ]++ ;
				break ;

			case 5:
				$eqMap[ $eid ][ "task-res" ]++ ;
				break ;

			default :
				$eqMap[ $eid ][ "lost" ]++ ;
				break ;

		}

		$eqMap[ $eid ][ "spec" ][]= $eutr[ "spec_id" ];
	}

	echo "<table align=\"center\" class=\"main-tab\">
		<tr>
			<td rowspan=\"4\" class=\"mt-h-1\">Наименование</td>
			<td rowspan=\"4\" class=\"mt-h-2\">Источник приобретения</td>
			<td rowspan=\"4\" class=\"mt-h-3\">Виды проводимых работ</td>
			<td rowspan=\"4\" class=\"mt-h-4\">Дата ввода в эксплуатацию</td>
			<td colspan=\"8\" class=\"mt-h-5g mt-h-5g-1\">Количество экспертиз, проведенных с использованием, в т.ч.</td>
			<td rowspan=\"4\" class=\"mt-h-6\">Примечания</td>
		</tr>
		<tr>
			<td rowspan=\"3\" class=\"mt-h-5g mt-h-5g-2\"><img src=\"?img=".urlencode( "Всего" )."\"></td>
			<td colspan=\"5\" class=\"mt-h-5g mt-h-5g-3\">В рамках выполнения государственного задания</td>
			<td colspan=\"2\" class=\"mt-h-5g mt-h-5g-2\"><img src=\"?img=".urlencode( "Сверх гос. задания" )."\"></td>
		</tr>
		<tr>
			<td colspan=\"4\" class=\"mt-h-5\">экспертизы по категориям сложности</td>
			<td rowspan=\"2\" class=\"mt-h-5\">иссл</td>
			<td rowspan=\"2\" class=\"mt-h-5\">Эксп</td>
			<td rowspan=\"2\" class=\"mt-h-5\">иссл</td>
		</tr>
		<tr>
			<td class=\"mt-h-5\">1</td>
			<td class=\"mt-h-5\">2</td>
			<td class=\"mt-h-5\">3</td>
			<td class=\"mt-h-5\">3+</td>
		</tr>" ;

	foreach( $eqTab as $eq ) {
		$eqName = $eq[ "name" ];
		$regNumber = $eq[ 'reg-number' ];
		$eqStartupDate = date( "d-m-Y" , $eq[ "startup-date" ] );
		$eqSpecList = "" ;

		$eqUsageTotal = "-" ;
		$eqUsageTask = array( "-" , "-" , "-" , "-" , "-" );
		$eqUsageTaskRes = "-" ;
		$eqUsageAboveExp = "-" ;
		$eqUsageAboveRes = "-" ;

		if ( isset( $eqMap[ $eq[ "id" ] ] ) ) {
			$eqm = $eqMap[ $eq[ "id" ] ];
			$eqm[ "spec" ] = array_unique( $eqm[ "spec" ] );
			$eqSpecList = array();
			foreach( $eqm[ "spec" ] as $sid ) {
				$sk = sprintf( "%08d-%02d" , $specsTab[ $sid ][ "index" ] , $specsTab[ $sid ][ "num" ] );
				$sv = "<b>".$specsTab[ $sid ][ "index" ].".".$specsTab[ $sid ][ "num" ]."</b> ".$specsTab[ $sid ][ "desc" ];
				$eqSpecList[ $sk ]= $sv ;
			}
			ksort( $eqSpecList );
			if ( count( $eqSpecList ) > 0 ) {
				$eqSpecList = implode( "<br>" , $eqSpecList );
			} else {
				$eqSpecList = "" ;
			}

			$eqUsageTotal = $eqm[ "total" ];
			$eqUsageTask = $eqm[ "task" ];
			$eqUsageTaskRes = $eqm[ "task-res" ];
			$eqUsageAboveExp = $eqm[ "above-exp" ];
			$eqUsageAboveRes = $eqm[ "above-res" ];
		}



		echo "<tr>
			<td class=\"mt-c-1\"><a href=\"log.equipment.php?eq=".$eq[ "id" ]."\" target=\"_blank\">".str_replace( "." , ". " , $eqName )."</a><br/>(".$regNumber.")</td>
			<td class=\"mt-c-2\">".$eq[ 'mop' ]."</td>
			<td class=\"mt-c-3\">".$eqSpecList."</td>
			<td class=\"mt-c-4\">".$eqStartupDate."</td>
			<td class=\"mt-c-5\">".$eqUsageTotal."</td>" ;
			for( $i = 1 ; $i <= 4 ; $i++ ) {
				echo "<td class=\"mt-c-5\">".$eqUsageTask[ $i ]."</td>" ;
			}
			echo "<td class=\"mt-c-5\">".$eqUsageTaskRes."</td>" ;
			echo "<td class=\"mt-c-5\">".$eqUsageAboveExp."</td>
			<td class=\"mt-c-5\">".$eqUsageAboveRes."</td>
			<td class=\"mt-c-6\"></td>
		</tr>" ;
	}

	echo "</table>" ;

	closeHtml();
