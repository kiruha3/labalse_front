<?php
	include_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 */
	include_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
		exit();
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "DOCS-ACCESS-LOG" , $Rights ) ) {
			$GoOut = !in_array( "VIEW" , $Rights[ "DOCS-ACCESS-LOG" ] );
		} else {
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}

	$tabWorkers = $portalDB->table( "workers" , "id" );
	$tabWorkersByFID = remap( $tabWorkers , "first_id" );
	foreach ( $tabWorkersByFID as &$twa ) {
		foreach ( $twa as &$tw ) {
			$tw = $tw[ "id" ];
		} unset( $tw );
	} unset( $twa );

	foreach ( $tabWorkers as &$tw ) {
		$tw = "<a href=\"docs-access-log.php?worker=".$tw[ "first_id" ]."\" class=\"exp-lnk\">".NAMES_Format( NAMES_parse( $tw[ "name" ] ) )."</a>" ;
	} unset( $tw );

	$queryFlt = array();
	if ( isset( $_REQUEST[ "err" ] ) ) {
		switch( $_REQUEST[ "err" ] ) {
			case "noacr" :
				$queryFlt[ "err" ] = "( `t1`.`result` = 'forbidden' ) and ( `t1`.`comment` = 'no access rights' )" ;
				break ;

			case "nolvl2" :
				$queryFlt[ "err" ] = "( `t1`.`result` = 'error' ) and ( `t1`.`comment` = 'No lvl2 cards' )" ;
				break ;
		}
	}

	if ( isset( $_REQUEST[ "user" ] ) ) {
		$queryFlt[ "user" ] = "( `t1`.`user_id` = ".Int2SQL( $_REQUEST[ "user" ] )." )" ;
	}

	if ( isset( $_REQUEST[ "worker" ] ) ) {
		$wfid = intval( $_REQUEST[ "worker" ] );
		if ( isset( $tabWorkersByFID[ $wfid ] ) && count( $tabWorkersByFID[ $wfid ] ) > 0 ) {
			$queryFlt[ "worker" ] = "( `t1`.`worker_id` in ( ".implode( "," , $tabWorkersByFID[ $wfid ] )." ) )" ;
		}
	}

	$fltByNum = false ;
	if ( isset( $_REQUEST[ "number" ] ) && isset( $_REQUEST[ "type" ] ) ) {
		$nums = getIDList( $_REQUEST[ "number" ] , true );
		$type = $_REQUEST[ "type" ];
		if ( count( $nums ) > 0 ) {
			switch( $type ) {
				case "docs" :
					$queryFlt[ "number" ] = "( matincomingNumber( `t2`.`ext_id` ) in ( ".implode( "," , $nums )." ) )" ;
					break ;
			}
			$fltByNum = true ;
		}
	}

	if ( isset( $_REQUEST[ "year" ] ) && isset( $_REQUEST[ "type" ] ) ) {
		$year = $_REQUEST[ "year" ];
		$type = $_REQUEST[ "type" ];
		switch( $type ) {
			case "docs" :
				$queryFlt[ "year" ] = "( matincomingYear( `t2`.`ext_id` ) = ".Int2SQL( $year )." )" ;
				break ;
		}
		$fltByNum = true ;
	}

	if ( isset( $_REQUEST[ "type" ] ) ) {
		$type = $_REQUEST[ "type" ];
		$queryFlt[ "type" ] = "( `t2`.`ext_type` = \"docs\" )" ;
		$fltByNum = true ;
	}

	$leftBound = $rightBound = false ;

	if ( isset( $_REQUEST[ 'y1' ] ) ) {
		if ( isset( $_REQUEST[ 'm1' ] ) ) {
			$leftBound = mktime( 0 , 0 , 0 , intval( $_REQUEST[ 'm1' ] ) , 1 , intval( $_REQUEST[ 'y1' ] ) );
		} else {
			$leftBound = mktime( 0 , 0 , 0 , 1 , 1 , intval( $_REQUEST[ 'y1' ] ) );
		}
	}

	if ( $leftBound ) {
		$queryFlt[ 'date_range_left' ] = "( `t1`.`date` >= ".$leftBound." )" ;
	}

	if ( isset( $_REQUEST[ 'y2' ] ) ) {
		if ( isset( $_REQUEST[ 'm2' ] ) ) {
			$rightBound = mktime( 0 , 0 , 0 , intval( $_REQUEST[ 'm2' ] ) + 1 , 1 , intval( $_REQUEST[ 'y2' ] ) ) - 1 ;
		} else {
			$rightBound = mktime( 0 , 0 , 0 , 1 , 1 , intval( $_REQUEST[ 'y2' ] ) + 1 ) - 1 ;
		}
	}

	if ( $rightBound ) {
		$queryFlt[ 'date_range_right' ] = "( `t1`.`date` <= ".$rightBound." )" ;
	}

	$logData = $portalDB->query(
		"select `t1`.* , `t2`.`ext_type` as `ex_type` , `t2`.`name` , `t2`.`ext_id` from `documents-access-log` as `t1` , `documents` as `t2` where ( `t1`.`tgt_id` = `t2`.`id` ) ".( count( $queryFlt ) > 0 ? " and ".implode( " and " , $queryFlt ) : "" )." order by `date` desc"
	);

	$flt = makeSimpleTable_init_filter();
	$docsMap = array(
		"docs"      => "Экспертизы" ,
		"subpoena"  => "Повестки" ,
		"correspondence" => "Журналы"
	);
	$flt[ "ex_type" ] = function( &$r , $c , $v ) {
		global $docsMap ;

		if ( isset( $docsMap[ $v ] ) ) {
			return $docsMap[ $v ];
		} else {
			return $v ;
		}
	};
	$flt[ "number" ] = function( &$r , $c , $v ) {
		switch ( $r[ "ex_type" ] ) {
			case "docs" :
				return "<a href=\"/maindb/main.php?idlist=".$v."\" target=\"_blank\" class=\"tgt-lnk\">".matincomingNumber( $v )." / ".matincomingYear( $v )."</a>" ;
				break ;
			case "subpoena" :
				return "<a href=\"/maindb/subpoenas.php?id=".$v."\" target=\"_blank\" class=\"tgt-lnk\">".subpoenaNumber( $v )." / ".subpoenaYear( $v )."</a>" ;
				break ;
		}
	};

	$flt[ "worker_id" ] = function( &$r , $c , $v ) {
		global $tabWorkers ;

		if ( isset( $tabWorkers[ $v ] ) ) {
			return $tabWorkers[ $v ];
		} else {
			return $v ;
		}
	};

	$flt[ "ip" ] = function( &$r , $c , $v ) {
		return long2ip( $v );
	};

	MainHead_L2( "База" , "<a href='main.php'>База</a> - Журнал доступа к документам" , array( "../%UT/buttons.css" , "%UT/docs-access-log.css" ) , array( "files/docs-access-log.js" ), "hlp/docs-access-log.html" );

	echo "<div class=\"topPanel\">
		<a href=\"docs-access-log.php?err=noacr\" class=\"btn3\">Попытка доступа без прав</a>
		<a href=\"docs-access-log.php?err=nolvl2\" class=\"btn3\">К нераспределенной</a>
		<a href=\"docs-access-log.php?type=docs\" class=\"btn3\">К экспертизам</a>
		<a href=\"docs-access-log.php\" class=\"btn3\">Все</a>
	</div>" ;

	if ( $fltByNum ) {
		echo "<div class=\"fltInfo\">Выборка по <span>".$docsMap[ $type ]."</span> с номерами <span>".implode( " , " , $nums )."</span> за <span>".$year."</span> г. </div>" ;
	}

	echo makeSimpleTable(
		'[]' ,
		'[ { "t" : 2 } ]' ,
		'[ { "n" : "date"      , "t" : "dt" , "h" : [ { "d" : "Дата и время" } ] } , '.
		'  { "n" : "ex_type"   , "t" : "ss" , "h" : [ { "d" : "Тип" } ] , "f" : "ex_type" } , '.
		'  { "n" : "ext_id"    , "t" : "ss" , "h" : [ { "d" : "Номер" } ] , "f" : "number" } , '.
		'  { "n" : "name"      , "t" : "sl" , "h" : [ { "d" : "Документ" } ] } , '.
		'  { "n" : "worker_id" , "t" : "ss" , "h" : [ { "d" : "Эксперт" } ] , "f" : "worker_id" } , '.
		'  { "n" : "ip"        , "t" : "ss" , "h" : [ { "d" : "IP" } ] , "f" : "ip" } , '.
		'  { "n" : "result"    , "t" : "ss" , "h" : [ { "d" : "Результат" } ] } , '.
		'  { "n" : "comment"   , "t" : "Sm" , "h" : [ { "d" : "Комментарий" } ] } ]' ,
		$logData , false , $flt );

	closeHtml();
