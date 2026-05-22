<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	require_once( "core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserID
	 * @var $dbConfig
	 */
	require_once( "maindb/gp-info.php" );

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( "auth.php" );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

		$mayRegisterCorrespondence = array_key_exists( "REGISTER-CORRESPONDENCE" , $Rights );
		$mayCorpMail = array_key_exists( "CORP-MAIL" , $Rights );
		$mayOrdersMat = array_key_exists( "ORDERS-MAT" , $Rights );
		$mayEquipment = array_key_exists( "EQUIPMENT" , $Rights );
	} else {
		$mayRegisterCorrespondence = $mayCorpMail = $mayOrdersMat = $mayEquipment = false ;
	}
	
	$mainDBRights = getRights( 3 );
	if ( array_key_exists( "GP-INDICATOR" , $mainDBRights ) ) {
		$mayGPIndicator = in_array( "SHOW-GP-INDICATOR" , $mainDBRights[ "GP-INDICATOR" ] );
	} else {
		$mayGPIndicator = false ;
	}
	
	$showGPIndicator = false ;
	switch ( $dbConfig[ 'gp-indicator-mode' ] ) {
		case 'show-all' :
			$showGPIndicator = true ;
			break ;
		
		case 'show-rights' :
			$showGPIndicator = $mayGPIndicator ;
			break ;
	}
	
	MainHead_L2( '' , '' , array(  '../../%UT/buttons.css' , '%UT/index.css' ) , array( '/ext-lib/pdf.js/build/pdf.js' , '/ext-lib/pdf.js/build/pdf.worker.js' , 'files/index.js' ) , 'hlp/index.html' , '' );

	$tabPlaces = $portalDB->query( "select `t2`.`name`, `t2`.`description`, `t2`.`sub_dir`, `t2`.`start_file`, `t1`.`rights` from `access-rights` as `t1`, `places` as `t2` where ( ( `t1`.`place`=`t2`.`id` ) and ( `t1`.`user_id` = ? ) and ( `t2`.`id` > 0 ) )" , false , "i" , $UserID );
	$tabNews = $portalDB->query( "select `t1`.`id`, `t1`.`date`, `t1`.`theme`, `t4`.`name` as `news_category`, `t2`.`name` as `author`, `t3`.`name` as `post`, `t1`.`content` from `news` as `t1`, `workers` as `t2`, `posts` as `t3`, `news-categories` as `t4` where ( `t1`.`date` is not null ) and ( `t2`.`id` = `t1`.`author` ) and ( `t3`.`id` = `t2`.`post_1_id` ) and ( `t4`.`id` = `t1`.`category` ) and ( `t1`.`actual` = 1 ) order by `t1`.`id` desc limit 5" );

	foreach( $tabNews as &$i ) {
		$i[ "date" ] = date( "d-m-Y" , strtotime( $i[ "date" ] ) );
		$i[ "content" ] = parseText( $i[ "content" ] );
		$i[ "author" ] = NAMES_Format( NAMES_parse( $i[ "author" ] ) , "%F1 %i.%o." );
		$i[ "fresh" ] = "" ;
	}
	if ( count( $tabNews ) > 0 ) {
		$tabNews[ 0 ][ "fresh" ] = "-fresh" ;
	}

	include_once 'file_store/integration.php' ;
	$opt = array(
		"header" => 0 ,
		"show-path-at-top" => 0 ,
		"may-select" => 0 ,
		"show-icons" => 1 ,
		"file-name-style" => "document-name-lnk" ,
		"important-files-mark" => "<div class=\"important-file\"></div>" ,
		"open-in" => "_blank"
	);
	$viewStyle = array(
		"style" => "list" ,
		"order" => "date" ,
		"param" => "asc"
	);
	$tableStyle = array(
		"t" => "document-table"
	);

	$optL = array(
		"show-icons" => 0 ,
		"name-preprocess" => function( $n ) {
			switch ( $n ) {
				case "Яндекс" :
					return "<font color=#ff0000>Я</font><font color=#000000>ndex</font>" ;
					break ;
				case "Google" :
					return "<font color=#0000ff>G</font><font color=#ff0000>o</font><font color=#ffc000>o</font><font color=#0000ff>g</font><font color=#008000>l</font><font color=#ff0000>e</font>" ;
					break ;
				case "Рамблер" :
					return "<font color=#0080ff>Rambler</font>" ;
					break ;
				case "@mail.ru" :
					return "<font color=#ff8000>@</font><font color=#0000ff>mail</font><font color=#ff8000>.ru</font>" ;
					break ;
				case "YAHOO!" :
					return "<font color=#e00000>YAHoO!</font>" ;
					break ;
				case "Минюст России - Официальный сайт" :
					return "<font color=#00a000>Минюст России</font>" ;
					break ;
				case "РФЦСЭ - Официальный сайт" :
					return "<font color=#0040ff>РФЦСЭ</font>" ;
					break ;
				default :
					return $n ;
					break ;
			}
		}
	);

	echo "
	<table id=\"main-table\">
		<tr>
			<td id=\"left-column\">
				<table id=\"sections\">
					<tr>
						<td class=\"panel-header\">
							&raquo; Разделы
						</td>
					</tr>
					<tr>
						<td class=\"panel-content\">
							" , Array2Str( $tabPlaces , "<div class=\"section-name\"><a href=\"\$sub_dir/\$start_file\">\$name</a></div><div class=\"section-desc\">\$description</div>" , array( "sub_dir" , "start_file" , "name" , "description" ) ) , "
						</td>
					</tr>
				</table>
				<br>
				<table id=\"tools\">
					<tr>
						<td class=\"panel-header\">
							&raquo; Инструменты
						</td>
					</tr>
					<tr>
						<td class=\"panel-content\">
							<div class=\"tools-buttons\">
								".( $mayRegisterCorrespondence ? "<a target=\"_blank\" href=\"register-correspondence.php\">Реестр корреспонденции</a><br>" : "" )."
								".( $mayCorpMail ? "<a target=\"_blank\" href=\"https://mail.vrcse.ru\">почта<font color=\"#e08000\">@</font>vrcse.ru</a><br>" : "" )."
								<!-- ".( $mayOrdersMat ? "<a target=\"_blank\" href=\"orders-mat.list.php\">Заказы - МТО</a><br>" : "" )." -->
								".( $mayOrdersMat ? "<a target=\"_blank\" href=\"https://forum.vrcse.ru/viewforum.php?f=3\">Заказы - МТО</a><br>" : "" )."

								".( $mayEquipment ? "<a target=\"_blank\" href=\"equipment.list.php\">Оборудование</a><br>" : "" )."
							</div>
						</td>
					</tr>
				</table>
				<br>
				<table id=\"searchers\">
					<tr>
						<td class=\"panel-header\">
							&raquo; Ссылки
						</td>
					</tr>
					<tr>
						<td class=\"panel-content\">
							<div class=\"searchers-links\">
								".( isset( $dbConfig[ "main.leftPanel.links.lnk.id" ] ) ? integrate( $dbConfig[ "main.leftPanel.links.lnk.id" ] , array_merge( $opt , $optL ) , $viewStyle , $tableStyle ) : "" )."
							</div>
						</td>
					</tr>
				</table>
			</td>
			<td id=\"middle-column\">
				<table id=\"news\">
					".( $UserID == 10000000 ? "<tr><td><video controls=\"controls\"><source src=\"rtsp://10.1.0.71:8554/video.sdp\" /></video></td></tr>" : "" )."
					<tr>
						<td class=\"panel-header\">
							&raquo; Поиск по порталу
						</td>
					</tr>
					<tr>
						<td id=\"news-contents\">
							<div id=\"search-frame-reserver\"></div>
						</td>
					</tr>
					<tr class=\"panel-spacer\"><td></td></tr>
					".( $showGPIndicator ? "<tr>
						<td class=\"panel-header\">
							&raquo; Выполнение государственного задания
						</td>
					</tr>
					<tr>
						<td id=\"news-contents\">
							".gpInfoMain( false, null , null , $dbConfig[ 'gp-indicator-inc-sndz' ] == 1 )."
							<center><a href=\"/maindb/gp-info.page.php\" class=\"btn1\">Подробнее</a></center>
						</td>
					</tr>
					<tr class=\"panel-spacer\"><td></td></tr>" : '' )."
					<tr>
						<td class=\"panel-header\">
							&raquo; Новости портала
						</td>
					</tr>
					<tr>
						<td id=\"news-contents\">
							" , Array2Str( $tabNews , "<div class=\"news2\$fresh\"><div class=\"news-title\$fresh\">\$date - \$theme</div><div class=\"news-content\$fresh\"><div class=\"news-content-text\$fresh\">\$content</div><div class=\"news-content-footer\$fresh\">\$post \$author</div></div></div>" , array( "date" , "theme" , "content" , "post" , "author" , "fresh" ) ) , "
						</td>
					</tr>
				</table>
			</td>
			<td id=\"right-column\">
				<table id=\"documents\">
					<tr>
						<td class=\"panel-header\">
							&raquo; Документы и бланки
						</td>
					</tr>
					<tr>
						<td class=\"panel-content\">
							".( isset( $dbConfig[ "main.rightPanel.docs.lnk.id" ] ) ? integrate( $dbConfig[ "main.rightPanel.docs.lnk.id" ] , $opt , $viewStyle , $tableStyle ) : "" )."
						</td>
					</tr>
				</table>
				<br>
				<table id=\"tech-info\">
					<tr>
						<td class=\"panel-header\">
							&raquo; Технический раздел
						</td>
					</tr>
					<tr>
						<td class=\"panel-content\">
							".( isset( $dbConfig[ "main.rightPanel.tech.lnk.id" ] ) ? integrate( $dbConfig[ "main.rightPanel.tech.lnk.id" ] , $opt , $viewStyle , $tableStyle ) : "" )."
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>";


	closeHtml();
?>