<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	include_once( "lconfig.php" );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights(strtoupper( $UserRights[0] ));
		if ( array_key_exists( "ACCOUNTS" , $Rights ) ) {
			$accountsEDIT = in_array( "EDIT" , $Rights[ "ACCOUNTS" ] );
			$accountsACCESS_EDIT = in_array( "ACCESS-EDIT" , $Rights[ "ACCOUNTS" ] );
			$GoOut = !( $accountsEDIT && $accountsACCESS_EDIT && isset( $_REQUEST[ "edit" ] ) );
		} else {
			$accountsEDIT = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}

	$user_id = intval( $_REQUEST[ "edit" ] );

	//$portalDB->dbgMode = true ;
	$tabPlaces = $portalDB->table( "places" );
	$user_worker = $portalDB->row( "select `t1`.* from `workers` as `t1`, `accounts` as `t2` where (`t1`.`id`=`t2`.`worker_id`) and ( `t2`.`id` = ? )" , "i" , $user_id );
	$user_name = NAMES_Format( NAMES_parse( $user_worker[ "name" ] ) , "%F1 %I1 %O1" );

	$RDS = array();

	$copyRights = false ;

	for ( $i = 0 ; $i < count( $tabPlaces ) ; $i++ ) {
		$rd = $tabPlaces[ $i ][ "rights_description" ];
		if ( !is_null( $rd ) ) {
			$RD = ParseRightsDescription( $rd );
			$RDS[] = $RD ;
		}
		if ( isset( $_REQUEST[ "copy_rights_btn_".str_replace( "-" , "_" , $tabPlaces[ $i ][ "id" ] ) ] ) ) {
			$copyRights = $tabPlaces[ $i ][ "id" ];
		}
	}

	if ( $copyRights !== false ) {
		$copyRightsPlaceId = $copyRights ;
		$copyRights = "copy_rights_".str_replace( "-" , "_" , "".$copyRightsPlaceId );
	}

	if ( $copyRights !== false ) {
		$pid = $copyRightsPlaceId ;
		$to_group = false ;
		if ( isset( $_REQUEST[ $copyRights ] ) ) {
			switch ( $_REQUEST[ $copyRights ] ) {
				case "toGroup" :
					if ( isset( $_REQUEST[ "rights_to_group" ] ) ) {
						$src_user_id = $user_id ;
						$user_id = $_REQUEST[ "rights_to_group" ];
						$to_group = true ;
					}
					break ;
				case "none" :
					$src_user_id = false ;
					break ;
				default :
					$src_user_id = intval( $_REQUEST[ $copyRights ] );
			}
		} else {
			$src_user_id = false ;
		}

		if ( !is_array( $user_id ) ) {
			$user_id = array( $user_id );
		}
		if ( $src_user_id !== false ) {
			//$src_r = $portalDB->row( "select * from `access-rights` where ( `place` = ".Int2SQL( $pid )." ) and ( `user_id` = ".Int2SQL( $src_user_id )." ) limit 1" );
			$src_r = $portalDB->simpleRow( "access-rights" , array( "place" => $pid , "user_id" => $src_user_id ) );
			$portalDB->noResult( "delete from `access-rights` where ( `place` = ? ) and ( `user_id` in ( ?* ) )" , "i*i" , $pid , $user_id );
			foreach( $user_id as $i ) {
				//$portalDB->noResult( "insert into `access-rights` ( `place` , `rights` , `user_id` ) values ( ".Int2SQL( $pid )." , ".Str2SQL( $src_r[ "rights" ] )." , ".Int2SQL( $i )." )" );
				$portalDB->insertRow( "access-rights" , array( "place" => $pid , "rights" => $src_r[ "rights" ] , "user_id" => $i ) );
			}
		}
	}

	$user_id = intval( $_REQUEST[ "edit" ] );

	if ( isset( $_REQUEST[ "set" ] ) && $copyRights === false ) {
		$RDSI = 0 ;
		for ( $i = 0 ; $i < count( $tabPlaces ) ; $i++ ) {
			if ( !is_null( $tabPlaces[ $i ][ "rights_description" ] ) ) {
				$place_id = $tabPlaces[ $i ][ "id" ];
				$ur_set_place_rights = isset( $_REQUEST[ "place$place_id" ] );
				//RowAsObject( $con , "select `id` from `access_rights` where (`user_id`=$user_id) and (`place`=$place_id);" );
				$row = $portalDB->simpleRow( "access-rights" , array( "user_id" => $user_id , "place" => $place_id ) );
				if ( $row !== false ) {
					$ur_access_rights_id = $row[ "id" ];
					if ( !$ur_set_place_rights ) {
						//NoResultQuery( $con , "delete from `access_rights` where `id`=$ur_access_rights_id;" );
						$portalDB->deleteRow( "access-rights" , $ur_access_rights_id );
					}
				} else
				if ( $ur_set_place_rights ) {
					//$row = RowAsObject( $con , "select `id` from `access_rights` where `place` is null limit 1;" );
					$row = $portalDB->simpleRow( "access-rights" , array( "place" => null ) );
					$ur_access_rights_id = $row[ "id" ];
					//NoResultQuery( $con , "update `access_rights` set `user_id`=$user_id , `place`=$place_id where `id`=$ur_access_rights_id;" );
					$portalDB->updateRow( "access-rights" , array( "user_id" => $user_id , "place" => $place_id , "id" => $ur_access_rights_id ) );
					$portalDB->insertRow( "access-rights" , array( "place" => null ) );
				}

				if ( $ur_set_place_rights ) {
					$ur_place_rights = "" ;
					$RD = $RDS[ $RDSI ];
					for ( $j = 0 ; $j < count( $RD ) ; $j++ ) {
						//
						$pdd = $RD[ $j ];
						if ( isset( $_REQUEST[ "use_place".$place_id."param".$pdd[ "N" ] ] ) ) {
							$ur_place_rights.= $pdd[ "N" ]." = " ;
							if ( $pdd[ "T" ] == "BF" ) {
								$bfc = 0 ;
								for ( $k = 0 ; $k < count( $pdd[ "V" ] ) ; $k++ ) {
									if ( isset( $_REQUEST[ "place".$place_id."param".$pdd[ "N" ]."_".$pdd[ "V" ][ $k ] ] ) ) {
										if ( $bfc > 0 ) {
											$ur_place_rights.= " / " ;
										}
										$ur_place_rights.= $pdd[ "V" ][ $k ];
										$bfc++ ;
									}
								}
							} else
							if ( $pdd[ "T" ] == "L" || $pdd[ "T" ] == "A" ) {
								$ur_place_rights.= $_REQUEST[ "place".$place_id."param".$pdd[ "N" ] ];
							}
							$ur_place_rights.= " ; " ;
						}
					}
					//echo $ur_place_rights."<br>\r\n" ;
					$portalDB->updateRow( "access-rights" , array( "rights" => $ur_place_rights , "id" => $ur_access_rights_id ) );
				}
				$RDSI++ ;
			}
		}
	}

	MainHead_L2( "Админка" , "<a href=\"main.php\">Админка</a> - <a href=\"accounts.php\">аккаунты</a> - права доступа" , array( "../%UT/buttons.css" , "%UT/rights.css" ) , array( "files/rights.js" ) , "hlp/rights.html" );

	$q = "select
			`t1`.*,
			`t2`.`name`
		from
			`accounts` as `t1`,
			`workers` as `t2`,
			`departments` as `t3`
		where
			(`t1`.`worker_id` = `t2`.`id`) and
			(`t2`.`dep` = `t3`.`id`) and
			( `t2`.`actual` = 1 )
		order by `t2`.`name`" ;

	$tabAccounts = $portalDB->query( $q );

	$accounts = "<option value=\"none\" selected></option><option value=\"toGroup\">/ назначить группе /</option>" ;
	$accounts2 = "<div class=\"cb-all\"><input id=\"rights_to_group_all\" type=\"checkbox\" value=\"\" onclick=\"rightsToGroup( 0 )\">Все</div>" ;
	foreach( $tabAccounts as $a ) {
		$accounts.= "<option value=\"".$a[ "id" ]."\">".NAMES_Format( NAMES_parse( $a[ "name" ] ) , "%F1 %i.%o." )." (".$a[ "login" ].")</option>" ;
		$accounts2.= "<input name=\"rights_to_group[]\" type=\"checkbox\" value=\"".$a[ "id" ]."\" onclick=\"rightsToGroup( 1 )\">".NAMES_Format( NAMES_parse( $a[ "name" ] ) , "%F1 %i.%o." )." (".$a[ "login" ].")<br>" ;
	}


	echo "<center>
		<div class=\"worker-div\">
			".$user_name."
		</div>
	</center>" ;

	if ( isset( $_REQUEST[ "set" ] ) ) {
		echo "<center>
			<div class=\"change-ok\">
				Изменения сохранены
			</div>
		</center>" ;
	}

	echo "<form action=\"rights.php?edit=".$user_id."&set\" method=\"post\"><table align=center class=\"places-rights-table\">" ;
	$RDSI = 0 ;
	for ( $i = 0 ; $i < count( $tabPlaces ) ; $i++ ) {
		if ( !is_null( $tabPlaces[ $i ][ "rights_description" ] ) ) {
			$place_id = $tabPlaces[ $i ][ "id" ];
			$row = $portalDB->simpleRow( "access-rights" , array( "user_id" => $user_id , "place" => $place_id ) );
            if ( $row !== false ) {
				$ur = ParseRights( $row[ "rights" ] );
			} else {
				$ur = array();
			}

			$RD = $RDS[ $RDSI ];
			echo "<tr>
				<td class=\"place-rights\">
				<table class=\"place-rights-table\">
					<tr>
						<td class=\"place-name\" colspan=\"2\">
							<div class=\"copy-rights-div\">
								<select name=\"copy_rights_".str_replace( "-" , "_" , $place_id )."\" class=\"copy-rights\" on>".$accounts."</select><input name=\"copy_rights_btn_".str_replace( "-" , "_" , $place_id )."\" type=\"submit\" value=\">>\" class=\"copy-rights-submit\">
							</div>
							<div class=\"place-name-div\"><input name=\"place".$place_id."\" type=checkbox ".( $row !== false ? "checked" : "" )."> ".$tabPlaces[ $i ][ "name" ]."</div>
						</td>
					</tr>" ;

			for ( $j = 0 ; $j < count( $RD ) ; $j++ ) {
				$pdd = $RD[ $j ];
				echo "<tr>
					<td class=\"param-name\">
						<input name=\"use_place".$place_id."param".$pdd[ "N" ]."\" type=checkbox ".( ( $row !== false ) && array_key_exists( $pdd[ "N" ] , $ur ) ? "checked" : "" )."> ".$pdd[ "D" ]."
					</td>
					<td class=\"param-value\">" ;

				if ( $pdd[ "T" ] == "BF" ) {
					for ( $k = 0 ; $k < count( $pdd[ "V" ] ) ; $k++ ) {
						echo "<input name=\"place".$place_id."param".$pdd[ "N" ]."_".$pdd[ "V" ][ $k ]."\" type=checkbox ".( ( $row !== false ) && array_key_exists( $pdd[ "N" ] , $ur ) && in_array( $pdd[ "V" ][ $k ] , $ur[ $pdd[ "N" ] ] ) ? "checked" : "" )."> ".$pdd[ "VD" ][ $k ]."<br><br>\r\n" ;
					}
				} else
				if ( $pdd[ "T" ] == "L" ) {
					$L = "" ;
					for ( $k = 0 ; $k < count( $pdd[ "V" ] ) ; $k++ ) {
						$L.= "<option value=\"".$pdd[ "V" ][ $k ]."\" ".( ( $row !== false ) && array_key_exists( $pdd[ "N" ] , $ur ) && ( $pdd[ "V" ][ $k ] == $ur[ $pdd[ "N" ] ][ 0 ] ) ? "selected" : "" )."> ".$pdd[ "V" ][ $k ]."</option>" ;
					}
					echo "<select name=\"place".$place_id."param".$pdd[ "N" ]."\" size=1>".$L."</select>" ;
				} else
				if ( $pdd[ "T" ] == "A" ) {
					echo "<input name=\"place".$place_id."param".$pdd[ "N" ]."\" type=text ".( ( $row !== false ) && array_key_exists( $pdd[ "N" ] , $ur ) ? " value=\"".$ur[ $pdd[ "N" ] ][ 0 ]."\"" : "" ).">" ;
				}
				echo "</td>
				</tr>" ;
			}

			echo "</table>
				</td>
			</tr>" ;
			$RDSI++ ;
		}
	}

	echo "<tr>
			<td class=\"formBtn\">
				<input type=submit value=\"Назначить права\">
			</td>
		</tr>
		</table>
		<div class=\"rights-2-group-panel\">
			".$accounts2."
		</div>
	</form>" ;

  closeHtml();
?>