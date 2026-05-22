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

	if ( isset( $_REQUEST[ 'exp-id-list-all' ] ) && $_REQUEST[ 'exp-id-list-all' ] == 'all'  ) {
		$workersFIDList = $portalDB->query( "select `t1`.`first_id` from `workers-no-spec` as `t1` group by `t1`.`first_id`" , 'first_id' );
		$workersFIDList = array_column( $workersFIDList , 'first_id' );
	} else
	if ( isset( $_REQUEST[ 'exp-id-list' ] ) ) {
		$workersFIDList = getIDList( implode( ',' , $_REQUEST[ 'exp-id-list' ] ) , 1 );
	} else {
		$workersFIDList = false ;
	}

	$tabWorkersLast = $portalDB->query( "select `t1`.* from `workers-no-spec` as `t1` left join `workers-no-spec` as `t2` on ( ( `t1`.`first_id` = `t2`.`first_id` ) and ( `t1`.`id` < `t2`.`id` ) ) where ( `t2`.`id` is null ) order by `actual` desc , `name` asc" , 'first_id' );
	foreach( $tabWorkersLast as &$wrk ) {
		$wrk[ 'name' ] = NAMES_Format( NAMES_parse( $wrk[ 'name' ] ) );
	} unset( $wrk );

	if ( $workersFIDList !== false ) {
		$WD = array();
		foreach( $workersFIDList as $wrk ) {
			$WD[]= '<b>'.$tabWorkersLast[ $wrk ][ 'name' ].'</b>' ;
		}
		$WD = implode( ', ' , $WD );
		MainHead_Print( '' , array( '%UT/expertize.report.deadline.css' ) );
		//MainHead_L2( '' , '' , array( '%UT/expertize.report.deadline.css' ) );
		$year= intval( date( 'Y' , time() ) );
		$month= intval( date( 'm' , time() ) );
		$lastDay = intval( date( 't' , mktime( 12 , 0 , 0 , $month , 1 , $year ) ) );
		$taw = $portalDB->simpleQuery( 'workers-no-spec' , array( 'first_id' => $workersFIDList ) );
		$targetAllWorkers = array();
		foreach( $taw as $w ) {
			$targetAllWorkers[]= $w[ "id" ];
		}

		$tabAllWorkers = $portalDB->table( 'workers-no-spec' , 'id' );
		foreach( $tabAllWorkers as &$wrk ) {
			$wrk[ 'name' ] = NAMES_Format( NAMES_parse( $wrk[ 'name' ] ) );
		} unset( $wrk );

		//print_r_html( $targetAllWorkers );

		//$portalDB->dbgMode = true ;

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
       			`t5`.`id` as `lvl3id` ,
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

		$groupIDList = array_unique( array_column( $res , 'group_id' ) );

		$groupDatesRes = $portalDB->query( "select `t1`.`group_id` , min( `t1`.`date` ) as `min-date` from `matincoming` as `t1` where ( `t1`.`group_id` is not null ) and ( `t1`.`group_id` <> 0 ) and ( `t1`.`group_id` in ( ?* ) )  group by `t1`.`group_id`" , 'group_id' , '*i' , $groupIDList );
		//print_r_html( $groupDatesRes );

		$lvl3idList = array_column( $res , 'lvl3id' );
		$lvl3Comments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = 'expertize' ) and ( `ext_id` in ( ?* ) )" , false , '*i' , $lvl3idList );
		$lvl3CommentsMap = remap( $lvl3Comments , 'ext_id' );

		$lvl1idList = array_column( $res , 'id' );
		$lvl1Comments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = 'matincoming' ) and ( `ext_id` in ( ?* ) )" , false , '*s' , $lvl1idList );
		$lvl1CommentsMap = remap( $lvl1Comments , 'ext_id' );

		$resCnt = $portalDB->query( "select count( `id` ) as `cnt` , `group_id` from `matincoming` where ( `group_id` in ( ?* ) ) and ( `group_id` is not null ) and ( `group_id` <> 0 )  group by `group_id` having ( `cnt` > 1 )" , 'group_id' , '*i' , $groupIDList );

		$showExpNameColumn = count( $workersFIDList ) > 1 ;

		echo '<center>Список <b>невыполненых</b> экспертиз '.$WD.'</center><br>' ;
		$col = 1 ;
		echo '<table class="MainTable" align="center">
			<tr>
				<td class="cap1">Номер<br>экспертизы</td>
				<td class="cap1">Дата поступления</td>
				<td class="cap1">Дней прошло</td>
				'.( $showExpNameColumn ? '<td class="cap1">Эксперт</td>' : '' ).'
				<td class="cap2">От кого поступили материалы, постановление и д.р.</td>
				<td class="cap2">Номера дела; Количество томов, страниц, приложений; Ф.И.О. лиц,<br>привлекаемых к ответственности,сторон по делу</td>
				<td class="cap2">Сведения о приостановлении срока производства экспертизы</td>
				<td class="cap2">Дата сдачи заключения, акта, сообщения, письма о возврате без исполнения и материалов для отправки</td>
				<td class="cap2">Коммент</td>
			</tr>
			<tr>
				<td class="cap1">'.( $col++ ).'</td>
				<td class="cap1">'.( $col++ ).'</td>
				<td class="cap1">'.( $col++ ).'</td>
				'.( $showExpNameColumn ? '<td class="cap1">'.( $col++ ).'</td>' : '' ).'
				<td class="cap2">'.( $col++ ).'</td>
				<td class="cap2">'.( $col++ ).'</td>
				<td class="cap2">'.( $col++ ).'</td>
				<td class="cap2">'.( $col++ ).'</td>
				<td class="cap2">'.( $col++ ).'</td>
			</tr>' ;

		$cTime = time();

		foreach( $res as $row ) {
			$grID = $row[ 'group_id' ];
			$cwfid = $tabAllWorkers[ $row[ 'exp_id' ] ][ 'first_id' ];

			$comments = array();
			$lvl1ID = $row[ 'id' ];
			if ( isset( $lvl1CommentsMap[ $lvl1ID ] ) ) {
				foreach( $lvl1CommentsMap[ $lvl1ID ] as $cd ) {
					$wrk = $tabAllWorkers[ $cd[ 'exp_id' ] ];
					$comments[]= '<div class="comment-area"><span class="comment-text">'.$cd[ 'comment' ].'</span><span class="comment-author">'.$wrk[ 'name' ].'</span></div>' ;
				}
			}
			$lvl3ID = $row[ 'lvl3id' ];
			if ( isset( $lvl3CommentsMap[ $lvl3ID ] ) ) {
				foreach( $lvl3CommentsMap[ $lvl3ID ] as $cd ) {
					$wrk = $tabAllWorkers[ $cd[ 'exp_id' ] ];
					$comments[]= '<div class="comment-area"><span class="comment-text">'.$cd[ 'comment' ].'</span><span class="comment-author">'.$wrk[ 'name' ].'</span></div>' ;
				}
			}

			if ( count( $comments ) > 0 ) {
				$comments = implode( $comments );
			} else {
				$comments = '' ;
			}

			$groupMinDate = strtotime( $row[ 'date' ] );
			if ( isset( $groupDatesRes[ $grID ] ) ) {
				$groupMinDate = strtotime( $groupDatesRes[ $grID ][ 'min-date' ] );
			}

			$lvl1Date = strtotime( $row[ 'date' ] );

			echo '<tr>
				<td class="dat1">
					<div>'.matincomingNumber( $row[ 'id' ] ).' '.( isset( $resCnt[ $grID ] ) ? '( '.$resCnt[ $grID ][ 'cnt' ].' )' : '' ).'</div>
					<div>
						<a href="main.php?idlist='.$row[ 'id' ].'" class="lnk" target="_blank">Ур.1</a>
						<a href="expertize.php?edit='.$row[ 'lvl3id' ].'" class="lnk" target="_blank">Ур.3</a>
					</div>
				</td>
				<td class="dat1">'.date( 'd-m-Y' , $lvl1Date ).( $lvl1Date <> $groupMinDate ? '<br/><span style="color : red ;">'.date( 'd-m-Y' , $groupMinDate ).'</span>' : '' ).'</td>
				<td class="dat1">'.( (int) ( ( $cTime - $lvl1Date ) / 86400 ) ).( $lvl1Date <> $groupMinDate ? '<br/><span style="color : red ;">'.( (int) ( ( $cTime - $groupMinDate ) / 86400 ) ).'</span>' : '' ).'</td>
				'.( $showExpNameColumn ? '<td class="dat1">'.$tabWorkersLast[ $cwfid ][ 'name' ].'</td>' : '' ).'
				<td class="dat2">'.$row[ 'ex_data_3' ].', '.$row[ 'agency' ].', '.$row[ 'agent' ].'</td>
				<td class="dat2">'.$row[ 'ex_data_4' ].'</td>
				<td class="dat2">'.$row[ 'ex_data_7' ].'</td>
				<td class="dat2">'.$row[ 'ex_data_8' ].', '.$row[ 'ex_data_9' ].'</td>
				<td class="dat2">'.$comments.'</td>
			</tr>' ;
		}
		echo '</table></div>' ;
		echo '<br><b>Обшее количество : </b>'.count( $res );
		//closeHtml();
		closeHtml_Print();
	} else {
		MainHead_L2( '' , '' , array( '%UT/expertize.report.deadline.css' ) , array() , '' );

		$LK = '' ;

		$expList = array();
		foreach( $tabWorkersLast as $wrk ) {
			$cA = $wrk[ 'actual' ] == 1 ? 1 : 0 ;
			$cFL = substr( $wrk[ 'name' ] , 0 , 1 );
			$cK = $cA.$cFL ;
			if ( $cK != $LK ) {
				$LK = $cK ;
				$expList[ $LK ] = array( '<div class="exp-name-group-name">'.$cFL.'</div>' );
			}
			$expList[ $LK ][]= '<div class="exp-name"><label class="exp-name'.( $cA == 1 ? '' : ' not-actual' ).'"><input type="checkbox" name="exp-id-list[]" value="'.$wrk[ 'first_id' ].'"> '.$wrk[ 'name' ].'</label></div>' ;
		}
		foreach( $expList as $gn => &$group ) {
				$group = '<div class="exp-name-group">'.implode( $group ).'</div>' ;
		} unset( $group );

		echo '<form method="post" action="expertize.report.deadline.php">
			<div class="exp-name-area">
				<input type="checkbox" id="i-exp-id-list-all" name="exp-id-list-all" value="all">
				<label class="exp-name" for="i-exp-id-list-all"> Все<div class="exp-name-all-slider"></div></label>
				'.implode( $expList ).'
			</div>
			<div class="toolbar"><input type=submit value="OK"></div>
		</form>' ;

		closeHtml();
     }
