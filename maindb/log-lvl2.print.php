<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	if ( isset( $_REQUEST[ "img" ] ) ) {

		function toUnicodeEntities( $text , $from = "w" ) {
			$text = convert_cyr_string( $text , $from , "i" );
			$uni = "" ;
			for ( $i = 0 , $len = strlen( $text ) ; $i < $len ; $i++ ) {
				$char = $text[ $i ];
				$code = ord( $char );
					$uni.= $code > 175 ? "&#".( 1040 + ( $code - 176 ) ).";" : $char ;
			}

			return $uni ;
		}

		header( "Content-type: image/png" );
		$font = "../files/fonts/times.ttf" ;
		$br = imagettfbbox( 14 , 0 , $font ,  toUnicodeEntities( urldecode( $_REQUEST[ "img" ] ) ) );

		$SH = $br[ 1 ] - $br[ 7 ] + 1 ;
		$SW = $br[ 2 ] - $br[ 0 ] + 1 ;

		$im = imagecreate( $SW , $SH );
		$bkColor = imagecolorallocate( $im , 255 , 255 , 255 );
		$fgColor = imagecolorallocate( $im , 0 , 0 , 0 );

		imagettftext( $im , 14 , 0 , 0 , $SH , $fgColor , $font ,  toUnicodeEntities( urldecode( $_REQUEST[ "img" ] ) ) );
		$im = imagerotate( $im , 90 , 0 );
		imagepng( $im );
		imagedestroy( $im );
		exit ;
	}

	include_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserDepartment
	 * @var $dbConfig
	 */

	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */

	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "LVL2CARD" , $Rights ) ) {
			$lvl2cardSEEALL = in_array( "SEEALL" , $Rights[ "LVL2CARD" ] );
			$GoOut = false ;
		} else {
			$lvl2cardSEEALL = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	$GoOut = !( isset( $_REQUEST[ "i_year" ] ) && isValidInt( $_REQUEST[ "i_year" ] ) && intval( $_REQUEST[ "i_year" ] ) >= 2008 && intval( $_REQUEST[ "i_year" ] ) <= 2020 );
	if ( $GoOut ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit ;
	}

	MainHead_L1( "Печать журнала - 2 уровень" , array( "%UT/log-lvl2.print.css" ) , false );

	$sa = true ;
	$sr = array();
	$se = array();
	for ( $i = 0 ; $i < 3 ; $i++ ) {
		$sr[ $i ] = isset( $_REQUEST[ "i_num_".$i ] ) && isValidInt( $_REQUEST[ "i_num_".$i ] , false );
		$se[ $i ] = array();
		for ( $j = 0 ; $j < 6 ; $j++ ) {
			$se[ $i ][ $j ] = isset( $_REQUEST[ "i_ed_".( $i * 6 + $j ) ] ) && $_REQUEST[ "i_ed_".( $i * 6 + $j ) ] == "on" && $sr[ $i ];
			$sa&= !$se[ $i ][ $j ];
		}
	}

	$tabCaseCategory = $portalDB->table( "casecategory" , "id" );

	echo "<div>
		<table class=\"MainTable".( $sa ? "Visible" : "Invisible" )."\">
			<caption>".$dbConfig[ "org.name.short" ]."</caption>
			<tr class=\"str".( $sa ? "v" : "i" )."\">
				<th class=\"col2\" rowspan=\"2\">
					Порядок вый номер экс пертизы
				</th>
				<th class=\"col2\" rowspan=\"2\">
					Дата поступлен ия материа лов
				</th>
				<th class=\"col3\" rowspan=\"2\">
					От кого поступили материалы, номер дела, Ф.И.О лиц, привлекаемых к ответст вености, сторон по делу
				</th>
				<th class=\"col4\" rowspan=\"2\">
					Предметы и документы, поступившие для исследования
				</th>
				<th class=\"col4\" rowspan=\"2\">
					Ф.И.О. и подпись эксперта получившего материалы
				</th>
				<th class=\"col5\" rowspan=\"2\">
					Сведения о приостановлении срока производства экспертизы
				</th>
				<th class=\"col6\" colspan=\"2\">
					Движение материалов
				</th>
				<th class=\"col7\" rowspan=\"2\">
					Виды исследований. Кол-во и характер выводов.Срок произ-ва экспертизы
				</th>
				<th class=\"col10\" rowspan=\"2\">
					Дата окон чания экспер тизы
				</th>
				<th class=\"col8\" rowspan=\"2\">
					Дата передачи в подраз деление делопроиз водства заключения
				</th>
			</tr>
			<tr class=\"str".( $sa ? "v" : "i" )."\">
				<th class=\"col9\">
					Перечень переда ваемых предметов
				</th>
				<th class=\"col9\">
					Куда и кому переданы
				</th>
			</tr>" ;

		$tabDep = $portalDB->table( "departments" , "id" );
		$tabWorkers = $portalDB->table( "workers" , "id" );
		$tabSpecGr = $portalDB->table( "specialities-groups" , "id" );
		$tabSpec = $portalDB->table( "specialities" , "id" );

	$rd = array();
	for ( $ri = 0 ; $ri < count( $sr ) ; $ri++ ) {

		for( $j = 0 ; $j <= 10 ; $j++ ) {
			$rd[ $j ] = "" ;
		}

		if ( $sr[ $ri ] ) {
			$rid = matincomingID( intval( $_REQUEST[ "i_num_".$ri ] ) , intval( $_REQUEST[ "i_year" ] ) );
			$row1 = $portalDB->simpleRow( "matincoming" , $rid );
			$rd[ 0 ] = intval( $_REQUEST[ "i_num_".$ri ] )." / ".$tabDep[ $UserDepartment ][ "ind" ].( $row1[ "exp_type" ] != 0 ? " - ".$tabCaseCategory[ $row1[ "exp_type" ] ][ "index" ] : "" );
			$agency = $portalDB->simpleRow( "agency" , $row1[ "from_agency" ] );
			$agent = $portalDB->simpleRow( "agent" , $row1[ "from_agent" ] );
			$row2 = $portalDB->row( "select * from `matincominglvl2` where ( `mat_id` = ? )".( $lvl2cardSEEALL ? "" : " and ( `dep_id` = ".Int2SQL( $UserDepartment )." )" ) , "s" , $rid );
			$row3 = $portalDB->simpleRow( "expertize" , array( "ext_id" => $row2[ "id" ] ) );
			$worker = $tabWorkers[ $row3[ "exp_id" ] ];

			$rd[ 1 ] = date( "d-m-Y" , strtotime( $row2[ "date" ] ) );

			if ( $sa ) {
				$rd[ 2 ] = $agency[ "name" ].", ".$agent[ "name" ].", ".$row1[ "ex_data_3" ].", ".$row1[ "ex_data_4" ];
				$rd[ 3 ] = $row2[ "materials" ];
				$rd[ 4 ] = NAMES_Format( NAMES_parse( $worker[ "name" ] ) , "%F1 %i.%o" ).", ".$row2[ "ex_data_6" ];
				$rd[ 5 ] = $row2[ "ex_data_7" ];
				$rd[ 6 ] = $row2[ "ex_data_8" ];
				$rd[ 7 ] = $row2[ "ex_data_9" ];
				$rd[ 8 ] = $tabSpecGr[ $tabSpec[ $row3[ "spec_id" ] ][ "group" ] ][ "index" ].".".$tabSpec[ $row3[ "spec_id" ] ][ "num" ];
				$rd[ 9 ] = $row3[ "state" ] > 0 ? date( "d-m-Y" , strtotime( $row3[ "fin_date" ] ) ) : "" ;
				$rd[ 10 ] = $row2[ "ex_data_12" ];
			} else {

				$ind0 = Array( 0 , 1 , 2 , 4 );
				$ind1 = Array( 7 , 8 , 9 , 12 );
				$ind2 = Array( 5 , 6 , 7 , 10 );
				for ( $ind = 0 ; $ind < 4 ; $ind++ ) {
					if ( $se[ $ri ][ $ind0[ $ind ] ] ) {
						$cp = strrpos( $row2[ "ex_data_".$ind1[ $ind ] ] , "," );
						if ( $cp !== false ) {
							$rd[ $ind2[ $ind ] ] = substr( $row2[ "ex_data_".$ind1[ $ind ] ] , 0 , $cp )."<font color=#000000>".substr( $row2[ "ex_data_".$ind1[ $ind ] ] , $cp )."</font>" ;
						} else {
							$rd[ $ind2[ $ind ] ] = "<font color=#000000>".$row2[ "ex_data_".$ind1[ $ind ] ]."</font>" ;
						}
					}
				}


				if ( $se[ $ri ][ 5 ] ) {
					$rd[ 8 ] = $tabSpecGr[ $tabSpec[ $row3[ "spec_id" ] ][ "group" ] ][ "index" ].".".$tabSpec[ $row3[ "spec_id" ] ][ "num" ] ;
				}

				if ( $se[ $ri ][ 3 ] ) {
					$rd[ 9 ] = $row3[ "state" ] > 0 ? date( "d-m-Y" , strtotime( $row3[ "fin_date" ] ) ) : "" ;
				}
			}
		}
		echo "<tr class=\"str1".( $sa ? "v" : "i" )."\">
			<td class=\"dat-col2\">".( $sa ? "<img src=\"log-lvl2.print.php?img=".urlencode( $rd[ 0 ] )."\" border=0>" : "" )."</td>
			<td class=\"dat-col2\">".( $sa ? "<img src=\"log-lvl2.print.php?img=".urlencode( $rd[ 1 ] )."\" border=0>" : "" )."</td>" ;
		$ea = array( 3 , 4 , 4 , 5 , 9 , 9 , 7 , 10 , 8 );
		for ( $i = 0 ; $i < count( $ea ) ; $i++ ) {
			if ( $i == 7 ) {
				if ( $rd[ $i + 2 ] != "" ) {
					echo "<td class=\"dat-col".$ea[ $i ]."\"><img src=\"log-lvl2.print.php?img=".urlencode( $rd[ $i + 2 ] )."\" border=0></td>" ;
				} else {
					echo "<td class=\"dat-col".$ea[ $i ]."\">".$rd[ $i + 2 ]."</td>" ;
				}
			} else {
				echo "<td class=\"dat-col".$ea[ $i ]."\">".$rd[ $i + 2 ]."</td>" ;
			}
		}

		echo "</tr>" ;
	}

	echo "</table></div></body></html>";
?>