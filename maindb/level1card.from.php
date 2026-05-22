<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	if ( isset( $_REQUEST[ 'toa' ] ) || isset( $_REQUEST[ 'agency' ] ) ) {
		require_once( '../core.php' );
		/**
		 * @var TDB $portalDB
		 */
		require_once( 'lconfig.php' );

		header( 'Content-Type: text/plain; charset=windows-1251' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );
	}

	if ( isset( $_REQUEST[ 'toa' ] ) ) {
		$tabResult = $portalDB->query( "select * from `agency` where `ext_id` = ? order by `_fr` desc" , false , 'i' , intval( $_REQUEST[ 'toa' ] ) );
		echo  '<select id="i_from_agency_list" name="i_from_agency_list" class="i_from_agency_list" size="20" onchange="agency_select()" onclick="agency_select()">' ;
	} else
	if ( isset( $_REQUEST[ 'agency' ] ) ) {
		$tabResult = $portalDB->query( "select * from `agent` where `ext_id`= ? Order by `_fr` desc" , false , 'i' , intval( $_REQUEST[ 'agency' ] ) );
		echo '<select id="i_from_agent_list" name="i_from_agent_list" class="i_from_agent_list" size="20" onchange="agent_select()" onclick="agent_select()">' ;
	}

	if ( isset( $_REQUEST[ 'toa' ] ) || isset( $_REQUEST[ 'agency' ] ) ) {
		foreach( $tabResult as &$i ) {
			echo '<option value="' , $i[ 'id' ] , '">' , $i[ 'name' ] , '</option>' ;
		} unset( $i );
		echo "</select>" ;
	}
