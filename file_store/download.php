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

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtolower( $UserRights[0] ) );
		if ( array_key_exists( "group" , $Rights ) ) {
			$groups = explode( "," , trim ( $Rights["group"][ 0 ] ) );
			$GoOut = false ;
		} else {
			$groups = array();
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit ;
	}

	if ( isset( $_GET["id"] ) ) {
		$cnid = intval( $_GET["id"] );
	} else {
		$cnid = 0 ;
	}

	$cl = $portalDB->row( "select * from `files` where `id` = ?" , "i" , $cnid );

	if ( $cl === false ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm( "Запрашиваемого файла нет на сервере." );
		closeHtml();
		exit ;
	}

	if ( in_array( strtolower( $cl[ "group" ] ) , $groups ) ) {
		$access_mask = $cl[ "group_access" ];
	} else {
		$access_mask = $cl[ "others_access" ];
	}

	if ( preg_match( "/d/" , $access_mask ) != 1 ) {
	    MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm( "У вас нет прав для скачивания данного файла." );
		closeHtml();
		exit();
	}

	if ( $cl[ "type" ] == "url" ) {
		Redirect( $cl[ "src_name" ] );
	}

	if ( substr( $cl[ "name" ] , -( strlen( $cl[ "type" ] ) + 1 ) ) == ".".$cl[ "type" ] ) {
	} else {
		$cl[ "name" ].= ".".$cl[ "type" ];
	}

	if ( !file_exists( "store/".$cl[ "src_name" ] ) ) {
		header( "HTTP/1.1 404 Not Found" );
		exit();
	}

	if ( isset( $_SERVER[ "HTTP_RANGE" ] ) ) {
		$r = $_SERVER[ "HTTP_RANGE" ];
		if ( substr( $r , 0 , 6 ) == "bytes=" ) {
			$ranges = explode( "," , substr( $r , 6 ) );
			$rl = -1 ;
			$rh = -1 ;
			for ( $i = 0 ; $i < count( $ranges ) ; $i++ ) {
				$cr = explode( "-" , $ranges[ $i ] );
				if ( $cr[ 0 ] == "" ) {
					//
				} else
				if ( $cr[ 1 ] == "" ) {
					$lb = intval( $cr[ 0 ] );
					if ( $lb < $cl[ "size" ] ) {
						$rb = $cl[ "size" ] - 1 ;
						if ( $rl == -1 || $rl > $lb ) {
							$rl = $lb ;
							$rh = $rb ;
						}
					}
				} else {
					$lb = intval( $cr[ 0 ] );
					$rb = intval( $cr[ 1 ] );
					if ( $lb < $rb && $lb < $cl[ "size" ] ) {
						if ( $rb >= $cl[ "size" ] ) {
							$rb = $cl[ "size" ] - 1 ;
						}
						if ( $rl == -1 || $rl > $lb ) {
							$rl = $lb ;
							$rh = $rb ;
						}
					}
				}
			}

			if ( $rl != -1 && $rh != -1 ) {
				header( "HTTP/1.1 206 Partial Content" );
				header( "Content-Length: ".( $rh - $rl + 1 ) );
				header( "Last-Modified: ".date( "D, d M Y H:i:s" , $cl[ "date" ] )." GMT" );
				header( "ETag: \"".$cl[ "id" ].":".$cl[ "date" ]."\"" );
				header( "Content-Type: text/plain" );
				header( "Accept-Ranges: bytes" );
				header( "Connection: close" );
				header( "Content-Disposition: attachment; filename=\"".$cl[ "name" ]."\"" );
				header( "Content-Range: bytes ".$rl."-".$rh."/".$cl[ "size" ] );
				$fh = fopen( "store/".$cl[ "src_name" ] , "rb" );
				fseek( $fh , $rl );

				while( !feof( $fh ) && connection_status() == 0 && $rl < $rh ) {
					set_time_limit( 0 );
					print( fread( $fh , min( $elementSize , $rh - $rl + 1 ) ) );
					flush();
					ob_flush();
					$rl+= $elementSize ;
					fseek( $fh , $rl );
				}
				fclose( $fh );
			}
		} else {
			header( "HTTP/1.1 416 Requested Range Not Satisfiable" );
		}
	} else {
		header( "HTTP/1.1 200 OK" );
		header( "Content-Length: ".$cl[ "size" ] );
		header( "Last-Modified: ".date( "D, d M Y H:i:s" , $cl[ "date" ] )." GMT" );
		header( "ETag: \"".$cl[ "id" ]."*".$cl[ "date" ]."\"" );
		header( "Content-Type: text/plain" );
		header( "Accept-Ranges: bytes" );
		header( "Connection: close" );
		header( "Content-Disposition: attachment; filename=\"".$cl[ "name" ]."\"" );

		$fh = fopen( "store/".$cl[ "src_name" ] , "rb" );

		while( !feof( $fh ) && connection_status() == 0 ) {
			set_time_limit( 0 );
			print( fread( $fh , $elementSize ) );
			flush();
			ob_flush();
		}
		fclose( $fh );
	}
?>