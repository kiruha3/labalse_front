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
	 * @var $dbConfig
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

	require_once( '../marks.core.php' );

	MainHead_L2( 'Админка' , 'Админка' , array( '../%UT/buttons.css' , '%UT/marks.css' ) , array( './files/marks.js' ) , 'hlp/main.html' );

	$marksList = $portalDB->table( 'marks-catalog' , 'id' );
	$marksGroups = $portalDB->table( 'marks-groups' , 'id' );
	$marksMarkGroup = $portalDB->table( 'marks-mark-group' , 'id' );
	$marksGroupGroup = $portalDB->table( 'marks-group-group' , 'id' );
	$marksStyles = $portalDB->table( 'marks-styles' , 'name' );

	foreach( $marksList as &$cm ) {
		$cm[ '__groups' ] = array();
	} unset( $cm );
	foreach( $marksGroups as &$cg ) {
		$cg[ '__childMarks' ] = array();
		$cg[ '__childGroups' ] = array();
		$cg[ '__parentGroups' ] = array();
	} unset( $cg );
	foreach( $marksMarkGroup as $cmgl ) {
		$cmID = $cmgl[ 'mark_id' ];
		$cgID = $cmgl[ 'group_id' ];
		$marksList[ $cmID ][ '__groups' ][]= $cgID ;
		$marksGroups[ $cgID ][ '__childMarks' ][]= $cmID ;
	}
	foreach( $marksGroupGroup as $cggl ) {
		$cpID = $cggl[ 'parent_id' ];
		$cgID = $cggl[ 'group_id' ];
		$marksGroups[ $cgID ][ '__parentGroups' ][]= $cpID ;
		$marksGroups[ $cpID ][ '__childGroups' ][]= $cgID ;
	}

	//print_r_html( $marksGroups );


	if ( $mayMark ) {

		$marksIntegrationOptions = array(
			'mode' => MARKS__MODE__SIMPLE_INLINE ,
			'id-mark' => true ,
			'description-as-title' => true ,
			'actions' => array(
				'onclick' => 'doMarkEdit'
			)
		);

		$systemMarks = array(
			$dbConfig[ CFG_MATINCOMING_MARK_NOPAY ]                     => array( '15-1' ) ,
			$dbConfig[ CFG_MATINCOMING_MARK_RP3214R ]                   => array( '15-1' ) ,
			$dbConfig[ CFG_MARK_MATINCOMING_CADASTRAL_VALUE ]           => array( '15-1' ) ,
			$dbConfig[ CFG_MARK_MATINCOMING_UNAUTHORIZED_CONSTRUCTION ] => array( '15-1' ) ,
		);

		$f = makeSimpleTable_init_filter();
		$f[ 'sample' ] = function( &$r , $c , $v ) use ( $systemMarks ) {
			global $marksIntegrationOptions , $marksList ;
			$sm = '' ;
			if ( isset( $systemMarks[ $v ] ) ) {
				$sm = '<span class="marks--system-mark">'.implode( '</span><span class="marks--system-mark">' , $systemMarks[ $v ] ).'</span>' ;
			}
			return Marks\integrate( $v , $marksIntegrationOptions , $marksList ).$sm ;
		};
		$f[ 'groups' ] = function( &$r , $c , $v ) use ( $marksGroups ) {
			$res = array();
			foreach( $r[ '__groups' ] as $cgi ) {
				$cg = $marksGroups[ $cgi ];
				$res[]= '<div class="marks--marks-tab--group" title="'.$cg[ 'descr' ].'">'.$cg[ 'name' ].'</div>' ;
			}
			return implode( '' , $res );
		};
		$f[ 'actual' ] = function( &$r , $c , $v ) {
			if ( $v != 1 ) {
				return '<div class="marks--not-actual"></div>' ;
			} else {
				return '' ;
			}
		};
		$t = '[]' ;
		/** marks table */
		$mt = makeSimpleTable(
			$t , '[ { "t" : 1 } ]' ,
			'[ { "n" : "id"     , "t" : "n"  , "h" : [ { "d" : "id" } ] } ,'.
			'  { "n" : "id"     , "t" : "Sl" , "h" : [ { "d" : "Образец" } ] , "f" : "sample" } ,'.
			'  { "n" : "actual" , "t" : "n"  , "h" : [ { "d" : "Активна" } ] , "f" : "actual" , "s" : "marks--not-actual-field" } ,'.
			'  { "n" : "id"     , "t" : "Sl" , "h" : [ { "d" : "Входит в группы" } ] , "f" : "groups" }'.
			']' ,
			$marksList , array( "dr" => "dr-d" ) , $f );

		$marksSamplesList = array();
		foreach( $marksStyles as $cmsn => $cms ) {
			$msID = 'sample--'.$cmsn ;
			$marksSamplesList[ $msID ] = array(
				'id' => $msID ,
				'name' => $cms[ 'descr' ] ,
				'description' => null ,
				'style' => $cmsn ,
				'actual' => 1
			);
		}

		$mstOpt = $marksIntegrationOptions ;
		$mstOpt[ 'actions' ] = array();
		$mst = Marks\integrate( array_keys( $marksSamplesList ) , $marksIntegrationOptions , $marksSamplesList );

		echo '<div class="marks--marks-tab-area">
			<div class="marks--marks-tab-area-title">Отметки</div>
			<div class="marks--marks-tab-area-message">Для редактирования отметки нажмите на её образец</div>
			<div class="marks--marks-tab-area-panel"><a class="btn3" onclick="doMarkCreate()">Создать отметку</a></div>
			<template id="marks-samples-area">'.$mst.'</template>
			<div class="marks--marks-table">'.$mt.'</div>
		</div>' ;
	}

	if ( $mayGroup ) {

		$marksIntegrationOptions = array(
			'mode' => MARKS__MODE__SIMPLE_INLINE ,
			'id-mark' => true ,
			'description-as-title' => true ,
			'actions' => array()
		);

		$f = makeSimpleTable_init_filter();
		$f[ 'group-texts' ] = function( &$r , $c , $v ) {
			return '<span
				class="marks--group-name"
				data-mark-element="group"
				data-group-id="'.$r[ 'id' ].'"
				data-group-actual="'.$r[ 'actual' ].'"
				onclick="doGroupEdit( event )"
			>'.$r[ 'name' ].'</span>
			<span
				class="marks--group-descr"
				data-mark-element="group-descr"
				data-group-id="'.$r[ 'id' ].'"
			>'.$r[ 'descr' ].'</span>' ;
		};
		$f[ 'sample' ] = function( &$r , $c , $v ) {
			global $marksIntegrationOptions , $marksList ;
			return Marks\integrate( $v , $marksIntegrationOptions , $marksList );
		};
		$f[ 'groups' ] = function( &$r , $c , $v ) use ( $marksGroups ) {
			$res = array();
			foreach( $r[ '__childGroups' ] as $cgi ) {
				$cg = $marksGroups[ $cgi ];
				$res[]= '<div class="marks--marks-tab--group" title="'.$cg[ 'descr' ].'">'.$cg[ 'name' ].'</div>' ;
			}
			return implode( '' , $res );
		};
		$f[ 'actual' ] = function( &$r , $c , $v ) {
			if ( $v != 1 ) {
				return '<div class="marks--not-actual"></div>' ;
			} else {
				return '' ;
			}
		};
		$t = '[]' ;
		/** marks groups table */
		$mgt = makeSimpleTable(
			$t , '[ { "t" : 1 } ]' ,
			'[ { "n" : "id"     , "t" : "n"  , "h" : [ { "d" : "id" } ] } ,'.
			'  { "n" : "id"     , "t" : "Sl" , "h" : [ { "d" : "Название и описание" } ] , "f" : "group-texts" } ,'.
			'  { "n" : "actual" , "t" : "n"  , "h" : [ { "d" : "Активна" } ] , "f" : "actual" , "s" : "marks--not-actual-field" } ,'.
			'  { "n" : "id"     , "t" : "Sl" , "h" : [ { "d" : "Содержит отметки и другие группы" } ] , "f" : "groups" }'.
			']' ,
			$marksGroups , array( "dr" => "dr-d" ) , $f );

		echo '<div class="marks--groups-tab-area">
			<div class="marks--groups-tab-area-title">Группы</div>
			<div class="marks--groups-tab-area-message">Для редактирования группы нажмите на ее название</div>
			<div class="marks--groups-tab-area-panel"><a class="btn3" onclick="doGroupCreate()">Создать группу</a></div>
			<div class="marks--groups-table">'.$mgt.'</div>
		</div>' ;
	}