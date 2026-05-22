<?php
    header( "content-type: application/json;charset=utf-8" );

    require_once( "../core.php" );

    $res = array( "success" => true );

    $data = file_get_contents( "php://input" );
    $data = json_decode( $data , true );

    if ( $data !== false && isset( $data[ "type" ] ) && isset( $data[ "id" ] ) && isset( $data[ "guid" ] ) ) {
        switch ( $data[ "type" ] ) {
            case "params" :
                switch ( $data[ "id" ] ) {
                    case "runType" :
                        $portalDB->noResult( "update `kuvk-params` set `value` = ? where `name` = 'runType'" , 's' , $data[ 'guid' ] );
                        break ;
                }
                break ;

            case "agent" :
            case "agency" :
            case "matincoming" :
            case "specialities" :
            case "specialities-groups" :
            case "workers" :
            case "departments" :
            case "posts" :
            case "staffing" :
            case "staffing-workers" :
            case "workers-spec" :
            case "correspondence" :
                $portalDB->noResult( "insert `kuvk-links` ( `ext_type` , `ext_id` , `kuvk-guid` ) values( ? , ? , ? )" , "sss" , $data[ "type" ] , $data[ "id" ] , $data[ "guid" ] );
                break ;
        }
    }

    $res = json_encode( $res , JSON_UNESCAPED_UNICODE );
    echo $res ;
