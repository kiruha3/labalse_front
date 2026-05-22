<?php
	//
	require_once( '../../core.php' );
	/**
	 * @var $portalDB
	 * @var $dbConfig
	 */
	require_once( '../../maindb/lconfig.php'  );

	$dataOrig = file_get_contents( 'php://input' );
	$data = json_decode( $dataOrig , true );

	//$res = array( 'success' => true , 'orig-data' => $data );
	$res = array( 'success' => true );

	function getDateInt( $v ) {
		return intval( substr( $v.'' , 0 , -3 ) , 10 );
	}

	if ( $data !== false && isset( $data[ 'type' ] ) ) {
		$iot = $data[ 'type' ];
		switch ( $iot ) {
			case 'agent' :
				$atMap = array();
				$longReqData = array();
				
				$iodGroups = array(
					'contacts' => array( 'phone' , 'email' )
				);
				
				foreach( $data[ 'data' ] as $iod ) {
					$guid = $iod[ 'kuvk-guid' ];
					$iodEV = array( 'phone' => '' , 'email' => '' );
					
					foreach( $iodGroups as $gn => $gsg ) {
						if ( !isset( $iod[ $gn ] ) ) {
							$iod[ $gn ] = array();
						}
						$iodSG = &$iod[ $gn ];
						foreach( $gsg as $sgn ) {
							if ( !isset( $iodSG[ $sgn ] ) || !$iodSG[ $sgn ] ) {
								$iodSG[ $sgn ] = '' ;
							}
							$iodEV[ $sgn ] = Str2SQL( rcvti( $iodSG[ $sgn ] ) );
						}
					}
					
					$longReqData[]= "( ".$iod[ 'ay' ]." , ".Str2SQL( rcvti( $iod[ 'name' ] ) )." , '".$guid."' , ".implode( ',' , $iodEV )." )" ;
				}
				$portalDB->noResult( "alter table `agent` add `kuvk-guid` VARCHAR(64) NOT NULL , add `phone` VARCHAR(64) NULL default NULL , add `email` VARCHAR(64) NULL default NULL ;" );
				$portalDB->noResult( "insert into `agent` ( `ext_id` , `name` , `kuvk-guid` , `phone` , `email` ) values ".implode( ',' , $longReqData ) );
				$portalDB->noResult( "insert into `kuvk-links` ( `ext_type` , `ext_id` , `kuvk-guid` ) select 'agent' as `ext_type` , `t1`.`id` as `ext_id` , `t1`.`kuvk-guid` from `agent` as `t1` ;" );
				
				$portalDB->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) select `t1`.`id` as `ext_id` , 1 as `type` , `t2`.`destination` , 1 as `actual` from `agent` as `t1` , `agency` as `t2` where ( `t1`.`ext_id` = `t2`.`id` );" );
				$portalDB->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) select `t1`.`id` as `ext_id` , 3 as `type` , `t2`.`phone` , 1 as `actual` from `agent` as `t1` , `agency` as `t2` where ( `t1`.`ext_id` = `t2`.`id` );" );
				$portalDB->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) select `t1`.`id` as `ext_id` , 2 as `type` , `t2`.`email` , 1 as `actual` from `agent` as `t1` , `agency` as `t2` where ( `t1`.`ext_id` = `t2`.`id` );" );
				$portalDB->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) select `t1`.`id` as `ext_id` , 1001 as `type` , `t2`.`inn` , 1 as `actual` from `agent` as `t1` , `agency` as `t2` where ( `t1`.`ext_id` = `t2`.`id` );" );
				$portalDB->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) select `t1`.`id` as `ext_id` , 1002 as `type` , `t2`.`kpp` , 1 as `actual` from `agent` as `t1` , `agency` as `t2` where ( `t1`.`ext_id` = `t2`.`id` );" );
				$portalDB->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) select `t1`.`id` as `ext_id` , 1003 as `type` , `t2`.`ogrn` , 1 as `actual` from `agent` as `t1` , `agency` as `t2` where ( `t1`.`ext_id` = `t2`.`id` );" );
				$portalDB->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) select `t1`.`id` as `ext_id` , 3 as `type` , `t1`.`phone` , 1 as `actual` from `agent` as `t1` ;" );
				$portalDB->noResult( "insert into `agent-contacts` ( `ext_id` , `type` , `value` , `actual` ) select `t1`.`id` as `ext_id` , 2 as `type` , `t1`.`email` , 1 as `actual` from `agent` as `t1` ;" );
				
				$portalDB->noResult( "alter table `agent` drop `kuvk-guid` , drop `phone` , drop `email` ;" );
				$portalDB->noResult( "alter table `agency` drop `phone` , drop `email` , drop `inn` , drop `kpp` , drop `ogrn` ;" );
				$portalDB->noResult( "delete from `agent-contacts` where ( `value` is null ) or ( trim( `value` ) = '' )" );
				$atMap = $portalDB->query(
					"select
						`t1`.`ext_id` as `mID` ,
       					`t1`.`kuvk-guid` as `kID` ,
       					`t2`.`ext_id` as `emID`
					from
						`kuvk-links` as `t1` ,
					     `agent` as `t2`
					where
						( `t1`.`ext_type` = 'agent' ) and 
						( `t2`.`id` = `t1`.`ext_id` )"
				);
				$res[ 'id-map' ] = $atMap ;

				break ;

			case 'agency' :
				$ayMap = array();
				$longReqData = array();
				
				$iodGroups = array(
					'contacts' => array( 'addr' , 'phone' , 'email' ) ,
					'attr' => array( 'inn' , 'kpp' , 'ogrn' )
				);
				
				foreach( $data[ 'data' ] as $iod ) {
					$guid = $iod[ 'kuvk-guid' ];
					
					$iodEV = array( 'destination' => '' , 'addr' => '' , 'phone' => '' , 'email' => '' , 'inn' => '' , 'kpp' => '' , 'ogrn' => '' );
					
					foreach( $iodGroups as $gn => $gsg ) {
						if ( !isset( $iod[ $gn ] ) ) {
							$iod[ $gn ] = array();
						}
						$iodSG = &$iod[ $gn ];
						foreach( $gsg as $sgn ) {
							if ( !isset( $iodSG[ $sgn ] ) || !$iodSG[ $sgn ] ) {
								$iodSG[ $sgn ] = '' ;
							}
							$iodEV[ $sgn ] = Str2SQL( rcvti( $iodSG[ $sgn ] ) );
						}
					}
					
					$iodEV[ 'destination' ] = $iodEV[ 'addr' ];
					unset( $iodEV[ 'addr' ] );
					
					$longReqData[]= "( ".$iod[ 'toa' ]." , ".Str2SQL( rcvti( isset( $iod[ 'name' ] ) && trim( $iod[ 'name' ] ) != '' ? $iod[ 'name' ] : $iod[ 'name_full' ] ) )." , '".$guid."' , ".implode( ',' , $iodEV )." )" ;
				}
				$portalDB->noResult( "alter table `agency` add `kuvk-guid` VARCHAR(64) NULL default NULL , add `phone` VARCHAR(64) NULL default NULL , add `email` VARCHAR(64) NULL default NULL , add `inn` VARCHAR(64) NULL default NULL , add `kpp` VARCHAR(64) NULL default NULL , add `ogrn` VARCHAR(64) NULL default NULL ;" );
				$portalDB->noResult( "insert into `agency` ( `ext_id` , `name` , `kuvk-guid` , `destination` , `phone` , `email` , `inn` , `kpp` , `ogrn` ) values ".implode( ',' , $longReqData ) );
				$portalDB->noResult( "insert into `kuvk-links` ( `ext_type` , `ext_id` , `kuvk-guid` ) select 'agency' as `ext_type` , `t1`.`id` as `ext_id` , `t1`.`kuvk-guid` from `agency` as `t1` where `t1`.`kuvk-guid` is not null ;" );
				$portalDB->noResult( "alter table `agency` drop `kuvk-guid` ;" );
				$ayMap = $portalDB->query(
					"select
						`t1`.`ext_id` as `mID` ,
       					`t1`.`kuvk-guid` as `kID`
					from
						`kuvk-links` as `t1`
					where
						( `t1`.`ext_type` = 'agency' )"
				);
				$res[ 'id-map' ] = $ayMap ;

				break ;
			
			case 'enforcement-proceedings' :
				//$portalDB->dbgMode = 'log' ;
				$longReqData = array();
				foreach( $data[ 'data' ] as $iod ) {
					$guid = $iod[ 'kuvk-guid' ];
					$longReqData[]= "( "
						.Str2SQL( rcvti( $iod[ 'woeNum' ] ) )." , "
						.Str2SQL( rcvti( $iod[ 'num' ] ) )." , "
						.getDateInt( $iod[ 'date' ] )." , "
						.intval( $iod[ 'agency' ] , 10 )." , "
						.intval( $iod[ 'state' ] , 10 )." , "
						.str_replace( ',' , '.' ,  $iod[ 'price' ] )." , "
						.getDateInt( $iod[ 'woeDate' ] )." , "
						.getDateInt( $iod[ 'stateDate' ] )." , "
						."'".$guid."' , "
						.Str2SQL( rcvti( $iod[ 'comment' ] ) )
					." )" ;
				}
				//error_log( $longReqData[ count( $longReqData ) - 1 ] );
				$portalDB->noResult( "alter table `enforcement-proceedings` add `kuvk-guid` VARCHAR(64) NULL default NULL , add `comment` TEXT NULL default NULL ;" );
				$portalDB->noResult( "insert into `enforcement-proceedings` ( `woe_num` , `num` , `date` , `agency` , `state` , `price` , `woe_date` , `state_date` , `kuvk-guid` , `comment` ) values ".implode( ',' , $longReqData ) );
				$portalDB->noResult( "insert into `kuvk-links` ( `ext_type` , `ext_id` , `kuvk-guid` ) select 'ep' as `ext_type` , `t1`.`id` as `ext_id` , `t1`.`kuvk-guid` from `enforcement-proceedings` as `t1` where `t1`.`kuvk-guid` is not null ;" );
				$portalDB->noResult( "insert into `expertize-comments` ( `ext_type` , `ext_id` , `date` , `exp_id` , `comment` ) select 'ep' as `ext_type` , `t1`.`id` as `ext_id` , ".time()." as `date` , 1 as `exp_id` , `t1`.`comment` from `enforcement-proceedings` as `t1` where ( `t1`.`comment` is not null ) and ( `t1`.`comment` <> '' );" );
				$portalDB->noResult( "alter table `enforcement-proceedings` drop `kuvk-guid` , drop `comment` ;" );
				$epMap = $portalDB->query(
					"select
						`t1`.`ext_id` as `mID` ,
       					`t1`.`kuvk-guid` as `kID`
					from
						`kuvk-links` as `t1`
					where
						( `t1`.`ext_type` = 'ep' )"
				);
				$res[ 'id-map' ] = $epMap ;
				
				break ;
				
			case 'equipment-list' :
				$longReqData = array();
				foreach( $data[ 'data' ] as $iod ) {
					$guid = $iod[ 'kuvk-guid' ];
					if ( $iod[ 'specs' ] !== false && is_array( $iod[ 'specs' ] ) && count( $iod[ 'specs' ] ) > 0 ) {
						$fr = '{"env:equipment-list-name":"expertize","spec-id":['.implode( ',' , $iod[ 'specs' ] ).']}' ;
					} else {
						$fr = '{"env:equipment-list-name":"expertize"}' ;
					}
					$longReqData[]= "( "
						.Str2SQL( rcvti( $iod[ 'name' ] ) )." , "
						.Str2SQL( rcvti( $iod[ 'label' ] ) )." , "
						.Str2SQL( rcvti( $iod[ 'reg-number' ] ) )." , "
						.Str2SQL( rcvti( $iod[ 'manufacture-number' ] ) )." , "
						.getDateInt( $iod[ 'startup-date' ] )." , "
						.Str2SQL( rcvti( $iod[ 'mop' ] ) )." , "
						.Str2SQL( $fr )." , "
						.Str2SQL( $guid )
						." )" ;
				}
				$portalDB->noResult( "alter table `equipment` add `kuvk-guid` VARCHAR(64) NULL default NULL ;" );
				$portalDB->noResult( "insert into `equipment` ( `name` , `label` , `reg-number` , `manufacture-number` , `startup-date` , `mop` , `filter_rules`, `kuvk-guid` ) values ".implode( ',' , $longReqData ) );
				$portalDB->noResult( "insert into `kuvk-links` ( `ext_type` , `ext_id` , `kuvk-guid` ) select 'equipment' as `ext_type` , `t1`.`id` as `ext_id` , `t1`.`kuvk-guid` from `equipment` as `t1` where `t1`.`kuvk-guid` is not null ;" );
				$portalDB->noResult( "insert into `exp-equipment` ( `id` , `ext_id` , `test_type` , `test_period` , `test_date` , `test_result` , `state` , `actual` ) select `t1`.`id` , `t1`.`id` as `ext_id` , 0 as `test_type` , '100y' as `test_period` , ".time()." as `test_date` , 0 as `test_result` , 0 as `state` , 1 as `actual` from `equipment` as `t1` ;" );
				$portalDB->noResult( "alter table `equipment` drop `kuvk-guid` ;" );
				$epMap = $portalDB->query(
					"select
						`t1`.`ext_id` as `eID` ,
       					`t1`.`kuvk-guid` as `kID`
					from
						`kuvk-links` as `t1`
					where
						( `t1`.`ext_type` = 'equipment' )"
				);
				$res[ 'id-map' ] = $epMap ;
				
				break ;
			
			
			
			/*case 'matincoming' :
				$date = getDateInt( $iod[ 'date' ] );
				$LID = matincomingID( $iod[ 'num' ] , date( 'Y' , $date ) );
				$portalDB->insertRow( 'matincoming' , array(
					'id' => $LID ,
					'date' =>  date( 'Y-m-d' , $date ),
					'from_agency' => $iod[ 'from_agency' ] ,
					'from_agent' => $iod[ 'from_agent' ] ,
					'ex_data_3' => rcvti( $iod[ 'ex_data_3' ] ) ,
					'ex_data_4' => rcvti( $iod[ 'ex_data_4' ] ) ,
					'ex_data_7' => rcvti( $iod[ 'ex_data_7' ] ) ,
					'exp_type' => $iod[ 'exp_type' ] == -1 ? 0 : $iod[ 'exp_type' ] ,

					'state' => $iod[ 'state' ] ,
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
						'order_date' => $date ,
						'state' => $c3state ,
						'fin_date' => $c3state > 0 ? date( 'Y-m-d' , getDateInt( $cc23[ 'fin_date' ] ) ) : null ,
						'reason_2' => $cc23[ 'reason_2' ] ,
						'reason_2_comment' => rcvti( $cc23[ 'reason_2_comment' ] ) ,
						'conclusion' => $cc23[ 'conclusion' ] ,
						'conclusion_1' => $cc23[ 'conclusion_1' ] ,
						'conclusion_2' => $cc23[ 'conclusion_2' ] ,
						'conclusion_2_1' => $cc23[ 'conclusion_2_1' ] ,
						'conclusion_2_2' => $cc23[ 'conclusion_2_2' ] ,
						'conclusion_3' => $cc23[ 'conclusion_3' ] ,
						'sndz' => $cc23[ 'sndz' ] ? 1 : 0 ,
					) );
				}
				break ;

			case 'workers-1' :
				if ( $guid == '849e81b9-f563-4908-96b9-1065901c42d3' ) {
					error_log( bin2hex( $dataOrig ) );
				}
				$portalDB->insertRow( 'workers-no-spec' , array(
					'name' =>  rcvti( $iod[ 'name' ] ) ,
					'actual' => 0 ,
				) );
				$iot = 'workers' ;
				$LID = $portalDB->lastInsertID();
				$portalDB->updateRow( 'workers-no-spec' , array(
					'id' => $LID ,
					'first_id' => $LID ,
				) );
				break ;

			case 'staffing' :
				$stID = 'p:'.$iod[ 'post' ].'@d:'.$iod[ 'dep' ];
				$wIDL = array();
				foreach( $iod[ 'workers' ] as $wsd ) {
					$wIDL[]= $wsd[ 'wid' ];
					$portalDB->insertRow( 'kuvk-links' , array(
						'ext_type' => 'staffing-workers' ,
						'ext_id' => 'w:'.$wsd[ 'wid' ].'@'.$stID ,
						'kuvk-guid' => $wsd[ 'wsid' ]
					) );
				}
				$portalDB->noResult( 'update `workers-no-spec` set `post_1_id` = ? , `post_2_id` = ? , `dep` = ? , `actual` = 1 where `id` in ( ?* )' , 'iii*i' , $iod[ 'post' ] , $iod[ 'post' ] , $iod[ 'dep' ] , $wIDL );
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
				$portalDB->insertRow( 'writ-of-execution' , array(
					'ext_id' => $iod[ 'ext_id' ] ,
					'num' => rcvti( $iod[ 'num' ] ) ,
					'date' => getDateInt( $iod[ 'date' ] ) ,
					'case_num' => rcvti( $iod[ 'case_num' ] ) ,
					'issue_date' => getDateInt( $iod[ 'issue_date' ] ) ,
					'from_agent' => $iod[ 'tgt' ] ,

					//'ep_num' =>
				) );
				$LID = $portalDB->lastInsertID();
				$portalDB->insertRow( 'writ-of-execution-payers' , array(
					'ext_id' => $LID ,
					'payer' => rcvti( $iod[ 'payer' ] ) ,
					'price' => $iod[ 'price' ]
				) );
				break ;
			*/
		}
	}


	$res = json_encode( $res , JSON_UNESCAPED_UNICODE /*| JSON_FORCE_OBJECT*/ );
	if ( $res == '' ) {
		echo json_encode( array( 'success' => false , 'error' => json_last_error() ) );
	} else {
		echo $res ;
	}
