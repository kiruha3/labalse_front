<?php
	if ( !isset( $_REQUEST[ "p" ] ) ) {

		echo "<a href=\"?p=".base64_encode( json_encode( array( "type" => "case-cat-v" , "imgType" => "png" , "index" => 1 ) ) )."\">view</a>" ;
		exit();
	}

	include_once("../core.php");
	require_once "lconfig.php";

	$font_dir = "../files/fonts/" ;
	$font_name = "verdanab.ttf" ;
	$font_size = 8 ;

	function toUnicodeEntities( $text , $from = "w" ) {
		$text = convert_cyr_string( $text , $from , "i" );
		$uni = "" ;
		for ( $i = 0 , $len = strlen( $text ) ; $i < $len ; $i++ ) {
			$char = $text[ $i ];
			$code = ord( $char );
			$uni.= $code > 175 ? "&#".( 1040 + ( $code - 176 ) ).";" : $char ;
		}
		return $uni ;
	}


	$params = $_REQUEST[ "p" ];
	$params = json_decode( base64_decode( $params ) );
	//var_dump( $params );

	header( "Content-type: image/".$params->imgType );

	switch ( $params->type ) {
		case "case-cat-v" :
			$v_imgs1 = intval( $params->index );

			$tabCaseCategory = $portalDB->table( "casecategory" );

			$mw = 0 ;
			$mh = 56 ;
			$ind = -1 ;
			$tmp = "" ;
			$txt = "" ;
			for ( $i = 0 ; $i < count( $tabCaseCategory ) ; $i++ ) {
				$tmp = inForm( $tabCaseCategory[ $i ][ "name" ] , 1 , false ) ;
				$res = imagettfbbox( $font_size , 0 , $font_dir.$font_name , toUnicodeEntities( $tmp ) );
				if ( $mw < $res[ 2 ] - $res[ 0 ] ) {
					$mw = $res[ 2 ] - $res[ 0 ] ;
				}
				if ( $tabCaseCategory[ $i ][ "index" ] == $v_imgs1 ) {
					$ind = $i ;
					$br = $res ;
					$txt = $tmp ;
				}
			}

			$mw+= 16 ;
			$im = imagecreatetruecolor( $mh , $mw ) or die( "Cannot Initialize new GD image stream" );
			$white = imagecolorallocate( $im , 64 , 64 , 64 );
			$black = imagecolorallocate( $im , 255 , 244 , 255 );
			imagefilledrectangle( $im , 0 , 0 , $mh , $mw , $white );

			if ( $ind > -1 ) {
				$cx = round( ( 56 - $br[ 7 ] - $br[ 1 ] ) / 2 ) ;
				$cy = -$br[ 0 ] - 8 ;
				imagettftext( $im , $font_size , 90 , $cx , $mw + $cy , $black , $font_dir.$font_name , toUnicodeEntities( $txt) );
			}

			break ;

		case "text" :
			$txt = iconv( "utf8" , "cp1251" , $params->text );
			$txt = explode( "\n" , trim( $txt ) );
			$font_name = $font_dir.$params->fn.".ttf" ;

			$fgColor = colorToIntArray( $params->fgc );
			$bkColor = colorToIntArray( $params->bgc );

			$SH = 0 ;
			$SW = 0 ;
			foreach ( $txt as &$ctxt ) {
				$ctxt = toUnicodeEntities( $ctxt );
				$br = imagettfbbox( $params->fs , 0 , $font_name , $ctxt );
				//print_r_html( $br , true );
				$SH = max( $br[ 1 ] - $br[ 7 ] , $SH );
				$SW = max( $br[ 2 ] - $br[ 0 ] , $SW );
				$ctxt = array( "txt" => $ctxt , "y" => -$br[ 7 ] );
			} unset( $ctxt );
			$SH++ ;
			$SW+= 2 ;

			//print_r_html( $txt , true );

			$im = imagecreate( $SW , $SH * count( $txt ) );
			$bkColor = imagecolorallocate( $im , $bkColor[ "r" ] , $bkColor[ "g" ] , $bkColor[ "b" ] );
			$fgColor = imagecolorallocate( $im , $fgColor[ "r" ] , $fgColor[ "g" ] , $fgColor[ "b" ] );

			$cy = 0 ;
			foreach ( $txt as &$ctxt ) {
				imagettftext( $im , $params->fs , 0 , 0 , $cy + $ctxt[ "y" ] , $fgColor , $font_name ,  $ctxt[ "txt" ] );
				$cy+= $SH ;
			} unset( $ctxt );

			$im = imagerotate( $im , 90 , 0 );

			break ;
	}

	switch ( $params->imgType ) {
		case "png" :
			imagepng( $im );
			break ;
		case "jpeg" :
			imagejpeg( $im );
			break ;
	}

	imagedestroy( $im );
?>