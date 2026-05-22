<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	if ( isset( $_REQUEST[ "toa" ] ) ) {
		require_once( "../core.php" );
		require_once( "lconfig.php" );

		header( "Content-Type: text/plain; charset=windows-1251" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		$tabAgency = $portalDB->query( "select * from `agency` where `ext_id` = ? order by `_fr` desc" , false , "i" , intval( $_REQUEST[ "toa" ] ) );
		$k = 0 ;
		echo  "<select id=\"woe_agency_sel\" class=\"woe-agency-sel\" size=\"10\" onchange=\"agency_select()\" onclick=\"agency_select()\">" ;
		foreach( $tabAgency as &$i ) {
			echo "<option value=\"" , $i[ "id" ] , "\">" , $i[ "name" ] , "</option>" ;
		} unset( $i );
		echo "</select>" ;

		exit();
	} else
	if ( isset( $_REQUEST[ "aa" ] ) ) {
		require_once( "../core.php" );
		require_once( "lconfig.php" );

		header( "Content-Type: text/plain; charset=windows-1251" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		if ( intval( $_REQUEST[ "aa" ] ) < 0 ) {
			echo "" ;
		} else {
			$agency = $portalDB->simpleRow( "agency" , intval( $_REQUEST[ "aa" ] ) );
			if ( $agency !== false ) {
				echo $agency[ "destination" ];
			} else {
				echo "" ;
			}
		}
		exit();
	} else
	if ( isset( $_REQUEST[ "agency" ] ) ) {
		require_once( "../core.php" );
		require_once( "lconfig.php" );

		header( "Content-Type: text/plain; charset=windows-1251" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		$tabAgent = $portalDB->query( "select * from `agent` where `ext_id` = ? order by `name` desc" , false , "i" , intval( $_REQUEST[ "agency" ] ) );
		$k = 0 ;
		echo "<select id=\"woe_agent_sel\" class=\"woe-agent-sel\" size=\"10\" onchange=\"agent_select()\" onclick=\"agent_select()\">" ;
		foreach( $tabAgent as &$i ) {
			echo "<option value=\"" , $i[ "id" ] , "\">" , $i[ "name" ] , "</option>" ;
		} unset( $i );
		echo "</select>" ;
		exit();
	}
?>