<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "core.php" );
	//include_once( "lconfig.php" );

	if ( isset( $_REQUEST[ "n" ] ) ) {
		$req = base64_decode( $_REQUEST[ "n" ] );
		$signature = substr( $req , -256 );
		$encData = substr( $req , 0 , -256 );
		$portalPK = openssl_pkey_get_private( file_get_contents( "keys/portal.key" ) );
		$docsCert = openssl_x509_read( file_get_contents( "keys/docs.crt" ) );

		$data = "" ;
		for( $i = 0 ; $i < strlen( $encData ) ; $i+= 256 ) {
			$res = openssl_private_decrypt( substr( $encData , $i , 256 ) , $dataPart , $portalPK );
			if ( $res === false ) {
				header( "HTTP/1.0 500 Internal Server Error" );
				echo "corrupted enc data" ;
				exit();
			}
			$data.= $dataPart ;
		}
		$res = openssl_verify( $data , $signature , $docsCert , OPENSSL_ALGO_SHA1 );
		$data = json_decode( $data , true );

		if ( !isset( $data[ "ntype" ] ) ) {
			$resData = array( "result" => "error" , "msg" => "ntype undefined" );
		} else {
			switch( $data[ "ntype" ] ) {
				case "new-file" :
					if ( !isset( $data[ "pack-data" ] ) ) {
						$data[ "pack-data" ] = "" ;
					} else {
						if ( is_array( $data[ "pack-data" ] ) ) {
							$data[ "pack-data" ] = json_encode( $data[ "pack-data" ] );
						}
					}
					$portalDB->noResult(
						"insert into `documents` ( `ext_type` , `ext_id` , `name` , `time` , `ctrl` , `size` , `orig-id` , `pack-data` ) values ( ? , ? , ? , ? , ? , ? , ? , ? )" ,
						"sssisiss" , $data[ "type" ] , $data[ "ext_id" ] , iconv( "utf8" , "cp1251" , $data[ "name" ] ) , $data[ "time" ] , $data[ "ctrl" ] , $data[ "size" ] , $data[ "orig-id" ] , $data[ "pack-data" ] );
					$resData = array( "result" => "ok" );
					break ;

				default :
					$resData = array( "result" => "error" , "msg" => "unknown handler for event \"".$data[ "ntype" ]."\"" );
					break ;
			}
		}

		$data = json_encode( $resData );
		$encData = "" ;
		$res = openssl_public_encrypt( $data , $encData , $docsCert );

		$signature = "" ;
		$res = openssl_sign( $data , $signature , $portalPK , OPENSSL_ALGO_SHA1 );
		echo base64_encode( $encData.$signature );
		exit();
	}
