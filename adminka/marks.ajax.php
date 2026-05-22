<?php
	/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var TDB $portalDB
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) != 1 ) {
		ErrorPageAndExit();
		exit();
	}

	$modeAjax = isset( $_REQUEST[ 'mode' ] ) && $_REQUEST[ 'mode' ] == 'ajax' ;

	if ( !$modeAjax ) {
		ErrorPageAndExit();
		exit();
	}

	$Rights = ParseRights( strtolower( $UserRights[ 0 ] ) );

	$mayMark = false ;
	$mayGroup = false ;
	$mayMarkAssociate = false ;
	$mayStyle = false ;

	/*if ( isset( $Rights[ RIGHTS_GR__ADMINKA__MARKS ] ) ) {
		$RightsMarks        = $Rights[ RIGHTS_GR__ADMINKA__MARKS ];
		$mayMarkCreate      = in_array( RIGHTS__ADMINKA__MARKS__MARKS_CATALOG_CREATE       , $RightsMarks );
		$mayMarkDisable     = in_array( RIGHTS__ADMINKA__MARKS__MARKS_CATALOG_DISABLE      , $RightsMarks );
		$mayMarkChangeText  = in_array( RIGHTS__ADMINKA__MARKS__MARKS_CATALOG_CHANGE_TEXT  , $RightsMarks );
		$mayMarkChangeStyle = in_array( RIGHTS__ADMINKA__MARKS__MARKS_CATALOG_CHANGE_STYLE , $RightsMarks );
		$mayMark = $mayMarkCreate || $mayMarkDisable || $mayMarkChangeText || $mayMarkChangeStyle ;

		$mayGroupCreate      = in_array( RIGHTS__ADMINKA__MARKS__MARKS_GROUPS_CREATE       , $RightsMarks );
		$mayGroupDisable     = in_array( RIGHTS__ADMINKA__MARKS__MARKS_GROUPS_DISABLE      , $RightsMarks );
		$mayGroupChangeText  = in_array( RIGHTS__ADMINKA__MARKS__MARKS_GROUPS_CHANGE_TEXT  , $RightsMarks );
		$mayGroup = $mayGroupCreate || $mayGroupDisable || $mayGroupChangeText ;

		$mayMarkAssociate    = in_array( RIGHTS__ADMINKA__MARKS__MARKS_ASSOCIATE           , $RightsMarks );

		$mayStyleCreate      = in_array( RIGHTS__ADMINKA__MARKS__STYLE_CREATE              , $RightsMarks );
		$mayStyleChange      = in_array( RIGHTS__ADMINKA__MARKS__STYLE_CHANGE              , $RightsMarks );
		$mayStyle = $mayStyleCreate || $mayStyleChange ;
	}*/

	if ( true ) {
		$mayMarkCreate      = true ;
		$mayMarkDisable     = true ;
		$mayMarkChangeText  = true ;
		$mayMarkChangeStyle = true ;
		$mayMark = $mayMarkCreate || $mayMarkDisable || $mayMarkChangeText || $mayMarkChangeStyle ;

		$mayGroupCreate      = true ;
		$mayGroupDisable     = true ;
		$mayGroupChangeText  = true ;
		$mayGroup = $mayGroupCreate || $mayGroupDisable || $mayGroupChangeText ;

		$mayMarkAssociate    = true ;

		$mayStyleCreate      = true ;
		$mayStyleChange      = true ;
		$mayStyle = $mayStyleCreate || $mayStyleChange ;
	}

	$GoOut = !( $mayMark || $mayGroup || $mayMarkAssociate || $mayStyle );

	if ( $GoOut ) {
		ErrorPageAndExit();
		exit();
	}

	function processLinksNodes( $linksNode ) {
		//error_log_ml( print_r( $linksNode , 1 ) );
		$childMarks = array();
		$ml = $linksNode->marks ;
		foreach( $ml->link as $l ) {
			$childMarks[]= (int) $l[ 'mark-id' ];
		}
		$gl = $linksNode->groups ;
		$childGroups = array();
		$parentGroups = array();
		foreach( $gl->link as $l ) {
			if ( isset( $l[ 'group-id' ] ) ) {
				$childGroups[]= (int) $l[ 'group-id' ];
			} else
			if ( isset( $l[ 'parent-id' ] ) ) {
				$parentGroups[]= (int) $l[ 'parent-id' ];
			}
		}
		return array(
			'childMarks' => $childMarks ,
			'childGroups' => $childGroups ,
			'parentGroups' => $parentGroups
		);
	}

	function updateLinks( $newLinks , $tableName , $selfID , $selfIDFieldName , $linkFieldName ) {
		global $portalDB ;

		$olcm = $portalDB->simpleQuery( $tableName , array( $selfIDFieldName => $selfID ) );
		$oldLinks = array_column( $olcm , $linkFieldName );

		$oldLinks = array_combine( $oldLinks , $oldLinks );
		$newLinks = array_combine( $newLinks , $newLinks );

		$toRemove = array();
		foreach( $oldLinks as $l ) {
			if ( !isset( $newLinks[ $l ] ) ) {
				$toRemove[]= $l ;
			}
		}

		$toAdd = array();
		foreach( $newLinks as $l ) {
			if ( !isset( $oldLinks[ $l ] ) ) {
				$toAdd[]= $l ;
			}
		}

		if ( count( $toRemove ) > 0 ) {
			$portalDB->noResult( "delete from `".$tableName."` where ( `".$selfIDFieldName."` = ? ) and ( `".$linkFieldName."` in ( ?* ) )" , 'i*i' , $selfID , $toRemove );
		}

		foreach( $toAdd as $id ) {
			$portalDB->insertRow( $tableName , array(
				$selfIDFieldName => $selfID ,
				$linkFieldName => $id
			) );
		}
	}

	header( 'Content-Type: text/xml' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Expires: '.date( 'r' ) );
	header( 'Expires: -1' , false );

	$res = new DOMDocument( '1.0' , 'windows-1251' );   //в методы передавать строки в utf-8, преобразование автоматически при сохранении через saveXML

	$resultNode = $res->createElement( 'result' );
	$res->appendChild( $resultNode );

	$ajaxRequest = simplexml_load_string( $_REQUEST[ 'data' ] , 'SimpleXMLElement' , LIBXML_NOCDATA );

	switch ( $ajaxRequest->getName() ) {
		case 'mark-change' :
			$markName   = (string) $ajaxRequest->name ;
			$markDescr  = (string) $ajaxRequest->descr ;
			$markStyle  = (string) $ajaxRequest->style ;
			$markID     = (int) $ajaxRequest[ 'id' ];
			$markActual = isset( $ajaxRequest[ 'actual' ] ) && $ajaxRequest[ 'actual' ] == 1 ? 1 : 0 ;

			$portalDB->updateRow( 'marks-catalog' , array(
				'id'          => $markID ,
				'name'        => rcvt( $markName ),
				'description' => rcvt( $markDescr ),
				'style'       => $markStyle ,
				'actual'      => $markActual ,
			) );

			$resultNode->setAttribute( 'state' , 'ok' );
			echo $res->saveXML();
			break ;

		case 'mark-create' :
			$markName   = (string) $ajaxRequest->name ;
			$markDescr  = (string) $ajaxRequest->descr ;
			$markStyle  = (string) $ajaxRequest->style ;
			$markActual = isset( $ajaxRequest[ 'actual' ] ) && $ajaxRequest[ 'actual' ] == 1 ? 1 : 0 ;

			$portalDB->insertRow( 'marks-catalog' , array(
				'name'        => rcvt( $markName ),
				'description' => rcvt( $markDescr ),
				'style'       => $markStyle ,
				'actual'      => $markActual ,
			) );

			$markID = $portalDB->lastInsertID();

			$resultNode->setAttribute( 'state' , 'ok' );
			$resultNode->setAttribute( 'new-id' , $markID );
			echo $res->saveXML();
			break ;

		case 'get-group-links' :
			$groupID     = (int) $ajaxRequest[ 'id' ];

			$marksLinksNode = $res->createElement( 'marks' );
			$linksList = $portalDB->simpleQuery( 'marks-mark-group' , array( 'group_id' => $groupID ) );
			foreach( $linksList as $link ) {
				$linkNode = $res->createElement( 'link' );
				$linkNode->setAttribute( 'mark-id' , $link[ 'mark_id' ] );
				$marksLinksNode->appendChild( $linkNode );
			}
			$resultNode->appendChild( $marksLinksNode );

			$groupsLinksNode = $res->createElement( 'groups' );
			$linksList = $portalDB->simpleQuery( 'marks-group-group' , array( 'group_id' => $groupID ) );
			foreach( $linksList as $link ) {
				$linkNode = $res->createElement( 'link' );
				$linkNode->setAttribute( 'parent-id' , $link[ 'parent_id' ] );
				$groupsLinksNode->appendChild( $linkNode );
			}
			$linksList = $portalDB->simpleQuery( 'marks-group-group' , array( 'parent_id' => $groupID ) );
			foreach( $linksList as $link ) {
				$linkNode = $res->createElement( 'link' );
				$linkNode->setAttribute( 'group-id' , $link[ 'group_id' ] );
				$groupsLinksNode->appendChild( $linkNode );
			}
			$resultNode->appendChild( $groupsLinksNode );


			$resultNode->setAttribute( 'state' , 'ok' );
			echo $res->saveXML();
			break ;

		case 'group-change' :
			$groupName   = (string) $ajaxRequest->name ;
			$groupDescr  = (string) $ajaxRequest->descr ;
			$groupID     = (int) $ajaxRequest[ 'id' ];
			$groupActual = isset( $ajaxRequest[ 'actual' ] ) && $ajaxRequest[ 'actual' ] == 1 ? 1 : 0 ;

			$portalDB->updateRow( 'marks-groups' , array(
				'id'          => $groupID ,
				'name'        => rcvt( $groupName ),
				'descr'       => rcvt( $groupDescr ),
				'actual'      => $groupActual ,
			) );

			$pl = processLinksNodes( $ajaxRequest->links );
			updateLinks( $pl[ 'childMarks' ] , 'marks-mark-group' , $groupID , 'group_id' , 'mark_id' );
			updateLinks( $pl[ 'childGroups' ] , 'marks-group-group' , $groupID , 'parent_id' , 'group_id' );
			updateLinks( $pl[ 'parentGroups' ] , 'marks-group-group' , $groupID , 'group_id' , 'parent_id' );

			$resultNode->setAttribute( 'state' , 'ok' );
			echo $res->saveXML();
			break ;

		case 'group-create' :
			$groupName   = (string) $ajaxRequest->name ;
			$groupDescr  = (string) $ajaxRequest->descr ;
			$groupStyle  = (string) $ajaxRequest->style ;
			$groupActual = isset( $ajaxRequest[ 'actual' ] ) && $ajaxRequest[ 'actual' ] == 1 ? 1 : 0 ;

			$portalDB->insertRow( 'marks-groups' , array(
				'name'        => rcvt( $groupName ),
				'descr'       => rcvt( $groupDescr ),
				'actual'      => $groupActual ,
			) );

			$groupID = $portalDB->lastInsertID();

			$pl = processLinksNodes( $ajaxRequest->links );
			updateLinks( $pl[ 'childMarks' ] , 'marks-mark-group' , $groupID , 'group_id' , 'mark_id' );
			updateLinks( $pl[ 'childGroups' ] , 'marks-group-group' , $groupID , 'parent_id' , 'group_id' );
			updateLinks( $pl[ 'parentGroups' ] , 'marks-group-group' , $groupID , 'group_id' , 'parent_id' );

			$resultNode->setAttribute( 'state' , 'ok' );
			$resultNode->setAttribute( 'new-id' , $groupID );
			echo $res->saveXML();
			break ;
	}
