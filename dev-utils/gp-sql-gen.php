<?php
	/**
	 * @var $portalDB
	 */

	error_reporting( E_ERROR | E_PARSE );

	function getPlanSQL( $y , $plan ) {
		$delta = $plan % 12 ;
		$ppm = ( $plan - $delta ) / 12 ;
		$mio = array(
			4 , 11 , 10 , 9 , 3 , 12 , 6 , 2 , 7 , 8 , 1 , 5
		);

		$m = array_fill( 0 , 12 , $ppm );
		for( $i = 0 ; $i < $delta ; $i++ ) {
			$m[ $mio[ $i ] - 1 ]++ ;
		}

		for( $i = 0 ; $i < 12 ; $i++ ) {
			$m[ $i ] = "( '".$y.str_pad( $i + 1 , 2 , 0 , STR_PAD_LEFT )."' , ".$m[ $i ]." )" ;
		}

		return "replace into `gov-plan` ( `period` , `plan` ) values ".implode( ' , ' , $m ).";" ;
	}

	if ( php_sapi_name() == "cli" ) {
		$plan = intval( $argv[ 1 ] , 10 );
		$cy = intval( $argv[ 2 ] , 10 );

		echo getPlanSQL( $cy , $plan );
	} else {
		$admPath = '/adminka/gov-plan.php' ;
		if ( isset( $_SERVER[ 'SCRIPT_NAME' ] ) ) {
			if (  $_SERVER[ 'SCRIPT_NAME' ] != $admPath ) {
				require_once( '../core.php' );
				Redirect( $admPath );
				exit();
			}
		} else {
			if (  $_SERVER[ 'SCRIPT_URL' ] != $admPath ) {
				require_once( '../core.php' );
				Redirect( $admPath );
				exit();
			}
		}
	}


