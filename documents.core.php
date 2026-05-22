<?php
	namespace Documents ;

	require_once ( 'core.php' );
	require_once ( 'barcode.php' );

	$iconMap = array(
		'.pdf' => 'pdf-24.png' ,
		//'.txt' => 'pdf-24.png'
	);

	/**
	 * @param $fi array File info
	 * @param $ficn array File info corrected name
	 * @param $escf string Name of <b>exStyleCalc</b> function
	 * @return string
	 */
	function integrateDocHTML( &$fi , &$ficn , $escf ) {
		$downloadMainBtn = '<a href="/documents.php?download='.$fi[ 'id' ].'" class="std-docs-lnk" target="_blank" title="'.$ficn[ 'descr' ].'">'.$ficn[ 'icon' ].'<span>'.$ficn[ 'name' ].'</span></a>' ;
		$downloadPackBtn = '' ;
		if ( $fi[ 'pack-data' ] != '' ) {
			$packData = json_decode( $fi[ 'pack-data' ] , true );
			if ( $packData[ 'pack-type' ] == 'tar' && $packData[ 'content-type' ] == 'main-and-signs' ) {
				$downloadPackBtn = '<a href="/documents.php?download='.$fi[ 'id' ].'&full" class="std-docs-pack-lnk" target="_blank" title="скачать весь архив"><img></a>' ;
			}
		}

		return '<div class="std-docs-btn'.( $ficn[ 'style' ] !== '' ? ' std-docs-btn-style'.$ficn[ 'style' ] : '' ).$escf( $fi ).'">'.$downloadMainBtn.$downloadPackBtn.$ficn[ 'delete-btn' ].'</div>' ;
	}

	function getIconHTML( $name ) {
		global $iconMap ;
		$n = preg_match( '/\.[^\.]+$/' , $name , $m );
		if ( $n == 1 ) {
			$m = strtolower( $m[ 0 ] );
		} else {
			$m = '' ;
		}
		return ( isset( $iconMap[ $m ] ) ? '<img src="/themes/icons/'.$iconMap[ $m ].'" class="std-docs-lnk-icon">' : '' );
	}

	function integrateDocXML( &$fi , &$ficn , $escf ) {
		$dpd = '' ;
		if ( $fi[ 'pack-data' ] != '' ) {
			$packData = json_decode( $fi[ 'pack-data' ] , true );
			if ( $packData[ 'pack-type' ] == 'tar' && $packData[ 'content-type' ] == 'main-and-signs' ) {
				$dpd = '<doc-pack-data/>' ;
			}
		}

		return '<f id="'.$fi[ 'id' ].'" style="'.$ficn[ 'style' ].'" ex-style="'.$escf( $fi ).'" icon="'.$ficn[ 'icon' ].'">
			<name>'.toCDATA( $ficn[ 'name' ] ).'</name>
			<descr>'.toCDATA( $ficn[ 'descr' ] ).'</descr>
			'.$dpd.'
		</f>' ;
	}

	function getIconXML( $name ) {
		global $iconMap ;
		$n = preg_match( '/\.[^\.]+$/' , $name , $m );
		if ( $n == 1 ) {
			$m = strtolower( $m[ 0 ] );
		} else {
			$m = '' ;
		}
		return ( isset( $iconMap[ $m ] ) ? '<icon type="'.$m.'" file="'.$iconMap[ $m ].'"/>' : '' );
	}

	function getIconNone( $name ) {
		return '' ;
	}

	function exStyleCalcNone( $param ) {
		return '' ;
	}

	function correctDocName( $origId , $name , $time ) {
		global $getBarCodeExtIDMap , $docStyles ;
		$fiName = array();
		$fiDesc = array();
		$fiStyle = '' ;
		if ( isset( $origId ) ) {
			$nidS = getCharIDStructure( $origId );
		} else {
			$nidS = false ;
		}

		if ( $nidS !== false ) {
			$nidSt = $nidS[ 't' ];
			if ( isset( $getBarCodeExtIDMap[ $nidSt ][ 'sname' ] ) ) {
				$fiName = $getBarCodeExtIDMap[ $nidSt ][ 'sname' ];
			} else
			if ( isset( $getBarCodeExtIDMap[ $nidSt ][ 'name' ] ) ) {
				$fiName = $getBarCodeExtIDMap[ $nidSt ][ 'name' ];
			} else {
				$fiName = $name ;
			}

			if ( isset( $getBarCodeExtIDMap[ $nidSt ][ 'name' ] ) ) {
				$fiDesc[]= $getBarCodeExtIDMap[ $nidSt ][ 'name' ];
			}

			if ( isset( $docStyles[ $nidSt ][ 'lnk-style' ] ) ) {
				$fiStyle = $docStyles[ $nidSt ][ 'lnk-style' ];
			}
		} else
		if ( preg_match( '/^(заключение)(?:-\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})?\.pdf/i' , $name , $fiName ) == 1 ) {
			$fiName = $fiName[ 1 ];
			$fiStyle = '-green' ;
		} else
		if ( preg_match( '/^(карточка)(?:-\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})?\.pdf/i' , $name , $fiName ) == 1 ) {
			$fiName = $fiName[ 1 ];
			$fiStyle = '-gray' ;
		} else
		if ( preg_match( '/^(доп.мат)(?:-\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})?\.pdf/i' , $name , $fiName ) == 1 ) {
			$fiName = $fiName[ 1 ];
			$fiStyle = '-blue' ;
		} else {
			$fiName = $name ;
		}

		$fiDesc[]= 'загружен '.date( 'd.m.Y' , $time );
		$fiDesc = implode( "\r\n" , $fiDesc );

		return array(
			'name'  => $fiName ,
			'descr' => $fiDesc ,
			'style' => $fiStyle
		);
	}

	function integrate( $docs , $opt = false ) {
		global $portalDB ;

		$defOpt = array(
			'docs'          => 'info' , // docs массив (выборка) из documents
			                // 'id'		  единичный или массив documents.id
			                // 'ref'      содержит ext_id (единичный или массив) и ext_type
			'ex-style-calc' => false ,
			'upload'        => false ,
			'show-icons'    => false ,
			'output'        => 'html' ,
			                // 'xml'
		);

		if ( $opt !== false ) {
			if ( is_string( $opt ) ) {
				$opt = json_decode( $opt , true );
			}
			$opt = array_merge( $defOpt , $opt );
		} else {
			$opt = $defOpt ;
		}

		switch( $opt[ 'docs' ] ) {
			case 'info' :
				break ;

			case 'id' :
				if ( !is_array( $docs ) ) {
					$docs = array( $docs );
				}
				$docs = $portalDB->simpleQuery( 'documents' , array( 'id' => $docs ) );
				break ;

			case 'ref' :
				$docs = getDocs( $docs[ 'ext_id' ] , $docs[ 'ext_type' ] );
				break ;
		}



		$res = array();

		if ( $opt[ 'ex-style-calc' ] !== false ) {
			$optExStyleCalcFn = $opt[ 'ex-style-calc' ];
		} else {
			$optExStyleCalcFn = 'Documents\exStyleCalcNone' ;
		}

		switch ( $opt[ 'output' ] ) {
			case 'html' :
				$getIconFn = $opt[ 'show-icons' ] ? 'Documents\getIconHTML' : 'Documents\getIconNone' ;
				$integrateFn = 'Documents\integrateDocHTML' ;
				break ;

			case 'xml' :
				$getIconFn = $opt[ 'show-icons' ] ? 'Documents\getIconXML' : 'Documents\getIconNone' ;
				$integrateFn = 'Documents\integrateDocXML' ;
				break ;

			default :
				$getIconFn = 'Documents\getIconNone' ;
				$integrateFn = 'Documents\integrateDocHTML' ;
				break ;
		}


		foreach( $docs as &$fi ) {
			$fiExData = correctDocName( $fi[ 'orig-id' ] , $fi[ 'name' ] , $fi[ 'time' ] );
			$fiExData[ 'icon' ] = $getIconFn( $fi[ 'name' ] );
			$fiExData[ 'delete-btn' ] = '' ;

			$res[]= $integrateFn( $fi , $fiExData , $optExStyleCalcFn );
		} unset( $fi );

		if ( $opt[ 'upload' ] !== false && is_array( $opt[ 'upload' ] ) ) {
			$res[]= '<div class="std-docs-lnk-add-btn-area" ><a class="std-docs-lnk-add-btn" onclick="showFUDlg( \''.$opt[ 'upload' ][ 'ext_id' ]."' , '".$opt[ 'upload' ][ 'ext_type' ].'\' )"></a></div>' ;
		}

		return implode( $res );
	}

	function uploadForm( $docs , $opt = false ) {

	}
