<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	use Marks\updateMarks;

	include_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserThemeLoc
	 * @var $UserID
	 * @var $dbConfig
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );
	require_once( '../cores/data-bank.php' );
	require_once( "../barcode.php" );
	require_once( "../documents.core.php" );
	require_once( '../shared/share.maindb.php' );
	require_once( '../doc-generator/doc-generator.core.php' );


	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	$mayWOEAdd = false ;
	$mayWOEEdit = false ;
	$mayWOEShow = false ;

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "PAYMENTS" , $Rights ) ) {
			$mayWOEAdd = in_array( "WOE-ADD" , $Rights[ "PAYMENTS" ] );
			$mayWOEEdit = in_array( "WOE-EDIT" , $Rights[ "PAYMENTS" ] );
			if (
				( isset( $_REQUEST[ "edit" ] ) && $mayWOEEdit ) ||
				( isset( $_REQUEST[ "create" ] ) && $mayWOEAdd ) ||
				isset( $_REQUEST[ "show" ] ) ||
				( isset( $_REQUEST[ "save" ] ) && isset( $_REQUEST[ "mode" ] ) &&
					( $_REQUEST[ "mode" ] == "create" && $mayWOEAdd ) ||
					( $_REQUEST[ "mode" ] == "edit" && $mayWOEEdit )
				) ||
				( isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" )
			) {
				$GoOut = false ;
			} else {
				$GoOut = true ;
			}
		} else {
			$mayWOEAdd = false ;
			$mayWOEEdit = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	function getOut() {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}

	if ( $GoOut ) {
		getOut();
	}

	if ( isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ) {
		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;
		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ "data" ] );

		$data = $DD->documentElement ;

		switch ( $data->nodeName ) {
			case "get-payments" :
				$pid = intval( $data->getAttribute( "id" ) );
				$payerData = $portalDB->row( "select * from `writ-of-execution-payers` where `id` = ?" , "i" , $pid );

				if ( $payerData === false ) {
					exit();
				}

				$res = $portalDB->query( "select `t1`.* , `t2`.`name` as `from_agency` from `writ-of-execution-payments` as `t1` , `agency` as `t2` where ( `t1`.`from_agency` = `t2`.`id` ) and ( `t1`.`ext_id` = ? ) and ( `t1`.`deleted` = 0 ) order by `date` desc" , false , "i" , $pid );

				$totalPayd = 0.0 ;
				if ( $res === false ) {
					exit();
				}

				foreach( $res as &$r ) {
					$totalPayd+= $r[ "price" ];
					$r = "<item id=\"".$r[ "id" ]."\" d=\"".date( "d.m.y" , $r[ "date" ] )."\" p=\"".money_format( "%!i" , $r[ "price" ] )."\" n=\"".$r[ "num" ]."\">".toCDATA( $r[ "from_agency" ] )."</item>" ;
				} unset( $r );
				$res = implode( "" , $res );

				echo "<result p=\"" , money_format( "%!i" , $totalPayd ) , "\"><payer>".toCDATA( $payerData[ "payer" ] )."</payer><payments>" , $res , "</payments></result>" ;
				break ;



			case "add-payment" :
				$pid = intval( $data->getAttribute( "pid" ) );
				$d = trim( $data->getAttribute( "d" ) );

				$m = array();
				$n = preg_match( '/^\s*([0-2]\d|3[01])[-.,](0\d|1[0-2])[-.,](\d{4})\s*$/' , $d , $m );
				if ( $n != 1 ) {
					echo "<result state=\"err.date\" />" ;
					exit();
				}
				list( , $dd , $dm , $dy ) = $m ;

				$p = floatval( str_replace( "," , "." , $data->getAttribute( "p" ) ) );

				$n = intval( $data->getAttribute( "n" ) );
				$a = iconv( "utf8" , "cp1251" , $data->nodeValue );
				$a = trim( $a );
				$t = intval( $data->getAttribute( "t" ) );
				$ar = $portalDB->row( "select * from `agency` where `name` = ?" , "i" , $a );
				if ( $ar !== false ) {
					$a_id = $ar[ "id" ];
				} else {
					$ar = $portalDB->row( "select `id` from `agency` where `ext_id` is null limit 1 ;" );
					$a_id = $ar[ "id" ];
					$portalDB->noResult( "update `agency` set `ext_id` = ? , `name` = ? , `_fr` = 1 where `id` = ?" , "isi" , $t , $a , $a_id );
					$portalDB->noResult( "insert into `agency` ( `ext_id` ) values ( null )" );
				}

				$payerData = $portalDB->noResult(
					"insert into `writ-of-execution-payments` ( `ext_id` , `date` , `price` , `num` , `from_agency` ) value ( ? , ? , ? , ? , ? )" ,
					"iidsi" , $pid , mktime( 0 , 0 , 0 , $dm , $dd , $dy ) , $p , $n , $a_id );

				echo "<result state=\"ok\" />" ;
				break ;

			case "del-payment" :
				$pid = intval( $data->getAttribute( "pid" ) );
				$portalDB->noResult( "update `writ-of-execution-payments` set `deleted` = 1 where `id` = ?" , "i" , $pid );
				echo "<result state=\"ok\" />" ;
				break ;

			case "close" :
			case "unclose" :
				//$woeID = intval( $data->getAttribute( "id" ) );
				//$portalDB->noResult( "update `writ-of-execution` set `state` = ? , `state_date` = ? where `id` = ?" , "iii" , $data->nodeName == "close" ? 1 : 0 , time() , $woeID );
				$portalDB->updateRow( "writ-of-execution" ,
					array(
						"state" => $data->nodeName == "close" ? 1 : 0 ,
						"state_date" => time() ,
						"id" => intval( $data->getAttribute( "id" ) )
					)
				);
				echo "<result state=\"ok\" />" ;
				break ;

			case "copy" :
				$woeID = intval( $data->getAttribute( "id" ) );
				$nwoeNum = trim( iconv( "utf8" , "cp1251" , $data->nodeValue ) );
				$m = array();
				$n = preg_match( '/^\s*([А-Я]{2})\s*№\s*(\d{9})\s*$/i' , $nwoeNum , $m );
				$mNew = array();
				$nNew = preg_match( '/^\s*(\d{2}RS\d{4}#\d{1,2}-\d{1,5}\/\d{4}#\d)\s*$/i' , $nwoeNum , $mNew );
				$nwoeNum = $n == 1 ? strtoupper( $m[ 1 ] )." № ".$m[ 2 ] : ( $nNew == 1 ? $mNew[ 1 ] : false );


				$portalDB->noResult(
					"insert into `writ-of-execution` ( `ext_id` , `num` , `date` , `case_num` , `issue_date` , `date_force` , `incoming_date` , `from_agent` , `state` , `state_date` , `comment` , `agency_addr` , `ep_num` ) select `ext_id` , `num` , `date` , `case_num` , `issue_date` , `date_force` , `incoming_date` , `from_agent` , `state` , `state_date` , `comment` , `agency_addr` , `ep_num` from `writ-of-execution` where `id` = ?" ,
					"i" , $woeID
				);
				$nwoeID = $portalDB->lastInsertID();
				//$portalDB->noResult( "update `writ-of-execution` set `num` = ? where `id` = ?" , "si" , $nwoeNum , $nwoeID );
				$portalDB->updateRow( "writ-of-execution" , array( "num" => $nwoeNum , "id" => $nwoeID ) );
				echo "<result state=\"ok\" id=\"".$nwoeID."\"/>" ;
				break ;

			case "getWOEData" :
				$woeID = intval( $data->getAttribute( "id" ) );
				$woe = $portalDB->row( "select * from `writ-of-execution` where `id` = ?" , "i" , $woeID );
				if ( $woe !== false ) {
					echo "<result state=\"ok\" caseNum=\"".$woe[ "case_num" ]."\">" ;
					$pd = $portalDB->query( "select * from `writ-of-execution-payers` where ( `ext_id` = ? )" , false , "i" , $woeID );
					foreach ( $pd as $p ) {
						$payd = $portalDB->row( "select ifnull( sum( round( round( `price` , 2 ) * 100 ) ) , 0 ) as `payd` from `writ-of-execution-payments` where ( `ext_id` = ? ) and ( `deleted` = 0 )" , "i" , $p[ "id" ] );
						echo "<payer price=\"".money_format( "%!i" , $p[ "price" ] )."\" payd=\"".money_format( "%!i" , round( $payd[ "payd" ] / 100 , 2 ) )."\">".toCDATA( $p[ "payer" ] )."</payer>" ;
					}
					echo "</result>" ;
				} else {
					//echo "<result state=\"ok\" caseNum=\"".$woe[ "case_num" ]."\" />" ;
				}
				break ;

			case "delete" ;
				$woeID = intval( $data->getAttribute( "id" ) );
				$payersIDL = $portalDB->query( "select `id` from `writ-of-execution-payers` where `ext_id` = ?" , false , "i" , $woeID );

				if ( $payersIDL !== false && count( $payersIDL ) > 0 ) {
					foreach( $payersIDL as &$pd ) {
						$pd = $pd[ "id" ];
					} unset( $pd );

					$portalDB->noResult( "delete from `writ-of-execution-payments` where `ext_id` in ( ?* )" , "*i" , $payersIDL );
					$portalDB->noResult( "delete from `writ-of-execution-payers` where `ext_id` = ?" , "i" , $woeID );
				}

				$portalDB->noResult( "delete from `writ-of-execution` where `id` = ?" , "i" , $woeID );

				echo "<result state=\"ok\" />" ;
				break ;
		}

		exit();
	}

	if ( isset( $_REQUEST[ "save" ] ) && isset( $_REQUEST[ "mode" ] ) && in_array( $_REQUEST[ "mode" ] , array( "create" , "edit" ) ) ) {
		//print_r_html( $_REQUEST );
		if ( isset( $_REQUEST[ "i_case_num" ] ) ) {
			$v_case_num = trim( $_REQUEST[ "i_case_num" ] );
		} else {
			$v_case_num = "" ;
		}

		/**
		 * @var $v_issue_date
		 * @var $v_incoming_date
		 * @var $v_date_force
		 * @var $v_date
		 */

		function chkDate( $dn ) {
			if ( isset( $_REQUEST[ "i_".$dn ] ) && strlen( trim( $_REQUEST[ "i_".$dn ] ) ) > 0 ) {
				$m = array();
				if ( preg_match( '/^\s*([0-2]\d|3[01])[-.,](0\d|1[0-2])[-.,](\d{4})\s*$/' , $_REQUEST[ "i_".$dn ] , $m ) == 1 ) {
					list( , $date_d , $date_m , $date_y ) = $m ;
					$dt = intval( date( "t" , mktime( 0 , 0 , 0 , $date_m , 1 , $date_y ) ) );
					if ( $date_d > 0 && $date_d <= $dt ) {
						$d = mktime( 0 , 0 , 0 , $date_m , $date_d , $date_y );
					} else {
						return false ;
					}
				} else {
					return false ;
				}
			} else {
				$d = 0 ;
			}

			$GLOBALS[ "v_".$dn ] = $d ;
			return true ;
		}

		if ( !chkDate( "date" ) ) {
			getOut();
		}
		if ( !chkDate( "issue_date" ) ) {
			getOut();
		}
		if ( !chkDate( "incoming_date" ) ) {
			getOut();
		}
		if ( !chkDate( "date_force" ) ) {
			getOut();
		}

		if ( isset( $_REQUEST[ "i_num" ] ) ) {
			$m = array();
			$n = preg_match( '/^\s*([А-Я]{2})\s*№\s*(\d{9})\s*$/i' , $_REQUEST[ "i_num" ] , $m );
			$mNew = array();
			$nNew = preg_match( '/^\s*(\d{2}RS\d{4}#\d{1,2}-\d{1,5}\/\d{4}#\d)\s*$/i' , $_REQUEST[ "i_num" ] , $mNew );
			$v_num = $n == 1 ? strtoupper( $m[ 1 ] )." № ".$m[ 2 ] : ( $nNew == 1 ? $mNew[ 1 ] : false );
			if ( $v_num === false ) {
				getOut();
			}
		} else {
			getOut();
		}

		if ( isset( $_REQUEST[ "i_ep_num" ] ) ) {
			$v_ep_num = trim( $_REQUEST[ "i_ep_num" ] );
		} else {
			$v_ep_num = "" ;
		}

		$sad = storeAgentData( $portalDB , 1 , $_REQUEST[ "i_agency_ta" ] , $_REQUEST[ "i_agent_ta" ] );
		$portalDB->updateRow( "agency" , array( "destination" => clearText( $_REQUEST[ "i_agency_addr_ta" ] ) , "id" => $sad[ "agency.id" ] ) );

		/*$v_agency = trim( $_REQUEST[ "i_agency_ta" ] );
		$ar = $portalDB->row( "select * from `agency` where `name` = ?" , "s" , $v_agency );
		if ( $ar !== false ) {
			$v_agency_id = $ar[ "id" ];
		} else {
			$ar = $portalDB->row( "select `id` from `agency` where `ext_id` is null limit 1" );
			$v_agency_id = $ar[ "id" ];
			$q = "update
					`agency`
				set
					`ext_id` = 1 ,
					`name`   = ".Str2SQL( $v_agency )." ,
					`destination` = ".Str2SQL( trim( $_REQUEST[ "i_agency_addr_ta" ] ) )." ,
					`_fr`    = 1
				where
					`id`     = ".$v_agency_id."
				limit 1 ;" ;
			$portalDB->noResult( $q );
			$portalDB->noResult( "insert into `agency` ( `ext_id` ) values ( null );" );
		}

		$v_agent = trim( $_REQUEST[ "i_agent_ta" ] );
		$ar = RowAsArray( $con , "select `id` from `agent` where ( `name` = ".Str2SQL( $v_agent )." ) and ( `ext_id` = ".$v_agency_id." );" );
		If ( $ar === false ) {
			$ar = RowAsArray( $con , "select `id` from `agent` where `ext_id` is null limit 1 ;" );
			$v_agent_id = $ar[ "id" ];
			$q = "update
					`agent`
				set
					`ext_id` = ".$v_agency_id." ,
					`name`   = ".Str2SQL( $v_agent )." ,
					`_fr`    = 1
				where
					`id`     = ".$v_agent_id."
					limit 1;" ;
			NoResultQuery( $con , $q );
			NoResultQuery( $con , "insert into `agent` ( `ext_id` ) values ( null );" );
		} else {
			$v_agent_id = $ar[ "id" ];
		}*/

		$v_agent_id = $sad[ "agent.id" ];

		switch ( $_REQUEST[ "mode" ] ) {
			case "create" :
				$np = array();
				if ( isset( $_REQUEST[ "i_new_payers_name" ] ) && isset( $_REQUEST[ "i_new_prices" ] ) ) {
					$inpn = $_REQUEST[ "i_new_payers_name" ];
					$inp  = $_REQUEST[ "i_new_prices" ];
					foreach( $inpn as $i => $n ) {
						$n = trim( $n );
						if ( strlen( $n ) > 0 && isset( $inp[ $i ] ) && isValidFloat( $inp[ $i ] , 2 , false ) ) {
							$np[]= Str2SQL( $n )." , ".Float2SQL( $inp[ $i ] );
						} else {
							getOut();
						}
					}
				}

				//NoResultQuery( $con , "insert into `writ_of_execution` ( `ext_id` , `num` , `date` , `issue_date` , `incoming_date` , `case_num` , `from_agent` , `state` , `agency_addr` , `ep_num` ) values ( ".Int2SQL( $_REQUEST[ "save" ] )." , ".Str2SQL( $v_num )." , ".Int2SQL( $v_date )." , ".Int2SQL( $v_issue_date )." , ".Int2SQL( $v_incoming_date )." , ".Str2SQL( $v_case_num )." , ".Int2SQL( $v_agent_id )." , 0 , ".Str2SQL( trim( $_REQUEST[ "i_agency_addr_ta" ] ) )." , ".Str2SQL( $v_ep_num )." )" );

				$portalDB->insertRow( "writ-of-execution" , array(
					"ext_id" => $_REQUEST[ "save" ] ,
					"num" => $v_num ,
					"date" => $v_date ,
					"issue_date" => $v_issue_date ,
					"date_force" => $v_date_force ,
					"incoming_date" => $v_incoming_date ,
					"case_num" => $v_case_num ,
					"from_agent" => $v_agent_id ,
					"state" => 0 ,
					"agency_addr" => clearText( $_REQUEST[ "i_agency_addr_ta" ] ) ,
					"ep_num" => $v_ep_num
				) );
				$woeID = $portalDB->lastInsertID();

				if ( count( $np ) > 0 ) {
					foreach( $np as &$d ) {
						$d = $woeID." , ".$d ;
					} unset( $d );
					$portalDB->noResult( "insert into `writ-of-execution-payers` ( `ext_id` , `payer` , `price` ) values ( ".implode( " ),( " , $np )." )" );
				}

				break ;

			case "edit" :
				$woeID = intval( $_REQUEST[ "save" ] );

				$np = array();
				if ( isset( $_REQUEST[ "i_new_payers_name" ] ) && isset( $_REQUEST[ "i_new_prices" ] ) ) {
					$inpn = $_REQUEST[ "i_new_payers_name" ];
					$inp  = $_REQUEST[ "i_new_prices" ];
					foreach( $inpn as $i => $n ) {
						$n = trim( $n );
						if ( strlen( $n ) > 0 && isset( $inp[ $i ] ) && isValidFloat( $inp[ $i ] , 2 , false ) ) {
							$np[]= Str2SQL( $n )." , ".Float2SQL( $inp[ $i ] );
						} else {
							getOut();
						}
					}
				}

				if ( count( $np ) > 0 ) {
					foreach( $np as &$d ) {
						$d = $woeID." , ".$d ;
					} unset( $d );
					$portalDB->noResult( "insert into `writ-of-execution-payers` ( `ext_id` , `payer` , `price` ) values ( ".implode( " ),( " , $np )." )" );
				}

				$payersData = $portalDB->query( "select `t1`.* , count( `t2`.`id` ) as `pc` from `writ-of-execution-payers` as `t1` left join `writ-of-execution-payments` as `t2` on ( `t2`.`ext_id` = `t1`.`id` ) where ( `t1`.`ext_id` = ? ) group by `t1`.`id`" , "id" , "i" , $woeID );
				$np = array( "names" => array() , "prices" => array() , "where" => array() );
				if ( isset( $_REQUEST[ "i_payers_name" ] ) && isset( $_REQUEST[ "i_prices" ] ) ) {
					$inpn = $_REQUEST[ "i_payers_name" ];
					$inp  = $_REQUEST[ "i_prices" ];
					foreach( $inpn as $i => $n ) {
						if ( $payersData[ $i ][ "pc" ] == 0 ) {
							$n = trim( $n );
							if ( strlen( $n ) > 0 && isset( $inp[ $i ] ) && isValidFloat( $inp[ $i ] , 2 , false ) ) {
								$np[ "names" ][]= "when `id` = ".Int2SQL( $i )." then ".Str2SQL( $n );
								$np[ "prices" ][]= "when `id` = ".Int2SQL( $i )." then ".Float2SQL( $inp[ $i ] );
								$np[ "where" ][]= Int2SQL( $i );
							} else {
								getOut();
							}
						}
					}
				}

				if ( count( $np[ "where" ] ) > 0 ) {
					$portalDB->noResult( "update `writ-of-execution-payers` set `payer` = case ".implode( " " , $np[ "names" ] )." end , `price` = case ".implode( " " , $np[ "prices" ] )." end where `id` in ( ?* )" , "*i" , $np[ "where" ] );
				}

				$portalDB->updateRow( "writ-of-execution" , array(
					"num" => $v_num ,
					"date" => $v_date ,
					"issue_date" => $v_issue_date ,
					"date_force" => $v_date_force ,
					"incoming_date" => $v_incoming_date ,
					"case_num" => $v_case_num ,
					"ep_num" => $v_ep_num ,
					"from_agent" => $v_agent_id ,
					"agency_addr" => clearText( $_REQUEST[ "i_agency_addr_ta" ] ) ,
					"id" => $woeID
				) );
				break ;
		}

		if ( isset( $_REQUEST[ "i_marks" ] ) ) {
			$v_marks = $_REQUEST[ "i_marks" ];
		} else {
			$v_marks = array();
		}

		print_r_html( $v_marks );

		Marks\updateMarks( $woeID , "woe" , $v_marks );

		//MainHead_L2( "База" , "<a href=\"./\">База</a> - Исполнительный Лист" , array( "../%UT/buttons.css" , "%UT/writ-of-execution.css" ) , array( "files/writ-of-execution.js" , "#var closePage = true ;" ) , "hlp/main.html" );
		//closeHtml();
		exit();
	}

	if ( isset( $_REQUEST[ "create" ] ) ) {
		$mode = "create" ;

		$v_ext_id = getCharID( $_REQUEST[ "create" ] );
		if ( $v_ext_id === false ) {
			getOut();
		}

		$mat = $portalDB->simpleRow( "matincoming" , $v_ext_id );

		$woeAgent = $portalDB->simpleRow( "agent" , $mat[ "from_agent" ] );
		if ( $woeAgent === false ) {
			getOut();
		}

		$woeAgency = $portalDB->simpleRow( "agency" , $woeAgent[ "ext_id" ] );
		if ( $woeAgency === false ) {
			getOut();
		}

		$v_agency_addr = "" ;
		$v_agency_addr_int = $woeAgency[ "destination" ];
		$v_agency_id   = $woeAgency[ "id" ];
		$v_agency      = $woeAgency[ "name" ];

		$v_agent_id    = $woeAgent[ "id" ];
		$v_agent       = $woeAgent[ "name" ];

		$cy = date( "y" , time() ) ;

		$v_num           = "" ;
		$v_ep_num        = "" ;
		$v_date          = "" ;
		$v_issue_date    = "" ;
		$v_date_force    = "" ;
		$v_incoming_date = "" ;

		$v_case_num      = "" ;
		$v_payers        = array();
		$selectMarks = array();
	} else
	if ( isset( $_REQUEST[ "edit" ] ) || isset( $_REQUEST[ "show" ] ) ) {
		if ( isset( $_REQUEST[ "edit" ] ) ) {
			$mode = "edit" ;
			$woeID = intval( $_REQUEST[ "edit" ] );
		} else {
			$mode = "show" ;
			$woeID = intval( $_REQUEST[ "show" ] );
		}
		$woe = $portalDB->simpleRow( "writ-of-execution" , $woeID );
		if ( $woe === false ) {
			getOut();
		}

		$v_ext_id = "nc" ;

		$v_case_num = $woe[ "case_num" ];

		if ( $woe[ "date" ] > 0 ) {
			$v_date = date( "d-m-Y" , $woe[ "date" ] );
		} else {
			$v_date = "" ;
		}

		if ( $woe[ "issue_date" ] > 0 ) {
			$v_issue_date = date( "d-m-Y" , $woe[ "issue_date" ] );
		} else {
			$v_issue_date = "" ;
		}

		if ( $woe[ "date_force" ] > 0 ) {
			$v_date_force = date( "d-m-Y" , $woe[ "date_force" ] );
		} else {
			$v_date_force = "" ;
		}

		if ( $woe[ "incoming_date" ] > 0 ) {
			$v_incoming_date = date( "d-m-Y" , $woe[ "incoming_date" ] );
		} else {
			$v_incoming_date = "" ;
		}

		$woeAgent = $portalDB->simpleRow( "agent" , $woe[ "from_agent" ] );
		//var_dump_html( $woe );
		if ( $woeAgent === false ) {
			getOut();
		}

		$woeAgency = $portalDB->simpleRow( "agency" , $woeAgent[ "ext_id" ] );
		if ( $woeAgency === false ) {
			getOut();
		}

		$v_agency_addr = $woe[ "agency_addr" ];
		$v_agency_addr_int = $woeAgency[ "destination" ];
		$v_agency_id   = $woeAgency[ "id" ];
		$v_agency      = $woeAgency[ "name" ];

		$v_agent_id    = $woeAgent[ "id" ];
		$v_agent       = $woeAgent[ "name" ];

		$v_payers = $portalDB->query( "select `t1`.* , count( `t2`.`id` ) as `pc` from `writ-of-execution-payers` as `t1` left join `writ-of-execution-payments` as `t2` on ( `t2`.`ext_id` = `t1`.`id` ) where ( `t1`.`ext_id` = ? ) group by `t1`.`id`" , false , "i" , $woeID );

		$selectMarks = Marks\getMarks( $woeID , "woe" , false );
		$comments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = 'woe' ) and ( `ext_id` = ? );" , false , 'i' , $woeID );

		$v_num = $woe[ "num" ];
		$v_ep_num = $woe[ "ep_num" ];
		$v_state = $woe[ "state" ];
		$v_state_date = $woe[ "state_date" ];
	} else {
		getOut();
	}

	$modeVar = array(
		"create" => array(
			"btnName" => "Создать Исполнительный лист" ,
			"saveID" => ( isset( $v_ext_id ) ? $v_ext_id : false )
		) ,
		"edit" => array(
			"btnName" => "Заменить" ,
			"saveID" => ( isset( $woeID ) ? $woeID : false )
		) ,
		"show" => array(
			"btnName" => false ,
			"saveID" => false
		)
	);

	$cVar = $modeVar[ $mode ];

	if ( $cVar[ "btnName" ] !== false ) {
		$cVar[ "btnName" ] = "<input type=\"button\" value=\"".$cVar[ "btnName" ]."\" onclick=\"checkForm();\">" ;
	}

	if ( $cVar[ "saveID" ] !== false ) {
		$cVar[ "formOpen" ] = "<form id=\"woe_form\" method=\"post\" action=\"writ-of-execution.php?save=".$cVar[ "saveID" ]."&mode=".$mode."\">" ;
		$cVar[ "formClose" ] = "</form>" ;
	} else {
		$cVar[ "formOpen" ] = "" ;
		$cVar[ "formClose" ] = "" ;
	}

	if ( $mode == "show" ) {
	} else {
	}

	MainHead_L2( "База" , "<a href=\"./\">База</a> - Исполнительный Лист" ,
		array( "../%UT/buttons.css" , "%UT/writ-of-execution.css" , '/doc-generator/%UT/forms.css' ) ,
		array(
			( $mode == "show" ? "files/writ-of-execution.show.js" : "files/writ-of-execution.js" ) ,
			"#var agencyID = ".$v_agency_id." ; var agentID = ".$v_agent_id." ; var UserThemeLoc = \"".$UserThemeLoc."\" ;" ,
			'/ext-lib/pdf.js/build/pdf.js' ,
			'/ext-lib/pdf.js/build/pdf.worker.js' ,
			'/doc-generator/doc-generator.base.js' ,
			'/doc-generator/doc-generator.js'
		) , "hlp/main.html" );

	//print_r_html( $_REQUEST );

	echo "<div id=\"payer-menu\" class=\"payer-menu\" style=\"display : none ;\">
		<div id=\"pm-pn-label\" class=\"pm-pn-label\"></div>
		<div class=\"pm-pt-wrapper\"><table id=\"pm-pt\" align=\"center\" class=\"pm-pt\">
			<tr class=\"pm-pt-h-row\">
				<td class=\"pm-pt-h-t\"></td>
				<td class=\"pm-pt-h-d\">Дата ПП</td>
				<td class=\"pm-pt-h-pa\">Плательщик</td>
				<td class=\"pm-pt-h-p\">Сумма</td>
				<td class=\"pm-pt-h-n\">№ ПП</td>
			</tr>
		</table></div>
		<div class=\"pm-total\">Всего : <span id=\"pm-total-v\" class=\"pm-total-v\">0.00</span> руб.</div>
		<div><a id=\"pm-ap-lnk\" onclick=\"\" class=\"pm-ap-lnk\">Добавить оплату</a></div>
	</div>" ;

	$tabTypeOfAgency = $portalDB->table( "type-of-agency" );
	$from_type_of_agency = "<select id=\"ap-toa\" size=\"1\" class=\"ap-toa\" onchange=\"upd( 'ap_agency_sel' , 'ap-toa' )\">" ;
	foreach( $tabTypeOfAgency as $i ) {
		$from_type_of_agency.= "<option value=\"".$i[ "id" ]."\">".inForm( $i[ "name" ] , 1 , false )."</option>" ;
	}
	$from_type_of_agency.= "</select>" ;

	echo "<div id=\"add-payment-dlg\" class=\"add-payment-dlg\" style=\"display : none ;\">
		<table id=\"ap-t\" align=\"center\" class=\"ap-t\">
			<tr>
				<td class=\"ap-t-d\">Дата ПП</td><td class=\"ap-t-d\">Сумма</td><td class=\"ap-t-d\">№ ПП</td>
			</tr>
			<tr>
				<td class=\"ap-t-v\"><input type=\"text\" id=\"ap-date\" class=\"ap-date\"></td><td class=\"ap-t-v\"><input type=\"text\" id=\"ap-price\" class=\"ap-price\"></td><td class=\"ap-t-v\"><input type=\"text\" id=\"ap-num\" class=\"ap-num\"></td>
			</tr>
			<tr>
				<td colspan=\"3\">
					<div>
						".$from_type_of_agency."
					</div>
					<div>
						<textarea id=\"ap_from_agency\" name=\"ap_from_agency\" class=\"ap-from-agency\" onkeyup=\"srch( 'ap_agency_sel' , 'ap_from_agency' )\"></textarea><br>
						<div id=\"tcel1\">
							<select id=\"ap_agency_sel\" class=\"ap-agency-sel\" size=\"20\" onchange=\"agency_select( 'ap_agency_sel' , 'ap_from_agency' , '' )\" onclick=\"agency_select( 'ap_agency_sel' , 'ap_from_agency' , '' )\"></select>
						</div>
					</div>
				</td>
			</tr>
		</table>
		<div><a id=\"ap-lnk-ok\" onclick=\"\" class=\"ap-lnk lnk-ok\">Принять</a><a onclick=\"closeAPDlg();\" class=\"ap-lnk lnk-cancel\">Отмена</a></div>
	</div>" ;

	if ( $mode == "edit" ) {
		if ( $v_state != 1 ) {
			echo "<div class=\"woe-panel woe-panel-pos-1\">
				<a onclick=\"closeWOE( " , $cVar[ "saveID" ] , " );\" class=\"woe-close-lnk\">Закрыть И/Л</a>
			</div>" ;
		} else {
			echo "<div class=\"woe-state-label\">
				ЗАКРЫТ<br>
				<span class=\"woe-state-sub-label\">" , date( "d.m.Y" , $v_state_date ) , "</span><br>
				<a onclick=\"uncloseWOE( " , $cVar[ "saveID" ] , " );\" class=\"woe-unclose-lnk\">Отменить закрытие И/Л</a>
			</div>" ;
		}

		echo "<div class=\"woe-panel woe-panel-pos-2\">
			<a onclick=\"copyWOE( " , $cVar[ "saveID" ] , " )\" class=\"woe-copy-lnk\">Создать копию</a><br><br>
			<a onclick=\"deleteWOE( " , $cVar[ "saveID" ] , " )\" class=\"woe-delete-lnk\">Удалить</a>
		</div>" ;

			$expList = $portalDB->simpleRow( "matincoming" , $woe[ "ext_id" ] );
			$expListGroup = $expList[ "group_id" ];
			if ( is_null( $expListGroup ) || $expListGroup == 0 ) {
				$expList = array( $expList[ "id" ] => $expList );
			} else {
				$expList = $portalDB->simpleQuery( "matincoming" , array( "group_id" => $expListGroup ) , "id" );
			}

			$matKeys = array_keys( $expList );
			$expertizeList = $portalDB->query( "select `t3`.`id` from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t2`.`id` = `t3`.`ext_id` ) and ( `t2`.`mat_id` in ( ?* ) )" , "id" ,  "*s" , $matKeys );
			$expertizeIDList = array_keys( $expertizeList );
			$paymentsList = $portalDB->query( "select * from `payments` where ( `expertize_id` in ( ?* ) )" , "id" , "*i" , $expertizeIDList );
			$paymentsIDList = array_keys( $paymentsList );

		echo "<div class=\"woe-panel woe-panel-pos-3\">" ;
			foreach ( $expList as $cexp ) {
				echo "<div><a href=\"main.php?idlist=".$cexp[ "id" ]."\" target=\"_blank\">".matincomingNumber( $cexp[ "id" ] )." за ".matincomingYear( $cexp[ "id" ] )." год</a></div>" ;
			}
			echo "<div><a href=\"".getPaymentsAddr()."?idlist=".implode( "," , $paymentsIDList )."\" target=\"_blank\">оплата</a></div>" ;
		echo "</div>" ;
	}

	if ( $mode == "edit" || $mode == "show" ) {
		$dataBank = fillDataBank2(
			array(
				'req:id' => $woeID ,
				'tmpl-data' => false ,
				'tmpl-list-name' => 'woe'
			) , 2
		);

		$tabDocTemplates = $portalDB->query( "select * from `doc-templates` where ( `user_id` is null ) or ( `user_id` = ? ) order by `id`" , false , "i" , $UserID );

		echo '<div id="docs-panel"><div class="doc-generator-list-area">' ;
		foreach ( $tabDocTemplates as $dt ) {
			if ( checkFilter( $dt[ 'filter_rules' ] , $dataBank ) ) {
				echo '<a onclick="genDoc( '.$dt[ 'id' ].' , '.$woeID.' , \'preview\' )" class="doc-generator-link">'.$dt[ 'short_name' ].'</a>' ;
			}
		}
		echo '</div>' ;

		echo Documents\integrate(
			array( "ext_id" => $woeID , "ext_type" => "woe" ) ,
			array( "docs" => "ref" , "upload" => array( "ext_id" => $woeID , "ext_type" => "woe" ) , "show-icons" => 1 )
		);
		echo '</div>' ;
	}
	echo '<div class="woe-area-container">
		'.$cVar[ 'formOpen' ].'
			<div class="woe-panel woe-panel-pos-4">'
				.Marks\integrate( array( $dbConfig[ CFG_MARK_GROUP_WOE ] ) , array( 'mode' => ( ( $mode == 'edit' || $mode == 'create' ) ? 'edit' : 'show' ) , 'mark-name-attr' => 'i_marks' , 'checked-id-only' => false ) , $selectMarks ).
			'</div>
			<div class="woe-area">
					<div class="woe-case-num">
						Дело №
						<input name="i_case_num" id="i_case_num" type="text" value="'.$v_case_num.'" class="woe-case-num-v">
					</div>
					<div class="woe-dates-wrapper">
						<div class="woe-date">
							<div>
								<input name="i_date" id="i_date" type="text" value="'.$v_date.'" class="woe-date-v">
							</div>
							<div class="woe-date-label">
								(дата принятия судебного акта)
							</div>
						</div>
						<div class="woe-date">
							<div>
								<input name="i_issue_date" id="i_issue_date" type="text" value="'.$v_issue_date.'" class="woe-date-v">
							</div>
							<div class="woe-date-label">
								(дата выдачи)
							</div>
						</div>
						<div class="woe-date">
							<div>
								<input name="i_incoming_date" id="i_incoming_date" type="text" value="'.$v_incoming_date.'" class="woe-date-v">
							</div>
							<div class="woe-date-label">
								(дата поступления)
							</div>
						</div>
						<div class="woe-date">
							<div>
								<input name="i_date_force" id="i_date_force" type="text" value="'.$v_date_force.'" class="woe-date-v">
							</div>
							<div class="woe-date-label">
								(дата вступления в законную силу)
							</div>
						</div>
					</div>
				<div style="clear : both ;">
				</div>
				<div class="woe-agency">
					<textarea name="i_agency_ta" id="woe_agency_ta" class="woe-agency-ta" onkeyup="srch();">'.htmlentities( $v_agency , ENT_QUOTES , 'cp1251' ).'</textarea><br>
					<select id="woe_agency_sel" class="woe-agency-sel" size="10" onchange="agency_select( \'woe_agency_sel\' , \'woe_agency_ta\' , \'woe_agent_sel\' , true )" onclick="agency_select( \'woe_agency_sel\' , \'woe_agency_ta\' , \'woe_agent_sel\' , true )"></select><br>
					<div class="woe-agency-label">(наименование суда / судебного участка)</div><br>
					<textarea name="i_agency_addr_ta" id="woe_agency_addr_ta" class="woe-agency-addr-ta">'.htmlentities( $v_agency_addr , ENT_QUOTES , 'cp1251' ).'</textarea><br>
					<div id="woe_agency_alt_addr_cont" class="woe-agency-alt-addr"><a id="woe_agency_alt_addr" class="woe-agency-alt-addr-lnk" onclick="fillAddress()">'.htmlentities( $v_agency_addr_int , ENT_QUOTES , 'cp1251' ).'</a></div>
					<div class="woe-agency-label">(адрес суда / судебного участка)</div><br>
					<textarea name="i_agent_ta" id="woe_agent_ta" class="woe-agent-ta" onkeypress="srch2();">'.htmlentities( $v_agent , ENT_QUOTES , 'cp1251' ).'</textarea><br>
					<select id="woe_agent_sel" class="woe-agent-sel" size="10" onchange="agent_select()" onclick="agent_select()"></select><br>
					<div class="woe-agency-label">(судья)</div><br>
				</div>
				<div class="woe-payers-area"><div>Плательщики</div>
					<table id="woe_payers_table" class="woe-payers-table" align="center">
						<tr>
							<td class="woe-pt-h-0">
							</td>
							<td class="woe-pt-h-n">
								Ф.И.О. или наименование плательщика
							</td>
							<td class="woe-pt-h-p">
								Сумма
							</td>
							<td class="woe-pt-h-al">
							</td>
						</tr>' ;

						$totalPrice = 0.0 ;

						foreach( $v_payers as $p ) {
							if ( $p[ "pc" ] == 0 ) {
								echo "<tr id=\"woe_pt_row_" , $p[ "id" ] , "\" class=\"woe-pt-row\">
									<td class=\"woe-pt-d-0\">
										".( in_array( $mode , array( "create" , "edit" ) ) ? "<a onclick=\"deletePayer( ".$p[ "id" ]." , 2 );\" class=\"woe-pt-i-0\"><img src=\"themes/".$UserThemeLoc."/btn_del.bmp\"></a>" : "" )."
									</td>
									<td class=\"woe-pt-d-n\">
										<div class=\"woe-pt-d-wrapper\"><input name=\"i_payers_name[" , $p[ "id" ] , "]\" type=\"text\" value=\"" , htmlentities( $p[ "payer" ] , ENT_QUOTES , "cp1251" ) , "\" class=\"woe-pt-i-n\"></div>
									</td>
									<td class=\"woe-pt-d-p\">
										<div class=\"woe-pt-d-wrapper\"><input name=\"i_prices[" , $p[ "id" ] , "]\" type=\"text\" value=\"".money_format( "%^!i" , $p[ "price" ] )."\" class=\"woe-pt-i-p\"></div>" ;
							} else {
								echo "<tr id=\"" , $p[ "id" ] , "\" class=\"woe-pt-row\">
									<td class=\"woe-pt-fd-0\">
										".( in_array( $mode , array( "create" , "edit" ) ) ? "<a onclick=\"deletePayer( ".$p[ "id" ]." , 0 );\" class=\"woe-pt-i-0\"><img src=\"themes/".$UserThemeLoc."/btn_del.bmp\"></a>" : "" )."
									</td>
									<td class=\"woe-pt-fd-n\">
										" , htmlentities( $p[ "payer" ] , ENT_QUOTES , "cp1251" ) , "
									</td>
									<td class=\"woe-pt-fd-p\">
										" , money_format( "%!i" , $p[ "price" ] ) , "" ;
							}

							echo "</td>
								<td class=\"woe-pt-d-al\">
									<a onclick=\"showPayerMenu( " , $p[ "id" ] , " );\" class=\"pm-activate-lnk\">&gt;</a>
								</td>
							</tr>" ;
							$totalPrice+= $p[ "price" ];
						}

					echo "</table>
					<div class=\"woe-pt-btn-area\">Общая сумма : " , money_format( "%!i" , $totalPrice ) , " руб.</div>
					<div class=\"woe-pt-btn-area\"><input type=\"button\" value=\"Добавить\" onclick=\"addPayer()\"></div></div>
				<div class=\"woe-num\">Cерия<input name=\"i_num\" id=\"i_num\" type=\"text\" value=\"" , htmlentities( $v_num , ENT_QUOTES , "cp1251" ) , "\" class=\"woe-num-v\"> <a class=\"woe-num-find-corr-lnk\" onclick=\"findCorr()\" title=\"Поиск по журналам вх/исх корреспонденции\"><img src=\"/themes/std0/search.main.png\"></a></div>
				<div class=\"woe-ep-num\">Исполнительное производство <input name=\"i_ep_num\" id=\"i_ep_num\" type=\"text\" value=\"" , htmlentities( $v_ep_num , ENT_QUOTES , "cp1251" ) , "\" class=\"woe-ep-num-v\"></div>
				<div class=\"btn-area\">".$cVar[ "btnName" ]."</div>
			</div>
		".$cVar[ "formClose" ]."
	</div>" ;

	closeHtml();
