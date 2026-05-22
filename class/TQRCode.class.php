<?php

	define( "QRCODE_VCCP" , "1111100100101" );
	define( "QRCODE_FCCP" , "10100110111" );
	define( "QRCODE_MODE_NUM" , "num" );
	define( "QRCODE_MODE_ALPHANUM" , "alpha-num" );
	define( "QRCODE_MODE_BYTE" , "byte" );
	define( "QRCODE_MODE_AUTO" , "auto" );

	class TQRCode {
		private $modePat = array();
		private $stdTab1 = array();
		private $stdTab3 = array();
		private $stdTab5rev = array();
		private $stdTab9 = array();
		private $stdTabE1 = array();
		private $errLvlCodeMap = array();
		private $modeCodeMap = array();
		private $stop = "0000" ;
		private $fillers = array( "11101100" , "00010001" );
		private $maskFunc = array();

		private $EXP_TAB = array();
		private $LOG_TAB = array();

		private $c = array();

		function __construct() {
			$this->modePat = array(
				QRCODE_MODE_NUM      => '\d+' ,
				QRCODE_MODE_ALPHANUM => '[A-Z0-9-$%*+./: ]+' ,
				QRCODE_MODE_BYTE     => '.+'
			);
			$this->c = array( "A" , "B" );


			$this->stdTab3 =
				array_fill_keys( range(  1 ,  9 ) , array( QRCODE_MODE_NUM => 10 , QRCODE_MODE_ALPHANUM =>  9 , QRCODE_MODE_BYTE =>  8 ) ) +
				array_fill_keys( range( 10 , 26 ) , array( QRCODE_MODE_NUM => 12 , QRCODE_MODE_ALPHANUM => 11 , QRCODE_MODE_BYTE => 16 ) ) +
				array_fill_keys( range( 27 , 40 ) , array( QRCODE_MODE_NUM => 14 , QRCODE_MODE_ALPHANUM => 13 , QRCODE_MODE_BYTE => 16 ) );

			$stdTabE1 = array( 1 => array() );
			$stdTabE1 = $stdTabE1 + array_fill_keys( range( 2 , 40 ) , array( 6 ) );
			$stdTabE1 = array_insert_col_keys( $stdTabE1 , array_combine( range(  2 , 40 ) , explode( "," , "18,22,26,30,34,22,24,26,28,30,32,34,26,26,26,30,30,30,34,28,26,30,28,32,30,34,26,30,26,30,34,30,34,30,24,28,32,26,30" ) ) );
			$stdTabE1 = array_insert_col_keys( $stdTabE1 , array_combine( range(  7 , 40 ) , explode( "," ,                "38,42,46,50,54,58,62,46,48,50,54,56,58,62,50,50,54,54,58,58,62,50,54,52,56,60,58,62,54,50,54,58,54,58" ) ) );
			$stdTabE1 = array_insert_col_keys( $stdTabE1 , array_combine( range( 14 , 40 ) , explode( "," ,                                     "66,70,74,78,82,86,90,72,74,78,80,84,86,90,74,78,78,82,86,86,90,78,76,80,84,82,86" ) ) );
			$stdTabE1 = array_insert_col_keys( $stdTabE1 , array_combine( range( 21 , 40 ) , explode( "," ,                                         "94,98,102,106,110,114,118,98,102,104,108,112,114,118,102,102,106,110,110,114" ) ) );
			$stdTabE1 = array_insert_col_keys( $stdTabE1 , array_combine( range( 28 , 40 ) , explode( "," ,                                                                  "122,126,130,134,138,142,146,126,128,132,136,138,142" ) ) );
			$stdTabE1 = array_insert_col_keys( $stdTabE1 , array_combine( range( 35 , 40 ) , explode( "," ,                                                                                              "150,154,158,162,166,170" ) ) );
			$this->stdTabE1 = $stdTabE1 ;

			for( $v = 1 ; $v <= 40 ; $v++ ) {
				$A = 17 + $v * 4 ;
				$t = count( $stdTabE1[ $v ] );
				$B = 64 * 3 + 2 * ( $A - 16 ) + ( $v > 1 ? ( $t * $t - 3 ) * 25 - 2 * ( $t - 2 ) * 5 : 0 );
				$C = 31 + ( $v >= 7 ? 36 : 0 );
				$D = $A * $A - $B - $C ;
				$F = $D % 8 ;
				$E = ( $D - $F ) / 8 ;
				$this->stdTab1[ $v ] = array( "v" => $v , "A" => $A , "B" => $B , "C" => $C , "D" => $D , "E" => $E , "F" => $F );
			}

			$EXP_TAB = array( 1 );
			for( $i = 1 ; $i < 256 ; $i++ ) {
				$v = $EXP_TAB[ $i - 1 ] * 2 ;
				if ( $v > 255 ) {
					$v = $v ^ 285 ;
				}
				$EXP_TAB[ $i ] = $v ;
			}
			$this->EXP_TAB = $EXP_TAB ;

			$LOG_TAB = array();
			for( $i = 1 ; $i < 256 ; $i++ ) {
				$LOG_TAB[ $EXP_TAB[ $i ] ] = $i ;
			}
			$this->LOG_TAB = $LOG_TAB ;

			$this->errLvlCodeMap = array( "L" => 1 , "M" => 0 , "Q" => 3 , "H" => 2 );

			$this->modeCodeMap = array( QRCODE_MODE_NUM => "0001" , QRCODE_MODE_ALPHANUM => "0010" , QRCODE_MODE_BYTE => "0100" );

			$stdTab9 = array();
			/*$stdTab9 = json_decode(
				'{"0":{"L":[[1,26,19,2]],"M":[[1,26,16,4]],"Q":[[1,26,13,6]],"H":[[1,26,9,8]]}'.
				 '"1":{"L":[[1,26,19,2]],"M":[[1,26,16,4]],"Q":[[1,26,13,6]],"H":[[1,26,9,8]]}'.
				'}' ,
			true );*/
			$stdTab9[ 1 ] = array( "L" => array( array( 1 ,26, 19, 2) ) , "M" => array( array( 1 ,26, 16, 4) ) , "Q" => array( array( 1 ,26, 13, 6) ) , "H" => array( array( 1 ,26, 9, 8) ) );
			$stdTab9[ 2 ] = array( "L" => array( array( 1 ,44, 34, 4) ) , "M" => array( array( 1 ,44, 28, 8) ) , "Q" => array( array( 1 ,44, 22, 11) ) , "H" => array( array( 1 ,44, 16, 14) ) );
			$stdTab9[ 3 ] = array( "L" => array( array( 1 ,70, 55, 7) ) , "M" => array( array( 1 ,70, 44, 13) ) , "Q" => array( array( 2 ,35, 17, 9) ) , "H" => array( array( 2 ,35, 13, 11) ) );
			$stdTab9[ 4 ] = array( "L" => array( array( 1 ,100, 80, 10) ) , "M" => array( array( 2 ,50, 32, 9) ) , "Q" => array( array( 2 ,50, 24, 13) ) , "H" => array( array( 4 ,25, 9, 8) ) );
			$stdTab9[ 5 ] = array( "L" => array( array( 1 ,134, 108, 13) ) , "M" => array( array( 2 ,67, 43, 12) ) , "Q" => array( array( 2 ,33, 15, 9) , array( 2 , 34, 16, 9) ) , "H" => array( array( 2 ,33, 11, 11) , array( 2 , 34, 12, 11) ) );
			$stdTab9[ 6 ] = array( "L" => array( array( 2 ,86, 68, 9) ) , "M" => array( array( 4 ,43, 27, 8) ) , "Q" => array( array( 4 ,43, 19, 12) ) , "H" => array( array( 4 ,43, 15, 14) ) );
			$stdTab9[ 7 ] = array( "L" => array( array( 2 ,98, 78, 10) ) , "M" => array( array( 4 ,49, 31, 9) ) , "Q" => array( array( 2 ,32, 14, 9) , array( 4 , 33, 15, 9) ) , "H" => array( array( 4 ,39, 13, 13) , array( 1 , 40, 14, 13) ) );
			$stdTab9[ 8 ] = array( "L" => array( array( 2 ,121, 97, 12) ) , "M" => array( array( 2 ,60, 38, 11) , array( 2 , 61, 39, 11) ) , "Q" => array( array( 4 ,40, 18, 11) , array( 2 , 41, 19, 11) ) , "H" => array( array( 4 ,40, 14, 13) , array( 2 , 41, 15, 13) ) );
			$stdTab9[ 9 ] = array( "L" => array( array( 2 ,146, 116, 15) ) , "M" => array( array( 3 ,58, 36, 11) , array( 2 , 59, 37, 11) ) , "Q" => array( array( 4 ,36, 16, 10) , array( 4 , 37, 17, 10) ) , "H" => array( array( 4 ,36, 12, 12) , array( 4 , 37, 13, 12) ) );
			$stdTab9[ 10 ] = array( "L" => array( array( 2 ,86, 68, 9) , array( 2 , 87, 69, 9) ) , "M" => array( array( 4 ,69, 43, 13) , array( 1 , 70, 44, 13) ) , "Q" => array( array( 6 ,43, 19, 12) , array( 2 , 44, 20, 12) ) , "H" => array( array( 6 ,43, 15, 14) , array( 2 , 44, 16, 14) ) );
			$stdTab9[ 11 ] = array( "L" => array( array( 4 ,101, 81, 10) ) , "M" => array( array( 1 ,80, 50, 15) , array( 4 , 81, 51, 15) ) , "Q" => array( array( 4 ,50, 22, 14) , array( 4 , 51, 23, 14) ) , "H" => array( array( 3 ,36, 12, 12) , array( 8 , 37, 13, 12) ) );
			$stdTab9[ 12 ] = array( "L" => array( array( 2 ,116, 92, 12) , array( 2 , 117, 93, 12) ) , "M" => array( array( 6 ,58, 36, 11) , array( 2 , 59, 37, 11) ) , "Q" => array( array( 4 ,46, 20, 13) , array( 6 , 47, 21, 13) ) , "H" => array( array( 7 ,42, 14, 14) , array( 4 , 43, 15, 14) ) );
			$stdTab9[ 13 ] = array( "L" => array( array( 4 ,133, 107, 13) ) , "M" => array( array( 8 ,59, 37, 11) , array( 1 , 60, 38, 11) ) , "Q" => array( array( 8 ,44, 20, 12) , array( 4 , 45, 21, 12) ) , "H" => array( array( 12 ,33, 11, 11) , array( 4 , 34, 12, 11) ) );
			$stdTab9[ 14 ] = array( "L" => array( array( 3 ,145, 115, 15) , array( 1 , 146, 116, 15) ) , "M" => array( array( 4 ,64, 40, 12) , array( 5 , 65, 41, 12) ) , "Q" => array( array( 11 ,36, 16, 10) , array( 5 , 37, 17, 10) ) , "H" => array( array( 11 ,36, 12, 12) , array( 5 , 37, 13, 12) ) );
			$stdTab9[ 15 ] = array( "L" => array( array( 5 ,109, 87, 11) , array( 1 , 110, 88, 11) ) , "M" => array( array( 5 ,65, 41, 12) , array( 5 , 66, 42, 12) ) , "Q" => array( array( 5 ,54, 24, 15) , array( 7 , 55, 25, 15) ) , "H" => array( array( 11 ,36, 12, 12) , array( 7 , 37, 13, 12) ) );
			$stdTab9[ 16 ] = array( "L" => array( array( 5 ,122, 98, 12) , array( 1 , 123, 99, 12) ) , "M" => array( array( 7 ,73, 45, 14) , array( 3 , 74, 46, 14) ) , "Q" => array( array( 15 ,43, 19, 12) , array( 2 , 44, 20, 12) ) , "H" => array( array( 3 ,45, 15, 15) , array( 13 , 46, 16, 15) ) );
			$stdTab9[ 17 ] = array( "L" => array( array( 1 ,135, 107, 14) , array( 5 , 136, 108, 14) ) , "M" => array( array( 10 ,74, 46, 14) , array( 1 , 75, 47, 14) ) , "Q" => array( array( 1 ,50, 22, 14) , array( 15 , 51, 23, 14) ) , "H" => array( array( 2 ,42, 14, 14) , array( 17 , 43, 15, 14) ) );
			$stdTab9[ 18 ] = array( "L" => array( array( 5 ,150, 120, 15) , array( 1 , 151, 121, 15) ) , "M" => array( array( 9 ,69, 43, 13) , array( 4 , 70, 44, 13) ) , "Q" => array( array( 17 ,50, 22, 14) , array( 1 , 51, 23, 14) ) , "H" => array( array( 2 ,42, 14, 14) , array( 19 , 43, 15, 14) ) );
			$stdTab9[ 19 ] = array( "L" => array( array( 3 ,141, 113, 14) , array( 4 , 142, 114, 14) ) , "M" => array( array( 3 ,70, 44, 13) , array( 11 , 71, 45, 13) ) , "Q" => array( array( 17 ,47, 21, 13) , array( 4 , 48, 22, 13) ) , "H" => array( array( 9 ,39, 13, 13) , array( 16 , 40, 14, 13) ) );
			$stdTab9[ 20 ] = array( "L" => array( array( 3 ,135, 107, 14) , array( 5 , 136, 108, 14) ) , "M" => array( array( 3 ,67, 41, 13) , array( 13 , 68, 42, 13) ) , "Q" => array( array( 15 ,54, 24, 15) , array( 5 , 55, 25, 15) ) , "H" => array( array( 15 ,43, 15, 14) , array( 10 , 44, 16, 14) ) );
			$stdTab9[ 21 ] = array( "L" => array( array( 4 ,144, 116, 14) , array( 4 , 145, 117, 14) ) , "M" => array( array( 17 ,68, 42, 13) ) , "Q" => array( array( 17 ,50, 22, 14) , array( 6 , 51, 23, 14) ) , "H" => array( array( 19 ,46, 16, 15) , array( 6 , 47, 17, 15) ) );
			$stdTab9[ 22 ] = array( "L" => array( array( 2 ,139, 111, 14) , array( 7 , 140, 112, 14) ) , "M" => array( array( 17 ,74, 46, 14) ) , "Q" => array( array( 7 ,54, 24, 15) , array( 16 , 55, 25, 15) ) , "H" => array( array( 34 ,37, 13, 12) ) );
			$stdTab9[ 23 ] = array( "L" => array( array( 4 ,151, 121, 15) , array( 5 , 152, 122, 15) ) , "M" => array( array( 4 ,75, 47, 14) , array( 14 , 76, 48, 14) ) , "Q" => array( array( 11 ,54, 24, 15) , array( 14 , 55, 25, 15) ) , "H" => array( array( 16 ,45, 15, 15) , array( 14 , 46, 16, 15) ) );
			$stdTab9[ 24 ] = array( "L" => array( array( 6 ,147, 117, 15) , array( 4 , 148, 118, 15) ) , "M" => array( array( 6 ,73, 45, 14) , array( 14 , 74, 46, 14) ) , "Q" => array( array( 11 ,54, 24, 15) , array( 16 , 55, 25, 15) ) , "H" => array( array( 30 ,46, 16, 15) , array( 2 , 47, 17, 15) ) );
			$stdTab9[ 25 ] = array( "L" => array( array( 8 ,132, 106, 13) , array( 4 , 133, 107, 13) ) , "M" => array( array( 8 ,75, 47, 14) , array( 13 , 76, 48, 14) ) , "Q" => array( array( 7 ,54, 24, 15) , array( 22 , 55, 25, 15) ) , "H" => array( array( 22 ,45, 15, 15) , array( 13 , 46, 16, 15) ) );
			$stdTab9[ 26 ] = array( "L" => array( array( 10 ,142, 114, 14) , array( 2 , 143, 115, 14) ) , "M" => array( array( 19 ,74, 46, 14) , array( 4 , 75, 47, 14) ) , "Q" => array( array( 28 ,50, 22, 14) , array( 6 , 51, 23, 14) ) , "H" => array( array( 33 ,46, 16, 15) , array( 4 , 47, 17, 15) ) );
			$stdTab9[ 27 ] = array( "L" => array( array( 8 ,152, 122, 15) , array( 4 , 153, 123, 15) ) , "M" => array( array( 22 ,73, 45, 14) , array( 3 , 74, 46, 14) ) , "Q" => array( array( 8 ,53, 23, 15) , array( 26 , 54, 24, 15) ) , "H" => array( array( 12 ,45, 15, 15) , array( 28 , 46, 16, 15) ) );
			$stdTab9[ 28 ] = array( "L" => array( array( 3 ,147, 117, 15) , array( 10 , 148, 118, 15) ) , "M" => array( array( 3 ,73, 45, 14) , array( 23 , 74, 46, 14) ) , "Q" => array( array( 4 ,54, 24, 15) , array( 31 , 55, 25, 15) ) , "H" => array( array( 11 ,45, 15, 15) , array( 31 , 46, 16, 15) ) );
			$stdTab9[ 29 ] = array( "L" => array( array( 7 ,146, 116, 15) , array( 7 , 147, 117, 15) ) , "M" => array( array( 21 ,73, 45, 14) , array( 7 , 74, 46, 14) ) , "Q" => array( array( 1 ,53, 23, 15) , array( 37 , 54, 24, 15) ) , "H" => array( array( 19 ,45, 15, 15) , array( 26 , 46, 16, 15) ) );
			$stdTab9[ 30 ] = array( "L" => array( array( 5 ,145, 115, 15) , array( 10 , 146, 116, 15) ) , "M" => array( array( 19 ,75, 47, 14) , array( 10 , 76, 48, 14) ) , "Q" => array( array( 15 ,54, 24, 15) , array( 25 , 55, 25, 15) ) , "H" => array( array( 23 ,45, 15, 15) , array( 25 , 46, 16, 15) ) );
			$stdTab9[ 31 ] = array( "L" => array( array( 13 ,145, 115, 15) , array( 3 , 146, 116, 15) ) , "M" => array( array( 2 ,74, 46, 14) , array( 29 , 75, 47, 14) ) , "Q" => array( array( 42 ,54, 24, 15) , array( 1 , 55, 25, 15) ) , "H" => array( array( 23 ,45, 15, 15) , array( 28 , 46, 16, 15) ) );
			$stdTab9[ 32 ] = array( "L" => array( array( 17 ,145, 115, 15) ) , "M" => array( array( 10 ,74, 46, 14) , array( 23 , 75, 47, 14) ) , "Q" => array( array( 10 ,54, 24, 15) , array( 35 , 55, 25, 15) ) , "H" => array( array( 19 ,45, 15, 15) , array( 35 , 46, 16, 15) ) );
			$stdTab9[ 33 ] = array( "L" => array( array( 17 ,145, 115, 15) , array( 1 , 146, 116, 15) ) , "M" => array( array( 14 ,74, 46, 14) , array( 21 , 75, 47, 14) ) , "Q" => array( array( 29 ,54, 24, 15) , array( 19 , 55, 25, 15) ) , "H" => array( array( 11 ,45, 15, 15) , array( 46 , 46, 16, 15) ) );
			$stdTab9[ 34 ] = array( "L" => array( array( 13 ,145, 115, 15) , array( 6 , 146, 116, 15) ) , "M" => array( array( 14 ,74, 46, 14) , array( 23 , 75, 47, 14) ) , "Q" => array( array( 44 ,54, 24, 15) , array( 7 , 55, 25, 15) ) , "H" => array( array( 59 ,46, 16, 15) , array( 1 , 47, 17, 15) ) );
			$stdTab9[ 35 ] = array( "L" => array( array( 12 ,151, 121, 15) , array( 7 , 152, 122, 15) ) , "M" => array( array( 12 ,75, 47, 14) , array( 26 , 76, 48, 14) ) , "Q" => array( array( 39 ,54, 24, 15) , array( 14 , 55, 25, 15) ) , "H" => array( array( 22 ,45, 15, 15) , array( 41 , 46, 16, 15) ) );
			$stdTab9[ 36 ] = array( "L" => array( array( 6 ,151, 121, 15) , array( 14 , 152, 122, 15) ) , "M" => array( array( 6 ,75, 47, 14) , array( 34 , 76, 48, 14) ) , "Q" => array( array( 46 ,54, 24, 15) , array( 10 , 55, 25, 15) ) , "H" => array( array( 2 ,45, 15, 15) , array( 64 , 46, 16, 15) ) );
			$stdTab9[ 37 ] = array( "L" => array( array( 17 ,152, 122, 15) , array( 4 , 153, 123, 15) ) , "M" => array( array( 29 ,74, 46, 14) , array( 14 , 75, 47, 14) ) , "Q" => array( array( 49 ,54, 24, 15) , array( 10 , 55, 25, 15) ) , "H" => array( array( 24 ,45, 15, 15) , array( 46 , 46, 16, 15) ) );
			$stdTab9[ 38 ] = array( "L" => array( array( 4 ,152, 122, 15) , array( 18 , 153, 123, 15) ) , "M" => array( array( 13 ,74, 46, 14) , array( 32 , 75, 47, 14) ) , "Q" => array( array( 48 ,54, 24, 15) , array( 14 , 55, 25, 15) ) , "H" => array( array( 42 ,45, 15, 15) , array( 32 , 46, 16, 15) ) );
			$stdTab9[ 39 ] = array( "L" => array( array( 20 ,147, 117, 15) , array( 4 , 148, 118, 15) ) , "M" => array( array( 40 ,75, 47, 14) , array( 7 , 76, 48, 14) ) , "Q" => array( array( 43 ,54, 24, 15) , array( 22 , 55, 25, 15) ) , "H" => array( array( 10 ,45, 15, 15) , array( 67 , 46, 16, 15) ) );
			$stdTab9[ 40 ] = array( "L" => array( array( 19 ,148, 118, 15) , array( 6 , 149, 119, 15) ) , "M" => array( array( 18 ,75, 47, 14) , array( 31 , 76, 48, 14) ) , "Q" => array( array( 34 ,54, 24, 15) , array( 34 , 55, 25, 15) ) , "H" => array( array( 20 ,45, 15, 15) , array( 61 , 46, 16, 15) ) );
			foreach( $this->stdTab1 as $v => $i ) {
				$stdTab9[ $v ][ "maxDataL" ] = array();
				foreach( array( "L" , "M" , "Q" , "H" ) as $j ) {
					$sum = 0 ;
					foreach( $stdTab9[ $v ][ $j ] as $blockInfo ) {
						$sum+= $blockInfo[ 0 ] * $blockInfo[ 2 ];
					}
					$stdTab9[ $v ][ "maxDataL" ][ $j ] = $sum * 8 ;
				}
			}
			//print_r_html( $stdTab9 , true );
			$this->stdTab9 = $stdTab9 ;

			$tmp = implode( range( 0 , 9 ) ).implode( range( "A" , "Z" ) )." $%*+-./:" ;
			$stdTab5rev = array();
			for( $i = 0 ; $i < strlen( $tmp ) ; $i++ ) {
				$stdTab5rev[ $tmp[ $i ] ] = $i ;
			}
			$this->stdTab5rev = $stdTab5rev ;

			$this->maskFunc = array(
				0 => function( $i , $j ) {
					return ( ( $i + $j ) % 2 ) == 0 ;
				} ,
				1 => function( $i , $j ) {
					return ( $i % 2 ) == 0 ;
				} ,
				2 => function( $i , $j ) {
					return ( $j % 3 ) == 0 ;
				} ,
				3 => function( $i , $j ) {
					return ( ( $i + $j ) % 3 ) == 0 ;
				} ,
				4 => function( $i , $j ) {
					return  ( ( ( $i - $i % 2 ) / 2 + ( $j - $j % 3 ) / 3 ) % 2 ) == 0 ;
				} ,
				5 => function( $i , $j ) {
					return ( ( $i * $j ) % 2 + ( $i * $j ) % 3 ) == 0 ;
				} ,
				6 => function( $i , $j ) {
					return ( ( ( $i * $j ) % 2 + ( $i * $j ) % 3 ) % 2 ) == 0 ;
				} ,
				7 => function( $i , $j ) {
					return ( ( ( $i + $j ) % 2 + ( $i * $j ) % 3 ) % 2 ) == 0 ;
				}
			);
		}

		function __destruct() {
		}

		private function calcCC( $s , $op ) {
			$sl = strlen( $s );
			$opl = strlen( $op );
			$m = 1 << ( $sl + $opl - 2 );
			$v1 = bindec( $s ) << ( $opl - 1 );
			$c = bindec( $op );
			$v2 = $c << ( $sl - 1 );
			while ( $v2 >= $c ) {
				$r = $v1 ;
				while ( ( ( $r & $m ) == 0 ) && $m > 0 && $v2 >= $c ) {
					$m = $m >> 1 ;
					$v2 = $v2 >> 1 ;
				}
				$v1 = $r ^ $v2 ;
			}
			return bitString( $r , $opl - 1 );
		}

		private function getCodeLength( $d , $v ) {
			$cc = array( QRCODE_MODE_NUM => 0 , QRCODE_MODE_ALPHANUM => 0 , QRCODE_MODE_BYTE => 0 );
			$res = count( $d ) * 4 ;
			foreach( $d as $e ) {
				$res+= strlen( $e[ "data" ] );
				$cc[ $e[ "mode" ] ]++ ;
			}
			$t3r = $this->stdTab3[ $v ];
			foreach( $cc as $m => $c ) {
				$res+= $c * $t3r[ $m ];
			}
			return $res ;
		}

		private function getDataCode( $d , $v ) {
			$res = "" ;
			$t3r = $this->stdTab3[ $v ];
			$mcm = $this->modeCodeMap ;
			//print_r_html( $t3r , true );
			//print_r_html( $mcm , true );
			foreach( $d as $e ) {
				//print_r_html( $e , true );
				$m = $e[ "mode" ];
				$res.= $mcm[ $m ].bitString( strlen( $e[ "orig-data" ] ) , $t3r[ $m ] ).$e[ "data" ];
			}
			return $res ;

		}

		private function fillRect( &$qrCode , $x , $y , $w , $h , $c ) {
			for( $j = $x ; $j < $x + $w ; $j++ ) {
				for( $i = $y ; $i < $y + $h ; $i++ ) {
					$qrCode[ $i ][ $j ] = $c ;
				}
			}
		}

		private function QRDrawMark( &$qrCode , $i , $j , $mf ) {
			$saa = array( "st" => array( 3 , 7 , 5 , 3 ) , "dt" => array( 2 , 5 , 3 , 1 ) );
			$sa = $saa[ $mf ];
			$x = $j - $sa[ 0 ];
			$y = $i - $sa[ 0 ];
			$cc = 1 ;
			for( $k = 1 ; $k <= 3 ; $k++ ) {
				$this->fillRect( $qrCode , $x , $y , $sa[ $k ] , $sa[ $k ] , $this->c[ $cc ] );
				$x++ ;
				$y++ ;
				$cc = 1 - $cc ;
			}
		}

		private function outFormatData( &$qrCode , $fmtData ) {
			$fmtData = $fmtData.$this->calcCC( $fmtData , QRCODE_FCCP );
			$fmtData = strrev( bitStringXOR( $fmtData , "101010000010010" ) );

			$sS = count( $qrCode );
			$c = $this->c ;
			$fcbi = 0 ;
			$i1 = 0 ;
			$j1 = 8 ;
			$i1s = 1 ;
			$j1s = 0 ;
			$i2 = 8 ;
			$j2 = $sS - 1 ;
			$i2s = 0 ;
			$j2s = -1 ;
			while ( $fcbi < strlen( $fmtData ) ) {
				$cc = $c[ $fmtData[ $fcbi ] ];
				$qrCode[ $i1 ][ $j1 ] = $cc ;
				$qrCode[ $i2 ][ $j2 ] = $cc ;
				$i1+= $i1s ;
				$j1+= $j1s ;
				$i2+= $i2s ;
				$j2+= $j2s ;
				switch ( $fcbi ) {
					case 5 :
					case 8 :
						$i1+= $i1s ;
						$j1+= $j1s ;
						break ;
					case 6 :
						$i1s = 0 ;
						$j1s = -1 ;
						break ;
					case 7 :
						$i2 = $sS - 7 ;
						$i2s = 1 ;
						$j2 = 8 ;
						$j2s = 0 ;
						$qrCode[ $sS - 8 ][ $j2 ] = $c[ 1 ];
						break ;
				}

				$fcbi++ ;
			}
		}

		private function getExp( $n ) {
			while( $n < 0 ) {
				$n+= 255 ;
			}
			while( $n >= 256 ) {
				$n-= 255 ;
			}
			return $this->EXP_TAB[ $n ];
		}

		private function mkPoly( $p , $k = 0 ) {
			$r = array();
			$b = true ;
			foreach( $p as $v ) {
				if ( $v == 0 && $b ) {
				} else {
					$r[]= $v ;
					$b = false ;
				}
			}
			return array_pad( $r , count( $r ) + $k , 0 );
		}

		private function mulPoly( $a , $b ) {
			$al = count( $a );
			$bl = count( $b );
			$res = array_pad( array() , $al + $bl - 1 , 0 );
			for( $i = 0 ; $i < $al ; $i++ ) {
				for( $j = 0 ; $j < $bl ; $j++ ) {
					$res[ $i + $j ] = $res[ $i + $j ] ^ $this->getExp( $this->LOG_TAB[ $a[ $i ] ] + $this->LOG_TAB[ $b[ $j ] ] );
				}
			}
			return $this->mkPoly( $res );
		}

		private function modPoly( $a , $b ) {
			$al = count( $a );
			$bl = count( $b );
			if ( $al < $bl ) {
				return $a ;
			}

			$k = $this->LOG_TAB[ $a[ 0 ] ] - $this->LOG_TAB[ $b[ 0 ] ];
			for( $i = 0 ; $i < $bl ; $i++ ) {
				$a[ $i ] = $a[ $i ] ^ $this->getExp( $this->LOG_TAB[ $b[ $i ] ] + $k );
			}

			return $this->modPoly( $this->mkPoly( $a ) , $b );
		}

		private function getECP( $ccl ) {
			$a = array( 1 );
			for( $i = 0 ; $i < $ccl ; $i++ ) {
				$b = array( 1 , $this->getExp( $i ) );
				$a = $this->mulPoly( $a , $b );
			}
			return $a ;
		}

		private function calcCC2( $sa , $ccl ) {
			$ecp = $this->getECP( $ccl );
			return $this->modPoly( $this->mkPoly( $sa , count( $ecp ) - 1 ) , $ecp );
		}

		private function prepData( $data , $mode , $ver ) {
			$res = array();
				//QRCODE_MODE_ALPHANUM => array( 6 , 11 ) ,

			$t5r = $this->stdTab5rev ;

			$encode = array(
				QRCODE_MODE_NUM => function( $s ) {
					$d = array( 4 , 7 , 10 );
					$r = str_split( $s , 3 );
					foreach( $r as &$e ) {
						$e = bitString( intval( $e ) , $d[ strlen( $e ) - 1 ] );
					} unset( $e );
					return implode( $r );
				} ,

				QRCODE_MODE_ALPHANUM => function( $s ) use ( $t5r ) {
					$d = array( 6 , 11 );
					$r = str_split( $s , 2 );
					foreach( $r as &$e ) {
						$ev = 0 ;
						for( $i = 0 ; $i < strlen( $e ) ; $i++ ) {
							$ev = $ev * 45 + $t5r[ $e[ $i ] ];
						}
						$e = bitString( intval( $ev ) , $d[ strlen( $e ) - 1 ] );
					} unset( $e );
					return implode( $r );
				} ,

				QRCODE_MODE_BYTE => function( $s ) {
					$r = unpack( "C*" , $s );
					foreach( $r as &$e ) {
						$e = bitString( $e , 8 );
					} unset( $e );
					return implode( $r );
				}
			);

			if ( $mode != QRCODE_MODE_AUTO ) {
				$res[ 0 ] = array( "mode" => $mode , "data" => $encode[ $mode ]( $data ) , "orig-data" => $data );
			} else {
				//$res[ 0 ] = array( "mode" => QRCODE_MODE_BYTE , "fin" => false , "data" => false , "orig-data" => $data );
				$sp = 0 ;
				$cmpd = $this->compress( $data , $ver );
				foreach( $cmpd[ "c" ] as $r ) {
					$cData = substr( $data , $sp , $r[ "l" ] );
					$res[]= array( "mode" => $r[ "m" ] , "data" => $encode[ $r[ "m" ] ]( $cData ) , "orig-data" => $cData );
					$sp+= $r[ "l" ];
				}
			}

			//print_r_html( $res , 1 );

			return $res ;
		}

		function generate( $src , $errLvl , $opt ) {
			$el = $errLvl ;
			$mode = isset( $opt[ "qrcode_mode" ] ) ? $opt[ "qrcode_mode" ] : QRCODE_MODE_AUTO ;

			$cV = false ;
			foreach( $this->stdTab9 as $v => $i ) {
				$tgtData = $this->prepData( $src , $mode , $v );
				$srcMaxLength = $this->getCodeLength( $tgtData , $v );
				if ( $srcMaxLength <= $i[ "maxDataL" ][ $el ] ) {
					$cV = $v ;
					break ;
				}
			}

			$dataCode = $this->getDataCode( $tgtData , $cV );

			$c = $this->c ;

			$sS = $this->stdTab1[ $cV ][ "A" ];

			$fullSize = $this->stdTab9[ $cV ][ "maxDataL" ][ $el ];

			$dcl = strlen( $dataCode );
			if ( $fullSize - strlen( $dataCode ) >= 4 ) {
				$dataCode.= $this->stop ;
				$dcl = strlen( $dataCode );
			}

			if ( $dcl % 8 != 0 ) {
				$dataCode.= str_pad( "" , 8 - ( $dcl % 8 ) , 0 );
			}

			$pi = 0 ;
			while( strlen( $dataCode ) < $fullSize ) {
				$dataCode.= $this->fillers[ $pi ];
				$pi = 1 - $pi ;
			}

			$dataCode = str_split( $dataCode , 8 );


			$t9d = $this->stdTab9[ $cV ][ $el ];
			$blocks = array();
			$p = 0 ;
			foreach( $t9d as $bi ) {
				for( $i = 0 ; $i < $bi[ 0 ] ; $i++ ) {
					$a = array();
					$cb = array();
					for( $j = 0 ; $j < $bi[ 2 ] ; $j++ ) {
						$cbd = $dataCode[ $p++ ];
						$cb[]= $cbd ;
						$a[]= bindec( $cbd );
					}
					$blocks[] = array( "data" => $cb , "ecc" => $this->calcCC2( $a , $bi[ 1 ] - $bi[ 2 ] ) );
				}
			}

			$tgtData = array();
			$eccData = array();
			$bi = 0 ;
			do {
				$all = true ;
				foreach ( $blocks as &$cb ) {
					if ( $bi < count( $cb[ "data" ] ) ) {
						$tgtData[]= $cb[ "data" ][ $bi ];
						$all = false ;
					}
					if ( $bi < count( $cb[ "ecc" ] ) ) {
						$eccData[]= bitString( $cb[ "ecc" ][ $bi ] , 8 );
						$all = false ;
					}
				} unset( $cb );
				$bi++ ;
			} while ( !$all );

			$resData = array_merge( $tgtData , $eccData );

			$qrCode = array_fill_keys( range( 0 , $sS - 1 ) , " " );
			$qrCode = array_fill_keys( range( 0 , $sS - 1 ) , $qrCode );

			$this->fillRect( $qrCode , 0 , 0 , 8 , 8 , $c[ 0 ] );
			$this->QRDrawMark( $qrCode , 3 , 3 , "st" );
			$this->fillRect( $qrCode , 0 , $sS - 8 , 8 , 8 , $c[ 0 ] );
			$this->QRDrawMark( $qrCode , $sS - 4 , 3 , "st" );
			$this->fillRect( $qrCode , $sS - 8 , 0 , 8 , 8 , $c[ 0 ] );
			$this->QRDrawMark( $qrCode , 3 , $sS - 4 , "st" );
			$dtInfo = $this->stdTabE1[ $cV ];
			$dtLN = count( $dtInfo );
			for( $in = 0 ; $in < $dtLN ; $in++ ) {
				for( $jn = 0 ; $jn < $dtLN ; $jn++ ) {
					 if ( ( $in == 0 && $jn == 0 ) || ( $in == 0 && $jn + 1 == $dtLN ) || ( $in + 1 == $dtLN && $jn == 0 ) ) {

					 } else {
					 	$this->QRDrawMark( $qrCode , $dtInfo[ $in ] , $dtInfo[ $jn ] , "dt" );
					 }
				}
			}

			$cc = 1 ;
			for( $sli = 8 ; $sli < $sS - 8 ; $sli++ ) {
				$qrCode[ $sli ][ 6 ] = $c[ $cc ];
				$qrCode[ 6 ][ $sli ] = $c[ $cc ];
				$cc = 1 - $cc ;
			}

			if ( $cV >= 7 ) {
				$cVCode = bitString( $cV , 6 );
				//echo $cVCode."<br>" ;
				$cVCode = strrev( $cVCode.$this->calcCC( $cVCode , QRCODE_VCCP ) );
				for( $vci = 0 ; $vci < strlen( $cVCode ) ; $vci++ ) {
					$vcir = $vci % 3 ;
					$qrCode[ $sS - 11 + $vcir ][ ( $vci - $vcir ) / 3 ] = $c[ $cVCode[ $vci ] ];
					$qrCode[ ( $vci - $vcir ) / 3 ][ $sS - 11 + $vcir ] = $c[ $cVCode[ $vci ] ];
				}
			}

			$elCode = bitString( $this->errLvlCodeMap[ $el ] , 2 );
			$cMask = 3 ;
			$cMaskCode = bitString( $cMask , 3 );
			$fmtCode = $elCode.$cMaskCode ;
			$this->outFormatData( $qrCode , $fmtCode );


			$i1 = $sS - 1 ;
			$i1s = -1 ;
			$j1 = $sS - 1 ;
			$col = 0 ;
			$c2 = array( "X" , "Y" );
			$cc = 0 ;

			foreach( $resData as $cb ) {
				$bi = 0 ;
				while( $bi < 8 ) {
					if ( $qrCode[ $i1 ][ $j1 ] == " " ) {
						//$qrCode[ $i1 ][ $j1 ] = $c2[ $cc ];
						$qrCode[ $i1 ][ $j1 ] = $cb[ $bi ];
						$bi++ ;
					}
					if ( $col == 0 ) {
						$col++ ;
						$j1-- ;
					} else {
						$col-- ;
						$j1++ ;
						$i1+= $i1s ;
						if ( $i1 < 0 ) {
							$i1 = 0 ;
							$i1s = -$i1s ;
							if ( $j1 == 8 ) {
								$j1-= 3 ;
							} else {
								$j1-= 2 ;
							}
						} else
						if ( $i1 >= $sS ) {
							$i1 = $sS - 1 ;
							$i1s = -$i1s ;
							if ( $j1 == 8 ) {
								$j1-= 3 ;
							} else {
								$j1-= 2 ;
							}
						}
					}
				}
				$cc = 1 - $cc ;
			}

			$minPoints = false ;
			$resQR = false ;
			$maskPoints = array();
			for ( $mi = 0 ; $mi < count( $this->maskFunc ) ; $mi++ ) {
				//echo "mi: ".$mi."<br>" ;
				$tmpQR = $qrCode ;
				$cmf = $this->maskFunc[ $mi ];
				$cMaskCode = bitString( $mi , 3 );
				$fmtCode = $elCode.$cMaskCode ;
				$this->outFormatData( $tmpQR , $fmtCode );

				$kkk = 1 ;

				for( $i = 0 ; $i < $sS ; $i++ ) {
					for( $j = 0 ; $j < $sS ; $j++ ) {
						if ( $cmf( $i , $j ) ) {
							switch( $tmpQR[ $i ][ $j ] ) {
								case "0" :
									$tmpQR[ $i ][ $j ] = "1" ;
									break ;
								case "1" :
									$tmpQR[ $i ][ $j ] = "0" ;
									break ;
							}
						}

						if ( $tmpQR[ $i ][ $j ] == $c[ 1 ] ) {
							$tmpQR[ $i ][ $j ] = "1" ;
						}
						if ( $tmpQR[ $i ][ $j ] == $c[ 0 ] ) {
							$tmpQR[ $i ][ $j ] = "0" ;
						}
					}
				}

				$points = 0 ;
				$kSum = 0 ;
				$testTmplD = array( "00001011101_" => array() , "10111010000_" => array() );
				foreach( $testTmplD as $ctt => &$cttd ) {
					$cttd[ "l" ] = strlen( $ctt ) - 1 ;
				} unset( $cttd );
				$indArr = array();
				foreach( array( 0 , 1 ) as $dir ) {
					for( $i_ = 0 ; $i_ < $sS ; $i_++ ) {
						$indArr[ 0 ] = $i_ ;
						$lastModule = false ;
						$lastModulePos = false ;
						foreach( $testTmplD as &$cttd ) {
							$cttd[ "p" ] = 0 ;
						} unset( $cttd );
						for( $j_ = 0 ; $j_ < $sS ; $j_++ ) {
							$indArr[ 1 ] = $j_ ;
							$i = $indArr[ $dir ];
							$j = $indArr[ 1 - $dir ];
							$cModule = $tmpQR[ $i ][ $j ];
							$isBorder = ( $j == $sS - 1 );
							if ( ( $cModule != $lastModule ) || $isBorder ) {
								if ( $lastModulePos !== false && ( $j - $lastModulePos ) >= 5  ) {
									$points+= ( $j - $lastModulePos ) - 2 ;
								}
								$lastModulePos = $j ;
								$lastModule = $cModule ;
							}

							foreach( $testTmplD as $ctt => $cttd ) {
								if ( ( $cModule != $ctt[ $cttd[ "p" ] ] ) || $isBorder ) {
									if ( $cttd[ "p" ] == $cttd[ "l" ] ) {
										$points+= 40 * $kkk ;
										$kkk = 0 ;
										$cttd[ "p" ] = 0 ;
									}
								} else {
									$cttd[ "p" ]++ ;
								}
							}

							if ( $dir == 0 ) {
								if ( $i < $sS -1 && $j < $sS - 1 ) {
									if ( $tmpQR[ $i + 1 ][ $j ] == $cModule && $tmpQR[ $i ][ $j + 1 ] == $cModule && $tmpQR[ $i + 1 ][ $j + 1 ] == $cModule ) {
										$points+= 3 ;
									}
								}

								if ( $cModule == "1" ) {
									$kSum++ ;
								}
							}
						}
					}
				}

				$kVal = abs( 50 - ( 100 * $kSum / ( $sS * $sS ) ) );
				for ( $i = 1 ; $i < 10 ; $i++ ) {
					if ( ( $kVal >= $i ) * 5 && ( $kVal <= ( $i + 1 ) * 5 ) ) {
						$points+= 10 * $i ;
						break ;
					}
				}

				if ( $minPoints === false || ( $minPoints !== false && $points < $minPoints  ) ) {
					$minPoints = $points ;
					$resQR = $tmpQR ;
				}

				$maskPoints[ $mi ] = $points ;

			}

			return array( "image" => $resQR , "size" => $sS , "version" => $cV , "mp" => $maskPoints );
		}

		function compress( $t , $v ) {
			$map = array();
			foreach( range( 0 , 255 ) as $i ) {
				$map[ $i ] = 1 ;
			}
			foreach( range( "A" , "Z" ) as $i ) {
				$map[ ord( $i ) ] = 3 ;
			}
			foreach( str_split( " $%*+-./:" ) as $i ) {
				$map[ ord( $i ) ] = 3 ;
			}
			foreach( range( 0 , 9 ) as $i ) {
				$map[ ord( "".$i ) ] = 7 ;
			}

			//var_dump_html( $T , 1 );
			$M = array();
			$src = unpack( "C*" , $t );
			foreach ( $src as $c ) {
				$M[]= $map[ $c ];
			}

			$mmap = array( "A" => QRCODE_MODE_ALPHANUM , "B" => QRCODE_MODE_BYTE , "N" => QRCODE_MODE_NUM );
			$vmap = array( 1 => array( "B" ) , 3 => array( "B" , "A" ) , 7 => array( "B" , "A" , "N" ) );
			$MP = array( QRCODE_MODE_NUM => array( 3 , 4 , 3 , 3 ) , QRCODE_MODE_ALPHANUM => array( 2 , 6 , 5 ) , QRCODE_MODE_BYTE => array( 1 , 8 ) );



			//var_dump_html( implode( $M ) , 1 );
			//print_r_html( $map , 1 );

			//$cV = 26 ;
			$t3r = $this->stdTab3[ $v ];

			$LP = "XS" ;
			$tp = array( $LP => array( "state" => 1 , "tgt" => array() , "points" => 0 ) );
			$lpva = array( $LP );
			foreach ( $M as $i => $m ) {
				$v = $vmap[ $m ];
				$cpva = array();
				foreach( $v as $e ) {
					$pID = $e.$i ;
					$tp[ $pID ]= array( "state" => null , "tgt" => array() , "points" => PHP_INT_MAX , "pp" => "" , "cc" => 0 );
					$cpva[]= $pID ;
				}

				foreach( $lpva as $lpv ) {
					foreach( $cpva as $cpv ) {
						$tp[ $lpv ][ "tgt" ][]= $cpv ;
					}
				}

				$lpva = $cpva ;
			}

			//print_r_html( $lpva , 1 );

			$iii = 0 ;
			$rrr = 0 ;
			$pn = array_keys( $tp );
			do {
				$rrr++ ;
				$all = true ;
				foreach( $pn as $kCP ) {
					$CP = &$tp[ $kCP ];
					if ( $CP[ "state" ] == 1 ) {
						//echo $rrr." - ".$kCP.":<br>" ;
						//$iii++ ;
						/*if ( $iii > 1000 ) {
							return ;
						}*/
						$CP[ "state" ] = 0 ;
						$all = false ;
						foreach ( $CP[ "tgt" ] as $ctgt ) {
							//echo $rrr." - ".$kCP." => ".$ctgt ;
							$ctgtp = &$tp[ $ctgt ];
							$CPt = $kCP[ 0 ];
							$tgtPt = $ctgt[ 0 ];
							$sp = 0 ;
							//echo "[".$CPt.",".$tgtPt."]" ;
							if ( $CPt != $tgtPt ) {
								$sp = 4 + $t3r[ $mmap[ $tgtPt ] ];
								$ncc = 0 ;
								//echo " 4 + ".$t3r[ $mmap[ $tgtPt ] ];
							} else {
								//echo 0 ;
								$ncc = $CP[ "cc" ] + 1 ;
							}

							$MPi = $ncc % $MP[ $mmap[ $tgtPt ] ][ 0 ];
							$sp+= $MP[ $mmap[ $tgtPt ] ][ $MPi + 1 ];
							//echo " -- ".$MPi.",".$MP[ $mmap[ $tgtPt ] ][ $MPi + 1 ]." = ".$sp."<br>" ;

							if ( $CP[ "points" ] + $sp < $ctgtp[ "points" ] ) {
								$ctgtp[ "state" ] = 1 ;
								$ctgtp[ "points" ] = $CP[ "points" ] + $sp ;
								$ctgtp[ "pp" ] = $kCP ;
								$ctgtp[ "cc" ] = $ncc ;
							}
							unset( $ctgtp );
						}
					}
					unset( $CP );
				}
			//} while ( false );
			} while ( !$all && $iii++ < 10 );

			//echo $rrr ;

			//print_r_html( $tp , 1 );

			$minP = $lpva[ 0 ];
			$min =  $tp[ $minP ][ "points" ];
			for ( $i = 1 ; $i < count( $lpva ) ; $i++ ) {
				$iP = $lpva[ $i ];
				if ( $min > $tp[ $iP ][ "points" ] ) {
					$minP = $iP ;
					$min =  $tp[ $minP ][ "points" ];
				}
			}

			//echo "point: ".$minP."<br>" ;
			$np = $minP ;
			//echo "path: ".$minP." " ;
			$res = array();
			while ( $np != "XS" ) {
				//echo $np." " ;
				$t = $np[ 0 ];
				$i = substr( $np , 1 );
				$res[ $i ]= $t ;
				$np = $tp[ $np ][ "pp" ];
			}

			ksort( $res );
			$lv = "" ;
			$lvc = 0 ;
			$tp = array();
			foreach( $res as $v ) {
				if ( $v != $lv ) {
					if ( $lv != "" ) {
						$tp[]= array( "m" => $mmap[ $lv ] , "l" => $lvc );
					}
					$lv = $v ;
					$lvc = 1 ;
				} else {
					$lvc++ ;
				}
			}
			$tp[]= array( "m" => $mmap[ $lv ] , "l" => $lvc );

			return array( "l" => $min , "c" => $tp );
		}
	}
?>