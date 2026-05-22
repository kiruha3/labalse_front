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
	 * @var $DepAllWorkers
	 * @var $UserAllWorkers
	 * @var $UserDepartment
	 * @var $UserID
	 * @var $UserThemeLoc
	 * @var $dbConfig
	 * @var $MonthNames
	 * @var $TAB_DEPARTMENTS
	 * @var $TAB_CASECATEGORY
	 */
	include_once( '../barcode.php' );
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );
	require_once( 'request.core.php' );
	require_once( 'gp-info.php' );
	require_once( '../shared/share.maindb.php' );
	require_once( '../documents.core.php' );

	$modeAjax = isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ;

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		if ( !$modeAjax ) {
			Redirect( "../auth.php" );
		} else {
			exit();
		}
	}

	if ( count( $UserRights ) != 1 ) {
		if ( !$modeAjax ) {
			MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
			echo "<br><br><br><br><br>" ;
			MessageForm();
			closeHtml();
			exit();
		} else {
			exit();
		}
	}

	fixTimerData( 'PRE MAIN LOOP' );

	$Rights= ParseRights( strtoupper( $UserRights[ 0 ] ) );

	if ( array_key_exists( "LVL1CARD" , $Rights ) ) {
		$lvl1cardADD = in_array( "ADD" , $Rights[ "LVL1CARD" ] );
		$lvl1cardEDIT = in_array( "EDIT" , $Rights[ "LVL1CARD" ] );
		$lvl1cardSEEALL = in_array( "SEEALL" , $Rights[ "LVL1CARD" ] );
	} else {
		$lvl1cardADD = $lvl1cardEDIT = $lvl1cardSEEALL = false ;
	}

	if ( array_key_exists( "LVL2CARD" , $Rights ) ) {
		$lvl2cardADD = in_array( "ADD" , $Rights[ "LVL2CARD" ] );
		$lvl2cardEDIT = in_array( "EDIT" , $Rights[ "LVL2CARD" ] );
		$lvl2cardSEEALL = in_array( "SEEALL" , $Rights[ "LVL2CARD" ] );
		$lvl2cardDELETE = in_array( "DELETE" , $Rights[ "LVL2CARD" ] );
		$lvl2cardDELETEANY = in_array( "DELETE-ANY" , $Rights[ "LVL2CARD" ] );
	} else {
		$lvl2cardADD = $lvl2cardEDIT = $lvl2cardSEEALL = $lvl2cardDELETE = $lvl2cardDELETEANY = false ;
	}

	if ( array_key_exists( "EXPERTIZE" , $Rights ) ) {
		$expertizeEDIT = in_array( "EDIT" , $Rights[ "EXPERTIZE" ] );
		$expertizeSEEALL = in_array( "SEEALL" , $Rights[ "EXPERTIZE" ] );
		$expertizeSEEUNORDERED = in_array( "SEEUNORDERED" , $Rights[ "EXPERTIZE" ] );
		$expertizeORDER = in_array( "ORDER" , $Rights[ "EXPERTIZE" ] );
	} else {
		$expertizeSEEALL = $expertizeEDIT = $expertizeSEEUNORDERED = false ;
	}

	if ( array_key_exists( "EXTENTIONS" , $Rights ) ) {
		$maySEARCH = in_array("SEARCH", $Rights["EXTENTIONS"]);
		$maySTATISTICS = in_array("STATISTICS", $Rights["EXTENTIONS"]);
		$mayCard_01 = in_array("CARD-01", $Rights["EXTENTIONS"]);
		$mayMAT_CHECKER_01 = in_array( "MAT-CHECKER-01" , $Rights["EXTENTIONS"] );
		$mayPrintAddressLabel = in_array( "PRINT-ADDRESS-LABEL" , $Rights[ "EXTENTIONS" ] );
		$mayLetterRegistry = in_array( "LETTER-REGISTRY" , $Rights[ "EXTENTIONS" ] );
	} else {
		$maySEARCH = $maySTATISTICS = $mayCard_01 = $mayMAT_CHECKER_01 = $mayLetterRegistry = $mayPrintAddressLabel = false ;
	}

	if ( array_key_exists( "PAYMENTS" , $Rights ) ) {
		$paymentsBtn = true ;
	} else {
		$paymentsBtn = false ;
	}

	if ( array_key_exists( "UTILS" , $Rights ) ) {
		$mayUtils = in_array( "LOGIC-CONTROL" , $Rights[ "UTILS" ] );
		$mayScanManualProcessing = in_array( "SCAN-MANUAL-PROCESSING" , $Rights[ "UTILS" ] );
	} else{
		$mayUtils = $mayScanManualProcessing = false ;
	}

	if ( array_key_exists( "ARCHIVE" , $Rights ) ) {
		$mayArchive = in_array( "ARCHIVE" , $Rights[ "ARCHIVE" ] );
	} else {
		$mayArchive = false ;
	}

	if ( array_key_exists( "ENVELOPES" , $Rights ) ) {
		$mayEnvForPayment = in_array( "FOR-PAYMENT" , $Rights[ "ENVELOPES" ] );
	} else {
		$mayEnvForPayment = false ;
	}

	if ( array_key_exists( "ORDERS" , $Rights ) ) {
		$mayOrders = in_array( "ORDERS" , $Rights[ "ORDERS" ] );
	} else {
		$mayOrders = false ;
	}

	if ( array_key_exists( "SUBPOENAS" , $Rights ) ) {
		$mayAddSubpoenas = in_array( "ADD" , $Rights[ "SUBPOENAS" ] );
	} else {
		$mayAddSubpoenas = false ;
	}

	if ( array_key_exists( "ALL-ALL" , $Rights ) ) {
		$mayALLALLALL = in_array( "ALL" , $Rights[ "ALL-ALL" ] );
	} else {
		$mayALLALLALL = false ;
	}
	
	if ( array_key_exists( "GP-INDICATOR" , $Rights ) ) {
		$mayGPIndicator = in_array( "SHOW-GP-INDICATOR" , $Rights[ "GP-INDICATOR" ] );
	} else {
		$mayGPIndicator = false ;
	}
	
	

	/*
	possibilityInc
	possibilityIncRights
	incomingCorrespondence
	incomingCorrespondenceRights
	outgoingCorrespondence
	outgoingCorrespondenceRights

	incomingCorrPayments
	incomingCorrPaymentsRights
	outgoingCorrPayments
	outgoingCorrPaymentsRights
	outgoingCorrPaymentsPre
	outgoingCorrPaymentsPreRights
	*/

	$mayPossibilityInc = array_key_exists( "POSSIBILITYINC" , $Rights );
	$mayIncomingCorr = array_key_exists( "INCOMINGCORRESPONDENCE" , $Rights );
	$mayOutgoingCorr = array_key_exists( "OUTGOINGCORRESPONDENCE" , $Rights );

	$mayIncomingCorrPayments = array_key_exists( "INCOMINGCORRPAYMENTS" , $Rights );
	$mayOutgoingCorrPayments = array_key_exists( "OUTGOINGCORRPAYMENTS" , $Rights );
	$mayOutgoingCorrPaymentsPre = array_key_exists( "OUTGOINGCORRPAYMENTSPRE" , $Rights );
	$mayReviewLog = array_key_exists( "REVIEWLOG" , $Rights );

	if ( array_key_exists( "DOCS-ACCESS-LOG" , $Rights ) ) {
		$mayViewDocsAccessLog = in_array( "VIEW" , $Rights[ "DOCS-ACCESS-LOG" ] );
	} else {
		$mayViewDocsAccessLog = false ;
	}


	if ( true/*$UserID == 1*/ ) {
		$tabSpecialities = $portalDB->query( "select `t1`.`id` , concat( `t2`.`index` , '.' , `t1`.`num` , if( `t1`.`comment` is null , '' , concat( ' (' , `t1`.`comment` , ')' ) ) , '(' , `t1`.`use_in_stat` , ')' ) as `fullNum` , concat( `t2`.`index` , '.' , `t1`.`num` ) as `fullNum-simple` , `norm1` , `norm2` , `norm3` , `norm4` , `use_in_stat` from `specialities` as `t1` ,  `specialities-groups` as `t2` where ( `t1`.`group` = `t2`.`id` )" , "id" );
	} else {
		$tabSpecialities = $portalDB->query( "select `t1`.`id` , concat( `t2`.`index` , '.' , `t1`.`num` , if( `t1`.`comment` is null , \"\" , concat( \" (\" , `t1`.`comment` , \")\" ) ) ) as `fullNum` , `norm1` , `norm2` , `norm3` , `norm4` from `specialities` as `t1` ,  `specialities-groups` as `t2` where ( `t1`.`group` = `t2`.`id` )" , "id" );
	}
	$tabSpecialities[ 0 ] = array( "id" => 0 , "fullNum" => "ОШИБКА" , 'fullNum-simple' => 'ОШИБКА' , 'use_in_stat' => 0 );
	$tabWorkers = $portalDB->table( "workers" , "id" );
	foreach ( $tabWorkers as &$w ) {
		$w[ "name" ] = NAMES_Format( NAMES_parse( $w[ "name" ] ) );
		$w[ "spec-arr" ] = explode( ';' , $w[ "spec" ] );
		$w[ 'stat' ] = 0 ;
	} unset( $w );
	
	$marksCatalog = $portalDB->table( 'marks-catalog' , 'id' );

	$state1Map = array(
		-2 => array( "img" => "e" , "descr" => "ошибочно зарегистрировано" ),
		-1 => array( "img" => "w" , "descr" => "ожидает выполнения другой экспертизы" ),
		 1 => array( "img" => "r" , "descr" => "готово к выдаче" ),
		 2 => array( "img" => "f" , "descr" => "выдано" )
	);

	$l1QueryTables = array(
		"`matincoming` as `t1` , `agency` as `t2` , `agent` as `t3`"
	);
	$l1QueryCondition = array(
		"( `t1`.`from_agency` = `t2`.`id` ) and ( `t1`.`from_agent` = `t3`.`id` )"
	);

	$l1QueryGroup = false ;
	$l1QueryOrder = "`t1`.`date` desc , `t1`.`id` desc" ;
	$l1QueryLimit = false ;

	$showMy = !( $lvl1cardADD || $lvl1cardEDIT || $lvl2cardADD || $lvl2cardEDIT );
	$showMyUnfinished = $showMyFinished = $showMyRet = false ;

	$l1DateYList = $portalDB->row( "select year( min( `date` ) ) as `min_y` , year( max( `date` ) ) as `max_y` from `matincoming` where `date` is not null ;" );
	$l1DateSY = date( 'Y' , time() );
	if ( isset( $_REQUEST[ 'y' ] ) ) {
		$l1DateSY = intval( $_REQUEST[ 'y' ] );
		$showMy = false ;
	}

	$l1DateMList = $portalDB->row( "select month( min( `date` ) ) as `min_m` , month( max( `date` ) ) as `max_m` from `matincoming` where ( year( `date` ) <=> ? );" , 'i' , $l1DateSY );
	$l1DateSM = date( 'm' , time() );
	if ( isset( $_REQUEST[ 'm' ] ) ) {
		$l1DateSM = intval( $_REQUEST[ 'm' ] );
		$l1DateSM = $l1DateSM < 10 ? '0'.$l1DateSM : ''.$l1DateSM ;
		$showMy = false ;
	}

	$SearchResults = false ;
	$dateFiltered = false ;
	$title = 'База' ;

	if ( $modeAjax ) {
		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ "data" ] );
		$data = $DD->documentElement ;

		switch ( $data->nodeName ) {
			case "get-group-cards" :
				$gid = $data->getAttribute( "gid" );
				$rid = $data->getAttribute( "rid" );
				$l1QueryCondition[]= "( `t1`.`group_id` = ".Int2SQL( $gid )." ) and ( `t1`.`id` <> ".Str2SQL( $rid )." )" ;
				break ;

			case "get-by-NY" :
				$l1Y = $data->getAttribute( "y" );
				$l1N = $data->getAttribute( "n" );
				$rid = matincomingID( $l1N , $l1Y );
				$l1QueryCondition[]= "( `t1`.`id` = ".Str2SQL( $rid )." )" ;
				break ;

			case "add-ex-num" :
				$l1ID = $data->getAttribute( "id" );
				$l1NN = $data->getAttribute( "ex-num" );
				$n = preg_match( '/^\s*\d{1,5}\s*(,\s*\d{1,5}\s*)*$/' , $l1NN );
				if ( $n !== 1 ) {
					$l1QueryCondition[]= "( 0 )" ;
				} else {
					$l1IDStr = getCharIDStructure( $l1ID );
					if ( $l1IDStr === false ) {
						$l1QueryCondition[]= "( 0 )" ;
					} else {
						$l1NN = explode( ',' , $l1NN );
						$l1IDStrList = array();
						foreach( $l1NN as $cl1nn ) {
							$l1IDStr[ 'n' ] = str_pad( trim( $cl1nn ) , 6 , 0 , STR_PAD_LEFT );
							$l1IDStrList[]= Str2SQL( mkCharID( $l1IDStr ) );
						}
						$l1QueryCondition[]= "( `t1`.`id` in ( ".implode( ',' , $l1IDStrList )." ) )" ;
					}
				}
				break ;

			case "get-l2-e-cards" :
				header( "Content-Type: text/xml" );
				header( "Pragma: no-cache" );
				header( "Cache-Control: no-store, no-cache, must-revalidate" );
				header( "Expires: ".date( "r" ) );
				header( "Expires: -1" , false );

				$rid = $data->getAttribute( "rid" );

				//$portalDB->dbgMode = true ;

				$res = $portalDB->row( "select `t1`.* from `matincoming` as `t1` where `t1`.`id` = ?" , "s" , $rid );
				if ( isset( $res[ "group_id" ] ) && !( is_null( $res[ "group_id" ] ) || $res[ "group_id" ] == 0 ) ) {
					$mRes = $portalDB->simpleQuery( "matincoming" , array( "group_id" => $res[ "group_id" ] ) , "id" );
				} else {
					$mRes = false ;
				}

				$tab2 = array();
				$tab2[]= '<?xml version="1.0" encoding="windows-1251" ?><result num="'.matincomingNumberFull( $res[ 'id' ] , null , $res[ 'exp_type' ] ).'" type="'.$TAB_CASECATEGORY[ $res[ 'exp_type' ] ][ 'index' ].'">' ;
				$matID = $res[ 'id' ];
				$expTypeID = $res[ 'exp_type' ];

				$res = $portalDB->query( "select `t1`.* , `t2`.* , `t1`.`id` as `l2id` , `t2`.`id` as `eid` from `matincominglvl2`  as `t1` , `expertize` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t1`.`mat_id` = ? )".( $expertizeSEEUNORDERED ? "" : " and ( `t2`.`order_date` is not null )" ).( $lvl2cardSEEALL ? "" : " and ( `t2`.`exp_id` in ( ".implode( " , " , $DepAllWorkers )." ) )" ).( $expertizeSEEALL ? "" : " and ( `t2`.`exp_id` in ( ".implode( " , " , $UserAllWorkers )." ) )" )." group by `t1`.`id` , `t2`.`id` order by null" , false , "s" , $rid );
				if ( $mRes !== false && count( $mRes ) > 1 ) {
					$mRes = $portalDB->query( "select `t1`.* , `t2`.* , `t1`.`id` as `l2id` , `t2`.`id` as `eid` from `matincominglvl2`  as `t1` , `expertize` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t1`.`mat_id` in ( ?* ) )".( $expertizeSEEUNORDERED ? "" : " and ( `t2`.`order_date` is not null )" ).( $lvl2cardSEEALL ? "" : " and ( `t2`.`exp_id` in ( ".implode( " , " , $DepAllWorkers )." ) )" ).( $expertizeSEEALL ? "" : " and ( `t2`.`exp_id` in ( ".implode( " , " , $UserAllWorkers )." ) )" )." group by `t1`.`id` , `t2`.`id` order by null" , false , "*s" , array_keys( $mRes ) );
				} else {
					$mRes = $res ;
				}

				$expertizeIDList = array();
				foreach ( $mRes as $l2card ) {
					$expertizeIDList[]= $l2card[ "eid" ];
				}

				$paymentsList = $portalDB->query( "select * from `payments` where ( `expertize_id` in ( ?* ) )" , "id" , "*i" , $expertizeIDList );
				$paymentsIDList = array_keys( $paymentsList );
				$comments = $portalDB->simpleQuery( 'expertize-comments' , array( 'ext_type' => 'expertize' , 'ext_id' => $expertizeIDList ) );
				$commentsMap = array();
				foreach( $comments as $cc ) {
					$cceid = $cc[ 'ext_id' ];
					if ( !isset( $commentsMap[ $cceid ] ) ) {
						$commentsMap[ $cceid ] = array();
					}
					$commentsMap[ $cceid ][]= '<comment author="'.$tabWorkers[ $cc[ 'exp_id' ] ][ 'name' ].'">'.toCDATA( $cc[ 'comment' ] ).'</comment>' ;
				}

				foreach( $res as $l2card ) {
					$tab2[]= "<tr l2id=\"" ;
					$tab2[]= $l2card[ "l2id" ];
					$tab2[]= "\" full-num=\"" ;
					$tab2[]= matincomingNumberFull( $matID , $l2card[ "dep_id" ] , $expTypeID );
					$tab2[]= "\" del=\"" ;
					$tab2[]= ( $lvl2cardDELETE && $l2card[ "dep_id" ] == $UserDepartment ) || $lvl2cardDELETEANY ? "1" : "0" ;
					$tab2[]= "\" eid=\"" ;
					$tab2[]= $l2card[ "eid" ];
					$tab2[]= "\" dep=\"" ;
					$tab2[]= $TAB_DEPARTMENTS[ $l2card[ "dep_id" ] ][ "ind" ];
					$tab2[]= "\" ordered=\"" ;
					$tab2[]= ( $UserID == 1 || true  ? ( is_null( $l2card[ "order_date" ] ) ? "none" : date( "d-m-Y" , strtotime( $l2card[ "order_date" ] ) ) ) : "1" );
					$tab2[]= "\" date=\"" ;
					$tab2[]= date( "d-m-Y" , strtotime( $l2card[ "date" ] ) );
					$tab2[]= "\" cat=\"" ;
					$tab2[]= $l2card[ "kat_slognost" ];
					$tab2[]= "\" spec=\"" ;
					$tab2[]= $tabSpecialities[ $l2card[ "spec_id" ] ][ "fullNum" ];
					$tab2[]= "\" accTime=\"" ;
					$tab2[]= $dbConfig[ 'matincominglvl2.acc-time.create' ] == 1 ? $l2card[ 'accounting_time' ] : $tabSpecialities[ $l2card[ "spec_id" ] ][ "norm".( $l2card[ "kat_slognost" ] ) ];
					$tab2[]= "\" state=\"" ;
					$tab2[]= $l2card[ "state" ];
					$tab2[]= "\" finDate=\"" ;
					$tab2[]=  in_array( $l2card[ "state" ] , array( 1 , 2 ) ) ? date( "d-m-Y" , strtotime( $l2card[ "fin_date" ] ) ) : "" ;
					$tab2[]= "\"><materials>" ;
					$tab2[]= toCDATA( $l2card[ "materials" ] );
					$tab2[]= "</materials><ed6>" ;
					$tab2[]= toCDATA( $l2card[ "ex_data_6" ] );
					$tab2[]= "</ed6><ed7>" ;
					$tab2[]= toCDATA( $l2card[ "ex_data_7" ] );
					$tab2[]= "</ed7><ed8>" ;
					$tab2[]= toCDATA( $l2card[ "ex_data_8" ] );
					$tab2[]= "</ed8><ed9>" ;
					$tab2[]= toCDATA( $l2card[ "ex_data_9" ] );
					$tab2[]= "</ed9><ed10>" ;
					$tab2[]= toCDATA( $l2card[ "ex_data_10" ] );
					$tab2[]= "</ed10><ed12>" ;
					$tab2[]= toCDATA( $l2card[ "ex_data_12" ] );
					$tab2[]= "</ed12><exp>" ;
					$tab2[]= toCDATA( $tabWorkers[ $l2card[ "exp_id" ] ][ "name" ] );
					$tab2[]= "</exp><price>" ;
					$tab2[]= toCDATA( money_format( "%!i" , $l2card[ "price" ] ) );
					$tab2[]= "</price><price-raw>" ;
					$tab2[]= $l2card[ "price" ];
					$tab2[]= "</price-raw><conclusions>" ;
					$tab2[]= toCDATA( json_encode( array_intersect_key( $l2card , array_flip( strexp( "conclusion{,_1,_2{,_1,_2,_3{,_comment}},_3}" ) ) ) ) );
					$tab2[]= "</conclusions><payment>" ;
					$tab2[]= toCDATA( json_encode( $paymentsIDList ) );
					$tab2[]= "</payment>" ;
					$tab2[]= "<comments>" ;
					$tab2[]= isset( $commentsMap[ $l2card[ "eid" ] ] ) ? implode( $commentsMap[ $l2card[ "eid" ] ] ) : '' ;
					$tab2[]= "</comments>" ;
					$tab2[]= "</tr>" ;
				}
				$tab2[]= "</result>" ;

				echo implode( '' , $tab2 );

				exit();
				break ;
				
			case 'get-spec-stat' :
				header( 'Content-Type: text/xml' );
				header( 'Pragma: no-cache' );
				header( 'Cache-Control: no-store, no-cache, must-revalidate' );
				header( 'Expires: '.date( 'r' ) );
				header( 'Expires: -1' , false );
				
				$tab2 = array();
				$tab2[]= '<?xml version="1.0" encoding="windows-1251" ?>' ;
				
				$rid = $data->getAttribute( 'rid' );
				$row3 = $portalDB->simpleRow( 'expertize' , $rid );
				if ( $row3 === false ) {
					$tab2[]= '<result state="error" />' ;
				} else {
					$specID = $row3[ 'spec_id' ];
					if ( !isset( $tabSpecialities[ $specID ] ) ) {
						$tab2[]= '<result state="error" />' ;
					} else {
						$specFullNum = $tabSpecialities[ $specID ][ 'fullNum-simple' ];
						$tabSpecRevMap = remap( $tabSpecialities , 'fullNum-simple' );
						$allSpec = $tabSpecRevMap[ $specFullNum ];
						$allSpec = array_column( $allSpec , 'id' );
						$specAllWorkers = $portalDB->simpleQuery( 'workers-spec' , array( 'spec_id' => $allSpec ) );
						$specAllWorkers = array_column( $specAllWorkers , 'worker_id' );
						$workersFID = $portalDB->query( "select * from `workers-no-spec` where ( ( `id` in ( ?* ) ) and ( `actual` = 1 ) ) or ( `id` = ? )" , false , '*ii' , $specAllWorkers , $row3[ 'exp_id' ] );
						$workersFID = array_column( $workersFID , 'first_id' );
						$tabWorkersFIDRevMap = remap( $tabWorkers , 'first_id' );
						$allWorkersID = array();
						foreach( $workersFID as $cwFID ) {
							$caw = $tabWorkersFIDRevMap[ $cwFID ];
							$cawID = array_column( $caw , 'id' );
							$allWorkersID = array_merge( $allWorkersID , $cawID );
						}
						
						$allWorkersID = array_unique( $allWorkersID );
						$workersData = array();
						foreach( $allWorkersID as $wid ) {
							$cwd = $tabWorkers[ $wid ];
							$cwFID = $cwd[ 'first_id' ];
							if ( $cwd[ 'actual' ] == 1 ) {
								$workersData[ $cwFID ] = $cwd ;
							}
						}
						
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
								( `t5`.`exp_id` in ( ?* ) ) and
								( `t4`.`id` = `t5`.`ext_id` ) and
								( `t1`.`id` = `t4`.`mat_id` ) and
								( `t1`.`from_agency` = `t2`.`id` ) and
								( `t1`.`from_agent` = `t3`.`id` ) and
								( `t1`.`date` > '2010-01-01' ) and
								( ( `t5`.`state` is null ) or ( `t5`.`state` = 0 ) )" , false , '*i' , $allWorkersID
						);
						
						foreach( $stat as $row ) {
							$cwID = $row[ 'exp_id' ];
							$cwFID = $tabWorkers[ $cwID ][ 'first_id' ];
							$workersData[ $cwFID ][ 'stat' ]++ ;
							if ( in_array( $row[ 'spec_id' ] , $allSpec ) ) {
								if ( !isset( $workersData[ $cwFID ][ 'stat-'.$row[ 'spec_id' ] ] ) ) {
									$workersData[ $cwFID ][ 'stat-'.$row[ 'spec_id' ] ] = 0 ;
								}
								$workersData[ $cwFID ][ 'stat-'.$row[ 'spec_id' ] ]++ ;
							}
						}
						
						$maxStat = 0 ;
						foreach( $workersData as $cwd ) {
							if ( $cwd[ 'stat' ] > $maxStat ) {
								$maxStat = $cwd[ 'stat' ];
							}
						}
						
						$tab2[]= '<result spec="'.$specFullNum.'">' ;
						
						//print_r( $workersData );
						//print_r( $allSpec );
						
						usort( $workersData , function( $a , $b ) { return strcmp( $a[ 'name' ] , $b[ 'name' ] ); } );
						
						foreach( $workersData as $cwd ) {
							//print_r( $cwd );
							foreach( $allSpec as $csid ) {
								//var_dump( $csid );
								$csa = $cwd[ 'spec-arr' ];
								if ( !in_array( $csid , $csa ) ) {
									continue;
								}
								if ( !isset( $cwd[ 'stat-'.$csid ] ) ) {
									$cwd[ 'stat-'.$csid ] = 0;
								}
								$sp = round( 100 * ( $cwd[ 'stat' ] / $maxStat ) , 1 );
								if ( $cwd[ 'stat' ] > 0 ) {
									$sps = round( 100 * ( $cwd[ 'stat-'.$csid ] / $cwd[ 'stat' ] ) , 1 );
								} else {
									$sps = 0 ;
								}
								$csd = $tabSpecialities[ $csid ];
								//print_r( $csd );
								$tab2[] = '<stat-row wid="'.$cwd[ 'id' ].'" sid="'.$csid.'" uis="'.( $csd[ 'use_in_stat' ] == 1 ? 1 : 0 ).'" vs="'.$cwd[ 'stat' ].'" sp="'.$sp.'" vss="'.$cwd[ 'stat-'.$csid ].'" sps="'.$sps.'">'.$cwd[ 'name' ].'</stat-row>';
							}
						}
						
						$tab2[]= '</result>' ;
					}
				}
				
				echo implode( '' , $tab2 );
				
				exit();
				break ;

			default :
				$l1QueryCondition[]= "( `t1`.`id` = '-' )" ;
				break ;
		}

		//$l1QueryCondition[]= "(  )" ;
	} else
	if ( isset( $_REQUEST[ "search" ] ) && $maySEARCH ) {
		$SearchResults = true ;
		//print_r_html( $_REQUEST );
		//$s = iconv( "utf8" , "cp1251" , $_REQUEST[ "i_search_string" ] );
		$s = $_REQUEST[ "i_search_string" ];
		$s = trim( $s );
		$ss = explode( " " , $s );
		$sp = array();
		for ( $i = 0 ; $i < count( $ss ) ; $i++ ) {
			$sp[ $i ] = trim( $ss[ $i ] );
		}

		if ( isset( $_REQUEST[ "i_operation" ] ) && $_REQUEST[ "i_operation" ] == "AND" ) {
			$spOp = "and" ;
		} else {
			$spOp = "or" ;
		}

		$SearchParams = "" ;

		$fields = array();
		if ( isset( $_REQUEST[ "i_sif_date" ] ) ) { array_push( $fields , "`t1`.`date`" ); }
		if ( isset( $_REQUEST[ "i_sif_from" ] ) ) {
			array_push( $fields , "`t2`.`name`" );
			array_push( $fields , "`t3`.`name`" );
      	}
      	if ( isset( $_REQUEST[ "i_sif_ex_data_3" ] ) ) { array_push( $fields , "`t1`.`ex_data_3`" ); }
      	if ( isset( $_REQUEST[ "i_sif_ex_data_4" ] ) ) { array_push( $fields , "`t1`.`ex_data_4`" ); }
      	if ( isset( $_REQUEST[ "i_sif_ex_data_6" ] ) ) { array_push( $fields , "`t1`.`ex_data_6`" ); }
      	if ( isset( $_REQUEST[ "i_sif_ex_data_7" ] ) ) { array_push( $fields , "`t1`.`ex_data_7`" ); }
      	if ( isset( $_REQUEST[ "i_sif_ex_data_8" ] ) ) { array_push( $fields , "`t1`.`ex_data_8`" ); }
      	if ( isset( $_REQUEST[ "i_sif_ex_data_9" ] ) ) { array_push( $fields , "`t1`.`ex_data_9`" ); }

      	for ( $i = 0 ; $i < count( $sp ) ; $i++ ) {
			$tmp = "" ;
			for ( $j = 0 ; $j < count ( $fields ) ; $j++ ) {
				if ( $fields[ $j ] == '`t1`.`date`' ) {
					$tmp = ( $j != 0 ? "$tmp or " : $tmp );
              		$tmp.= "( ".$fields[ $j ]." like concat( '%' , ".Str2SQL( PrepDate( $sp[ $i ] ) )." , '%' ) )" ;
              	} else {
					$tmp = ( $j != 0 ? "$tmp or " : $tmp );
              		$tmp.= "( ".$fields[ $j ]." like concat( '%' , ".Str2SQL( $sp[ $i ] )." , '%' ) )" ;
              	}
			}

			$tmp = "( $tmp )" ;
			$SearchParams = ( $i != 0 ? "$SearchParams $spOp " : $SearchParams );
			$SearchParams.= $tmp ;
		}

		$l1QueryCondition[]= "( $SearchParams )" ;
		$l1QueryLimit = 1000 ;
		$title = "<a href=\"main.php\">База</a> - Результаты поиска" ;
	} else
	if ( isset( $_REQUEST[ "unfilled" ] ) && $lvl1cardSEEALL ) {
		if ( $UserID == 1 && false ) {
			$unfilledCards = $portalDB->query( "select `t1`.`id` from `matincoming` as `t1` left outer join `matincominglvl2` as `t2` on `t1`.`id`=`t2`.`mat_id` where ( `t2`.`mat_id` is null ) and !( `t1`.`state` <=> -2 ) order by `t1`.`id` desc ;" );
		} else {
			$unfilledCards = $portalDB->query( "select `t1`.`id` from `matincoming` as `t1` left outer join `matincominglvl2` as `t2` on `t1`.`id`=`t2`.`mat_id` where ( `t2`.`mat_id` is null ) and ( `t1`.`date` > '2012-01-01' ) and !( `t1`.`state` <=> -2 ) order by `t1`.`id` desc ;" );
		}
		$tr = array( -1 );
		foreach( $unfilledCards as $r ) {
			$tr[]= Str2SQL( $r[ "id" ] );
		}

		if ( !$lvl1cardSEEALL ) {
			$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
			$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` ) and ( `t5`.`exp_id` in ( ".implode( "," , $UserAllWorkers )." ) )" ;
		}

		$l1QueryCondition[]= "( `t1`.`id` in ( ".implode( " , " , $tr )." ) )" ;
		$l1QueryGroup = "`t1`.`id`" ;
	} else
	if ( isset( $_REQUEST[ "singlerow" ] ) ) {
		$Y = intval( $_REQUEST[ "y" ] );
		$mats = explode( "," , $_REQUEST[ "n" ] );
		$matID = array();
		foreach( $mats as $m ) {
			$matID[]= Str2SQL( matincomingID( intval( trim( $m ) ) , $Y ) );
		}

		if ( !$lvl1cardSEEALL ) {
			$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
			$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` ) and ( `t5`.`exp_id` in ( ".implode( "," , $UserAllWorkers )." ) )" ;
		}

		if ( count( $matID ) > 0 ) {
			$l1QueryCondition[]= "( `t1`.`id` in ( ".implode( " , " , $matID )." ) )" ;
		} else {
			$l1QueryCondition[]= "( 0 )" ;
		}
		$l1QueryGroup = "`t1`.`id`" ;
	} else
	if ( isset( $_REQUEST[ "idlist" ] ) ) {
		$mats = getCharIDList( $_REQUEST[ "idlist" ] , true , DOCTYPE_MATINCOMING );

		$matID = array();
		foreach( $mats as $m ) {
			$matID[]= Str2SQL( $m );
		}

		if ( !$lvl1cardSEEALL ) {
			$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
			$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` ) and ( `t5`.`exp_id` in ( ".implode( "," , $UserAllWorkers )." ) )" ;
		}

		if ( count( $matID ) > 0 ) {
			$l1QueryCondition[]= "( `t1`.`id` in ( ".implode( " , " , $matID )." ) )" ;
		} else {
			$l1QueryCondition[]= "( 0 )" ;
		}
		$l1QueryGroup = "`t1`.`id`" ;
	} else
	if ( isset( $_REQUEST[ "category" ] ) ) {
		$category = intval( $_REQUEST[ "category" ] );

		$l1QueryCondition[]= "( `t1`.`exp_type` = ".Int2SQL( $category )." )" ;
		$l1QueryGroup = "`t1`.`id`" ;
		$l1QueryLimit = 1000 ;
	} else
	if ( isset( $_REQUEST[ "marks" ] ) ) {
		$marksIDL = getIDList( $_REQUEST[ "marks" ] , true );
		//$portalDB->dbgMode = true ;
		$marksObjectsLinks = $portalDB->query( "select * from `marks-objects` where ( `mark_id` in ( ?* ) ) and ( `ext_type` = ? )" , false , "*is" , $marksIDL , "matincoming" );
		//print_r_html( $marksObjectsLinks );
		$mats = array_column( $marksObjectsLinks , "ext_id" );

		$matID = array();
		foreach( $mats as $m ) {
			$matID[]= Str2SQL( $m );
		}

		if ( !$lvl1cardSEEALL ) {
			$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
			$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` ) and ( `t5`.`exp_id` in ( ".implode( "," , $UserAllWorkers )." ) )" ;
		}

		if ( count( $matID ) > 0 ) {
			$l1QueryCondition[]= "( `t1`.`id` in ( ".implode( " , " , $matID )." ) )" ;
		} else {
			$l1QueryCondition[]= "( 0 )" ;
		}
		$l1QueryGroup = "`t1`.`id`" ;
	} else
	if ( isset( $_REQUEST[ "experts" ] ) ) {
		$expFIDL = getIDList( $_REQUEST[ "experts" ] );
		$expIDL = array();
		foreach ( $tabWorkers as $cwi ) {
			if ( in_array( $cwi[ "first_id" ] , $expFIDL ) ) {
				$expIDL[]= $cwi[ "id" ];
			}
		}

		$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
		$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` )" ;
		if ( !$lvl1cardSEEALL ) {
			$l1QueryCondition[]= "( `t5`.`exp_id` in ( ".implode( "," , $UserAllWorkers )." ) )" ;

		}

		if ( count( $expIDL ) > 0 ) {
			$l1QueryCondition[]= "( `t5`.`exp_id` in ( ".implode( " , " , $expIDL )." ) )" ;
		} else {
			$l1QueryCondition[]= "( 0 )" ;
		}
		$l1QueryGroup = "`t1`.`id`" ;
	} else
	if ( isset( $_REQUEST[ 'spec' ] ) ) {
		$specIDL = getIDList( $_REQUEST[ 'spec' ] );

		$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
		$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` )" ;
		if ( !$lvl1cardSEEALL ) {
			$l1QueryCondition[]= "( `t5`.`exp_id` in ( ".implode( "," , $UserAllWorkers )." ) )" ;

		}

		if ( count( $specIDL ) > 0 ) {
			$l1QueryCondition[]= "( `t5`.`spec_id` in ( ".implode( " , " , $specIDL )." ) )" ;
		} else {
			$l1QueryCondition[]= "( 0 )" ;
		}
		$l1QueryGroup = "`t1`.`id`" ;
	} else
	if ( isset( $_REQUEST[ "efilter" ] ) ) {
		$flt = json_decode( urldecode( $_REQUEST[ "efilter" ] ) , true );
		if ( isset( $flt[ "state" ] ) ) {
			if ( !is_array( $flt[ "state" ] ) ) {
				$flt[ "state" ] = array( $flt[ "state" ] );
			}
			$l1QueryCondition[]= "( `t1`.`state` in ( ".implode( "," , $flt[ "state" ] )." ) )" ;
			$l1QueryGroup = "`t1`.`id`" ;
		}
	} else
	if ( isset( $_REQUEST[ "unordered" ] ) && $expertizeSEEUNORDERED ) {

		$l1QueryOrder = "`t1`.`date` desc" ;
		$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
		$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` ) and ( `t5`.`order_date` is null )" ;
		$l1QueryGroup = "`t1`.`id`" ;

		$SearchResults = false;
	} else {
		//$showMy = !$lvl1cardSEEALL ;
		if ( isset( $_REQUEST[ "my" ] ) ) {
			$showMy = true ;
		}

		if ( $showMy ) {
			$showMyUnfinished = isset( $_REQUEST[ "myUnfinished" ] );
			$showMyFinished = isset( $_REQUEST[ "myFinished" ] );
			$showMyRet = isset( $_REQUEST[ "myRet" ] );
			if ( !$showMyUnfinished && !$showMyFinished && !$showMyRet ) {
				$showMyUnfinished = true ;
			}

			$sq = array();
			if ( $showMyUnfinished ) {
				$sq[]= "( `t5`.`state` is null )" ;
				$sq[]= "( `t5`.`state` = 0 )" ;
			}

			if ( $showMyFinished ) {
				$sq[]= "( `t5`.`state` = 1 )" ;
			}

			if ( $showMyRet ) {
				$sq[]= "( `t5`.`state` = 2 )" ;
			}

			if ( $showMyUnfinished && ( $showMyFinished || $showMyRet ) ) {
				$l1QueryOrder = "`t1`.`date` desc" ;
			} else
			if ( $showMyUnfinished ) {
				$l1QueryOrder = "`t1`.`date` asc" ;
			} else
			if ( $showMyFinished || $showMyRet ) {
				$l1QueryOrder = "`t5`.`fin_date` desc" ;
			}

			$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
			//$l1QueryCondition[]= "( `t5`.`order_date` is not null )" ;
			$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` and `t5`.`exp_id` in ( ".implode( " , " , $UserAllWorkers )." ) ) and ( ".implode( " or " ,  $sq )." )" ;
			$l1QueryGroup = "`t1`.`id`" ;
		} else {
			if ( !$lvl1cardSEEALL ) {
				$l1QueryTables[]= "`matincominglvl2` as `t4` , `expertize` as `t5`" ;
				$l1QueryCondition[]= "( `t1`.`id` = `t4`.`mat_id` ) and ( `t4`.`id` = `t5`.`ext_id` ) and ( `t5`.`exp_id` in ( ".implode( "," , $UserAllWorkers )." ) )" ;
			}

			$l1DateSMDC = intval( date( "t" , mktime( 0 , 0 , 0 , $l1DateSM , 1 , $l1DateSY ) ) );

			$l1QueryCondition[]= "( `t1`.`date` between '".$l1DateSY."-".$l1DateSM."-01' and '".$l1DateSY."-".$l1DateSM."-".$l1DateSMDC."' )" ;
			$l1QueryGroup = "`t1`.`id`" ;
			$dateFiltered = true ;
		}

		$SearchResults = false;
	}


	$q = "select `t1`.* , `t2`.`name` as `agency_name` , `t3`.`name` as `agent_name` from ".implode( " , ", $l1QueryTables )." where ".implode( " and " , $l1QueryCondition ).( $l1QueryGroup !== false ? " group by ".$l1QueryGroup : "" )." order by ".$l1QueryOrder." ".( $l1QueryLimit !== false ? "limit ".$l1QueryLimit : "" )." ;" ;

	$cw = date( "W" , time() );
	$cy = date( "Y" , time() );
	$currentLine = 0 ;

	$cardsL1Map = array();
	$cardsL2Map = array();
	$cardsL3Map = array();
	$paymentsMap = array();
	$groupsMap = array();

	//fixTimerData( 'main query' );
	$lvl1CardsList = $portalDB->query( $q );
	//fixTimerData( 'main query' );
	$groupIDList = array();
	foreach( $lvl1CardsList as &$lvl1card ) {
		$l1id = $lvl1card[ "id" ];
		$cardsL1Map[ $l1id ] = array( "l1" => &$lvl1card , "l2" => array() , "docs" => array() , "marks" => array() , "e-data" => extractCaseData( $lvl1card ) );
		$gid = $lvl1card[ "group_id" ];
		if ( !is_null( $gid ) && $gid != 0 ) {
			$groupIDList[]= $gid ;
		}
		if ( !is_null( $gid ) && $gid != 0 ) {
			if ( !isset( $groupsMap[ $gid ] ) ) {
				$groupsMap[ $gid ] = array();
			}
			$groupsMap[ $gid ][]= &$cardsL1Map[ $l1id ];
		}
	} unset( $lvl1card );


	$groupIDList = array_unique( $groupIDList );
	if ( count( $groupIDList ) > 0 ) {
		//$groupCountMap = QueryAsArray( $con , "select `group_id` , count( `group_id` ) as `gc` from `matincoming` where ( `group_id` is not null ) and ( `group_id` in ( ".implode( "," , $groupIDList )." ) ) group by `group_id`" , "group_id" );
		$groupCountMap = $portalDB->query( "select `group_id` , count( `group_id` ) as `gc` from `matincoming` where ( `group_id` is not null ) and ( `group_id` in ( ?* ) ) group by `group_id`" , "group_id" , "*i" , $groupIDList );
	} else {
		$groupCountMap = array();
	}

	if ( count( $cardsL1Map ) > 0 ) {
		$lvl1CardIDList = array_keys( $cardsL1Map );
		$q2 = "select `t2`.* from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t2`.`mat_id` in ( ".implode( "," , Str2SQL( $lvl1CardIDList ) )." ) ) and ( `t3`.`ext_id` = `t2`.`id` )".( $lvl2cardSEEALL ? "" : " and ( `t3`.`exp_id` in ( ".implode( " , " , $DepAllWorkers )." ) )" )." group by `t2`.`id` order by null ;" ;
		$lvl2CardsList = $portalDB->query( $q2 );
		foreach( $lvl2CardsList as &$lvl2card ) {
			$l2id = $lvl2card[ "id" ];
			$l1id = $lvl2card[ "mat_id" ];
			$cardsL2Map[ $l2id ] = array( "l2" => &$lvl2card , "l3" => array() );
			$cardsL1Map[ $l1id ][ "l2" ][]= &$cardsL2Map[ $l2id ];
		} unset( $lvl2card );
		
		$marksObjectsLinks = $portalDB->query( "select * from `marks-objects` where ( `ext_id` in ( ?* ) ) and ( `ext_type` = ? )" , false , '*ss' , $lvl1CardIDList , 'matincoming' );
		foreach( $marksObjectsLinks as &$cmol ) {
			$l1id = $cmol[ 'ext_id' ];
			$cardsL1Map[ $l1id ][ 'marks' ][]= &$cmol ;
		} unset( $cmol );

		$fileList = $portalDB->query( "select * , `ext_id` as `mat_id` from `documents` force index ( `ext_type_ext_id` ) where ( `ext_type` = ? ) and ( `ext_id` in ( ?* ) )" , false , "s*s" , "docs" , $lvl1CardIDList );

		if ( !$modeAjax ) {
			foreach( $fileList as &$fi ) {
				$fiExData = Documents\correctDocName( $fi[ 'orig-id' ] , $fi[ 'name' ] , $fi[ 'time' ] );
				$cardsL1Map[ $fi[ 'mat_id' ] ][ 'docs' ][]= '<a href="/documents.php?download='.$fi[ 'id' ].'" class="docs-lnk'.$fiExData[ 'style' ].'" target="_blank" title="'.$fiExData[ 'descr' ].'">'.$fiExData[ 'name' ].'</a>' ;
			} unset( $fi );
		} else {
			foreach( $fileList as &$fi ) {
				$cardsL1Map[ $fi[ "mat_id" ] ][ "docs" ][]= $fi ;
			} unset( $fi );
		}
	}

	if ( count( $cardsL2Map ) > 0 ) {
		$lvl2CardIDList = implode( "," , array_keys( $cardsL2Map ) );
		$q3 = "select * from `expertize` where ( `ext_id` in ( ".$lvl2CardIDList." ) )".( $expertizeSEEALL ? "" : "and ( `exp_id` in ( ".implode( "," , $UserAllWorkers )." ) )" )." ;" ;
		$lvl3CardsList = $portalDB->query( $q3 );
		foreach( $lvl3CardsList as &$lvl3card ) {
			$l3id = $lvl3card[ 'id' ];
			$l2id = $lvl3card[ 'ext_id' ];
			$cardsL2Map[ $l2id ][ 'l3' ][]= &$lvl3card ;
			$cardsL3Map[ $l3id ] = &$lvl3card ;
		} unset( $lvl3card );

		$lvl3CardsIDList = array_column( $lvl3CardsList , 'id' );
		$paymentsList = $portalDB->query( "select * from `payments` where ( `type` = 0 ) and ( `expertize_id` in ( ?* ) )" , false , '*i' , $lvl3CardsIDList );
		
		foreach( $paymentsList as &$payment ) {
			$l3id = $payment[ 'expertize_id' ];
			$cardsL3Map[ $l3id ][ 'payment-data' ] = &$payment ;
		}
	}

	if ( $modeAjax ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );

		echo '<?xml version="1.0" encoding="windows-1251" ?><result>' ;

		$tab1 = array();

		$docsIntegrateOpt = array(
			'docs' => 'info' ,
			'output' => 'xml'
		);

		foreach ( $cardsL1Map as &$c1m ) {
			$row = &$c1m[ 'l1' ];
			$w = date( 'W' , strtotime( $row[ 'date' ] ) );
			$y = date( 'Y' , strtotime( $row[ 'date' ] ) );


			$subtab = '' ;
			$subtab2 = '' ;
			$j = 0 ;
			$inWork = false ;
			$ExpGenus = array();

			$l2c = count( $c1m[ 'l2' ] );

			foreach ( $c1m[ 'l2' ] as &$c2m ) {
				foreach ( $c2m[ 'l3' ] as &$row3 ) {
					$sid = $row3[ 'spec_id' ];
					$ExpGenus[]= array(
						'name' => $tabWorkers[ $row3[ 'exp_id' ] ][ 'name' ] ,
						'spec' => isset( $tabSpecialities[ $sid ] ) ? $tabSpecialities[ $sid ][ 'fullNum' ] : '???'
					);

					$expertize_finished = $row3[ 'state' ] == 1 ;
      				$expertize_woexecute = $row3[ 'state' ] == 2 ;
      				$inWork = $inWork || ( !( $expertize_finished || $expertize_woexecute ) );
				} unset( $row3 );
			} unset( $c2m );

			$mol = $c1m[ 'marks' ];
			$marksEl = '<marks>' ;
			foreach ( $mol as $cmd ) {
				$marksEl.= '<mark id="'.$cmd[ 'mark_id' ].'"><comment>'.toCDATA( 'уст: '.date( 'd-m-Y' , $cmd[ 'date' ] ) ).'</comment></mark>' ;
			}
			$marksEl .= '</marks>' ;

			//$ExpGenus = implode( "\r\n" , $ExpGenus );

			$state1 = $row[ 'state' ];
			$r1id = $row[ 'id' ];
			$l1gid = $row[ 'group_id' ];
			$l1gc = $l1gid != 0 && isset( $groupCountMap[ $l1gid ] ) ? $groupCountMap[ $l1gid ][ 'gc' ] : 0 ;
			$tab1[]= '<tr id="'.$r1id.'" l2c="'.$l2c.'" inWork="'.( $inWork ? '1' : '0' ).'" state="'.$state1.'" 
				matNumber="'.matincomingNumber( $r1id ).'" matNumberFull="'.matincomingNumberFull( $r1id , null , $row[ 'exp_type' ] ).'"
				expType="'.$row[ 'exp_type' ].'" date="'.date( 'd-m-Y' , strtotime( $row[ 'date' ] ) ).'" cw="'.( $w == $cw && $y == $cy ? '1' : '0' ).'"
				gid="'.$l1gid.'" gc="'.$l1gc.'">
					<ay>'.toCDATA( ClearOutputText( $row[ 'agency_name' ] ) ).'</ay>
					<at>'.toCDATA( ClearOutputText( $row[ 'agent_name' ] ) ).'</at>
					<ed3>'.toCDATA( ClearOutputText( $row[ 'ex_data_3' ] ) ).'</ed3>
					<ed4>'.toCDATA( ClearOutputText( $row[ 'ex_data_4' ] ) ).'</ed4>
					<egl>' ;
			foreach ( $ExpGenus as $eg ) {
				$tab1[]= '<eg spec="'.$eg[ 'spec' ].'">'.toCDATA( ClearOutputText( $eg[ 'name' ] ) ).'</eg>' ;
			}
			$tab1[]= '</egl>'.$marksEl.'
					<fl>' ;
			$fileList = $c1m[ 'docs' ];
			$tab1[]= Documents\integrate( $fileList , $docsIntegrateOpt );
			/*foreach ( $fileList as $f ) {
				$tab1[]= '<f><fs>'.toCDATA( ClearOutputText( $f[ 0 ] ) ).'</fs>' ;
				$tab1[]= '<fn>'.toCDATA( ClearOutputText( $f[ 1 ] ) ).'</fn></f>' ;
			}*/
			$tab1[]= '</fl>
					<ed6>'.toCDATA( ClearOutputText( $row[ 'ex_data_6' ] ) ).'</ed6>
					<ed7>'.toCDATA( ClearOutputText( $row[ 'ex_data_7' ] ) ).'</ed7>
					<ed8>'.toCDATA( ClearOutputText( $row[ 'ex_data_8' ] ) ).'</ed8>
					<ed9>'.toCDATA( ClearOutputText( $row[ 'ex_data_9' ] ) ).'</ed9>
				</tr>' ;

			if ( $l2c > 0 ) {
			}
		} unset( $c1m );

		echo implode( $tab1 );

		echo '</result>' ;
	} else {
		MainHead_L2( "База" , $title, array("../%UT/buttons.css", "%UT/main.css" , '/doc-generator/%UT/forms.css'  ), array( "#var UserThemeLoc = \"".$UserThemeLoc."\" ; " , '@/files/labeling/brother--ql-570/main.js' , 'files/main.js' , '/doc-generator/doc-generator.base.js' , '/doc-generator/doc-generator.js' ) , "hlp/main.html");

		//$fKey = isset( $_COOKIE[ "fk" ] ) && ( $_COOKIE[ "fk" ] == base64_encode( sha1( "all-time:".$UserID ) ) );
			
		echo ( $lvl1cardADD ? '<a href="level1card.php?add" class="btn3">добавить</a>' : '' ).
			( $maySEARCH ? '<a href="search.php" class="btn3">поиск</a>' : '' ).
			( $maySTATISTICS ? mkBtn3Menu( 'Форма 15.1 2024 (xlsx,п.88)' , 'stat_preselect.php?as=form-XLSX-15.1-88' ,
				'<a href="stat_preselect.php?as=form-XLSX-15.1-129" class="btn3">Форма 15.1 2022 (xlsx)</a>'
			) : '' ).
			( $lvl1cardADD || $lvl1cardEDIT ? '<a href="log-lvl1.php" class="btn3">Первый уровень 2013</a>' : '' ).
			( $lvl2cardADD || $lvl2cardEDIT ? '<a href="log-lvl2.php" class="btn3">Второй уровень</a>' : '' ).
			( $maySTATISTICS ? mkBtn3Menu( 'Отчет по люд.' , 'ot4et1.php' ,
				'<a href="ot4etruk.php" class="btn3">Стат. отд.</a>
				<a href="ot4etruk-acctime.php" class="btn3">Стат. отд. (индив)</a>
				<a href="report-annual.appendix-5.php" class="btn3">Приложение 5 к годовому отчету</a>
				<a href="report-annual.appendix-5.ind.php" class="btn3">Приложение 5 к годовому отчету (инд)</a>
				<a href="report-annual.reasons.php" class="btn3">О причинах возвратов и сроках</a>
				<a href="ot4etruk_bySpecID.php" class="btn3">Стат. спец. отд.</a>
				<a href="expertize.report.in-the-pipeline.php" class="btn3">В производстве</a>
				<a href="expertize.report.deadline.php" class="btn3">О сроках</a>'
			) : '' ).
			'<br>'.
			( $paymentsBtn ? mkBtn3Menu( 'Оплата' , getPaymentsAddr() ,
				'<a href="payments.report.php" class="btn3" target="_blank">Оплата расшир.</a>
				<a href="writ-of-execution.list.php" class="btn3" target="_blank">Список И/Л</a>'
			) : '' ).
			( $mayUtils ? mkBtn3Menu( 'Логический контроль' , 'utils/logic-control.php' ,
				'<a href="utils/logic-control-2.php" class="btn3">Логический контроль 2</a>
				<a href="utils/logic-control-eq.php" class="btn3">Лог. контроль оборудование</a>'
			) : '' ).
			( $mayScanManualProcessing ? '<a href="utils/scan-mover.php" class="btn3">Ручная обработка сканов</a>' : '' ).
			( $mayLetterRegistry ? '<a href="../register-correspondence.php" class="btn3">Реестр кор.</a>' : '' ).
			( $mayALLALLALL ? '<a href="all.php" class="btn3">Фильтры</a>' : '' ).
			( $lvl1cardEDIT || $lvl1cardSEEALL ? '<a href="main.php?efilter=%7B%22state%22%3A%5B1%5D%7D" class="btn3">Готовые к выдаче</a>' : '' ).
			( $mayArchive ? '<a href="paper-storage.php" class="btn3">Архив</a>' : '' )."

		".( $mayPossibilityInc ? "<a href=\"correspondence.php?view=possibilityInc\" class=\"btn3\" target=\"_blank\">Журнал о возм. произв. эксп</a>" : "" )."
		".( $mayIncomingCorr   ? "<a href=\"correspondence.php?view=incomingCorr\" class=\"btn3\" target=\"_blank\">Журнал вх. корр.</a>" : "" )."
		".( $mayOutgoingCorr   ? "<a href=\"correspondence.php?view=outgoingCorr\" class=\"btn3\" target=\"_blank\">Журнал исх. корр.</a>" : "" )."
		".( $mayIncomingCorrPayments ? "<a href=\"correspondence.php?view=incomingCorrPayments\" class=\"btn3\" target=\"_blank\">Журнал вх. корр. (опл. эксп.)</a>" : "" )."
		".( $mayOutgoingCorrPayments ? "<a href=\"correspondence.php?view=outgoingCorrPayments\" class=\"btn3\" target=\"_blank\">Журнал исх. корр. (опл. эксп.)</a>" : "" )."
		".( $mayOutgoingCorrPaymentsPre ? "<a href=\"correspondence.php?view=outgoingCorrPaymentsPre\" class=\"btn3\" target=\"_blank\">Журнал исх. корр. (опл. эксп.) - Предрег</a>" : "" )."
		".( $mayReviewLog ? "<a href=\"correspondence.php?view=reviewLog\" class=\"btn3\" target=\"_blank\">Журнал рецензий</a>" : "" )."

		".( $mayAddSubpoenas ? "<a href=\"subpoenas.php\" class=\"btn3\">Повестки</a>" : "" )."

		".( $maySTATISTICS ? '<a href="stat_preselect.php?as=stat-equipment" class="btn3" target="_blank">Стат. оборуд</a>' : '' )."
		
		".( $mayViewDocsAccessLog ? '<a href="stat_preselect.php?as=docs-access-log" class="btn3" target="_blank">Журнал доступа к документам</a>' : "" );

		/*if ( count( $multiCards ) > 0 ) {
			echo "<br><a href=\"main.php?singlerow&amp;multicard\" class=\"alert_btn3\" title=\"ACHTUNG !!! HIER DRUCKEN!!!\r\nДвойные карточки 2го уровня\">ВНИМАНИЕ !!!</a>" ;
		}

		if ( count( $katSlognost ) > 0 ) {
			echo "<br><a href=\"main.php?singlerow&amp;katSlognost\" class=\"alert_btn3\" title=\"ACHTUNG !!! HIER DRUCKEN!!!\r\nНе выставлена категория сложности\">ВНИМАНИЕ !!!</a>" ;
		}

		if ( count( $conclusionErr ) > 0 ) {
			echo "<br><a href=\"main.php?singlerow&amp;conclusionErr\" class=\"alert_btn3\" title=\"ACHTUNG !!! HIER DRUCKEN!!!\r\nНе верное число выводов\">ВНИМАНИЕ !!!</a>" ;
		}*/

		echo '<div class="mat-checker">' ;

		if ( $mayMAT_CHECKER_01 ) {
				echo '<input type="text" id="iMatChecker01" class="imc01" onKeyPress="exMatCheck01(event.keyCode);">
				<select id="sMatChecker01">' ;
					$tmp = intval( date( 'Y' , time() ) );
					for( $i = $tmp ; $i >= 2008 ; $i-- ) {
						if ( $i == $tmp ) {
							echo '<option value="'.$i.'" selected="selected">'.$i.'</option>' ;
						} else {
							echo '<option value="'.$i.'">'.$i.'</option>' ;
						}
					}
				echo '</select>
				<a onClick="exMatCheck01(13);" class="btn3">Проверить</a>
				<a onClick="showsinglerow();" class="btn3">Показать это дело</a>
				<div id="rMatChecker01" class="mc01-ru"></div>' ;
		}
		
		$showGPIndicator = false ;
		switch ( $dbConfig[ 'gp-indicator-mode' ] ) {
			case 'show-all' :
					$showGPIndicator = true ;
					break ;
					
			case 'show-rights' :
				$showGPIndicator = $mayGPIndicator ;
				break ;
		}
		
		if ( $showGPIndicator ) {
			fixTimerData( '- gpInfoMain' );
			echo gpInfoMain( true , null , null , $dbConfig[ 'gp-indicator-inc-sndz' ] == 1 );
			fixTimerData( '- gpInfoMain' );
		}
		echo '</div>' ;

		if ( !$SearchResults ) {
			echo "<div><br>" ;

			echo "<span class=\"".( $showMy ? "c" : "" ) ."su_link\">
				<a ".( !$showMy ? "href=\"main.php?my\"" : "" ).">
					Мои экспертизы
				</a>
				<div class=\"myExpPanel\">
					<form action=\"?my\" method=\"post\">
						<input name=\"myFinished\" type=\"checkbox\" value=\"myFinished\" ".( $showMyFinished ? "checked" : "" )."> Выполненые<br>
						<input name=\"myUnfinished\" type=\"checkbox\" value=\"myUnfinished\" ".( $showMyUnfinished ? "checked" : "" )."> В производстве<br>
						<input name=\"myRet\" type=\"checkbox\" value=\"myRet\" ".( $showMyRet ? "checked" : "" )."> Без производства<br>
						<center><input type=\"submit\" value=\"Показать\"></center>
					</form>
				</div>
			</span>" ;

			echo " | " ;

			if ( $expertizeSEEUNORDERED ) {

				if ( isset( $_REQUEST[ "unordered" ] ) ) {
					echo "<span class=\"cmon_link\"><a>Не порученные</a></span>" ;
				} else {
					echo "<span class=\"mon_link\"><a href=\"main.php?unordered\">Не порученные</a></span>" ;
				}

				echo " | " ;
			}

			if ( $dateFiltered ) {
				if ( $l1DateMList !== false && $l1DateMList[ 'min_m' ] !== null && $l1DateMList[ 'max_m' ] !== null ) {
					for( $l1dm = $l1DateMList[ 'max_m' ] ; $l1dm >= $l1DateMList[ 'min_m' ] ; $l1dm-- ) {
						if ( $l1dm == $l1DateSM && !$showMy ) {
							echo '<span class="cmon_link"><a>'.inForm( $MonthNames[ $l1dm - 1 ] , 1 ).'</a></span>' ;
						} else {
							echo '<span class="mon_link"><a href="main.php?m='.$l1dm.'&amp;y='.$l1DateSY.'">'.inForm( $MonthNames[ $l1dm - 1 ] , 1 ).'</a></span>' ;
						}
					}
					echo ' | ' ;
				}

				$yc = 0 ;
				if ( $l1DateYList !== false && $l1DateYList[ 'min_y' ] !== null && $l1DateYList[ 'max_y' ] !== null ) {
					for( $l1dy = $l1DateYList[ 'max_y' ] ; $l1dy >= $l1DateYList[ 'min_y' ] ; $l1dy-- ) {
						if ( $yc == 3 ) {
							echo '<span class="su_link"><a>&gt;&gt;&gt;</a><div class="years-list">' ;
						}
						if ( $l1dy == $l1DateSY ) {
							echo '<span class="cmon_link"><a>'.$l1dy.'</a></span>' ;
						} else {
							echo '<span class="mon_link"><a href="main.php?m='.$l1DateSM.'&amp;y='.$l1dy.'">'.$l1dy.'</a></span>' ;
						}
						$yc++ ;
					}
				}
				if ( $yc > 2 ) {
					echo '</div></span>' ;
				}
			} else {
				echo '<span class="mon_link"><a href="main.php?m='.$l1DateSM.'&y='.$l1DateSY.'">По датам</a></span>' ;
			}

			if ( $lvl1cardSEEALL ) {
				echo " | " ;
				if ( isset( $_REQUEST[ "unfilled" ] ) ) {
					echo "<span class=\"csu_link\"><a>Ожидающие заполнения</a></span>" ;
				} else {
					echo "<span class=\"su_link\"><a href=\"main.php?singlerow&amp;unfilled\">Ожидающие заполнения</a></span>" ;
				}

				echo " | " ;
				if ( isset( $_REQUEST[ "marks" ] ) && $_REQUEST[ "marks" ] == "10" ) {
					echo "<span class=\"csu_link\"><a>Ожидающие пред.оплаты</a></span>" ;
				} else {
					echo "<span class=\"su_link\"><a href=\"main.php?marks=10\">Ожидающие пред.оплаты</a></span>" ;
				}

				echo " | " ;
				if ( isset( $_REQUEST[ "marks" ] ) && $_REQUEST[ "marks" ] == "11" ) {
					echo "<span class=\"csu_link\"><a>пред.оплата поступила</a></span>" ;
				} else {
					echo "<span class=\"su_link\"><a href=\"main.php?marks=11\">пред.оплата поступила</a></span>" ;
				}
			}

			echo "</div>" ;
		}

		function PrintField( $field , $val ) {
			global $SearchResults , $fields , $ss ;
			return ( $SearchResults && in_array( $field , $fields ) ? PrepareOutputText( $val , $ss ) : ClearOutputText( $val ) );
		}

		echo '<br>Всего записей : '.count( $cardsL1Map ).'
		<br><br>
		<table id="MainTable" class="MainTable">
			<tr class="cap1">
				<td colspan="6">
				</td>
				<td>
					Порядковый номер экспертизы
				</td>
				<td>
					Дата поступления материалов / Дата окончания экспертизы
				</td>
				<td>
					От кого поступили материалы, постановление и д.р.
				</td>
				<td>
					Номер дела;
					Количество томов, страниц, приложений;
					Ф.И.О. лиц, привлекаемых к ответственности, сторон по делу
				</td>
				<td>
					Вид экспертизы
				</td>
				<td>
					Ф.И.О. и подпись работника подразделения, получившего материалы, дата получения
				</td>
				<td>
					Сведения о приостановлении срока производства экспертизы
					(причина, даты приостановления и возобновления производства, результат рассмотрения или ходатайства)
				</td>
				<td>
					Дата сдачи заключения, акта, сообщения, письма о возврате без исполнения и материалов для отправки
				</td>
				<td>
					Дата и способ отправки заключения, акта, сообщения, письма о возврате без исполнения и материалов
				</td>
			</tr>
			<tr class="cap2">
				<td class="cap2f0" colspan="6">
				</td>
				<td class="cap2f1">
					1
				</td>
				<td class="cap2f2">
					2
				</td>
				<td class="cap2f3">
					3
				</td>
				<td class="cap2f4">
					4
				</td>
				<td class="cap2f5">
					5
				</td>
				<td class="cap2f6">
					6
				</td>
				<td class="cap2f7">
					7
				</td>
				<td class="cap2f8">
					8
				</td>
				<td class="cap2f9">
					9
				</td>
			</tr>';


		flush();

		$tab1 = array();

		$rpc = 0 ;
		$rpcVL = 50 ;
		$vrid = array();
		$hrid = array();
		$erid = array();

		$sra = false ;
		if ( isset( $_REQUEST[ "showRow" ] ) ) {
			$showRow = trim( $_REQUEST[ "showRow" ] );
		}  else {
			$showRow = false ;
		}

		function hlExData( $f , $ed ) {
			foreach ( $ed as $d ) {
				if ( $d != "" ) {
					$f = str_replace( $d , "<span style=\"background-color : #f00 ; color : #fff\">".$d."</span>" , $f );
				}
			}

			//$f .= "<br>".print_r_html_2( $ed );
			//print_r_html( $ed );

			return $f ;
		}

		fixTimerData( 'PRE MAIN LOOP' );

		fixTimerData( 'MAIN LOOP' );

		foreach ( $cardsL1Map as &$c1m ) {
			$row = &$c1m[ "l1" ];
			$w = date( "W" , strtotime( $row[ "date" ] ) );
			$y = date( "Y" , strtotime( $row[ "date" ] ) );

			$subtab = "" ;
			$subtab2 = "" ;
			$j = 0 ;
			$inWork = false ;
			$ExpGenus = array();
			
			$mol = $c1m[ "marks" ];
			//$marksCatalog
			
			$marksEl = '<div class="mdb-marks-area" style="height : '.str_replace( ',' , '.' , ( count( $mol ) * 1.8 ) ).'em">' ;
			$moli = 0 ;
			foreach ( $mol as $cmd ) {
				$cm = $cmd[ 'mark_id' ];
				$marksEl.= '<div class="nrr-mark-'.$marksCatalog[ $cm ][ 'style' ].'" style="top : '.str_replace( ',' , '.' , ( $moli * 1.8 + 0.9 ) ).'em" title="'.$marksCatalog[ $cm ][ 'name' ]."\nуст: ".date( 'd-m-Y' , $cmd[ 'date' ] ).'">'.$marksCatalog[ $cm ][ 'name' ].'</div>' ;
				$moli++ ;
			}
			$marksEl .= '</div>' ;
			

			$l2c = count( $c1m[ "l2" ] );
			
			$ps1c = 0 ;

			foreach ( $c1m[ "l2" ] as &$c2m ) {
				foreach ( $c2m[ "l3" ] as &$row3 ) {
					$tsfn = $tabSpecialities[ $row3[ "spec_id" ] ][ "fullNum" ];
					$ExpGenus[]= $tabWorkers[ $row3[ "exp_id" ] ][ "name" ]." <span class=\"exp-genus\" ".( ( substr( $tsfn , -3 ) == '(0)' || $row3[ 'use_in_stat' ] == 0 ) && false ? 'style="background-color : #48f"' : '' ).">".$tsfn."</span>" ;
					$expertize_finished = $row3[ "state" ] == 1 ;
					$expertize_woexecute = $row3[ "state" ] == 2 ;
					$inWork = $inWork || ( !( $expertize_finished || $expertize_woexecute ) );
					
					if ( isset( $row3[ 'payment-data' ] ) && $row3[ 'payment-data' ][ 'state' ] == 1 ) {
						$ps1c++ ;
					}
				} unset( $row3 );
			} unset( $c2m );
			
			$paymentStateImg = '' ;
			if ( ( $ps1c > 0 ) ) {
				$paymentStateImg = '<div class="psi-'.( $ps1c == $l2c ? 'full' : 'partial' ).'">&#8381;</div>' ;
			}

			$ExpGenus = implode( "<br>" , $ExpGenus );

			//$ExpGenus = PrepExpGenus( $ExpGenus );
			$state1 = $row[ "state" ];
			$stateImg = "" ;
			if ( isset( $state1Map[ $state1 ] ) ) {
				$state1 = $state1Map[ $state1 ];
				$stateImg = '<div class="l1-s-'.$state1[ 'img' ].'" title="'.$state1[ 'descr' ].'"></div>' ;
			}

			$r1id = $row[ 'id' ];
			$r1idjs = "'".$r1id."'" ;
			if ( isset( $_REQUEST[ 'unordered' ] ) && $expertizeSEEUNORDERED ) {
				$erid[]= $r1id ;
			}
			if ( $showRow !== false ) {
				if ( $r1id == $showRow ) {
					$sra = 0 ;
				} else
				if ( $sra !== false ) {
					$sra++ ;
				}
			}

			$tab1[]= '<tr id="lvl1cRow' ;
			$tab1[]= $r1id ;
			$tab1[]= '" class="mi' ;
			$tab1[]= ( $w == $cw && $y == $cy ? 'c' : 'a' );
			$tab1[]= 'w" onclick="ts(' ;
			$tab1[]= $r1idjs ;
			$tab1[]= ')" valign="middle"' ;
			if ( ( $showRow === false && $rpc++ > $rpcVL ) || ( $showRow !== false && $sra !== false && $sra > 5 ) ) {
				$tab1[]= ' style="display : none ;"' ;
				$hrid[]= $r1id ;
			} else {
				$vrid[]= $r1id ;
			}
			$tab1[]= '><td class="f0M">' ;
			$tab1[]= $marksEl ;
			$tab1[]= '</td><td class="f01">' ;
			if ( $l2c > 0 ) {
				$tab1[]= '<div id="tcimg'.$r1id.'" onclick="tc2('.$r1idjs.')" title="'.( $w == $cw && $y == $cy ? 'Свернуть' : 'Развернуть' ).'" class="l1-c-e"></div>' ;
			}
			$tab1[]= '</td><td class="f02">' ;
			if ( $lvl1cardEDIT ) {
				$tab1[]= '<a href="level1card.php?edit='.$r1id.'" title="Редактировать карточку" class="l1-a-e"></a>' ;
			} else {
				$tab1[]= '<a href="level1card.php?view='.$r1id.'" title="Показать карточку" class="l1-a-e" target="_blank"></a>' ;
			}
			if ( $lvl1cardADD ) {
				$tab1[]= '<a href="level1card.php?add&amp;assign='.$r1id.'" title="Создать карточку 1 уровня и связать с этой" class="l1-a-a"></a>' ;
			}
			if ( $lvl2cardADD ) {
				$tab1[]= '<a href="level2card.php?add='.$r1id.'" title="Добавить карточку 2 уровня" class="l1-a-a2"></a>' ;
			}
			if ( $mayPrintAddressLabel ) {
				$tab1[]= '<a onclick="showLetterDlg( event , '.$r1idjs.' )" title="Этикетка адресная" class="l1-a-l l1-at"><span>Э</span></a>' ;
			}
			$tab1[]= '<a onclick="showAddressesFillDlg( event , ' ;
			$tab1[]= $r1idjs ;
			$tab1[]= ' )" title="Указать адреса" class="l1-a-l l1-at"><span>У</span></a>' ;
			if ( $l2c > 0 && !$inWork ) {
				$tab1[]= '<a href="main.cover.php?id='.$r1id.'" target="_blank" title="Наблюдательное производство" class="l1-a-c l1-at"></a>' ;
			}
			if ( $mayEnvForPayment ) {
				$tab1[]= '<a href="payment-details.php?id='.$r1id.'" class="l1-a-l l1-at" title="Конверт для оплаты"><span>О</span></a>' ;
			}
			if ( $mayOrders ) {
				$tab1[]= '<a href="order-new.php?id='.$r1id.'" target="_blank" class="l1-a-c l1-at" title="Поручение"><span>п</span></a>' ;
				$tab1[]= '<a href="order-2-dmtx.php?id='.$r1id.'" target="_blank" class="l1-a-c l1-at" title="Поручение новое"><span>N</span></a>' ;
				$tab1[]= '<a href="order-2-side-2.php?id='.$r1id.'" target="_blank" class="l1-a-c l1-at" title="Выдача"><span>S</span></a>' ;
				$tab1[]= '<a href="order-3.php?id='.$r1id.'" target="_blank" class="l1-a-c l1-at" title="Поручение"><span>3</span></a>' ;
				$tab1[]= '<a href="order-4-dmtx.php?id='.$r1id.'" target="_blank" class="l1-a-c l1-at" title="Поручение"><span>4</span></a>' ;
				$tab1[]= '<a href="order-5-dmtx.php?id='.$r1id.'" target="_blank" class="l1-a-c l1-at" title="Поручение"><span>5</span></a>' ;
			}
			$tab1[]= '</td><td class="f03' ;
			$tab1[]= ( $w == $cw && $y == $cy ? 0 : 1 );
			$l1gid = $row[ 'group_id' ];
			$l1gc = $l1gid != 0 && isset( $groupCountMap[ $l1gid ] ) ? $groupCountMap[ $l1gid ][ 'gc' ] : 0 ;
			$tab1[]= '"><div class="l3-s-' ;
			$tab1[]= ( $l2c > 0 ? ( $inWork ? 'r' : 'g' ) : 'm1' ).( $l1gc > 1 ? ' l1-gc' : '' );
			$tab1[]= "\" title=\"" ;
			$tab1[]= ( $l2c > 0 ? ( $inWork ? 'В работе' : 'Завершено' ) : 'Ожидает' ).( $l1gc > 1 ? ' ( есть связанные )' : '' );
			$tab1[]= '">' ;
			if ( $l1gc > 1 ) {
				$tab1[]= '<span onclick="showGroup( '.$l1gid.' , '.$r1idjs.' )">'.$l1gc.'</span>' ;
			}
			$tab1[]= '</div></td><td class="f04">' ;
			$tab1[]= $stateImg ;
			$tab1[]= '</td><td class="f05">' ;
			$tab1[]= $paymentStateImg ;
			$tab1[]= '</td><td class="f1"><a id="row' ;
			$tab1[]= $r1id ;
			$tab1[]= '">' ;
			$tab1[]= matincomingNumberFull( $r1id , null , $row[ 'exp_type' ] );
			$tab1[]= '</a></td><td class="f2">' ;
			$tab1[]= PrintField( "`t1`.`date`" , date( 'd-m-Y' , strtotime( $row[ 'date' ] ) ) );
			$tab1[]= '</td><td class="f3">' ;
			$tab1[]= PrintField( "`t2`.`name`", $row[ 'agency_name' ] );
			$tab1[]= ", " ;
			$tab1[]= PrintField( "`t3`.`name`", $row[ 'agent_name' ] );
			$tab1[]= ', ' ;
			if ( $UserID != 1 && $UserID != 133 && $UserID != 169 ) {
				$tab1[]= PrintField( "`t1`.`ex_data_3`", $row[ 'ex_data_3' ] );
			} else {
				$tab1[]= hlExData( PrintField( "`t1`.`ex_data_3`", $row[ 'ex_data_3' ] ) , $cardsL1Map[ $r1id ][ 'e-data' ] );
			}
			$tab1[]= '</td><td class="f4">' ;
			if ( $UserID != 1 && $UserID != 133 && $UserID != 169 ) {
				$tab1[]= PrintField( '`t1`.`ex_data_4`', $row[ 'ex_data_4' ] );
			} else {
				$tab1[]= hlExData( PrintField( "`t1`.`ex_data_4`", $row[ 'ex_data_4' ] ) , $cardsL1Map[ $r1id ][ 'e-data' ] );
			}
			$tab1[]= '</td><td class="f5">' ;
			$tab1[]= $ExpGenus ;
			$tab1[]= '</td><td class="f6">' ;
			$tab1[]= PrintField( "`t1`.`ex_data_6`", $row[ 'ex_data_6' ] );
			$tab1[]= '</td><td class="f7">' ;
			$fileList = $c1m[ 'docs' ];
			$tab1[]= implode( '' , $fileList );
			$tab1[]= PrintField( "`t1`.`ex_data_7`", $row[ 'ex_data_7' ] );
			$tab1[]= '</td><td class="f8">' ;
			$tab1[]= PrintField( "`t1`.`ex_data_8`", $row[ 'ex_data_8' ] );
			$tab1[]= '</td><td class="f9">' ;
			$tab1[]= PrintField( "`t1`.`ex_data_9`", $row[ 'ex_data_9' ] );
			$tab1[]= '</td></tr>' ;

			if ( $l2c > 0 ) {
			}

			if ( $rpc == $rpcVL ) {
				flush();
			}
		} unset( $c1m );

		fixTimerData( 'MAIN LOOP' );

		fixTimerData( 'MAIN LOOP IMPLODE' );
		echo implode( $tab1 );
		fixTimerData( 'MAIN LOOP IMPLODE' );

		echo '</table>' ;

		if ( $mayPrintAddressLabel ) {
			echo '<div id="letter_dlg" class="letter-dlg" style="display : none ;">
				<div class="letter-dlg-close-box"><div onclick="hideLetterDlg();" title="Закрыть" class="dlg-close-box-btn"></div></div>
				<div class="letter-dlg-cont">
					<table id="letter_dlg_tab" class="letter-dlg-tab">
					</table>
					<div class="letter-dlg-ex">
						Вес <input id="new-weight" type="text" class="weight-inp" onkeyup="changeWeight()"> заказное <input id="new-letter-type" type="checkbox" onchange="changeWeight()"> =&gt; <input id="new-price" type="text" class="price-inp">
					</div>
					<table id="letter_dlg_tab_2" class="letter-dlg-tab">
					</table>
				</div>
				<div class="letter-dlg-btn-panel">
					<button onclick="sendMessage();" class="letter-dlg-button">Уведомить о недостающих адресах</button>
					<select id="labelFormat" onchange="labelFormatChange();">
						<option value="29x90">29 x 90</option>
						<option value="38x90">38 x 90</option>
						<option value="62x100">62 x 100</option>
						<option value="62">62 -></option>
					</select>
				</div>
			</div>' ;
		}

		echo '<div id="addresses_fill_dlg" class="addresses-fill-dlg" style="display : none ;">
			<div class="addresses-fill-dlg-close-box"><div onclick="hideAddressesFillDlg();" title="Закрыть" class="dlg-close-box-btn"></div></div>
			<iframe id="addresses_fill_dlg_frame" frameborder="no" seamless="seamless" src="" width="100%" height="240px" class="iframe-std-1"></iframe>
		</div>
		
		<div id="stat-panel" class="stat-panel" style="display : none">
			<div class="stat-panel-cap">Не выполенные <div class="stat-panel-close-btn" onclick="hideStatPanel();" title="Закрыть"></div></div>
			<div class="stat-panel-container">
				<div><div class="stat-panel-legend no-stat"></div> / <div class="stat-panel-legend-2 no-stat"></div> - экспертоучастие</div>
				<div id="stat-panel.spec-area" class="spec-area">
				</div>
			</div>
		</div>

		<script type="text/javascript">
			var userRights = {
				l1add : '.( $lvl1cardADD ? 1 : 0 ).' ,
				l1edit : '.( $lvl1cardEDIT ? 1 : 0 ).' ,
				l1seeAll : '.( $lvl1cardSEEALL ? 1 : 0 ).' ,

				l2add : '.( $lvl2cardADD ? 1 : 0 ).' ,
				l2edit : '.( $lvl2cardEDIT ? 1 : 0 ).' ,
				l2seeAll : '.( $lvl2cardSEEALL ? 1 : 0 ).' ,
				l2delete : '.( $lvl2cardDELETE ? 1 : 0 ).' ,
				l2deleteAny : '.( $lvl2cardDELETEANY ? 1 : 0 ).' ,

				l3edit : '.( $expertizeEDIT ? 1 : 0 ).' ,
				l3seeAll : '.( $expertizeSEEALL ? 1 : 0 ).' ,
				l3seeUnordered : '.( $expertizeSEEUNORDERED ? 1 : 0 ).' ,
				l3order : '.( $expertizeORDER ? 1 : 0 ).' ,

				mayPrintAddressLabel : '.( $mayPrintAddressLabel ? 1 : 0 ).' ,
				mayEnvForPayment : '.( $mayEnvForPayment ? 1 : 0 ).' ,
				mayOrders : '.( $mayOrders ? 1 : 0 ).' ,
			};
			var visibleRowsIDList = "'.implode( ',' , $vrid ).'" ;
			var hiddenRowsIDList = "'.implode( ',' , $hrid ).'" ;
			var expandRowsIDList = "'.implode( ',' , $erid ).'" ;
			var UserOptions = {
				paymentsAddress : "'.getPaymentsAddr().'"
			};
		</script>' ;
		
		closeHtml();
	}
