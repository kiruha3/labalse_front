<?php
	if ( isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ) {
		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		$ajaxRequest = simplexml_load_string( $_REQUEST[ "data" ] , 'SimpleXMLElement' , LIBXML_NOCDATA );

		switch ( $ajaxRequest->getName() ) {
			case "get-files-to-upload" :
				call_user_func( function () {
					echo "<result>" ;
					echo "</result>" ;
				} );
				break ;
		}

		exit();
	}
