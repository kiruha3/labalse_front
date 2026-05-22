<?php
	/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	include_once( '../core.php' );
	/**
	 * @var TDB $portalDB
	 * @var boolean $LoginOk
	 * @var array $UserRights
	 */
	include_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	//$portalDB->dbgMode = true ;
	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'SET-PLAN' , $Rights ) ) {
			$maySetPlan = in_array( 'SET-PLAN' , $Rights[ 'SET-PLAN' ] );
			$GoOut = !$maySetPlan ;
		} else {
			$maySetPlan = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		ErrorMessage( 403 );
	}

	MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' , '%UT/gov-plan.css' ) , array() , 'hlp/no_access.html' );
	$cY = intval( date( 'Y' ) , 10 );

	echo '<br><br><br><br>' ;
	if ( !isset( $_REQUEST[ 'set' ] ) || !isset( $_REQUEST[ 'plan' ] ) ) {
		$plan = $portalDB->row( "select sum( `plan` ) as `plan` from `gov-plan` where left( `period` , 4 ) = ?" , 'i' , $cY );
		if ( $plan !== false ) {
			$plan = $plan[ 'plan' ];
		} else {
			$plan = 0 ;
		}
		echo '<form action="gov-plan.php" method="post" enctype="multipart/form-data" class="plan-form">
			<div class="plan-data">
				<label class="plan-data-label">Размер государственного задания (экспертиз) <input type="number" name="plan" min="0" max="100000" value="'.$plan.'" class="plan-data-value"/> шт.</label>
			</div>
			<div>
				<input type="submit" name="set" value="Установить"/>
			</div>
		</form>' ;
	} else {
		require_once( '../dev-utils/gp-sql-gen.php' );
		$plan = intval( $_REQUEST[ 'plan' ] , 10 );
		$portalDB->noResult( getPlanSQL( $cY , $plan ) );
		echo '<br>' ;
		MessageForm( 'План на '.$cY.' год установлен в размере '.$plan );
	}

	closeHtml();
