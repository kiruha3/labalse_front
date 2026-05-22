<?php
	require_once ( './class/TQRCode.class.php' );

	$src = $argv[ 1 ];
	$bc = new TQRCode();
	$qrCodeData = $bc->generate( $src , 'L' , array() );
	$qrCode = $qrCodeData[ "image" ];
	$sS = $qrCodeData[ "size" ];

	$white = '*' ;
	$black = ' ' ;
	$ctt = array( " " => $white , "A" => $white , "B" => $black , "0" => $white , "1" => $black );
	for( $i = 0 ; $i < $sS ; $i++ ) {
		for( $j = 0 ; $j < $sS ; $j++ ) {
			$cc = $qrCode[ $i ][ $j ];
			$cc = $ctt[ $cc ];
			echo $cc ;
		}
		echo "\n" ;
	}

