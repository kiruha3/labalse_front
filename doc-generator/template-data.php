<?php
	require_once( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserID
	 * @var $portalDB
	 */
	TryLoginFromCookie();
	if ( !$LoginOk ) {
		echo 'auth error' ;
		exit();
	}

	require_once( '../maindb/lconfig.php' );
	require_once( '../cores/core.maindb.php' );
	require_once( './doc-generator.core.php' );
	require_once( '../cores/data-bank.php' );

	function prepDocVarDesc( $n ) {
		$m = array();
		$res = explode( ">" , $n );
		$i = 0 ;
		foreach ( $res as &$c ) {
			$c = "<span class=\"var-desc-el\" style=\"margin-left : ".( $i++ * 16 )."px ;\">".trim( $c )."</span>" ;
		} unset( $c );
		return implode( $res ) ;
	}


	if ( isset( $_REQUEST[ 'mode' ] ) && $_REQUEST[ 'mode' ] == 'ajax' ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );

		/* echo '<?xml version="1.0" encoding="windows-1251" ?>' ; */
		$res = new DOMDocument( '1.0' , 'windows-1251' );   //в методы передавать строки в utf-8, преобразование автоматически при сохранении через saveXML

		$resultNode = $res->createElement( 'result' );
		$res->appendChild( $resultNode );

		$ajaxRequest = simplexml_load_string( $_REQUEST[ 'data' ] , 'SimpleXMLElement' , LIBXML_NOCDATA );

		switch ( $ajaxRequest->getName() ) {
			case 'get-tmpl-data' :
				if ( isset( $ajaxRequest[ 'tmpl' ] ) ) {
					$tmplID = intval( $ajaxRequest[ 'tmpl' ] );
				} else {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'Doc-Template ID undefined' );
					echo $res->saveXML();
					exit();
				}

				$tmplData = $portalDB->simpleRow( 'doc-templates' , $tmplID );
				if ( $tmplData === false ) {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'Doc-Template with ID='.$tmplID.' not found' );
					echo $res->saveXML();
					exit();
				}

				dgRowToXML( $tmplData , $resultNode ,
					array(
						'name'       => 'name' ,
						'short_name' => 'short-name' ,
						'file_name'  => 'file-name' ,
						'code'       => 'code' ,
					) ,
					array(
						'proceeding'     => 'proceeding' ,
						'preview_mode'   => 'preview-mode' ,
						'download_mode'  => 'download-mode' ,
						'download_types' => 'download-types' ,
					)
				);

				/*$resultNode->setAttribute( 'preview-mode' , 1 );
				$resultNode->setAttribute( 'download-mode' , 1 );
				$resultNode->setAttribute( 'download-types' , 'print' );*/

				/*$tmplNameNode = $res->createElement( 'name' );
					$tmplNameNode->appendChild( $res->createTextNode( iconv( 'cp1251' , 'utf8' , $tmplData[ 'name' ] ) ) );
				$resultNode->appendChild( $tmplNameNode );

				$tmplShortNameNode = $res->createElement( 'short-name' );
					$tmplShortNameNode->appendChild( $res->createTextNode( iconv( 'cp1251' , 'utf8' , $tmplData[ 'short_name' ] ) ) );
				$resultNode->appendChild( $tmplShortNameNode );*/

				if ( !is_null( $tmplData[ 'triggers' ] ) ) {
					$triggersData = new DOMDocument();
					$triggersData->loadXML( $tmplData[ 'triggers' ] );
					$triggersDataNode = $res->importNode( $triggersData->documentElement , true );
					$resultNode->appendChild( $triggersDataNode );
				} else {
					$triggersDataNode = $res->createElement( 'triggers' );
					$resultNode->appendChild( $triggersDataNode );
				}
				
				if ( !is_null( $tmplData[ 'ext-var' ] ) ) {
					$extVarNode = $res->createElement( 'ext-var' );
					$extVarNode->appendChild( $res->createCDATASection( iconv( 'cp1251' , 'utf8' , $tmplData[ 'ext-var' ] ) ) );
					$resultNode->appendChild( $extVarNode );
				}

				if ( !is_null( $tmplData[ 'classes' ] ) ) {
					$classList = json_decode( $tmplData[ 'classes' ] );
					$classData = array();
					while( count( $classList ) > 0 ) {
						//$cid = array_shift( $classList );
						$tcd = $portalDB->simpleQuery( 'doc-generator-classes' , array( 'id' => $classList ) );
						$classData = array_merge( $classData , $tcd );
						$classList = array_column( $tcd , 'base_id' , 'base_id' );
						//error_log( 'DEBUG get-tmpl-data : count( classList ) = '.count( $classList ) );
						unset( $classList[ null ] );
						//error_log( 'DEBUG get-tmpl-data : now count( classList ) = '.count( $classList ) );
					}

					$classesNode = $res->createElement( 'classes' );
					foreach( $classData as $ccd ) {
						$classDefinitionXML = new DOMDocument();
						$classDefinitionXML->loadXML( iconv( 'cp1251' , 'utf8' , $ccd[ 'definition' ] ) );
						if ( isset( $ccd[ 'base_id' ] ) && $ccd[ 'base_id' ] != '' ) {
							$classDefinitionXML->documentElement->setAttribute( 'base_id' , $ccd[ 'id' ] );
						}
						$classNode = $res->importNode( $classDefinitionXML->documentElement , true );
						$classesNode->appendChild( $classNode );
					}
					$resultNode->appendChild( $classesNode );
				}

				echo $res->saveXML();
				break ;

			case 'get-tmpl-tmpl' :
				if ( isset( $ajaxRequest[ 'id' ] ) ) {
					$tmplID = intval( $ajaxRequest[ 'id' ] );
				} else {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'Doc-Template ID undefined' );
					echo $res->saveXML();
					exit();
				}

				$tmplData = $portalDB->simpleRow( 'doc-templates' , $tmplID );
				if ( $tmplData === false ) {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'Doc-Template with ID='.$tmplID.' not found' );
					echo $res->saveXML();
					exit();
				}

				$xmlTmpl = new DOMDocument();
				$xmlTmpl->loadXML( iconv( 'cp1251' , 'utf8' , $tmplData[ 'tmpl' ] ) );
				//$xmlTmpl->load( './test/test--dyna-con-3.xml' );
				//$xmlTmpl->load( './test/test--2.xml' );
				//$xmlTmpl->load( './test/test--dyna-con-3.xml' );
				normalizeTemplate( $xmlTmpl );

				$resultTmplNode = $res->importNode( $xmlTmpl->documentElement , true );
				$resultNode->appendChild( $resultTmplNode );

				echo $res->saveXML();

				break ;

			case 'get-tmpl-vars' :
				if ( isset( $ajaxRequest[ 'id' ] ) ) {
					$docID = $ajaxRequest[ 'id' ].'' ;
				} else {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'docID undefined' );
					echo $res->saveXML();
					exit();
				}

				error_log( 'dbg: tmpl.... doc id '.$docID );

				$docGeneratorData = $portalDB->simpleRow( 'doc-generator-data' , array(
					'doc_id'  => $docID ,
					'user_id' => $UserID ,
				) );

				if ( $docGeneratorData === false ) {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'doc data not found' );
					echo $res->saveXML();
					exit();
				}

				$tmplID = $docGeneratorData[ 'tmpl_id' ];
				$root_id = $docGeneratorData[ 'root_id' ];

				$tmplData = $portalDB->simpleRow( 'doc-templates' , $tmplID );
				if ( $tmplData === false ) {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'Doc-Template with ID='.$tmplID.' not found' );
					echo $res->saveXML();
					exit();
				}

				$docVar = fillDataBank2(
					array(
						'req:id' => $root_id ,
						'tmpl-data' => $tmplData
					)
				);

				if ( isset( $docVar[ 'expert-fid' ] ) ) {
					$xmlTmpl = new DOMDocument();
					$xmlTmpl->loadXML( iconv( 'cp1251' , 'utf8' , $tmplData[ 'tmpl' ] ) );

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
				}
				foreach ( $docVar as $dvk => $dvd ) {
					$el = $res->createElement( 'var' );
					$el->setAttribute( 'name' , $dvk );
					$resultNode->appendChild( $el );
					if ( isset( $dvd[ 'type' ] ) ) {
						$el->setAttribute( 'type' , $dvd[ 'type' ] );
					}

					if ( $dvd[ 'mf' ] ) {
						$el->setAttribute( 'mf' , $dvd[ 'mf' ] );
						$elVal = $res->createElement( 'value' );
						$elVal->appendChild( $res->createTextNode( iconv( 'cp1251' , 'utf8' , $dvd[ 'value' ] ) ) );
						$el->appendChild( $elVal );
					} else {
						$elVal = $res->createElement( 'value' );
						$elVal->appendChild( $res->createTextNode( iconv( 'cp1251' , 'utf8' , $dvd[ 'value' ] ) ) );
						$el->appendChild( $elVal );
					}
					$elDescr = $res->createElement( 'description' );
					$elDescr->appendChild( $res->createTextNode( iconv( 'cp1251' , 'utf8' , $dvd[ 'desc' ] ) ) );
					$el->appendChild( $elDescr );
				}

				$el = $res->createElement( 'ext-var' );
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
				$el->appendChild( $res->createCDATASection( iconv( 'cp1251' , 'utf8' , $wData ) ) );
				$resultNode->appendChild( $el );

				echo $res->saveXML();

				break ;

			case 'get-tmpl-base-vars' :
				if ( isset( $ajaxRequest[ 'id' ] ) ) {
					$tmplID = $ajaxRequest[ 'id' ].'' ;
				} else {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'id undefined' );
					echo $res->saveXML();
					exit();
				}

				error_log( 'dbg: tmpl.... tmpl id '.$tmplID );

				$tmplData = $portalDB->simpleRow( 'doc-templates' , $tmplID );
				if ( $tmplData === false ) {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'Doc-Template with ID='.$tmplID.' not found' );
					echo $res->saveXML();
					exit();
				}

				if ( isset( $ajaxRequest[ 'root-id' ] ) ) {
					$root_id = $ajaxRequest[ 'root-id' ].'' ;
				} else {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'root-id undefined' );
					echo $res->saveXML();
					exit();
				}

				$docVar = fillDataBank2(
					array(
						'req:id' => $root_id ,
						'tmpl-data' => $tmplData
					)
				);

				if ( isset( $docVar[ 'expert-fid' ] ) ) {
					$xmlTmpl = new DOMDocument();
					$xmlTmpl->loadXML( iconv( 'cp1251' , 'utf8' , $tmplData[ 'tmpl' ] ) );

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
				}
				foreach ( $docVar as $dvk => $dvd ) {
					$el = $res->createElement( 'var' );
					$el->setAttribute( 'name' , $dvk );
					$resultNode->appendChild( $el );
					if ( isset( $dvd[ 'type' ] ) ) {
						$el->setAttribute( 'type' , $dvd[ 'type' ] );
					}

					if ( $dvd[ 'mf' ] ) {
						$el->setAttribute( 'mf' , $dvd[ 'mf' ] );
						$elVal = $res->createElement( 'value' );
						$elVal->appendChild( $res->createTextNode( iconv( 'cp1251' , 'utf8' , $dvd[ 'value' ] ) ) );
						$el->appendChild( $elVal );
					} else {
						$elVal = $res->createElement( 'value' );
						$elVal->appendChild( $res->createTextNode( iconv( 'cp1251' , 'utf8' , $dvd[ 'value' ] ) ) );
						$el->appendChild( $elVal );
					}
					$elDescr = $res->createElement( 'description' );
					$elDescr->appendChild( $res->createTextNode( iconv( 'cp1251' , 'utf8' , $dvd[ 'desc' ] ) ) );
					$el->appendChild( $elDescr );
				}

				$el = $res->createElement( 'ext-var' );
				$wData = '[]' ;
				$el->appendChild( $res->createCDATASection( iconv( 'cp1251' , 'utf8' , $wData ) ) );
				$resultNode->appendChild( $el );

				echo $res->saveXML();

				break ;

			case 'get-generated-docs-list' :
				if ( isset( $ajaxRequest[ 'tmpl' ] ) ) {
					$tmplID = intval( $ajaxRequest[ 'tmpl' ] );
				} else {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'tmpl undefined' );
					echo $res->saveXML();
					exit();
				}

				if ( isset( $ajaxRequest[ 'root' ] ) ) {
					$rootID = intval( $ajaxRequest[ 'root' ] );
				} else {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'root undefined' );
					echo $res->saveXML();
					exit();
				}

				$docGeneratorData = $portalDB->query(
					"select * from `doc-generator-data` where ( `tmpl_id` = ? ) and ( `root_id` = ? ) and ( `user_id` = ? ) order by `time_created` desc" ,
					false , 'isi' , $tmplID , $rootID , $UserID
				);

				if ( $docGeneratorData === false ) {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'doc data not found' );
					echo $res->saveXML();
					exit();
				}

				foreach ( $docGeneratorData as $dgd ) {
					$el = $res->createElement( 'doc-data' );
					$el->setAttribute( 'doc-id' , $dgd[ 'doc_id' ] );
					$el->setAttribute( 'time-created' , $dgd[ 'time_created' ] );
					$el->setAttribute( 'time-edited' , $dgd[ 'time_edited' ] );
					$el->setAttribute( 'time-downloaded' , $dgd[ 'time_downloaded' ] );
					$el->setAttribute( 'state' , $dgd[ 'state' ] );
					$resultNode->appendChild( $el );
				}

				echo $res->saveXML();

				break ;
		}

		exit();
	}

	echo 'mode error' ;


