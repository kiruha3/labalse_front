<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "core.php" );

	define( 'BARCODE_TYPE_1D' , '1D' );
	define( 'BARCODE_TYPE_QRCODE' , 'QR' );
	define( 'BARCODE_TYPE_DATAMATRIX' , 'DMTX' );

	function generateBarcode( $src , $direct = true , $type = BARCODE_TYPE_1D , $opt = array() ) {
		switch( $type ) {
			case BARCODE_TYPE_1D :
				return generateBarcode1D( $src , $direct , $opt );
				break ;

			case BARCODE_TYPE_QRCODE :
				return generateBarcodeQR( $src , $direct , $opt );
				break ;

			case BARCODE_TYPE_DATAMATRIX :
				return generateBarcodeDmtx( $src , $direct , $opt );
				break ;

		}
	}

	function generateBarcode1D( $src , $direct = true , $opt = array() ) {
		$tab = array (
			"stop" => "1100011101011" ,
			  0 => "11011001100" ,
			  1 => "11001101100" ,
			  2 => "11001100110" ,
			  3 => "10010011000" ,
			  4 => "10010001100" ,
			  5 => "10001001100" ,
			  6 => "10011001000" ,
			  7 => "10011000100" ,
			  8 => "10001100100" ,
			  9 => "11001001000" ,
			 10 => "11001000100" ,
			 11 => "11000100100" ,
			 12 => "10110011100" ,
			 13 => "10011011100" ,
			 14 => "10011001110" ,
			 15 => "10111001100" ,
			 16 => "10011101100" ,
			 17 => "10011100110" ,
			 18 => "11001110010" ,
			 19 => "11001011100" ,
			 20 => "11001001110" ,
			 21 => "11011100100" ,
			 22 => "11001110100" ,
			 23 => "11101101110" ,
			 24 => "11101001100" ,
			 25 => "11100101100" ,
			 26 => "11100100110" ,
			 27 => "11101100100" ,
			 28 => "11100110100" ,
			 29 => "11100110010" ,
			 30 => "11011011000" ,
			 31 => "11011000110" ,
			 32 => "11000110110" ,
			 33 => "10100011000" ,
			 34 => "10001011000" ,
			 35 => "10001000110" ,
			 36 => "10110001000" ,
			 37 => "10001101000" ,
			 38 => "10001100010" ,
			 39 => "11010001000" ,
			 40 => "11000101000" ,
			 41 => "11000100010" ,
			 42 => "10110111000" ,
			 43 => "10110001110" ,
			 44 => "10001101110" ,
			 45 => "10111011000" ,
			 46 => "10111000110" ,
			 47 => "10001110110" ,
			 48 => "11101110110" ,
			 49 => "11010001110" ,
			 50 => "11000101110" ,
			 51 => "11011101000" ,
			 52 => "11011100010" ,
			 53 => "11011101110" ,
			 54 => "11101011000" ,
			 55 => "11101000110" ,
			 56 => "11100010110" ,
			 57 => "11101101000" ,
			 58 => "11101100010" ,
			 59 => "11100011010" ,
			 60 => "11101111010" ,
			 61 => "11001000010" ,
			 62 => "11110001010" ,
			 63 => "10100110000" ,
			 64 => "10100001100" ,
			 65 => "10010110000" ,
			 66 => "10010000110" ,
			 67 => "10000101100" ,
			 68 => "10000100110" ,
			 69 => "10110010000" ,
			 70 => "10110000100" ,
			 71 => "10011010000" ,
			 72 => "10011000010" ,
			 73 => "10000110100" ,
			 74 => "10000110010" ,
			 75 => "11000010010" ,
			 76 => "11001010000" ,
			 77 => "11110111010" ,
			 78 => "11000010100" ,
			 79 => "10001111010" ,
			 80 => "10100111100" ,
			 81 => "10010111100" ,
			 82 => "10010011110" ,
			 83 => "10111100100" ,
			 84 => "10011110100" ,
			 85 => "10011110010" ,
			 86 => "11110100100" ,
			 87 => "11110010100" ,
			 88 => "11110010010" ,
			 89 => "11011011110" ,
			 90 => "11011110110" ,
			 91 => "11110110110" ,
			 92 => "10101111000" ,
			 93 => "10100011110" ,
			 94 => "10001011110" ,
			 95 => "10111101000" ,
			 96 => "10111100010" ,
			 97 => "11110101000" ,
			 98 => "11110100010" ,
			 99 => "10111011110" ,
			100 => "10111101110" ,
			101 => "11101011110" ,
			102 => "11110101110" ,
			103 => "11010000100" ,
			104 => "11010010000" ,
			105 => "11010011100"
		);

		$revTab = array_flip( $tab );

		$start = array(
			"A" => &$tab[ 103 ] ,
			"B" => &$tab[ 104 ] ,
			"C" => &$tab[ 105 ]
		);

		$tabR = array(
			"A" => array (
				array( 32 , 95 ) ,
				array( 00 , 31 ) ,
			) ,
			"B" => array (
				array( 32 , 127 )
			) ,
			"C" => array (
				array( 00 , 99 )
			)
		);

		function mkTab( $r ) {
			$v = array();
			$ind = 0 ;
			foreach( $r as $sr ) {
				for( $j = $sr[ 0 ] ; $j <= $sr[ 1 ] ; $j++ ) {
					$v[ $j ] = $ind++ ;
				}
			}
			return $v ;
		}

		$CCM = "B" ;
		$tabCC = mkTab( $tabR[ $CCM ] );

		$code = $start[ $CCM ];
		$codeValue = $revTab[ $code ];
		$factor = 1 ;
		if ( $CCM == "C" ) {
			for( $i = 0 ; $i < strlen( $src ) ; $i+= 2 ) {
				$cc = intval( substr( $src , $i , 2 ) );
				$cc = $tabCC[ $cc ];
				$codeValue+= $cc * $factor ;
				$factor++ ;
				$code.= $tab[ $cc ];
			}
		} else {
			for( $i = 0 ; $i < strlen( $src ) ; $i++ ) {
				$cc = ord( $src[ $i ] );
				$cc = $tabCC[ $cc ];
				$codeValue+= $cc * $factor ;
				$factor++ ;
				$code.= $tab[ $cc ];
			}
		}

		$cc = $codeValue % 103 ;

		$code.= $tab[ $cc ].$tab[ "stop" ];
		//echo $code ;

		$unit = 1 * 4 ;
		//$unit = 1 ;

		$mw = strlen( $code ) * $unit ;
		$mh = intval( 28 * 4 );
		//$mh = 28 ;

		$im = imagecreatetruecolor( $mw , $mh );

		$black = imagecolorallocate( $im , 0 , 0 , 0 );
		$white = imagecolorallocate( $im , 255 , 255 , 255 );

		imagefilledrectangle( $im , 0 , 0 , $mw , $mh , $white );
		for( $i = 0 ; $i < strlen( $code ) ; $i++ ) {
			if ( $code[ $i ] == "1" ) {
				imagefilledrectangle( $im , $i * $unit , 0 , ( $i + 1 ) * $unit - 1 , $mh , $black );
			}
		}

		if ( $direct ) {
			imagepng( $im );
			imagedestroy( $im );
			return array( "w" => $mw , "h" => $mh );
		} else {
			ob_start();
			imagepng( $im );
			imagedestroy( $im );
			$imgData = ob_get_contents();
			ob_end_clean();
			return array( "w" => $mw , "h" => $mh , "raw" => $imgData );
		}
	}

	function array_insert_col_keys( $a , $col ) {
		$res = $a ;
		foreach( $col as $k => $v ) {
			$res[ $k ][] = $v ;
		}
		return $res ;
	}

	function bitString( $v , $size ) {
		//echo "v: ".$v." , s: ".$size."<br>" ;
		return str_pad( decbin( $v ) , $size , 0 , STR_PAD_LEFT );
	}

	function bitStringXOR ( $a , $b , $c = array( "0" , "1" ) ) {
		$ml = min( strlen( $a ) , strlen( $b ) );
		//echo "a: ".$a."<br>" ;
		//echo "b: ".$b."<br>" ;
		for( $i = 0 ; $i < $ml ; $i++ ) {
			$a[ $i ] = ( $a[ $i ] == $b[ $i ] ? $c[ 0 ] : $c[ 1 ] );
		}
		//echo "r: ".$a."<br>" ;
		return $a ;
	}

	function bitStringXOREx( $a , $b , $offset = 0 , $c = array( "0" , "1" ) ) {
		if ( $offset < 0 ) {
			return bitStringXOR( $b , $a , -$offset , $c );
		}

		$al = strlen( $a );
		$bl = strlen( $b );
		$ml = min( $al - $offset , $bl );
		$res = substr( $a , 0 , $offset );
		for( $i = 0 ; $i < $ml ; $i++ ) {
			$res.= ( $a[ $i + $offset ] == $b[ $i ] ? $c[ 0 ] : $c[ 1 ] );
		}
		if ( $bl + $offset > $al ) {
			$res.= substr( $b , $ml );
		} else
		if ( $bl + $offset < $al ) {
			$res.= substr( $a , $bl + $offset );
		}
		return $res ;
	}

	function generateBarcodeQR( $src , $direct = true , $opt = array() ) {

		//echo $getCodeLength( "01234567" , QRCODE_MODE_NUM );
		//return ;

		// error level
		$el = "L" ;
		if ( isset( $opt[ "EL" ] ) ) {
			$el = $opt[ "EL" ];
		}

		$bc = new TQRCode();
		$qrCodeData = $bc->generate( $src , $el , $opt );
		$qrCode = $qrCodeData[ "image" ];
		$sS = $qrCodeData[ "size" ];

	////////////////////////////////////////////////////////////

		if ( isset( $opt[ "pix_size" ] ) ) {
			$unit = $opt[ "pix_size" ];
		} else {
			$unit = 1 ;
		}

		$aS = $sS + 8 ;
		$aW = $aS * $unit ;

		$im = imagecreatetruecolor( $aW , $aW );

		$black = imagecolorallocate( $im , 0 , 0 , 0 );
		$white = imagecolorallocate( $im , 255 , 255 , 255 );
		$red = imagecolorallocate( $im , 255 , 0 , 0 );

		$colX = imagecolorallocate( $im , 0 , 255 , 0 );
		$colY = imagecolorallocate( $im , 0 , 128 , 255 );

		imagefilledrectangle( $im , 0 , 0 , $aW , $aW , $white );
		//$ctt = array( " " => $red , "A" => $white , "B" => $black , "0" => $white , "1" => $black , "X" => $colX , "Y" => $colY );
		$ctt = array( " " => $white , "A" => $white , "B" => $black , "0" => $white , "1" => $black , "X" => $colX , "Y" => $colY );
		for( $i = 0 ; $i < $sS ; $i++ ) {
			for( $j = 0 ; $j < $sS ; $j++ ) {
				$cc = $qrCode[ $i ][ $j ];
				$cc = $ctt[ $cc ];
				imagefilledrectangle( $im , ( $j + 4 ) * $unit , ( $i + 4 ) * $unit , ( $j + 5 ) * $unit - 1 , ( $i + 5 ) * $unit - 1 , $cc );
				//imagefilledrectangle( $im , ( $i + 4 ) * $unit , ( $j + 4 ) * $unit , ( $i + 5 ) * $unit - 1 , ( $j + 5 ) * $unit - 1 , $cc );
			}
		}

		if ( $direct ) {
			imagepng( $im );
			imagedestroy( $im );
			return array( "w" => $aW , "h" => $aW );
		} else {
			ob_start();
			imagepng( $im );
			imagedestroy( $im );
			$imgData = ob_get_contents();
			ob_end_clean();
			return array( "w" => $aW , "h" => $aW , "raw" => $imgData , "generated-data" => $qrCodeData );
		}
	}

	function generateBarcodeDmtx( $src , $direct = true , $opt = array() ) {

		$bc = new dmtxWrite( $src );
		$dtf = '/tmp/php-dmtx-'.date( 'YmdHis' , time() ).'-'.microtime( true ).'.png' ;
		$bc->save( $dtf , dmtx::SYMBOL_SQUARE_AUTO );
		$dmtxCodeData = imagecreatefrompng( $dtf );
		unlink( $dtf );

		$aW = imagesx( $dmtxCodeData );

		if ( $direct ) {
			imagepng( $dmtxCodeData );
			imagedestroy( $dmtxCodeData );
			return array( "w" => $aW , "h" => $aW );
		} else {
			ob_start();
			imagepng( $dmtxCodeData );
			imagedestroy( $dmtxCodeData );
			$imgData = ob_get_contents();
			ob_end_clean();
			return array( "w" => $aW , "h" => $aW , "raw" => $imgData );
		}
	}

	function AdjustBarCode( $bc , $bca ) {
		$m = array();
		preg_match_all( '/([a-z0-9]+)(?:\.|$)/i' , strtolower( $bca ) , $m );
		$bca = $m[ 1 ];

		$bcs = getCharIDStructure( $bc );

		if ( $bcs === false ) {
			foreach ( $bca as $cbca ) {
				switch( $cbca[ 0 ] ) {
					case "r" :
						$rs = intval( $cbca[ 1 ] );
						$rd = substr( $cbca , 2 );
						if ( strlen( $bc ) <= $rs ) {
							$bc = $rd ;
						} else {
							$bc = $rd.substr( $bc , $rs );
						}
						break ;
					case "m" :
						$m = array();
						preg_match( '/^m(?<p>\d+)t(?<r>\d+)e(?<e>[0-1])$/' , $cbca , $m );
						if ( substr( $bc , 0 , strlen( $m[ "p" ] ) ) == $m[ "p" ] ) {
							$bc = $m[ "r" ].substr( $bc , strlen( $m[ "p" ] ) );
						} else {
							if ( $m[ "e" ] == "1" ) {
								return false ;
							}
						}
						break ;
				}
			}
		} else {
			foreach ( $bca as $cbca ) {
				switch( $cbca[ 0 ] ) {
					case "t" :
						$n = preg_match( '/^t(?<t>\w{4})$/' , $cbca , $m );
						if ( $n != 1 ) {
							return false ;
						}
						$bcs[ "t" ] = $m[ "t" ];
						$bc = mkCharID( $bcs );
						break ;
				}
			}
		}

		return $bc ;
	}

	function renewID( $oid , $dt = "0110" ) {
		global $UserOrgIndex ;
		$oid = preg_replace( '/^(\d{6})\d(\d)$/' , '$1$2' , $oid );
		$s = array(
			"v" => VERSION_CHAR_ID ,
			"o" => $UserOrgIndex ,
			"t" => $dt ,
			"y" => 2008 + intval( $oid / 1000000 ) ,
			"n" => str_pad( ( $oid % 1000000 ) , 6 , 0 , STR_PAD_LEFT )
		);
		return mkCharID( $s );
	}

	function renewID2900( $oid ) {
		return renewID( $oid , "0600" );
	}
	function renewID2101( $oid ) {
		return renewID( $oid , "0610" );
	}

	$getBarCodeExtIDMap =
		array_fill_keys( strexp( "{0{{0,1,2}1,4{0,1,2,3},5{0,2},6{0,1,2},89,9{1,9}}0,1{0{1,2},{3,4}1,{5{1},60}}0,3190}" ) , array( "tgtType" => "docs" , "id-doctype" => "0110" ) ) +
		array_fill_keys( strexp( "1110" ) , array( "tgtType" => "subpoena" , "id-doctype" => "1110" ) ) +
		array_fill_keys( strexp( "1210" ) , array( "tgtType" => "correspondence" , "id-doctype" => "1210" ) ) +
		array_fill_keys( strexp( "21{0,1}0" ) , array( "tgtType" => "correspondence" , "id-doctype" => "2100" ) ) +
		array_fill_keys( strexp( "2200" ) , array( "tgtType" => "correspondence" , "id-doctype" => "2200" ) )+
		array_fill_keys( strexp( "2300" ) , array( "tgtType" => "correspondence" , "id-doctype" => "2300" ) );

	$getBarCodeExtIDMap[ "0010" ][ "name" ] = "Обложка наблюдательного производства" ;
	$getBarCodeExtIDMap[ "0010" ][ "sname" ] = "Обложка НП" ;
	$getBarCodeExtIDMap[ "0110" ][ "name" ] = "Заключение / Акт" ;
	$getBarCodeExtIDMap[ "0110" ][ "sname" ] = "Заключение" ;
	$getBarCodeExtIDMap[ "0210" ][ "name" ] = "Сопроводительное письмо" ;
	$getBarCodeExtIDMap[ "0410" ][ "name" ] = "Карточка поручения" ;
	$getBarCodeExtIDMap[ "0420" ][ "name" ] = "Карточка движения материалов" ;
	$getBarCodeExtIDMap[ "0420" ][ "sname" ] = "К мат" ;
	$getBarCodeExtIDMap[ "0430" ][ "name" ] = "Карточка вещ.док" ;
	$getBarCodeExtIDMap[ "0430" ][ "sname" ] = "К ВД" ;
	$getBarCodeExtIDMap[ "0600" ][ "name" ] = "Ходатайство" ;
	$getBarCodeExtIDMap[ "0610" ][ "name" ] = "Ходатайство о предоставлении объекта" ;
	$getBarCodeExtIDMap[ "0610" ][ "sname" ] = "ход.объекта" ;
	$getBarCodeExtIDMap[ "0620" ][ "name" ] = "Ходатайство о продлении сроков" ;
	$getBarCodeExtIDMap[ "0620" ][ "sname" ] = "ход.сроков" ;
	$getBarCodeExtIDMap[ "0890" ][ "name" ] = "Прочие рапорты" ;
	$getBarCodeExtIDMap[ "0910" ][ "name" ] = "Отчет факса" ;
	$getBarCodeExtIDMap[ "0990" ][ "name" ] = "Прочие отчеты" ;
	$getBarCodeExtIDMap[ "1010" ][ "name" ] = "Постановление/определение" ;
	$getBarCodeExtIDMap[ "1010" ][ "sname" ] = "Постановление" ;
	$getBarCodeExtIDMap[ "1110" ][ "name" ] = "Повестки" ;
	$getBarCodeExtIDMap[ "1210" ][ "name" ] = "Запросы о возможности, стоимости и сроках" ;
	$getBarCodeExtIDMap[ "1310" ][ "name" ] = "Жалобы" ;
	$getBarCodeExtIDMap[ "1410" ][ "name" ] = "Дополнительные материалы" ;
	$getBarCodeExtIDMap[ "1410" ][ "sname" ] = "Доп мат" ;
	$getBarCodeExtIDMap[ "1510" ][ "name" ] = "Уведомление (эксперт)" ;
	$getBarCodeExtIDMap[ "2110" ][ "name" ] = "Исходящая корреспонденция" ;
	$getBarCodeExtIDMap[ "3110" ][ "name" ] = "Счет" ;
	$getBarCodeExtIDMap[ "3114" ][ "name" ] = "Квитанция" ;
	$getBarCodeExtIDMap[ "3190" ][ "name" ] = "По оплате" ;

	$docStyles =
		array_fill_keys( strexp( "0110" ) , array( "lnk-style" => "-green" ) ) +
		array_fill_keys( strexp( "{04{0,1,2}0,0890,09{1,9}0}" ) , array( "lnk-style" => "-gray" ) ) +
		array_fill_keys( strexp( "{0430,1410}" ) , array( "lnk-style" => "-blue" ) ) +
		array_fill_keys( strexp( "1510" ) , array( "lnk-style" => "-purple" ) ) +
		array_fill_keys( strexp( "06{0,1,2}0" ) , array( "lnk-style" => "-lt-blue" ) ) +
		array_fill_keys( strexp( "31{10,14,90}" ) , array( "lnk-style" => "-white" ) );


	function getBarCodeExtID( $bc ) {
		global $getBarCodeExtIDMap ;
		$id = getCharID( $bc , false , ORG_INDEX_ANY );
		if ( $id !== false ) {
			$s = getCharIDStructure( $bc );
			$bcst = $s[ "t" ];
			if ( isset( $getBarCodeExtIDMap[ $bcst ] ) ) {
				$s[ "t" ] = $getBarCodeExtIDMap[ $bcst ][ "id-doctype" ];
				$id = mkCharID( $s );
			}
			return $id ;
		}
		$pa = array(
			array( 'p' => '/^(?:[0-1][0-3]0(?<extid>\d{7}))$/i' , 'f' => 'renewID' ) ,
			array( 'p' => '/^(?:2900(?<extid>\d{8}))$/i' , 'f' => 'renewID2900' ) ,
			array( 'p' => '/^(?:2101(?<extid>\d{8}))$/i' , 'f' => 'renewID2101' )
		);
		foreach( $pa as $i ) {
			if ( is_array( $i ) ) {
				$m = array();
				$n = preg_match( $i[ 'p' ] , $bc , $m );
				if ( $n == 1 ) {
					return $i[ 'f' ]( $m[ "extid" ] );
				}
			} else {
				$m = array();
				$n = preg_match( $i , $bc , $m );
				if ( $n == 1 ) {
					return $m[ "extid" ];
				}
			}
		}

		return false ;
	}

	if ( isset( $_REQUEST[ "src" ] ) ) {
		$src = $_REQUEST[ "src" ];

		if ( isset( $_REQUEST[ "type" ] ) ) {
			switch ( $_REQUEST[ "type" ] ) {
				case BARCODE_TYPE_1D :
				case BARCODE_TYPE_QRCODE :
				case BARCODE_TYPE_DATAMATRIX :
					$bcType = $_REQUEST[ "type" ];
					break ;

				default :
					$bcType = false ;
					break ;
			}
		} else {
			$bcType = BARCODE_TYPE_1D ;
		}
		if ( isset( $_REQUEST[ "opt" ] ) ) {
			$opt = json_decode( $_REQUEST[ "opt" ] , true );
		} else {
			$opt = array();
		}

		/*if ( $_SERVER[ "REMOTE_ADDR" ] == "10.1.0.71" ) {
			var_dump( $src );
		} else {
			header( "Content-type: image/png" );
		}*/
		header( "Content-type: image/png" );
		generateBarcode( $src , true , $bcType , $opt );
		exit ;
	}




	/*
	 * 0*** - maindb - Внутренние и исходящие
	 * 0010 - Обложка наблюдательного производства
	 * 0110 - matincoming / Заключение
	 * 0190 - Предварительная регистрация
	 * 0210 - Сопроводительное письмо
	 * 04** - Карточки
	 *   0410 - Карточка поручения
	 *   0420 - Карточка передачи материалов
	 *   0430 - Карточка вещ.док
	 * 05** - Ответы на запросы
	 * 06** - Ходатайства, запросы и пр. (с нашей стороны...)
	 *   0600 - Ходатайство
	 *   0610 - Ходатайство о предоставлении объекта
	 *   0620 - Ходатайство о продлении сроков
	 * 08** - Рапорты, внутренние документы
	 * 0890 - Прочие рапорты
	 * 09** - Отчеты
	 * 0910 - Отчет факса
	 * 0990 - Прочие отчеты
	 * 1*** - maindb - Входящие
	 * 1010 - Постановление/определение
	 * 1110 - Повестки
	 * 1210 - Запросы о возможности, стоимости и сроках
	 * 1310 - Жалобы
	 * 1410 - Доп мат
	 * 15** - Запросы о сроках, экспертах, уведомления (со стороны судей...)
	 * 16** - Ответы на ходатайства и запросы
	 *
	 *
	 * 2*** - portal
	 * 21** - Входящая корреспонденция
	 *   2110 -
	 * 22** - Исходящая корреспонденция
	 *   2210 -
	 * 30** -
	 * 31** - bills
	 * 3110 - Счет
	 * 3190 - Прочие документы по оплате экспертиз
	 * 32** - time-tables
	 * 33** - ertech
	 * 39** -
	 * 4010 - Квитанция на оплату (нотариусы / со своей нумерацией)
	 * 4020 - Квитанция на оплату (предрегистрация)
	 *
	 * 99** - Различные несуществующие тестовые объекты
	 * Заявление о выдаче И/Л, заявления о возрате денег, исп листы, от приставов (исп производства, и прочее по оплате)
	 * рицензии,
	 */


	/*
		000******* : Обложка наблюдательного производства, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		010******* : Заключение эксперта, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		020******* : Сопроводительное письмо, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		030******* : Постановление/определение, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		100******* : Карточка поручения, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		110******* : Карточка передачи материалов, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		120******* : Счет, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		130******* : Заявление о выдаче И/Л, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		140******* : Доп.материалы, где ******* ID экспертизы ( matincoming.id ) (старый формат)

		2*** : Ходатайства, запросы и пр.
			2101****** : Ходатайство о предоставлении объекта, где ****** ID карточки эксперта ( matincoming.id )
			2102****** : Ходатайство о продлении сроков, где ****** ID карточки эксперта ( matincoming.id )
		2900****** : Бланк с шапкой, обобщенным препровождением, заголовком "ЗАПРОС/ХОДАТАЙСТВО" и пустым телом , где ****** ID карточки эксперта ( expertize.id )
		2999****** : По пользовательскому шаблону

		-------------------------------------------------------------------

		0010******* : Обложка наблюдательного производства, где ******* ID экспертизы ( matincoming.id ) (старый формат)
	+	0110******* : Заключение эксперта, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		0210******* : Сопроводительное письмо, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		0310******* : Постановление/определение, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		1010******* : Карточка поручения, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		1110******* : Карточка передачи материалов, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		1210******* : Счет, где ******* ID экспертизы ( matincoming.id ) (старый формат)
		1310******* : Заявление о выдаче И/Л, где ******* ID экспертизы ( matincoming.id ) (старый формат)

		2*** : Ходатайства, запросы и пр.
			2101****** : Ходатайство о предоставлении объекта, где ****** ID карточки эксперта ( matincoming.id )
			2102****** : Ходатайство о продлении сроков, где ****** ID карточки эксперта ( matincoming.id )
		2900****** : Бланк с шапкой, обобщенным препровождением, заголовком "ЗАПРОС/ХОДАТАЙСТВО" и пустым телом , где ****** ID карточки эксперта ( expertize.id )
		2999****** : По пользовательскому шаблону
	*/
?>