<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	require_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserWorkerFirstID
	 * @var $UserDepartment
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );
	require_once( "../ext-lib/rtf-gen.php" );
	require_once( '../shared/share.maindb.php' );

	TryLoginFromCookie( $PlaceID );

	$modeAJAX = isset( $_REQUEST[ "mode" ] );

	if ( !$LoginOk ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			Redirect( "../auth.php" );
		}
	}

	$docMode = "html" ;
	if ( isset( $_REQUEST[ "genRTF" ] ) ) {
		$docMode = "rtf" ;
	}
	if ( isset( $_REQUEST[ "genCSV" ] ) ) {
		$docMode = "csv" ;
	}
	if ( isset( $_REQUEST[ "genXLSX" ] ) ) {
		$docMode = "xlsx" ;
	}
	if ( isset( $_REQUEST[ "applyFilters" ] ) ) {
		$docMode = "html" ;
	}


	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "PAYMENTS" , $Rights ) ) {
			$mayViewNew = in_array( "VIEW-NEW" , $Rights[ "PAYMENTS" ] );
			$mayViewOld = in_array( "VIEW-OLD" , $Rights[ "PAYMENTS" ] );
			$mayViewChecked = in_array( "VIEW-CHECKED" , $Rights[ "PAYMENTS" ] );
			$mayViewUnChecked = in_array( "VIEW-UNCHECKED" , $Rights[ "PAYMENTS" ] );
			$mayCheck = in_array( "CHECK" , $Rights[ "PAYMENTS" ] );
			$mayEdit = in_array( "EDIT" , $Rights[ "PAYMENTS" ] );
			$maySearch = in_array( "SEARCH" , $Rights[ "PAYMENTS" ] );
			$mayComment = in_array( "COMMENT" , $Rights[ "PAYMENTS" ] );
			$mayViewComment = in_array( "VIEW-COMMENT" , $Rights[ "PAYMENTS" ] );
			$mayMarkAdd = in_array( "MARKS-ADD" , $Rights[ "PAYMENTS" ] );

			$mayViewOldPeriod = ( array_key_exists( "PAYMENTS-OLD" , $Rights ) ? intval( $Rights[ "PAYMENTS-OLD" ][ 0 ] ) : 30 );

			$viewStyle = strtolower( array_key_exists( "PAYMENTS-STYLE" , $Rights ) ? $Rights[ "PAYMENTS-STYLE" ][ 0 ] : "Simple" );
			$paymentsAccess = strtolower( array_key_exists( "PAYMENTS-ACCESS" , $Rights ) ? $Rights[ "PAYMENTS-ACCESS" ][ 0 ] : "Expert" );

			if ( $modeAJAX ) {
				$GoOut = !( ( $mayViewNew || $mayViewOld ) && ( $mayEdit || $mayCheck || $mayComment ) );
			} else {
				$GoOut = !( $mayViewNew || $mayViewOld );
			}
		} else {
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
			echo "<br><br><br><br><br>" ;
			MessageForm();
			closeHtml();
			exit ;
		}
	}

		$workersN = $portalDB->table( "workers" , "id" );
		foreach( $workersN as &$w ) {
			$w = array( NAMES_Format( NAMES_parse( $w[ "name" ] ) ) , $w[ "first_id" ] , $w[ "dep" ] );
		} unset( $w );

		$marksCatalog = $portalDB->table( 'marks-catalog' , 'id' );

		$departmentsN = $portalDB->table( "departments" , "id" );
		foreach( $departmentsN as &$d ) {
			$d = array( $d[ "name" ] , $d[ "ind" ] , $d[ "short_name" ] );
		} unset( $d );

		$caseCategory = $portalDB->table( "casecategory" , "id" );
		foreach( $caseCategory as &$cc ) {
			$cc = array( inForm( $cc[ "name" ] ) , inForm( $cc[ "name" ] , 1 , false ) );
		} unset( $cc );

		$workersInPayments = $portalDB->query( "select `t2`.`exp_id` as `id` from `payments` as `t1` , `expertize` as `t2` where `t1`.`expertize_id` = `t2`.`id` group by `t2`.`exp_id` ; " );
		foreach( $workersInPayments as &$w ) {
			$w = $w[ "id" ];
		} unset( $w );

		//print_r_html( $workersN );

	$filter = array();

	if ( isset( $_REQUEST[ "i_case_category" ] ) ) {
		$caseCategoryIndexes = $_REQUEST[ "i_case_category" ];
		$filter[]= "( `t4`.`exp_type` in ( ".implode( " , " , $caseCategoryIndexes )." ) )" ;
	} else {
		$caseCategoryIndexes = array();
		$filter[]= "( `t4`.`exp_type` <> 1 )" ;
		$filter[]= "( `t4`.`exp_type` <> 5 )" ;
	}

	if ( isset( $_REQUEST[ "i_show_year" ] ) ) {
		$showYears = $_REQUEST[ "i_show_year" ];
	} else {
		$showYears = array( intval( date( "Y" , time() ) ) );
	}
	$filter[]= "( year( from_unixtime( `t1`.`create_date` ) ) in ( ".implode( " , " , $showYears )." ) )" ;
	$dateRange = $portalDB->row( "select year( from_unixtime( min( `create_date` ) ) ) as `mid` , year( from_unixtime( max( `create_date` ) ) ) as `mad` from `payments`" );

	if ( isset( $_REQUEST[ "i_view_state" ] ) ) {
		$showUnChecked = ( isset( $_REQUEST[ "i_view_state" ] ) && in_array( "unchecked" , $_REQUEST[ "i_view_state" ] ) );
		$showChecked = ( isset( $_REQUEST[ "i_view_state" ] ) && in_array( "checked" , $_REQUEST[ "i_view_state" ] ) );
	} else {
		$showUnChecked = true ;
		$showChecked = false ;
	}

	if ( isset( $_REQUEST[ "bySubpoenasOnly" ] ) && $_REQUEST[ "bySubpoenasOnly" ] == 1 ) {
		$showBySubpoenasOnly = true ;
	} else {
		$showBySubpoenasOnly = false ;
	}

	$viewStateParam = array();
	$viewOrderParam = array();
	$viewGroupParam = array();
	if ( $mayViewUnChecked && $showUnChecked ) {
		$viewStateParam[]= "unchecked" ;
		$viewStateFilter[ "unchecked" ]= "( `t1`.`state` <> 1 )" ;
		$viewOrderParam[ "unchecked" ]= "`create_date` desc" ;
		$viewGroupParam[ "unchecked" ]= "create_date" ;
	}
	if ( $mayViewChecked && $showChecked ) {
		$viewStateParam[]= "checked" ;
		$viewStateFilter[ "checked" ]= "( `t1`.`state` = 1 )" ;
		$viewOrderParam[ "checked" ]= "`create_date` desc" ;
		$viewGroupParam[ "checked" ]= "create_date" ;
	}

	//print_r_html( $filter );

	$expertFilter = "" ;
	$expertFilterArray = array();

	$GTKs = array(
		"year" => array( "по годам" , "Y" , "год %G" ) ,
		"month" => array( "по месяцам" , "Y-m" , "%B %G" ) ,
		"day" => array( "по дням" , "Y-m-d" , "%e %B %G" )
	);

	if ( isset( $_REQUEST[ "i_group_by" ] ) ) {
		$groupBy = $_REQUEST[ "i_group_by" ];
	} else {
		$groupBy = "day" ;
	}

	switch ( $paymentsAccess ) {
		case "expert" :
			$expertFIDs = array( $UserWorkerFirstID );
			foreach( $workersN as $wid => $w ) {
				if ( $w[ 1 ] == $expertFIDs[ 0 ] && in_array( $wid , $workersInPayments ) ) {
					$expertFilterArray[]= $wid ;
					$workersL[ $w[ 0 ] ] = $w[ 1 ];
				}
			}
			if ( count( $expertFilterArray ) > 0 ) {
				$filter[]= "( `t2`.`exp_id` in ( ".implode( " , " , $expertFilterArray )." ) )" ;
			} else {
				$filter[]= "( 0 )" ;
			}
			$groupBy = "month" ;
			break ;

		case "department" :
			foreach( $workersN as $wid => $w ) {
				if ( $w[ 2 ] == $UserDepartment && in_array( $wid , $workersInPayments ) ) {
					$workersL[ $w[ 0 ] ] = $w[ 1 ];
				}
			}
			if ( isset( $_REQUEST[ "i_worker" ] ) ) {
				$expertFIDs = $_REQUEST[ "i_worker" ];
				foreach( $workersN as $wid => $w ) {
					if ( $w[ 2 ] == $UserDepartment && in_array( $w[ 1 ] , $expertFIDs ) ) {
						$expertFilterArray[]= $wid ;
					}
				}
			} else {
				foreach( $workersN as $wid => $w ) {
					if ( $w[ 2 ] == $UserDepartment ) {
						$expertFilterArray[]= $wid ;
					}
				}
			}
			$groupBy = "month" ;
			if ( count( $expertFilterArray ) > 0 ) {
				$filter[]= "( `t2`.`exp_id` in ( ".implode( " , " , $expertFilterArray )." ) )" ;
			} else {
				$filter[]= "( 0 )" ;
			}
			break ;

		case "all" :
			foreach( $workersN as $wid => $w ) {
				if ( in_array( $wid , $workersInPayments ) ) {
					$workersL[ $w[ 0 ] ] = $w[ 1 ];
				}
			}
			if ( isset( $_REQUEST[ "i_worker" ] ) ) {
				$expertFIDs = $_REQUEST[ "i_worker" ];
				foreach( $workersN as $wid => $w ) {
					if ( in_array( $w[ 1 ] , $expertFIDs ) && in_array( $wid , $workersInPayments ) ) {
						$expertFilterArray[]= $wid ;
					}
				}
				if ( count( $expertFilterArray ) > 0 ) {
					$filter[]= "( `t2`.`exp_id` in ( ".implode( " , " , $expertFilterArray )." ) )" ;
				} else {
					$filter[]= "( 0 )" ;
				}
				//$groupBy = "month" ;
			}
			break ;
	}

	$GTK = $GTKs[ $groupBy ][ 1 ];
	$GTK2 = $GTKs[ $groupBy ][ 2 ];

	function rangeAdd( &$rl , $nr ) {
		$rll = count( $rl );
		if ( $rll == 0 ) {
			$rl = array( $nr );
		} else {
			$i = 0 ;
			while ( $i < $rll ) {

				if ( $nr[ 0 ] - 1 > $rl[ $i ][ 1 ] ) {
					$i++ ;
					continue ;
				}

				if ( $nr[ 1 ] + 1 < $rl[ $i ][ 0 ] ) {
					array_splice( $rl , $i , 0 , array( $nr ) );
					return ;
				}

				$nr[ 0 ] = min( $nr[ 0 ] , $rl[ $i ][ 0 ] );
				$nr[ 1 ] = max( $nr[ 1 ] , $rl[ $i ][ 1 ] );
				array_splice( $rl , $i , 1 );
				$rll-- ;
			}
			array_push( $rl , $nr );
		}
	}

	function cvt__chk_btn( &$r ) {
		return ( $r[ "state" ] > 0 ? "*" : "" ) ;
	}

	function cvt__exp_number( &$r ) {
		return matincomingNumber( $r[ "mat_id" ] );
	}

	function cvt__exp_date( &$r ) {
		return date( "d.m.Y" , $r[ "date" ] );
	}

	function cvt__exp_cc( &$r ) {
		global $caseCategory ;
		return $caseCategory[ $r[ "exp_type" ] ][ 0 ];
	}

	function cvt__exp_fin_date( &$r ) {
		return date( "d.m.Y" , $r[ "fin_date" ] );
	}

	function cvt__worker( &$r ) {
		global $workersN ;
		return $workersN[ $r[ "exp_id" ] ][ 0 ];
	}

	function cvt__worker_dep( &$r ) {
		global $workersN , $departmentsN ;
		return $departmentsN[ $workersN[ $r[ "exp_id" ] ][ 2 ] ][ 2 ];
	}

	function cvt__create_date( &$r ) {
		return date( "H:i" , $r[ "create_date" ] );
	}

	function cvt__price( &$r ) {
		global $docMode ;
		switch ( $docMode ) {
			case "html" :
			case "rtf" :
				return money_format( "%!i" , $r[ "price" ] );
				break ;

			case "csv" :
				return number_format( $r[ "price" ] , 2 , "," , "" );
				break ;
		}
		return false ;
	}

	function cvt__from_agency( &$r ) {
		return preg_replace( '/\s+/' , " " , $r[ "agency" ] );
	}

	function cvt__from_agent( &$r ) {
		return preg_replace( '/\s+/' , " " , $r[ "agent" ] );
	}

	function cvt__from_by( &$r ) {
		return preg_replace( '/\s+/' , " " , $r[ "ex_data_3" ] );
	}

	function cvt__from( &$r ) {
		return preg_replace( '/\s+/' , " " , $r[ "agent" ].", ".$r[ "agency" ].", ".$r[ "ex_data_3" ] );
	}

	function cvt__ex_data_4( &$r ) {
		return preg_replace( '/\s+/' , " " , $r[ "ex_data_4" ] );
	}

	function cvt__pay_date( &$r ) {
		global $docMode ;
		switch( $docMode ) {
			case "html" :
				return ( $r[ "type" ] == 1 ? "<span class=\"subpoena-mark\">Выход в суд</span>" : "" ).$r[ "pay_date" ];
				break ;

			case "rtf" :
				return ( $r[ "type" ] == 1 ? "Выход в суд / " : "" ).$r[ "pay_date" ];
				break ;

			case "csv" :
				return ( $r[ "type" ] == 1 ? "Выход в суд / " : "" ).preg_replace( '/\s+/' , " " , $r[ "pay_date" ] );
				break ;
		}
	}

	function cvt__pay_details( &$r ) {
		return preg_replace( '/\s+/' , " " , $r[ "pay_details" ] );
	}

	function cvt__check_date( &$r ) {
		return ( intval( $r[ "check_date" ] ) == 0 ? "" : date( "d.m.Y" , $r[ "check_date" ] ) );
	}

	function cvt__comment( &$r ) {
		global $mayViewComment , $rowID , $ectm , $workersN , $UserAllWorkers , $docMode ;

		switch( $docMode ) {
			case "html" :
				if ( isset( $ectm[ $r[ "expertize_id" ] ] ) && count( $ectm[ $r[ "expertize_id" ] ] ) > 0 ) {
					$c = array();
					$cuct = "" ;
					foreach ( $ectm[ $r[ "expertize_id" ] ] as &$ec ) {
						if ( in_array( $ec[ "exp_id" ] , $UserAllWorkers ) ) {
							$cuct = trim( $ec[ "comment" ] );
						} else {
							$c[]= "<div class=\"uc-comment\"><span class=\"uc-text\">".$ec[ "comment" ]."</span><span class=\"uc-author\">".$workersN[ $ec[ "exp_id" ] ][ 0 ]."</span><div style=\"clear : both ;\"></div></div>" ;
						}
					}
					return "<div class=\"uc-area\">".implode( "" , $c )."</div>".( $mayViewComment ? "<span id=\"pcc_".$rowID."\">".$cuct."</span>" : "<center><i><font color=\"#808080\">Комментарий скрыт</i></font></center>" );
				} else {
					return ( $mayViewComment ? "<span id=\"pcc_".$rowID."\"></span>" : "<center><i><font color=\"#808080\">Комментарий скрыт</i></font></center>" );
				}
				break ;

			case "rtf" :
				if ( isset( $ectm[ $r[ "expertize_id" ] ] ) && count( $ectm[ $r[ "expertize_id" ] ] ) > 0 ) {
					$c = array();
					$cuct = "" ;
					foreach ( $ectm[ $r[ "expertize_id" ] ] as &$ec ) {
						if ( in_array( $ec[ "exp_id" ] , $UserAllWorkers ) ) {
							$cuct = trim( $ec[ "comment" ] );
						} else {
							$c[]= $ec[ "comment" ]." / ".$workersN[ $ec[ "exp_id" ] ][ 0 ];
						}
					}
					return implode( "\r\n" , $c )." ".( $mayViewComment ? $cuct : "Комментарий скрыт" );
				} else {
					return ( $mayViewComment ? "" : "Комментарий скрыт" );
				}
				break ;

			case "csv" :
				if ( isset( $ectm[ $r[ "expertize_id" ] ] ) && count( $ectm[ $r[ "expertize_id" ] ] ) > 0 ) {
					$c = array();
					$cuct = "" ;
					foreach ( $ectm[ $r[ "expertize_id" ] ] as &$ec ) {
						if ( in_array( $ec[ "exp_id" ] , $UserAllWorkers ) ) {
							$cuct = trim( $ec[ "comment" ] );
						} else {
							$c[]= preg_replace( '/\s+/' , " " , $ec[ "comment" ] )." / ".$workersN[ $ec[ "exp_id" ] ][ 0 ];
						}
					}
					return implode( "\r\n" , $c )." ".( $mayViewComment ? $cuct : "Комментарий скрыт" );
				} else {
					return ( $mayViewComment ? "" : "Комментарий скрыт" );
				}
				break ;
		}



	}

	function cvt__woe( &$r ) {
		global $woeMap , $woel ;

		global $docMode ;
		switch( $docMode ) {
			case "html" :
				$res = array();
				if ( isset( $woeMap[ $r[ "mat_id" ] ] ) ) {
					foreach( $woeMap[ $r[ "mat_id" ] ] as $woeID ) {
						$cw = $woel[ $woeID ];
						$res[]= "<div class=\"\">".$cw[ "num" ]."</div>" ;
					}
				}
				return implode( "" , $res );
				break ;


			case "rtf" :
			case "csv" :
				$res = array();
				if ( isset( $woeMap[ $r[ "mat_id" ] ] ) ) {
					foreach( $woeMap[ $r[ "mat_id" ] ] as $woeID ) {
						$cw = $woel[ $woeID ];
						$res[]= $cw[ "num" ];
					}
				}
				return implode( "\r\n" , $res );
				break ;
		}
	}

	function cvt__application_for_issuance( &$r ) {
		global $UserThemeLoc , $docMode ;
		switch( $docMode ) {
			case "html" :
				return ( $r[ "application_for_issuance" ] == "1" ? "<img src=\"themes/".$UserThemeLoc."/state-fin.png\" class=\"elt-d-afi-i\">" : "" );
				break ;

			case "rtf" :
			case "csv" :
				return ( $r[ "application_for_issuance" ] == "1" ? "+" : "" );
				break ;
		}
	}

	function cvt__sndz( &$r ) {
		global $UserThemeLoc ;
		return ( $r[ "sndz" ] == "1" ? "сндз" : "" );
	}

	function cvt__marks( &$r ) {
		global $mayMarkAdd , $pmtm , $marksCatalog , $UserThemeLoc ;
		global $docMode ;

		if ( !$mayMarkAdd ) {
			return '' ;
		}
		$eid = $r[ 'id' ];
		if ( !isset( $pmtm[ $eid ] ) ) {
			return '' ;
		}

			$pml = $pmtm[ $eid ];
			if ( count( $pml ) > 0 ) {
				foreach( $pml as &$m ) {
					$m = $m[ 'mark_id' ].':'.$eid ;
				} unset( $m );
				switch( $docMode ) {
					case "html" :
						$c = Marks\integrate( $pml , array( 'mode' => 'simple-inline' , 'id-mark' => true ) , $marksCatalog );
						break ;

					case "rtf" :
					case "csv" :
						$c = Marks\integrate( $pml , array( 'mode' => 'text-quoted' , 'id-mark' => true , 'id-combined' => true ,
							'show-timestamp' => false ,
							'q-open' => '[' ,
							'q-close' => ']' ,
							'separator' => "\r\n" ) , $marksCatalog );
						break ;
				}
			} else {
				$c = '' ;
			}

			/*
			 			return Marks\integrate(
				explode( ',' , $v ) ,
				array(
					'mode' => 'text-quoted' ,
					'id-combined' => true ,
					'show-timestamp' => false ,
					'q-open' => '[' ,
					'q-close' => ']' ,
					'separator' => "\r\n"
				) ,
				$tabMarksCatalog
			);

			 */

		return $c ;
	}


	function set_evt__comment( &$r ) {
		global $rowID , $mayComment ;
		global $docMode ;
		switch( $docMode ) {
			case "html" :
				if ( $mayComment ) {
					return "onclick=\"editComment( ".$rowID." , ".$r[ "id" ]." , ".$r[ "expertize_id" ]." );\"" ;
				} else {
					return "" ;
				}
				break ;

			case "rtf" :
			case "csv" :
				return "" ;
				break ;
		}
	}

	function set_evt__marks( &$r ) {
		global $rowID , $mayMarkAdd ;
		if ( $mayMarkAdd ) {
			return 'oncontextmenu="marksMenu( '.$rowID.' , '.$r[ 'id' ].' );"' ;
		} else {
			return '' ;
		}
	}

	function get_flt__exp_number( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$fltR = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$p = strpos( $fltp , "-" );
			if ( $p !== false ) {
				switch ( $p ) {
					case 0 :
						rangeAdd( $fltR , array( 0 , intval( substr( $fltp , 1 ) ) ) );
						break ;
					case strlen( $fltp ) - 1 :
						rangeAdd( $fltR , array( intval( $fltp ) , 999999 ) );
						break ;
					default :
						rangeAdd( $fltR , array( intval( substr( $fltp , 0 , $p ) ) , intval( substr( $fltp , $p + 1 ) ) ) );
						break ;
				}
			} else {
				rangeAdd( $fltR , array( intval( $fltp ) , intval( $fltp ) ) );
			}
		}

		$rr = array();
		$rl = array();
		foreach( $fltR as $fv ) {
			if ( $fv[ 0 ] == $fv[ 1 ] ) {
				$rl[]= $fv[ 0 ];
			} else {
				$rr[]= "( matincomingNumber( `t3`.`mat_id` ) between ".$fv[ 0 ]." and ".$fv[ 1 ]." )" ;
			}
		}

		if ( count( $rl ) > 0 ) {
			$rr[]= "( matincomingNumber( `t3`.`mat_id` ) in ( ".implode( " , " , $rl )." ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__exp_date( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$fltR = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$p = strpos( $fltp , "-" );
			if ( $p !== false ) {
				switch ( $p ) {
					case 0 :
						rangeAdd( $fltR , array( 0 , Date2Int( substr( $fltp , 1 ) ) ) );
						break ;
					case strlen( $fltp ) - 1 :
						rangeAdd( $fltR , array( Date2Int( substr( $fltp , 0 , -1 ) ) , 2147483647 ) );
						break ;
					default :
						rangeAdd( $fltR , array( Date2Int( substr( $fltp , 0 , $p ) ) , Date2Int( substr( $fltp , $p + 1 ) ) ) );
						break ;
				}
			} else {
				rangeAdd( $fltR , array( Date2Int( $fltp ) , Date2Int( $fltp ) ) );
			}
		}

		$rr = array();
		$rl = array();
		foreach( $fltR as $fv ) {
			if ( $fv[ 0 ] == $fv[ 1 ] ) {
				$rl[]= date( "'Y-m-d'" , $fv[ 0 ] );
			} else {
				$rr[]= "( `t4`.`date` between ".date( "'Y-m-d'" , $fv[ 0 ] )." and ".date( "'Y-m-d'" , $fv[ 1 ] )." )" ;
			}
		}

		if ( count( $rl ) > 0 ) {
			$rr[]= "( `t4`.`date` in ( ".implode( " , " , $rl )." ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__exp_fin_date( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$fltR = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$p = strpos( $fltp , "-" );
			if ( $p !== false ) {
				switch ( $p ) {
					case 0 :
						rangeAdd( $fltR , array( 0 , Date2Int( substr( $fltp , 1 ) ) ) );
						break ;
					case strlen( $fltp ) - 1 :
						rangeAdd( $fltR , array( Date2Int( substr( $fltp , 0 , -1 ) ) , 2147483647 ) );
						break ;
					default :
						rangeAdd( $fltR , array( Date2Int( substr( $fltp , 0 , $p ) ) , Date2Int( substr( $fltp , $p + 1 ) ) ) );
						break ;
				}
			} else {
				rangeAdd( $fltR , array( Date2Int( $fltp ) , Date2Int( $fltp ) ) );
			}
		}

		$rr = array();
		$rl = array();
		foreach( $fltR as $fv ) {
			if ( $fv[ 0 ] == $fv[ 1 ] ) {
				$rl[]= Int2SQL( $fv[ 0 ] );
			} else {
				$rr[]= "( `t1`.`create_date` between ".Int2SQL( $fv[ 0 ] )." and ".Int2SQL( $fv[ 1 ] )." )" ;
			}
		}

		if ( count( $rl ) > 0 ) {
			$rr[]= "( `t1`.`create_date` in ( ".implode( " , " , $rl )." ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__price( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$fltR = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$p = strpos( $fltp , "-" );
			if ( $p !== false ) {
				switch ( $p ) {
					case 0 :
						rangeAdd( $fltR , array( 0 , round( floatval( substr( $fltp , 1 ) ) , 2 ) * 100 ) );
						break ;
					case strlen( $fltp ) - 1 :
						rangeAdd( $fltR , array( round( floatval( $fltp ) , 2 ) * 100 , 999999 ) );
						break ;
					default :
						rangeAdd( $fltR , array( round( floatval( substr( $fltp , 0 , $p ) ) , 2 ) * 100 , round( floatval( substr( $fltp , $p + 1 ) ) , 2 ) * 100 ) );
						break ;
				}
			} else {
				rangeAdd( $fltR , array( round( floatval( $fltp ) , 2 ) * 100 , round( floatval( $fltp ) , 2 ) * 100 ) );
			}
		}

		$rr = array();
		$rl = array();
		foreach( $fltR as $fv ) {
			if ( $fv[ 0 ] == $fv[ 1 ] ) {
				$rl[]= str_replace( "," , "." , $fv[ 0 ] / 100.0 );
			} else {
				$rr[]= "( `t2`.`price` between ".str_replace( "," , "." , $fv[ 0 ] / 100.0 )." and ".str_replace( "," , "." , $fv[ 1 ] / 100.0 )." )" ;
			}
		}

		if ( count( $rl ) > 0 ) {
			$rr[]= "( `t2`.`price` in ( ".implode( " , " , $rl )." ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__from_agency( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$rr = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$rr[]= "( `t5`.`name` like concat( \"%\" , ".Str2SQL( $fltp )." , \"%\" ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__from_agent( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$rr = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$rr[]= "( `t6`.`name` like concat( \"%\" , ".Str2SQL( $fltp )." , \"%\" ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__from_by( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$rr = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$rr[]= "( `t4`.`ex_data_3` like concat( \"%\" , ".Str2SQL( $fltp )." , \"%\" ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__from( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$rr = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$rr[]= "( concat( `t6`.`name` , \", \" , `t5`.`name` , \", \" , `t4`.`ex_data_3` ) like concat( \"%\" , ".Str2SQL( $fltp )." , \"%\" ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__ex_data_4( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$rr = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$rr[]= "( `t4`.`ex_data_4` like concat( \"%\" , ".Str2SQL( $fltp )." , \"%\" ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__pay_date( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$rr = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$rr[]= "( `t2`.`pay_date` like concat( \"%\" , ".Str2SQL( $fltp )." , \"%\" ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	function get_flt__pay_details( $flt ) {
		$flt = trim( trim( $flt ) , "," );
		$flt = explode( "," , $flt );
		$rr = array();
		foreach( $flt as $fltp ) {
			$fltp = trim( $fltp );
			$rr[]= "( `t2`.`pay_details` like concat( \"%\" , ".Str2SQL( $fltp )." , \"%\" ) )" ;
		}

		if ( count( $rr ) > 0 ) {
			return "( ".implode( " or " , $rr )." )" ;
		} else {
			return "1" ;
		}
	}

	$resHead = array(
		"chk_btn"      => array( "cb" , "" , false , false , 18 , 1 ),
		"exp_number"   => array( "en" , "Номер экспертизы" , false , true , 96 , 1 ),
		"exp_date"     => array( "ed" , "Дата назначения экспертизы" , false , true , 96 , 1 ),
		"exp_fin_date" => array( "efd" , "Дата окончания экспертизы" , false , true , 96 , 1 ),
		"check_date"   => array( "ckd" , "Дата отметки" , false , false , 96 , 1 ),
		"exp_cc"       => array( "ecc" , "Категория дела" , false , false , 128 , 1 ),
		"worker"       => array( "wn" , "Эксперт" , false , false , 144 , 0 ),
		"worker_dep"   => array( "wd" , "Отдел" , false , false , 96 , 1 ),
		"price"        => array( "p" , "Стоимость" , false , true , 96 , 2 ),
		"from_agency"  => array( "fay" , "От кого (орган)" , false , true , 256 , 0 ),
		"from_agent"   => array( "fat" , "От кого (лицо)" , false , true , 256 , 0 ),
		"from_by"      => array( "fby" , "От кого (документ)" , false , true , 256 , 0 ),
		"woe"          => array( "woe" , "Исполнительные листы" , false , false , 128 , 1 ),
		"application_for_issuance" => array( "afi" , "Заявл. И/Л" , false , false , 48 , 1 ),
		"sndz"         => array( "sndz" , "сндз" , false , false , 48 , 1 ),
		"from"         => array( "f" , "От кого" , false , true , 256 , 0 ),
		"ex_data_4"    => array( "ed4" , "Номер дела и т.д." , false , true , 256 , 0 ),
		"pay_date"     => array( "pd" , "Дата платежа" , false , true , 96 , 1 ),
		"pay_details"  => array( "pde" , "Плательщик" , false , true , 256 , 0 ),
		"comment"      => array( "com" , "Пометки" , true , false , 320 , 0 ),

		"marks"     => array( "mark" , "########" , true , false , 120 , 1 ) ,
	);

	foreach( $resHead as $rhn => $rhv ) {
		if ( $rhv[ 3 ] ) {
			$rhvn = "i_flt__".$rhn ;
			$$rhvn = "" ;
			$rhvn = "i_flt__".$rhn."_e" ;
			$$rhvn = "" ;
		}
	}

	$workersC = count( $workersL );
	$wrc = ceil( sqrt( $workersC ) );
	$wcc = min( floor( $workersC / $wrc ) , 7 );
	$wrc = ceil( $workersC / $wcc );

	$wt = array();
	$wtri = 0 ;
	$wtci = 0 ;
	ksort( $workersL );
	foreach( $workersL as $j => $i ) {
		if ( !isset( $wt[ $wtci ] ) ) {
			$wt[ $wtci ] = array();
		}
		switch ( $docMode ) {
			case "html" :
				$wt[ $wtci ][]= "<input name=\"i_worker[]\" type=\"checkbox\" value=\"".$i."\"".( isset( $expertFIDs ) && in_array( $i , $expertFIDs ) ? " checked" : "" )."> ".$j ;
				break ;
			case "rtf" :
			case "csv" :
				if ( isset( $expertFIDs ) && in_array( $i , $expertFIDs ) ) {
					$wt[ $wtci ][]= $j ;
				}
				break ;
		}
		$wtri++ ;
		if ( $wtri >= $wrc ) {
			$wtci ++ ;
			$wtri = 0 ;
		}
	}

	function startDoc() {
		global $docMode , $doc , $viewStateParam , $maySearch ,
				$resHead , $caseCategory , $caseCategoryIndexes ,
				$dateRange , $showYears , $GTKs , $groupBy , $colFlt , $wt ;

		switch( $docMode ) {
			case "html" :
				if ( $maySearch && isset( $_REQUEST[ 'search' ] ) ) {
					MainHead_L2( 'База' , '<a href="./">База</a><a href="'.getPaymentsAddr().'">Оплата</a> - Результаты поиска' , array( '../%UT/buttons.css' , '%UT/payments.report.css' ) , array( 'files/payments.report.js' ) , 'hlp/main.html' );
				} else {
					MainHead_L2( 'База' , "<a href=\"./\">База</a> - Оплата" , array( "../%UT/buttons.css" , "%UT/payments.report.css" ) , array( "files/payments.report.js" ) , "hlp/main.html" );
				}

				echo '<form id="search-form" action="payments.report.php" method="post">' ;

					if ( $maySearch ) {
						$colFlt = array();
						foreach( $resHead as $rhn => $rhv ) {
							if ( $rhv[ 3 ] ) {
								$rhvn = "i_flt__".$rhn ;
								if ( isset( $_REQUEST[ $rhvn ] ) && trim( $_REQUEST[ $rhvn ] ) != "" ) {
									echo "<input id=\"i_flt__".$rhn."\" name=\"i_flt__".$rhn."\"  type=\"hidden\" value=\"".$_REQUEST[ $rhvn ]."\">" ;
									$colFlt[]= "<div class=\"rpflt-lnk-area\"><a onclick=\"fdc( '".$rhn."' , event )\" class=\"rpflt-lnk\">".$rhv[ 1 ]." : ".$_REQUEST[ $rhvn ]."</a></div>" ;
								} else {
									echo "<input id=\"i_flt__".$rhn."\" name=\"i_flt__".$rhn."\"  type=\"hidden\" value=\"\">" ;
								}
							}
						}
						echo "<div id=\"filter_dialog\" class=\"search-panel\" style=\"display : none ;\">
							<div>
								<input id=\"i_search\" type=\"text\" class=\"search-input\" onkeyup=\"sfkp( event );\" value=\"\"><br>
								<input name=\"i_flt_btn_ok\" type=\"button\" value=\"ok\" onclick=\"sfsf( 1 );\" form=\"search-form\"> <input name=\"i_flt_btn_clr\" type=\"submit\" value=\"del\" onclick=\"sfsf( 0 );\" form=\"search-form\">
							</div>
						</div>" ;
					}

					echo "<div class=\"tools-panel\">
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
					</div>
				</form>" ;

				break ;

			case "rtf" :
				header( "Content-Type: application/rtf" );
				header( "Content-Disposition: attachment;filename=\"Отчет по оплате ".date( "Y.m.d H-i" , time() ).".rtf\"" );

				$doc = new RTFDocument();

				$doc->paperFormat = PAPER_SIZE_A4_LANDSCAPE ;
				$doc->margins = "10mm" ;

				$doc->setFontName( FONT_CALIBRI )->setTextColor( "#000" );

				$doc->setMainContext()->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "8pt" )
					->addTag( "caps" )->addTag( "b" )
					->addTextLine( "Параметры отчета" )
					->addTag( "b0" )->addTag( "caps0" );

				$tbl = $doc->addTable();
					$r = $tbl->insertRow();
					$r->height = "4mm" ;

						$tmp = array();
						if ( in_array( "unchecked" , $viewStateParam ) ) {
							$tmp[]= "неоплаченные" ;
						}
						if ( in_array( "checked" , $viewStateParam ) ) {
							$tmp[]= "оплаченные" ;
						}

						$c = $r->insertCell();
						$c->width = "65mm" ;
						$c->verticalAlign = CELL_ALIGN_CENTER ;
						$c->setBorders( "ltrb" , "s" );
						$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "8pt" )
							->addText( "Показать: " )->setFontSize( "10pt" )
							->addTag( "b" )->addText( implode( " и " , $tmp ) )->addTag( "b0" );

						$c = $r->insertCell();
						$c->width = "60mm" ;
						$c->verticalAlign = CELL_ALIGN_CENTER ;
						$c->setBorders( "ltrb" , "s" );
						$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "10pt" );
						$tmp = array_pop( $caseCategoryIndexes );
						foreach( $caseCategoryIndexes as $ccn ) {
							$doc->addTextLine( $caseCategory[ $ccn ][ 1 ] );
						}
						if ( $tmp !== NULL ) {
							$doc->addText( $caseCategory[ $tmp ][ 1 ] );
						}

						$c = $r->insertCell();
						$c->width = "25mm" ;
						$c->verticalAlign = CELL_ALIGN_CENTER ;
						$c->setBorders( "ltrb" , "s" );
						$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "8pt" );
						$doc->addTextLine( "Год окончания: " )->setFontSize( "10pt" );
						$tmp = array_pop( $showYears );
						foreach ( $showYears as $i ) {
							$doc->addTag( "b" )->addTextLine( $i )->addTag( "b0" );
						}
						if ( $tmp !== NULL ) {
							$doc->addTag( "b" )->addText( $tmp )->addTag( "b0" );
						}

						$c = $r->insertCell();
						$c->width = "40mm" ;
						$c->verticalAlign = CELL_ALIGN_CENTER ;
						$c->setBorders( "ltrb" , "s" );
						$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "8pt" );
						$doc->addText( "Группировать " )->setFontSize( "10pt" );
						foreach( $GTKs as $k => $v ) {
							if ( $k == $groupBy ) {
								$doc->addTag( "b" )->addText( $v[ 0 ] )->addTag( "b0" );
							}
						}

						$c = $r->insertCell();
						$c->width = "85mm" ;
						$c->verticalAlign = CELL_ALIGN_CENTER ;
						$c->setBorders( "ltrb" , "s" );
						$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "8pt" );
						$doc->addTextLine( "Фильтры столбцов: " )->setFontSize( "10pt" );
						if ( isset( $colFlt ) && count( $colFlt ) > 0 ) {
							$doc->addTag( "b" )->addText( implode( " , " , $colFlt ) )->addTag( "b0" );
						}


					$r = $tbl->insertRow();
					$r->height = "4mm" ;

						$c = $r->insertCell();
						$c->width = "275mm" ;
						$c->verticalAlign = CELL_ALIGN_CENTER ;
						$c->setBorders( "ltrb" , "s" );
						$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "10pt" );
						$tmp = array();
						foreach ( $wt as $cwt ) {
							$tmp = array_merge( $tmp , $cwt );
						}
						$doc->addText( implode( ", " , $tmp ) );
				break ;

			case "csv" :
				header( "Content-Type: text/csv" );
				header( "Content-Disposition: attachment;filename=\"Отчет по оплате ".date( "Y.m.d H-i" , time() ).".csv\"" );
				$doc = fopen( "php://output" , "w" );
				break ;

		}
	}

	function stopDoc() {
		global $docMode , $doc ;

		switch ( $docMode ) {
			case "html" :
				closeHtml();
				break ;

			case "rtf" :
				$doc->write();
				break ;

			case "csv" :
				fclose( $doc );
				break ;
		}
	}

	function genHeadV(){
		global $docMode , $resHead , $filter ;

		$res = array();

		switch ( $docMode ) {
			case "html" :
				foreach( $resHead as $rhn => $rhv ) {
					if ( $rhv[ 3 ] ) {
						$rhvn = "i_flt__".$rhn ;
						if ( isset( $_REQUEST[ $rhvn ] ) && trim( $_REQUEST[ $rhvn ] ) != "" ) {
							$res[ $rhn ] = "<td class=\"elt-h w-".$rhv[ 0 ]."\"><a onclick=\"fdc( '".$rhn."' , event )\" class=\"flt-lnk-hl\" title=\"применен фильтр: ".htmlspecialchars( $_REQUEST[ $rhvn ] )."\">".$rhv[ 1 ]."*</a></td>" ;
							$rhvn_ffn = "get_flt__".$rhn ;
							$filter[]= $rhvn_ffn( $_REQUEST[ $rhvn ] );
						} else {
							$res[ $rhn ] = "<td class=\"elt-h w-".$rhv[ 0 ]."\"><a onclick=\"fdc( '".$rhn."' , event )\" class=\"flt-lnk\">".$rhv[ 1 ]."</a></td>" ;
						}
					} else {
						$res[ $rhn ] = "<td class=\"elt-h w-".$rhv[ 0 ]."\">".$rhv[ 1 ]."</td>" ;
					}
				}

				break ;


			case "rtf" :
			case "csv" :
				foreach( $resHead as $rhn => $rhv ) {
					if ( $rhv[ 3 ] ) {
						$rhvn = "i_flt__".$rhn ;
						if ( isset( $_REQUEST[ $rhvn ] ) && trim( $_REQUEST[ $rhvn ] ) != "" ) {
							$res[ $rhn ] = $rhv[ 1 ]." [".$_REQUEST[ $rhvn ]."]" ;
							$rhvn_ffn = "get_flt__".$rhn ;
							$filter[]= $rhvn_ffn( $_REQUEST[ $rhvn ] );
						} else {
							$res[ $rhn ] = $rhv[ 1 ];
						}
					} else {
						$res[ $rhn ] = $rhv[ 1 ];
					}
				}

				break ;

		}

		return $res ;
	}

	function writeTitle( $title , $lvl = 1 ) {
		global $docMode , $col , $doc , $tbl ;
		$txt1 = "экспертиз за период: " ;
		$txt2 = " на общую сумму " ;
		$txt3 = "Всего экспертиз: " ;
		switch ( $docMode ) {
			case "html" :
				switch( $lvl ) {
					case 1 :
						echo "<div class=\"elt-title\">".$title."</div>" ;
						break ;
					case 2 :
						echo "<tr><td class=\"elt-h-period\" colspan=\"".count( $col )."\">".$title."</td></tr>" ;
						break ;
					case 3 :
						echo "<tr><td class=\"elt-d-total\" colspan=\"".count( $col )."\">".$txt1."<span class=\"elt-d-total-h\">".$title[ 0 ]."</span>".$txt2."<span class=\"elt-d-total-h\">".$title[ 1 ]."</span></td></tr>" ;
						break ;
					case 4 :
						echo "<tr><td class=\"elt-d-total\" colspan=\"".count( $col )."\">".$txt3."<span class=\"elt-d-total-h\">".$title[ 0 ]."</span>".$txt2."<span class=\"elt-d-total-h\">".$title[ 1 ]."</span></td></tr>" ;
						break ;
				}
				break ;


			case "rtf" :
				switch( $lvl ) {
					case 1 :
						$doc->setMainContext()->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "18pt" )
							->addTextLine()->addTextLine( $title );
						break ;
					case 2 :
						$r = $tbl->insertRow();
						$r->height = "4mm" ;

							$c = $r->insertCell();
							$c->width = "275mm" ;
							$c->verticalAlign = CELL_ALIGN_CENTER ;
							$c->setBorders( "ltrb" , "none" );
							$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "14pt" )
								->addTextLine()->addText( $title );
						break ;
					case 3 :
						$r = $tbl->insertRow();
						$r->height = "4mm" ;

							$c = $r->insertCell();
							$c->width = "275mm" ;
							$c->verticalAlign = CELL_ALIGN_CENTER ;
							$c->setBorders( "ltrb" , "none" );
							$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "14pt" )
								->addText( $txt1 )
								->addTag( "b" )->addText( $title[ 0 ] )->addTag( "b0" )
								->addText( $txt2 )
								->addTag( "b" )->addText( $title[ 1 ] )->addTag( "b0" );
						break ;
					case 4 :
						$r = $tbl->insertRow();
						$r->height = "4mm" ;

							$c = $r->insertCell();
							$c->width = "275mm" ;
							$c->verticalAlign = CELL_ALIGN_CENTER ;
							$c->setBorders( "ltrb" , "none" );
							$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "14pt" )
								->addTextLine()->addText( $txt3 )
								->addTag( "b" )->addText( $title[ 0 ] )->addTag( "b0" )
								->addText( $txt2 )
								->addTag( "b" )->addText( $title[ 1 ] )->addTag( "b0" );
						break ;
				}
				break ;

			case "csv" :
				switch( $lvl ) {
					case 1 :
						fputcsv( $doc , array( $title ) , ";"  );
						break ;
					case 2 :
						fputcsv( $doc , array( $title ) , ";"  );
						break ;
					case 3 :
						fputcsv( $doc , array( $txt1." ".$title[ 0 ]." ".$txt2." ".$title[ 1 ] ) , ";"  );
						break ;
					case 4 :
						fputcsv( $doc , array( $txt3." ".$title[ 0 ]." ".$txt2." ".$title[ 1 ] ) , ";"  );
						break ;
				}
				break ;
		}
	}

	function startTable() {
		global $docMode , $doc , $tbl ;
		switch ( $docMode ) {
			case "html" :
				echo "<table align=\"center\" class=\"exp-list-table\">" ;
				break ;

			case "rtf" :
				$tbl = $doc->addTable();
				break ;
		}
	}

	function stopTable(){
		global $docMode ;
		switch ( $docMode ) {
			case "html" :
				echo "</table>" ;
				break ;
		}
	}

	function writeRow( $row , $id = false , $evt = false , $isCap = false ) {
		global $docMode , $resHead , $col , $doc , $tbl ;
		switch ( $docMode ) {
			case "html" :
				echo "<tr".( $id !== false ? " id=\"".$id."\"" : "" ).( $evt !== false ? " ".$evt : "" ).">" ;
				foreach ( $col as &$c ) {
					echo $row[ $c ];
				} unset( $c );
				echo "</tr>" ;

				break ;

			case "rtf" :
				$r = $tbl->insertRow();
				$r->height = "4mm" ;

				foreach ( $col as &$c ) {
					$rtfC = $r->insertCell();
						$rtfC->width = $resHead[ $c ][ 4 ]."px" ;
						$rtfC->verticalAlign = CELL_ALIGN_CENTER ;
						$rtfC->setBorders( "ltrb" , "s" );
						if ( $isCap ) {
							$txtAlign = TEXT_ALIGN_CENTER ;
						} else {
							switch( $resHead[ $c ][ 5 ] ) {
								case "0" :
									$txtAlign = TEXT_ALIGN_LEFT ;
									break ;
								case "1" :
									$txtAlign = TEXT_ALIGN_CENTER ;
									break ;
								case "2" :
									$txtAlign = TEXT_ALIGN_RIGHT ;
									break ;
							}
						}
						$doc->setTableCellContext( $rtfC )->setTextAlign( $txtAlign )->setFontSize( "6pt" )
							->addText( $row[ $c ] );
				} unset( $c );

				break ;

			case "csv" :
				$fd = array();
				foreach ( $col as &$c ) {
					$fd[]= $row[ $c ];
				} unset( $c );
				fputcsv( $doc , $fd , ";" );
				//var_dump( $row );
				break ;
		}
	}

	startDoc();

	$resHeadV = genHeadV();


	foreach( $filter as &$f ) {
		if ( is_array( $f ) ) {
			$f = "( ".implode( " or " , $f )." )" ;
		}
	}
	unset( $f );

	if ( count( $filter ) > 0 ) {
		$filter = " and ".implode( " and " , $filter );
	} else {
		$filter = "" ;
	}

	$qt = ( !$showBySubpoenasOnly ? "
		(
			select
				`t1`.* ,
				`t2`.`exp_id` ,
				`t2`.`price` ,
				`t2`.`pay_date` ,
				`t2`.`pay_details` ,
				`t2`.`application_for_issuance` ,
				`t2`.`sndz` ,
				UNIX_TIMESTAMP( `t2`.`fin_date` ) as `fin_date` ,
				`t3`.`mat_id` ,
				UNIX_TIMESTAMP( `t4`.`date` ) as `date` ,
				`t4`.`ex_data_3` ,
				`t4`.`ex_data_4` ,
				`t4`.`exp_type` ,
				ifnull( `t4`.`group_id` , 0 ) as `group_id` ,
				`t5`.`name` as `agency` ,
				`t6`.`name` as `agent`
			from
				`payments` as `t1` ,
				`expertize` as `t2` ,
				`matincominglvl2` as `t3` ,
				`matincoming` as `t4` ,
				`agency` as `t5` ,
				`agent` as `t6`
			where
				( `t2`.`id` = `t1`.`expertize_id` ) and
				( `t3`.`id` = `t2`.`ext_id` ) and
				( `t4`.`id` = `t3`.`mat_id` ) and
				( `t5`.`id` = `t4`.`from_agency` ) and
				( `t6`.`id` = `t4`.`from_agent` ) and
				( `t1`.`type` <=> 0 ) and
				[[VIEW_STATE_FILTER]]
				[[FILTER]]
		) UNION " : "" )."(
			select
				`t1`.* ,
				`t2`.`exp_id` ,
				( `t7`.`price` / 100 ) as `price` ,
				`t7`.`comment` as `pay_date` ,
				`t7`.`payer` as `pay_details` ,
				0 as `application_for_issuance` ,
				`t2`.`sndz` ,
				UNIX_TIMESTAMP( `t2`.`fin_date` ) as `fin_date` ,
				`t3`.`mat_id` ,
				UNIX_TIMESTAMP( `t4`.`date` ) as `date` ,
				`t4`.`ex_data_3` ,
				`t4`.`ex_data_4` ,
				`t4`.`exp_type` ,
				ifnull( `t4`.`group_id` , 0 ) as `group_id` ,
				`t5`.`name` as `agency` ,
				`t6`.`name` as `agent`
			from
				`payments` as `t1` ,
				`expertize` as `t2` ,
				`matincominglvl2` as `t3` ,
				`matincoming` as `t4` ,
				`agency` as `t5` ,
				`agent` as `t6` ,
				`subpoena-addressee` as `t7`
			where
				( `t2`.`id` = `t1`.`expertize_id` ) and
				( `t3`.`id` = `t2`.`ext_id` ) and
				( `t4`.`id` = `t3`.`mat_id` ) and
				( `t5`.`id` = `t4`.`from_agency` ) and
				( `t6`.`id` = `t4`.`from_agent` ) and
				( `t7`.`p_id` = `t1`.`id` ) and
				( `t1`.`type` <=> 1 ) and
				[[VIEW_STATE_FILTER]]
				[[FILTER]]
		) order by [[VIEW_ORDER]]
	" ;

	$viewStateName = array( "unchecked" => "Неоплаченные" , "checked" => "Оплаченные" );

	$rowID = 0 ;

	foreach( $viewStateParam as $vspn ) {

		$res = array();

		/*if ( count( $yearsFilter ) > 0 ) {
			$cdpv = "( year( from_unixtime( `create_date` ) ) in ( ".implode( " , " , $yearsFilter )." ) )" ;
		} else {
			$cdpv = "( 1 )" ;
		}*/

		$q = str_replace( "[[VIEW_STATE_FILTER]]" , $viewStateFilter[ $vspn ] , $qt );
		$q = str_replace( "[[FILTER]]" , $filter , $q );
		$q = str_replace( "[[VIEW_ORDER]]" , $viewOrderParam[ $vspn ] , $q );

		$res1 = $portalDB->query( $q );
		$res = array_merge( $res , $res1 );

		$ecidl = array();
		$midl = array();  // array of mat_id
		$gridl = array();  // array of group_id
		$pidl = array();
		foreach( $res as &$r ) {
			$pidl[]= $r[ "id" ];
			$ecidl[]= $r[ "expertize_id" ];
			$midl[]= $r[ "mat_id" ];
			if ( !is_null( $r[ "group_id" ] ) && $r[ "group_id" ] != 0 ) {
				$gridl[]= $r[ "group_id" ];
			}
		} unset( $r );
		if ( count( $ecidl ) > 0 ) {
			$expComments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = \"expertize\" ) and ( `ext_id` in ( ?* ) )" , false , "*i" , $ecidl );
		} else {
			$expComments = array();
		}

		$ectm = array();
		foreach ( $expComments as &$ec ) {
			$eid = $ec[ "ext_id" ];
			if ( !isset( $ectm[ $eid ] ) ) {
				$ectm[ $eid ] = array();
			}

			$ectm[ $eid ][]= &$ec ;
		} unset( $ec );

		if ( count( $pidl ) > 0 ) {
			$paymentsMarks = $portalDB->query( "select * from `marks-objects` where ( `ext_id` in ( ?* ) ) and ( `ext_type` = 'payments' )" , false , "*i" , $pidl );
		} else {
			$paymentsMarks = array();
		}

		$pmtm = array();  // pay. marks tree map
		foreach ( $paymentsMarks as &$pm ) {
			$eid = $pm[ "ext_id" ];
			if ( !isset( $pmtm[ $eid ] ) ) {
				$pmtm[ $eid ] = array();
			}

			$pmtm[ $eid ][]= &$pm ;
		} unset( $pm );



		sort( $gridl );
		$gridl = array_unique( $gridl );
		$tmidl = $midl ;

		if ( count( $gridl ) > 0 ) {
			$migidl = $portalDB->query( 'select `id` , `group_id` from `matincoming` where ( `group_id` in ( ?* ) )' , "id" , "*i" , $gridl );
		} else {
			$migidl = array();
		}

		$grMap = array();  // group_id => array of mat_id

		foreach( $migidl as &$r ) {
			$grid = $r[ "group_id" ];
			if ( !isset( $grMap[ $grid ] ) ) {
				$grMap[ $grid ] = array();
			}
			$grMap[ $grid ][]= $r[ "id" ];
			$tmidl[]= $r[ "id" ];
		} unset( $r );

		sort( $midl );
		$midl = array_unique( $midl );

		sort( $tmidl );
		$tmidl = array_unique( $tmidl );

		if ( count( $midl ) < 1 ) {
			$woel = array();
		} else {
			//$woel = $portalDB->query( 'select * from `writ-of-execution` where `ext_id` in ( ?* )' , "id" , "*i" , $tmidl );
			//$portalDB->dbgMode = true ;
			$woel = $portalDB->simpleQuery( "writ-of-execution" , array( "ext_id" => $tmidl ) , "id" );
		}

		$woeMap = array(); // mat_id => array of woe_id
		foreach( $woel as &$woe ) {
			$woeEID = $woe[ 'ext_id' ];
			if ( !isset( $woeMap[ $woeEID ] ) ) {
				$woeMap[ $woeEID ] = array();
			}

			$woeMap[ $woeEID ][]= $woe[ 'id' ];
		} unset( $woe );

		$woeMapPlus = array();
		foreach( $woeMap as $mi => &$me ) {
			if ( isset( $migidl[ $mi ] ) ) {
				$grid = $migidl[ $mi ][ "group_id" ];
				foreach( $grMap[ $grid ] as $mid ) {
					$woeMapPlus[ $mid ] = &$me ;
				}
			}
		} unset( $me );

		$woeMap = $woeMap + $woeMapPlus ;

		//print_r_html( $woel );


		$resTotal = array (
			"count" => 0 ,
			"price" => 0
		);

		$resTotalLocal = array();

		$groupTree = array();

		foreach( $res as &$r ) {
			$gtk = date( $GTK , $r[ $viewGroupParam[ $vspn ] ] );

			if ( !isset( $groupTree[ $gtk ] ) ) {
				$groupTree[ $gtk ] = array();
				$resTotalLocal[ $gtk ]= array(
					"count" => 0 ,
					"price" => 0 ,
					"date" => 0
				);
			}
			$groupTree[ $gtk ][] = &$r ;
			$resTotalLocal[ $gtk ][ "count" ]++ ;
			$resTotalLocal[ $gtk ][ "price" ]+= $r[ "price" ];
			$resTotalLocal[ $gtk ][ "date" ] = $r[ $viewGroupParam[ $vspn ] ];


			$resTotal[ "count" ]++ ;
			$resTotal[ "price" ]+= $r[ "price" ];

			foreach( $resHead as $rhn => $rhv ) {
				$rhv__set_evt = "set_evt__".$rhn ;
				$rhv__cvt = "cvt__".$rhn ;

				switch( $docMode ) {
					case "html" :
						$r[ $rhn ] = "<td id=\"pr_".$rowID."_".$rhv[ 0 ]."\" class=\"elt-d w-".$rhv[ 0 ]." a-".$rhv[ 0 ]."\" ".( $rhv[ 2 ] ? $rhv__set_evt( $r ) : "" ).">".$rhv__cvt( $r )."</td>" ;
						break ;
					case "rtf" :
						$r[ $rhn ] = $rhv__cvt( $r );
						break ;
					case "csv" :
						$r[ $rhn ] = $rhv__cvt( $r );
						break ;
				}
			}

			$rowID++ ;
		} unset( $r );


		writeTitle( $viewStateName[ $vspn ] );

		startTable();

		$col = array_keys( $resHead );

		foreach( $groupTree as $ym => $res ) {
			writeTitle( strftime( $GTK2 , $resTotalLocal[ $ym ][ "date" ] ) , 2 );

			writeRow( $resHeadV , false , false , true );

			foreach( $res as &$r ) {
				writeRow( $r , "tr_cid_".$r[ "id" ] , "onclick=\"trs( event , ".$r[ "id" ]." )\"" );
			} unset( $r );

			writeTitle( array( $resTotalLocal[ $ym ][ "count" ] , money_format( "%!i" , $resTotalLocal[ $ym ][ "price" ] ) ) , 3 );
		}

		writeTitle( array( $resTotal[ "count" ] , money_format( "%!i" , $resTotal[ "price" ] ) ) , 4 );

		stopTable();
	}

	stopDoc();
/*
	http://portal.srv.local/maindb/payments.report.php?i_view_state[]=unchecked&i_show_year[]=2013&i_group_by=day&i_flt__exp_number=&i_flt__exp_date=&i_flt__exp_cc=&i_flt__worker_dep=&i_flt__price=&i_flt__from_agency=&i_flt__from_agent=&i_flt__from_by=&i_flt__from=&i_flt__ex_data_4=&i_flt__pay_date=&i_flt__pay_details=&i_flt__comment=&i_flt_btn_ok=ok
*/
