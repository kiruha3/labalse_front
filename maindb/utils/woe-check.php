<?php
	include_once( '../../core.php' );
	require_once( '../lconfig.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../../auth.php' );
	}

	MainHead_L2( 'База' , '<a href="./">База</a> - Тесты' , array( '../%UT/buttons.css' , './woe-check.css' ) , array( ) , 'hlp/main.html' );

	echo '<div class="form-container">
		'.NAMES_Format( NAMES_parse( $UserName ) , '%I1 %O1' ).', функционал отключен
	</div>' ;

	closeHtml();
	exit();

	if ( isset( $_REQUEST[ "send-src-file" ] ) ) {
		$fh = fopen( $_FILES[ "src-file-data" ][ "tmp_name" ] , "r" );
		$cap = fgetcsv( $fh , 0 , "," , "\"" , "\"" );

		$res = array();
		$woeEList = array();
		$woePList = array();
		$uIDList = array();
		while ( !feof( $fh ) ) {
			$r = fgetcsv( $fh , 0 , "," , "\"" , "\"" );
			if ( count( $r ) > 0 ) {
				$nr = array();
				foreach( $r as $k => $v ) {
					$nr[ $cap[ $k ] ] = $v ;
				}
				$res[]= &$nr ;

				$woeTNum = $nr[ "Номер ИД" ];
				$woeTNum = preg_replace( '/^\s*([а-я]{2})\s*(?:№)?\s*(\d{8,10})\s*$/i' , '\1 № \2' , strtoupper( $woeTNum ) );
				$woeTNum = preg_replace( '/^([а-я]{2})\s3\s(\d{8,10})$/i' , '\1 № \2' , strtoupper( $woeTNum ) );
				$nr[ "woeTNum" ] = $woeTNum ;
				if ( preg_match( '/^([а-я]{2})\s№\s(\d{8,10})$/i' , $woeTNum ) == 1 ) {
					if ( !isset( $woeEList[ $woeTNum ] ) ) {
						$woeEList[ $woeTNum ] = array();
					}
					$woeEList[ $woeTNum ][]= &$nr ;
				} else
				if ( preg_match( '/^(\d{8,10})$/i' , $woeTNum ) == 1 ) {
					if ( !isset( $woePList[ $woeTNum ] ) ) {
						$woePList[ $woeTNum ] = array();
					}
					$woePList[ $woeTNum ][]= &$nr ;
				} else {
					$uIDList[]= &$nr ;
					//echo $woeTNum."<br>" ;
				}

				unset( $nr );
			}
		}
		fclose( $fh );
		echo "<br><br><a href=\"woe-check.php\">Назад</a>
			<a href=\"#res-t-1\">Таблица 1</a>
			<a href=\"#res-t-2\">Таблица 2</a>
			<a href=\"#res-t-3\">Таблица 3</a>
			<a href=\"#res-t-4\">Таблица 4</a>
			<a href=\"#res-t-5\">Таблица 5</a>
			<a href=\"#res-t-6\">Таблица 6</a>" ;

		$woeActual = array();
		$woeTotalList = array_merge( $woeEList , $woePList );
		$woeNA = array();
		$woeYA = array();
		$woeSA = array();
		$woeS1A = array();
		$woeStateMap = array(
			"Окончено" => 1 ,
			"В исполнении" => 0 ,
			"Передано в другое ОСП" => 0 ,
			"Новый" => 0 ,
			"Отказан" => 3 ,
			"Прекращено" => 4 ,
			"Приостановлен" => 5 ,
		);

		//$woe
		foreach( $woeTotalList as $k => $v ) {
			if ( count( $v ) == 1 ) {
				$v = $v[ 0 ];
				$woeInfo = $portalDB->query( "select `t1`.* , count( `t2`.`id` ) as `cnt` , sum( `t2`.`price` ) as `t_price` from `writ-of-execution` as `t1` left join `writ-of-execution-payers` as `t2` on ( `t1`.`id` = `t2`.`ext_id` ) where ( `num` like concat( '%' , ? ) ) group by `t1`.`id`" , false , "s" , $k );
				if ( $v[ "Статья" ] != "" ) {
					$vState = $v[ "Статья" ].".".$v[ "Пункт" ].".".$v[ "Подпункт" ];
				} else {
					$vState = "" ;
				}
				if ( count( $woeInfo ) == 1 ) {
					if ( ( $vState == "47.1.1" ) || ( $vState == "" ) ) {
						$woeS1Ar = array();
						$woeInfo = $woeInfo[ 0 ];
						$actualState = $woeStateMap[ $v[ "Статус" ] ] == $woeInfo[ "state" ];
						$wiTPo = money_format( "%!i" , $woeInfo[ "t_price" ] );
						$wiTP = explode( "." , preg_replace( '/\xA0+/' , "" , $wiTPo ) );
						$vTPo = str_replace( "," , "." , $v[ "Сумма долга" ] );
						$vTP = explode( "." , preg_replace( '/\xA0+/' , "" , $vTPo ) );
						$actualPrice = ( count( $wiTP ) == count( $vTP ) ) & ( count( $vTP ) == 2 ) & ( abs( intval( $wiTP[ 0 ] ) - intval( $vTP[ 0 ] ) ) < 1 );
						$woeS1Ar[ "lnk" ] = "<a href=\"/maindb/writ-of-execution.php?edit=".$woeInfo[ "id" ]."\" target=\"_blank\">".$woeInfo[ "num" ]."</a>" ;
						$woeS1Ar[ "woeTNum" ] = $v[ "woeTNum" ];
						$woeS1Ar[ "num" ] = $woeInfo[ "num" ];
						$woeS1Ar[ "Номер ИД" ] = $v[ "Номер ИД" ];
						if ( !$actualState ) {
							$woeS1Ar[ "state" ] = $woeInfo[ "state" ]." - ".$v[ "Статус" ];
						}
						if ( !$actualPrice ) {
							$woeS1Ar[ "price" ] = $wiTPo." - ".$vTPo ;
						}
						if ( !$actualPrice || !$actualState ) {
							$woeS1A[]= $woeS1Ar ;
						} else {
							$woeS1Ar[ "num-check" ] = ( substr( $v[ "Номер ИД" ] , -9 ) == substr( $woeInfo[ "num" ] , -9 ) );
							$woeActual[]= $woeS1Ar ;
						}
					} else {
						$woeSAr = array();
						$woeInfo = $woeInfo[ 0 ];
						$actualState = $woeStateMap[ $v[ "Статус" ] ] == $woeInfo[ "state" ];
						$wiTPo = money_format( "%!i" , $woeInfo[ "t_price" ] );
						$wiTP = explode( "." , preg_replace( '/\xA0+/' , "" , $wiTPo ) );
						$vTPo = str_replace( "," , "." , $v[ "Сумма долга" ] );
						$vTP = explode( "." , preg_replace( '/\xA0+/' , "" , $vTPo ) );
						$actualPrice = ( count( $wiTP ) == count( $vTP ) ) & ( count( $vTP ) == 2 ) & ( abs( intval( $wiTP[ 0 ] ) - intval( $vTP[ 0 ] ) ) < 1 );
						$woeSAr[ "lnk" ] = "<a href=\"/maindb/writ-of-execution.php?edit=".$woeInfo[ "id" ]."\"  target=\"_blank\">".$woeInfo[ "num" ]."</a>" ;
						$woeSAr[ "woeTNum" ] = $v[ "woeTNum" ];
						$woeSAr[ "num" ] = $woeInfo[ "num" ];
						$woeSAr[ "Номер ИД" ] = $v[ "Номер ИД" ];
						if ( !$actualState ) {
							$woeSAr[ "state" ] = $woeInfo[ "state" ]." - ".$v[ "Статус" ];
						}
						if ( !$actualPrice ) {
							$woeSAr[ "price" ] = $wiTPo." - ".$vTPo ;
						}
						unset( $v[ "Статус" ] );
						unset( $v[ "№ п/п" ] );
						$woeSAr = array_merge( $woeSAr , $v );
						$woeSA[]= $woeSAr ;
					}
				} else
				if ( count( $woeInfo ) > 1 ) {
					$lnk = array();
					foreach( $woeInfo as $cwi ) {
						$lnk[]=  "<a href=\"/maindb/writ-of-execution.php?edit=".$cwi[ "id" ]."\" target=\"_blank\">".$cwi[ "num" ]."</a><br>" ;
					}
					$v[ "woeTNum" ].= "<br>".implode( "" , $lnk );
					$woeYA[]= $v ;
				} else {
					array_unshift( $uIDList , $v );
				}
			} else {
				$woeInfo = $portalDB->query( "select `t1`.* , count( `t2`.`id` ) as `cnt` , sum( `t2`.`price` ) as `t_price` from `writ-of-execution` as `t1` left join `writ-of-execution-payers` as `t2` on ( `t1`.`id` = `t2`.`ext_id` ) where ( `num` like concat( '%' , ? ) ) group by `t1`.`id`" , false , "s" , $k );
				$lnk = array();
				if ( $woeInfo !== false && count( $woeInfo ) > 0 ) {
					foreach( $woeInfo as $cwi ) {
						$lnk[]=  "<a href=\"/maindb/writ-of-execution.php?edit=".$cwi[ "id" ]."\" target=\"_blank\">".$cwi[ "num" ]."</a><br>" ;
					}
					$lnk = "<br>".implode( "" , $lnk );
					foreach( $v as &$cv ) {
						$cv[ "woeTNum" ].= $lnk ;
					} unset( $cv );
					$woeNA = array_merge( $woeNA , $v );
				} else {
					$uIDList = array_merge( $v , $uIDList );
				}
			}
		}

		echo "<div class=\"res-title\" id=\"res-t-1\">1. а) У приставов значится как взысканные в полном объеме, а у нас не сходится<br>
			б) В работе, а у нас не сходится
		</div>" ;
		echo makeSimpleTable( $woeS1A );

		echo "<div class=\"res-title\" id=\"res-t-2\">2. У приставов Отказы, не возможно взыскать и т.д.</div>" ;
		echo makeSimpleTable( $woeSA );

		echo "<div class=\"res-title\" id=\"res-t-3\">3. У нас несколько листов</div>" ;
		echo makeSimpleTable( $woeYA );

		echo "<div class=\"res-title\" id=\"res-t-4\">4. У приставов несколько записей</div>" ;
		echo makeSimpleTable( $woeNA );

		echo "<div class=\"res-title\" id=\"res-t-5\">5. Остатки</div>" ;
		echo makeSimpleTable( $uIDList );

		echo "<div class=\"res-title\" id=\"res-t-6\">6. Вроде ОК</div>" ;
		echo makeSimpleTable( $woeActual );

		echo "<br><br><a href=\"woe-check.php\">Назад</a>
			<a href=\"#res-t-1\">Таблица 1</a>
			<a href=\"#res-t-2\">Таблица 2</a>
			<a href=\"#res-t-3\">Таблица 3</a>
			<a href=\"#res-t-4\">Таблица 4</a>
			<a href=\"#res-t-5\">Таблица 5</a>
			<a href=\"#res-t-6\">Таблица 6</a>" ;
	} else {
		echo "<div class=\"form-container\"><form id=\"src-file\" name=\"src-file\" method=\"POST\" action=\"woe-check.php\" enctype=\"multipart/form-data\">
			<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"8589934592\">
			<input type=\"file\" name=\"src-file-data\">
			<input name=\"send-src-file\" type=\"submit\" value=\"Загрузить\">
		</form></div>" ;
	}

	closeHtml();
