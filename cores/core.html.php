<?php
	
	function listToOptions( &$data , $default = false ) {
		$res = "" ;
		
		if ( func_num_args() == 1 || $default == false ) {
			foreach ( $data  as $k => &$d ) {
				$res.= "<option value=\"".$k."\">".$d."</option>" ;
			} unset( $d );
		} else {
			foreach ( $data  as $k => &$d ) {
				$res.= "<option value=\"".$k."\"".( $k == $default ? " selected" : "" ).">".$d."</option>" ;
			} unset( $d );
		}
		
		return $res ;
	}
	
	function makeSimpleSelectTagOptions( &$data , $val , $id = "id" , $def = false , $func = false ) {
		$res = "" ;
		
		if ( $func !== false ) {
			if ( $def !== false ) {
				foreach ( $data  as &$d ) {
					if ( $d[ $id ] == $def ) {
						$res.= "<option value=\"".htmlentities( $d[ $id ] )."\" selected>".htmlentities( $func( $d[ $val ] ) )."</option>" ;
					} else {
						$res.= "<option value=\"".htmlentities( $d[ $id ] )."\">".htmlentities( $func( $d[ $val ] ) )."</option>" ;
					}
				} unset( $d );
			} else {
				foreach ( $data  as &$d ) {
					$res.= "<option value=\"".htmlentities( $d[ $id ] )."\">".htmlentities( $func( $d[ $val ] ) )."</option>" ;
				} unset( $d );
			}
		} else {
			if ( $def !== false ) {
				foreach ( $data  as &$d ) {
					if ( $d[ $id ] == $def ) {
						$res.= "<option value=\"".htmlentities( $d[ $id ] )."\" selected>".htmlentities( $d[ $val ] )."</option>" ;
					} else {
						$res.= "<option value=\"".htmlentities( $d[ $id ] )."\">".htmlentities( $d[ $val ] )."</option>" ;
					}
				} unset( $d );
			} else {
				foreach ( $data  as &$d ) {
					$res.= "<option value=\"".htmlentities( $d[ $id ] )."\">".htmlentities( $d[ $val ] )."</option>" ;
				} unset( $d );
			}
		}
		
		return $res ;
	}

	function makeSimpleSelectTagOptions1D( &$data , $def = null , $kFunc = null , $vFunc = null ) {
		$res = '' ;

		if ( !is_callable( $kFunc ) ) {
			$kFunc = function( $k ) {
				return $k ;
			};
		}

		if ( !is_callable( $vFunc ) ) {
			$vFunc = function( $k , $v ) {
				return $v ;
			};
		}

		foreach ( $data  as $k => &$v ) {
			$res.= '<option value="'.htmlentities( $kFunc( $k ) ).'" '.( $k == $def ? ' selected' : '' ).'>'.htmlentities( $vFunc( $k , $v ) ).'</option>' ;
		} unset( $v );

		return $res ;
	}

	function makeSimpleTable_init_filter() {
		return array(
			// price
			"p" => function( &$r , $c , $v ) {
				return money_format( "%!i" , $v ) ;
			} ,
			// date 'd-m-Y'
			"d" => function( &$r , $c , $v ) {
				if ( !is_null( $v ) && $v != 0 ) {
					return date( "d-m-Y" , $v );
				} else {
					return "" ;
				}
			} ,
			// date & time 'd-m-Y H:i'
			"dt" => function( &$r , $c , $v ) {
				if ( !is_null( $v ) && $v != 0 ) {
					return date( "d-m-Y H:i" , $v );
				} else {
					return "" ;
				}
			} ,
			// date 'd-m-Y' from string
			"ds" => function( &$r , $c , $v ) {
				if ( !is_null( $v ) ) {
					return date( "d-m-Y" , strtotime( $v ) );
				} else {
					return "" ;
				}
			} ,
			// date & time 'd-m-Y H:i' from string
			"dts" => function( &$r , $c , $v ) {
				if ( !is_null( $v ) ) {
					return date( "d-m-Y H:i" , strtotime( $v ) );
				} else {
					return "" ;
				}
			} ,
			// file size
			"fs" => function( &$r , $c , $v ) {
				$s = $v ;
				$sa = array( "Б" , "КБ" , "МБ" , "ГБ" , "ТБ" , "ПБ" );
				$si = 0 ;
				$f = 1 ;
				$ts = strlen( "".floor( $s / $f ) );
				while ( $ts > 3 ) {
					$f*= 1024 ;
					$si++ ;
					$ts = strlen( "".floor( $s / $f ) );
				}
				
				if ( $ts < 3 ) {
					$ts = round( $s / $f , 3 - $ts );
				} else {
					$ts = round( $s / $f , 0 );
				}
				
				return $ts." ".$sa[ $si ];
			} ,
			// raw data
			"raw" => function( &$r , $c , $v ) {
				return $v ;
			}
		);
	}
	
	function makeSimpleTable( $t , $h = '' , $c = '' , &$d = null , $s = false , $f = false ) {
		if ( func_num_args() == 1 ) {
			$res = '' ;
			$cols = array();
			$data = func_get_arg( 0 );
			foreach ( $data as &$row ) {
				if ( !is_array( $row ) ) {
				} else {
					$cc = array_keys( $row );
					$cols = array_merge( $cols , array_combine( $cc , $cc ) );
				}
			} unset( $row );
			
			$res.= '<table class="simplest-tab" align="center">' ;
			$res.= '<tr><td></td>' ;
			foreach ( $cols as $col ) {
				$res.= '<td class="simplest-tab-h">'.$col.'</td>' ;
			}
			$res.= '</tr>' ;
			
			//echo $res ;
			
			foreach ( $data as $index => &$row ) {
				$res.= '<tr><td class="simplest-tab-index">'.$index.'</td>' ;
				if ( !is_array( $row ) ) {
					$res.= '<td colspan="'.count( $cols ).'" class="simplest-tab-normal-str">'.$row.'</td>' ;
				} else {
					foreach ( $cols as $col ) {
						if ( !array_key_exists( $col , $row ) ) {
							$res.= '<td class="simplest-tab-empty"></td>' ;
						} else {
							$nn = $row[ $col ];
							if ( is_array( $nn ) ) {
								if ( count( $nn ) > 0 ) {
									$res.= '<td class="simplest-tab-array"><a class="simplest-tab-array-lnk">'.count( $nn ).'<div class="simplest-tab-array-data">'.makeSimpleTable( $nn ).'</div></a></td>' ;
								} else {
									$res.= '<td class="simplest-tab-array">'.count( $nn ).'</td>' ;
								}
							} else
								if ( is_null( $nn ) ) {
									$res.= '<td class="simplest-tab-NULL">NULL</td>' ;
								} else
									if ( is_bool( $nn ) ) {
										$res.= '<td class="simplest-tab-normal-bool-'.( $row[ $col ] ? 'true' : 'false' ).'">'.( $row[ $col ] ? '+' : '-' ).'</td>' ;
									} else
										if ( is_numeric( $nn ) ) {
											$res.= '<td class="simplest-tab-normal-num">'.$row[ $col ].'</td>' ;
										} else {
											$res.= '<td class="simplest-tab-normal-str">'.$row[ $col ].'</td>' ;
										}
						}
					}
				}
				$res.= '</tr>' ;
			} unset( $row );
			$res.= '</table>' ;
			
			return $res ;
		}
		
		$makeSimpleTable__default_styles = array (
			't'     => 'def-simple-tab' ,
			'h1r'   => 'h1r'   ,
			'h2r'   => 'h2r'   ,
			'h3r'   => 'h3r'   ,
			'dr'    => 'dr-l'  ,
			'tn'    => 'tn'    , // number
			'tN'    => 'tN'    , // long number
			'tss'   => 'tss'   , // single-row string 128px width
			'tsm'   => 'tsm'   , // single-row string 256px width
			'tsl'   => 'tsl'   , // single-row string 384px width
			'ts16'  => 'ts16'  ,    
			'ts24'  => 'ts24'  ,    
			'ts32'  => 'ts32'  ,    
			'ts48'  => 'ts48'  ,    
			'ts64'  => 'ts64'  , // single-row string 64px width
			'ts96'  => 'ts96'  , // single-row string 96px width
			'ts128' => 'ts128' , // single-row string 128px width
			'ts160' => 'ts160' , // single-row string 160px width
			'ts192' => 'ts192' , // single-row string 192px width
			'ts256' => 'ts256' , // single-row string 256px width
			'ts320' => 'ts320' , // single-row string 320px width
			'ts384' => 'ts384' , // single-row string 384px width
			'ts448' => 'ts448' , // single-row string 448px width
			'ts512' => 'ts512' , // single-row string 512px width
			'tsf'   => 'tsf'   , // single-row string free width
			'tSs'   => 'tSs'   , // multi-row string 128px width
			'tSm'   => 'tSm'   , // multi-row string 256px width
			'tSl'   => 'tSl'   , // multi-row string 384px width
			'tS16'  => 'tS16'  ,    
			'tS24'  => 'tS24'  ,    
			'tS32'  => 'tS32'  ,    
			'tS48'  => 'tS48'  ,    
			'tS64'  => 'tS64'  , // multi-row string 64px width
			'tS96'  => 'tS96'  , // multi-row string 96px width
			'tS128' => 'tS128' , // multi-row string 128px width
			'tS160' => 'tS160' , // multi-row string 160px width
			'tS192' => 'tS192' , // multi-row string 192px width
			'tS256' => 'tS256' , // multi-row string 256px width
			'tS320' => 'tS320' , // multi-row string 320px width
			'tS384' => 'tS384' , // multi-row string 384px width
			'tS448' => 'tS448' , // multi-row string 448px width
			'tS512' => 'tS512' , // multi-row string 512px width
			'tSf'   => 'tSf'   , // multi-row string free width
			'tp'    => 'tp'    , // price
			'td'    => 'td'    , // date
			'tdt'   => 'tdt'   , // date & time
			'tds'   => 'tds'   , // date from string
			'tdts'  => 'tdts'  , // date & time from string
			'tfs'   => 'tfs'   , //
			'csnp'  => 'cell-' ,
			'tcb'   => 'tcb' // checkbox
		);
		
		if ( is_string( $t ) ) {
			$t = json_decode( $t , true );
		}
		
		if ( is_string( $h ) ) {
			$h = json_decode( $h , true );
		}
		
		if ( is_string( $c ) ) {
			$c = json_decode( cvt( $c ) , true );
			foreach ( $c as &$cc ) {
				if ( isset( $cc[ 'h' ] ) ) {
					foreach ( $cc[ 'h' ] as &$cch ) {
						$cch[ 'd' ] = isset( $cch[ 'd' ] ) ? rcvt( $cch[ 'd' ] ) : '' ;
					} unset( $cch );
				} else {
					$cc[ 'h' ] = array( 'd' => '' , 'r' => count( $h ) );
				}
			} unset( $cc );
		}
		
		if ( $s !== false ) {
			$s = array_merge( $makeSimpleTable__default_styles , $s );
		} else {
			$s = $makeSimpleTable__default_styles ;
		}
		
		if ( $f === false  ) {
			$f = makeSimpleTable_init_filter();
		}
		
		foreach ( $c as &$cc ) {
			$fn = isset( $cc[ 'f' ] ) ? $cc[ 'f' ] : ( isset( $cc[ 't' ] ) ? ( isset( $t[ 'fp' ] ) ? $t[ 'fp' ] : '' ).$cc[ 't' ] : false );
			$cc[ 'f' ] = $fn === false || !isset( $f[ $fn ] ) ? $f[ 'raw' ] : $f[ $fn ];
		} unset( $cc );
		
		if ( isset( $t[ 'no-table-open-tag' ] ) && $t[ 'no-table-open-tag' ] == 1 ) {
			$r = '' ;
		} else {
			$r = '<table'.( isset( $t[ 'id' ] ) ? ' id="'.$t[ 'id' ].'"' : '' ).' class="'.$s[ 't' ].'" align="center">' ;
		}
		
		$rowIndex = 1 ;
		foreach ( $h as $hr ) {
			$rt = isset( $hr[ 't' ] ) ? $hr[ 't' ] : $rowIndex ;
			$rs = $s[ 'h'.$rt.'r' ];
			$r.= '<tr'.( isset( $hr[ 'id' ] ) ? ' id="'.$hr[ 'id' ].'"' : '' ).' class="'.$rs.'"'.( isset( $hr[ 'a' ] ) ? ' onclick="'.$hr[ 'a' ].'()"' : '' ).'>' ;
			$skipCell = 0 ;
			foreach ( $c as $cc ) {
				if ( $skipCell-- > 0 ) {
					continue ;
				}
				if ( !isset( $cc[ 'h' ][ $rowIndex - 1 ] ) ) {
					continue ;
				} else {
					$cch = $cc[ 'h' ][ $rowIndex - 1 ];
				}
				
				if ( isset( $cch[ 'c' ] ) && $cch[ 'c' ] > 1 ) {
					$cchc = ' colspan="'.$cch[ 'c' ].'"' ;
					$skipCell = $cch[ 'c' ] - 1 ;
				} else {
					$cchc = '' ;
				}
				
				if ( isset( $cch[ 'r' ] ) && $cch[ 'r' ] > 1 ) {
					$cchr = min( count( $h ) - $rowIndex + 1 , $cch[ 'r' ] );
					if ( $cchr > 1 ) {
						$cchr = ' rowspan="'.$cch[ 'r' ].'"' ;
					} else {
						$cchr = '' ;
					}
				} else {
					$cchr = '' ;
				}
				
				$ccs = ( isset( $cch[ 's' ] ) ? $cch[ 's' ].' ' : '' ).( $rs.( isset( $cc[ 's' ] ) ? ' '.$cc[ 's' ] : ( isset( $cc[ 't' ] ) ? ' '.$s[ 't'.$cc[ 't' ] ] : ( isset( $cc[ 'n' ] ) ? ' '.$s[ 'csnp' ].$cc[ 'n' ] : '' ) ) ) );
				if ( isset( $cch[ 'skip' ] ) && $cch[ 'skip' ] ) {
				
				} else {
					$r.= '<td'.( isset( $cc[ 'id' ] ) ? ' id="hr'.$rowIndex.'c-'.$cc[ 'id' ].'"' : '' ).' class="'.$ccs.'"'.$cchc.$cchr.'>' ;
					$r.= isset( $cch[ 'img' ] ) ? '<img src="'.$cch[ 'img' ].'" border="0" alt="'.( isset( $cch[ 'd' ] ) ? htmlspecialchars( $cch[ 'd' ] ) : '' ).'">' : htmlspecialchars( $cch[ 'd' ] );
					$r.= '</td>' ;
				}
			}
			$r.= '</tr>' ;
			$rowIndex++ ;
		}
		
		foreach ( $c as &$cc ) {
			$cc[ 's' ] = $s[ 'dr' ].( isset( $cc[ 's' ] ) ? ' '.$cc[ 's' ] : ( isset( $cc[ 't' ] ) ? ' '.$s[ 't'.$cc[ 't' ] ] : ( isset( $cc[ 'n' ] ) ? ' '.$s[ 'csnp' ].$cc[ 'n' ] : '' ) ) ).( isset( $cc[ '+s' ] ) ? ' '.$cc[ '+s' ] : '' );
		} unset( $cc );
		
		$rowIndex = 1 ;
		if ( isset( $t[ 'drid' ] ) ) {
			if ( !isset( $t[ 'drid-pref' ] ) ) {
				if ( isset( $t[ 'id' ] ) ) {
					$t[ 'drid-pref' ] = $t[ 'id' ];
				} else {
					$t[ 'drid-pref' ] = '' ;
				}
			}
		}
		foreach ( $d as $drk => &$drv ) {
			$rs = $s[ 'dr' ];
			if ( isset( $t[ 'dresf' ] ) ) {
				$ers = $t[ 'dresf' ]( $drv );
				if ( $ers != '' ) {
					$rs.= ' '.$ers ;
				}
			}
			$r.= '<tr'.( isset( $t[ 'drid' ] ) ? ' id="'.$t[ 'drid-pref' ].'dr'.$drk.'"' : '' ).' class="'.$rs.'"'.( isset( $t[ 'dra' ] ) ? ' onclick="'.$t[ 'dra' ].'( event , '.$drk.' )"' : '' ).'>' ;
			foreach ( $c as &$cc ) {
				$ro = array();
				if ( isset( $cc[ 'n' ] ) ) {
					$tr = $cc[ 'f' ]( $drv , $cc[ 'n' ] , $drv[ $cc[ 'n' ] ] , $ro );
				} else {
					$tr = $cc[ 'f' ]( $drv , '' , '' , $ro );
				}
				
				if ( isset( $ro[ 'skip' ] ) && $ro[ 'skip' ] ) {
				} else {
					$r.= '<td'.( isset( $cc[ 'id' ] ) ? ' id="'.$t[ 'drid-pref' ].'dr'.$drk.'c-'.$cc[ 'id' ].'"' : '' ).' class="'.$cc[ 's' ].'"' ;
					if ( isset( $ro[ 'colspan' ] ) ) {
						$r.= ' colspan="'.$ro[ 'colspan' ].'"' ;
					}
					if ( isset( $ro[ 'rowspan' ] ) ) {
						$r.= ' rowspan="'.$ro[ 'rowspan' ].'"' ;
					}
					$r.= '>'.$tr.'</td>' ;
				}
			} unset( $cc );
			$r.= '</tr>'."\r\n" ;
			$rowIndex++ ;
		} unset( $drv );
		
		if ( isset( $t[ 'no-table-close-tag' ] ) && $t[ 'no-table-close-tag' ] == 1 ) {
		} else {
			$r.= '</table>';
		}
		return $r ;
	}
	
	function Redirect( $SubAddr ) {
		header( 'Location: '.$SubAddr );
		exit();
	}
	
	function ErrorMessage( $errNo ) {
		$errors = array (
			400 => array( 0 , 'Bad Request' ),
			401 => array( 0 , 'Unauthorized' ),
			402 => array( 1 , 'Payment Required' ),
			403 => array( 0 , 'Forbidden' ),
			404 => array( 0 , 'Not Found' ),
			405 => array( 1 , 'Method Not Allowed' ),
			406 => array( 1 , 'Not Acceptable' ),
			407 => array( 1 , 'Proxy Authentication Required' ),
			408 => array( 1 , 'Request Timeout' ),
			409 => array( 1 , 'Conflict' ),
			410 => array( 1 , 'Gone' ),
			411 => array( 1 , 'Length Required' ),
			412 => array( 1 , 'Precondition Failed' ),
			413 => array( 1 , 'Request Entity Too Large' ),
			414 => array( 1 , 'Request-URI Too Long' ),
			415 => array( 1 , 'Unsupported Media Type' ),
			416 => array( 1 , 'Requested Range Not Satisfiable' ),
			417 => array( 1 , 'Expectation Failed' ),
			418 => array( 0 , 'I`m a teapot' )
		);
		header( 'HTTP/1'.$errors[ $errNo ][ 0 ].' '.$errNo.' '.$errors[ $errNo ][ 1 ] );
		exit ;
	}
	
	function compressHTML( $s ) {
		$s = str_replace( "\r" , " " , $s );
		$s = str_replace( "\n" , " " , $s );
		$s = str_replace( "\t" , " " , $s );
		$c = 0 ;
		$s = str_replace( "  " , " " , $s , $c );
		while ( $c > 0 ) {
			$s = str_replace( "  " , " " , $s , $c );
		}
		$s = str_replace( " >" , ">" , $s );
		$s = str_replace( "< " , "<" , $s );
		return $s ;
	}
	
	function compressCSS( $s ) {
		$s = str_replace( "\r" , " " , $s );
		$s = str_replace( "\n" , " " , $s );
		$s = str_replace( "\t" , " " , $s );
		$c = 0 ;
		$s = str_replace( "  " , " " , $s , $c );
		while ( $c > 0 ) {
			$s = str_replace( "  " , " " , $s , $c );
		}
		$s = str_replace( " }" , "}" , $s );
		$s = str_replace( "{ " , "{" , $s );
		$s = str_replace( " {" , "{" , $s );
		$s = str_replace( ": " , ":" , $s );
		$s = str_replace( " :" , ":" , $s );
		$s = str_replace( "; " , ";" , $s );
		$s = str_replace( " ;" , ";" , $s );
		return $s ;
	}
	
	function MainHead_L1( $SubTitle , $CSSFiles = array() , $uCap = true , $JSFiles = array() ) {
		global $BASE_JS_LIST , $dbConfig , $RelRootDir , $AbsRootDir , $UserThemeLoc , $portalDB , $UserDepartment , $UserOptions ;
		global $MonthNames , $DaysOfWeek , $DaysOfWeekShort ;
		if ( $SubTitle != "" ) {
			$SubTitle = " - ".$SubTitle ;
		}
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
				<html>
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
						<meta name="description" content="">
						<meta name="keywords" content="">
						<meta http-equiv="Expires" content="-1">
						<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
						<link rel="stylesheet" href="'.$RelRootDir.'themes/std0/base.css" type="text/css">' ;
		
		for ( $k = 0 ; $k < count( $CSSFiles ) ; $k++ ) {
			echo '<link rel="stylesheet" href="'.str_ireplace( '%UT' , 'themes/std0' , $CSSFiles[ $k ] ).'" type="text/css">' ;
		}

		$baseScripts = '' ;
		foreach( $BASE_JS_LIST as $f ) {
			$baseScripts.= '<script type="text/javascript">'.file_get_contents( $AbsRootDir.'/files/'.$f ).'</script>' ;
		}
		require_once( $RelRootDir.'files/config.js.php' );
		require_once( $RelRootDir.'files/base.constants.js.php' );
		echo $baseScripts ;

		if ( is_string( $JSFiles ) ) {
			$JSFiles = array( $JSFiles );
		}
		
		foreach ( $JSFiles as $f ) {
			if ( substr( $f , 0 , 1 ) == '#' ) {
				echo '<script type="text/javascript">'."\r\n" ;
				echo substr( $f , 1 );
				echo '</script>' ;
			} elseif ( substr( $f , 0 , 1 ) == '@' ) {
				$f = str_ireplace( '%UT' , 'themes/'.$UserThemeLoc , substr( $f , 1 ) );
				$f = $f.( strpos( $f , '?' ) !== false ? '&' : '?' ).'mtime='.time();

				echo '<script src="'.$f.'" type="module"></script>' ;
			} else {
				echo '<script src="'.str_ireplace( '%UT' , 'themes/'.$UserThemeLoc , $f ).'" type="text/javascript"></script>' ;
			}
		}

		$portalTitle = $dbConfig[ CFG_ORG_NAME_SIMPLE ];
		if ( isset( $dbConfig[ CFG_ENGINE_PORTAL_ALTER_TITLE ] ) ) {
			$portalTitle = $dbConfig[ CFG_ENGINE_PORTAL_ALTER_TITLE ];
		}

		if ( isset( $dbConfig[ CFG_ENGINE_PORTAL_STYLE_MOD ] ) ) {
			echo '<style type="text/css">'.compressCSS( $dbConfig[ CFG_ENGINE_PORTAL_STYLE_MOD ] ).'</style>' ;
		}

		if ( isset( $UserOptions[ OPTION__PORTAL__STYLE_MOD ] ) ) {
			echo '<style type="text/css">'.compressCSS( $UserOptions[ OPTION__PORTAL__STYLE_MOD ][ 'op_value' ] ).'</style>' ;
		}

		echo '<title>АИС СЭУ - '.$portalTitle.' '.$SubTitle.'</title>
						</head>' ;
		flush();
		echo '<body>'
			.( $uCap ? '<div id="page"><div id="page-head">
							<p class="mhTitle">
								<span class="mhTitle1">'.$portalTitle.'</span>
								<br>
								<span class="mhTitle2">АИС СЭУ</span>
							</p>
						</div>' : '' );
	}
	
	function MainHead_L2( $SubTitle = "" , $SubTitle2 = "" , $CSSFiles =  array() , $JSFiles =  array() , $hlpPage = "" , $BodyEXT = "" ) {
		global $BASE_JS_LIST , $HTML_TEMPLATES_LIST , $dbConfig , $portalDB ;
		global $LoginOk ;
		global $UserName , $UserLastVisitDate , $UserLastVisitTime , $UserThemeLoc , $UserID , $UserWorkerFirstID , $UserDepartment , $UserOptions ;
		global $RelRootDir , $AbsRootDir ;
		global $MonthNames , $DaysOfWeek , $DaysOfWeekShort ;
		
		if ( $SubTitle != "" ) {
			$SubTitle = " - ".$SubTitle ;
		}
		
		$baseScripts = '' ;
		foreach( $BASE_JS_LIST as $f ) {
			$baseScripts.= '<script type="text/javascript">'.file_get_contents( $AbsRootDir.'/files/'.$f ).'</script>' ;
		}
		
		echo compressHTML( file_get_contents( $AbsRootDir."/files/main-head--l2.html" ) ).
			'<style type="text/css">'.compressCSS( file_get_contents( $AbsRootDir.'/themes/'. $UserThemeLoc .'/base.css' ) ).'</style>' ;
		require_once( $RelRootDir.'files/config.js.php' );
		require_once( $RelRootDir.'files/base.constants.js.php' );
		echo $baseScripts ;
		
		echo '<style type="text/css">'.compressCSS( iconv( 'utf8' , 'cp1251' , file_get_contents( $AbsRootDir.'/themes/'. $UserThemeLoc .'/search.css' ) ) ).'</style>'.
			'<script type="text/javascript">'.file_get_contents( $AbsRootDir.'/files/search.js' ).'</script>' ;
		
		for ( $k = 0 ; $k < count( $CSSFiles ) ; $k++ ) {
			echo '<link rel="stylesheet" href="'.str_ireplace( '%UT', 'themes/'.$UserThemeLoc , $CSSFiles[ $k ] ).'?mtime='.time().'" type="text/css">' ;
		}
		
		foreach ( $JSFiles as $f ) {
			if ( substr( $f , 0 , 1 ) == '#' ) {
				echo '<script type="text/javascript">'."\r\n" ;
				echo substr( $f , 1 );
				echo '</script>' ;
			} elseif ( substr( $f , 0 , 1 ) == '@' ) {
				$f = str_ireplace( '%UT' , 'themes/'.$UserThemeLoc , substr( $f , 1 ) );
				$f = $f.( strpos( $f , '?' ) !== false ? '&' : '?' ).'mtime='.time();

				echo '<script src="'.$f.'" type="module"></script>' ;
			} else {
				$f = str_ireplace( '%UT' , 'themes/'.$UserThemeLoc , $f );
				$f = $f.( strpos( $f , '?' ) !== false ? '&' : '?' ).'mtime='.time();
				
				echo '<script src="'.$f.'" type="text/javascript"></script>' ;
			}
		}

		$portalTitle = $dbConfig[ CFG_ORG_NAME_SIMPLE ];
		if ( isset( $dbConfig[ CFG_ENGINE_PORTAL_ALTER_TITLE ] ) ) {
			$portalTitle = $dbConfig[ CFG_ENGINE_PORTAL_ALTER_TITLE ];
		}

		if ( isset( $dbConfig[ CFG_ENGINE_PORTAL_STYLE_MOD ] ) ) {
			echo '<style type="text/css">'.compressCSS( $dbConfig[ CFG_ENGINE_PORTAL_STYLE_MOD ] ).'</style>' ;
		}

		if ( isset( $UserOptions[ OPTION__PORTAL__STYLE_MOD ] ) ) {
			echo '<style type="text/css">'.compressCSS( $UserOptions[ OPTION__PORTAL__STYLE_MOD ][ 'op_value' ] ).'</style>' ;
		}
		
		echo '<title>АИС СЭУ - '.$portalTitle.' '.$SubTitle.'</title>
			</head>' ;
		flush();
		echo '<body '.$BodyEXT.'>' ;
		ob_start();
		foreach( $HTML_TEMPLATES_LIST as $tfn ) {
			include( $AbsRootDir.'/cores/core.html.custom.'.$tfn.'.php' );
		}
		$tmpl = ob_get_contents();
		ob_end_clean();
		echo $tmpl ;
		echo '<div id="page">
			<div id="page-head">
				<p class="mhTitle">
					<span class="mhTitle1">'.$portalTitle.'</span>
					<br/>
					<span class="mhTitle2">АИС СЭУ</span>
				</p>
				<table class="mhTable">
					<tr>
						<td class="mhtCell">
							<table class="mhInfo">
								<tr>
									<td class="mhiSubTable">
										'.$SubTitle2.'
									</td>
									<td>
										'.( $LoginOk ? '<span style="float: right;">
											<span class="mhiUserNameL">Добро пожаловать, </span><span class="mhiUserNameV">'.NAMES_Format( NAMES_parse( $UserName ) , '%F1 %I1 %O1' ).'</span><br>
											<span class="mhiLastVisitL">Ваш последний визит: <span class="mhiLastVisitV">'.$UserLastVisitDate.'</span> в <span class="mhiLastVisitV">'.$UserLastVisitTime.'</span></span>
										</span>' : '' ).'
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table class="mhMenu">
								<tr>
									<td>
										<a href="/">
											Главная
										</a>
									</td>
									<td>
										<a href="/">
											Профиль
										</a>
									</td>
									<td>
										<a href="/">
											Календарь
										</a>
									</td>
									<td>
										<a href="'.$hlpPage.'" target="_blank">
											Справка
										</a>
									</td>
									<td>
										<a href="/messages.list.php">
											Сообщения -/-
										</a>
									</td>
									'.( false ? '<td>
										<a href="/exit.php">
											выход
										</a>
									</td>' : '' ).'
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div id="page-content">' ;
		flush();
	}
	
	function MainHead_L2V2( $SubTitle , $SubTitle2 , $CSSFiles =  array() , $JSFiles =  array() , $hlpPage ) {
		global $BASE_JS_LIST , $dbConfig , $dbHost , $dbDatabase , $dbUser , $dbPassword ;
		global $LoginOk ;
		global $UserName , $UserLastVisitDate , $UserLastVisitTime , $UserThemeLoc , $UserID , $UserWorkerFirstID ;
		global $RelRootDir , $AbsRootDir ;
		
		if ( $SubTitle != "" ) {
			$SubTitle = " - ".$SubTitle ;
		}
		
		$baseScripts = '' ;
		foreach( $BASE_JS_LIST as $f ) {
			$baseScripts.= '<script type="text/javascript">'.file_get_contents( $AbsRootDir.'/files/'.$f ).'</script>' ;
		}
		
		echo compressHTML( file_get_contents( $AbsRootDir."/files/main-head--l2.html" ) ) , "<style type=\"text/css\">" , compressCSS( file_get_contents( $AbsRootDir."/themes/". $UserThemeLoc ."/base-2.css" ) ) , "</style>" ,
		$baseScripts ;
		
		$con = OpenDB( $dbHost , $dbUser , $dbPassword , $dbDatabase );
		$msgNC = RowAsArray( $con , "select count( `id` ) as `cid` from `messages` where ( `to_user` = ".$UserWorkerFirstID." ) and ( `status` < 1 );" );
		$msgUC = RowAsArray( $con , "select count( `id` ) as `cid` from `messages` where ( `to_user` = ".$UserWorkerFirstID." ) and ( `status` = 1 );" );
		$msgTC = RowAsArray( $con , "select count( `id` ) as `cid` from `messages` where ( `to_user` = ".$UserWorkerFirstID." ) and ( `status` <= 2 );" );
		NoResultQuery( $con , "update `messages` set `status` = 1 where ( `to_user` = ".$UserWorkerFirstID." ) and ( `status` < 1 );" );
		
		if ( $msgNC[ "cid" ] > 0 ) {
			echo "<script type=\"text/javascript\">
					if ( confirm( \"С момента вашего последнего визита получены новые сообщения ( ".$msgNC[ "cid" ]." ).\\r\\nЖелаете их прочесть?\" ) ) {
						window.open( \"/messages.list.php\" , \"_blank\" );
					}
				</script>" ;
		}
		mysql_close( $con );
		
		for ( $k = 0 ; $k < count( $CSSFiles ) ; $k++ ) {
			echo '<link rel="stylesheet" href="'.str_ireplace( '%UT', 'themes/'.$UserThemeLoc , $CSSFiles[ $k ] ).'" type="text/css">' ;
		}
		
		foreach ( $JSFiles as $f ) {
			if ( substr( $f , 0 , 1 ) == "#" ) {
				echo "<script type=\"text/javascript\">\r\n" ;
				echo substr( $f , 1 );
				echo "</script>" ;
			} else {
				echo "<script src=\"".str_ireplace( "%UT" , "themes/".$UserThemeLoc , $f )."\" type=\"text/javascript\"></script>" ;
			}
		}
		
		echo "<title>АИС СЭУ - ".$dbConfig[ "org.name.simple" ]." ".$SubTitle."</title>
		</head>" ;
		flush();
		echo "<body>
			<div id=\"page\">
				<div id=\"page-head\">
					<p class=\"mhTitle\">
						<span class=\"mhTitle1\">".$dbConfig[ "org.name.simple" ]."</span>
						<br>
						<span class=\"mhTitle2\">АИС СЭУ</span>
					</p>
					<table class=\"mhTable\">
						<tr>
							<td class=\"mhtCell\">
								<table class=\"mhInfo\">
									<tr>
										<td class=\"mhiSubTable\">
											".$SubTitle2."
										</td>
										<td>
											".( $LoginOk ? "<span style=\"float: right;\">
												<span class=\"mhiUserNameL\">Добро пожаловать, </span><span class=\"mhiUserNameV\">".NAMES_Format( NAMES_parse( $UserName ) , "%F1 %I1 %O1" )."</span><br>
												<span class=\"mhiLastVisitL\">Ваш последний визит: <span class=\"mhiLastVisitV\">".$UserLastVisitDate."</span> в <span class=\"mhiLastVisitV\">".$UserLastVisitTime."</span></span>
											</span>" : "" )."
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<table class=\"mhMenu\">
									<tr>
										<td>
											<a href=\"/\">
												Главная
											</a>
										</td>
										<td>
											<a href=\"/\">
												Профиль
											</a>
										</td>
										<td>
											<a href=\"/\">
												Календарь
											</a>
										</td>
										<td>
											<a href=\"".$hlpPage."\" target=\"_blank\">
												Справка
											</a>
										</td>
										<td>
											<a href=\"/messages.list.php\">
												Сообщения [ ".( $msgNC[ "cid" ] + $msgUC[ "cid" ] )." / ".$msgTC[ "cid" ]." ]
											</a>
										</td>
										".( false ? "<td>
											<a href=\"/exit.php\">
												выход
											</a>
										</td>" : "" )."
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<div id=\"page-content\">";
		return;
	}
	
	function MainHead_Frame( $CSSFiles =  array() , $JSFiles =  array() , $BodyEXT = '' ) {
		global $BASE_JS_LIST , $dbHost , $dbDatabase , $dbUser , $dbPassword , $portalDB ;
		global $LoginOk ;
		global $UserName , $UserLastVisitDate , $UserLastVisitTime , $UserThemeLoc , $UserID , $UserWorkerFirstID , $UserOptions ;
		global $RelRootDir , $AbsRootDir ;
		
		$baseScripts = '' ;
		foreach( $BASE_JS_LIST as $f ) {
			$baseScripts.= '<script type="text/javascript">'.file_get_contents( $AbsRootDir.'/files/'.$f ).'</script>' ;
		}
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
			<meta name="description" content="">
			<meta name="keywords" content="">
			<meta http-equiv="Expires" content="-1">
			<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
			<!--[if lt IE 7]><style>
				body {width:100%;height:100%;padding:0;margin:0;}
				.msg-ie6-Table{width:600px;height:100%;}
				.msg-ie6-content{padding:12px;border:1px dashed #808080;background-color:#ffe0c0;text-align:justify;font-family:sans-serif;font-size:12pt;text-indent:0.5cm;}
				.msg-ie6-content:first-letter{color:#ff0000;}
				.msg-ie6-highlight-content{color: #0000ff;}
				</style></head><body><table align=center class="msg-ie6-Table"><tr><td><div class="msg-ie6-content">
					Вы просматриваете эту страницу через обозреватель <span class="msg-ie6-highlight-content">Internet Explorer</span> версии ниже <span class="msg-ie6-highlight-content">7.0</span>.
					Обновите ваш обозреватель интернет до версии 7.0 или выше или используйте другой обозреватель.
				</div></td></tr></table></body></html><![endif]-->
			<!--[if IE]><![if gte IE 7]><![endif]-->

			<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico">
			<link rel="stylesheet" href="/themes/'.$UserThemeLoc.'/base.css" type="text/css">'.$baseScripts ;
		
		
		
		for ( $k = 0 ; $k < count( $CSSFiles ) ; $k++ ) {
			echo '<link rel="stylesheet" href="'.str_ireplace( '%UT', 'themes/'.$UserThemeLoc, $CSSFiles[ $k ] ).'" type="text/css">' ;
		}
		
		foreach ( $JSFiles as $f ) {
			if ( $f == '<_GET>' ) {
				echo '<script type="text/javascript">'."\r\n" ;
				echo 'var _GET = [] ;'."\r\n" ;
				foreach( $_GET as $gk => $gv ) {
					echo '_GET.push( [ "'.$gk.'" , "'.$gv.'" ] );'."\r\n" ;
				}
				echo '</script>' ;
			} else
				if ( substr( $f , 0 , 4 ) == '#var' ) {
					echo '<script type="text/javascript">'."\r\n" ;
					echo substr( $f , 1 );
					echo '</script>' ;
				} else {
					echo '<script src="'.str_ireplace( '%UT' , 'themes/'.$UserThemeLoc , $f ).'" type="text/javascript"></script>' ;
				}
		}

		if ( isset( $dbConfig[ CFG_ENGINE_PORTAL_STYLE_MOD ] ) ) {
			echo '<style type="text/css">'.compressCSS( $dbConfig[ CFG_ENGINE_PORTAL_STYLE_MOD ] ).'</style>' ;
		}

		if ( isset( $UserOptions[ OPTION__PORTAL__STYLE_MOD ] ) ) {
			echo '<style type="text/css">'.compressCSS( $UserOptions[ OPTION__PORTAL__STYLE_MOD ][ 'op_value' ] ).'</style>' ;
		}

		echo '</head>' ;
		flush();
		echo '<body ' , $BodyEXT , '>
					<div id="page">
						<div id="page-content" style="padding : 0px ;">' ;
	}
	
	
	function MainHead_Print( $title , $CSSFiles =  array() , $JSFiles = array() ) {
		global $BASE_JS_LIST , $dbConfig , $LoginOk ;
		global $UserName , $UserLastVisitDate , $UserLastVisitTime , $UserThemeLoc , $UserDepartment , $portalDB , $UserOptions ;
		global $RelRootDir , $AbsRootDir ;
		global $MonthNames , $DaysOfWeek , $DaysOfWeekShort ;
		
		if ( $title != '' ) {
			$title = ' - '.$title ;
		}

		$baseScripts = '' ;
		foreach( $BASE_JS_LIST as $f ) {
			$baseScripts.= '<script type="text/javascript">'.file_get_contents( $AbsRootDir.'/files/'.$f ).'</script>' ;
		}
		
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<meta http-equiv="Expires" content="-1">
		<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
		<!--[if lt IE 7]><style>
			body {width:100%;height:100%;padding:0;margin:0;}
			.msg-ie6-Table{width:600px;height:100%;}
			.msg-ie6-content{padding:12px;border:1px dashed #808080;background-color:#ffe0c0;text-align:justify;font-family:sans-serif;font-size:12pt;text-indent:0.5cm;}
			.msg-ie6-content:first-letter{color:#ff0000;}
			.msg-ie6-highlight-content{color: #0000ff;}
			</style></head><body><table align=center class="msg-ie6-Table"><tr><td><div class="msg-ie6-content">
				Вы просматриваете эту страницу через обозреватель <span class="msg-ie6-highlight-content">Internet Explorer</span> версии ниже <span class="msg-ie6-highlight-content">7.0</span>.
				Обновите ваш обозреватель интернет до версии 7.0 или выше или используйте другой обозреватель.
			</div></td></tr></table></body></html><![endif]-->
		<!--[if IE]><![if gte IE 7]><![endif]-->
		<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico">
		<link rel="stylesheet" href="/themes/'.$UserThemeLoc.'/base.prn.css" type="text/css">' ;

		for ( $k = 0 ; $k < count( $CSSFiles ) ; $k++ ) {
			echo '<link rel="stylesheet" href="'.str_ireplace( '%UT' , 'themes/'.$UserThemeLoc , $CSSFiles[ $k ] ).'" type="text/css">' ;
		}

		require_once( $RelRootDir.'files/config.js.php' );
		require_once( $RelRootDir.'files/base.constants.js.php' );
		echo $baseScripts ;

		if ( isset( $JSFiles[ 'inc' ] ) ) {
			foreach( $JSFiles[ 'inc' ] as $f ) {
				echo '<script defer src="'.str_ireplace( '%UT' , 'themes/'.$UserThemeLoc , $f ).'" type="text/javascript"></script>' ;
			}
		}
		if ( isset( $JSFiles[ 'init' ] ) ) {
			echo '<script type="text/javascript">'.$JSFiles[ 'init' ].'</script>' ;
		}

		$portalTitle = $dbConfig[ CFG_ORG_NAME_SIMPLE ];
		if ( isset( $dbConfig[ CFG_ENGINE_PORTAL_ALTER_TITLE ] ) ) {
			$portalTitle = $dbConfig[ CFG_ENGINE_PORTAL_ALTER_TITLE ];
		}

		if ( isset( $dbConfig[ CFG_ENGINE_PORTAL_STYLE_MOD ] ) ) {
			echo '<style type="text/css">'.compressCSS( $dbConfig[ CFG_ENGINE_PORTAL_STYLE_MOD ] ).'</style>' ;
		}

		if ( isset( $UserOptions[ OPTION__PORTAL__STYLE_MOD ] ) ) {
			echo '<style type="text/css">'.compressCSS( $UserOptions[ OPTION__PORTAL__STYLE_MOD ][ 'op_value' ] ).'</style>' ;
		}

		echo '<title>АИС СЭУ - '.$portalTitle.' '.$title.'</title>
			</head>
				<body>' ;
	}
	
	function closeHtml() {
		global $UserID , $timerData ;
		$res = array();
		foreach( $timerData as $tdn => $tdv ) {
			$m = 0 ;
			$c = 0 ;
			if ( count( $tdv ) > 1 ) {
				$res[ $tdn ] = array();
				for( $i = 1 ; $i < count( $tdv ) ; $i+= 2 ) {
					$v = $tdv[ $i ] - $tdv[ $i - 1 ];
					$res[ $tdn ][]= number_format( $v , 6 );
					$m+= $v ;
					$c++ ;
				}
				$res[ $tdn ]= $tdn." : ".implode( " , " , $res[ $tdn ] );
				if ( $c > 1 ) {
					$res[ $tdn ].= " , total : ".number_format( $m , 6 )." , count : ".$c." , middle : ".number_format( ( $m / $c ) , 6 );
				}
			} else {
			}
		}
		if ( count( $res ) > 0 ) {
			$res[]= "memory ( peak / current ): ".number_format( memory_get_peak_usage() / 1048576 , 2 , "," , " " )." MB / ".number_format( memory_get_usage() / 1048576 , 2 , "," , " " )." MB" ;
		}
		echo "</div>
			<div id=\"page-footer\">
				<div style=\"color: #808080; font-size: 8pt; text-align: center\">
					<span>Copyright &copy; Пекшев Петр Александрович, 2008<br>
					e-mail: <a href=\"mailto:petrox@rambler.ru\">petrox@rambler.ru</a>
					icq: 468583467</span>
				</div>
			</div>
		</div>
		" , ( count( $res ) > 0 && $UserID == 1 ? "<script type=\"text/javascript\">console.log( \"".implode( "\\n" , $res )."\" );</script>" : "" ) , "
		</body>
	</html><!--[if IE]><![endif]>" ;
	}
	
	
	
	function closeHtml_Frame() {
		global $UserID , $timerData ;
		$res = array();
		foreach( $timerData as $tdn => $tdv ) {
			$m = 0 ;
			$c = 0 ;
			if ( count( $tdv ) > 1 ) {
				$res[ $tdn ] = array();
				for( $i = 1 ; $i < count( $tdv ) ; $i+= 2 ) {
					$v = $tdv[ $i ] - $tdv[ $i - 1 ];
					$res[ $tdn ][]= number_format( $v , 6 );
					$m+= $v ;
					$c++ ;
				}
				$res[ $tdn ]= $tdn." : ".implode( " , " , $res[ $tdn ] );
				if ( $c > 1 ) {
					$res[ $tdn ].= " , total : ".number_format( $m , 6 )." , count : ".$c." , middle : ".number_format( ( $m / $c ) , 6 );
				}
			} else {
			}
		}
		echo "</div>
		</div>
		" , ( count( $res ) > 0 && $UserID == 1 ? "<script type=\"text/javascript\">console.log( \"".implode( "\\n" , $res )."\" );</script>" : "" ) , "
		</body>
	</html><!--[if IE]><![endif]>" ;
	}
	
	function closeHtml_Print() {
		echo "</body>
	</html><!--[if IE]><![endif]>" ;
	}
	
	function MessageForm( $Text = "Вы не имеете прав для доступа к этой части портала." , $Cap = "Сообщение" , $Btn = "на главную" , $Target = "/index.php" , $Img = "/themes/%UT/icon1.gif" ) {
		echo "<table align=center class=\"msgForm\">
			<tr>
				<td class=\"msgFormTitle\">
					".$Cap."
				</td>
			</tr>
			<tr>
				<td class=\"msgFormDesc\">
					".$Text."<br><br>
					<div align=center><a href=\"".$Target."\" class=\"btn3\">".$Btn."</a></div>
				</td>
			</tr>
		</table>" ;
		return ;
	}
	
	function MessageFormFrame( $Text = "Вы не имеете прав для доступа к этой части портала." , $Cap = "Сообщение" , $Img = "/themes/%UT/icon1.gif" ) {
		echo "<table align=center class=\"msgForm\">
			<tr>
				<td class=\"msgFormTitle\">
					".$Cap."
				</td>
			</tr>
			<tr>
				<td class=\"msgFormDesc\">
					".$Text."
				</td>
			</tr>
		</table>" ;
		return ;
	}
	
	function InlineMessage( $Text ) {
		echo "<table align=center class=\"inlineMsg\">
			<tr>
				<td class=\"inlineMsgDesc\">
					".$Text."
				</td>
			</tr>
		</table>" ;
		return ;
	}
	
	function readFormP0( $fd ) {
		$v = array_flip( strexp( $fd ) );
		array_walk( $v , function ( &$v , $k ) {
			$t = substr( $k , -1 );
			$kn = 'i_'.substr( $k , 0 , -2 );
			$v = isset( $_REQUEST[ $kn ] ) ? $_REQUEST[ $kn ] : null ;
			switch ( $t ) {
				case 'i' : $v = intval( $v , 10 ); break ;
				case 's' : $v = clearText( $v ); break ;
				case 'S' : $v = clearText( $v , true ); break ;
				case 'd' : $v = PrepDate( $v ); break ;
				case 'p' : $v = str_replace( ',' , '.' , floatval( str_replace( ',' , '.' , $v ) ) ); break ;
				case 'b' : $v = !is_null( $v ) && $v != '' ? 1 : 0 ; break ;
				case 'B' :
					$tmp = 0 ;
					foreach( $v as $i ) {
						$tmp+= intval( $i );
					}
					$v = $tmp ;
					break ;
			}
		} );
		return array_rekey( $v , '/(.+):(?:i|s|d|p|b)$/i' , '${1}' );
	}
	
	function ClearOutputText( $text , $rs = array ( array( "<" , "&lt;" ) , array( ">" , "&gt;" ) ) ) {
		$res = $text ;
		for ( $i = 0 ; $i < count( $rs ) ; $i++ ) {
			$res = str_replace( $rs[ $i ][ 0 ] , $rs[ $i ][ 1 ] , $res );
		}
		
		return $res;
	}
	
	function HighlightOutputText( $text , $hightlight = array() ) {
		$res = $text ;
		for ( $n = 0 ; $n < count( $hightlight ) ; $n++ ) {
			$chl = $hightlight[ $n ];
			$hll = strlen( $chl );
			$poss = array();
			$pos = mb_stripos( $res, $chl, 0, "cp1251" );
			$i = 0;
			while ( $pos !== false ) {
				$poss[]= $pos;
				$i++ ;
				$pos = mb_stripos( $res, $chl, $pos + 1, "cp1251" );
			}
			$poss[]= strlen( $res );
			$tmp = "";
			for ( $j = $i - 1 ; $j >= 0 ; $j-- ) {
				$tmp = "<span style=\"color: #ffffff; background-color: #4080ff; font-weight: bold;\">".substr( $res, $poss[ $j ], $hll )."</span>".substr( $res, $poss[ $j ]+ $hll, $poss[ $j+ 1 ]- $poss[ $j ]- $hll ).$tmp;
			}
			
			$tmp = substr( $res, 0, $poss[ 0 ] ).$tmp;
			$res = $tmp;
		}
		return $res;
	}
	
	function PrepareOutputText( $text, $hightlight=array() )
	{
		$res = ClearOutputText( $text );
		$res = HighlightOutputText( $res, $hightlight );
		return $res;
	}
	
	$ra = array(
		array( "~<~" , "&lt;" ) ,
		array( "~>~" , "&gt;" ) ,
		array( "~\\[ *(/?(?:b|i)) *\\]~i" , "<$1>" ) ,
		array( "~\\[ *c +(#[0-9a-fA-F]{6}) *\\]~i" , "<font color=\"$1\">" ) ,
		array( "~\\[ */c *\\]~i" , "</font>" ) ,
		array( "~\\[ *bgc +(#[0-9a-fA-F]{6}) *\\]~i" , "<span style=\"background-color : $1\">" ) ,
		array( "~\\[ */bgc *\\]~i" , "</span>" ) ,
		array( "~\\[ *a +\"([^\\]\"]+)(?:\" *)?\\]~i" , "<a href=\"$1\">" ) ,
		array( "~\\[ */a *\\]~i" , "</a>" ) ,
		array( "~\\[ *br *\\]~i" , "<br>" ) ,
		array( "~\\[ *frame +\"([^\\]\"]+)(?:\" *)?\\]~i" , "<iframe frameborder=\"no\" seamless=\"seamless\" src=\"$1\" width=\"100%\" height=\"240px\" class=\"iframe-std-1\"></iframe>" ) ,
		array( "~\\[ *img +\"([^\\]\"]+)(?:\" *)?\\]~i" , "<center><a href=\"$1\" target=\"_blank\"><img src=\"$1\" style=\"width : 20cm ;\"></a></center>" ) ,
		array( "~\\[ *imgo +\"([^\\]\"]+)(?:\" *)?\\]~i" , "<center><a href=\"$1\" target=\"_blank\"><img src=\"$1\"></a></center>" ) ,
		array( "~\\[ *imgl +\"([^\\]\"]+)(?:\" *)? +\"([^\\]\"]+)(?:\" *)?\\]~i" , "<center><a href=\"$2\" target=\"_blank\"><img src=\"$1\"></a></center>" ) ,
		array( "~\\[ *pdf +\"([^\\]\"]+)(?:\" *)?\\]~i" , '<div class="pdf-preview-area force-big"><div class="pdf-preview-wrapper" data-link="$1"></div></div>' )
	);
	
	function parseText( $text ) {
		global $ra ;
		$res = trim( $text );
		foreach( $ra as $m ) {
			$res = preg_replace( $m[ 0 ] , $m[ 1 ] , $res );
		}
		$res = str_replace( "\r" , " " , $res );
		$res = str_replace( "  " , " " , $res );
		$res = str_replace( "\n" , "<p>" , $res );
		$res = "<p>".$res ;
		return $res ;
	}
	
	function Text2HTMLAttr( $str , $qs = "\"" ) {
		$str = str_replace( $qs , "&;" , $str );
	}
	
	function toHTML( $str , $enc = DEF_CODEPAGE ) {
		return htmlentities( $str , ENT_QUOTES | ENT_HTML401 , $enc );
	}
	
	function mkBtn3Menu( $text , $href , $menu ) {
		return '<div class="btn3-w-menu">
			<a href="'.$href.'" class="btn3" target="_blank">'.$text.'</a>
			<div class="btn3-menu-btn">&#x2BC6;</div>
			<div class="btn3-menu">'.$menu.'</div>
		</div>' ;
	}

	function ErrorPageAndExit( $Text = 'Вы не имеете прав для доступа к этой части портала.' , $Cap = 'Сообщение' , $Btn = 'на главную' , $Target = '/index.php' , $Img = '/themes/%UT/icon1.gif' ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br/><br/><br/><br/><br/>' ;
		MessageForm( $Text , $Cap , $Btn , $Target , $Img );
		closeHtml();
		exit();
	}
	