<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( '../../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $portalDB
	 */
	require_once( '../lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		echo '/* Нет Доступа */' ;
		exit ;
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'LVL2CARD' , $Rights ) ) {
			$lvl2cardADD = in_array( 'ADD' , $Rights[ 'LVL2CARD' ] );
			$lvl2cardEDIT = in_array( 'EDIT' , $Rights[ 'LVL2CARD' ] );
			$GoOut = isset( $_GET[ 'AE' ] ) ? !( $lvl2cardADD || $lvl2cardEDIT ) : true ;
		} else {
			$lvl2cardADD = $lvl2cardEDIT = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		echo '/* Нет Доступа */' ;
		exit ;
	}




	$tab = $portalDB->table( 'workers' );
	$wt = array();
	foreach ( $tab as $i ) {
		$tt = array();
		$ts = explode( ';' , $i[ 'spec' ] );
		foreach( $ts as $j ) {
			$j = trim( $j );
			if ( $j != '' ) {
				$tt[]= $j ;
			}
		}
		if ( count( $tt ) > 0 ) {
			$wt[]= 'w'.$i[ 'id' ].':['.implode( ',' , $tt ).']' ;
		}
	}

	echo "\r\n\t".'var wrkr = {'.implode( ',' , $wt ).'};'."\r\n" ;

	$tab = $portalDB->query( "select `t2`.`id` , concat( `t1`.`index` , \".\" , `t2`.`num` , if( `t2`.`comment` is null , \"\" , concat( \" (\" , `t2`.`comment` , \")\" ) ) ) as `spec` , `t2`.`use_in_stat` , `t2`.`actual` , `t2`.`norm1` , `t2`.`norm2` , `t2`.`norm3` , `t2`.`norm4` from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id` = `t2`.`group` );" );
	$st = array();
	foreach ( $tab as $i ) {
		for( $j = 1 ; $j <= 4 ; $j++ ) {
			if ( is_null( $i[ 'norm'.$j ] ) ) {
				$i[ 'norm'.$j ] = 0 ;
			}
		}
		$st[]= 's'.$i[ 'id' ].':{c:"'.$i[ 'spec' ].( $i[ 'use_in_stat' ] != 1 ? ' ! ЭУ' : '' ).'",uis:'.( $i[ 'use_in_stat' ] == 1 ? 1 : 0 ).',a:'.( $i[ 'actual' ] == 1 ? 1 : 0 ).',n1:'.$i[ 'norm1' ].',n2:'.$i[ 'norm2' ].',n3:'.$i[ 'norm3' ].',n4:'.$i[ 'norm4' ].'}' ;
	}

	echo "\t".'var specs = {'.implode( ',' , $st ).'};'."\r\n" ;

