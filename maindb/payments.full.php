<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "../core.php" );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $portalDB
	 * @var $UserAllWorkers
	 * @var $UserWorkerID
	 * @var $UserWorkerFirstID
	 * @var $UserDepartment
	 * @var $UserID
	 * @var $UserThemeLoc
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 * @var $paymentsStyles
	 */
	require_once( '../cores/core.maindb.php' );
	require_once( '../shared/share.maindb.php' );

	require_once( '../marks.core.php' );

	if ( isset( $_REQUEST[ "imgs1" ] ) ) {

		$font_dir = "../files/" ;
		$font_name = "verdanab.ttf" ;
		$font_size = 8 ;

		function toUnicodeEntities( $text , $from = "w" ) {
			$text = convert_cyr_string( $text , $from , "i" );
			$uni = "" ;
			for ( $i = 0 , $len = strlen( $text ) ; $i < $len ; $i++ ) {
				$char = $text[ $i ];
				$code = ord( $char );
				$uni.= $code > 175 ? "&#".( 1040 + ( $code - 176 ) ).";" : $char ;
			}
			return $uni ;
		}

		$v_imgs1 = $_REQUEST[ "imgs1" ];
		header( "Content-type: image/png" );

		$mw = 0 ;
		$mh = 56 ;
		$ind = -1 ;
		$tmp = "" ;
		$txt = "" ;

		$tmp = $v_imgs1 ;
		$res = imagettfbbox( $font_size , 0 , $font_dir.$font_name , toUnicodeEntities( $tmp ) );

		$mw = $res[ 2 ] - $res[ 0 ] + 32 ;
		$mh = $res[ 1 ] - $res[ 7 ] + 16 ;
		//print_r( $res );
		$im = imagecreatetruecolor( $mh , $mw ) or die( "Cannot Initialize new GD image stream" );
		$white = imagecolorallocate( $im , 64 , 64 , 64 );
		$black = imagecolorallocate( $im , 255 , 255 , 255 );
		imagefilledrectangle( $im , 0 , 0 , $mh , $mw , $white );
		$cx = round( ( $mh - $res[ 7 ] - $res[ 1 ] ) / 2 ) ;
		$cy = -$res[ 0 ] - 8 ;
		imagettftext( $im , $font_size , 90 , $cx , $mw + $cy , $black , $font_dir.$font_name , toUnicodeEntities( $tmp ) );

      	imagepng( $im );
		imagedestroy( $im );

		exit ;
	}

	TryLoginFromCookie( $PlaceID );

	$modeAJAX = isset( $_REQUEST[ "mode" ] );

	if ( !$LoginOk ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			Redirect( "../auth.php" );
		}
	}

	$mayWOEAdd = false ;
	$mayWOEEdit = false ;

	$mayMarkAdd = false ;

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
			$mayWOEAdd = in_array( "WOE-ADD" , $Rights[ "PAYMENTS" ] );
			$mayWOEEdit = in_array( "WOE-EDIT" , $Rights[ "PAYMENTS" ] );
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

	$workersN = $portalDB->query( "select `id` , `name` , `first_id` , `dep` from `workers`" , "id" );
	foreach( $workersN as &$w ) {
		$w = array( NAMES_Format( NAMES_parse( $w[ "name" ] ) ) , $w[ "first_id" ] , $w[ "dep" ] );
	} unset( $w );

	$marksCatalog = $portalDB->table( 'marks-catalog' , 'id' );

	if ( isset( $_REQUEST[ "mode" ] ) ) {

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
			case "check" :
				$pcid = intval( $data->getAttribute( "id" ) );
				$portalDB->noResult( "update `payments` set `state` = 1 , `check_date` = ? where `id` = ?" , "ii" , time() , $pcid );
				echo "<result />" ;
				break ;

			case "uncheck" :
				$pcid = intval( $data->getAttribute( "id" ) );
				$portalDB->noResult( "update `payments` set `state` = 0 , `check_date` = 0 where `id` = ?" , "i" , $pcid );
				echo "<result />" ;
				break ;

			case "comment" :
				$pcid = intval( $data->getAttribute( "id" ) );
				$eid = intval( $data->getAttribute( "eid" ) );
				$comment = trim( iconv( "utf8" , "cp1251" , $data->nodeValue ) );
				if ( strlen( $comment ) > 0 ) {
					$c = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = 'expertize' ) and ( `ext_id` = ? ) and ( `exp_id` in ( ?* ) );" , false , "i*i" , $eid , $UserAllWorkers );
					if ( $c === false || count( $c ) == 0 ) {
						$portalDB->noResult( "insert into `expertize-comments` ( `ext_type` , `ext_id` , `date` , `exp_id` , `comment` ) values ( 'expertize' , ? , ? , ? , ? )" , "iiis" , $eid , time() , $UserWorkerID , $comment );
					} else {
						$portalDB->noResult( "update `expertize-comments` set `comment` = ? where ( `id` = ? )" , "si" , $comment , $c[ 0 ][ "id" ] );
						if ( count( $c ) > 1 ) {
							$ustd = array();
							for( $i = 1 ; $i < count( $c ) ; $i++ ) {
								$ustd[]= $c[ $i ][ "id" ];
							}
							$portalDB->noResult( "delete from `expertize-comments` where ( `id` in ( ?* ) )" , "*i" , $ustd );
						}
					}
				} else {
					$portalDB->noResult( "delete from `expertize-comments` where ( `ext_type` = 'expertize' ) and ( `ext_id` = ? ) and ( `exp_id` in ( ?* ) )" , "i*i" , $eid , $UserAllWorkers );
				}
				echo "<result />" ;
				break ;
			case "marklist" :
				$pcid = $data->getAttribute( "id" );

				$res = $portalDB->query(
					"select `t1`.* , `t2`.`exp_id` , `t3`.`mat_id` from `payments` as `t1` , `expertize` as `t2` , `matincominglvl2` as `t3` where ( `t2`.`id` = `t1`.`expertize_id` ) and ( `t3`.`id` = `t2`.`ext_id` ) and ( `t1`.`id` in ( ? ) ) order by `t1`.`create_date` desc" ,
					false , "i" , $pcid
				);
				echo "<result>" ;
				foreach( $res as $r ) {
					echo "<i id=\"".$r[ "id" ]."\" number=\"".matincomingNumber( $r[ "mat_id" ] )."\">".toCDATA( $workersN[ $r[ "exp_id" ] ][ 0 ] )."</i>" ;
				}
				echo "</result>" ;
				break ;

			case "setCheckDate" :
				$pcid = intval( $data->getAttribute( "id" ) );
				$ndate = $data->nodeValue ;
				$m = array();
				$n = preg_match( "/^\\s*([0-2]\\d|3[0-1])[.,-](0\\d|1[0-2])[.,-](?:20)?(\\d{2})\\s*$/" , $ndate , $m );
				if ( $n == 1 ) {
					$m[ 1 ] = intval( $m[ 1 ] );
					$m[ 2 ] = intval( $m[ 2 ] );
					$m[ 3 ] = intval( $m[ 3 ] );
					if ( $m[ 3 ] >= 0 && $m[ 3 ] <= 99 ) {
						$m[ 3 ]+= 2000 ;
					}
					$t = mktime( 0 , 0 , 0 , $m[ 2 ] , 1 , $m[ 3 ] );
					$dc = intval( date( "t" , $t ) );
					if ( $dc >= $m[ 1 ] ) {
						$t = mktime( 0 , 0 , 0 , $m[ 2 ] , $m[ 1 ] , $m[ 3 ] );
						$portalDB->noResult( "update `payments` set `check_date` = ? where `id` = ?" , "ii" , $t , $pcid );
						echo '<result state="ok" />' ;
					} else {
						echo '<result state="err" />' ;
					}
				} else {
					echo '<result state="err" />' ;
				}
				break ;
				
			case 'toggle-mark' :
				$mid = $data->getAttribute( "id" );
				$pid = $data->getAttribute( "pid" );
				if ( !$mayMarkAdd ) {
					echo '<result state="err" />' ;
				}
				
				$markLink = $portalDB->simpleRow( 'marks-objects' , array( 'ext_type' => 'payments' , 'ext_id' => $pid , 'mark_id' => $mid ) );
				if ( $markLink === false ) {
					$portalDB->insertRow( 'marks-objects' , array( 'ext_type' => 'payments' , 'ext_id' => $pid , 'mark_id' => $mid ) );
					echo '<result state="ok" action="created" />' ;
				} else {
					//error_log( 'delete marks link : '.$markLink[ 'id' ] );
					$portalDB->deleteRow( 'marks-objects' , $markLink[ 'id' ] );
					echo '<result state="ok" action="deleted" />' ;
				}
				break ;

		}

		exit();
	}

	$cTime = time();
	$cy = intval( date( "Y" , $cTime ) );

	$createDateParam = array();
	if ( $mayViewNew ) {
		$createDateParam[ "new" ] = "( `t1`.`create_date` >= ".( $cTime - $mayViewOldPeriod * 86400 )." )" ;
	}
	if ( $mayViewOld ) {
		$createDateParam[ "old" ] = "( `t1`.`create_date` < ".( $cTime - $mayViewOldPeriod * 86400 )." )" ;
	}

	$hideUnChecked = ( isset( $_REQUEST[ "hideUnChecked" ] ) && $_REQUEST[ "hideUnChecked" ] == "1" );
	$hideChecked = !( isset( $_REQUEST[ "showChecked" ] ) && $_REQUEST[ "showChecked" ] == "1" );
	//$hideUnChecked = false ;
	//$hideChecked = false ;

	$ccgContract = array();
	if ( isset( $TAB_CC_GROUPS[ 0 ] ) ) {
		$ccgContract = $TAB_CC_GROUPS[ 0 ];
	}
	if ( count( $ccgContract ) == 0 ) {
		$ccgContract = array( 0 );
	}


	if ( isset( $_REQUEST[ 'contractFilter' ] ) ) {
		switch ( strtolower( $_REQUEST[ 'contractFilter' ] ) ) {
			case 'all' :
				$contractFilter = '' ;
				break ;

			case 'contract' :
				$contractFilter = '( `t4`.`exp_type` in ( '.implode( ',' , $ccgContract ).' ) ) and' ;
				break ;

			case 'nocontract' :
				$contractFilter = '( `t4`.`exp_type` not in ( '.implode( ',' , $ccgContract ).' ) ) and' ;
				break ;

			case "none" :
				$contractFilter = "( 0 ) and" ;
				break ;
		}
	} else {
		$contractFilter = "" ;
		$_REQUEST[ "contractFilter" ] = "all" ;
	}
	//$contractFilter = "" ;
	//$_REQUEST[ "contractFilter" ] = "all" ;

	if ( isset( $_REQUEST[ "bySubpoenasOnly" ] ) && $_REQUEST[ "bySubpoenasOnly" ] == 1 ) {
		$showBySubpoenasOnly = true ;
	} else {
		$showBySubpoenasOnly = false ;
	}
	//$showBySubpoenasOnly = false ;

	if ( isset( $_REQUEST[ "resultLimit" ] ) && $_REQUEST[ "resultLimit" ] == 1 ) {
		$limit = "limit 20" ;
	} else {
		$limit = "" ;
	}

	//$contractFilter = ( isset( $_REQUEST[ "hideUnChecked" ] ) && $_REQUEST[ "hideUnChecked" ] == "1" );


	$dateRange = $portalDB->row( "select min( `create_date` ) as `mid` , max( `create_date` ) as `mad` from `payments`" );
	$showYears = array();

	for ( $i = intval( date( "Y" , $dateRange[ "mad" ] ) ) ; $i >= intval( date( "Y" , $dateRange[ "mid" ] ) ) ; $i-- ) {
		$showYears[ $i ] = ( isset( $_REQUEST[ "showYear_".$i ] ) && $_REQUEST[ "showYear_".$i ] == "1" );
	}

	$viewStateParam = array();
	$viewOrderParam = array();
	$viewGroupParam = array();
	if ( $mayViewUnChecked && !$hideUnChecked ) {
		$viewStateParam[ "unchecked" ]= "( `t1`.`state` = 0 )" ;
		$viewOrderParam[ "unchecked" ]= "`create_date` desc" ;
		$viewGroupParam[ "unchecked" ]= "create_date" ;
	}
	if ( $mayViewChecked && !$hideChecked ) {
		$viewStateParam[ "checked" ]= "( `t1`.`state` = 1 )" ;
		$viewOrderParam[ "checked" ]= "`check_date` desc" ;
		$viewGroupParam[ "checked" ]= "check_date" ;
	}

	$expertFilter = "" ;
	$expertFilterArray = array();
	$singleExpertMode = false ;

	$GTKs = array(
		"month" => array( "по месяцам" , "Y-m" , "%B %Y" ) ,
		"day" => array( "по дням" , "Y-m-d" , "%e %B %Y" )
	);

	if ( isset( $_REQUEST[ "groupBy" ] ) ) {
		$groupBy = $_REQUEST[ "groupBy" ];
	} else {
		$groupBy = "day" ;
	}

	$GTK = $GTKs[ $groupBy ][ 1 ];
	$GTK2 = $GTKs[ $groupBy ][ 2 ];

	if ( isset( $_REQUEST[ "idlist" ] ) ) {
		$idFilter = getIDList( $_REQUEST[ "idlist" ] );
		if ( count( $idFilter ) > 0 ) {
			$idFilter = "( `t1`.`id` in (".implode( "," , $idFilter ).") ) and " ;
		} else {
			$idFilter = "" ;
		}

	} else {
		$idFilter = "" ;
	}

	//exit();

	switch ( $paymentsAccess ) {
		case "expert" :
			$expertFID = $UserWorkerFirstID ;
			foreach( $workersN as $wid => $w ) {
				if ( $w[ 1 ] == $expertFID ) {
					$expertFilterArray[]= $wid ;
				}
			}
			if ( count( $expertFilterArray ) > 0 ) {
				$expertFilter = "and ( `t2`.`exp_id` in ( ".implode( " , " , $expertFilterArray )." ) )" ;
			} else {
				$expertFilter = "and ( 0 )" ;
			}
			$GTK = "Y-m" ;
			$GTK2 = "%B %Y" ;
			$singleExpertMode = true ;
			break ;

		case "department" :
			if ( isset( $_REQUEST[ "expert" ] ) ) {
				$expertFID = intval( $_REQUEST[ "expert" ] );
				foreach( $workersN as $wid => $w ) {
					if ( $w[ 2 ] == $UserDepartment && $w[ 1 ] == $expertFID ) {
						$expertFilterArray[]= $wid ;
					}
				}
				$singleExpertMode = true ;
			} else {
				$tmpw = array();
				foreach( $workersN as $wid => $w ) {
					if ( $w[ 2 ] == $UserDepartment ) {
						$tmpw[]= $w[ 1 ];
					}
				}

				foreach( $workersN as $wid => $w ) {
					if ( in_array( $w[ 1 ] , $tmpw ) ) {
						$expertFilterArray[]= $wid ;
					}
				}
			}
			$GTK = "Y-m" ;
			$GTK2 = "%B %Y" ;
			if ( count( $expertFilterArray ) > 0 ) {
				$expertFilter = "and ( `t2`.`exp_id` in ( ".implode( " , " , $expertFilterArray )." ) )" ;
			} else {
				$expertFilter = "and ( 0 )" ;
			}
			break ;

		case "all" :
			if ( isset( $_REQUEST[ "expert" ] ) ) {
				$expertFID = intval( $_REQUEST[ "expert" ] );
				foreach( $workersN as $wid => $w ) {
					if ( $w[ 1 ] == $expertFID ) {
						$expertFilterArray[]= $wid ;
					}
				}
				if ( count( $expertFilterArray ) > 0 ) {
					$expertFilter = "and ( `t2`.`exp_id` in ( ".implode( " , " , $expertFilterArray )." ) )" ;
				} else {
					$expertFilter = "and ( 0 )" ;
				}
				$GTK = "Y-m" ;
				$GTK2 = "%B %Y" ;
				$singleExpertMode = true ;
			} else {
			}
			break ;
	}

	$marksCatalogJS = array_values( $marksCatalog );
	foreach( $marksCatalogJS as &$m ) {
		$m[ 'name' ] = trim( iconv( DEF_CODEPAGE , 'utf8' , $m[ 'name' ] ) );
		if ( $m[ 'description' ] ) {
			$m[ 'description' ] = trim( iconv( DEF_CODEPAGE , 'utf8' , $m[ 'description' ] ) );
		}
	} unset( $m );
	$marksCatalogJS = iconv( 'utf8' , DEF_CODEPAGE , json_encode( $marksCatalogJS ) );

	if ( $maySearch && isset( $_REQUEST[ "search" ] ) ) {
		MainHead_L2( "База" , "<a href=\"./\">База</a> - <a href=\"./".getPaymentsAddr()."\">Оплата</a> - Результаты поиска" , array( "../%UT/buttons.css" , "%UT/payments.css" ) , array( "#var marksCatalog = '".$marksCatalogJS."' ;" , "files/payments.js" ) , "hlp/main.html" );
		$searchString = Str2SQL( $_REQUEST[ "i_search" ] );
	} else {
		MainHead_L2( "База" , "<a href=\"./\">База</a> - Оплата" , array( "../%UT/buttons.css" , "%UT/payments.css" ) , array( "#var marksCatalog = '".$marksCatalogJS."' ;" , "files/payments.js" ) , "hlp/main.html" );
		$searchString = "" ;
	}

	//echo "<div id=\"woeDataTooltip\"></div>" ;

	echo "<div class=\"tools-panel\">" ;
		echo"<span class=\"tool-group\"><input id=\"i_show_unchecked\" type=\"checkbox\" ".( !$hideUnChecked ? "checked" : "" )." onclick=\"toggleVisibility()\"> Показать неоплаченные</span>
		<span class=\"tool-group\"><input id=\"i_show_checked\" type=\"checkbox\" ".( !$hideChecked ? "checked" : "" )." onclick=\"toggleVisibility()\"> Показать оплаченные</span>
		|
		<span class=\"tool-group\"><input id=\"i_contracts\" type=\"checkbox\" ".( $_REQUEST[ "contractFilter" ] == "all" || $_REQUEST[ "contractFilter" ] == "contract" ? "checked" : "" )." onclick=\"toggleVisibility()\"> Показать договоры</span>
		<span class=\"tool-group\"><input id=\"i_no_contracts\" type=\"checkbox\" ".( $_REQUEST[ "contractFilter" ] == "all" || $_REQUEST[ "contractFilter" ] == "nocontract" ? "checked" : "" )." onclick=\"toggleVisibility()\"> Показать экспертизы</span>
		<span class=\"tool-group\"><input id=\"i_by_subpoenas_only\" type=\"checkbox\" ".( $showBySubpoenasOnly ? "checked" : "" )." onclick=\"toggleVisibility()\"> Показать только выходы в суд </span>
		| " ;

	$yearsFilter = array();

	//print_r_html( $_REQUEST );

	foreach ( $showYears as $i => $j ) {
		echo "<span class=\"tool-group\"><input id=\"i_show_year_".$i."\" type=\"checkbox\" ".( $j ? "checked" : "" )." onclick=\"toggleYears()\"> ".$i."</span> " ;
		if ( $j ) {
			$yearsFilter[]= $i ;
		}
	}

	echo "| <span class=\"tool-group\">Группировать <select id=\"i_group_by\" onchange=\"toggleVisibility()\">" ;
		foreach( $GTKs as $k => $v ) {
			echo "<option value=\"".$k."\" ".( $k == $groupBy ? "selected" : "" ).">".$v[ 0 ]."</option>" ;
		}
	echo "</select></span>" ;

	echo "| <span class=\"tool-group\"><input id=\"i_result_limit\" type=\"checkbox\" ".( $limit != "" ? "checked" : "" )." onclick=\"toggleResultLimit()\"> Ограничить вывод</span>" ;

	echo "</div>" ;
	echo "<div><a href=\"writ-of-execution.list.php\" class=\"btn3\" target=\"_blank\">Список И/Л</a> <a href=\"utils/logic-control-payments.php\" class=\"btn3\" target=\"_blank\">Логический контроль</a>
	</div>" ;


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
		global $mayCheck ;
		return ( $mayCheck ? "<input id=\"pcid_".$r[ "id" ]."\" type=\"checkbox\" onclick=\"checkPayment( event , ".$r[ "id" ]." );\" ".( $r[ "state" ] > 0 ? "checked" : "" )." autocomplete=\"off\">" : "" );
	}

	function cvt__exp_number( &$r ) {
		global $mayEdit , $cy , $ccgContract ;

		if ( in_array( $r[ "exp_type" ] , $ccgContract ) ) {
			$mid = "<span class=\"elt-d-en-et\">Д</span>" ;
		} else {
			$mid = "" ;
		}

		if ( $cy == intval( $r[ "date_year" ] ) ) {
			$mid = matincomingNumber( $r[ "mat_id" ] ).$mid ;
			$s = "n" ;
		} else {
			$mid = matincomingNumber( $r[ "mat_id" ] )." / ".$r[ "date_year" ].$mid ;
			$s = "h" ;
		}

		if ( $mayEdit ) {
			return "<a class=\"elt-d-en-".$s."-lnk\" href=\"expertize.php?edit=".$r[ "expertize_id" ]."\" target=\"_blank\">".$mid."</a>" ;
		} else {
			return "<span class=\"elt-d-en-".$s."\">".$mid."</span>" ;
		}
	}

	function cvt__worker( &$r ) {
		global $singleExpertMode , $workersN ;
		if ( !$singleExpertMode ) {
			return "<a class=\"elt-d-wn-lnk\" href=\"".getPaymentsAddr()."?expert=".$workersN[ $r[ "exp_id" ] ][ 1 ]."\" target=\"_blank\">".$workersN[ $r[ "exp_id" ] ][ 0 ]."</a>" ;
		} else {
			return $workersN[ $r[ "exp_id" ] ][ 0 ];
		}
	}

	function cvt__create_date( &$r ) {
		return date( "H:i" , $r[ "create_date" ] );
	}

	function cvt__price( &$r ) {
		global $rowID ;
		$tr = money_format( "%!i" , $r[ "price" ] );
		if ( $r[ "type" ] == 1 ) {
			$tr = "<a id=\"spp-".$r[ "id" ]."\" class=\"elt-d-p-lnk\" onclick=\"updSubpoenaPrice(".$r[ "id" ].",".$rowID.")\" data-price=\"".number_format( $r[ "price" ] , 2 , "." , "" )."\">".$tr."</a>" ;
		}
		return $tr ;
	}

	function cvt__from( &$r ) {
		return $r[ "agent" ].", ".$r[ "agency" ].", ".$r[ "ex_data_3" ];
	}

	function cvt__pay_date( &$r ) {
		$pd = $r[ "pay_date" ];

		$checks = array(
			array(
				"p" => '/(?:сч[её]т|сч\.?)\s*[№N]?\s*(\d+)\s*от\s*\d{1,2}\.\d{1,2}\.\d{0,2}(\d{2})\s*(?:г\.|года)?/i' ,
				"r" => '<a href="/bills/bill.print.php?n=${1}&y=20${2}" class="bill-mark" target="_blank">${0}</a>'
			) ,
			/*array(
				"p" => '/[№N]\s*(\d+)\s*от\s*\d{1,2}\.\d{1,2}\.\d{0,2}(\d{2})\s*(?:г\.|года)?/i' ,
				"r" => '<a href="/bills/bill.print.php?n=${1}&y=20${2}" class="bill-mark" target="_blank">${0}</a>'
			) ,*/
			array(
				"p" => '/(?:сч[её]т|сч\.?)\s*[№N]?\s*(\d+)/i' ,
				"r" => '<a href="/bills/bill.print.php?n=${1}&y='.$r[ "date_year" ].'" class="bill-mark" target="_blank" title="Год не указан, выбран '.$r[ "date_year" ].'">${0} <font color="#0f0">?</font></a>'
			)
		);

		foreach ( $checks as &$chk ) {
			if ( preg_match( $chk[ "p" ] , $pd ) == 1 ) {
				$pd = preg_replace( $chk[ "p" ] , $chk[ "r" ] , $pd );
				break ;
			}
		} unset( $chk );

		return ( $r[ "type" ] == 1 ? "<span class=\"subpoena-mark\">Выход в суд</span>" : "" ).$pd ;
	}

	function cvt__pay_details( &$r ) {
		return $r[ "pay_details" ];
	}

	function cvt__check_date( &$r ) {
		global $UserThemeLoc ;
		return ( intval( $r[ "check_date" ] ) == 0 ? "" : "<a onclick=\"setCheckDate( ".$r[ "id" ]." , '".date( "d-m-Y" , $r[ "check_date" ] )."' )\"><img src=\"themes/".$UserThemeLoc."/edit.gif\"></a> ".date( "d-m-Y" , $r[ "check_date" ] ) );
	}

	function cvt__marks( &$r ) {
		global $mayMarkAdd , $pmtm , $marksCatalog , $UserThemeLoc ;
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
			//print_r_html( $pml );
			$c = Marks\integrate( $pml , array( 'mode' => 'simple-inline' , 'id-mark' => true ) , $marksCatalog );
		} else {
			$c = '' ;
		}

		return $c ;
	}

	function cvt__comment( &$r ) {
		global $mayViewComment , $rowID , $ectm , $workersN , $UserAllWorkers ;
		if ( isset( $ectm[ $r[ "expertize_id" ] ] ) && count( $ectm[ $r[ "expertize_id" ] ] ) > 0 ) {
			$c = array();
			$cuct = "" ;
			foreach ( $ectm[ $r[ "expertize_id" ] ] as &$ec ) {
				if ( in_array( $ec[ "exp_id" ] , $UserAllWorkers ) ) {
					$cuct = trim( $ec[ "comment" ] );
				} else {
					$c[]= "<div class=\"uc-comment\"><span id=\"comment-text-".$ec[ "id" ]."\" class=\"uc-text\">".$ec[ "comment" ]."</span><span class=\"uc-author\">".$workersN[ $ec[ "exp_id" ] ][ 0 ]."</span><div style=\"clear : both ;\"></div></div>" ;
				}
			}
			return "<div class=\"uc-area\">".implode( "" , $c )."</div>".( $mayViewComment ? "<span id=\"pcc_".$rowID."\">".$cuct."</span>" : "<center><i><font color=\"#808080\">Комментарий скрыт</i></font></center>" );
		} else {
			return ( $mayViewComment ? "<span id=\"pcc_".$rowID."\"></span>" : "<center><i><font color=\"#808080\">Комментарий скрыт</i></font></center>" );
		}
	}

	function cvt__application_for_issuance( &$r ) {
		global $UserThemeLoc ;
		switch ( $r[ 'application_for_issuance' ] ) {
			case '1' :
				return '<img src="themes/'.$UserThemeLoc.'/state-fin.png" class="elt-d-afi-i">' ;

			case 'not-applicable' :
				return '---' ;

			default :
				return '';
		}
	}

	function cvt__sndz( &$r ) {
		global $UserThemeLoc ;
		return ( $r[ "sndz" ] == "1" ? "сндз" : "" );
	}

	function set_evt__comment( &$r ) {
		global $rowID , $mayComment ;
		if ( $mayComment ) {
			return "onclick=\"editComment( ".$rowID." , ".$r[ "id" ]." , ".$r[ "expertize_id" ]." );\"" ;
		} else {
			return "" ;
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

	function get_flt__application_for_issuance( $flt ) {
		error_log( 'DBG: FLT '.$flt );
		$flt =  trim( $flt );
		switch ( $flt ) {
			case '1' :
				error_log( 'DBG: FLT BRANCH 1' );
				return '( `t2`.`application_for_issuance` = 1 )' ;
			case '0' :
				error_log( 'DBG: FLT BRANCH 2' );
				return '( ( `t2`.`application_for_issuance` = 0 ) or ( `t2`.`application_for_issuance` is null ) )' ;
			default :
				error_log( 'DBG: FLT BRANCH *' );
				return '1' ;
		}
	}

	function get_flt__comment( $flt ) {
		global $portalDB ;
		//error_log( 'DBG: FLT '.$flt );
		//$expComments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = 'expertize' ) and ( MATCH ( `comment` ) AGAINST( ? in boolean mode) )" , false , 's' , $flt );
		$expComments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = 'expertize' ) and ( `comment` like concat( '%' , ? , '%' ) )" , false , 's' , $flt );
		$IDList = array_column( $expComments , 'ext_id' );
		if ( count( $IDList ) == 0 ) {
			return '( 0 )' ;
		} else {
			return '( `t2`.`id` in ( '.implode( ',' , $IDList ).' ) )' ;
		}
	}



	// <Name of function> =>  array( "<html id and class subname>" , "<caption>" , <onclick event handler> , <filtrable> )
	$resHead = array(
		"chk_btn"                  => array( 'sub_name' => "cb"   , 'descr' => ""                 , 'events' => false , 'filter' => false ),
		"exp_number"               => array( 'sub_name' => "en"   , 'descr' => "Номер экспертизы" , 'events' => false , 'filter' => true ),
		"worker"                   => array( 'sub_name' => "wn"   , 'descr' => "Эксперт"          , 'events' => false , 'filter' => false ),
		"create_date"              => array( 'sub_name' => "cd"   , 'descr' => ""                 , 'events' => false , 'filter' => false ),
		"price"                    => array( 'sub_name' => "p"    , 'descr' => "Стоимость"        , 'events' => false , 'filter' => true ),
		"from"                     => array( 'sub_name' => "f"    , 'descr' => "От кого"          , 'events' => false , 'filter' => true ),
		"pay_date"                 => array( 'sub_name' => "pd"   , 'descr' => "Дата платежа"     , 'events' => false , 'filter' => true ),
		"pay_details"              => array( 'sub_name' => "pde"  , 'descr' => "Плательщик"       , 'events' => false , 'filter' => true ),
		"check_date"               => array( 'sub_name' => "ckd"  , 'descr' => "Дата отметки"     , 'events' => false , 'filter' => false ),
		"comment"                  => array( 'sub_name' => "com"  , 'descr' => "Пометки"          , 'events' => true  , 'filter' => true ),
		"application_for_issuance" => array( 'sub_name' => "afi"  , 'descr' => "Заявл. И/Л"       , 'events' => false , 'filter' => true ) ,
		"sndz"                     => array( 'sub_name' => "sndz" , 'descr' => "сндз"             , 'events' => false , 'filter' => false ) ,
		"marks"                    => array( 'sub_name' => "mark" , 'descr' => ""                 , 'events' => true  , 'filter' => false )
	);

	foreach( $resHead as $rhn => $rhv ) {
		if ( $rhv[ 'filter' ] ) {
			$rhvn = "i_flt__".$rhn ;
			$$rhvn = "" ;
			$rhvn = "i_flt__".$rhn."_e" ;
			$$rhvn = "" ;
		}
	}

	if ( $maySearch ) {

		if ( isset( $_REQUEST[ "search" ] ) ) {
			foreach( $resHead as $rhn => $rhv ) {
				if ( $rhv[ 'filter' ] ) {
					$rhvn = "i_flt__".$rhn."_e" ;
					$$rhvn = isset( $_REQUEST[ $rhvn ] ) ? $_REQUEST[ $rhvn ] : "" ;
					if ( $$rhvn == "1" ) {
						$rhvn = "i_flt__".$rhn ;
						$$rhvn = isset( $_REQUEST[ $rhvn ] ) ? $_REQUEST[ $rhvn ] : "" ;
					} else {
						$rhvn = "i_flt__".$rhn ;
						$$rhvn = "" ;
					}
				}
			}

			if ( isset( $_REQUEST[ "i_flt_ft" ] ) && array_key_exists( $_REQUEST[ "i_flt_ft" ] , $resHead ) && $resHead[ $_REQUEST[ "i_flt_ft" ] ][ 'filter' ] ) {
				if ( isset( $_REQUEST[ "i_flt_btn_ok" ] ) && $_REQUEST[ "i_flt_btn_ok" ] == "ok" ) {
					$rhvn = "i_flt__".$_REQUEST[ "i_flt_ft" ]."_e" ;
					$$rhvn = "1" ;
					$rhvn = "i_flt__".$_REQUEST[ "i_flt_ft" ];
					$$rhvn = isset( $_REQUEST[ "i_search" ] ) ? $_REQUEST[ "i_search" ] : "" ;
				} else
				if ( isset( $_REQUEST[ "i_flt_btn_clr" ] ) && $_REQUEST[ "i_flt_btn_clr" ] == "del" ) {
					$rhvn = "i_flt__".$_REQUEST[ "i_flt_ft" ]."_e" ;
					$$rhvn = "" ;
					$rhvn = "i_flt__".$_REQUEST[ "i_flt_ft" ];
					$$rhvn = "" ;
				}
			}
		}

		echo "<div id=\"filter_dialog\" class=\"search-panel\" style=\"display : none ;\">
				<div>
				<form id=\"search-form\" action=\"".getPaymentsAddr()."?search\" method=\"get\">
					<input name=\"search\" type=\"hidden\" value=\"search\">" ;
					foreach( $resHead as $rhn => $rhv ) {
						if ( $rhv[ 'filter' ] ) {
							$rhvn = "i_flt__".$rhn ;
							echo "<input id=\"i_flt__".$rhn."\"  name=\"i_flt__".$rhn."\"  type=\"hidden\" value=\"".$$rhvn."\">" ;
							$rhvn = "i_flt__".$rhn."_e" ;
							echo "<input id=\"i_flt__".$rhn."_e\"  name=\"i_flt__".$rhn."_e\"  type=\"hidden\" value=\"".$$rhvn."\" >" ;
						}
					}

					echo "<input id=\"i_flt_ft\" name=\"i_flt_ft\" type=\"hidden\" value=\"\">
					<input id=\"i_search\" name=\"i_search\" type=\"text\" class=\"search-input\" onkeyup=\"sfkp( event );\" value=\"\"><br>
					<input name=\"i_flt_btn_ok\" type=\"submit\" value=\"ok\"> <input name=\"i_flt_btn_clr\" type=\"submit\" value=\"del\">
				</form>
			</div>
		</div>" ;
	}

	$searchString = array();

	$resHeadV = array();

	foreach( $resHead as $rhn => $rhv ) {
		if ( $rhv[ 'filter' ] ) {
			$rhvn = "i_flt__".$rhn."_e" ;
			if ( $$rhvn == "1" ) {
				$rhvn = "i_flt__".$rhn ;
				$resHeadV[ $rhn ] = "<td class=\"elt-h-".$rhv[ 'sub_name' ]."\"><a onclick=\"fdc( '".$rhn."' , event )\" class=\"flt-lnk-hl\" title=\"применен фильтр: ".htmlspecialchars( $$rhvn )."\">".$rhv[ 'descr' ]."*</a></td>" ;
				$rhvn_ffn = "get_flt__".$rhn ;
				$searchString[]= $rhvn_ffn( $$rhvn );
			} else {
				$resHeadV[ $rhn ] = "<td class=\"elt-h-".$rhv[ 'sub_name' ]."\"><a onclick=\"fdc( '".$rhn."' , event )\" class=\"flt-lnk\">".$rhv[ 'descr' ]."</a></td>" ;
			}
		} else {
			$resHeadV[ $rhn ] = "<td class=\"elt-h-".$rhv[ 'sub_name' ]."\">".$rhv[ 'descr' ]."</td>" ;
		}
	}

	if ( count( $searchString ) > 0 ) {
		$searchString = implode( " and " , $searchString )." and " ;
	} else {
		$searchString = "" ;
	}

	if ( isset( $_REQUEST[ 'marks' ] ) ) {
		$marksIDL = getIDList( $_REQUEST[ 'marks' ] );
	} else {
		$marksIDL = array();
	}

	if ( count( $marksIDL ) > 0 ) {
		//print_r_html( $marksIDL );
		$markedPayments = $portalDB->query( "select * from `marks-objects` where ( `mark_id` in ( ?* ) ) and ( `ext_type` = 'payments' )" , false , "*i" , $marksIDL );
		//print_r_html( $markedPayments );
		if ( count( $markedPayments ) > 0 ) {
			$marksFilter = "( `t1`.`id` in (".implode( "," , array_column( $markedPayments , 'ext_id' ) ).") ) and " ;
		} else {
			$marksFilter = "( 0 ) and " ;
		}
		//print_r_html( $marksFilter );
	} else {
		$marksFilter = '' ;
	}

	$gzCCID = array_merge( $TAB_CC_GROUPS[ 1 ] , $TAB_CC_GROUPS[ 5 ] , $TAB_CC_GROUPS[ 6 ] );
	//print_r_html( $gzCCID , 1 );

		$qt = ( !$showBySubpoenasOnly ? "
			(
				select
					`t1`.* ,
					`t2`.`exp_id` ,
					`t2`.`price` ,
					`t2`.`sndz` ,
					`t2`.`pay_date` ,
					`t2`.`pay_details` ,
					`t2`.`application_for_issuance` ,
					`t3`.`mat_id` ,
					YEAR( `t4`.`date` ) as `date_year` ,
					`t4`.`ex_data_3` ,
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
					[[ID_FILTER]]
					[[MARKS_FILTER]]
					[[SEARCH_STRING]]
					[[VIEW_STATE]] and
					( `t2`.`id` = `t1`.`expertize_id` ) and
					( `t2`.`state` <=> 1 ) and
					( `t3`.`id` = `t2`.`ext_id` ) and
					( `t4`.`id` = `t3`.`mat_id` ) and
					( `t5`.`id` = `t4`.`from_agency` ) and
					( `t6`.`id` = `t4`.`from_agent` ) and
					( `t4`.`exp_type` not in ( ".implode( ',' , $gzCCID )." ) ) and
					( `t1`.`type` <=> 0 ) and
					[[CONTRACT_FILTER]]
					[[CREATE_DATE]]
					[[EXPERT_FILTER]]
					[[LIMIT]]
			) UNION " : "" )."(
				select
					`t1`.* ,
					`t2`.`exp_id` ,
					( `t7`.`price` / 100 ) as `price` ,
					`t2`.`sndz` ,
					`t7`.`comment` as `pay_date` ,
					`t7`.`payer` as `pay_details` ,
					'not-applicable' as `application_for_issuance` ,
					`t3`.`mat_id` ,
					YEAR( `t4`.`date` ) as `date_year` ,
					`t4`.`ex_data_3` ,
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
					[[ID_FILTER]]
					[[MARKS_FILTER]]
					[[SEARCH_STRING]]
					[[VIEW_STATE]] and
					( `t2`.`id` = `t1`.`expertize_id` ) and
					( `t3`.`id` = `t2`.`ext_id` ) and
					( `t4`.`id` = `t3`.`mat_id` ) and
					( `t5`.`id` = `t4`.`from_agency` ) and
					( `t6`.`id` = `t4`.`from_agent` ) and
					( `t7`.`p_id` = `t1`.`id` ) and
					( `t4`.`exp_type` not in ( ".implode( ',' , $gzCCID )." ) ) and
					( `t1`.`type` <=> 1 ) and
					[[CONTRACT_FILTER]]
					[[CREATE_DATE]]
					[[EXPERT_FILTER]]
					[[LIMIT]]
			) order by [[VIEW_ORDER]] [[LIMIT]]
		" ;

	$viewStateName = array( "unchecked" => "Неоплаченные" , "checked" => "Оплаченные" );

	$rowID = 0 ;

	echo "<div id=\"main-tab-container\" class=\"main-tab-container\">
	<div id=\"main-tab-item-menu\" class=\"main-tab-item-menu\">
		<a id=\"item-delete-btn\" class=\"btn3\">Удалить оплату</a>
	</div>" ;

	if ( $UserID == 1 ) {
		//$portalDB->dbgMode = true ;
	}

	if ( $limit != "" ) {
		echo "<div class=\"limit-warn\">Вывод ограничен</div>" ;
	}

	$rowsCount = 0 ;

	foreach( $viewStateParam as $vspn => $vspv ) {
		$vgp = $viewGroupParam[ $vspn ];
		$res = array();

		foreach( $createDateParam as $cdpn => $cdpv ) {
			if ( count( $yearsFilter ) > 0 ) {
				$cdpv.= " and ( year( from_unixtime( `create_date` ) ) in ( ".implode( " , " , $yearsFilter )." ) )" ;
			}

			$q = str_replace( "[[VIEW_STATE]]" , $vspv , $qt );
			$q = str_replace( "[[CREATE_DATE]]" , $cdpv , $q );
			$q = str_replace( "[[VIEW_ORDER]]" , $viewOrderParam[ $vspn ] , $q );
			$q = str_replace( "[[EXPERT_FILTER]]" , $expertFilter , $q );
			$q = str_replace( "[[SEARCH_STRING]]" , $searchString , $q );
			$q = str_replace( "[[CONTRACT_FILTER]]" , $contractFilter , $q );
			$q = str_replace( "[[ID_FILTER]]" , $idFilter , $q );
			$q = str_replace( "[[MARKS_FILTER]]" , $marksFilter , $q );
			$q = str_replace( "[[LIMIT]]" , $limit , $q );
			/*if ( $UserID == 1 ) {
				echo $q."<br><br>" ;
			}*/
			$res1 = $portalDB->query( $q );
			$res = array_merge( $res , $res1 );
		}

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
			$expComments = $portalDB->query( "select * from `expertize-comments` where ( `ext_id` in ( ?* ) ) and ( `ext_type` = 'expertize' )" , false , "*i" , $ecidl );
		} else {
			$expComments = array();
		}

		$ectm = array(); // exp comment tree map
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

		//print_r_html( $pmtm );

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
			$woel = $portalDB->query( 'select * from `writ-of-execution` where `ext_id` in ( ?* )' , "id" , "*s" , $tmidl );
		}

		$woeMap = array();  //  mat_id => array of woe_id
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

		$resTotal = array (
			"count" => 0 ,
			"price" => 0
		);

		$resTotalLocal = array();

		$groupTree = array();

		foreach( $res as &$r ) {
			$gtk = date( $GTK , $r[ $vgp ] );
			//$gtk = date( $GTK , $viewGroupParam[ $vspn ] );

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
				$r[ $rhn ] = "<td id=\"pr_".$rowID."_".$rhv[ 'sub_name' ]."\" class=\"elt-d-".$rhv[ 'sub_name' ]."\" ".( $rhv[ 'events' ] ? $rhv__set_evt( $r ) : "" ).">".$rhv__cvt( $r )."</td>" ;
			}

			$rowID++ ;
		} unset( $r );

		echo "<br><br><br><div class=\"elt-title\">".$viewStateName[ $vspn ]."</div><br>" ;

		echo "<table align=\"center\" class=\"exp-list-table\">" ;

		$col = $paymentsStyles[ $vspn ][ $viewStyle ];

		foreach( $groupTree as $ym => $res ) {
			echo "<tr><td class=\"elt-h-period\" colspan=\"".count( $col )."\">".strftime( $GTK2 , $resTotalLocal[ $ym ][ "date" ] )."</td></tr>" ;
			echo "<tr>" ;
			foreach ( $col as &$c ) {
				echo $resHeadV[ $c ];
			}
			unset( $c );

			echo "</tr>" ;

			foreach( $res as &$r ) {
				echo "<tr id=\"tr_cid_".$r[ "id" ]."\" onclick=\"trs( event , ".$r[ "id" ]." )\" class=\"elt-row\">" ;
				foreach ( $col as &$c ) {
					echo $r[ $c ];
				} unset( $c );

				if ( isset( $woeMap[ $r[ "mat_id" ] ] ) ) {
					foreach( $woeMap[ $r[ "mat_id" ] ] as $woeID ) {
						$cw = $woel[ $woeID ];
						if ( $mayWOEEdit ) {
							echo "<td class=\"woe-num\"><a href=\"writ-of-execution.php?edit=", $woeID ,"\" class=\"woe-num-lnk" , ( $cw[ "state" ] == 1 ? " woe-closed" : "" ) , "\" target=\"_blank\" onmouseover=\"$.Tooltip.tooltip( this , ".$woeID." )\" onmouseout=\"$.Tooltip.hide_info( this )\">" , $cw[ "num" ] , "</a></td>" ;
						} else {
							echo "<td class=\"woe-num\"><a href=\"writ-of-execution.php?show=", $woeID ,"\" class=\"woe-num-lnk" , ( $cw[ "state" ] == 1 ? " woe-closed" : "" ) , "\" target=\"_blank\" onmouseover=\"$.Tooltip.tooltip( this , ".$woeID." )\" onmouseout=\"$.Tooltip.hide_info( this )\">" , $cw[ "num" ] , "</a></td>" ;
						}
					}
				}

				if ( $mayWOEAdd ) {
					echo "<td class=\"\"><a href=\"writ-of-execution.php?create=", $r[ "mat_id" ] ,"\" class=\"woe-add-lnk\" title=\"Добавить И/Л\" target=\"_blank\"><img src=\"themes/".$UserThemeLoc."/btn_add.bmp\"></a></td>" ;
				}

				echo "</tr>" ;
				$rowsCount++ ;
			}
			unset( $r );

			echo "<tr><td class=\"elt-d-total\"></td><td class=\"elt-d-total\" colspan=\"".count( $col )."\">экспертиз за период: <span class=\"elt-d-total-h\">".$resTotalLocal[ $ym ][ "count" ]."</span> на общую сумму <span class=\"elt-d-total-h\">".money_format( "%!i" , $resTotalLocal[ $ym ][ "price" ] )."</span></td></tr>" ;
		}

		echo "<tr><td class=\"elt-d-total\"></td><td class=\"elt-d-total\" colspan=\"".count( $col )."\">Всего экспертиз: <span class=\"elt-d-total-h\">".$resTotal[ "count" ]."</span> на общую сумму <span class=\"elt-d-total-h\">".money_format( "%!i" , $resTotal[ "price" ] )."</span></td></tr>" ;

		echo "</table>" ;
	}

	echo "</div>" ;

	closeHtml();
