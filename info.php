<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "core.php" );

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( "auth.php" );
	}

	$boss = NAMES_Format( NAMES_parse( $dbConfig[ "org.boss" ][ "name" ] ) , "%F1 %I1 %O1" ) ;
	$accountantGeneral = NAMES_Format( NAMES_parse( $dbConfig[ "org.accountantGeneral" ][ "name" ] ) , "%F1 %I1 %O1" ) ;

	MainHead_L2( "" , "" , array( "%UT/info.css" ) , array() , "hlp/index.html" , "" );

		echo "<table align=\"center\" class=\"info-tab\">
			<tr>
				<td colspan=\"2\" class=\"info-tab-title\">
					СВЕДЕНИЯ<br>
					".$dbConfig[ "org.name.full" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Полное наименование
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.name.full" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Полное наименование (англ.)
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.name.fullEnglish" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Сокращенное наименование
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.name.short" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Сокращенное наименование (англ.)
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.name.shortEnglish" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Адрес центрального офиса
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.address" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Начальник
				</td>
				<td class=\"info-tab-val\">
					".$boss."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Территориальные подразделения
				</td>
				<td class=\"info-tab-val\">" ;
					if( $dbConfig[ "org.branch.count" ] > 0 ) {
						$bc = $dbConfig[ "org.branch.count" ];
						for( $i = 1 ; $i <= $bc ; $i++ ) {
							if ( $i > 1 && $i < $bc ) {
								echo ", " ;
							} else
							if ( $i == $bc ) {
								echo " и " ;
							}
							$bp = "org.branch.".$i ;
							echo $dbConfig[ $bp.".name.short" ]." ( ".$dbConfig[ $bp.".address" ]." )" ;
						}
						echo " ".$dbConfig[ "org.branch.comment" ] ;
					} else {
						echo "Отсутствуют" ;
					}

				echo "</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					ИНН
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.inn" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					КПП
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.kpp" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					ОКПО
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.okpo" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					ОКВЭД
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.okved2" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					ОКТМО
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.oktmo" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					ОГРН
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.ogrn" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Банк
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.bank.name" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					К/счёт
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.bank.corrAccountNumber" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Р/С
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.beneficiary.accountNumber" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					БИК
				</td>
				<td class=\"info-tab-val\">
					".$dbConfig[ "org.bank.bic" ]."
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc itv-w1\">
					Назначение платежа
				</td>
				<td class=\"info-tab-val\">
					(00000000000000000130) За производство экспертиз
				</td>
			</tr>
		</table>" ;

		echo "<table align=\"center\" class=\"info-tab\">
			<tr>
				<td colspan=\"2\" class=\"info-tab-title\">
					Другие сведения
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-desc\">
					Электронная почта (e-mail)
				</td>
				<td class=\"info-tab-val\">
					voronezhskiy_rcse@minjust.ru<br>
					incoming@vrcse.ru<br>
				</td>
			</tr>
			<tr>
				<td rowspan=\"14\" class=\"info-tab-desc\">
					Skype
				</td>
				<td class=\"info-tab-val\">
					kansler_belg
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					vrcse.belgorod
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					vinokurovvrcse
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					vrcse.ignatova
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					sannikowa86
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					petelina25
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					vrcse.vasilenko
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					kansler_vrn
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					vrcse.golubkova_vrn
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					fedotova_vrcse
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					kia_vrcse
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					vrcse_tech
				</td>
			</tr>
			<tr>
				<td class=\"info-tab-val\">
					vrcse.alexeeva
				</td>
			</tr>
		</table>" ;

	$wivp = array(
		"fio" => array( "order" => "`t1`.`name`" , "group" => "noGroup" , "col" => array( "name" , "dep_s" , "cab" , "phone" , "extension" ) ) ,
		"dep" => array( "order" => "`t1`.`name`" , "group" => "dep" , "col" => array( "name" , "cab" , "phone" , "extension" ) ) ,
		"cab" => array( "order" => "`t2`.`building` , `t2`.`name` , `t1`.`name`" , "group" => "cab" , "col" => array( "name" , "dep_s" , "phone" , "extension" ) ) ,
		"phone" => array( "order" => "`t2`.`phone` , `t1`.`name`" , "group" => "phone" , "col" => array( "name" , "dep_s" , "cab" , "extension" ) ) ,
		"extension" => array( "order" => "`t2`.`extension` , `t1`.`name`" , "group" => "extension" , "col" => array( "name" , "dep_s" , "cab" , "phone" ) )
	);

	$wicn = array(
		"name" => array( "name" => "Ф.И.О. Сотрудника" , "lnk" => "fio" , "style" => "info-tab-val" ) ,
		"dep" => array( "name" => "Отдел" , "lnk" => "dep" , "style" => "info-tab-val" ) ,
		"dep_s" => array( "name" => "Отдел" , "lnk" => "dep" , "style" => "info-tab-val" ) ,
		"cab" => array( "name" => "Кабинет" , "lnk" => "cab" , "style" => "info-tab-val ta-c" ) ,
		"phone" => array( "name" => "Номер тел." , "lnk" => "phone" , "style" => "info-tab-val ta-c" ) ,
		"extension" => array( "name" => "Внутренний номер" , "lnk" => "extension" , "style" => "info-tab-val ta-c" )
	);

	$wivpi = "fio" ;
	if ( isset( $_REQUEST[ "wiv" ] ) ) {
		if ( isset( $wivp[ $_REQUEST[ "wiv" ] ] ) ) {
			$wivpi = $_REQUEST[ "wiv" ];
		}
	}

	$wivpOrder = $wivp[ $wivpi ][ "order" ];
	$wivpGroup = $wivp[ $wivpi ][ "group" ];
	$wivpCol = $wivp[ $wivpi ][ "col" ];

	$pt = $portalDB->query( "select
			`t1`.`name` ,
			`t3`.`name` as `dep` ,
			`t3`.`short_name` as `dep_s` ,
			`t2`.`phone` ,
			`t2`.`name` as `cab` ,
			IF( `t1`.`personal-number` is not null , CONCAT( `t2`.`extension` , ' , ' , `t1`.`personal-number` ) , `t2`.`extension` ) as `extension` ,
			\"Ф.И.О. Сотрудника\" as `noGroup`
		from
			`workers` as `t1` ,
			`cabinet` as `t2` ,
			`departments` as `t3`
		where
			( `t1`.`cab` = `t2`.`id` ) and
			( `t1`.`dep` = `t3`.`id` ) and
			( `t1`.`actual` = 1 )
		order by
			".$wivpOrder
	);


		$gr = array();

		foreach( $pt as $p ) {
			$p[ "name" ] = NAMES_Format( NAMES_parse( $p[ "name" ] ) , "%F1 %I1 %O1" );
			if ( !isset( $gr[ $p[ $wivpGroup ] ] ) ) {
				$gr[ $p[ $wivpGroup ] ] = array();
			}
			$gr[ $p[ $wivpGroup ] ][]= $p ;
		}

		//print_r_html( $gr );

		echo "<div id=\"phone-book\" class=\"info-label\">
			<ol>
				<li>для совершения звонков по городу, междугородних, а так же на номера сотовых телефонов в Воронежских и Белгородских подразделениях необходимо перед набором номера набрать цифру <span class=\"num-ext\">9</span>,а затем набрать номер абонента.<br>
				<span class=\"example\">Например:</span> <span class=\"num-ext\">9</span> 32-45-29 , <span class=\"num-ext\">9</span> <span class=\"num-lead\">8</span> ( 473 ) 237-71-38 , <span class=\"num-ext\">9</span> <span class=\"num-lead\">8</span> ( 920 ) 123-45-67</li>
				<li>для совершения звонков из Воронежских подразделений на внутренние номера Белгородских, нужно набрать внутренний номер согласно нижней таблицы.<br>
				<span class=\"example\">Например:</span> 403</li></li>
				<li>для совершения звонков из Белгородских подразделений на внутренние номера Воронежских, нужно набрать цифру <span class=\"num-ext\">5</span>, а затем внутренний номер согласно нижней таблицы.<br>
				<span class=\"example\">Например:</span> <span class=\"num-ext\">5</span> 310</li>
			</ol>
		</div>" ;

		echo "<table align=\"center\" class=\"info-tab\">
			<tr>" ;
			foreach( $wivpCol as $c ) {
				echo "<td class=\"info-tab-title\">
					<a href=\"?wiv=".$wicn[ $c ][ "lnk" ]."#phone-book\" class=\"worker-info-view-lnk\">".$wicn[ $c ][ "name" ]."</a>
				</td>" ;
			}
		echo "</tr>" ;

		ksort( $gr , SORT_LOCALE_STRING );

		/*if ( count( $gr ) > 1 ) {
			echo "<tr><td colspan=\"".count( $wivpCol )."\" class=\"info-tab-desc ta-l\">" ;
			foreach( $gr as $gi => $g ) {
				echo "<a onclick=\"showItem( '".base64_encode( $gi )."' )\" class=\"\">".$gi."</a>" ;
			}
			echo "</td></tr>" ;
		}*/

		foreach( $gr as $gi => $g ) {

			if ( count( $g ) > 0 ) {
				echo "<tr>
					<td id=\"".base64_encode( $gi )."\" colspan=\"".count( $wivpCol )."\" class=\"info-tab-desc ta-c\">
						".$gi."
					</td>
				</tr>" ;
				foreach( $g as $p ) {
					echo "<tr>" ;
					foreach( $wivpCol as $c ) {
						echo "<td class=\"".$wicn[ $c ][ "style" ]."\">".$p[ $c ]."</td>" ;
					}
					echo "</tr>" ;
				}
			}
		}

		echo "</table>" ;

	closeHtml();
?>