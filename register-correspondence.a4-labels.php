<?php

	require_once ( 'core.php' );
	require_once ( 'ext-lib/rtf-gen.php' );

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

		if ( array_key_exists( 'REGISTER-CORRESPONDENCE' , $Rights ) ) {
			$mayOutput = in_array( 'OUTPUT' , $Rights[ 'REGISTER-CORRESPONDENCE' ] );
			$GoOut = !$mayOutput ;
		} else {
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit ;
	}

	header( 'Content-Type: application/rtf' );
	header( 'Content-Disposition: attachment;filename="Этикетки '.date( 'Y.m.d H-i' , time() ).'.rtf"' );

	if ( isset( $_REQUEST[ 'selected' ] ) ) {
		$ida = explode( ',' , trim( $_REQUEST[ 'selected' ] ) );
		$idl = array();
		foreach( $ida as $id ) {
			$mm = explode( '-' , trim( $id ) );
			if ( count( $mm ) == 1 ) {
				$idl[]= $mm[ 0 ];
			} else
			if ( count( $mm ) == 2 ) {
				for( $i = $mm[ 0 ] ; $i <= $mm[ 1 ] ; $i++ ) {
					$idl[]= $i ;
				}
			}
		}

		if ( count( $idl ) > 0 ) {
			$res = $portalDB->query( 'select * from `register-correspondence` where ( `id` in ( ?* ) )' , false , '*i' , $idl );
			$portalDB->noResult( 'update `register-correspondence` set `print_date` = ? where ( `id` in ( ?* ) )' , 'i*i' , time() , $idl );
		} else {
			$res = array();
		}

	}

	$doc = new RTFDocument();
	$doc->paperFormat = PAPER_SIZE_A4_LANDSCAPE ;
	$doc->margins = '5mm' ;


	$doc->setMainContext();
	$doc->setFontSize( '12pt' );

	$tbl = $doc->addTable();

	$i = 1 ;
	foreach( $res as &$row ) {
		$row[ 'comment' ] = str_replace( ',' , ', ' , $row[ 'comment' ] );
		$row[ 'comment' ] = str_replace( '  ' , ' ' , $row[ 'comment' ] );
		$row[ 'destination' ] = str_replace( ',' , ', ' , $row[ 'destination' ] );
		$row[ 'destination' ] = str_replace( '  ' , ' ' , $row[ 'destination' ] );
		$row[ 'addressee' ] = str_replace( ',' , ', ' , $row[ 'addressee' ] );
		$row[ 'addressee' ] = str_replace( '  ' , ' ' , $row[ 'addressee' ] );

		if ( $i % 4 == 1 ) {
			$rN = $tbl->insertRow();
			$rN->height = '14mm' ;
			$rA = $tbl->insertRow();
			$rA->height = '14mm' ;
		}

		$i++ ;

		$c = $rN->insertCell();
		$c->width = '7cm' ;
		$c->setBorders( 'ltr' , 's' );
		$c->setBorders( 'b' , 'none' );
		$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->addText( $row[ 'addressee' ] );

		$c = $rA->insertCell();
		$c->width = '7cm' ;
		$c->setBorders( 'lrb' , 's' );
		$c->setBorders( 't' , 'none' );
		$doc->setTableCellContext( $c )->setTextAlign( TEXT_ALIGN_LEFT )->addText( $row[ 'destination' ] );
	} unset( $row );



	$doc->write();
?>