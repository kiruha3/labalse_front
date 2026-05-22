<?php
	/**
	 * @var string $dbHost
	 * @var string $dbUser
	 * @var string $dbPassword
	 * @var string $dbDatabase
	 */

	$portalDB = new TDB( $dbHost , $dbUser , $dbPassword , $dbDatabase );
	
	function OpenDB( $host = "localhost" , $user , $pass , $db , $codepage = "cp1251" ) {
		global $locale ;
		$con = mysql_connect( $host, $user, $pass, true ) or die( $locale[ "Database" ][ "ConnectES" ].mysql_error().$locale[ "Database" ][ "ConnectEE" ] );
		mysql_select_db( $db, $con ) or die( $locale[ "Database" ][ "SelectDBES" ].mysql_error().$locale[ "Database" ][ "SelectDBEE" ] );
		$res = mysql_query( "set names $codepage;", $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ] );
		return $con ;
	}
	
	function RowAsArray( $con , $query , $type = MYSQL_ASSOC ) {
		global $locale ;
		
		$res = mysql_query( $query , $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ]." : ".$query );
		if ( $row = mysql_fetch_array( $res , $type ) ) {
			$result = $row ;
		} else {
			$result = false ;
		}
		
		mysql_free_result( $res );
		return $result ;
	}
	
	function RowAsObject( $con , $query ) {
		global $locale ;
		$res = mysql_query( $query, $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ]." : ".$query );
		if ( $row = mysql_fetch_object( $res ) )
		{
			$result = $row;
		}
		else
		{
			$result = false;
		}
		mysql_free_result( $res );
		return $result;
	}
	
	/*function TableAsArray( $con , $TableName , $fIN = false ) {
		global $locale ;
		$result = array();
		$q = "select * from `".$TableName."`;" ;
		$res = mysql_query( $q , $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ]." : ".$query );
		if ( $fIN === false ) {
			while ( $row = mysql_fetch_array( $res , MYSQL_ASSOC ) ) {
				$result[]= $row;
			}
		} else {
			while ( $row = mysql_fetch_array( $res , MYSQL_ASSOC ) ) {
				$result[ $row[ $fIN ] ] = $row;
			}
		}

		mysql_free_result( $res );
		return $result ;
	}*/
	
	/*function TableAsObjectArray( $con , $TableName ) {
		global $locale ;
		$result = array();
		$q = "select * from `".$TableName."`;" ;
		$res = mysql_query( $q , $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ]." : ".$query );
		while ( $row = mysql_fetch_object( $res ) ) {
			$result[]= $row ;
		}

		mysql_free_result( $res );
		return $result ;
	}*/
	
	function QueryAsArray( $con , $query , $fIN = false ) {
		global $locale ;
		$result = array();
		
		$res = mysql_query( $query , $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ]." : ".$query );
		
		if ( $fIN === false ) {
			while ( $row = mysql_fetch_array( $res , MYSQL_ASSOC ) ) {
				$result[]= $row ;
			}
		} else {
			while ( $row = mysql_fetch_array( $res , MYSQL_ASSOC ) ) {
				$result[ $row[ $fIN ] ] = $row ;
			}
		}
		
		mysql_free_result( $res );
		return $result ;
	}
	
	function QueryAsTreeArray( $con , $query , $fIN , $di = false , $extract = false ) {
		global $locale ;
		
		$result = array();
		$res = mysql_query( $query , $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ]." : ".$query );
		while ( $row = mysql_fetch_array( $res , MYSQL_ASSOC ) ) {
			setTreeElement( $result , $fIN , $row , $di , $extract );
		}
		
		mysql_free_result( $res );
		return $result ;
	}
	
	function QueryAsObjectArray( $con , $query ) {
		global $locale ;
		$result = array();
		$res = mysql_query( $query , $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ]." : ".$query );
		while ( $row = mysql_fetch_object( $res ) ) {
			$result[]= $row ;
		}
		
		mysql_free_result( $res );
		return $result ;
	}
	
	function NoResultQuery( $con , $query ) {
		global $locale ;
		mysql_query( $query , $con ) or die( $locale[ "Database" ][ "QueryES" ].mysql_error().$locale[ "Database" ][ "QueryEE" ]." : ".$query );
		return ;
	}
	
	function Str2SQL( $str ) {
		if ( is_array( $str ) ) {
			$res = array();
			foreach( $str as $ae ) {
				$res[]= ( strlen( $ae ) > 0 ?  "0x".bin2hex( iconv( "cp1251" , "utf8" , $ae ) ) : "''" );
			}
			return $res ;
		} else {
			return ( strlen( $str ) > 0 ?  "0x".bin2hex( iconv( "cp1251" , "utf8" , $str ) ) : "''" );
		}
	}
	
	function Int2SQL( $str , $err = 0 ) {
		return isValidInt( $str ) ? intval( $str ) : $err ;
	}
	
	function Float2SQL( $str , $err = 0 ) {
		return isValidFloat( $str ) ? str_replace( "," , "." , $str ) : $err ;
	}
	
	function Date2SQL( $d ) {
		$d = Date2Int( $d );
		if ( $d !== false ) {
			return date( "'Y-m-d'" , $d );
		} else {
			return "NULL" ;
		}
	}


