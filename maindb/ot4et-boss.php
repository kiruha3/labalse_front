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
	 * @var $portalDB
	 * @var $dbConfig
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

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'EXTENTIONS' , $Rights ) ) {
			$mayListAll = in_array( 'EXP-EXP-LIST-ALL-REV' , $Rights[ 'EXTENTIONS' ] );
		} else {
			$mayListAll = false ;
		}

		$GoOut = !$mayListAll ;
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit();
	}

	$color = isset( $_REQUEST[ 'color' ] );

	if ( isset( $_REQUEST[ 'date' ] ) ) {
		$tmp = $_REQUEST[ 'date' ];
		$tmp = explode( '-' , $tmp );
		$ds = $tmp[ 0 ];
		$de = $tmp[ 1 ];

		$tabDepartments = $portalDB->table( 'departments' );
		$specs = $portalDB->query( "select `id` , concat( `group` , '.' , `num` , if ( `comment` is null , \"\" , concat( \" (\" , `comment` , \")\" ) ) ) as `spec` , `norm1` , `norm2` , `norm3` , `norm4` from `specialities`" , 'id' );
		$workersFID = $portalDB->query( "select `name` , `first_id` from `workers` order by `name` asc" , "first_id" );
		$tabCaseCategory = $portalDB->query( "select * from `casecategory`" , 'id' );

		$caps = array();

		$caps[ 0 ]= array();
		$caps[ 1 ]= array();
		$caps[ 2 ]= array();
		$caps[ 3 ]= array();
		$ci = 4 ;
		$groupWidth =  0 ;

		$catGroup = '<td class="cnv"><img src="ot4etruk.php?imgs='.urlencode( '1-й категории сложности' ).'" border=0></td>
			<td class="cnv"><img src="ot4etruk.php?imgs='.urlencode( '2-й категории сложности' ).'" border=0></td>
			<td class="cnv"><img src="ot4etruk.php?imgs='.urlencode( '3-й категории сложности' ).'" border=0></td>
			<td class="cnv"><img src="ot4etruk.php?imgs='.urlencode( 'Свыше 3-й категории сл.' ).'" border=0></td>' ;


		$ksIndex = $ksIndexWS = array( 1 , 2 , 3 , 4 ); // индексы категорий сложности
		array_unshift( $ksIndexWS , 'sum' ); // индексы категорий сложности с индексом для суммы
		$ccIndex = array();  // индексы категорий дела
		foreach( $tabCaseCategory as $cd ) {
			$ccIndex[]= $cd[ 'id' ];
			$caps[ 0 ][]= '<td class="cnh2" colspan="5">В том числе</td>' ;
			$caps[ 1 ][]= '<td class="cnh2" colspan="5">'.inForm( $cd[ 'name' ] , 1 , false ).'</td>' ;
			$caps[ 2 ][]= '<td class="cnv" rowspan="2"><img src="ot4etruk.php?imgs='.urlencode( 'Всего' ).'" border=0></td>
				<td class="cnh3" colspan="4">в том числе</td>' ;
			$caps[ 3 ][]= $catGroup ;
			$caps[ 4 ][]= $ci ;
			$ci+= 5 ;
		}
		$ccIndexWS = $ccIndex ;
		array_unshift( $ccIndexWS , 'sum' );  // индексы категорий дела с индексом для суммы

		$groupWidth = 5 * count( $tabCaseCategory );
		$groupElCount = count( $tabCaseCategory );

		$caps[ 3 ][]= $catGroup ;

		$normFactor = array(
			0 => 0.3 ,
			1 => 0.9 ,
			2 => 1.0 ,
			3 => 1.0 ,
			4 => 1.0 ,
			5 => 0.9 ,
		);

		MainHead_Print( '' , array( '%UT/ot4etruk.css' ) , array( 'inc' => array( 'files/ot4etruk.js' ) ) );

		echo '<center>СПРАВКА<br>
			<br>
			Обобщенные сведения об экспертной нагрузке оперативных работников<br>
			'.inForm( $dbConfig[ 'org.name.full.type' ] , 2 ).' '.inForm( $dbConfig[ 'org.name.full.name' ] , 1 ).' <br>
			'.inForm( $dbConfig[ 'org.name.full.head' ] , 2 ).' <br>
			( '.date( 'd.m.Y' , strtotime( $ds ) ).' - '.date( 'd.m.Y' , strtotime( $de ) ).' )<br>
			<br>
			<br>
		</center>
		<table align="center" class="MainTable">
			<tr>
				<td class="">Отдел</td>
				<td class="">Выполнено всего</td>
				<td class="">Часов всего</td>
				<td class="">Часов в рамках Г.З.</td>
				<td class="">С комиссионными часов всего</td>
			</tr>' ;

		//print_r_html( $tabDepartments );


		foreach( $tabDepartments as $cDep ) {
			$cDepID = $cDep[ 'id' ];
			$depData = $cDep ;
			$depName = $depData[ 'name' ];
			$workers = $portalDB->query( "select `id` , `name` , `spec` , `first_id` from `workers` where `dep` = ? order by `name` asc" , 'id' , 'i' , $cDepID );

			if ( count( $workers ) == 0 ) {
				echo '<tr><td colspan="5">'.$depName.'</td></tr>' ;
				continue ;
			}

			echo '<tr><td>'.$depName.'</td>' ;

			$ignoreLVL1IDs = array( 'no_id' );

			$calcTypes = array( 'no_com' , '' );

			foreach( $calcTypes as $cCalcType ) {
				$totalRes = array();

				foreach( $workers as $w ) {
					$wfid = $w[ "first_id" ];
					if ( !isset( $totalRes[ $wfid ] ) ) {
						$totalRes[ $wfid ] = array();
					}
					$q = "select
						`t3`.`spec_id` ,
						count( `t3`.`id` ) as `count` ,
						`t1`.`exp_type` ,
						`t2`.`kat_slognost`
					from
						`expertize` as `t3` ,
						`matincominglvl2` as `t2` ,
						`matincoming` as `t1`
					where
						( `t3`.`fin_date` between ? and ? ) and
						( `t3`.`exp_id` = ? ) and
						( `t3`.`state` = 1 ) and
						( `t2`.`id` = `t3`.`ext_id` ) and
						( `t1`.`id` = `t2`.`mat_id` ) and
						( `t2`.`kat_slognost` >= 1 )
						".( $cCalcType == 'no_com' ? " and ( not ( `t1`.`id` in ( \"".implode( "\",\"" , $ignoreLVL1IDs )."\" ) ) )" : "" )."
					group by
						`t1`.`exp_type` ,
						`t2`.`kat_slognost` ,
						`t3`.`spec_id`
					order by
						`t3`.`spec_id` asc ;" ;


					$specList = trim( trim( $w[ 'spec' ] ) , ';' );
					if ( strlen( $specList ) < 1 ) {
						$specList = array();
					} else {
						$specList = explode( ';' , $specList );
					}
					$specList[] = 0 ;
					sort( $specList , SORT_NUMERIC );

					$res = $portalDB->query( $q , false , 'ssi' , $ds , $de , $w[ 'id' ] );

					if ( $cCalcType == 'no_com' ) {
						$q2 = "select
							`t1`.`id`
						from
							`expertize` as `t3` ,
							`matincominglvl2` as `t2` ,
							`matincoming` as `t1`
						where
							( `t3`.`fin_date` between ? and ? ) and
							( `t3`.`exp_id` = ? ) and
							( `t3`.`state` = 1 ) and
							( `t2`.`id` = `t3`.`ext_id` ) and
							( `t1`.`id` = `t2`.`mat_id` ) and
							( `t2`.`kat_slognost` >= 1 ) and
						    ( not ( `t1`.`id` in ( \"".implode( '","' , $ignoreLVL1IDs )."\" ) ) );" ;

						$res2 = $portalDB->query( $q2 , 'id' , 'ssi' , $ds , $de , $w[ 'id' ] );

						$ignoreLVL1IDs = array_merge( $ignoreLVL1IDs , array_keys( $res2 ) );
					}

					foreach( $specList as $sl ) {
						if ( !isset( $totalRes[ $wfid ][ $sl ] ) ) {
							$totalRes[ $wfid ][ $sl ] = array();
							foreach( $ccIndexWS as $i ) {
								$totalRes[ $wfid ][ $sl ][ $i ] = array();
								foreach( $ksIndexWS as $j ) {
									$totalRes[ $wfid ][ $sl ][ $i ][ $j ] = 0 ;
								}
							}
						}
					}

					foreach( $res as $r ) {
						$totalRes[ $wfid ][ $r[ 'spec_id' ] ][ $r[ 'exp_type' ] ][ $r[ 'kat_slognost' ] ] += $r[ 'count' ];
					}
				}

				// [worker.first_id][spec_id][casecategory][kat_slognost]

				$fullTotal = array(
					'count' => array() ,
					'norm' => array()
				);

				foreach( $ccIndexWS as $i ) {
					$fullTotal[ 'count' ][ $i ] = array();
					$fullTotal[ 'norm' ][ $i ] = 0 ;
					foreach( $ksIndexWS as $j ) {
						$fullTotal[ 'count' ][ $i ][ $j ] = 0 ;
					}
				}

				foreach( $totalRes as $wfid => $rr ) {
					foreach( $rr as $sid => $data ) {
						foreach( $ccIndex as $i ) {
							foreach( $ksIndex as $j ) {
								$v = $rr[ $sid ][ $i ][ $j ];
								$rr[ $sid ][ $i ][ 'sum' ] += $v ;
								$fullTotal[ 'count' ][ $i ][ $j ] += $v ;
								$fullTotal[ 'count' ][ $i ][ 'sum' ] += $v ;
								$fullTotal[ 'norm' ][ $i ] += $v * $specs[ $sid ][ 'norm'.$j ];
							}
						}
					}
				}

				$total = array();
				foreach( $ksIndexWS as $j ) {
					$total[ $j ] = 0;
				}

				foreach( $ccIndex as $i ) {
					foreach( $ksIndexWS as $j ) {
						$total[ $j ] += $fullTotal[ "count" ][ $i ][ $j ];
					}
				}

				$fullTotal[ "norm" ][ "sum" ] = 0;
				foreach( $ccIndex as $i ) {
					$fullTotal[ "norm" ][ "sum" ] += $fullTotal[ "norm" ][ $i ];
				}

				if ( $cCalcType == 'no_com' ) {
					echo '<td>'.$total[ 'sum' ].'</td>
						<td>'.$fullTotal[ 'norm' ][ 'sum' ].'</td>
						<td>'.( $fullTotal[ 'norm' ][ 1 ] + $fullTotal[ 'norm' ][ 5 ] + $fullTotal[ 'norm' ][ 6 ] ).'</td>' ;
				} else {
					echo '<td>'.$fullTotal[ 'norm' ][ 'sum' ].'</td>' ;
				}
			}
			echo '</tr>' ;
		}

		closeHtml_Print();
    } else {
		$row = $portalDB->row( "select MONTH( min( `date` ) ) as `mim` , YEAR( min( `date` ) ) as `miy` , MONTH( max( `date` ) ) as `mam` , YEAR( max( `date` ) ) as `may` from `matincoming` where `date` is not null" );
		$row2 = $portalDB->row( "select MONTH( max( `fin_date` ) ) as `mam` , YEAR( max( `fin_date` ) ) as `may` from `expertize` where `fin_date` is not null" );

		if ( $row[ "may" ] < $row2[ "may" ] ) {
			$row[ "mam" ] = $row2[ "mam" ] ;
			$row[ "may" ] = $row2[ "may" ] ;
		} else
		if ( $row[ "may" ] == $row2[ "may" ] && $row[ "mam" ] < $row2[ "mam" ] ) {
			$row[ "mam" ] = $row2[ "mam" ] ;
		}

		$dateList = '' ;
		$m = $row[ 'mam' ];
		$y = $row[ 'may' ];
		while( $y > $row[ 'miy' ] || ( $y == $row[ 'miy' ] && $m >= $row[ 'mim' ] ) ) {
			$ms = ( $m < 10 ? '0' : '' ).$m ;
			$ds = date( 't' , mktime( 12 , 0 , 0 , $m , 1 , $y ) );
			$dateList.= '<option value="'.$y.'/'.$ms.'/01-'.$y.'/'.$ms.'/'.$ds.'">'.$ms.' . '.$y.'</option>' ;
			$m-- ;
			if ( $m < 1 ) {
				$y-- ;
				$dateList.= '<option disabled="disabled">----- '.$y.' -----</option>' ;
				$m = 12 ;
			}
		}

		MainHead_L2( 'База' , '<a href="./">База</a> - Статистика' , array( '%UT/viborka.css' ) , array( 'files/ot4etruk.js' ) , 'htl/main.php' );
		echo '<br><br><br><br><br>
		<form method="post" action="" target="_blank">
			<table align="center" class="IT">
				<tr>
					<td class="L">
						Месяц
					</td>
					<td class="D">
						<select size="1" name="date" id="date">
							'.$dateList.'
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="B">
						<input type="submit" value="Вывести статистику">
					</td>
				</tr>
			</table>
		</form>' ;

		closeHtml();
 	}
