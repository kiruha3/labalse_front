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

	$modeAjax = isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ;

	if ( isset( $_REQUEST[ "tmpl" ] ) ) {
		$tmplID = intval( $_REQUEST[ "tmpl" ] );
	} else {
		exit();
	}

	if ( !isset( $_REQUEST[ "id" ] ) ) {
		exit();
	} else {
		$expertize_id = intval( $_REQUEST[ "id" ] );
	}

	require_once( "request.core.php" );

	$tabDepartments = array();
	$tabWorkers = array();
	$tabPosts = array();
	$tabSpecGroups = array();
	$tabCaseCategory = array();
	$tabTypeOfAgency = array();

	$tmplData = $portalDB->row( "select * from `doc-templates` where `id` = ?" , "i" , $tmplID );
	if ( $tmplData === false ) {
		exit();
	}
	$tmplExtVar = json_decode( iconv( "cp1251" , "utf8" , $tmplData[ "ext-var" ] ) , true );
	$docVar = loadVariables( $tmplData , $expertize_id );

	if ( $docVar === false ) {
		exit();
	}

	$docVarVal = $docVar ;
	foreach ( $docVarVal as &$dvv ) {
		$dvv = $dvv[ "value" ];
	} unset( $dvv );

	$tmplDoc = new TDocTemplate( $tmplData[ "tmpl" ] );

	if ( $modeAjax ) {
		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ "data" ] );

		$data = $DD->documentElement ;
		switch ( $data->nodeName ) {
			case "save" :
				$tid = $data->getAttribute( "id" );
				$portalDB->noResult( "insert into `doc-templates` ( `tmpl` ) values ( ? )" , "s" , iconv( "utf8" , "cp1251" , $data->nodeValue ) );
				break ;
			case "docVar" :
				treeConvertEncoding( $docVar );
				echo "<result>".toCDATA( json_encode( $docVar ) )."</result>" ;
				break ;
		}

		exit();
	}

	if ( $tmplDoc->prependText !== false ) {
		$tmplPTt = readTextHTML( $tmplDoc->prependText , $expertize_id );
	} else {
		$tmplPTt = "<p></p>" ;
	}

	if ( $tmplDoc->title !== false ) {
		$tmplTt = readTextHTML( $tmplDoc->title , $expertize_id );
	} else {
		$tmplTt = "" ;
	}

	if ( $tmplDoc->mainText !== false ) {
		$tmplMTt = readTextHTML( $tmplDoc->mainText , $expertize_id );
	} else {
		$tmplMTt = "<p></p>" ;
	}

	MainHead_Print( "" , array( "../%UT/buttons.css" , "%UT/request.css" ) , array( "inc" => array( "files/request.js" ) , "init" => "var expertizeID = ".$expertize_id." ; var docTemplateID = ".$tmplID." ;" ) );

	$onhbr = json_decode( ( $dbConfigFull[ "org.name.full.head" ][ "e-data" ] ) )->br ;
	$onnbr = json_decode( ( $dbConfigFull[ "org.name.full.name" ][ "e-data" ] ) )->br ;
	$pageBarCode = $docVarVal[ "page-code" ];


		echo "<div id=\"page-area\" class=\"page-paper-size-A4\"><div id=\"data-area\">
			<div class=\"tgt-displayable\" title=\"Шапка с гербом\"><div id=\"page-head\">
				<div id=\"head-left\">
					<img src=\"themes/".$UserThemeLoc."/nationalEmblem.png\" class=\"nationalEmblem\"><br>
					<div class=\"org-name\">
					".breakLineByRule( inForm( $dbConfig[ "org.name.full.head" ] , 1 ) , $onhbr )."<br>
					<br>
					".inForm( $dbConfig[ "org.name.full.type" ] , 1 )."<br>
					".breakLineByRule( inForm( $dbConfig[ "org.name.full.name" ] , 1 ) , $onnbr )."<br>
					</div>
					".$dbConfig[ "org.address" ]."<br>
					Тел.: 8 ( ".$dbConfig[ "org.phone.code" ]." ) ".$dbConfig[ "org.phone" ]."<br>
					Факс: 8 ( ".$dbConfig[ "org.phone.code" ]." ) ".$dbConfig[ "org.fax" ]."<br>
					e-mail: <a class=\"e-mail\" href=\"mailto:".$dbConfig[ "org.email" ]."\">".$dbConfig[ "org.email" ]."</a><br>
					№ ".$docVarVal[ "exp-number-full" ]." от ".date( "d.m.Y" , time() )." г.
				</div>
				<div id=\"head-right\">
					<div class=\"barcode\"><img id=\"barcode\" src=\"../barcode.php?src=".$pageBarCode."\"><br><span class=\"barcode-text\">".$pageBarCode."</span></div>
					<div class=\"clear\"></div>
					<div class=\"addressee\">
						".$docVarVal[ "agency-address" ]."<br>
						<br>
						".inForm( $docVarVal[ "agency" ] )."<br>
						<br>".inForm( $docVarVal[ "agent" ] )."
					</div>
				</div>
				<div class=\"clear\"></div>
			</div></div>
			<div class=\"tgt-displayable\" title=\"Препровождение\"><div><div id=\"prepend-text\" contenteditable=\"true\">".$tmplPTt."</div><div id=\"boss-signature\">".$tabPosts[ $dbConfig[ "org.boss" ][ "post_1_id" ] ][ "short_name" ]." ".$dbConfig[ "org.name.short" ]." ________ ".NAMES_Format( NAMES_parse( $dbConfig[ "org.boss" ][ "name" ] ) , "%i.%o. %F1" )."</div></div></div>
			<div class=\"tgt-displayable\" title=\"Заголовок\"><div id=\"title\" contenteditable=\"true\">".$tmplTt."</div></div>
			<div id=\"main-text\" class=\"main-text\" contenteditable=\"true\">".$tmplMTt."</div>
			<div class=\"signature\">".$docVarVal[ "expert-post-simple" ]." ___________ ".inForm( $docVarVal[ "expert-name1" ] ).", тел.: ".$docVarVal[ "expert-phone" ]."</div>
		</div></div>" ;

	function prepDocVarDesc( $n ) {
		$m = array();
		$res = explode( ">" , $n );
		$i = 0 ;
		foreach ( $res as &$c ) {
			$c = "<span class=\"var-desc-el\" style=\"margin-left : ".( $i++ * 16 )."px ;\">".trim( $c )."</span>" ;
		} unset( $c );
		return implode( $res ) ;
	}

	echo "<div class=\"ctrl-area\">
		<div>
			Размер странцы ".$tmplDoc->pageFormat."<br>
			<a href=\"request.edit.php?tmpl=".$tmplID."&id=".( $expertize_id - 1 )."\">&lt;&lt;</a> <a href=\"main.php?idlist=".$docVarVal[ "matincoming-id" ]."\" target=\"_blank\">".$docVarVal[ "exp-number-full" ]."</a> <a href=\"request.edit.php?tmpl=".$tmplID."&id=".( $expertize_id + 1 )."\">&gt;&gt;</a>
		</div>
		<div id=\"tgt-displayable-ia\"></div>
		<div style=\"display : none\">
			<div>
				Дата и время осмотра<br>
				<input id=\"tgt-date-i\" type=\"text\" value=\"00-".date( "m-Y" , time() )."\" maxlength=\"10\"><input id=\"tgt-time-i\" type=\"text\" value=\"10:00\" maxlength=\"5\">
			</div>
			<div>
				Предмет осмотра<br>
				<textarea id=\"tgt-object-i\">Предмет осмотра</textarea>
			</div>
		</div>" ;

		echo "<div class=\"var-area\"><table>" ;
		foreach ( $docVar as $dvk => $dvd ) {
			$varLnkT = ( $dvd[ "mf" ] ? inForm( $dvd[ "value" ] , 1 ) : $dvd[ "value" ] );
			echo "<tr><td>".prepDocVarDesc( $dvd[ "desc" ] )."</td><td><a unselectable=\"on\" onselectstart=\"return false;\" onmousedown=\"return false;\"  class=\"var-lnk\" onclick=\"doPasteVariable( '".$dvk."' )\"><div class=\"var-lnk-icon\"></div>".$varLnkT."</a></td></tr>" ;
		}
		echo "</table></div>" ;

		echo "<div>
			<a onclick=\"doGetRTF(".$tmplID.",".$expertize_id.")\" class=\"btn3\" target=\"_blank\">скачать</a>
			<a unselectable=\"on\" onselectstart=\"return false;\" onmousedown=\"return false;\"  class=\"btn3\" onclick=\"doSave( ".$tmplID." )\">Сохранить</a>
		</div>
	</div>" ;


	echo "<div>
	</div>" ;

	closeHtml_Print();
?>