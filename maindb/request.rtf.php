<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $UserID
	 * @var TDB $portalDB
	 * @var $LoginOk
	 * @var $dbConfigFull
	 * @var $dbConfig
	 * @var $UserThemeLoc
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	require_once( '../cores/core.maindb.php' );
	
	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( isset( $_REQUEST[ 'tmpl' ] ) ) {
		$tmplID = intval( $_REQUEST[ 'tmpl' ] );
	} else {
		exit();
	}

	if ( !isset( $_REQUEST[ 'type' ] ) ) {
		exit();
	} else {
		$doc_type = $_REQUEST[ 'type' ];
	}

	if ( !isset( $_REQUEST[ 'id' ] ) ) {
		exit();
	} else {
		$doc_id = intval( $_REQUEST[ 'id' ] );
	}

	require_once ( '../barcode.php' );
	require_once ( '../ext-lib/rtf-gen.php' );
	require_once( 'request.core.php' );

	$pageBarcodeSettings = [
		BARCODE_TYPE_1D => [
			'height' => '0.7cm' ,
			'align' => TEXT_ALIGN_RIGHT ,
			'text' => true
		] ,
		BARCODE_TYPE_QRCODE => [
			'height' => '2cm' ,
			'align' => TEXT_ALIGN_CENTER ,
			'text' => false
		] ,
		BARCODE_TYPE_DATAMATRIX => [
			'height' => '2cm' ,
			'align' => TEXT_ALIGN_CENTER ,
			'text' => false
		] ,
	];

	$tabDepartments = array();
	$tabWorkers = array();
	$tabPosts = array();
	$tabSpecGroups = array();
	$tabCaseCategory = array();
	$tabTypeOfAgency = array();

	$tmplData = $portalDB->row( "select * from `doc-templates` where `id` = ?" , 'i' , $tmplID );
	//$tmplExtVar = json_decode( iconv( "cp1251" , "utf8" , $tmplData[ "ext-var" ] ) , true );
	//$docVar = loadVariables( $tmplData , $expertize_id );
	$docVar = fillDataBank( $tmplData , $doc_type ,  $doc_id );
	$listsData = array();

	if ( $docVar === false ) {
		exit();
	}

	$docVarVal = $docVar ;
	foreach ( $docVarVal as &$dvv ) {
		$dvv = $dvv[ 'value' ];
	} unset( $dvv );

	$tmplDoc = new TDocTemplate( $tmplData[ 'tmpl' ] );

	if ( isset( $_REQUEST[ 'dbg' ] ) && $_REQUEST[ 'dbg' ] == 1 ) {

	} else {
		header( 'Content-Type: application/rtf' );
		header( 'Content-Disposition: attachment;filename="'.$tmplData[ 'name' ].' '.date( 'Y.m.d H-i' , time() ).'.rtf"' );
	}

	$doc = new RTFDocument();

	$tod = $tmplDoc->origDOM ;

	$doc->paperFormat = $tmplDoc->pageFormat ;
	$doc->margins = '20mm 10mm 20mm 20mm' ;

	$doc->setFontName( FONT_TIMES_NEW_ROMAN )->setTextColor( '#000' );

	foreach( $tod->childNodes as $ccn ) {
		//var_dump_html( $ccn );
		if ( $ccn->nodeType != XML_TEXT_NODE ) {
			if ( $ccn->nodeName == 'header' ) {
				$onhbr = json_decode( ( $dbConfigFull[ 'org.name.full.head' ][ 'e-data' ] ) )->br ;
				$onnbr = json_decode( ( $dbConfigFull[ 'org.name.full.name' ][ 'e-data' ] ) )->br ;
				$pageBarCodeType = $docVarVal[ 'cfg:docs-barcode-type' ];
				$pageBarCode = $docVarVal[ 'page-code' ];
				$pbcs = $pageBarcodeSettings[ $pageBarCodeType ];

				$tbl = $doc->addTable();

				$r = $tbl->insertRow();

					$c = $r->insertCell();
					$c->width = '85mm' ;
					$c->verticalAlign = CELL_ALIGN_TOP ;
					$c->setBorders( 'ltrb' , 'none' );
					$nationalEmblem = array(
						'raw' => file_get_contents( './themes/'.$UserThemeLoc.'/nationalEmblem.png' ),
						'w' => 165 ,
						'h' => 174
					);
					$bch = ceil( unitConvert( '2cm' , 'tw' ) );
					$bcw = ceil( $bch * $nationalEmblem[ 'w' ] / $nationalEmblem[ 'h' ] );
					$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )
						->addImagePNG( $nationalEmblem[ "raw" ] , $nationalEmblem[ 'w' ] , $nationalEmblem[ 'h' ] , $bcw.'tw' , $bch.'tw' )->addTextLine()->addTextLine()
						->setFontSize( '9pt' )->addTag( 'caps' )->addTag( 'b' )
						->addTextLine( breakLineByRule( inForm( $dbConfig[ 'org.name.full.head' ] , 1 ) , $onhbr , "\r\n" ) )->addTextLine()
						->addTextLine( inForm( $dbConfig[ 'org.name.full.type' ] , 1 ) )
						->addTextLine( breakLineByRule( inForm( $dbConfig[ 'org.name.full.name' ] , 1 ) , $onnbr , "\r\n" ) )
						->addTag( 'b0' )->addTag( 'caps0' )

						->addTextLine()

						->addTextLine( $dbConfig[ 'org.address' ] )
						->addTextLine( 'Тел.: 8 ( '.$dbConfig[ 'org.phone.code' ]." ) ".$dbConfig[ 'org.phone' ] )
						->addTextLine( 'Факс: 8 ( '.$dbConfig[ 'org.phone.code' ]." ) ".$dbConfig[ 'org.fax' ] )
						->addText( 'e-mail: ' )->setTextColor( '#06f' )->addTag( 'ul' )->addText( $dbConfig[ 'org.email' ] )->addTag( 'ul0' )->setTextColor( '#000' )->addTextLine()
						->addTextLine( '№ '.$docVarVal[ 'exp-number-full' ].' от '.date( 'd.m.Y' , time() ).' г.' );

					$c = $r->insertCell();
					$c->width = '85mm' ;
					$c->verticalAlign = CELL_ALIGN_TOP ;
					$c->setBorders( 'ltrb' , 'none' );
					$bc = generateBarcode( $pageBarCode , false , $pageBarCodeType );
					$bch = ceil( unitConvert( $pbcs[ 'height' ] , 'tw' ) );
					$bcw = ceil( $bch * $bc[ 'w' ] / $bc[ 'h' ] );

					$doc->setTableCellContext( $c )->setTextAlign( $pbcs[ 'align' ] )->setFontSize( '9pt' )
					->addImagePNG( $bc[ 'raw' ] , $bc[ 'w' ] , $bc[ 'h' ] , $bcw.'tw' , $bch.'tw' )->addTextLine();
					if ( $pbcs[ 'text' ] ) {
						$doc->setFontName( FONT_CALIBRI )->addTag( 'fittext'.$bcw )->addTextLine( $pageBarCode )->addTag( 'fittext-1' );
					}
					$doc->addTextLine();

					//$bcw = ceil( $bch * $bc[ "w" ] / $bc[ "h" ] );


					$doc->setFontName( FONT_TIMES_NEW_ROMAN )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "12pt" )
						->addTextLine( inForm( $docVarVal[ "agency" ] ) )
						->addTextLine()
						->addTextLine( inForm( $docVarVal[ "agent" ] ) )
						->addTextLine()
						->addTextLine( $docVarVal[ "agency-address" ] )
						->addTextLine()
						->addText( "дело №" )->setHighlight( "#0f0" )->addTextLine( $docVarVal[ "case-num" ] )->setHighlight();

				$doc->setMainContext()->addTextLine();
			} else
			if ( $ccn->nodeName == "prepend-text" ) {
				$bossPostName = !is_null( $tabPosts[ $dbConfig[ 'org.boss' ][ 'post_1_id' ] ][ 'short_name' ] ) ? $tabPosts[ $dbConfig[ 'org.boss' ][ 'post_1_id' ] ][ 'short_name' ] : $tabPosts[ $dbConfig[ 'org.boss' ][ 'post_1_id' ] ][ 'name' ];
				$doc->setTextAlign( TEXT_ALIGN_JUSTIFIED )->setFontSize( "13pt" )->setFirstLineIndent( "1cm" );
				readTextRTF( $ccn , $doc_id , $doc )->addTextLine()->addTextLine();
				$doc->setFirstLineIndent( "0cm" )->setTextAlign( TEXT_ALIGN_CENTER )
					->addTextLine( $bossPostName." ".$dbConfig[ "org.name.short" ]." ________ ".NAMES_Format( NAMES_parse( $dbConfig[ "org.boss" ][ "name" ] ) , "%i.%o. %F1" ) )
					->addTextLine()->addTextLine();
			} else
			if ( $ccn->nodeName == "title" ) {
				$doc->setFirstLineIndent( "0cm" )->setTextAlign( TEXT_ALIGN_CENTER )->addTag( "caps" )->addTag( "b" );
				readTextRTF( $ccn , $doc_id , $doc );
				$doc->addTag( "b0" )->addTag( "caps0" )
					->addTextLine()->addTextLine();
			} else
			if ( $ccn->nodeName == "main-text" ) {
				$doc->setFontSize( "13pt" );
				$doc->setFirstLineIndent( "1cm" )->setParSpace( "0pt" )->setTextAlign( TEXT_ALIGN_JUSTIFIED );
				readTextRTF( $ccn , $doc_id , $doc );
			}
		}
	}

	$doc->write();
