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
		echo  "<select id=\"nrr_agency_sel\" class=\"nrr-agency-sel\" size=\"10\" onchange=\"agency_select()\" onclick=\"agency_select()\">" ;
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
			$agency = $portalDB->row( "select * from `agency` where `id` = ?" , "i" , intval( $_REQUEST[ "aa" ] ) );
			if ( $agency !== false ) {
				echo $agency[ "destination" ];
			} else {
				echo "" ;
			}
		}
		exit();
	} else
	if ( isset( $_REQUEST[ "ac" ] ) ) {
		require_once( "../core.php" );
		require_once( "lconfig.php" );

		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		if ( intval( $_REQUEST[ "ac" ] ) < 0 ) {
			echo "<result state=\"ok\" />" ;
		} else {
			$contacts = $portalDB->query( "select * from `agent-contacts` where `ext_id` = ? order by `actual` desc , `type` asc ;" , "id" , "i" , $_REQUEST[ "ac" ] );
			echo "<result>" ;
			foreach( $contacts as $cc ) {
				echo "<contact t=\"".$cc[ "type" ]."\" a=\"".$cc[ "actual" ]."\">".toCDATA( $cc[ "value" ] )."</contact>" ;
			}
			echo "</result>" ;
		}
		exit();
	} else
	if ( isset( $_REQUEST[ "acl" ] ) ) {
		require_once( "../core.php" );
		require_once( "lconfig.php" );

		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		if ( intval( $_REQUEST[ "acl" ] ) < 0 ) {
			echo "<result state=\"ok\" />" ;
		} else {
			echo "<result>" ;
			$contacts = $portalDB->row( "select * from `agency` where ( `id` = ? )" , "i" , $_REQUEST[ "acl" ] );
			if ( $contacts !== false ) {
				echo "<contact t=\"1\" a=\"1\">".toCDATA( $contacts[ "destination" ] )."</contact>" ;
			}
			$contacts = $portalDB->query( "select `t2`.* , `t1`.`name` from `agent` as `t1` , `agent-contacts` as `t2` where ( `t1`.`ext_id` = ? ) and ( `t2`.`ext_id` = `t1`.`id` ) group by `value` order by `actual` desc , `type` asc ;" , "id" , "i" , $_REQUEST[ "acl" ] );
			if ( $contacts !== false ) {
				foreach( $contacts as $cc ) {
					echo "<contact t=\"".$cc[ "type" ]."\" a=\"".$cc[ "actual" ]."\">".toCDATA( $cc[ "value" ] )."</contact>" ;
				}
			}
			echo "</result>" ;
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

		$tabAgent = $portalDB->query( "select * from `agent` where `ext_id` = ? Order by `name` desc" , false , "i" , intval( $_REQUEST[ "agency" ] ) );
		$k = 0 ;
		echo "<select id=\"nrr-agent-sel\" class=\"woe-agent-sel\" size=\"10\" onchange=\"agent_select()\" onclick=\"agent_select()\">" ;
		foreach( $tabAgent as &$i ) {
			echo "<option value=\"" , $i[ "id" ] , "\">" , $i[ "name" ] , "</option>" ;
		} unset( $i );
		echo "</select>" ;
		exit();
	}

?>