<?php
/*
	Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
	Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
	copyright (c) Пекшев Петр Александрович, 2008
*/

	define( 'ACCOUNT_TYPE_USER' , 'user' );
	define( 'ACCOUNT_TYPE_GROUP' , 'group' );

	class ObjectNotFoundException extends Exception {}

	$AbsRootDir = '' ;
	if ( isset( $_SERVER[ 'DOCUMENT_ROOT' ] ) ) {
		$AbsRootDir = realpath( $_SERVER[ 'DOCUMENT_ROOT' ] );
	}
	
	$RelScriptPath = '' ;
	$RelScriptDir = '' ;
	if ( isset( $_SERVER[ 'PHP_SELF' ] ) ) {
		$RelScriptPath = $_SERVER[ 'PHP_SELF' ];
		$RelScriptDir = dirname( $RelScriptPath );
	}
	
	$AbsScriptPath = '' ;
	$AbsScriptDir = '' ;
	if ( isset( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) {
		$AbsScriptPath = realpath( $_SERVER[ 'SCRIPT_FILENAME' ] );
		$AbsScriptDir = realpath( dirname( $AbsScriptPath ) );
	}
	
	$L = strlen( $AbsRootDir );
	$RelRootDir = substr( $AbsScriptDir , $L );
	$RRDE = explode( '/' , $RelRootDir );
	$RelRootDir = '' ;
	for ( $k = sizeof( $RRDE ) - 1 ; $k >= 0 ; $k-- ) {
		if ( $RRDE[ $k ] != '' ) {
			$RelRootDir = $RelRootDir.'../' ;
		}
	}
	
	error_reporting( E_ALL );
	setlocale( LC_ALL , 'ru_RU.cp1251' );
	
	function __autoload( $className ) {
		if ( !class_exists( $className , false ) ) {
			require_once( $className.'.class.php' );
		}
	}
	
	$Err = 0 ;
	
	require_once( $RelRootDir.'cores/core.value.php' );
	require_once( $RelRootDir.'cores/core.debug.php' );
	require_once( $RelRootDir.'cores/core.html.php' );
	
	require_once( $RelRootDir.'cores/core.config.php' );
	/**
	 * @var $dbConfig
	 * @var $dbConfigFull
	 */
	
	require_once( $RelRootDir.'cores/core.db.php' );
	/**
	 * @var $portalDB
	 */
	
	loadConfig();

	require_once( $RelRootDir.'cores/core.globals.php' );
	
	require_once( $RelRootDir.'cores/core.user.php' );
	require_once( $RelRootDir.'cores/core.auth.php' );
	
	require_once( 'marks.core.php' );
	

	define( 'VERSION_CHAR_ID' , '10' );
	define( 'ORG_INDEX_PATTERN' , '\w{3}' );
	define( 'ORG_INDEX_ANY' , '*' );
	define( 'ORG_INDEX_TEST' , 'Te0' );
	define( 'ORG_INDEX_TRAINING' , 'Tr0' );
	define( 'DOCTYPE_PATTERN' , '\w{4}' );
	define( 'OBJ_YEAR_PATTERN' , '20\d{2}' );
	define( 'OBJ_L_NUMBER_PATTERN' , '\w{6,8}' );
	define( 'OBJ_G_NUMBER_PATTERN' , OBJ_YEAR_PATTERN.OBJ_L_NUMBER_PATTERN );
	define( 'CHARID_STRUCTURE_PATTERN' , '(?<v>'.VERSION_CHAR_ID.')\.(?<o>'.ORG_INDEX_PATTERN.')\.(?<t>'.DOCTYPE_PATTERN.')\.(?<y>'.OBJ_YEAR_PATTERN.')(?<n>'.OBJ_L_NUMBER_PATTERN.')' );
	
	//12345678901234567890123456789012
	//10.360.0110.2022000001
	//11.0360.0110000000.2022000001-12

	$placesDescr = $portalDB->table( "places" , "id" );
	


	function breakLineByRule( $l , $r , $v = "<br>" ) {
		$rc = strlen( $r );
		$p = 1 ;
		$pattern = '^\s*' ;
		$replace = "" ;

		for( $i = 0 ; $i < $rc ; $i++ ) {
			/*if ( $i > 0 ) {
				$pattern.= '(\s+)' ;
			}*/

			if ( $r[ $i ] != "." ) {
				$rv = intval( $r[ $i ] );
				if ( $rv == 0 ) {
					$rv = 10 ;
				}

				for( $j = 0 ; $j < $rv * 2 - 1 ; $j++ ) {
					$replace.= '${'.( $p++ ).'}' ;
					if ( $j % 2 == 1 ) {
						$pattern.= '(\s+)' ;
					} else {
						$pattern.= '(\S+)' ;
					}
				}
			} else {
				$pattern.= '(\s+)' ;
				$replace.= $v ;
				$p++ ;
			}
		}

		//var_dump_html( $pattern );
		//var_dump_html( $replace );
		return preg_replace( '/'.$pattern.'/' , $replace , $l );
	}

	$BASE_JS_LIST = strexp( '{base,base.{NAMES,DOM,polyfill,calendarDlg,TDLG{CustomElements.{SlidingPanel,ChartPanel},AgentSelect,Comments,Components,FileUpload,InputTemplate,ProgressBar,SimpleMenu}}}.js' );
	$HTML_TEMPLATES_LIST = strexp( '{SlidingPanel,ChartPanel}' );

	
	function PrepExpGenus( $expGenus ) {
		$t = $expGenus ;
		$t = str_replace( "," , " " , $t );
		$t = str_replace( "/" , " " , $t );
		$t = str_replace( ";" , " " , $t );
		$t = trim( $t );
		$repco = 1 ;
		while ( $repco > 0 ) {
			$t = str_replace( "  " , " " , $t , $repco );
		}
		//$t = str_replace( " " , " " , $t );
		return $t ;
	}

	class baseExt {
		public function getClassName() {
			return get_class( $this );
		}

		public function __get( $name ) {
			$gmn = "get".ucfirst( $name );
			if ( method_exists( $this , $gmn ) ) {
				return $this->$gmn();
			} else
			if ( property_exists( $this , $name ) ) {
				$bt = debug_backtrace();
				trigger_error(
					sprintf( "Cannot access protected property %s::#%s in <b>%s</b> on line <b>%s</b>" , $this->getClassName() , $name , $bt[ 0 ][ "file" ] , $bt[ 0 ][ "line" ] , E_USER_ERROR )
				);
			} else {
				$bt = debug_backtrace();
				trigger_error(
					sprintf( "<b>Notice</b>: Undefined property: %s::#%s in <b>%s</b> on line <b>%s</b>" , $this->getClassName() , $name , $bt[ 0 ][ "file" ] , $bt[ 0 ][ "line" ] , E_USER_ERROR )
				);
			}

			return NULL ;
		}

		public function __set( $name , $value ) {
			$smn = "set".ucfirst( $name );
			if ( method_exists( $this , $smn ) ) {
				$this->$smn( $value );
			} else
			if ( property_exists( $this , $name ) ) {
				$bt = debug_backtrace();
				trigger_error(
					sprintf( "Cannot access protected property %s::#%s in <b>%s</b> on line <b>%s</b>" , $this->getClassName() , $name , $bt[ 0 ][ "file" ] , $bt[ 0 ][ "line" ] , E_USER_ERROR )
				);
			} else {
				$bt = debug_backtrace();
				trigger_error(
					sprintf( "<b>Notice</b>: Undefined property: %s::#%s in <b>%s</b> on line <b>%s</b>" , $this->getClassName() , $name , $bt[ 0 ][ "file" ] , $bt[ 0 ][ "line" ] , E_USER_ERROR )
				);
			}

			return NULL ;
		}
	}

	/*function num2word_( $num , $form = 1 , $singular = true , $gender = "m" , $ordinal = false ) {
		$groups = array ( "тысяч{а|и|е|у|ей|е^и||ам|и|ами|ах}" , "миллион{|а|у||ом|е^ы|ов|ам|ы|ами|ах}" );
		$name = array (
			"m" => array(
				0 => "ноль" ,
				1 => "од{ин|ного|ному|ин|ним|ном^ни|них|ним|них|ними|них}" ,
				2 => "дв{а|ух|ум|а|умя|ух^ое|оих|оим|оих|двоими|оих}" ,
				3 => "три" ,
				4 => "четыре" ,
				5 => "пять" ,
				6 => "шесть" ,
				7 => "семь" ,
				8 => "восемь" ,
				9 => "девять"
			),
			"f" => array(
				0 => "ноль" ,
				1 => "одна" ,
				2 => "две" ,
				3 => "три" ,
				4 => "четыре" ,
				5 => "пять" ,
				6 => "шесть" ,
				7 => "семь" ,
				8 => "восемь" ,
				9 => "девять"
			),
			"n" => array(
				0 => "ноль" ,
				1 => "одно" ,
				2 => "два" ,
				3 => "три" ,
				4 => "четыре" ,
				5 => "пять" ,
				6 => "шесть" ,
				7 => "семь" ,
				8 => "восемь" ,
				9 => "девять"
			)
		);
		$name1 = array( "десять" , "одиннадцать" , "двенадцать" , "тринадцать" , "четырнадцать" , "пятнадцать" , "шестнадцать" , "семнадцать" , "восемнадцать" , "девятнадцать" );
		$name2 = array( "ноль" , "десять" , "двадцать" , "тридцать" , "сорок" , "пятьдесят" , "шестьдесят" , "семьдесят" , "восемьдесят" , "девяносто" );
		$name3 = array( "ноль" , "сто" , "двести" , "триста" , "четыреста" , "пятьсот" , "шестьсот" , "семьсот" , "восемьсот" , "девятьсот" );

		$tmp = $num ;

		$res = "" ;
		if ( intval( $tmp ) == 0 ) {
			$res = inForm( $name[ $gender ][ 0 ] , $form , $singular );
		} else {
			for ( $j = 0 ; strlen( $tmp ) > 0 ; $j++ ) {
				$n = substr( $tmp, -min( 3 , strlen( $tmp ) ) );
				$nn = intval( $n );

				$tmp = substr( $tmp , 0 , -3 );
				$tmp2 = "" ;
				if ( $nn > 99 ) {
					$nnn = intval( substr( $n , -3 , 1 ) );
					if ( $nnn > 0 ) {
						$tmp2 = $name3[ $nnn ]." " ;
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
					$tmp2.= $name1[ $k ]." " ;
					$l = 2 ;
					$m = false ;
				} else {
					if ( $nn > 9 ) {
						$tmp2.= $name2[ $nnn ]." " ;
					}

					if ( $nn > 0 && $k != 0 ) {
						$tmp2.= $name[ $o ][ $k ]." " ;
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

			$o = "m" ;
			if ( $j == 1 ) {
				$o = "f" ;
			}

			if ( $nn > 10 && $nn < 20 ) {
				$l = 2 ;
				$m = false ;
			}

			$res.= $tmp." ".inForm( $kop , $l , $m );
		}

		return $res ;
	}*/

	function toCDATA( $s ) {
		return "<![CDATA[".str_replace( "]]>" , "]]]]><![CDATA[>" , $s )."]]>" ;
	}

	function groupArray( &$src , $key ) {
		$result = array();

		foreach( $src as &$r ) {
			$kv = $r[ $key ];
			if ( !isset( $result[ $kv ] ) ) {
				$result[ $kv ] = array();
			}
			$result[ $kv ][]= $r ;
		} unset( $r );

		return result ;
	}

	/*
	 * a : array( "v" => "def" )
	 * b : abc${v}ghi
	 * result : abcdefghi
	 */

	function substitute( $a , $b ) {
		foreach ( $a  as $k => $v ) {
			$b = str_replace( '${'.$k.'}' , $v , $b );
		}
		return $b ;
	}

	function treeConvertEncoding( &$t , $ics = "cp1251" , $ocs = "utf8" ) {
		if ( is_string( $t ) ) {
			$t = iconv( $ics , $ocs , $t );
		} else
		if ( is_array( $t ) ) {
			foreach ( $t as &$ct ) {
				treeConvertEncoding( $ct , $ics , $ocs );
			} unset( $ct );
		}
	}

	function updateInputTemplates( $key , $newTemplates ) {
		global $portalDB , $UserID ;

		$o = $portalDB->row( "select * from `options` where ( `op_name` = ? ) and ( `user_id` = ? )" , "si" , $key , $UserID );
		if ( $o !== false ) {
			$portalDB->noResult( "update `options` set `op_value` = ? where ( `id` = ? )" , "si" , $newTemplates , $o[ "id" ] );
		} else {
			$portalDB->noResult( "insert into `options` ( `op_name` , `op_value` , `user_id` ) values( ? , ? , ? )" , "ssi" , $key , $newTemplates , $UserID );
		}
	}
	
	require_once $RelRootDir."ext-lib/XMPPHP/Exception.php" ;
	require_once $RelRootDir."ext-lib/XMPPHP/Log.php" ;
	require_once $RelRootDir."ext-lib/XMPPHP/Roster.php" ;
	require_once $RelRootDir."ext-lib/XMPPHP/XMLObj.php" ;
	require_once $RelRootDir."ext-lib/XMPPHP/XMLStream.php" ;
	require_once $RelRootDir."ext-lib/XMPPHP/XMPP.php" ;

	function sendJabberMessagesPackInit() {
		global $dbConfig ;
		if ( isset( $dbConfig[ "engine.jabber.enabled" ] ) && $dbConfig[ "engine.jabber.enabled" ] == 1 ) {
			$jabberServer = "v-jabber.vrcse.local" ;
			$jabberServerPort = 5222 ;
			$jabberBotLogin = "ut-portal-bot" ;
			$jabberBotPassword = "u-t.123" ;
		} else {
			return ;
		}

		$XMPP = new BirknerAlex\XMPPHP\XMPP( $jabberServer , $jabberServerPort , $jabberBotLogin , $jabberBotPassword , "PHP" );
		$XMPP->useEncryption( true );
		$XMPP->connect();
		$XMPP->processUntil( "session_start" );
		$XMPP->presence();
		return $XMPP ;
	}
	function sendJabberMessagesPackSend( $pack , $to , $msg ) {
		global $dbConfig ;
		if ( isset( $dbConfig[ "engine.jabber.enabled" ] ) && $dbConfig[ "engine.jabber.enabled" ] == 1 ) {
			$jabberServer = "v-jabber.vrcse.local" ;
			$jabberServerPort = 5222 ;
			$jabberBotLogin = "ut-portal-bot" ;
			$jabberBotPassword = "u-t.123" ;
		} else {
			return ;
		}

		$pack->message( $to."@".$jabberServer , iconv( "cp1251" , "utf8" , $msg ) );
	}
	function sendJabberMessagesPackFree( $pack ) {
		global $dbConfig ;
		if ( isset( $dbConfig[ "engine.jabber.enabled" ] ) && $dbConfig[ "engine.jabber.enabled" ] == 1 ) {
			$jabberServer = "v-jabber.vrcse.local" ;
			$jabberServerPort = 5222 ;
			$jabberBotLogin = "ut-portal-bot" ;
			$jabberBotPassword = "u-t.123" ;
		} else {
			return ;
		}

		$pack->disconnect();
	}

	function sendJabberMessage( $to , $msg ) {
		global $dbConfig ;
		if ( isset( $dbConfig[ "engine.jabber.enabled" ] ) && $dbConfig[ "engine.jabber.enabled" ] == 1 ) {
			$jabberServer = "v-jabber.vrcse.local" ;
			$jabberServerPort = 5222 ;
			$jabberBotLogin = "ut-portal-bot" ;
			$jabberBotPassword = "u-t.123" ;
		} else {
			return ;
		}

		$msg = iconv( "cp1251" , "utf8" , $msg );
		$XMPP = new BirknerAlex\XMPPHP\XMPP( $jabberServer , $jabberServerPort , $jabberBotLogin , $jabberBotPassword , "PHP" );
		$XMPP->useEncryption( true );
		$XMPP->connect();
		$XMPP->processUntil( "session_start" );
		$XMPP->presence();
		if ( is_array( $to ) ) {
			foreach ( $to as $cto ) {
				$XMPP->message( $cto."@".$jabberServer , $msg );
			}
		} else {
			$XMPP->message( $to."@".$jabberServer , $msg );
		}
		$XMPP->disconnect();
	}


	function getIDList( $src , $uniq = true ) {
		$n = preg_match( '/^(?:\d+)(?:,(?:\d+))*$/D' , $src );
		if ( $n == 1 ) {
			$n = explode( "," , $src );
			foreach ( $n as &$v ) {
				$v = intval( trim( $v ) );
			} unset( $v );
			if ( $uniq ) {
				$n = array_unique( $n );
			}
			return $n ;
		} else {
			return false ;
		}
	}

	function getCharIDList( $src , $uniq = true , $docType = false , $region = false ) {
		global $UserOrgIndex ;

		if ( $docType === false ) {
			$pDocType = DOCTYPE_PATTERN ;
		} else {
			$pDocType = ''.$docType ;
		}
		if ( $region === false ) {
			$pReg = ''.$UserOrgIndex ;
		} else
		if ( $region == "*" ) {
			$pReg = ORG_INDEX_PATTERN ;
		} else {
			$pReg = ''.$region ;
		}

		$pat = VERSION_CHAR_ID.'\.'.$pReg.'\.'.$pDocType.'\.'.OBJ_G_NUMBER_PATTERN ;
		$n = preg_match( '/^\s*(?:'.$pat.')\s*(?:,\s*(?:'.$pat.')\s*)*$/D' , $src );
		if ( $n == 1 ) {
			$n = explode( "," , $src );
			foreach ( $n as &$v ) {
				$v = trim( $v );
			} unset( $v );
			if ( $uniq ) {
				$n = array_unique( $n );
			}
			return $n ;
		} else {
			return false ;
		}
	}

	function getCharID( $src , $docType = false , $region = false ) {
		global $UserOrgIndex ;
		if ( $docType === false ) {
			$pDocType = DOCTYPE_PATTERN ;
		} else {
			$pDocType = ''.$docType ;
		}
		if ( $region === false ) {
			$pReg = ''.$UserOrgIndex ;
		} else
		if ( $region == "*" ) {
			$pReg = ORG_INDEX_PATTERN ;
		} else {
			$pReg = ''.$region ;
		}

		$pat = VERSION_CHAR_ID.'\.'.$pReg.'\.'.$pDocType.'\.'.OBJ_G_NUMBER_PATTERN ;
		$n = preg_match( '/^'.$pat.'$/D' , $src );
		if ( $n == 1 ) {
			return $src ;
		} else {
			return false ;
		}
	}

	function getCharIDStructure( $src ) {
		$n = preg_match( '/^'.CHARID_STRUCTURE_PATTERN.'$/D' , $src , $m );
		if ( $n == 1 ) {
			unset( $m[ 0 ] );
			unset( $m[ 1 ] );
			unset( $m[ 2 ] );
			unset( $m[ 3 ] );
			unset( $m[ 4 ] );
			unset( $m[ 5 ] );
			//$m[ "n" ] = preg_replace( '/^0+(?!\.|$)/' , '' , $m[ "n" ] );
			return $m ;
		} else {
			return false ;
		}
	}

	/**
	 * @param array $s <p>массив вида array(<br/>
	 * 		"v" => ...  версия ,<br/>
	 * 		"o" => ...  код организации ,<br/>
	 * 		"t" => ...  тип документа ,<br/>
	 * 		"y" => ...  год ,<br/>
	 * 		"n" => ...  номер по порядку в течение года<br/>
	 * )</p>
	 * @return string <p>Возвращает строку вида <b>10.360.0110.2023000001</b></p>
	 */
	function mkCharID( $s ) {
		return $s[ "v" ].".".$s[ "o" ].".".$s[ "t" ].".".$s[ "y" ].$s[ "n" ];
	}

	function colorToIntArray( $s ) {
		$m = array();
		$n = preg_match( '/^\#(?<r>[0-9a-f]{2})(?<g>[0-9a-f]{2})(?<b>[0-9a-f]{2})$/' , $s , $m );
		if ( $n != 1 ) {
			$m = array();
			$n = preg_match( '/^\#(?<r>[0-9a-f])(?<g>[0-9a-f])(?<b>[0-9a-f])$/' , $s , $m );
			if ( $n == 1 ) {
				$m[ "r" ].= $m[ "r" ];
				$m[ "g" ].= $m[ "g" ];
				$m[ "b" ].= $m[ "b" ];
			}
		}

		if ( $n == 1 ) {
			$m[ "r" ] = intval( $m[ "r" ] , 16 );
			$m[ "g" ] = intval( $m[ "g" ] , 16 );
			$m[ "b" ] = intval( $m[ "b" ] , 16 );
			return array( "r" => $m[ "r" ] , "g" => $m[ "r" ] , "b"=> $m[ "b" ] );
		} else {
			return false ;
		}
	}

	$eventTypes = $portalDB->table( "event-types" , "type" );

	function event( $type , $param1 = null , $param2 = null ) {
		global $portalDB , $eventTypes ;

		if ( !isset( $eventTypes[ $type ] ) ) {
			event(
				"dbg.error.eventTypeError" ,
				$type ,
				json_encode(
					array(
						"param1" => iconv( "cp1251" , "utf8" , $param1 ),
						"param2" => iconv( "cp1251" , "utf8" , $param2 )
					)
				)
			);
			return false ;
		}

		$extID = $eventTypes[ $type ][ "id" ];
		$env = array(
			"request" => $_REQUEST ,
			"server" => $_SERVER
		);
		$env = json_encode( $env );
		$portalDB->noResult( "insert into `events` ( `ext_id` , `time` , `param1` , `param2` , `env` ) values ( ? , ? , ? , ? , ? )" , "iisss" , $extID , time() , $param1 , $param2 , $env );
		return true ;
	}

	function getDocs( $extID , $extType ) {
		global $portalDB ;
		if ( is_int( $extID ) || is_string( $extID ) ) {
			$extID = array( $extID );
		}

		foreach( $extID as &$v ) {
			$v = "".$v ;
		} unset( $v );

		$res = $portalDB->query( "select * from `documents` where ( `ext_type` = ? ) and ( `ext_id` in ( ?* ) ) order by `time` asc" , false , "s*s" , $extType , $extID );
		return $res ;
	}


	function toFlat( $a , $cn , &$r , $ex = array() ) {
		foreach ( $a as $n => $v ) {
			$tn = ( $cn == "" ? $n : $cn.".".$n );
			if ( !in_array( $tn , $ex ) && is_array( $v ) ) {
				toFlat( $v , $tn , $r );
			} else {
				$r[ $tn ] = $v ;
			}
		}
	}

	function error_log_ml( $s , $clear = 8 ) {
		$sl = explode( "\n" , $s );
		if ( $clear >= 0 ) {
			$mss = 0 ;
			foreach( $sl as $cs ) {
				$mss = max( $mss , strlen( $cs ) );
			}
			$mss+= $clear ;
			foreach( $sl as &$cs ) {
				$cs = str_pad( $cs , $mss , " " , STR_PAD_RIGHT );
			} unset( $cs );
		}

		foreach( $sl as $cs ) {
			error_log( $cs );
		}
	}


	$base32_alphabet = array_merge( range( 'A' , 'Z' ) , range( 2 , 7 ) , array( '=' ) );
	function base32encode( $s ) {
		global $base32_alphabet ;

		$ba = unpack( 'C*' , $s );
		foreach( $ba as &$cb ) {
			$cb = str_pad( decbin( $cb ) , 8 , 0 , STR_PAD_LEFT );
		} unset( $cb );

		$ba = implode( '' , $ba );

		$belm = strlen( $ba ) % 5 ;
		if ( $belm > 0 ) {
			$ba.= str_repeat( 0 , 5 - $belm );
		}

		$beflm = strlen( $ba ) % 40 ;
		if ( $beflm > 0 ) {
			$pad = str_repeat( '=' , ( 40 - $beflm ) / 5 );
		} else {
			$pad = '' ;
		}

		$res = '' ;
		while ( strlen( $ba ) > 0 ) {
			$cc = bindec( substr( $ba , 0 , 5 ) );
			$res.= $base32_alphabet[ $cc ];
			$ba = substr( $ba , 5 );
		}
		return $res.$pad ;
	}

