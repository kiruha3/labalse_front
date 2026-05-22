<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $portalDB
	 * @var $dbConfig
	 */
	include_once( 'lconfig.php' );
	require_once( '../cores/core.maindb.php' );
	require_once( 'correspondence.core.php' );
	/**
	 * @var $queryTables
	 * @var $currViewName
	 * @var $currViewCfg
	 * @var $UserID
	 */

	$corTypes = $portalDB->table( 'correspondence-types' , 'id' );
	$corMarks = $portalDB->table( 'marks-catalog' , 'id' );

	if ( $currViewName != 'any' ) {
		$queryGroup = $currViewCfg[ 'query' ][ 'group' ];
		$queryOrder = "( `t1`.`num` * 1 ) desc , `t1`.`date` desc" ;
	} else {
		$queryGroup = "`t1`.`id`" ;
		$queryOrder = "`t1`.`date` desc" ;
	}

	$tabWorkers = $portalDB->table( 'workers' , 'id' );
	foreach ( $tabWorkers as &$w ) {
		$w[ 'name' ] = NAMES_Format( NAMES_parse( $w[ 'name' ] ) );
	} unset( $w );

	$tabWorkersActual = $portalDB->query( "select * from `workers` where ( `actual` <=> 1 ) order by `name`" , 'id' );

	if ( isset( $_REQUEST[ 'mode' ] ) && $_REQUEST[ 'mode' ] == 'ajax' ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );

		echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

		$ajaxRequest = simplexml_load_string( $_REQUEST[ 'data' ] , 'SimpleXMLElement' , LIBXML_NOCDATA );

		switch ( $ajaxRequest->getName() ) {
			case 'updTmpl' :
				updateInputTemplates( 'correspondence.'.$currViewName , iconv( 'utf8' , 'cp1251' , $ajaxRequest ) );
				echo '<result></result>' ;
				break ;

			case 'get-cor-ny' :
				$cid = $ajaxRequest[ 'id' ];
				$res = $portalDB->row( "select * from `correspondence-main` where `id` = ?" , 'i' , $cid );
				if ( $res !== false ) {
					echo '<result state="ok" y="'.date( 'Y' , $res[ 'date' ] ).'" n="'.htmlspecialchars( $res[ 'num' ] , ENT_XML1 ).'"/>' ;
				} else {
					echo '<result state="error" />' ;
				}
				break ;

			case 'add-correspondence' :
				$regDate = Date2Int( $ajaxRequest[ 'd' ] );
				$ajaxRequest[ 'd' ] = $regDate ;
				$regY = date( 'Y' , $regDate );

				$corPages = array();
				$pn = trim( $ajaxRequest[ 'pn' ] ); // pn - pages number
				if ( $pn != '' & $pn != '-' ) {
					$corPages[]= '"m":"'.$pn.'"' ;
				}
				$an = trim( $ajaxRequest[ 'an' ] ); // an - attachments number
				if ( $an != '' & $an != '-' ) {
					$corPages[]= '"a":"'.$an.'"' ;
				}
				$corPages = '{'.implode( ',' , $corPages ).'}' ;

				$te = $ajaxRequest[ 'te' ]; // te - target experts: 1,37,48
				$marks = $ajaxRequest[ 'marks' ]; // marks : -1,-3,4

				$ajaxRequest->ext[ 'd' ] = Date2Int( $ajaxRequest->ext[ 'd' ] );
				$ajaxRequest->ext = Correspondence\tntb( $ajaxRequest->ext );
				$ajaxRequest->name = Correspondence\tntb( $ajaxRequest->name );
				$ajaxRequest->desc = Correspondence\tntb( $ajaxRequest->desc );

				if ( $ajaxRequest[ 'd' ] === false || $ajaxRequest->ext[ 'd' ] === false ) {
					echo '<result state="err">Неверный формат даты</result>' ;
				} else {
					$nn = $portalDB->row( "select ifnull( max( cast( `num` as signed integer ) ) + 1 , 1 ) as `next-num` from `correspondence-main` as `t1` where ( `t1`.`date` >= ? ) and ( `t1`.`date` < ? ) and ( ".$currViewCfg[ 'query' ][ 'nextNumCondition' ]." )" , 'ii' , mktime( 0 , 0 , 0 , 1 , 1 , $regY ) , mktime( 0 , 0 , 0 , 1 , 1 , $regY + 1 ) );
					$portalDB->noResult(
						"insert into `correspondence-main` ( `type` , `num` , `date` , `ext_num` , `ext_date` , `name` , `description` , `pages` ) ".
						"values ( ? , ? , ? , ? , ? , ? , ? , ? )" , 'isisisss' ,
						$ajaxRequest[ 't' ] , $nn[ 'next-num' ] , $ajaxRequest[ 'd' ] , $ajaxRequest->ext , $ajaxRequest->ext[ 'd' ] ,
						$ajaxRequest->name , $ajaxRequest->desc , $corPages
					);
					$lid = $portalDB->row( "select last_insert_id() as `lid`" );
					$lid = $lid[ 'lid' ];

					$te = explode( ',' , $te );
					$tena = array();
					foreach( $te as &$ce ) {
						$portalDB->noResult( "insert into `correspondence-experts` ( `ext_id` , `exp` ) values ( ? , ? )" , 'ii' , $lid , $ce );
						if ( isset( $tabWorkers[ $ce ] ) && !is_null( $tabWorkers[ $ce ][ 'ad-login' ] ) ) {
							//$tena[]= $tabWorkers[ $ce ][ 'ad-login' ];
						}
					} unset( $ce );
					$tena[]= 'uw-dan' ;
					$tena[]= 'test-user' ;
					$tclfr = array(); // total contact list for reply : [ agent.id => [ contact.id , ... ] , ... ]

					foreach( $ajaxRequest->{'addressee-list'}->addressee as $ain ) {
						$aicl = array(); // addressee info contact list
						foreach ( $ain->contacts->contact as $aincc ) {
							$aicl[]= array(
								'type' => $aincc[ 't' ] ,
								'value' => Correspondence\tntb( $aincc ) ,
								'ufr' => $aincc[ 'ufr' ] ,
								'state' => $aincc[ 's' ] ,
								'state-date' => Date2Int( $aincc[ 'sd' ] )
							);
						}
						$aiRes = storeAgentData( $portalDB , $ain->agency[ 'toa' ] , Correspondence\tntb( $ain->agency ) , Correspondence\tntb( $ain->agent ) , $aicl );
						$aiclfr = array();
						foreach ( $aiRes[ 'contacts' ] as $aincc ) {
							if ( $aincc[ 'ufr' ] == 1 ) {
								$aiclfr[ 'c'.$aincc[ 'id' ] ] = $aincc ;
							}
						}
						$agentID = $aiRes[ 'agent.id' ];
						if ( !isset( $tclfr[ $agentID ] ) ) {
							$tclfr[ $agentID ] = array();
						}
						$tclfr[ $agentID ] = array_merge( $tclfr[ $agentID ] , $aiclfr );
					}

					foreach ( $tclfr as $ctgtlk => $ctgtlv ) {
						$portalDB->noResult( "insert into `correspondence-target` ( `ext_id` , `tgt` ) values ( ? , ? )" , "ii" , $lid , $ctgtlk );
						$tgtID = $portalDB->row( "select last_insert_id() as `lid`" );
						$tgtID = $tgtID[ 'lid' ];
						foreach ( $ctgtlv as $ctgtr ) {
							$portalDB->noResult( "insert into `correspondence-reply` ( `ext_id` , `contact` , `state` , `state-date` ) values ( ? , ? , ? , ? )" , "iiii" , $tgtID , $ctgtr[ "id" ] , $ctgtr[ "state" ] , $ctgtr[ "state-date" ] );
						}
					}

					if ( $marks != '' ) {
						$marks = explode( ',' , $marks );
						foreach( $marks as &$ce ) {
							$portalDB->noResult( "insert into `marks-objects` ( `ext_id` , `ext_type` , `mark_id` ) values ( ? , 'correspondence' , ? )" , "ii" , $lid , $ce );
						} unset( $ce );
					}

					sendJabberMessage( $tena , "на Ваше имя зарегистрирован [b]".$currViewCfg[ "doc-name" ]."[/b]" );
					echo "<result state=\"ok\" />" ;
				}
				break ;



			case 'get-correspondence' :
				$cid = (int) $ajaxRequest[ 'id' ];
				$queryCondition[]= "`t1`.`id` = ?" ;

				/*if ( $UserID == 1 ) {
					$portalDB->dbgMode = true ;
				}*/
				$res = $portalDB->row( "select `t1`.* from ".implode( ',' , $queryTables )." where ( ".implode( ' ) and ( ' , $queryCondition )." ) group by `t1`.`id` ".( $queryOrder !== false ? ' order by '.$queryOrder : "" ).'' , 'i' , $cid );
				$cid = $res[ "id" ];
				$res[ "experts" ] = implode( "," , array_column( $portalDB->simpleQuery( "correspondence-experts" , array( "ext_id" => $cid ) ) , "exp" ) );
				$res[ "addressee" ] = $portalDB->query( "SELECT `t5`.`name` as `ay` , `t4`.`name` as `at` , `t3`.`ext_id` FROM `correspondence-target` as `t3` , `agent` as `t4` , `agency` as `t5` WHERE ( `t3`.`ext_id` = ? ) AND ( `t4`.`id` = `t3`.`tgt` ) AND ( `t5`.`id` = `t4`.`ext_id` )" , false , "i" , $cid );
				$res[ "marks" ] = implode( "," , array_column( $portalDB->simpleQuery( "marks-objects" , array( "ext_id" => $cid , "ext_type" => "correspondence" ) ) , "mark_id" ) );

				//var_dump( $res );
				/*foreach( $res[ "addressee" ] as &$tmpRes ) {
					$tmpRes = $tmpRes[ "ay" ].", ".$tmpRes[ "at" ];
				} unset( $tmpRes );
				$res[ "addressee" ] = implode( "<br>" , $res[ "addressee" ] );*/

				$query =
					"select
						`t1`.* ,
						`t2`.`ext_id` as `toa` ,
						`t2`.`name` as `agency` ,
						`t3`.`name` as `agent` ,
						`t3`.`id` as `agent.id`
					from
						`correspondence-target` as `t1` ,
						`agency` as `t2` ,
						`agent` as `t3`
					where
						( `t1`.`ext_id` = ? ) and
						( `t3`.`id` = `t1`.`tgt` ) and
						( `t3`.`ext_id` = `t2`.`id` )" ;
				$addresseeRes = $portalDB->query( $query , "id" , "i" , $cid );
				$corPages = json_decode( $res[ "pages" ] , true );
				if ( !isset( $corPages[ "m" ] ) ) {
					$corPages[ "m" ] = "" ;
				}
				if ( !isset( $corPages[ "a" ] ) ) {
					$corPages[ "a" ] = "" ;
				}
				echo '<result t="'.$res[ "type" ]."\" te=\"".$res[ "experts" ]."\" pn=\"".$corPages[ "m" ]."\" an=\"".$corPages[ "a" ]."\" marks=\"".$res[ "marks" ]."\">".
					"<num d=\"".date( "d-m-Y" , $res[ "date" ] )."\">".toCDATA( $res[ "num" ] )."</num>".
					"<ext d=\"".date( "d-m-Y" , $res[ "ext_date" ] )."\">".toCDATA( $res[ "ext_num" ] )."</ext>".
					"<name>".toCDATA( $res[ "name" ] )."</name>".
					"<desc>".toCDATA( $res[ "description" ] )."</desc><addressee-list>" ;
					foreach ( $addresseeRes as $car ) {
						echo "<addressee><agency toa=\"".$car[ "toa" ]."\">".toCDATA( $car[ "agency" ] )."</agency><agent>".toCDATA( $car[ "agent" ] )."</agent><contacts>" ;
						$contactsForReply = $portalDB->query( "select `t1`.* , `t2`.`state` , `t2`.`state-date` from `agent-contacts` as `t1` , `correspondence-reply` as `t2` where ( `t1`.`ext_id` = ? ) and ( `t2`.`contact` = `t1`.`id` ) and ( `t2`.`ext_id` = ? )" , "id" , "ii" , $car[ "agent.id" ] , $car[ "id" ] );
						foreach ( $contactsForReply as $ccr ) {
							echo "<contact id=\"".$ccr[ "id" ]."\" t=\"".$ccr[ "type" ]."\" ufr=\"1\" s=\"".$ccr[ "state" ]."\" sd=\"".date( "d-m-Y" , $ccr[ "state-date" ] )."\">".toCDATA( $ccr[ "value" ] )."</contact>" ;
						}
						echo "</contacts></addressee>" ;
					}
				echo "</addressee-list></result>" ;

				$portalDB->dbgMode = false ;
				break ;



			case "change-correspondence" :
				//$portalDB->dbgMode = true ;
				$cid = intval( $ajaxRequest[ "id" ] );
				$ajaxRequest[ "d" ] = Date2Int( $ajaxRequest[ "d" ] );

				$corPages = array();
				$pn = trim( $ajaxRequest[ "pn" ] );
				if ( $pn != "" & $pn != "-" ) {
					$corPages[]= '"m":"'.$pn.'"' ;
				}
				$an = trim( $ajaxRequest[ "an" ] );
				if ( $an != "" & $an != "-" ) {
					$corPages[]= '"a":"'.$an.'"' ;
				}
				$corPages = '{'.implode( "," , $corPages ).'}' ;

				$te = $ajaxRequest[ "te" ];
				$marks = $ajaxRequest[ "marks" ];

				$ajaxRequest->ext[ "d" ] = Date2Int( $ajaxRequest->ext[ "d" ] );
				$ajaxRequest->ext = Correspondence\tntb( $ajaxRequest->ext );
				$ajaxRequest->name = Correspondence\tntb( $ajaxRequest->name );
				$ajaxRequest->desc = Correspondence\tntb( $ajaxRequest->desc );

				if ( $ajaxRequest[ "d" ] === false || $ajaxRequest->ext[ "d" ] === false ) {
					echo "<result state=\"err\">Неверный формат даты</result>" ;
				} else {
					$portalDB->noResult(
						"update `correspondence-main` set `type` = ? , `date` = ? , `ext_num` = ? , `ext_date` = ? , `name` = ? , `description` = ? , `pages` = ? where `id` = ? " ,
						"iisisssi" ,
						$ajaxRequest[ "t" ] , $ajaxRequest[ "d" ] , $ajaxRequest->ext , $ajaxRequest->ext[ "d" ] ,
						$ajaxRequest->name , $ajaxRequest->desc , $corPages , $cid
					);

					/*$te = explode( "," , $te );
					foreach( $te as &$ce ) {
						$portalDB->noResult( "insert into `correspondence-experts` ( `ext_id` , `exp` ) values ( ? , ? )" , "ii" , $lid , $ce );
					} unset( $ce );*/

					$cel = $portalDB->query( "select * from `correspondence-experts` where `ext_id` = ?" , "exp" , "i" , $cid );
					if ( $te != "" ) {
						$te = explode( "," , $te );
					} else {
						$te = array();
					}
					$ep = array();
					foreach( $te as &$ce ) {
						if ( !isset( $cel[ $ce ] ) ) {
							$portalDB->noResult( "insert into `correspondence-experts` ( `ext_id` , `exp` ) values ( ? , ? )" , "ii" , $cid , $ce );
						}
						$ep[]= $ce ;
					} unset( $ce );
					foreach ( $ep as $ce ) {
						unset( $cel[ $ce ] );
					}
					foreach ( $cel as $ce ) {
						$portalDB->noResult( "delete from `correspondence-experts` where ( `id` = ? )" , "i" , $ce[ "id" ] );
					}



					$ctl = $portalDB->query( "select * from `correspondence-target` where ( `ext_id` = ? )" , "id" , "i" , $cid );
					$ctlRevMap = array(); // agent.id => [ correspondence-target.id , ... ]
					foreach ( $ctl as $cte ) {
						$cteID = $cte[ "tgt" ];
						if ( !isset( $ctlRevMap[ $cteID ] ) ) {
							$ctlRevMap[ $cteID ] = array();
						}
						$ctlRevMap[ $cteID ][]= $cte[ "id" ];
					}

					$tclfr = array(); // total contact list for reply : [ agent.id => [ contact.id , ... ] , ... ]

					foreach( $ajaxRequest->{'addressee-list'}->addressee as $ain ) {
						$aicl = array();
						foreach ( $ain->contacts->contact as $aincc ) {
							$aicl[]= array(
								"type" => $aincc[ "t" ] ,
								"value" => Correspondence\tntb( $aincc ) ,
								"ufr" => $aincc[ "ufr" ] ,
								"state" => $aincc[ "s" ] ,
								"state-date" => Date2Int( $aincc[ "sd" ] )
							);
						}
						$aiRes = storeAgentData( $portalDB , $ain->agency[ "toa" ] , Correspondence\tntb( $ain->agency ) , Correspondence\tntb( $ain->agent ) , $aicl );
						$aiclfr = array();
						foreach ( $aiRes[ "contacts" ] as $aincc ) {
							if ( $aincc[ "ufr" ] == 1 ) {
								$aiclfr[ "c".$aincc[ "id" ] ] = $aincc ;
							}
						}
						$agentID = $aiRes[ "agent.id" ];
						if ( !isset( $tclfr[ $agentID ] ) ) {
							$tclfr[ $agentID ] = array();
						}
						$tclfr[ $agentID ] = array_merge( $tclfr[ $agentID ] , $aiclfr );
					}

					$tp = array();
					foreach ( $tclfr as $ctgtlk => $ctgtlv ) {
						if ( isset( $ctlRevMap[ $ctgtlk ] ) ) {
							$portalDB->noResult( "delete from `correspondence-target` where ( `ext_id` = ? ) and ( `tgt` = ? ) and ( `id` <> ? )" , "iii" , $cid , $ctgtlk , $ctlRevMap[ $ctgtlk ][ 0 ] );
							foreach ( $ctlRevMap[ $ctgtlk ] as $tgtID ) {
								$portalDB->noResult( "delete from `correspondence-reply` where ( `ext_id` = ? )" , "i" , $tgtID );
							}
							$tgtID = $ctlRevMap[ $ctgtlk ][ 0 ];
							foreach ( $ctgtlv as $ctgtr ) {
								$portalDB->noResult( "insert into `correspondence-reply` ( `ext_id` , `contact` , `state` , `state-date` ) values ( ? , ? , ? , ? )" , "iiii" , $tgtID , $ctgtr[ "id" ] , $ctgtr[ "state" ] , $ctgtr[ "state-date" ] );
							}
							$tp[]= $ctgtlk ;
						} else {
							$portalDB->noResult( "insert into `correspondence-target` ( `ext_id` , `tgt` ) values ( ? , ? )" , "ii" , $cid , $ctgtlk );
							$tgtID = $portalDB->row( "select last_insert_id() as `lid`" );
							$tgtID = $tgtID[ "lid" ];
							foreach ( $ctgtlv as $ctgtr ) {
								$portalDB->noResult( "insert into `correspondence-reply` ( `ext_id` , `contact` , `state` , `state-date` ) values ( ? , ? , ? , ? )" , "iiii" , $tgtID , $ctgtr[ "id" ] , $ctgtr[ "state" ] , $ctgtr[ "state-date" ] );
							}
							$tp[]= $ctgtlk ;
						}
					}

					foreach ( $tp as $ctp ) {
						unset( $ctlRevMap[ $ctp ] );
					}

					foreach ( $ctlRevMap as $ctgtlk => $ctgtlv ) {
						$portalDB->noResult( "delete from `correspondence-target` where ( `id` = ?* )" , "*i" , $ctgtlv );
					}

					//var_dump( $cid );
					$cml = $portalDB->simpleQuery( "marks-objects" , array( "ext_id" => $cid , "ext_type" => 'correspondence' ) , "mark_id" );
					if ( $marks != "" ) {
						$marks = explode( "," , $marks );
					} else {
						$marks = array();
					}
					$mp = array();
					foreach( $marks as &$ce ) {
						if ( !isset( $cml[ $ce ] ) ) {
							$portalDB->noResult( "insert into `marks-objects` ( `ext_id` , `ext_type` , `mark_id` ) values ( ? , 'correspondence' , ? )" , "ii" , $cid , $ce );
						}
						$mp[]= $ce ;
					} unset( $ce );
					foreach ( $mp as $ce ) {
						unset( $cml[ $ce ] );
					}
					foreach ( $cml as $ce ) {
						$portalDB->noResult( "delete from `marks-objects` where ( `id` = ? )" , "i" , $ce[ "id" ] );
					}

					echo "<result state=\"ok\" />" ;
				}
				break ;

			case "get-files-to-upload" :
				call_user_func( function () {
					echo "<result>" ;
					echo "</result>" ;
				} );
				break ;

			case "link-file" :
				break ;
		}

		exit();
	}

	$docUploadRedirectParams = array();

	$queryCondition[ "idList" ] = array();

	if ( isset( $_REQUEST[ "experts" ] ) ) {
		$docUploadRedirectParams[ 'experts' ] = $_REQUEST[ "experts" ];
		if ( is_array( $_REQUEST[ "experts" ] ) ) {
			$experts = $_REQUEST[ "experts" ];
			foreach ( $experts as &$exp ) {
				$exp = intavl( trim( $exp ) );
			} unset( $exp );
		} else {
			$experts = getIDList( $_REQUEST[ "experts" ] );
		}
		$expertsIDL = array();
		foreach ( $experts as $exp ) {
			$idlist = getAllWorkersIDL( $exp );
			if ( $idlist !== false && count( $idlist ) > 0 ) {
				$expertsIDL = array_merge( $expertsIDL , $idlist );
			}
		}
		if ( count( $expertsIDL ) > 0 ) {
			$expertsIDL = array_unique( $expertsIDL );
			$queryTables[]= "`correspondence-experts` as `tef`" ;
			$queryCondition[]= "( `tef`.`exp` in ( ".implode( "," , $expertsIDL )." ) ) and ( `t1`.`id` = `tef`.`ext_id` )" ;
		} else {
			$queryCondition[]= "( 0 )" ;
		}
	}

	//print_r_html( $_REQUEST , 1 );
	// TODO: проблема с годами
	if ( isset( $_REQUEST[ "marks" ] ) ) {
		$docUploadRedirectParams[ 'marks' ] = $_REQUEST[ "marks" ];
		if ( is_array( $_REQUEST[ "marks" ] ) ) {
			$marks = $_REQUEST[ "marks" ];
			foreach ( $marks as &$mark ) {
				$mark = intval( trim( $mark ) );
			} unset( $mark );
		} else {
			$marks = getIDList( $_REQUEST[ "marks" ] );
		}
		if ( count( $marks ) > 0 ) {
			$marks = array_unique( $marks );
			$queryTables[]= "`marks-objects` as `tmf`" ;
			$queryCondition[]= "( `tmf`.`mark_id` in ( ".implode( "," , $marks )." ) ) and ( `t1`.`id` = `tmf`.`ext_id` ) and ( `tmf`.`ext_type` = 'correspondence' )" ;
		} else {
			$queryCondition[]= "( 0 )" ;
		}
	} else {
		$marks = array();
	}

	if ( isset( $_REQUEST[ "idlist" ] ) ) {
		$docUploadRedirectParams[ 'idList' ] = $_REQUEST[ 'idlist' ];
		$idlist = explode( "," , $_REQUEST[ "idlist" ] );
		$resIDList = array();
		foreach( $idlist as &$idl ) {
			$idl = trim( $idl );
			if ( strlen( $idl ) > 0 ) {
				$idl = intval( $idl );
				$resIDList[ $idl ] = $idl ;
			}
		} unset( $idl );
		if ( count( $resIDList ) > 0 ) {
			$queryCondition[ "idList" ] = array_merge( $queryCondition[ "idList" ] , $resIDList );
		} else {
			$queryCondition[ "idList" ] = "( 0 )" ;
		}
	} else
	if ( isset( $_REQUEST[ "text" ] ) ) {
		$docUploadRedirectParams[ 'text' ] = $_REQUEST[ 'text' ];
		$queryCondition[]= "( ( `t1`.`name` like concat( '%' , ".Str2SQL( $_REQUEST[ "text" ] )." , '%' ) ) or ( `t1`.`description` like concat( '%' , ".Str2SQL( $_REQUEST[ "text" ] )." , '%' )  ) )" ;
	} else
	if ( isset( $_REQUEST[ "y" ] ) ) {
		$docUploadRedirectParams[ 'y' ] = $_REQUEST[ 'y' ];
		$selYear = intval( $_REQUEST[ "y" ] );
		$selDateMin = mktime( 0 , 0 , 0 , 1 , 1 , $selYear );
		$selDateMax = mktime( 0 , 0 , 0 , 1 , 1 , $selYear + 1 );
		$queryCondition[]= "( `t1`.`date` >= ".$selDateMin." ) and ( `t1`.`date` < ".$selDateMax." )" ;
	} else {
		$selYear = intval( date( "Y" , time() ) );
		$selDateMin = mktime( 0 , 0 , 0 , 1 , 1 , $selYear );
		$selDateMax = mktime( 0 , 0 , 0 , 1 , 1 , $selYear + 1 );
		$queryCondition[]= "( `t1`.`date` >= ".$selDateMin." ) and ( `t1`.`date` < ".$selDateMax." )" ;
	}


	$expList = array();
	$eli = 0 ;
	$elj = 0 ;
	$elc = ceil( count( $tabWorkersActual ) / 3 );
	$lfl = "" ;
	foreach ( $tabWorkersActual as $w ) {
		if ( $elj == 0 ) {
			$expList[ $eli ] = array();
		}
		switch ( $w[ "post_1_id" ] ) {
			case 1 :
			case 2 :
			case 3 :
			case 4 :
				$expList[ $eli ][ $elj ] = "<label class=\"nrr-elt-mark1\"><input name=\"nrren[]\" type=\"checkbox\" value=\"".$w[ "id" ]."\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) )."</label>" ;
				break ;
			case 5 :
			case 14 :
			case 17 :
				$expList[ $eli ][ $elj ] = "<label class=\"nrr-elt-mark2\"><input name=\"nrren[]\" type=\"checkbox\" value=\"".$w[ "id" ]."\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) )."</label>" ;
				break ;
			case 6 :
			case 9 :
			case 15 :
				$expList[ $eli ][ $elj ] = "<label class=\"nrr-elt-mark3\"><input name=\"nrren[]\" type=\"checkbox\" value=\"".$w[ "id" ]."\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) )."</label>" ;
				break ;
			default :
				$expList[ $eli ][ $elj ] = "<label><input name=\"nrren[]\" type=\"checkbox\" value=\"".$w[ "id" ]."\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) )."</label>" ;
				break ;
		}
		$eli++ ;
		if ( $eli == $elc ) {
			$eli = 0 ;
			$elj++ ;
		}
	}

	foreach( $expList as &$e ) {
		$e = "<tr><td class=\"nrr-elt-n\">".implode( "</td><td class=\"nrr-elt-n\">" , $e )."</td></tr>" ;
	}

	$expList = '<table id="nrr-elt" class="nrr-elt">'.implode( $expList )."</table>" ;

	$tabTypeOfAgency = $portalDB->table( "type-of-agency" );
	$from_type_of_agency = '<select id="nrr-toa" size="1" class="nrr-toa" onchange="upd( \'nrr-agency-sel\' , \'nrr-toa\' )">' ;
	foreach( $tabTypeOfAgency as $i ) {
		$from_type_of_agency.= '<option value="'.$i[ "id" ].'">'.inForm( $i[ "name" ] , 1 , false )."</option>" ;
	}
	$from_type_of_agency.= "</select>" ;

	$corMarksList = "" ;
	$corMarksListFlt = "" ;
	foreach( $corMarks as $cm ) {
		if ( $cm[ "actual" ] == 1 ) {
			$corMarksList.= '<div class="nrr-mark-cont"><input type="checkbox" id="mark-'.$cm[ "id" ].'" name="nrr-marks[]" value="'.$cm[ "id" ].'"><label for="mark-'.$cm[ "id" ].'" class="nrr-mark-'.$cm[ "style" ].'">'.$cm[ 'name' ].'</label></div>' ;
		}
		$corMarksListFlt.= '<div class="nrr-mark-cont"><input type="checkbox" id="mark-flt-'.$cm[ 'id' ].'" name="marks[]" value="'.$cm[ 'id' ].'" '.( in_array( $cm[ 'id' ] , $marks ) ? " checked" : "" ).'><label for="mark-flt-'.$cm[ 'id' ].'" class="nrr-mark-'.$cm[ 'style' ].'">'.$cm[ 'name' ].'</label></div>' ;
		//$corMarksList.= "<div class=\"nrr-mark-cont\"><input type=\"checkbox\" id=\"mark-".$cm[ "id" ]."\" name=\"nrr-marks[]\" value=\"".$cm[ "id" ]."\"><label for=\"mark-".$cm[ "id" ]."\" class=\"nrr-mark-".$cm[ "style" ]."\">".$cm[ "name" ]."</label></div>" ;
	}

	if ( isset( $queryCondition[ "idList" ] ) ) {
		$qcidl = $queryCondition[ "idList" ];
		$qcidl = array_unique( $qcidl );
		if ( count( $qcidl ) > 0 ) {
			$queryCondition[]= "( `t1`.`id` in ( ".implode( "," , $qcidl )." ) )";
		}
		unset( $queryCondition[ "idList" ] );
	}

	if ( $UserID == 10000 ) {
		$portalDB->dbgMode = true ;
		fixTimerData( "pp1" );
	}

	//$portalDB->dbgMode = true ;
	$res = $portalDB->query( "select `t1`.* from ".implode( ',' , $queryTables )." where ( ".implode( ' ) and ( ' , $queryCondition )." ) group by `t1`.`id` ".( $queryOrder !== false ? " order by ".$queryOrder : "" ) , 'id' );
	//$portalDB->dbgMode = false ;
	$corIDList = array_keys( $res );
	if ( count( $corIDList ) > 0 ) {
		$tmpRes = $portalDB->simpleQuery( 'correspondence-experts' , array( 'ext_id' => $corIDList ) );
		linkTablesIntoTreeDirect( $res , $tmpRes , 'ext_id' , 'experts' );
		$tmpRes = $portalDB->query( "SELECT `t5`.`name` as `ay` , `t4`.`name` as `at` , `t3`.`ext_id` FROM `correspondence-target` as `t3` , `agent` as `t4` , `agency` as `t5` WHERE ( `t3`.`ext_id` in ( ?* ) ) AND ( `t4`.`id` = `t3`.`tgt` ) AND ( `t5`.`id` = `t4`.`ext_id` )" , false , '*i' , $corIDList );
		linkTablesIntoTreeDirect( $res , $tmpRes , 'ext_id' , 'addressee' );
		$tmpRes = $portalDB->simpleQuery( 'marks-objects' , array( 'ext_id' => $corIDList , 'ext_type' => 'correspondence' ) );
		linkTablesIntoTreeDirect( $res , $tmpRes , 'ext_id' , 'marks' );
	}

	if ( $UserID == 10000 ) {
		fixTimerData( "pp1" );
	}

	$query =
		"select ".
			"`t2`.`id` ,".
			"`t1`.`ext_id` as `cor_id` ,".
			"`t3`.`value` ,".
			"`t2`.`state` ,".
			"`t2`.`state-date` ".
		"from ".
			"`correspondence-target` as `t1` , ".
			"`correspondence-reply` as `t2` , ".
			"`agent-contacts` as `t3` ".
		"where ".
			"( `t1`.`ext_id` in ( ?* ) ) and ".
			"( `t2`.`ext_id` = `t1`.`id` ) and ".
			"( `t2`.`contact` = `t3`.`id` )" ;
	$corContacts = $portalDB->query( $query , "id" , "*i" , $corIDList );

	$corContactsMap = remap( $corContacts , "cor_id" );

	$opName = "correspondence.".$currViewName ;

	$corFiles = $portalDB->query( "select * from `documents` force index ( `ext_type_ext_id` ) where ( `ext_type` = ? ) and ( `ext_id` in ( ?* ) )" , "id" , "s*i" , "correspondence" , $corIDList );
	$corFilesMap = remap( $corFiles , "ext_id" );


	/*print_r_html( $docUploadRedirectParams );
	print_r_html( $_SERVER[ 'QUERY_STRING' ] );*/
	$docUploadRedirectParams = http_build_query( $docUploadRedirectParams );
	//print_r_html( $docUploadRedirectParams );

	MainHead_L2( "" , "" ,
		array(
			"../%UT/buttons.css" ,
			"%UT/correspondence.css"
		) ,
		array( "#
			$.tmpl = ".( isset( $UserOptions[ $opName ] ) && isValidJSON( $UserOptions[ $opName ][ "op_value" ] ) ? $UserOptions[ $opName ][ "op_value" ] : "[]" )." ;
			$.tmplVar = [ { k : \"cd\" , v : \"".date( "d-m-Y" )."\" , \"d\" : \"Текущая дата\" } , { k : \"cdT\" , v : \"".date( "d-m-Y" , time() + 86400 )."\" , \"d\" : \"Завтрашняя дата\" } , { k : \"cdY\" , v : \"".date( "d-m-Y" , time() - 86400 )."\" , \"d\" : \"Вчерашняя дата\" } ];
			$.tmplTargets = [ \"nrr-name\" , \"nrr-desc\" , \"nrr-ext-num\" ];
			$.tmplUpdateURL = \"".$currViewName."\" ;
			" ,
			"files/correspondence.js" ,
			"/ext-lib/pdf.js/build/pdf.js" ,
			"/ext-lib/pdf.js/build/pdf.worker.js" ,
			'@/files/labeling/brother--ql-570/main.js'
		)
	);

	/*<div class=\"tools-panel\">
						<div class=\"rp\">
							<div id=\"rpaa\" class=\"rpaa\" onclick=\"showRP()\">Параметры отчета</div>
							<div id=\"rpa\" class=\"rpa\" style=\"display : none\">
								<div class=\"hr\"></div>
								<table class=\"rpt\" align=\"center\">
									<tr>
										<td valign=\"top\" class=\"rptcl\">
											<div><input id=\"i_view_state__unchecked\" name=\"i_view_state[]\" type=\"checkbox\" value=\"unchecked\" ".( in_array( "unchecked" , $viewStateParam ) ? "checked" : "" )."> Показать неоплаченные</div>
											<div><input id=\"i_view_state__checked\" name=\"i_view_state[]\" type=\"checkbox\" value=\"checked\" ".( in_array( "checked" , $viewStateParam ) ? "checked" : "" )."> Показать оплаченные</div>
										</td>
										<td valign=\"top\" class=\"rptcc\">" ;
											foreach( $caseCategory as $cci => $ccn ) {
												echo "<div><input id=\"i_case_category__".$cci."\" name=\"i_case_category[]\" type=\"checkbox\" value=\"".$cci."\"".( in_array( $cci , $caseCategoryIndexes ) ? " checked" : "" )."> ".$ccn[ 1 ]."</div>" ;
											}
										echo "</td>
										<td valign=\"top\" class=\"rptcc\">
											<div>Год окончания</div>" ;
											for ( $i = $dateRange[ "mad" ] ; $i >= $dateRange[ "mid" ] ; $i-- ) {
												echo "<div><input id=\"i_show_year_".$i."\" name=\"i_show_year[]\" type=\"checkbox\" value=\"".$i."\"".( in_array( $i , $showYears ) ? " checked" : "" )."> ".$i."</div>" ;
											}
										echo "</td>
										<td valign=\"top\" class=\"rptcc\">
											Группировать <select id=\"i_group_by\" name=\"i_group_by\">" ;
												foreach( $GTKs as $k => $v ) {
													echo "<option value=\"".$k."\" ".( $k == $groupBy ? "selected" : "" ).">".$v[ 0 ]."</option>" ;
												}
											echo "</select>
										</td>
										<td valign=\"top\" class=\"rptcr\">
											Фильтры столбцов:" ;
											if ( isset( $colFlt ) && count( $colFlt ) > 0 ) {
												echo implode( $colFlt );
											}
										echo "</td>
									</tr>
								</table>
								<div class=\"hr\"></div>
								<table class=\"rpt\">
									<tr>" ;
										for( $i = 0 ; $i < count( $wt ) ; $i++ ) {
											echo "<td valign=\"top\" class=\"".( $i == 0 ? "rptcl" : ( $i < count( $wt ) - 1 ) ? "rptcc" : "rptcr" )."\">".implode( "<br>" , $wt[ $i ] )."</td>" ;
										}
										//unset( $wtc );
									echo "</tr>
								</table>
								<div class=\"hr\"></div>
								<center><input name=\"applyFilters\" type=\"submit\" value=\"Применить фильтры\" class=\"btn3\"> | <button name=\"genRTF\" type=\"submit\" class=\"btn3\"><div class=\"def-file-icon-doc\"></div>Скачать RTF</button> <button name=\"genCSV\" type=\"submit\" class=\"btn3\"><div class=\"def-file-icon-xls\"></div>Скачать CSV</button></center>
							</div>
						</div>
					</div>*/

	echo '<div>
		<form action="correspondence.php?view='.$currViewName.'" method="post">
			<div id="marks-flt-panel">
				'.Marks\integrate( array( $dbConfig[ CFG_MARK_GROUP_CORRESPONDENCE_PREFX.'.'.$currViewName ] ) , array() , $marks ).'
				<input type="submit" name="do-marks-filter" value="Фильтровать" class="btn3">
			</div>
		</form>
	</div>' ;

	if ( $currViewName != 'any' ) {
		$options = array();
	} else {
		$options = array( 'paginate' => false , 'show-toolbar' => false );
	}

	$lastCorrType = -1 ;
	$corrListByTypes = array();
	$currCorrListOfType = false ;
	foreach( $res as $corRow ) {
		if ( $corRow[ 'type' ] != $lastCorrType ) {
			if ( $lastCorrType != -1 ) {
				$corrListByTypes[]= $currCorrListOfType ;
			}
			$currCorrListOfType = array();
			$lastCorrType = $corRow[ 'type' ];
		}
		$currCorrListOfType[]= $corRow ;
	}
	if ( $currCorrListOfType !== false ) {
		$corrListByTypes[]= $currCorrListOfType ;
		foreach( $corrListByTypes as $currCorrListOfType ) {
			$cvn = Correspondence\getCorrViewName( $currCorrListOfType[ 0 ][ 'type' ] );
			Correspondence\showCorrespondencesOnlyView ( $cvn , $currCorrListOfType , $options );
		}
	} else {
		if ( $currViewName != 'any' ) {
			Correspondence\showCorrespondencesOnlyView ( $currViewName , array() , $options );
		}
	}


	echo '<div id="nrr-dlg" class="nrr-dlg-wrapper" style="display : none ;">
	<div class="nrr-dlg">
		<div class="nrr-dlg-cap">Добавить<div class="nrr-dlg-close-btn" onclick="hideAddNRRDlg();"></div></div>
		<div class="nrr-coll">
			<table class="nrr-t">
				<tr>
					<td colspan="2" class="nrr-t-v">
						Тип документа <select id="nrr-type" class="nrr-type" disabled="disabled">'.makeSimpleSelectTagOptions( $corTypes , "description" , "id" , $currViewCfg[ "type" ] ).'</select>
					</td>
				</tr>
				<tr>
					<td class="nrr-t-d">
						Номер и дата регистрации
					</td>
					<td class="nrr-t-d">
						Исходящий номер и дата
					</td>
				</tr>
				<tr>
					<td class="nrr-t-v">
						№ <span id="nrr-num">*</span> от <input type="text" id="nrr-date" class="nrr-date" value="'.date( "d-m-Y" , time() ).'">
					</td>
					<td class="nrr-t-v nrr-ext-num">
						<input type="text" id="nrr-ext-num" class="nrr-num" value="">
						<input type="text" id="nrr-ext-date" class="nrr-date" value="'.date( "d-m-Y" , time() ).'" placeholder="дд-мм-гггг">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="nrr-t-v">
						Название документа <input type="text" id="nrr-name" name="nrr_name" class="nrr-name">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="nrr-descr-c">
						Краткое описание <textarea id="nrr-desc" name="nrr_desc" class="nrr-descr"></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="nrr-t-v">
						Кол-во страниц <input type="text" id="nrr-page-num" class="nrr-page-num" value="" placeholder="&ndash;">
						Кол-во приложений <input type="text" id="nrr-att-num" class="nrr-page-num" value="" placeholder="&ndash;">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="nrr-t-v">
						<div class="nrr-addressee-list-label">
							Адресаты
						</div>
						<div id="nrr-addressee-list-area" class="nrr-addressee-list-area">
							<table id="nrr-addressee-list-tab" class="nrr-addressee-list-tab"></table>
						</div>
						<div class="nrr-addressee-list-btn">
							<!-- <a class="btn1" onclick="doAddressee( 1 )"><div class="nrr-act-abp"></div> адресат</a> -->
						</div>
					</td>
				</tr>
				<!-- <tr>
					<td></td>
					<td></td>
				</tr> -->
				<tr>
					<td colspan="2" class="nrr-m-c">
						<div class="nrr-marks-area">
							<div class="nrr-mark-add-btn">
								<!-- <div class="nrr-mark-add-btn-img"></div><br> -->
								<div class="nrr-mark-add-lst">'.$corMarksList.'</div>
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class="nrr-colc">
			<table class="nrr-t">
				<tr>
					<td class="nrr-t-v">
						'.$from_type_of_agency.'
					</td>
				</tr>
				<tr>
					<td class="nrr-toa-c">
						<textarea id="nrr-from-agency" name="nrr_from_agency" class="nrr-from-agency" onkeyup="srch( \'nrr-agency-sel\' , \'nrr-from-agency\' )"></textarea>
					</td>
				</tr>
				<tr>
					<td class="nrr-a-c">
						<div class="nrr-a-c1">
							<div class="nrr-a-c2">
								<select id="nrr-agency-sel" size="2" class="nrr-agency-sel" onchange="agency_select( \'nrr-agency-sel\' , \'nrr-from-agency\' , \'nrr-agent-sel\' , true )" onclick="agency_select( \'nrr-agency-sel\' , \'nrr-from-agency\' , \'nrr-agent-sel\' , true )"></select>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="nrr-toa-c">
						<textarea id="nrr-from-agent" name="nrr_from_agent" class="nrr-from-agency" onkeyup="srch( \'nrr-agent-sel\' , \'nrr-from-agent\' )"></textarea>
					</td>
				</tr>
				<tr>
					<td class="nrr-a-c">
						<div class="nrr-a-c1">
							<div class="nrr-a-c2">
								<select id="nrr-agent-sel" size="2" class="nrr-agency-sel" onchange="agent_select()" onclick="agent_select()"></select>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="nrr-t-v">
						<div class="nrr-addressee-contacts-label">
							Контакты
						</div>
						<div class="nrr-addressee-contacts-area">
							<table id="nrr-addressee-contacts-tab" class="nrr-addressee-contacts-tab"></table>
						</div>
						<div class="nrr-addressee-contacts-btn">
							<a class="btn1" onclick="doAddContact( 1 )"><div class="nrr-act-abp"></div> адрес</a>
							<a class="btn1" onclick="doAddContact( 2 )"><div class="nrr-act-abp"></div> e-mail</a>
							<a class="btn1" onclick="doAddContact( 3 )"><div class="nrr-act-abp"></div> тел./факс</a>
							<a class="btn1" onclick="doAddContact( 4 )"><div class="nrr-act-abp"></div> мобильный</a>
							<a class="btn1" onclick="doAddContact( 5 )"><div class="nrr-act-abp"></div> на руки</a>
							<a class="btn1" onclick="doAddAddressee()">Сохранить адресата</a>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class="nrr-colr">
			<div class="nrr-elt-cap">Эксперты</div>
			<div class="nrr-elt-area">'.$expList.'</div>
		</div>
		<div class="nrr-tool-panel">
			<a id="nrr-lnk-ok" class="nrr-lnk lnk-ok">Принять</a>
			<a onclick="hideAddNRRDlg();" class="nrr-lnk lnk-cancel">Отмена</a>
		</div>
	</div></div>' ;

	echo '<div id="fu-dlg" class="fu-dlg-wrapper" style="display : none ;"><div id="fu-dlg-bg" class="fu-dlg-bg"></div>
		<div class="fu-dlg">
			<div class="fu-dlg-cap">Прикрепить файл<div class="fu-dlg-close-btn" onclick="hideFUDlg();"></div></div>
			<div class="fu-tabs">
				<input id="fu-tab-1" name="fu-tabs" type="radio" checked>
				<input id="fu-tab-2" name="fu-tabs" type="radio">
				<label for="fu-tab-1">С сервера</label><label for="fu-tab-2">С компьютера</label>
				<span></span>
				<div>
					<div class="fu-tla">
						<div id="fu-tlal" class="fu-tlal" style="display : none ;"></div>
						<div id="fu-tl" class="fu-tl">' ;
					echo "</div>
					</div>" ;

					echo '<div class="fu-pdf-pa">
						<div id="fu-ppa" class="fu-pdf-pw"><div id="fu-pa-sizer" class="fu-pa-sizer"></div></div>
					</div>' ;

					$docTypeOptions = listToOptions( $currViewCfg[ "doc-types" ] );

					echo '
				</div>
				<div class="fu-file-select-area">
					<div>
						<!-- <form id="fu-file-select-form" action="./correspondence.file.php?view='.$currViewName.'" method="post" enctype="multipart/form-data">
							<input id="fu-cor-id" name="fu_cor_id" type="hidden">
							<input name="fu_file" type="file" class="fu-file">
						</form> -->
						<form id="fu-file-select-form" action="https://'.$dbConfig[ 'engine.addresses.docs.local' ].'/upload-new.manual.php" method="post" enctype="multipart/form-data">
							<input id="fu-cor-id" name="extId" type="hidden">
							<input id="fu-cor-y" name="reg_y" type="hidden">
							<input id="fu-cor-n" name="reg_n" type="hidden">
							<select name="docType">
								'.$docTypeOptions.'
							</select>
							<input name="redirect" type="hidden" value="https://'.$dbConfig[ 'engine.addresses.base' ].'/maindb/correspondence.php?view='.$currViewName.( $docUploadRedirectParams != '' ? '&' : '' ).$docUploadRedirectParams.'">
							<input name="uf" type="file" class="fu-file">
						</form>
					</div>
				</div>
			</div>
			<div class="fu-toolbar">
				<a id="fu-attache-btn" class="btn3">Прикрепить</a>
			</div>
		</div>
	</div>' ;

	echo '<div id="letter_dlg" class="letter-dlg" style="display : none ;">
		<input type="checkbox" id="letter-dlg--show-hide-at-addr" style="display : none ;"/>
		<div class="letter-dlg-close-box"><div onclick="hideLetterDlg();" title="Закрыть" class="dlg-close-box-btn"></div></div>
		<div class="letter-dlg-cont">
			<table id="letter_dlg_tab" class="letter-dlg-tab">
			</table>
			<div class="letter-dlg-ex">
				Вес <input id="new-weight" type="text" class="weight-inp" onkeyup="changeWeight()"> заказное <input id="new-letter-type" type="checkbox" onchange="changeWeight()"> =&gt; <input id="new-price" type="text" class="price-inp">
				<label for="letter-dlg--show-hide-at-addr"></label>
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


	if ( isset( $_REQUEST[ "error" ] ) ) {
		echo '<div class="error-dlg-wrapper">
			<div class="error-dlg"></div>
		</div>' ;
	}

	//fixTimerData( "core" );
	closeHtml();
	