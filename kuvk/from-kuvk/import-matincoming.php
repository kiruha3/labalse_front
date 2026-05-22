<?php
	//
	require_once( '../../core.php' );
	/**
	 * @var $portalDB
	 * @var $dbConfig
	 */
	require_once( '../../maindb/lconfig.php'  );
	require_once( '../../cores/core.maindb.php'  );

	require_once( '../core.export.const.php'  );

	$dataOrig = file_get_contents( 'php://input' );
	$data = json_decode( $dataOrig , true );

	//$res = array( 'success' => true , 'orig-data' => $data );
	$res = array( 'success' => true );

	function getDateInt( $v ) {
		return intval( substr( $v.'' , 0 , -3 ) , 10 );
	}

	if ( $data !== false && isset( $data[ 'type' ] ) && isset( $data[ 'kuvk-guid' ] ) ) {
		$iod =  $data[ 'data' ];
		$iot = $data[ 'type' ];
		$guid = $data[ 'kuvk-guid' ];
		$LID = false ;
		switch ( $iot ) {
			case 'agent' :
				$portalDB->insertRow( 'agent' , array(
					'ext_id' => $iod[ 'ay' ] ,
					'name' =>  rcvti( $iod[ 'name' ] ),
				) );
				break ;

			case 'agency' :
				$portalDB->insertRow( 'agency' , array(
					'ext_id' => $iod[ 'toa' ] ,
					'name' =>  rcvti( $iod[ 'name' ] ),
				) );
				break ;

			case 'matincoming' :
				$date = getDateInt( $iod[ 'date' ] );
				$LID = matincomingID( $iod[ 'num' ] , date( 'Y' , $date ) );
				$ed7 = array();
				$ed7[]= rcvti( $iod[ 'ex_data_7' ] );
				$ed8 = array();
				$ed8[]= rcvti( $iod[ 'ex_data_8' ] );
				$portalDB->insertRow( 'matincoming' , array(
					'id' => $LID ,
					'date' =>  date( 'Y-m-d' , $date ),
					'from_agency' => $iod[ 'from_agency' ] ,
					'from_agent' => $iod[ 'from_agent' ] ,
					'ex_data_3' => $iod[ 'ex_data_3' ] ? date( 'd-m-Y' , getDateInt( $iod[ 'ex_data_3' ] ) ) : '' , //rcvti( $iod[ 'ex_data_3' ] ) ,
					'ex_data_4' => $iod[ 'ex_data_4' ] ? rcvti( $iod[ 'ex_data_4' ] ) : '' ,
					// 'ex_data_7' => ... ,
					// 'ex_data_8' => ... ,
					'ex_data_9' => rcvti( $iod[ 'ex_data_9' ] ) ,
					'exp_type' => $iod[ 'exp_type' ] == -1 ? 0 : $iod[ 'exp_type' ] ,

					'state' => $iod[ 'state' ] ,
					
					'group_id' => $iod[ 'group_id' ] ,
				) );
				
				$markNoPayID = $dbConfig[ "matincoming.markNoPay" ];
				$markImportantID = $dbConfig[ "matincoming.Important" ];
				if ( $iod[ 'sourceFin' ] == 'mark-GZ' && in_array( $iod[ 'exp_type' ] , array( 2 , 3 , 4 ) ) ) {
					$portalDB->insertRow( 'marks-objects' , array(
						'mark_id' => $markNoPayID ,
						'ext_type' => 'matincoming' ,
						'ext_id' => $LID ,
						'date' => time()
					) );
				}
				
				if ( $iod[ 'control' ] ) {
					$portalDB->insertRow( 'marks-objects' , array(
						'mark_id' => $markImportantID ,
						'ext_type' => 'matincoming' ,
						'ext_id' => $LID ,
						'date' => time()
					) );
				}
				
				foreach ( $iod[ 'evidence' ] as $cev ) {
					$portalDB->insertRow( 'evidence' , array(
						'ext_id' => $LID ,
						'inc_date' =>  getDateInt( $cev[ 'inc_date' ] ) ,
						'descr' => rcvti( $cev[ 'descr' ] )
					) );
				}
				foreach ( $iod[ 'lvl23' ] as $cc23 ) {
					$portalDB->insertRow( 'matincominglvl2' , array(
						'mat_id' => $LID ,
						'date' =>  date( 'Y-m-d' , $date ),
						'kat_slognost' => $cc23[ 'kat_slognost' ] ,
					) );
					$c2ID = $portalDB->lastInsertID();
					//$c3state = ( $cc23[ 'state' ] ? 1 : 0 );
					$c3state = $cc23[ 'state' ];
					$portalDB->insertRow( 'expertize' , array(
						'ext_id' => $c2ID ,
						'exp_id' =>  $cc23[ 'exp_id' ] ,
						'spec_id' => $cc23[ 'spec_id' ] ,
						'order_date' => date( 'Y-m-d' , $date ) ,
						'state' => $c3state ,
						'fin_date' => $c3state > 0 ? date( 'Y-m-d' , getDateInt( $cc23[ 'fin_date' ] ) ) : null ,
						'reason_1' => $cc23[ 'reason_1' ] ,
						'reason_1_comment' => rcvti( $cc23[ 'reason_1_comment' ] ) ,
						'reason_2' => $cc23[ 'reason_2' ] ,
						'reason_2_comment' => rcvti( $cc23[ 'reason_2_comment' ] ) ,
						'conclusion' => $cc23[ 'conclusion' ] ,
						'conclusion_1' => $cc23[ 'conclusion_1' ] ,
						'conclusion_2' => $cc23[ 'conclusion_2' ] ,
						'conclusion_2_1' => $cc23[ 'conclusion_2_1' ] ,
						'conclusion_2_2' => $cc23[ 'conclusion_2_2' ] ,
						'conclusion_3' => $cc23[ 'conclusion_3' ] ,
						'sndz' => $cc23[ 'sndz' ] ? 1 : 0 ,
						'pay_details' => rcvti( $cc23[ 'pay_details' ] ) ,
						'price' => str_replace( ',' , '.' , $cc23[ 'price' ] )
					) );
					
					if ( $c3state > 0 ) {
						if ( $c3state == 1 ) {
							$ed8s = 'Заключение' ;
						} else
						if ( $c3state == 2 ) {
							$ed8s = 'Без/пр' ;
						} else {
							$ed8s = 'ошибка' ;
						}
						
						if ( $cc23[ 'sndz' ] ) {
							$ed8s.= ' [ СНДЗ ]';
						}
						
						$ed8s.= '  '.date( 'd-m-Y' , getDateInt( $cc23[ 'fin_date' ] ) );
						$ed8[]= $ed8s ;
					}

					$c3ID = $portalDB->lastInsertID();

					$portalDB->insertRow( 'kuvk-links' , array(
						'ext_type'  => KUVK_LINK_MATINCOMING_C23 ,
						'ext_id'    => $c3ID ,
						'kuvk-guid' => $cc23[ 'kuvk-guid' ]
					) );

					$eul = $cc23[ 'eqUsage' ];
					foreach( $eul as $eu ) {
						$portalDB->insertRow( 'exp-equipment-usage' , array(
							'ext_id' => $c3ID ,
							'eq_id'  => $eu[ 'eq_id' ] ,
							'start'  => getDateInt( $eu[ 'start' ] ),
							'finish' => getDateInt( $eu[ 'finish' ] ) ,
							'comment'=> ''
						) );
					}

					if ( isset( $cc23[ 'stopExp' ] ) ) {
						$sel = $cc23[ 'stopExp' ];
						foreach( $sel as $sei ) {
							$ed7s = rcvti( $sei[ 'type' ] ).' ( '.
									( $sei[ 'date_events' ] ? 'начало '.date( 'd-m-Y' , getDateInt( $sei[ 'date_events' ] ) ) : '' ).
									( $sei[ 'control_date' ] ? ', контроль '.date( 'd-m-Y' , getDateInt( $sei[ 'control_date' ] ) ) : '' ).
									( $sei[ 'date_finish' ] ? ', факт '.date( 'd-m-Y' , getDateInt( $sei[ 'date_finish' ] ) ) : '' ).') '.
									( isset( $sei[ 'inform_event' ] ) ? rcvti( $sei[ 'inform_event' ] ) : '...' ).' , '.
									( isset( $sei[ 'senddate' ] ) ? rcvti( $sei[ 'senddate' ] ) : '...' ).' , '.
									( isset( $sei[ 'addr' ] ) ? rcvti( $sei[ 'addr' ] ) : '' );
							$ed7[]= $ed7s ;
						}
					}
				}
				
				$portalDB->updateRow( 'matincoming' , array(
					'id' => $LID ,
					'ex_data_7' => trim( implode( "\r\n" , $ed7 ) ),
					'ex_data_8' => trim( implode( "\r\n" , $ed8 ) )
				) );
				break ;

			case 'workers-1' :
				if ( $guid == '849e81b9-f563-4908-96b9-1065901c42d3' ) {
					error_log( bin2hex( $dataOrig ) );
				}
				$wrkName = trim( rcvti( $iod[ 'name' ] ) );
				$n = preg_match( '/^f=\w+(?:\{\w*\|\w*\|\w*\|\w*\|\w*\|\w*\})?;\s*i=\w+(?:\{\w*\|\w*\|\w*\|\w*\|\w*\|\w*\})?;\s*o=\w+(?:\{\w*\|\w*\|\w*\|\w*\|\w*\|\w*\})?/i' , $wrkName );
				if ( $n !== 1 ) {
					$n = preg_replace( '/\s+/' , ' ' , $wrkName );
					$n = explode( ' ' , $n );
					$wrkName = array();
					if ( count( $n ) > 0 ) {
						$wrkName[]= 'f='.$n[ 0 ];
					}
					if ( count( $n ) > 1 ) {
						$wrkName[]= 'i='.$n[ 1 ];
					}
					if ( count( $n ) > 2 ) {
						$wrkName[]= 'o='.$n[ 2 ];
					}
					$wrkName = implode( ';' , $wrkName );
				}

				$prevWrk = $portalDB->simpleRow( 'workers-no-spec' , array( 'name' => $wrkName ) );
				if ( $prevWrk !== false ) {
					$wrkFirstID = $prevWrk[ 'id' ];
				} else {
					$wrkFirstID = false ;
				}
				$portalDB->insertRow( 'workers-no-spec' , array(
					'name' =>  $wrkName ,
					'actual' => 0 ,
				) );
				$iot = 'workers' ;
				$LID = $portalDB->lastInsertID();
				if ( $wrkFirstID === false ) {
					$wrkFirstID = $LID ;
				}
				$portalDB->updateRow( 'workers-no-spec' , array(
					'id' => $LID ,
					'first_id' => $wrkFirstID ,
				) );

				if ( isset( $iod[ 'login' ] ) && trim( $iod[ 'login' ] ) != '' ) {
					$portalDB->insertRow( 'accounts' , array(
						'worker_id' =>  $LID ,
						'login' =>  rcvti( $iod[ 'login' ] ) ,
						'any_ip' => 1 ,
						'theme' => 1 ,
//						'alt-login' => '' ,
//						'alt-pass' => '' ,
						'guid' => '' ,
						'mac_addr' => ''
					) );
					
					$accID = $portalDB->lastInsertID();
					$portalDB->insertRow( 'options' , array(
						'op_name' =>  'kuvk.pass' ,
						'op_value' =>  rcvti( $iod[ 'pass' ] ) ,
						'user_id' => $accID
					) );
				}
				
				break ;

			case 'staffing' :
				$stID = 'p:'.$iod[ 'post' ].'@d:'.$iod[ 'dep' ];
				$wIDL = array();
				$ccd = time();
				foreach( $iod[ 'workers' ] as $wsd ) {
					$owID = $wsd[ 'wid' ];
					$owr = $portalDB->simpleRow( 'workers-no-spec' , $owID );
					$wrkFirstID = $owr[ 'first_id' ];
					//error_log( 'DBG staff: ccd: '.$ccd.'  ,  from : '.getDateInt( $wsd[ 'dateFrom' ] ).'  ,  to : '.getDateInt( $wsd[ 'dateTo' ] ) );
					error_log( 'DBG staff : '.json_encode( $wsd ) );
					$portalDB->insertRow( 'workers-no-spec' , array(
						'name' => $owr[ 'name' ] ,
						'first_id' => $wrkFirstID ,
						'post_1_id' => $iod[ 'post' ] ,
						'post_2_id' => $iod[ 'post' ] ,
						'dep' => $iod[ 'dep' ] ,
						'actual' => ( ( getDateInt( $wsd[ 'dateFrom' ] ) <= $ccd ) && ( getDateInt( $wsd[ 'dateTo' ] ) >= $ccd )  ) ? 1 : 0
					) );

					$wIDL[]= $wsd[ 'wid' ];
					$portalDB->insertRow( 'kuvk-links' , array(
						'ext_type' => 'staffing-workers' ,
						'ext_id' => 'w:'.$wrkFirstID.'@'.$stID ,
						'kuvk-guid' => $wsd[ 'wsid' ]
					) );
				}
				//$portalDB->noResult( 'update `workers-no-spec` set `post_1_id` = ? , `post_2_id` = ? , `dep` = ? , `actual` = 1 where `id` in ( ?* )' , 'iii*i' , $iod[ 'post' ] , $iod[ 'post' ] , $iod[ 'dep' ] , $wIDL );
				$LID = $stID ;
				break ;

			case 'departments' :
				$portalDB->insertRow( 'departments' , array(
					'ind' => $iod[ 'ind' ] ,
					'name' =>  rcvti( $iod[ 'name' ] ) ,
					'short_name' => rcvti( $iod[ 'short_name' ] ) ,
					'actual' => 1 ,
				) );
				break ;

			case 'posts' :
				$portalDB->insertRow( 'posts' , array(
					'name' =>  rcvti( $iod[ 'name' ] ),
					'simple_name' => rcvti( $iod[ 'simple_name' ] ),
				) );
				break ;

			case 'workers-spec' :
				if ( !isset( $iod[ 'sid' ] ) ) {
					error_log( 'NO SID!!! worker ID = '.$iod[ 'wid' ] );
				}
				$portalDB->insertRow( 'workers-spec' , array(
					'worker_id' => $iod[ 'wid' ] ,
					'spec_id' => $iod[ 'sid' ] ,
					'date_from' => getDateInt( $iod[ 'date_from' ] ) ,
					'date_to' => getDateInt( $iod[ 'date_to' ] ) ,
					'org_label' =>  rcvti( $iod[ 'att_seo' ] )
				) );
				break ;

			case 'correspondence' :
				$pages = array();
				if ( isset( $iod[ 'pages' ] ) ) {
					$pages[ 'm' ] = $iod[ 'pages' ];
				}
				if ( isset( $iod[ 'copies' ] ) ) {
					$pages[ 'c' ] = $iod[ 'copies' ];
				}
				$portalDB->insertRow( 'correspondence-main' , array(
					'type' =>  $iod[ 'type' ] ,
					'num' => $iod[ 'num' ] ,
					'date' => getDateInt( $iod[ 'date' ] ) ,
					'ext_num' => rcvti( $iod[ 'ext_num' ] ) ,
					'ext_date' => getDateInt( $iod[ 'ext_date' ] ) ,
					'name' => rcvti( $iod[ 'nameDescr' ] ) ,
					'description' => rcvti( $iod[ 'nameDescr' ] ) ,
					'pages' => json_encode( $pages )
				) );
				$LID = $portalDB->lastInsertID();
				$portalDB->insertRow( 'correspondence-target' , array(
					'ext_id' => $LID ,
					'tgt' => $iod[ 'tgt' ]
				) );
				if ( isset( $iod[ 'expList' ] ) && is_array( $iod[ 'expList' ] ) && count( $iod[ 'expList' ] ) > 0 ) {
					foreach( $iod[ 'expList' ] as $expID ) {
						$portalDB->insertRow( 'correspondence-experts' , array(
							'ext_id' => $LID ,
							'exp' => $expID
						) );
					}
				} else {
					$portalDB->insertRow( 'correspondence-experts' , array(
						'ext_id' => $LID ,
						'exp' => 1
					) );
				}
				break ;

			case 'subpoena' :
				$portalDB->insertRow( 'subpoena' , array(
					'id' => $iod[ 'number' ] ,
					'date' => getDateInt( $iod[ 'date' ] ) ,
					'agency_id' => $iod[ 'agency_id' ] ,
					'address' => '-' ,
					'to_date' => getDateInt( $iod[ 'to_date' ] ) ,
					'type' =>  $iod[ 'type' ] ,
				) );
				$LID = $portalDB->lastInsertID();
				$portalDB->insertRow( 'subpoena-experts' , array(
					's_id' => $LID ,
					'exp_id' => $iod[ 'exp_id' ]
				) );
				break ;

			case 'woe' :
				error_log( print_r( $iod , true ) );
				$l23c = $portalDB->query( 'select `t3`.`id` from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t2`.`mat_id` = ? ) and ( `t2`.`id` = `t3`.`ext_id` )' , false , 's' , $iod[ 'ext_id' ] );
				$cDate = time();
				$portalDB->insertRow( 'writ-of-execution' , array(
					'ext_id' => $iod[ 'ext_id' ] ,
					'num' => rcvti( $iod[ 'num' ] ) ,
					'date' => getDateInt( $iod[ 'date' ] ) ,
					'case_num' => rcvti( $iod[ 'case_num' ] ) ,
					'issue_date' => getDateInt( $iod[ 'issue_date' ] ) ,
					'date_force' => getDateInt( $iod[ 'date_force' ] ) ,
					'from_agent' => $iod[ 'tgt' ] ,
					'state' => $iod[ 'state' ]
					//'ep_num' =>
				) );
				$LID = $portalDB->lastInsertID();
				$portalDB->insertRow( 'writ-of-execution-payers' , array(
					'ext_id' => $LID ,
					'payer' => rcvti( $iod[ 'payer' ] ) ,
					'price' => str_replace( ',' , '.' , $iod[ 'price' ] )
				) );
				if ( $l23c !== false && count( $l23c ) > 0 && count( $iod[ 'payments' ] ) > 0 ) {
					$comments = array();
					foreach( $iod[ 'payments' ] as $p ) {
						$comments[]=
							date( 'd-m-Y' , getDateInt( $p[ 'date' ] ) ).' оплачено '.
							rcvti( $p[ 'ex_data' ] ).
							money_format( "%!i" , floatval( str_replace( ',' , '.' ,$p[ 'sum' ] ) ) );
					}
					$comments = implode( "\r\n\r\n" , $comments );
					$portalDB->insertRow( 'expertize-comments' , array(
						'ext_type' => 'expertize' ,
						'ext_id' => $l23c[ 0 ][ 'id' ] ,
						'date' => $cDate ,
						'exp_id' => 1 ,
						'comment' => $comments
					) );
				}
				break ;

			case 'bill' :
				error_log( print_r( $iod , true ) );

				$kl = false ;
				$lvl1c = false ;
				if ( isset( $iod[ 'exp_id' ] ) ) {
					$kl = $portalDB->simpleRow( 'kuvk-links' , array(
						'kuvk-guid' => $iod[ 'exp_id' ] ,
						'ext_type'  => KUVK_LINK_MATINCOMING_C23
					) );
					if ( $kl !== false ) {
						$kl = $kl[ 'ext_id' ];
						$lvl1c = $portalDB->row(
							"select
								`t1`.*
							from
								`matincoming` as `t1` ,
								`matincominglvl2` as `t2` ,
								`expertize` as `t3`
							where
							    ( `t1`.`id` = `t2`.`mat_id` ) and
							    ( `t2`.`id` = `t3`.`ext_id` ) and
							    ( `t3`.`id` = ? )" , 'i' , $kl );
					}
				}

				$cDate = time();
				$portalDB->insertRow( 'bills' , array(
					'number'    => $iod[ 'number' ] == '' ? 0 : $iod[ 'number' ] ,
					'date'      => date( 'Y-m-d' , getDateInt( $iod[ 'date' ] ) ) ,
					'payer'     => rcvti( $iod[ 'payer' ] ) ,
					'address'   => '-' ,
					'customer'  => rcvti( $iod[ 'customer' ] ) ,
					'worker'    => $iod[ 'worker_id' ] ,
					'reason_id' => 1 ,
					'exp_id'    => ( $kl ? $kl : null ) ,
					'state'     => null
				) );
				$LID = $portalDB->lastInsertID();

				$portalDB->insertRow( 'items' , array(
					'ext_id' => $LID ,
					'name'   =>
						isset( $iod[ 'item' ] ) && trim( $iod[ 'item' ] ) != '' ?
							rcvti( $iod[ 'item' ] ) :
							( $lvl1c !== false ?
								'Экспертиза (исследование) № '.matincomingNumber( $lvl1c[ 'id' ] ).' за '.matincomingYear( $lvl1c[ 'id' ] ).'г.' :
								'---'
							),
					'count'  =>  1 ,
					'price'  => round( floatval( str_replace( ',' , '.' , $iod[ 'price' ] ) ) * 100 , 0 )
				) );

				break ;

		}

		if ( $LID !== false ) {
			$iod[ 'id' ] = $LID ;
		} else {
			$iod[ 'id' ] = $portalDB->lastInsertID();
		}
		$res[ 'data' ] = $iod ;
		$portalDB->noResult( 'insert `kuvk-links` ( `ext_type` , `ext_id` , `kuvk-guid` ) values( ? , ? , ? )' , 'sss' , $iot , $iod[ 'id' ] , $guid );
	}


	$res = json_encode( $res , JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
	if ( $res == '' ) {
		echo json_encode( array( 'success' => false , 'error' => json_last_error() ) );
	} else {
		echo $res ;
	}
