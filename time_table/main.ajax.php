<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( '../core.php' );
	/**
	 * @var TDB $portalDB
	 * @var $LoginOk
	 * @var $UserID
	 * @var $UserRights
	 * @var $UserThemeLoc
	 * @var $UserWorkerID
	 * @var $UserWorkerFirstID
	 * @var $MonthNames
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	require_once( 'tt-core.php' );
	/**
	 * @var $ttDescr
	 * @var $ttType
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	header( 'Content-Type: text/xml' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Expires: '.date( 'r' ) );
	header( 'Expires: -1' , false );

	echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

	$cttd = $ttDescr[ $ttType ];
	$row = $portalDB->simpleRow( 'access-rights' , array( 'user_id' => $UserID , 'place' => $cttd[ 'placeID' ] ) );
	if ( $row !== false ) {
		$Rights = ParseRights( strtoupper( $row[ 'rights' ] ) );
		if ( array_key_exists( $cttd[ 'ruleSet' ] , $Rights ) ) {
			$Rights = $Rights[ $cttd[ 'ruleSet' ] ];
		} else {
			$Rights = array();
		}

		$mayAdd       = in_array( 'ADD'        , $Rights );
		$mayEdit      = in_array( 'EDIT'       , $Rights );
		$mayEditAll   = in_array( 'EDIT-ALL'   , $Rights );
		$mayDelete    = in_array( 'DELETE'     , $Rights );
		$mayDeleteAll = in_array( 'DELETE-ALL' , $Rights );
		$mayFill      = in_array( 'FILL'       , $Rights );
		$GoOut = false ;
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		echo '<result status="error"/>' ;
		exit();
	}

    $DD = new DomDocument();
    $DD->loadXML( $_REQUEST[ 'data' ] );

    $data = $DD->documentElement ;

		switch ( $data->nodeName ) {
			case 'get-month-list' :
				$sy = intval( $data->getAttribute( 'year' ) );

				$syfd = mktime( 0 , 0 , 0 , 1 , 1 , $sy );
				$syld = mktime( 23 , 59 , 59 , 12 , 31 , $sy );

				$months = $portalDB->query( "select month( from_unixtime( `date` ) ) as `month` from `time-table` where ( `date` is not null ) and ( `date` >= ? ) and ( `date` <= ? ) and ( `type` = ? ) group by `month` order by `month` asc ;" , false , 'iii' , $syfd , $syld , $ttType );

				echo '<result>' ;
				foreach ( $months as $v ) {
					$cm = $v[ 'month' ];
					echo '<month index="'.$cm.'">'.toCDATA( ucfirst( inForm( $MonthNames[ $cm - 1 ] ) ) ).'</month>' ;
				}
				echo '</result>' ;

				break ;

			case 'get-list' :
				$sm = intval( $data->getAttribute( 'm' ) , 10 );
				if ( $sm < 1 ) {
					$sm = 1 ;
				} else
				if ( $sm > 12 ) {
					$sm = 12 ;
				}

				$sy = intval( $data->getAttribute( 'y' ) , 10 );
				if ( $sy < 0 ) {
					$sy = 0 ;
				}

				echo '<result>' ;

				$smfd = mktime( 0 , 0 , 0 , $sm , 1 , $sy );
				$smdc = date( 't' , $smfd );
				$smld = mktime( 23 , 59 , 59 , $sm , $smdc , $sy );

				$res = $portalDB->query( "select * from `time-table` where ( `date` is not null ) and ( `date` >= ? ) and ( `date` <= ? ) and ( `type` = ? ) order by `date` asc ;" , false , 'iii' , $smfd , $smld , $ttType );

				foreach( $res as &$row ) {

					$dt = $row[ 'date' ];
					$dow = date( 'w' , $dt );
					$dow = $dow == 0 ? 7 : $dow ;

					echo '<rec ' , ( ( $mayDelete && $row[ 'exp_id' ] == $UserWorkerFirstID ) || $mayDeleteAll ? 'del="'.$row[ 'id' ].'"' : '' ) , ' ' , ( ( $mayEdit && $row[ 'exp_id' ] == $UserWorkerFirstID ) || $mayEditAll ? 'edit="'.$row[ 'id' ].'"' : '' ) , ' d="' , date( 'd-m-Y' , $dt ) , '" w="' , $dow , '"><destination><![CDATA[' , $row[ 'destination' ] , ']]></destination><purpose><![CDATA[' , $row[ 'purpose' ] , ']]></purpose><expert><![CDATA[' , $row[ 'experts' ] , ']]></expert></rec>' ;
				}

				echo '</result>' ;
				break ;

			case 'get-record' :
				$id = intval( $data->getAttribute( 'id' ) );

				//$row = $portalDB->row( "select * from `time-table` where ( `id` = ? )" , 'i' , $id );
                $row = $portalDB->simpleRow( 'time-table' , array( 'id' => $id , 'type' => $ttType ) );
				$dt = $row[ 'date' ];

				echo '<result d="' , date( 'd-m-Y' , $dt ) , '"><destination><![CDATA[' , $row[ 'destination' ] , ']]></destination><purpose><![CDATA[' , $row[ 'purpose' ] , ']]></purpose><expert><![CDATA[' , $row[ 'experts' ] , ']]></expert></result>' ;

				break ;

			case 'add-record' :
				if ( !$mayAdd ) {
					echo '<result status="access-error"/>' ;
					break ;
				}
				$d = array( 'date' => '' , 'purpose' => '' , 'destination' => '' , 'experts' => '' );
				foreach( $data->childNodes as $cn ) {
					switch ( $cn->nodeName ) {
						case 'date' :
						case 'purpose' :
						case 'destination' :
						case 'experts' :
							$d[ $cn->nodeName ] = iconv( 'utf8' , 'cp1251' , $cn->nodeValue );
							break ;
					}
				}
				$d[ 'date' ] = explode( '-' , str_replace( ',' , '-' , str_replace( '.' , '-' , trim( $d[ 'date' ] ) ) ) );
				if ( count( $d[ 'date' ] ) != 3 ) {
					echo '<result status="date-error"/>' ;
					break ;
				}

				$dd = intval( $d[ 'date' ][ 0 ] );
				$mm = intval( $d[ 'date' ][ 1 ] );
				$yy = intval( $d[ 'date' ][ 2 ] );

				if ( $yy < 2000 || $yy > 2100 || $mm < 1 || $mm > 12 ) {
					echo '<result status="date-error"/>' ;
					break ;
				}

				$dc = intval( date( 't' , mktime( 0 , 0 , 0 , $mm , 1 , $yy ) ) );
				if ( $dd < 1 || $dd > $dc ) {
					echo '<result status="date-error"/>' ;
					break ;
				}

				$tt = mktime( 0 , 0 , 0 , $mm , $dd , $yy );
				$portalDB->noResult( 'insert into `time-table` ( `type` , `date` , `destination` , `purpose` , `experts` , `exp_id` ) values ( ? , ? , ? , ? , ? , ? )' , 'iisssi' , $ttType , $tt , $d[ 'destination' ] ,  $d[ 'purpose' ] ,  $d[ 'experts' ] , $UserWorkerFirstID );
				echo '<result status="ok" m="'.intval( $mm ).'" y="'.intval( $yy ).'"/>' ;
				break ;

			case 'edit-record' :
				if ( !( $mayEdit || $mayEditAll ) ) {
					echo '<result status="access-error"/>' ;
					break ;
				}
				$d = array( 'date' => '' , 'purpose' => '' , 'destination' => '' , 'experts' => '' );
				$id = intval( $data->getAttribute( 'id' ) );
				foreach( $data->childNodes as $cn ) {
					switch ( $cn->nodeName ) {
						case 'date' :
						case 'purpose' :
						case 'destination' :
						case 'experts' :
							$d[ $cn->nodeName ] = iconv( 'utf8' , 'cp1251' , $cn->nodeValue );
							break ;
					}
				}
				$d[ 'date' ] = explode( '-' , str_replace( ',' , '-' , str_replace( '.' , '-' , trim( $d[ 'date' ] ) ) ) );
				if ( count( $d[ 'date' ] ) != 3 ) {
					echo '<result status="date-error"/>' ;
					break ;
				}

				$dd = intval( $d[ 'date' ][ 0 ] );
				$mm = intval( $d[ 'date' ][ 1 ] );
				$yy = intval( $d[ 'date' ][ 2 ] );

				if ( $yy < 2000 || $yy > 2100 || $mm < 1 || $mm > 12 ) {
					echo '<result status="date-error"/>' ;
					break ;
				}

				$dc = intval( date( 't' , mktime( 0 , 0 , 0 , $mm , 1 , $yy ) ) );
				if ( $dd < 1 || $dd > $dc ) {
					echo '<result status="date-error"/>' ;
					break ;
				}

				$tt = mktime( 0 , 0 , 0 , $mm , $dd , $yy );
				$portalDB->noResult( "update `time-table` set `destination` = ? , `purpose` = ? , `experts` = ? where ".( !$mayEditAll ? "( `exp_id` = ".Int2SQL( $UserWorkerFirstID )." ) and " : "" )." ( `id` = ? ) and ( `type` = ? )" , 'sssii' , $d[ 'destination' ] , $d[ 'purpose' ] , $d[ 'experts' ] , $id , $ttType );
				echo '<result status="ok" m="'.intval( $mm ).'" y="'.intval( $yy ).'"/>' ;
				break ;

			case 'delete-records' :
				if ( !( $mayDelete || $mayDeleteAll ) ) {
					echo '<result status="access-error"/>' ;
					break ;
				}
				$nums = array();
				foreach( $data->childNodes as $cn ) {
					switch ( $cn->nodeName ) {
						case 'r' :
							$nums[] = Int2SQL( $cn->getAttribute( 'id' ) );
							break ;
					}
				}

				if ( count( $nums ) == 0 ) {
					echo '<result status="nothing-to-delete"/>' ;
					break ;
				}

				$rd = $portalDB->query( "select * from `time-table` where ( `id` in ( ?* ) ) and ( `type` = ? )" , false , '*ii' , $nums , $ttType );
				$nums = array();
				$my = array();
				foreach( $rd as &$r ) {
					if ( $mayDeleteAll || $r[ 'exp_id' ] == $UserWorkerFirstID ) {
						$nums[]= Int2SQL( $r[ 'id' ] );
						$k = date( 'Y.m' , $r[ 'date' ] );
						if ( !isset( $my[ $k ] ) ) {
							$my[ $k ] = $k ;
						}
					}
				}
				unset( $r );

				$portalDB->noResult( "delete from `time-table` where ( `id` in ( ?* ) ) and ( `type` = ? )" , '*ii' , $nums , $ttType );

				echo '<result status="ok">' ;

				foreach( $my as $i ) {
					list( $yy , $mm ) = explode( '.' , $i );
					echo '<my m="'.intval( $mm ).'" y="'.intval( $yy ).'"/>' ;
				}

				echo '</result>' ;

				break ;

			case 'get-exp-list' :
				$tabWorkers = $portalDB->table( 'workers' , 'id' );
				$res = $portalDB->query( "select `t1`.`rights` , `t2`.`worker_id` from `access-rights` as `t1` , `accounts` as `t2` , `workers` as `t3` where ( `place` = ? ) and ( `t2`.`id` = `t1`.`user_id` ) and ( `t2`.`worker_id` = `t3`.`id` ) and ( `t3`.`actual` ) order by `t3`.`name`" , false , 'i' , $PlaceID );

				echo '<result status="ok">' ;

				foreach( $res as $ed ) {
					$r = ParseRights( strtoupper( $ed[ 'rights' ] ) );
					if ( array_key_exists( 'RECORDS' , $r ) && in_array( 'FILLING-PARTICIPANT' , $r[ 'RECORDS' ] ) ) {
						echo '<exp id="' , $tabWorkers[ $ed[ 'worker_id' ] ][ 'first_id' ] , '"><![CDATA[' , NAMES_Format( NAMES_parse( $tabWorkers[ $ed[ 'worker_id' ] ][ 'name' ] ) ) , ']]></exp>' ;
					}
				}

				echo '</result>' ;

				break ;

			case 'fill' :
				if ( !$mayFill ) {
					echo '<result status="access-error"/>' ;
					break ;
				}

				$nums = array();
				$mm = intval( $data->getAttribute( 'm' ) );
				$yy = intval( $data->getAttribute( 'y' ) );
				foreach( $data->childNodes as $cn ) {
					switch ( $cn->nodeName ) {
						case 'r' :
							$nums[] = array( 'd' => intval( $cn->getAttribute( 'd' ) ) , 'e' => intval( $cn->getAttribute( 'e' ) ) );
							break ;
					}
				}

				echo '<result status="ok">' ;

				$tabWorkers = $portalDB->query( "select `first_id` , `name` from `workers` where ( `actual` = 1 ) group by `first_id`" , 'first_id' );

				foreach( $nums as $fd ) {
					if ( isset( $tabWorkers[ $fd[ 'e' ] ] ) ) {
						$portalDB->noResult( "insert into `time-table` ( `type` , `date` , `exp_id` , `experts` ) values ( ? , ? , ? , ? )" , 'iiis' , $ttType , mktime( 0 , 0 , 0 , $mm , $fd[ 'd' ] , $yy ) , $fd[ 'e' ] , NAMES_Format( NAMES_parse( $tabWorkers[ $fd[ 'e' ] ][ 'name' ] ) ) );
					}
				}

				echo '</result>' ;

				break ;
		}

	exit();

?>