<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "../core.php" );
	/**
	 * @var $LoginOk
	 * @var TDB $portalDB
	 * @var $UserRights
	 * @var $UserID
	 * @var $dbConfig
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	function isValidBillID( $id ) {
		global $portalDB ;
		$row = $portalDB->row( "select count(*) as `count` from `bills` where `id`= ? and `date` is not null;" , "i" , $id );
		return $row[ "count" ] == 1 ;
	}

	$modeAjax = isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ;

	if ( count($UserRights) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "VIEW_BASE" , $Rights ) ) {
			$listVIEW_SD = in_array( "VIEW_SD" , $Rights[ "VIEW_BASE" ] );
			$listVIEW_OD = in_array( "VIEW_OD" , $Rights[ "VIEW_BASE" ] );

			$GoOut = !( $listVIEW_SD || $listVIEW_OD );
		} else {
			$listVIEW_SD = $listVIEW_OD = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		MainHead_L2("", "", array( "../%UT/buttons.css" , "../%UT/forms.css"), array(), "hlp/no_access.html");
		echo "<br><br><br><br><br>";
		MessageForm();
		closeHtml();
		exit;
	}

	$row = $portalDB->row( "select `worker_id` from `accounts` where `id` = ?" , "i" , $UserID );
	$WorkerID = $row[ "worker_id" ] ;

	$boss = NAMES_Format( NAMES_parse( $dbConfig[ "org.boss" ][ "name" ] ) );
	$accountantGeneral = NAMES_Format( NAMES_parse( $dbConfig[ "org.accountantGeneral" ][ "name" ] ) );


	if ( $modeAjax ) {
		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ "data" ] );

		$data = $DD->documentElement ;

		switch( $data->nodeName ) {
			case "search-bills" :
				$iid = Int2SQL( $data->getAttribute( "id" ) );
				$item = $portalDB->row( "select * from `items` where `id` = ".$iid );
				$res = $portalDB->query( "select * from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`name` = ".Str2SQL( $item[ "name" ] )." ) and ( `t2`.`id` <> ".$iid." )" );
				echo "<result>" ;
				foreach ( $res as $bi ) {
					echo "<bi id=\"".$bi[ "ext_id" ]."\">".toCDATA( $bi[ "number" ]." от ".date( "d-m-Y" , strtotime( $bi[ "date" ] ) ) )."</bi>" ;
				}
				echo "</result>" ;
				break ;
		}

		exit();
	}

	if ( isset( $_REQUEST[ "id" ] ) ) {
		$v_id = Int2SQL( $_REQUEST[ "id" ] );
	} else
	if ( isset( $_REQUEST[ "n" ] ) && isset( $_REQUEST[ "y" ] ) ) {
		$row = $portalDB->row( "select * from `bills` where ( `number` = ".Int2SQL( $_REQUEST[ "n" ] )." ) and ( YEAR( `date` ) = ".Int2SQL( $_REQUEST[ "y" ] )." )" );
		if ( $row !== false ) {
			$v_id = $row[ "id" ];
		} else {
			$v_id = -1 ;
		}
	} else {
		$v_id = -1 ;
	}


	$row = $portalDB->row( "select `b`.* , `r`.`reason_code` from `bills` as `b` , `reasons` as `r` where ( `b`.`reason_id` = `r`.`id` ) and ( `b`.`id` = ".Int2SQL( $v_id )." ) limit 1;" );
	$v_bill_number = $row[ "number" ] ;
	$v_date = date( "d.m.Y" , strtotime( $row[ "date" ] ) );
	$v_payer = $row[ "payer" ];
	$v_address = $row[ "address" ];
	$v_customer = $row[ "customer" ];
	$v_CBC = $row[ "reason_code" ];

	$tabItems = $portalDB->query( "select * from `items` where ( `ext_id` = ".Int2SQL( $v_id )." );" );
	$linkedBills = array();
	foreach ( $tabItems as &$ti ) {
		$ti[ "price" ]/= 100 ;
		if ( !is_null( $ti[ "from" ] ) && $ti[ "from" ] != 0 ) {
			$linkedBills[]= $ti[ "from" ];
		}
	} unset( $ti );
	$linkedBills = array_unique( $linkedBills );
	if ( count( $linkedBills ) > 0 ) {
		$linkedBills = $portalDB->query( "select * from `bills` where ( `id` in ( ".implode( "," , $linkedBills )." ) )" , "id" );
		foreach ( $linkedBills  as &$lb ) {
			$lb = "<a href=\"bill.print.php?id=".$lb[ "id" ]."\" target=\"_blank\">".$lb[ "number" ]." от ".date( "d-m-Y" , strtotime( $lb[ "date" ] ) )."</a>" ;
		} unset( $lb );
	}

	$lnk = $portalDB->query( "select `t1`.* from `bills` as `t1` , `items` as `t2` where ( `t1`.`id` = `t2`.`ext_id` ) and ( `t2`.`from` = ".Int2SQL( $v_id )." )" );
	foreach( $lnk as &$l ) {
		$l = "<a href=\"bill.print.php?id=".$l[ "id" ]."\" target=\"_blank\">".$l[ "number" ]." от ".date( "d-m-Y" , strtotime( $l[ "date" ] ) )."</a>" ;
	} unset( $l );

	$lnk = implode( "" , $lnk );

	MainHead_L1( "Печатать счет" , array( "../%UT/buttons.css" , "%UT/bill-2.print.css" ) , false , "files/bill.print.js" );

	function ca( $str ) {
		return ClearOutputText( $str , array( array( "\"" , "&quot;" ) ) );
	}

	echo "<div class=\"menu\">
		<div><a href=\"bill.php?edit=".$v_id."\" class=\"btn3\">Внести изменения</a> <a href=\"bill.print.php?id=".$v_id."\" class=\"btn3\">Счет</a></div>
	</div>" ;

	$total_1 = 0 ;
	$payFor = array();
	foreach( $tabItems as $ti ) {
		$total_p1 = $ti[ "count" ] * $ti[ "price" ]; /* сумма с НДС*/
		$payFor[]= ca( $ti[ "name" ] ).( $ti[ "count" ] > 1 ? " (".$ti[ "count" ]." шт)" : "" );
		$total_1+= $total_p1 ;
	}

	$barCodeData = array(
		"ST00011" ,
		"Name=".$dbConfig[ "org.beneficiary.name.simple" ]." (".$dbConfig[ "org.name.short" ]." л/с ".$dbConfig[ "org.clientAccount" ].")" ,
		"PersonalAcc=".$dbConfig[ "org.beneficiary.accountNumber" ] ,
		"BankName=".$dbConfig[ "org.bank.name" ] ,
		"BIC=".$dbConfig[ "org.bank.bic" ] ,
		"CorrespAcc=".$dbConfig[ "org.bank.corrAccountNumber" ] ,
		"PayeeINN=".$dbConfig[ "org.inn" ] ,
		"KPP=".$dbConfig[ "org.kpp" ] ,
		"CBC=".$v_CBC ,
		"OKTMO=".$dbConfig[ "org.oktmo" ] ,
		"Sum=".number_format( $total_1 , 2 , "" , "" ) ,
		"UIN=0" ,
		"LastName=".clearText( $v_payer ) ,
		"PayerAddress=".clearText( $v_address ) ,
		"Purpose=".clearText( implode( " / " , $payFor ) )
	);

	$T = implode( "|" , $barCodeData );
	//$T = mb_strtoupper( $T , "cp1251" );


	echo "<div id=\"page\">
		<div id=\"page-content\">
			<div class=\"label-title\">Квитанция на оплату экспертизы</div>
			<table align=\"center\" id=\"prop-table\">
					<tr>
						<td rowspan=\"11\" class=\"qr-code-area-td\">
							<div class=\"qr-code-area\">
								<div class=\"label-qr-code\">Извещение</div>
								<div>
									<img src=\"/barcode.php?timernd=".( date( "YmdHis" ).mt_rand() )."&dbg=1&src=".urlencode( $T ).
										"&type=QR&opt=".urlencode( json_encode( array( "EL" => "L" , "qrcode_mode" => "byte" , "pix_size" => 4 ) ) ).
									"\" class=\"qr-code\">
								</div>
							</div>
						</td>
						<td class=\"label-bank\">
							ПАО СБЕРБАНК
						</td>
						<td class=\"label-form-name\">
							Форма №ПД-4
						</td>
					</tr>
					<tr>
						<td colspan=\"2\">
							<div class=\"org-data-1\">".$dbConfig[ "org.beneficiary.name.simple" ]."<br> (".$dbConfig[ "org.name.short" ]." л/с ".$dbConfig[ "org.clientAccount" ].")</div>
							<div class=\"org-data-1-label\">(наименование получателя платежа)</div>
						</td>
					</tr>
					<tr>
						<td class=\"org-data-2\">
							ИНН ".$dbConfig[ "org.inn" ]." КПП ".$dbConfig[ "org.kpp" ]."
						</td>
						<td class=\"org-data-3\">
							".$dbConfig[ "org.beneficiary.accountNumber" ]."
						</td>
					</tr>
					<tr>
						<td class=\"org-data-2-label\">
							(инн получателя платежа)
						</td>
						<td class=\"org-data-3-label\">
							(номер счёта получателя платежа)
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" class=\"org-data-4\">
							БИК ".$dbConfig[ "org.bank.bic" ]." К/счёт ".$dbConfig[ "org.bank.corrAccountNumber" ]." (".$dbConfig[ "org.bank.name" ].")
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" class=\"org-data-4-label\">
							(наименование банка получателя платежа)
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" class=\"payment-data\">
							ФИО: ".ca( $v_payer )."; Адрес: ".ca( $v_address )."; Назначение: ".ca( implode( " / " , $payFor ) )."; КБК: ".$v_CBC." ; ОКТМО: ".$dbConfig[ "org.oktmo" ]."
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" class=\"payment-data-label\">
							(назначение платежа)
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" class=\"sum\">
							Сумма: ".money_format( "%!i" , $total_1 )."
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" class=\"sum-label\">
							(сумма платежа)
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" class=\"notification\">
							С условиями приёма указанной в платёжном документе суммы, в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен.<br>
							Подпись плательщика _______________________________________\
						</td>
					</tr>
				</table>
			</div>
		</div>
	</body>
</html>" ;
