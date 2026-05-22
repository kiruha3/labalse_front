<?php
    header( 'content-type: application/json;charset=utf-8' );
	require_once( './core.export.const.php' );
    require_once( '../core.php' );
	require_once( '../cores/core.maindb.php' );
	require_once( '../cores/core.globals.php' );
    /**
     * @var TDB $portalDB
     * @var array $dbConfig
     */
    require_once( '../maindb/lconfig.php'  );

    function getIDList2Sync( $tab , $name = false , $opt = array() ) {
        global $portalDB ;
        if ( $name === false ) {
            $name = $tab ;
        }

        if ( isset( $opt[ 'lnk' ] ) ) {
            $id = $opt[ 'lnk' ];
        } else {
            $id = 'id' ;
        }

        $l1 = $portalDB->query( "select `t1`.`id` from `".$tab."` as `t1` left join `kuvk-links` as `t2` on ( ( `t1`.`".$id."` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = '".$name."' ) ) where ( `t2`.`id` is null )" );
        $l2 = $portalDB->query( "select `t1`.`ext_id` as `id` from `kuvk-sync` as `t1` where ( ( `t1`.`ext_type` = '".$name."' ) and ( `t1`.`processed` <> 1 ) )" );
        $res = array();
        foreach ( $l1 as &$i ) {
            $res[]= $i[ 'id' ];
        } unset( $i );
        foreach ( $l2 as &$i ) {
            $res[]= $i[ 'id' ];
        } unset( $i );
        return $res ;
    }

    function getDataWSync( $tab , $name = false , $opt = array()  ) {
        global $portalDB ;
        if ( $name === false ) {
            $name = $tab ;
        }

        if ( isset( $opt[ 'lnk' ] ) ) {
            $id = $opt[ 'lnk' ];
        } else {
            $id = 'id' ;
        }

        $did = getIDList2Sync( $tab , $name , $opt );
        if ( count( $did ) > 0 ) {
            $res = $portalDB->query(
                "select `t1`.* , `t2`.`kuvk-guid` , ( `t1`.`id` in ( ?* ) ) as `2sync` from `".$tab."` as `t1`
                    left join `kuvk-links` as `t2` on ( ( `t1`.`".$id."` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = '".$name."' ) )" ,
                'id' , '*i' , $did );
        } else {
            $res = $portalDB->query(
                "select `t1`.* , `t2`.`kuvk-guid` , 0 as `2sync` from `".$tab."` as `t1`
                    left join `kuvk-links` as `t2` on ( ( `t1`.`".$id."` = `t2`.`ext_id` ) and ( `t2`.`ext_type` = '".$name."' ) )" ,
                'id' );
        }

        return $res ;
    }

    function cvtArr( &$arr , $col ) {
        if ( is_array( $col ) ) {

        } else {
            $col = array( $col );
        }
        foreach ( $col as $c ) {
            foreach ( $arr as &$i ) {
                $i[ $c ] = cvt( $i[ $c ] );
            } unset( $i );
        }
    }

    function unsetArr( &$arr , $col ) {
        if ( is_array( $col ) ) {
        } else {
            $col = array( $col );
        }
        foreach ( $col as $c ) {
            foreach ( $arr as &$i ) {
                unset( $i[ $c ] );
            } unset( $i );
        }
    }

    function sendResult( $res ) {
        $res = json_encode( $res , JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
        if ( $res == '' ) {
            echo json_encode( array( 'success' => false , 'error' => json_last_error() ) );
        } else {
            echo $res ;
        }
    }



