<?php
	
	$LoginOk = false ;
	
	function TryLoginFromCookie( $placeId = -1 ) {
		global	$portalDB , $dbConfig ;
		global	$UserLogin , $UserName ,
				  $UserID , $UserLastVisitDate , $UserLastVisitTime ,
				  $UserDepartment , $UserPost , $UserSpecialityNumber ,
				  $UserRights , $UserOptions , $UserThemeLoc , $UserGroups ;
		global	$Err , $LoginOk ;
		global  $UserWorkerID , $UserWorkerFirstID , $UserAllWorkers , $UserAllDeps , $DepAllWorkers ;
		
		$LoginOk = false ;
		$Err = false ;
		
		if ( $dbConfig[ "engine.mode" ] == "ADMIN" ) {
			$portalAdmins = explode( ";" , strtolower( $dbConfig[ "engine.admin" ] ) );
			if ( !in_array( $_COOKIE[ "uLogin" ] , $portalAdmins ) ) {
				$Err = 800 ;
				return ;
			}
		}
		
		if ( isset( $_COOKIE[ "uLogin" ] ) && ( $_COOKIE[ "uLogin" ] != "" ) ) {
		} else {
			$Err = 100 ;
			return ;
		}
		$UserLogin = $_COOKIE[ "uLogin" ];
		
		if ( isset( $_COOKIE[ "uPassword" ] ) && ( $_COOKIE[ "uPassword" ] != "" ) ) {
		} else {
			$Err = 101 ;
			return ;
		}
		$UserPassword = $_COOKIE[ "uPassword" ];

		$row = $portalDB->row( "select `t1`.* from `accounts` as `t1` , `workers-no-spec` as `t2` where ( `t2`.`actual` = 1 ) and ( `t1`.`worker_id` = `t2`.`id` ) and ( `t1`.`login` = ? )" , "s" , strtolower( $UserLogin ) );
		if ( $row === false  ) {
			$Err = 102 ;
			return ;
		}
		
		$ipList = array_fill_keys( strexp( $row[ "ip" ] ) , true );
		if ( ( $row[ "any_ip" ] == 1 ) || isset( $ipList[ $_SERVER[ "REMOTE_ADDR" ] ] ) ) {
		} else {
			$Err = 104 ;
			return ;
		}
		
		if ( $row[ "pass" ] == $UserPassword ) {
		} else {
			$Err = 103 ;
			return ;
		}
		
		$LoginOk = true ;
		$UserID = $row[ "id" ];
		$UserGroups = explode( ";" , trim( strtolower( $row[ "groups" ] ) ) );
		
		$UserWorkerID = $row[ "worker_id" ];
		$row2 = $portalDB->row( "select * from `workers-no-spec` where `id` = ?" , "i" , $row[ "worker_id" ] );
		$UserName             = $row2[ "name" ];
		$UserDepartment       = $row2[ "dep" ];
		$UserPost             = $row2[ "post_1_id" ];
		$UserSpecialityNumber = $portalDB->query( "select `spec_id` from `workers-spec` where `worker_id` = ?" , false , "i" , $row[ "worker_id" ] );
		$UserSpecialityNumber = implode( ',' , array_unique( array_column( $UserSpecialityNumber , 'spec_id' ) ) );
		$UserWorkerFirstID    = $row2[ "first_id" ];

		$uaw = $portalDB->query( "select `id` , `dep` from `workers-no-spec` where `first_id` = ?" , false , "i" , $row2[ "first_id" ] );
		$UserAllWorkers = Array();
		$UserAllDeps = Array();
		foreach( $uaw as &$r ) {
			$UserAllWorkers[]= $r[ "id" ];
			$UserAllDeps[]= $r[ "dep" ];
		}
		unset( $r );

		$daw = array_keys( $portalDB->query( "select `first_id` from `workers-no-spec` where ( `dep` <=> ? ) and ( `actual` <=> 1 )" , "first_id" , "i" , $UserDepartment ) );
		$DepAllWorkers = array_keys( $portalDB->query( "select `id` from `workers-no-spec` where ( `first_id` in ( ?* ) )" , "id" , "*i" , $daw ) );

		$row2 = $portalDB->row( "select `location` from `themes` where `id` = ? limit 1" , "i" , $row[ "theme" ] );
		if ( $row2 !== false ) {
			$UserThemeLoc = $row2[ "location" ] ;
		} else {
			$UserThemeLoc = "std0" ;
		}
		
		if ( isset( $_COOKIE[ "uLastVisitDate" ] ) && isset( $_COOKIE[ "uLastVisitTime" ] ) ) {
			$UserLastVisitDate = $_COOKIE[ "uLastVisitDate" ];
			$UserLastVisitTime = $_COOKIE[ "uLastVisitTime" ];
		} else {
			$UserLastVisitDate = date( "d-m-Y" , strtotime( $row[ "last_visit_date" ] ) );
			$UserLastVisitTime = date( "H:i" , strtotime( $row[ "last_visit_time" ] ) );
			setcookie( "uLastVisitDate" , $UserLastVisitDate , null , "/" , "" , "0" );
			setcookie( "uLastVisitTime" , $UserLastVisitTime , null , "/" , "" , "0" );
		}
		
		$Temp = date( "d-m-Y" , time() );
		$UserLastVisitDate = ( $UserLastVisitDate == $Temp ? "сегодня" : $UserLastVisitDate );
		$Temp = date( "d-m-Y" , mktime( 0 , 0 , 0 , date( "m" ) , date( "d" ) - 1 , date( "Y" ) ) );
		$UserLastVisitDate = ( $UserLastVisitDate == $Temp ? "вчера" : $UserLastVisitDate );
		$portalDB->noResult( "update `accounts` set `last_visit_date` = ? , `last_visit_time` = ? where `id` = ? limit 1" , "ssi" , PrepDate( date( "d-m-Y" , time() ) ) , date( "H:i" , time() ) , $UserID );

		$row2 = $portalDB->row( "select `rights` from `access-rights` where ( `user_id` = ? ) and ( `place` = ? )" , "ii" , $UserID , $placeId );
		if ( $row2 !== false ) {
			$UserRights[ 0 ] = $row2[ "rights" ];
		}

		$UserOptions = $portalDB->query( "select * from `options` where ( `user_id` = ? )" , "op_name" , "i" , $UserID );
	}
	
	function TryLoginFromPost( $placeId = -1 ) {
		global	$portalDB , $dbConfig ;
		global	$UserLogin , $UserName ,
				  $UserID , $UserLastVisitDate , $UserLastVisitTime ,
				  $UserDepartment , $UserPost , $UserSpecialityNumber ,
				  $UserRights , $UserOptions , $UserThemeLoc , $UserGroups ;
		global	$Err , $LoginOk ;
		global  $UserWorkerID , $UserWorkerFirstID , $UserAllWorkers , $UserAllDeps , $DepAllWorkers ;
		
		$LoginOk = false ;
		$Err = false ;
		
		if ( $dbConfig[ "engine.mode" ] == "ADMIN" ) {
			$portalAdmins = explode( ";" , strtolower( $dbConfig[ "engine.admin" ] ) );
			if ( !in_array( $_POST[ "uLogin" ] , $portalAdmins ) ) {
				$Err = 800 ;
				return ;
			}
		}
		
		if ( isset( $_POST[ "uLogin" ] ) && ( $_POST[ "uLogin" ] != "" ) ) {
		} else {
			$Err = 100 ;
			return ;
		}
		$UserLogin = $_POST[ "uLogin" ];
		
		if ( isset( $_POST[ "uPassword" ] ) && ( $_POST[ "uPassword" ] != "" ) ) {
		} else {
			$Err = 101 ;
			return ;
		}
		$UserPassword = $_POST[ "uPassword" ];
		
		$row = $portalDB->row( "select `t1`.* from `accounts` as `t1` , `workers-no-spec` as `t2` where ( `t2`.`actual` = 1 ) and ( `t1`.`worker_id` = `t2`.`id` ) and ( `t1`.`login` = ? )" , "s" , strtolower( $UserLogin ) );
		
		if ( $row === false  ) {
			$Err = 102 ;
			return ;
		}
		
		
		$ipList = array_fill_keys( strexp( $row[ "ip" ] ) , true );
		if ( ( $row[ "any_ip" ] == 1 ) || isset( $ipList[ $_SERVER[ "REMOTE_ADDR" ] ] ) ) {
		} else {
			$Err = 104 ;
			return ;
		}
		
		$UserOptions = $portalDB->query( "select * from `options` where ( `user_id` = ? )" , 'op_name' , 'i' , $row[ 'id' ] );
		if ( isset( $UserOptions[ 'kuvk.pass' ] ) && $UserOptions[ 'kuvk.pass' ][ 'op_value' ] == '{MD5}'.base64_encode( md5( iconv( 'cp1251' , 'utf8' , $UserPassword ) , true ) ) ) {
			$row[ 'pass' ] = base64_encode( sha1( $UserPassword ) );
			$portalDB->updateRow( 'accounts' , array( 'pass' => $row[ 'pass' ] , 'id' => $row[ 'id' ] ) );
			$portalDB->noResult( "delete from `options` where ( `op_name` = 'kuvk.pass' ) and ( `user_id` = ? )" , 'i' , $row[ 'id' ] );
		}
		
		if ( !is_null( $row[ 'otp-core' ] ) ) {
			$sk = base64_decode( $row[ 'otp-core' ] );
			$tvp = false ;
			for( $c = -4 ; $c <= 4 ; $c++  ) {
				$vp = generateOTP( $sk , $c * 30 );
				if ( $vp == $UserPassword ) {
					$tvp = true ;
					break ;
				}
			}
			if ( $tvp ) {
			} else {
				$Err = 103 ;
				return ;
			}
		} else
			if ( $row[ "pass" ] == base64_encode( sha1( $UserPassword ) ) ) {
			} else {
				$Err = 103 ;
				return ;
			}
		
		$cookieDomain = $dbConfig[ 'engine.addresses.cookieDomain' ];
		$storeDate = intval( date_create()->modify( "+5 year" )->format( "U" ) );
		setcookie( "uLogin"    , $UserLogin     , $storeDate , "/" , $cookieDomain , "0" );
		setcookie( "uPassword" , $row[ "pass" ] , $storeDate , "/" , $cookieDomain , "0" );
		
		$LoginOk = true ;
		$UserID = $row[ "id" ];
		$UserGroups = explode( ";" , trim( strtolower( $row[ "groups" ] ) ) );
		
		$UserWorkerID = $row[ "worker_id" ];
		$row2 = $portalDB->row( "select * from `workers` where `id` = ?" , "i" , $row[ "worker_id" ] );
		$UserName             = $row2[ "name" ];
		$UserDepartment       = $row2[ "dep" ];
		$UserPost             = $row2[ "post_1_id" ];
		$UserSpecialityNumber = $row2[ "spec" ];
		$UserWorkerFirstID    = $row2[ "first_id" ];

		$uaw = $portalDB->query( "select `id` , `dep` from `workers` where `first_id` = ?" , false , "i" , $row2[ "first_id" ] );
		$UserAllWorkers = Array();
		$UserAllDeps = Array();
		foreach( $uaw as &$r ) {
			$UserAllWorkers[]= $r[ "id" ];
			$UserAllDeps[]= $r[ "dep" ];
		} unset( $r );

		$daw = $portalDB->query( "select `first_id` from `workers` where ( `dep` <=> ? ) and ( `actual` <=> 1 )" , false , "i" , $UserDepartment );
		foreach( $daw as &$r ) {
			$tmp = $portalDB->row( "select group_concat( cast( `id` as CHAR ) separator ',' ) as `ids` from `workers` where ( `first_id` <=> ? )" , "i" , $r[ "first_id" ] );
			$DepAllWorkers = array_merge( $DepAllWorkers , explode( "," , $tmp[ "ids" ] ) );
		} unset( $r );
		
		$row2 = $portalDB->row( "select `location` from `themes` where `id` = ? limit 1" , "i" , $row[ "theme" ] );
		if ( $row2 !== false ) {
			$UserThemeLoc = $row2[ "location" ] ;
		} else {
			$UserThemeLoc = "std0" ;
		}
		
		if ( isset( $_COOKIE[ "uLastVisitDate" ] ) && isset( $_COOKIE[ "uLastVisitTime" ] ) ) {
			$UserLastVisitDate = $_COOKIE[ "uLastVisitDate" ];
			$UserLastVisitTime = $_COOKIE[ "uLastVisitTime" ];
		} else {
			$UserLastVisitDate = date( "d-m-Y" , strtotime( $row[ "last_visit_date" ] ) );
			$UserLastVisitTime = date( "H:i" , strtotime( $row[ "last_visit_time" ] ) );
			setcookie( "uLastVisitDate" , $UserLastVisitDate , null , "/" , "" , "0" );
			setcookie( "uLastVisitTime" , $UserLastVisitTime , null , "/" , "" , "0" );
		}
		
		$Temp = date( "d-m-Y" , time() );
		$UserLastVisitDate = ( $UserLastVisitDate == $Temp ? "сегодня" : $UserLastVisitDate );
		$Temp = date( "d-m-Y" , mktime( 0 , 0 , 0 , date( "m" ) , date( "d" ) - 1 , date( "Y" ) ) );
		$UserLastVisitDate = ( $UserLastVisitDate == $Temp ? "вчера" : $UserLastVisitDate );
		$portalDB->noResult( "update `accounts` set `last_visit_date` = ? , `last_visit_time` = ? where `id` = ? limit 1" , "ssi" , PrepDate( date( "d-m-Y" , time() ) ) , date( "H:i" , time() ) , $UserID );

		$row2 = $portalDB->row( "select `rights` from `access-rights` where ( `user_id` = ? ) and ( `place` = ? ) limit 1 ;" , "ii" , $UserID , $placeId );
		if ( $row2 !== false ) {
			$UserRights[ 0 ] = $row2[ "rights" ] ;
		}
	}
	
	$RegisterOk = false ;
	
	function TryRegister() {
		global $portalDB ,
		$Err ,
		$RegisterOk ;
		
		$RegisterOk = false ;
		$Err = false ;

		if ( isset( $_POST[ 'uLogin' ] ) && ( trim( $_POST[ 'uLogin' ] ) != '' ) ) {
			$uLogin = strtolower( trim( $_POST[ 'uLogin' ] ) );
			$row = $portalDB->simpleRow( 'accounts' , array( 'login' => $uLogin ) );
			if ( $row !== false ) {
				$Err = 105 ;
				return ;
			}
		} else {
			$Err = 100 ;
			return ;
		}
		
		if ( isset( $_POST[ 'uPassword' ] ) && ( trim( $_POST[ 'uPassword' ] ) != '' ) ) {
			$uPassword = trim( $_POST[ 'uPassword' ] );
		} else {
			$Err = 101 ;
			return ;
		}
		
		if ( isset( $_POST[ 'uWorker' ] ) && ( intval( $_POST[ 'uWorker' ] , 10 ) > 0 ) ) {
			$WorkerID = $_POST[ "uWorker" ];
		} else {
			$Err = 106 ;
			return ;
		}
		
		if ( isset( $_POST[ 'uTheme' ] ) && ( intval( $_POST[ 'uTheme' ] , 10 ) > 0 ) ) {
			$UserThemeID = $_POST[ 'uTheme' ];
		} else {
			$Err = 107 ;
			return ;
		}
		
		$UserIP = ip2long( $_SERVER[ 'REMOTE_ADDR' ] );

		$portalDB->insertRow( 'accounts' , array(
			'login'     => strtolower( $uLogin ) ,
			'ip'        => $UserIP ,
			'pass'      => base64_encode( sha1( $uPassword ) ) ,
			'any_ip'    => 1 ,
			'worker_id' => $WorkerID ,
			'theme'     => $UserThemeID ,
			'guid'      => '' ,
			'mac_addr'  => ''
		) );

		
		$RegisterOk = true ;
	}
	
	function generateOTP( $k , $c = false ) {
		if ( $c !== false ) {
			$counter  = floor( ( time() + intval( $c.'' , 10 ) ) / 30 );
		} else {
			$counter  = floor( time() / 30 );
		}
		$bytes = '' ;
		for( $i = 0 ; $i < 8 ; $i++ ) {
			$bytes = chr( $counter & 0xff ).$bytes ;
			$counter = $counter >> 8 ;
		}
		$hs = unpack( 'C*' , hash_hmac( 'sha1' , $bytes , $k , true ) );
		$n = ( $hs[ 20 ] & 0xf ) + 1 ;
		$result = ( $hs[ $n ] << 24 | $hs[ $n + 1 ] << 16 | $hs[ $n + 2 ] << 8 | $hs[ $n + 3 ] ) & 0x7fffffff ;
		return substr( $result , -6 );
	}

