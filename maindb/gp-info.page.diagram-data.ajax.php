<?php
	include_once( '../core.php' );
	/**
	 * @var $dbConfig
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserThemeLoc
	 * @var $portalDB
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */
	require_once( 'gp-info.php' );

	/**
	 * report 15-1 row order field name
	 */
	define( 'FSOF' , 'order--15-1.246--ed-88' );


	$modeAjax = isset( $_REQUEST[ 'mode' ] ) && $_REQUEST[ 'mode' ] == 'ajax' ;

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		if ( !$modeAjax ) {
			Redirect( '../auth.php' );
		} else {
			exit();
		}
	}

	if ( count( $UserRights ) != 1 ) {
		if ( !$modeAjax ) {
			ErrorPageAndExit();
		}
		exit();
	}

	$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
	
	$mainDBRights = getRights( 3 );
	if ( array_key_exists( 'GP-INDICATOR' , $mainDBRights ) ) {
		$mayGPIndicator = in_array( 'SHOW-GP-INDICATOR' , $mainDBRights[ 'GP-INDICATOR' ] );
	} else {
		$mayGPIndicator = false ;
	}
	
	$showGPIndicator = false ;
	switch ( $dbConfig[ 'gp-indicator-mode' ] ) {
		case 'show-all' :
			$showGPIndicator = true ;
			break ;
		
		case 'show-rights' :
			$showGPIndicator = $mayGPIndicator ;
			break ;
	}
	
	if ( !$showGPIndicator ) {
		if ( !$modeAjax ) {
			ErrorPageAndExit();
		}
		exit();
	}

	if ( isset( $_REQUEST[ 'dbg' ] ) ) {
		MainHead_L2();
	} else {
		header( 'Content-Type: application/json' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );
	}

	$data = isset( $_REQUEST[ 'data' ] ) ? $_REQUEST[ 'data' ] : '' ;

	switch ( $data ) {
		case 'get-lists' :
			$tspc = $portalDB->query( "select * from `specialities` where `use_in_stat` = 1 order by `".FSOF."` , `group` , `num`" , 'id' );
			$tspcIDArray = array_keys( $tspc );

			$specList = $portalDB->query( "select `t2`.`id` , `t1`.`index` , `t2`.`num` , `t2`.`desc` from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id` = `t2`.`group` ) and ( `t2`.`id` in ( ?* ) ) order by `t1`.`index` asc , `t2`.`num` asc" , 'id' , '*i' , $tspcIDArray );
			foreach( $specList as &$row ) {
				$row[ 'desc' ] = cvt( $row[ 'desc' ] );
			} unset( $row );
			$result = array(
				'spec-list' => $specList
			);
			echo json_encode( $result , JSON_UNESCAPED_UNICODE );
			break;

		case 'get-data' :
			if ( isset( $_REQUEST[ 'ts1' ] ) && $_REQUEST[ 'ts2' ] ) {
				$ts1 = intval( $_REQUEST[ 'ts1' ] , 10 );
				$ts2 = intval( $_REQUEST[ 'ts2' ] , 10 );
			} else {
				$cTime = time();
				$cy = intval( date( 'Y' , $cTime ) , 10 ) - 1 ;
				$ts1 = mktime( 0 , 0 , 0 , 1  , 1  , $cy );
				$ts2 = mktime( 0 , 0 , 0 , 12 , 31 , $cy );
			}
			$diagramData = gpDiagramData( $ts1 , $ts2 );
			$result = array(
				'diagram-data' => $diagramData ,
			);
			echo json_encode( $result , JSON_UNESCAPED_UNICODE );
			break;

		case 'get-data-dbg' :
			if ( isset( $_REQUEST[ 'ts1' ] ) && $_REQUEST[ 'ts2' ] ) {
				$ts1 = strtotime( $_REQUEST[ 'ts1' ] );
				$ts2 = strtotime( $_REQUEST[ 'ts2' ] );
			} else {
				$cTime = time();
				$cy = intval( date( 'Y' , $cTime ) , 10 ) - 1 ;
				$ts1 = mktime( 0 , 0 , 0 , 1  , 1  , $cy );
				$ts2 = mktime( 0 , 0 , 0 , 12 , 31 , $cy );
			}
			fixTimerData( 'gpDiagramData' );
			$diagramData = gpDiagramData( $ts1 , $ts2 , true );
			fixTimerData( 'gpDiagramData' );
			$result = array(
				'rows-count' => count( $diagramData ) ,
				'diagram-data' => $diagramData ,
			);
			fixTimerData( 'json_encode' );
			echo json_encode( $result , JSON_UNESCAPED_UNICODE );
			fixTimerData( 'json_encode' );
			break;
	}

	if ( isset( $_REQUEST[ 'dbg' ] ) ) {
		closeHtml();
	} else {
	}


