<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	/**
	 * @var $dbConfigFull
	 * @var $LoginOk
	 * @var TDB $portalDB
	 * @var $UserThemeLoc
	 * @var $dbConfig
	 * @var $UserID
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */

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

	/*if ( !isset( $_REQUEST[ "type" ] ) ) {
		exit();
	} else {
		$doc_type = $_REQUEST[ "type" ];
	}*/

	if ( !isset( $_REQUEST[ "id" ] ) ) {
		exit();
	} else {
		$doc_id = intval( $_REQUEST[ "id" ] );
	}

	require_once( "request.core.php" );
	require_once( "../cores/data-bank.php" );

	/*$tabDepartments = array();
	$tabWorkers = array();
	$tabPosts = array();
	$tabSpecGroups = array();
	$tabCaseCategory = array();
	$tabTypeOfAgency = array();*/

	$tmplData = $portalDB->row( "select * from `doc-templates` where `id` = ?" , "i" , $tmplID );
	//$ddd = fillDataBank( $tmplData , $doc_type ,  $doc_id );
	//print_r_html( $ddd );
	$docVar = fillDataBank2(
		array(
			'req:id' => $doc_id ,
			'tmpl-data' => $tmplData ,
			'tmpl-list-name' => 'expertize' ,
			//'post-init' => loadVariables2_post_init_tmpl
		)
	);
	//print_r_html( $docVar );
	$allTmplData = $portalDB->table( 'doc-templates' );
	foreach( $allTmplData as $ctd ) {
		checkFilter( $ctd[ 'filter_rules' ] , $ddd );
	}
	//print_r_html( $ddd );
	//exit();
	$exClasses = array();
	$listsData = array();

	if ( $docVar === false ) {
		exit();
	}

	$docVarVal = $docVar ;
	foreach ( $docVarVal as &$dvv ) {
		$dvv = $dvv[ "value" ];
	} unset( $dvv );

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

	$tmplDoc = new TDocTemplate( $tmplData[ "tmpl" ] );
	//var_dump_html( $tmplDoc );

	MainHead_Print( "" , array( "../%UT/buttons.css" , "%UT/request.css" ) , array( "inc" => array( "files/request.test1.js" ) , "init" => "var docType = \"".$doc_type."\" ; var docID = ".$doc_id." ; var docTemplateID = ".$tmplID." ;" ) );

	echo '<input type="checkbox" id="doc-vis"><div id="document-area" class="document-area"><div id="page-area" class="page-area page-paper-size-A4"><div id="data-area-wrapper" class="data-area-wrapper"><div id="data-area" class="data-area">' ;


	$tod = $tmplDoc->origDOM ;

	foreach( $tod->childNodes as $ccn ) {
		//var_dump_html( $ccn );
		if ( $ccn->nodeType != XML_TEXT_NODE ) {
			if ( $ccn->nodeName == "header" ) {
				$onhbr = json_decode( ( $dbConfigFull[ "org.name.full.head" ][ "e-data" ] ) )->br ;
				$onnbr = json_decode( ( $dbConfigFull[ "org.name.full.name" ][ "e-data" ] ) )->br ;
				$pageBarCodeType = $docVarVal[ 'cfg:docs-barcode-type' ];
				$pageBarCode = $docVarVal[ "page-code" ];
				echo "<div class=\"tgt-displayable\" title=\"Шапка с гербом\"><div id=\"page-head\">
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
						Факс: 8 ( ".$dbConfig[ "org.phone.code" ]." ) ".$dbConfig[ "org.fax" ].'<br>
						e-mail: <a class="e-mail" href="mailto:'.$dbConfig[ 'org.email' ].'">'.$dbConfig[ 'org.email' ].'</a><br>
						№ '.$docVarVal[ 'exp-number-full' ].' от '.date( 'd.m.Y' , time() ).' г.
					</div>
					<div id="head-right">
						<div class="barcode"><img id="barcode" data-type="'.$pageBarCodeType.'" src="../barcode.php?type='.$pageBarCodeType.'&src='.$pageBarCode.'"><br><span class="barcode-text">'.$pageBarCode.'</span></div>
						<div class="clear"></div>
						<div class="addressee">
							'.inForm( $docVarVal[ 'agency' ] ).'<br>
							<br>'.inForm( $docVarVal[ 'agent' ] , 1 ).'<br>
							<br>'.$docVarVal[ 'agency-address' ].'<br>
							<br>дело № '.$docVarVal[ 'case-num' ].'
						</div>
					</div>
					<div class="clear"></div>
				</div></div>' ;

					/*if ( $UserID == 1 ) {
						$bc = generateBarcode( $pageBarCode , false , "QR" , array( "EL" => "Q" , "pix_size" => 8 ) );
						$bch = ceil( unitConvert( "2cm" , "tw" ) );
						$bcw = ceil( $bch * $bc[ "w" ] / $bc[ "h" ] );

						$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "9pt" )
						->addImagePNG( $bc[ "raw" ] , $bc[ "w" ] , $bc[ "h" ] , $bcw."tw" , $bch."tw" )->addText( "   " )
						->addTextLine()->addTextLine();
					} else {
						$bc = generateBarcode( $pageBarCode , false );
						$bch = ceil( unitConvert( "0.7cm" , "tw" ) );
						$bcw = ceil( $bch * $bc[ "w" ] / $bc[ "h" ] );
						$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "9pt" )
						->addImagePNG( $bc[ "raw" ] , $bc[ "w" ] , $bc[ "h" ] , $bcw."tw" , $bch."tw" )->addTextLine()
						->setFontName( FONT_CALIBRI )->addTag( "fittext".$bcw )->addTextLine( $pageBarCode )->addTag( "fittext-1" )
						->addTextLine();
					}*/
			} else
			if ( $ccn->nodeName == 'prepend-text' ) {
				$tmplPTt = readTextHTML( $ccn , $doc_id );
				echo '<div class="tgt-displayable" title="Препровождение"><div><div id="prepend-text" contenteditable="false">'.$tmplPTt.'</div><div id="boss-signature">'.$tabPosts[ $dbConfig[ "org.boss" ][ "post_1_id" ] ][ "short_name" ]." ".$dbConfig[ "org.name.short" ]." ________ ".NAMES_Format( NAMES_parse( $dbConfig[ "org.boss" ][ "name" ] ) , "%i.%o. %F1" ).'</div></div></div>' ;
			} else
			if ( $ccn->nodeName == "title" ) {
				$tmplTt = readTextHTML( $ccn , $doc_id );
				echo '<div class="tgt-displayable" title="Заголовок"><div class="title" contenteditable="false">'.$tmplTt."</div></div>" ;
			} else
			if ( $ccn->nodeName == "main-text" ) {
				$tmplMTt = readTextHTML( $ccn , $doc_id );
				echo '<div id="main-text" class="main-text" contenteditable="false">'.$tmplMTt."</div>" ;
			} else {
				echo "<!-- comment -->" ;
			}
		}
	}

	echo "</div></div></div></div>" ;

	//print_r_html( $exClasses );

	if ( count( $exClasses ) > 0 ) {
		$exClassesRes = array();
		foreach( $exClasses as $sel => $def ) {
			if ( is_array( $def ) ) {
				$rdef = array();
				foreach( $def as $cpn => $cdef ) {
					$rdef[]= $cpn.":".$cdef ;
				}
				$exClassesRes[]= $sel." {".implode( ";" , $rdef )."}" ;
			} else {
				$exClassesRes[]= $sel." {".$def."}" ;
			}
		}
		echo "<style>".implode( " " , $exClassesRes )."</style>" ;
	}


	function prepDocVarDesc( $n ) {
		$m = array();
		$res = explode( ">" , $n );
		$i = 0 ;
		foreach ( $res as &$c ) {
			$c = "<span class=\"var-desc-el\" style=\"margin-left : ".( $i++ * 16 )."px ;\">".trim( $c )."</span>" ;
		} unset( $c );
		return implode( $res ) ;
	}

	$usedVars = $tmplDoc->extractVars();
	uksort( $docVar , function( $k1 , $k2 ) use ( $usedVars ) {
		$keysData = array( $k1 => 0 , $k2 => 0 );
		$w = array( 'tmpl' => 1 , 'ext' => 4 , '' => 3 , 'env' => 2 , 'cfg' => 0 );

		foreach( $keysData as $k => &$v ) {
			$c = varCategory( $k );
			if ( isset( $w[ $c ] ) ) {
				$v += 10 * $w[ $c ];
			}
			if ( isset( $usedVars[ $k ] ) ) {
				$v += 100 ;
			}
		} unset( $v );
		if ( $keysData[ $k1 ] != $keysData[ $k2 ] ) {
			return ( $keysData[ $k2 ] - $keysData[ $k1 ] );
		} else {
			return strcasecmp( $k1 , $k2 );
		}
	} );


	echo "<div class=\"ctrl-area\" style=\"right : -8cm\">
		<label id=\"doc-vis-ctrl\" for='doc-vis'></label>
		<div id=\"tgt-displayable-ia\"></div>
		<div class=\"var-area\"><table>" ;
		foreach ( $docVar as $dvk => $dvd ) {
			$varLnkT = ( $dvd[ "mf" ] ? inForm( $dvd[ "value" ] , 1 ) : $dvd[ "value" ] );
			echo "<tr>
				<td>".prepDocVarDesc( $dvd[ "desc" ] )."</td>
				<td>
					<a unselectable=\"on\" onselectstart=\"return false;\" onmousedown=\"return false;\"  class=\"var-lnk\" onclick=\"doPasteVariable( '".$dvk."' )\"><div class=\"var-lnk-icon\"></div><span data-editor-var-name=\"".$dvk."\">".$varLnkT."</span></a>".
					( !$dvd[ "mf" ] ? "<a unselectable=\"on\" onselectstart=\"return false;\" onmousedown=\"return false;\"  class=\"var-lnk\" onclick=\"doEditVariable( '".$dvk."' )\"><div class=\"var-e-lnk-icon\"></div></a>" : "" )."
				</td>
			</tr>" ;
		}
		echo "</table></div>" ;

		echo "<div style=\"text-align : left\">
			".( $UserID == 100000 ? "<input type=\"checkbox\" id=\"req-dbg-mode\"> Режим отладки " : "" )."<a onclick=\"doGetRTF(".$tmplID.",'".$doc_type."',".$doc_id.")\" class=\"btn3\" target=\"_blank\">скачать</a>
		</div>
	</div>" ;


	echo "<div>
	</div>" ;

	closeHtml_Print();
