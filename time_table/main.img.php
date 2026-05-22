<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	require_once ( "../core.php" );
	require_once ( "lconfig.php" );

	TryLoginFromCookie( $PlaceID );
	if ( $LoginOk ) {
		if ( count( $UserRights ) == 1 ) {
			$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
			if ( array_key_exists( "RECORDS" , $Rights ) ) {
				$mayFill = in_array( "FILL"   , $Rights[ "RECORDS" ] );

				if ( $mayFill ) {

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

					if ( isset( $_REQUEST[ "t" ] ) ) {
						$txt = toUnicodeEntities( iconv( "utf8" , "cp1251" , $_REQUEST[ "t" ] ) );
						header( "Content-type: image/png" );

						$mw = 0 ;
						$mh = 28 ;

						$res = imagettfbbox( $font_size , 0 , $font_dir.$font_name , $txt );
						$mw = $res[ 2 ] - $res[ 0 ] ;

						$mw+= 16 ;
						$im = imagecreatetruecolor( $mh , $mw ) or die( "Cannot Initialize new GD image stream" );
						$black = imagecolorallocate( $im , 0 , 0 , 0 );
						$white = imagecolorallocate( $im , 255 , 255 , 255 );
						imagefilledrectangle( $im , 0 , 0 , $mh , $mw , $white );


						$cx = round( ( $mh - $res[ 7 ] - $res[ 1 ] ) / 2 ) ;
						$cy = -$res[ 0 ] - 8 ;
						imagettftext( $im , $font_size , 90 , $cx , $mw + $cy , $black , $font_dir.$font_name , $txt );

						imagepng( $im );
						imagedestroy( $im );

						exit();
					}
				}
			}
		}
	}
?>