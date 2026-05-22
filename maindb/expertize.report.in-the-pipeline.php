<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
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

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'EXTENTIONS' , $Rights ) ) {
			$mayLIST = in_array( 'EXP-EXP-LIST' , $Rights[ 'EXTENTIONS' ] );
			$mayLIST_ALL = in_array( 'EXP-EXP-LIST-ALL' , $Rights[ 'EXTENTIONS' ] );
		} else {
			$mayLIST = $mayLIST_ALL = false ;
		}
		$GoOut = !( $mayLIST | $mayLIST_ALL );
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/main.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit ;
	}

	if ( isset( $_GET[ 'i_worker' ] ) ) {
		$worker = intval( $_GET[ 'i_worker' ] );
	} else {
		$worker = false ;
	}

	if ( $worker !== false ) {
		MainHead_Print( '' , array( '%UT/expertize.report.in-the-pipeline.css' ) );
		$year= intval( date( 'Y' , time() ) );
		$month= intval( date( 'm' , time() ) );
		$lastDay = intval( date( 't' , mktime( 12 , 0 , 0 , $month , 1 , $year ) ) );
			$WD = $portalDB->simpleRow( 'workers' , $worker );
			$taw = $portalDB->simpleQuery( 'workers' , array( 'first_id' => $WD[ 'first_id' ] ) );
		$targetAllWorkers = array();
		foreach( $taw as $w ) {
			$targetAllWorkers[]= $w[ "id" ];
		}

		//print_r_html( $targetAllWorkers );

		$res = $portalDB->query( "select
				`t1`.`id` ,
				`t1`.`date` ,
				`t1`.`ex_data_3` ,
				`t1`.`ex_data_4` ,
				`t1`.`group_id` ,
				`t2`.`name` as `agency` ,
				`t3`.`name` as `agent`,
				`t1`.`ex_data_7`,
				`t1`.`ex_data_8`,
				`t1`.`ex_data_9`,
				`t5`.`ext_id`,
				`t5`.`exp_id`,
				`t5`.`state`
			from
				`matincoming` as `t1` ,
				`agency` as `t2` ,
				`agent` as `t3` ,
				`matincominglvl2` as `t4`,
				`expertize` as `t5`
			where
				( `t1`.`state` <> -2 ) and
				( `t5`.`exp_id` in ( ?* ) ) and
				( `t4`.`id` = `t5`.`ext_id` ) and
				( `t1`.`id` = `t4`.`mat_id` ) and
				( `t1`.`from_agency` = `t2`.`id` ) and
				( `t1`.`from_agent` = `t3`.`id` ) and
				( `t1`.`date` between '2010-01-01' and '$year-$month-$lastDay' ) and
				( ( `t5`.`state` is null ) or ( `t5`.`state` = 0 ) )
			order by
				`t1`.`date` asc" , false , "*i" , $targetAllWorkers );

		$resGroups = array();
		foreach ( $res as &$cres ) {
			$grID = $cres[ "group_id" ];
			if ( !is_null( $grID ) ) {
				$resGroups[ $grID ] = $grID ;
			}
		} unset( $cres );

			//echo  $dep."fdsf";

		$resCnt = $portalDB->query( "select count( `id` ) as `cnt` , `group_id` from `matincoming` where ( `group_id` in ( ?* ) ) group by `group_id` having ( `cnt` > 1 )" , "group_id" , "*i" , array_keys( $resGroups ) );
		unset( $resCnt[ 0 ] );
		//linkTablesIntoTree($a0, $a1, $a1k, $a0nk)

		echo "<center>Список <b>невыполненых</b> экспертиз ".NAMES_Format( NAMES_parse( $WD[ "name" ] ) , "%F2 %i.%o." )."</center><br>" ;
		echo "<table class=\"MainTable\" align=\"center\">
			<tr>
				<td class=\"cap1\">Порядковый номер<br>экспертизы</td>
				<td class=\"cap1\">Дата поступления</td>
				<td class=\"cap2\">От кого поступили материалы, постановление и д.р.</td>
				<td class=\"cap2\">Номера дела; Количество томов, страниц, приложений; Ф.И.О. лиц,<br>привлекаемых к ответственности,сторон по делу</td>
				<td class=\"cap2\">Сведения о приостановлении срока производства экспертизы</td>
				<td class=\"cap2\">Дата сдачи заключения, акта, сообщения, письма о возврате без исполнения и материалов для отправки </td>
			</tr>
			<tr>
				<td class=\"cap1\">1</td>
				<td class=\"cap1\">2</td>
				<td class=\"cap2\">3</td>
				<td class=\"cap2\">4</td>
				<td class=\"cap2\">5</td>
				<td class=\"cap2\">6</td>
			</tr>" ;

		foreach( $res as $row ) {
			$grID = $row[ "group_id" ];
			echo "<tr>
				<td class=\"dat1\"><a href=\"main.php?idlist=".$row[ "id" ]."\" class=\"lnk\" target=\"_blank\">".matincomingNumber( $row[ "id" ] )."</a>".( isset( $resCnt[ $grID ] ) ? "( ".$resCnt[ $grID ][ "cnt" ]." )" : "" )."</td>
				<td class=\"dat1\">".date( "d-m-Y" , strToTime( $row[ "date" ] ) )."</td>
				<td class=\"dat2\">".$row[ "ex_data_3" ].", ".$row[ "agency" ].", ".$row[ "agent" ]."</td>
				<td class=\"dat2\">".$row[ "ex_data_4" ]."</td>
				<td class=\"dat2\">".$row[ "ex_data_7" ]."</td>
				<td class=\"dat2\">".$row[ "ex_data_8" ].", ".$row[ "ex_data_9" ]."</td>
			</tr>" ;
		}
		echo "</table></div>" ;
		echo "<br><b>Обшее количество : </b>".count( $res );
		if ( count( $res ) == 0 ) {
			echo "<style type=\"text/css\">
				.block {
					width : 600px ;
					background : #fc0 ;
					padding : 5px ;
					border : solid 1px black ;
					float : center ;
					position : relative ;
					top : 60px ;
					left : 350px ;
					font-size : 25pt ;
				}
			</style>" ;
		}
	} else {
		MainHead_L2( "" , "" , array() , array() , "" );

		$tabWorkers = $portalDB->query( "select * from `workers` where  (`spec` is not null) and ( `actual` = 1 ) order by `name` ;" );
		$tabPosts = $portalDB->table( "posts" , "id" );
		$WorkersList = "<select id=\"i_worker\" name=\"i_worker\" class=\"i_worker\">";
		for ( $i = 0 ; $i < count( $tabWorkers ) ; $i++ ) {
			$WorkersList.= "<option value=\"".$tabWorkers[ $i ][ "id" ]."\">".NAMES_Format( NAMES_parse( $tabWorkers[ $i ][ "name" ] ) )."</option>" ;
		}
		$WorkersList.= "</select>" ;

		echo "<form method=\"get\" action=\"expertize.report.in-the-pipeline.php\">
			<br><br><br><br><br><br>
			<div align=\"center\">
			<table>
				<tr>
					<td><b>Нужный человек</b></td>
					<td>$WorkersList</td>
				</tr>
				<tr>
					<td colspan=2>
						<input type=submit value=\"OK\">
					</td>
				</tr>
			</table>
		</div>" ;

		closeHtml();
     }
