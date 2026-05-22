<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	include_once( "core.php" );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var TDB $portalDB
	 */

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

		if ( array_key_exists( "EQUIPMENT" , $Rights ) ) {
			$mayAdd = in_array( "ADD" , $Rights[ "EQUIPMENT" ] );
			$mayEdit = in_array( "EDIT" , $Rights[ "EQUIPMENT" ] );
			$mayView = in_array( "VIEW" , $Rights[ "EQUIPMENT" ] );
			$GoOut = !( $mayAdd || $mayEdit || $mayView );
		} else {
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}

	//$con = OpenDB( $dbHost , $dbUser , $dbPassword , $dbDatabase );
	//$eqTab = QueryAsArray( $con , "select * from `equipment` order by `label` asc" , "id" );
	$eqTab = $portalDB->query( 'select * , if( ( `decommissioned_date` is null ) or ( `decommissioned_date` = 0 ) , 1 , 0 ) as `in_use` from `equipment` order by `in_use` desc , `label` asc' , 'id' );
	//$expEqTab = QueryAsArray( $con , "select * from `exp-equipment`" , "id" );
	$expEqTab = $portalDB->query( 'select * from `exp-equipment`' , 'id' );

	$eqMap = array();
	$docsMap = array();
	foreach ( $expEqTab as &$eet ) {
		$eid = $eet[ "ext_id" ];
		if ( !isset( $eqMap[ $eid ] ) && isset( $eqTab[ $eid ] ) ) {
			$eqMap[ $eid ] = array(
				"eq" => &$eqTab[ $eid ],
				"type" => array( "exp" => 1 ),
				"data" => array( "exp" => array() ),
				"actual" => array(),
				"docs" => array( "exp" => array() )
			);
		}

		$ceqm = &$eqMap[ $eid ];

		$ceqmData = &$ceqm[ "data" ][ "exp" ];
		$ceqmData[]= &$eet ;

		if ( $eet[ "actual" ] == 1 ) {
			$ceqm[ "actual" ][ "exp" ] = &$eet ;
		}

		$ceqmDocs = &$ceqm[ "docs" ][ "exp" ];
		if ( !is_null( $eet[ "test_result" ] ) ) {
			$did = $eet[ "test_result" ];
			$ceqmDocs[ $did ]= $did ;
			$docsMap[ $did ] = &$ceqmDocs[ $did ];
		}
	} unset( $eet );

	if ( count( $docsMap ) > 0 ) {
		//$q = "select * from `file_store`.`files` where ( `id` in ( ".implode( "," , array_keys( $docsMap ) )." ) )" ;
		//$docsTab = QueryAsArray( $con , $q , "id" );
		$docsTab = $portalDB->query( 'select * from `files` where ( `id` in ( ?* ) )' , 'id' , '*i' , array_keys( $docsMap ) );

		foreach ( $docsMap as &$dv ) {
			if ( isset( $docsTab[ $dv ] ) ) {
				$dv = "<a href=\"/file_store/download.php?id=".$dv."\" class=\"eq-doc-lnk\" title=\"".$docsTab[ $dv ][ "description" ]."\">".$docsTab[ $dv ][ "name" ]."</a>" ;
			} else {
				$dv = "<a href=\"\" class=\"eq-doc-lnk-err\">ДОКУМЕНТ УДАЛЕН</a>" ;
			}
		} unset( $dv );

		/*foreach ( $docsTab as $d ) {
			$did = $d[ "id" ];
			$docsMap[ $did ] = "<a href=\"/file_store/download.php?id=".$did."\" class=\"eq-doc-lnk\" title=\"".$d[ "description" ]."\">".$d[ "name" ]."</a>" ;
		}*/
	}

	//mysql_close( $con );

	MainHead_L2( "Оборудование" , "Оборудование" , array( "%UT/equipment.list.css" , "%UT/buttons.css" ) , array( "files/orders-mat.list.js" ) , "hlp/list.html" );

	if ( $mayAdd ) {
		echo "<div><a href=\"equipment.form.php?add\" class=\"btn3\">Добавить</a></div>" ;
	}

	echo "<div class=\"eq-tab\">" ;

	foreach ( $eqTab as $eq ) {
		$eid = $eq[ "id" ];
		echo "<div class=\"eq-info".( $eq[ 'in_use' ] == 0 ? ' not-in-use' : '' )."\">
			<div class=\"eq-h\"><div class=\"eq-name\">".$eq[ "label" ]."</div><div class=\"eq-buttons\">".( $mayEdit ? "<a href=\"equipment.form.php?edit=".$eid."\" class=\"eq-b-e\" title=\"Редактировать\" target=\"_blank\"></a>" : "" )."".( $mayEdit ? "<a href=\"maindb/log.equipment.php?eq=".$eid."\" class=\"eq-b-l\" title=\"Журнал использования\" target=\"_blank\"></a>" : "" )."</div></div>
			<div class=\"eq-v\">
				<div class=\"eq-pl eq-rn\">Инв. № <div class=\"eq-pv\">".$eq[ "reg-number" ]."</div></div>
				<div class=\"eq-label\">".str_replace( "." , ". " , $eq[ "name" ] )."</div>
				<div class=\"eq-clear\"></div>
				<div class=\"eq-pl\">Дата ввода в эксплуатацию: <div class=\"eq-pv eq-td\">".date( "d-m-Y" , $eq[ "startup-date" ] )."</div></div>
				<div class=\"eq-pl\">Способ приобретения: <div class=\"eq-pv\">".$eq[ "mop" ]."</div></div>" ;

		if ( isset( $eqMap[ $eid ] ) ) {
			$ceqm = &$eqMap[ $eid ];
			echo "<div class=\"eq-tab-separator\"></div>" ;
			if ( isset( $ceqm[ "actual" ][ "exp" ] ) ) {
				$ceqmActual = $ceqm[ "actual" ][ "exp" ];
				echo "<div class=\"eq-pl\">поверка/калибровка" ;
				switch ( $ceqmActual[ "test_type" ] ) {
					case 1 :
						$tmpS = "ПОВЕРКА" ;
						break ;
					case 2 :
						$tmpS = "КАЛИБРОВКА" ;
						break ;
					default :
						$tmpS = "&mdash;" ;
						break ;
				}
				echo "<div class=\"eq-pv eq-tt\">".$tmpS."</div></div>" ;
				switch ( $ceqmActual[ "test_type" ] ) {
					case 1 :
					case 2 :
						$lastTestDate = $ceqmActual[ "test_date" ];
						$lastTestDate = explode( "-" , date( "d-m-Y" , $lastTestDate ) );
						$testPeriod = $ceqmActual[ "test_period" ];
						preg_match( '/^(\d+d)?(\d+m)?(\d+y)?$/' , $testPeriod , $testPeriod );
						$testPeriodDay = 0 ;
						$testPeriodMonth = 0 ;
						$testPeriodYear = 0 ;
						for( $i = 1 ; $i < count( $testPeriod ) ; $i++ ) {
							switch( substr( $testPeriod[ $i ] , -1 ) ) {
								case "d" :
									$testPeriodDay = intval( substr( $testPeriod[ $i ] , 0 , -1 ) );
									break ;
								case "m" :
									$testPeriodMonth = intval( substr( $testPeriod[ $i ] , 0 , -1 ) );
									break ;
								case "y" :
									$testPeriodYear = intval( substr( $testPeriod[ $i ] , 0 , -1 ) );
									break ;
							}
						}
						/*echo $testPeriodDay." - ".$testPeriodMonth." - ".$testPeriodYear."<br>" ;
						print_r_html( $testPeriod );*/
						$nextTestDate = $lastTestDate ;
						$nextTestDate = explode( "-" , date( "d-m-Y" , mktime( 0 , 0 , 0 , $nextTestDate[ 1 ] , $nextTestDate[ 0 ] , $nextTestDate[ 2 ] + $testPeriodYear ) ) );
						$nextTestDate = explode( "-" , date( "d-m-Y" , mktime( 0 , 0 , 0 , $nextTestDate[ 1 ] + $testPeriodMonth , $nextTestDate[ 0 ] , $nextTestDate[ 2 ] ) ) );
						$nextTestDate = mktime( 0 , 0 , 0 , $nextTestDate[ 1 ] , $nextTestDate[ 0 ] + $testPeriodDay , $nextTestDate[ 2 ] );
						echo "<div class=\"eq-pl\">последний раз<div class=\"eq-pv eq-td\">".date( "d-m-Y" , $ceqmActual[ "test_date" ] )."</div></div>
						<div class=\"eq-pl\">следующий раз<div class=\"eq-pv eq-td\">".date( "d-m-Y" , $nextTestDate )."</div></div>" ;
						break ;
				}

				if ( count( $ceqm[ "docs" ][ "exp" ] ) > 0 ) {
					echo "<div class=\"eq-tab-separator\"></div>" ;
					foreach ( $ceqm[ "docs" ][ "exp" ] as $d ) {
						echo $d ;
					}
				}
			} else {
				echo "<div>Актуальных данных о приборе нет</div>" ;
			}

		}
			echo "</div>
		</div>" ;
	}

	echo "</div>" ;

	closeHtml();
