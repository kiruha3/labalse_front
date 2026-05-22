<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	include_once( 'core.php' );
    /**
     * @var $LoginOk
     * @var $UserRights
     * @var TDB $portalDB
     * @var $UserID
     */

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

		if ( array_key_exists( 'EQUIPMENT' , $Rights ) ) {
			$mayAdd = in_array( 'ADD' , $Rights[ 'EQUIPMENT' ] );
			$mayEdit = in_array( 'EDIT' , $Rights[ 'EQUIPMENT' ] );
			$mayView = in_array( 'VIEW' , $Rights[ 'EQUIPMENT' ] );
			$GoOut = !( $mayAdd || $mayEdit || $mayView );
		} else {
			$GoOut = true ;
		}
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

    if ( isset( $_REQUEST[ 'mode' ] ) ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );
		echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ 'data' ] );
		$data = $DD->documentElement ;

		switch ( $data->nodeName ) {
			case 'add-test-result-req' :
				$reqID = $data->getAttribute( 'reqid' );
				$portalDB->noResult( "insert into `upload-file-temp` ( `id` , `time` ) values ( ? , ? )" , 'si' , $reqID , time() );
				echo '<result result="ok" />' ;
				break ;

			case 'get-test-result-req' :
				$reqID = $data->getAttribute( 'reqid' );
				$res = $portalDB->row( "select * from `upload-file-temp` where `id` = ?" , 's' , $reqID );
				if ( $res !== false && !is_null( $res[ 'file' ] ) ) {
					echo '<result result="ok" id="'.$res[ 'file' ].'" />' ;
				} else {
					echo '<result result="err" />' ;
				}

				break ;
		}

		exit();
	}

	if ( isset( $_REQUEST[ 'delete' ] ) ) {
		$eqID = intval( $_REQUEST[ 'delete' ] );
		$portalDB->noResult( "delete from `equipment` where `id` = ?" , 'i' , $eqID );
		exit();
	}

	if ( isset( $_REQUEST[ 'edit' ] ) ) {
		$vID = intval( $_REQUEST[ 'edit' ] );
	} else {
		$vID = -1 ;
	}

	$tabTestType = $portalDB->table( 'exp-equipment-test-types' , 'id' );

	$eqData = array();
	$expEqData = array();
	$expEqDataNew = array();
	$expEqDataNewErr = false ;
	if ( isset( $_REQUEST[ 'btnAddTestInfo' ] ) || isset( $_REQUEST[ 'btnSaveTestInfo' ] ) || isset( $_REQUEST[ 'btnReplTestInfo' ] ) ) {
		function readTestInfoCol( $cn , $tn ) {
			global $expEqDataNew ;
			if ( isset( $_REQUEST[ $cn ] ) && is_array( $_REQUEST[ $cn ] ) ) {
				foreach( $_REQUEST[ $cn ] as $ntiEk => $ntiEv ) {
					if ( !isset( $expEqDataNew[ $ntiEk ] ) ) {
						$expEqDataNew[ $ntiEk ] = array( 'id' => $ntiEk );
					}
					$expEqDataNew[ $ntiEk ][ $tn ] = $ntiEv ;
				}
			}
		}

		readTestInfoCol( 'ntiTestDate'   , 'test_date' );
		readTestInfoCol( 'ntiTestType'   , 'test_type' );
		readTestInfoCol( 'ntiTestPeriod' , 'test_period' );
		readTestInfoCol( 'ntiTestResult' , 'test_result' );
		readTestInfoCol( 'ntiState'      , 'state' );

		if ( isset( $_REQUEST[ 'btnAddTestInfo' ] ) ) {
			$expEqDataNew[]= array(
				'id' => count( $expEqDataNew ),
				'test_date' => date( 'd-m-Y' , time() ),
				'test_type' => '' ,
				'test_period' => '1г' ,
				'test_result' => '' ,
			);
		} else
		if ( isset( $_REQUEST[ 'btnReplTestInfo' ] ) ) {
			foreach ( $expEqDataNew as $eedn ) {
				$res = Date2Int( $eedn[ 'test_date' ] );
				if ( $res === false ) {
					$expEqDataNewErr = array( $eedn[ 'id' ] , 'test_date' );
					break ;
				}
				if ( $eedn[ 'test_type' ] == '' || !isset( $tabTestType[ $eedn[ 'test_type' ] ] ) ) {
					$expEqDataNewErr = array( $eedn[ 'id' ] , 'test_type' );
					break ;
				}
				$res = trim( $eedn[ 'test_period' ] );
				if ( preg_match( '/^(\d{1,2}г)?(\d{1,2}м)?(\d{1,2}д)?$/' , $res ) != 1 || $res == '' ) {
					$expEqDataNewErr = array( $eedn[ 'id' ] , 'test_period' );
					break ;
				}
				if ( $eedn[ 'test_result' ] == '' ) {
					$expEqDataNewErr = array( $eedn[ 'id' ] , 'test_result' );
					break ;
				}
			}

			if ( $expEqDataNewErr === false ) {
				foreach ( $expEqDataNew as $eedn ) {
					$res = trim( $eedn[ 'test_period' ] );
					$res = str_replace( 'г' , 'y' , $res );
					$res = str_replace( 'м' , 'm' , $res );
					$res = str_replace( 'д' , 'd' , $res );
					$portalDB->noResult(
						"update `exp-equipment` set `test_type` = ? , `test_period` = ? , `test_date` = ? , `test_result` = ? , `state` = ? , `actual` = ? where `ext_id` = ?" ,
						'isiiiii' ,
						$eedn[ 'test_type' ] , $res , Date2Int( $eedn[ 'test_date' ] ) , $eedn[ 'test_result' ] , isset( $eedn[ 'state' ] ) ? 0 : -1 , 1 , $vID
					);

					$portalDB->noResult( "delete from `upload-file-temp` where `file` = ?" , 'i' , $eedn[ 'test_result' ] );
				}
				$expEqDataNew = array();
			}
		} else {
			foreach ( $expEqDataNew as $eedn ) {
				$res = Date2Int( $eedn[ 'test_date' ] );
				if ( $res === false ) {
					$expEqDataNewErr = array( $eedn[ 'id' ] , 'test_date' );
					break ;
				}
				if ( $eedn[ 'test_type' ] == '' || !isset( $tabTestType[ $eedn[ 'test_type' ] ] ) ) {
					$expEqDataNewErr = array( $eedn[ 'id' ] , 'test_type' );
					break ;
				}
				$res = trim( $eedn[ 'test_period' ] );
				if ( preg_match( '/^(\d{1,2}г)?(\d{1,2}м)?(\d{1,2}д)?$/' , $res ) != 1 || $res == '' ) {
					$expEqDataNewErr = array( $eedn[ 'id' ] , 'test_period' );
					break ;
				}
				if ( $eedn[ 'test_result' ] == '' ) {
					$expEqDataNewErr = array( $eedn[ 'id' ] , 'test_result' );
					break ;
				}
			}

			if ( $expEqDataNewErr === false ) {
				foreach ( $expEqDataNew as $eedn ) {
					$res = trim( $eedn[ 'test_period' ] );
					$res = str_replace( 'г' , 'y' , $res );
					$res = str_replace( 'м' , 'm' , $res );
					$res = str_replace( 'д' , 'd' , $res );
					$portalDB->noResult(
						"insert into `exp-equipment` ( `ext_id` , `test_type` , `test_period` , `test_date` , `test_result` , `state` , `actual` ) values ( ? , ? , ? , ? , ? , ? , ? )" ,
						'iisiiii' ,
						$vID , $eedn[ 'test_type' ] , $res , Date2Int( $eedn[ 'test_date' ] ) , $eedn[ 'test_result' ] , isset( $eedn[ 'state' ] ) ? 0 : -1 , 1
					);

					$portalDB->noResult( "delete from `upload-file-temp` where `file` = ?" , 'i' , $eedn[ 'test_result' ] );
				}
				$expEqDataNew = array();
			}
		}
	}

	$tabSpecGroups = $portalDB->query( "select * from `specialities-groups` where `index` is not null order by `index`" , 'id' );
	$tabSpecialities = $portalDB->table( 'specialities' , 'id' );
	$tabSpecAMap = array();
	foreach( $tabSpecialities as &$specInfo ) {
		$sG = $tabSpecGroups[ $specInfo[ 'group' ] ];
		$sNum = $sG[ 'index' ].'.'.$specInfo[ 'num' ];
		if ( !isset( $tabSpecAMap[ $sNum ] ) ) {
			$tabSpecAMap[ $sNum ] = array();
		}
		$tabSpecAMap[ $sNum ][]= $specInfo[ 'id' ];
		$specInfo[ '__full_num' ] = $sNum ;
	} unset( $specInfo );

	if ( isset( $_REQUEST[ 'btnApply' ] ) ) {
		$eqData = array(
			'name'                => isset( $_REQUEST[ 'i_name' ] ) ? $_REQUEST[ 'i_name' ] : '' ,
			'label'               => isset( $_REQUEST[ 'i_label' ] ) ? $_REQUEST[ 'i_label' ] : '' ,
			'reg-number'          => isset( $_REQUEST[ 'i_regNumber' ] ) ? $_REQUEST[ 'i_regNumber' ] : '' ,
			'manufacture-number'  => isset( $_REQUEST[ 'i_manufactureNumber' ] ) ? $_REQUEST[ 'i_manufactureNumber' ] : '' ,
			'mi-type-number'      => isset( $_REQUEST[ 'i_miTypeNumber' ] ) ? $_REQUEST[ 'i_miTypeNumber' ] : '' ,
			'mi-type-title'       => isset( $_REQUEST[ 'i_miTypeTitle' ] ) ? $_REQUEST[ 'i_miTypeTitle' ] : '' ,
			'mi-type-type'        => isset( $_REQUEST[ 'i_miTypeType' ] ) ? $_REQUEST[ 'i_miTypeType' ] : '' ,
			'mi-modification'     => isset( $_REQUEST[ 'i_miTypeModification' ] ) ? $_REQUEST[ 'i_miTypeModification' ] : '' ,
			'startup-date'        => isset( $_REQUEST[ 'i_startupDate' ] ) ? $_REQUEST[ 'i_startupDate' ] : '' ,
			'book_value'          => isset( $_REQUEST[ 'i_book_value' ] ) ? $_REQUEST[ 'i_book_value' ] : '' ,
			'not_in_demand'       => ( isset( $_REQUEST[ 'i_not_in_demand' ] ) && $_REQUEST[ 'i_not_in_demand' ] == 'not_in_demand' ) ? 1 : 0 ,
			'reallocation_ready'  => ( isset( $_REQUEST[ 'i_reallocation_ready' ] ) && $_REQUEST[ 'i_reallocation_ready' ] == 'reallocation_ready' ) ? 1 : 0 ,
			'decommissioned_date' => ( isset( $_REQUEST[ 'i_decommissioned_date' ] ) && isset( $_REQUEST[ 'i_decommissioned' ] ) && $_REQUEST[ 'i_decommissioned' ] == 'decommissioned' ) ? $_REQUEST[ 'i_decommissioned_date' ] : '' ,
			'mop'                 => isset( $_REQUEST[ 'i_mop' ] ) ? $_REQUEST[ 'i_mop' ] : '' ,
			'mop-comment'         => '' ,
			'use-in-any-exp'      => isset( $_REQUEST[ 'i_useInAnyExp' ] ) && $_REQUEST[ 'i_useInAnyExp' ] == 'useInAnyExp' ? 1 : 0
		);

		$eqData[ 'not_in_demand_comment' ] = ( $eqData[ 'not_in_demand' ] == 1 && isset( $_REQUEST[ 'i_not_in_demand_comment' ] ) ) ? $_REQUEST[ 'i_not_in_demand_comment' ] : '' ;
		$eqData[ 'reallocation_comment' ]  = ( $eqData[ 'reallocation_ready' ] == 1 && isset( $_REQUEST[ 'i_reallocation_comment' ] ) ) ? $_REQUEST[ 'i_reallocation_comment' ] : '' ;

		foreach( $eqData as &$v ) {
			if ( is_string( $v ) ) {
				$v = trim( $v );
			}
		} unset( $v );

		$m = array();
		if ( preg_match( '/^(\d{2})[,.-](\d{2})[,.-](\d{2,4})$/' , $eqData[ 'startup-date' ] , $m ) == 1 ) {
			$eqData[ 'startup-date' ] = mktime( 0 , 0 , 0 , intval( $m[ 2 ] , 10 ) , intval( $m[ 1 ] , 10 ) , intval( $m[ 3 ] , 10 ) );
		} else {
			$eqData[ 'startup-date' ] = 0 ;
		}

		/* TODO: price
		 * $m = array();
		if ( preg_match( '/^(\d{2})[,.-](\d{2})[,.-](\d{2,4})$/' , $eqData[ 'decommissioned_date' ] , $m ) == 1 ) {
			$eqData[ 'decommissioned_date' ] = mktime( 0 , 0 , 0 , intval( $m[ 2 ] , 10 ) , intval( $m[ 1 ] , 10 ) , intval( $m[ 3 ] , 10 ) );
		} else {
			$eqData[ 'decommissioned_date' ] = 0 ;
		}*/

		$m = array();
		if ( preg_match( '/^(\d{2})[,.-](\d{2})[,.-](\d{2,4})$/' , $eqData[ 'decommissioned_date' ] , $m ) == 1 ) {
			$eqData[ 'decommissioned_date' ] = mktime( 0 , 0 , 0 , intval( $m[ 2 ] , 10 ) , intval( $m[ 1 ] , 10 ) , intval( $m[ 3 ] , 10 ) );
		} else {
			$eqData[ 'decommissioned_date' ] = 0 ;
		}

		$specIDList = array();
		if ( !$eqData[ 'use-in-any-exp' ] ) {
			if ( isset( $_REQUEST[ 'i_spec' ] ) && is_array( $_REQUEST[ 'i_spec' ] ) ) {
				foreach( $_REQUEST[ 'i_spec' ] as $sID => $se ) {
					if ( $se == 1 && isset( $tabSpecialities[ $sID ] ) ) {
						$sNum = $tabSpecialities[ $sID ][ '__full_num' ];
						$specIDList = array_merge( $specIDList , $tabSpecAMap[ $sNum ] );
					}
				}
				if ( count( $specIDList ) == 1 ) {
					$specIDList = $specIDList[ 0 ];
				}
			}
		}

		if ( $eqData[ 'use-in-any-exp' ] ) {
			$filterRules = array(
				'env:equipment-list-name' => 'expertize'
			);
		} else {
			if ( count( $specIDList ) == 0 ) {
				$filterRules = array(
					'env:equipment-list-name' => 'none'
				);
			} else {
				$filterRules = array(
					'env:equipment-list-name' => 'expertize' ,
					'spec-id' => $specIDList
				);
			}
		}

		print_r_html( $filterRules );

		//$eqData[ 'name' ] = ;

		if ( isset( $_REQUEST[ 'add' ] ) ) {
			$portalDB->noResult(
				"insert into `equipment` (
                         `name` ,
                         `label` ,
                         `reg-number` ,
                         `startup-date` ,
                         `book_value` ,
                         `mop` ,
                         `mop-comment` ,                         
                         `filter_rules` ,
                         `manufacture-number` ,
                         `mi-type-number` ,
                         `mi-type-title` ,
                         `mi-type-type` ,
                         `mi-modification`
                    ) values ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )" ,
				'sssisssssssss' ,
				$eqData[ 'name' ] ,
				$eqData[ 'label' ] ,
				$eqData[ 'reg-number' ] ,
				$eqData[ 'startup-date' ] ,
				$eqData[ 'book_value' ] == '' ? null : str_replace( ',' , '.' , $eqData[ 'book_value' ] ),
				$eqData[ 'mop' ] ,
				$eqData[ 'mop-comment' ] ,
				json_encode( $filterRules ) ,
				$eqData[ 'manufacture-number' ] ,
				$eqData[ 'mi-type-number' ] ,
				$eqData[ 'mi-type-title' ] ,
				$eqData[ 'mi-type-type' ] ,
				$eqData[ 'mi-modification' ]
			);
			Redirect( 'equipment.list.php' );
			exit();
		} else
		if ( isset( $_REQUEST[ 'edit' ] ) ) {
			$portalDB->noResult(
				"update `equipment`
					set
						`name` = ? ,
					    `label`  = ? ,
					    `reg-number` = ? ,
					    `startup-date` = ? ,
					    `book_value` = ? ,
					    `reallocation_ready` = ? ,
					    `reallocation_comment` = ? ,
					    `not_in_demand` = ? ,
					    `not_in_demand_comment` = ? ,
					    `decommissioned_date` = ? ,
					    `mop` = ? ,
					    `mop-comment` = ? ,
					    `filter_rules` = ? ,
					    `manufacture-number` = ? ,
					    `mi-type-number` = ? ,
					    `mi-type-title` = ? ,
					    `mi-type-type` = ? ,
					    `mi-modification` = ?
					where
					     `id` = ?" ,
				'sssisisisissssssssi' ,
				$eqData[ 'name' ] ,
				$eqData[ 'label' ] ,
				$eqData[ 'reg-number' ] ,
				$eqData[ 'startup-date' ] ,
				$eqData[ 'book_value' ] == '' ? null : $eqData[ 'book_value' ] ,
				$eqData[ 'reallocation_ready' ] ,
				$eqData[ 'reallocation_comment' ] ,
				$eqData[ 'not_in_demand' ] ,
				$eqData[ 'not_in_demand_comment' ] ,
				$eqData[ 'decommissioned_date' ] ,
				$eqData[ 'mop' ] ,
				$eqData[ 'mop-comment' ] ,
				json_encode( $filterRules ) ,
				$eqData[ 'manufacture-number' ] ,
				$eqData[ 'mi-type-number' ] ,
				$eqData[ 'mi-type-title' ] ,
				$eqData[ 'mi-type-type' ] ,
				$eqData[ 'mi-modification' ] ,
				$vID
			);
			Redirect( 'equipment.list.php' );
			exit();
		}
	} else{
		if ( isset( $_REQUEST[ 'add' ] ) ) {
			$eqData = array(
				'name'                  => '' ,
				'label'                 => '' ,
				'reg-number'            => '' ,
				'manufacture-number'    => '' ,
				'mi-type-number'        => '' ,
				'mi-type-title'         => '' ,
				'mi-type-type'          => '' ,
				'mi-modification'       => '' ,
				'startup-date'          => time(),
				'book_value'            => false ,
				'reallocation_ready'    => false ,
				'reallocation_comment'  => '' ,
				'not_in_demand'         => false ,
				'not_in_demand_comment' => '' ,
				'decommissioned_date'   => 0 ,
				'mop'                   => 'Другой' ,
				'mop-comment'           => '' ,
				'use-in-any-exp'        => 0 ,
				'filter_rules'          => '{"env:equipment-list-name":["none"]}'
			);
			$cMode = 'add' ;
		} else
		if ( isset( $_REQUEST[ 'edit' ] ) ) {
			$eqData = $portalDB->row( "select * from `equipment` where `id` = ?" , 'i' , $vID );
			$expEqData = $portalDB->query( "select * from `exp-equipment` where `ext_id` = ? order by `test_date` asc ;" , 'id' , 'i' , $vID );
			$cMode = 'edit' ;
		} else {
			$cMode = 'exit' ;
		}
	}

	$modes = array(
		'add' => array(
			'name' => 'add' ,
			'action' => 'add' ,
			'btnText' => 'Добавить'
		) ,
		'edit' => array(
			'name' => 'edit' ,
			'action' => 'edit='.$vID ,
			'btnText' => 'Сохранить'
		)
	);

	if ( isset( $modes[ $cMode ] ) ) {
		$cMode = $modes[ $cMode ];
	} else {
		$cMode = false ;
		ErrorPageAndExit();
		exit();
	}

    $t_spec = '' ;
	$wsl = array();
	$eqData[ 'use-in-any-exp' ] = 0 ;
    if ( $eqData[ 'filter_rules' ] ) {
    	$filterRules = json_decode( $eqData[ 'filter_rules' ] , true );
    	if (
    		$filterRules != null &&
			isset( $filterRules[ 'env:equipment-list-name' ] )
		) {
    		$eln = &$filterRules[ 'env:equipment-list-name' ];
    		if ( !is_array( $eln ) ) {
				$eln = array( $eln );
			}
    		if ( in_array( 'expertize' , $eln ) ) {
    			if ( isset( $filterRules[ 'spec-id' ] ) ) {
					$spIDL = &$filterRules[ 'spec-id' ];
					if ( !is_array( $spIDL ) ) {
						$spIDL = array( $spIDL );
					}
					$wsl = $spIDL ;
				} else {
					$eqData[ 'use-in-any-exp' ] = 1 ;
				}
			}
		}
	}

    foreach( $tabSpecGroups as $gr ) {
        $specs = $portalDB->simpleQuery( "specialities" , array( 'group' => $gr[ 'id' ] , 'original' => 1 ) , false , array( 'order' => 'num' ) );
        if ( count( $specs ) > 0 ) {
			$sl = Array();
			foreach( $specs as $sp ) {
				$sl[]= '<div><input name="i_spec['.$sp[ 'id' ].']" type="checkbox" value="1"'.( in_array( $sp[ 'id' ] , $wsl ) ? ' checked' : '' ).'> <span class="spec-num">'.$gr[ 'index' ].'.'.$sp[ 'num' ].'</span> '.$sp[ 'desc' ].'</div>' ;
			}
			$t_spec.= '<div class="spec-list-item">
				<div class="param-value" data-group-label="'.$gr[ 'index' ].'. '.inForm( $gr[ 'name' ] , 1 ).'">
					'.implode( $sl ).'
				</div>
			</div>' ;
		}
    }




	MainHead_L2( 'Карточка оборудования' , 'Карточка оборудования' , array( '%UT/buttons.css' , '%UT/equipment.form.css' ) , array( 'files/equipment.form.js' ) );

	echo '<form method="post" action="equipment.form.php?'.$cMode[ 'action' ].'">
		<div class="eq-data-area">
			<div class="panel-left">
				<div class="tab-cap">Сведения об оборудовании</div>
				<div class="ed-container">			
					<div class="ed-tab">
						<div class="ed-tab-section">
							<div>Название (как в ведомости)</div>
							<div><textarea id="i_name" name="i_name">'.$eqData[ 'name' ].'</textarea></div>
						</div>
						<div class="ed-tab-section">
							<div>Название (краткое, для списка)</div>
							<div><textarea id="i_label" name="i_label">'.$eqData[ 'label' ].'</textarea></div>
						</div>
						<div class="ed-tab-section">
							<div class="ed-param-right">Инвентарный номер <input id="i_regNumber" name="i_regNumber" type="text" value="'.$eqData[ 'reg-number' ].'"></div>
						</div>
						<div class="ed-tab-section">
							<div class="ed-param-right">Заводской номер <input id="i_manufactureNumber" name="i_manufactureNumber" type="text" value="'.$eqData[ 'manufacture-number' ].'"></div>
						</div>
						<div class="ed-tab-section">
							<div class="ed-param-right">Регистрационный номер типа СИ <input id="i_miTypeNumber" name="i_miTypeNumber" type="text" value="'.$eqData[ 'mi-type-number' ].'" oninput="doMiTypeNumberCange()"><a id="miTypeInfoLink" target="_blank"></a></div>
						</div>
						<div class="ed-tab-section">
							<!-- <a onclick="checkARSHIN()" class="btn3">Поискать в системе АРШИН</a> -->
						</div>
						<div class="ed-tab-section">
							<div>Наименование типа СИ <textarea id="i_miTypeTitle" name="i_miTypeTitle">'.$eqData[ 'mi-type-title' ].'</textarea></div>
						</div>
						<div class="ed-tab-section">
							<div>Тип СИ <textarea id="i_miTypeType" name="i_miTypeType">'.$eqData[ 'mi-type-type' ].'</textarea></div>
						</div>
						<div class="ed-tab-section">
							<div>Модификация СИ <textarea id="i_miTypeModification" name="i_miTypeModification">'.$eqData[ 'mi-modification' ].'</textarea></div>
						</div>
						<div class="ed-tab-section">
							<div class="ed-param-right">Дата ввода в эксплуатацию <input id="i_startupDate" name="i_startupDate" type="text" value="'.date( 'd-m-Y' , $eqData[ 'startup-date' ] ).'"></div>
						</div>
						<div class="ed-tab-section">
							<div class="ed-param-right">Балансовая стоимость <input id="i_book_value" name="i_book_value" type="text" value="'.( $eqData[ 'book_value' ] !== false ? $eqData[ 'book_value' ] : '' ).'"></div>
						</div>' ;
						if ( $cMode !== false && $cMode[ 'name' ] == 'edit' ) {
							echo '<div class="ed-tab-section">
								<input type="checkbox" id="i_reallocation_ready"  name="i_reallocation_ready"  value="reallocation_ready" '.( $eqData[ 'reallocation_ready' ] ? 'checked="checked"' : '' ).' /> <label for="i_reallocation_ready">Готовы передать</label>
								<div><textarea id="i_reallocation_comment" name="i_reallocation_comment">'.htmlspecialchars( $eqData[ 'reallocation_comment' ] ).'</textarea></div>
							</div>
							<div class="ed-tab-section">
								<input type="checkbox" id="i_not_in_demand"  name="i_not_in_demand"  value="not_in_demand" '.( $eqData[ 'not_in_demand' ] ? 'checked="checked"' : '' ).' /> <label for="i_not_in_demand">Не востребовано</label>
								<div><textarea id="i_not_in_demand_comment" name="i_not_in_demand_comment">'.htmlspecialchars( $eqData[ 'not_in_demand_comment' ] ).'</textarea></div>
							</div>
							<div class="ed-tab-section">
								<div class="ed-param-center"><input type="checkbox" id="i_decommissioned" name="i_decommissioned" value="decommissioned" '.( ( !is_null( $eqData[ 'decommissioned_date' ] ) && ( $eqData[ 'decommissioned_date' ] > 0 ) ) ? 'checked="checked"' : '' ).' /> <label for="i_decommissioned">Списано</label> <input id="i_decommissioned_date" name="i_decommissioned_date" type="text" value="'.date( 'd-m-Y' , $eqData[ 'decommissioned_date' ] ).'"></div>
							</div>' ;
						}
						echo '<div class="ed-tab-section">
							<div>Способ приобретения</div>
							<div><textarea id="i_mop" name="i_mop">'.$eqData[ 'mop' ].'</textarea></div>
						</div>
					</div>
					<div id="exp-section">
						<div class="tab-cap">Сведения о поверке / калибровке</div>' ;

						$f = makeSimpleTable_init_filter();
						$f[ 'test_type' ] = function( &$r , $c , $v ) use ( $tabTestType ) {
							return $tabTestType[ $v ][ 'name' ];
						};
						$f[ 'period' ] = function( &$r , $c , $v ) {
							$v = str_replace( 'y' , 'г' , $v );
							$v = str_replace( 'm' , 'м' , $v );
							$v = str_replace( 'd' , 'д' , $v );
							return $v ;
						};
						$f[ 'result' ] = function( &$r , $c , $v ) {
							if ( !is_null( $v ) ) {
								return '<a href="/file_store/download.php?id='.$v.'" class="ee-result-lnk"></a>' ;
							} else {
								return '' ;
							}
						};
						$f[ 'state' ] = function( &$r , $c , $v ) {
							return ( $v > -1 ? '<div class="ee-state-ok"></div>' : '<div class="ee-state-err"></div>' );
						};

						echo makeSimpleTable(
							'{ "id" : "ee-tab" , "no-table-close-tag" : 1 }' ,
							'[ { "t" : 1 } ]' ,
							'[ '.
							'{ "n" : "test_date"   , "t" : "d"   , "h" : [ { "d" : "Дата теста" } ] } , '.
							'{ "n" : "test_type"   , "t" : "ss"  , "h" : [ { "d" : "Тип теста" } ] , "f" : "test_type" } , '.
							'{ "n" : "test_period" , "t" : "ss"  , "h" : [ { "d" : "Периодичность теста" } ] , "s" : "ee-period" , "f" : "period" } , '.
							'{ "n" : "test_result" , "t" : "ss"  , "h" : [ { "d" : "Результат теста" } ] , "s" : "ee-result" , "f" : "result" } , '.
							'{ "n" : "state"       , "t" : "ss"  , "h" : [ { "d" : "Можно использовать" } ] , "s" : "ee-state" , "f" : "state" }'.
							' ]' ,
							$expEqData ,
							array( 'dr' => 'dr-d' ) ,
							$f
						);

						$f = makeSimpleTable_init_filter();
						$f[ 'test_date' ] = function( &$r , $c , $v ) use ( $expEqDataNewErr ) {
							$hl = $expEqDataNewErr !== false && $expEqDataNewErr[ 0 ] == $r[ 'id' ] && $expEqDataNewErr[ 1 ] == 'test_date' ;
							return '<input name="ntiTestDate['.$r[ 'id' ].']" type="text" value="'.$v.'" class="ntiTestDate" '.( $hl ? 'style="background-color : #f00 ;"' : '' ).'>' ;
						};
						$f[ 'test_type' ] = function( &$r , $c , $v ) use ( $tabTestType , $expEqDataNewErr ) {
							$hl = $expEqDataNewErr !== false && $expEqDataNewErr[ 0 ] == $r[ 'id' ] && $expEqDataNewErr[ 1 ] == 'test_type' ;
							$res = '<select name="ntiTestType['.$r[ 'id' ].']" '.( $hl ? 'style="background-color : #f00 ;"' : '' ).'>' ;
							foreach ( $tabTestType as $tt ) {
								$res.= '<option value="'.$tt[ 'id' ].'" '.( $tt[ 'id' ] == $v ? 'selected' : '' ).'>'.$tt[ 'name' ].'</option>' ;
							}
							$res.= '</select>' ;
							return $res ;
						};
						$f[ 'period' ] = function( &$r , $c , $v ) use ( $expEqDataNewErr ) {
							$hl = $expEqDataNewErr !== false && $expEqDataNewErr[ 0 ] == $r[ 'id' ] && $expEqDataNewErr[ 1 ] == 'test_period' ;
							return '<input name="ntiTestPeriod['.$r[ 'id' ].']" type="text" value="'.$v.'" class="ntiTestPeriod" '.( $hl ? 'style="background-color : #f00 ;"' : '' ).'>' ;
						};
						$f[ 'result' ] = function( &$r , $c , $v ) use ( $expEqDataNewErr ) {
							global $dbConfig ;
							$hl = $expEqDataNewErr !== false && $expEqDataNewErr[ 0 ] == $r[ 'id' ] && $expEqDataNewErr[ 1 ] == 'test_result' ;
							return '<input id="ntiTestResult_'.$r[ 'id' ].'" name="ntiTestResult['.$r[ 'id' ].']" type="hidden" value="'.$v.'">'.( $hl ? '<div class="ee-result-err">' : '' ).
								'<a id="ntiTestResult_'.$r[ 'id' ].'_see" class="ntiTestResultSee" '.( $v != '' ? 'href="/file_store/download.php?id='.$v.'"' : 'style="display : none ;"' ).' title="Открыть"></a>'.
								'<a id="ntiTestResult_'.$r[ 'id' ].'_add" onclick="addTestResult( '.$r[ 'id' ].' , '.$dbConfig[ 'main.equipment.testResult.lnk.id' ].' )" class="ntiTestResultAdd" '.( $v != '' ? 'style="display : none ;"' : '' ).' title="Добавить"></a>'.
								'<a id="ntiTestResult_'.$r[ 'id' ].'_del" onclick="delTestResult( '.$r[ 'id' ].' , '.$dbConfig[ 'main.equipment.testResult.lnk.id' ].' )" class="ntiTestResultDel" '.( $v == '' ? 'style="display : none ;"' : '' ).' title="Удалить"></a>'.( $hl ? '</div>' : '' );
						};
						$f[ 'state' ] = function( &$r , $c , $v ) {
							return '<input name="ntiState['.$r[ 'id' ].']" type="checkbox" '.( isset( $r[ 'state' ] ) ? 'checked' : '' ).' class="ntiState">' ;
						};

						echo makeSimpleTable(
							'{ "no-table-open-tag" : 1 }' ,
							'[]' ,
							'[ '.
							'{ "n" : "test_date"   , "t" : "d"   , "h" : [ { "d" : "Дата теста" } ] , "f" : "test_date" } , '.
							'{ "n" : "test_type"   , "t" : "ss"  , "h" : [ { "d" : "Тип теста" } ] , "f" : "test_type" } , '.
							'{ "n" : "test_period" , "t" : "ss"  , "h" : [ { "d" : "Периодичность теста" } ] , "s" : "ee-period" , "f" : "period" } , '.
							'{ "n" : "test_result" , "t" : "ss"  , "h" : [ { "d" : "Результат теста" } ] , "s" : "ee-result" , "f" : "result" } , '.
							'{ "n" : "id"          , "t" : "ss"  , "h" : [ { "d" : "Можно использовать" } ] , "s" : "ee-state" , "f" : "state" }'.
							' ]' ,
							$expEqDataNew ,
							array( 'dr' => 'dr-d' ) ,
							$f
						);

						echo '<div>
							<input name="btnAddTestInfo" type="submit" value="Добавить" class="btn3">
							'.( count( $expEqDataNew ) > 0 ? '<input name="btnSaveTestInfo" type="submit" value="Сохранить" class="btn3">' : '' ).'
							'.( count( $expEqDataNew ) == 1 && count( $expEqData ) == 1 && $UserID == 1 ? '<input name="btnReplTestInfo" type="submit" value="Заменить" class="btn3">' : '' ).'
						</div>
					</div>
				</div>
			</div>
			<div class="panel-right">
				<div class="tab-cap">Используется при производстве всех экспертиз <input id="i_useInAnyExp" name="i_useInAnyExp" type="checkbox" value="useInAnyExp" '.( $eqData[ 'use-in-any-exp' ] == 1 ? ' checked ' : '' ).' onchange="toggleUseInAnyExp()"></div>
				<div id="spec-list-area" class="spec-list-area" data-spec-all="'.$eqData[ 'use-in-any-exp' ].'">
					<div class="spec-list">
						'.$t_spec.'
					</div>
				</div>
			</div>
		</div>
		<div class="btn-panel"><input name="btnApply" type="submit" value="'.$cMode[ 'btnText' ].'" class="btn3"></div>
	</form>
	<div id="uploadDlg" style="display : none">
		<div>
			<iframe id="uploadDlgFrame" frameborder="no" seamless="seamless" src="" width="100%" height="380px" class="iframe-std-2"></iframe>
		</div>
		<div>
			<a id="uploadDlgCloseBtn" onclick="uploadDlgClose()" class="btn3">Закрыть</a>
		</div>
	</div>' ;

	closeHtml();
