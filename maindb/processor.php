<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserOrgIndex
	 * @var $UserRights
	 * @var $UserID
	 * @var TDB $portalDB
	 * @var $UserDepartment
	 * @var array $dbConfig
	 *
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 * @var $defRedirect
	 */
	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) != 1 ) {
		ErrorPageAndExit();
	}

	$Rights= ParseRights( strtoupper( $UserRights[ 0 ] ) );
	if ( array_key_exists( "LVL1CARD" , $Rights ) ) {
		$lvl1cardADD = in_array( "ADD" , $Rights[ "LVL1CARD" ] );
		$lvl1cardEDIT = in_array( "EDIT" , $Rights[ "LVL1CARD" ] );
	} else {
		$lvl1cardADD = $lvl1cardEDIT = false ;
	}

	$lvl2cardADD = $lvl2cardEDIT = $lvl2cardDELETE = $lvl2cardDELETEANY = false ;
	$lvl2card_accTimeCreate = $lvl2card_accTimeEdit = $lvl2card_accTimeView = false ;
	$lvl2card_priceCreate = $lvl2card_priceEdit = $lvl2card_priceView = false ;
	$lvl2card_ChangeDateMatExpSpecAfterOrder = false ;
	if ( array_key_exists( "LVL2CARD" , $Rights ) ) {
		$lvl2cardADD            = in_array( 'ADD'            , $Rights[ 'LVL2CARD' ] );
		$lvl2cardEDIT           = in_array( 'EDIT'           , $Rights[ 'LVL2CARD' ] );
		$lvl2cardDELETE         = in_array( 'DELETE'         , $Rights[ 'LVL2CARD' ] );
		$lvl2cardDELETEANY      = in_array( 'DELETE-ANY'     , $Rights[ 'LVL2CARD' ] );
		$lvl2card_accTimeCreate = in_array( 'ACCTIME-CREATE' , $Rights[ 'LVL2CARD' ] );
		$lvl2card_accTimeEdit   = in_array( 'ACCTIME-EDIT'   , $Rights[ 'LVL2CARD' ] );
		$lvl2card_accTimeView   = in_array( 'ACCTIME-VIEW'   , $Rights[ 'LVL2CARD' ] );
		$lvl2card_priceCreate   = in_array( 'PRICE-CREATE'   , $Rights[ 'LVL2CARD' ] );
		$lvl2card_priceEdit     = in_array( 'PRICE-EDIT'     , $Rights[ 'LVL2CARD' ] );
		$lvl2card_priceView     = in_array( 'PRICE-VIEW'     , $Rights[ "LVL2CARD" ] );
		$lvl2card_ChangeDateMatExpSpecAfterOrder = in_array( 'CHANGE-DATE-MAT-EXP-SPEC-AFTER-ORDER' , $Rights[ 'LVL2CARD' ] );
	}

	if ( array_key_exists( 'EXPERTIZE' , $Rights ) ) {
		$mayExpertizeEDIT                = in_array( 'EDIT' , $Rights[ 'EXPERTIZE' ] );
		$mayExpertizeCORRECT             = in_array( 'CORRECT_AFTER_CLOSE' , $Rights[ 'EXPERTIZE' ] );
		$expertizeORDER                  = in_array( 'ORDER' , $Rights[ 'EXPERTIZE' ] );
		$mayExpertizeCorrectPaymentsInfo = in_array( 'CORRECT_PAYMENTS_INFO' , $Rights[ 'EXPERTIZE' ] );
	} else {
		$mayExpertizeEDIT = $expertizeORDER = $mayExpertizeCORRECT = $mayExpertizeCorrectPaymentsInfo = false ;
	}

	if ( array_key_exists( "RECEIPTS" , $Rights ) ) {
		$receiptsADD = in_array( "ADD" , $Rights[ "RECEIPTS" ] );
		$receiptsEDIT = 0 ;
		if ( in_array( "EDIT-NC" , $Rights[ "RECEIPTS" ] ) ) {
			$receiptsEDIT+= 1 ;
		}
		if ( in_array( "EDIT-AC" , $Rights[ "RECEIPTS" ] ) ) {
			$receiptsEDIT+= 2 ;
		}
		if ( in_array( "EDIT-CC" , $Rights[ "RECEIPTS" ] ) ) {
			$receiptsEDIT+= 4 ;
		}
	} else {
		$receiptsADD = $receiptsEDIT = false ;
	}

	$maySEARCH = in_array( "SEARCH" , $Rights );
	$maySTATISTICS = in_array( "STATISTICS" , $Rights );

	$lvl1cardFFA = "{date:d,state:i,ex_data_{3,4,6,7,8,9}:S}" ;

	$Redir = $defRedirect ;
	if ( isset( $_REQUEST[ "lvl1cadd" ] ) && $lvl1cardADD ) {
		$v_type_of_agency = intval( $_REQUEST[ "i_from_type_of_agency" ] );
		$v_contacts = array();
		if (
			isset( $_REQUEST[ "i_contacts" ] ) && is_array( $_REQUEST[ "i_contacts" ] ) &&
			isset( $_REQUEST[ "i_contacts-type" ] ) && is_array( $_REQUEST[ "i_contacts-type" ] )
		) {
			$contactsType = &$_REQUEST[ "i_contacts-type" ];
			if ( isset( $_REQUEST[ "i_contacts-cb" ] ) && is_array( $_REQUEST[ "i_contacts-cb" ] ) ) {
				$contactsChecked = &$_REQUEST[ "i_contacts-cb" ];
			} else {
				$contactsChecked = array();
			}
			foreach( $_REQUEST[ "i_contacts" ] as $cck => $ccv ) {
				$v_contacts[]= array(
					"value" => $ccv ,
					"type" => ( isset( $contactsType[ $cck ] ) ? $contactsType[ $cck ] : 1 ) ,
					"checked" => ( isset( $contactsChecked[ $cck ] ) ? $contactsChecked[ $cck ] == "checked" : false )
				);
			}
		}
		$saad = storeAgentData( $portalDB , $v_type_of_agency , $_REQUEST[ "i_from_agency" ] , $_REQUEST[ "i_from_agent" ] , $v_contacts );

		if ( isset( $_REQUEST[ "assign" ] ) ) {
			$v_assign = getCharID( $_REQUEST[ "assign" ] , DOCTYPE_MATINCOMING );
		} else {
			$v_assign = false ;
		}

		if ( $v_assign !== false ) {
			$ocr = $portalDB->row( "select * from `matincoming` as `t1` where ( `t1`.`id` = ? )" , "s" , $v_assign );
			if ( $ocr === false ) {
				$v_group_id = 0 ;
			} else {
				if ( $ocr[ "group_id" ] == 0 ) {
					$portalDB->insertRow( "matincoming-groups" );
					$v_group_id = $portalDB->lastInsertID();
					$portalDB->updateRow( "matincoming" , array( "group_id" => $v_group_id , "id" => $ocr[ "id" ] ) );
				} else {
					$v_group_id = $ocr[ "group_id" ];
				}
			}
		} else {
			$v_group_id = 0 ;
		}


		$v_ = array_merge(
			readFormP0( $lvl1cardFFA ) ,
			array_rekey( $saad , '/(agen(?:t|cy)).id/' , 'from_${1}' , "agen{t,cy}.id" )
		);
		$v_[ "id" ] = VERSION_CHAR_ID.".".$UserOrgIndex.".".DOCTYPE_MATINCOMING.".20" ;
		$v_[ "exp_type" ] = intval( $_REQUEST[ "i_case_category" ] );
		$v_[ "group_id" ] = $v_group_id ;

		//print_r_html( $v_ );
		//$portalDB->dbgMode = true ;
		$portalDB->insertRow( "matincoming" , $v_ );

		$niid = $portalDB->lastInsertID();
		$niid = $portalDB->row( "select * from `matincoming` where `__id` = ?" , "i" , $niid );
		$niid = $niid[ "id" ];
		//var_dump_html( $niid );


		$v_en_date = isset( $_REQUEST[ "en_date" ] ) ? $_REQUEST[ "en_date" ] : array();
		$v_en_descr = isset( $_REQUEST[ "en_descr" ] ) ? $_REQUEST[ "en_descr" ] : array();
		storeEvidenceData( $niid , $v_en_date , $v_en_descr );

		if ( isset( $_REQUEST[ "i_marks" ] ) ) {
			$v_marks = $_REQUEST[ "i_marks" ];
		} else {
			$v_marks = array();
		}
		Marks\updateMarks( $niid , "matincoming" , $v_marks );

		if ( isset( $_REQUEST[ "o_ADD_REP" ] ) ) {
			$Redir = array();
			if ( isset( $_REQUEST[ "o_ADD_SAMEDATE" ] ) ) {
				$samedate = strtotime( PrepDate( $_REQUEST[ "i_date" ] ) );
				if ( $samedate == "" ) {
					$samedate = time();
				}
				$Redir[ "o_ADD_SAMEDATE" ] = $samedate ;
			}
			if ( isset( $_REQUEST[ "o_ADD_SAMEALL" ] ) ) {
				$Redir[ "o_ADD_SAMEALL" ] = array_merge(
					array_intersect_key(
						$_REQUEST ,
						array_flip(
							strexp( "i_{case_category,from_{agency,agent},ex_data_{3,4,6,7,8,9},state,marks}" )
						)
					) ,
					array(
						"i_from_type_of_agency" => $v_type_of_agency ,
						"i_date" => $samedate
					) ,
					array_intersect_key(
						$saad ,
						array_flip( strexp( "agen{cy,t}.id" ) ) )
				);

				foreach( $Redir[ "o_ADD_SAMEALL" ] as &$tmpstr ) {
					if ( is_string( $tmpstr ) ) {
						$tmpstr = iconv( "cp1251" , "utf8" , $tmpstr );
					}
				} unset( $tmpstr );
			}
			$Redir = "level1card.php?add&o_ADD_REP=".urlencode( base64_encode( json_encode( $Redir , JSON_UNESCAPED_UNICODE ) ) );
		}
	} else
	if ( isset( $_REQUEST[ "lvl1cedit" ] ) && $lvl1cardEDIT ) {
		$v_type_of_agency = intval( $_REQUEST[ "i_from_type_of_agency" ] );
		$v_contacts = array();
		if (
			isset( $_REQUEST[ "i_contacts" ] ) && is_array( $_REQUEST[ "i_contacts" ] ) &&
			isset( $_REQUEST[ "i_contacts-type" ] ) && is_array( $_REQUEST[ "i_contacts-type" ] )
		) {
			$contactsType = &$_REQUEST[ "i_contacts-type" ];
			if ( isset( $_REQUEST[ "i_contacts-cb" ] ) && is_array( $_REQUEST[ "i_contacts-cb" ] ) ) {
				$contactsChecked = &$_REQUEST[ "i_contacts-cb" ];
			} else {
				$contactsChecked = array();
			}
			foreach( $_REQUEST[ "i_contacts" ] as $cck => $ccv ) {
				$v_contacts[]= array(
					"value" => $ccv ,
					"type" => ( isset( $contactsType[ $cck ] ) ? $contactsType[ $cck ] : 1 ) ,
					"checked" => ( isset( $contactsChecked[ $cck ] ) ? $contactsChecked[ $cck ] == "checked" : false )
				);
			}
		}

		$saad = storeAgentData( $portalDB , $v_type_of_agency , $_REQUEST[ "i_from_agency" ] , $_REQUEST[ "i_from_agent" ] , $v_contacts );

		$v_id = getCharID( $_REQUEST[ "lvl1cedit" ] , DOCTYPE_MATINCOMING );

		if ( $v_id === false ) {
			MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
			echo "<br><br><br><br><br>" ;
			MessageForm();
			closeHtml();
			exit ;
		}

		$v_ = array_merge(
			readFormP0( $lvl1cardFFA ) ,
			array_rekey( $saad , '/(agen(?:t|cy)).id/' , 'from_${1}' , "agen{t,cy}.id" )
		);
		$v_[ "id" ] = $v_id ;
		$v_[ "exp_type" ] = intval( $_REQUEST[ "i_case_category" ] );

		//print_r_html( $v_ );
		//$portalDB->dbgMode = true ;
		$portalDB->updateRow( "matincoming" , $v_ );

		$v_en_date = isset( $_REQUEST[ "en_date" ] ) ? $_REQUEST[ "en_date" ] : array();
		$v_en_descr = isset( $_REQUEST[ "en_descr" ] ) ? $_REQUEST[ "en_descr" ] : array();
		storeEvidenceData( $v_id , $v_en_date , $v_en_descr );

		if ( isset( $_REQUEST[ "i_marks" ] ) ) {
			$v_marks = $_REQUEST[ "i_marks" ];
		} else {
			$v_marks = array();
		}
		Marks\updateMarks( $v_id , "matincoming" , $v_marks );

		if ( isset( $_REQUEST[ "o_EDIT_NEXT" ] ) ) {
			$row = $portalDB->row( "select `id` from `matincoming` where `id` > ? limit 1" , "s" , $v_id );
			if ( !( $row === false ) ) {
				$Redir = "level1card.php?edit=".$row[ "id" ]."&o_EDIT_NEXT" ;
			}
		}
	} else
	if ( isset( $_REQUEST[ 'lvl2cadd' ] ) && $lvl2cardADD ) {
		$v_id = getCharID( $_REQUEST[ 'i_mat_id' ] , DOCTYPE_MATINCOMING );
		$v_accounting_time = null ;
		if ( $lvl2card_accTimeCreate && isset( $_REQUEST[ 'i_accounting_time' ] ) ) {
			$v_accounting_time = intval( $_REQUEST[ 'i_accounting_time' ] , 10 );
		}
		$workerID = intval( $_REQUEST[ "i_worker" ] , 10 );
		$workerInfo = $portalDB->simpleRow( 'workers-no-spec' , $workerID );

		if ( isset( $_REQUEST[ 'i-ex-num' ] ) ) {
			$idList = getCharIDList( $_REQUEST[ 'i-ex-num' ] );
			if ( $idList === false ) {
				$idList = array();
			}
		} else {
			$idList = array();
		}

		array_unshift( $idList , $v_id );

		/*print_r_html( $_REQUEST );

		print_r_html( $idList , 1 );

		exit();*/

		foreach( $idList as $cv_id ) {
			$lvl1Row = $portalDB->simpleRow( 'matincoming' , $cv_id );
			if ( count( $idList ) > 1 ) {
				$_REQUEST[ "i_materials" ] = $lvl1Row[ 'ex_data_4' ];
			}

			if ( isset( $_REQUEST[ 'i_date_from_lvl1' ] ) ) {
				$_REQUEST[ "i_date" ] = date( 'd-m-Y' , strtotime( $lvl1Row[ 'date' ] ) );
				$_REQUEST[ "i_ex_data_6" ] = $_REQUEST[ "i_date" ].', ' ;
			}

			error_log_ml( print_r( $_REQUEST , 1 ) );

			$portalDB->noResult(
				"insert into `matincominglvl2` ( `mat_id` , `dep_id` , `date` , `materials` , `ex_data_6` , `ex_data_7` , `ex_data_8` , `ex_data_9` , `ex_data_10` , `ex_data_12` , `kat_slognost` , `accounting_time` ) values ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )" ,
				"sissssssssii" ,
				$cv_id , $workerInfo[ 'dep' ] , PrepDate( $_REQUEST[ "i_date" ] ) , $_REQUEST[ "i_materials" ] , $_REQUEST[ "i_ex_data_6" ] , $_REQUEST[ "i_ex_data_7" ] ,
				$_REQUEST[ "i_ex_data_8" ] , $_REQUEST[ "i_ex_data_9" ] , $_REQUEST[ "i_ex_data_10" ] , $_REQUEST[ "i_ex_data_12" ] , intval( $_REQUEST[ "kat" ] ) , $v_accounting_time
			);

			$v_id2 = $portalDB->lastInsertID();

			$v_use_in_stat = ( isset( $_REQUEST[ "i_no_use_in_stat" ] ) && ( $_REQUEST[ "i_no_use_in_stat" ] == 1 ) ? 0 : 1 );
			$v_price = null ;
			if ( $lvl2card_priceCreate && isset( $_REQUEST[ 'i_price' ] ) ) {
				$v_price = $_REQUEST[ 'i_price' ];
				$v_price = preg_replace( '/^\s*(\d+)[,-](\d+)\s*$/' , '${1}.${2}' , $v_price );
			}

			$portalDB->noResult(
				"insert into `expertize` ( `ext_id` , `exp_id` , `spec_id` , `use_in_stat` , `reason_1` , `reason_2` , `state` , `order_date` , `price` ) values ( ? , ? , ? , ? , 0 , 0 , 0 , ? , ? )" ,
				'iiiisd' ,
				$v_id2 , $workerID , intval( $_REQUEST[ "i_spec" ] ) , $v_use_in_stat , ( $dbConfig[ "matincoming.autoOrder" ] == 1 ? date( 'Y-m-d' , time() ) : null ) , $v_price
			);

			$row = $portalDB->row( "select month( `date` ) as `m` , year( `date` ) as `y` from `matincoming` where `id` = ?" , "s" , $cv_id );
		}
		
		$Redir.="?m=".$row[ "m" ]."&y=".$row[ "y" ]."&showRow=".$v_id."#row".$v_id ;
	} else
	if ( isset( $_REQUEST[ "lvl2cedit" ] ) && $lvl2cardEDIT ) {
		$v_id2 = intval( $_REQUEST[ 'lvl2cedit' ] );
		$oldRow = $portalDB->simpleRow( 'matincominglvl2' , $v_id2 );
		$oldLvl3Row = $portalDB->simpleRow( 'expertize' , array( 'ext_id' => $v_id2 ) );

		if ( $oldRow !== false && $oldLvl3Row !== false ) {
			$isOrdered = !is_null( $oldLvl3Row[ 'order_date' ] );
			$v_ = readFormP0( '{kat_slognost:i,ex_data_{6,7,8,9,10,12}:S}' );
			$v_exp = array( 'ext_id' => $v_id2 );


			if ( !$isOrdered || $lvl2card_ChangeDateMatExpSpecAfterOrder ) {
				$v_p1 = readFormP0( '{date:d,materials:S}' );
				$workerID = intval( $_REQUEST[ 'i_worker' ] , 10 );
				$workerInfo = $portalDB->simpleRow( 'workers-no-spec' , $workerID );
				$specID = intval( $_REQUEST[ 'i_spec' ] , 10 );
				$specInfo = $portalDB->simpleRow( 'specialities' , $specID );
				if ( $workerInfo !== false && $specInfo !== false ) {
					$v_p1[ 'dep_id' ] = $workerInfo[ 'dep' ];
					$v_ = array_merge( $v_ , $v_p1 );

					$v_exp_p1 = array(
						'exp_id' => $workerID ,
						'spec_id' => $specID ,
						'use_in_stat' => ( isset( $_REQUEST[ "i_no_use_in_stat" ] ) && ( $_REQUEST[ "i_no_use_in_stat" ] == 1 ) ? 0 : 1 )
					);
					$v_exp = array_merge( $v_exp , $v_exp_p1 );
				} else {
					$v_ = false ;
				}
			}

			if ( $v_ !== false ) {
				$v_[ 'id' ] = $v_id2 ;

				if ( $lvl2card_accTimeEdit && isset( $_REQUEST[ 'i_accounting_time' ] ) ) {
					$v_at = readFormP0( 'accounting_time:i' );
					$v_ = array_merge( $v_ , $v_at );
				}

				$portalDB->updateRow( 'matincominglvl2' , $v_ );

				if ( $lvl2card_priceEdit && isset( $_REQUEST[ 'i_price' ] ) ) {
					$v_price = readFormP0( 'price:p' );
					$v_exp = array_merge( $v_exp , $v_price );
				}

				if ( count( $v_exp ) > 1 ) {
					$portalDB->updateRow( 'expertize' , $v_exp , 'ext_id' );
				}

				$row = $portalDB->row( "select `t1`.`id` , month( `t1`.`date` ) as `m` , year( `t1`.`date` ) as `y` from `matincoming` as `t1` , `matincominglvl2` as `t2` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = ? )" , 'i' , $v_id2 );
				$Redir.="?m=".$row[ "m" ]."&y=".$row[ "y" ]."&showRow=".$row[ "id" ]."#row".$row[ "id" ];
			}
		}
	} else
	if ( isset( $_REQUEST[ "lvl2cdelete" ] ) && ( $lvl2cardDELETE || $lvl2cardDELETEANY ) ) {
		$v_id2 = intval( $_REQUEST[ "lvl2cdelete" ] );
		$row = $portalDB->row( "select `mat_id` , `dep_id` from `matincominglvl2` where `id` = ?" , "i" , $v_id2 );
		if ( $row !== false ) {
			if ( $lvl2cardDELETEANY || ( $row[ "dep_id" ] == $UserDepartment ) ) {
				// TODO: check payments , equipment , generated docs , subpoenas ...
				//$row2 = $portalDB->row( "select count(*) as `cnt` from ``" );
				$portalDB->noResult( "delete from `expertize` where `ext_id` = ?" , "i" , $v_id2 );
				$portalDB->noResult( "delete from `matincominglvl2` where `id` = ?" , "i" , $v_id2 );
				echo "" ;
			} else {
				echo "NO RIGHTS" ;
			}
		} else {
			echo "NOT FOUND" ;
		}
		$Redir = "[NO REDIRECT]" ;
	} else
	if ( isset( $_REQUEST[ 'expertizeedit' ] ) && $mayExpertizeEDIT ) {
		$expertizeID = intval( $_REQUEST[ 'expertizeedit' ] );
		$oldLvlC1C2C3Row = $portalDB->row( "select `t1`.* , `t2`.`mat_id` , `t2`.`dep_id` , `t3`.`exp_type` , `t3`.`date` as `lvl1c-date` , `t3`.`group_id` from `expertize` as `t1` , `matincominglvl2` as `t2` , `matincoming` as `t3` where ( ( `t1`.`id` = ? ) and ( `t1`.`ext_id` = `t2`.`id` ) and ( `t2`.`mat_id` = `t3`.`id` ) )" , 'i' , $expertizeID );

		if ( $oldLvlC1C2C3Row === false ) {
			ErrorPageAndExit();
		}

		$cr = $portalDB->row( "select count( * ) as `count` from `payments` where ( `expertize_id` = ? ) and ( `type` <=> 0 );" , 'i' , $expertizeID );

		$lvl2c_mat_id   = $oldLvlC1C2C3Row[ 'mat_id' ];
		$lvl2c_dep_id   = $oldLvlC1C2C3Row[ 'dep_id' ];
		$lvl1c_exp_type = $oldLvlC1C2C3Row[ 'exp_type' ];
		$lvl1c_date     = strtotime( $oldLvlC1C2C3Row[ 'lvl1c-date' ] ); // local ??

		$rowMarkNoPay = $portalDB->simpleRow( 'marks-objects' , array(
			'ext_type' => 'matincoming' ,
			'ext_id'   => $lvl2c_mat_id ,
			'mark_id'  => $dbConfig[ CFG_MATINCOMING_MARK_NOPAY ]
		) );

		$os_finished = $oldLvlC1C2C3Row[ 'state' ] == 1 ;
		$os_woexecution = $oldLvlC1C2C3Row[ 'state' ] == 2 ;
		$os_sndz = $oldLvlC1C2C3Row[ 'sndz' ] == 1 ;
		$os_closed = $os_finished || $os_woexecution ;

		if ( $os_closed && !$mayExpertizeCORRECT ) {
		} else {
			$lvl3cardFFAp1 = '{state:i}' ;
			$v_ = readFormP0( $lvl3cardFFAp1 );
			$v_[ 'id' ] = $expertizeID ;

			if ( !( is_numeric( $v_[ 'state' ] ) && in_array( $v_[ 'state' ] , array( 0 , 1 , 2 , 10 ) ) ) ) {
				ErrorPageAndExit();
			}

			$ns_finished = $v_[ 'state' ] == 1 || $v_[ 'state' ] == 10 ;
			$ns_woexecution = $v_[ 'state' ] == 2 ;
			$ns_sndz = $v_[ 'state' ] == 10 ;
			$ns_closed = $ns_finished || $ns_woexecution ;
			if ( $ns_sndz ) {
				$v_[ 'state' ] = 1 ;
				$v_[ 'sndz' ] = 1 ;
			} else {
				$v_[ 'sndz' ] = 0 ;
			}

			$lvl3cardFFAp21 = '{conclusion{:i,_1:i,_2{:i,_1:i,_2:i,_3{:i,_comment:S}},_3:i}}' ;

			if ( $ns_closed ) {
				$lvl3cardFFAp20 = '{fin_date:d,reason_1{:B,_comment:S}}' ;
				$v_p20 = readFormP0( $lvl3cardFFAp20 );
				$expertize_fin_date_date = strtotime( $v_p20[ 'fin_date' ] );

				$wDays = ceil( ( $expertize_fin_date_date - $lvl1c_date ) / 86400 ) + 1 ;
				if ( $wDays > 30 ) {

				}
				if ( $ns_finished ) {
					if ( $ns_sndz ) {
						$v_ = array_merge( $v_ , $v_p20 );
					} else {
						$v_p21 = readFormP0( $lvl3cardFFAp21 );
						$v_ = array_merge( $v_ , $v_p20 , $v_p21 );
					}

					if ( ( intval( $cr[ 'count' ] ) < 1 ) && !( isCCGZ( $lvl1c_exp_type ) || ( $rowMarkNoPay !== false ) ) ) {
						$portalDB->noResult( "insert into `payments` ( `expertize_id` , `state` , `create_date` , `check_date` , `type` ) values ( ? , 0 , ? , 0 , 0 )" , 'ii' , $expertizeID , time() );
					}
				} else {
					$v_p21 = array_rekey( array_fill_keys( strexp( $lvl3cardFFAp21 ) , null ) , '/:\w$/' , '' );
					$lvl3cardFFAp22 = '{reason_2{:i,_comment:S}}' ;
					$v_p22 = readFormP0( $lvl3cardFFAp22 );
					$v_ = array_merge( $v_ , $v_p20 , $v_p22 );
				}
			} else {
			}
			$portalDB->updateRow( 'expertize' , $v_ );
		}

		if ( isset( $_REQUEST[ 'i_comment' ] ) ) {
			storeExpertizeComment( $expertizeID , $_REQUEST[ 'i_comment' ] );
		}

		if ( ( ( $dbConfig[ CFG_EXPERTIZE_PRICE_EDITABLE ] == 1 ) || $mayExpertizeCorrectPaymentsInfo ) && !( isCCGZ( $lvl1c_exp_type ) || ( $rowMarkNoPay !== false ) ) ) {
			$mayChange = true ;
			$mayPriceChange = false ;
			if ( $dbConfig[ CFG_EXPERTIZE_PRICE_EDITABLE ] == 1 ) {
				$lvl3cardFFApp = '{price:p,pay_{date,details}:S}' ;
				$mayPriceChange = true ;
			} else
			if ( $mayExpertizeCorrectPaymentsInfo ) {
				$lvl3cardFFApp = '{pay_{date,details}:S}' ;
			} else {
				$mayChange = false ;
			}

			if ( $mayChange ) {
				$v_price = readFormP0( $lvl3cardFFApp );
				$v_price[ 'id' ] = $expertizeID ;
				$v_price[ 'application_for_issuance' ] = isset( $_REQUEST[ 'i_afi' ] ) && $_REQUEST[ 'i_afi' ] == 'yes' ? 1 : 0 ;
				$portalDB->updateRow( 'expertize' , $v_price );

				if ( $mayPriceChange && intval( $cr[ 'count' ] ) >= 1 ) {
					if ( $oldLvlC1C2C3Row[ 'price' ] != $v_price[ 'price' ] ) {
						$portalDB->noResult( "update `payments` set `state`= 0 , `create_date` = ? , `check_date` = 0 where ( `expertize_id` = ? ) and ( `type` <=> 0 );" , 'ii' , time() , $expertizeID );
					}
				}
			}
		}

		$Redir.= '?m='.date( 'm' , $lvl1c_date )."&y=".date( 'Y' , $lvl1c_date )."&showRow=".$lvl2c_mat_id."#row".$lvl2c_mat_id ;
	} else
	if ( isset( $_REQUEST[ "expertizeorder" ] ) && $expertizeORDER ) {
		$expertizeID = intval( $_REQUEST[ "expertizeorder" ] );
		$exp = $portalDB->simpleRow( 'expertize' , $expertizeID );
		if ( $exp ) {
			$portalDB->noResult( "update `expertize` set `order_date` = ? where `id` = ?" , 'si' , PrepDate( date( 'd-m-Y' , time() ) ) , $expertizeID );
			if ( isset( $_REQUEST[ 'acctime' ] ) ) {
				$portalDB->noResult( "update `matincominglvl2` set `accounting_time` = ? where `id` = ?" , "ii" , intval( $_REQUEST[ 'acctime' ] , 10 ) , $exp[ 'ext_id' ] );
			}
		}
		$Redir = "main.php?unordered" ;
	}

	if ( $Redir != "[NO REDIRECT]" ) {
		Redirect( $Redir );
	}
