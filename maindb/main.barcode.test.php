<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/


	include_once ( "../core.php" );
	require_once "lconfig.php" ;
	require_once( '../cores/core.maindb.php' );

	/*TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) != 1 ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}*/

	if ( isset( $_REQUEST[ "code" ] ) ) {
		$v = $_REQUEST[ "code" ];
	} else {
		$v = "" ;
	}

	function cvtr( $s , $f = 'utf8' , $t = DEF_CODEPAGE ) {
		return iconv( $f , $t , $s );
	}


	$v = pack( "H*" , $v );

	$id = getCharID( $v , DOCTYPE_MATINCOMING );
	if ( $id === false ) {
		$n = preg_match( '/^[a-zA-Z0-9\+\/]+={0,3}$/' , $v );
		if ( $n == 1 ) {
			$ed = base64_decode( $v );
			$priv = file_get_contents( '../keys/pre-reg.priv.4096.pem' );
			$priv = openssl_get_privatekey( $priv );
			$decData = '' ;
			openssl_private_decrypt( $ed , $decData , $priv );
			openssl_free_key( $priv );

			if ( isset( $_REQUEST[ 'noreg' ] ) ) {
				echo '<?xml version="1.0" encoding="cp1251"?><xml>'.gzdecode( $decData ).'</xml>' ;
				$data = simplexml_load_string( '<?xml version="1.0" encoding="cp1251"?><xml>'.gzdecode( $decData ).'</xml>' );
				var_dump( ''.$data->ay , 1 );
				var_dump( ''.$data->at , 1 );
				exit();
			}

			$data = simplexml_load_string( '<?xml version="1.0" encoding="cp1251"?><xml>'.gzdecode( $decData ).'</xml>' );

			$dataID = trim( (string) $data->d[ 'id' ] );
			$comment = $portalDB->simpleRow( 'expertize-comments' , array( 'comment' => $dataID , 'ext_type' => 'matincoming' ) );
			if ( $comment !== false ) {
				/*MainHead_L2( "" , "" , array( "/%UT/buttons.css" , "/%UT/forms.css" ) , array() , "hlp/no_access.html" );
				echo "<br><br><br><br><br>" ;
				MessageForm( "ДОГОВОР УЖЕ ЗАРЕГИСТРИРОВАН" , "Сообщение" , 'Показать' , '/maindb/main.php?idlist='.$comment[ 'ext_id' ] );
				closeHtml();
				exit();*/
				Redirect( '?code='.bin2hex( $comment[ 'ext_id' ] ) );
			}

			$docNum = str_replace( '.' , '' , $dataID );

			$toaMap = array(
				"ind" => 11
			);
			$dataTOA = (string)( $data->d[ 't' ] );
			if ( !isset( $toaMap[ $dataTOA ] ) ) {
				$v_type_of_agency = 11 ;
			} else {
				$v_type_of_agency = $toaMap[ $dataTOA ];
			}

			$v_contacts = array();
			$saad = storeAgentData( $portalDB , $v_type_of_agency , cvtr( ''.$data->ay ) , cvtr( ''.$data->at ) , $v_contacts );

			$tabDepartments = $portalDB->table( "departments" , "id" );
			$tabSpecialities = $portalDB->table( "specialities" , "id" );
			$tabWorkers = $portalDB->table( "workers" , "id" );
			foreach( $tabWorkers as &$worker ) {
				$worker[ 'name' ] = NAMES_Format( NAMES_parse( $worker[ 'name' ] ) , '%F1 %i.%o.' );
			} unset( $worker );

			$dataExpID = intval( $data->d[ 'e' ] , 10 );
			$dataSpecID = intval( $data->d[ 's' ] , 10 );
			$cWorker = $tabWorkers[ $dataExpID ];
			$cTime = time();

			$v_ = array(
				'id' => VERSION_CHAR_ID.'.'.$UserOrgIndex.'.'.DOCTYPE_MATINCOMING.'.20' ,
				'date' => date( 'Y-m-d' , intval( $data->d[ 'd' ] , 10 ) ) ,
				'from_agency' => $saad[ 'agency.id' ] ,
				'from_agent' => $saad[ 'agent.id' ] ,
				'state' => -2 ,
				'ex_data_3' => 'Договор от '.date( 'd.m.Y' , intval( $data->d[ 'd' ] , 10 ) ) ,
				'ex_data_4' => 'Дог.'.$docNum.' '.trim( cvtr( $data->t ) ) ,
				'ex_data_6' => date( 'd-m-Y' , $cTime ).' '.$cWorker[ 'name' ] ,
				'exp_type' => 0 ,
				'group_id' => 0 ,
			);

			$portalDB->insertRow( "matincoming" , $v_ );
			$niid = $portalDB->lastInsertID();

			$row = $portalDB->simpleRow( 'matincoming' , array( '__id' => $niid ) );

			$v_id = $row[ 'id' ];

			$portalDB->noResult( "insert into `expertize-comments` ( `ext_type` , `ext_id` , `date` , `exp_id` , `comment` ) values ( 'matincoming' , ? , ? , 119 , ? )" , "sis" , $v_id , time() , $dataID );

			$portalDB->noResult(
				"insert into `matincominglvl2` ( `mat_id` , `dep_id` , `date` , `materials` , `ex_data_6` , `kat_slognost` ) values ( ? , ? , ? , ? , ? , ? )" ,
				"sisssi" ,
				$v_id , $cWorker[ 'dep' ] , date( 'Y-m-d' , $cTime ) , cvtr( $data->obj ) , date( 'Y-m-d' , $cTime ).', ' , 1
			);

			$v_id2 = $portalDB->lastInsertID();

			$v_use_in_stat = ( isset( $_REQUEST[ "i_no_use_in_stat" ] ) && ( $_REQUEST[ "i_no_use_in_stat" ] == 1 ) ? 0 : 1 );

		//pay_{date,details}
			$portalDB->insertRow( 'expertize' , array(
				'ext_id' => $v_id2 ,
				'exp_id' => $dataExpID ,
				'spec_id' => $dataSpecID ,
				'use_in_stat' => 1 ,
				'reason_1' => 0 ,
				'reason_2' => 0 ,
				'state' => 0 ,
				'order_date' => date( "Y\m\d" , $cTime ) ,
				'price' => $data->d[ 'p' ] / 100.0 ,
				'pay_date' => 'Квитанция к договору '.$docNum ,
				'pay_details' => cvtr( $agent )
			) );

			$expertizeID = $portalDB->lastInsertID();

			$portalDB->noResult( "insert into `payments` ( `expertize_id` , `state` , `create_date` , `check_date` , `type` ) values ( ? , 0 , ? , 0 , 0 )" , "ii" , $expertizeID , time() );

			Redirect( "/maindb/order-2.php?id=".$v_id );

			exit();
		} else {
			MainHead_L2( "" , "" , array( "/%UT/buttons.css" , "/%UT/forms.css" ) , array() , "hlp/no_access.html" );
			echo "<br><br><br><br><br>" ;
			MessageForm( "НЕВЕРНЫЙ КОД<br>".$v , "Сообщение" );
			closeHtml();
			exit();
		}
	}

	$tabDepartments = $portalDB->table( "departments" , "id" );
	$tabSpecialities = $portalDB->table( "specialities" , "id" );
	$tabWorkers = $portalDB->table( "workers" , "id" );

	$tabCaseCategory = $portalDB->table( "casecategory" , "id" );

	$t1 = $portalDB->row( "select `t1`.* , `t4`.`name` as `agency` , `t5`.`name` as `agent` , ifnull( `t1`.`group_id` , 0 ) as `group_id` from `matincoming` as `t1` , `agency` as `t4` , `agent` as `t5` where ( `t1`.`id` = ? ) and ( `t4`.`id` = `t1`.`from_agency` ) and ( `t5`.`id` = `t1`.`from_agent` )" , "s" , $id );
	if ( $t1 === false ) {
		MainHead_L2( "" , "" , array( "/%UT/buttons.css" , "/%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm( "КАРТОЧКА НЕ СУЩЕСТВУЕТ" , "Сообщение" );
		closeHtml();
		exit();
	}

	$t1Array = array();
	if ( $t1[ "group_id" ] != 0 ) {
		$t1Array = $portalDB->query( "select `t1`.* , `t4`.`name` as `agency` , `t5`.`name` as `agent` , ifnull( `t1`.`group_id` , 0 ) as `group_id` from `matincoming` as `t1` , `agency` as `t4` , `agent` as `t5` where ( `t1`.`group_id` = ? ) and ( `t4`.`id` = `t1`.`from_agency` ) and ( `t5`.`id` = `t1`.`from_agent` )" , false , "i" , $t1[ "group_id" ] );
	} else {
		$t1Array = array( $t1 );
	}

	MainHead_L2( "База" , "<a href=\"main.php\">База</a> - Карточка" , array( "%UT/main.barcode.css" ) , array() , "" );

	$t1States = array (
		-2 => "ошибочно зарегистрировано" ,
		-1 => "ожидает выполнения другой экспертизы" ,
		 0 => "в производстве" ,
		 1 => "готово к выдаче" ,
		 2 => "выдано"
	);

	foreach( $t1Array as $t1 ) {
		echo "<div id=\"cards-area\">
		<div class=\"t1-state\">".$t1States[ $t1[ "state" ] ]."</div>
		<table align=\"center\">
			<tr>
				<td>
					<div class=\"t1-number-title\">Номер</div><div class=\"t1-number-value\">".matincomingNumberFull( $t1[ "id" ]  , null , $t1[ "exp_type" ] )." (".$tabCaseCategory[ $t1[ "exp_type" ] ][ "short_name" ].")</div>
					<div class=\"t1-date-title\">Дата<br><span>поступления</span></div><div class=\"t1-date-value\">".date( "d-m-Y" , strtotime( $t1[ "date" ] ) )."</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class=\"t1-from-title\">Назначил</div><div class=\"t1-from-value\">".$t1[ "agency" ].", ".$t1[ "agent" ]."</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class=\"t1-ex_data_7-title\">Запросы и т.д.</div><div class=\"t1-ex_data_7-value\">".$t1[ "ex_data_7" ]."</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class=\"t2t3-cards-area-container\">
					<table class=\"t2t3-cards-area-container-table\" align=\"center\">
						<tr>" ;

		$res = $portalDB->query( "select `t2`.`mat_id` , `t2`.`dep_id` , `t2`.`kat_slognost` , `t3`.* from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t2`.`mat_id` = ? ) and ( `t3`.`ext_id` = `t2`.`id` )" , false , "s" , $t1[ "id" ] );

		$ind = 0 ;
		foreach( $res as $row ) {
			$cw = $tabWorkers[ $row[ "exp_id" ] ];
			$pr = $portalDB->query( "select * from `payments` where ( `state` <=> 1 ) and ( `expertize_id` = ? )" , false , "i" , $row[ "id" ] );
			echo "<td class=\"t2t3-cards-area\" ".( $ind != 0 ? "style=\"width : ".( 100.0 / count( $res ) )."% ; border-left : 1px dotted #606060 ;\"" : "" )." valign=\"top\"><table class=\"t2t3-card\">
				<tr>
					<td colspan=\"3\">
						<div class=\"expert-name\">".NAMES_Format( NAMES_Parse( $cw[ "name" ] ) , "%F1 %i.%o." )."</div><div class=\"expert-spec\">".$tabSpecialities[ $row[ "spec_id" ] ][ "group" ].".".$tabSpecialities[ $row[ "spec_id" ] ][ "num" ].( !is_null( $tabSpecialities[ $row[ "spec_id" ] ][ "comment" ] ) ? " (".$tabSpecialities[ $row[ "spec_id" ] ][ "comment" ].")" : "" )."</div>
						<div class=\"kat_slognost\">".$row[ "kat_slognost" ]." категория</div>
					</td>
				</tr>
				<tr>
					<td colspan=\"3\">
						<div class=\"exp-state\" style=\"background-color : #".( $row[ "state" ] == 1 ? "00c000" : ( $row[ "state" ] == 2 ? "ffc000" : "ff8080" ) )."\">".( $row[ "state" ] == 1 ? "".date( "d-m-Y" , strtotime( $row[ "fin_date" ] ) ) : ( $row[ "state" ] == 2 ? "Без производства" : "В призводстве" ) )."</div>
					</td>
				</tr>
				<tr>
					<td colspan=\"3\">
						<div class=\"exp-price-title\">стоимость</div><div class=\"exp-price-value\">".money_format( "%!i" , $row[ "price" ] )."</div> руб.
					</td>
				</tr>
				<tr>
					<td colspan=\"3\">
						<div class=\"exp-conclusion-title\">поставлено<br>вопросов</div><div class=\"exp-conclusion-value\">".$row[ "conclusion" ]."</div>
					</td>
				</tr>
				<tr>
					<td style=\"width : 33%\">
						<div class=\"exp-conclusion_1-title\">дано<br>категорических<br>выводов</div><div class=\"exp-conclusion_1-value\">".$row[ "conclusion_1" ]."</div>
					</td>
					<td style=\"width : 33%\">
						<div class=\"exp-conclusion_3-title\">дано<br>вероятных<br>выводов</div><div class=\"exp-conclusion_3-value\">".$row[ "conclusion_3" ]."</div>
					</td>
					<td>
						<div class=\"exp-conclusion_2-title\">невозможно<br>решить<br>вопросов</div><div class=\"exp-conclusion_2-value\">".$row[ "conclusion_2" ]."</div>
					</td>
				</tr>
				<tr>
					<td colspan=\"3\">" ;
				if ( $pr !== false && count( $pr ) > 0 ) {
					echo "<div class=\"pay-details\">Оплата</div>" ;
					foreach( $pr as $p ) {
						echo "<div class=\"pay-data\"><div class=\"pay-day\">".date( "d-m-Y" , $p[ "check_date" ] )."</div> ".$p[ "comment" ]."</div>" ;
					}
				}
			echo "</td>
				</tr>
			</table>
			</td>" ;

			$ind++ ;
		}

			echo "</tr>
				</table></td></div>
			</tr>
			</table>
		</div>" ;
	}

	closeHtml();
?>