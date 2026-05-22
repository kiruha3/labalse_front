<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	//phpinfo();
	include_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $UserDepartment
	 * @var $UserWorkerFirstID
	 * @var $UserThemeLoc
	 * @var $dbConfig
	 */
	include_once( "lconfig.php" );
	require_once( '../cores/core.maindb.php' );
	require_once( "subpoenas.core.php" );
	/**
	 * @var $subpoenaAccess
	 * @var $subpoenaEdit
	 */

	$tabWorkersAll = $portalDB->query( "select * from `workers`" , "id" );
	$tabWorkersActual = $portalDB->query( "select * from `workers` where ( `actual` <=> 1 ) order by `name`" , "id" );

	$docTypes = array(
		0 => "Повестка" ,
		1 => "Требование" ,
		2 => "Определение"
	);

	$queryCondition = array();

	switch ( $subpoenaAccess ) {
		case "department" :
			$expertFID = array();
			foreach( $tabWorkersAll as $w ) {
				if ( $UserDepartment == $w[ 'dep' ] ) {
					$expertFID[]= $w[ 'first_id' ];
				}
			}

			$eIDL = array();
			foreach( $tabWorkersAll as $w ) {
				if ( in_array( $w[ 'first_id' ] , $expertFID ) ) {
					$eIDL[]= $w[ 'id' ];
				}
			}

			if ( count( $eIDL ) > 0 ) {
				$sIDL = $portalDB->query( "select `s_id` from `subpoena-experts` where `exp_id` in ( ".implode( "," , $eIDL )." )" );
				$sIDL = array_column( $sIDL , "s_id" );
			} else {
				$sIDL = array();
			}

			if ( count( $sIDL ) > 0 ) {
				$queryCondition[]= "`id` in (".implode( "," , $sIDL ).")" ;
			} else {
				$queryCondition[]= "0" ;
			}
			break ;

		case "all" :
			$expertFID = false ;
			break ;

		default :
			$expertFID = $UserWorkerFirstID ;
			$eIDL = array();
			foreach( $tabWorkersAll as $w ) {
				if ( $w[ 'first_id' ] == $expertFID ) {
					$eIDL[]= $w[ 'id' ];
				}
			}

			if ( count( $eIDL ) > 0 ) {
				$sIDL = $portalDB->query( "select `s_id` from `subpoena-experts` where `exp_id` in ( ".implode( "," , $eIDL )." )" );
				$sIDL = array_column( $sIDL , "s_id" );
			} else {
				$sIDL = array();
			}

			if ( count( $sIDL ) > 0 ) {
				$queryCondition[]= "`id` in (".implode( "," , $sIDL ).")" ;
			} else {
				$queryCondition[]= "0" ;
			}

			break ;
	}

	if ( $expertFID !== false ) {
		if ( !is_array( $expertFID ) ) {
			$expertFID = array( $expertFID );
		}
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
			case "add-subpoena" :
				$d = $data->getAttribute( "d" );
				$m = array();
				$n = preg_match( "/^\\s*([0-2]\\d|3[0-1])[.,-](0\\d|1[0-2])[.,-](?:20)?(\\d{2})\\s*/" , $d , $m );
				$cd = false ;
				if ( $n == 1 ) {
					$m[ 1 ] = intval( $m[ 1 ] );
					$m[ 2 ] = intval( $m[ 2 ] );
					$m[ 3 ] = intval( $m[ 3 ] );
					if ( $m[ 3 ] >= 0 && $m[ 3 ] <= 99 ) {
						$m[ 3 ]+= 2000 ;
					}

					$dc = intval( date( "t" , mktime( 0 , 0 , 0 , $m[ 2 ] , 1 , $m[ 3 ] ) ) );
					if ( $dc >= $m[ 1 ] ) {
						$cd = true ;
						$d = $m ;
					}
				}

				$td = $data->getAttribute( "td" );
				$m = array();
				$n = preg_match( "/^\\s*([0-2]\\d|3[0-1])[.,-](0\\d|1[0-2])[.,-](?:20)?(\\d{2})\\s*/" , $td , $m );
				$ctd = false ;
				if ( $n == 1 ) {
					$m[ 1 ] = intval( $m[ 1 ] );
					$m[ 2 ] = intval( $m[ 2 ] );
					$m[ 3 ] = intval( $m[ 3 ] );
					if ( $m[ 3 ] >= 0 && $m[ 3 ] <= 99 ) {
						$m[ 3 ]+= 2000 ;
					}

					$dc = intval( date( "t" , mktime( 0 , 0 , 0 , $m[ 2 ] , 1 , $m[ 3 ] ) ) );
					if ( $dc >= $m[ 1 ] ) {
						$ctd = true ;
						$td = $m ;
					}
				}

				$tt = $data->getAttribute( "tt" );
				$m = array();
				$n = preg_match( "/^\\s*([0-1]\\d|2[0-3])[.,-\\:]([0-5]\\d)\\s*/" , $tt , $m );
				$ctt = false ;
				if ( $n == 1 ) {
					$m[ 1 ] = intval( $m[ 1 ] );
					$m[ 2 ] = intval( $m[ 2 ] );
					$ctt = true ;
					$tt = $m ;
				}

				$te = $data->getAttribute( "te" );
				$as = $data->getAttribute( "as" );
				$aa = iconv( "utf8" , "cp1251" , $data->nodeValue );
				$dt = $data->getAttribute( "dt" );

				if ( !( $cd && $ctd && $ctt ) ) {
					echo "<result state=\"err\">Неверный формат даты</result>" ;
				} else {
					$d = mktime( 0 , 0 , 0 , $d[ 2 ] , $d[ 1 ] , $d[ 3 ] );
					$td = mktime( $tt[ 1 ] , $tt[ 2 ] , 0 , $td[ 2 ] , $td[ 1 ] , $td[ 3 ] );
					$portalDB->noResult( "insert into `subpoena` ( `date` , `agency_id` , `address` , `to_date` , `type` ) values ( ? , ? , ? , ? , ? )" , "iisii" , $d , $as , $aa , $td , $dt );
					$lid = $portalDB->lastInsertID();
					$te = explode( "," , $te );
					$agencyData = $portalDB->row( "select * from `agency` where `id` = ?" , "i" , $as );
					$tena = array();
					foreach( $te as &$ce ) {
						if ( isset( $tabWorkersAll[ $ce ] ) && !is_null( $tabWorkersAll[ $ce ][ "ad-login" ] ) ) {
							//$tena[]= $tabWorkersAll[ $ce ][ "ad-login" ];
						}
						$ce = $lid." , ".Int2SQL( $ce );
					} unset( $ce );
					$tena[]= "test-user" ;
					$tena[]= "uw-dan" ;
					$te = "(".implode( "),(" , $te ).")";
					$portalDB->noResult( "insert into `subpoena-experts` ( `s_id` , `exp_id` ) values ".$te.";" );
					sendJabberMessage( $tena , "на Ваше имя зарегистрировано [b]".$docTypes[ $dt ]."[/b]\r\n\tиз [b]".$agencyData[ "name" ]."[/b]\r\n\tна [b]".date( "d.m.Y H:i" , $td )."[/b]" );
					echo "<result state=\"ok\" />" ;
				}

				break ;

			case "get-subpoena" :
				$sid = intval( $data->getAttribute( "id" ) );
				$res = $portalDB->row( "select * from `subpoena-list` where ( `id` = ? )" , "i" , $sid );
				$sp = $portalDB->query( "select * from `subpoena-addressee` where ( `s_id` = ? )" , false , "i" , $sid );
				//$sp = QueryAsArray( $con , "select `t1`.* , `t2`.`state` from `subpoena_addressee` as `t1` , `payments` as `t2` where ( `id` = ".Int2SQL( $sid )." ) and ( `t2`.`id` = `t1`.`p_id` )" );
				echo
					"<result " ,
						"num=\"".subpoenaNumber( $sid )."\" " ,
						"d=\"".date( "d-m-Y" , $res[ "date" ] )."\" " ,
						"td=\"".date( "d-m-Y" , $res[ "to_date" ] )."\" " ,
						"tt=\"".date( "H:i" , $res[ "to_date" ] )."\" " ,
						"t=\"".$res[ "type" ]."\" " ,
						"toa=\"".$res[ "toa" ]."\" " ,
						"ay=\"".$res[ "agency_id" ]."\" " ,
						"mc=\"".( count( $sp ) == 0 ? "1" : "0" )."\" " ,
						"exp=\"".$res[ "experts" ]."\">" ,
						"<addr>".toCDATA( $res[ "address" ] )."</addr>" ,
						"<agency>".toCDATA( $res[ "agency" ] )."</agency>
				</result>" ;
				break ;

			case "change-subpoena" :
				$sid = $data->getAttribute( "id" );
				$d = $data->getAttribute( "d" );
				$m = array();
				$n = preg_match( "/^\\s*([0-2]\\d|3[0-1])[.,-](0\\d|1[0-2])[.,-](?:20)?(\\d{2})\\s*/" , $d , $m );
				$cd = false ;
				if ( $n == 1 ) {
					$m[ 1 ] = intval( $m[ 1 ] );
					$m[ 2 ] = intval( $m[ 2 ] );
					$m[ 3 ] = intval( $m[ 3 ] );
					if ( $m[ 3 ] >= 0 && $m[ 3 ] <= 99 ) {
						$m[ 3 ]+= 2000 ;
					}

					$dc = intval( date( "t" , mktime( 0 , 0 , 0 , $m[ 2 ] , 1 , $m[ 3 ] ) ) );
					if ( $dc >= $m[ 1 ] ) {
						$cd = true ;
						$d = $m ;
					}
				}

				$td = $data->getAttribute( "td" );
				$m = array();
				$n = preg_match( "/^\\s*([0-2]\\d|3[0-1])[.,-](0\\d|1[0-2])[.,-](?:20)?(\\d{2})\\s*/" , $td , $m );
				$ctd = false ;
				if ( $n == 1 ) {
					$m[ 1 ] = intval( $m[ 1 ] );
					$m[ 2 ] = intval( $m[ 2 ] );
					$m[ 3 ] = intval( $m[ 3 ] );
					if ( $m[ 3 ] >= 0 && $m[ 3 ] <= 99 ) {
						$m[ 3 ]+= 2000 ;
					}

					$dc = intval( date( "t" , mktime( 0 , 0 , 0 , $m[ 2 ] , 1 , $m[ 3 ] ) ) );
					if ( $dc >= $m[ 1 ] ) {
						$ctd = true ;
						$td = $m ;
					}
				}

				$tt = $data->getAttribute( "tt" );
				$m = array();
				$n = preg_match( "/^\\s*([0-1]\\d|2[0-3])[.,-\\:]([0-5]\\d)\\s*/" , $tt , $m );
				$ctt = false ;
				if ( $n == 1 ) {
					$m[ 1 ] = intval( $m[ 1 ] );
					$m[ 2 ] = intval( $m[ 2 ] );
					$ctt = true ;
					$tt = $m ;
				}

				$te = $data->getAttribute( "te" );
				$as = $data->getAttribute( "as" );
				$aa = iconv( "utf8" , "cp1251" , $data->nodeValue );
				$dt = $data->getAttribute( "dt" );

				if ( !( $cd && $ctd && $ctt ) ) {
					echo "<result state=\"err\">Неверный формат даты</result>" ;
				} else {
					$d = mktime( 0 , 0 , 0 , $d[ 2 ] , $d[ 1 ] , $d[ 3 ] );
					$td = mktime( $tt[ 1 ] , $tt[ 2 ] , 0 , $td[ 2 ] , $td[ 1 ] , $td[ 3 ] );
					$portalDB->noResult( "update `subpoena` set `date` = ".Int2SQL( $d )." , `agency_id` = ".Int2SQL( $as )." , `address` = ".Str2SQL( $aa )." , `to_date` = ".Int2SQL( $td )." , `type` = ".Int2SQL( $dt )." where `id` = ".Int2SQL( $sid ) );
					$portalDB->noResult( "delete from `subpoena-experts` where `s_id` = ".Int2SQL( $sid ) );
					$lid = Int2SQL( $sid );
					$te = explode( "," , $te );
					foreach( $te as &$ce ) {
						$ce = $lid." , ".Int2SQL( $ce );
					} unset( $ce );
					$te = "(".implode( "),(" , $te ).")";
					$portalDB->noResult( "insert into `subpoena-experts` ( `s_id` , `exp_id` ) values ".$te.";" );
					echo "<result state=\"ok\" />" ;
				}

				break ;

			/*case "get_joined" :
				$lid = intval( $data->getAttribute( "id" ) );
				$licenseUsers = QueryAsArray( $con , "select * from `license_user` where ( `lic_id` = ".Int2SQL( $lid )." ) order by `user` asc ;" );
				echo "<result>" ;
				foreach( $licenseUsers as $l ) {
					echo "<data d=\"".date( "d-m-Y" , $l[ "date" ] )."\"><user>".toCDATA( $l[ "user" ] )."</user></data>" ;
				}
				echo "</result>" ;
				break ;

			case "get_license_user" :
				echo "<result>".gethostbyaddr( $_SERVER[ "REMOTE_ADDR" ] )."</result>" ;
				break ;

			case "get_key" :
				$lid = intval( $data->getAttribute( "id" ) );
				$lic = RowAsArray( $con , "select `t1`.* , count( `t2`.`lic_id` ) as `lic_used` from `licenses` as `t1` left outer join `license_user` as `t2` on `t1`.`id` = `t2`.`lic_id` where ( `t1`.`id` = ".Int2SQL( $lid )." ) group by `t2`.`lic_id` ;" );
				if ( $lic[ "lic_count" ] > $lic[ "lic_used" ] ) {
					NoResultQuery( $con , "insert into `license_user` ( `lic_id` , `user` , `date` ) values ( ".$lid." , ".Str2SQL( $data->nodeValue )." , ".Int2SQL( time() )." );" );
					echo "<result state=\"ok\">".toCDATA( $lic[ "key" ] )."</result>" ;
				} else {
					echo "<result state=\"expired\" />" ;
				}

				break ;*/

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

	MainHead_L2(
		"База - Повестки" , "База - Повестки" ,
		array(
			"%UT/subpoenas.css" ,
			"../%UT/buttons.css"
		) ,
		array(
			"files/subpoenas.js" ,
			"#var UserThemeLoc = \"".$UserThemeLoc."\" ;" ,
			"/ext-lib/pdf.js/build/pdf.js" ,
			"/ext-lib/pdf.js/build/pdf.worker.js" ,
			'@/files/labeling/brother--ql-570/main.js'
		) ,
		"hlp/subpoenas.html"
	);

	$sy = false ;

	$anyFilter = false ;

	if ( isset( $_REQUEST[ "idlist" ] ) ) {
		$sIDL = getIDList( $_REQUEST[ "idlist" ] );
		if ( count( $sIDL ) > 0 ) {
			$queryCondition[]= "`id` in (".implode( "," , $sIDL ).")" ;
		} else {
			$queryCondition[]= "0" ;
		}
        $anyFilter = true ;
	}
	if ( isset( $_REQUEST[ "experts" ] ) ) {
		$eFIDL = getIDList( $_REQUEST[ "experts" ] );
		$eIDL = array();
		foreach ( $tabWorkersAll as $w ) {
			if ( in_array( $w[ "first_id" ] , $eFIDL ) ) {
				$eIDL[]= $w[ "id" ];
			}
		}

		if ( count( $eIDL ) > 0 ) {
			$sIDL = $portalDB->query( "select `s_id` from `subpoena-experts` where `exp_id` in ( ".implode( "," , $eIDL )." )" );
			$sIDL = array_column( $sIDL , "s_id" );
		} else {
			$sIDL = array();
		}

		if ( count( $sIDL ) > 0 ) {
			$queryCondition[]= "`id` in (".implode( "," , $sIDL ).")" ;
            $anyFilter = true ;
		}
	}

	if ( isset( $_REQUEST[ "id" ] ) ) {
		$queryCondition[] = "`id` = " . Int2SQL(intval($_REQUEST["id"]));
        $anyFilter = true ;
	}

	if ( isset( $_REQUEST[ 'year' ] ) ) {
		$sy = intval( $_REQUEST[ 'year' ] , 10 );
		if ( ( $sy < 2000 ) || ( $sy > 2100 ) ) {
			$queryCondition = [ 'false' ];
		} else {
			$y1 = mktime( 0 , 0 , 0 , 1 , 1 , $sy );
			$y2 = mktime( 0 , 0 , 0 , 1 , 1 , $sy + 1 );
			$queryCondition[]= "( `date` >= ".$y1." ) and ( `date` < ".$y2." )" ;
		}
        $anyFilter = true ;
	}

    if ( isset( $_REQUEST[ 'type' ] ) ) {
        $ttIDL = getIDList( $_REQUEST[ 'type' ] , 1 );
        $tIDL = array();
        foreach( $ttIDL as &$ctid ) {
            if ( isset( $docTypes[ $ctid ] ) ) {
                $tIDL[]= $ctid ;
            }
        } unset( $ctid );

        if ( count( $tIDL ) > 0 ) {
            $queryCondition[]= '( `type` in ( '.implode( ' , ' , $tIDL ).' ) )' ;
            $anyFilter = true ;
        }
    }

    if ( isset( $_REQUEST[ 'toa' ] ) ) {
        $tIDL = getIDList( $_REQUEST[ 'toa' ] , 1 );
        if ( $tIDL !== false && count( $tIDL ) > 0 ) {
            $queryCondition[]= '( `toa` in ( '.implode( ' , ' , $tIDL ).' ) )' ;
            $anyFilter = true ;
        }
    }

    if ( !$anyFilter ) {
		$sy = intval( date( 'Y' , time() ) , 10 );
		$y1 = mktime( 0 , 0 , 0 , 1 , 1 , $sy );
		$y2 = mktime( 0 , 0 , 0 , 1 , 1 , $sy + 1 );
		$queryCondition[]= "( `date` >= ".$y1." ) and ( `date` < ".$y2." )" ;
	}

	echo '<div class="date-selector-panel">' ;
	$subpoenaYears = $portalDB->query( "select year( from_unixtime( `date` ) ) as `s_y` from `subpoena` group by `s_y` order by `s_y` desc" );
	foreach( $subpoenaYears as $csy ) {
		$csy = $csy[ "s_y" ];
		if ( $csy == $sy ) {
			echo '<span class="cmon_link"><a>'.$csy.'</a></span>' ;
		} else {
			echo '<span class="mon_link"><a href="subpoenas.php?year='.$csy.'">'.$csy.'</a></span>' ;
		}
	}

	echo '</div>' ;

	$tabSubpoenas = $portalDB->query( "select * from `subpoena-list` where (".implode( ") and (" , $queryCondition ).") order by `date` desc , `id` desc ;" , "id" );
	if ( count( $tabSubpoenas ) > 0 ) {
		$tabScanFiles = //QueryAsArray( $con , "select * from `docs-any` where ( `ext_type` = 'subpoena' ) and ( `ext_id` in ( ".implode( "," , array_keys( $tabSubpoenas ) )." ) ) order by `time` asc ;" );
			$portalDB->query( "select * from `documents` where ( `ext_type` = ? ) and ( `ext_id` in ( ?* ) ) order by `time` asc" , false , "s*i" , "subpoena" , array_keys( $tabSubpoenas ) );
	} else {
		$tabScanFiles = array();
	}

	$scanFilesMap = array();
	foreach ( $tabScanFiles as &$sf ) {
		$sfid = $sf[ "ext_id" ];
		if ( !isset( $scanFilesMap[ $sfid ] ) ) {
			$scanFilesMap[ $sfid ] = array();
		}
		$scanFilesMap[ $sfid ][]= &$sf ;
	} unset( $sf );

	echo '<div>
		<button onclick="showAddNRRDlg()" class="btn3">Добавить</button>
	</div>' ;

	echo '<table id="subpoena-table" align="center" class="slt">
		<tr class="slrh">
			<td class="slch-0">
			</td>
			<td class="slch-1">
				№
			</td>
			<td class="slch-2">
				Дата
			</td>
			<td class="slch-3">
				Куда
			</td>
			<td class="slch-4">
				Когда
			</td>
			<td class="slch-5">
				Кого
			</td>
			<td class="slch-6">
				Скан
			</td>
		</tr>' ;

	$cd = time();

	///print_r_html( $tabWorkersAll );

	foreach( $tabSubpoenas as &$subpoena ) {
		$sid = $subpoena[ "id" ];

		echo "<tr id=\"subpoena_list_row_" , $subpoena[ "id" ] , "\" class=\"slr\">
			<td class=\"slc-0\">
				<div class=\"sub-b-l b-l-at\" onclick=\"showLetterDlg( event , ".$sid." )\" title=\"Этикетка адресная\"><span>Э</span></div>
			</td>
			<td class=\"slc-1\">
				" , ( $subpoenaEdit ? "<a onclick=\"showEditNRRDlg(".$subpoena[ "id" ].")\" class=\"sl-a-e\">".subpoenaNumber( $subpoena[ "id" ] )."</a>" : subpoenaNumber( $subpoena[ "id" ] ) ) , "
			</td>
			<td class=\"slc-2\">
				<span class=\"sl-date\">".date( "d-m-Y" , $subpoena[ "date" ] )."</span>
			</td>
			<td class=\"slc-3\">
				".$subpoena[ "agency" ]."
			</td>
			<td class=\"slc-4\">
				<span class=\"sl-date\">".date( "d-m-Y" , $subpoena[ "to_date" ] )."</span><span class=\"sl-time\">".date( "H:i" , $subpoena[ "to_date" ] )."</span>
			</td>
			<td class=\"slc-5\">" ;
				$se = trim( $subpoena[ "experts" ] );
				$se = explode( "," , trim( $se , "," ) );
				$cel = array();
				foreach ( $se as $wid ) {
					if ( isset( $tabWorkersAll[ $wid ] ) ) {
						$cel[]= NAMES_Format( NAMES_parse( $tabWorkersAll[ $wid ][ "name" ] ) );
					} else {
						$cel[]= "<span class=\"err-worker\">".$wid."</span>" ;
					}
				}
				echo implode( ", " , $cel ) ;
			echo "</td>
			<td class=\"slc-6\">" ;
				if ( isset( $scanFilesMap[ $sid ] ) ) {
					foreach ( $scanFilesMap[ $sid ] as &$sf ) {
						echo "<a href=\"/documents.php?download=".$sf[ "id" ]."\" class=\"scan-lnk\" target=\"_blank\">".$sf[ "name" ]."</a>" ;
					} unset( $sf );
				}
				echo '<a class="su-files-ab" onclick="showFUDlg( '.$sid.' )"></a>' ;
			echo '</td>
		</tr>' ;
	} unset( $subpoena );

	echo '</table>' ;

	$tabTypeOfAgency = $portalDB->table( "type-of-agency" );
	$from_type_of_agency = '<select id="nrr-toa" size="1" class="nrr-toa" onchange="upd( \'nrr-agency-sel\' , \'nrr-toa\' )">' ;
	foreach( $tabTypeOfAgency as $i ) {
		$from_type_of_agency.= "<option value=\"".$i[ "id" ]."\">".inForm( $i[ "name" ] , 1 , false )."</option>" ;
	}
	$from_type_of_agency.= "</select>" ;

	$expList = array();
	$eli = 0 ;
	$elj = 0 ;
	$elc = ceil( count( $tabWorkersActual ) / 3 );
	foreach ( $tabWorkersActual as $w ) {
		if ( $expertFID !== false ) {
			if ( !in_array( $w[ 'first_id' ] , $expertFID ) ) {
				continue ;
			}
		}
		if ( $elj == 0 ) {
			$expList[ $eli ] = array();
		}
		//$expList[ $eli ][ $elj ] = "<input name=\"nnren[]\" type=\"checkbox\" value=\"".$w[ "id" ]."\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) );
		switch ( $w[ "post_1_id" ] ) {
			case 1 :
			case 2 :
			case 3 :
			case 4 :
				$expList[ $eli ][ $elj ] = "<input name=\"nnren[]\" type=\"checkbox\" value=\"".$w[ "id" ]."\"><span class=\"nrr-elt-mark1\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) )."</span>" ;
				break ;
			case 5 :
			case 14 :
			case 17 :
				$expList[ $eli ][ $elj ] = "<input name=\"nnren[]\" type=\"checkbox\" value=\"".$w[ "id" ]."\"><span class=\"nrr-elt-mark2\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) )."</span>" ;
				break ;
			default :
				$expList[ $eli ][ $elj ] = "<input name=\"nnren[]\" type=\"checkbox\" value=\"".$w[ "id" ]."\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) );
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

	$expList = "<table id=\"nrr-elt\" class=\"nrr-elt\">".implode( $expList )."</table>" ;



	//$UserAllWorkers

	echo "<div id=\"nrr-dlg\" class=\"nrr-dlg\" style=\"display : none ;\">
		<div class=\"nrr-t-area\">
			<table class=\"nrr-t\">
				<tr>
					<td class=\"nrr-t-d\">
						Номер и дата регистрации
					</td>
					<td class=\"nrr-t-d\">
						Назначенные дата и время
					</td>
				</tr>
				<tr>
					<td class=\"nrr-t-v\">
						№ <span id=\"nrr-num\">*</span> от <input type=\"text\" id=\"nrr-date\" class=\"nrr-date\" value=\"".date( "d-m-Y" , time() )."\">
					</td>
					<td class=\"nrr-t-v\">
						<input type=\"text\" id=\"nrr-to-date\" class=\"nrr-date\" value=\"".date( "d-m-Y" , time() )."\">
						<input type=\"text\" id=\"nrr-to-time\" class=\"nrr-time\" value=\"".date( "H:i" , time() )."\">
					</td>
				</tr>
				<tr>
					<td colspan=\"2\" class=\"nrr-t-v\">
						Тип документа <select id=\"nrr-type\" class=\"nrr-type\">
							<option value=\"0\" selected>Повестка</option>
							<option value=\"1\">Требование</option>
							<option value=\"2\">Определение</option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan=\"2\" class=\"nrr-t-v\">
						".$from_type_of_agency."
					</td>
				</tr>
				<tr>
					<td colspan=\"2\" class=\"nrr-toa-c\">
						<textarea id=\"nrr-from-agency\" name=\"nrr_from_agency\" class=\"nrr-from-agency\" onkeyup=\"srch( 'nrr-agency-sel' , 'nrr-from-agency' )\"></textarea>
					</td>
				</tr>
				<tr>
					<td colspan=\"2\" class=\"nrr-a-c\">
						<div class=\"nrr-a-c1\">
							<div class=\"nrr-a-c2\">
								<select id=\"nrr-agency-sel\" size=\"2\" class=\"nrr-agency-sel\" onchange=\"agency_select( 'nrr-agency-sel' , 'nrr-from-agency' , '' , true )\" onclick=\"agency_select( 'nrr-agency-sel' , 'nrr-from-agency' , '' , true )\"></select>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan=\"2\" class=\"nrr-t-v\">
						<textarea id=\"nrr-from-agency-alt-address\" class=\"nrr-from-agency-alt-address\"></textarea>
						<div class=\"nrr-from-agency-address-c\">
							<a id=\"nrr-from-agency-address\" class=\"nrr-from-agency-address\" onclick=\"fillAddress()\"></a>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class=\"nrr-elc\">
			<div class=\"nrr-elt-cap\">Эксперты</div>
			<div class=\"nrr-elt-area\">".$expList."</div>
		</div>
		<div class=\"nrr-tool-panel\">
			<a id=\"nrr-lnk-ok\" class=\"nrr-lnk lnk-ok\">Принять</a>
			<a onclick=\"hideAddNRRDlg();\" class=\"nrr-lnk lnk-cancel\">Отмена</a>
		</div>
	</div>" ;

	echo "<div id=\"fu-dlg\" class=\"fu-dlg-wrapper\" style=\"display : none ;\"><div id=\"fu-dlg-bg\" class=\"fu-dlg-bg\"></div>
		<div class=\"fu-dlg\">
			<div class=\"fu-dlg-cap\">Прикрепить файл<div class=\"fu-dlg-close-btn\" onclick=\"hideFUDlg();\"></div></div>
			<div class=\"fu-tabs\">
				<input id=\"fu-tab-1\" name=\"fu-tabs\" type=\"radio\" checked>
				<input id=\"fu-tab-2\" name=\"fu-tabs\" type=\"radio\">
				<label for=\"fu-tab-1\">С сервера</label><label for=\"fu-tab-2\">С компьютера</label>
				<span></span>
				<div>
					<div class=\"fu-tla\">
						<div id=\"fu-tlal\" class=\"fu-tlal\" style=\"display : none ;\"></div>
						<div id=\"fu-tl\" class=\"fu-tl\">" ;
					echo "</div>
					</div>" ;

					echo "<div class=\"fu-pdf-pa\">
						<div id=\"fu-ppa\" class=\"fu-pdf-pw\"><div id=\"fu-pa-sizer\" class=\"fu-pa-sizer\"></div></div>
					</div>" ;

					$cy = date( "Y" , time() );

					//$localDB = new TDB( $dbHost , $dbUser , $dbPassword , $dbLocalDatabase );
					//$yearsList = $localDB->query( "select YEAR( `date` ) as `year` from `matincoming` where ( `date` is not null ) group by YEAR( `date` ) order by YEAR( `date` ) desc ;" );

					echo "
				</div>
				<div class=\"fu-file-select-area\">
					<div>
						<form id=\"fu-file-select-form\" action=\"https://".$dbConfig[ 'engine.addresses.docs.local' ]."/upload-new.manual.php\" method=\"post\" enctype=\"multipart/form-data\">
							<input id=\"fu-cor-id\" name=\"extId\" type=\"hidden\">
							<input name=\"docType\" type=\"hidden\" value=\"1110\">
							<input name=\"redirect\" type=\"hidden\" value=\"https://".$dbConfig[ 'engine.addresses.base' ]."/maindb/subpoenas.php\">
							<input name=\"uf\" type=\"file\" class=\"fu-file\">
						</form>
					</div>
				</div>
			</div>
			<div class=\"fu-toolbar\">
				<a id=\"fu-attache-btn\" class=\"btn3\">Прикрепить</a>
			</div>
		</div>
	</div>" ;

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


	closeHtml();
