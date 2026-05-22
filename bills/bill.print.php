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
	 * @var $UserRights
	 * @var $UserID
	 * @var $dbConfig
	 * @var $UserThemeLoc
	 * @var $dbConfigFull
	 * @var $UserName
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	function isValidBillID( $id ) {
		global $portalDB ;
		$row = $portalDB->row( "select count(*) as `count` from `bills` where `id`= ? and `date` is not null;" , "i" , $id );
		return $row[ 'count' ] == 1 ;
	}

	$modeAjax = isset( $_REQUEST[ 'mode' ] ) && $_REQUEST[ 'mode' ] == 'ajax' ;
	$GoOut = true ;
	if ( count($UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'VIEW_BASE' , $Rights ) ) {
			$listVIEW_SD = in_array( 'VIEW_SD' , $Rights[ 'VIEW_BASE' ] );
			$listVIEW_OD = in_array( 'VIEW_OD' , $Rights[ 'VIEW_BASE' ] );

			$GoOut = !( $listVIEW_SD || $listVIEW_OD );
		} else {
			$listVIEW_SD = $listVIEW_OD = false ;
		}

		if ( array_key_exists( 'EXTENTIONS' , $Rights ) ) {
			$mayRCAdd = in_array( 'PRINT-ADDRESS-LABEL' , $Rights[ 'EXTENTIONS' ] );
		} else {
			$mayRCAdd = false ;
		}
	}

	if ( $GoOut ) {
		MainHead_L2('' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html');
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit ;
	}

	$tabPosts = $portalDB->table( 'posts' , 'id' );
	$row = $portalDB->row( "select `worker_id` from `accounts` where `id` = ?" , 'i' , $UserID );
	$WorkerID = $row[ 'worker_id' ] ;

	$boss = NAMES_Format( NAMES_parse( $dbConfig[ 'org.boss' ][ 'name' ] ) );
	$bossPost = $tabPosts[ $dbConfig[ 'org.boss' ][ 'post_1_id' ] ][ 'name' ];
	$accountantGeneral = NAMES_Format( NAMES_parse( $dbConfig[ 'org.accountantGeneral' ][ 'name' ] ) );
	$agPost = $tabPosts[ $dbConfig[ 'org.accountantGeneral' ][ 'post_1_id' ] ][ 'name' ];
	if ( isset( $dbConfig[ 'bills.NDS' ] ) ) {
		if ( $dbConfig[ 'bills.NDS' ] == 'no-nds' ) {
			$NDS = false ;
		} else {
			$NDS = intval( $dbConfig[ 'bills.NDS' ] , 10 );
		}
	} else {
		$NDS = 20 ;
	}

	function calcPrices( $p , $c , $nds ) {
		$pt = $p * $c ;
		if ( $nds === false ) {
			$vat = 0 ;  /* НДС численно */
		} else {
			$vat = round( $pt - ( $pt / ( ( 100 + $nds ) / 100 ) ) , 2 );  /* НДС численно */
		}

		$cl = $pt - $vat ; /* Сумма без НДС */
		$mcl = round( $p / ( ( 100 + $nds ) / 100 ) , 2 );  /* Сумма без НДС за 1 ед. */
		return array( $pt , $vat , $cl , $mcl );
	}

	function NDSlabel( $p ) {
		global $NDS ;
		if ( $NDS !== false ) {
			return ( $p / ( ( 100 + $NDS ) / 100 ) );
		} else {
			return 'без НДС' ;
		}
	}


	if ( $modeAjax ) {
		header( 'Content-Type: text/xml' );
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: '.date( 'r' ) );
		header( 'Expires: -1' , false );

		echo '<?xml version="1.0" encoding="windows-1251" ?>' ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ 'data' ] );

		$data = $DD->documentElement ;

		switch( $data->nodeName ) {
			case 'search-bills' :
				$iid = Int2SQL( $data->getAttribute( 'id' ) );
				$item = $portalDB->row( "select * from `items` where `id` = ".$iid );
				$res = $portalDB->query( "select * from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`name` = ".Str2SQL( $item[ 'name' ] )." ) and ( `t2`.`id` <> ".$iid." )" );
				echo '<result>' ;
				foreach ( $res as $bi ) {
					echo '<bi id="'.$bi[ 'ext_id' ].'">'.toCDATA( $bi[ 'number' ].' от '.date( 'd-m-Y' , strtotime( $bi[ 'date' ] ) ) ).'</bi>' ;
				}
				echo '</result>' ;
				break ;
		}

		exit();
	}

	if ( isset( $_REQUEST[ 'id' ] ) ) {
		$v_id = Int2SQL( $_REQUEST[ 'id' ] );
	} else
	if ( isset( $_REQUEST[ 'n' ] ) && isset( $_REQUEST[ 'y' ] ) ) {
		$row = $portalDB->row( "select * from `bills` where ( `number` = ".Int2SQL( $_REQUEST[ 'n' ] )." ) and ( YEAR( `date` ) = ".Int2SQL( $_REQUEST[ 'y' ] )." )" );
		if ( $row !== false ) {
			$v_id = $row[ 'id' ];
		} else {
			$v_id = -1 ;
		}
	} else {
		$v_id = -1 ;
	}


	$row = $portalDB->row( "select `b`.* , concat( 'КБК ' , `r`.`reason_code` , ' ' , `r`.`reason_text` ) as `reason` from `bills` as `b` , `reasons` as `r` where ( `b`.`reason_id` = `r`.`id` ) and ( `b`.`id` = ".Int2SQL( $v_id )." ) limit 1;" );
	$v_bill_number = $row[ 'number' ];
	$v_date = date( 'd.m.Y' , strtotime( $row[ 'date' ] ) );
	$v_payer = $row[ 'payer' ];
	$v_address = $row[ 'address' ];
	$v_customer = $row[ 'customer' ];
	$v_reason = $row[ 'reason' ];

	$tabItems = $portalDB->query( "select * from `items` where ( `ext_id` = ".Int2SQL( $v_id )." );" );
	$linkedBills = array();
	foreach ( $tabItems as &$ti ) {
		$ti[ 'price' ]/= 100 ;
		if ( !is_null( $ti[ 'from' ] ) && $ti[ 'from' ] != 0 ) {
			$linkedBills[]= $ti[ 'from' ];
		}
	} unset( $ti );
	$linkedBills = array_unique( $linkedBills );
	if ( count( $linkedBills ) > 0 ) {
		$linkedBills = $portalDB->query( "select * from `bills` where ( `id` in ( ".implode( ',' , $linkedBills )." ) )" , 'id' );
		foreach ( $linkedBills  as &$lb ) {
			$lb = '<a href="bill.print.php?id='.$lb[ 'id' ].'" target="_blank">'.$lb[ 'number' ].' от '.date( 'd-m-Y' , strtotime( $lb[ 'date' ] ) ).'</a>' ;
		} unset( $lb );
	}

	$lnk = $portalDB->query( "select `t1`.* from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`from` = ".Int2SQL( $v_id )." )" );
	foreach( $lnk as &$l ) {
		$l = '<a href="bill.print.php?id='.$l[ 'id' ].'" target="_blank">'.$l[ 'number' ].' от '.date( 'd-m-Y' , strtotime( $l[ 'date' ] ) ).'</a>' ;
	} unset( $l );

	$lnk = implode( '' , $lnk );

	MainHead_L1( 'Печатать счет' , array( '../%UT/buttons.css' , '%UT/bill.print.css' ) , false , array( '@/files/labeling/brother--ql-570/main.js' , 'files/bill.print.js' ) );

	function ca( $str ) {
		return ClearOutputText( $str , array( array( '"' , '&quot;' ) ) );
	}

	echo '<div class="menu">
		<div>
			<input id="bill-tgt-ex-cb" type="checkbox" onclick="tbpte();"> добавить назначение в платежа<br><textarea id="bill-tgt-ex" class="bill-tgt-ex" onkeyup="tbpte()"></textarea>
		</div>
		<div>
			<input id="bill-date-ex-cb" type="checkbox" onclick="tbpde( \''.$v_date.'\' );"> заменить дату <input id="bill-date-ex" type="text" value="'.date( 'd.m.Y' ).'" class="bill-date-ex" onkeyup="tbpde( \''.$v_date.'\' )">
		</div>
		<hr>
		<div>
			<input id="bill-price-wVAT-cb" type="checkbox" onclick="tbppwVAT();"> Показывать доп. информацию
		</div>' ;

	if ( $mayRCAdd ) {
		echo '<div>
			<a onclick="printLetterLabel('.$v_id.')" title="адресные наклейки" class="btn3"><img src="themes/'.$UserThemeLoc.'/btn-letter.png" border=0> Адресная наклейка</a>
		</div>' ;
	}

	if ( strlen( $lnk ) > 0 ) {
		echo '<hr><div class="linked">
			<div>Связанные счета</div>
			'.$lnk.'
		</div>' ;
	}

	echo '<hr>
		<div><a href="bill.php?edit='.$v_id.'" class="btn3">Внести изменения</a> <a href="bill-2.print.php?id='.$v_id.'" class="btn3">Квитанция</a></div>
	</div>' ;

	$signatures = json_decode( $dbConfig[ 'bills.signatures' ] , true );

	$pbd = json_decode( $dbConfig[ 'bills.printBankData' ] );
	$pbd = array_combine( $pbd , $pbd );
	$pbd2 = array_intersect_key( $dbConfig , $pbd );
	$pbd2F = array_intersect_key( $dbConfigFull , $pbd );
	$pbd = array_merge( $pbd , $pbd2 );
	$pbdN = array_column( array_merge( $pbd , $pbd2F ) , 'label_short' );

	echo '<div id="page">
		<div id="page-content">
			<div id="title">
				<div id="title-agency">' ;
					if ( isset( $dbConfig[ 'bills.warnings' ] ) && trim( $dbConfig[ 'bills.warnings' ] )  != '' ) {
						$tmp = trim( $dbConfig[ 'bills.warnings' ] );
						$tmp = str_replace( "\r\n" , "\n" , $tmp );
						$tmp = str_replace( "\n" , '<br>' , $tmp );
						echo $tmp.'<br><br>' ;
					}
					echo inForm( $dbConfig[ 'org.name.full.type' ] , 1 ).'<br>
					'.inForm( $dbConfig[ 'org.name.full.name' ] , 1 ).'<br>
					'.inForm( $dbConfig[ 'org.name.full.head' ] , 2 ).'
					'.( isset( $dbConfig[ 'bills.orgNameExt' ] ) && $dbConfig[ 'bills.orgNameExt' ] == '{"type":"inn-kpp"}' ? '<br>ИНН '.$dbConfig[ 'org.inn' ].' КПП '.$dbConfig[ 'org.kpp' ] : '' ).'
				</div>
				<br>
				<div id="title-address">
					<div>Адрес: '.$dbConfig[ 'org.address' ].',</div> <div>тел./факс: ('.$dbConfig[ 'org.phone.code' ].') '.$dbConfig[ 'org.phone' ].'</div>
				</div>
			</div>
			<table align="center" id="prop-table">
				<tr>
					<td>
						Получатель: '.$dbConfig[ 'org.beneficiary.name.simple' ].'<br>
						('.$dbConfig[ 'org.name.short' ].' л/с '.$dbConfig[ 'org.clientAccount' ].')
					</td>
					<td class="prop-table-col-2">
						ИНН<br>
						КПП<br>
						ОКТМО<br>
						Р/счет
					</td>
					<td>
						'.$dbConfig[ 'org.inn' ].'<br>
						'.$dbConfig[ 'org.kpp' ].'<br>
						'.$dbConfig[ 'org.oktmo' ].'<br>
						'.$dbConfig[ 'org.beneficiary.accountNumber' ].'
					</td>
				</tr>
				<tr>
					<td>
						Банк получателя:<br>
						'.$dbConfig[ 'org.bank.name' ].'
					</td>
					<td class="prop-table-col-2">
						'.implode( '<br>' , $pbdN ).'
					</td>
					<td>
						'.implode( '<br>' , $pbd ).'
					</td>
				</tr>
				<tr>
					<td colspan=3>
						Назначение платежа: '.$v_reason.'<span id="bill-tgt"></span>
					</td>
				</tr>
			</table>
			<div id="bill-header">
				Счет № <span id="bill-number">'.$v_bill_number.'</span> от <span id="bill-date">'.$v_date.'</span>
			</div>
			<table align="center" id="pac-table">
				<tr>
					<td class="pac-table-col-1">
						Плательщик
					</td>
					<td class="pac-table-col-2">
						'.ca( $v_payer ).'
					</td>
				</tr>
				<tr>
					<td class="pac-table-col-1">
						Адрес плательщика
					</td>
					<td class="pac-table-col-2">
						'.ca( $v_address ).'
					</td>
				</tr>
				<tr>
					<td class="pac-table-col-1">
						Заказчик
					</td>
					<td class="pac-table-col-2">
						'.ca( $v_customer ).'
					</td>
				</tr>
			</table>
			<table id="items-table">
				<tr>
					<td class="items-table-head-0">
						№
					</td>
					<td class="items-table-head-1">
						Предмет счета
					</td>
					<td class="items-table-head-2">
						Кол-во
					</td>
					<td class="items-table-head-3">
						Цена, руб.
					</td>
					<td class="items-table-head-4">
						Сумма, руб.
					</td>
				</tr>' ;

					$total_1 = 0 ;
					$total_2 = 0 ;
					$total_3 = 0 ;
					$total_5 = 0 ;
					$total_4 = count( $tabItems );
					for ( $i = 0 ; $i < $total_4 ; $i++ ) {
						$ti = $tabItems[ $i ];
						list( $total_p1 , $total_p2 , $total_p3 , $total_p4 ) = calcPrices( $ti[ 'price' ] , $ti[ 'count' ] , $NDS );
						echo '<tr id="row'.$i.'" onclick="ts('.$i.')" class="items-row">
							<td class="items-table-col-0">
								'.( $i + 1 ).'
							</td>
							<td class="items-table-col-1">
								'.ca( $ti[ 'name' ] ).'
							</td>
							<td class="items-table-col-2">
								'.$ti[ 'count' ].'
							</td>
							<td class="items-table-col-3">
								'.money_format( '%!i' , $total_p4 ).'
							</td>
							<td class="items-table-col-4">
								<div class="pr-d1">
									<div class="pr-d2">
										'.money_format( "%!i" , $total_p3 ).'<br>
										<span class="wVAT" title="С НДС">'.money_format( '%!i' , $total_p4 ).'</span>
									</div>
									<div class="bill-lnk-area">
										<div>'.( !is_null( $ti[ 'from' ] ) && isset( $linkedBills[ $ti[ 'from' ] ] ) ? $linkedBills[ $ti[ 'from' ] ] : '<a id="search-bill-lnk-'.$ti[ 'id' ].'" class="search-bill-lnk" onclick="doSearchBill( \''.$ti[ 'id' ].'\' )">Найти похожие счета</a>' ).'</div>
									</div>
								</div>
							</td>
						</tr>' ;
						$total_1+= $total_p1 ;
						$total_2+= $total_p2 ;
						$total_3+= $total_p3 ;
					}

					$total_5 = price2word( round( $total_1 , 2 ) );

					echo '<tr>
						<td colspan=2 class="items-table-total-0">
						</td>
						<td colspan=2 class="items-table-total-1">
							Итого
						</td>
						<td class="items-table-total-2">
							'.money_format( "%!i" , $total_3 ).'
						</td>
					</tr>
				<tr>
					<td colspan="2" class="items-table-total-0">
					</td>
					<td colspan="2" class="items-table-total-1">
						НДС
					</td>
					<td class="items-table-total-2">
						'.( $NDS !== false ? money_format( '%!i' , $total_2 ) : 'без НДС' ).'
					</td>
				</tr>
				<tr>
					<td colspan="2" class="items-table-total-0">
					</td>
					<td colspan="2" class="items-table-total-1-b">
						Всего к оплате
					</td>
					<td class="items-table-total-2-b">
						'.money_format( '%!i' , $total_1 ).'
					</td>
				</tr>
			</table>
			<div id="bill-total">
				Всего наименований <span id="bill-total-count">'.$total_4.'</span><br>
				На сумму <span id="bill-total-price">'.$total_5.'</span>
			</div>
			<table id="bill-end">
				'.( isset( $signatures[ 'boss' ] ) && $signatures[ 'boss' ] == 1 ? '<tr>
					<td>
						'.$bossPost.'
					</td>
					<td>
						_____________________________<input id="bp_ruk_i" type="checkbox" checked class="bp-i" onclick="tbps( \'ruk\' );"><span id="bp_ruk" class="bp-ruk">/ '.$boss.' /</span>
					</td>
				</tr>' : '' ).'
				'.( isset( $signatures[ 'ag' ] ) && $signatures[ 'ag' ] == 1 ? '<tr>
					<td>
						'.$agPost.'
					</td>
					<td>
						_____________________________<input id="bp_gb_i" type="checkbox" checked class="bp-i" onclick="tbps( \'gb\' );"><span id="bp_gb" class="bp-gb">/ '.$accountantGeneral.' /</span>
					</td>
				</tr>' : '' ).'
				'.( isset( $signatures[ 'user' ] ) && $signatures[ 'user' ] == 1 ? '<tr>
					<td>
						Счет выписал
					</td>
					<td>
						_____________________________<input id="bp_exp_i" type="checkbox" checked class="bp-i" onclick="tbps( \'exp\' );"><span id="bp_exp" class="bp-exp">/ '.NAMES_Format( NAMES_parse( $UserName ) ).' /</span>
					</td>
				</tr>' : '' ).'
			</table>' ;
		echo '</div>
		</div>
	</body>
</html>' ;
