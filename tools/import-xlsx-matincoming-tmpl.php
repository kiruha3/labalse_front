<?php
	require_once ( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $portalDB
	 */

	include_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	require_once( '../maindb/lconfig.php' );

	function str2KEY( $str ) {
		return strtoupper( clearText( $str ) );
	}

	function mkMap( $arr , $keyCol ) {
		return array_column( array_map(
			function( $row ) use ( $keyCol ) {
				$row[ 'KEY' ] = str2KEY( $row[ $keyCol ] );
				return $row ;
			} , $arr
		) , null , 'KEY' );
	}

	$stateRevMap_default = 'ошибочно зарегистрировано' ;
	$stateRevMap = array(
		$stateRevMap_default                   => -2 ,
		'ожидает выполнения другой экспертизы' => -1 ,
		'в производстве'                       => 0 ,
		'готово к выдаче'                      => 1 ,
		'выдано'                               => 2
	);

	$caseCategoryRevMap = array(
		'' => 0 ,
		'1' => 1 ,
		'2' => 2 ,
		'3' => 3 ,
		'4' => 4 ,
		'5' => 5 ,
	);
	
	$expertizeStateRevMap_default = 'в производстве' ;
	$expertizeStateRevMap = array(
		$expertizeStateRevMap_default   => 0 ,
		'выполнено'                     => 1 ,
		'без производства'              => 2
	);

	$tableTOA = $portalDB->table( 'type-of-agency' );
	foreach( $tableTOA as &$row ) {
		$row[ 'name' ]= strtolower( inForm( $row[ 'name' ] , 1 , false ) );
	} unset( $row );
	$toaRevMap = array_column( $tableTOA , 'id' , 'name' );
	//var_dump_html( $toaRevMap );
	
	$tableWorkers = $portalDB->query( "select `t1`.* from `workers-no-spec` as `t1` left join `workers-no-spec` as `t2` on ( ( `t1`.`name` = `t2`.`name` ) and ( `t1`.`id` < `t2`.`id` ) ) where `t2`.`id` is null" , 'id' );
	foreach( $tableWorkers as &$row ) {
		$row[ 'name' ]= strtolower( NAMES_Format( NAMES_parse( $row[ 'name' ] ) , '%F1 %i.%o.' ) );
	} unset( $row );
	$workersRevMap_default = $tableWorkers[ 1 ][ 'name' ];
	$workersRevMap = array_column( $tableWorkers , 'id' , 'name' );
	//var_dump_html( $workersRevMap );
	
	$tableSpecialities = $portalDB->query( "select `t2`.`id` , concat( cast( `t1`.`index` as CHAR ) , '.' , cast( `t2`.`num` as CHAR )  ) as `spec-num` from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id` = `t2`.`group` ) and ( `t2`.`use_in_stat` = 1 )" );
	$specialitiesRevMap = array_column( $tableSpecialities , 'id' , 'spec-num' );
	$specialitiesRevMap_default = '99.1' ;
	//var_dump_html( $specialitiesRevMap );

	$tableMarksCatalog = $portalDB->table( 'marks-catalog' );
	foreach( $tableMarksCatalog as &$row ) {
		$row[ 'name' ]= strtolower( clearText( $row[ 'name' ] ) );
	} unset( $row );
	$marksCatalogMap = array_column( $tableMarksCatalog , 'name' , 'id' );

	$tableMarksMarkGroup = $portalDB->simpleQuery( 'marks-mark-group' , array(
		'group_id' => array( 8 , 9 )
	) );

	$marksMarkGroupSet = array_fill_keys( array_column( $tableMarksMarkGroup , 'mark_id' ) , 1 );

	//print_r_html( $marksCatalogMap );

	//exit();

	function ptListTest( $list , $str , $field ) {
		$res = false ;
		foreach( $list as $pt ) {
			$m = array();
			$n = preg_match( $pt , $str , $m , PREG_OFFSET_CAPTURE );
			if ( $n == 1 ) {
				$res = array( 'ret' => $m[ $field ][ 0 ] , 'm' => $m );
				break ;
			}
		}
		return $res ;
	}

	$noWarnExpType = isset( $_REQUEST[ 'no_warn_exp_type' ] ) && $_REQUEST[ 'no_warn_exp_type' ] == 1 ;

	if ( isset( $_REQUEST[ 'do-import' ] ) ) {
		//print_r_html( $_FILES );
		//header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		//header( 'Content-Disposition: attachment;filename="Объединенные XLSX '.date( 'Y.m.d H-i' , mktime() ).'.xlsx"' );

		$TB = microtime( 1 );
		$xlsx_matincoming  = new TSimpleXLSXTemplate( $_FILES[ 'src-file-matincoming'  ][ 'tmp_name' ] );

		$tabSpec = $portalDB->query( "select concat( cast( `t1`.`index` as char ) , '.' , cast( `t2`.`num` as char ) ) as `spec-num` , `t2`.`id` as `spec-id` , count( * ) as `spec-count` from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id` = `t2`.`group` ) and ( `use_in_stat` = 1 ) group by `spec-num`" , 'spec-num' );

		$xlsx_matincoming->selectSheet( 'Лист1' );
		//$xlsx_matincoming->selectSheet( 'журнал 2021' );

		$row = 4 ;

		$dateDelta = mktime( 0 , 0 , 0 , 12 , 30 , 1899 );

		$repoCount = 0 ;

		while ( $row < 10000 ) {
			$vNum = clearText( $xlsx_matincoming->getCellValue( 'A'.$row ) );
			if ( $vNum == '' ) {
				echo '</br><span style="color : blue ;">Найдена строка без номера ['.$row.']! обработка завершена</span></br>' ;
				break ;
			}

			$vDate = clearText( $xlsx_matincoming->getCellValue( 'B'.$row ) );
			$expDate = $vDate * 86400 + $dateDelta ;
			$lineRepo = array( 'строка '.$row.' : '.$vNum.' от '.date( 'd-m-Y' , $expDate ) );

			$expType = false ;
			$n = preg_match( '/^\d+$/' , ''.$vNum );
			$expNum = false ;
			if ( $n == 1 ) {
				$expType = 0 ;
				$expNum = $vNum ;
			} else {
				$m = array();
				$n = preg_match( '/^\d+-\d$/' , ''.$vNum );
				if ( $n == 1 ) {
					$expTypeIndex = substr( $vNum , -1 );
					if ( isset( $caseCategoryRevMap[ $expTypeIndex ] ) ) {
						$expType = $caseCategoryRevMap[ $expTypeIndex ];
						$expNum = substr( $vNum , 0 , -2 );
					}
				}
			}

			if ( $expType === false && !$noWarnExpType ) {
				$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Ошибка определения категории дела</span>' ;
			}

			$matincomingID = null ;
			if ( $expNum !== false ) {
				$s = array(
					'v' => 10 ,
					'o' => ORG_INDEX_VRCSE ,
					't' => DOCTYPE_MATINCOMING ,
					'y'	=> date( 'Y' , $expDate ) ,
					'n' => str_pad( $expNum , 6 , 0 , STR_PAD_LEFT )
				);
				//var_dump_html( $s );
				$matincomingID = mkCharID( $s );
				//var_dump_html( $matincomingID );
			}

			$vTypeOfAgency = clearText( $xlsx_matincoming->getCellValue( 'D'.$row ) );
			$eTypeOfAgency = strtolower( $vTypeOfAgency );
			if ( !isset( $toaRevMap[ $eTypeOfAgency ] ) ) {
				$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Тип органа отсутствует в каталоге: "'.$vTypeOfAgency.'". Замена на "Иные органы"</span>' ;
				$eTypeOfAgency = 'иные органы' ;
			}
			
			
			$vAgency = clearText( $xlsx_matincoming->getCellValue( 'E'.$row ) );
			$vAgent  = clearText( $xlsx_matincoming->getCellValue( 'F'.$row ) );
			$vAgentContacts = clearText( $xlsx_matincoming->getCellValue( 'G'.$row ) );

			$vExData3 = clearText( $xlsx_matincoming->getCellValue( 'H'.$row ) );
			$vExData4 = clearText( $xlsx_matincoming->getCellValue( 'I'.$row ) );
			$vMaterials = clearText( $xlsx_matincoming->getCellValue( 'J'.$row ) );

			//var_dump_html( $vTypeOfAgency );
			//var_dump_html( $toaRevMap[ $vTypeOfAgency ] );
			//var_dump_html( $toaRevMap );
			$saad = storeAgentData( $portalDB , $toaRevMap[ $eTypeOfAgency ] , $vAgency , $vAgent , array( $vAgentContacts ) );
			//var_dump_html( $saad );

			//exit(0);

			$vState = clearText( $xlsx_matincoming->getCellValue( 'C'.$row ) );
			$eState = strtolower( $vState );
			if ( !isset( $stateRevMap[ $eState ] ) ) {
				$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Неизвестное состояние: "'.$vState.'". Замена на "'.$stateRevMap_default.'"</span>' ;
				$eState = $stateRevMap_default ;
			}

			$portalDB->insertRow( 'matincoming' , array(
				'id' => $matincomingID ,
				'date' => date( 'Y-m-d' , $expDate ) ,
				'from_agency' => $saad[ 'agency.id' ] ,
				'from_agent' => $saad[ 'agent.id' ] ,
				'exp_type' => $expType ,
				'ex_data_3' => $vExData3 ,
				'ex_data_4' => $vExData4 ,
				'state' => $stateRevMap[ $eState ]
			) );
			
			$vExpert = clearText( $xlsx_matincoming->getCellValue( 'K'.$row ) );
			$eExpert = strtolower( $vExpert );
			if ( !isset( $workersRevMap[ $eExpert ] ) ) {
				$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Сотрудник отсутствует в каталоге: "'.$vExpert.'". Замена на "'.$workersRevMap_default.'"</span>' ;
				$eExpert = $workersRevMap_default ;
			}
			
			$cWorkerID = $workersRevMap[ $eExpert ];
			$cWorker = $tableWorkers[ $cWorkerID ];
			
			$vSpec = clearText( $xlsx_matincoming->getCellValue( 'L'.$row ) );
			$eSpec = strtolower( $vSpec );
			if ( !isset( $specialitiesRevMap[ $eSpec ] ) ) {
				$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Специальность отсутствует в каталоге: "'.$vSpec.'". Замена на "Прочие"</span>' ;
				$eSpec = $specialitiesRevMap_default ;
			}
			
			$vKatSl = clearText( $xlsx_matincoming->getCellValue( 'M'.$row ) );
			if ( !in_array( $vKatSl , array( 1 , 2 , 3 , 4 ) ) ) {
				$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Неизвестная категория сложности: "'.$vKatSl.'". Замена на "1"</span>' ;
				$vKatSl = 1 ;
			}
			
			$portalDB->insertRow( 'matincominglvl2' , array(
				'mat_id' => $matincomingID ,
				'kat_slognost' => $vKatSl ,
				'dep_id' => $cWorker[ 'dep' ] ,
				'date' => date( 'Y-m-d' , $expDate ) ,
				'materials' => $vMaterials
			) );
			
			$vExpState = clearText( $xlsx_matincoming->getCellValue( 'N'.$row ) );
			$eExpState = strtolower( $vExpState );
			if ( !isset( $expertizeStateRevMap[ $eExpState ] ) ) {
				$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Неизвестное состояние : "'.$vExpState.'". Замена на "'.$expertizeStateRevMap_default.'"</span>' ;
				$eExpState = $expertizeStateRevMap_default ;
			}
			
			
			if ( $expertizeStateRevMap[ $eExpState ] == 1 || $expertizeStateRevMap[ $eExpState ] == 2 ) {
				$vFinDate = clearText( $xlsx_matincoming->getCellValue( 'O'.$row ) );
				$eFinDate = date( 'Y-m-d' , $vFinDate * 86400 + $dateDelta );
			} else {
				$eFinDate = null ;
			}

			$vConclusion = null ;
			$vConclusion_1 = null ;
			$vConclusion_3 = null ;
			$vConclusion_2 = null ;
			$vConclusion_2_1 = null ;
			$vConclusion_2_2 = null ;
			$vConclusion_2_3 = null ;
			$vConclusion_2_3_comment = null ;
			
			if ( $expertizeStateRevMap[ $eExpState ] == 1 ) {
				$vConclusion = clearText( $xlsx_matincoming->getCellValue( 'P'.$row ) );
				$vConclusion_1 = clearText( $xlsx_matincoming->getCellValue( 'Q'.$row ) );
				$vConclusion_3 = clearText( $xlsx_matincoming->getCellValue( 'R'.$row ) );
				$vConclusion_2 = clearText( $xlsx_matincoming->getCellValue( 'S'.$row ) );
				$vConclusion_2_1 = 0 ;
				$vConclusion_2_2 = 0 ;
				$vConclusion_2_3 = 0 ;
				$vConclusion_2_3_comment = '' ;

				if ( preg_match( '/^\d+$/' , $vConclusion ) != 1 ) {
					$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Количество вопросов не определено : "'.$vConclusion.'". Замена на "0"</span>' ;
					$vConclusion = 0 ;
				}

				if ( preg_match( '/^\d+$/' , $vConclusion_1 ) != 1 ) {
					$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Количество категорических выводов не определено : "'.$vConclusion_1.'". Замена на "0"</span>' ;
					$vConclusion_1 = 0 ;
				}

				if ( preg_match( '/^\d+$/' , $vConclusion_3 ) != 1 ) {
					$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Количество вероятных выводов не определено : "'.$vConclusion_3.'". Замена на "0"</span>' ;
					$vConclusion_3 = 0 ;
				}

				if ( preg_match( '/^\d+$/' , $vConclusion_2 ) != 1 ) {
					$lineRepo[]= '<span style="color: red ; font-weight: bold ;">Количество вопросов, которые невозможно решить не определено : "'.$vConclusion_2.'". Замена на "0"</span>' ;
					$vConclusion_2 = 0 ;
				}

				$vConclusion_2_r = clearText( $xlsx_matincoming->getCellValue( 'T'.$row ) );
				switch( strtolower( $vConclusion_2_r ) ) {
					case 'недостаточно исх. данных' :
						$vConclusion_2_2 = $vConclusion_2 ;
						break ;
						
					default :
						$vConclusion_2_3 = $vConclusion_2 ;
						$vConclusion_2_3_comment = $vConclusion_2_r ;
						break ;
						
				}
			}
			
			$vPrice = clearText( $xlsx_matincoming->getCellValue( 'U'.$row ) );
			if ( !( $vPrice == '' || $vPrice == 0 ) ) {
				$ePrice = $vPrice ;
			} else {
				$ePrice = null ;
			}
			
			$lvl2cID = $portalDB->lastInsertID();
			$portalDB->insertRow( 'expertize' , array(
				'ext_id' => $lvl2cID ,
				'exp_id' => $workersRevMap[ $eExpert ] ,
				'spec_id' => $specialitiesRevMap[ $eSpec ] ,
				'order_date' => date( 'Y-m-d' , $expDate ) ,
				'state' => $expertizeStateRevMap[ $eExpState ] ,
				'fin_date' => $eFinDate ,
				'conclusion' => $vConclusion ,
				'conclusion_1' => $vConclusion_1 ,
				'conclusion_3' => $vConclusion_3 ,
				'conclusion_2' => $vConclusion_2 ,
				'conclusion_2_1' => $vConclusion_2_1 ,
				'conclusion_2_2' => $vConclusion_2_2 ,
				'conclusion_2_3' => $vConclusion_2_3 ,
				'conclusion_2_3_comment' => $vConclusion_2_3_comment ,
				'price' => $ePrice ,
				'use_in_stat' => 1 ,
			) );
			
			$lvl3cID = $portalDB->lastInsertID();
			
			if ( $expertizeStateRevMap[ $eExpState ] == 1 && in_array( $expType , array( 2 , 3 , 4 , 0 ) ) ) {
				$portalDB->insertRow( 'payments' , array(
					'expertize_id' => $lvl3cID ,
					'state' => 0 ,
					'create_date' => $vFinDate * 86400 + $dateDelta ,
					'type' => 0
				) );
			}
			
			$vComment = clearText( $xlsx_matincoming->getCellValue( 'V'.$row ) );
			if ( $vComment != '' ) {
				$portalDB->insertRow( 'expertize-comments' , array(
					'ext_type' => 'expertize' ,
					'ext_id' => $lvl3cID ,
					'date' => $vFinDate * 86400 + $dateDelta ,
					'exp_id' => $workersRevMap[ $eExpert ] ,
					'comment' => $vComment
				) );
			}

			$vMark = clearText( $xlsx_matincoming->getCellValue( 'W'.$row ) );
			if ( $vMark != '' ) {
				$markID = array_search( strtolower( $vMark ) , $marksCatalogMap );
				if ( $markID === false ) {
					$portalDB->insertRow( 'marks-catalog' , array(
						'name' => $vMark ,
						'style' => 'grey' ,
						'actual' => 1
					) );
					$markID = $portalDB->lastInsertID();
					$marksCatalogMap[ $markID ] = strtolower( $vMark );
				}

				if ( !isset( $marksMarkGroupSet[ $markID ] ) ) {
					$portalDB->insertRow( 'marks-mark-group' , array(
						'mark_id' => $markID ,
						'group_id' => 8
					) );
					$marksMarkGroupSet[ $markID ] = 1 ;
				}

				$portalDB->insertRow( 'marks-objects' , array(
					'mark_id' => $markID ,
					'ext_type' => 'matincoming' ,
					'ext_id' => $matincomingID ,
					'date' => $expDate
				) );
			}

			if ( count( $lineRepo ) > 1 ) {
				echo implode( '<br/>' , $lineRepo ).'<br/><br/>' ;
				$repoCount++ ;
			}

			$row++ ;
		}

		echo 'Обработка закончена на строке: '.$row.', сообщений об ошибке: '.$repoCount.'<br/>' ;

		exit();
	}

	MainHead_L2( 'Инструменты - объединить XLSX' , 'Инструменты - объединить XLSX' , array( '../%UT/buttons.css' ) , array() , 'hlp/main.html' );

	echo '<div>
			<form action="?do-import" method="post" enctype="multipart/form-data">
				<label>Журнал экспертиз <input type="file" name="src-file-matincoming" /></label><br/>
				<div>
					<label><input type="checkbox" name="no_warn_exp_type" value="1"> Скрыть уведомления категории</label>
				</div>
				<input type="submit" value="Отправить">
			</form>
		</div>' ;

	closeHtml();

	//$xlsx = new TSimpleXLSXTemplate( './files/tmpl-151.xlsx' );

