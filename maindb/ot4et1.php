<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserDepartment
	 * @var $MonthNames
	 * @var $TAB_CASECATEGORY
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights(strtoupper($UserRights[0]));
		if ( array_key_exists("EXTENTIONS", $Rights) ) {
			$mayList = in_array( "EXP-EXP-LIST-REV" , $Rights[ "EXTENTIONS" ] );
			$mayListAll = in_array("EXP-EXP-LIST-ALL-REV", $Rights["EXTENTIONS"]);
		} else {
			$mayList = $mayListAll = false ;
		}

		$GoOut = !( $mayList || $mayListAll );
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit ;
	}

	$allWorkers = isset( $_REQUEST[ "aw" ] );

	$tabWorkers = $portalDB->query( "select * from `workers` where `id` in ( select max( `id` ) from `workers` where ".( $mayListAll ? "" : "( `dep` = ".$UserDepartment." )  and " )."( `spec` is not null ) group by `first_id` ) order by `name` asc" );
	$tabPosts = $portalDB->table( "posts" , "id" );

	$TAB_CASECATEGORY = $portalDB->table( "casecategory" );

	if ( isset( $_COOKIE[ 'ot4et1_cc' ] ) ) {
		$sCC = getIDList( $_COOKIE[ 'ot4et1_cc' ] , true );
	} else {
		$sCC = false ;
	}

	$caseCategoryList = "" ;
	foreach( $TAB_CASECATEGORY as $cc ) {
		if ( $sCC !== false ) {
			$ccc = in_array( $cc[ 'id' ] , $sCC );
		} else {
			$ccc = !( $cc[ "id" ] == 1 || $cc[ "id" ] == 5 );
		}
		$caseCategoryList.= "<input type=\"checkbox\" name=\"i_case_cat[]\" value=\"".$cc[ "id" ]."\" ".( $ccc ? "checked" : '' )."> ".inForm( $cc[ "name" ] )."<br>" ;
	}

	$WorkersList = "<select id=\"i_worker\" name=\"i_worker\" class=\"i_worker\">" ;
	$WorkerPost = -1 ;
	foreach( $tabWorkers as $w ) {
		if ( $w[ "actual" ] == 1 || $allWorkers ) {
			$WorkersList.= "<option value=\"".$w[ "first_id" ]."\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) , "%F1 %I1 %O1" )."</option>" ;
		}
	}

	$WorkersList.= "</optgroup></select>" ;

	$func = "ot4et.php" ;
	MainHead_L2("База - карточка 2 уровня", "<a href='main.php'>База</a> - отчет", array("../%UT/buttons.css" , "../%UT/buttons.css", "%UT/ot4et1.css"),array(), "hlp/main.html");

	if (
		isset( $_COOKIE[ 'ot4et1_ds' ] ) && isValidDate( $_COOKIE[ 'ot4et1_ds' ] )  &&
		isset( $_COOKIE[ 'ot4et1_de' ] ) && isValidDate( $_COOKIE[ 'ot4et1_de' ] )
	) {
		$sds = Date2Int( $_COOKIE[ 'ot4et1_ds' ] );
		$sde = Date2Int( $_COOKIE[ 'ot4et1_de' ] );
		$sdsY = intval( date( "Y" , $sds ) );
		$sdsM = intval( date( "m" , $sds ) );
		$sdsD = date( "d" , $sds );
		$sdeY = intval( date( "Y" , $sde ) );
		$sdeM = intval( date( "m" , $sde ) );
		$sdeD = date( "d" , $sde );

	} else {
		$cy = intval( date( "Y" , time() ) );
		$cm = intval( date( "m" , time() ) );
		$sdsY = $cy ;
		$sdsM = $cm ;
		$sdsD = '01' ;
		$sdeY = $cy ;
		$sdeM = $cm ;
		$sdeD = '01' ;
	}


	echo "<br><br>
	<form method=\"post\" action=\"".$func."\">
		<table align=\"center\" class=\"ST\">
			<tr>
				<td class=\"r1\">
					с <input name=\"i_day_from\" type=\"text\" value=\"".$sdsD."\" class=\"day\">
					<select size=\"1\" name=\"i_month_from\" class=\"month\">" ;
						for ( $i = 1 ; $i <= 12 ; $i++ ) {
							echo "<option value=\"".$i."\"".( $sdsM == $i ? " selected" : "" ).">".inForm( $MonthNames[ $i - 1 ] , 2 )."</option>" ;
						}
   					echo "</select>
					<select size=\"1\" name=\"i_year_from\" class=\"year\">" ;
						for ( $i = intval( date( "Y" , time() ) ) ; $i >= 2008 ; $i-- ) {
							echo "<option value=\"".$i."\"".( $sdsY == $i ? " selected" : "" ).">".$i."</option>" ;
						}
					echo "</select> года
				</td>
			</tr>
			<tr>
				<td class=\"r2\">
					по <input name=\"i_day_to\" type=\"text\" value=\"".$sdeD."\" class=\"day\">
					<select size=\"1\" name=\"i_month_to\" class=\"month\">" ;
						for ( $i = 1 ; $i <= 12 ; $i++ ) {
							echo "<option value=\"".$i."\"".( $sdeM == $i ? " selected" : "" ).">".inForm( $MonthNames[ $i - 1 ] , 2 )."</option>" ;
						}
   					echo "</select>
					<select size=\"1\" name=\"i_year_to\" class=\"year\">" ;
						for ( $i = intval( date( "Y" , time() ) ) ; $i >= 2008 ; $i-- ) {
							echo "<option value=\"".$i."\"".( $sdeY == $i ? " selected" : "" ).">".$i."</option>" ;
						}
					echo "</select> года
   				</td>
   			</tr>
   			<tr>
				<td class=\"r3\">
					Фамилия
					".$WorkersList."
					".( !$allWorkers ? "<a href=\"?aw\">Все</a>" : "<a href=\"?\">Актуальные</a>" )."
				</td>
			</tr>
   			<tr>
				<td class=\"r4\">
					".$caseCategoryList."
				</td>
			</tr>
   			<tr>
				<td class=\"r4\">
					<input type=\"checkbox\" name=\"format\" value=\"xlsx\"> в формате XLSX <input type=\"submit\" value=\"Вывести на предварительный просмотр\">
				</td>
			</tr>
		</table>" ;
	closeHtml();
