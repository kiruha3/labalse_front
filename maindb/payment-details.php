<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once ( "../core.php" );
	/**
	 * @var $LoginOk
	 * @var $portalDB
	 */
	require_once ( "lconfig.php" );
	require_once( '../cores/core.maindb.php' );
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

	//$res = RowAsArray( $con , "select ifnull( `group_id` , 0 ) as `grp` from `matincoming` where ( `id` = ".Int2SQL( $t1ID )." )" );
	$res = $portalDB->row( "select ifnull( `group_id` , 0 ) as `grp` from `matincoming` where ( `id` = ? )" , "s" , $t1ID );
	$t1IDL = array();
	if ( $res[ "grp" ] == 0 ) {
		$t1IDL[]= $t1ID ;
	} else {
		$t1Group = intval( $res[ "grp" ] );
		//$res = QueryAsArray( $con , "select `id` from `matincoming` where ( `group_id` <=> ".Int2SQL( $t1Group )." ) order by `id` " );
		$res = $portalDB->query( "select `id` from `matincoming` where ( `group_id` <=> ? ) order by `id`" , false , "i" , $t1Group );
		foreach ( $res as &$r ) {
			$t1IDL[]= $r[ "id" ];
		} unset( $r );
	}

	$q = "select
			`t1`.`id` ,
			`t1`.`exp_type` ,
			`t1`.`ex_data_3` ,
			`t1`.`ex_data_4` ,
			`t2`.`dep_id` ,
			`t4`.`name` as `agency` ,
			`t5`.`name` as `agent`
		from
			`matincoming` as `t1` ,
			`matincominglvl2` as `t2` ,
			`expertize` as `t3` ,
			`agency` as `t4` ,
			`agent` as `t5`
		where
			( `t1`.`id` in ( ?* ) ) and
			( `t1`.`id` = `t2`.`mat_id` ) and
			( `t2`.`id` = `t3`.`ext_id` ) and
			( `t1`.`from_agency` = `t4`.`id` ) and
			( `t1`.`from_agent` = `t5`.`id` )
		order by `t1`.`id` asc" ;

	$res = $portalDB->query( $q , false , "*s" , $t1IDL );

	$expNumL = array();
	$expDataL = array();
	foreach ( $res as &$r ) {
		$expNum = implode( ' ' , matincomingNumberFullParts( $r[ "id" ] , $r[ "dep_id" ] , $r[ "exp_type" ] ) );
		$expNumL[]= $expNum ;

		$expDataL[]= "по ".$r[ "ex_data_4" ]." назначенной ".$r[ "ex_data_3" ]." ".$r[ "agent" ]." ".$r[ "agency" ];
	} unset( $r );
	$expNumL = array_unique( $expNumL );
	$expDataL = array_unique( $expDataL );

	header( "Content-Type: application/rtf" );
	header( "Content-Disposition: attachment;filename=\"На конверт с документами для оплаты ".date( "Y.m.d H-i" , time() ).".rtf\"" );

	$doc = new RTFDocument();

	$doc->paperFormat = PAPER_SIZE_C4_PORTRAIT ;
	$doc->margins = "5mm" ;
	$doc->headerMargin = "5mm" ;

	$doc->setMainContext();
	$doc->setFontName( FONT_TIMES_NEW_ROMAN );

	$tbl = $doc->addTable();

		$r = $tbl->insertRow();
		$r->height = "5mm" ;

			$c = $r->insertCell();
			$c->width = "5mm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "208.5mm" ;
			$c->borders = array();
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setTextColor( "#ff0000" )->setFontSize( "10pt" )
				->addText( "Внимание!   Документы на оплату экспертизы            Внимание!   Документы на оплату экспертизы" );


			$c = $r->insertCell();
			$c->width = "5mm" ;
			$c->borders = array();


		$r = $tbl->insertRow();
		$r->height = "303mm" ;

			$c = $r->insertCell();
			$c->width = "5mm" ;
			$c->borders = array();
			$c->textDirection = "btlr" ;
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setTextColor( "#ff0000" )->setFontSize( "10pt" )
				->addText( "Внимание!   Документы на оплату экспертизы               Внимание!   Документы на оплату экспертизы               Внимание!   Документы на оплату экспертизы" );

			$c = $r->insertCell();
			$c->width = "208.5mm" ;
			$c->borders = array(
				"l" => array( "t" => "dash" , "c" => $doc->addColor( "#548dd4" ) ) ,
				"t" => array( "t" => "dash" , "c" => $doc->addColor( "#548dd4" ) ) ,
				"r" => array( "t" => "dash" , "c" => $doc->addColor( "#548dd4" ) ) ,
				"b" => array( "t" => "dash" , "c" => $doc->addColor( "#548dd4" ) )
			);
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setTextColor( "#ff0000" )->setFontSize( "18pt" )
				->addTag( "li" , "3cm" )->addTag( "ri" , "3cm" )
				->addTag( "caps" )->addTag( "expndtw" , "9pt" )
				->addTextLine( "Документы на оплату экспертиз".( count( $expNumL ) > 1 ? "" : "ы" ) )->addTextLine()
				->addTag( "caps0" )->addTag( "expndtw0" );

			$doc->setTextColor( "#000000" )->setFontSize( "14pt" )->addText( "№".( count( $expNumL ) > 1 ? "№" : "" ) );
			$enSep = " " ;
			foreach ( $expNumL as $en ) {
				$doc->addText( $enSep )->addTag( "ul" )->addText( $en )->addTag( "ul0" );
				$enSep = " , " ;
			}
			$doc->setFontSize( "14pt" )->addTextLine()->addTextLine()->addTextLine()->addTextLine();

			$doc->setTextAlign( TEXT_ALIGN_JUSTIFIED );
			foreach ( $expDataL as $ed ) {
				$ed = str_ireplace( "судья" , "судьи" , $ed );
				$ed = str_ireplace( "определение" , "определением" , $ed );
				$ed = str_ireplace( "постановление" , "постановлением" , $ed );
				$ed = str_ireplace( "\r" , " " , $ed );
				$ed = str_ireplace( "\n" , " " , $ed );
				$ed = str_ireplace( "  " , " " , $ed );
				$doc->addTextLine( $ed );
			}
			$doc->addTextLine()->addTextLine();

			$doc->setTextAlign( TEXT_ALIGN_LEFT )->addParagraphBorders( "ltbr" , "s" , "#548dd4" , "1.5pt" , "2mm" )
				->addText( "- Счет № " )->setTextColor( "#00c000" )->addText( "XXXX" )->setTextColor( "#000000" )->addText( " от " )->setTextColor( "#00c000" )->addText( "ДД.ММ.ГГГГ" )->setTextColor( "#000000" )->addTextLine( "г." )
				->addTextLine( "- Квитанция" )
				->addTextLine( "- Заявление" )
				->addParagraphBorders( "ltbr" , "none" )->addTag( "li0" )->addTag( "ri0" );


			$c = $r->insertCell();
			$c->width = "5mm" ;
			$c->borders = array();
			$c->textDirection = "tbrl" ;
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setTextColor( "#ff0000" )->setFontSize( "10pt" )
				->addText( "Внимание!   Документы на оплату экспертизы               Внимание!   Документы на оплату экспертизы               Внимание!   Документы на оплату экспертизы" );

		$r = $tbl->insertRow();
		$r->height = "5mm" ;

			$c = $r->insertCell();
			$c->width = "5mm" ;
			$c->borders = array();

			$c = $r->insertCell();
			$c->width = "208.5mm" ;
			$c->borders = array();
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setTextColor( "#ff0000" )->setFontSize( "10pt" )
				->addText( "Внимание!   Документы на оплату экспертизы            Внимание!   Документы на оплату экспертизы" );

			$c = $r->insertCell();
			$c->width = "5mm" ;
			$c->borders = array();

		$doc->setMainContext()->addTag( "v" )->addTextLine()->addTag( "v0" );

	$doc->write();
