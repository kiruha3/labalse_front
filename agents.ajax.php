<?php
	require_once( 'core.php' );

	/**
	 * @var $portalDB
	 */

	header( 'Content-Type: application/json' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Expires: '.date( 'r' ) );
	header( 'Expires: -1' , false );

	$DD = new DomDocument();
	$DD->loadXML( $_REQUEST[ 'data' ] );
	$data = $DD->documentElement ;

	switch ( $data->nodeName ) {
		case 'agency-list' :
			$tabAgency = $portalDB->query( "select `id` , `name` , `destination` from `agency` where `ext_id` = ? order by `_fr` desc" , false , 'i' , $data->getAttribute( 'toa' ) );
			foreach( $tabAgency as &$i ) {
				$i[ 'name' ] = iconv( 'cp1251' , 'utf8' , $i[ 'name' ] );
				$i[ 'destination' ] = iconv( 'cp1251' , 'utf8' , $i[ 'destination' ] );
			} unset( $i );
			echo json_encode( $tabAgency , JSON_UNESCAPED_UNICODE );
			break ;

		case 'agent-list' :
			$withContacts = ( $data->hasAttribute( 'contacts' ) && $data->getAttribute( 'contacts' ) == true );
			$tabAgent = $portalDB->query( "select `id` , `name` from `agent` where `ext_id`= ? Order by `_fr` desc" , false , 'i' , $data->getAttribute( 'agency' ) );
			$aID = array();
			$aMap = array();
			foreach( $tabAgent as &$i ) {
				$i[ 'name' ] = iconv( 'cp1251' , 'utf8' , $i[ 'name' ] );
				$aID[]= $i[ 'id' ];
				if ( $withContacts ) {
					$i[ 'contacts' ] = array();
				}
				$aMap[ $i[ 'id' ] ] = &$i ;
			} unset( $i );

			if ( $withContacts ) {
				$tabContacts = $portalDB->query( "select * from `agent-contacts` where ( `ext_id` in ( ?* ) )" , false , '*i' , $aID );
				foreach( $tabContacts as &$i ) {
					$aID = $i[ 'ext_id' ];
					$aMap[ $aID ][ 'contacts' ][]= &$i ;
					$i[ 'value' ] = iconv( 'cp1251' , 'utf8' , $i[ 'value' ] );
					$i[ 'uid' ] = sha1( $i[ 'id' ] );
				} unset( $i );
			}
			echo json_encode( $tabAgent , JSON_UNESCAPED_UNICODE );
			break ;


		case 'hide-contact' :
		case 'show-contact' :
			if ( $data->hasAttribute( 'id' ) ) {
				$contactID = intval( $data->getAttribute( 'id' ) );
				$portalDB->updateRow( 'agent-contacts' , array(
					'id' => $contactID ,
					'actual' => $data->nodeName == 'hide-contact' ? 0 : 1
				) );
				echo json_encode( array( 'result' => 'ok' ) , JSON_UNESCAPED_UNICODE );
			} else {
				echo json_encode( array() , JSON_UNESCAPED_UNICODE );
			}
			break ;
	}
