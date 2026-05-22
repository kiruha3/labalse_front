<?php
	require_once( '../core.php' );
	/**
	 * @var $portalDB
	 */

	if ( isset( $_REQUEST[ 'mode' ] ) && $_REQUEST[ 'mode' ] == 'ajax' ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );

		/* echo '<?xml version="1.0" encoding="windows-1251" ?>' ; */
		$res = new DOMDocument( '1.0' , 'windows-1251' );
		$resultNode = $res->createElement( 'result' );
		$res->appendChild( $resultNode );

		$ajaxRequest = simplexml_load_string( $_REQUEST[ 'data' ] , 'SimpleXMLElement' , LIBXML_NOCDATA );

		switch ( $ajaxRequest->getName() ) {
			case 'get-triggers' :
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

				$triggersData = new DOMDocument();
				$triggersData->loadXML( $tmplData[ 'triggers' ] );
				$triggersDataNode = $res->importNode( $triggersData->documentElement );
				$resultNode->appendChild( $triggersDataNode );
				echo $res->saveXML();
				break ;
		}

		exit();
	}

	echo 'mode error' ;


