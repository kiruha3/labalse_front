<?php

	require_once ( "core.php" );
	require_once ( "ext-lib/rtf-gen.php" );

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

		if ( array_key_exists( "REGISTER-CORRESPONDENCE" , $Rights ) ) {
			$mayOutput = in_array( "OUTPUT" , $Rights[ "REGISTER-CORRESPONDENCE" ] );
			$GoOut = !$mayOutput ;
		} else {
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit ;
	}

	header( "Content-Type: application/rtf" );
	header( "Content-Disposition: attachment;filename=\"Реестр ".date( "Y.m.d H-i" , time() ).".rtf\"" );

	if ( isset( $_REQUEST[ "selected" ] ) ) {
		$ida = explode( "," , trim( $_REQUEST[ "selected" ] ) );
		$idl = array();
		foreach( $ida as $id ) {
			$mm = explode( "-" , trim( $id ) );
			if ( count( $mm ) == 1 ) {
				$idl[]= $mm[ 0 ];
			} else
			if ( count( $mm ) == 2 ) {
				for( $i = $mm[ 0 ] ; $i <= $mm[ 1 ] ; $i++ ) {
					$idl[]= $i ;
				}
			}
		}

		if ( count( $idl ) > 0 ) {
			$res = $portalDB->query( "select * from `register-correspondence` where ( `id` in ( ?* ) )" , false , "*i" , $idl );
			$portalDB->noResult( "update `register-correspondence` set `print_date` = ? where ( `id` in ( ?* ) )" , "i*i" , time() , $idl );
		} else {
			$res = array();
		}

	}

	$doc = new RTFDocument();
	$doc->paperFormat = PAPER_SIZE_A4_LANDSCAPE ;
	$doc->margins = "5mm" ;
	$doc->headerMargin = "5mm" ;

	$doc->setHeaderFPContext()->textAlign = TEXT_ALIGN_CENTER ;
	$doc->fontName = FONT_TIMES_NEW_ROMAN ;
	$doc->fontSize = "11pt" ;
	$doc->addTextLine( "Список №_________" );
	$doc->addTextLine( "внутренних почтовых отправлений на франкировку" );
	$doc->addTextLine( "от ".date( "d.m.Y" , time() ) );
	$doc->textAlign = TEXT_ALIGN_LEFT ;
	$doc->addTextLine( "Вид и категория РПО: Заказные письма / заказные бандероли" );
	$doc->addTextLine( "Отправитель: ".$dbConfig[ "org.name.short" ].", ".$dbConfig[ "org.address" ]."" );
	$doc->addText( "Наименование и индекс места приема: " )->addTag( "ul" )->addText( "  ".$dbConfig[ "postOffice.address" ]."  " )->addTag( "ul0" )->addTextLine( "" );
	$doc->addText( "Всего РПО : ".count( $res )."\t\tВсего листов : " )->addRaw( "{\\field{\\*\\fldinst NUMPAGES}{\\fldrslt 0}}" )->addText( "\t\tстр. № " )->addTag( "chpgn" )->addTextLine( "" );
	$doc->addTextLine( "" );

	$doc->setHeaderContext()->textAlign = TEXT_ALIGN_LEFT ;
	$doc->fontName = FONT_TIMES_NEW_ROMAN ;
	$doc->fontSize = "11pt" ;
	$doc->addTextLine( "Вид и категория РПО: Заказные письма / заказные бандероли" );
	$doc->addTextLine( "Отправитель: ".$dbConfig[ "org.name.short" ].", ".$dbConfig[ "org.address" ]."" );
	$doc->addText( "Наименование и индекс места приема: " )->addTag( "ul" )->addText( "  ".$dbConfig[ "postOffice.address" ]."  " )->addTag( "ul0" )->addTextLine( "" );
	$doc->addText( "Всего РПО : ".count( $res )."\t\tВсего листов : " )->addRaw( "{\\field{\\*\\fldinst NUMPAGES}{\\fldrslt 0}}" )->addText( "\t\tстр. № " )->addTag( "chpgn" )->addTextLine( "" );
	$doc->addTextLine( "" );

	$doc->setMainContext();
	$doc->setFontSize( "9pt" );

	$tbl = $doc->addTable();

		$r = $tbl->insertRow();
		$r->height = "30mm" ;
		$r->isHeader = true ;

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addTextLine( "№" )->addText( "п/п" );

			$c = $r->insertCell();
			$c->width = "14cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addTextLine( "Адрес" )->addText( "(Ф.И.О., почтовый адрес)" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$c->textDirection = "btlr" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "ШПИ (Штриховой почтовый идентификатор)" );

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addTextLine( "Вес" )->addText( "(кг.)" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$c->textDirection = "btlr" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "Сумма объявленной ценности (руб.)" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$c->textDirection = "btlr" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "Сумма наложенного платежа (руб.)" );

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->textDirection = "btlr" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "Сумма платы за пересылку без НДС (руб.)" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$c->textDirection = "btlr" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "Сумма платы за пересылку с НДС (руб.)" );

			$c = $r->insertCell();
			$c->width = "3cm" ;
			$c->textDirection = "btlr" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "Примечание" );

		$r = $tbl->insertRow();
		$r->isHeader = true ;

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "1" );

			$c = $r->insertCell();
			$c->width = "14cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "2" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "3" );

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "4" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "5" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "6" );

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "7" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "8" );

			$c = $r->insertCell();
			$c->width = "3cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "9" );


	$i = 1 ;
	$total = array( 0 , 0 );
	foreach( $res as &$row ) {
		$row[ "comment" ] = str_replace( "," , ", " , $row[ "comment" ] );
		$row[ "comment" ] = str_replace( "  " , " " , $row[ "comment" ] );
		$row[ "destination" ] = str_replace( "," , ", " , $row[ "destination" ].', '.$row[ "addressee" ] );
		$row[ "destination" ] = str_replace( "  " , " " , $row[ "destination" ] );

		$total[ 0 ]+= $row[ "price" ];
		$total[ 1 ]+= $row[ "add_price" ];

		$r = $tbl->insertRow();

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( $i++ );

			$c = $r->insertCell();
			$c->width = "14cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->addText( $row[ "destination" ] );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "" );

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addText( number_format( $row[ "weight" ] / 1000.0 , 3 ) );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "" );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "" );

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addText( money_format( "%!i" , $row[ "add_price" ] ) );

			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "" );

			$c = $r->insertCell();
			$c->width = "3cm" ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->addText( $row[ "comment" ] );

	} unset( $row );

	$r = $tbl->insertRow();

		$c = $r->insertCell();
		$c->width = "22cm" ;
		$c->borders = array();
		$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addText( "Итого:" );

		$c = $r->insertCell();
		$c->width = "2cm" ;
		$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addText( money_format( "%!i" , $total[ 1 ] ) );

		$c = $r->insertCell();
		$c->width = "1.5cm" ;
		$c->borders = array();
		$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "" );

		$c = $r->insertCell();
		$c->width = "3cm" ;
		$c->borders = array();
		$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->addText( "" );

	$VAT = round( $total[ 1 ] * 0.18 , 2 );

	$doc->setMainContext()->setFontSize( "10pt" )->addTextLine( "" );
	$doc->addTextLine( "Общее количество : ".count( $res ) );
	$doc->addTextLine( "Общая сумма объявленной ценности : -----" );
	$doc->addText( "Общая сумма платы за пересулку : " )->addTag( "ul" )->addText( "  ".money_format( "%!i" , $total[ 1 ] )." руб. ( ".price2word( $total[ 1 ] )." )  " )->addTag( "ul0" )->addTextLine( "" );
	$doc->addText( "НДС 18% (сверху): " )->addTag( "ul" )->addText( "  ".money_format( "%!i" , $VAT )." руб. ( ".price2word( $VAT )." )  " )->addTag( "ul0" )->addTextLine( "" );
	$doc->addTextLine( "Общая сумма платы за объявленную ценность с НДС : -----" );
	$doc->addTextLine( "В т.ч. НДС : -----" );
	$doc->addText( "Итого за пересылку, с НДС: " )->addTag( "ul" )->addText( "  ".money_format( "%!i" , $total[ 1 ] + $VAT )." руб. ( ".price2word( $total[ 1 ] + $VAT )." )  " )->addTag( "ul0" )->addTextLine( "" );

	$doc->addTextLine( "" );
	$doc->addTextLine( "Дополнительные услуги :" );
	$doc->addTextLine( "Общая сумма платы за пересылку заказных уведомлений о вручении : -----" );
	$doc->addTextLine( "В т.ч. НДС : -----" );
	$doc->addTextLine( "Всего к оплате : -----" );
	$doc->addTextLine( "В т.ч. НДС : -----" );
	$doc->addTextLine( "" );

	$tbl = $doc->addTable();
		$r = $tbl->insertRow();
			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$c->borders = array();
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addText( "Сдал" );

			$c = $r->insertCell();
			$c->width = "6cm" ;
			$c->borders = array( "b" => array( "t" => "s" , "c" => 1 ) );

			$c = $r->insertCell();
			$c->width = "0.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->borders = array( "l" => array( "t" => "s" , "c" => 1 ) ); $c->borders[ "t" ] = $c->borders[ "r" ] = $c->borders[ "l" ];

			$c = $r->insertCell();
			$c->width = "3cm" ;
			$c->borders = array();
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addText( "Принял" );

			$c = $r->insertCell();
			$c->width = "6cm" ;
			$c->borders = array( "b" => array( "t" => "s" , "c" => 1 ) );

			$c = $r->insertCell();
			$c->width = "0.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->borders = array( "l" => array( "t" => "s" , "c" => 1 ) ); $c->borders[ "t" ] = $c->borders[ "r" ] = $c->borders[ "l" ];

		$r = $tbl->insertRow();
			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "6cm" ;
			$c->borders = array( "b" => array( "t" => "s" , "c" => 1 ) );
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "6pt" )->addText( "(должность)" )->setFontSize( "10pt" );

			$c = $r->insertCell();
			$c->width = "0.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->borders = array( "l" => array( "t" => "s" , "c" => 1 ) ); $c->borders[ "r" ] = $c->borders[ "l" ];
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "М.П." );

			$c = $r->insertCell();
			$c->width = "3cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "6cm" ;
			$c->borders = array( "b" => array( "t" => "s" , "c" => 1 ) );
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "6pt" )->addText( "(должность)" )->setFontSize( "10pt" );

			$c = $r->insertCell();
			$c->width = "0.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->borders = array( "l" => array( "t" => "s" , "c" => 1 ) ); $c->borders[ "r" ] = $c->borders[ "l" ];
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->addText( "М.П." );

		$r = $tbl->insertRow();
			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "6cm" ;
			$c->borders = array( "b" => array( "t" => "s" , "c" => 1 ) );
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "6pt" )->addText( "(подпись)" )->setFontSize( "10pt" );


			$c = $r->insertCell();
			$c->width = "0.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->borders = array( "l" => array( "t" => "s" , "c" => 1 ) ); $c->borders[ "r" ] = $c->borders[ "l" ];

			$c = $r->insertCell();
			$c->width = "3cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "6cm" ;
			$c->borders = array( "b" => array( "t" => "s" , "c" => 1 ) );
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "6pt" )->addText( "(подпись)" )->setFontSize( "10pt" );

			$c = $r->insertCell();
			$c->width = "0.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->borders = array( "l" => array( "t" => "s" , "c" => 1 ) ); $c->borders[ "r" ] = $c->borders[ "l" ];

		$r = $tbl->insertRow();
			$c = $r->insertCell();
			$c->width = "1.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "6cm" ;
			$c->borders = array();
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "6pt" )->addText( "(Ф.И.О.)" )->setFontSize( "10pt" );


			$c = $r->insertCell();
			$c->width = "0.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->borders = array( "l" => array( "t" => "s" , "c" => 1 ) ); $c->borders[ "b" ] = $c->borders[ "r" ] = $c->borders[ "l" ];

			$c = $r->insertCell();
			$c->width = "3cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "6cm" ;
			$c->borders = array();
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "6pt" )->addText( "(Ф.И.О.)" )->setFontSize( "10pt" );

			$c = $r->insertCell();
			$c->width = "0.5cm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "2cm" ;
			$c->borders = array( "l" => array( "t" => "s" , "c" => 1 ) ); $c->borders[ "b" ] = $c->borders[ "r" ] = $c->borders[ "l" ];

	$doc->write();
?>