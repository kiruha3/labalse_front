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
			$GoOut = !$workerEDIT ;
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

	$ei = array( 'f' => 'Фамилия' , 'i' => 'Имя' , 'o' => 'Отчество' );
	$pn = array(
		array( 'И' , '( Есть кто / что ? )' ) ,
		array( 'Р' , '( Нет кого / чего ? )' ) ,
		array( 'Д' , '( Дать кому / чему ? )' ) ,
		array( 'В' , '( Видеть кого / что ? )' ) ,
		array( 'Т' , '( Творить кем / чем ? )' ) ,
		array( 'П' , '( Думать о ком / о чем ? )' )
	);

	$tabDepartments = $portalDB->simpleQuery( 'departments' , array( 'actual' => 1 ) , 'id' );
	$tabSpecGroups = $portalDB->query( "select * from `specialities-groups` where `index` is not null order by `index`" , 'id' );
	$tabPosts = $portalDB->table( 'posts' , 'id' );
	$tabBuilding = $portalDB->table( 'building' , 'id' );
	$tabCabs = $portalDB->table( 'cabinet' );

	foreach ( $tabBuilding as &$bi ) {
		$bi[ 'cabList' ] = array();
	} unset( $bi );

	foreach ( $tabCabs as &$ci ) {
		$tabBuilding[ $ci[ 'building' ] ][ 'cabList' ][]= &$ci ;
	} unset( $ci );

	if ( isset( $_GET[ 'add' ] ) ) {
		$v_department = $_POST[ 'i_department' ];
		$v_post_1 = $_POST[ 'i_post_1' ];
		$v_dual_posts = ( isset( $_POST[ 'i_dual_posts' ] ) ? $_POST[ 'i_dual_posts' ] == 1 : false );
		$v_post_2 = $v_dual_posts ? $_POST[ 'i_post_2' ] : $v_post_1 ;
		$v_cab = $_POST[ 'i_cab' ];
		$v_skype = $_POST[ 'i_skype' ];
		$v_email = $_POST[ 'i_email' ];

		$spl = $portalDB->query( "select `id` from `specialities` order by `id`" );

		$spidl = Array();
		foreach( $spl as $sp ) {
			if ( isset( $_POST[ "i_spec_".$sp[ "id" ] ] ) && $_POST[ "i_spec_".$sp[ "id" ] ] == 1 ) {
				$spidl[]= $sp[ "id" ];
			}
		}

		$name = array();
		foreach( $ei as $ek => $ev ) {
			$name[ $ek ] = array();
			foreach( $pn as $k => $v ) {
				if ( isset( $_REQUEST[ "w-name-".$ek."-".$k ] ) ) {
					$name[ $ek ][ $k ] = trim( $_REQUEST[ "w-name-".$ek."-".$k ] );
				} else {
					$name[ $ek ][ $k ] = "" ;
				}
			}

			$name[ $ek ]= $ek."=".packFormsES( $name[ $ek ] );
		}

		$name = implode( ";" , $name );

		//$portalDB->noResult( "insert into `workers` ( `name` , `post_1_id` , `post_2_id` , `dep` , `spec` , `actual` , `cab` , `skype` , `email` ) values ( ? , ? , ? , )" , "sii" , $name , $v_post_1 , $v_post_2 , $v_department )." , ".Str2SQL( implode( ";" , $spidl ) )." , 1 , ".Int2SQL( $v_cab )." , ".Str2SQL( $v_skype )." , ".Str2SQL( $v_email )." )" );
		//$portalDB->noResult( "insert into `workers` ( `name` , `post_1_id` , `post_2_id` , `dep` , `actual` , `cab` , `skype` , `email` ) values ( ? , ? , ? , ? , 1 , ? , ? , ? )" , "siiiiiss" , $name , $v_post_1 , $v_post_2 , $v_department , $v_cab , $v_skype , $v_email );
		$portalDB->insertRow( "workers-no-spec" , array( "name" => $name , "post_1_id" => $v_post_1 , "post_2_id" => $v_post_2 , "dep" => $v_department , "actual" => 1 , "cab" => $v_cab , "skype" => $v_skype , "email" => $v_email ) );
		$worker_id = $portalDB->lastInsertID();
		foreach( $spidl as $sid ) {
			$specFrom = isset( $_REQUEST[ "spec_from" ][ $sid ] ) ? Date2Int( $_REQUEST[ "spec_from" ][ $sid ] ) : null ;
			$specTo = isset( $_REQUEST[ "spec_to" ][ $sid ] ) ? Date2Int( $_REQUEST[ "spec_to" ][ $sid ] ) : null ;
			$portalDB->insertRow( "workers-spec" , array( "worker_id" => $worker_id , "spec_id" => $sid , "date_from" => $specFrom , "date_to" => $specTo , "actual" => 1 ) );
		}
		$portalDB->noResult( "update `workers-no-spec` set `first_id` = `id` where `id` = ?" , 'i' , $worker_id );

		Redirect( '/reg.php?for-worker='.$worker_id );
	}

	$s_department = "<select name=\"i_department\" class=\"i_department\">" ;
	foreach( $tabDepartments as $dep ) {
		$s_department.= "<option value=\"".$dep[ "id" ]."\">".$dep[ "name" ]."</option>" ;
	}
	$s_department.= "</select>" ;

	$s_post_1 = "<select name=\"i_post_1\" class=\"i_post\">" ;
	$s_post_2 = "<select id=\"i_post_2\" name=\"i_post_2\" class=\"i_post\"><option value=\"-1\"></option>" ;
	foreach( $tabPosts as $post ) {
		$s_post_1.= "<option value=\"".$post[ "id" ]."\">".$post[ "name" ]."</option>" ;
		$s_post_2.= "<option value=\"".$post[ "id" ]."\">".$post[ "name" ]."</option>" ;
	}
	$s_post_1.= "</select>" ;
	$s_post_2.= "</select>" ;

	$s_cab = "<select name=\"i_cab\" class=\"i_cab\">" ;
	foreach ( $tabBuilding as &$bi ) {
		$s_cab.= "<optgroup label=\"".$bi[ "short_name" ]."\">" ;
		foreach ( $bi[ "cabList" ] as &$ci ) {
			$s_cab.= "<option value=\"".$ci[ "id" ]."\">".$ci[ "name" ]."</option>" ;
		} unset( $ci );
		$s_cab.= "</optgroup>" ;
	} unset( $bi );
	$s_cab.= "</select>" ;

	$wsl = "" ;
	if ( strlen( $wsl ) > 0 ) {
		$wsl = explode( ";" , $wsl );
	} else {
		$wsl = Array();
	}
	$t_spec = "" ;
	foreach( $tabSpecGroups as $gr ) {
		$specs = $portalDB->query( "select * from `specialities` where `group` = ? order by `num`" , false , "i" , $gr[ "id" ] );
		$sl = Array();
		foreach( $specs as $sp ) {
			$sl[]= "<input name=\"i_spec_".$sp[ "id" ]."\" type=\"checkbox\" value=\"1\"".( in_array( $sp[ "id" ] , $wsl ) ? " checked" : "" )."> <span class=\"spec-num\">".$gr[ "index" ].".".$sp[ "num" ]."</span> ".$sp[ "desc" ]."<br>" ;
		}
		$t_spec.= "<tr>
			<td class=\"param-name\">
				".$gr[ "index" ].". ".inForm( $gr[ "name" ] , 1 )."
			</td>
			<td class=\"param-value\">
				".implode( $sl )."
			</td>
		</tr>" ;
	}

	MainHead_L2("Админка", "<a href=\"main.php\">Админка</a> - <a href=\"accounts.php\">аккаунты</a> - Регистрация Пользователя" , array( "../%UT/buttons.css" , "%UT/add-user.css" ) , array( "files/add-user.js" ) , "hlp/rights.html" );

		$ei = array( "f" => "Фамилия" , "i" => "Имя" , "o" => "Отчество" );
		$pn = array( array( "И" , "( Есть кто / что ? )" ) , array( "Р" , "( Нет кого / чего ? )" ) , array( "Д" , "( Дать кому / чему ? )" ) , array( "В" , "( Видеть кого / что ? )" ) , array( "Т" , "( Творить кем / чем ? )" ) , array( "П" , "( Думать о ком / о чем ? )" ) );


		echo "<form action=\"add-user.php?add\" method=\"post\" enctype=\"multipart/form-data\">" ;
			echo "<center>
	 			<div class=\"worker-name-div\">
	 			<table align=\"center\">
					<tr><td></td>" ;
					foreach( $ei as $ek => $ev ) {
						echo "<td>".$ev."<br><a onclick=\"\$.NAMES.fioUpd( '" , $ek , "' )\" class=\"w-fio-fill\">заполнить</a></td>" ;
					}
					echo "</tr>" ;

					foreach( $pn as $k => $v ) {
						echo "<tr><td class=\"w-fio-label\">".$v[ 0 ]."<span class=\"w-fio-hlp\">".$v[ 1 ]."</span></td>" ;
						foreach( $ei as $ek => $ev ) {
							echo "<td><input type=\"text\" name=\"w-name-".$ek."-".$k."\" id=\"w-name-".$ek."-".$k."\" class=\"w-fio\"></td>" ;
						}
						echo "</tr>" ;
					}

	 			echo "</table>
	 			</div>
	 		</center>" ;

			echo "<table align=center class=\"worker-datas-table\">
			<tr>
				<td class=\"worker-data\">
					<table class=\"worker-data-table\">
						<tr>
							<td class=\"data-name\" colspan=2>
								Сведения о сотруднике
							</td>
						</tr>
						<tr>
							<td class=\"param-name\">
								Отдел
							</td>
							<td class=\"param-value\">
								$s_department
							</td>
						</tr>
						<tr>
							<td class=\"param-name\">
								Должность
							</td>
							<td class=\"param-value\">
								".$s_post_1."<br>
								<input name=\"i_dual_posts\" type=\"checkbox\" onclick=\"\" value=\"1\"> Так же занимает вторую должность
								".$s_post_2."
							</td>
						</tr>
						<tr>
							<td class=\"param-name\">
								Кабинет
							</td>
							<td class=\"param-value\">
								".$s_cab."
							</td>
						</tr>
						<tr>
							<td class=\"param-name\">
								Skype
							</td>
							<td class=\"param-value\">
								<input name=\"i_skype\" type=\"text\" value=\"\" class=\"i_skype\">
							</td>
						</tr>
						<tr>
							<td class=\"param-name\">
								eMail
							</td>
							<td class=\"param-value\">
								<input name=\"i_email\" type=\"text\" value=\"\" class=\"i_email\">
							</td>
						</tr>
						<tr>
							<td class=\"data-name\" colspan=2>
								Специальности
							</td>
						</tr>
						$t_spec
					</table>
				</td>
			</tr> " ;

		echo "<tr>
			<td class=\"formBtn\">
				<input type=submit value=\"Зарегистрировать\">
			</td>
		</tr>
		</table>
		</form>" ;

  closeHtml();
