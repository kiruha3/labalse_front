<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( '../core.php' );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( isset( $_REQUEST[ 'mode' ] ) ) {

		header( 'Content-Type: application/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );

		echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ 'data' ] );

		$data = $DD->documentElement ;

		switch ( $data->nodeName ) {
			case 'updTmpl' :
				updateInputTemplates( 'maindb.lvl1.card.tmpl' , iconv( 'utf8' , 'cp1251' , $data->nodeValue ) );
				echo '<result></result>' ;
				break ;

			case "unlink" :
				$matID = getCharID( $data->getAttribute( "id" ) , DOCTYPE_MATINCOMING );
				if ( $matID !== false ) {
					$portalDB->updateRow( 'matincoming' , array( 'group_id' => 0 , 'id' => $matID ) );
					echo '<result state="ok"/>' ;
				} else {
					echo '<result state="err" />' ;
				}
				break ;

			case 'mklink-get' :
				$matN = intval( $data->getAttribute( 'n' ) );
				$matY = intval( $data->getAttribute( 'y' ) );

				$tgtMatID = matincomingID( $matN , $matY );
				$matID = getCharID( $data->getAttribute( 'id' ) , DOCTYPE_MATINCOMING );

				if ( $matID === false ) {

				}

				$res = $portalDB->row( "select `t1`.* , `t4`.`name` as `agency` , `t5`.`name` as `agent` from `matincoming` as `t1` , `agency` as `t4` , `agent` as `t5` where ( `t1`.`id` = ? ) and ( `t1`.`from_agency` = `t4`.`id` ) and ( `t1`.`from_agent` = `t5`.`id` )" , 's' , $tgtMatID );
				if ( $res !== false ) {
					echo '<result state="ok" id="'.$matID.'" tid="'.$tgtMatID.'">'.toCDATA( $res[ 'agency' ].', '.$res[ 'agent' ].', '.$res[ 'ex_data_3' ].', '.$res[ 'ex_data_4' ] ).'</result>' ;
				} else {
					echo '<result state="error" />' ;
				}
				break ;

			case "mklink-do" :
				$matID = getCharID( $data->getAttribute( "id" ) , DOCTYPE_MATINCOMING );
				$tgtMatID = getCharID($data->getAttribute( "tid" ) , DOCTYPE_MATINCOMING );

				$res = $portalDB->row( "select ifnull( `group_id` , 0 ) as `gid` from `matincoming` where ( `id` = ? )" , "s" , $tgtMatID );
				if ( $res !== false && $res[ "gid" ] > 0 ) {
					$portalDB->noResult( "update `matincoming` set `group_id` = ? where ( `id` = ? )" , "is" , $res[ "gid" ] , $matID );
					echo "<result state=\"ok\" />" ;
				} else {
					$portalDB->noResult( "insert into `matincoming-groups` set `id` = null ;" );
					$gID = $portalDB->lastInsertID();
					$portalDB->noResult( "update `matincoming` set `group_id` = ? where ( `id` in ( ?* ) )" , "i*s" , $gID , array( $matID , $tgtMatID ) );
					echo "<result state=\"ok\" />" ;
				}


				break ;

			case "get-evidance" :
				$evID = intval( $data->getAttribute( "id" ) );
				$evData = $portalDB->row( "select * from `evidence` where `id` = ?" , "i" , $evID );
				if ( $evData !== false ) {
					echo "<result state=\"ok\"><evidence incDate=\"".$evData[ "inc_date" ]."\" outDate=\"".$evData[ "out_date" ]."\" state=\"".$evData[ "state" ]."\">".toCDATA( $evData[ "descr" ] )."</evidence></result>" ;
				} else {
					echo "<result state=\"error\" />" ;
				}
				break ;

			/*case "store-evidance" :
				$evID = $data->getAttribute( "id" );
				if ( $evID == "" ) {
					$matID = getCharID( $data->getAttribute( "id" ) );
					$evID = false ;
				} else {
					$matID = false ;
					$evID = intval( $evID );
				}
				$evDescr = $data->nodeValue ;
				$evIncDate = $data->getAttribute( "incDate" );
				$evState = $data->getAttribute( "state" );
				$evOutDate = $data->getAttribute( "outDate" );
				if ( $evID == "" ) {
					$localDB->noResult( "insert into `evidence` ( `ext_id` , `descr` , `inc_date` , `state` , `out_date` ) values ( ? , ? , ? , ? , ? )" , "ssiii" , $matID , $evDescr , $evIncDate , $evState , $evOutDate );
				} else {
					$localDB->noResult( "update `evidence` set `descr` = ? , `inc_date` = ? , `state` = ? , `out_date` = ? where `id` = ?" , "siiii" , $evDescr , $evIncDate , $evState , $evOutDate , $evID );
				}
				echo "<result state=\"ok\" />" ;
				break ;*/

			case "next-evidence-state" :
				$evID = intval( $data->getAttribute( "id" ) );
				$evData = $portalDB->row( "select * from `evidence` where `id` = ?" , "i" , $evID );
				if ( is_null( $evData[ "state" ] ) ) {
					$evData[ "state" ] = 0 ;
				}
				$evData[ "state" ]++ ;
				if ( $evData[ "state" ] > 2 ) {
					$evData[ "state" ] = -2 ;
				}

				$nsd = time();

				switch ( $evData[ "state" ] ) {
					case 2  :
					case -2 :
						$evOC = iconv( "utf8" , "cp1251" , trim( $data->nodeValue ) );
						$portalDB->noResult( "update `evidence` set `state` = ? , `state_date` = ? , `out_comment` = ? where `id` = ?" , "iisi" , $evData[ "state" ] , $nsd , $evOC , $evID );
						echo "<result state=\"ok\" ns=\"".$evData[ "state" ]."\" nsd=\"".date( "d-m-Y" , $nsd )."\">".toCDATA( $evOC )."</result>" ;
						break ;

					default :
						$portalDB->noResult( "update `evidence` set `state` = ? , `state_date` = ? where `id` = ?" , "iii" , $evData[ "state" ] , $nsd , $evID );
						echo "<result state=\"ok\" ns=\"".$evData[ "state" ]."\" nsd=\"".date( "d-m-Y" , $nsd )."\" />" ;
				}
				break ;

			default :
				echo "<result state=\"error\">".toCDATA( "No handler fo \"".$data->nodeName."\"" )."</result>" ;
				break ;

		}

		exit();
	} else {
		print_r_html( $_POST );
	}
