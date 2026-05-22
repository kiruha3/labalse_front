<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	include_once( "lconfig.php" );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	$delimitersMap = array(
		"z1" => "+" ,
		"z2" => "-" ,
		"z3" => "|" ,
		"z4" => chr( 179 ),
		"z5" => chr( 180 ),
		"z6" => chr( 191 ),
		"z7" => chr( 192 ),
		"z8" => chr( 193 ),
		"z9" => chr( 194 ),
		"z10" => chr( 195 ),
		"z11" => chr( 196 ),
		"z12" => chr( 197 ),
		"z13" => chr( 217 ),
		"z14" => chr( 218 )
	);

	function checkRowDelimiter( $s ) {
		global $delimiters , $breakRowByText ;
		//var_dump( $breakRowByText );
		$res = true ;
		for( $i = 0 ; $i < strlen( $s ) ; $i++ ) {
			$res &= isset( $delimiters[ $s[ $i ] ] );
		}

		if ( $breakRowByText !== false ) {
			$v = substr( $s , $breakRowByText[ "s" ] , $breakRowByText[ "w" ] );
			$res|= trim( $v ) != '' ;
		}

		return $res ;
	}

	function checkColDelimiter( &$d , $p ) {
		global $delimiters ;
		$res = true ;
		foreach( $d as &$r ) {
			$res &= isset( $delimiters[ $r[ $p ] ] );
			if ( !$res ) {
				return $res ;
			}
		} unset( $r );
		return $res ;
	}

	$step = 1 ;
	$data = array();
	if ( !isset( $_FILES[ "tgtFile" ] ) || $_FILES[ "tgtFile" ][ "error" ] != 0 ) {
		$step = 1 ;
	} else {
		$tgtFileName = $_FILES[ "tgtFile" ][ "name" ];
		$fh = fopen( $_FILES[ "tgtFile" ][ "tmp_name" ] , "r" );
		$sfb = intval( $_REQUEST[ "skipFB" ] );
		$sfe = intval( $_REQUEST[ "skipFE" ] );
		$breakRowByText = isset( $_REQUEST[ "breakRowByText" ] ) && $_REQUEST[ "breakRowByText" ] == "breakRowByText" ;
		if ( $breakRowByText === true ) {
			$breakRowByText = isset( $_REQUEST[ "brbtCOL" ] ) ? intval( $_REQUEST[ "brbtCOL" ] ) : 1 ;
		}

		$skipFlt = array();
		if ( isset( $_REQUEST[ "skipFlt" ] ) && strlen( $_REQUEST[ "skipFlt" ] ) > 0 ) {
			$skipFlt = str_replace( "\r" , "\n" , $_REQUEST[ "skipFlt" ] );
			$skipFlt = str_replace( "\n\n" , "\n" , $skipFlt );
			$skipFlt = explode( "\n" , $skipFlt );
			foreach ( $skipFlt as &$csf ) {
				$csf = "/".trim( $csf )."/" ;
			} unset( $csf );
		}

		$delimiters = array();
		foreach( $_REQUEST[ "delim" ] as $cd ) {
			$delimiters[ $delimitersMap[ $cd ] ] = true ;
		}

		$fsEnc = $_REQUEST[ "fEnc" ];

		$strNum = 0 ;
		while( !feof( $fh ) ) {
			$s = fgets( $fh );

			if ( $fsEnc !== "cp1251" ) {
				$s = iconv( $fsEnc , "cp1251//TRANSLIT" , $s );
			}

			$strNum++ ;
			if ( $strNum < $sfb ) {
				continue ;
			}

			if ( $strNum > $sfe ) {
				break ;
			}

			$maySkip = false ;
			foreach ( $skipFlt as &$csf ) {
				$mn = preg_match( $csf , $s );
				$maySkip|= $mn == 1 ;
				if ( $maySkip ) {
					break ;
				}
			} unset( $csf );
			if ( !$maySkip ) {
				$data[]= $s ;
			}
		}
		fclose( $fh );

		$mll = 0 ;
		foreach( $data as &$r ) {
			if ( strlen( $r ) > $mll ) {
				$mll = strlen( $r );
			}
		} unset( $r );

		$colDelimiters = array();
		$colLast = 0 ;
		for( $i = 0 ; $i < $mll ; $i++ ) {
			if ( checkColDelimiter( $data , $i ) ) {
				$colLast = $i ;
				$colDelimiters[]= $i ;
			}
		}

		if ( count( $colDelimiters ) > 1 ) {
			$p = $colDelimiters[ 0 ];
		} else {
			echo "Таблица не найдена" ;
			exit ;
		}

		$tw = $colLast - $p + 1 ;
		foreach( $data as &$r ) {
			$r = substr( $r , $p , $tw );
		} unset( $r );

		foreach( $colDelimiters as &$cd ) {
			$cd -= $p ;
		} unset( $cd );

		$colCount = count( $colDelimiters ) - 1 ;
		$colWidth = array();
		for( $i = 0 ; $i < $colCount ; $i++ ) {
			$colWidth[]= $colDelimiters[ $i + 1 ] - $colDelimiters[ $i ] - 1 ;
		}

		if ( $breakRowByText !== false ) {
			$breakRowByText = array( "s" => $colDelimiters[ $breakRowByText - 1 ] + 1 , "w" => $colWidth[ $breakRowByText - 1 ] );
		}


		$currentRow = false ;
		$convertedTab = array();
		foreach( $data as &$r ) {
			if ( checkRowDelimiter( $r ) ) {
				if ( $currentRow !== false ) {
					foreach( $currentRow as &$ccd ) {
						$ccd = str_replace( "\r" , " " , $ccd );
						$ccd = str_replace( "\n" , " " , $ccd );
						$ccd = str_replace( "\t" , " " , $ccd );
						$ccdl = strlen( $ccd );
						$ccd = str_replace( "  " , " " , $ccd );
						while ( $ccdl > strlen( $ccd ) ) {
							$ccdl = strlen( $ccd );
							$ccd = str_replace( "  " , " " , $ccd );
						}
						$ccd = trim( $ccd );
					} unset( $ccd );
					$convertedTab[]= $currentRow ;
				}
				$currentRow = array();
				for( $i = 0 ; $i < $colCount ; $i++ ) {
					$currentRow[]= "" ;
				}
			}

			if ( $currentRow !== false ) {
				for( $i = 0 ; $i < $colCount ; $i++ ) {
					$currentRow[ $i ].= substr( $r , $colDelimiters[ $i ] + 1 , $colWidth[ $i ] );
				}
			}
		} unset( $r );

		$fltData = array(
			"Price" => array( '/(\d+)[-](\d{2})/' , '$1,$2' )
		);

		foreach( $fltData as $cfltn => $cfltd ) {
			$fltColInd = array();
			if ( isset( $_REQUEST[ "flt".$cfltn ] ) && preg_match( '/^\s*\d+\s*(?:[,]\s*\d+\s*)*$/' , $_REQUEST[ "flt".$cfltn ] ) == 1 ) {
				$fltColInd = explode( "," , $_REQUEST[ "fltPrice" ] );
				foreach( $fltColInd as &$cci ) {
					$cci = intval( trim( $cci ) ) - 1 ;
				} unset( $cci );
			}
			if ( count( $fltColInd ) > 0 ) {
				foreach( $convertedTab as &$ctr ) {
					foreach ( $fltColInd as $ind ) {
						$ctr[ $ind ] = preg_replace( $cfltd[ 0 ] , $cfltd[ 1 ] , $ctr[ $ind ] );
					}
				} unset( $ctr );
			}
		}

		$step = 2 ;
	}

	//print_r_html( $_FILES );

	switch( $step ) {
		case 1 :
			MainHead_L2( "Админка" , "Админка" , array( "%UT/tab-cvt.css" ) , array() , "hlp/tab-cvt.html" );

			/*$ec = iconv_get_encoding( "all" );
			print_r_html( $ec );*/

			echo "<form action=\"tab-cvt.php\" method=\"post\" enctype=\"multipart/form-data\">
				<center>
					<input name=\"MAX_FILE_SIZE\" type=\"hidden\" value=\"8589934592\"><br>
					<input name=\"tgtFile\" type=\"file\" class=\"tgtFile\"><br>

					<select name=\"fEnc\">
						<option value=\"cp1251\">CP1251 / Windows-1251</option>
						<option value=\"cp866\">CP866 / DOS</option>
					</select>

					Начать со строки <input name=\"skipFB\" type=\"text\" value=\"0\" class=\"skip\"> строк<br>
					Окончить строкой <input name=\"skipFE\" type=\"text\" value=\"0\" class=\"skip\"> строк<br>
					<br> ";

					foreach( $delimitersMap as $dk => $dv ) {
						echo "<span class=\"delim-symbols\"><input name=\"delim[]\" type=\"checkbox\" value=\"".$dk."\" checked class=\"delim\"> ".iconv( "cp866" , "cp1251//TRANSLIT" , $dv )." (".ord( $dv ).")</span>" ;
					}

					echo "<br><br><br>
					разбиение строк текстом <input name=\"breakRowByText\" type=\"checkbox\" value=\"breakRowByText\" class=\"\"><br>
					контрольный столбец <input name=\"brbtCOL\" type=\"text\" value=\"1\">
					<br>" ;

					echo "<br><br>
					пропустить (regexp)<br>
					<textarea name=\"skipFlt\" class=\"skipFlt\"></textarea>


					<br><br>

					Фильтры:<br>
					Стоимость xxxxx-yy -> xxxxx.yy <input name=\"fltPrice\" type=\"text\" value=\"\" class=\"fltColumns\"><br>

					<br><br>

					<input name=\"doCheck\" type=\"submit\" value=\"Проверить\">
					<input name=\"checkRowsCount\" type=\"text\" value=\"100\" class=\"checkRowsCount\"> строк <br>
					<input name=\"doCvt\" type=\"submit\" value=\"Преобразовать\"><br>

				</center>
			</form>" ;

			closeHtml();
			break ;

		case 2 :
			if ( isset( $_REQUEST[ "doCheck" ] ) ) {
				MainHead_L2( "Админка" , "Админка" , array( "%UT/tab-cvt.css" ) , array() , "hlp/tab-cvt.html" );
				echo "<div class=\"cap\">Образец исходного файла</div><div class=\"preview\">" ;
				for( $i = 0 ; $i < min( intval( $_REQUEST[ "checkRowsCount" ] ) , count( $data ) ) ; $i++ ) {
					echo "<span class=\"lineNum\">".( $sfb + $i + 1 )."</span>".print_r_html_2( $data[ $i ] );
				}
				echo "</div>" ;

				echo "<div class=\"cap\">Образец результата обработки</div><div class=\"preview-res\"><table class=\"preview-res-tab\">" ;
				for( $i = 0 ; $i < min( intval( $_REQUEST[ "checkRowsCount" ] ) , count( $convertedTab ) ) ; $i++ ) {
					echo "<tr>" ;
					foreach( $convertedTab[ $i ] as $ctc ) {
						echo "<td>".$ctc."</td>" ;
					}
					echo "</tr>" ;
				}
				echo "</table></div>" ;
				closeHtml();
			} else {
				header( "Content-Type: text/csv" );
				header( "Content-Disposition: attachment;filename=".$tgtFileName.".csv" );
				$fp = fopen( "php://output" , "w" );
				foreach ( $convertedTab as &$rr ) {
					fputcsv( $fp , $rr , ";" );
				}
				fclose( $fp );
			}
			break ;
	}
?>