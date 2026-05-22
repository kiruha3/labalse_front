<?php
	$docsAnyType = 'subpoena' ;

	/**
	 * @var $PlaceID
	 * @var $LoginOk
	 * @var $UserRights
	 */

	$modeAJAX = isset( $_REQUEST[ "mode" ] );
	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			Redirect( "../auth.php" );
		}
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "SUBPOENAS" , $Rights ) ) {
			$subpoenaAdd = in_array( "ADD" , $Rights[ "SUBPOENAS" ] );
			$subpoenaEdit = in_array( "EDIT" , $Rights[ "SUBPOENAS" ] );
			$subpoenaAccess = strtolower( array_key_exists( "SUBPOENAS-ACCESS" , $Rights ) ? $Rights[ "SUBPOENAS-ACCESS" ][ 0 ] : "Expert" );
			$GoOut = !$subpoenaAdd ;
		} else {
			$subpoenaAdd = $subpoenaEdit = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			ErrorMessage( 403 );
		}
	}

	function tntb( $n ) {
		return iconv( "utf8" , "cp1251" , trim( preg_replace( '/\s+/' , " " , $n ) ) );
	}
