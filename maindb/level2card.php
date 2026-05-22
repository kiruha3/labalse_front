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
	 * @var $UserID
	 * @var $UserRights
	 * @var $UserDepartment
	 * @var $UserWorkerFirstID
	 * @var $DepAllWorkers
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

	$lvl2cardADD = $lvl2cardEDIT = false ;
	$lvl2card_accTimeCreate = $lvl2card_accTimeEdit = $lvl2card_accTimeView = false ;
	$lvl2card_priceCreate = $lvl2card_priceEdit = $lvl2card_priceView = false ;
	$lvl2card_StatEIPOnlyDep = $lvl2card_OnlyDep = false ;
	$lvl2card_ChangeDateMatExpSpecAfterOrder = false ;

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'LVL2CARD' , $Rights ) ) {
			$lvl2cardADD = in_array( 'ADD' , $Rights[ 'LVL2CARD' ] );
			$lvl2cardEDIT = in_array( 'EDIT' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_accTimeCreate = in_array( 'ACCTIME-CREATE' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_accTimeEdit = in_array( 'ACCTIME-EDIT' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_accTimeView = in_array( 'ACCTIME-VIEW' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_priceCreate = in_array( 'PRICE-CREATE' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_priceEdit = in_array( 'PRICE-EDIT' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_priceView = in_array( 'PRICE-VIEW' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_OnlyDep = in_array( 'ASSIGN-ONLY-DEP' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_StatEIPOnlyDep = in_array( 'STAT-EIP-ONLY-DEP' , $Rights[ 'LVL2CARD' ] );
			$lvl2card_ChangeDateMatExpSpecAfterOrder = in_array( 'CHANGE-DATE-MAT-EXP-SPEC-AFTER-ORDER' , $Rights[ 'LVL2CARD' ] );
		}
	}

	$access = ( isset( $_REQUEST[ 'add' ] ) && $lvl2cardADD ) || ( isset( $_REQUEST[ 'edit' ] ) && $lvl2cardEDIT );

	if ( !$access ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit ;
	}

	if ( !$lvl2card_OnlyDep ) {
		$tabWorkers = $portalDB->query( "select * from `workers` where ( ( `spec` is not null ) and ( `actual` = 1 ) ) order by `name` ;" , false );
	} else {
		$tabWorkers = $portalDB->query( "select * from `workers` where ( ( `dep` = ? ) and ( `spec` is not null ) and ( `actual` = 1 ) and ( `controlled_by` <=> null ) ) order by `name` ;" , false , 'i' , $UserDepartment );
		$tabWorkers2 = $portalDB->query( "select * from `workers` where ( ( `spec` is not null ) and ( `actual` = 1 ) and ( `controlled_by` <=> ? ) ) order by `name` ;" , false , 'i' , $UserWorkerFirstID );
		$tabWorkers = array_merge( $tabWorkers , $tabWorkers2 );
	}
	//print_r_html( $tabWorkers , 1 );
	//$tabWorkers = $portalDB->query( "select * from `workers` where ( ( `spec` is not null ) and ( `actual` = 1 ) ) order by `name` ;" , false );
	$tabPosts = $portalDB->table( 'posts' , 'id' );
	$tabDepartments = $portalDB->table( 'departments' , 'id' );
	$tabCaseCategory = $portalDB->table( 'casecategory' , 'id' );
	$tabSpecialities = $portalDB->query( "select `t2`.* , concat( cast( `t1`.`index` as char ) , '.' , cast( `t2`.`num` as char ) ) as `spec_full_num` from `specialities-groups` as `t1` , `specialities` as `t2` where `t1`.`id` = `t2`.`group`" , 'id' );

	$lvl23c = false ;
	$specsAssigned = array();

	$mode = false ;
	$v_accounting_time = false ;
	$v_price = false ;

	$func = 'main.php' ;
	$useForm = false ;

	$isOrdered = false ;

	$kat_slognost1 = 1 ;

	if ( isset( $_REQUEST[ 'edit' ] ) ) {
		$v_id = intval( $_REQUEST[ 'edit' ] , 10 );
		$row = $portalDB->row( "select `t1`.* , `t2`.`exp_type` , `t1`.`kat_slognost` from `matincominglvl2` as `t1` , `matincoming` as `t2` where ( ( `t1`.`id`= ? ) and ( `t1`.`mat_id` = `t2`.`id` ) ) limit 1;" , 'i' , $v_id );
		$lvl3Row = $portalDB->simpleRow( 'expertize' , array( 'ext_id' => $v_id ) );

		if ( $row !== false && $lvl3Row !== false ) {
			$lvl1c_ID = $row[ 'mat_id' ];
			$lvl1c_EXPTYPE = $row[ 'exp_type' ];
			$lvl2c_DEP_ID = $row[ 'dep_id' ];
			$lvl2c_Date = date( 'd-m-Y' , strtotime( $row[ 'date' ] ) );
			$lvl2c_MATERIALS = $row[ 'materials' ];
			$lvl2c_EXDATA6 = $row[ 'ex_data_6' ];
			$lvl2c_EXDATA7 = $row[ 'ex_data_7' ];
			$lvl2c_EXDATA8 = $row[ 'ex_data_8' ];
			$lvl2c_EXDATA9 = $row[ 'ex_data_9' ];
			$lvl2c_EXDATA10 = $row[ 'ex_data_10' ];
			$lvl2c_EXDATA12 = $row[ 'ex_data_12' ];
			$kat_slognost1 = $row[ 'kat_slognost' ] ;
			$row2 = $portalDB->row( "select * from `expertize` where `ext_id` = ? limit 1;" , 'i' , $v_id );
			$v_exp_id = $row2[ 'exp_id' ];
			$v_spec_id = $row2[ 'spec_id' ];
			$v_use_in_stat = ( $row2[ 'use_in_stat' ] == 1 ? 1 : 0 );

			$isOrdered = !is_null( $row2[ 'order_date' ] );

			if ( $lvl2card_accTimeEdit || $lvl2card_accTimeView ) {
				$v_accounting_time = is_null( $row[ 'accounting_time' ] ) ? '' : $row[ 'accounting_time' ];
			}

			if ( $lvl2card_priceEdit || $lvl2card_priceView ) {
				$v_price = is_null( $lvl3Row[ 'price' ] ) ? '' : $lvl3Row[ 'price' ];
			}

			$lvl23c = $portalDB->query( "select `t1`.`id` , `t2`.`use_in_stat` , `t2`.`spec_id` from `matincominglvl2` as `t1` , `expertize` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t1`.`mat_id` = ? ) and ( `t1`.`id` <> ? )" , false , 'si' , $lvl1c_ID , $v_id );
			if ( $lvl23c === false ) {
				$lvl23c = array();
			}
			foreach( $lvl23c as $c23c ) {
				$cSpecID = $c23c[ 'spec_id' ];
				if ( !isset(  $tabSpecialities[ $cSpecID ] ) ) {
				}
				if ( $c23c[ 'use_in_stat' ] == 1 && $tabSpecialities[ $cSpecID ][ 'use_in_stat' ] == 1 ) {
					$specsAssigned[ $cSpecID ] = true ;
				}
			}

			$func = 'processor.php?lvl2cedit='.$v_id ;
			$useForm = true ;
			$btnName = 'Заменить' ;
			$mode = 'edit' ;
		} else {
			$v_exp_id = -1 ;
			$v_spec_id = -1 ;
		}

		$oldWorker = $portalDB->row( "select * from `workers` where `id` = ?" , 'i' , $v_exp_id );
		$oldSpec = $portalDB->row( "select concat( `t1`.`index` , \".\" , `t2`.`num` , if( `t2`.`comment` is null , \"\" , concat( \" (\" , `t2`.`comment` , \")\" ) ) ) as `spec`  from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id`=`t2`.`group` ) and ( `t2`.`id`= ? );" , 'i' , $v_spec_id );
	} else
	if ( isset( $_REQUEST[ 'add' ] ) ) {
		$lvl1c_ID = getCharID( $_REQUEST[ 'add' ] , DOCTYPE_MATINCOMING );
		if ( $lvl1c_ID === false ) {
			//
		}

		//$row = $portalDB->row( "select count( * ) as `lvl2cc` from `matincominglvl2` as `t1` where `t1`.`mat_id` = ? " , 's' , $lvl1c_ID );
		$lvl23c = $portalDB->query( "select `t2`.`use_in_stat` , `t2`.`spec_id` from `matincominglvl2` as `t1` , `expertize` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t1`.`mat_id` = ? )" , false , 's' , $lvl1c_ID );
		if ( $lvl23c === false ) {
			$lvl23c = array();
		}
		foreach( $lvl23c as $c23c ) {
			$cSpecID = $c23c[ 'spec_id' ];
			if ( !isset(  $tabSpecialities[ $cSpecID ] ) ) {
			}
			if ( $c23c[ 'use_in_stat' ] == 1 && $tabSpecialities[ $cSpecID ][ 'use_in_stat' ] == 1 ) {
				$specsAssigned[ $cSpecID ] = true ;
			}
		}

		$row = $portalDB->row( "select `id`, `exp_type`, `ex_data_4` from `matincoming` where `id` = ?" , 's' , $lvl1c_ID );

		if ( $row !== false ) {
			$lvl1c_ID = $row[ 'id' ];
			$lvl1c_EXPTYPE = $row[ 'exp_type' ];
			$lvl2c_DEP_ID = $UserDepartment ;
			$lvl2c_Date = date( 'd-m-Y' , time() );
			$lvl2c_MATERIALS = $row[ 'ex_data_4' ];
			$lvl2c_EXDATA6 = date( 'd-m-Y' , time() ).", " ;
			$lvl2c_EXDATA7 = '' ;
			$lvl2c_EXDATA8 = '' ;
			$lvl2c_EXDATA9 = '' ;
			$lvl2c_EXDATA10 = '' ;
			$lvl2c_ENDEXP = false ;
			$lvl2c_ENDDATE = date( 'd-m-Y' , time() );
			$lvl2c_EXDATA12 = '' ;
			$func = 'processor.php?lvl2cadd' ;
			$useForm = true ;
			$btnName = 'Добавить' ;

			if ( $lvl2card_accTimeCreate ) {
				$v_accounting_time = '' ;
			}

			if ( $lvl2card_priceCreate ) {
				$v_price = '' ;
			}

			$mode = 'create' ;
		}
		$v_exp_id = -1 ;
		$v_spec_id = -1 ;
		$v_use_in_stat = 1 ;
	} else {
		Redirect( $func );
	}

	if ( !$isOrdered || $lvl2card_ChangeDateMatExpSpecAfterOrder ) {
		$incomingDate = '<input id="i_date" name="i_date" type="text" value="'.$lvl2c_Date.'" class="i_date" onbeforeinput="beforeDateChange()" oninput="doDateChange()"> <label><input type="checkbox" id="i_date_from_lvl1" name="i_date_from_lvl1" onchange="doCheckDateFromLvl1()" value="fromLVL1"> Как в карточке 1 ур.</label>' ;
		$materialsDescr = '<textarea id="i_materials" name="i_materials" class="i_materials">'.$lvl2c_MATERIALS.'</textarea>' ;

		$WorkersList = '<select id="i_worker" name="i_worker" class="i_worker" onchange="upd()">' ;
		if ( isset( $oldWorker ) ) {
			$WorkersList.= '<option value="'.$oldWorker[ 'id' ].'">'.NAMES_Format( NAMES_parse( $oldWorker[ 'name' ] ) ).'   ( '.$tabPosts[ $oldWorker[ 'post_1_id' ] ][ 'name' ].' )</option>' ;
		}

		$wpel = array();
		foreach( $tabWorkers as $w ) {
			$p = NAMES_Format( NAMES_parse( $w[ 'name' ] ) );
			$w[ '_name_parsed' ] = $p ;
			$wpel[ $p.':'.$w[ 'first_id' ] ] = $w ;
		}

		usort( $wpel , function( $a , $b ) use ( $UserDepartment ) {
			if ( $a[ 'dep' ] == $UserDepartment && $b[ 'dep' ] != $UserDepartment ) {
				return -1 ;
			} else
				if ( $a[ 'dep' ] != $UserDepartment && $b[ 'dep' ] == $UserDepartment ) {
					return 1 ;
				} else {
					return strcmp( $a[ '_name_parsed' ] , $b[ '_name_parsed' ] );
				}
		} );

		//print_r_html( $wpel , 1 );

		$lastMyDep = 1 ;
		foreach( $wpel as $w ) {
			if ( $w[ 'dep' ] != $UserDepartment && $lastMyDep == 1 ) {
				$WorkersList.= '<option value="" disabled></option><option value="" disabled>-----------------------</option><option value="" disabled></option>' ;
				$lastMyDep = 0 ;
			}
			$WorkersList.= '<option value="'.$w[ 'id' ].'">'.NAMES_Format( NAMES_parse( $w[ 'name' ] ) ).'   ( '.$tabPosts[ $w[ 'post_1_id' ] ][ 'name' ].' )</option>' ;
		}
		$WorkersList.= '</select>' ;

		$SpecsList = '<select id="i_spec" name="i_spec" class="i_spec" onchange="upd2()"></select>' ;

		$useInStat = '<label><input type="checkbox" id="i-no-use-in-stat" name="i_no_use_in_stat" value="1" '.( $v_use_in_stat ? '' : ' checked="checked" ' ).'> Экспертоучастие</label>' ;
	} else {
		$incomingDate = '<span>'.$lvl2c_Date.'</span>' ;
		$materialsDescr = '<span>'.$lvl2c_MATERIALS.'</span>' ;
		$WorkersList = '<span>'.NAMES_Format( NAMES_parse( $oldWorker[ 'name' ] ) ).'   ( '.$tabPosts[ $oldWorker[ 'post_1_id' ] ][ 'name' ].' )</span>' ;
		$SpecsList = '<span>'.$tabSpecialities[ $v_spec_id ][ 'spec_full_num' ].'</span>' ;
		$useInStat = $v_use_in_stat == 0 || $tabSpecialities[ $v_spec_id ][ 'use_in_stat' ] == 0 ? '<span> Экспертоучастие</span>' : '' ;
	}


	MainHead_L2('База', '<a href="main.php">База</a> - карточка 2' ,
		array( '../%UT/buttons.css' , '../%UT/forms.css' , '%UT/level2card.css' ) ,
		array(
			'files/level2card.js.php?AE' ,
			'files/level2card.2.js' ,
			'#var specFilter = JSON.parse( atob( "'.base64_encode( json_encode( $specsAssigned ) ).'" ) );
			$.LVLC2init = { expID : '.$v_exp_id.' , specID : '.$v_spec_id.' }'
		) ,
		'hlp/level2card.html'
	);

	if ( $mode == 'create' && isset( $lvl23c ) && count( $lvl23c ) > 0 ) {
		echo '<br><br>' ;
		InlineMessage( 'Внимание ! Если вы продолжите произойдет добавление второй карточки для этого дела! Если вы ошиблись, то советуем вам вернуться назад и выбрать другое дело. Если существующая карточка была добавлена ошибочно, а правильную хотите добавить вы, то советуем вам не добавлять новую, а отредактировать старую. Если же существующая карточка верна и вы намерены добавить еще одну, то продолжайте.' );
		echo '<br><br>' ;
	}

	echo ( $useForm ? '<form id="PostForm" action="'.$func.'" method="post">' : '' ).'
		<table align="center" class="PT">
			<tr>
				<td class="D">
					Порядковый номер экспертизы
				</td>
				<td class="I">
					<div class="number">'.matincomingNumberFull( $lvl1c_ID , $lvl2c_DEP_ID , $lvl1c_EXPTYPE ).( $mode == 'create' ? '<a onclick="addExNumber()" class="add-ex-num-btn"></a></div><div id="add-ex-num-area" class="add-ex-num-area"></div>' : '' ).'
				</td>
			</tr>
			<tr>
				<td class="D">
					Категория сложности дел
				</td>
				<td class="I"> ' ;
					echo '<select id="i_kat_slognost" name="i_kat_slognost" size="1" onchange="upd2()">' ;
					for( $n = 1 ; $n <= 4 ; $n++ ) {
						echo '<option '.( $kat_slognost1 != $n ? '' : 'selected' ).' value="'.$n.'">'.( $n < 4 ? $n.' категория' : 'свыше 3 категории' ).'</option>' ;
					}
					echo '</select>
					</td>
				</tr>' ;
				echo '<tr>
					<td class="D">
						Дата поступления материалов
					</td>
					<td class="I">
						'.$incomingDate.'
					</td>
				</tr>
				<tr>
					<td class="D">
						Предметы и документы, поступившие для исследования, материалы дела
					</td>
					<td class="I">
						'.$materialsDescr.'
					</td>
				</tr>

				<tr>
					<td class="D">
						Фамилия эксперта, которому поручено производство экспертизы; специальность
					</td>
					<td class="I">
						'.$WorkersList.' '.$SpecsList.$useInStat.'
					</td>
				</tr>' ;

				if ( $mode !== false && $v_accounting_time !== false && ( $lvl2card_accTimeCreate || $lvl2card_accTimeEdit || $lvl2card_accTimeView ) ) {
					if ( ( $mode == 'create' && $lvl2card_accTimeCreate ) || ( $mode == 'edit' && $lvl2card_accTimeEdit ) ) {
						echo '<tr>
							<td class="D">
								Учетное время выполнения экспертизы
							</td>
							<td class="I">
								<input id="i_accounting_time" type="text" name="i_accounting_time" value="'.$v_accounting_time.'" class="">
							</td>
						</tr>' ;
					} else
					if ( $mode == 'edit' && $lvl2card_accTimeView ) {
						echo '<tr>
							<td class="D">
								Учетное время выполнения экспертизы
							</td>
							<td class="I">
								<div class="">'.$v_accounting_time.'</div>
							</td>
						</tr>' ;
					}
				}

				if ( $mode !== false && $v_price !== false && ( $lvl2card_priceCreate || $lvl2card_priceEdit || $lvl2card_priceView ) ) {
					if ( ( $mode == 'create' && $lvl2card_priceCreate ) || ( $mode == 'edit' && $lvl2card_priceEdit ) ) {
						echo '<tr>
								<td class="D">
									Стоимость экспертизы
								</td>
								<td class="I">
									<input id="i_price" type="text" name="i_price" value="'.$v_price.'" class="">
								</td>
							</tr>' ;
					} else
					if ( $mode == 'edit' && $lvl2card_priceView ) {
						echo '<tr>
							<td class="D">
								Стоимость экспертизы
							</td>
							<td class="I">
								<div class="">'.$v_price.'</div>
							</td>
						</tr>' ;
					}
				}

				echo '<tr>
					<td class="D">
						его подпись за получение материалов, дата получения
					</td>
					<td class="I">
						<textarea id="i_ex_data_6"  name="i_ex_data_6" class="i_ex_data_6">'.$lvl2c_EXDATA6.'</textarea>
					</td>
				</tr>
				<tr>
					<td class="D">
						Сведения о приостановлении срока производства экспертизы
						(причина, даты приостановления и возобновления производства, результат рассмотрения или ходатайства)
					</td>
					<td class="I">
						<textarea name="i_ex_data_7" class="i_ex_data_7">'.$lvl2c_EXDATA7.'</textarea>
					</td>
				</tr>
				<tr>
					<td class="D">
						Перечень передаваемых (полученных) предметов, веществ, документов
					</td>
					<td class="I">
						<textarea name="i_ex_data_8" class="i_ex_data_8">'.$lvl2c_EXDATA8.'</textarea>
					</td>
				</tr>
				<tr>
					<td class="D">
						Куда и кому переданы (подразделение, фамилия эксперта),
						подпись эксперта в получении, дата получения (возврата)
					</td>
					<td class="I">
						<textarea name="i_ex_data_9" class="i_ex_data_9">'.$lvl2c_EXDATA9.'</textarea>
					</td>
				</tr>

				<tr>
					<td class="D">
						Количество объектов;
						Срок производства экспертизы
					</td>
					<td class="I">
						<textarea name="i_ex_data_10" class="i_ex_data_10">'.$lvl2c_EXDATA10.'</textarea>
					</td>
				</tr>
				<tr>
					<td class="D">
						Дата передачи в подразделение делопроизводства заключения, акта, сообщения,
						письма о возврате без исполнения. Подпись работника подразделения делопроизводства за получение
					</td>
					<td class="I">
						<textarea name="i_ex_data_12" class="i_ex_data_12">'.$lvl2c_EXDATA12.'</textarea>
					</td>
				</tr>
				<tr>
				<td colspan="2" class="btnTB">
					<input name="i_mat_id" type="hidden" value="'.$lvl1c_ID.'">
					<input name="i_dep_id" type="hidden" value="'.$UserDepartment.'">
					'.( $useForm ? '<input type="button" value="'.$btnName.'" class="btn" onclick="doCheckForm()">' : '<span class="btn3"><a href="'.$func.'">Отмена</a></span>' ).'
				</td>
			</tr>
		</table>
	'.( $useForm ? '</form>' : '' );
	
	if ( $lvl2card_StatEIPOnlyDep ) {
		$tgtWorkersID = $DepAllWorkers ;
	} else {
		$tgtWorkersID = array_column( $tabWorkers , 'id' );
	}

	$tabAllWorkers = $portalDB->table( 'workers' , 'id' );
	$workersData = array();
	//foreach( $DepAllWorkers as $wid ) {
	foreach( $tgtWorkersID as $wid ) {
		$cwd = $tabAllWorkers[ $wid ];
		$cwFID = $cwd[ 'first_id' ];
		if ( $cwd[ 'actual' ] == 1 ) {
			$workersData[ $cwFID ] = $cwd ;
		}
	}

	$tgtWorkersFID = array_keys( $workersData );
	foreach( $tabAllWorkers as $cwd ) {
		$cwFID = $cwd[ 'first_id' ];
		if ( in_array( $cwFID , $tgtWorkersFID ) ) {
			$tgtWorkersID[]= $cwd[ 'id' ];
		}
	}
	
	$tgtWorkersID = array_unique( $tgtWorkersID );

	$specLock = array();
	$specAvail = array();
	$specRevMap = remap( $tabSpecialities , 'spec_full_num' );
	foreach( $workersData as &$cwd ) {
		$cwd[ 'name' ] = NAMES_Format( NAMES_parse( $cwd[ 'name' ] ) );
		$cwd[ 'stat' ] = 0 ;
		$csa = explode( ';' , $cwd[ 'spec' ] );
		$cwd[ 'spec' ] = $csa ;
		foreach( $csa as $csid ) {
			if ( !isset( $tabSpecialities[ $csid ] ) ) {
			} else {
				$csd = $tabSpecialities[ $csid ];
				$csfn = $csd[ 'spec_full_num' ];
				if ( !isset( $specAvail[ $csfn ] ) ) {
					$specAvail[ $csfn ] = array();
				}
				$specAvail[ $csfn ][ $cwd[ 'first_id' ] ]= &$cwd ;
			}
		}
	} unset( $cwd );

	$specsAssignedMap = array();
	foreach( $specsAssigned as $csid => $v ) {
		$csd = $tabSpecialities[ $csid ];
		$specsAssignedMap[ $csd[ 'spec_full_num' ] ] = true ;
	}

	uksort( $specAvail , function( $a , $b ) use ( $specsAssignedMap ) {
		if ( isset( $specsAssignedMap[ $a ] ) && !isset( $specsAssignedMap[ $b ] ) ) {
			return -1 ;
		} else
		if ( !isset( $specsAssignedMap[ $a ] ) && isset( $specsAssignedMap[ $b ] ) ) {
			return 1 ;
		} else {
			return strcmp(
				str_pad( $a , 4 , 0 , STR_PAD_LEFT ) ,
				str_pad( $b , 4 , 0 , STR_PAD_LEFT )
			);
		}
	} );

	$stat = $portalDB->query(
		"select
			`t1`.`id` ,
			`t5`.`exp_id` ,
    		`t5`.`spec_id`
		from
			`matincoming` as `t1` ,
			`agency` as `t2` ,
			`agent` as `t3` ,
			`matincominglvl2` as `t4` ,
			`expertize` as `t5`
		where
			( `t1`.`state` <> -2 ) and
			( `t5`.`exp_id` in ( ?* ) ) and
			( `t4`.`id` = `t5`.`ext_id` ) and
			( `t1`.`id` = `t4`.`mat_id` ) and
			( `t1`.`from_agency` = `t2`.`id` ) and
			( `t1`.`from_agent` = `t3`.`id` ) and
			( `t1`.`date` > '2010-01-01' ) and
			( ( `t5`.`state` is null ) or ( `t5`.`state` = 0 ) )" , false , '*i' , $tgtWorkersID
	);

	foreach( $stat as $row ) {
		$cwID = $row[ 'exp_id' ];
		$cwFID = $tabAllWorkers[ $cwID ][ 'first_id' ];
		$workersData[ $cwFID ][ 'stat' ]++ ;
		if ( !in_array( $row[ 'spec_id' ] , $workersData[ $cwFID ][ 'spec' ] ) ) {
			$specLock[ $cwFID.':'.$row[ 'spec_id' ] ] = 1 ;
			$workersData[ $cwFID ][ 'spec' ][]= $row[ 'spec_id' ];
		}
		if ( !isset( $workersData[ $cwFID ][ 'stat-'.$row[ 'spec_id' ] ] ) ) {
			$workersData[ $cwFID ][ 'stat-'.$row[ 'spec_id' ] ] = 0 ;
		}
		$workersData[ $cwFID ][ 'stat-'.$row[ 'spec_id' ] ]++ ;
	}

	$maxStat = 0 ;
	foreach( $workersData as $cwd ) {
		if ( $cwd[ 'stat' ] > $maxStat ) {
			$maxStat = $cwd[ 'stat' ];
		}
	}

	foreach( $specAvail as &$csa ) {
		usort( $csa , function( $a , $b ) { return strcmp( $a[ 'name' ] , $b[ 'name' ] ); } );
	} unset( $csa );

	if ( !$isOrdered || $lvl2card_ChangeDateMatExpSpecAfterOrder ) {
		echo '<div class="stat-panel">
			<div class="stat-panel-container">
				<div><div class="stat-panel-legend no-stat"></div> / <div class="stat-panel-legend-2 no-stat"></div> - экспертоучастие</div>' ;
			foreach( $specAvail as $specNum => $cswd ) {
				echo '<div class="spec-area">
						<div class="spec-row">'.$specNum.'</div>' ;
				$csL = $specRevMap[ $specNum ];
				foreach( $cswd as $cwd ) {
					foreach( $csL as $csd ) {
						$csid = $csd[ 'id' ];
						$forceNoUseInStat = false ;
						if ( count($specsAssigned ) > 0 && !isset( $specsAssigned[ $csid ] ) && $tabSpecialities[ $csid ][ 'use_in_stat' ] == 1 ) {
							$forceNoUseInStat = true ;
						}
						$csa = $cwd[ 'spec' ];
						if ( !in_array( $csid , $csa ) ) {
							continue ;
						}
						if ( !isset( $cwd[ 'stat-'.$csid ] ) ) {
							$cwd[ 'stat-'.$csid ] = 0 ;
						}
						$sp = round( 100 * ( $cwd[ 'stat' ] / $maxStat ) , 1 );
						$sps = round( 100 * ( $cwd[ 'stat-'.$csid ] / $cwd[ 'stat' ] ) , 1 );
						$lockedSpec = isset( $specLock[ $cwd[ 'first_id' ].':'.$csid ] );
						echo '<div class="stat-row'.( $csd[ 'use_in_stat' ] == 1 ? '' : ' no-stat' ).'">
									<div class="stat-row-name'.( $lockedSpec ? ' locked' : '' ).'"'.( !$lockedSpec ? ' onclick="doSelectWorker( event , '.$cwd[ 'id' ].' , '.$csid.' , '.( $forceNoUseInStat ? 1 : 0 ).' )"' : '' ).'>'.$cwd[ 'name' ].' <a href="/maindb/expertize.report.in-the-pipeline.php?i_worker='.$cwd[ 'id' ].'" class="stat-row-name-details" target="_blank">?</a></div>
									<div class="stat-row-data">
										<div class="stat-row-data-bar" style="width : '.str_replace( ',' , '.' , $sp ).'%">
											<div class="stat-row-data-label left">'.$cwd[ 'stat-'.$csid ].'</div>
											<div class="stat-row-data-label right">'.$cwd[ 'stat' ].'</div>
											<div class="stat-row-data-bar-sub" style="width : '.str_replace( ',' , '.' , $sps ).'%"></div>
										</div>
									</div>
								</div>';
					}
				}
				echo '</div>' ;
			}
			echo '</div>
		</div>' ;
	}
	closeHtml();
