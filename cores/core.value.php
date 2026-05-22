<?php
	function isValidLogin( $login ) {
		$re = "/^.{3,32}$/" ;
		return ( preg_match( $re , "".$login ) == 1 );
	}
	
	function isValidPassHash( $pass ) {
		$re = "/^[a-zA-Z0-9]{54}\\=\\=$/" ;
		return ( preg_match( $re , "".$pass ) == 1 );
	}
	
	function isValidJSON( $v ) {
		$r = json_decode( iconv( "cp1251" , "utf8" , $v ) );
		return ( $r !== null );
	}
	
	function PrepDate( $date ) {
		$date = str_replace( "." , "-" , $date );
		$date = str_replace( "," , "-" , $date );
		$date = str_replace( "--" , "-" , $date );
		$n = 0 ;
		for ( $k= 0 ; $k < strlen( $date ) ; $k++ ) {
			if ( $date[ $k ] == "-" ) {
				$n++ ;
			}
		}
		
		if ( $n == 2 ) {
			list( $dd , $mm , $yy ) = explode( "-" , $date );
			$date = $yy."-".$mm."-".$dd ;
		} else
			if ( $n == 1 ) {
				list( $mm , $yy ) = explode( "-" , $date );
				$date = $yy."-".$mm ;
			}
		
		return $date ;
	}
	
	function clearText( $text , $multiline = false ) {
		if ( $multiline ) {
			return trim( preg_replace( '/ {2,}/' , ' ' , $text ) );
		} else {
			return trim( preg_replace( '/\s+/' , ' ' , $text ) );
		}
	}
	
	function strexp( $str ) {
		$pref = "" ;
		$i = 0 ;
		$s = "pref" ;
		$l = strlen( $str );
		$il = $l ;
		$v = array();
		$cv = "" ;
		$bl = 0 ;
		while ( $i < $l && $s != "fin" ) {
			$c = $str[ $i ];
			$i++ ;
			switch ( $s ) {
				case "pref" :
					switch ( $c ) {
						case "{" :
							$s = "br" ;
							$cv = "" ;
							break ;
						
						case "\\" :
							$s = "pref.sch" ;
							break ;
						
						default :
							$pref.= $c ;
							break ;
					}
					break ;
				
				case "pref.sch" :
					$pref.= $c ;
					$s = "pref" ;
					break ;
				
				case "br" :
					switch ( $c ) {
						case "," :
							$v[]= $cv ;
							$cv = "" ;
							break ;
						
						case "}" :
							$v[]= $cv ;
							$il = $i ;
							$s = "fin" ;
							break ;
						
						case "\\" :
							$s = "br.sch" ;
							break ;
						
						case "{" :
							$cv.= $c ;
							$bl = 1 ;
							$s = "sbr" ;
							break ;
						
						default :
							$cv.= $c ;
							break ;
					}
					break ;
				
				case "br.sch" :
					$cv.= $c ;
					$s = "br" ;
					break ;
				
				case "sbr" :
					switch ( $c ) {
						case "}" :
							$cv.= $c ;
							$bl-- ;
							if ( $bl == 0 ) {
								$s = "br" ;
							}
							break ;
						
						case "{" :
							$cv.= $c ;
							$bl++ ;
							break ;
						
						case "\\" :
							$s = "sbr.sch" ;
							break ;
						
						default :
							$cv.= $c ;
							break ;
					}
					break ;
				
				case "sbr.sch" :
					$cv.= $c ;
					$s = "sbr" ;
					break ;
			}
		}
		
		$suff = substr( $str , $il );
		$res = array();
		if ( count( $v ) > 0 ) {
			foreach ( $v as $cv ) {
				$res = array_merge( $res , strexp( $pref.$cv.$suff ) );
			}
		} else {
			$res[]= $pref.$suff ;
		}
		
		return $res ;
	}

	function strexp2( $str ) {
		$pref = "" ;
		$i = 0 ;
		$s = "pref" ;
		$l = strlen( $str );
		$il = $l ;
		$v = array();
		$cv = "" ;
		$bl = 0 ;
		$vlt = false ;
		while ( $i < $l && $s != "fin" ) {
			$c = $str[ $i ];
			$i++ ;
			switch ( $s ) {
				case "pref" :
					switch ( $c ) {
						case "{" :
							$s = "br" ;
							$cv = "" ;
							break ;

						case "\\" :
							$s = "pref.sch" ;
							break ;

						default :
							$pref.= $c ;
							break ;
					}
					break ;

				case "pref.sch" :
					$pref.= $c ;
					$s = "pref" ;
					break ;

				case "br" :
					switch ( $c ) {
						case "." :
							$s = "br.dot" ;
							break ;
						case "," :
							if ( $vlt == false || $vlt == "list" || $vlt == "range" ) {
								$v[]= array( "t" => "list" , "v" => $cv );
								$vlt = "list" ;
							} else
								if ( $vlt == "rangeL" ) {
									$tmp = array_pop( $v );
									$tmp[ "v" ][ "R" ] = $cv ;
									$v[]= $tmp ;
									$vlt = "range" ;
								}
							$cv = "" ;
							break ;

						case "}" :
							if ( $vlt == false || $vlt == "list" || $vlt == "range" ) {
								$v[]= array( "t" => "list" , "v" => $cv );
								$vlt = "list" ;
							} else
								if ( $vlt == "rangeL" ) {
									$tmp = array_pop( $v );
									$tmp[ "v" ][ "R" ] = $cv ;
									$v[]= $tmp ;
									$vlt = "range" ;
								}
							$il = $i ;
							$s = "fin" ;
							break ;

						case "\\" :
							$s = "br.sch" ;
							break ;

						case "{" :
							$cv.= $c ;
							$bl = 1 ;
							$s = "sbr" ;
							break ;

						default :
							$cv.= $c ;
							break ;
					}
					break ;

				case "br.sch" :
					$cv.= $c ;
					$s = "br" ;
					break ;

				case "br.dot" :
					if ( $c == "." ) {
						$v[]= array( "t" => "range" , "v" => array( "L" => $cv ) );
						$vlt = "rangeL" ;
						$cv = "" ;
					} else {
						$cv.= ".".$c ;
					}
					$s = "br" ;
					break ;

				case "sbr" :
					switch ( $c ) {
						case "}" :
							$cv.= $c ;
							$bl-- ;
							if ( $bl == 0 ) {
								$s = "br" ;
							}
							break ;

						case "{" :
							$cv.= $c ;
							$bl++ ;
							break ;

						case "\\" :
							$s = "sbr.sch" ;
							break ;

						default :
							$cv.= $c ;
							break ;
					}
					break ;

				case "sbr.sch" :
					$cv.= $c ;
					$s = "sbr" ;
					break ;
			}
		}

		//print_r_html( $v , 1 );

		$suff = substr( $str , $il );
		$res = array();
		if ( count( $v ) > 0 ) {
			foreach ( $v as $cv ) {
				if ( $cv[ "t" ] == "list" ) {
					$res = array_merge( $res , strexp2( $pref.$cv[ "v" ].$suff ) );
				} else
					if ( $cv[ "t" ] == "range" ) {
						foreach ( range( $cv[ "v" ][ "L" ] , $cv[ "v" ][ "R" ] ) as $crv ) {
							$res = array_merge( $res , strexp2( $pref.$crv.$suff ) );
						}
					}
			}
		} else {
			$res[]= $pref.$suff ;
		}

		return $res ;
	}

	function array_rekey( $data , $srcKeyPattern , $destKeyPattern , $thisKey = false ) {
		$res = array();
		if ( $thisKey !== false ) {
			if ( !is_array( $thisKey ) ) {
				$thisKey = strexp( "".$thisKey );
			}
			$data = array_intersect_key( $data , array_flip( $thisKey ) );
		}
		foreach ( $data as $k => $v ) {
			$k = preg_replace( $srcKeyPattern , $destKeyPattern , $k );
			$res[ $k ] = $v ;
		}
		
		return $res ;
	}
	
	function cvt( $s , $from = DEF_CODEPAGE , $to = "utf8" ) {
		return iconv( $from , $to , $s );
	}

	function rcvt( $s , $from = "utf8" , $to = DEF_CODEPAGE ) {
		return iconv( $from , $to , $s );
	}

	function rcvti( $s , $from = "utf8" , $to = DEF_CODEPAGE ) {
		return iconv( $from , $to.'//IGNORE' , $s );
	}
	
	function splitEx( $d , $str , $limit = -1 ) {
		if ( $limit == -1 ) {
			$res = preg_split( "/(?<!\\\\)\\$d/" , $str );
		} else {
			$res = preg_split( "/(?<!\\\\)\\$d/" , $str , $limit );
		}
		
		for ( $i = 0 ; $i < count( $res ) ; $i++ ) {
			$res[ $i ] = str_replace( "\\$d" , $d , $res[ $i ] );
		}
		return $res ;
	}
	
	$MonthNames = array (
		"январ{ь|я|ю|ь|ем|е}" , "феврал{ь|я|ю|ь|ем|е}", "март{|а|у||ом|е}"     ,
		"апрел{ь|я|ю|ь|ем|е}" , "ма{й|я|ю|й|ем|е}"    , "июн{ь|я|ю|ь|ем|е}"    ,
		"июл{ь|я|ю|ь|ем|е}"   , "август{|а|у||ом|е}"  , "сентябр{ь|я|ю|ь|ем|е}",
		"октябр{ь|я|ю|ь|ем|е}", "ноябр{ь|я|ю|ь|ем|е}" , "декабр{ь|я|ю|ь|ем|е}"  );
	$DaysOfWeek = array (
		"воскресень{е|я|ю|е|ем|е}" , "понедельник{|а|у||ом|е}" , "вторник{|а|у||ом|е}"  , "сред{а|ы|е|у|ой|е}"     ,
		"четверг{|а|у||ом|е}"      , "пятниц{а|ы|е|у|ей|е}"    , "суббот{а|ы|е|у|ой|е}" ,  "воскресень{е|я|ю|е|ем|е}" );

	$DaysOfWeekShort = array(
		'Вс' , 'Пн' , 'Вт' , 'Ср' , 'Чт' , 'Пт' , 'Сб' , 'Вс'
	);

	/**
	 * Форма слова - номер падежа
	 * Код формы слова [1-6][tf][mfo][ai]
	 * 		1ffi - Иминительный падеж / множ число / женский род / неодушевл
	 * 		4tma - Винительный  падеж / един число / мужской род / одушевл
	 * @param string $src слово с формами
	 * @param int $form номер падежа от 1 до 6
	 * @param bool $singular единственное число (true) или множественное (false)
	 * @param string $gender род - мужской (m) или женский (f)
	 * @param false $animacy одушевленность
	 * @return array|string|string[]|null
	 */
	function inForm( $src , $form = 1 , $singular = true , $gender = "m" , $animacy = false ) {
		/*if ( !$singular ) {
			if ( $form == 4 ) {
				if ( $animacy ) {
					$form = 2 ;
				} else {
					$form = 1 ;
				}
			}
			$form += 6 ;
		} else {
			if ( $gender == "m" && $form == 4 ) {
				if ( $animacy ) {
					$form = 2 ;
				} else {
					$form = 1 ;
				}
			}
		}*/
		$re = '([^^|}]*)\|' ;
		$re = str_pad( "" , strlen( $re ) * 6 - 2 , $re );
		$repl = '${'.$form.'}' ;
		$src = preg_replace( '/\{'.$re.'\}/i' , $repl , $src );
		$src = preg_replace( '/\{'.$re.'\^'.$re.'\}/i' , $repl , $src );
		return $src ;
	}
	
	/*function inForm( src , form , singular ) {
		if ( typeof singular === "undefined" ) {
			singular = true ;
		}
		if ( typeof form === "undefined" ) {
			form = 1 ;
		}
		if ( !singular ) {
			form+= 6 ;
		}

		var re = '([^^|}]*)\\|' ;
		re = str_pad( "" , re.length * 6 - 2 , re );
		alert( re );
		var repl = '$' + form ;
		src = src.replace( new RegExp( '\\{' + re + '\\}' , 'ig' ) , repl );
		src = src.replace( new RegExp( '\\{' + re + '\\^' + re + '\\}' , 'ig' ) , repl );

		return src ;
	}*/
	
	
	function packFormsES_CheckP( &$p , &$forms ) {
		foreach( $p as $pk => $pv ) {
			if ( !isset( $forms[ $pk ][ $pv ] ) ) {
				unset( $p[ $pk ] );
			}
		}
	}
	
	function packFormsES( $forms ) {
		$res = "" ;
		$p = array();
		for( $i = 0 ; $i < 6 ; $i++ ) {
			$p[ $i ] = 0 ;
		}
		$m = true ;
		
		packFormsES_CheckP( $p , $forms );
		
		while ( count( $p ) == 6 ) {
			for( $i = 1 ; $i < 6 ; $i++ ) {
				$m = $m & ( $forms[ $i ][ $p[ $i ] ] == $forms[ 0 ][ $p[ 0 ] ] );
			}
			
			if ( $m ) {
				$res.= $forms[ 0 ][ $p[ 0 ] ];
				foreach( $p as &$pv ) {
					$pv++ ;
				} unset( $pv );
			} else {
				$res.= "{" ;
				foreach( $p as $pk => &$pv ) {
					while( isset( $forms[ $pk ][ $pv ] ) && $forms[ $pk ][ $pv ] != " " ) {
						$res.= $forms[ $pk ][ $pv++ ];
					}
					$res.= $pk < 5 ? "|" : "}" ;
				} unset( $pv );
				$m = true ;
			}
			
			packFormsES_CheckP( $p , $forms );
		}
		
		if ( count( $p ) > 0 ) {
			if ( $m ) {
				$res.= "{" ;
			}
			
			for( $pk = 0 ; $pk < 6 ; $pk++ ) {
				while( isset( $p[ $pk ] ) && isset( $forms[ $pk ][ $p[ $pk ] ] ) ) {
					$res.= $forms[ $pk ][ $p[ $pk ]++ ];
				}
				$res.= $pk < 5 ? "|" : "}" ;
			}
		}
		
		return $res ;
	}
	
	function LongDate( $date , $form = 2 ) {
		global $MonthNames ;
		$d = strtotime( PrepDate( $date ) );
		$day = date( "d", $d );
		$month = intval( date( "m" , $d ) );
		$year = date( "Y" , $d );
		
		return "&#171;".$day."&#187; ".inForm( $MonthNames[ $month - 1 ], $form )." ".$year." г." ;
	}
	
	function setTreeElement( &$root , $fIN , &$val , $di = false , $extract = false ) {
		if ( count( $fIN ) > 0 ) {
			$cFIN = $fIN[ 0 ];
			$cIV = $val[ $cFIN ];
			if ( !isset( $root[ $cIV ] ) ) {
				$root[ $cIV ] = array();
			}
			array_shift( $fIN );
			if ( $di ) {
				unset( $val[ $cFIN ] );
			}
			setTreeElement( $root[ $cIV ] , $fIN , $val , $di , $extract );
		} else {
			//print_r_html( $root );
			//print_r_html( $val );
			if ( $extract !== false ) {
				$root[]= $val[ $extract ];
			} else {
				$root = $val ;
			}
			//print_r_html( $root );
			//echo "<hr>" ;
		}
	}
	
	function treeArray( $data , $fIN , $di = false , $extract = false ) {
		$result = array();
		foreach( $data as $row ) {
			setTreeElement( $result , $fIN , $row , $di , $extract );
		}
		
		return $result ;
	}
	
	function Array2Str( &$arr , $str , $col , $doEcho = false , $autoflush = 5 ) {
		if ( $doEcho ) {
			$i = 0 ;
			foreach( $arr as &$ai ) {
				$tmp = $str ;
				foreach( $col as &$c ) {
					$tmp = str_replace( "\$".$c , $ai[ $c ] , $tmp );
					$tmp = str_replace( "%".$c."%" , $ai[ $c ] , $tmp );
				}
				unset( $c );
				echo $tmp ;
				$i++ ;
				if ( $i == $autoflush ) {
					flush();
				}
			}
			unset( $ai );
		} else {
			$res = "" ;
			
			foreach( $arr as &$ai ) {
				$tmp = $str ;
				foreach( $col as &$c ) {
					$tmp = str_replace( "\$".$c , $ai[ $c ] , $tmp );
					$tmp = str_replace( "%".$c."%" , $ai[ $c ] , $tmp );
				}
				unset( $c );
				$res.= $tmp ;
			}
			unset( $ai );
			
			return $res ;
		}
	}
	
	function Float2Int( $str , $digit , $err = 0 ) {
		if ( isValidFloat( $str , $digit ) ) {
			$str = str_replace( "," , "." , $str );
			$p = strpos( $str , "." );
			if ( $p === FALSE ) {
				$p = strlen( $str );
				$d = 0 ;
			} else {
				$d = strlen( $str ) - $p - 1 ;
			}
			
			return substr( $str , 0 , $p ).substr( $str , $p + 1 , $d ).str_repeat( "0" , $digit - $d );
		} else {
			return $err ;
		}
	}
	
	function Date2Int( $d ) {
		$m = array();
		$n = preg_match( '/^\s*(\d{2})[,.-](\d{2})[,.-](\d{4})\s*$/' , $d , $m );
		if ( $n != 1 ) {
			return false ;
		}
		
		list( , $dd , $mm , $yy ) = $m ;
		$dd = intval( $dd );
		$mm = intval( $mm );
		$yy = intval( $yy );
		
		if ( $mm < 1 || $mm > 12 ) {
			return false ;
		}
		
		$t = intval( date( "t" , mktime( 0 , 0 , 0 , $mm , 1 , $yy ) ) );
		if ( $dd < 1 || $dd > $t ) {
			return false ;
		}
		
		return mktime( 0 , 0 , 0 , $mm , $dd , $yy );
	}
	
	function isEmptyString( $str , $pattern = "" ) {
		if ( $pattern != "" ) {
			$str = trim( $str , $pattern );
		} else {
			$str = trim( $str );
		}
		
		if ( strlen( $str ) > 0 ) {
			$res = FALSE ;
		} else {
			$res = TRUE;
		}
		
		return $res;
	}
	
	function isValidDate( $date ) {
		$m = array();
		$n = preg_match( '/^\s*(\d{2})[,.-](\d{2})[,.-](\d{4})\s*$/' , $date , $m );
		if ( $n != 1 ) {
			return false ;
		}
		
		list( , $dd , $mm , $yy ) = $m ;
		$dd = intval( $dd );
		$mm = intval( $mm );
		$yy = intval( $yy );
		
		if ( $mm < 1 || $mm > 12 ) {
			return false ;
		}
		
		$t = intval( date( "t" , mktime( 0 , 0 , 0 , $mm , 1 , $yy ) ) );
		if ( $dd < 1 || $dd > $t ) {
			return false ;
		}
		return true ;
	}
	
	function isValidInt( $num , $may_negative = TRUE ) {
		$re = "/^".( $may_negative ? "\\-?" : "" )."\\d{1,11}$/" ;
		return ( preg_match( $re , "".$num ) == 1 );
	}
	
	function isValidFloat( $num , $digit = 15 , $may_negative = TRUE ) {
		$re = "/^[".( $may_negative ? "\\-" : "" )."\\+]?\\d{1,11}".( $digit > 0 ? "(?:[\\.\\,]\\d{1,".$digit."})?" : "" )."$/" ;
		return ( preg_match( $re , "".$num ) == 1 );
	}
	
	
	/*
	Функции для работы с именами.
	Формат имени: `f=Фамил{ия|ию|ии|ию|ией|ии};i=Им{я|ени|ени|я|енем|ени};o=Отчеств{о|ва|у|о|ом|е}`
	Форматирование: %Fx, %Ix, %Ox - Полные Фамилия Имя и Отчество в падеже x, где x номер падежа
		Именительный - 1, Родительный, Дательный, Винительный, Тварительный, Предложный - 6.
		%f, %i, %o - 'Инициалы' (сокращенные) Фамилия, Имя, Отчество
	Примеры: $str = "f=Фамил{ия|ию|ии|ию|ией|ии};i=Им{я|ени|ени|я|енем|ени};o=Отчеств{о|ва|у|о|ом|е}";
		$ss = NAMES_parse($str); # Дает Array(
								 #			[f] => Фамил{ия|ию|ии|ию|ией|ии}
								 #			[i] => Им{я|ени|ени|я|енем|ени}
								 #			[o] => Отчеств{о|ва|у|о|ом|е}
								 #			[n] => Назван{ие|ия|ию|ие|ием|ии}
								 #		)
		echo NAMES_Format($ss, "%F1 %i.%o."); # выведет: Фамилия И.О.
	*/
	
	function NAMES_clear( $s ) {
		$res = trim( $s );
		$res = trim( $res , ";/,." );
		$res = str_replace( "  " , " " , $res );
		$res = str_replace( "/" , ";" , $res );
		$res = str_replace( "." , ";" , $res );
		$res = str_replace( "," , ";" , $res );
		return $res ;
	}
	
	function NAMES_parse( $s ) {
		$ss = explode( ";" , $s );
		$res = array();
		foreach( $ss as &$i ) {
			$ps = NAMES_clear( $i );
			$pos = strpos( $ps , "=" );
			if ( ( $pos === false ) || ( $pos == 0 ) ) {
				array_push( $res , $ps );
			} else {
				$res[ strtolower( substr( $ps , 0 , $pos ) ) ] = substr( $ps , $pos + 1 , strlen( $ps ) - $pos - 1 );
			}
		}
		unset( $i );
		return $res ;
	}
	
	function NAMES_Format( $fio , $fmt = "%F1 %i.%o." , $afmt = "%N1" ) {
		$res = "" ;
		if ( isset( $fio[ "n" ] ) ) {
			$fmt = $afmt ;
		}
		for( $i = 0 ; $i < strlen( $fmt ) ; $i++ ) {
			if ( $fmt[ $i ] == "%" ) {
				$i++ ;
				$l = $fmt[ $i ];
				switch( $l ) {
					case "f" :
					case "i" :
					case "o" :
					case "n" :
						if ( isset( $fio[ $l ] ) ) {
							$res.= $fio[ $l ][ 0 ];
						}
						break ;
					case "F" :
					case "I" :
					case "O" :
					case "N" :
						$i++ ;
						$k = $fmt[ $i ];
						$l = strtolower( $l );
						if ( isset( $fio[ $l ] ) ) {
							if ( $k == "0" ) {
								$res.= $fio[ $l ];
							} else {
								$res.= inForm( $fio[ $l ] , $k );
							}
						}
						break ;
				}
			} else {
				$res.= $fmt[ $i ];
			}
		}
		return $res ;
	}
	
	function getInt( $i , $r = false , $onErr = false ) {
		$m = array();
		$n = preg_match( "/\\-?\\d{1,".PHP_INT_SIZE."}/" , "".$i , $m );
		if ( $n == 1 ) {
			$i = intval( $i );
			if ( is_array( $r ) && isset( $r[ "min" ] ) && $r[ "min" ] > $i ) {
				return $onErr ;
			}
			if ( is_array( $r ) && isset( $r[ "max" ] ) && $r[ "max" ] < $i ) {
				return $onErr ;
			}
			return $i ;
		} else {
			return $onErr ;
		}
	}
	
	function remap( &$a , $c ) {
		$res = array();
		foreach ( $a as &$ca ) {
			$id = $ca[ $c ];
			if ( !isset( $res[ $id ] ) ) {
				$res[ $id ] = array();
			}
			$res[ $id ][]= $ca ;
		} unset( $ca );
		
		return $res ;
	}
	
	function linkTablesIntoTree( $a0 , $a1 , $a1k , $a0nk , $a1nk = null , &$lost = null ) {
		foreach ( $a0 as &$i0 ) {
			$i0[ $a0nk ] = array();
		} unset( $i0 );
		
		$sa1nk = $a1nk !== null ;
		
		$al = isset( $lost ) && $lost !== null ;
		
		if ( $al ) {
			$lost = array();
		}
		
		foreach ( $a1 as &$i1 ) {
			$a0k = $i1[ $a1k ];
			
			if ( isset( $a0[ $a0k ] ) ) {
				$i0 = &$a0[ $a0k ];
				$i0[ $a0nk ][]= &$i1 ;
				if ( $sa1nk ) {
					$i1[ $a1nk ] = &$i0 ;
				}
			} else
				if ( $al ) {
					$lost[]= &$i1 ;
				}
		} unset( $i1 );
		unset( $i0 );
		
		return $a0 ;
	}
	
	function linkTablesIntoTreeDirect( &$a0 , &$a1 , $a1k , $a0nk , $a1nk = null , &$lost = null ) {
		foreach ( $a0 as &$i0 ) {
			$i0[ $a0nk ] = array();
		} unset( $i0 );
		
		$sa1nk = $a1nk !== null ;
		
		$al = isset( $lost ) && $lost !== null ;
		
		if ( $al ) {
			$lost = array();
		}
		
		foreach ( $a1 as &$i1 ) {
			$a0k = $i1[ $a1k ];
			
			if ( isset( $a0[ $a0k ] ) ) {
				$i0 = &$a0[ $a0k ];
				$i0[ $a0nk ][]= &$i1 ;
				if ( $sa1nk ) {
					$i1[ $a1nk ] = &$i0 ;
				}
			} else
				if ( $al ) {
					$lost[]= &$i1 ;
				}
		} unset( $i1 );
		unset( $i0 );
	}
	
	function packTreeIntoFlatTable( $a , $ak , $func , $defv = null , $nk = null , $dak = false ) {
		if ( $nk !== null ) {
			foreach ( $a as &$i ) {
				$i[ $nk ] = array_reduce( $i[ $ak ] , $func , $defv );
				if ( $dak ) {
					unset( $i[ $ak ] );
				}
			} unset( $i );
		} else {
			foreach ( $a as &$i ) {
				$i[ $ak ] = array_reduce( $i[ $ak ] , $func , $defv );
			} unset( $i );
		}
		return $a ;
	}
	
	function price2word( $price ) {
		$kop = "копе{йка|йки|йке|йку|йками|йке^йки|ек|йкам|йки|йками|йках}" ;
		$groups = array ( "рубл{ь|я|ю|ь|ем|е^и|ей|ям|и|ями|ях}" , "тысяч{а|и|е|у|ей|е^и||ам|и|ами|ах}" , "миллион{|а|у||ом|е^ы|ов|ам|ы|ами|ах}" );
		$num = array ( );
		$num[ "m" ] = array( "ноль" , "один" , "два" , "три" , "четыре" , "пять" , "шесть" , "семь" , "восемь" , "девять" ) ;
		$num[ "f" ] = array( "ноль" , "одна" , "две" , "три" , "четыре" , "пять" , "шесть" , "семь" , "восемь" , "девять" ) ;
		$num1 = array( "десять" , "одиннадцать" , "двенадцать" , "тринадцать" , "четырнадцать" , "пятнадцать" , "шестнадцать" , "семнадцать" , "восемнадцать" , "девятнадцать" ) ;
		$num2 = array( "ноль" , "десять" , "двадцать" , "тридцать" , "сорок" , "пятьдесят" , "шестьдесят" , "семьдесят" , "восемьдесят" , "девяносто" ) ;
		$num3 = array( "ноль" , "сто" , "двести" , "триста" , "четыреста" , "пятьсот" , "шестьсот" , "семьсот" , "восемьсот" , "девятьсот" ) ;
		
		$p = $price ;
		$pos = strpos( $p , "," );
		if ( $pos !== false ) {
			$tmp = substr( $p , 0 , $pos );
		} else {
			$tmp = $p ;
		}
		
		$res = "" ;
		if ( intval( $tmp ) == 0 ) {
			$res = $num[ "m" ][ 0 ]." ".inForm( $groups[ 0 ] , 2 , false )." " ;
		} else {
			for ( $j = 0 ; strlen( $tmp ) > 0 ; $j++ ) {
				$n = substr( $tmp, -min( 3 , strlen( $tmp ) ) );
				$nn = intval( $n );
				
				$tmp = substr( $tmp , 0 , -3 );
				$tmp2 = "" ;
				if ( $nn > 99 ) {
					$nnn = intval( substr( $n , -3 , 1 ) );
					if ( $nnn > 0 ) {
						$tmp2 = $num3[ $nnn ]." " ;
					}
					$nn = intval( substr( $n , -2 ) );
				}
				
				$k = intval( substr ( $n , -1 ) );
				if ( $k == 1 ) {
					$l = 1 ;
					$m = true ;
				} else
					if ( $k > 1 && $k < 5 ) {
						$l = 2 ;
						$m = true ;
					} else {
						$l = 2 ;
						$m = false ;
					}
				
				$o = "m" ;
				if ( $j == 1 ) {
					$o = "f" ;
				}
				
				if ( $nn > 9 ) {
					$nnn = intval( substr( $n , -2 , 1 ) );
				} else {
					$nnn = 0 ;
				}
				
				if ( $nnn == 1 ) {
					$tmp2.= $num1[ $k ]." " ;
					$l = 2 ;
					$m = false ;
				} else {
					if ( $nn > 9 ) {
						$tmp2.= $num2[ $nnn ]." " ;
					}
					
					if ( $nn > 0 && $k != 0 ) {
						$tmp2.= $num[ $o ][ $k ]." " ;
					}
				}
				
				if ( $tmp2 != "" || $j == 0 ) {
					$res = $tmp2.inForm( $groups[ $j ] , $l , $m )." ".$res ;
				}
			}
		}
		
		if ( $pos != false ) {
			$tmp = substr( $p , $pos + 1 );
		} else {
			$tmp = "00" ;
		}
		
		while ( strlen( $tmp ) < 2 ) {
			$tmp.= "0" ;
		}
		
		$nn = intval( $tmp );
		
		if ( $nn == 0 ) {
			$res.= "00 ".inForm( $kop , 2 , false );
		} else {
			$k = intval( substr( $tmp , -1 ) );
			if ( $k == 1 ) {
				$l = 1 ;
				$m = true ;
			} else
				if ( $k > 1 && $k < 5 ) {
					$l = 2 ;
					$m = true ;
				} else {
					$l = 2 ;
					$m = false ;
				}
			
			if ( $nn > 10 && $nn < 20 ) {
				$l = 2 ;
				$m = false ;
			}
			
			$res.= $tmp." ".inForm( $kop , $l , $m );
		}
		
		return $res ;
	}
	
	function iconvRecursion ( $from , $to , $src ) {
		if ( is_string( $src ) ) {
			return iconv( $from , $to , $src );
		} else
			if ( is_array( $src ) )	{
				$res = $src ;
				foreach( $res as &$i ) {
					$i = iconvRecursion( $from , $to , $i );
				}
				return $res ;
			} else {
				return $src ;
			}
	}

	function generateGUID() {
		$strong = false ;
		return vsprintf( '%s%s-%s-%s-%s-%s%s%s' , str_split( bin2hex( openssl_random_pseudo_bytes( 16 , $strong ) ) , 4 ) );
	}
