<?php
	require_once( 'core.export.php' );
	/**
	 * @var TDB $portalDB
	 */

	$res = array( 'success' => true );

	$data = file_get_contents( 'php://input' );
	$data = json_decode( $data , true );

	if ( $data !== false && isset( $data[ 'type' ] ) && isset( $data[ 'id' ] ) && isset( $data[ 'guid' ] ) ) {
		switch ( $data[ 'type' ] ) {
			case 'params' :
				switch ( $data[ 'id' ] ) {
					case 'runType' :
						$portalDB->noResult( "update `kuvk-params` set `value` = ? where `name` = 'runType'" , 's' , $data[ 'guid' ] );
						break ;
				}
				break ;

			case KUVK_LINK_AGENT :
			case KUVK_LINK_AGENCY :
			case KUVK_LINK_MATINCOMING :
			case KUVK_LINK_MATINCOMING_C23 :
			case KUVK_LINK_SPECIALITIES :
			case KUVK_LINK_SPECIALITIES_GROUPS :
			case KUVK_LINK_WORKERS :
			case KUVK_LINK_DEPARTMENTS :
			case KUVK_LINK_POSTS :
			case KUVK_LINK_STAFFING :
			case KUVK_LINK_STAFFING_WORKERS :
			case KUVK_LINK_WORKERS_SPEC :
			case KUVK_LINK_CORRESPONDENCE :
			case KUVK_LINK_SUBPOENA :
			case KUVK_LINK_EQUIPMENT :
				$portalDB->noResult( "insert `kuvk-links` ( `ext_type` , `ext_id` , `kuvk-guid` ) values( ? , ? , ? )" , 'sss' , $data[ 'type' ] , $data[ 'id' ] , $data[ 'guid' ] );
				break ;
		}
	}

	sendResult( $res );

	/*$res = json_encode( $res , JSON_UNESCAPED_UNICODE );
	echo $res ;*/