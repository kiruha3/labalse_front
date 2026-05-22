<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once ( '../core.php' );
	/**
	 * @var TDB $portalDB
	 * @var $LoginOk
	 * @var $UserWorkerID
	 * @var $dbConfig
	 * @var $MonthNames
	 */
	require_once ( '../barcode.php' );
	require_once ( 'lconfig.php' );
	require_once( '../cores/core.maindb.php' );
	require_once ( '../ext-lib/rtf-gen.php' );

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( isset( $_REQUEST[ 'id' ] ) ) {
		$t1ID = getCharID( $_REQUEST[ 'id' ] , DOCTYPE_MATINCOMING );
	} else {
		echo 'ДоZZвидания!' ;
		exit();
	}

	$UserWorker = $portalDB->row( "select * from `workers` where `id` = ?" , 'i' , $UserWorkerID );

	$res = $portalDB->query( "select `t1`.`name` , `t1`.`dep` from `workers` as `t1` where ( `t1`.`actual` <=> 1 )" );

	$bossID = "org.boss" ;
	if ( isset( $dbConfig[ "report.order-4.boss" ] ) ) {
		$bossID = "report.order-4.boss" ;
	}
	if ( isset( $_REQUEST[ 'boss-raw' ] ) ) {
		$boss = trim( $_REQUEST[ 'boss-raw' ] );
	} else
	if ( isset( $_REQUEST[ 'no-boss' ] ) ) {
		$boss = '___________________' ;
	} else {
		$boss = NAMES_Format( NAMES_parse( $dbConfig[ $bossID ][ "name" ] ) , "%F1 %i.%o." );
	}

	$bossID = $transferBossID = "org.boss" ;
	if ( isset( $dbConfig[ "report.order-4.boss" ] ) ) {
		$bossID = $transferBossID = "report.order-4.boss" ;
	}
	if ( isset( $dbConfig[ "report.order-4.s1.transfer.boss" ] ) ) {
		$transferBossID = "report.order-4.s1.transfer.boss" ;
	}
	if ( isset( $_REQUEST[ 'boss-raw' ] ) ) {
		$boss = trim( $_REQUEST[ 'boss-raw' ] );
	} else
	if ( isset( $_REQUEST[ 'no-boss' ] ) ) {
		$boss = '___________________' ;
	} else {
		$boss = NAMES_Format( NAMES_parse( $dbConfig[ $bossID ][ "name" ] ) , "%F1 %i.%o." );
	}

	if ( isset( $_REQUEST[ 'tr-boss-raw' ] ) ) {
		$transferBoss = trim( $_REQUEST[ 'tr-boss-raw' ] );
	} else
	if ( isset( $_REQUEST[ 'tr-no-boss' ] ) ) {
		$transferBoss = '___________________' ;
	} else {
		$transferBoss = NAMES_Format( NAMES_parse( $dbConfig[ $transferBossID ][ "name" ] ) , "%F1 %i.%o." );
	}

	$cfgTransferNoExp = false ;
	if ( isset( $dbConfig[ 'report.order-4.s1.transfer.no-exp' ] ) && $dbConfig[ 'report.order-4.s1.transfer.no-exp' ] == 1 ) {
		$cfgTransferNoExp = true ;
	}

	$ndct = array();
	foreach ( $res as $r ) {
		$n = NAMES_Format( NAMES_parse( $r[ 'name' ] ) , '%F1 %i.%o.' );
		$nt = NAMES_Format( NAMES_parse( $r[ 'name' ] ) , '%F1 %i.%o.' );
		$nr = NAMES_Format( NAMES_parse( $r[ 'name' ] ) , '%F3 %i.%o.' );
		$ndct[ $n ] = array(
			't' => $nt ,
			'r' => $nr ,
			'i' => $r[ 'dep' ]
		);
	}

	$res = $portalDB->row( "select `t1`.* , `t2`.`name` as `agency` , `t3`.`name` as `agent` from `matincoming` as `t1` , `agency` as `t2` , `agent` as `t3` where ( `t1`.`id` = ? ) and ( `t2`.`id` = `t1`.`from_agency` ) and ( `t3`.`id` = `t1`.`from_agent` )" , 's' , $t1ID );
	$resEv = $portalDB->query( "select `t1`.`descr` from `evidence` as `t1` where ( `t1`.`ext_id` = ? )" , false , 's' , $t1ID );
	if ( $resEv !== false ) {
		$resEv = array_column( $resEv , 'descr' );
		$resEv = implode( ' ; ' , $resEv );
	} else {
		$resEv = '<ОШИБКА>' ;
	}

	$m = array();
	$n = preg_match( '/([А-Яа-яёЁ]+ [А-Яа-яёЁ]\.[А-Яа-яёЁ]\.)/' , $res[ 'ex_data_6' ] , $m );
	if ( $n == 1 ) {
		$n = $m[ 1 ];
		if ( isset( $ndct[ $n ] ) ) {
			$nt = $ndct[ $n ][ 't' ];
			$nr = $ndct[ $n ][ 'r' ];
			$depID = $ndct[ $n ][ 'i' ];
		} else {
			$nt = $n ;
			$nr = $n ;
			$depID = null ;
		}

	} else {
		$nt = "  " ;
		$nr = "  " ;
		$depID = null ;
	}

	$t1Date = explode( '-' , date( 'd-m-Y' , strtotime( $res[ 'date' ] ) ) );

	$t1Date = $t1Date[ 0 ].' '.inForm( $MonthNames[ intval( $t1Date[ 1 ] ) - 1 ] , 2 ).' '.$t1Date[ 2 ].' г.' ;

	function packText( $t ) {
		$res = trim( $t );
		$res = preg_replace( '/\s+/' , ' ' , $res );
		$res = preg_replace( '/,([а-я])/i' , ', ${1}' , $res );
		return $res ;
	}

	header( 'Content-Type: application/rtf' );
	header( 'Content-Disposition: attachment;filename="Поручение '.date( 'Y.m.d H-i' , time() ).'.rtf"' );

	$doc = new RTFDocument();

	$doc->paperFormat = PAPER_SIZE_A5_LANDSCAPE ;
	if ( isset( $dbConfig[ 'report.order-4.page.format' ] ) ) {
		$doc->paperFormat = $dbConfig[ 'report.order-4.page.format' ];
	}

	if ( isset( $_REQUEST[ 'pf' ] ) ) {
		$doc->paperFormat = $_REQUEST[ 'pf' ];
	}

	$doc->margins = '10mm' ;

	$doc->setFontName( FONT_CALIBRI )->setTextColor( '#000000' );

	$doc->setMainContext()->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( '8pt' )
		->addTag( 'caps' )->addTag( 'b' )
		->addTextLine( inForm( $dbConfig[ 'org.name.full.type' ] , 1 ) )
		->addTextLine( inForm( $dbConfig[ 'org.name.full.name' ] , 1 ) )
		->addTextLine( inForm( $dbConfig[ 'org.name.full.head' ] , 2 ) )
		->addTag( 'b0' )->addTag( 'caps0' );

	$tbl = $doc->addTable();

		$r = $tbl->insertRow();
		$r->height = '4mm' ;

			$c = $r->insertCell();
			$c->width = '140mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 'none' )->setBorders( 'b' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( '8pt' )
				->addText( $dbConfig[ 'org.address' ] );

			$pfd = array();
			if ( isset( $dbConfig[ 'org.phone' ] ) && !is_null( $dbConfig[ 'org.phone' ] ) ) {
				$pfd[]= 'тел.: '.$dbConfig[ 'org.phone' ];
			}
			if ( isset( $dbConfig[ 'org.fax' ] ) && !is_null( $dbConfig[ 'org.fax' ] ) ) {
				$pfd[]= 'факс: '.$dbConfig[ 'org.fax' ];
			}
			if ( count( $pfd ) == 2 && $dbConfig[ 'org.phone' ] == $dbConfig[ 'org.fax' ] ) {
				$pfd = [ 'тел./факс: '.$dbConfig[ 'org.phone' ] ];
			}

			$c = $r->insertCell();
			$c->width = '50mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 'none' )->setBorders( 'b' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( '8pt' )
				->addText( implode( ', ' , $pfd ) );

		$r = $tbl->insertRow();
		$r->height = '6mm' ;

			$c = $r->insertCell();
			$c->width = '125mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'lt' , 's' )->setBorders( 'br' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( '12pt' )
				->addText( 'Поручаю производство экспертизы (экспертного исследования) №' );

			$c = $r->insertCell();
			$c->width = '40mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'tb' , 's' )->setBorders( 'rl' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( '16pt' )
				->addText( implode( ' ' , matincomingNumberFullParts( $t1ID , $depID , $res[ 'exp_type' ] ) ) );

			$c = $r->insertCell();
			$c->width = '25mm' ;
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$c->setBorders( 'tb' , 's' )->setBorders( 'lr' , 'none' );
			$c->vMerge = CELL_MERGE_FIRST ;

			$t1IDs = getCharIDStructure( $t1ID );
			$t1IDs[ 't' ] = '0420' ;
			$bc = generateBarcode( mkCharID( $t1IDs ) , false , BARCODE_TYPE_DATAMATRIX );
			$bch = $bcw = ceil( unitConvert( '1.95cm' , 'tw' ) ).'tw' ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->addImagePNG( $bc[ 'raw' ] , $bc[ 'w' ] , $bc[ 'h' ] , $bcw , $bch );


		$d1d2Text = array();

		$showExpNameArea = false ;
		if ( isset( $dbConfig[ 'report.order-4.s1.exp-name-area.show' ] ) && $dbConfig[ 'report.order-4.s1.exp-name-area.show' ] == 1 ) {
			$showExpNameArea = true ;
		}

		if ( $showExpNameArea ) {
			if ( isset( $dbConfig[ 'report.order-4.s1.exp-name-area.merge-next' ] ) && $dbConfig[ 'report.order-4.s1.exp-name-area.merge-next' ] == 1 ) {
				$d1d2Text[]= '__________________________________________' ;
			} else {
				$r = $tbl->insertRow();
				$r->height = '10mm' ;

					$c = $r->insertCell();
					$c->width = '165mm' ;
					$c->verticalAlign = CELL_ALIGN_CENTER ;
					$c->setBorders( 'l' , 's' )->setBorders( 'trb' , 'none' );
					$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( '12pt' )
						->addText( '__________________________________________' );

					$c = $r->insertCell();
					$c->width = '25mm' ;
					$c->setBorders( 'ltrb' , 'none' );
					$c->vMerge = CELL_MERGE_PRECEDING ;
			}
		}

		$date1AlterLabel = false ;
		if ( isset( $dbConfig[ 'report.order-4.s1.date-1.replace-label' ] ) && $dbConfig[ 'report.order-4.s1.date-1.replace-label' ] ) {
			$date1AlterLabel = $dbConfig[ 'report.order-4.s1.date-1.replace-label' ] ;
		}

		$d1d2Text[]= 'в срок до ____'.( $date1AlterLabel ? $date1AlterLabel : ' календарных дней' );
		if ( !( isset( $dbConfig[ 'report.order-4.s1.date-2.hide' ] ) && $dbConfig[ 'report.order-4.s1.date-2.hide' ] == 1 ) ) {
			$d1d2Text[]= 'в срок, установленный судом эксперту ____________' ;
		}

		$r = $tbl->insertRow();
		$r->height = '10mm' ;

			$c = $r->insertCell();
			$c->width = '165mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'l' , 's' )->setBorders( 'trb' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( '12pt' )
				->addText( implode( ', ' , $d1d2Text ) );

			$c = $r->insertCell();
			$c->width = '25mm' ;
			$c->setBorders( 'ltrb' , 'none' );
			$c->vMerge = CELL_MERGE_PRECEDING ;

		$r = $tbl->insertRow();
		$r->height = '15mm' ;

			$c = $r->insertCell();
			$c->width = '165mm' ;
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$c->setBorders( 'lb' , 's' )->setBorders( 'tr' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( '12pt' )
				->addText( $t1Date )->addText( '___________ '.$boss );


			$c = $r->insertCell();
			$c->width = '25mm' ;
			$c->setBorders( 'ltrb' , 'none' );
			$c->vMerge = CELL_MERGE_PRECEDING ;

		$r = $tbl->insertRow();
		$r->height = '15mm' ;

			$exData3 = packText( $res[ 'ex_data_3' ].' '.$res[ 'agent' ].' '.$res[ 'agency' ] );

			$c = $r->insertCell();
			$c->width = '190mm' ;
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$c->setBorders( 'ltb' , 's' )->setBorders( 'r' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_JUSTIFIED )
				->setFontSize( '9pt' )->addTag( 'b' )->addTag( 'ul' )->addText( 'основание: ' )->addTag( 'ul0' )->addTag( 'b0' )
				->setFontSize( ( strlen( $exData3 ) > 130 ? '7pt' : '10pt' ) )->addText( $exData3 );

			/*$c = $r->insertCell();
			$c->width = '25mm' ;
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$c->setBorders( 'lr' , 'none' )->setBorders( 'tb' , 's' );
			$c->vMerge = CELL_MERGE_PRECEDING ;*/

		$r = $tbl->insertRow();
		$r->height = '2mm' ;

			$c = $r->insertCell();
			$c->width = '190mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( '1pt' );

		$r = $tbl->insertRow();
		$r->height = '10mm' ;

			$exData4 = packText( $res[ 'ex_data_4' ] );
			$c = $r->insertCell();
			$c->width = '190mm' ;
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$c->setBorders( 'ltrb' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_JUSTIFIED )
				->setFontSize( '9pt' )->addTag( 'b' )->addTag( 'ul' )->addText( 'материалы: ' )->addTag( 'ul0' )->addTag( 'b0' )
				->setFontSize( ( strlen( $exData4 ) > 200 ? '8pt' : '10pt' ) )->addText( $exData4 );

		$r = $tbl->insertRow();
		$r->height = '10mm' ;

			$evDescr = packText( $resEv );

			$c = $r->insertCell();
			$c->width = '190mm' ;
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$c->setBorders( 'ltrb' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_JUSTIFIED )
				->setFontSize( '9pt' )->addTag( 'b' )->addTag( 'ul' )->addText( 'Объекты исследования: ' )->addTag( 'ul0' )->addTag( 'b0' )
				->setFontSize( ( strlen( $exData4 ) > 200 ? '8pt' : '10pt' ) )->addText( $evDescr );


		$r = $tbl->insertRow();
		$r->height = '7mm' ;

			$c = $r->insertCell();
			$c->width = '75mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
				->setFontSize( '9pt' )->addText( 'передаются: ' )->setFontSize( '14pt' )->addText( $cfgTransferNoExp ? '' : $nr );

			$c = $r->insertCell();
			$c->width = '45mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 's' )->setBorders( 'b' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( '14pt' )
				->addText( $t1Date );

			$c = $r->insertCell();
			$c->width = '70mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( '14pt' )
				->addText( $transferBoss );

		$r = $tbl->insertRow();
		$r->height = '7mm' ;

			$c = $r->insertCell();
			$c->width = '75mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
				->setFontSize( '9pt' )->addText( 'материалы получил(а): ' );

			$c = $r->insertCell();
			$c->width = '45mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 's' )->setBorders( 'b' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( '14pt' )
				->addText( $t1Date );

			$c = $r->insertCell();
			$c->width = '70mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 's' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( '14pt' )
				->addText( $cfgTransferNoExp ? '' : $nt );

		$r = $tbl->insertRow();
		$r->height = '7mm' ;

			$c = $r->insertCell();
			$c->width = '190mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
				->setFontSize( '9pt' )->addText( 'Движение материалов ( постановление / определение / дело / доп. материалы и т.д. ) и объектов исследования: ' );


		$rowsCount = 2 ;
		if ( isset( $dbConfig[ 'report.order-4.s1.rows.count' ] ) ) {
			$rowsCount = intval( $dbConfig[ 'report.order-4.s1.rows.count' ] , 10 );
		}

		for( $i = 0 ; $i < $rowsCount ; $i++ ) {
			$r = $tbl->insertRow();
			$r->height = '5mm' ;

				$c = $r->insertCell();
				$c->width = '100mm' ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( 'ltr' , 's' )->setBorders( 'b' , 'none' );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
					->setFontSize( '9pt' )->addText( 'получил(а)' );

				$c = $r->insertCell();
				$c->width = '25mm' ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( 'ltr' , 's' )->setBorders( 'b' , 'none' );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( '6pt' );

				$c = $r->insertCell();
				$c->width = '65mm' ;
				$c->verticalAlign = CELL_ALIGN_BOTTOM ;
				$c->setBorders( 'ltr' , 's' )->setBorders( 'b' , 'dot' );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( '6pt' )
					->addText( 'Ф.И.О.' );

			$r = $tbl->insertRow();
			$r->height = '5mm' ;

				$c = $r->insertCell();
				$c->width = '100mm' ;
				$c->verticalAlign = CELL_ALIGN_TOP ;
				$c->setBorders( 'lbr' , 's' )->setBorders( 't' , 'none' );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
					->setFontSize( '9pt' )->addText( 'передал(а)' );

				$c = $r->insertCell();
				$c->width = '25mm' ;
				$c->verticalAlign = CELL_ALIGN_TOP ;
				$c->setBorders( 'lbr' , 's' )->setBorders( 't' , 'none' );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( '6pt' )
					->addText( 'дата' );

				$c = $r->insertCell();
				$c->width = '65mm' ;
				$c->verticalAlign = CELL_ALIGN_TOP ;
				$c->setBorders( 'lbr' , 's' )->setBorders( 't' , 'dot' );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( '6pt' )
					->addText( 'подпись' );
		}

		/*$r = $tbl->insertRow();
		$r->height = '10mm' ;

			$c = $r->insertCell();
			$c->width = '50mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )
				->setFontSize( '9pt' )->addText( 'Перечисленные выше материалы и экспертизу получил: ' );

			$c = $r->insertCell();
			$c->width = '60mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( '16pt' )->setTextColor( '#888' )
				->addTag( 'u171' )->addText( '"__' )->addTag( 'u187' )->addText( '" _________ '.substr( date( 'Y' , time() ) , 0 , 3 ).'__г.' )->setTextColor( '#000' );

			$c = $r->insertCell();
			$c->width = '80mm' ;
			$c->verticalAlign = CELL_ALIGN_BOTTOM ;
			$c->setBorders( 'ltrb' , 'none' );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( '16pt' )
				->addText( '___________ '.NAMES_Format( NAMES_parse( $UserWorker[ 'name' ] ) , '%F1 %i.%o.' ) );*/

		$doc->setMainContext();
		$doc->addTag( 'v' )->addTextLine()->addTag( 'v0' );

		$doc->write();
