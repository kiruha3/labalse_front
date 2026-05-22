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

	MainHead_L2( "" , "" , array( "%UT/tech-info.css" ) , array() , "hlp/index.html" , "" );

	echo "<table align=\"center\" class=\"doc-table\">
		<tr>
			<td>
				<div class=\"p-text\">
					Сеть Воронежского РЦСЭ соедржит: рабочие компьютеры, серверы, сетевые принтеры и коммутаторы/маршрутизаторы.
				</div>
				<div class=\"p-text\">
					Поддерживается сетевой протокол <span class=\"d-blue\">IPv4</span>,
					сеть <span class=\"d-blue\">10.0.0.0</span> с маской подсети <span class=\"d-blue\">255.0.0.0</span> ( сеть <span class=\"d-blue\">10.0.0.0/8</span> или <span class=\"d-blue\">10.0.0.0</span> - <span class=\"d-blue\">10.255.255.255</span> ),
					адреса выдаются автоматически, привязка к портам. Домен организации <span class=\"d-blue\">vrcse.local</span>
				</div>
				<div class=\"p-text\">
					Рабочие компьютеры (РК) являются основными конечными потребителями интернет-трафика.
					Получают адреса из диапазона <span class=\"d-blue\">10.1.0.0</span> - <span class=\"d-blue\">10.1.0.255</span>
				</div>
				<div class=\"p-text\">
					Серверы являются неосновными потребителями интернет-трафика, управляют работой РК, обеспечивают функционирование сети.
					Получают адреса из диапазона <span class=\"d-blue\">10.0.0.1</span> - <span class=\"d-blue\">10.0.0.255</span>.
					<ul>Некоторые серверы организации:
						<li>DHCP - <span class=\"d-blue\">10.0.0.1</span></li>
						<li>DNS - <span class=\"d-blue\">10.0.0.1</span> , <span class=\"d-blue\">10.0.0.2</span> , <span class=\"d-blue\">10.0.0.3</span></li>
						<li>Контроллеры домена - <span class=\"d-blue\">10.0.0.1</span> , <span class=\"d-blue\">10.0.0.2</span> , <span class=\"d-blue\">10.0.0.3</span></li>
						<li>Интернет-шлюз - <span class=\"d-blue\">10.0.0.100</span></li>
						<li>Сервер обновлений Microsoft <span class=\"d-bold-name\">WSUS 3.0</span> - <span class=\"d-blue\">http://server--wsus-1.vrcse.local/</span></li>
						<li>Сервер ИБ <span class=\"d-bold-name\">Консультант +</span> - <span class=\"d-blue\">prog--consultant-plus.vrcse.local</span> ( <a href=\"file://prog--consultant-plus.vrcse.local/Consultant/cons.exe\" class=\"d-lnk\">ссылка</a> )</li>
						<li>Сервер ИБ <span class=\"d-bold-name\">Строй Консультант</span> - <span class=\"d-blue\">prog--stroy-consultant.vrcse.local</span> ( <a href=\"file://prog--stroy-consultant.vrcse.local/StroyConsultant/Distr/SC disk 4 (июнь)/setup.exe\" class=\"d-lnk\">клиент</a> )</li>
						<li>Сервер ИБ <span class=\"d-bold-name\">Тех Эксперт</span> - <span class=\"d-blue\">prog--texpert.vrcse.local</span> ( <a href=\"file://prog--texpert.vrcse.local/ТехЭксперт/Texpert-Server/Client/texclient.lnk\" class=\"d-lnk\">ярлык</a> )</li>
						<li>Сервер времени - <span class=\"d-blue\">server--time-1.vrcse.local</span></li>
						<li>Обменники - <a href=\"file://10.0.0.254/Документы/Обменная/\" class=\"d-lnk\">раз</a> и <a href=\"file://server--share.vrcse.local/Обменник\" class=\"d-lnk\">два</a></li>
						<li><a href=\"files/iptv-freedom-http-proxy.m3u\" class=\"d-lnk\">IP TV</a></li>
					</ul>
				</div>
				<div class=\"p-text\">
					Сетевые принтеры распологаются как в кабинетах так и в коридорах этажей.
					Адреса принтеры получают из диапазона <span class=\"d-blue\">10.0.2.0</span> - <span class=\"d-blue\">10.0.2.255</span>.<br>
					<ul>Общие сетевые принтеры <span class=\"d-bold-name\">Konica Minolta bizhub c353p</span> ( <a href=\"files/KonicaMinolta.zip\" class=\"d-lnk\">драйвер</a> ) :
					<li>1-й этаж - <span class=\"d-blue\">konica-minolta-1.prn.local</span></li>
					<li>2-й этаж - <span class=\"d-blue\">konica-minolta-2.prn.local</span></li>
					<li>3-й этаж - <span class=\"d-blue\">konica-minolta-3.prn.local</span></li>
					</ul>
					<ul>Другие сетевые принтеры :
					<li>кабинет <span class=\"d-bold-name\">104</span> - <span class=\"d-blue\">cab-104.prn.local</span></li>
					<li>кабинет <span class=\"d-bold-name\">212</span> - <span class=\"d-blue\">cab-212.prn.local</span></li>
					<li>кабинет <span class=\"d-bold-name\">303</span> - <span class=\"d-blue\">cab-303--m401dn.prn.local</span></li>
					<li>кабинет <span class=\"d-bold-name\">306</span> - <span class=\"d-blue\">secretar-print-server.prn.local</span></li>
					<li>кабинет <span class=\"d-bold-name\">310</span> - <span class=\"d-blue\">gagarin-print-server.prn.local</span></li>
					</ul>
				</div>
			</td>
		</tr>
	</table>" ;

	closeHtml();
?>