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

	$UserWorker = $portalDB->row( "select * from `workers` where `id` = ?" , "i" , $UserWorkerID );

	$res = $portalDB->query( "select `t1`.`name` , `t2`.`ind` from `workers` as `t1` , `departments` as `t2` where ( `t1`.`dep` = `t2`.`id` ) and ( `t1`.`actual` <=> 1 )" );

	$boss = NAMES_Format( NAMES_parse( $dbConfig[ "org.boss" ][ "name" ] ) , "%F1 %i.%o." );

	$tabCaseCategory = $portalDB->table( "casecategory" , "id" );

	$ndct = array();
	foreach ( $res as $r ) {
		$n = NAMES_Format( NAMES_parse( $r[ "name" ] ) , "%F1 %i.%o." );
		$nt = NAMES_Format( NAMES_parse( $r[ "name" ] ) , "%F1 %i.%o." );
		$nr = NAMES_Format( NAMES_parse( $r[ "name" ] ) , "%F3 %i.%o." );
		$ndct[ $n ] = array(
			"t" => $nt ,
			"r" => $nr ,
			"i" => $r[ "ind" ]
		);
	}

	$res = $portalDB->row( "select `t1`.* , `t2`.`name` as `agency` , `t3`.`name` as `agent` from `matincoming` as `t1` , `agency` as `t2` , `agent` as `t3` where ( `t1`.`id` = ? ) and ( `t2`.`id` = `t1`.`from_agency` ) and ( `t3`.`id` = `t1`.`from_agent` )" , "s" , $t1ID );

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

	function packText( $t ) {
		$res = trim( $t );
		$res = preg_replace( '/\s+/' , " " , $res );
		$res = preg_replace( '/,([а-я])/i' , ', ${1}' , $res );
		return $res ;
	}

	header( "Content-Type: application/rtf" );
	header( "Content-Disposition: attachment;filename=\"Поручение ".date( "Y.m.d H-i" , time() ).".rtf\"" );

	$doc = new RTFDocument();

	$doc->paperFormat = PAPER_SIZE_A5_LANDSCAPE ;
	//$doc->paperFormat = PAPER_SIZE_A4_PORTRAIT ;
	$doc->margins = "10mm" ;

	$doc->setFontName( FONT_CALIBRI )->setTextColor( "#000000" );

	$doc->setMainContext()->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "8pt" )
		->addTag( "caps" )->addTag( "b" )
		->addTextLine( inForm( $dbConfig[ "org.name.full.type" ] , 1 ) )
		->addTextLine( inForm( $dbConfig[ "org.name.full.name" ] , 1 ) )
		->addTextLine( inForm( $dbConfig[ "org.name.full.head" ] , 2 ) )
		->addTag( "b0" )->addTag( "caps0" );

	$tbl = $doc->addTable();

		$r = $tbl->insertRow();
		$r->height = "4mm" ;

			$c = $r->insertCell();
			$c->width = "140mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "8pt" )
				->addText( $dbConfig[ "org.address" ] );

			$c = $r->insertCell();
			$c->width = "50mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "8pt" )
				->addText( "тел.: ".$dbConfig[ "org.phone" ].", факс: ".$dbConfig[ "org.fax" ] );

		$r = $tbl->insertRow();
		$r->height = "6mm" ;

			$c = $r->insertCell();
			$c->width = "90mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltb" , "s" )->setBorders( "r" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "8pt" )
				->addText( "Экспертиза (экспертное исследование) №" );

			$c = $r->insertCell();
			$c->width = "50mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "tb" , "s" )->setBorders( "rl" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "16pt" )
				->addText( matincomingNumber( $t1ID )." / ".$ind." - ".$tabCaseCategory[ $res[ "exp_type" ] ][ "index" ] );

			$c = $r->insertCell();
			$c->width = "50mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "trb" , "s" )->setBorders( "l" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "16pt" );

		$r = $tbl->insertRow();
		$r->height = "2mm" ;

			$c = $r->insertCell();
			$c->width = "190mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "1pt" );

		$r = $tbl->insertRow();
		$r->height = "15mm" ;

			$exData3 = packText( $res[ "ex_data_3" ]." ".$res[ "agent" ]." ".$res[ "agency" ] );

			$c = $r->insertCell();
			$c->width = "85mm" ;
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$c->setBorders( "ltb" , "s" )->setBorders( "r" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_JUSTIFIED )
				->setFontSize( "9pt" )->addTag( "b" )->addTag( "ul" )->addText( "основание: " )->addTag( "ul0" )->addTag( "b0" )
				->setFontSize( ( strlen( $exData3 ) > 130 ? "7pt" : "10pt" ) )->addText( $exData3 );

			$c = $r->insertCell();
			$c->width = "105mm" ;
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$c->setBorders( "lr" , "none" )->setBorders( "tb" , "s" );
			$t1IDs = getCharIDStructure( $t1ID );
			$t1IDs[ "t" ] = "0420" ;
			$bc = generateBarcode( mkCharID( $t1IDs ) , false );
			$bch = ceil( unitConvert( "1cm" , "tw" ) )."tw" ;
			$bcw = ceil( unitConvert( str_replace( "," , "." , ( $bc[ "w" ] / $bc[ "h" ] ) )."cm" , "tw" ) )."tw" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addImagePNG( $bc[ "raw" ] , $bc[ "w" ] , $bc[ "h" ] , $bcw , $bch );

		$r = $tbl->insertRow();
		$r->height = "20mm" ;

			$exData4 = packText( $res[ "ex_data_4" ] );
			$c = $r->insertCell();
			$c->width = "190mm" ;
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$c->setBorders( "ltrb" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_JUSTIFIED )
				->setFontSize( "9pt" )->addTag( "b" )->addTag( "ul" )->addText( "материалы: " )->addTag( "ul0" )->addTag( "b0" )
				->setFontSize( ( strlen( $exData4 ) > 500 ? "8pt" : "10pt" ) )->addText( $exData4 );

		$r = $tbl->insertRow();
		$r->height = "7mm" ;

			$c = $r->insertCell();
			$c->width = "75mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
				->setFontSize( "9pt" )->addText( "передаются: " )->setFontSize( "14pt" )->addText( $nr );

			$c = $r->insertCell();
			$c->width = "45mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "s" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "14pt" )
				->addText( $t1Date );

			$c = $r->insertCell();
			$c->width = "70mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "14pt" )
				->addText( $boss );

		$r = $tbl->insertRow();
		$r->height = "7mm" ;

			$c = $r->insertCell();
			$c->width = "75mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
				->setFontSize( "9pt" )->addText( "материалы получил(а): " );

			$c = $r->insertCell();
			$c->width = "45mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "s" )->setBorders( "b" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "14pt" )
				->addText( $t1Date );

			$c = $r->insertCell();
			$c->width = "70mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "14pt" )
				->addText( $nt );

		$r = $tbl->insertRow();
		$r->height = "7mm" ;

			$c = $r->insertCell();
			$c->width = "190mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
				->setFontSize( "9pt" )->addText( "Движение материалов ( постановление / определение / дело / доп. материалы и т.д. ): " );

		for( $i = 0 ; $i < 3 ; $i++ ) {
			$r = $tbl->insertRow();
			$r->height = "5mm" ;

				$c = $r->insertCell();
				$c->width = "100mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "ltr" , "s" )->setBorders( "b" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
					->setFontSize( "9pt" )->addText( "получил(а)" );

				$c = $r->insertCell();
				$c->width = "25mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "ltr" , "s" )->setBorders( "b" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "6pt" );

				$c = $r->insertCell();
				$c->width = "65mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "ltr" , "s" )->setBorders( "b" , "dot" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "6pt" )
					->addText( "Ф.И.О." );

			$r = $tbl->insertRow();
			$r->height = "5mm" ;

				$c = $r->insertCell();
				$c->width = "100mm" ;
				$c->verticalAlign = CELL_ALIGN_TOP ;
				$c->setBorders( "lbr" , "s" )->setBorders( "t" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
					->setFontSize( "9pt" )->addText( "передал(а)" );

				$c = $r->insertCell();
				$c->width = "25mm" ;
				$c->verticalAlign = CELL_ALIGN_TOP ;
				$c->setBorders( "lbr" , "s" )->setBorders( "t" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "6pt" )
					->addText( "дата" );

				$c = $r->insertCell();
				$c->width = "65mm" ;
				$c->verticalAlign = CELL_ALIGN_TOP ;
				$c->setBorders( "lbr" , "s" )->setBorders( "t" , "dot" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "6pt" )
					->addText( "подпись" );
		}

		$r = $tbl->insertRow();
		$r->height = "10mm" ;

			$c = $r->insertCell();
			$c->width = "50mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
				->setFontSize( "9pt" )->addText( "Перечисленные выше материалы и экспертизу получил: " );

			$c = $r->insertCell();
			$c->width = "60mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "16pt" )->setTextColor( "#888" )
				->addTag( "u171" )->addText( "\"__" )->addTag( "u187" )->addText( "\" _________ ".substr( date( "Y" , time() ) , 0 , 3 )."__г." )->setTextColor( "#000" );

			$c = $r->insertCell();
			$c->width = "80mm" ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( "ltrb" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "16pt" )
				->addText( "___________ ".NAMES_Format( NAMES_parse( $UserWorker[ "name" ] ) , "%F1 %i.%o." ) );

		$doc->setMainContext();
		$doc->addTag( "v" )->addTextLine()->addTag( "v0" );

		$doc->write();
