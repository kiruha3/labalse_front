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
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserPost
	 * @var $UserName
	 * @var $dbConfig
	 * @var $MonthNames
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );

	//tmpl.report.annual.appendix-5

	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'EXTENTIONS' , $Rights ) ) {
			$mayListAll = in_array( 'EXP-EXP-LIST-ALL-REV' , $Rights[ 'EXTENTIONS' ] );
		} else {
			$mayListAll = false ;
		}

		$GoOut = !$mayListAll ;
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit();
	}
	
	if ( isset( $_REQUEST[ 'ds' ] ) && isset( $_REQUEST[ 'de' ] ) && isset( $_REQUEST[ 'd' ] ) ) {
		
		$REPORT_TIME = time();
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment;filename="Годовой отчет - приложение 5 '.date( 'Y.m.d H-i' , $REPORT_TIME ).'.xlsx"' );
		
		$xlsx = new TSimpleXLSXTemplate( './files/tmpl.report.annual.appendix-5.xlsx' );
		$xlsx->selectSheet( 'Лист1' );
		
		if ( $_REQUEST[ 'ds' ] == 'manual' ) {
			$tmp = str_replace( ',' , '.' , $_REQUEST[ 'mds' ] );
			$tmp = str_replace( '-' , '.' , $tmp );
			$tmp = explode( '.' , $tmp );
			$ds = $tmp[ 2 ].'/'.$tmp[ 1 ].'/'.$tmp[ 0 ];
		} else {
			$ds = $_REQUEST[ 'ds' ];
		}

		if ( $_REQUEST[ 'de' ] == 'manual' ) {
			$tmp = str_replace( ',' , '.' , $_REQUEST[ 'mde' ] );
			$tmp = str_replace( '-' , '.' , $tmp );
			$tmp = explode( '.' , $tmp );
			$de = $tmp[ 2 ].'/'.$tmp[ 1 ].'/'.$tmp[ 0 ];
		} else {
			$de = $_REQUEST[ 'de' ];
		}
		
		error_log( 'DBG: ds: '.$ds.'   de: '.$de );

		if ( $_REQUEST[ 'd' ] == 'all' ) {
			$workers = $portalDB->query( "select `id` , `name` , `spec` , `first_id` from `workers` where ( `id` <> 0 ) order by `name` asc ;" , 'id' );
		} else {
			$workers = $portalDB->query( "select `id` , `name` , `spec` , `first_id` from `workers` where `dep` = ? order by `name` asc" , 'id' , 'i' , intval( $_REQUEST[ 'd' ] ) );
		}
		$workersIDList = array_column( $workers , 'id' );
		error_log( 'DBG: workersIDList: '.implode( ',' , $workersIDList ) );
		$workersFID = $portalDB->query( "select `name` , `first_id` from `workers-no-spec` order by `name` asc" , 'first_id' );
		foreach( $workersFID as &$w ) {
			$w[ 'name' ] = NAMES_Format( NAMES_parse( $w[ 'name' ] ) , '%F1 %I1 %O1' );
		} unset( $w );
		$specs = $portalDB->query( "select `t2`.`id` , concat( `t1`.`index` , '.' , `t2`.`num` ) as `spec` , concat( `t1`.`index` , '.' , `t2`.`num` , if ( `t2`.`comment` is null , '' , concat( ' (' , `t2`.`comment` , ')' ) ) ) as `spec_ex` , `norm1` , `norm2` , `norm3` , `norm4` from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id` = `t2`.`group` )" , 'id' );

		$depData = $portalDB->simpleRow( "departments" , intval( $_REQUEST[ 'd' ] ) );
		$depName = $depData[ 'name' ];

		$postData = $portalDB->simpleRow( "posts" , $UserPost );

		$tabCaseCategory = $portalDB->query( "select * from `casecategory`" , 'id' );
		
		$ksIndex = $ksIndexWS = array( 1 , 2 , 3 , 4 ); // индексы категорий сложности
		array_unshift( $ksIndexWS , 'sum' , 'norm' ); // индексы категорий сложности с индексом для суммы и нормочасов
		$ccIndex = array();  // индексы категорий дела
		$ccTypeMap = array();
		$ccTypeIndex = array( 'exp' , 'res' );
		foreach( $tabCaseCategory as $cd ) {
			$ccIndex[]= $cd[ 'id' ];
			$ccTypeMap[ $cd[ 'id' ] ] = $ccTypeIndex[ $cd[ 'type' ] ];
		}
		$ccIndexWS = $ccIndex ;
		array_unshift( $ccIndexWS , 'sum' );  // индексы категорий дела с индексом для суммы*/
		
		$resNorm = array();
		$totalRes = array();

		$tspc = $portalDB->query( "select * from `specialities` where `use_in_stat` = 1 order by `".FSOF."` , `group` , `num`" , "id" );
		$tspcIDArray = array_keys( $tspc );
		
		$q = "select
			`t3`.`exp_id` ,
			`t3`.`spec_id` ,
			count( `t3`.`id` ) as `count` ,
			`t1`.`exp_type` ,
			`t2`.`kat_slognost` ,
       		`t3`.`sndz` ,
       		sum( `t2`.`accounting_time` ) as `norm`
		from
			`expertize` as `t3` ,
			`matincominglvl2` as `t2` ,
			`matincoming` as `t1`
		where
			( `t3`.`fin_date` between ? and ? ) and
			( `t3`.`state` = 1 ) and
		    ( `t3`.`exp_id` in ( ?* ) ) and
		    ( `t3`.`use_in_stat` = 1 ) and
			( `t2`.`id` = `t3`.`ext_id` ) and
			( `t1`.`id` = `t2`.`mat_id` ) and
		    ( `t1`.`state` <> -2 ) and
		    ( `t1`.`date` is not null ) and
			( `t2`.`kat_slognost` >= 1 ) and
		    ( `t3`.`spec_id` in ( ?* ) )
		group by
			`t1`.`exp_type` ,
			`t2`.`kat_slognost` ,
			`t3`.`spec_id` ,
			`t3`.`exp_id` ,
			`t3`.`sndz`" ;
		
		$res = $portalDB->query( $q , false , 'ss*i*i' , $ds , $de , $workersIDList , $tspcIDArray );
		
		// [worker.first_id][spec_id][casecategory][kat_slognost]
		
		foreach ( $res as $row ) {
			$wID = $row[ 'exp_id' ];
			$w = $workers[ $wID ];
			
			$wFID = $w[ 'first_id' ];
			if ( !isset( $totalRes[ $wFID ] ) ) {
				$totalRes[ $wFID ] = array();
			}
			
			$specID = $row[ 'spec_id' ];
			if ( !isset( $totalRes[ $wFID ][ $specID ] ) ) {
				$totalRes[ $wFID ][ $specID ] = array();
				foreach ( $ccTypeIndex as $i ) {
					$totalRes[ $wFID ][ $specID ][ $i ] = array();
					foreach ( $ksIndexWS as $j ) {
						$totalRes[ $wFID ][ $specID ][ $i ][ $j ] = 0 ;
					}
					$totalRes[ $wFID ][ $specID ][ 'sndz' ] = 0 ;
					$totalRes[ $wFID ][ $specID ][ 'sndz_norm' ] = 0 ;
				}
			}
			
			$expType = $ccTypeMap[ $row[ 'exp_type' ] ];
			$v = $row[ 'count' ];
			$n = $row[ 'norm' ];
			
			if ( $row[ 'sndz' ] != 1 ) {
				$totalRes[ $wFID ][ $specID ][ $expType ][ $row[ 'kat_slognost' ] ]+= $v ;
				$totalRes[ $wFID ][ $specID ][ $expType ][ 'sum' ]+= $v ;
				$totalRes[ $wFID ][ $specID ][ $expType ][ 'norm' ]+= $n ;
			} else {
				$totalRes[ $wFID ][ $specID ][ 'sndz' ]+= $v ;
				$totalRes[ $wFID ][ $specID ][ 'sndz_norm' ]+= $n ;
			}
		}

		// удаление строк где все 0
		foreach( $totalRes as $wfid => $wr ) {
			foreach( $wr as $sid => $sr ) {
				$su = $sr[ 'sndz' ] != 0 ;
				foreach ( $ccTypeIndex as $i ) {
					foreach ( $ksIndex as $j ) {
						$su|= $sr[ $i ][ $j ] != 0 ;
					}
				}
				if ( !$su ) {
					unset( $totalRes[ $wfid ][ $sid ] );
				}
			}
			if ( count( $totalRes[ $wfid ] ) == 0 ) {
				unset( $totalRes[ $wfid ] );
			}
		}
		
		$ri = 1 ;
		
		uksort( $totalRes , function( $a , $b ) use ( $workersFID ) {
			return strcmp( $workersFID[ $a ][ 'name' ] , $workersFID[ $b ][ 'name' ] );
		} );
		
		foreach( $totalRes as $wFID => &$res ) {
			uksort( $res , function( $a , $b ) use ( $specs ) {
				$sa = str_pad( $specs[ $a ][ 'spec' ] , 10 , 0 , STR_PAD_LEFT );
				$sb = str_pad( $specs[ $b ][ 'spec' ] , 10 , 0 , STR_PAD_LEFT );
				return strcmp( $sa , $sb );
			} );
			foreach( $res as $specID => $row ) {
				$xlsxRI = ( $ri + 5 );
				$xlsx->setCellValue( 'A'.$xlsxRI , $ri );
				$xlsx->setCellValue( 'B'.$xlsxRI , $workersFID[ $wFID ][ 'name' ] );
				$xlsx->setCellValue( 'C'.$xlsxRI , $specs[ $specID ][ 'spec_ex' ] , true );
				$xlsx->setCellValue( 'D'.$xlsxRI , $row[ 'exp' ][ 'sum' ] );
				$xlsx->setCellValue( 'E'.$xlsxRI , $row[ 'exp' ][ 1 ] );
				$xlsx->setCellValue( 'F'.$xlsxRI , $row[ 'exp' ][ 2 ] );
				$xlsx->setCellValue( 'G'.$xlsxRI , $row[ 'exp' ][ 3 ] );
				$xlsx->setCellValue( 'H'.$xlsxRI , $row[ 'exp' ][ 4 ] );
				$xlsx->setCellValue( 'I'.$xlsxRI , $row[ 'res' ][ 'sum' ] );
				$xlsx->setCellValue( 'J'.$xlsxRI , $row[ 'res' ][ 1 ] );
				$xlsx->setCellValue( 'K'.$xlsxRI , $row[ 'res' ][ 2 ] );
				$xlsx->setCellValue( 'L'.$xlsxRI , $row[ 'res' ][ 3 ] );
				$xlsx->setCellValue( 'M'.$xlsxRI , $row[ 'res' ][ 4 ] );
				$xlsx->setCellValue( 'N'.$xlsxRI , $row[ 'sndz' ] );
				$xlsx->setCellValue( 'O'.$xlsxRI , $row[ 'exp' ][ 'norm' ] );
				$xlsx->setCellValue( 'P'.$xlsxRI , $row[ 'res' ][ 'norm' ] );
				$xlsx->setCellValue( 'Q'.$xlsxRI , $row[ 'sndz_norm' ] );
				$xlsx->setCellValue( 'R'.$xlsxRI , 0 );
				$ri++ ;
			}
		} unset( $res );
		
		$xlsx->write();
    } else {
		$row = $portalDB->row( "select YEAR( min( `date` ) ) as `miy` , YEAR( max( `date` ) ) as `may` from `matincoming` where `date` is not null" );
		
		$dates = range( $row[ 'miy' ] , $row[ 'may' ] );
		rsort( $dates );
		foreach ( $dates as &$ym ) {
			$ym = '<a class="rpdl" onclick="showRep( '.$ym.' );">01.01.'.$ym.' - 31.12.'.$ym.'</a>' ;
		} unset( $ym );

		MainHead_L2( 'База' , '<a href="./">База</a> - Статистика' , array( '%UT/viborka.css' ) , array( 'files/report-annual.appendix-5.js' ) , 'htl/main.php' );
		echo '<br><br><br><br><br>
		<form method="post" action="">
			<table align="center" class="IT">
				<tr>
					<td class="L">
						Отдел
					</td>
					<td class="D">
						<select id="d" size="1" name="d">
							<option value="all">--- ВСЕ ---</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="L">
						Отчетный период
					</td>
					<td class="D">
						<div class="rpd">'.implode( ' | ' , $dates ).'</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="B">
						<input type="submit" value="Вывести статистику">
					</td>
				</tr>
			</table>
		</form>' ;

		closeHtml();
 	}
