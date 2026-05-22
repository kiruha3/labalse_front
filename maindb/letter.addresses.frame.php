<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserThemeLoc
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	 
	require_once( "../cores/core.maindb.php" );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	$GoOut = !isset( $_REQUEST[ "mid" ] );

	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit ;
	}

	/*function getAddr( $code , $ap2 , $av = false ) {
		global $con2 ;

		$res = array();

		if ( strlen( $code ) != 17 || $code == "00000000000000000" ) {
			return "< адрес не указан >" ;
		}

		$sd = RowAsArray( $con2 , "select * from `street` where `CODE` = ".Str2SQL( $code )." ;" );
		$res[]= trim( $sd[ "SOCR" ] ).". ".trim( $sd[ "NAME" ] );
		$postIndex = trim( $sd[ "INDEX" ] );

		$kc = array( substr( $code , 0 , 2 ) , substr( $code , 2 , 3 ) , substr( $code , 5 , 3 ) , substr( $code , 8 , 3 ) );
		$kcm = array( "00" , "000" , "000" , "000" );
		$p = 3 ;
		while ( $p >= 0 && $kc[ $p ] == $kcm[ $p ] ) {
			$p-= 1 ;
		}

		while ( $p >= 0 ) {
			$klo = RowAsArray( $con2 , "select * from `kladr` where `CODE` = ".Str2SQL( implode( $kc )."00" )." ;" );
			$res[]= trim( $klo[ "SOCR" ] ).". ".trim( $klo[ "NAME" ] );
			$kc[ $p ] = $kcm[ $p ];
			$p-= 1 ;
			while ( $p >= 0 && $kc[ $p ] == $kcm[ $p ] ) {
				$p-= 1 ;
			}
		}

		$res = array_reverse( $res );

		if ( !$av ) {
			return ( $postIndex != "" ? $postIndex.", " : "" ).implode( ", " , $res ).", ".$ap2 ;
		} else {
			return array( "index" => $postIndex , "addr" => ( implode( ", " , $res ).", ".$ap2 ) );
		}
	}*/

	function processAddress( $addr , $splited = false ) {
		$sdi = array();
		if ( preg_match( "/(?:[,. ]+(\\d{6})[,. ]*)/" , $addr , $sdi ) == 1 ) {
			if ( $splited ) {
				return array( "index" => $sdi[ 1 ] , "address" => preg_replace( "/(?:[,. ]+(\\d{6})[,. ]*)/" , "" , $addr ) );
			} else {
				return $sdi[ 1 ].", ".preg_replace( "/(?:[,. ]+(\\d{6})[,. ]*)/" , "" , $addr );
			}
		} else
		if ( preg_match( "/(?:[,. ]*(\\d{6})[,. ]+)/" , $addr , $sdi ) == 1 ) {
			if ( $splited ) {
				return array( "index" => $sdi[ 1 ] , "address" => preg_replace( "/(?:[,. ]*(\\d{6})[,. ]+)/" , "" , $addr ) );
			} else {
				return $sdi[ 1 ].", ".preg_replace( "/(?:[,. ]*(\\d{6})[,. ]+)/" , "" , $addr );
			}
		} else {
			if ( $splited ) {
				return array( "index" => "" , "address" => $addr );
			} else {
				return $addr ;
			}
		}
	}



	MainHead_Frame( array( "%UT/letter.addresses.frame.css" ) , array( "files/letter.addresses.frame.js" ) , "" );

	$mid = getCharID( $_REQUEST[ "mid" ] , DOCTYPE_MATINCOMING );

	if ( isset( $_REQUEST[ "addressee" ] ) || isset( $_REQUEST[ "destination" ] ) ) {
		foreach( $_REQUEST[ "destination" ] as $id => $d ) {
			$d = processAddress( $d );
			if ( $id == "fromBase" ) {
				$ad = $portalDB->row( "select `from_agency` as `id` from `matincoming` where ( `id` = ? )" , "s" , $mid );
				$portalDB->updateRow( "agency" , array( "destination" => $d , "id" => $ad[ "id" ] ) );
			} else {
				if ( isset( $_REQUEST[ "addressee" ][ $id ] ) ) {
					$a = $_REQUEST[ "addressee" ][ $id ];
				} else {
					$a = "" ;
				}
				$portalDB->updateRow( "addresses" , array( "addressee" => $a , "destination" => $d , "id" => $id ) );
			}
		}
	}

	if ( isset( $_REQUEST[ "addDestination" ] ) ) {
		$portalDB->insertRow( "addresses" , array( "mat_id" => $mid , "addressee" => "" , "destination" => "" ) );
	} else
	if ( isset( $_REQUEST[ "delDestination" ] ) ) {
		if ( isset( $_REQUEST[ "id" ] ) ) {
			$portalDB->noResult( "delete from `addresses` where ( `id` in ( ?* ) )" , "*i" , $_REQUEST[ "id" ] );
		}
	} else
	if ( isset( $_REQUEST[ "saveDestination" ] ) ) {
	}


	echo "<form action=\"./letter.addresses.frame.php?mid=" , $mid , "\" method=\"post\">
	<table id=\"letter_dlg_tab\" class=\"letter-dlg-tab\">
		<tr>
			<td class=\"ldt-cap\"></td>
			<td class=\"ldt-cap\">Кому</td>
			<td class=\"ldt-cap\">Куда</td>
		</tr>" ;

	$ad = $portalDB->row( "select `t1`.`ext_id` as `type_of_agency` , `t1`.`name` as `agency_name` , `t2`.`name` as `agent_name` , `t1`.`destination` from `agency` as `t1` , `agent` as `t2` , `matincoming` as `t3` where ( `t1`.`id` = `t3`.`from_agency` ) and ( `t2`.`id` = `t3`.`from_agent` ) and ( `t3`.`id` = ? )" , "s" , $mid );

		echo "<tr>
			<td class=\"ldt-prna-btn\"></td>
			<td class=\"ldt-addressee\">" , ( $ad[ "type_of_agency" ] != "11" ? $ad[ "agency_name" ].", " : "" ) , $ad[ "agent_name" ] , "</td>
			<td class=\"ldt-destination\">
				<table class=\"destination-panel-top\">
					<tr>
						<td class=\"destination-panel-inp\">
							<input name=\"destination[fromBase]\" type=\"text\" value=\"" , $ad[ "destination" ] , "\" class=\"destination-inp\">
						</td>
						<td class=\"destination-panel-btn\">
							<a onclick=\"editAddress( 'from-base' )\" class=\"address-lnk\"><img src=\"themes/".$UserThemeLoc."/search.png\"></a>
						</td>
					</tr>
				</table>
			</td>
		</tr>" ;

		$ad = $portalDB->simpleQuery( "addresses" , array( "mat_id" => $mid ) );
		foreach( $ad as &$i ) {
			echo "<tr>
				<td class=\"ldt-prna-btn\"><input name=\"id[]\" type=\"checkbox\" value=\"" , $i[ "id" ] , "\"></td>
				<td class=\"ldt-addressee\"><input name=\"addressee[" , $i[ "id" ] , "]\" type=\"text\" value=\"" , $i[ "addressee" ] , "\" class=\"addressee-inp\"></td>
 				<td class=\"ldt-destination\">
					<table class=\"destination-panel-top\">
						<tr>
							<td class=\"destination-panel-inp\">
								<input name=\"destination[" , $i[ "id" ] , "]\" type=\"text\" value=\"" , $i[ "destination" ] , "\" class=\"destination-inp\">
							</td>
							<td class=\"destination-panel-btn\">
								<a onclick=\"editAddress( " , $i[ "id" ] , " )\" class=\"address-lnk\"><img src=\"themes/".$UserThemeLoc."/search.png\"></a>
							</td>
						</tr>
					</table>
				</td>
			</tr>" ; //<td class=\"ldt-destination\"><input name=\"destination[" , $i[ "id" ] , "]\" type=\"text\" value=\"" , $i[ "destination" ] , "\" class=\"destination-inp\"> <a onclick=\"editAddress( " , $i[ "id" ] , " )\" class=\"address-lnk\"><img src=\"themes/".$UserThemeLoc."/search.png\"></a></td>
		} unset( $i );

		echo "</table>
		<div class=\"buttons-panel\">
			<input name=\"addDestination\" type=\"submit\" value=\"добавить адресата\">
			<input name=\"delDestination\" type=\"submit\" value=\"удалить адресата\">
			<input name=\"saveDestination\" type=\"submit\" value=\"сохранить изменения\">
		</div>
		</form>" ;

	closeHtml_Frame();

?>