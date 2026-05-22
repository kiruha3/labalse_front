<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var TDB $portalDB
	 * @var $MonthNames
	 * @var $dbConfig
	 * @var $TAB_DEPARTMENTS
	 * @var $TAB_CASECATEGORY
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	$formatXLSX = ( isset( $_REQUEST[ 'format' ] ) && $_REQUEST[ 'format' ] == 'xlsx' );
	$REPORT_TIME = time();

	//print_r_html( $_REQUEST );
	if ( isset( $_REQUEST[ 'i_year_from' ] ) ) {
		$yf = intval( $_REQUEST[ 'i_year_from' ] );
	} else {
		$yf = intval( date( 'Y' , time() ) );
	}

	if ( isset( $_REQUEST[ 'i_month_from' ] ) ) {
		$mf = intval( $_REQUEST[ 'i_month_from' ] );
	} else {
		$mf = intval( date( 'm' , time() ) );
	}

	$dc = intval( date( 't' , mktime( 0 , 0 , 0 , $mf , 1 , $yf ) ) );
	if ( isset( $_REQUEST[ 'i_day_from' ] ) ) {
		$df = intval( $_REQUEST[ 'i_day_from' ] );
	} else {
		$df = 1 ;
	}
	if ( $df > $dc ) {
		$df = $dc ;
	}

	if ( isset( $_REQUEST[ 'i_year_to' ] ) ) {
		$yt = intval( $_REQUEST[ 'i_year_to' ] );
	} else {
		$yt = intval( date( 'Y' , time() ) );
	}

	if ( isset( $_REQUEST[ 'i_month_to' ] ) ) {
		$mt = intval( $_REQUEST[ 'i_month_to' ] );
	} else {
		$mt = intval( date( 'm' , time() ) );
	}

	$dc = intval( date( "t" , mktime( 0 , 0 , 0 , $mt , 1 , $yt ) ) );
	if ( isset( $_REQUEST[ "i_day_to" ] ) ) {
		$dt = intval( $_REQUEST[ "i_day_to" ] );
	} else {
		$dt = 31 ;
	}
	if ( $dt > $dc ) {
		$dt = $dc ;
	}

	$cookieDomain = $dbConfig[ 'engine.addresses.cookieDomain' ];
	setcookie( 'ot4et1_ds' , date( 'd.m.Y'  , mktime( 0 , 0 , 0 , $mf , $df , $yf ) ) , time() + 7 * 86400 , '/' , $cookieDomain , '0' );
	setcookie( 'ot4et1_de' , date( 'd.m.Y'  , mktime( 0 , 0 , 0 , $mt , $dt , $yt ) ) , time() + 7 * 86400 , '/' , $cookieDomain , '0' );
	setcookie( 'ot4et1_cc' , implode( ',' , $_REQUEST[ "i_case_cat" ] ) , time() + 7 * 86400 , '/' , $cookieDomain , '0' );

	$wAll = $portalDB->query( "select * from `workers` where `first_id` = ? order by `id` asc" , false , "i" , intval( $_REQUEST[ "i_worker" ] ) );

	$wa = array();
	$w = array( "dep" => -1 , "name" => "f=;i=;o=;" );
	foreach( $wAll as $wd ) {
		$wa[]= $wd[ "id" ];
		$w[ "dep" ] = $wd[ "dep" ];
		$w[ "name" ] = $wd[ "name" ];
	}

	$q = "
		select
			`t1`.`id` ,
			`t1`.`exp_type` ,
			`t4`.`name` as `agency` ,
			`t5`.`name` as `agent` ,
			`t1`.`ex_data_3` ,
			`t1`.`ex_data_4` ,
			`t3`.`price` ,
			`t3`.`pay_date` ,
			`t3`.`pay_details` ,
			`t3`.`sndz`
		from
			`matincoming` as `t1` ,
			`matincominglvl2` as `t2` ,
			`expertize` as `t3` ,
			`agency` as `t4` ,
			`agent` as `t5`
		where
			( `t3`.`state` = 1 ) and
			( `t3`.`fin_date` between ? and ? ) and
			( `t3`.`exp_id` in ( ?* ) ) and
			( `t1`.`exp_type` in ( ?* ) ) and
			( `t2`.`id` = `t3`.`ext_id` ) and
			( `t1`.`id` = `t2`.`mat_id` ) and
			( `t1`.`from_agency` = `t4`.`id` ) and
			( `t1`.`from_agent` = `t5`.`id` )
		order by `t1`.`id`" ;

	$res = $portalDB->query( $q , false , "ss*i*i" , $yf."-".$mf."-".$df , $yt."-".$mt."-".$dt , $wa , $_REQUEST[ "i_case_cat" ] );
	$i = 1 ;

	$mIDL = array_column( $res , 'id' );

	$docsLinks = $portalDB->query( "select * from `documents` where ( `ext_id` in ( ?* ) ) and ( `ext_type` = 'docs' )" , false , '*s' , $mIDL );
	$docsMap = remap( $docsLinks , 'ext_id' );


	if ( $formatXLSX ) {
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment;filename="Отчет по людям '.date( 'Y.m.d H-i' , $REPORT_TIME ).'.xlsx"' );
		$xlsx = new TSimpleXLSXTemplate( './files/tmpl.ot4et.xlsx' );

		$xlsx->selectSheet( 'Отчет' );

		$rowIndex = 0 ;

		foreach( $res as $r ) {
			$mID = $r[ 'id' ];
			if ( isset( $docsMap[ $mID ] ) ) {
				$dl = $docsMap[ $mID ];
			} else {
				$dl = array();
			}

			$dl = array_map( function( $v ) {
				return '<a href="/documents.php?download='.$v[ 'id' ].'" target="_blank">doc</a>' ;
			} , $dl );

			$tri = ( $rowIndex + 6 );
			$xlsx->setCellValue( 'A'.$tri , $rowIndex + 1 );
			$xlsx->setCellValue( 'B'.$tri , matincomingNumberFull( $mID  , null , $r[ 'exp_type' ] ) );
			$xlsx->setCellValue( 'C'.$tri , $r[ 'agency' ].', '.$r[ 'agent' ].', '.$r[ 'ex_data_3' ].( $r[ 'sndz' ] ? ' [ СНДЗ ]' : '' ) );
			$xlsx->setCellValue( 'D'.$tri , $r[ 'ex_data_4' ] );
			$xlsx->setCellValue( 'E'.$tri , $r[ 'pay_details' ] );
			$xlsx->setCellValue( 'F'.$tri , $r[ 'price' ] );
			$xlsx->setCellValue( 'G'.$tri , $r[ 'pay_date' ] );
			$rowIndex++ ;
			//break ;
		}

		$xlsx->setCellValue( 'D1' , 'с '.$df.' '.inForm( $MonthNames[ $mf - 1 ] , 2 ).$yf.' года' );
		$xlsx->setCellValue( 'D2' , 'по '.$dt.' '.inForm( $MonthNames[ $mt - 1 ] , 2 ).$yt.' года' );
		$xlsx->setCellValue( 'A3' , 'По '.$dbConfig[ 'org.name.short' ] );
		$xlsx->setCellValue( 'D3' , $TAB_DEPARTMENTS[ $w[ 'dep' ] ][ 'name' ] );
		$xlsx->setCellValue( 'D4' , 'Эксперт '.NAMES_Format( NAMES_parse( $w[ 'name' ] ) ) );

		$xlsx->write();
	} else {
		MainHead_Print( "" , array( "%UT/ot4et.css" ) );

		echo '<table class="MainTable" align="center">
			<caption>
				Отчет
			</caption>
			<tr class="strk1">
				<td class="stolb6" colspan="3">
					О выполнеиии экспертиз за
				</td>
				<td class="stolb7" colspan="4">
					с <b>'.$df.' '.inForm( $MonthNames[ $mf - 1 ] , 2 ).'</b> <b>'.$yf.' года</b><br>
					по <b>'.$dt.' '.inForm( $MonthNames[ $mt - 1 ] , 2 ).'</b> <b>'.$yt.' года</b>
				</td>
			</tr>
			<tr class="strk1">
				<td class="stolb6" rowspan="2" colspan="3">
					По '.$dbConfig[ 'org.name.short' ].'
				</td>
				<td class="stolb7" colspan="4">
					'.$TAB_DEPARTMENTS[ $w[ 'dep' ] ][ 'name' ].'
				</td>
			</tr>
			<tr  class="strk1">
				<td class="stolb7" colspan="4">Эксперт '.NAMES_Format( NAMES_parse( $w[ 'name' ] ) ).'</td>
			</tr>
			<tr  class="strk2">
				<td class="stolb1">
					№ пп
				</td>
				<td class="stolb2">
					№
				</td>
				<td class="stolb3">
					От кого
				</td>
				<td class="stolb3p">
					Номер дела и пр.
				</td>
				<td class="stolb4">
					Плательщик
				</td>
				<td class="stolb5">
					Стоим.
				</td>
				<td class="stolb5-2">
					Дата оплаты
				</td>
			</tr>' ;

			foreach( $res as $r ) {
				$mID = $r[ 'id' ];
				if ( isset( $docsMap[ $mID ] ) ) {
					$dl = $docsMap[ $mID ];
				} else {
					$dl = array();
				}

				$dl = array_map( function( $v ) {
					return '<a href="/documents.php?download='.$v[ 'id' ].'" target="_blank">doc</a>' ;
				} , $dl );

				echo '<tr class="strk2">
					<td class="stolb1">
						'.$i.'
					</td>
					<td class="stolb2">
						<a href="main.php?idlist='.$mID.'" target="_blank">'.matincomingNumberFull( $mID , null , $r[ 'exp_type' ] ).'
					</td>
					<td class="stolb3">
						'.$r[ 'agency' ].', '.$r[ 'agent' ].', '.$r[ 'ex_data_3' ].( $r[ 'sndz' ] ? ' <div class="sndz-label">СНДЗ</div>' : '' ).' 
					</td>
					<td class="stolb3p">
						'.$r[ 'ex_data_4' ].' 
					</td>
					<td class="stolb4">'.$r[ 'pay_details' ].'</td>
					<td class="stolb5">'.money_format( '%!i' , $r[ 'price' ] ).'</td>
					<td class="stolb5-2">'.$r[ 'pay_date' ].'</td>
					<td class="stolb6">'.implode( ' ' , $dl ).'</td>
				</tr>' ;
				$i++ ;
			}

			echo '</table>' ;
			closeHtml_Print();
		}
