<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $dbConfigFull
	 * @var $LoginOk
	 * @var TDB $portalDB
	 * @var $UserThemeLoc
	 * @var $dbConfig
	 * @var $UserID
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	$modeAjax = isset( $_REQUEST[ 'mode' ] ) && $_REQUEST[ 'mode' ] == 'ajax' ;

	if ( isset( $_REQUEST[ 'tmpl' ] ) ) {
		$tmplID = intval( $_REQUEST[ 'tmpl' ] );
	} else {
		exit();
	}

	if ( !isset( $_REQUEST[ 'id' ] ) ) {
		exit();
	} else {
		$expertize_id = intval( $_REQUEST[ 'id' ] );
	}

	require_once( 'request.core.php' );

	$tabDepartments = array();
	$tabWorkers = array();
	$tabPosts = array();
	$tabSpecGroups = array();
	$tabCaseCategory = array();
	$tabTypeOfAgency = array();

	$tmplData = $portalDB->row( "select * from `doc-templates` where `id` = ?" , 'i' , $tmplID );
	$tmplExtVar = json_decode( iconv( 'cp1251' , 'utf8' , $tmplData[ 'ext-var' ] ) , true );
	$docVar = loadVariables( $tmplData , $expertize_id );

	if ( $docVar === false ) {
		exit();
	}

	$docVarVal = $docVar ;
	foreach ( $docVarVal as &$dvv ) {
		$dvv = $dvv[ 'value' ];
	} unset( $dvv );

	if ( $modeAjax ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );

		echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ 'data' ] );

		$data = $DD->documentElement ;
		switch ( $data->nodeName ) {
			case 'save' :
				$tid = $data->getAttribute( 'id' );
				$portalDB->noResult( "insert into `doc-templates` ( `tmpl` ) values ( ? )" , 's' , iconv( 'utf8' , 'cp1251' , $data->nodeValue ) );
				break ;
			case 'docVar' :
				treeConvertEncoding( $docVar );
				echo '<result>'.toCDATA( json_encode( $docVar ) ).'</result>' ;
				break ;
		}

		exit();
	}

	$tmplDoc = new TDocTemplate( $tmplData[ 'tmpl' ] );
	//print_r_html( $vv );

	if ( $tmplDoc->prependText !== false ) {
		$bossPostName = !is_null( $tabPosts[ $dbConfig[ 'org.boss' ][ 'post_1_id' ] ][ 'short_name' ] ) ? $tabPosts[ $dbConfig[ 'org.boss' ][ 'post_1_id' ] ][ 'short_name' ] : $tabPosts[ $dbConfig[ 'org.boss' ][ 'post_1_id' ] ][ 'name' ];
		$tmplPTt = '<div class="tgt-displayable" title="Препровождение"><div><div id="prepend-text" contenteditable="true">'.readTextHTML( $tmplDoc->prependText , $expertize_id ).'</div><div id="boss-signature">'.$bossPostName." ".$dbConfig[ 'org.name.short' ].' ________ '.NAMES_Format( NAMES_parse( $dbConfig[ 'org.boss' ][ 'name' ] ) , '%i.%o. %F1' ).'</div></div></div>' ;
	} else {
		$tmplPTt = '' ;
	}

	if ( $tmplDoc->title !== false ) {
		$tmplTt = readTextHTML( $tmplDoc->title , $expertize_id );
	} else {
		$tmplTt = '' ;
	}

	if ( $tmplDoc->mainText !== false ) {
		$tmplMTt = readTextHTML( $tmplDoc->mainText , $expertize_id );
	} else {
		$tmplMTt = '<p></p>' ;
	}

	MainHead_Print( '' , array( '../%UT/buttons.css' , '%UT/request.css' ) , array( 'inc' => array( 'files/request.js' ) , 'init' => 'var expertizeID = '.$expertize_id.' ; var docTemplateID = '.$tmplID.' ;' ) );

	$onhbr = json_decode( ( $dbConfigFull[ 'org.name.full.head' ][ 'e-data' ] ) )->br ;
	$onnbr = json_decode( ( $dbConfigFull[ 'org.name.full.name' ][ 'e-data' ] ) )->br ;
	$pageBarCodeType = $docVarVal[ 'cfg:docs-barcode-type' ];
	$pageBarCode = $docVarVal[ 'page-code' ];



		echo '<div id="page-area" class="page-paper-size-A4"><div id="data-area">
			<div class="tgt-displayable" title="Шапка с гербом"><div id="page-head">
				<div id="head-left">
					<img src="themes/'.$UserThemeLoc.'/nationalEmblem.png" class="nationalEmblem"><br>
					<div class="org-name">
					'.breakLineByRule( inForm( $dbConfig[ 'org.name.full.head' ] , 1 ) , $onhbr ).'<br>
					<br>
					'.inForm( $dbConfig[ 'org.name.full.type' ] , 1 ).'<br>
					'.breakLineByRule( inForm( $dbConfig[ 'org.name.full.name' ] , 1 ) , $onnbr ).'<br>
					</div>
					'.$dbConfig[ 'org.address' ].'<br>
					Тел.: 8 ( '.$dbConfig[ 'org.phone.code' ].' ) '.$dbConfig[ 'org.phone' ].'<br>
					Факс: 8 ( '.$dbConfig[ 'org.phone.code' ].' ) '.$dbConfig[ 'org.fax' ].'<br>
					e-mail: <a class="e-mail" href="mailto:'.$dbConfig[ 'org.email' ].'">'.$dbConfig[ 'org.email' ].'</a><br>
					№ '.$docVarVal[ 'exp-number-full' ].' от '.date( 'd.m.Y' , time() ).' г.
				</div>
				<div id="head-right">
					<div class="barcode"><img id="barcode" data-type="'.$pageBarCodeType.'" src="../barcode.php?type='.$pageBarCodeType.'&src='.$pageBarCode.'"><br><span class="barcode-text">'.$pageBarCode.'</span></div>
					<div class="clear"></div>
					<div class="addressee">
						'.inForm( $docVarVal[ 'agency' ] ).'<br>
						<br>'.inForm( $docVarVal[ 'agent' ] , 1 ).'<br>
						<br>'.$docVarVal[ 'agency-address' ].'<br>
						<br>'.$docVarVal[ 'case-num' ].'
					</div>
				</div>
				<div class="clear"></div>
			</div></div>
			'.$tmplPTt.'
			<div class="tgt-displayable" title="Заголовок"><div id="title" contenteditable="true">'.$tmplTt.'</div></div>
			<div id="main-text" class="main-text" contenteditable="true">'.$tmplMTt.'</div>
		</div></div>' ;

	function prepDocVarDesc( $n ) {
		$m = array();
		$res = explode( '>' , $n );
		$i = 0 ;
		foreach ( $res as &$c ) {
			$c = '<span class="var-desc-el" style="margin-left : '.( $i++ * 16 ).'px ;">'.trim( $c ).'</span>' ;
		} unset( $c );
		return implode( $res ) ;
	}

	echo '<div class="ctrl-area">
		<div id="tgt-displayable-ia"></div>
		<div style="display : none">
			<div>
				Дата и время осмотра<br>
				<input id="tgt-date-i" type="text" value="00-'.date( "m-Y" , time() ).'" maxlength="10"><input id="tgt-time-i" type="text" value="10:00" maxlength="5">
			</div>
			<div>
				Предмет осмотра<br>
				<textarea id="tgt-object-i">Предмет осмотра</textarea>
			</div>
		</div>' ;

		$usedVars = $tmplDoc->extractVars();
		uksort( $docVar , function( $k1 , $k2 ) use ( $usedVars ) {
			$keysData = array( $k1 => 0 , $k2 => 0 );
			$w = array( 'tmpl' => 1 , 'ext' => 4 , '' => 3 , 'env' => 2 , 'cfg' => 0 );

			foreach( $keysData as $k => &$v ) {
				$c = varCategory( $k );
				if ( isset( $w[ $c ] ) ) {
					$v += 10 * $w[ $c ];
				}
				if ( isset( $usedVars[ $k ] ) ) {
					$v += 100 ;
				}
			} unset( $v );
			if ( $keysData[ $k1 ] != $keysData[ $k2 ] ) {
				return ( $keysData[ $k2 ] - $keysData[ $k1 ] );
			} else {
				return strcasecmp( $k1 , $k2 );
			}
		} );
		//print_r_html( $docVar );
		echo '<div class="var-area"><table>' ;
		foreach ( $docVar as $dvk => $dvd ) {
			$varLnkT = ( $dvd[ 'mf' ] ? inForm( $dvd[ 'value' ] , 1 ) : $dvd[ 'value' ] );
			echo '<tr>
				<td>'.prepDocVarDesc( $dvd[ "desc" ] ).'</td>
				<td>
					<a unselectable="on" onselectstart="return false;" onmousedown="return false;"  class="var-lnk" onclick="doPasteVariable( \''.$dvk.'\' )"><div class="var-lnk-icon"></div><span data-editor-var-name="'.$dvk.'">'.$varLnkT.'</span></a>'.
					( !$dvd[ 'mf' ] ? '<a unselectable="on" onselectstart="return false;" onmousedown="return false;"  class="var-lnk" onclick="doEditVariable( \''.$dvk.'\' )"><div class="var-e-lnk-icon"></div></a>' : '' ).'
				</td>
			</tr>' ;
		}
		echo '</table></div>' ;

		echo '<div>
			'.( $UserID == 1 ? '<input type="checkbox" id="req-dbg-mode"> Режим отладки ' : '' ).'<a onclick="doGetRTF('.$tmplID.','.$expertize_id.')" class="btn3" target="_blank">скачать</a>
		</div>
	</div>' ;


	echo '<div>
	</div>' ;

	closeHtml_Print();