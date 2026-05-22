<?php
	require_once ( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $portalDB
	 */
	include_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	function str2KEY( $str ) {
		return strtoupper( clearText( $str ) );
	}

	function mkMap( $arr , $keyCol ) {
		return array_column( array_map(
			function( $row ) use ( $keyCol ) {
				$row[ 'KEY' ] = str2KEY( $row[ $keyCol ] );
				return $row ;
			} , $arr
		) , null , 'KEY' );
	}

	if ( isset( $_REQUEST[ 'do-import' ] ) ) {
		//print_r_html( $_FILES );
		//header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		//header( 'Content-Disposition: attachment;filename="Объединенные XLSX '.date( 'Y.m.d H-i' , mktime() ).'.xlsx"' );

		$TB = microtime( 1 );
		$xlsx_wrk          = new TSimpleXLSXTemplate( $_FILES[ 'src-file-wrk' ][ 'tmp_name' ] );

		$tabPosts = $portalDB->table( 'posts' );
		$postsMap = mkMap( $tabPosts );

		$tabSpec = $portalDB->query( "select concat( cast( `t1`.`index` as char ) , '.' , cast( `t2`.`num` as char ) ) as `spec-num` , `t2`.`id` as `spec-id` , count( * ) as `spec-count` from `specialities-groups` as `t1` , `specialities` as `t2` where ( `t1`.`id` = `t2`.`group` ) and ( `use_in_stat` = 1 ) group by `spec-num`" , 'spec-num' );
		$tabDepartments = $portalDB->table( 'departments' );
		$depMap = mkMap( $tabDepartments );

		$xlsx_wrk->selectSheet( 'wrk' );

		$wrk = array();
		$row = 2 ;

		while ( $row < 1000 ) {
			echo 'Обрабатывается строка '.$row.'<br/>' ;
			$vName = clearText( $xlsx_wrk->getCellValue( 'A'.$row ) );
			if ( $vName != '' ) {
				$m = array();
				$n = preg_match( '/^(?<f>\w+)\s(?<i>\w+)\s(?<o>\w+)$/' , $vName , $m );
				if ( $n == 1 ) {
					echo 'Определено Ф.И.О.: '.$m[ 'f' ].' '.$m[ 'i' ].' '.$m[ 'o' ].'<br/>' ;
				} else {
					$m[ 'f' ] = preg_replace( '/\s+/' , '_' , $vName );
					$m[ 'i' ] = '_' ;
					$m[ 'o' ] = '_' ;
					echo '<span style="color: red">Не удалось определить Ф.И.О.: '.$vName.'</span><br/>' ;
				}

				foreach( str_split( 'fio' ) as $c ) {
					$m[ $c ] = ucfirst( strtolower( $m[ $c ] ) );
				}
				$wName = 'f='.$m[ 'f' ].';i='.$m[ 'i' ].';o='.$m[ 'o' ];
				$wK = str2KEY( $m[ 'f' ].' '.$m[ 'i' ].' '.$m[ 'o' ] );
				$vPost = clearText( $xlsx_wrk->getCellValue( 'B'.$row ) );
				$pK = str2KEY( $vPost );
				if ( !isset( $postsMap[ $pK ] ) ) {
					$portalDB->insertRow( 'posts' , array(
						'name' => $vPost ,
						'actual' => 1
					) );
					$wPostID = $portalDB->lastInsertID();
					$postsMap[ $pK ] = array(
						'id' => $wPostID ,
						'name' => $vPost
					);

					$tabPosts[]= $postsMap[ $pK ];
				} else {
					$wPostID = $postsMap[ $pK ][ 'id' ];
				}

				$vSpec = trim( $xlsx_wrk->getCellValue( 'C'.$row ) );
				$m = array();
				$n = preg_match( '/^(?<s>\d{1,2}\.\d)[\.\s]/' , $vSpec , $m );
				$specNum = '---' ;
				if ( $n == 1 ) {
					$specNum = $m[ 's' ];
				}

				if ( isset( $tabSpec[ $specNum ] ) ) {
				} else {
					echo 'Ошибка: специальность не определена! ['.$vSpec.']<br>/' ;
				}

				$vDep = clearText( $xlsx_wrk->getCellValue( 'D'.$row ) );
				$dK = str2KEY( $vDep );
				if ( !isset( $depMap[ $dK ] ) ) {
					$portalDB->insertRow( 'departments' , array(
						'name' => $vDep ,
						'actual' => 1
					) );
					$wDepID = $portalDB->lastInsertID();
					$depMap[ $dK ] = array(
						'id' => $wDepID ,
						'name' => $vDep
					);

					$tabDepartments[]= $depMap[ $dK ];
				} else {
					$wDepID = $depMap[ $dK ][ 'id' ];
				}


				if ( !isset( $wrk[ $wK ] ) ) {
					$portalDB->insertRow( 'workers-no-spec' , array(
						'name' => $wName ,
						'post_1_id' => $wPostID ,
						'post_2_id' => $wPostID ,
						'dep' => $wDepID ,
						'actual' => 1
					) );
					$wID = $portalDB->lastInsertID();
					$portalDB->updateRow( 'workers-no-spec' , array(
						'id' => $wID ,
						'first_id' => $wID
					) );
					$wrk[ $wK ] = array(
						'id' => $wID ,
						'name' => $wName ,
						'post1' => $wPostID ,
						'post2' => $wPostID ,
						'specs' => array()
					);
				}

				$cWrk = &$wrk[ $wK ];
				$wID = $cWrk[ 'id' ];

				if ( isset( $tabSpec[ $specNum ] ) ) {
					$wSpecID = $tabSpec[ $specNum ][ 'spec-id' ];
					if ( !isset( $cWrk[ 'specs' ][ $wSpecID ] ) ) {
						$portalDB->insertRow( 'workers-spec' , array(
							'worker_id' => $wID ,
							'spec_id' => $wSpecID
						) );
						$cWrk[ 'specs' ][ $wSpecID ] = $wSpecID ;
					}
				}
			} else {
				echo 'Найдена пустая строка -> останавливаем обработку<br/>' ;
				break ;
			}

			echo '<br/>' ;
			$row++ ;
		}

		echo 'Обработка закончена на строке: '.$row.'<br/>' ;

		exit();
	}

	MainHead_L2( 'Инструменты - объединить XLSX' , 'Инструменты - объединить XLSX' , array( '../%UT/buttons.css' ) , array() , 'hlp/main.html' );

	echo '<div>
			<form action="?do-import" method="post" enctype="multipart/form-data">
				<label>Сотрудники <input type="file" name="src-file-wrk" /></label><br/>
				<input type="submit" value="Отправить">
			</form>
		</div>' ;

	closeHtml();

	//$xlsx = new TSimpleXLSXTemplate( './files/tmpl-151.xlsx' );

