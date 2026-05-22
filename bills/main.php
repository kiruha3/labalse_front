<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once("../core.php");
	require_once "lconfig.php";

	TryLoginFromCookie($PlaceID);
	if ( !$LoginOk ) {
		Redirect("../auth.php");
	}

	if ( count($UserRights) != 1 ) {
		MainHead_L2("", "", array("../%UT/buttons.css", "../%UT/forms.css"), array(), "");
		echo "<br><br><br><br><br>";
		MessageForm();
		closeHtml();
		exit;
	}

	$Rights= ParseRights(strtoupper($UserRights[0]));
	if ( array_key_exists("VIEW_BASE", $Rights) ) {
		$mayVIEW_SD = in_array("VIEW_SD", $Rights["VIEW_BASE"]);
		$mayVIEW_OD = in_array("VIEW_OD", $Rights["VIEW_BASE"]);
	} else {
		$mayVIEW_SD = $mayVIEW_OD = false;
	}

	if ( array_key_exists("BILL", $Rights) ) {
		$mayINVOICE = in_array("INVOICE", $Rights["BILL"]);
	} else {
		$mayINVOICE = false;
	}

	MainHead_L2(
		"Выписка счетов",
		"Выписка счетов",
		array("../%UT/buttons.css", "%UT/main.css"),
		array(),
		"hlp/main.html"
	);

	echo "<br><br><br>
	<table align=center class=\"tt\">
		<tr>";
			if ( $mayINVOICE ) {
				echo "<td class=\"t\">
					<table class=\"ctt\">
						<tr>
							<td class=\"th\">&raquo; <a href=\"bill.php?invoice\" class=\"ctl\">Выписать счет</a></td>
						</tr>
						<tr>
							<td class=\"td\">
								Выписать счет
							</td>
						</tr>
					</table>
				</td>";
			}

			if ( $mayVIEW_SD || $mayVIEW_OD ) {
				echo "<td class=\"t\">
					<table class=\"ctt\">
						<tr>
							<td class=\"th\">&raquo; <a href=\"list.php\" class=\"ctl\">База счетов</a></td>
						</tr>
						<tr>
							<td class=\"td\">
								База счетов
							</td>
						</tr>
					</table>
				</td>";
			}
		echo "</tr>
	</table>";

	closeHtml();
?>