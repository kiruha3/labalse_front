<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once ( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $MonthNames
	 * @var $dbConfigFull
	 * @var $UserWorkerID
	 */
	require_once ( "lconfig.php" );
	require_once ( "../ext-lib/rtf-gen.php" );
	require_once( '../cores/core.maindb.php' );


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

	$tabWorkers = $portalDB->table( "workers" , "id" );
	$tabSpecialities = $portalDB->query( "select `t2`.`id` , concat( `t1`.`index` , '.' , `t2`.`num` ) as `spec` from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id` = `t2`.`group` )" , "id" );
	$tabDepartments = $portalDB->table( "departments" , "id" );
	$UserWorkers = $portalDB->row( "select * from `workers` where `id` = ?" , "i" , $UserWorkerID );

	if ( isset( $dbConfig[ "report.order-3.boss" ] ) ) {
		$bossID = "report.order-3.boss" ;
	} else {
		$bossID = "org.boss" ;
	}
	$boss = NAMES_Format( NAMES_parse( $dbConfig[ $bossID ][ "name" ] ) , "%F1 %i.%o." );

	$res = $portalDB->query( "select `t1`.`name` , `t2`.`ind` from `workers` as `t1` , `departments` as `t2` where ( `t1`.`dep` = `t2`.`id` ) and ( `t1`.`actual` <=> 1 )" );
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

	$tabCaseCategory = $portalDB->table( "casecategory" , "id" );
	$res = $portalDB->row( "select `t1`.* , `t2`.`name` as `agency` , `t3`.`name` as `agent` from `matincoming` as `t1` , `agency` as `t2` , `agent` as `t3` where ( `t1`.`id` = ? ) and ( `t2`.`id` = `t1`.`from_agency` ) and ( `t3`.`id` = `t1`.`from_agent` )" , "s" , $t1ID );
	$res2 = $portalDB->query( "select `t3`.`exp_id` , `t3`.`spec_id` from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t2`.`mat_id` = ? ) and ( `t2`.`id` = `t3`.`ext_id` )" , false , "s" , $t1ID );
	$expNum = array();
	$experts = array();
	if ( $res2 !== false && count( $res2 ) > 0 ) {
		foreach ( $res2 as $r2 ) {
			$cw = $r2[ "exp_id" ];
			$cw = $tabWorkers[ $cw ];
			$expNum[] = implode( ' ' , matincomingNumberFullParts( $res[ "id" ] , $cw[ "dep" ] , $res[ "exp_type" ] ) );
			$experts[] = NAMES_Format( NAMES_parse( $cw[ "name" ] ) , "%F1 %i.%o." ).", ".$tabSpecialities[ $r2[ "spec_id" ] ][ "spec" ];
		}
	} else {
		$expNum[] = implode( ' ' , matincomingNumberFullParts( $res[ "id" ]  , null , $res[ "exp_type" ] ) );
	}

	$expNum = array_unique( $expNum );

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

	$doc->setFontName( FONT_TIMES_NEW_ROMAN )->setTextColor( "#000000" );

	/*$doc->setMainContext()->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "8pt" )
		->addTag( "caps" )->addTag( "b" )
		->addTextLine( inForm( $dbConfig[ "org.name.full.type" ] , 1 ) )
		->addTextLine( inForm( $dbConfig[ "org.name.full.name" ] , 1 ) )
		->addTextLine( inForm( $dbConfig[ "org.name.full.head" ] , 2 ) )
		->addTag( "b0" )->addTag( "caps0" );*/

	$onhbr = json_decode( ( $dbConfigFull[ "org.name.full.head" ][ "e-data" ] ) )->br ;

	$tbl = $doc->addTable();
		$r = $tbl->insertRow();
		$r->height = "15mm" ;
			$c = $r->insertCell();
			$c->width = "190mm" ;
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$c->setBorders( "ltrb" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "14pt" )
				->addTag( "caps" )->addText( breakLineByRule( inForm( $dbConfig[ "org.name.full.head" ] ) , $onhbr , "\r\n" ) )->addTag( "caps0" );

		$r = $tbl->insertRow();
		$r->height = "15mm" ;
			$c = $r->insertCell();
			$c->width = "190mm" ;
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$c->setBorders( "ltrb" , "s" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "14pt" )
				->addText( $dbConfig[ $bossID.".post" ][ "short_name" ]." " )
				->addTextLine( inForm( $dbConfig[ "org.name.full.type" ] , 1 )." " )
				->addText( inForm( $dbConfig[ "org.name.full.name" ] , 1 )." " );


		$r = $tbl->insertRow();
		$r->height = "15mm" ;
			$c = $r->insertCell();
			$c->width = "190mm" ;
			$c->verticalAlign = CELL_ALIGN_TOP ;
			$c->setBorders( "ltr" , "s" )->setBorders( "b" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "14pt" )
				->addText( "Поручаю производство экспертизы (исследования) №".( count( $expNum ) > 1 ? "№" : "" )." " );
			$doc->addTag( "b" )->addText( implode( ", " , $expNum ) )->addTag( "b0" );

		$r = $tbl->insertRow();
		$r->height = "65mm" ;
			if ( count( $experts ) == 0 ) {
				$c = $r->insertCell();
				$c->width = "190mm" ;
				$c->verticalAlign = CELL_ALIGN_CENTER ;
				$c->setBorders( "lr" , "s" )->setBorders( "tb" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "18pt" )
					->addTag( "b" )->addText( "Экспертиза не распределена" )->addTag( "b0" );
			} else {
				$c = $r->insertCell();
				$c->width = "40mm" ;
				$c->verticalAlign = CELL_ALIGN_TOP ;
				$c->setBorders( "l" , "s" )->setBorders( "trb" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "14pt" )
					->addText( "эксперту(ам) " );

				$c = $r->insertCell();
				$c->width = "150mm" ;
				$c->verticalAlign = CELL_ALIGN_TOP ;
				$c->setBorders( "r" , "s" )->setBorders( "ltb" , "none" );
				$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->setFontSize( "14pt" );
				$doc->addTag( "b" )->addText( implode( "\r\n" , $experts ) )->addTag( "b0" );
			}

		$r = $tbl->insertRow();
		$r->height = "10mm" ;
			$c = $r->insertCell();
			$c->width = "70mm" ;
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$c->setBorders( "lb" , "s" )->setBorders( "rt" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_RIGHT )->setFontSize( "14pt" )
				->addText( "_______ ".$t1Date );

			$c = $r->insertCell();
			$c->width = "120mm" ;
			$c->verticalAlign = CELL_ALIGN_CENTER ;
			$c->setBorders( "br" , "s" )->setBorders( "lt" , "none" );
			$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_CENTER )->setFontSize( "14pt" )
				->addText( NAMES_Format( NAMES_parse( $dbConfig[ $bossID ][ "name" ] ) , "%i.%o. %F1" ) );

		$doc->write();
