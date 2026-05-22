<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
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

	MainHead_Print( "" , array( "%UT/log-lvl1.print.css" ) );

	$tabCaseCategory = $portalDB->table( "casecategory" , "id" );

	$ad = false ;
	$mn = false ;
	for ( $i = 1 ; $i <= 3 ; $i++ ) {
		for ( $j = 1 ; $j <= 3 ; $j++ ) {
			$ad |= isset( $_POST[ "i_ex_data_".$i."_".$j ] );
		}
		$mn |= ( isset( $_POST[ "i_mat_number_".$i ] ) && preg_match( "/^\\d+$/" , $_POST[ "i_mat_number_".$i ] ) == 1 );
	}

	if ( !$mn ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm( "Не задан ни один номер экспертизы" , "Сообщение" , "назад" , "log-lvl1.php" );
		closeHtml();
		exit ;
	}

	$td = array();

	for ( $i = 1 ; $i <= 3 ; $i++ ) {
		$tmp = array();
		for ( $j = 0 ; $j < 9 ; $j++ ) {
			$tmp[]= "" ;
		}
		$td[ $i ]= $tmp ;
	}

	if ( isset( $_POST[ "i_year" ] ) ) {
		$yp = intval( $_POST[ "i_year" ] );
	}

	if ( $ad ) {
		$tcs = "MainTable-White" ;

		$map = array( 0 => "7" , 1 => "8" , 2 => "9" );

		for( $mni = 1 ; $mni <= 3 ; $mni++ ) {
			$k = array( "" , "" , "" );
			if ( isset( $_POST[ "i_mat_number_".$mni ] ) && preg_match( "/^\\d+$/" , $_POST[ "i_mat_number_".$mni ] ) == 1 ) {
				$qq = matincomingID( intval( $_POST[ "i_mat_number_".$mni ] ) , $yp );

				$row = $portalDB->row( "select
						`t1`.`id` ,
						`t1`.`date` ,
						`t2`.`name` as `agency` ,
						`t3`.`name` as `agent` ,
						`t1`.`ex_data_4` ,
						`t1`.`ex_data_3` ,
						`t1`.`exp_type` ,
						`t1`.`ex_data_6` ,
						`t1`.`ex_data_7` ,
						`t1`.`ex_data_8` ,
						`t1`.`ex_data_9`
					from
						`matincoming` as `t1` ,
						`agency` as `t2` ,
						`agent` as `t3`
					where
						( `t1`.`from_agency` = `t2`.`id` ) and
						( `t1`.`from_agent` = `t3`.`id` ) and
						( `t1`.`id` = ? )" , "s" , $qq
				);

				for ( $api = 0 ; $api < 3 ; $api++ ) {

					if( isset( $_POST[ "i_ex_data_".$mni."_".( $api + 1 ) ] ) ) {
						$eda = explode( "," , $row[ "ex_data_".$map[ $api ] ] );
						$new = count( $eda ) - 1 ;
						if( $new > 0 ) {
							for( $i = 0 ; $i < $new ; $i++ ) {
								$k[ $api ] .= $eda[ $i ]."<br><br>" ;
							}
						}

						$k[ $api ] .= "<span class=\"blacktext\">".$eda[ $new ]."</span>" ;
					} else {
						$eda = explode( "," , $row[ "ex_data_".$map[ $api ] ] );
						$new = count( $eda ) - 1 ;
						if( $new > 0 ) {
							for( $i = 0 ; $i < $new ; $i++ ) {
								$k[ $api ] .= $eda[ $i ]."<br><br>" ;
							}
						}

						$k[ $api ] .= $eda[ $new ];
					}
				}

				$td[ $mni ][ 0 ] = matincomingNumberFull( $row[ "id" ] , null , $row[ "exp_type" ] );
				$td[ $mni ][ 1 ] = date( "d-m-Y" , strtotime( $row[ "date" ] ) );
				$td[ $mni ][ 2 ] = $row[ "agency" ].", ".$row[ "agent" ].", ".$row[ "ex_data_3" ];
				$td[ $mni ][ 3 ] = $row[ "ex_data_4" ];
				$td[ $mni ][ 5 ] = $row[ "ex_data_6" ];

				$td[ $mni ][ 6 ] = $k[ 0 ];
				$td[ $mni ][ 7 ] = $k[ 1 ];
				$td[ $mni ][ 8 ] = $k[ 2 ];
			}
		}
	} else {
		$tcs = "MainTable-Black" ;

		for( $mni = 1 ; $mni <= 3 ; $mni++ ) {
			if ( isset( $_POST[ "i_mat_number_".$mni ] ) && preg_match( "/^\\d+$/" , $_POST[ "i_mat_number_".$mni ] ) == 1 ) {
				$qq = matincomingID( intval( $_POST[ "i_mat_number_".$mni ] ) , $yp );

				$row = $portalDB->row( "select
						`t1`.`id` ,
						`t1`.`date` ,
						`t2`.`name` as `agency` ,
						`t3`.`name` as `agent` ,
						`t1`.`ex_data_4` ,
						`t1`.`ex_data_3` ,
						`t1`.`exp_type` ,
						`t1`.`ex_data_6` ,
						`t1`.`ex_data_7` ,
						`t1`.`ex_data_8` ,
						`t1`.`ex_data_9`
					from
						`matincoming` as `t1` ,
						`agency` as `t2` ,
						`agent` as `t3`
					where
						( `t1`.`from_agency` = `t2`.`id` ) and
						( `t1`.`from_agent` = `t3`.`id` ) and
						( `t1`.`id` = ? )" , "s" , $qq );

				$td[ $mni ][ 0 ] = matincomingNumberFull( $row[ "id" ] , null , $row[ "exp_type" ] );
				$td[ $mni ][ 1 ] = date( "d-m-Y" , strtotime( $row[ "date" ] ) );
				$td[ $mni ][ 2 ] = $row[ "agency" ].", ".$row[ "agent" ].", ".$row[ "ex_data_3" ];
				$td[ $mni ][ 3 ] = $row[ "ex_data_4" ];
				$td[ $mni ][ 5 ] = $row[ "ex_data_6" ];
			}
		}
	}

	echo "<table align=\"center\" class=\"".$tcs."\">
	<tr>
		<td class=\"mt-c-1\">
			Порядковый номер экспертизы
		</td>
		<td class=\"mt-c-2\">
			Дата поступления материалов
		</td>
		<td class=\"mt-c-3\">
			От кого поступили материалы, постановление и д.р.
		</td>
		<td class=\"mt-c-4\">
			Номер дела;
			Количество томов, страниц, приложений;
			Ф.И.О. лиц, привлекаемых к ответственности, сторон по делу
		</td>
		<td class=\"mt-c-5\">
			Вид экспертизы
		</td>
		<td class=\"mt-c-6\">
			Ф.И.О. и подпись работника подразделения, получившего материалы, дата получения
		</td>
		<td class=\"mt-c-7\">
			Сведения о приостановлении срока производства экспертизы
		</td>
		<td class=\"mt-c-8\">
			Дата сдачи заключения, акта, сообщения, письма о возврате без исполнения и материалов для отправки
		</td>
		<td class=\"mt-c-9\">
			Дата и способ отправки заключения, акта, сообщения, письма о возврате без исполнения и материалов
		</td>
	</tr>" ;

	for ( $nmi = 1 ; $nmi <= 3 ; $nmi++ ) {
		echo "<tr>" ;
		for ( $i = 0 ; $i < 9 ; $i++ ) {
			$v = $td[ $nmi ][ $i ];
			$v = str_replace( "," , ", " , $v );
			$v = str_replace( "  " , " " , $v );
			echo "<td class=\"mt-d-".( $i + 1 )."\">".$v."</td>" ;
		}
		echo "</tr>" ;
	}

	echo "</table>" ;

	closeHtml_Print();
?>