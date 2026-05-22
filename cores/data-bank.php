<?php

	define( 'DATABANK_DBG' , 1 );

	$bankSet = null ;
	$loadVariables2_functionList = array();

	function fillDataBank2( $param , $defBankSet = false ) {
		global $bankSet , $portalDB , $loadVariables2_functionList ;

		if ( $param[ 'tmpl-data' ] !== false  ) {
			$tmplData = $param[ 'tmpl-data' ];
			$bankSetID = $tmplData[ 'data_bank_set' ];
		} else {
			$bankSetID = $defBankSet ;
		}


		if ( $bankSet == null ) {
			$bankSet = $portalDB->simpleQuery( 'bank-set-items' , array( 'bank_set_id' => $bankSetID ) );
			$bankSet = array_map( function( $i ) {
				if ( is_null( $i[ 'order' ] ) ) {
					$i[ 'order' ] = 999 ;
				}
				return $i ;
			} , $bankSet );
		}

		usort( $bankSet , function( $a , $b ) {
			if ( $a[ 'order' ] == $b[ 'order' ] ) {
				return $a[ 'id' ] - $b[ 'id' ];
			} else {
				return $a[ 'order' ] - $b[ 'order' ];
			}
		} );

		$data = loadVariables2_Init( $param );
		foreach( $bankSet as $bankData ) {
			$type = $bankData[ 'bank_name' ];
			$pInp  = $bankData[ 'param_in' ];
			$pOutp = $bankData[ 'param_out' ];
			if ( isset( $loadVariables2_functionList[ $type ] ) ) {
				$dataEx = $loadVariables2_functionList[ $type ]( $param , $pInp );
				if ( !is_null( $pOutp ) && isset( $dataEx[ $pOutp ] ) ) {
					$param[ $pOutp ] = $dataEx[ $pOutp ][ 'value' ];
				}
				$data = array_merge( $dataEx , $data );
			}
		}
		return $data ;
	}

	function loadVariables2_Init( $param ) {
		global $portalDB , $dbConfigFull , $tabDepartments , $tabWorkers , $tabPosts , $MonthNames ;
		global $UserName ;

		$tabDepartments = $portalDB->table( 'departments' , 'id' );
		$tabWorkers = $portalDB->query( "select `t1`.* , `t2`.`phone` from `workers` as `t1` left join `cabinet` as `t2` on `t1`.`cab` = `t2`.`id`" , 'id' );
		$tabPosts = $portalDB->table( 'posts' , 'id' );

		$docVar = array();
		if ( $param[ 'tmpl-data' ] !== false  ) {
			$tmplData = $param[ 'tmpl-data' ];
			$docVar = array_merge( $docVar , array(
				'tmpl:name'       => array( 'type' => 'string' , 'value' => $tmplData[ 'name' ]       , 'desc' => 'шаблон : Название документа полное'  , 'mf' => true ) ,
				'tmpl:name-short' => array( 'type' => 'string' , 'value' => $tmplData[ 'short_name' ] , 'desc' => 'шаблон : Название документа краткое' , 'mf' => true )
			) );
		}

		$ct = time();

		$docVar = array_merge( $docVar , array(
			'env:date'       => array( 'type' => 'date-time' , 'value' => $ct * 1000 , 'desc' => 'Окружение > Текущая дата' , 'mf' => false ) ,
			'env:user-name'  => array(                         'value' => NAMES_Format( NAMES_parse( $UserName ) , '%F0 %I0 %O0' ) , 'desc' => 'Пользователь > Фамилия Имя Отчество' , 'mf' => true ),
			'env:user-name1' => array(                         'value' => NAMES_Format( NAMES_parse( $UserName ) , '%F0 %i.%o.' ) , 'desc' => 'Пользователь > Фамилия И.О.' , 'mf' => true ),
			'env:user-name2' => array(                         'value' => NAMES_Format( NAMES_parse( $UserName ) , '%i.%o. %F0' ) , 'desc' => 'Пользователь > И.О. Фамилия' , 'mf' => true ),
		) );

		if ( isset( $param[ 'tmpl-list-name' ] ) ) {
			$docVar = array_merge( $docVar , array(
				"env:tmpl-list-name" => array( "value" => $param[ 'tmpl-list-name' ] , "desc" => "Вид списка шаблонов" , "mf" => false ) ,
			) );
		}

		foreach( $dbConfigFull as $c ) {
			if ( !is_null( $c[ 'e-data' ] ) ) {
				$ced = json_decode( $c[ 'e-data' ] , true );
			} else {
				$ced = array();
			}

			if ( isset( $ced[ 'd-tmpl' ] ) && $ced[ 'd-tmpl' ] == 0 ) {
				continue ;
			}

			$ccmf = isset( $ced[ 'mf' ] ) && $ced[ 'mf' ] == 1 ;

			$docVar[ "cfg:".$c[ 'name' ] ] = array(
				'value' => $c[ 'value' ] ,
				'desc' => $c[ 'description' ] ,
				'mf' => $ccmf
			);
		}

		if ( isset( $param[ 'post-init' ] ) ) {
			$pif = $param[ 'post-init' ];
			if ( !is_array( $pif ) ) {
				$pif = array( $pif );
			}
			foreach( $pif as &$cpif ) {
				$cpif( $param , $docVar );
			} unset( $cpif );
		}

		return $docVar ;
	}

	$loadVariables2_functionList[ 'matincoming' ] = function ( $param , $paramIn ) {
		global $portalDB , $MonthNames ;
		global $tabDepartments , $tabTypeOfAgency ;

		$matincoming_id = $param[ $paramIn ];

		$tabCaseCategory = array_column( $portalDB->table( 'casecategory' ) , 'name' , 'id' );
		$tabCaseCategoryIndexes = array_column( $portalDB->table( 'casecategory' ) , 'index' , 'id' );

		$tabTypeOfAgency = $portalDB->table( 'type-of-agency' , 'id' );

		$t1 = $portalDB->row( "select `t1`.* , ifnull( `t1`.`group_id` , 0 ) as `group_id_norm` , `t4`.`ext_id` as `agency_type` , `t4`.`name` as `agency` , `t4`.`destination` as `agency_address` , `t5`.`name` as `agent` from `matincoming` as `t1` , `agency` as `t4` , `agent` as `t5` where ( `t1`.`id` = ? ) and ( `t4`.`id` = `t1`.`from_agency` ) and ( `t5`.`id` = `t1`.`from_agent` )" , 's' , $matincoming_id );
		if ( $t1 === false ) {
			return array();
		}

		if ( $t1[ 'group_id_norm' ] != 0 ) {
			$t1All = $portalDB->query( "select `t1`.* , `t2`.`dep_id` , `t3`.`price` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `group_id` = ? )" , false , 'i' , $t1[ 'group_id_norm' ] );
		} else {
			$t1All = $portalDB->query( "select `t1`.* , `t2`.`dep_id` , `t3`.`price` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `t1`.`id` = ? )" , false , 's' , $t1[ 'id' ] );
			$t1[ 'dep_id' ] = $t1All[ 0 ][ 'dep_id' ];
		}
		$nums = array();
		$totalPrice = 0 ;
		foreach( $t1All as $ct1 ) {
			//$nums[]= matincomingNumber( $ct1[ 'id' ] )."/".$tabDepartments[ $ct1[ 'dep_id' ] ][ 'ind' ].'-'.$ct1[ 'exp_type' ];
			$nums[]= matincomingNumberFull( $ct1[ 'id' ] , $ct1[ 'dep_id' ] , $ct1[ 'exp_type' ] );
			$totalPrice+= $ct1[ 'price' ];
		}
		$nums = array_unique( $nums );

		$t1[ 'agency' ] = normalizeAgency( $t1[ 'agency' ] , $t1 );
		list( $t1[ 'agent' ] , $agentPost , $agentName ) = normalizeAgent( $t1[ 'agent' ] , $t1 );

		$caseData = extractCaseData( $t1 );

		$docVar = array(
			'exp-number'             => array(                         'value' => matincomingNumber( $t1[ 'id' ] ) , 'desc' => 'порядковый номер экспертизы' , 'mf' => false ),
			'exp-number-full'        => array(                         'value' => matincomingNumberFull( $t1[ 'id' ] , $t1[ 'dep_id' ] , $t1[ 'exp_type' ] ) , 'desc' => "номер экспертизы" , 'mf' => false ),
			'exp-number-all'         => array(                         'value' => implode( ", " , $nums ) , 'desc' => "порядковый номер экспертизы (комплексной)" , 'mf' => false ),
			'matincoming-date'       => array( 'type' => 'date-time' , 'value' => strtotime( $t1[ 'date' ] ) * 1000 , 'desc' => 'дата поступления материалов дела' , 'mf' => false ),
			'matincoming-id'         => array(                         'value' => $matincoming_id , 'desc' => 'ID материалов дела' , 'mf' => false ),
			'case-category'          => array(                         'value' => $tabCaseCategory[ $t1[ 'exp_type' ] ] , 'desc' => 'категория дела' , 'mf' => true ),
			'case-category-index'    => array(                         'value' => $tabCaseCategoryIndexes[ $t1[ 'exp_type' ] ] , 'desc' => 'категория дела (индекс)' , 'mf' => false ),
			'case-category-cc-group' => array(                         'value' => $tabCaseCategory[ $t1[ 'exp_type' ] ] , 'desc' => 'категория дела' , 'mf' => true ),
			'case-num'               => array(                         'value' => $caseData[ 'case-num' ] , 'desc' => 'номер дела' , 'mf' => false ),
			'case-size'              => array(                         'value' => $caseData[ 'case-size' ] , 'desc' => 'Количество томов/страниц' , 'mf' => false ),
			'case-pers'              => array(                         'value' => $caseData[ 'case-pers' ] , 'desc' => 'иск кого к кому' , 'mf' => false ),
			'doc-type'               => array(                         'value' => strtolower( $caseData[ 'doc-type' ] ) , 'desc' => 'тип документа' , 'mf' => false ),
			'doc-date'               => array(                         'value' => $caseData[ 'doc-date' ] , 'desc' => 'дата документа' , 'mf' => false ),
			'agency'                 => array(                         'value' => $t1[ 'agency' ] , 'desc' => 'организация-заказчик' , 'mf' => true ),
			'agency-type'            => array(                         'value' => $tabTypeOfAgency[ $t1[ 'agency_type' ] ][ 'name' ] , 'desc' => 'тип организации-заказчика' , 'mf' => true ),
			'agency-address'         => array(                         'value' => $t1[ 'agency_address' ] , 'desc' => 'адрес организации-заказчика' , 'mf' => false ),
			'agent'                  => array(                         'value' => $t1[ 'agent' ] , 'desc' => 'представитель организации-заказчика' , 'mf' => true ),
			'agent-name'             => array(                         'value' => $agentName , 'desc' => 'представитель организации-заказчика > Имя' , 'mf' => true ),
			'agent-post'             => array(                         'value' => $agentPost , 'desc' => 'представитель организации-заказчика > Должность' , 'mf' => true ),
			'state'                  => array(                         'value' => $t1[ 'state' ] , 'desc' => 'состояние экспертизы' , 'mf' => false ) ,
			'exp-price-all'          => array( 'type' => 'price'     , 'value' => $totalPrice , 'desc' => "Стоимость (комплексной)" , 'mf' => false ),
			'lvl1-ex-data3'          => array(                         'value' => $t1[ 'ex_data_3' ] , 'desc' => 'ex_data_3' , 'mf' => false ),
			'lvl1-ex-data4'          => array(                         'value' => $t1[ 'ex_data_4' ] , 'desc' => 'ex_data_4' , 'mf' => false ),
		);

		//print_r_html( $param );
		//print_r_html( $docVar );

		return $docVar ;
	};

	$loadVariables2_functionList[ 'matincominglvl2' ] = function ( $param , $paramIn ) {
		global $portalDB , $tabDepartments ;

		$lvl2cID = $param[ $paramIn ];

		$lvlc2 = $portalDB->simpleRow( 'matincominglvl2' , $lvl2cID );
		$matID = $lvlc2[ 'mat_id' ];
		$lvlc1 = $portalDB->simpleRow( 'matincoming' , $matID );


		$docVar = array(
			'matincoming-id'  => array( 'value' => $matID , 'desc' => 'ID материалов дела' , 'mf' => false ),
			'exp-number-full' => array( 'value' => matincomingNumberFull( $matID , $lvlc2[ 'dep_id' ] , $lvlc1[ 'exp_type' ] ) , 'desc' => "номер экспертизы" , 'mf' => false ),
		);

		return $docVar ;
	};

	$loadVariables2_functionList[ 'expertize' ] = function ( $param , $paramIn ) {
		global $portalDB , $tabDepartments , $tabWorkers , $tabPosts , $tabSpecGroups ;

		$expertize_id = $param[ $paramIn ];

		$tabDepartments = $portalDB->table( 'departments' , 'id' );
		$tabWorkers = $portalDB->query( "select `t1`.* , `t2`.`phone` from `workers` as `t1` left join `cabinet` as `t2` on `t1`.`cab` = `t2`.`id`" , 'id' );
		$tabPosts = $portalDB->table( 'posts' , 'id' );
		$tabSpecGroups = array_column(
			$portalDB->query( "select `t2`.`id` , `t1`.`name` from `specialities-groups` as `t1` , `specialities` as `t2` where `t2`.`group` = `t1`.`id`" ) ,
			'name' , 'id'
		);

		$row = $portalDB->row( "select `t2`.`dep_id` , `t3`.* from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t3`.`id` = ? ) and ( `t3`.`ext_id` = `t2`.`id` )" , 'i' , $expertize_id );
		$worker = $tabWorkers[ $row[ 'exp_id' ] ];
		$name = NAMES_parse( $worker[ 'name' ] );

		$docVar = array(
			'lvl2cID'                 => array(                         'value' => $row[ 'ext_id' ]                                     , 'desc' => "ID карточки 2-го уровня" , 'mf' => false ),
			'expert-id'               => array(                         'value' => $worker[ 'id' ]                                      , 'desc' => "ID эксперта" , 'mf' => false ),
			'expert-fid'              => array(                         'value' => $worker[ 'first_id' ]                                , 'desc' => "FirstID эксперта" , 'mf' => false ),
			'expert-name'             => array(                         'value' => NAMES_Format( $name , "%F0 %I0 %O0" )                , 'desc' => "Эксперт > Фамилия Имя Отчество" , 'mf' => true ),
			'expert-name1'            => array(                         'value' => NAMES_Format( $name , "%F0 %i.%o." )                 , 'desc' => "Эксперт > Фамилия И.О." , 'mf' => true ),
			'expert-name2'            => array(                         'value' => NAMES_Format( $name , "%i.%o. %F0" )                 , 'desc' => "Эксперт > И.О. Фамилия" , 'mf' => true ),
			'expert-post'             => array(                         'value' => $tabPosts[ $worker[ 'post_1_id' ] ][ 'name' ]        , 'desc' => "Эксперт > Должность > Название" , 'mf' => false ),
			'expert-post-simple'      => array(                         'value' => $tabPosts[ $worker[ 'post_1_id' ] ][ 'simple_name' ] , 'desc' => "Эксперт > Должность > Название (упрощенное)" , 'mf' => false ),
			'expert-department'       => array(                         'value' => $tabDepartments[ $worker[ 'dep' ] ][ 'name' ]        , 'desc' => "Эксперт > Отдел > Название" , 'mf' => false ),
			'expert-department-short' => array(                         'value' => $tabDepartments[ $worker[ 'dep' ] ][ 'short_name' ]  , 'desc' => "Эксперт > Отдел > Название (Краткое)" , 'mf' => false ),
			'expert-phone'            => array(                         'value' => $worker[ 'phone' ]                                   , 'desc' => "Эксперт > Отдел > Номер телефона" , 'mf' => false ),
			'spec-group'              => array(                         'value' => $tabSpecGroups[ $row[ 'spec_id' ] ]                  , 'desc' => "вид экспертизы" , 'mf' => true ),
			//'spec-name'             => array(                         'value' => $tabSpecGroups[ $row[ 'spec_id' ] ]                  , 'desc' => "вид экспертизы" , 'mf' => true ),
			'spec-id'                 => array(                         'value' => $row[ 'spec_id' ]                                    , 'desc' => "ID специальности" , 'mf' => true ),
			'exp-price'               => array( 'type' => 'price'     , 'value' => $row[ 'price' ]                                      , 'desc' => "Стоимость" , 'mf' => false ),
			'exp-fin-date'            => array( 'type' => 'date-time' , 'value' => strtotime( $row[ 'fin_date' ] ) * 1000               , 'desc' => "Дата завершения экспертизы" , 'mf' => false ),
			'exp-pay-date'            => array(                         'value' => $row[ 'pay_date' ]                                   , 'desc' => "Дата платежа" , 'mf' => false ),
			'exp-pay-details'         => array(                         'value' => $row[ 'pay_details' ]                                , 'desc' => "Дата платежа" , 'mf' => false ),
		);
		//print_r_html( $param );
		//print_r_html( $docVar );
		return $docVar ;
	};


	$loadVariables2_functionList[ 'expertize-generated-doc' ] = function ( $param , $paramIn ) {
		global $portalDB ;

		$tmplData = $param[ 'tmpl-data' ];
		if ( $tmplData === false ) {
			$tmplData = array( 'code' => '999' );
		}


		$expertize_id = $param[ $paramIn ];

		$row = $portalDB->row( "select `t2`.`mat_id` , `t2`.`dep_id` , `t3`.* from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t3`.`id` = ? ) and ( `t3`.`ext_id` = `t2`.`id` )" , 'i' , $expertize_id );

		$bcs = getCharIDStructure( $row[ 'mat_id' ] );
		$bcs[ 't' ] = $tmplData[ 'code' ];

		$docVar = array(
			'page-code' => array( 'value' => mkCharID( $bcs ) , 'desc' => "код документа" , 'mf' => false ),
		);

		return $docVar ;
	};

	$loadVariables2_functionList[ 'writ-of-execution' ] = function ( $param , $paramIn ) {
		global $portalDB , $tabDepartments , $tabWorkers , $tabPosts , $tabSpecGroups , $tabTypeOfAgency ;

		$woeID = $param[ $paramIn ];

		$dbg = array(
			'woeID'   => $woeID ,
			'param' => $param
		);

		$woe = $portalDB->row( 'select `t1`.* , `t4`.`ext_id` as `type-of-agency` ,  `t4`.`name` as `agency` , `t4`.`destination` as `agency_address` , `t5`.`name` as `agent` from `writ-of-execution` as `t1` , `agency` as `t4` , `agent` as `t5` where ( `t1`.`id` = ? ) and ( `t5`.`id` = `t1`.`from_agent` ) and ( `t4`.`id` = `t5`.`ext_id` )' , 'i' , $woeID );
		$dbg[ 'woe' ] = $woe ;

		//error_log_ml( 'DBG PREVIEW: '.print_r( $dbg , 1 ) );

		$woe[ 'agency' ] = normalizeAgency( $woe[ 'agency' ] , array( 'exp_type' => 2 ) );
		list( $woe[ 'agent' ] , $agentPost , $agentName ) = normalizeAgent( $woe[ 'agent' ] , array( 'exp_type' => 2 ) );

		$docVar = array(
			'woe-num'            => array(                         'value' => $woe[ 'num' ] , 'desc' => "Номер исполнительного листа" , 'mf' => false ),
			'woe-date'           => array( 'type' => 'date-time' , 'value' => $woe[ 'date' ] * 1000 , 'desc' => "Дата составления исполнительного листа" , 'mf' => false ),
			'woe-issue-date'     => array( 'type' => 'date-time' , 'value' => $woe[ 'issue_date' ] * 1000 , 'desc' => "Дата выдачи исполнительного листа" , 'mf' => false ),
			'woe-agency'         => array(                         'value' => $woe[ 'agency' ] , 'desc' => 'организация-заказчик' , 'mf' => true ),
			'woe-agency-type'    => array(                         'value' => $tabTypeOfAgency[ $woe[ 'type-of-agency' ] ][ 'name' ] , 'desc' => 'тип организации-заказчика' , 'mf' => true ),
			'woe-agency-address' => array(                         'value' => $woe[ 'agency_address' ] , 'desc' => 'адрес РОСП' , 'mf' => false ),
			'woe-agent'          => array(                         'value' => $woe[ 'agent' ] , 'desc' => 'представитель организации-заказчика' , 'mf' => true ),
			'woe-agent-name'     => array(                         'value' => $agentName , 'desc' => 'представитель организации-заказчика > Имя' , 'mf' => true ),
			'woe-agent-post'     => array(                         'value' => $agentPost , 'desc' => 'представитель организации-заказчика > Должность' , 'mf' => true ),
			'woe-case-num'       => array(                         'value' => $woe[ 'case_num' ] , 'desc' => 'Номер дела' , 'mf' => false ) ,
		);

		$woePayers = $portalDB->simpleQuery( 'writ-of-execution-payers' , array( 'ext_id' => $woeID ) );
		if ( $woePayers !== false && is_array( $woePayers ) && count( $woePayers ) > 0 ) {
			$woePayer1 = $woePayers[ 0 ];
			$docVar = array_merge( $docVar , array(
				'woe-payer-1-name'   => array(                     'value' => $woePayer1[ 'payer' ] , 'desc' => 'Наименование должника' , 'mf' => false ) ,
				'woe-payer-1-price'  => array( 'type' => 'price' , 'value' => $woePayer1[ 'price' ] , 'desc' => 'Задолженность' , 'mf' => false ) ,
			) );
		}
		return $docVar ;
	};



	function filterElementMatch( $name , $value , &$dataBank ) {
		$__dbg = array(
			'name' => $name ,
			'value' => $value
		);
		if ( isset( $dataBank[ $name ] ) ) {
			$tv = $dataBank[ $name ][ 'value' ];
			if ( !is_array( $value ) ) {
				$value = array( $value );
			}
			$match = false ;
			foreach( $value as $cv ) {
				$match = $match | ( $tv == $cv );
				if ( $match ) {
					break ;
				}
			}
			/*if ( is_null( $tv ) ) {
				print_r_html( $dataBank );
			}*/
			$__dbg[ 'data-bank' ] = $tv ;
			$__dbg[ 'result' ] = $match ;
			//print_r_html( $__dbg );
			return $match ;
		} else {
			$__dbg[ 'result' ] = false ;
			//print_r_html( $__dbg );
			return false ;
		}
	};

	$checkFilterOps = array();

	$checkFilterOps[ 'eq' ] = function( $operand , &$dataBank ) {
		$cop = count( $operand );
		if ( $cop < 2 ) {
			return false ;
		}
		$r = true ;
		$v0 = checkFilter( $operand[ 0 ] , $dataBank );
		for( $i = 1 ; $i <  $cop ; $i++ ) {
			$r = $r & ( $v0 == checkFilter( $operand[ $i ] , $dataBank ) );
			if ( !$r ) {
				break ;
			}
		}
		return $r ;
	};
	$checkFilterOps[ 'not' ] = function( $operand , &$dataBank ) {
		if ( isset( $operand[ 0 ] ) ) {
			return !( checkFilter( $operand[ 0 ] , $dataBank ) );
		} else {
			return false ;
		}
	};
	$checkFilterOps[ 'and' ] = function( $operand , &$dataBank ) {
		if ( count( $operand ) == 0 ) {
			return false ;
		} else {
			$r = true ;
		}
		foreach( $operand as $cop ) {
			$r = $r & checkFilter( $cop , $dataBank );
			if ( !$r ) {
				break ;
			}
		}
		return $r ;
	};
	$checkFilterOps[ 'or' ] = function( $operand , &$dataBank ) {
		$r = false ;
		foreach( $operand as $cop ) {
			$r = $r | checkFilter( $cop , $dataBank );
			if ( $r ) {
				break ;
			}
		}
		return $r ;
	};

	$checkFilterOps[ 'gt' ] = function( $operand , &$dataBank ) {
		if ( count( $operand ) < 2 ) {
			return false ;
		}
		return checkFilter( $operand[ 0 ] , $dataBank ) > checkFilter( $operand[ 1 ] , $dataBank );
	};
	$checkFilterOps[ 'gte' ] = function( $operand , &$dataBank ) {
		if ( count( $operand ) < 2 ) {
			return false ;
		}
		return checkFilter( $operand[ 0 ] , $dataBank ) >= checkFilter( $operand[ 1 ] , $dataBank );
	};
	$checkFilterOps[ 'lt' ] = function( $operand , &$dataBank ) {
		if ( count( $operand ) < 2 ) {
			return false ;
		}
		return checkFilter( $operand[ 0 ] , $dataBank ) < checkFilter( $operand[ 1 ] , $dataBank );
	};
	$checkFilterOps[ 'lte' ] = function( $operand , &$dataBank ) {
		if ( count( $operand ) < 2 ) {
			return false ;
		}
		return checkFilter( $operand[ 0 ] , $dataBank ) <= checkFilter( $operand[ 1 ] , $dataBank );
	};

	function checkFilter( $filterRule , &$dataBank ) {
		global $checkFilterOps ;
		if ( is_string( $filterRule ) ) {
			$filterRule = json_decode( $filterRule , false );
		}
		//print_r_html( $filterRule );
		if ( is_object( $filterRule ) ) {
			if ( property_exists( $filterRule , 'type' ) ) {
				switch( $filterRule->type ) {
					case 'operation' :
						if ( isset( $checkFilterOps[ $filterRule->name ] ) ) {
							$__dbg = array(
								'function' => 'checkFilterOps' ,
								'name' => $filterRule->name ,
								'operands' => $filterRule->operand ,
							);
							$res = $checkFilterOps[ $filterRule->name ]( $filterRule->operand , $dataBank );
							$__dbg[ 'res' ] = $res ;
							//print_r_html( $__dbg );
							return $res ;
						} else {
							return false ;
						}
						break ;

					case 'check' :
						return filterElementMatch( $filterRule->name , $filterRule->value , $dataBank );
						break ;

					case 'value' :
						if ( isset( $dataBank[ $filterRule->name ] ) ) {
							return $dataBank[ $filterRule->name ][ 'value' ];
						} else {
							return null ;
						}
						break ;

					case 'const' :
						return $filterRule->value ;
						break ;
				}
			} else {
				$tmp = get_object_vars( $filterRule );
				$totalMatch = true ;
				foreach( $tmp as $n => $v ) {
					$totalMatch = $totalMatch & filterElementMatch( $n , $v , $dataBank );
					if ( !$totalMatch ) {
						break ;
					}
				}
				return $totalMatch ;
			}
		} else
		if ( is_array( $filterRule ) ) {
			$totalMatch = true ;
			foreach( $filterRule as $cfr ) {
				$totalMatch = $totalMatch & checkFilter( $cfr , $dataBank );
				if ( !$totalMatch ) {
					break ;
				}
			}
			return $totalMatch ;
		} else {
			return false ;
		}
	}
