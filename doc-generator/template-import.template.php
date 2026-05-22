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

	if ( !isset( $_FILES[ 'ais-tmpl-pack-file' ] ) ) {
		ErrorPageAndExit( 'Ошибка загрузки файла: файл не передавался' );
		exit();
	}

	$ufd = $_FILES[ 'ais-tmpl-pack-file' ];

	if ( isset( $ufd[ 'error' ] ) && $ufd[ 'error' ] != 0 ) {
		$emm = array(
			UPLOAD_ERR_INI_SIZE   => 'Размер принятого файла превысил максимально допустимый размер.' ,
			UPLOAD_ERR_FORM_SIZE  => 'Размер принятого файла превысил максимально допустимый размер.' ,
			UPLOAD_ERR_PARTIAL    => 'Загружаемый файл был получен только частично.' ,
			UPLOAD_ERR_NO_FILE    => 'Файл не был загружен.' ,
			UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка.' ,
			UPLOAD_ERR_CANT_WRITE => 'Не получилось записать файл на диск.' ,
			UPLOAD_ERR_EXTENSION  => 'Сбой вызван расширением.'
		);

		ErrorPageAndExit( 'Во врема загрузки файла произошла ошибка: ('.$ufd[ 'error' ].') '.( isset( $emm[ $ufd[ 'error' ] ] ) ? $emm[ $ufd[ 'error' ] ] : '' ) );
		exit();
	}

	$dfnz = sys_get_temp_dir().'/docTemplateImport--'.time().'-'.rand( 0 , 65535 ).'.tar.gz' ;
	if ( file_exists( $dfnz ) ) {
		unlink( $dfnz );
	}

	rename( $ufd[ 'tmp_name' ] , $dfnz );

	$dfz = new PharData( $dfnz );
	$tmplLoadParams = $dfz[ 'param' ]->getContent();
	$tmplData = unserialize( $tmplLoadParams );
	$tmplData[ 'user_id' ] = $UserID ;
	foreach( strexp( '{tmpl,ext-var,triggers}' ) as $k ) {
		$tmplData[ $k ] = $dfz[ $k ]->getContent();
	}
	$tmplData[ 'filter_rules' ] = '{"env:tmpl-list-name":["expertize"]}' ;
	$portalDB->insertRow( 'doc-templates' , $tmplData );

	unset( $dfz );
	PharData::unlinkArchive( $dfnz );