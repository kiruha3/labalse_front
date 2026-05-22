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

	if ( !isset( $_REQUEST[ 'id' ] ) ) {
		exit();
	} else {
		$docID = trim( $_REQUEST[ 'id' ] );
	}

	$docGeneratorData = $portalDB->simpleRow( 'doc-generator-data' , array(
		'doc_id'  => $docID ,
		'user_id' => $UserID ,
	) );

	if ( $docGeneratorData === false ) {
		exit();
	}

	$tmplID = $docGeneratorData[ 'tmpl_id' ];
	$doc_id = $docGeneratorData[ 'root_id' ];
	//$tmplID = 7 ;
	//$doc_id = 20 ;

	$tmplData = $portalDB->row( "select * from `doc-templates` where `id` = ?" , "i" , $tmplID );
	if ( $tmplData === false ) {
		exit();
	}

	require_once( '../maindb/lconfig.php' );
	require_once( '../cores/core.maindb.php' );
	//require_once( '../maindb/request.core.php' );
	require_once( '../cores/data-bank.php' );
	require_once( './doc-generator.core.php' );

	$docVar = fillDataBank2(
		array(
			'req:id' => $doc_id ,
			'tmpl-data' => $tmplData
		)
	);

	$xmlTmpl = new DOMDocument();
	if ( $_REQUEST[ 'help' ] ) {
		$xmlTmpl->load( './tmpl/help.xml' );
	} else {
		$xmlTmpl->loadXML( iconv( 'cp1251' , 'utf8' , $tmplData[ 'tmpl' ] ) );
	}
	normalizeTemplate( $xmlTmpl );

	$xmlTmplXPATH = new DOMXPath( $xmlTmpl );
	$varUserBankList = $xmlTmplXPATH->query( '//var[substring(@name , 1 , 10 )="user-bank:"]' );
	$userBankNameList = array();
	for( $i = 0 ; $i < $varUserBankList->length ; $i++ ) {
		$varUserBank = $varUserBankList->item( $i );
		$userBankName = substr( $varUserBank->getAttribute( 'name' ) , 10 );
		$userBankNameList[ $userBankName ] = $userBankName ;
	}

	$userBankData = $portalDB->query(
		"select
			`t1`.`name` ,
			`t1`.`description` ,
			`t1`.`type_data` ,
			`t2`.`data`
		from
			`data-collection-description` as `t1` ,
			`data-collection-data` as `t2`
		where
			( `t1`.`id` = `t2`.`collection_id` ) and
			( `t2`.`worker_fid` = ? ) and
			( `t1`.`name` in ( ?* ) )" ,
		"name" , "i*s" , $docVar[ 'expert-fid' ][ 'value' ] , $userBankNameList
	);

	if ( $userBankData !== false ) {
		foreach( $userBankData as &$row ) {
			$v = array(
				'name' => 'user-bank:'.$row[ 'name' ] ,
				'mf' => false ,
				'desc' => $row[ 'description' ] ,
				'value' => $row[ 'data' ]
			);
			$docVar[ $v[ 'name' ] ] = $v ;
		} unset( $row );
	}



	$xsltTmpl2Doc = new DOMDocument();
	$xsltTmpl2Doc->load( './tmpl/tmpl2Doc.xslt' );

	$xsltXPATH = new DOMXPath( $xsltTmpl2Doc );
	$variablesNode = $xsltXPATH->query( '/xsl:transform/xsl:variable[@name="docDataSrc"]/variables' )->item( 0 );
	
	function addVar( $name , $data , $parentNode = false ) {
		global $xsltTmpl2Doc , $variablesNode ;
		
		$value = $data[ 'value' ];
		$multiForm = isset( $data[ 'mf' ] ) ? $data[ 'mf' ] : false ;
		$type = isset( $data[ 'type' ] ) ? $data[ 'type' ] : 'string' ;
		
		if ( $parentNode === false ) {
			$varNode = $xsltTmpl2Doc->createElement( 'var' );
			$varNode->setAttribute( 'name' , $name );
			//error_log( 'addVar no parent. create new var "'.$name.'"' );
		} else {
			$varNode = $parentNode ;
			//error_log( 'addVar with parent' );
			//error_log( 'addVar value: '.print_r( $value , 1 ) );
		}

		$varNode->setAttribute( 'type' , $type );
		$varNode->setAttribute( 'descr' , iconv( 'cp1251' , 'utf8' , $data[ 'desc' ] ) );

		
		if ( !is_null( $value ) ) {
			switch( $type ) {
				case 'string' :
					if ( $multiForm ) {
						$varNode->setAttribute( 'mf' , 1 );
					}
					$varNode->appendChild( $xsltTmpl2Doc->createTextNode( iconv( 'cp1251' , 'utf8' , $value ) ) );
					break ;
					
				case 'array' :
					foreach( $value as $item ) {
						//reset( $item );
						//$elName = key( $item );
						//$elData = $item[ $elName ];
						$elName = $item[ 'name' ];
						$elData = $item[ 'items' ];
						$row = $xsltTmpl2Doc->createElement( $elName );
						foreach( $elData as $svn => $svv ) {
							$svNode = $xsltTmpl2Doc->createElement( $svn );
							//$svNode->appendChild( $xsltTmpl2Doc->createTextNode( iconv( 'cp1251' , 'utf8' , $svv ) ) );
							//error_log_ml( 'addVar in array: '.print_r( $svv , 1 ) );
							$svd = array(
								'value' => $svv
							);
							if ( is_array( $svv ) ) {
								$svd[ 'type' ] = 'array' ;
							}
							addVar( 'vvv-sub' , $svd , $svNode );
							$row->appendChild( $svNode );
						}
						$varNode->appendChild( $row );
					}
					break ;
					
				case 'options' :
					foreach( $value as $oID ) {
						$oOption = $xsltTmpl2Doc->createElement( 'option' );
						$oOption->setAttribute( 'id' , $oID );
						$varNode->appendChild( $oOption );
					}
					break ;

				default :
					$varNode->appendChild( $xsltTmpl2Doc->createTextNode( ''.$value ) );
					break ;
			}
			
		}
		$variablesNode->appendChild( $varNode );
		return $varNode ;
	}
	
	function convertArray( $arr , $f , $t ) {
		$res = array();
		foreach( $arr as $k => $v ) {
			$nk = iconv( $f , $t , $k );
			if ( is_array( $v ) ) {
				$nv = convertArray( $v , $f , $t );
			} else
			if ( is_string( $v ) ) {
				$nv = iconv( $f , $t , $v );
			} else {
				$nv = $v ;
			}
			
			$res[ $nk ] = $nv ;
		}
		return $res ;
	}

	$classesXML = new DOMDocument( '1.0' , 'windows-1251' );   //в методы передавать строки в utf-8, преобразование автоматически при сохранении через saveXML
	$classesNode = $classesXML->createElement( 'classes' );

	if ( !is_null( $tmplData[ 'classes' ] ) ) {
		$classList = json_decode( $tmplData[ 'classes' ] );
		$classData = array();
		while( count( $classList ) > 0 ) {
			$tcd = $portalDB->simpleQuery( 'doc-generator-classes' , array( 'id' => $classList ) );
			$classData = array_merge( $classData , $tcd );
			$classList = array_column( $tcd , 'base_id' , 'base_id' );
			unset( $classList[ null ] );
		}

		foreach( $classData as $ccd ) {
			$classDefinitionXML = new DOMDocument();
			$classDefinitionXML->loadXML( iconv( 'cp1251' , 'utf8' , $ccd[ 'definition' ] ) );
			if ( isset( $ccd[ 'base_id' ] ) && $ccd[ 'base_id' ] != '' ) {
				$classDefinitionXML->documentElement->setAttribute( 'base_id' , $ccd[ 'id' ] );
			}
			$classNode = $classesXML->importNode( $classDefinitionXML->documentElement , true );
			$classesNode->appendChild( $classNode );
		}
	}
	$classesXML->appendChild( $classesNode );

	//
	$extVarDef = json_decode( iconv( 'cp1251' , 'utf8' , $tmplData[ 'ext-var' ] ) , true );
	switch( $docGeneratorData[ 'place_index' ] ) {
		case 2 :
			$wData = gzuncompress( base64_decode( $docGeneratorData[ 'doc_ex_data' ] ) );
			break ;
		case 4 :
			$fn = $docGeneratorData[ 'doc_ex_data' ];
			$wData = gzuncompress( file_get_contents( './doc_ex_data/'.$fn ) );
			break ;
		default :
			$wData = $docGeneratorData[ 'doc_ex_data' ];
			break ;
	}
	
	$savedDocExData = json_decode( iconv( 'cp1251' , 'utf8' , $wData ) , true );
	//$savedDocExData = json_decode( $docGeneratorData[ 'doc_ex_data' ] , true );
	$extVar = array();
	$exData = array(
		'classes' => readClasses( $classesNode )
	);

	//error_log_ml( print_r( $savedDocExData , 1 ) );

	TDGVariableImage::$ImagesIDMap = array();
	foreach( $extVarDef as $cv ) {
		$v = $cv ;
		if ( $v[ 'type' ] != '@form-data' ) {
			$vName = 'ext:'.$v[ 'name' ];
			$v[ 'name' ] = $vName ;
			$extVar[ $vName ] = TDGVariable::fromDef( null , $v , $exData );
			$extVar[ $vName ]->read( $savedDocExData[ $vName ] );

			$varNode = $xsltTmpl2Doc->createElement( 'var' );
			$varNode->setAttribute( 'name' , $vName );
			$extVar[ $vName ]->export( $varNode );
			$variablesNode->appendChild( $varNode );
		}
	}

	foreach( $savedDocExData as $svName => $svValue ) {
		if ( !isset( $extVar[ $svName ] ) ) {
			if ( isset( $docVar[ $svName ] ) ) {
				$docVar[ $svName ][ 'value' ] = iconv( 'utf8' , 'cp1251' , $svValue );
			}
		}
	}

	foreach( $docVar as $varName => $varData ) {
		addVar( $varName , $varData );
	}

	if ( isset( $_REQUEST[ 'dbg' ] ) && $_REQUEST[ 'dbg' ] == 'tmpl2Doc' ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );
		echo iconv( 'utf8' , 'cp1251' , $xsltTmpl2Doc->saveXML() );
		exit();
	}


	$xslProc = new XSLTProcessor();
	$xslProc->registerPHPFunctions( array(
		'tmpl2Doc_formatDate' ,
		'tmpl2Doc_formatPrice' ,
		'tmpl2Doc_moreData' ,
		'tmpl2Doc_calc' ,
		'tmpl2Doc_storeCalcResultWOID' ,
		'tmpl2Doc_storeCalcResultWID' ,
		'tmpl2Doc_restoreCalcResultWOID' ,
		'tmpl2Doc_restoreCalcResultWID'
	) );
	$xslProc->importStylesheet( $xsltTmpl2Doc );

	$xmlDoc = $xslProc->transformToDoc( $xmlTmpl );

	$totalImagesList = array();

	$xmlDocXPATH = new DOMXPath( $xmlDoc );

	$docQRCodeImagesList = $xmlDocXPATH->query( '//image[@type="dmtx"]' );
	for( $cImgIndex = 0 ; $cImgIndex < $docQRCodeImagesList->length ; $cImgIndex++ ) {
		$cin = $docQRCodeImagesList->item( $cImgIndex );
		$newImgID = $cin->getAttribute( 'id' ).','.time().':'.generateGUID();
		$cin->setAttribute( 'id' , $newImgID );
		$imageID = 'post-data:'.( $cin->getAttribute( 'id' ) ).'.png' ;
		//error_log( 'DBG : download.php : img post data : '.$cin->nodeValue );
		$bc = generateBarcode( $cin->nodeValue , false , BARCODE_TYPE_DATAMATRIX );
		$totalImagesList[ $imageID ] = $bc[ 'raw' ];
	}

	$staticImagesList = $xmlDocXPATH->query( '//image[not(@type)]' );
	for( $cImgIndex = 0 ; $cImgIndex < $staticImagesList->length ; $cImgIndex++ ) {
		$cin = $staticImagesList->item( $cImgIndex );
		$newImgSrc = preg_replace( '/^\s*url\(\s*post-data\s*:\s*(.+)\s*\)$/' , '$1' , $cin->getAttribute( 'src' ) );
		$imageID = 'post-data:'.( $newImgSrc );
		//error_log( 'DBG : download.php : img post data : '.$cin->nodeValue );
		$statImgData = file_get_contents( './files/static-files/'.$newImgSrc );
		$totalImagesList[ $imageID ] = $statImgData ;
	}


	foreach( TDGVariableImage::$ImagesIDMap as $imageID => &$imageData ) {
		$totalImagesList[ 'post-data:'.$imageID ] = $imageData->value ;
		//error_log( 'DBG : DG : image data test : '.strlen( $totalImagesList[ 'post-data:'.$imageID ] ) );
	} unset( $imageData );

	//error_log_ml( 'DBG: totalImagesList: '.print_r( array_keys( $totalImagesList ) , 1 ) );

	if ( isset( $_REQUEST[ 'dbg' ] ) && $_REQUEST[ 'dbg' ] == 'doc' ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );
		echo iconv( 'utf8' , 'cp1251' , $xmlDoc->saveXML() );
		exit();
	}


	$xsltDoc2FO = new DOMDocument();
	$xsltDoc2FO->load( './tmpl/doc2FO.xslt' );

	$xslProc2 = new XSLTProcessor();
	$xslProc2->importStylesheet( $xsltDoc2FO );

	$xmlFO = $xslProc2->transformToDoc( $xmlDoc );

	if ( isset( $_REQUEST[ 'dbg' ] ) && $_REQUEST[ 'dbg' ] == 'fo' ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );
		echo iconv( 'utf8' , 'cp1251//IGNORE' , $xmlFO->saveXML() );
		exit();
	}

	$typeMap = array(
		'pdf'     => 'application/pdf'        ,
		'rtf'     => 'application/rtf'        ,
		'ps'      => 'application/postscript' ,
		'bmp'     => 'image/x-bitmap'         ,
		'pcl'     => 'application/x-pcl'      ,
		'pcl-alt' => 'application/vnd.hp-PCL' ,
		'jpg'     => 'image/jpeg'             ,
		'png'     => 'image/png'              ,
		'printing:ps'  => 'application/json' ,
		'printing:pcl' => 'application/json' ,
		'printing:pdf' => 'application/json' ,
	);

	$pType = 'pdf' ;
	if ( isset( $_REQUEST[ 'type' ] ) ) {
		$pType = $_REQUEST[ 'type' ];
		if ( !isset( $typeMap[ $pType ] ) ) {
			$pType = 'pdf' ;
		}
	}
	$rType = $typeMap[ $pType ];

	$TS = microtime( 1 );
	function curlHeaderFunction() {
	}

	$headers = array( 'Content-Type:multipart/form-data' );
	$postData = array(
		'fo-inline' => $xmlFO->saveXML() ,
		//'post-data:emblem.png' => file_get_contents( './files/static-files/emblem.png' )
	);
	$postData = array_merge( $postData , $totalImagesList );
	$curlOpts = array(
		CURLOPT_POST => 1 ,
		CURLOPT_POSTFIELDS => $postData ,
		CURLOPT_HTTPHEADER => $headers ,
		CURLOPT_RETURNTRANSFER => true ,
		//CURLOPT_HEADER => true ,
		//CURLOPT_HEADERFUNCTION => "curlHeaderFunction"
	);
	$ch = curl_init( 'http://'.$dbConfig[ 'doc-generator.fop-address' ].'/fop-servlet-2.8/fop?type='.$pType.( isset( $_REQUEST[ 'printer-id' ] ) ? '&printer-id='.$_REQUEST[ 'printer-id' ] : '' ) );
	curl_setopt_array( $ch , $curlOpts );
	$TS2 = microtime( 1 );
	$resp = curl_exec( $ch );
	error_log( 'DBG: pdf generate time network : '.( microtime( 1 ) - $TS2 ) );
	if( !curl_errno( $ch ) ) {
		$info = curl_getinfo( $ch );
		if ( $info[ 'http_code' ] == 200 ) {
			header( 'Content-Type: '.$rType );
			header( 'Content-Disposition: inline;filename="'.$tmplData[ 'name' ].' '.date( 'Y.m.d H-i' , time() ).'.'.$pType.'"' );
			echo $resp ;
			$errmsg = "File uploaded successfully [size : ".strlen( $resp )."]" ;
		} else {
			echo $resp ;
		}
		error_log_ml( print_r( $info , 1 ) );
	} else {
		echo $resp ;
		$errmsg = curl_error( $ch );
		error_log( $errmsg );
	}
	curl_close( $ch );
	error_log( 'DBG: pdf generate time : '.( microtime( 1 ) - $TS ) );
	
	$portalDB->updateRow( 'doc-generator-data' , array(
		'id' => $docGeneratorData[ 'id' ] ,
		'time_downloaded' => time()
	) );
