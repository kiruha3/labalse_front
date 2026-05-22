<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	include( "core.php" );
	/**
	 * @var $LoginOk
	 * @var TDB $portalDB
	 * @var $UserID
	 * @var $timerData
	 * @var $TAB_CASECATEGORY
	 */
	//include( "lconfig.php" );
	// Подключим файл с api
	//include( "ext-lib/sphinxapi.php" );

	fixTimerData( 'login' );

	//TryLoginFromCookie( $PlaceID );
	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	fixTimerData( 'login' );
	fixTimerData( 'main' );

	require_once( 'shared/share.maindb.php' );


	$cache = array();

	function prep( $src , & $mod , & $data ) {
		foreach ( $mod as $mk => & $mv ) {
			$v = '${'.$mk.'[' ;
			$i = 0 ;
			foreach ( $mv as & $n ) {
				switch( $mk ) {
					case "f" :
						$f = $n ;
						$i++ ;
						$src = str_replace( $v.$i."]}" , $f( $data ) , $src );
						break ;

					default :
						$src = str_replace( $v.$n."]}" , $data[ $mk ][ $n ] , $src );
						break ;
				}
			} unset( $n );
		} unset( $mv );
		return $src ;
	}

	function checkLimits( &$l , &$lr , &$sr , $c ) {
		switch ( $l[ 0 ] ) {
			case "local" :
				if ( $l[ 1 ] >= count( $lr ) ) {
					return array( count( $lr ) , false );
				} else {
					return array( max( $l[ 1 ] - 1 , 0 ) , true );
				}
				break ;

			case "seqres" :
				if ( $l[ 1 ] >= count( $sr ) + count( $lr ) ) {
					return array( count( $lr ) , false );
				} else {
					return array( max( $l[ 1 ] - count( $sr ) - 1 , 0 ) , true );
				}
				break ;
		}
	}

	$getIDList = function ( &$r ){
		$b = array();
		$lr = &$r[ "result" ];
		foreach( $lr as &$a ) {
			$b[]= $a[ "fullid" ];
		} unset( $a );
		return implode( "," , array_unique( $b ) );
	};

	$getFirstIDList = function ( &$r ){
		$b = array();
		$lr = &$r[ "result" ];
		foreach( $lr as &$a ) {
			$b[]= $a[ "first_id" ];
		} unset( $a );
		return implode( "," , array_unique( $b ) );
	};



	$subpoenaType = function( &$r ) {
		$tl = array( 0 => "повестка" , 1 => "требование" , 2 => "определение" );
		$lr = &$r[ "r" ];
		$t = $lr[ "type" ];
		if ( !is_null( $t ) && isset( $tl[ $t ] ) ) {
			return $tl[ $t ];
		} else {
			return $tl[ 0 ];
		}
	};

	$subpoenaExpert = function( &$r ) {
		global $tabWorkers ;
		$lr = &$r[ "r" ];
		$widl = array_unique( explode( "," , $lr[ "experts" ] ) );

		$res = array();
		foreach ( $widl as $wid ) {
			if ( isset( $tabWorkers[ $wid ] ) ) {
				$res[]= "[b]".NAMES_Format( NAMES_parse( $tabWorkers[ $wid ][ "name" ] ) , "%F2 %i.%o." )."[/b]" ;
			}
		}

		if ( count( $res ) > 1 ) {
			$le = array_pop( $res );

			return implode( $res , ", " )." и ".$le ;
		} else {
			return implode( $res , ", " );
		}
	};

	$search_Worker = function ( $m ) {
		global $tabWorkers , $cache ;
		$cid = "search_Worker:".base64_encode( $m[ 0 ] );
		if ( isset( $cache[ $cid ] ) ) {
			return $cache[ $cid ];
		}
		$res = array();
		$pos = array();
		$mkpat = function( $s ) {
			return '(?<![а-я])'.$s.'[а-я]*' ;
		};
		$p0 = '[а-я]+' ;
		switch ( count( $m ) ) {
			case 2 :
				$p1 = $mkpat( $m[ 1 ] );
				$pat = '/('.$p1.')/i' ;
				break ;

			case 3 :
				$p1 = $mkpat( $m[ 1 ] );
				$p2 = $mkpat( $m[ 2 ] );

				$pat = '/('.$p1.'\s+'.$p2.')|'.
				        '('.$p1.'\s+'.$p0.'\s+'.$p2.')|'.
				        '('.$p2.'\s+'.$p1.')|'.
				        '('.$p2.'\s+'.$p0.'\s+'.$p1.')/i' ;
				break ;

			case 4 :
				break ;
		}
		foreach ( $tabWorkers as $w ) {
			$cwn = NAMES_Format( NAMES_parse( $w[ "name" ] ) , "%F1 %I1 %O1" , "N1" );
			$m = array();
			$n = preg_match( $pat , $cwn , $m , PREG_OFFSET_CAPTURE );
			if ( $n == 1 ) {
				$res[ $cwn.":".$w[ "first_id" ] ] = array(
					"id" => $w[ "id" ] ,
					"first_id" => $w[ "first_id" ] ,
					"name" => $cwn ,
					"name2" => NAMES_Format( NAMES_parse( $w[ "name" ] ) , "%F2 %I2 %O2" , "N2" )
				);
				$pos[ $cwn.":".$w[ "first_id" ] ] = $m[ 0 ][ 1 ];
			}
		}
		ksort( $pos , SORT_STRING );
		asort( $pos );
		$res2 = array();
		foreach ( $pos as $pk => $pv ) {
			if ( isset( $res[ $pk ] ) ) {
				$res2[] = $res[ $pk ];
			}
		}
		$cache[ $cid ] = $res2 ;
		return $res2 ;
	};

	$search_Spec = function ( $m ) {
		global $tabSpecialities , $cache ;
		$cid = "search_Spec:".base64_encode( $m[ 0 ] );
		if ( isset( $cache[ $cid ] ) ) {
			return $cache[ $cid ];
		}
		$res = array();
		$pos = array();
		foreach ( $tabSpecialities as $s ) {
			$num = $s[ 'index' ].'.'.$s[ 'num' ];
			if ( $m[ 0 ] == $num ) {
				$res[ $s[ 'id' ] ] = array(
					'id' => $s[ 'id' ] ,
					'desc' => $num.' : '.$s[ 'desc' ]
				);
				$pos[ $s[ 'id' ] ] = $num.':'.( $s[ 'use_in_stat' ] == 1 ? 'a' : 'b' ).':'.$s[ 'comment' ];
			}
		}
		asort( $pos , SORT_STRING );
		$res2 = array();
		foreach ( $pos as $pk => $pv ) {
			if ( isset( $res[ $pk ] ) ) {
				$res2[] = $res[ $pk ];
			}
		}
		$cache[ $cid ] = $res2 ;
		return $res2 ;
	};

	$search_CaseCat = function ( $m ) {
		global $TAB_CASECATEGORY , $cache ;
		$cid = "search_CaseCat:".base64_encode( $m[ 0 ] );
		if ( isset( $cache[ $cid ] ) ) {
			return $cache[ $cid ];
		}
		$res = array();
		$pos = array();
		foreach ( $TAB_CASECATEGORY as $s ) {
			$n = inForm( $s[ 'name' ] , 1 , false );
			if ( ( $m[ 0 ] == $s[ 'short_name' ] ) || ( $m[ 0 ] == $n ) ) {
				$res[ $s[ 'id' ] ] = array(
					'id' => $s[ 'id' ] ,
					'desc' => ''.$s[ 'index' ].' - '.$n
				);
				$pos[ $s[ 'id' ] ] = $s[ 'index' ];
			}
		}
		asort( $pos , SORT_STRING );
		$res2 = array();
		foreach ( $pos as $pk => $pv ) {
			if ( isset( $res[ $pk ] ) ) {
				$res2[] = $res[ $pk ];
			}
		}
		$cache[ $cid ] = $res2 ;
		return $res2 ;
	};

	$search_Sphinx = function ( $m , $type ) {
		global $dbConfig , $con , $cache ;
		if ( !( isset( $dbConfig[ "engine.sphinxSearch.enabled" ] ) && $dbConfig[ "engine.sphinxSearch.enabled" ] == 1 ) ) {
			return array();
		}

		$cid = "search_Sphinx:".base64_encode( $type.":".$m[ 0 ] );
		if ( isset( $cache[ $cid ] ) ) {
			return $cache[ $cid ];
		}

		$res = array();
		$cl = new SphinxClient();
		$cl->SetServer( $dbConfig[ "engine.sphinxSearch.server" ] , $dbConfig[ "engine.sphinxSearch.port" ] );
		$cl->SetMatchMode( SPH_MATCH_ALL );

		$kw = array();
		if ( count( $m ) > 1 ) {
			for( $i = 1 ; $i < count( $m ) ; $i++ ) {
				$kw[]= strtolower( $m[ $i ] );
			}
		} else {
			$kw[]= strtolower( $m[ 0 ] );
		}

		//echo "<ft>".$type."</ft>" ;

		switch ( $type ) {
			case "agent" :
				$result = $cl->Query( "\"".implode( " " , $kw )."\"" , "agentTabIndex" );
				echo "<ft>" ;
				echo toCDATA( print_r_html_2( $result , false , true ) );
				echo "</ft>" ;
				$words = array();
				if ( $result !== false && isset( $result[ "words" ] ) ) {
					foreach ( $result[ "words" ] as $k => $d ) {
						if ( $d[ "docs" ] > 0 ) {
							$words[ $k ] = $d[ "docs" ];
						}
					}
				}

				$result = $cl->Query( "\"".implode( " " , $kw )."\"" , "agencyNTabIndex" );
				if ( $result !== false && isset( $result[ "words" ] ) ) {
					foreach ( $result[ "words" ] as $k => $d ) {
						if ( $d[ "docs" ] > 0 ) {
							$words[ $k ] = $d[ "docs" ];
						}
					}
				}

				uasort( $words , function( $a , $b ) {
					return ( $b - $a );
				} );

				if ( count( $kw ) == 1 && isset( $words[ $kw[ 0 ] ] ) ) {
					$res[]= array(
						"id" => sha1( time().":".$kw[ 0 ] ) ,
						"res-type" => "lnk" ,
						"name" => $kw[ 0 ] ,
						"ss" => urlencode( base64_encode( $kw[ 0 ] ) )
					);
					unset( $words[ $kw[ 0 ] ] );
				}

				foreach ( $words as $cwk => $cwc ) {
					$res[]= array(
						"id" => sha1( time().":".$cwk ) ,
						"res-type" => "lnk" ,
						"name" => $cwk ,
						"ss" => urlencode( base64_encode( $cwc ) )
					);
				}

				break ;
		}

		/*echo "<ft>" ;
		echo toCDATA( print_r_html_2( $result , false , true ) );
		echo "</ft>" ;*/

		return $res ;
	};

	$cDate = time();
	$cYear = intval( date( "Y" , $cDate ) );
	$cMonth = intval( date( "m" , $cDate ) );
	$cDay = intval( date( "d" , $cDate ) );
	$yDate = strtotime( "-1 day" , $cDate );
	$yYear = intval( date( "Y" , $yDate ) );
	$yMonth = intval( date( "m" , $yDate ) );
	$yDay = intval( date( "d" , $yDate ) );
	$tDate = strtotime( "+1 day" , $cDate );
	$tYear = intval( date( "Y" , $tDate ) );
	$tMonth = intval( date( "m" , $tDate ) );
	$tDay = intval( date( "d" , $tDate ) );

	$sequences = array(
		array(
			"scope" => "exp" ,
			"seqid" => "matincoming-1" ,
			"base" => "maindb" ,
			"seq" => array(
				array(
					"tmpl" => '0*(\d{1,5})\s*[/]\s*(\d{1,2})\s*[-]\s*(\d)' ,
					"req" => "select matincomingNumber( `t1`.`id` ) as `id` , `t1`.`id` as `fullid` , YEAR( `t1`.`date` ) as `year` , `t5`.`name` as `agency` , `t6`.`name` as `agent` , `ex_data_3` , `ex_data_4` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` , `departments` as `t4` , `agency` as `t5` , `agent` as `t6` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `t4`.`id` = `t2`.`dep_id` ) and ( MATCH( `t1`.`__v_id_reversed` ) AGAINST( CONCAT( RPAD( REVERSE( '\${m[1]}' ) , 6 , '0' ) , '*' ) in boolean mode ) ) and ( `t1`.`id` like matincomingIDPatternWOY( \${m[1]} ) ) and ( `t4`.`ind` = \${m[2]} ) and ( `t1`.`exp_type` = \${m[3]} ) and ( `t1`.`from_agency` = `t5`.`id` ) and ( `t1`.`from_agent` = `t6`.`id` ) group by `t1`.`id` order by `t1`.`id` desc" ,
					"climit" => array( "local" , 3 ),
					"full" => array(
						"type" => "exp1" ,
						"resid" => array( "src" => '${r[fullid]}' , "mod" => array( "r" => array( "fullid" ) ) ) ,
						"text" => array( "src" => 'Экспертиза № [b]${r[id]}/${m[2]}-${m[3]}[/b] за ${r[year]} год, ${r[agent]}, ${r[agency]}, ${r[ex_data_4]}, ${r[ex_data_3]}' , "mod" => array( "m" => array( 1 , 2 , 3 ) , "r" => array( "id" , "year" , "agency" , "agent" , "ex_data_3" , "ex_data_4" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'singlerow&amp;n=${r[id]}&amp;y=${r[year]}' , "mod" => array( "r" => array( "id" , "year" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "expc" ,
						"resid" => array( "src" => 'c0' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b]экспертиз с номером [b]${m[1]}/${m[2]}-${m[3]}[/b]' , "mod" => array( "m" => array( 1 , 2 , 3 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				) ,
				array(
					"tmpl" => '0*(\d{1,5})' ,
					"req" => "select matincomingNumber( `t1`.`id` ) as `id` , `t1`.`id` as `fullid` , YEAR( `t1`.`date` ) as `year` , `t1`.`exp_type` as `type` , `t5`.`name` as `agency` , `t6`.`name` as `agent` , `ex_data_3` , `ex_data_4` from `matincoming` as `t1` , `agency` as `t5` , `agent` as `t6` where ( MATCH( `t1`.`__v_id_reversed` ) AGAINST( CONCAT( RPAD( REVERSE( '\${m[1]}' ) , 6 , '0' ) , '*' ) in boolean mode ) ) and ( `t1`.`id` like matincomingIDPatternWOY( \${m[1]} ) ) and ( `t1`.`from_agency` = `t5`.`id` ) and ( `t1`.`from_agent` = `t6`.`id` )  group by `t1`.`id` order by `t1`.`id` desc" ,
					"climit" => array( "seqres" , 3 ),
					"full" => array(
						"type" => "exp1" ,
						"resid" => array( "src" => '${r[fullid]}' , "mod" => array( "r" => array( "fullid" ) ) ) ,
						"text" => array( "src" => 'Экспертиза № [b]${m[1]}/*-${r[type]}[/b] за ${r[year]} год, ${r[agent]}, ${r[agency]}, ${r[ex_data_4]}, ${r[ex_data_3]}' , "mod" => array( "m" => array( 1 ) , "r" => array( "year" , "type" , "agency" , "agent" , "ex_data_3" , "ex_data_4" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'singlerow&amp;n=${r[id]}&amp;y=${r[year]}' , "mod" => array( "r" => array( "id" , "year" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "expc" ,
						"resid" => array( "src" => 'c3' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] экспертиз с номером [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				) ,
			)
		) ,
		array(
			"scope" => "exp" ,
			"seqid" => "matincoming-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([а-я]+)(?:\s+([а-я]+)+)?' ,
					"req" => $search_Worker ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "exp1" ,
						"resid" => array( "src" => '${r[first_id]}' , "mod" => array( "r" => array( "first_id" ) ) ) ,
						"text" => array( "src" => 'экспертизы [b]${r[name2]}[/b]' , "mod" => array( "r" => array( "name2" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'experts=${r[first_id]}' , "mod" => array( "r" => array( "first_id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "expc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего найдено [b]${c[local]}[/b] экспертов' , "mod" => array( "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'experts=${f[1]}' , "mod" => array( "f" => array( $getFirstIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "exp" ,
			"seqid" => "matincoming-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					//"tmpl" => '([а-я]+)(?:\s+([а-я]+)+)?' ,
					"tmpl" => '((?:[а-я]+)(?:\s+([а-я]+)+)*)' ,
					//"req" => function( $m ) use ( $search_Sphinx ) { return $search_Sphinx( $m , "agent" ); } ,
					"req" =>
						"(
							select 
								`t6`.`id` as `id` ,
								concat( 'agent-' , `t6`.`id` ) as `fullid` ,
								`t5`.`name` as `agency` ,
								`t6`.`name` as `agent` ,
								`t6`.`name` as `r-name` ,
								'agent' as `r-type`
							from
								`matincoming` as `t1` ,
								`agency` as `t5` ,
								`agent` as `t6`
							where
								( MATCH( `t6`.`name` ) AGAINST ( '\${m[1]}' in boolean mode ) ) and
								( `t1`.`from_agency` = `t5`.`id` ) and
								( `t1`.`from_agent` = `t6`.`id` )
							group by `t6`.`id`
						) union distinct (
							select
								`t5`.`id` as `id` ,
								concat( 'agency-' , `t6`.`id` ) as `fullid` ,
								`t5`.`name` as `agency` ,
								`t6`.`name` as `agent` ,
								`t5`.`name` as `r-name` ,
								'agency' as `r-type`
							from
								`matincoming` as `t1` ,
								`agency` as `t5` ,
								`agent` as `t6`
							where
								( MATCH( `t5`.`name` ) AGAINST ( '\${m[1]}' in boolean mode ) ) and
								( `t1`.`from_agency` = `t5`.`id` ) and
								( `t1`.`from_agent` = `t6`.`id` )
							group by `t5`.`id`
						) order by `r-name` asc" ,
					"climit" => array( "local" , 100 ) ,
					"full" => array(
						"type" => "exp1" ,
						'resid' => array( 'src' => '${r[fullid]}' , 'mod' => array( 'r' => array( 'fullid' ) ) ) ,
						//"res-type" => array( "src" => '${r[res-type]}' , "mod" => array( "r" => array( "res-type" ) ) ) ,
						'text' => array( "src" => 'Поиск по заказчику [b]${r[agent]}, ${r[agency]}[/b]' , 'mod' => array( 'r' => array( 'agent' , 'agency' ) ) ) ,
						'datas' => array(
							'1' => array( "src" => 'singlerow&amp;${r[r-type]}=${r[id]}' , 'mod' => array( 'r' => array( 'r-type' , 'id' ) ) )
						)
					)/* ,
					"limited" => array(
						"type" => "expc" ,
						"resid" => array( "src" => 'c0' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b]заказчиков [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)*/
				)
			)
		) ,
		array(
			"scope" => "exp" ,
			"seqid" => "matincoming-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '(\d{1,2}\.\d)' ,
					"req" => $search_Spec ,
					"climit" => array( "local" , 10 ) ,
					"full" => array(
						"type" => "exp1" ,
						"resid" => array( 'src' => '${r[id]}' , 'mod' => array( 'r' => array( 'id' ) ) ) ,
						"text" => array( 'src' => 'экспертизы по специальности [b]${r[desc]}[/b]' , 'mod' => array( 'r' => array( 'desc' ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'spec=${r[id]}' , 'mod' => array( 'r' => array( 'id' ) ) )
						)
					) ,
					'limited' => array(
						'type' => "expc" ,
						'resid' => array( 'src' => 'c1' , 'mod' => array() ) ,
						'text' => array( 'src' => 'Всего найдено [b]${c[local]}[/b] специальностей' , 'mod' => array( 'c' => array( 'local' ) ) ) ,
						'datas' => array(
							'1' => array( 'src' => 'spec=${f[1]}' , 'mod' => array( 'f' => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "exp" ,
			"seqid" => "matincoming-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '(договоры|дог|уголовные|уг|гражданские|гражд|арбитражные|арб|административные|адм|По\s+материалам\s+проверки|кусп)' ,
					"req" => $search_CaseCat ,
					"climit" => array( "local" , 10 ) ,
					"full" => array(
						"type" => "exp1" ,
						"resid" => array( 'src' => '${r[id]}' , 'mod' => array( 'r' => array( 'id' ) ) ) ,
						"text" => array( 'src' => 'экспертизы категории [b]${r[desc]}[/b]' , 'mod' => array( 'r' => array( 'desc' ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'category=${r[id]}' , 'mod' => array( 'r' => array( 'id' ) ) )
						)
					) ,
					'limited' => array(
						'type' => "expc" ,
						'resid' => array( 'src' => 'c1' , 'mod' => array() ) ,
						'text' => array( 'src' => 'Всего найдено [b]${c[local]}[/b] категорий' , 'mod' => array( 'c' => array( 'local' ) ) ) ,
						'datas' => array(
							'1' => array( 'src' => 'category=${f[1]}' , 'mod' => array( 'f' => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		/*array(
			"scope" => "exp" ,
			"seqid" => "matincoming-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '(\d+)' ,
					"req" => "select matincomingNumber( `t1`.`id` ) as `id` , `t1`.`id` as `fullid` , YEAR( `t1`.`date` ) as `year` , `t5`.`name` as `agency` , `t6`.`name` as `agent` , `ex_data_3` , `ex_data_4` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` , `departments` as `t4` , `agency` as `t5` , `agent` as `t6` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `t4`.`id` = `t2`.`dep_id` ) and ( MATCH( `t1`.`__v_id_reversed` ) AGAINST( CONCAT( RPAD( REVERSE( '\${m[1]}' ) , 6 , '0' ) , '*' ) in boolean mode ) ) and ( `t1`.`id` like matincomingIDPatternWOY( \${m[1]} ) ) and ( `t4`.`ind` = \${m[2]} ) and ( `t1`.`exp_type` = \${m[3]} ) and ( `t1`.`from_agency` = `t5`.`id` ) and ( `t1`.`from_agent` = `t6`.`id` ) group by `t1`.`id` order by `t1`.`id` desc" ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "exp1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"res-type" => array( "src" => '${r[res-type]}' , "mod" => array( "r" => array( "res-type" ) ) ) ,
						"text" => array( "src" => 'Поиск по заказчику [b]${r[name]}[/b]' , "mod" => array( "r" => array( "name" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'experts=${r[ss]}' , "mod" => array( "r" => array( "ss" ) ) )
						)
					)
				)
			)
		) ,*/
		array(
			"scope" => "bills" ,
			"seqid" => "bill-1" ,
			"base" => "bills" ,
			"seq" => array(
				array(
					"tmpl" => '0*(\d{1,5})' ,
					"req" => "select `t1`.`id` , `t1`.`id` as `fullid` , `t1`.`number` , DATE_FORMAT( `t1`.`date` , '%d.%m.%Y' ) as `date` , `payer` , group_concat( `t2`.`name` separator ', ' ) as `item_name` from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t1`.`number` = \${m[1]} ) group by `t1`.`id` order by `t1`.`id` desc" ,
					"climit" => array( "local" , 3 ),
					"full" => array(
						"type" => "bill1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Счет № [b]${r[number]}[/b] от ${r[date]}, ${r[payer]}, ${r[item_name]}' , "mod" => array( "r" => array( "number" , "date" , "payer" , "item_name" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'id=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "billc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] счетов с номером [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "bills" ,
			"seqid" => "bill-2" ,
			"base" => "bills" ,
			"seq" => array(
				array(
					"tmpl" => '0*(\d{1,5})\s*[/]\s*(\d{1,2})\s*[-]\s*(\d)' ,
					"req" => "select `t1`.`id` , `t1`.`id` as `fullid` , `t1`.`number` , DATE_FORMAT( `t1`.`date` , '%d.%m.%Y' ) as `date`, `payer` , group_concat( `t2`.`name` separator ', ' ) as `item_name`  from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( MATCH( `t2`.`name` ) AGAINST( '\${m[1]}' ) ) and ( `t2`.`name` RLIKE '([^0-9]|^)\${m[1]}[[:blank:]]*[/][[:blank:]]*\${m[2]}[[:blank:]]*[-][[:blank:]]*\${m[3]}([^0-9]|\$)' ) group by `t1`.`id` order by `t1`.`id` desc" ,
					"climit" => array( "local" , 3 ),
					"full" => array(
						"type" => "bill1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => '[b]${m[1]}/${m[2]}-${m[3]}[/b] встречается в счете № [b]${r[number]}[/b] от ${r[date]}, ${r[payer]}, ${r[item_name]}' , "mod" => array( "r" => array( "number" , "date" , "payer" , "item_name" ) , "m" => array( 1 , 2 , 3 ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'id=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "billc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] счетов за экспертизу [b]${m[1]}/${m[2]}-${m[3]}[/b]' , "mod" => array( "m" => array( 1 , 2 , 3 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				) ,
				array(
					"tmpl" => '0*(\d{1,5})' ,
					//"req" => "select `t1`.`id` , `t1`.`id` as `fullid` , `t1`.`number` , DATE_FORMAT( `t1`.`date` , '%d.%m.%Y' ) as `date`, `payer` , group_concat( `t2`.`name` separator ', ' ) as `item_name`  from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`name` RLIKE '([^0-9]|^)\${m[1]}([^0-9]|\$)' ) group by `t1`.`id` order by `t1`.`id` desc" ,
					"req" => "select `t1`.`id` , `t1`.`id` as `fullid` , `t1`.`number` , DATE_FORMAT( `t1`.`date` , '%d.%m.%Y' ) as `date`, `payer` , group_concat( `t2`.`name` separator ', ' ) as `item_name`  from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( MATCH( `t2`.`name` ) AGAINST( '\${m[1]}' ) ) group by `t1`.`id` order by `t1`.`id` desc" ,
					"climit" => array( "local" , 3 ),
					"full" => array(
						"type" => "bill1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => '[b]${m[1]}[/b] встречается в счете № [b]${r[number]}[/b] от ${r[date]}, ${r[payer]}, ${r[item_name]}' , "mod" => array( "r" => array( "number" , "date" , "payer" , "item_name" ) , "m" => array( 1 ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'id=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "billc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] счетов с текстом [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "pay" ,
			"seqid" => "pay-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([а-яА-Я]{2})\s*№?\s*(\d{1,9})' ,
					"req" => "select * , `id` as `fullid` from `writ-of-execution` where `num` like '\${m[1]} № \${m[2]}%' order by `id` desc" ,
					"climit" => array( "local" , 2 ) ,
					"full" => array(
						"type" => "pay1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Исполнительный лист № [b]${r[num]}[/b]' , "mod" => array( "r" => array( "num" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'edit=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "payc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] Исполнительных листов с номером [b]${m[1]} № ${m[2]}[/b]' , "mod" => array( "m" => array( 1 , 2 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				) ,
				// 36RS0038#2-5/2023#1
				array(
					"tmpl" => '(\d{1,2}RS\d{4}\#\d-\d+\/20\d{2}\#\d)' ,
					"req" => "select * , `id` as `fullid` from `writ-of-execution` where `num` = '\${m[1]}' order by `id` desc" ,
					"climit" => array( "local" , 2 ) ,
					"full" => array(
						"type" => "pay1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Исполнительный лист № [b]${r[num]}[/b]' , "mod" => array( "r" => array( "num" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'edit=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "payc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] Исполнительных листов с номером [b]${m[1]} № ${m[2]}[/b]' , "mod" => array( "m" => array( 1 , 2 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				) ,
				array(
					"tmpl" => '(?:[а-яА-Я]{2})\s*№?\s*(\d{1,9})' ,
					"req" => "select * , `id` as `fullid` from `writ-of-execution` where `num` like '__ № \${m[1]}%'  order by `id` desc" ,
					"climit" => array( "seqres" , 3 ) ,
					"full" => array(
						"type" => "pay1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Исполнительный лист № [b]${r[num]}[/b]' , "mod" => array( "r" => array( "num" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'edit=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "payc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] Исполнительных листов с номером [b]** № ${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				) ,
				array(
					"tmpl" => '(\d{3,9})' ,
					"req" => "select * , LOCATE( '\${m[1]}' , `num` ) as `nloc` , `id` as `fullid` from `writ-of-execution` having `nloc` > 0 order by `nloc`" ,
					"climit" => array( "local" , 2 ) ,
					"full" => array(
						"type" => "pay1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Исполнительный лист № [b]${r[num]}[/b]' , "mod" => array( "r" => array( "num" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'edit=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "payc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] Исполнительных листов с номером, содержащим [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "pay" ,
			"seqid" => "pay-2" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([0-9/]{1,15}(?:\-ИП)?)' ,
					"req" => "select * , `id` as `fullid` from `writ-of-execution` where `ep_num` like '%\${m[1]}%' order by `id` desc" ,
					"climit" => array( "local" , 2 ) ,
					"full" => array(
						"type" => "pay1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Исполнительный лист с № исполнительного производства [b]${r[ep_num]}[/b]' , "mod" => array( "r" => array( "ep_num" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'edit=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "payc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] Исполнительных листов с номером исполнительного произволства [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "pay" ,
			"seqid" => "pay-3" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '(А?\d{1,3}-\d{1,6}/(?:(?:20)?\d{1,2})?)' ,
					"req" => "select * , `id` as `fullid` from `writ-of-execution` where `case_num` like '%\${m[1]}%' order by `id` desc" ,
					"climit" => array( "local" , 2 ) ,
					"full" => array(
						"type" => "pay1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Исполнительный лист с № дела [b]${r[case_num]}[/b]' , "mod" => array( "r" => array( "case_num" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'edit=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "payc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] Исполнительных листов с номером дела [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "pay" ,
			"seqid" => "pay-3" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([-А-Я\w\s]{1,500})' ,
					//"req" => "select `t1`.* , `t1`.`id` as `fullid` , `t2`.`payer` from `writ-of-execution` as `t1` , `writ-of-execution-payers` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and  ( `t2`.`payer` like '%\${m[1]}%' ) order by `t2`.`payer` desc" ,
					"req" => "select `t1`.* , `t1`.`id` as `fullid` , `t2`.`payer` from `writ-of-execution` as `t1` , `writ-of-execution-payers` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and  ( MATCH( `t2`.`payer` ) AGAINST ( '\${m[1]}' ) ) order by `t2`.`payer` desc" ,
					"climit" => array( "local" , 2 ) ,
					"full" => array(
						"type" => "pay1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Исполнительный лист с плательщиком [b]${r[payer]}[/b]' , "mod" => array( "r" => array( "payer" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'edit=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "payc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] Исполнительных листов с плательщиком [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "pay" ,
			"seqid" => "pay-4" ,
			"base" => "portal" ,
			"seq" => array (
				array(
					"tmpl" => '([а-я]+)(?:\s+([а-я]+)+)?' ,
					"req" => $search_Worker ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "pay2" ,
						"resid" => array( "src" => '${r[first_id]}' , "mod" => array( "r" => array( "first_id" ) ) ) ,
						"text" => array( "src" => 'оплата для [b]${r[name]}[/b]' , "mod" => array( "r" => array( "name" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'expert=${r[first_id]}' , "mod" => array( "r" => array( "first_id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "payn" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего найдено [b]${c[local]}[/b] экспертов' , "mod" => array( "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => '' , "mod" => array() )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "timet" ,
			"seqid" => "timet-1" ,
			"base" => "time_tables" ,
			"seq" => array (
				array(
					"tmpl" => '(\d{1,5})' ,
					//"req" => 'select `id` , `id` as `fullid` , DATE_FORMAT( FROM_UNIXTIME( `date` ) , \'%d.%m.%Y\' ) as `date` , `experts` , `purpose` , `destination` from `time-table` where `purpose` RLIKE \'(^|[^-0-9/])${m[1]}([^0-9]|$)\' order by `date` desc' ,
					"req" => "select `id` , `id` as `fullid` , DATE_FORMAT( FROM_UNIXTIME( `date` ) , '%d.%m.%Y' ) as `date` , `experts` , `purpose` , `destination` from `time-table` where ( MATCH( `purpose` ) AGAINST ( '\${m[1]}' ) ) order by `date` desc" ,
					"climit" => array( "local" , 2 ) ,
					"full" => array(
						"type" => "timet1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Выезд [b]${r[date]}[/b] с отметкой [b]${m[1]}[/b], ${r[experts]}, ${r[purpose]} , ${r[destination]}' , "mod" => array( "m" => array( 1 ) , "r" => array( "date" , "experts" , "purpose" , "destination" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'id=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "timetc" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] выездов с отметкой [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "subpoena" ,
			"seqid" => "subpoena-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '(\d{1,4})' ,
					"req" =>
						"select
							subpoenaNumber( `t1`.`id` ) as `id` ,
       						`t1`.`id` as `fullid` ,
       						DATE_FORMAT( FROM_UNIXTIME( `t1`.`to_date` ) , '%d.%m.%Y' ) as `date` ,
       						group_concat( cast( `t2`.`exp_id` as char charset utf8 ) separator ',' ) AS `experts` ,
       						`t1`.`type` ,
       						`t1`.`address` ,
       						`t3`.`name` as `agency`
						from `subpoena` as `t1`
						left join `subpoena-experts` as `t2` on ( `t1`.`id` = `t2`.`s_id` )
						left join `agency` as  `t3` on ( `t1`.`agency_id` = `t3`.`id` )
						where ( `__v_subpoena_number` = \${m[1]} )
						order by `fullid` desc" ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "subpoena1" ,
						"resid" => array( "src" => '${r[fullid]}' , "mod" => array( "r" => array( "fullid" ) ) ) ,
						"text" => array( "src" => '${f[2]} №[b]${r[id]}[/b] от ${r[date]} для ${f[1]} в ${r[agency]}' , "mod" => array( "m" => array( 1 ) , "r" => array( "id" , "date" , "agency" ) , "f" => array( $subpoenaExpert , $subpoenaType ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'id=${r[fullid]}' , "mod" => array( "r" => array( "fullid" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "subpoenac" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] повесток с №[b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "subpoena" ,
			"seqid" => "subpoena-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '(\d{1,2})[.-](\d{2})' ,
					"req" => 'select `id` , `id` as `fullid` , DATE_FORMAT( FROM_UNIXTIME( `to_date` ) , \'%d.%m.%Y\' ) as `date` , `experts` , `type` , `address` , `agency` from `subpoena-list` where ( YEAR( FROM_UNIXTIME( `to_date` ) ) = '.$cYear.' ) and ( MONTH( FROM_UNIXTIME( `to_date` ) ) = ${m[2]} ) and ( DAY( FROM_UNIXTIME( `to_date` ) ) = ${m[1]} ) order by `id` desc' ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "subpoena1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Повестка на [b]${r[date]}[/b] для ${f[1]} в ${r[agency]}' , "mod" => array( "m" => array( 1 ) , "r" => array( "date" , "experts" , "agency" ) , "f" => array( $subpoenaExpert ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'id=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "subpoenac" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] повесток на [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "subpoena" ,
			"seqid" => "subpoena-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '(\d{1,2})[.-](\d{2})[.-](\d{2,4})' ,
					"req" => 'select `id` , `id` as `fullid` , DATE_FORMAT( FROM_UNIXTIME( `to_date` ) , \'%d.%m.%Y\' ) as `date` , `experts` , `type` , `address` , `agency` from `subpoena-list` where ( YEAR( FROM_UNIXTIME( `to_date` ) ) = ${m[3]} ) and ( MONTH( FROM_UNIXTIME( `to_date` ) ) = ${m[2]} ) and ( DAY( FROM_UNIXTIME( `to_date` ) ) = ${m[1]} ) order by `id` desc' ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "subpoena1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Повестка на [b]${r[date]}[/b] для ${f[1]} в ${r[agency]}' , "mod" => array( "m" => array( 1 ) , "r" => array( "date" , "experts" , "agency" ) , "f" => array( $subpoenaExpert ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'id=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "subpoenac" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] повесток на [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "subpoena" ,
			"seqid" => "subpoena-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '(вчера)' ,
					"req" => 'select `id` , `id` as `fullid` , DATE_FORMAT( FROM_UNIXTIME( `to_date` ) , \'%d.%m.%Y\' ) as `date` , `experts` , `type` , `address` , `agency` from `subpoena-list` where ( YEAR( FROM_UNIXTIME( `to_date` ) ) = '.$yYear.' ) and ( MONTH( FROM_UNIXTIME( `to_date` ) ) = '.$yMonth.' ) and ( DAY( FROM_UNIXTIME( `to_date` ) ) = '.$yDay.' ) order by `id` desc' ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "subpoena1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Повестка на [b]${r[date]}[/b] для ${f[1]} в ${r[agency]}' , "mod" => array( "m" => array( 1 ) , "r" => array( "date" , "experts" , "agency" ) , "f" => array( $subpoenaExpert ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'id=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "subpoenac" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего [b]${c[local]}[/b] повесток на [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "subpoena" ,
			"seqid" => "subpoena-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([а-я]+)(?:\s+([а-я]+)+)?' ,
					"req" => $search_Worker ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "subpoena1" ,
						"resid" => array( "src" => '${r[first_id]}' , "mod" => array( "r" => array( "first_id" ) ) ) ,
						"text" => array( "src" => 'Повестки для [b]${r[name2]}[/b]' , "mod" => array( "r" => array( "name2" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'experts=${r[first_id]}' , "mod" => array( "r" => array( "first_id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "subpoenac" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Повестки для [b]${c[local]}[/b] экспертов' , "mod" => array( "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'experts=${f[1]}' , "mod" => array( "f" => array( $getFirstIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "portal" ,
			"seqid" => "portal-1" ,
			"base" => "portal" ,
			"seq" => array (
				array(
					"tmpl" => '([а-я]+)(?:\s+([а-я]+)+)?' ,
					"req" => $search_Worker ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "portal-adm-1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Регистрационные данные [b]${r[name]}[/b]' , "mod" => array( "r" => array( "name" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${r[first_id]}' , "mod" => array( "r" => array( "first_id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "portal-adm-c" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Регистрационные данные [b]${c[local]}[/b] сотрудников с [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'idlist=${f[1]}' , "mod" => array( "f" => array( $getFirstIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "portal" ,
			"seqid" => "portal-1" ,
			"base" => "portal" ,
			"seq" => array (
				array(
					"tmpl" => '(\d{1,2}\.\d)' ,
					"req" => $search_Spec ,
					"climit" => array( "local" , 10 ) ,
					"full" => array(
						"type" => "portal-adm-1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => 'Сотрудники со специальностью [b]${r[desc]}[/b]' , "mod" => array( "r" => array( "desc" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'spec=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "portal-adm-c" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => '[b]${c[local]}[/b] сотрудников со специальностью [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'spec=${f[1]}' , "mod" => array( "f" => array( $getFirstIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "cor_t1" ,
			"seqid" => "cor_t1-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([\wА-Я\s]{1,100})' ,
					//"req" => '( select `id` , `name` as `text` , `id` as `fullid` from `correspondence-main` where ( `name` like "%${m[1]}%" ) and ( `type` = 1 ) ) union distinct ( select `id` , `description` as `text` , `id` as `fullid` from `correspondence-main` where ( `description` like "%${m[1]}%" ) and ( `type` = 1 ) ) order by `id` desc' ,
					"req" => "( select `id` , `name` as `text` , `id` as `fullid` from `correspondence-main` where ( MATCH( `name` ) AGAINST ( '\${m[1]}' ) ) and ( `type` = 1 ) ) union distinct ( select `id` , `description` as `text` , `id` as `fullid` from `correspondence-main` where ( MATCH( `description` ) AGAINST ( '\${m[1]}' ) ) and ( `type` = 1 ) ) order by `id` desc" ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "cor1-t1" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => '[b]${r[text]}[/b]' , "mod" => array( "r" => array( "text" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'view=incomingCorr&amp;idlist=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "cor1-t1c" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего записей [b]${c[local]}[/b] с текстом [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'view=incomingCorr&amp;idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "cor_t2" ,
			"seqid" => "cor_t2-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([\wА-Я\s]{1,100})' ,
					//"req" => '( select `id` , `name` as `text` , `id` as `fullid` from `correspondence-main` where ( `name` like "%${m[1]}%" ) and ( `type` = 2 ) ) union distinct ( select `id` , `description` as `text` , `id` as `fullid` from `correspondence-main` where ( `description` like "%${m[1]}%" ) and ( `type` = 2 ) ) order by `id` desc' ,
					"req" => "( select `id` , `name` as `text` , `id` as `fullid` from `correspondence-main` where ( MATCH( `name` ) AGAINST ( '\${m[1]}' ) ) and ( `type` = 2 ) ) union distinct ( select `id` , `description` as `text` , `id` as `fullid` from `correspondence-main` where ( MATCH( `description` ) AGAINST ( '\${m[1]}' ) ) and ( `type` = 2 ) ) order by `id` desc" ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "cor1-t2" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => '[b]${r[text]}[/b]' , "mod" => array( "r" => array( "text" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'view=outgoingCorr&amp;idlist=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "cor1-t2c" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего записей [b]${c[local]}[/b] с текстом [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'view=outgoingCorr&amp;idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,

		array(
			"scope" => "cor_t3" ,
			"seqid" => "cor_t3-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([\wА-Я\s]{1,100})' ,
					//"req" => '( select `id` , `name` as `text` , `id` as `fullid` from `correspondence-main` where ( `name` like "%${m[1]}%" ) and ( `type` = 7 ) ) union distinct ( select `id` , `description` as `text` , `id` as `fullid` from `correspondence-main` where ( `description` like "%${m[1]}%" ) and ( `type` = 7 ) ) order by `id` desc' ,
					"req" => "( select `id` , `name` as `text` , `id` as `fullid` from `correspondence-main` where ( MATCH( `name` ) AGAINST ( '\${m[1]}' ) ) and ( `type` = 7 ) ) union distinct ( select `id` , `description` as `text` , `id` as `fullid` from `correspondence-main` where ( MATCH( `description` ) AGAINST ( '\${m[1]}' ) ) and ( `type` = 7 ) ) order by `id` desc" ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "cor1-t3" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => '[b]${r[text]}[/b]' , "mod" => array( "r" => array( "text" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'view=incomingCorrPayments&amp;idlist=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "cor1-t3c" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего записей [b]${c[local]}[/b] с текстом [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'view=incomingCorrPayments&amp;idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
		array(
			"scope" => "cor_t4" ,
			"seqid" => "cor_t4-1" ,
			"base" => "maindb" ,
			"seq" => array (
				array(
					"tmpl" => '([\wА-Я\s]{1,100})' ,
					//"req" => '( select `id` , `name` as `text` , `id` as `fullid` from `correspondence-main` where ( `name` like "%${m[1]}%" ) and ( `type` = 8 ) ) union distinct ( select `id` , `description` as `text` , `id` as `fullid` from `correspondence-main` where ( `description` like "%${m[1]}%" ) and ( `type` = 8 ) ) order by `id` desc' ,
					"req" => "( select `id` , `name` as `text` , `id` as `fullid` from `correspondence-main` where ( MATCH( `name` ) AGAINST ( '\${m[1]}' ) ) and ( `type` = 8 ) ) union distinct ( select `id` , `description` as `text` , `id` as `fullid` from `correspondence-main` where ( MATCH( `description` ) AGAINST ( '\${m[1]}' ) ) and ( `type` = 8 ) ) order by `id` desc" ,
					"climit" => array( "local" , 3 ) ,
					"full" => array(
						"type" => "cor1-t4" ,
						"resid" => array( "src" => '${r[id]}' , "mod" => array( "r" => array( "id" ) ) ) ,
						"text" => array( "src" => '[b]${r[text]}[/b]' , "mod" => array( "r" => array( "text" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'view=outgoingCorrPayments&amp;idlist=${r[id]}' , "mod" => array( "r" => array( "id" ) ) )
						)
					) ,
					"limited" => array(
						"type" => "cor1-t4c" ,
						"resid" => array( "src" => 'c1' , "mod" => array() ) ,
						"text" => array( "src" => 'Всего записей [b]${c[local]}[/b] с текстом [b]${m[1]}[/b]' , "mod" => array( "m" => array( 1 ) , "c" => array( "local" ) ) ) ,
						"datas" => array(
							"1" => array( "src" => 'view=outgoingCorrPayments&amp;idlist=${f[1]}' , "mod" => array( "f" => array( $getIDList ) ) )
						)
					)
				)
			)
		) ,
	);

	fixTimerData( 'main' );

	if ( isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ) {

		fixTimerData( 'ajax main' );

		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		if ( $_REQUEST[ 'get_opt' ] ) {
			$fKey = ( isset( $UserOptions[ "payments.full" ] ) && $UserOptions[ "payments.full" ][ "op_value" ] == 1 );
			if ( /*$fKey*/ true ) {
				$searchScope = array( "exp" , "bills" , "pay" , "subp" , "timet" , "subpoena" , "portal" , "cor_t1" , "cor_t2" , "cor_t3" , "cor_t4" );
			} else {
				$searchScope = array( "exp" , "bills" , /*"pay" ,*/ "subp" , "timet" , "subpoena" , "portal" , "cor_t1" , "cor_t2" /*, "cor_t3" , "cor_t4"*/ );
			}

			echo '<result>
				<payment-addr>'.toCDATA( getPaymentsAddr() ).'</payment-addr>
				<search-scope-allowed>'.implode( $searchScope , ',' ).'</search-scope-allowed>
			</result>' ;
			exit ;
		}

		if ( $_REQUEST[ 'mark_list' ] ) {
			$mk = $portalDB->table( 'marks-catalog' );
			echo '<result>
				<payment-addr>'.toCDATA( getPaymentsAddr() ).'</payment-addr>
			</result>' ;
			exit ;
		}


		echo '<result>' ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ "data" ] );
		$data = $DD->documentElement ;
		//if ( $data-> )
		$searchScope = trim( $data->getAttribute( "searchScope" ) );
		if ( strlen( $searchScope ) == 0 ) {
			$fKey = ( isset( $UserOptions[ "payments.full" ] ) && $UserOptions[ "payments.full" ][ "op_value" ] == 1 );
			error_log( print_r( $UserOptions , 1 ) );
			if ( $fKey ) {
				$searchScope = array( "exp" , "bills" , "pay" , "subp" , "timet" , "subpoena" , "portal" , "cor_t1" , "cor_t2" , "cor_t3" , "cor_t4" );
			} else {
				$searchScope = array( "exp" , "bills" , /*"pay" ,*/ "subp" , "timet" , "subpoena" , "portal" , "cor_t1" , "cor_t2" /*, "cor_t3" , "cor_t4"*/ );
			}
		} else {
			$searchScope = explode( "," , $searchScope );
		}

		$searchScope = array_combine( $searchScope , $searchScope );

		$tabWorkers = $portalDB->table( 'workers' , 'id' );
		$tabSpecialities = $portalDB->query( "select `t2`.`id` , `t1`.`index` , `t2`.`num` , `t2`.`desc` , `t2`.`comment` , `t2`.`use_in_stat` from `specialities-groups` as `t1` , `specialities` as `t2` where `t1`.`id` = `t2`.`group`" , 'id' );
		$str = iconv( 'utf8' , 'cp1251' , $data->nodeValue );

		$searchResult = array();
		$resCounter = array(
			'total' => 0 ,
			'seq' => 0 ,
			'local' => 0
		);
		foreach ( $sequences as $cSeq ) {
			$ss = $cSeq[ 'scope' ];
			if ( !isset( $searchScope[ $ss ] ) ) {
				continue ;
			}

			$resCounter[ "seq" ] = 0 ;
			$seqRes = array();
			foreach ( $cSeq[ "seq" ] as $se ) {
				$m = array();
				$ctmpl = '=(?:(?:^\s*)|(?:\s+))'.$se[ "tmpl" ].'(?:(?:\s*$)|(?:\s+))=i' ;
				$n = preg_match( $ctmpl , $str , $m );
				if ( $n == 1 ) {
					//mysql_select_db( $cSeq[ "base" ] , $con );
					$req = $se[ "req" ];
					if ( is_callable( $req ) && !is_string( $req ) ) {
						$dbgTimeB = microtime( true );
						$res = $req( $m );
						$dbgTimeE = microtime( true );
						$searchResult[]= '<dbg t="'.$se[ 'full' ][ 'type' ].'" s="'.$ss.'" dbgt="time" value="'.( $dbgTimeE - $dbgTimeB ).'"></dbg>' ;
					} else {
						foreach( $m as $mi => $mv ) {
							$req = str_replace( '${m['.$mi.']}' , $mv , $req );
							//$req = str_replace( '${m-rev['.$mi.']}' , strrev( $mv ) , $req );
						}
						//echo "\n".$req."\n" ;
						if ( $UserID == 1 ) {
							//$portalDB->dbgMode = true ;
						}

						$dbgTimeB = microtime( true );
						$res = $portalDB->query( $req );
						$dbgTimeE = microtime( true );
						$searchResult[]= '<dbg t="'.$se[ 'full' ][ 'type' ].'" s="'.$ss.'" dbgt="time" value="'.( $dbgTimeE - $dbgTimeB ).'">'.toCDATA( $req ).'</dbg>' ;
					}
					$resCounter[ 'local' ] = count( $res );
					$resCounter[ 'seq' ]+= $resCounter[ 'local' ];
					$resCounter[ 'total' ]+= $resCounter[ 'local' ];

					$limChk = checkLimits( $se[ 'climit' ] , $res , $seqRes , $resCounter );
					$frc = 0 ;
					$rt = '<r t="'.$se[ 'full' ][ 'type' ].'" s="'.$ss.'" ' ;
					$datas = & $se[ 'full' ][ 'datas' ];
					foreach( $res as $r ) {
						if ( $frc < $limChk[ 0 ] ) {
							$rr = $rt ;
							$da = array( "r" => & $r , "m" => & $m , "c" => & $resCounter , "result" => & $res );
							if ( isset( $se[ "full" ][ "res-type" ] ) ) {
								$resType = prep( $se[ "full" ][ "res-type" ][ "src" ] , $se[ "full" ][ "res-type" ][ "mod" ] , $da );
								$rr.= "rt=\"".$resType."\" " ;
							} else {
								$rr.= "rt=\"lnk\" " ;
							}
							foreach ( $datas as $dn => & $dv ) {
								$rr.= "data".$dn."=\"".prep( $dv[ "src" ] , $dv[ "mod" ] , $da )."\" " ;
							}
							$rr.= ">".toCDATA( prep( $se[ "full" ][ "text" ][ "src" ] , $se[ "full" ][ "text" ][ "mod" ] , $da ) )."</r>" ;
							$pd = prep( $se[ "full" ][ "resid" ][ "src" ] , $se[ "full" ][ "resid" ][ "mod" ] , $da );
							if ( !isset( $seqRes[ $pd ] ) ) {
								$seqRes[ $pd ]= $rr ;
								$frc++ ;
							}
						} else {
							break ;
						}
					}
					if ( $limChk[ 1 ] && isset( $se[ "limited" ] ) ) {
						$rt = "<r t=\"".$se[ "limited" ][ "type" ]."\" s=\"".$ss."\" " ;
						$datas = & $se[ "limited" ][ "datas" ];
						$rr = $rt ;
						$da = array( "m" => & $m , "c" => & $resCounter , "result" => & $res );
						if ( isset( $se[ "limited" ][ "res-type" ] ) ) {
							$resType = prep( $se[ "limited" ][ "res-type" ][ "src" ] , $se[ "limited" ][ "res-type" ][ "mod" ] , $da );
							$rr.= "rt=\"".$resType."\" " ;
						} else {
							$rr.= "rt=\"lnk\" " ;
						}
						foreach ( $datas as $dn => & $dv ) {
							$rr.= "data".$dn."=\"".prep( $dv[ "src" ] , $dv[ "mod" ] , $da )."\" " ;
						}
						$rr.= ">".toCDATA( prep( $se[ "limited" ][ "text" ][ "src" ] , $se[ "limited" ][ "text" ][ "mod" ] , $da ) )."</r>" ;
						$pd = prep( $se[ "limited" ][ "resid" ][ "src" ] , $se[ "limited" ][ "resid" ][ "mod" ] , $da );
						if ( !isset( $seqRes[ $pd ] ) ) {
							$seqRes[ $pd ]= $rr ;
						}
					}
				}
			}

			$searchResult[]= implode( "" , $seqRes );
		}

		$dbgTimeB = microtime( true );
		echo implode( $searchResult );

		fixTimerData( 'ajax main' );

		$dbgTimeE = microtime( true );
		echo '<dbg t="LAST">'.( $dbgTimeE - $dbgTimeB ).'</dbg>' ;

		//$timerData ;
		$res = array();
		foreach( $timerData as $tdn => $tdv ) {
			$m = 0 ;
			$c = 0 ;
			if ( count( $tdv ) > 1 ) {
				$res[ $tdn ] = array();
				for( $i = 1 ; $i < count( $tdv ) ; $i+= 2 ) {
					$v = $tdv[ $i ] - $tdv[ $i - 1 ];
					$res[ $tdn ][]= number_format( $v , 6 );
					$m+= $v ;
					$c++ ;
				}
				$res[ $tdn ]= '<dbg t="TIMER_DATA" name="'.$tdn.'">'.implode( ' , ' , $res[ $tdn ] );
				if ( $c > 1 ) {
					$res[ $tdn ].= ' , total : '.number_format( $m , 6 ).' , count : '.$c.' , middle : '.number_format( ( $m / $c ) , 6 );
				}
				$res[ $tdn ].= '</dbg>' ;
			} else {
			}
		}
		if ( count( $res ) > 0 ) {
			$res[]= '<dbg t="MEMORY">memory ( peak / current ): '.number_format( memory_get_peak_usage() / 1048576 , 2 , ',' , ' ' ).' MB / '.number_format( memory_get_usage() / 1048576 , 2 , ',' , ' ' ).' MB</dbg>' ;
		}

		echo implode( $res );

		/*if ( $UserID == 1 ) {
			echo "<ft>".toCDATA( print_r( $searchResult , true ) )."</ft>" ;
		}*/

		if ( $UserID != -1 ) {
			echo "</result>" ;
			exit();
		}

	}