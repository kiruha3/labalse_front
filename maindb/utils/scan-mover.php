<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once ( '../../core.php' );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 */
	require_once ( '../lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	require_once( '../../cores/core.maindb.php' );

	$modeAjax = isset( $_REQUEST[ 'mode' ] ) && $_REQUEST[ 'mode' ] == 'ajax' ;

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		if ( !$modeAjax ) {
			Redirect( '/auth.php' );
		} else {
			exit();
		}
	}

	if ( count( $UserRights ) != 1 ) {
		if ( !$modeAjax ) {
			MainHead_L2( '' , '' , array( '/%UT/buttons.css' , '/%UT/forms.css' ) , array() , 'hlp/no_access.html' );
			echo '<br><br><br><br><br>' ;
			MessageForm();
			closeHtml();
			exit();
		} else {
			exit();
		}
	}

	$Rights= ParseRights( strtoupper( $UserRights[ 0 ] ) );

	if ( array_key_exists( 'UTILS' , $Rights ) ) {
		$mayScanManualProcessing = in_array( 'SCAN-MANUAL-PROCESSING' , $Rights[ 'UTILS' ] );
	} else{
		$mayScanManualProcessing = false ;
	}
	$GoOut = !$mayScanManualProcessing ;

	if ( $GoOut ) {
		if ( $modeAjax ) {
			exit ;
		} else {
			ErrorMessage( 403 );
		}
	}

	if ( isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ) {
		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		$ajaxRequest = simplexml_load_string( $_REQUEST[ "data" ] , 'SimpleXMLElement' , LIBXML_NOCDATA );

		switch ( $ajaxRequest->getName() ) {
			case "get-id" :
				$ten = $ajaxRequest[ "n" ];
				$tey = $ajaxRequest[ "y" ];
				$tid = matincomingID( $ten , $tey );

				echo "<result id=\"".$tid."\" />" ;
				break ;

		}

		exit();
	}


	MainHead_L2( "" , "" , array( "../../%UT/buttons.css" , "../../%UT/forms.css" , "../%UT/main.css" , "scan-mover.css" ) , array( "/ext-lib/pdf.js/build/pdf.js" , "/ext-lib/pdf.js/build/pdf.worker.js" , "../files/main.js" , "scan-mover.js" ) , "hlp/no_access.html" );
	echo "<div class=\"target-list-area\">
		<div id=\"target-list-area-locker\" class=\"target-list-area-locker\" style=\"display : none ;\"></div>
		<div id=\"target-list\" class=\"target-list\">" ;
	echo "</div>
	</div>" ;

	echo "<div class=\"pdf-preview-area\">
		<div id=\"ppa\" class=\"pdf-preview-wrapper\"></div>
	</div>" ;

	$cy = date( "Y" , time() );

	$yearsList = $portalDB->query( "select YEAR( `date` ) as `year` from `matincoming` where ( `date` is not null ) group by YEAR( `date` ) order by YEAR( `date` ) desc ;" );
	
	$space = str_repeat( '&nbsp;' , 4 );

	echo '<div class="toolbar">
		<a id="move-btn" onclick="doMove()" class="btn3">
			Привязать к экспертизе № </a> <input type="text" id="exp-num" class="exp-num" oninput="numInput()">
			<select id="exp-year" class="exp-year">'.makeSimpleSelectTagOptions( $yearsList , 'year' , 'year' , $cy ).'</select>
			<select id="doc-type">
				<option value="0110">Заключение</option>
				<option value="1010">Пост/Опред</option>
				<option value="0420">Карт.движ.мат</option>
				<option value="0430">Карт.вещ.док</option>
				<option value="0610">Ход. объект</option>
				<option value="0620">Ход. срок</option>
				<option value="0600">Ход. другое</option>
				<option value="1410">Доп.мат</option>
				<option value="0520">увед. о сроках</option>
				<option value="0500">увед. другое</option>
				<option value="0890">Прочие рапорты</option>
				<option value="0910">Отчет факса</option>
				<option value="0990">Прочие отчеты</option>
				<option disabled>По оплате</option>
				<option value="3110">'.$space.'Счет</option>
				<option value="3114">'.$space.'Квитанция</option>
				<option value="3190">'.$space.'Прочие документы</option>
			</select><br>
		<div>
			<table id="exp-tab"></table>
		</div>
		<a id="delete-btn" onclick="doDelete()" class="btn3">Удалить</a>
	</div>' ;

	closeHtml();
