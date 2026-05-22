<?php
	$UserLogin = '' ;
	$UserName = '' ;
	$UserID = 0 ;
	$UserLastVisitDate = '' ;
	$UserLastVisitTime = '' ;
	$UserDepartment = 0 ;
	$UserPost = '' ;
	$UserSpecialityNumber = '' ;
	$UserRights = array();
	$UserOptions = array() ;
	$UserThemeDir = '' ;
	$UserThemeLoc = '' ;
	$UserGroups = Array();
	$UserWorkerID = 0 ;
	$UserWorkerFirstID = 0 ;
	$UserAllWorkers = Array();
	$UserAllDeps = Array();
	$DepAllWorkers = Array();
	
	if ( isset( $dbConfig[ "engine.orgIndex" ] ) ) {
		$UserOrgIndex = $dbConfig[ "engine.orgIndex" ];
	} else {
		$UserOrgIndex = ORG_INDEX_TEST ;
	}
	
	$user = new TUser( false );
	$user->assign();
	
	function getRights( $placeID ) {
		global $UserID , $portalDB ;
		
		$row = $portalDB->row( "select `rights` from `access-rights` where ( `user_id` = ? ) and ( `place` = ? ) limit 1 ;" , "ii" , $UserID , $placeID );
		if ( $row !== false ) {
			$rights = ParseRights( strtoupper( $row[ "rights" ] ) );
		} else {
			$rights = array();
		}
		
		$UserRights[ $placeID ] = $rights ;
		
		return $rights ;
	}
	
	function ParseRights( $rights ) {
		$res = array();
		$r = trim( $rights );
		$r = trim( $r , ";" );
		$r = explode( ";" , $r );
		for ( $i = 0 ; $i < count( $r ) ; $i++ ) {
			$r[ $i ] = trim( $r[ $i ] );
			$r[ $i ] = trim( $r[ $i ] , "=" );
			$rr = explode( "=" , $r[ $i ] );
			$crr = count( $rr );
			if ( $crr > 0 ) {
				$s= $rr[ $crr - 1 ];
				$s= trim( $s );
				$s= trim( $s , "/" );
				$ss= explode( "/" , $s );
				for ( $ii = 0 ; $ii < count( $ss ) ; $ii++ ) {
					$ss[ $ii ] = trim( $ss[ $ii ] );
				}
			}
			
			if ( $crr == 1 ) {
				$res = array_merge( $res , $ss );
			} else {
				for ( $ii = 0 ; $ii < $crr - 1 ; $ii++ ) {
					$rr[ $ii ] = trim( $rr[ $ii ] );
					$res[ $rr[ $ii ] ] = $ss ;
				}
			}
		}
		return $res ;
	}
	
	function ParseRightsToFlat( $PlaceID ) {
		global $UserRights , $placesDescr ;
		$rights =	strtoupper( $UserRights[ 0 ] );
		$rightsDescr = ParseRightsDescriptionNew( $placesDescr[ $PlaceID ][ "rights_description" ] );
		$res = array();
		$r = trim( $rights );
		$r = trim( $r , ";" );
		$r = explode( ";" , $r );
		foreach( $r as $ri ) {
			$ri = trim( $ri );
			$ri = trim( $ri , "=" );
			$rr = explode( "=" , $ri );
			$crr = count( $rr );
			if ( $crr > 0 ) {
				$s= $rr[ $crr - 1 ];
				$s= trim( $s );
				$s= trim( $s , "/" );
				$ss= explode( "/" , $s );
				for ( $ii = 0 ; $ii < count( $ss ) ; $ii++ ) {
					$ss[ $ii ] = trim( $ss[ $ii ] );
				}
			}
			
			if ( $crr == 1 ) {
				$res = array_merge( $res , $ss );
			} else {
				for ( $ii = 0 ; $ii < $crr - 1 ; $ii++ ) {
					$rr[ $ii ] = trim( $rr[ $ii ] );
					$res[ $rr[ $ii ] ] = $ss ;
				}
			}
		}
		
		foreach( $res as $k => &$v ) {
			if ( isset( $rightsDescr[ $k ] ) ) {
				switch( $rightsDescr[ $k ][ "T" ] ) {
					case "BF" :
						$v = array_fill_keys( $v , true );
						break ;
					
					case "A" :
					case "L" :
						$v = $v[ 0 ];
						break ;
				}
			}
		} unset( $v );
		$res2 = array();
		toFlat( $res , "" , $res2 );
		return $res2 ;
	}
	
	function ParseRightsDescription( $rights ) {
		$res = array();
		$a = trim( $rights );
		$a = trim( $a , ";" );
		$as = splitEx( ";" , $a );
		for ( $i = 0 ; $i < count( $as ) ; $i++ ) {
			$b = trim( $as[ $i ] );
			$p = strpos( $b , "=" );
			$bs = splitEx( "=" , $b , 2 );
			$bsl = count( $bs );
			
			$r = $bs[ $bsl - 1 ];
			$r = trim( $r );
			$rl = strlen( $r );
			
			$pdd = array();
			
			if ( $r[ 0 ] == "{" && $r[ $rl - 1 ] == "}" ) {
				$pdd[ "T" ] = "BF" ;
				$pdd[ "V" ] = array();
				$pdd[ "VD" ] = array();
				$pvl = trim( $r , "{}" );
				$pvs = splitEx( "," , $pvl );
				for ( $k = 0 ; $k < count( $pvs ) ; $k++ ) {
					$pv = trim( $pvs[ $k ] );
					$p = splitEx( "|" , $pv , 2 );
					if ( count( $p ) == 1 ) {
						$p[]= $p[ 0 ];
					}
					$pdd[ "V" ][]= $p[ 0 ];
					$pdd[ "VD" ][]= $p[ 1 ];
				}
			} else
				if ( $r[ 0 ] == "[" && $r[ $rl - 1 ] == "]" ) {
					$pdd[ "T" ] = "A" ;
				} else
					if ( $r[ 0 ] == "(" && $r[ $rl - 1 ] == ")" ) {
						$pdd[ "T" ] = "L" ;
						$pdd[ "V" ] = array();
						$pvl = trim( $r , "()" );
						$pvs = splitEx( "," , $pvl );
						for ( $k = 0 ; $k < count( $pvs ) ; $k++ ) {
							$pv = trim( $pvs[ $k ] );
							$pdd[ "V" ][]= $pv ;
						}
					} else {
						$pdd[ "T" ] = "UNDEFINED" ;
					}
			
			for ( $k = 0 ; $k < $bsl - 1 ; $k++ ) {
				$p = splitEx( "|" , $bs[ $k ] , 2 );
				if ( count( $p ) == 1 ) {
					$p[]= $p[ 0 ];
				}
				$pdd[ "N" ] = $p[ 0 ];
				$pdd[ "D" ] = $p[ 1 ];
				$res[]= $pdd ;
			}
		}
		return $res ;
	}
	
	function ParseRightsDescriptionNew( $rights ) {
		$resNew = array();
		$a = trim( $rights );
		$a = trim( $a , ";" );
		$as = splitEx( ";" , $a );
		
		foreach( $as as $asv ) {
			$b = trim( $asv );
			$p = strpos( $b , "=" );
			$bs = splitEx( "=" , $b , 2 );
			$bsl = count( $bs );
			
			$r = $bs[ $bsl - 1 ];
			$r = trim( $r );
			$rl = strlen( $r );
			
			$pdd = array();
			
			if ( $r[ 0 ] == "{" && $r[ $rl - 1 ] == "}" ) {
				$pdd[ "T" ] = "BF" ;
				$pdd[ "V" ] = array();
				//$pdd[ "VD" ] = array();
				$pvl = trim( $r , "{}" );
				$pvs = splitEx( "," , $pvl );
				for ( $k = 0 ; $k < count( $pvs ) ; $k++ ) {
					$pv = trim( $pvs[ $k ] );
					$p = splitEx( "|" , $pv , 2 );
					if ( count( $p ) == 1 ) {
						$p[]= $p[ 0 ];
					}
					$pdd[ "V" ][ strtoupper( $p[ 0 ] ) ] = $p[ 1 ];
				}
			} else
				if ( $r[ 0 ] == "[" && $r[ $rl - 1 ] == "]" ) {
					$pdd[ "T" ] = "A" ;
				} else
					if ( $r[ 0 ] == "(" && $r[ $rl - 1 ] == ")" ) {
						$pdd[ "T" ] = "L" ;
						$pdd[ "V" ] = array();
						$pvl = trim( $r , "()" );
						$pvs = splitEx( "," , $pvl );
						for ( $k = 0 ; $k < count( $pvs ) ; $k++ ) {
							$pv = trim( $pvs[ $k ] );
							$pdd[ "V" ][]= strtoupper( $pv );
						}
					} else {
						$pdd[ "T" ] = "UNDEFINED" ;
					}
			
			for ( $k = 0 ; $k < $bsl - 1 ; $k++ ) {
				$p = splitEx( "|" , $bs[ $k ] , 2 );
				if ( count( $p ) == 1 ) {
					$p[]= $p[ 0 ];
				}
				$pdd[ "D" ] = $p[ 1 ];
				$resNew[ strtoupper( $p[ 0 ] ) ] = $pdd ;
			}
		}
		return $resNew ;
	}
	
	const RIGHTS_OR  = "OR" ;
	const RIGHTS_AND = "AND" ;
	
	function checkAccess( $pr , $ar , $op = RIGHTS_AND ) {
		if ( !is_array( $ar ) ) {
			$ar = strexp( "".$ar );
		}
		
		if ( $op == RIGHTS_OR ) {
			$r = false ;
			foreach ( $ar as $k ) {
				$r = $r || ( isset( $pr[ $k ] ) && ( $pr[ $k ] == true ) );
			}
		} else {
			$r = true ;
			foreach ( $ar as $k ) {
				$r = $r && ( isset( $pr[ $k ] ) && ( $pr[ $k ] == true ) );
			}
		}
		
		return $r ;
	}


