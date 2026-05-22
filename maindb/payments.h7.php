<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "../core.php" );
	require_once( "lconfig.php" );
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

	$mayWOEAdd = false ;
	$mayWOEEdit = false ;

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


	if ( in_array( $UserID , array( 1 , 132 , 169 , 198 , 146 ) ) ) {
		$serverName = $_SERVER[ "SERVER_NAME" ];
		$storeDate = intval( date_create()->modify( "+5 year" )->format( "U" ) );
		setcookie( "fk" , base64_encode( sha1( "all-time:".$UserID ) ) , $storeDate , "/" , ".".$serverName , "0" );
	}
	Redirect( getPaymentsAddr() );
?>