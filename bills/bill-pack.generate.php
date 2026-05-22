<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "../core.php" );
	require_once( "lconfig.php" );
	require_once ( "../barcode.php" );
	require_once ( "../ext-lib/rtf-gen.php" );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "VIEW_BASE" , $Rights ) ) {
			$listVIEW_SD = in_array( "VIEW_SD" , $Rights[ "VIEW_BASE" ] );
			$listVIEW_OD = in_array( "VIEW_OD" , $Rights[ "VIEW_BASE" ] );

			$GoOut = !( $listVIEW_SD || $listVIEW_OD );
		} else {
			$listVIEW_SD = $listVIEW_OD = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2("", "", array( "../%UT/buttons.css" , "../%UT/forms.css"), array(), "hlp/no_access.html");
		echo "<br><br><br><br><br>";
		MessageForm();
		closeHtml();
		exit;
	}

	$row = $portalDB->row( "select `worker_id` from `accounts` where `id` = ?" , "i" , $UserID );
	$WorkerID = $row[ "worker_id" ];

	$tabWorkers = $portalDB->table( "workers-no-spec" , "id" );
	foreach( $tabWorkers as &$worker ) {
		$worker = NAMES_Format( NAMES_parse( $worker[ "name" ] ) );
	} unset( $worker );

	$boss = NAMES_Format( NAMES_parse( $dbConfig[ "org.boss" ][ "name" ] ) );
	$accountantGeneral = NAMES_Format( NAMES_parse( $dbConfig[ "org.accountantGeneral" ][ "name" ] ) );

	$nl = $portalDB->simpleQuery( "simple-lists" , array( "group" => "notarius" ) , "key" );

	function getNotarius( $id ) {
		global $nl ;
		$s = getCharIDStructure( $id );
		$nid = intval( substr( $s[ "n" ] , 0 , 3 ) , 10 );
		if ( isset( $nl[ $nid ] ) ) {
			return $nl[ $nid ][ "value" ];
		} else {
			return "< - >" ;
		}
	}

	function getGRange( $id ) {
		$s = getCharIDStructure( $id );
		return intval( substr( $s[ "n" ] , 3 ) , 10 );
	}

	function getGYear( $id ) {
		$s = getCharIDStructure( $id );
		return $s[ "y" ];
	}

	if ( isset( $_REQUEST[ "download" ] ) ) {
		$dID = intval( $_REQUEST[ "download" ] , 10 );
		$gr = $portalDB->row( "select * from `pre-generated-id-packs` where ( `from_id` like '10.360.4010.%' ) and ( `id` = ? )" , "i" , $dID );
		if ( $gr === false ) {
			exit();
		}

		$sf = getCharIDStructure( $gr[ "from_id" ] );
		if ( $sf === false ) {
			exit ;
		}
		$sfn = intval( $sf[ "n" ] , 10 );

		$st = getCharIDStructure( $gr[ "to_id" ] );
		if ( $st === false ) {
			exit ;
		}
		$stn = intval( $st[ "n" ] , 10 );
		//$stn = $sfn + 1 ;

		$gr[ "ex_data" ] = json_decode( $gr[ "ex_data" ] , true );
		$gr[ "ex_data.sum" ] = $gr[ "ex_data" ][ "sum" ] / 100 ;

		$barCodeData = array(
			"ST00011" ,
			"Name=л/с ".$dbConfig[ "org.clientAccount" ]." ".$dbConfig[ "org.beneficiary.name.short" ]." (".$dbConfig[ "org.name.short" ].")" ,
			"PersonalAcc=".$dbConfig[ "org.beneficiary.accountNumber" ] ,
			"BankName=".$dbConfig[ "org.bank.name" ] ,
			"BIC=".$dbConfig[ "org.bank.bic" ] ,
			"CorrespAcc=00000000000000000000" ,
			"PayeeINN=".$dbConfig[ "org.inn" ] ,
			"KPP=".$dbConfig[ "org.kpp" ] ,
			"CBC=00000000000000000130" ,
			"OKTMO=".$dbConfig[ "org.oktmo" ] ,
			"Sum=".number_format( $gr[ "ex_data.sum" ] , 2 , "" , "" ) ,
			//"Purpose=".clearText( implode( " / " , $payFor ) )
		);

		header( "Content-Type: application/rtf" );
		header( "Content-Disposition: attachment;filename=\"Платежки для ".getNotarius( $gr[ "from_id" ] )." #".getGRange( $gr[ "from_id" ] )." - ".getGRange( $gr[ "to_id" ] )." ".date( "Y.m.d H-i-s" , time() ).".rtf\"" );

		$doc = new RTFDocument();

		$doc->paperFormat = PAPER_SIZE_A5_LANDSCAPE ;
		//$doc->paperFormat = PAPER_SIZE_A4_PORTRAIT ;
		$doc->margins = "10mm 15mm 10mm 15mm" ;

		$doc->setFontName( FONT_CALIBRI )->setTextColor( "#000000" );

		$redColor = $doc->addColor( "#f00" );

		/*$doc->setHeaderContext()->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "8pt" )
			->addTextLine()->addTextLine( "Квитанция на оплату экспертизы/исследования" );*/

		$doc->setMainContext()->addText();
		$si = $sf ;
		for( $i = $sfn ; $i <= $stn ; $i++ ) {

			$si[ "n" ] = str_pad( $i , 6 , 0 , STR_PAD_LEFT );
			$iID = mkCharID( $si );


			$tbl = $doc->addTable();

			$r = $tbl->insertRow();

				$T = implode( "|" , array_merge( $barCodeData , array( "Purpose=#".$iID." Оценка транспортного средства" ) ) );
				$bc = generateBarcode( $T , false , "QR" , array( "EL" => "Q" , "pix_size" => 8 ) );
				$bch = ceil( unitConvert( "5.5cm" , "tw" ) );
				$bcw = ceil( $bch * $bc[ "w" ] / $bc[ "h" ] );

				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->verticalAlign = CELL_ALIGN_CENTER ;
				$c->setBorders( "ltrb" , "s" );
				$c->vMerge = CELL_MERGE_FIRST ;
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "10pt" )
					->addTextLine( "Извещение" )->addImagePNG( $bc[ "raw" ] , $bc[ "w" ] , $bc[ "h" ] , $bcw."tw" , $bch."tw" );

				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lt" , "s" );
				$c->setBorders( "rb" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "9pt" )
					->addText( "ПАО СБЕРБАНК" );

				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "tr" , "s" );
				$c->setBorders( "lb" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "9pt" )
					->addText( "Форма #ПД-4" );


			$r = $tbl->insertRow();
			$r->height = "1.1cm" ;
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "lrb" , "s" );
				$c->setBorders( "t" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "10pt" )
					->addTextLine( $dbConfig[ "org.clientAccount" ]." ".$dbConfig[ "org.beneficiary.name.short" ] )
					->addText( "(".$dbConfig[ "org.name.short" ].")" );

			$r = $tbl->insertRow();
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->setBorders( "ltr" , "s" );
				$c->setBorders( "b" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "7pt" )
					->addText( "(наименование получателя платежа)" );

			$r = $tbl->insertRow();
			$r->height = "0.7cm" ;
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "70mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "lb" , "s" );
				$c->setBorders( "tr" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "10pt" )
					->addText( "ИНН ".$dbConfig[ "org.inn" ]." КПП ".$dbConfig[ "org.kpp" ] );

				$c = $r->insertCell();
				$c->width = "50mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "rb" , "s" );
				$c->setBorders( "lt" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "10pt" )
					->addText( $dbConfig[ "org.beneficiary.accountNumber" ] );


			$r = $tbl->insertRow();
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "70mm" ;
				$c->setBorders( "lt" , "s" );
				$c->setBorders( "rb" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "7pt" )
					->addText( "(инн получателя платежа)" );

				$c = $r->insertCell();
				$c->width = "50mm" ;
				$c->setBorders( "tr" , "s" );
				$c->setBorders( "lb" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "7pt" )
					->addText( "(номер счёта получателя платежа)" );



			$r = $tbl->insertRow();
			$r->height = "0.7cm" ;
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "lrb" , "s" );
				$c->setBorders( "t" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "10pt" )
					->addText( "БИК ".$dbConfig[ "org.bank.bic" ]." (".$dbConfig[ "org.bank.name" ].")" );

			$r = $tbl->insertRow();
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->setBorders( "ltr" , "s" );
				$c->setBorders( "b" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "7pt" )
					->addText( "(наименование банка получателя платежа)" );


			$r = $tbl->insertRow();
			$r->height = "1.1cm" ;
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "lrb" , "s" );
				$c->setBorders( "t" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "10pt" )
					->addTextLine( "Назначение: Оценка ТС ID: ".$iID."; " )
					->addText( "КБК: 00000000000000000130 ; ОКТМО: ".$dbConfig[ "org.oktmo" ] );

			$r = $tbl->insertRow();
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->setBorders( "ltr" , "s" );
				$c->setBorders( "b" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "7pt" )
					->addText( "(назначение платежа)" );

			$r = $tbl->insertRow();
			$r->height = "0.7cm" ;
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "lrb" , "s" );
				$c->setBorders( "t" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "10pt" )
					->addText( "Сумма: ".money_format( "%!i" , $gr[ "ex_data.sum" ] ) );

			$r = $tbl->insertRow();
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lr" , "s" );
				$c->setBorders( "tb" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->setBorders( "ltr" , "s" );
				$c->setBorders( "b" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "7pt" )
					->addText( "(сумма платежа)" );

			$r = $tbl->insertRow();
			$r->height = "1.2cm" ;
				$c = $r->insertCell();
				$c->width = "60mm" ;
				$c->setBorders( "lrb" , "s" );
				$c->setBorders( "t" , "none" );
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( "lrb" , "s" );
				$c->setBorders( "t" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_JUSTIFIED )->setFontSize( "7pt" )
					->addTextLine( "С условиями приёма указанной в платёжном документе суммы, в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен." )
					->addText( "Подпись плательщика ______________________________________\\" );

			$r = $tbl->insertRow();
			$r->height = "1.2cm" ;
				$c = $r->insertCell();
				$c->width = "180mm" ;
				$c->setBorders( "ltrb" , "none" );
				$doc->setTableCellContext( $c );


			$r = $tbl->insertRow();
			$r->height = "1.5cm" ;

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->vMerge = CELL_MERGE_FIRST ;
				$c->verticalAlign = CELL_ALIGN_CENTER ;
				$c->setBorders( "ltrb" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_JUSTIFIED )->setFontSize( "14pt" )->addTag( "i" )
					->addText( "Сфотографируйте и отправьте этот штрих-код в составе пакета документов" )->addTag( "i0" );

				$c = $r->insertCell();
				$c->width = "25mm" ;
				$c->setBorders( "rb" , "s" , $redColor );
				$c->setBorders( "lt" , "none" );
				$doc->setTableCellContext( $c );

				$bc = generateBarcode( $iID , false , "QR" , array( "EL" => "Q" , "pix_size" => 32 ) );
				$bch = ceil( unitConvert( "3cm" , "tw" ) );
				$bcw = ceil( $bch * $bc[ "w" ] / $bc[ "h" ] );

				$c = $r->insertCell();
				$c->width = "35mm" ;
				$c->vMerge = CELL_MERGE_FIRST ;
				$c->verticalAlign = CELL_ALIGN_CENTER ;
				$c->setBorders( "ltr" , "s" , $redColor , "3pt" );
				$c->setBorders( "b" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "10pt" )
					->addImagePNG( $bc[ "raw" ] , $bc[ "w" ] , $bc[ "h" ] , $bcw."tw" , $bch."tw" );

			$r = $tbl->insertRow();
			$r->height = "1.5cm" ;

				$c = $r->insertCell();
				$c->width = "120mm" ;
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$c->setBorders( "ltrb" , "none" );
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "25mm" ;
				$c->setBorders( "tr" , "s" , $redColor );
				$c->setBorders( "lb" , "none" );
				$doc->setTableCellContext( $c );

				$c = $r->insertCell();
				$c->width = "35mm" ;
				$c->vMerge = CELL_MERGE_PRECEDING ;
				$c->setBorders( "lrb" , "s" , $redColor , "3pt" );
				$c->setBorders( "t" , "none" );
				$doc->setTableCellContext( $c );


			if ( $i < $stn ) {
				$doc->setMainContext();
				$doc->addTag( "pagebb" )->addTextLine()->addTag( "pagebb0" );
				$doc->textColor = "#000" ;
			}
		}


		$doc->write();

		exit();
	}

	if ( isset( $_REQUEST[ "generate" ] ) ) {
		//print_r_html( $_REQUEST );

		$y = intval( $_REQUEST[ "bpg-year" ] , 10 );
		$not = intval( $_REQUEST[ "bpg-not" ] , 10 );

		$s = array( "v" => 10 , "o" => 360 , "t" => 4010 , "y" => $y );

		$s[ "y" ] = $y ;
		$s[ "n" ] = str_pad( $not * 1000 + intval( $_REQUEST[ "bpg-from" ] , 10 ) , 6 , 0 , STR_PAD_LEFT );
		$fromID = mkCharID( $s );

		$s[ "n" ] = str_pad( $not * 1000 + intval( $_REQUEST[ "bpg-to" ] , 10 ) , 6 , 0 , STR_PAD_LEFT );
		$toID = mkCharID( $s );

		$portalDB->insertRow( "pre-generated-id-packs" , array(
			"from_id" => $fromID ,
			"to_id" => $toID ,
			"date" => time() ,
			"worker_id" => $UserWorkerID
		 ) );

		//exit();
	}

	$gl = $portalDB->query( "select * from `pre-generated-id-packs` where `from_id` like '10.360.4010.%'" );
	foreach( $gl as &$gli ) {
		$gli[ "ex_data" ] = json_decode( $gli[ "ex_data" ] , true );
		$gli[ "ex_data.sum" ] = $gli[ "ex_data" ][ "sum" ] / 100 ;
	} unset( $gli );

	MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "%UT/bill-pack.generate.css" ) , array() );

	$t = '[]' ;
	$h = '[ { "t" : 1 } ]' ;
	$c = '[ { "n" : "ex_data" , "t" : "sl" , "h" : [ { "d" : "Нотариус" } ] , "f" : "notarius" } ,'.
		 '  { "n" : "from_id" , "t" : "i" , "h" : [ { "d" : "Год" } ] , "f" : "gyear" } ,'.
		 '  { "n" : "from_id" , "t" : "N" , "h" : [ { "d" : "Начало" } ] , "f" : "grange" } ,'.
		 '  { "n" : "to_id" , "t" : "N" , "h" : [ { "d" : "Конец" } ] , "f" : "grange" } ,'.
		 '  { "n" : "ex_data.sum" , "t" : "p" , "h" : [ { "d" : "Сумма" } ] } ,'.
		 '  { "n" : "date" , "t" : "dt" , "h" : [ { "d" : "Добавлено" } ] } ,'.
		 '  { "n" : "worker_id" , "t" : "ss" , "h" : [ { "d" : "Составил" } ] , "f" : "gworker" } ,'.
		 '  { "n" : "id" , "t" : "ss" , "h" : [ { "d" : "Скачать" } ] , "f" : "glnk" }'.
		 '  ]' ;

	$f = makeSimpleTable_init_filter();
	$f[ "notarius" ] = function( &$r , $c , &$v ) {
		return getNotarius( $v );
	};

	$f[ "gyear" ] = function( &$r , $c , &$v ) {
		return getGYear( $v );
	};

	$f[ "grange" ] = function( &$r , $c , &$v ) {
		return getGRange( $v );
	};

	$f[ "gworker" ] = function( &$r , $c , &$v ) use ( $tabWorkers ) {
		if ( isset( $tabWorkers[ $v ] ) ) {
			return $tabWorkers[ $v ];
		} else {
			return "< - >" ;
		}
	};

	$f[ "glnk" ] = function( &$r , $c , &$v ) {
		return "<a href=\"?download=".$v."\" class=\"bpg-lnk\" target=\"_blank\">скачать</a>" ;
	};

	echo makeSimpleTable( $t , $h , $c , $gl , array( "dr" => "dr-d dr-h" ) , $f );

	echo "<div class=\"form-container\"><form class=\"form\" action=\"?generate\" method=\"post\">
		<div>
			<label for=\"bpg-not\">Нотариус</label>
			<select id=\"bpg-not\" name=\"bpg-not\" class=\"bpg-notarius\">".makeSimpleSelectTagOptions( $nl , "value" , "key" )."</select>
		</div>
		<div>
			<label for=\"bpg-year\">Год</label><input type=\"text\" id=\"bpg-year\" name=\"bpg-year\" value=\"\" class=\"bpg-year\">
			<label for=\"bpg-from\">Начать с #</label><input type=\"text\" id=\"bpg-from\" name=\"bpg-from\" value=\"\" class=\"bpg-from\">
			<label for=\"bpg-to\"  >Закончить #</label><input type=\"text\" id=\"bpg-to\" name=\"bpg-to\" value=\"\" class=\"bpg-to\">
		</div>
		<div><input type=\"submit\" value=\"Сгенерировать\" class=\"btn3\"></div>
	</form></div>" ;


	closeHtml();
?>