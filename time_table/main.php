<?php
/*
	Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
	Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
	copyright (c) Пекшев Петр Александрович, 2008
*/

	//insert into `time-table` ( `id`,`date`,`destination`,`purpose`,`experts`) select `COL 1`,unix_timestamp(str_to_date(`COL 2`,"%e.%c.%Y %k:%i:%s")),`COL 3`,`COL 4`,`COL 5` from `TABLE 2`
	require_once( '../core.php' );
	/**
	 * @var TDB $portalDB
	 * @var $LoginOk
	 * @var $UserID
	 * @var $UserRights
	 * @var $UserThemeLoc
	 * @var $UserWorkerID
	 * @var $UserWorkerFirstID
	 * @var $MonthNames
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	require_once( 'tt-core.php' );
	/**
	 * @var $ttDescr
	 * @var $ttType
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	$visTTList = array();
	foreach( $ttDescr as $cttt => &$cttd ) {
		$row = $portalDB->simpleRow( 'access-rights' , array( 'user_id' => $UserID , 'place' => $cttd[ 'placeID' ] ) );
		if ( $row !== false ) {
			$Rights = ParseRights( strtoupper( $row[ 'rights' ] ) );
			if ( array_key_exists( $cttd[ 'ruleSet' ] , $Rights ) ) {
				$Rights = $Rights[ $cttd[ 'ruleSet' ] ];
			} else {
				$Rights = array();
			}
			$cttd[ '__rights' ] = $Rights ;
			$visTTList[]= $cttt ;
		} else {
			$cttd[ '__rights' ] = false ;
		}
	} unset( $cttd );

	$GoOut = ( count( $visTTList ) == 0 );
	if ( $GoOut ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit();
	}

	$ct = time();
	$cm = date( 'm' , $ct );
	$cy = date( 'Y' , $ct );

	if ( isset( $_REQUEST[ 'y' ] ) ) {
		$sy = intval( $_REQUEST[ 'y' ] );
		if ( $sy < 2000 ) {
			$sy = 2000 ;
		}
	} else {
		$sy = $cy ;
	}


	MainHead_L2( 'Графики' , 'Графики' , array( '../%UT/buttons.css' , '%UT/main.css' ) , array( '#var UserThemeLoc = "'.$UserThemeLoc.'" ; var typeIDs = [ '.implode( ' , ' , $visTTList ).' ]; var openedBlocks = { "type-'.$ttType.'" : { "y-'.$sy.'" : { "m-'.$cm.'" : 1 } } }; var lastOpenedYear = { "type-'.$ttType.'" : '.$sy.' };' , 'files/main.js' ) , 'hlp/main.html' );

	$tabWorkers = $portalDB->table( 'workers' , 'id' );

	echo '<p align="center">
		<span class="c">построено <span class="cD">' , date( 'd-m-Y' , $ct ) , '</span> в <span class="cT">' , date( 'H:i:s' , $ct ) , '</span></span>
	</p>' ;

	echo '<div class="tt-tabs">' ;
	foreach( $visTTList as $cvtt ) {
		echo '<input id="tt-tab-'.$cvtt.'" name="tt-tabs" type="radio" '.( $ttType == $cvtt ? 'checked="checked"' : '' ).' data-ttt="'.$cvtt.'">' ;
	}
	foreach( $visTTList as $cvtt ) {
		echo '<label for="tt-tab-'.$cvtt.'">'.$ttDescr[ $cvtt ][ 'labelText' ].'</label>' ;
	}

	echo '<span></span>' ;

	foreach( $visTTList as $cttt ) {
		$cttd = $ttDescr[ $cttt ];
		$Rights = $cttd[ '__rights' ];
		$mayAdd       = in_array( 'ADD'        , $Rights );
		$mayEdit      = in_array( 'EDIT'       , $Rights );
		$mayEditAll   = in_array( 'EDIT-ALL'   , $Rights );
		$mayDelete    = in_array( 'DELETE'     , $Rights );
		$mayDeleteAll = in_array( 'DELETE-ALL' , $Rights );
		$mayFill      = in_array( 'FILL'       , $Rights );

		$years = $portalDB->query( "select year( from_unixtime( `date` ) ) as `year` from `time-table` where ( `date` is not null ) and ( `type` = ? )  group by `year` order by `year` desc ;" , false , 'i' , $cttt );
		$syfd = mktime( 0 , 0 , 0 , 1 , 1 , $sy );
		$syld = mktime( 23 , 59 , 59 , 12 , 31 , $sy );
		$months = $portalDB->query( "select month( from_unixtime( `date` ) ) as `month` from `time-table` where ( `date` is not null ) and ( `date` >= ? ) and ( `date` <= ? ) and ( `type` = ? ) group by `month` order by `month` asc ;" , false , 'iii' , $syfd , $syld , $cttt );

		echo '<div>' ;

		echo ( $mayAdd ? '<a onclick="showAddRecordDlg( '.$cttt.' )" class="btn3">добавить</a>' : '' ) ,
		( $cttt == 0 && $mayFill ? '<a onclick="showFillDlg( '.$cttt.' )" class="btn3">заполнить</a>' : '' ) ,
		'<br>' ;

		echo '<div id="year-area--'.$cttt.'">' ;
			foreach( $years as $yRow ) {
				if ( $sy == $yRow[ 'year' ] ) {
					echo '<a class="c-year-link" data-type="'.$cttt.'" data-year="'.$yRow[ 'year' ].'">'.$yRow[ 'year' ].'</a>' ;
				} else {
					echo '<a class="year-link" data-type="'.$cttt.'" data-year="'.$yRow[ 'year' ].'">'.$yRow[ 'year' ].'</a>' ;
				}
			}
		echo '</div>' ;    

		echo '<br><br>' ;
		echo '<table id="tt-'.$cttt.'" class="tt" align="center">' ;
		$rno = 0 ;

		if ( $cttt == $ttType ) {
			foreach ( $months as $mm ) {
				$mm = intval( $mm[ 'month' ] );
				$tmyID = $cttt.'_'.$mm.'_'.$sy ;
				echo '<tr id="ttLIST_cap_'.$tmyID.'">
					<td unselectable="on" class="ttMonth" onclick="tc( ' , $mm , ' , ' , $sy , ' , ' , $cttt , ' )">
						<img id="cttbimg_' , $tmyID , '" border=0 src="themes/' , $UserThemeLoc , '/' , ( ( $mm == $cm ) && ( $sy == $cy ) ? 'col' : 'exp' ) , '.bmp">' ,
						ucfirst( inForm( $MonthNames[ $mm - 1 ] ) ) ,
					'</td>
				</tr>
				<tr id="ttLIST_' , $tmyID , '"' , ( ( $mm == $cm ) && ( $sy == $cy ) ? '' : ' style="display : none ;"' ) , '>
					<td class="ttl">
						<div>
							<table id="ttLIST_t_' , $tmyID , '" class="ttlt">' ;

								if ( ( $mm == $cm ) && ( $sy == $cy ) ) {
									echo '<tr>
											<td class="ttcapt_c1">
											</td>
											<td class="ttcapt_c2">
												дата
											</td>
											<td class="ttcapt_c3">
												куда (адрес)
											</td>
											<td class="ttcapt_c4">
												цель выезда
											</td>
											<td class="ttcapt_c5">
												эксперт
											</td>
										</tr>' ;

									$smfd = mktime(0 , 0 , 0 , $cm , 1 , $cy );
									$smdc = date('t' , $smfd );
									$smld = mktime(23 , 59 , 59 , $cm , $smdc , $cy );

									$res = $portalDB->query( "select * from `time-table` where ( `date` is not null ) and ( `date` >= ? ) and ( `date` <= ? ) and ( `type` = ? ) order by `date` asc ;" , false , 'iii' , $smfd , $smld , $cttt );

									foreach ( $res as &$row ) {
										$dt = $row[ 'date' ];
										$dow = date('w' , $dt );
										$dow = $dow == 0 ? 7 : $dow ;

										echo '<tr class="ttDoW' , $dow , '">' ,
										'<td id="ttITEM_'.$row[ 'id' ].'" class="ttdc_1">' ,
										( ( $mayDelete && $row[ 'exp_id' ] == $UserWorkerFirstID ) || $mayDeleteAll ? '<input type="checkbox" name="ttli[]" value="'.$row[ 'id' ].'">' : '' ),
										( ( $mayEdit && $row[ 'exp_id' ] == $UserWorkerFirstID ) || $mayEditAll ? '<a onclick="showEditRecordDlg( '.$row[ 'id' ].' , '.$cttt.' )"><img src="themes/'.$UserThemeLoc.'/edit.gif"></a>' : '' ) ,
										'</td>' ,
										'<td class="ttdc_2">' , date('d-m-Y' , $dt ) , '</td>' ,
										'<td class="ttdc_3">' , ClearOutputText( $row[ 'destination' ] ) , '</td>' ,
										'<td class="ttdc_4">' , ClearOutputText( $row[ 'purpose' ] ) , '</td>' ,
										'<td class="ttdc_5">' , $row[ 'experts' ] , '</td>' ,
										'</tr>' ;
									}
								}
							echo '</table>
						</div>
					</td>
				</tr>';
			}
		}
		echo '</table>' , ( $mayDelete || $mayDeleteAll ? '<button onclick="deleteRecords( '.$cttt.' )" class="btn" title="Удалить отмеченные записи">удалить</button>' : '' );
		echo '</div>' ;
	}

	echo '</div>' ;

	echo '<div id="add_record_dlg" class="add-record-dlg" style="display : none ;">
		<div class="add-record-dlg-close-box"><img src="themes/'.$UserThemeLoc.'/btn_close.bmp" border="0" onclick="hideAddRecordDlg();" title="Закрыть"></div>
		<div class="add-record-dlg-cont">
			<table class="ardc-t">
				<tr>
					<td class="ardc-f1">Дата <input id="i_date" type="text" value="' , date( 'd-m-Y' , $ct ) , '" class="i-date"></td>
					<td class="ardc-f2">Цель выезда <input id="i_purpose" type="text" value="" class="i-purpose"></td>
				</tr>
			</table>
			<table class="ardc-t">
				<tr>
					<td class="ardc-f3">Адрес</td>
					<td class="ardc-f4"><input id="i_destination" type="text" value="" class="i-destination"></td>
				</tr>
			</table>
			<table class="ardc-t">
				<tr>
					<td class="ardc-f5">Ф.И.О. эксперта</td>
					<td class="ardc-f6"><input id="i_experts" type="text" value="'.NAMES_Format( NAMES_parse( $tabWorkers[ $UserWorkerID ][ 'name' ] ) ).'" class="i-experts"></td>
				</tr>
			</table>
			<div class="ardc-pannel">
				<button id="add_record_dlg_btn" onclick="addRecordDlg_Add()">Добавить</button>
			</div>
		</div>
	</div>' ;

	echo '<div id="fill_dlg" class="fill-dlg" style="display : none ;">
		<div class="fill-dlg-close-box"><img src="themes/'.$UserThemeLoc.'/btn_close.bmp" border="0" onclick="hideFillDlg();" title="Закрыть"></div>
		<div class="fill-dlg-cont">
			<table class="fdc-m-panel" align="center">
				<tr>
					<td><a onclick="fillDlg_ChangeMonth( -1 )" class="fdc-m-l"><img src="themes/'.$UserThemeLoc.'/ab1-b.png"></a></td>
					<td><span id="fill_dlg_cont_month" class="fdc-m-m"></span></td>
					<td><a onclick="fillDlg_ChangeMonth( 1 )" class="fdc-m-l"><img src="themes/'.$UserThemeLoc.'/af1-b.png"></a></td>
				<tr>
			</table>
			<table id="fill_dlg_cont_tab" class="fdc-t" align="center">
			</table>
			<div class="fdc-pannel">
				<button onclick="fillDlg_Fill()">Заполнить</button>
			</div>
		</div>
	</div>' ;

	//fixTimerData( "core" );
	closeHtml();
?>