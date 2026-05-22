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
	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( isset( $_REQUEST [ 'mode' ] ) && $_REQUEST [ 'mode' ] == 'edit-template' ) {
		if ( !isset( $_REQUEST[ 'id' ] ) ) {
			error_log( 'DBG : no id' );
			exit();
		} else {
			$tmplID = trim( $_REQUEST[ 'id' ] );
		}
		$tmplData = $portalDB->row( "select * from `doc-templates` where `id` = ?" , 'i' , $tmplID );
		if ( $tmplData === false ) {
			error_log( 'DBG : tmplData === false' );
			exit();
		}

		if ( !isset( $_REQUEST[ 'root-id' ] ) ) {
			error_log( 'DBG : no root-id' );
			exit();
		} else {
			$rootID = trim( $_REQUEST[ 'root-id' ] );
		}

		MainHead_Print(
			'' ,
			array( '../%UT/base.css' , '../%UT/buttons.css' , './%UT/preview.css' , '/doc-generator/%UT/forms.css' ) ,
			array(
				'inc'  => array( '/doc-generator/doc-generator.base.js' , '/doc-generator/doc-generator.js' , './files/preview.js' ) ,
				'init' => 'let DGData = {
				rootID : '.$rootID.' ,
				tmplID : '.$tmplID.'
			};'
			)
		);

		echo '<input type="checkbox" id="doc-vis">
		<label for="selected-doc">Часть № </label><select id="selected-doc" onchange="doSelectDocument()"></select>
		<div id="main-area" class="main-area">' ;
		echo '</div></div>' ;

		echo '<div class="ctrl-area">
		<div>Шаблон: '.$tmplData[ 'name' ].'</div>
		<div>
			<form action="template-import.docx.php?tmpl-id='.$tmplID.'" target="_blank" method="post" enctype="multipart/form-data">
				<label>Загрузить из файла <input type="file" name="tmpl-docx" accept=".docx"></label>
				<button type="submit" class="btn1">Загрузить</button>
			</form>
		</div>
		<label id="doc-vis-ctrl" for="doc-vis"></label>
		<div id="tgt-displayable-ia"></div>' ;

		echo '<div style="text-align : left">
			'.( $UserID == 100000 ? '<input type="checkbox" id="req-dbg-mode"> Режим отладки ' : '' ).'<a id="btn-download-pdf" class="btn3" target="_blank">скачать PDF</a>
			'.( $UserID == 100000 ? '<input type="checkbox" id="req-dbg-mode"> Режим отладки ' : '' ).'<a id="btn-download-rtf" class="btn3" target="_blank">скачать RTF</a>
		</div>
	</div>' ;


		closeHtml_Print();


	} else {
		if ( !isset( $_REQUEST[ 'id' ] ) ) {
			error_log( 'DBG : no id' );
			exit();
		} else {
			$docID = trim( $_REQUEST[ 'id' ] );
		}

		$docGeneratorData = $portalDB->simpleRow( 'doc-generator-data' , array(
			'doc_id'  => $docID ,
			'user_id' => $UserID ,
		) );

		if ( $docGeneratorData === false ) {
			error_log( 'DBG : docGeneratorData === false' );
			exit();
		}

		$tmplID = $docGeneratorData[ 'tmpl_id' ];
		$root_id = $docGeneratorData[ 'root_id' ];

		$tmplData = $portalDB->row( "select * from `doc-templates` where `id` = ?" , 'i' , $tmplID );

		MainHead_Print(
			'' ,
			array( '../%UT/base.css' , '../%UT/buttons.css' , './%UT/preview.css' , '/doc-generator/%UT/forms.css' ) ,
			array(
				'inc'  => array( '/doc-generator/doc-generator.base.js' , '/doc-generator/doc-generator.js' , './files/preview.js' ) ,
				'init' => 'let DGData = {
				docID : "'.$docID.'" ,
				rootID : '.$root_id.' ,
				tmplID : '.$tmplID.'
			};'
			)
		);

		echo '<input type="checkbox" id="doc-vis">
		<label for="selected-doc">Часть № </label><select id="selected-doc" onchange="doSelectDocument()"></select>
		<div id="main-area" class="main-area">' ;
		echo '</div></div>' ;

		echo '<div class="ctrl-area" style="right : -8cm">
		<label id="doc-vis-ctrl" for="doc-vis"></label>
		<div id="tgt-displayable-ia"></div>' ;

		echo '<div style="text-align : left">
			'.( $UserID == 100000 ? '<input type="checkbox" id="req-dbg-mode"> Режим отладки ' : '' ).'<a id="btn-download-pdf" class="btn3" target="_blank">скачать PDF</a>
			'.( $UserID == 100000 ? '<input type="checkbox" id="req-dbg-mode"> Режим отладки ' : '' ).'<a id="btn-download-rtf" class="btn3" target="_blank">скачать RTF</a>
		</div>
	</div>' ;


		closeHtml_Print();

		$portalDB->updateRow( 'doc-generator-data' , array(
			'id' => $docGeneratorData[ 'id' ] ,
			'time_edited' => time()
		) );
	}

