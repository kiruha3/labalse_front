<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	class TDB {
		protected $con = false ;
		public $dbgMode = false ;

		protected $dbgQueryIndex ;

		function __construct( $host , $user , $pass , $db = DB_NAME , $codepage = DB_CODEPAGE ) {
			$cc = new mysqli( $host , $user , $pass , $db );
			if ( $cc->connect_error ) {
				die( "Connect Error ( ".$cc->connect_errno. " ) ".$cc->connect_error );
			}
			$cc->query( "set names '".$codepage."' ;" );
			if ( $cc->errno ) {
				die( "set names error ( ".$cc->errno. " ) ".$cc->error );
			}
			$this->con = $cc ;

			$this->dbgQueryIndex = 0 ;
		}

		function __destruct() {
			if ( $this->con !== false ) {
				$this->con->close();
			}
		}

		function getValueType( &$v ) {
			if ( !is_null( $v ) ) {
				if ( is_int( $v ) ) {
					$ct = "i" ;
				} else
				if ( is_double( $v ) ) {
					$ct = "d" ;
				} else
				if ( is_string( $v ) ) {
					$ct = "s" ;
				} else
				if ( is_array( $v ) ) {
					$ct = "" ;
					foreach ( $v as &$cv ) {
						$ct.= $this->getValueType( $cv );
					} unset( $cv );
				}
			} else {
				$ct = "null" ;
			}

			return $ct ;
		}

		private function dbgInfo( $q = false , $p = false ) {
			if ( $this->dbgMode === true ) {
				fixTimerData( "Query: ".$this->dbgQueryIndex );
				fixTimerData( "TDB" );
				if ( $q !== false || $p !== false ) {
					$eid = uniqid( "v" , true );
					echo "<div id=\"--dbg-".$this->dbgQueryIndex."\" class=\"stddbg-info\"><div class=\"stddbg-cap\">".$this->dbgQueryIndex."</div><input id=\"--dbg-input-rand-".$eid."\" name=\"--dbg-input-rand-".$eid."\" type=\"checkbox\" class=\"stddbg-input\"><label for=\"--dbg-input-rand-".$eid."\" class=\"stddbg-label\">" ;
					if ( $q !== false ) {
						echo "<div>".$q."</div>" ;
					}
					if ( $p !== false ) {
						echo "<div>".print_r_html_2( $p , true , true )."</div>" ;
					}
					echo "</label></div>" ;
				} else {
					$this->dbgQueryIndex++ ;
				}
			} else
			if ( $this->dbgMode === 'log' ) {
				if ( $q !== false || $p !== false ) {
					if ( $q !== false ) {
						$errStr = 'TDB debug [ '.$this->dbgQueryIndex.' ]: '.$q ;
						error_log( preg_replace( '/\s+/' , ' ' , $errStr ) );
					}
					if ( $p !== false ) {
						$errStr = 'TDB debug [ '.$this->dbgQueryIndex.' ]: '.print_r( $p , true ) ;
						error_log( preg_replace( '/\s+/' , ' ' , $errStr ) );
					}
				} else {
					$this->dbgQueryIndex++ ;
				}
			}
		}

		function rawQuery( $q , $o = false ) {
			$cc = $this->con ;
			$this->dbgInfo( $q );
			$res = $cc->query( $q );
			if ( $cc->errno ) {
				die( "Error ( ".$cc->errno. " ) ".$cc->error );
			}

			if ( $res !== true && $res !== false ) {
				$result = $res->fetch_all( MYSQLI_ASSOC );
				$res->free();

				if ( $o !== false ) {
					$result = array_column( $result , null , $o );
				}

				$this->dbgInfo();
				return $result ;
			} else {
				$this->dbgInfo();
				return $res ;
			}
		}

		private function select( &$q , $o , &$t , &$p ) {
			$it = 0 ;
			switch( $o[ "t" ] ) {
				case "n" :
				case "s" :
					$ip = 2 ;
					break ;

				case "m" :
					$ip = 3 ;
					break ;
			}
			$ic = strlen( $t );
			$rt = "" ;
			$rp = array();
			$rp[]= &$rt ;
			while ( $it < $ic ) {
				switch( $t[ $it ] ) {
					case "*" :
						$pc = count( $p[ $ip ] );
						$rt.= str_pad( "" , $pc , $t[ ++$it ] );
						foreach ( $p[ $ip ] as &$v ) {
							$rp[]= &$v ;
						} unset( $v );
						if ( $pc == 0 ) {
							$q = preg_replace( '/\?\*/' , "''" , $q , 1 );
						} else {
							$q = preg_replace( '/\?\*/' , str_pad( "" , 2 * $pc - 1 , "?," ) , $q , 1 );
						}
						break ;

					default :
						$rt.= $t[ $it ];
						$rp[]= &$p[ $ip ];
						break ;
				}

				$it++ ;
				$ip++ ;
			}

			$cc = $this->con ;

			$this->dbgInfo( $q , $rp );

			$stmt = $cc->prepare( $q );
			if ( $stmt === false ) {
				die( "Error ( ".$cc->errno. " ) ".$cc->error );
			}

			if ( count( $rp ) > 1 ) {
				call_user_func_array( array( $stmt , "bind_param" ) , $rp );
			}

			if ( !$stmt->execute() ) {
				die( "Error ( ".$cc->errno. " ) ".$cc->error );
			}

			$res = $stmt->get_result();

			switch( $o[ "t" ] ) {
				case "n" :
					$result = true ;
					break ;

				case "s" :
					$result = $res->fetch_all( MYSQLI_ASSOC );
					$res->free();
					if ( $result !== false && count( $result ) == 1 ) {
						$result = $result[ 0 ];
					} else {
						$result = false ;
					}
					break ;

				case "m" :
					$result = $res->fetch_all( MYSQLI_ASSOC );
					if ( $o[ "k" ] !== false ) {
						$result = array_column( $result , null , $o[ "k" ] );
					}
					$res->free();
					break ;
			}

			$stmt->close();

			$this->dbgInfo();

			return $result ;
		}

		function row( $q , $t = "" ) {
			$p = func_get_args();
			return $this->select( $q , array( "t" => "s" ) , $t , $p );
		}

		function table( $n , $k = false ) {
			return $this->rawQuery( "select * from `".$n."`" , $k );
		}
		
		/**
		 * @param string $q запрос
		 * @param midex $k поле результата, значение которого используется в качестве ключей массива
		 * @param string $t строка с описанием типа подставляемого параметра: "is*i" => 1 , "1" , (1,2,3)
		 * @return array|bool|mixed
		 */
		function query( $q , $k = false , $t = "" ) {
			$p = func_get_args();
			return $this->select( $q , array( "t" => "m" , "k" => $k ) , $t , $p );
		}


		/**
		 * для запросов типа insert, delete и т.д.
		 *
		 * @param string $q запрос
		 * @param string $t строка с описанием типа подставляемого параметра: "is*i" => 1 , "1" , (1,2,3)
		 */
		function noResult( $q , $t = "" ) {
			$p = func_get_args();
			return $this->select( $q , array( "t" => "n" ) , $t , $p );
		}

		function insertRow( $tab , $values = array( "id" => null ) , $opt = array() ) {
			$cOpt = array(
			);
			if ( is_array( $opt ) ) {
				$cOpt+= $opt ;
			}

			$n = array();
			$qm = array();
			$t = "" ;
			$v = array();
			$v[]= &$t ;

			foreach ( $values as $vn => $vv ) {
				if ( !is_null( $vv ) ) {
					if ( is_int( $vv ) ) {
						$t.= "i" ;
					} else
					if ( is_double( $vv ) ) {
						$t.= "d" ;
					} else
					if ( is_string( $vv ) ) {
						$t.= "s" ;
					}
					$qm[]= "?" ;
					$v[]= &$values[ $vn ];
				} else {
					$qm[]= "null" ;
				}

				$n[]= $vn ;
			}

			$q = "insert into `".$tab."` ( `".implode( "` , `" , $n )."` ) values ( ".implode( " , " , $qm )." )" ;
			$cc = $this->con ;

			$this->dbgInfo( $q , $v );

			$stmt = $cc->prepare( $q );
			if ( count( $values ) > 1 ) {
				call_user_func_array( array( $stmt , "bind_param" ) , $v );
			}

			if ( !$stmt->execute() ) {
				echo "insert error ( ".$cc->errno. " ) ".$cc->error ;
			}

			$stmt->close();

			$this->dbgInfo();
		}

		function updateRow( $tab , $values , $id = "id" , $opt = array() ) {
			$cOpt = array(
			);
			if ( is_array( $opt ) ) {
				$cOpt+= $opt ;
			}

			$n = array();
			$t = "" ;
			$v = array();
			$v[]= &$t ;

			foreach ( $values as $vn => $vv ) {
				$ct = $this->getValueType( $vv );

				if ( $vn == $id ) {
					$idt = $ct ;
					$idv = &$values[ $id ];
				} else {
					if ( $ct != "null" ) {
						$n[]= "`".$vn."` = ?" ;
						$v[]= &$values[ $vn ];
						$t.= $ct ;
					} else {
						$n[]= "`".$vn."` = null" ;
					}
				}
			}

			$v[]= &$idv ;
			$t.= $idt ;

			$q = "update `".$tab."` set ".implode( " , " , $n )." where `".$id."` = ?" ;
			$cc = $this->con ;

			$this->dbgInfo( $q , $v );

			$stmt = $cc->prepare( $q );
			if ( count( $values ) > 1 ) {
				call_user_func_array( array( $stmt , "bind_param" ) , $v );
			}

			if ( !$stmt->execute() ) {
				echo "update error ( ".$cc->errno. " ) ".$cc->error ;
			}

			$stmt->close();

			$this->dbgInfo();
		}

		function deleteRow( $tab , $value , $key = "id" ) {
			$t = $this->getValueType( $value );
			$v = array(
				&$t ,
				&$value
			);

			$q = "delete from `".$tab."` where `".$key."` = ?" ;
			$cc = $this->con ;

			$this->dbgInfo( $q , $v );

			$stmt = $cc->prepare( $q );
			call_user_func_array( array( $stmt , "bind_param" ) , $v );

			if ( !$stmt->execute() ) {
				echo "delete error ( ".$cc->errno. " ) ".$cc->error ;
			}

			$stmt->close();

			$this->dbgInfo();
		}

		private function simpleSelect( &$tab , &$values , $opt = array() ) {
			$cOpt = array(
				"t" => "m" ,
				"op" => "and"
			);
			if ( is_array( $opt ) ) {
				$cOpt = $opt + $cOpt ;
			}

			$n = array();
			$qm = array();
			$t = "" ;
			$v = array();
			$v[]= &$t ;

			foreach ( $values as $vn => $vv ) {
				$ct = $this->getValueType( $vv );

				if ( $ct != "null" ) {
					if ( strlen( $ct ) == 1 ) {
						$n[]= "`".$vn."` = ?" ;
						if ( is_array( $values[ $vn ] ) ) {
							$v[]= &$values[ $vn ][ 0 ];
						} else {
							$v[]= &$values[ $vn ];
						}
						$t.= $ct ;
					} else {
						$cn = array();
						foreach ( $vv as $cvk => $cvv ) {
							$cn[]= "?" ;
							$v[]= &$values[ $vn ][ $cvk ];
						}
						$n[]= "`".$vn."` in ( ".implode( " , " , $cn )." )" ;
						$t.= $ct ;
					}
				} else {
					$n[]= "`".$vn."` is null" ;
				}
			}

			$q = "select * from `".$tab."` where ( ".implode( ") ".$cOpt[ "op" ]." (" , $n )." )".( isset( $cOpt[ "order" ] ) ? " order by `".$cOpt[ "order" ]."`" : "" );
			$cc = $this->con ;
			$this->dbgInfo( $q , $v );

			$stmt = $cc->prepare( $q );
			if ( count( $v ) > 1 ) {
				$fr = call_user_func_array( array( $stmt , "bind_param" ) , $v );
			}

			if ( !$stmt->execute() ) {
				echo "select error ( ".$cc->errno. " ) ".$cc->error ;
			}

			$res = $stmt->get_result();

			switch( $cOpt[ "t" ] ) {
				case "n" :
					$result = true ;
					break ;

				case "s" :
					$result = $res->fetch_all( MYSQLI_ASSOC );
					$res->free();
					if ( $result !== false && count( $result ) == 1 ) {
						$result = $result[ 0 ];
					} else {
						$result = false ;
					}
					break ;

				case "m" :
					$result = $res->fetch_all( MYSQLI_ASSOC );
					if ( $cOpt[ "k" ] !== false ) {
						$result = array_column( $result , null , $cOpt[ "k" ] );
					}
					$res->free();
					break ;
			}

			$stmt->close();

			$this->dbgInfo();

			return $result ;
		}

		function simpleRow( $tab , $data , $opt = array() ) {
			$cOpt = array(
				"id" => "id"
			);
			if ( is_array( $opt ) ) {
				$cOpt = $opt + $cOpt ;
				$cOpt[ "t" ] = "s" ;
			}

			if ( !is_array( $data ) ) {
				$data = array( $cOpt[ "id" ] => $data );
			}
			return $this->simpleSelect( $tab , $data , $cOpt );
		}

		function simpleQuery( $tab , $data , $k = false , $opt = array() ) {
			$cOpt = array(
				"id" => "id"
			);
			if ( is_array( $opt ) ) {
				$cOpt = $opt + $cOpt ;
				$cOpt[ "t" ] = "m" ;
				$cOpt[ "k" ] = $k ;
			}

			if ( !is_array( $data ) ) {
				$data = array( $cOpt[ "key" ] => $data );
			}

			return $this->simpleSelect( $tab , $data , $cOpt );
		}

		function lastInsertID() {
			return $this->con->insert_id ;
		}

	}
?>