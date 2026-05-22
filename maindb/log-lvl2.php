<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	require_once( "lconfig.php" );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count($UserRights) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "EXTENTIONS" , $Rights ) ) {
			$maySEARCH = in_array( "SEARCH" , $Rights[ "EXTENTIONS" ] );
		} else {
			$maySEARCH = false ;
		}

		$GoOut = !$maySEARCH ;
	} else {
		$GoOut = true ;
	}

	$GoOut = false ;
	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit ;
	}

	MainHead_L2( "База - отчет" , "<a href=\"main.php\">База</a> - отчет" , array( "../%UT/buttons.css" , "../%UT/buttons.css" , "%UT/log-lvl2.css" ), array() , "hlp/main.html" );

	echo "<form method=\"post\" action=\"log-lvl2.print.php\">
		<table align=\"center\" class=\"main-tab\">
			<tr>
				<td class=\"label\" colspan=\"2\">
					год <select name=\"i_year\">" ;
						$tmp = intval( date( "Y" , time() ) );
						for( $i = $tmp ; $i >= 2008 ; $i-- ) {
							if ( $i == $tmp ) {
								echo "<option value=\"".$i."\" selected> ".$i." </option>" ;
							} else {
								echo "<option value=\"".$i."\"> ".$i." </option>" ;
							}
						}
					echo "</select>
				</td>
			</tr>" ;
			for ( $i = 0 ; $i < 3 ; $i++ ) {
				echo "<tr>
					<td class=\"label\">
						Номера дела
						<input name=\"i_num_".$i."\">
					</td>
					<td class=\"params\">
						<span><input name=\"i_ed_".( 6 * $i + 0 )."\" type=checkbox unchecked>Свединия о приостановлении<br>срокa производства экспертизы</span><br>
						<span><input name=\"i_ed_".( 6 * $i + 1 )."\" type=checkbox unchecked>Перечень передаваемых предметов</span><br>
						<span><input name=\"i_ed_".( 6 * $i + 2 )."\" type=checkbox unchecked>Куда и кому переданы</span><br>
						<span><input name=\"i_ed_".( 6 * $i + 3 )."\" type=checkbox unchecked>Дата окончания экспертизы</span><br>
						<span><input name=\"i_ed_".( 6 * $i + 4 )."\" type=checkbox unchecked>Дата передачи в подразделение</span><br>
						<span><input name=\"i_ed_".( 6 * $i + 5 )."\" type=checkbox unchecked>Виды исследований, количество и характер выводов, срок производства экспертизы</span>
					</td>
				</tr>" ;
			}

		echo "<tr>
			<td class=\"btn-area\" colspan=\"2\">
				<input type=\"submit\" value=\"Вывести на предварительный просмотр\">
			</td>
		</tr>
	</table>" ;
?>