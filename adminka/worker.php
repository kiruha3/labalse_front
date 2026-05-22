<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	include_once( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $portalDB
	 */
	include_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'ACCOUNTS' , $Rights ) ) {
			$workerEDIT = in_array( 'WORKER-EDIT' , $Rights[ 'ACCOUNTS' ] );
			$GoOut = !( $workerEDIT && isset( $_REQUEST[ 'edit' ] ) );
		} else {
			$accountsEDIT = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit ;
	}

	//$portalDB->dbgMode = true ;

	$ei = array( "f" => "Фамилия" , "i" => "Имя" , "o" => "Отчество" );
	$pn = array( array( "И" , "( Есть кто / что ? )" ) , array( "Р" , "( Нет кого / чего ? )" ) , array( "Д" , "( Дать кому / чему ? )" ) , array( "В" , "( Видеть кого / что ? )" ) , array( "Т" , "( Творить кем / чем ? )" ) , array( "П" , "( Думать о ком / о чем ? )" ) );

	$worker_id = intval( $_REQUEST[ 'edit' ] );

	$worker = $portalDB->simpleRow( 'workers' , $worker_id );
	$worker = $portalDB->row( "select max( `id` ) as `max-id` from `workers` where ( `first_id` = ? )" , 'i' , $worker[ 'first_id' ] );

	if ( $worker_id != $worker[ 'max-id' ] ) {
		Redirect( 'worker.php?edit='.$worker[ 'max-id' ] );
		exit();
	}

	$worker_id = $worker[ 'max-id' ];
	$worker = $portalDB->simpleRow( 'workers' , $worker_id );
	$worker_name = NAMES_Format( NAMES_parse( $worker[ 'name' ] ) , '%F1 %I1 %O1' );
	$tabDepartments = $portalDB->query( "select * from `departments` order by `ind` asc" , 'id' );
	$tabSpecGroups = $portalDB->query( "select * from `specialities-groups` where `index` is not null order by `index`" , 'id' );
	$tabPosts = $portalDB->query( "select * from `posts` order by `actual` desc , `name` asc" , 'id' );
	$tabBuilding = $portalDB->table( 'building' , 'id' );
	$tabCabs = $portalDB->table( 'cabinet' );

	foreach ( $tabBuilding as &$bi ) {
		$bi[ "cabList" ] = array();
	} unset( $bi );

	foreach ( $tabCabs as &$ci ) {
		$tabBuilding[ $ci[ "building" ] ][ "cabList" ][]= &$ci ;
	} unset( $ci );

	if ( isset( $_REQUEST[ "set" ] ) ) {
		//echo "<br>set<br>" ;
		//print_r_html( $_REQUEST , 1 );
		$v_department = $_REQUEST[ "i_department" ];
		$v_post_1 = $_REQUEST[ "i_post_1" ];
		$v_dual_posts = ( isset( $_REQUEST[ "i_dual_posts" ] ) ? $_REQUEST[ "i_dual_posts" ] == 1 : false );
		$v_post_2 = $v_dual_posts ? $_REQUEST[ "i_post_2" ] : $v_post_1 ;
		$v_cab = $_REQUEST[ "i_cab" ];
		$v_skype = $_REQUEST[ "i_skype" ];
		$v_email = $_REQUEST[ "i_email" ];
		$v_adLogin = $worker[ "ad-login" ];

		$v_change_name = isset( $_REQUEST[ "i_change_name" ] ) && $_REQUEST[ "i_change_name" ] == "change_name" ;
		if ( $v_change_name ) {
			$v_name = array();
			foreach( $ei as $ek => $ev ) {
				$v_name[ $ek ] = array();
				foreach( $pn as $k => $v ) {
					if ( isset( $_REQUEST[ "w-name-".$ek."-".$k ] ) ) {
						$v_name[ $ek ][ $k ] = trim( $_REQUEST[ "w-name-".$ek."-".$k ] );
					} else {
						$v_name[ $ek ][ $k ] = "" ;
					}
				}

				$v_name[ $ek ]= $ek."=".packFormsES( $v_name[ $ek ] );
			}

			$v_name = implode( ";" , $v_name );
		}

		$spl = $portalDB->query( "select `id` from `specialities` order by `id`" );

		$spidl = Array();
		foreach( $spl as $sp ) {
			if ( isset( $_REQUEST[ "i_spec_".$sp[ "id" ] ] ) && $_REQUEST[ "i_spec_".$sp[ "id" ] ] == 1 ) {
				$spidl[]= $sp[ "id" ];
			}
		}

		$nn = false ;
		if ( $worker[ "dep" ] != $v_department ) {
			$nn = true ;
		}
		if ( $worker[ "post_1_id" ] != $v_post_1 ) {
			$nn = true ;
		}
		if ( $worker[ "post_2_id" ] != $v_post_2 ) {
			$nn = true ;
		}
		if ( $v_change_name && !( isset( $_REQUEST[ 'i_change_name_correct' ] ) && $_REQUEST[ 'i_change_name_correct' ] == 'change_name_correct' ) ) {
			$nn = true ;
		}


		$nns = false ;
		if ( $worker[ "cab" ] != $v_cab ) {
			$nns = true ;
		}
		if ( $worker[ "skype" ] != $v_skype ) {
			$nns = true ;
		}
		if ( $worker[ "email" ] != $v_email ) {
			$nns = true ;
		}
		if ( $v_change_name && ( isset( $_REQUEST[ 'i_change_name_correct' ] ) && $_REQUEST[ 'i_change_name_correct' ] == 'change_name_correct' ) ) {
			$nns = true ;
		}

		$owsl = $portalDB->simpleQuery( "workers-spec" , compact( "worker_id" ) );
		$ospidl = array_column( $owsl , "spec_id" );

		$ospidl = array_unique( $ospidl );
		sort( $ospidl , SORT_NUMERIC );

		$spidl = array_unique( $spidl );
		sort( $spidl , SORT_NUMERIC );


		$ospidl = array_combine( $ospidl , $ospidl );
		$spidl = array_combine( $spidl , $spidl );

		$d_spidl = array_diff_key( $ospidl , $spidl );
		$a_spidl = array_diff_key( $spidl , $ospidl );
		if ( count( $d_spidl ) > 0 || count( $a_spidl ) > 0 ) {
			$nn = true ;
		}

		//var_dump_html( $nn );
		if ( $nn ) {
			$portalDB->rawQuery( "lock tables `workers` read , `workers-no-spec` write , `accounts` write" );
			//$wids = $portalDB->simpleQuery( "workers" , array( "first_id" => $worker[ "first_id" ] ) , "id" );
			$wids = $portalDB->query( "select * from `workers` where `first_id` = ?" , "id" , "i" , $worker[ "first_id" ] );
			$widl = array_column( $wids , "id" );
			$portalDB->noResult( "update `workers-no-spec` set `actual` = 0 where ( `first_id` = ? )" , "i" , $worker[ "first_id" ] );
			$portalDB->insertRow( "workers-no-spec" , array(
				"first_id" => $worker[ "first_id" ] ,
				"name" => $worker[ "name" ] ,
				"post_1_id" => $v_post_1 ,
				"post_2_id" => $v_post_2 ,
				"dep" => $v_department ,
				//"spec" => implode( ";" , $spidl ) ,
				"actual" => 1 ,
				"cab" => $v_cab ,
				"skype" => $v_skype ,
				"email" => $v_email ,
				"ad-login" => $v_adLogin
			) );

			$lid = $portalDB->lastInsertID();
			if ( $v_change_name ) {
				$portalDB->updateRow( "workers-no-spec" , array( "name" => $v_name , "id" => $lid ) );
			}

			$portalDB->noResult( "update `accounts` set `worker_id` = ? where `worker_id` in ( ?* )" , "i*i" , $lid , $widl );
			$portalDB->rawQuery( "unlock tables" );
			$worker_id = $lid ;
			foreach ( $owsl as $owsli ) {
				$sid = $owsli[ "spec_id" ];
				if( isset( $spidl[ $sid ] ) ) {
					$specFrom = isset( $_REQUEST[ "spec_from" ][ $sid ] ) ? Date2Int( $_REQUEST[ "spec_from" ][ $sid ] ) : null ;
					$specTo = isset( $_REQUEST[ "spec_to" ][ $sid ] ) ? Date2Int( $_REQUEST[ "spec_to" ][ $sid ] ) : null ;
					$portalDB->insertRow( "workers-spec" , array( "worker_id" => $lid , "spec_id" => $sid , "date_from" => $specFrom , "date_to" => $specTo , "actual" => 1 ) );
				}
			}

			foreach ( $a_spidl as $sid ) {
				$specFrom = isset( $_REQUEST[ "spec_from" ][ $sid ] ) ? Date2Int( $_REQUEST[ "spec_from" ][ $sid ] ) : null ;
				$specTo = isset( $_REQUEST[ "spec_to" ][ $sid ] ) ? Date2Int( $_REQUEST[ "spec_to" ][ $sid ] ) : null ;
				$portalDB->insertRow( "workers-spec" , array( "worker_id" => $lid , "spec_id" => $sid , "date_from" => $specFrom , "date_to" => $specTo , "actual" => 1 ) );
			}
		} else
		if ( $nns ) {
			$portalDB->updateRow( "workers-no-spec" , array( "cab" => $v_cab , "skype" => $v_skype , "email" => $v_email , "id" => $worker[ "id" ] ) );
			if ( $v_change_name ) {
				$portalDB->updateRow( "workers-no-spec" , array( "name" => $v_name , "id" => $worker[ "id" ] ) );
			}
		}
	} else
	if ( isset( $_REQUEST[ "deactivate" ] ) ) {
		$portalDB->updateRow( "workers-no-spec" , array( "actual" => 0 ,  "id" => $worker_id ) );
		Redirect( "worker.php?edit=".$worker_id );
		exit();
	} else
	if ( isset( $_REQUEST[ "activate" ] ) ) {
		$portalDB->updateRow( "workers-no-spec" , array ( "actual" => 1 , "id" => $worker_id ) );
		Redirect( "worker.php?edit=".$worker_id );
		exit();
	}

	$worker = $portalDB->simpleRow( "workers" , $worker_id );
	$workerNameParsed = NAMES_parse( $worker[ "name" ] );
	$worker_name = NAMES_Format( $workerNameParsed , "%F1 %I1 %O1" );

	$s_department = "<select name=\"i_department\" class=\"i_department\">" ;
	foreach( $tabDepartments as $dep ) {
		$s_department.= "<option value=\"".$dep[ "id" ]."\"".( $worker[ "dep" ] == $dep[ "id" ] ? " selected" : "" ).( $dep[ "actual" ] != 1 ? " disabled" : "" ).">".$dep[ "ind" ]." : ".$dep[ "name" ]."</option>" ;
	}
	$s_department.= "</select>" ;

	$s_cab = "<select name=\"i_cab\" class=\"i_cab\">" ;
	foreach ( $tabBuilding as &$bi ) {
		$s_cab.= "<optgroup label=\"".$bi[ "short_name" ]."\">" ;
		foreach ( $bi[ "cabList" ] as &$ci ) {
			$s_cab.= "<option value=\"".$ci[ "id" ]."\"".( $worker[ "cab" ] == $ci[ "id" ] ? " selected" : "" ).">".$ci[ "name" ]."</option>" ;
		} unset( $ci );
		$s_cab.= "</optgroup>" ;
	} unset( $bi );
	$s_cab.= "</select>" ;

	$s_post_1 = "<select name=\"i_post_1\" class=\"i_post\">" ;
	$s_post_2 = "<select id=\"i_post_2\" name=\"i_post_2\" class=\"i_post\"><option value=\"-1\"".( $worker[ "post_1_id" ] == $worker[ "post_2_id" ] ? " selected" : "" )."></option>" ;
	foreach( $tabPosts as $post ) {
		$s_post_1.= "<option value=\"".$post[ "id" ]."\"".( $worker[ "post_1_id" ] == $post[ "id" ] ? " selected" : "" ).( $post[ 'actual' ] == 1 ? '' : 'disabled="disabled"' ).">".$post[ "name" ]."</option>" ;
		$s_post_2.= "<option value=\"".$post[ "id" ]."\"".( $worker[ "post_2_id" ] == $post[ "id" ] && $worker[ "post_1_id" ] != $worker[ "post_2_id" ] ? " selected" : "" ).( $post[ 'actual' ] == 1 ? '' : 'disabled="disabled"' ).">".$post[ "name" ]."</option>" ;
	}
	$s_post_1.= "</select>" ;
	$s_post_2.= "</select>" ;

	$wsl = array_column( $portalDB->simpleQuery( "workers-spec" , array( "worker_id" => $worker_id ) ) , "spec_id" );
	/*foreach( $wsl as $cws ) {

	}*/

	$t_spec = '' ;
	foreach( $tabSpecGroups as $gr ) {
		$specs = $portalDB->simpleQuery( 'specialities' , array( 'group' => $gr[ 'id' ] ) , false , array( 'order' => 'num' ) );
		$sl = Array();
		foreach( $specs as $sp ) {
			$sl[]= '<input name="i_spec_'.$sp[ 'id' ].'" type="checkbox" value="1"'.( in_array( $sp[ 'id' ] , $wsl ) ? ' checked' : '' ).'> <span class="spec-num">'.$gr[ 'index' ].'.'.$sp[ 'num' ].'</span> '.$sp[ 'desc' ].'<br>' ;
		}
		$t_spec.= '<tr>
			<td class="param-name">
				'.$gr[ 'index' ].'. '.inForm( $gr[ 'name' ] , 1 ).'
			</td>
			<td class="param-value">
				'.implode( $sl ).'
			</td>
		</tr>' ;
	}

	MainHead_L2('Админка', '<a href="main.php">Админка</a> - <a href="accounts.php">аккаунты</a> - сведения о сотруднике' , array( "../%UT/buttons.css" , "%UT/worker.css" ) , array( 'files/worker.js' ) , 'hlp/rights.html' );

 		echo '<form action="worker.php?edit='.$worker_id.'&set" method="post" enctype="multipart/form-data">
		<center>
			<input id="change-name" name="i_change_name" type="checkbox" value="change_name">
 			<div class="worker-name-div">
 				<div>'.$worker_name.' <label for="change-name" class="worker-name-editor-btn"></label></div>
				<div class="worker-name-editor">
					<table align="center">
						<tr><td></td>' ;
							foreach( $ei as $ek => $ev ) {
								echo '<td>'.$ev.'<br><a onclick="$.NAMES.fioUpd( \'' , $ek , '\' )" class="w-fio-fill">заполнить</a></td>' ;
							}
							echo '</tr>' ;

							foreach( $pn as $k => $v ) {
								echo '<tr><td class="w-fio-label">'.$v[ 0 ].'<span class="w-fio-hlp">'.$v[ 1 ].'</span></td>' ;
								foreach( $ei as $ek => $ev ) {
									echo '<td><input type="text" name="w-name-'.$ek.'-'.$k.'" id="w-name-'.$ek.'-'.$k.'" class="w-fio" value="'.NAMES_Format( $workerNameParsed , '%'.strtoupper( $ek ).( $k + 1 ) ).'"></td>' ;
								}
								echo '</tr>' ;
							}
					echo '</table>
					<label><input id="change-name-correct" name="i_change_name_correct" type="checkbox" value="change_name_correct"> В порядке исправления ошибки</label>
				</div>
			</div>
		</center>' ;


		if ( $worker[ 'actual' ] ) {
 			echo '<div class="deactivate-panel"><a onclick="deactivate( '.$worker_id.' )" class="btn3">Деактивировать (увольнение или ...)</a></div>' ;
 		} else {
 			echo '<div class="deactivate-panel"><a onclick="activate( '.$worker_id.' )" class="btn3">Активировать</a></div>' ;
 		}


		echo '<table align="center" class="worker-datas-table">
			<tr>
				<td class="worker-data">
					<table class="worker-data-table">
						<tr>
							<td class="data-name" colspan="2">
								Сведения о сотруднике
							</td>
						</tr>
						<tr>
							<td class="param-name">
								Отдел
							</td>
							<td class="param-value">
								'.$s_department.'
							</td>
						</tr>
						<tr>
							<td class="param-name">
								Должность
							</td>
							<td class="param-value">
								'.$s_post_1.'<br>
								<input name="i_dual_posts" type="checkbox" onclick=""'.( $worker[ 'post_1_id' ] == $worker[ 'post_2_id' ] ? '' : ' checked' ).' value="1"> Так же занимает вторую должность
								'.$s_post_2.'
							</td>
						</tr>
						<tr>
							<td class="param-name">
								Кабинет
							</td>
							<td class="param-value">
								'.$s_cab.'
							</td>
						</tr>
						<tr>
							<td class="param-name">
								Skype
							</td>
							<td class="param-value">
								<input name="i_skype" type="text" value="'.$worker[ 'skype' ].'" class="i_skype">
							</td>
						</tr>
						<tr>
							<td class="param-name">
								eMail
							</td>
							<td class="param-value">
								<input name="i_email" type="text" value="'.$worker[ 'email' ].'" class="i_email">
							</td>
						</tr>
						<tr>
							<td class="data-name" colspan="2">
								Специальности
							</td>
						</tr>
							'.$t_spec.'
					</table>
				</td>
			</tr><tr>
				<td class="formBtn">
					<input type="submit" value="Сохранить изменения">
				</td>
			</tr>
		</table>
	</form>' ;

  closeHtml();
