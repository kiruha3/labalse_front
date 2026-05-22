<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $MonthNames
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	$GoOut = true ;
	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'EXTENTIONS' , $Rights ) ) {
			$maySTATISTICS = in_array( 'STATISTICS' , $Rights[ 'EXTENTIONS' ] );
			$GoOut = !$maySTATISTICS ;
		}
	}

	if ( $GoOut ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit ;
	}

	if ( isset( $_REQUEST[ 'yf' ] ) ) {
		$yf = $_REQUEST[ 'yf' ];
	} else {
		$yf = 'normal' ;
	}


	$repData = array(
		'statistics'         => array(
			'addr' => 'statistics.php' ,
			'type' => 'ym1m2'
		) ,
		'form-XLSX-15.1-88'  => array(
			'addr' => './reports/15-1.246/ed-88/main.php' ,
			'type' => 'ym1m2'
		) ,
		'form-XLSX-15.1-129' => array(
			'addr' => './reports/15-1.246/ed-129/main.php' ,
			'type' => 'ym1m2'
		) ,
		'form-new-1'         => array(
			'addr' => 'report-new-1.php' ,
			'type' => 'ym1m2'
		) ,
		'form-new-2'         => array(
			'addr' => 'report-new-2.php' ,
			'type' => 'ym1m2'
		) ,
		'stat-equipment'     => array(
			'addr' => 'eq-stat.php' ,
			'type' => 'm1y1m2y2'
		) ,
		'docs-access-log'    => array(
			'addr' => 'docs-access-log.php' ,
			'type' => 'm1y1m2y2'
		) ,
	);
	if ( isset( $_REQUEST[ 'as' ] ) ) {
		$as = $_REQUEST[ 'as' ];
	} else {
		$as = 'statistics' ;
	}

	if ( isset( $repData[ $as ] ) ) {
		$as = $repData[ $as ];
	} else {
		$as = $repData[ 'statistics' ];
	}

	$yearsList = range( intval( date( 'Y' , time() ) ) , 2008 );
	$yearsList = array_combine( $yearsList , $yearsList );
	$m1Select = '<select size="1" name="m1">'.makeSimpleSelectTagOptions1D( $MonthNames , null , function( $v ) { return $v + 1 ; } , function( $k , $v ) { return inForm( $v , 2 ); } ).'</select>' ;
	$m2Select = '<select size="1" name="m2">'.makeSimpleSelectTagOptions1D( $MonthNames , null , function( $v ) { return $v + 1 ; } , function( $k , $v ) { return inForm( $v , 1 ); } ).'</select>' ;

	MainHead_L2( 'База - Статистика Мин' , '<a href="main.php">База</a> - отчет' , array( '../%UT/buttons.css' , '%UT/pre_select.css' ) , array() , 'hlp/main.html' );
		echo '<form method="get" action="'.$as[ 'addr' ].'" class="param-wrapper">
			<div class="param-area">
				<div>' ;
					switch ( $as[ 'type' ] ) {
						case 'ym1m2' :
							$ySelect = '<select size="1" name="y">'.makeSimpleSelectTagOptions1D( $yearsList ).'</select>' ;
							echo '<label>Год '.$ySelect.'
							</label>
							<label>
								с '.$m1Select.'
							</label>
							<label>
								по '.$m2Select.'
							</label>' ;

							break ;

						case 'm1y1m2y2' :
							$y1Select = '<select size="1" name="y1">'.makeSimpleSelectTagOptions1D( $yearsList ).'</select>' ;
							$y2Select = '<select size="1" name="y2">'.makeSimpleSelectTagOptions1D( $yearsList ).'</select>' ;
							echo '<label>С '.$m1Select.'
							</label>
							<label>
								'.$y1Select.'
							</label>
							<label>по '.$m2Select.'
							</label>
							<label>
								'.$y2Select.'
							</label>' ;

							break ;
					}
				echo '</div>
				<div>
					<input type="submit" value="Сформировать" class="btn3">
				</div>
			</div>
		</form>' ;

		if ( in_array( $_REQUEST[ 'as' ] , array( 'form-15.1' , 'form-15.1-129' , 'form-XLSX-15.1-129' ) ) ) {
			echo '<div class="tools-wrapper">
				<div class="tools-area">
					<a href="/maindb/reports/15-1.246/ed-129/utils/view-rep-details.php" class="btn3">Детали отчета 15.1</a>
					<a href="/maindb/reports/15-1.246/ed-129/utils/cmp-rep.php" class="btn3">Сравнение отчетов 15.1</a>
					<a href="/maindb/reports/15-1.246/ed-129/utils/rep-confirmation.php" class="btn3">приложение 2 к отчету</a>
					<a href="/maindb/reports/15-1.246/ed-129/utils/rep-confirmation.order-num.php" class="btn3">приложение 2 к отчету (сорт. по номерам)</a>
					<a href="/maindb/reports/15-1.246/ed-129/utils/rep-minjust-20231128.php" class="btn3">Отчет для Минюста 28.11.2023</a>
					<a href="/maindb/reports/15-1.246/ed-129/utils/rep-minjust-20240401-p4.php" class="btn3">Отчет для Минюста 01.04.2024 р4</a>
				</div>
			</div>' ;
		} else
		if ( in_array( $_REQUEST[ 'as' ] , array( 'form-15.1-88' , 'form-XLSX-15.1-88' ) ) ) {
			echo '<div class="tools-wrapper">
					<div class="tools-area">
						<a href="/maindb/reports/15-1.246/ed-88/utils/view-rep-details.php" class="btn3">Детали отчета 15.1</a>
						<a href="/maindb/reports/15-1.246/ed-88/utils/cmp-rep.php" class="btn3">Сравнение отчетов 15.1</a>
						<a href="/maindb/reports/15-1.246/ed-88/utils/rep-confirmation.php" class="btn3">приложение 2 к отчету</a>
						<a href="/maindb/reports/15-1.246/ed-88/utils/rep-confirmation.order-num.php" class="btn3">приложение 2 к отчету (сорт. по номерам)</a>
						<a href="/maindb/reports/15-1.246/ed-88/utils/rep-minjust-20231128.php" class="btn3">Отчет для Минюста 28.11.2023</a>
						<a href="/maindb/reports/15-1.246/ed-88/utils/rep-minjust-20240401-p4.php" class="btn3">Отчет для Минюста 01.04.2024 р4</a>
					</div>
				</div>' ;
		}

	closeHtml();
