<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	require_once( "core.php" );
	/**
	 * @var $dbConfig
	 * @var $portalDB
	 */


	if ( isset( $dbConfig[ CFG__ENGINE__AUTH__REALM ] ) && $dbConfig[ CFG__ENGINE__AUTH__REALM ] != '' ) {
		$rs = '@'.$dbConfig[ CFG__ENGINE__AUTH__REALM ];
		if( isset( $_SERVER[ 'REMOTE_USER' ] ) && substr( $_SERVER[ 'REMOTE_USER' ] , -strlen( $rs ) ) == $rs ) {
			$rul = substr( $_SERVER[ 'REMOTE_USER' ] , 0 , -strlen( $rs ) );
			$w = $portalDB->row( "select * from `workers` where ( `ad-login` = ? ) and ( `actual` = 1 )" , 's' , $rul );
			if ( $w !== false ) {
				$a = $portalDB->row( "select * from `accounts` where ( `worker_id` = ? )" , 'i' , $w[ 'id' ] );
				if ( $a !== false ) {
					$cookieDomain = $dbConfig[ 'engine.addresses.cookieDomain' ];
					setcookie( 'uLogin' , $a[ 'login' ] , time() + 60 * 60 * 24 * 1024 , '/' , $cookieDomain , '0' );
					setcookie( 'uPassword' , $a[ 'pass' ] , time() + 60 * 60 * 24 * 1024 , '/' , $cookieDomain , '0' );
					Redirect( 'index.php' );
				}
			}
		}
	}

	Redirect( 'auth.php?noad' );
