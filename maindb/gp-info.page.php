<?php
	include_once( '../core.php' );
	/**
	 * @var $dbConfig
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserThemeLoc
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */
	require_once( 'gp-info.php' );

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

	$Rights= ParseRights( strtoupper( $UserRights[ 0 ] ) );
	
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
		ErrorPageAndExit();
		exit();
	}
	
	
	MainHead_L2( 'База' , 'Государственное задание' , array( '../%UT/buttons.css' , '%UT/gp-info.page.css' ) , array( '#var UserThemeLoc = "'.$UserThemeLoc.'" ; ' , 'files/main.js' , 'files/gp-info.page.js' ) , 'hlp/main.html' );

		if ( isset( $_REQUEST[ 'date' ] ) ) {
			$d = $_REQUEST[ 'date' ];
		} else {
			$d = null ;
		}

		if ( isset( $_REQUEST[ 'yc' ] ) ) {
			$yc = intval( $_REQUEST[ 'yc' ] );
		} else {
			$yc = intval( $dbConfig[ 'gp-indicator-years' ] , 10 );
		}

		$cy = intval( date( 'Y' , time() ) );

		$da = range( $cy , $cy - $yc + 1 , -1 );

		echo '<a href="?date=31.12.'.date( 'Y' , time() ).'" class="btn3">За год</a>
		<a href="?nodate" class="btn3">За аналогичный период</a>' ;

		if ( $dbConfig[ 'gp-indicator-inc-sndz' ] == 1 ) {
			$wSNDZ = !isset( $_REQUEST[ 'no-sndz' ] );
			if ( !$wSNDZ ) {
				echo '<a href="?" class="btn3">С СНДЗ</a>' ;
			} else {
				echo '<a href="?no-sndz=1" class="btn3">Без СНДЗ</a>' ;
			}
		} else {
			$wSNDZ = isset( $_REQUEST[ 'sndz' ] );
			if ( !$wSNDZ ) {
				echo '<a href="?sndz=1" class="btn3">С СНДЗ</a>' ;
			} else {
				echo '<a href="?" class="btn3">Без СНДЗ</a>' ;
			}
		}

		echo gpInfoMain( false , $d , $da , $wSNDZ );

		echo '<div id="cart-panel-control"></div>
		<vrcse-chart-panel id="year-stat" class="chart-area"></vrcse-chart-panel>' ;

	closeHtml();
