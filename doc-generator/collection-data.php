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

	require_once( './doc-generator.core.php' );
	require_once( '../cores/data-bank.php' );

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
			case 'get-collection-data' :
				if ( isset( $ajaxRequest[ 'collection' ] ) ) {
					$collectionID = trim( $ajaxRequest[ 'collection' ] );
				} else {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'collection ID undefined' );
					echo $res->saveXML();
					exit();
				}

				$collectionData = $portalDB->simpleRow( 'data-collection-description' , $collectionID );
				if ( $collectionData === false ) {
					$resultNode->setAttribute( 'error' , '' );
					$resultNode->setAttribute( 'message' , 'collection with ID='.$collectionID.' not found' );
					echo $res->saveXML();
					exit();
				}

				dgRowToXML( $collectionData , $resultNode ,
					array(
						'name'       => 'name' ,
						'description' => 'description'
					) ,
					array(
						'list_size'  => 'list-size' ,
					)
				);

				$typeDataNode = $res->createElement( 'type-data' );
				$typeDataNode->appendChild( $res->createCDATASection( iconv( 'cp1251' , 'utf8' , $collectionData[ 'type_data' ] ) ) );
				$resultNode->appendChild( $typeDataNode );

				echo $res->saveXML();

				break;
		}
		exit();
	}

	echo 'mode error' ;
