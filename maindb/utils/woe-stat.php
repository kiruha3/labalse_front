<?php
	include_once( "../../core.php" );
	/**
	 * @var $LoginOk
	 * @var TDB $portalDB
	 * @var $MonthNames
	 * @var $TAB_CC_GROUPS
	 */
	include_once( "../lconfig.php" );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../../auth.php" );
	}

	MainHead_L2( "" , "" , array( "%UT/base.css" ) );

	$cy = intval( date( "Y" , time() ) );

	$gzCCID = array_merge( $TAB_CC_GROUPS[ 1 ] , $TAB_CC_GROUPS[ 5 ] , $TAB_CC_GROUPS[ 6 ] );

	$tab = array();
	for( $i = 2008 ; $i <= $cy ; $i++ ) {
		$res3 = $portalDB->row(
			"select count( `t1`.`id` ) as `res-cnt` , sum( `t1`.`price` ) as `res-sum`".
			"from `writ-of-execution-payments` as `t1` ".
			"where ( `t1`.`deleted` <> 1 ) and ( year( from_unixtime( `t1`.`date` ) ) = ? )" , "i" , $i );

		echo "<span style=\"color : #888\">поступило денег по исп/листам в <span style='display : inline-block ; width : 1.25cm ; text-align : right ; color : #000 ;'>".$i."</span> году: <span style='display : inline-block ; width : 1.25cm ; text-align : right ; color : #000 ;'>".$res3[ "res-cnt" ]."</span> на сумму: <span style='display : inline-block ; width : 3.5cm ; text-align : right ; color : #000 ;'>".money_format( "%!i" , $res3[ "res-sum" ] )."</span></span><br>" ;
	}
	echo "<br><br>" ;

	for( $i = 1 ; $i <= intval( date( "m" , time() ) ) ; $i++ ) {
		$res3 = $portalDB->row(
			"select count( `t1`.`id` ) as `res-cnt` , sum( `t1`.`price` ) as `res-sum`".
			"from `writ-of-execution-payments` as `t1` ".
			"where ( `t1`.`deleted` <> 1 ) and ( year( from_unixtime( `t1`.`date` ) ) = ? ) and ( month( from_unixtime( `t1`.`date` ) ) = ? )" , "ii" , $cy , $i );

		echo "<span style=\"color : #888\">поступило денег по исп/листам в <span style='display : inline-block ; width : 2cm ; text-align : left ; color : #000 ;'>".inForm( $MonthNames[ $i - 1 ] , 6 )."</span> ".$cy." года: <span style='display : inline-block ; width : 1.25cm ; text-align : right ; color : #000 ;'>".$res3[ "res-cnt" ]."</span> на сумму: <span style='display : inline-block ; width : 3.25cm ; text-align : right ; color : #000 ;'>".money_format( "%!i" , $res3[ "res-sum" ] )."</span></span><br>" ;
	}
	echo "<br><br>" ;

	$WoEErrList = array();
	$WoEDuplList = array();
	$res4YMap = array();
	$res4 = $portalDB->query( "select `t1`.* from `writ-of-execution` as `t1` where ( `t1`.`state` = 0 )" , 'id' );
	//echo "writ-of-execution count : ".count( $res4 ).'<br/>' ;
	foreach( $res4 as &$WoE ) {
		if ( isset( $WoE[ 'incoming_date' ] ) && is_numeric( $WoE[ 'incoming_date' ] ) && $WoE[ 'incoming_date' ] > 0 ) {
			$idY = intval( date( 'Y' , $WoE[ 'incoming_date' ] ) , 10 );
		} else {
			$idY = '-' ;
		}
		if ( !isset( $res4YMap[ $idY ] ) ) {
			$res4YMap[ $idY ] = array();
		}
		$r4ymYL = &$res4YMap[ $idY ];
		$WoENumber = $WoE[ 'num' ];
		if ( !isset( $r4ymYL[ $WoENumber ] ) ) {
			$r4ymYL[ $WoENumber ] = &$WoE ;
			$WoE[ '@price' ] = 0 ;
			$WoE[ '@payed' ] = 0 ;
		} else {
			$ILDuplList[]= &$WoE ;
			$r4ymYL[ $WoENumber.'#'.count( $ILDuplList ) ] = &$WoE ;
			$WoE[ '@price' ] = 0 ;
			$WoE[ '@payed' ] = 0 ;

		}
	} unset( $WoE );

	//print_r_html( $ILDuplList , 1 );

	$res4IDList = array_keys( $res4 );
	$res41 = $portalDB->query( "select `t1`.* from `writ-of-execution-payers` as `t1` where `ext_id` in ( ?* )" , 'id' , '*i' , $res4IDList );
	//echo "writ-of-execution-payers count : ".count( $res41 ).'<br/>' ;
	foreach( $res41 as $WoEP ) {
		$WoEID = $WoEP[ 'ext_id' ];
		if ( !isset( $res4[ $WoEID ] ) ) {
			$WoEErrList[]= array(
				'type' => 'no-woe' ,
				'data' => $WoEP
			);
			continue ;
		}
		$WoE = &$res4[ $WoEID ];
		$WoE[ '@price' ] += $WoEP[ 'price' ];
	}

	$res41IDList = array_keys( $res41 );
	$res42 = $portalDB->query( "select `t1`.* from `writ-of-execution-payments` as `t1` where `ext_id` in ( ?* )" , 'id' , '*i' , $res41IDList );
	//echo "writ-of-execution-payments count : ".count( $res42 ).'<br/>' ;
	foreach( $res42 as $WoEPm ) {
		$pID = $WoEPm[ 'ext_id' ];
		if ( !isset( $res41[ $pID ] ) ) {
			$res41ErrList[]= array(
				'type' => 'no-payer' ,
				'data' => $WoEPm
			);
			continue ;
		}
		$WoEP = &$res41[ $pID ];
		$WoEID = $WoEP[ 'ext_id' ];
		if ( !isset( $res4[ $WoEID ] ) ) {
			$WoEErrList[]= array(
				'type' => 'no-woe' ,
				'data' => $WoEP
			);
			continue ;
		}
		$WoE = &$res4[ $WoEID ];
		$WoE[ '@payed' ] += $WoEPm[ 'price' ];
	}

	$totalRow = array(
		'count' => 0 ,
		'price' => 0 ,
		'payed' => 0
	);

	$YList = array_keys( $res4YMap );
	sort( $YList );

	foreach( $YList as $i ) {
		$WoEYL = $res4YMap[ $i ];
		$totalYRow = array(
			'count' => count( $WoEYL ) ,
			'price' => 0 ,
			'payed' => 0
		);
		$LL = array();
		foreach( $WoEYL as $WoE ) {
			$LL[]= $WoE[ 'id' ];
			$totalYRow[ 'price' ] += $WoE[ '@price' ];
			$totalYRow[ 'payed' ] += $WoE[ '@payed' ];
		}

		if ( count( $LL ) > 0 ) {
			$LL = '<a href="/maindb/writ-of-execution.list.php?idlist='.implode( ',' , $LL ).'" target="_blank">'.count( $LL ).'</a>' ;
		} else {
			$LL = 0 ;
		}
		echo 'не оплачено исп/листов, поступивших '.( $i == '-' ? '<b>неизвестно когда</b>' : 'в '.$i.' году' ).': '.$LL.' на сумму: '.money_format( '%!i' , $totalYRow[ 'price' ] - $totalYRow[ 'payed' ] ).' ( оплачено '.money_format( '%!i' , $totalYRow[ 'payed' ] ).' из '.money_format( '%!i' , $totalYRow[ 'price' ] ).' )<br>' ;

		$totalRow[ 'count' ] += $totalYRow[ 'count' ];
		$totalRow[ 'price' ] += $totalYRow[ 'price' ];
		$totalRow[ 'payed' ] += $totalYRow[ 'payed' ];
	}
	echo "<br><br>" ;
	echo 'не оплачено всего исп/листов: '.$totalRow[ 'count' ].' на сумму: '.money_format( '%!i' , $totalRow[ 'price' ] - $totalRow[ 'payed' ] ).' ( оплачено '.money_format( '%!i' , $totalRow[ 'payed' ] ).' из '.money_format( '%!i' , $totalRow[ 'price' ] ).' )<br>' ;

	$res7 = $portalDB->query(
		"select `t1`.`num`".
		"from `writ-of-execution` as `t1` ".
		"left join `writ-of-execution-payers` as `t2`".
		"on `t1`.`id` = `t2`.`ext_id` ".
		"where `t2`.`id` is null" , false );

	echo "листы без информации о плательщике: ".makeSimpleTable( $res7 )."<hr>" ;





	closeHtml();
