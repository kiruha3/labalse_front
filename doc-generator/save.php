<?php
	set_time_limit( 0 );
	error_log( 'DBG SAVE : begin' );
	include_once( '../core.php' );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserID
	 * @var $dbConfig
	 */

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		exit();
	}

	if ( isset( $_REQUEST[ 'tmpl' ] ) ) {
		$tmplID = intval( $_REQUEST[ 'tmpl' ] , 10 );
		$tmplData = $portalDB->simpleRow( 'doc-templates' , $tmplID );
		if ( $tmplData === false ) {
			error_log( 'no doc-template data for id '.$tmplID );
			exit();
		}
	} else {
		error_log( 'no template id (param "tmpl")' );
		exit();
	}

	if ( isset( $_REQUEST[ 'id' ] ) ) {
		$rootID = intval( $_REQUEST[ 'id' ] , 10 );
	} else {
		error_log( 'no root id (param "id")' );
		exit();
	}

	if ( isset( $_REQUEST[ 'doc' ] ) ) {
		$docID = trim( $_REQUEST[ 'doc' ] );
	} else {
		error_log( 'no doc id (param "doc")' );
		exit();
	}

	if ( isset( $_REQUEST[ 'data' ] ) ) {
		$docData = iconv( 'utf8' , 'cp1251' , trim( $_REQUEST[ 'data' ] ) );
	} else {
		var_dump( $_REQUEST );
		error_log( 'no data (param "data")' );
		exit();
	}

	if ( strlen( $docData ) <= 65536 ) {
		$wData = base64_encode( gzcompress( $docData ) );
		if ( strlen( $wData ) >= strlen( $docData ) ) {
			$wData = $docData ;
			$placeIndex = 0 ;
		} else {
			$placeIndex = 2 ;
		}
	} else {
		$wData = gzcompress( $docData );
		$placeIndex = 4 ;
	}
	
	$params = array(
		'tmpl_id'     => $tmplID ,
		'root_id'     => $rootID ,
		'doc_id'      => $docID ,
		'user_id'     => $UserID
	);
	
	error_log( 'DBG SAVE : BEFORE TRANSACTION' );
	
	$r = $portalDB->simpleRow( 'doc-generator-data' , $params );
	$portalDB->rawQuery( 'start transaction' );
	if ( $r !== false ) {
		$portalDB->updateRow( 'doc-generator-data' , array(
			'id'          => $r[ 'id' ] ,
			'place_index' => $placeIndex ,
			'time_edited' => time()
		) );
		$tgtRow = $r[ 'id' ];
	} else {
		$params[ 'time_created' ] = time();
		$params[ 'place_index' ] = $placeIndex ;
		$portalDB->insertRow( 'doc-generator-data' , $params );
		$tgtRow = $portalDB->lastInsertID();
	}
	error_log( 'DBG SAVE : ' );
	
	switch( $placeIndex ) {
		case 0 :
		case 2 :
			$portalDB->updateRow( 'doc-generator-data' , array(
				'id'          => $tgtRow ,
				'doc_ex_data' => $wData
			) );
			break ;
			
		case 4 :
			$fn = bin2hex( $docID ).'.json.gz' ;
			$portalDB->updateRow( 'doc-generator-data' , array(
				'id'          => $tgtRow ,
				'doc_ex_data' => $fn
			) );
			file_put_contents( './doc_ex_data/'.$fn , $wData );
			break ;
	}
	
	$portalDB->rawQuery( 'commit' );
	
	$v = print_r( $_POST , 1 );
	$res = new DOMDocument( '1.0' , 'windows-1251' );   //в методы передавать строки в utf-8, преобразование автоматически при сохранении через saveXML

	$resultNode = $res->createElement( 'result' );

		//$resultNode->appendChild( $res->createCDATASection( $v ) );

	$res->appendChild( $resultNode );
	echo $res->saveXML();
