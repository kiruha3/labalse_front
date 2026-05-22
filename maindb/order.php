<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once ( "../core.php" );
	require_once ( "../barcode.php" );
	require_once ( "lconfig.php" );
	require_once ( "../ext-lib/rtf-gen.php" );

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( isset( $_REQUEST[ "id" ] ) ) {
		$t1ID = getCharID( $_REQUEST[ "id" ] , DOCTYPE_MATINCOMING );
	} else {
		echo "ДоZZвидания!" ;
		exit();
	}



	$res = $portalDB->query( "select `t1`.`name` , `t2`.`ind` from `workers` as `t1` , `departments` as `t2` where ( `t1`.`dep` = `t2`.`id` ) and ( `t1`.`actual` <=> 1 )" );

	$boss = NAMES_Format( NAMES_parse( $dbConfig[ "org.boss" ][ "name" ] ) , "%i.%o. %F1" );
	$tabCaseCategory = $portalDB->table( "casecategory" , "id" );

	$ndct = array();
	foreach ( $res as $r ) {
		$n = NAMES_Format( NAMES_parse( $r[ "name" ] ) , "%F1 %i.%o." );
		$nt = NAMES_Format( NAMES_parse( $r[ "name" ] ) , "%F3 %i.%o." );
		$nr = NAMES_Format( NAMES_parse( $r[ "name" ] ) , "%i.%o. %F1" );
		$ndct[ $n ] = array(
			"t" => $nt ,
			"r" => $nr ,
			"i" => $r[ "ind" ]
		);
	}

	$res = $portalDB->row( "select * from `matincoming` where ( `id` = ? )" , "s" , $t1ID );

	$m = array();
	$n = preg_match( "/([А-Яа-яёЁ]+ [А-Яа-яёЁ]\\.[А-Яа-яёЁ]\\.)/" , $res[ "ex_data_6" ] , $m );
	if ( $n == 1 ) {
		$n = $m[ 1 ];
		if ( isset( $ndct[ $n ] ) ) {
			$nt = $ndct[ $n ][ "t" ];
			$nr = $ndct[ $n ][ "r" ];
			$ind = $ndct[ $n ][ "i" ];
		} else {
			$nt = $n ;
			$nr = $n ;
			$ind = "  " ;
		}

	} else {
		$nt = "  " ;
		$nr = "  " ;
		$ind = "  " ;
	}

	$t1Date = explode( "-" , date( "d-m-Y" , strtotime( $res[ "date" ] ) ) );

	$t1Date = $t1Date[ 0 ]." ".inForm( $MonthNames[ intval( $t1Date[ 1 ] ) - 1 ] , 2 )." ".$t1Date[ 2 ]." г." ;

	header( "Content-Type: application/rtf" );
	header( "Content-Disposition: attachment;filename=\"Поручение ".date( "Y.m.d H-i" , time() ).".rtf\"" );

	$doc = new RTFDocument();

	$doc->paperFormat = PAPER_SIZE_A5_LANDSCAPE ;
	$doc->margins = "15mm" ;

	$doc->setFontName( FONT_TIMES_NEW_ROMAN )->setTextColor( "#000000" );

	$doc->setMainContext()->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "12pt" )
		->addTag( "caps" )->addTag( "b" )
		->addTextLine( inForm( $dbConfig[ "org.name.full.type" ] , 1 ) )
		->addTextLine( inForm( $dbConfig[ "org.name.full.name" ] , 1 ) )
		->addTextLine( inForm( $dbConfig[ "org.name.full.head" ] , 2 ) )
		->addTag( "b0" )->addTag( "caps0" );

	$tbl = $doc->addTable();

		$r = $tbl->insertRow();
		$r->height = "7mm" ;

			$c = $r->insertCell();
			$c->width = "110mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "11pt" )
				->addText( $dbConfig[ "org.address" ] );

			$c = $r->insertCell();
			$c->width = "70mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "11pt" )
				->addText( "тел.: ".$dbConfig[ "org.phone" ].", факс: ".$dbConfig[ "org.fax" ] );

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "80mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "12pt" )
				->addText( "Экспертиза (экспертное исследование) №" );

			$c = $r->insertCell();
			$c->width = "48.5mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "20pt" )
				->addText( matincomingNumber( $t1ID )." / ".$ind." - ".$tabCaseCategory[ $res[ "exp_type" ] ][ "index" ] );

			$c = $r->insertCell();
			$c->width = "51.5mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "12pt" )
				->addText( "поручается к исполнению:" );

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "180mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "20pt" )
				->addTag( "expndtw" , "6pt" )->addText( $nt )->addTag( "expndtw" , "0pt" );

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "60mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "20pt" )
				->addText( $t1Date );

			$c = $r->insertCell();
			$c->width = "120mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "14pt" )
				->addText( "________________ ".$boss );

		$r = $tbl->insertRow();
		$r->height = "4mm" ;

			$c = $r->insertCell();
			$c->width = "180mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" , $doc->addColor( "#000000" ) , "2pt" );

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "85mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "12pt" )
				->addText( "Руководитель экспертного подразделения:" );

			$c = $r->insertCell();
			$c->width = "105mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$t1IDs = getCharIDStructure( $t1ID );
			$t1IDs[ "t" ] = "0410" ;
			$bc = generateBarcode( mkCharID( $t1IDs ) , false );
			$bch = ceil( unitConvert( "1cm" , "tw" ) )."tw" ;
			$bcw = ceil( unitConvert( str_replace( "," , "." , ( $bc[ "w" ] / $bc[ "h" ] ) )."cm" , "tw" ) )."tw" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addImagePNG( $bc[ "raw" ] , $bc[ "w" ] , $bc[ "h" ] , $bcw , $bch );

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "80mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "12pt" )
				->addText( "Экспертиза (экспертное исследование) №" );

			$c = $r->insertCell();
			$c->width = "48.5mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "20pt" )
				->addText( matincomingNumber( $t1ID )." / ".$ind." - ".$tabCaseCategory[ $res[ "exp_type" ] ][ "index" ] );

			$c = $r->insertCell();
			$c->width = "51.5mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "12pt" )
				->addText( "поручается к исполнению:" );

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "180mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "80mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "14pt" )
				->addText( "в срок до _____________ дней" );

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "60mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "20pt" )
				->addText( $t1Date );

			$c = $r->insertCell();
			$c->width = "120mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "14pt" )
				->addText( "________________ ".$nr );

		$doc->write();

?>