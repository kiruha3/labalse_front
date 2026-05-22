<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	use Marks\updateMarks ;

	include_once( '../core.php' );
	/**
	 * @var TDB $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserAllWorkers
	 * @var $UserID
	 * @var $dbConfig
	 */
	require_once( 'lconfig.php' );
	require_once( '../cores/core.maindb.php' );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/data-bank.php' );
	require_once( 'request.core.php' );
	require_once( '../equipment.core.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	$mayExpertizeEDIT = false ;
	$mayExpertizeCORRECT = false ;
	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'EXPERTIZE' , $Rights ) ) {
			$mayExpertizeEDIT = in_array( 'EDIT' , $Rights[ 'EXPERTIZE' ] );
			$mayExpertizeCORRECT = in_array( 'CORRECT_AFTER_CLOSE' , $Rights[ 'EXPERTIZE' ] );
		}
	}
	
	$access = ( isset( $_REQUEST[ 'edit' ] ) || isset( $_REQUEST[ 'mode' ] ) ) && $mayExpertizeEDIT ;
	$modeAJAX = isset( $_REQUEST[ 'mode' ] );

	if ( !$access || $modeAJAX ) {
		ErrorPageAndExit();
	}

	$cTime = time(); // UTC
	$lvlC1C2C3row = false ;
	$expertize_id = false ;
	if ( isset( $_GET[ 'edit' ] ) ) {
		$expertize_id = intval( $_GET[ 'edit' ] );
		$lvlC1C2C3row = $portalDB->row( "select `t1`.* , `t2`.`mat_id` , `t2`.`dep_id` , `t3`.`exp_type` , `t3`.`date` as `lvl1c-date` , `t3`.`group_id` from `expertize` as `t1` , `matincominglvl2` as `t2` , `matincoming` as `t3` where ( ( `t1`.`id` = ? ) and ( `t1`.`ext_id` = `t2`.`id` ) and ( `t2`.`mat_id` = `t3`.`id` ) )" , 'i' , $expertize_id );
	}

	if ( $lvlC1C2C3row === false ) {
		Redirect( 'main.php' );
		exit ;
	}

	$tabWorkers = $portalDB->table( 'workers' , 'id' );
	$tabEquipment = $portalDB->query( "select `t2`.`id` , `t1`.`name` , `t1`.`label` , `t1`.`reg-number` , IFNULL( `t2`.`state` , 0 ) as `state` , `t1`.`filter_rules` from `equipment` as `t1` , `exp-equipment` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) order by `label`" , 'id' );
	$tabSubstances = $portalDB->query( "select `t2`.`id` , `t2`.`ext_id` , `t1`.`name` , `t1`.`label`, `t1`.`unit` , IFNULL( `t2`.`state` , 0 ) as `state` from `substances` as `t1` , `substances-in-stock` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) order by `label`" , 'id' );
	$tabNorms = $portalDB->query( "select * from `substances-norms` ;" , 'id' );

	$mList = '<option value=""></option>' ;
	foreach ( $tabSubstances as $us ) {
		if ( intval( $us[ 'state' ] ) != -1 ) {
			$mList.= '<option value="'.$us[ 'id' ].'">'.$us[ 'label' ].'</option>' ;
		}
	}

	$snm = array();
	foreach( $tabNorms as &$sn ) {
		$sid = $sn[ 'ext_id' ];
		if ( !isset( $snm[ $sid ] ) ) {
			$snm[ $sid ] = array();
		}
		$snm[ $sid ][]= &$sn ;
	} unset( $sn );

	$tabUnusedSubpoenas = $portalDB->query( "select `t1`.* , `t4`.`name` as `agency` from `subpoena` as `t1` left join `subpoena-experts` as `t2` on `t1`.`id` = `t2`.`s_id` left outer join `subpoena-addressee` as `t3` on `t1`.`id` = `t3`.`s_id` left join `agency` as `t4` on `t1`.`agency_id` = `t4`.`id` where ( `t2`.`exp_id` in ( ?* ) )" , false , '*i' , $UserAllWorkers );

	$sList = '<option value=""></option>' ;
	foreach ( $tabUnusedSubpoenas as $tus ) {
		$tusText = '№ '.subpoenaNumber( $tus[ 'id' ] ).( date( 'Y' , $cTime ) != date( 'Y' , $tus[ 'date' ] ) ? ' от '.date( 'd-m-Y' , $tus[ 'date' ] ) : '' ).' : '.date( 'd-m-Y' , $tus[ 'to_date' ] ).' к '.date( 'H:i' , $tus[ 'to_date' ] ).' в '.$tus[ 'agency' ];
		$sList.= '<option value="'.$tus[ 'id' ].'">'.$tusText.'</option>' ;
	}


	$tabDocTemplates = $portalDB->query( "select * from `doc-templates` where ( `user_id` is null ) or ( `user_id` = ? ) order by `id`" , false , 'i' , $UserID );

	$tabReturnReasons = $portalDB->table( 'matincoming-return-reasons' , 'id' );

	$rowMarkNoPay = false ;
	$groupLVL1C = false ;
	$selectMarks = array();

	$comments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = 'expertize' ) and ( `ext_id` = ? );" , false , "i" , $expertize_id );
	$eul = $portalDB->query( "select * from `exp-equipment-usage` where ( `ext_id` = ? ) order by `start` asc ;" , false , "i" , $expertize_id );
	$ml = $portalDB->query( "select * from `exp-substances-usage` where ( `ext_id` = ? ) order by `date` ;" , false , "i" , $expertize_id );
	$pl_2 = $portalDB->query( "select `t1`.* , 'subpoena-payments' as `__res_type` , `t2`.`date` , `t2`.`to_date` , `t2`.`agency` , `t3`.`state` from `subpoena-addressee` as `t1` , `subpoena-list` as `t2` , `payments` as `t3` where ( `t3`.`expertize_id` = ? ) and ( `t1`.`s_id` = `t2`.`id` ) and ( `t1`.`p_id` = `t3`.`id` ) order by `t2`.`date` ;" , false , 'i' , $expertize_id );

	$pl_1 = array();
	$pl_3 = array();

	$pl = $pl_1 + $pl_2 + $pl_3 ;
	usort( $pl , function( $a , $b ) {
		return $a[ 'date' ] - $b[ 'date' ];
	} );
	//$pl_2 = $portalDB->query( "select `t1`.* , 'subpoena-payments' as `__res_type` , `t2`.`date` , `t2`.`to_date` , `t2`.`agency` , `t3`.`state` from `subpoena-addressee` as `t1` , `subpoena-list` as `t2` , `payments` as `t3` where ( `t3`.`expertize_id` = ? ) and ( `t1`.`s_id` = `t2`.`id` ) and ( `t1`.`p_id` = `t3`.`id` ) order by `t2`.`date` ;" , false , "i" , $expertize_id );

	$bl = $portalDB->query( "select `t1`.* , sum( `t2`.`price` ) as `sum` from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t1`.`exp_id` = ? ) group by `t1`.`id` " , false , "i" , $expertize_id );

	//var_dump( $lvlC1C2C3row );

	$lvl2c_mat_id   = $lvlC1C2C3row[ 'mat_id' ];
	$lvl2c_dep_id   = $lvlC1C2C3row[ 'dep_id' ];
	$lvl1c_exp_type = $lvlC1C2C3row[ 'exp_type' ];
	$lvl1c_date     = strtotime( $lvlC1C2C3row[ 'lvl1c-date' ] ); // local ??

	$selectMarks = Marks\getMarks( $lvl2c_mat_id , 'matincoming' );

	if ( $lvlC1C2C3row[ 'group_id' ] != 0 ) {
		$groupLVL1C = $portalDB->simpleQuery( 'matincoming' , array( 'group_id' => $lvlC1C2C3row[ 'group_id' ] ) );
	} else {
		$groupLVL1C = array(
			$lvlC1C2C3row
		);
	}

	extract( array_rekey( $lvlC1C2C3row , '/(.+)/' , 'expertize_${1}' , "{ext_id,spec_id,reason_{1,2}{,_comment},price,conclusion{,_1,_2{,_1,_2,_3{,_comment}},_3},sndz,pay_{date,details}}" ) , EXTR_OVERWRITE );
	/**
	 * @var $expertize_reason_1
	 * @var $expertize_reason_2
	 */

	$expertize_finished = $lvlC1C2C3row[ 'state' ] == 1 ;
	$expertize_woexecution = $lvlC1C2C3row[ 'state' ] == 2 ;
	$expertize_sndz = $lvlC1C2C3row[ 'sndz' ] == 1 ;
	$expertize_closed = $expertize_finished || $expertize_woexecution ;

	$expertize_fin_date_date = !$expertize_closed ? $cTime : strtotime( $lvlC1C2C3row[ 'fin_date' ] );
	$expertize_fin_date = date( 'd-m-Y' , $expertize_fin_date_date );


	$expertize_reason_1_0 = !is_null( $lvlC1C2C3row[ 'reason_1' ] ) && $expertize_reason_1 == 0 ? 'checked' : '' ;
	$expertize_reason_1_1 = ( $expertize_reason_1 & 1 )  > 0 ? 'checked' : '' ;
	$expertize_reason_1_2 = ( $expertize_reason_1 & 2 )  > 0 ? 'checked' : '' ;
	$expertize_reason_1_3 = ( $expertize_reason_1 & 4 )  > 0 ? 'checked' : '' ;
	$expertize_reason_1_4 = ( $expertize_reason_1 & 8 )  > 0 ? 'checked' : '' ;
	$expertize_reason_1_5 = ( $expertize_reason_1 & 16 ) > 0 ? 'checked' : '' ;
	$expertize_reason_1_6 = ( $expertize_reason_1 & 32 ) > 0 ? 'checked' : '' ; // Значит объем исслед

	$expertize_reason_2_0 = is_null( $expertize_reason_2 ) || $expertize_reason_2 == 0 ? 'checked' : '' ;

	if ( is_null( $lvlC1C2C3row[ 'application_for_issuance' ] ) ) {
		$expertize_afi_no = '' ;
		$expertize_afi_yes = '' ;
	} else {
		$expertize_afi_no = ( $lvlC1C2C3row[ 'application_for_issuance' ] == '1' ? '' : 'checked' );
		$expertize_afi_yes = ( $lvlC1C2C3row[ 'application_for_issuance' ] == '1' ? 'checked' : '' );
	}

	$rowMarkNoPay = $portalDB->simpleRow( 'marks-objects' , array(
		'ext_type' => 'matincoming' ,
		'ext_id'   => $lvl2c_mat_id ,
		'mark_id'  => $dbConfig[ CFG_MATINCOMING_MARK_NOPAY ]
	) );

	$dataBank = fillDataBank2(
		array(
			'req:id' => $expertize_id ,
			'tmpl-data' => false ,
			'tmpl-list-name' => 'expertize' ,
			'equipment-list-name' => 'expertize' ,
			'post-init' => array(
				function( &$a , &$b ) {
					loadVariables2_post_init_tmpl( $a , $b );
				} ,
				function( &$a , &$b ) {
					loadVariables2_post_init_equipment( $a , $b );
				}
			),
		) , $dbConfig[ CFG_DATABANK_DEFAULT_EXPERTIZE ]
	);

	$eqList = '<option value=""></option>' ;
	foreach ( $tabEquipment as $ue ) {
		if ( ( intval( $ue[ 'state' ] , 10 ) != -1 ) && checkFilter( $ue[ 'filter_rules' ] , $dataBank ) ) {
			$eqList.= '<option value="'.$ue[ 'id' ].'">'.$ue[ 'label' ].' ( '.$ue[ 'reg-number' ].' )</option>' ;
		}
	}

	MainHead_L2( 'База' , '<a href="main.php">База</a> - карточка 3' ,
		array( '../%UT/buttons.css' , '%UT/expertize.css' , '/doc-generator/%UT/forms.css' ) ,
		array(
			'files/expertize.js' ,
			'#'.implode(
				preg_replace( '/^(.+)$/' , 'var $1 ;' , array(
						'incDY = '.matincomingYear( $lvl2c_mat_id ) ,
						'incDate = '.$lvl1c_date ,
						'isCCGZ = '.( isCCGZ( $lvl1c_exp_type ) ? 1 : 0 ) ,
						'isMarkNoPay = '.( $rowMarkNoPay !== false ? 'true' : 'false' ) ,
						'expClosedNoCorrect = '.( $expertize_closed && !$mayExpertizeCORRECT ? '{ "date" : '.$expertize_fin_date_date.' }' : 'false' ) ,
						'finDateDeltaLowLimit  = '.( $mayExpertizeCORRECT ? '"no-check"' : ( isset( $dbConfig[ CFG_MATINCOMING_FINDATE_DELTALIMIT_LOW  ] ) ? intval( $dbConfig[ CFG_MATINCOMING_FINDATE_DELTALIMIT_LOW  ] , 10 ) : 0 ) ) ,
						'finDateDeltaHighLimit = '.( $mayExpertizeCORRECT ? '"no-check"' : ( isset( $dbConfig[ CFG_MATINCOMING_FINDATE_DELTALIMIT_HIGH ] ) ? intval( $dbConfig[ CFG_MATINCOMING_FINDATE_DELTALIMIT_HIGH ] , 10 ) : 0 ) )
				) )
			) ,
			'/doc-generator/doc-generator.base.js' ,
			'/doc-generator/doc-generator.js'
		), 'hlp/expertize.html' );

	echo '<form id="PostForm" action="processor.php?expertizeedit='.$expertize_id.'" method="post" name="PostForm">
		<div class="legend"><span class="req">*</span> - обязательные для заполнения поля</div>' ;

	if ( $expertize_closed && !$mayExpertizeCORRECT ) {
	} else {
		echo '<input id="i_state__0"  name="i_state" type="radio" value="0" '.( !$expertize_closed                        ? 'checked' : '' ).' class="i_finished_radio"/>
		<input id="i_state__1"  name="i_state" type="radio" value="1" '.( $expertize_finished && !$expertize_sndz         ? 'checked' : '' ).' class="i_finished_radio"/>
		<input id="i_state__10" name="i_state" type="radio" value="10" '.( $expertize_finished && $expertize_sndz         ? 'checked' : '' ).' class="i_finished_radio"/>
		<input id="i_state__2"  name="i_state" type="radio" value="2" '.( $expertize_woexecution                          ? 'checked' : '' ).' class="i_finished_radio"/>' ;
	}

	echo '<table align="center" class="PT">
		<tr id="row--exp-num">
			<td class="D">
				Номер экспертизы
			</td>
			<td class="I">
				<a href="main.php?idlist='.$lvl2c_mat_id.'" class="mat1lvl-lnk" target="_blank">'.matincomingNumberFull( $lvl2c_mat_id , $lvl2c_dep_id , $lvl1c_exp_type ).'</a>'.( $groupLVL1C !== false && count( $groupLVL1C ) > 1 ? ' [ комплексная ]' : '' ).'
				'.Marks\integrate( array( $dbConfig[ CFG_MARK_GROUP_MATINCOMING ] ) , array( 'mark-name-attr' => 'i_marks' , 'mode' => 'show' ) , $selectMarks ).'
			</td>
		</tr>' ;

	if ( $expertize_closed && !$mayExpertizeCORRECT ) {
		$wDays = ceil( ( $expertize_fin_date_date - $lvl1c_date ) / 86400 ) + 1 ;
		echo '<tr id="row--exp-state">
				<td class="D">
					Состояние экспертизы
				</td>
				<td class="I">
					'.( $expertize_finished ? ( $expertize_sndz ? 'СНДЗ' : 'Окончена' ) : 'Без исполнения' ).' [ <span id="i_fin_date">'.$expertize_fin_date.'</span> , '.$wDays.' дней с даты регистрации '.date( 'd-m-Y' , $lvl1c_date ).' ]
				</td>
			</tr>
			<tr>
				<td class="D">
					В срок, свыше 30 дней, по причине
				</td>
				<td class="I">
					'.( $expertize_reason_1_1 != '' ? '<div> сложности и многообъектности</div>' : '' ).'
					'.( $expertize_reason_1_6 != '' ? '<div> объем (исследование значительного объема материалов)</div>' : '' ).'
					'.( $expertize_reason_1_2 != '' ? '<div> загруженности экспертов</div>' : '' ).'
					'.( $expertize_reason_1_4 != '' ? '<div> требований методик</div>' : '' ).'
					'.( $expertize_reason_1_5 != '' ? '<div> согласования с органом (лицом), назначившим экспертизу</div>' : '' ).'
					'.( $expertize_reason_1_3 != '' ? '<div> по иным причинам : '.$expertize_reason_1_comment.'</div>' : '' ).'
				</td>
			</tr>' ;
		if ( $expertize_woexecution ) {
			$reason = $tabReturnReasons[ $expertize_reason_2 ];
			echo '<tr><td class="D">
				Возвращено без производства экспертизы по причине
			</td>
			<td class="I">
				<div>'.inForm( $reason[ 'name' ] , 2 ).'</div>
				<div>'.$expertize_reason_2_comment.'</div>
			</td></tr>' ;
		} else
		if ( $expertize_finished && !$expertize_sndz ) {
			echo '<tr>
				<td class="D">
					Поставлено вопросов
				</td>
				<td class="I">
					'.$expertize_conclusion.'
				</td>
			</tr>
			<tr>
				<td class="D">
					Дано категорических выводов
				</td>
				<td class="I">
					'.$expertize_conclusion_1.'
				</td>
			</tr>
			<tr>
				<td class="D">
					дано вероятных выводов
				</td>
				<td class="I">
					'.$expertize_conclusion_3.'
				</td>
			</tr>
			<tr>
				<td class="D">
					невозможно решить вопросов
				</td>
				<td class="I">
					'.$expertize_conclusion_2.'
				</td>
			</tr>
			<tr>
				<td class="D" colspan="2">
					из них по причинам:
				</td>
			</tr>
			<tr>
				<td class="D">
					недостаточности разработанности методик
				</td>
				<td class="I">
					'.$expertize_conclusion_2_1.'
				</td>
			</tr>
			<tr>
				<td class="D">
					недостаточности представленных материалов
				</td>
				<td class="I">
					'.$expertize_conclusion_2_2.'
				</td>
			</tr>
			<tr>
				<td class="D">
					по иным причинам ( указать причины )
				</td>
				<td class="I">
					'.$expertize_conclusion_2_3.'<br/>
					'.$expertize_conclusion_2_3_comment.'
				</td>
			</tr>' ;
		}
	} else {
		echo '<tr id="row--exp-state">
				<td class="D">
					Состояние экспертизы
				</td>
				<td class="I">
					<div class="i_state_labels">
						<label id="i_state__0__label"  for="i_state__0" >В производстве</label>
						<label id="i_state__1__label"  for="i_state__1" >Окончена</label>
						<label id="i_state__10__label" for="i_state__10">СНДЗ</label>
						<label id="i_state__2__label"  for="i_state__2" >Без исполнения</label>
					</div>
				</td>
			</tr>
			<tr id="row--fin-date">
				<td class="D">
					Дата <span class="state__ok__text">окончания</span><span class="state__cancel__text">возврата</span> <span class="req">*</span>
				</td>
				<td class="I">
					<input id="i_fin_date" name="i_fin_date" type="text" value="'.$expertize_fin_date.'" class="i_fin_date" oninput="finDateInput()"/> <span id="i_fin_date_comment"></span>
				</td>
			</tr>
			<tr id="row--reasons-1" data-show-fin-date-reason="0">
				<td class="D">
					В срок, свыше 30 дней, по причине
				</td>
				<td class="I">
					<input                   name="i_reason_1[]" type="checkbox" '.$expertize_reason_1_1.' value="1"  class="i_reason_1_1"/> сложности и многообъектности<br>
					<input                   name="i_reason_1[]" type="checkbox" '.$expertize_reason_1_6.' value="32" class="i_reason_1_6"/> объем (исследование значительного объема материалов)<br>
					<input                   name="i_reason_1[]" type="checkbox" '.$expertize_reason_1_2.' value="2"  class="i_reason_1_2"/> загруженности экспертов<br>
					<input                   name="i_reason_1[]" type="checkbox" '.$expertize_reason_1_4.' value="8"  class="i_reason_1_4"/> требований методик<br>
					<input                   name="i_reason_1[]" type="checkbox" '.$expertize_reason_1_5.' value="16" class="i_reason_1_5"/> согласования с органом (лицом), назначившим экспертизу<br>
					<input id="i_reason_1_a" name="i_reason_1[]" type="checkbox" '.$expertize_reason_1_3.' value="4"  class="i_reason_1_3"/> по иным причинам <br>
					<textarea id="i_reason_1_comment" name="i_reason_1_comment" class="i_reason_1_comment">'.$expertize_reason_1_comment.'</textarea>
				</td>
			</tr>
			<tr id="row--conclusion">
				<td class="D">
					Поставлено вопросов <span class="req">*</span>
				</td>
				<td class="I">
					<input id="i_conclusion" name="i_conclusion" type="text" value="'.$expertize_conclusion.'" class="i_conclusion"/>
				</td>
			</tr>
			<tr id="row--conclusion-1">
				<td class="D">
					Дано категорических выводов <span class="req">*</span>
				</td>
				<td class="I">
					<input id="i_conclusion_1" name="i_conclusion_1" type="text" value="'.$expertize_conclusion_1.'" class="i_conclusion_1"/>
				</td>
			</tr>
			<tr id="row--conclusion-3">
				<td class="D">
					дано вероятных выводов <span class="req">*</span>
				</td>
				<td class="I">
					<input id="i_conclusion_3" name="i_conclusion_3" type="text" value="'.$expertize_conclusion_3.'" class="i_conclusion_3"/>
				</td>
			</tr>
			<tr id="row--conclusion-2">
				<td class="D">
					невозможно решить вопросов <span class="req">*</span>
				</td>
				<td class="I">
					<input id="i_conclusion_2" name="i_conclusion_2" type="text" value="'.$expertize_conclusion_2.'" class="i_conclusion_2"/>
				</td>
			</tr>
			<tr id="row--conclusion-2-0">
				<td class="D" colspan="2">
					из них по причинам:
				</td>
			</tr>
			<tr id="row--conclusion-2-1">
				<td class="D">
					недостаточности разработанности методик <span class="req">*</span>
				</td>
				<td class="I">
					<input id="i_conclusion_2_1" name="i_conclusion_2_1" type="text" value="'.$expertize_conclusion_2_1.'" class="i_conclusion_2_1"/>
				</td>
			</tr>
			<tr id="row--conclusion-2-2">
				<td class="D">
					недостаточности представленных материалов <span class="req">*</span>
				</td>
				<td class="I">
					<input id="i_conclusion_2_2" name="i_conclusion_2_2" type="text" value="'.$expertize_conclusion_2_2.'" class="i_conclusion_2_2"/>
				</td>
			</tr>
			<tr id="row--conclusion-2-3">
				<td class="D">
					по иным причинам ( указать причины )
				</td>
				<td class="I">
					<input id="i_conclusion_2_3" name="i_conclusion_2_3" type="text" value="'.$expertize_conclusion_2_3.'" class="i_conclusion_2_3"/><br>
					<textarea id="i_conclusion_2_3_comment" name="i_conclusion_2_3_comment" class="i_conclusion_2_3_comment">'.$expertize_conclusion_2_3_comment.'</textarea>
				</td>
			</tr>
			<tr id="row--reasons-2">
				<td class="D">
					Возвращено без производства экспертизы по причине
				</td>
				<td class="I">' ;

				if ( !$expertize_closed || ( $mayExpertizeCORRECT && $expertize_reason_2 == 0 ) ) {
					echo '<input id="i_reason_2_ns" name="i_reason_2" type="radio" value="0" '.$expertize_reason_2_0.' data-comment-required="0" class="i_reason_2" onclick="selectReason2()"/> -- указать причину --<br>' ;
				}

				usort( $tabReturnReasons , function( $a , $b ) {
					return $a[ 'order' ] - $b[ 'order' ];
				} );

				foreach( $tabReturnReasons as $reason ) {
					if ( $reason[ 'actual' ] == 1 || $reason[ 'id' ] == $expertize_reason_2 ) {
						echo '<input name="i_reason_2" type="radio" value="'.$reason[ 'id' ].'" '.( $reason[ 'id' ] == $expertize_reason_2 ? 'checked="checked"' : '' ).' data-comment-required="'.$reason[ 'comment_required' ].'" data-comment-placeholder="'.( isset( $reason[ 'comment_placeholder' ] ) ? $reason[ 'comment_placeholder' ] : '' ).'" class="i_reason_2" onclick="selectReason2()"/> '.inForm( $reason[ 'name' ] , 2 ).'<br/>' ;
					}
				}

				echo '<textarea id="i_reason_2_comment" name="i_reason_2_comment" class="i_reason_2_comment">'.$expertize_reason_2_comment.'</textarea>
				</td>
			</tr>' ;
	}

	if ( isCCGZ( $lvl1c_exp_type ) || ( $rowMarkNoPay !== false ) ) {
	} else {
		echo '<tr id="row--price">
		<td class="D">
			Стоимость экспертизы <span class="req">*</span>
		</td>
		<td class="I">' ;

		if ( $dbConfig[ CFG_EXPERTIZE_PRICE_EDITABLE ] == 1 ) {
			echo '<input id="i_price" name="i_price" type="text" value="'.$expertize_price.'" class="i_price"/> руб. ' ;
		} else {
			echo '<div class="i_price">'.money_format( '%!i' , $expertize_price ).' руб. </div>' ;
		}

		if ( count( $bl ) > 0 ) {
			echo '<div class="bills-panel-wrapper"><div class="bills-panel"><a onclick="mkBill( '.$expertize_id.' )" class="bill-lnk">квитанция/счет</a><div class="bill-list">';
			foreach( $bl as $cb ) {
				echo '<a href="/bills/bill.php?edit='.$cb[ 'id' ].'" target="_blank" class="bill-lnk'.( $cb[ 'state' ] == -1 ? ' bill-wrong' : '' ).'">'.$cb[ 'number' ].' от '.date( 'd-m-Y' , strtotime( $cb[ 'date' ] ) ).' : '.$cb[ 'payer' ].' ('.money_format( '%!i' , $cb[ 'sum' ] / 100 ).')</a>' ;
			}
			echo '</div></div></div>' ;
		} else {
			echo '<a onclick="mkBill( '.$expertize_id.' )" class="bill-lnk">квитанция/счет</a>' ;
		}
		echo '</td>
		</tr>
		<tr id="row--pay-details">
			<td class="D">
				Плательщик
			</td>
			<td class="I">
				<textarea name="i_pay_details" class="i_pay_details">'.$expertize_pay_details.'</textarea>
			</td>
		</tr>
		<tr id="row--pay-date">
			<td class="D">
				Дата платежа / номер счета
			</td>
			<td class="I">
				<textarea name="i_pay_date" class="i_pay_date">'.$expertize_pay_date.'</textarea>
			</td>
		</tr>
		<tr id="row--woe-req">
			<td class="D">
				Прикладывается ли заявление о выдаче исполнительного листа ? <span class="req">*</span>
			</td>
			<td class="I">
				<input type="radio" id="i_afi_no" name="i_afi" value="no" '.$expertize_afi_no.'> нет <input type="radio" id="i_afi_yes" name="i_afi" value="yes" '.$expertize_afi_yes.'> да
			</td>
		</tr>' ;
	}

	echo '<tr>
				<td class="D">
					Пометки
				</td>
				<td class="I" data-comment-style="inline" data-comment-ext-type="expertize" data-comment-ext-id="'.$expertize_id.'" data-comment-substyle="e-list" data-comment-v-style-pref="i_comment" data-comment-auto-edit="true">' ;
					$cta = false ;
					foreach ( $comments as $cmt ) {
						if ( in_array( $cmt[ 'exp_id' ] , $UserAllWorkers ) ) {
							echo '<textarea name="i_comment" class="i_comment editor">'.$cmt[ 'comment' ].'</textarea>' ;
							$cta = true ;
						} else {
							echo '<div class="i_comment area"><div class="i_comment text">'.str_replace( "\r\n" , '<br/>' , $cmt[ 'comment' ] ).'</div><div class="i_comment author">'.NAMES_Format( NAMES_parse( $tabWorkers[ $cmt[ 'exp_id' ] ][ 'name' ] ) , '%F1 %i.%o.' ).' , '.date( 'd-m-Y H:i' , $cmt[ 'date' ] ).'</div></div>' ;
						}
					}

					if ( !$cta ) {
						echo '<textarea name="i_comment" class="i_comment editor"></textarea>' ;
					}
				echo '</td>
			</tr>
			
			<tr>
				<td colspan=2 class="btnTB">
    				<input type="button" value="Заменить" class="btn" onclick="doCheckForm();">
				</td>
			</tr>
		</table>
	</form>
	<vrcse-sliding-panel id="eul-dlg" caption="Оборудование" side="right">
		<table id="eul-list-tab" class="dlg-list-tab">
			<tr class="dlg-list-row">
				<td class="dlg-list-h eul-col-dt"></td>
				<td class="dlg-list-h eul-col-dp">Дата и время<br>начала и окончания</td>
				<td class="dlg-list-h eul-col-dn">Наименование прибора</td>
				<td class="dlg-list-h eul-col-dc">Комментарий</td>
			</tr>' ;
			if ( count( $eul ) > 0 ) {
				foreach( $eul as $eq ) {
					echo '<tr class="dlg-list-row" id="eul-tab-row-'.$eq[ 'id' ].'">
						<td class="dlg-list-d eul-col-dt"><a class="dlg-row-delete" onclick="deleteRow( \'eul\' , '.$eq[ 'id' ].' )" title="Удалить"></a></td>
						<td class="dlg-list-d eul-col-dp">
							<span class="dlg-list-d-v">'.date( 'd-m-Y' , $eq[ 'start' ] ).'</span><span class="dlg-list-t-v">'.date( 'H:i' , $eq[ 'start' ] ).'</span><br>
							<span class="dlg-list-d-v">'.date( 'd-m-Y' , $eq[ 'finish' ] ).'</span><span class="dlg-list-t-v">'.date( 'H:i' , $eq[ 'finish' ] ).'</span>
						</td>
						<td class="dlg-list-d eul-col-dn">
							'.$tabEquipment[ $eq[ 'eq_id' ] ][ 'label' ].'
						</td>
						<td class="dlg-list-d eul-col-dc">
							'.$eq[ 'comment' ].'
						</td>
					</tr>' ;
				}
			} else {
				echo '<tr id="eul-row-empty" class="dlg-list-row">
					<td class="dlg-list-d eul-col-e" colspan="4">Нет записей</td>
				</tr>' ;
			}
			echo '<tr id="eul-new-record-row" class="dlg-list-row">
				<td class="dlg-list-d eul-col-dt"></td>
				<td class="dlg-list-d eul-col-dp">
					<input id="eul-nrr-ds" type="text" value="'.date( 'd-m-Y' , $cTime ).'" class="dlg-nrr-d"> <input id="eul-nrr-ts" type="text" value="'.date( 'H:i' , $cTime ).'" class="dlg-nrr-t"><br>
					<input id="eul-nrr-de" type="text" value="'.date( 'd-m-Y' , $cTime ).'" class="dlg-nrr-d"> <input id="eul-nrr-te" type="text" value="'.date( 'H:i' , $cTime ).'" class="dlg-nrr-t">
				</td>
				<td class="dlg-list-d eul-col-dn">
					<select id="eul-nrr-el" class="dlg-nrr-el">'.$eqList.'</select>
				</td>
				<td class="dlg-list-d eul-col-dc">
					<textarea id="eul-nrr-ta" class="dlg-nrr-ta"></textarea>
				</td>
			</tr>
		</table>
		<div id="eul-list-tool-panel" class="dlg-tool-panel">
			<button id="eul-add-btn" class="dlg-add-btn" onclick="doAddPosition( \'eul\' )">Добавить</button>
			<button id="eul-apply-btn" class="dlg-apply-btn" style="display : none ;" onclick="doAddPositionApplyEUL( '.$expertize_id.' )">Принять</button>
			<button id="eul-cancel-btn" class="dlg-cancel-btn" style="display : none ;" onclick="doAddPositionCancel( \'eul\' )">Отмена</button>
		</div>
	</vrcse-sliding-panel>' ;

	echo '<vrcse-sliding-panel id="ml-dlg" caption="Материалы" side="right">
			<table id="ml-list-tab" class="dlg-list-tab">
				<tr class="dlg-list-row">
					<td class="dlg-list-h ml-col-dtb"></td>
					<td class="dlg-list-h ml-col-dd">Дата и время получения</td>
					<td class="dlg-list-h ml-col-dm">Материал / вещество</td>
					<td class="dlg-list-h ml-col-dd2">Предназначение</td>
					<td class="dlg-list-h ml-col-dc">Кол-во</td>
					<td class="dlg-list-h ml-col-dn">Норма</td>
					<td class="dlg-list-h ml-col-dt">Всего</td>
				</tr>' ;
				if ( count( $ml ) > 0 ) {
					foreach( $ml as $me ) {
						$sid = $me[ 's_id' ];
						$nid = $me[ 'n_id' ];
						$cn = $nid != -1 && !is_null( $nid ) && isset( $tabNorms[ $nid ] ) ? $tabNorms[ $nid ] : false ;
						echo '<tr class="dlg-list-row" id="ml-tab-row-'.$me[ 'id' ].'">
							<td class="dlg-list-d ml-col-dtb"><a class="dlg-row-delete" onclick="deleteRow( \'ml\' , '.$me[ 'id' ].' )" title="Удалить"></a></td>
							<td class="dlg-list-d ml-col-dd">
								<span class="dlg-list-d-v">'.date( 'd-m-Y' , $me[ 'date' ] ).'</span><span class="dlg-list-t-v">'.date( 'H:i' , $me[ 'date' ] ).'</span>
							</td>
							<td class="dlg-list-d ml-col-dm">
								'.$tabSubstances[ $sid ][ 'label' ].'
							</td>
							<td class="dlg-list-d ml-col-dd2">
								'.( $cn !== false ? $cn[ 'name' ] : $me[ 'comment' ] ).'
							</td>
							<td class="dlg-list-d ml-col-dc">
								'.( $cn !== false ? $me[ 'count' ] : '1' ).'
							</td>
							<td class="dlg-list-d ml-col-d'.( $cn !== false ? 'n' : 'na' ).'">
								'.( $cn !== false ? $cn[ 'norm' ].' '.$tabSubstances[ $sid ][ 'unit' ] : '&mdash;' ).'
							</td>
							<td class="dlg-list-d ml-col-dt">
								'.number_format( ( $cn !== false ? $cn[ 'norm' ] * $me[ 'count' ] : $me[ 'count' ] ) , 0 , '.' , ' ' ).' '.$tabSubstances[ $sid ][ 'unit' ].'
							</td>
						</tr>' ;
					}
				} else {
					echo '<tr id="ml-row-empty" class="dlg-list-row">
						<td class="dlg-list-d ml-col-e" colspan="7">Нет записей</td>
					</tr>' ;
				}
				echo '<tr id="ml-new-record-row" class="dlg-list-row">
					<td class="dlg-list-d ml-col-dtb"></td>
					<td class="dlg-list-d ml-col-dd">
						<input id="ml-nrr-ds" type="text" value="'.date( 'd-m-Y' , $cTime ).'" class="dlg-nrr-d"> <input id="ml-nrr-ts" type="text" value="'.date( 'H:i' , $cTime ).'" class="dlg-nrr-t">
					</td>
					<td class="dlg-list-d ml-col-dm">
						<select id="ml-nrr-ml" class="dlg-nrr-el" onchange="doSubstanceSelect()">'.$mList.'</select>
					</td>
					<td class="dlg-list-d ml-col-dd2">
						<select id="ml-nrr-dd2l" class="dlg-nrr-el" onchange="doSubstanceNormSelect()"></select><br>
						<textarea id="ml-nrr-dd2ta" class="dlg-nrr-ta" style="display : none ;"></textarea>
					</td>
					<td class="dlg-list-d ml-col-dc">
						<input id="ml-nrr-dc" type="text" value="1" class="ml-nrr-c" onchange="doChangeCount()" onkeyup="doChangeCount()">
					</td>
					<td class="dlg-list-d ml-col-dn">
						<span id="ml-nrr-dn"></span>
					</td>
					<td class="dlg-list-d ml-col-dt">
						<input id="ml-nrr-dt" type="text" value="0" class="ml-nrr-t2">
					</td>
				</tr>
			</table>
			<div id="ml-list-tool-panel" class="dlg-tool-panel">
				<button id="ml-add-btn" class="dlg-add-btn" onclick="doAddPosition( \'ml\' )">Добавить</button>
				<button id="ml-apply-btn" class="dlg-apply-btn" style="display : none ;" onclick="doAddPositionApplyML( '.$expertize_id.' )">Принять</button>
				<button id="ml-cancel-btn" class="dlg-cancel-btn" style="display : none ;" onclick="doAddPositionCancel( \'ml\' )">Отмена</button>
			</div>
		</vrcse-sliding-panel>' ;

	echo '<vrcse-sliding-panel id="pl-dlg" caption="Выезды" side="left">
		<table id="pl-list-tab" class="dlg-list-tab">
			<tr class="dlg-list-row">
				<td class="dlg-list-h pl-col-t"></td>
				<td class="dlg-list-h pl-col-s">Повестка</td>
				<td class="dlg-list-h pl-col-pr">Стоимость</td>
				<td class="dlg-list-h pl-col-pa">Плательщик</td>
				<td class="dlg-list-h pl-col-cmt">Комментарий</td>
				<td class="dlg-list-h pl-col-st">Оплачено</td>
			</tr>' ;
			if ( count( $pl ) > 0 ) {
				foreach( $pl as $pe ) {
					$peText = '№ '.subpoenaNumber( $pe[ 's_id' ] ).( date( 'Y' , $cTime ) != date( 'Y' , $pe[ 'date' ] ) ? ' от '.date( 'd-m-Y' , $pe[ 'date' ] ) : '' ).' : '.date( 'd-m-Y' , $pe[ 'to_date' ] ).' к '.date( 'H:i' , $pe[ 'to_date' ] ).' в '.$pe[ 'agency' ];
					echo '<tr class="dlg-list-row" id="pl-tab-row-'.$pe[ 'id' ].'">
						<td class="dlg-list-d pl-col-t"><a class="dlg-row-delete" onclick="deleteRow( \'pl\' , '.$pe[ 'id' ].' )" title="Удалить"></a></td>
						<td class="dlg-list-d pl-col-s">
							'.$peText.'
						</td>
						<td class="dlg-list-d pl-col-pr">
							'.money_format( '%!i' , ( $pe[ 'price' ] / 100 ) ).'
						</td>
						<td class="dlg-list-d pl-col-pa">
							'.$pe[ 'payer' ].'
						</td>
						<td class="dlg-list-d pl-col-cmt">
							'.$pe[ 'comment' ].'
						</td>
						<td class="dlg-list-d pl-col-st">
						</td>
					</tr>' ;
				}
			} else {
				echo '<tr id="pl-row-empty" class="dlg-list-row">
					<td class="dlg-list-d pl-col-e" colspan="5">Нет записей</td>
				</tr>' ;
			}

			echo '<tr id="pl-new-record-row" class="dlg-list-row">
				<td class="dlg-list-d pl-col-t"></td>
				<td class="dlg-list-d pl-col-s">
					<select id="pl-nrr-sl" class="dlg-nrr-el" onchange="doSubstanceSelect()">'.$sList.'</select>
				</td>
				<td class="dlg-list-d pl-col-pr">
					<input id="pl-nrr-pr" type="text" value="" class="pl-nrr-pr">
				</td>
				<td class="dlg-list-d pl-col-pa">
					<textarea id="pl-nrr-pa" class="dlg-nrr-ta"></textarea>
				</td>
				<td class="dlg-list-d pl-col-cmt">
					<textarea id="pl-nrr-cmt" class="dlg-nrr-ta"></textarea>
				</td>
				<td class="dlg-list-d pl-col-st">
					<span id="pl-nrr-dn"></span>
				</td>
			</tr>
		</table>
		<div id="pl-list-tool-panel" class="dlg-tool-panel">
			<button id="pl-add-btn" class="btn3 dlg-add-btn" onclick="doAddPosition( \'pl\' )">Выход в суд</button>
			<button id="pl-add-btn" class="btn3 dlg-add-btn" onclick="doAddPosition( \'pl\' )">Платный выход в суд</button>
			<button id="pl-add-btn" class="btn3 dlg-add-btn" onclick="doAddPosition( \'pl\' )">Выезд на осмотр</button>
			<button id="pl-apply-btn" class="dlg-apply-btn" style="display : none ;" onclick="doAddPositionApplyPL( '.$expertize_id.' )">Принять</button>
			<button id="pl-cancel-btn" class="dlg-cancel-btn" style="display : none ;" onclick="doAddPositionCancel( \'pl\' )">Отмена</button>
		</div>
	</vrcse-sliding-panel>' ;

	echo '<vrcse-sliding-panel id="tl-dlg" caption="Шаблоны" side="left">
		<table id="tl-list-tab" class="dlg-list-tab">
			<tr class="dlg-list-row">
				<td class="dlg-list-h tl-col-s">Шаблон</td>
			</tr>
			<!--tr class="dlg-list-row">
				<td class="dlg-list-h tl-col-s"><a class="btn1" onclick="createNewTmpl()">создать шаблон</a> <a class="btn1" onclick="showLoadTmplDlg()">загрузить шаблон</a></td>
			</tr-->' ;

			foreach ( $tabDocTemplates as $dt ) {
				if ( checkFilter( $dt[ 'filter_rules' ] , $dataBank ) ) {
					echo '<tr class="dlg-list-row">
						<td class="dlg-list-d tl-col-s">
							<a onclick="genDoc( '.$dt[ 'id' ].' , '.$expertize_id.' , \'preview\' )" class="tl-col-s-a">'.$dt[ 'short_name' ].'</a>
						</td>
					</tr>' ;
				}
			}
		echo '<tr id="tl-new-record-row" class="dlg-list-row"></tr>
		</table>
	</vrcse-sliding-panel>' ;

	closeHtml();
