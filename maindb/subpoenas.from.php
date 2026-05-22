<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( '../core.php' );
	/**
	 * @var $portalDB
	 */
	require_once( 'lconfig.php' );

	header( 'Content-Type: text/plain; charset=windows-1251' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Expires: '.date( 'r' ) );
	header( 'Expires: -1' , false );

	if ( isset( $_REQUEST[ 'toa' ] ) ) {
		$tabAgency = $portalDB->query( "select * from `agency` where `ext_id` = ? order by `_fr` desc" , false , 'i' , intval( $_REQUEST[ 'toa' ] ) );
		$k = 0 ;
		echo  '<select id="nrr_agency_sel" class="nrr-agency-sel" size="10" onchange="agency_select()" onclick="agency_select()">' ;
		foreach( $tabAgency as &$i ) {
			echo '<option value="'.$i[ 'id' ].'">'.$i[ 'name' ].'</option>' ;
		} unset( $i );
		echo '</select>' ;
	} else
	if ( isset( $_REQUEST[ 'aa' ] ) ) { // agency address
		if ( intval( $_REQUEST[ 'aa' ] ) < 0 ) {
			echo '' ;
		} else {
			$agency = $portalDB->row( "select * from `agency` where `id` = ?" , 'i' , intval( $_REQUEST[ 'aa' ] ) );
			if ( $agency !== false ) {
				echo $agency[ 'destination' ];
			} else {
				echo '' ;
			}
		}
	} else
	if ( isset( $_REQUEST[ 'aaa' ] ) ) { // agency all addresses
		if ( intval( $_REQUEST[ 'aaa' ] ) < 0 ) {
			echo '' ;
		} else {
			$ayID = intval( $_REQUEST[ 'aaa' ] );
			$aList = array();
			$agency = $portalDB->row( "select * from `agency` where `id` = ?" , 'i' , $ayID );
			if ( $agency !== false ) {
				$aList[]= $agency[ 'destination' ];
			}

			$res = $portalDB->query( "select `t2`.`value` from `agent` as `t1` , `agent-contacts` as `t2` where ( `t1`.`ext_id` = ? ) and ( `t2`.`ext_id` = `t1`.`id` ) and ( `t2`.`type` = 1 ) group by `t2`.`value`" , false , 'i' , $ayID );
			$aList = $aList + array_column( $res , 'value' );

			$res = $portalDB->query( "select `address` from `subpoena` where ( `agency_id` = ? ) order by `id` desc" , false , 'i' , $ayID );
			$aList = $aList + array_column( $res , 'address' );
			foreach( $aList as &$aa ) {
				$aa = preg_replace( '/[,](\S)/' , ', $1' , trim( $aa ) );
				$aa = preg_replace( '/(г|д|ул)\.(\S)/' , '$1. $2' , trim( $aa ) );
				$aa = preg_replace( '/,\s*(\d{1,4}(?:[\/-]?[а-я0-9])?)$/' , ', д. $1' , trim( $aa ) );

				$aa = preg_replace( '/\s+/' , ' ' , trim( $aa ) );
			} unset( $aa );
			$aList = array_filter( $aList , function( $v ) { return !!$v ;  } );
			$aList = array_unique( $aList , SORT_STRING );
			echo implode( "\r\n" , $aList );
		}
	} else
	if ( isset( $_REQUEST[ 'agency' ] ) ) {
		$tabAgent = $portalDB->query( "select * from `agent` where `ext_id` = ? order by `name` desc" , false , 'i' , intval( $_REQUEST[ 'agency' ] ) );
		$k = 0 ;
		echo '<select id="nrr-agent-sel" class="woe-agent-sel" size="10" onchange="agent_select()" onclick="agent_select()">' ;
		foreach( $tabAgent as &$i ) {
			echo '<option value="'.$i[ 'id' ].'">'.$i[ 'name' ].'</option>' ;
		} unset( $i );
		echo '</select>' ;
	}
