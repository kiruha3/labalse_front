<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	require_once( "core.php" );

	if ( isset( $_REQUEST[ "reauth" ] ) ) {
	} else
	if ( isset( $_REQUEST[ "doAuthPost" ] ) ) {
		TryLoginFromPost();
		if ( $LoginOk ) {
			Redirect( "index.php" );
		}
	} else {
		if ( !isset( $_REQUEST[ "noad" ] ) && isset( $dbConfig[ "engine.auth.kerb" ] ) && $dbConfig[ "engine.auth.kerb" ] == 1 ) {
			Redirect( "auth-kerb.php" );
		}
	}

	MainHead_L1( "Авторизация" , array() );
	echo "<br><br><br>
	<p align=center>
		<font color=\"#ff0000\">
			".( isset( $locale[ $Err ] ) ? $locale[ $Err ] : "" )."
		</font>
	</p>
	<form action=\"auth.php\" method=\"post\" enctype=\"multipart/form-data\">
		<table align=center>
			<tr>
				<td>
					Имя учетной записи
				</td>
				<td>
					<input type=text name=\"uLogin\" value=\"\" style=\"width: 200px\">
				</td>
			</tr>
			<tr>
				<td>
					Пароль учетной записи
				</td>
				<td>
					<input type=password name=\"uPassword\" value=\"\" style=\"width: 200px\">
				</td>
			</tr>
			<tr>
				<td align=center colspan=2>
					<input type=\"submit\" name=\"doAuthPost\" value=\"Авторизоваться\" style=\"width: 200px\">
				</td>
			</tr>
		</table>
	</FORM>";
	closeHtml();
?>