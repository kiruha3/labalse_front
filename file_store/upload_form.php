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
		$Rights = ParseRights( strtolower( $UserRights[ 0 ] ) );
		if ( array_key_exists( "group" , $Rights ) ) {
			$groups = explode( "," , trim( $Rights["group"][ 0 ] ) );
			$GoOut = count( $groups ) < 1 ;
		} else {
			$groups = array();
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
		exit ;
	}

	if ( isset( $_REQUEST[ "id" ] ) ) {
		$cnid = intval( $_REQUEST[ "id" ] );
	} else {
		$cnid = 0 ;
	}

	if ( isset( $_REQUEST[ "req" ] ) ) {
		$reqID = $_REQUEST[ "req" ];
	} else {
		$reqID = false ;
	}

	$inFrame = isset( $_REQUEST[ "frame" ] );

	$cl = $portalDB->simpleRow( "dir" , $cnid );

	if ( $cl === false ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm( "Заданного каталога не существует." );
		closeHtml();
		mysql_close( $con );
		exit();
	}

	if ( in_array( strtolower( $cl[ "group" ] ) , $groups ) ) {
		$access_mask = $cl[ "group_access" ] ;
	} else {
		$access_mask = $cl[ "others_access" ] ;
	}

	if ( preg_match( "/c/" , $access_mask ) != 1 ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm( "У вас нет прав для размещения файлов в этом каталоге." );
		closeHtml();
		exit();
	}

	if ( $inFrame ) {
		MainHead_Frame( array( "../%UT/buttons.css" , "%UT/upload.css" ) , array( "files/main.js" ) , "" );
	} else {
		MainHead_L2( "Файловое хранилище" , "<a href=\"main.php?id=".$cl[ "id" ]."\">Файловое хранилище</a> - загрузка файла" , array( "../%UT/buttons.css" , "%UT/upload.css" ) , array( "files/main.js" ) , "hlp/tree_view.html" , "" );
	}

	echo "<form action=\"upload.php?id=".$cl[ "id" ].( $reqID !== false ? "&req=".$reqID : "" ).( $inFrame ? "&frame" : "" )."\" method=\"post\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"8589934592\"><br>
		<table align=\"center\" class=\"main-tab\">
			<tr>
				<td>
					<input name=\"uf\" type=\"file\" class=\"mt-f\">
				</td>
			</tr>
			<tr>
				<td>
					описание:<br><textarea name=\"ufd\" class=\"mt-desc\"></textarea><br>
				</td>
			</tr>
			<tr>
				<td>
					<input type=\"checkbox\" name=\"ufi\" value=\"ufi\">Пометить как важное
				</td>
			</tr>
			<tr>
				<td>
					<input type=\"checkbox\" name=\"ufs\" value=\"ufs\">Добавить уведомление <input name=\"ufsd\" id=\"testInput\" onkeypress=\"return $.calendarDlg.show( event , 'testInput' );\" class=\"mt-sd\" placeholder=\"Дата\"><br>
					<textarea name=\"ufst\" class=\"mt-st\"></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<center><input type=\"submit\" value=\"загрузить файл\" class=\"btn3\"></center>
				</td>
			</tr>
		</table>
	</form>" ;

	if ( $inFrame ) {
		closeHtml_Frame();
	} else {
		CloseHtml();
	}
?>