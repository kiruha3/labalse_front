<?php
	/*
		Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
		Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
		copyright (c) Пекшев Петр Александрович, 2008
	*/

	include_once( '../core.php' );
	require_once ( '../barcode.php' );
	/**
	 * @var $dbConfigFull
	 * @var $LoginOk
	 * @var TDB $portalDB
	 * @var $UserThemeLoc
	 * @var $dbConfig
	 * @var $UserID
	 */
	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( !isset( $_REQUEST[ 'tmpl' ] ) ) {
		exit();
	} else {
		$tmplID = intval( $_REQUEST[ 'tmpl' ] );
	}

	$tmplData = $portalDB->row( "select * from `doc-templates` where `id` = ?" , "i" , $tmplID );
	if ( $tmplData === false ) {
		exit();
	}

	if ( $tmplID == 16 ) {
		$tmplData[ 'tmpl' ] = iconv( 'utf8' , 'cp1251' , file_get_contents( './test/gsa/test--dyna-con-4--chusova.xml' ) );
	}

	$dfn = sys_get_temp_dir().'/docTemplateExport--'.time().'-'.rand( 0 , 65535 ).'.tar' ;
	if ( file_exists( $dfn ) ) {
		unlink( $dfn );
	}

	$tmplSaveParams = array_intersect_key( $tmplData , array_fill_keys( strexp( '{{,short_,file_}name,classes,data_bank_set,proceeding,preview_mode,download_mode,download_types}' ) , 1 ) );

	$df = new PharData( $dfn );
	$df->startBuffering();
	$df->addFromString( 'param' , serialize( $tmplSaveParams ) );
	foreach( strexp( '{tmpl,ext-var,triggers}' ) as $k ) {
		$df->addFromString( $k , $tmplData[ $k ] );
	}

	$df->stopBuffering();

	function sendFile( $tfn , $name ) {
		$tfs = filesize( $tfn );
		$elementSize = 65536 ;

		$ct = "application/octet-stream" ;
		//$ct = "application/x-tar" ;

		$fh = fopen( $tfn , "rb" );
		if ( $fh === false ) {
			event( "dbg.error" , "file open error" , '' );
			return ;
		}

		header( "HTTP/1.1 200 OK" );
		header( "Content-Length: ".$tfs );
		header( "Last-Modified: ".date( "D, d M Y H:i:s" , time() )." GMT" );
		header( "Date: ".date( "D, d M Y H:i:s" , time() )." GMT" );
		header( "ETag: \"".rand()."*".time()."\"" );
		header( "Content-Type: ".$ct );
		header( "Accept-Ranges: none" );
		header( "Connection: Keep-Alive" );
		header( "Keep-Alive:\"timeout=5, max=100\"" );
		header( "Content-Disposition: inline; filename=\"".$name."\"" );

		set_time_limit( 0 );
		while( !feof( $fh ) && connection_status() == 0 ) {
			print( fread( $fh , $elementSize ) );
			flush();
			ob_flush();
		}
		fclose( $fh );
	}

	$dfnz = $dfn.'.gz' ;
	if ( file_exists( $dfnz ) ) {
		unlink( $dfnz );
	}

	$dfz = $df->compress( Phar::GZ , '.tar.gz' );

	sendFile( $dfnz , 'Шаблон - '.$tmplData[ 'name' ].'.ais-tmpl' );

	unset( $df );
	PharData::unlinkArchive( $dfn );

	unset( $dfz );
	PharData::unlinkArchive( $dfnz );