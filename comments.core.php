<?php
	namespace Comments ;
	require_once( "core.php" );

	$modeAjax = isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ;

	TryLoginFromCookie( -1 );
	if ( !$LoginOk ) {
		if ( !$modeAjax ) {
			Redirect( "../auth.php" );
		} else {
			exit();
		}
	}

	if ( $modeAjax ) {
		$ajaxRequest = simplexml_load_string( $_REQUEST[ "data" ] , 'SimpleXMLElement' , LIBXML_NOCDATA );

		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		switch ( $ajaxRequest->getName() ) {
			case "get-comments-for" :
				//echo count( $ajaxRequest->item )."\r\n" ;
				$idList = array();
				$items = $ajaxRequest->item ;
				$itemsCount = count( $items );
				$aepl = array();
				for( $i = 0 ; $i < $itemsCount ; $i++ ) {
					//var_dump( $items[ $i ] );
					$cItem = $items[ $i ];
					$cit = ( string ) $cItem[ "type" ];
					$ciid = ( string ) $cItem[ "id" ];
					$mae = filter_var( $cItem[ "auto-edit" ] , FILTER_VALIDATE_BOOLEAN );
					if ( !isset( $idList[ $cit ] ) ) {
						$idList[ $cit ] = array();
					}
					$idList[ $cit ][]= $ciid ;
					if ( $mae ) {
						if ( !isset( $aepl[ $cit ] ) ) {
							$aepl[ $cit ] = array();
						}
						$aepl[ $cit ][ $ciid ] = 1 ;
					}
				}

				$tabWorkers = $portalDB->table( "workers-no-spec" , "id" );
				foreach( $tabWorkers as &$w ) {
					$w = NAMES_Format( NAMES_parse( $w[ "name" ] ) );
				} unset( $w );

				echo "<result>" ;

				$idListTC = $idList ;
				$idList = array( "comments" => array() );

				while ( $idListTC !== false ) {
					foreach( $idListTC as $t => $idl ) {
						$comments = $portalDB->query( "select * from `expertize-comments` where ( `ext_type` = ? ) and ( `ext_id` in ( ?* ) ) order by `date` asc" , false , "s*s" , $t , $idl );
						foreach ( $comments as $cc ) {
							if ( isset( $aepl[ $t ] ) && isset( $aepl[ $t ][ $cc[ "ext_id" ] ] ) ) {
								$ae = in_array( $cc[ "exp_id" ] , $UserAllWorkers ) ? 1 : 0 ;
							} else {
								$ae = 0 ;
							}
							echo "<comment id=\"".$cc[ "id" ]."\" etype=\"".$t."\" eid=\"".$cc[ "ext_id" ]."\" date=\"".$cc[ "date" ]."\" date_s=\"".date( "d-m-Y H:i" , $cc[ "date" ] )."\" exp=\"".$cc[ "exp_id" ]."\" exp_s=\"".$tabWorkers[ $cc[ "exp_id" ] ]."\" auto_edit=\"".$ae."\" rights=\"{&quot;add&quot;:1,&quot;edit&quot;:1,&quot;del&quot;:1}\">".toCDATA( $cc[ "comment" ] )."</comment>" ;
							$idList[ "comments" ][]= $cc[ "id" ];
						}
					}
					if ( count( $idList[ "comments" ] ) == 0 ) {
						$idListTC = false ;
					} else {
						$idListTC = $idList ;
						$idList[ "comments" ] = array();
					}
				}
				echo "</result>" ;
				break ;
		}

	}
