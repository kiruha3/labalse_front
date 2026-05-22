<?php

	namespace Correspondence ;
	
	/**
	 * @var $PlaceID
	 * @var $LoginOk
	 * @var TDB $portalDB
	 * @var $UserRights
	 * @var $UserID
	 * @var $UserAllWorkers
	 */

	const TAB_DESCR_COL_SET = array(
		"id-reply"    => '{ "n" : "id" ,          "t" : "Sm" ,   "h" : [ { "d" : "Доставка" } ] , "f" : "reply" }',
		"id-files"    => '{ "n" : "id" ,          "t" : "S160" , "h" : [ { "d" : "файлы" } ] , "f" : "files" }',
		"id-review"   => '{ "n" : "ext_num" ,     "t" : "S160" , "h" : [ { "d" : "№ п/п" } ] , "f" : "review-num" }',
		"addressee"   => '{ "n" : "addressee" ,   "t" : "Sm" ,   "h" : [ { "d" : "Адресант" } ] , "f" : "addressee" }',
		"addressee2"  => '{ "n" : "addressee" ,   "t" : "Sm" ,   "h" : [ { "d" : "Адресаты" } ] , "f" : "addressee" }',
		"name"        => '{ "n" : "name" ,        "t" : "S160" , "h" : [ { "d" : "Наименование документа" } ] }',
		"name-descr"  => '{ "n" : "name" ,        "t" : "Sm" ,   "h" : [ { "d" : "Название документа" } ] , "f" : "descr" }',
		"description" => '{ "n" : "description" , "t" : "Sm" ,   "h" : [ { "d" : "Краткое содержание" } ] }',
		"ext_num"     => '{ "n" : "ext_num" ,     "t" : "S160" , "h" : [ { "d" : "Исходящий номер" } ] }',
		"ext_num2"    => '{ "n" : "ext_num" ,     "t" : "ss" ,   "h" : [ { "d" : "Исходящий номер" } ] }',
		"ext_date"    => '{ "n" : "ext_date" ,    "t" : "d" ,    "h" : [ { "d" : "дата" } ] }',
		"experts"     => '{ "n" : "experts" ,     "t" : "Ss" ,   "h" : [ { "d" : "Резолюция" } ] , "f" : "experts" }',
		"experts2"    => '{ "n" : "experts" ,     "t" : "Ss" ,   "h" : [ { "d" : "Кому распределено" } ] , "f" : "experts" }',
		"experts3"    => '{ "n" : "experts" ,     "t" : "Ss" ,   "h" : [ { "d" : "Ф.И.О. исполнителя" } ] , "f" : "experts" }',
		"experts4"    => '{ "n" : "experts" ,     "t" : "Ss" ,   "h" : [ { "d" : "Рецензент" } ] , "f" : "experts" }',
		"pages1"      => '{ "n" : "pages" ,       "t" : "n" ,    "h" : [ { "d" : "кол-во стра ниц" } ] , "s" : "pages" , "f" : "pages1" }',
		"pages2"      => '{ "n" : "pages" ,       "t" : "n" ,    "h" : [ { "d" : "кол-во при ложе ний" } ] , "s" : "pages" , "f" : "pages2" }',
		"review-org"  => '{ "n" : "addressee" ,   "t" : "Sm" ,   "h" : [ { "d" : "СЭУ" } ] , "f" : "review-to-org" }',
		"review-pers" => '{ "n" : "addressee" ,   "t" : "Sm" ,   "h" : [ { "d" : "Рецензируемый" } ] , "f" : "review-to-person" }',
	);

	const QUERY_TABLES_MAIN = "`correspondence-main` as `t1`" ;

	$viewCfg = array(
		"possibilityInc" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 3" ) ,
				"nextNumCondition" => "`t1`.`type` = 3" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{id-reply,addressee,id-files,name,ext_{num,date},description,experts}' ) ,
			"type" => 3 ,
			"caption" => "Журнал запросов о возможности производства экспертизы" ,
			//'doc-name.reg-corr' => 'Журнал о возм.произ.эксп' ,
			"doc-name" => "Запрос о возможности производства экспертизы" ,
			"doc-types" => array(
				"1210" => "Запрос о возможности, стоимости, сроках"
			) ,
			"marks-groups" => array() ,
			'access-mode-mnemo' => 'POSSIBILITYINC'
		),
		"incomingCorr" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 1" ) ,
				"nextNumCondition" => "`t1`.`type` = 1" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{name-descr,pages{1,2},experts2,addressee,ext_{num2,date},id-files}' ) ,
			"type" => 1 ,
			"caption" => " Журнал входящей корреспонденции" ,
			"doc-name" => "Входящий документ" ,
			"doc-types" => array(
				"2100" => "Письмо (общее)"
			) ,
			"marks-groups" => array( 10 ) ,
			'access-mode-mnemo' => 'INCOMINGCORRESPONDENCE'
		),
		"outgoingCorr" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 2" ) ,
				"nextNumCondition" => "`t1`.`type` = 2" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{addressee2,name-descr,experts3,id-files}' ) ,
			"type" => 2 ,
			"caption" => " Журнал исходящей корреспонденции" ,
			"doc-name" => "Исходящий документ" ,
			"doc-types" => array(
				"2200" => "Исходящий документ"
			) ,
			"marks-groups" => array() ,
			'access-mode-mnemo' => 'OUTGOINGCORRESPONDENCE'
		) ,

		"auto_log_1" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 5" ) ,
				"nextNumCondition" => "`t1`.`type` = 5" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{addressee2,name-descr,experts3,id-files}' ) ,
			"type" => 5 ,
			"caption" => " Журнал автоматических рассылок" ,
			"doc-name" => "Исходящий документ ( автоматическая рассылка )" ,
			"doc-types" => array(
			) ,
			'access-mode-mnemo' => 'AUTO_LOG_1'
		) ,
		"test" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 6" ) ,
				"nextNumCondition" => "`t1`.`type` = 6" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{id-reply,addressee,id-files,name,ext_{num,date},description,experts}' ) ,
			"type" => 6 ,
			"caption" => "Тестовый журнал" ,
			"doc-name" => "Тестовый запрос" ,
			"doc-types" => array(
				"0000" => "Тестовый документ"
			) ,
			'access-mode-mnemo' => 'TEST'
 		) ,
		"incomingCorrPayments" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 7" ) ,
 				"nextNumCondition" => "`t1`.`type` = 7" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{name-descr,pages{1,2},experts2,addressee,ext_{num2,date},id-files}' ) ,
			"type" => 7 ,
			"caption" => "Журнал входящей корреспонденции (относительно оплаты экспертиз)" ,
			"doc-name" => "Входящий документ" ,
			"doc-types" => array(
				"2100" => "Письмо (общее)"
			) ,
			"marks-groups" => array() ,
			'access-mode-mnemo' => 'INCOMINGCORRPAYMENTS'
 		) ,
 		"outgoingCorrPayments" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 8" ) ,
 				"nextNumCondition" => "`t1`.`type` = 8" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{addressee2,name-descr,experts3,id-files}' ) ,
			"type" => 8 ,
			"caption" => "Журнал исходящей корреспонденции (относительно оплаты экспертиз)" ,
			"doc-name" => "Исходящий документ" ,
			"doc-types" => array(
				"2100" => "Письмо (общее)"
			) ,
			"marks-groups" => array() ,
			'access-mode-mnemo' => 'OUTGOINGCORRPAYMENTS'
 		) ,
 		"outgoingCorrPaymentsPre" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 9" ) ,
 				"nextNumCondition" => "`t1`.`type` = 9" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{addressee2,name-descr,experts3,id-files}' ) ,
			"type" => 9 ,
			"caption" => "Журнал исходящей корреспонденции (относительно оплаты экспертиз) - Предрегистрация" ,
			"doc-name" => "Исходящий документ" ,
			"doc-types" => array(
				"2100" => "Письмо (общее)"
			) ,
			"marks-groups" => array() ,
			'access-mode-mnemo' => 'OUTGOINGCORRPAYMENTSPRE'
		) ,
		"reviewLog" => array(
			"query" => array(
				"tables" => array( QUERY_TABLES_MAIN ) ,
				"condition" => array( "`t1`.`type` = 54001" ) ,
				"nextNumCondition" => "`t1`.`type` = 54001" ,
				"group" => "`t1`.`id`"
			) ,
			"tabDescr" => strexp( '{id-review:fid-num,review-org,review-pers,experts4,ext_date,id-files}' ) ,
			"type" => 54001 ,
			"caption" => "Журнал рецензий" ,
			"doc-name" => "Журнал рецензий" ,
			"doc-types" => array(
				"2300" => "Рецензия"
			) ,
			"marks-groups" => array() ,
			'access-mode-mnemo' => 'REVIEWLOG'
		),
	);

 	$corrIDMnemoMap = array();
 	foreach ( $viewCfg as $ccm => $cvc ) {
 		$corrIDMnemoMap[ $cvc[ "type" ] ] = $ccm ;
 	}

	function tntb( $n ) {
		return iconv( "utf8" , "cp1251" , trim( preg_replace( '/\s+/' , " " , $n ) ) );
	}

	$flt = makeSimpleTable_init_filter();
	$flt[ "review-num" ] = function( &$r , $c , &$v ) {
		//return $v."Р" ;
		return $v ;
	};
	$flt[ "marks" ] = function( &$r , $c , &$v ) {
		global $corMarks ;
		if ( is_null( $v ) || $v == "" ) {
			return "" ;
		}

		$res = "<div class=\"cor-marks-area\" style=\"height : ".str_replace( "," , "." , ( count( $v ) * 1.8 ) )."em\">" ;
		$i = 0 ;
		foreach ( $v as $cmd ) {
			$cm = $cmd[ "mark_id" ];
			$res.= "<div class=\"nrr-mark-".$corMarks[ $cm ][ "style" ]."\" style=\"top : ".str_replace( "," , "." , ( $i * 1.8 + 0.9 ) )."em\" title=\"".$corMarks[ $cm ][ "name" ]."\">".$corMarks[ $cm ][ "name" ]."</div>" ;
			$i++ ;
		}
		return $res."</div>" ;
	};

	$flt[ "thisDocID" ] = function( &$r , $c , $v ) {
		return $r[ "num" ]." от ".date( "d-m-Y" , $r[ "date" ] );
	};
	$flt[ "descr" ] = function( &$r , $c , $v ) {
		return "<div>".$v."</div><div>".$r[ "description" ]."</div>" ;
	};
	$flt[ "pages1" ] = function( &$r , $c , $v ) {
		$v = json_decode( $v , true );
		if ( isset( $v[ "m" ] ) ) {
			return $v[ "m" ];
		} else {
			return "&mdash;" ;
		}
	};
	$flt[ "pages2" ] = function( &$r , $c , $v ) {
		$v = json_decode( $v , true );
		if ( isset( $v[ "a" ] ) ) {
			return $v[ "a" ];
		} else {
			return "&mdash;" ;
		}
	};
	$flt[ "experts" ] = function( &$r , $c , &$v ) {
		global $tabWorkers ;
		if ( is_null( $v ) ) {
			return "" ;
		} else {
			foreach ( $v  as &$wd ) {
				$w = $wd[ "exp" ];
				$wd = $tabWorkers[ $w ][ "name" ];
			} unset( $wd );
			return implode( "<br>" , $v );
		}
	};

	$flt[ "reply" ] = function( &$r , $c , $v ) {
		global $corContactsMap ;
		$rid = $r[ "id" ];
		if ( isset( $corContactsMap[ $rid ] ) ) {
			$crl = &$corContactsMap[ $rid ];
		} else {
			$crl = array();
		}
		$res = "" ;
		foreach ( $crl as & $cr ) {
			if ( $cr[ "state" ] == 1 && $cr[ "state-date" ] != 0 ) {
				$ds = date( "d-m-Y" , $cr[ "state-date" ] );
				$ss = "ok" ;
				$tt = $ds." доставлено : " ;

			} else {
				$ds = "" ;
				$ss = "wait" ;
				$tt = "" ;
			}
			$ev = base64_encode( iconv( DEF_CODEPAGE , 'utf-8' , $cr[ 'value' ] ) );
			$res.= '<div class="cor-reply" title="'.$tt.$cr[ 'value' ].'"><div class="nrr-alt-sd">'.$ds.'</div><div class="nrr-alt-ss-'.$ss.'"></div><span class="cor-reply--contact-value" onclick="copyValueToClipboard( event , \''.$ev.'\' );">'.$cr[ 'value' ].'</span></div>' ;
		} unset( $cr );
		return $res ;
	};

	$flt[ "files" ] = function( &$r , $c , $v ) {
		global $currViewName , $corFilesMap ;
		$res = "" ;
		if ( isset( $corFilesMap[ $v ] ) ) {
			$fl = &$corFilesMap[ $v ];
		} else {
			$fl = array();
		}
		foreach ( $fl as &$cf ) {
			$res.= "<a href=\"/documents.php?download=".$cf[ "id" ]."\" class=\"cor-files-lnk\" title=\"".$cf[ "name" ]."\" target=\"_blank\">".$cf[ "name" ]."</a>" ;
		} unset( $cf );
		$res.= "<a class=\"cor-files-ab\" onclick=\"showFUDlg( ".$r[ "id" ]." , '".$currViewName."' )\"></a>" ;
		return $res ;
	};
	$flt[ "btns" ] = function( &$r , $c , $v ) {
		global $currViewName ;
		$res = "" ;
		$res.= "<div class=\"cor-b-e\" onclick=\"showEditNRRDlg( ".$r[ "id" ]." , '".$currViewName."' )\" title=\"Открыть для редактирования\"></div>" ;
		$res.= "<div class=\"cor-b-c\" onclick=\"showEditNRRDlg( ".$r[ "id" ]." , '".$currViewName."' , 1 )\" title=\"Добавить как копию\"></div>" ;
		$res.= "<div class=\"cor-b-a\" onclick=\"new $.TDLGAgentSelect(); showAssignNRRDlg( ".$r[ "id" ]." , '".$currViewName."' )\" title=\"Добавить связанную\"></div>" ;

		$res.= "<div class=\"cor-b-l b-l-at\" onclick=\"showLetterDlg( event , ".$r[ "id" ]." )\" title=\"Этикетка адресная\"><span>Э</span></div>" ;
		return $res ;
	};

	$flt[ "addressee" ] = function( &$r , $c , $v ) {
		foreach( $v as &$a ) {
			$a = $a[ "ay" ].", ".$a[ "at" ];
		} unset( $a );
		return implode( "<br>" , $v ) ;
	};

	$flt[ "review-to-org" ] = function( &$r , $c , $v ) {
		foreach( $v as &$a ) {
			$a = $a[ "ay" ];
		} unset( $a );
		return implode( "<br/>" , $v ) ;
	};
	$flt[ "review-to-person" ] = function( &$r , $c , $v ) {
		foreach( $v as &$a ) {
			$a = $a[ "at" ];
		} unset( $a );
		return implode( "<br/>" , $v ) ;
	};



	const DEFAULT_OPTIONS = array(
		'show-captions' => true,
		'captions-class' => 'cor-title',
		'show-toolbar' => true,
		'paginate' => true,
		'pagination-type' => 'date'
	);

	function IntegrateCorr( $id , $opt ) {
		global $tabDescrColSet , $flt ;


		if ( !is_array( $id ) ) {
			$id = array( $id );
		}

		if ( $opt !== false ) {
			if ( is_string( $opt ) ) {
				$opt = json_decode( $opt , true );
			}
			$opt = array_merge( DEFAULT_OPTIONS , $opt );
		} else {
			$opt = DEFAULT_OPTIONS ;
		}

		if ( isset( $opt[ "cache" ] ) ) {
			$rCache = &$opt[ "cache" ];
		} else {
			$rCache = array();
		}

		$rCacheID = array_keys( $rCache );
		$rNID = array_flip( array_diff_key( array_flip( $id ) , array_flip( $rCacheID ) ) );



		$cvtd = array(
			'fid-btns' => '{ "n" : "id" , "t" : "S64" , "h" : [ { "d" : "" } ] , "f" : "btns" }' ,
			'fid-num'  => '{ "n" : "num" , "t" : "n" , "h" : [ { "d" : "№ п/п" } ] }' ,
			'fid-date' => '{ "n" : "date" , "t" : "d" , "h" : [ { "d" : "Дата" } ] }' ,
		);
		foreach( $currViewCfg[ "tabDescr" ] as $cvtdK ) {
			$dp = strpos( $cvtdK , ':' );
			if ( $dp !== false && $dp > 0 ) {
				$cvtdKFu = substr( $cvtdK , 0 , $dp );
				$cvtdKFi = substr( $cvtdK , $dp + 1 );
				$cvtd[ $cvtdKFi ]= $tabDescrColSet[ $cvtdKFu ];
			} else {
				$cvtd[]= $tabDescrColSet[ $cvtdK ];
			}
		}

		echo makeSimpleTable(
			'[]' ,
			'[ { "t" : 1 } ]' ,
			'[ { "n" : "marks" , "t" : "S64" , "h" : [ { "d" : "" } ] , "f" : "marks" , "s" : "cor-marks-col" } ,'
			.implode( "," , $cvtd ).']' ,
			$res , array( "dr" => "dr-d dr-h" ) , $flt
		);
	}


	$modeAJAX = isset( $_REQUEST[ "mode" ] );
	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			Redirect( "../auth.php" );
		}
	}

	$queryTables = array( QUERY_TABLES_MAIN );
	$queryCondition = array();

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( $UserID == 1 ) {
			//print_r_html( $Rights );
			//print_r_html( $DepAllWorkers );
		}

		foreach( $viewCfg as &$cvci ) {
			$cvciAMM = $cvci[ 'access-mode-mnemo' ];
			if ( isset( $Rights[ $cvciAMM ] ) && is_array( $Rights[ $cvciAMM ] ) ) {
				switch( $Rights[ $cvciAMM ][ 0 ] ) {
					case 'EXPERT' :
						$queryTables[ 'teac' ] = '`correspondence-experts` as `teac`' ;
						$cvci[ 'query' ][ 'condition' ][]= '`teac`.`exp` in ( '.implode( ',' , $UserAllWorkers ).' )' ;
						$cvci[ 'query' ][ 'condition' ][]= '`teac`.`ext_id` = `t1`.`id`' ;
						$cvci[ 'access' ] = true ;
						break ;

					case 'DEPARTMENT' :
						$queryTables[ 'teac' ] = '`correspondence-experts` as `teac`' ;
						$cvci[ 'query' ][ 'condition' ][]= '`teac`.`exp` in ( '.implode( ',' , $DepAllWorkers ).' )' ;
						$cvci[ 'query' ][ 'condition' ][]= '`teac`.`ext_id` = `t1`.`id`' ;
						$cvci[ 'access' ] = true ;
						break ;

					case 'ALL' :
						$queryTables[ 'teac' ] = '`correspondence-experts` as `teac`' ;
						$cvci[ 'access' ] = true ;
						$cvci[ 'query' ][ 'condition' ][]= '`teac`.`ext_id` = `t1`.`id`' ;
						break ;

					default :
						$cvci[ 'query' ][ 'condition' ][]= '0' ;
						$cvci[ 'access' ] = false ;
				}
			} else {
				$cvci[ 'access' ] = false ;
				$cvci[ 'query' ][ 'condition' ][]= '0' ;
			}
		} unset( $cvci );

		/*$viewCfg[ "possibilityInc" ][ "access" ] = array_key_exists( "POSSIBILITYINC" , $Rights );
		$viewCfg[ "incomingCorr" ][ "access" ] = array_key_exists( "INCOMINGCORRESPONDENCE" , $Rights );
		$viewCfg[ "outgoingCorr" ][ "access" ] = array_key_exists( "OUTGOINGCORRESPONDENCE" , $Rights );
		$viewCfg[ "auto_log_1" ][ "access" ] = array_key_exists( "AUTO_LOG_1" , $Rights );
		$viewCfg[ "test" ][ "access" ] = $UserID == 1 ;
		$viewCfg[ "incomingCorrPayments" ][ "access" ] = array_key_exists( "INCOMINGCORRPAYMENTS" , $Rights );
		$viewCfg[ "outgoingCorrPayments" ][ "access" ] = array_key_exists( "OUTGOINGCORRPAYMENTS" , $Rights );
		$viewCfg[ "outgoingCorrPaymentsPre" ][ "access" ] = array_key_exists( "OUTGOINGCORRPAYMENTSPRE" , $Rights );*/
		$viewCfg[ "test" ][ "access" ] = $UserID == 1 ;

		if ( isset( $_REQUEST[ "view" ] ) && ( ( $_REQUEST[ "view" ] == 'any' ) || ( isset( $viewCfg[ $_REQUEST[ "view" ] ] ) && $viewCfg[ $_REQUEST[ "view" ] ][ "access" ] ) ) ) {
			/*if ( array_key_exists( "SUBPOENAS" , $Rights ) ) {
				$subpoenaAdd = in_array( "ADD" , $Rights[ "SUBPOENAS" ] );
				$subpoenaEdit = in_array( "EDIT" , $Rights[ "SUBPOENAS" ] );
				$GoOut = !$subpoenaAdd ;
			} else {
				$subpoenaAdd = $subpoenaEdit = false ;
				$GoOut = true ;
			}*/

			$currViewName = $_REQUEST[ "view" ];
			$totalAccCtrl = array();
			if ( $currViewName != 'any' ) {
				$currViewCfg = $viewCfg[ $currViewName ];
				$totalAccCtrl[]= '( ( '.implode( ' ) and ( ' , $currViewCfg[ 'query' ][ 'condition' ] ).' ) )' ;
			} else {
				$currViewCfg = false ;
				foreach( $viewCfg as &$cvci ) {
					$totalAccCtrl[]= '( ( '.implode( ' ) and ( ' , $cvci[ 'query' ][ 'condition' ] ).' ) )' ;
				} unset( $cvci );
			}

			$totalAccCtrl = '( ( '.implode( ' ) or ( ' , $totalAccCtrl ).' ) )' ;
			$queryCondition[]= $totalAccCtrl ;
			//print_r_html( $totalAccCtrl );

			$GoOut = false ;
		} else {
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	//var_dump( $GoOut );

	if ( $GoOut ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			ErrorMessage( 403 );
		}
	}

	$corrViewCfgRevMap = array();
	foreach( $viewCfg as $cvn => $cvc ) {
		$corrViewCfgRevMap[ $cvc[ 'type' ] ] = $cvn ;
	}

	function getCorrViewName( $type ) {
		global $corrViewCfgRevMap ;
		if ( isset( $corrViewCfgRevMap[ $type ] ) ) {
			return $corrViewCfgRevMap[ $type ];
		} else {
			return false ;
		}
	}

	function getCorrViewCfg( $viewName ) {
		global $viewCfg ;
		return $viewCfg[ $viewName ];
	}

	$corDateYList = $portalDB->query( "select year( from_unixtime( `date` ) ) as `y` from `correspondence-main` where ( `date` is not null ) group by `y` order by `y` desc ;" );
	$corDateSY = date( 'Y' , time() );
	if ( isset( $_REQUEST[ 'y' ] ) ) {
		$corDateSY = intval( $_REQUEST[ 'y' ] );
	}

	$corDateMList = $portalDB->query( "select month( from_unixtime( `date` ) ) as `m` from `correspondence-main` where ( year( from_unixtime( `date` ) ) <=> ? ) group by `m` order by `m` desc ;" , false , 'i' , $corDateSY );
	$corDateSM = date( 'm' , time() );
	if ( isset( $_REQUEST[ 'm' ] ) ) {
		$corDateSM = intval( $_REQUEST[ 'm' ] );
		$corDateSM = $corDateSM < 10 ? '0'.$corDateSM : ''.$corDateSM ;
	}

	function showCorrespondencesOnlyView( $currViewName , $res , $opt = false ) {
		global $viewCfg , $corDateYList , $corDateSY , $corDateMList , $corDateSM , $flt ;

		$currViewCfg = $viewCfg[ $currViewName ];

		if ( $opt !== false ) {
			if ( is_string( $opt ) ) {
				$opt = json_decode( $opt , true );
			}
			$opt = array_merge( DEFAULT_OPTIONS , $opt );
		} else {
			$opt = DEFAULT_OPTIONS ;
		}

		if ( $opt[ 'show-captions' ] ) {
			echo '<div class="'.$opt[ 'captions-class' ].'">
				'.$currViewCfg[ 'caption' ].'
			</div>' ;
		}


		if ( $opt[ 'show-toolbar' ] ) {
			echo '<div>
				<button onclick="showAddNRRDlg( \''.$currViewName.'\' )" class="btn3">Добавить</button>
			</div>' ;
		}

		if ( $opt[ 'paginate' ] ) {

			echo '<div class="date-selector-panel">' ;
				/*foreach( $corDateMList as $cordm ) {
					$cordm = $cordm[ "m" ];
					if ( $cordm == $corDateSM ) {
						echo "<span class=\"cmon_link\"><a>".inForm( $MonthNames[ $cordm - 1 ] , 1 )."</a></span>" ;
					} else {
						echo "<span class=\"mon_link\"><a href=\"main.php?m=".$cordm."&amp;y=".$corDateSY."\">".inForm( $MonthNames[ $cordm - 1 ] , 1 )."</a></span>" ;
					}
				}

				echo " | " ;*/

				foreach ( $corDateYList as $cordy ) {
					$cordy = $cordy[ "y" ];
					if ( $cordy == $corDateSY ) {
						echo '<span class="cmon_link"><a>'.$cordy.'</a></span>' ;
					} else {
						//echo "<span class=\"mon_link\"><a href=\"correspondence.php?view=".$currViewName."&m=".$corDateSM."&amp;y=".$cordy."\">".$cordy."</a></span>" ;
						echo '<span class="mon_link"><a href="correspondence.php?view='.$currViewName.'&y='.$cordy.'">'.$cordy.'</a></span>' ;
					}
				}
			echo "</div>" ;
		}


		$tdcs = TAB_DESCR_COL_SET ;
		$cvtd = array(
			'fid-btns' => '{ "n" : "id" , "t" : "S64" , "h" : [ { "d" : "" } ] , "f" : "btns" }' ,
			'fid-num'  => '{ "n" : "num" , "t" : "n" , "h" : [ { "d" : "№ п/п" } ] }' ,
			'fid-date' => '{ "n" : "date" , "t" : "d" , "h" : [ { "d" : "Дата" } ] }' ,
		);
		foreach( $currViewCfg[ "tabDescr" ] as $cvtdK ) {
			$dp = strpos( $cvtdK , ':' );
			if ( $dp !== false && $dp > 0 ) {
				$cvtdKFu = substr( $cvtdK , 0 , $dp );
				$cvtdKFi = substr( $cvtdK , $dp + 1 );
				$cvtd[ $cvtdKFi ]= $tdcs[ $cvtdKFu ];
			} else {
				$cvtd[]= $tdcs[ $cvtdK ];
			}
		}

		echo makeSimpleTable(
			'{ "dra" : "ts" }' ,
			'[ { "t" : 1 } ]' ,
			'[ { "n" : "marks" , "t" : "S64" , "h" : [ { "d" : "" } ] , "f" : "marks" , "s" : "cor-marks-col" } ,'
//			.' { "n" : "id" , "t" : "S64" , "h" : [ { "d" : "" } ] , "f" : "btns" } ,'
//			.' { "n" : "num" , "t" : "n" , "h" : [ { "d" : "№ п/п" } ] } ,'
//			.' { "n" : "date" , "t" : "d" , "h" : [ { "d" : "Дата" } ] } ,'
			.implode( "," , $cvtd ).']' ,
			$res , array( 'dr' => 'dr-d dr-h' ) , $flt
		);
	}
