<?php
    require_once( 'core.export.php' );
    /**
     * @var TDB $portalDB
     * @var array $dbConfig
	 * @var $clearArea
	 * @var $TAB_CASECATEGORY
     */

    $res = array( 'success' => true , 'orgName' => iconv( 'cp1251' , 'utf8' , $dbConfig[ 'org.name.short' ] ) );

    $workerIDMap = $portalDB->query( "select `id` , `first_id` from `workers-no-spec`" , "id" );
	$workerFIDMap = array();
	foreach( $workerIDMap as $r ) {
		$wID = $r[ 'id' ];
		$wFID = $r[ 'first_id' ];
		if ( !isset( $workerFIDMap[ $wFID ] ) ) {
			$workerFIDMap[ $wFID ] = $wID ;
		} else {
			if ( $workerFIDMap[ $wFID ] < $wID ) {
				$workerFIDMap[ $wFID ] = $wID ;
			}
		}
	}

    $runType = $portalDB->simpleRow( 'kuvk-params' , array( 'name' => 'runType' ) );
    $runType = json_decode( $runType[ 'value' ] , true );
    $res[ 'runType' ] = $runType ;
    $runTypeClear = false ;
    foreach( $clearArea as $caName => $caValue ) {
    	if ( isset( $runType[ $caName ] ) && $runType[ $caName ] == 1 ) {
    		if ( !is_array( $caValue ) ) {
    			$caValue = array( $caValue );
			}
			$portalDB->noResult( "delete from `kuvk-links` where `ext_type` in ( ?* )" , '*s' , $caValue );
			$portalDB->noResult( "delete from `kuvk-sync`  where `ext_type` in ( ?* )" , '*s' , $caValue );
			$runTypeClear = true ;
		}
	}
    /*if ( $runType[ 'clear-corr' ] == 1 ) {
        $portalDB->noResult( "delete from `kuvk-links` where `ext_type` = ?" , 's' , KUVK_LINK_CORRESPONDENCE );
        $portalDB->noResult( "delete from `kuvk-sync` where `ext_type`  = ?" , 's' , KUVK_LINK_CORRESPONDENCE );
        $runTypeClear = true ;
    }
    if ( $runType[ 'clear-matincoming' ] == 1 ) {
        $portalDB->noResult( "delete from `kuvk-links` where `ext_type` = ?" , 's' , KUVK_LINK_MATINCOMING );
        $portalDB->noResult( "delete from `kuvk-sync` where `ext_type`  = ?" , 's' , KUVK_LINK_MATINCOMING );
        $runTypeClear = true ;
    }
    if ( $runType[ 'clear-agents' ] == 1 ) {
        $portalDB->noResult( "delete from `kuvk-links` where `ext_type` in ( ? , ? )" , 'ss' , KUVK_LINK_AGENT , KUVK_LINK_AGENCY );
        $portalDB->noResult( "delete from `kuvk-sync` where `ext_type`  in ( 'agent' , 'agency' )" );
        $runTypeClear = true ;
    }
    if ( $runType[ 'clear-workers-info' ] == 1 ) {
        $portalDB->noResult( "delete from `kuvk-links` where `ext_type` in ( 'specialities' , 'specialities-groups' , 'workers' , 'departments' , 'posts' , 'staffing' , 'staffing-workers' , 'workers-spec' )" );
        $portalDB->noResult( "delete from `kuvk-sync` where `ext_type`  in ( 'specialities' , 'specialities-groups' , 'workers' , 'departments' , 'posts' , 'staffing' , 'staffing-workers' , 'workers-spec' )" );
        $runTypeClear = true ;
    }
	if ( $runType[ 'clear-subpoenas' ] == 1 ) {
		$portalDB->noResult( "delete from `kuvk-links` where `ext_type` = 'subpoena'" );
		$portalDB->noResult( "delete from `kuvk-sync` where `ext_type`  = 'subpoena'" );
		$runTypeClear = true ;
	}*/

    if ( $runTypeClear ) {
        sendResult( $res );
        exit();
    }



	$res[ 'posts' ] = getDataWSync( 'posts' );
    cvtArr( $res[ 'posts' ] , array( 'name' , 'short_name' , 'simple_name' ) );

    $res[ 'dep' ] = getDataWSync( 'departments' );
    cvtArr( $res[ 'dep' ] , array( 'name' , 'short_name' ) );

    $res[ 'staffing' ] = getDataWSync( 'staffing' );
    $res[ 'staffing-workers' ] = getDataWSync( 'staffing-workers' );

    $res[ 'spec-gr' ] = getDataWSync( 'specialities-groups' );
    unsetArr( $res[ 'spec-gr' ] , 'name' );

    $res[ 'spec' ] = getDataWSync( 'specialities' );
    unsetArr( $res[ 'spec' ] , strexp( "{desc,actual{_from,_to},norm{1,2,3,4},comment}" ) );

	$res[ "ep-states" ] = getDataWSync( "ep-states" );
	cvtArr( $res[ "ep-states" ] , array( "name" ) );
	$res[ "ep-ret-reasons" ] = getDataWSync( "ep-ret-reasons" );
	cvtArr( $res[ "ep-ret-reasons" ] , array( "name" ) );

	$res[ 'casecategory' ] = $TAB_CASECATEGORY ;
	cvtArr( $res[ 'casecategory' ] , array( 'name' , 'short_name' ) );

	$toal = $portalDB->table( 'type-of-agency' );
	foreach( $toal as &$i ) {
		$i[ 'name' ] = cvt( inForm( ( !is_null( $i[ 'kuvk_label' ] ) ? $i[ 'kuvk_label' ] : $i[ 'name' ] ) , 1 ) );
		unset( $i[ '_fr' ] );
		unset( $i[ 'fmt' ] );
		unset( $i[ 'for-test' ] );
		unset( $i[ 'kuvk_label' ] );
	} unset( $i );
	$res[ 'type_of_agency' ] = &$toal ;

	$res[ 'wrk' ] = getDataWSync( 'workers' , 'workers' , array( 'lnk' => 'first_id' ) );
	foreach( $res[ 'wrk' ] as &$wrk ) {
		$wrk[ 'nameim' ] = NAMES_Format( NAMES_parse( $wrk[ 'name' ] ) , "%F1 %I1 %O1" );
		$wrk[ 'name_f' ] = NAMES_Format( NAMES_parse( $wrk[ 'name' ] ) , "%F1" );
		$wrk[ 'name_i' ] = NAMES_Format( NAMES_parse( $wrk[ 'name' ] ) , "%I1" );
		$wrk[ 'name_o' ] = NAMES_Format( NAMES_parse( $wrk[ 'name' ] ) , "%O1" );
		$wrk[ 'work_from' ] = date( DATE_ATOM , $wrk[ 'work_from' ] );
	} unset( $wrk );
	cvtArr( $res[ 'wrk' ] , strexp( "name{,im,_f,_i,_o}" ) );

	$res[ 'wrk-spec' ] = getDataWSync( 'workers-spec' , 'workers-spec' );
	foreach( $res[ 'wrk-spec' ] as &$wrkSpec ) {
		$wID = $wrkSpec[ 'worker_id' ];
		$wFID = $workerIDMap[ $wID ][ 'first_id' ];
		$wID = $workerFIDMap[ $wFID ];
		$wrkSpec[ 'worker_id' ] = $wID ;
		$wrkSpec[ 'date_from_orig' ] = $wrkSpec[ 'date_from' ];
		$wrkSpec[ 'date_from' ] = date( DATE_ATOM , is_null( $wrkSpec[ 'date_from' ] ) ? 0 : $wrkSpec[ 'date_from' ] );
	} unset( $wrkSpec );
	cvtArr( $res[ 'wrk-spec' ] , array( 'org_label' ) );

	sendResult( $res );
