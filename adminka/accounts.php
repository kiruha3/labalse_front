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

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'ACCOUNTS' , $Rights ) ) {
			$accountsEDIT = in_array( 'EDIT' , $Rights[ 'ACCOUNTS' ] );
			$accountsACCESS_EDIT = in_array( 'ACCESS-EDIT' , $Rights[ 'ACCOUNTS' ] );
			$accountsWORKER_EDIT = in_array( 'WORKER-EDIT' , $Rights[ 'ACCOUNTS' ] );
			$GoOut = !( $accountsEDIT || $accountsWORKER_EDIT || $accountsACCESS_EDIT );
		} else {
			$accountsEDIT = false ;
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		ErrorMessage( 403 );
	}

	if ( isset( $_REQUEST[ 'dep' ] ) && $_REQUEST[ 'dep' ] != 'all' ) {
		$targetDep = intval( $_REQUEST[ 'dep' ] );
	} else {
		$targetDep = false ;
	}

	$currentDateTime = time();

	$wrkrID = false ;
	$specIDL = false ;
	if ( $targetDep === false ) {
		if ( isset( $_REQUEST[ 'idlist' ] ) ) {
			$wrkrs = explode( ',' , $_REQUEST[ 'idlist' ] );
			$wrkrID = array();
			foreach( $wrkrs as $w ) {
				$w = trim( $w );
				if ( strlen( $w ) > 0 && preg_match( '/^\d+$/' , $w ) == 1 ) {
					$wrkrID[]= intval( $w );
				}
			}
			if ( count( $wrkrID ) < 1 ) {
				$wrkrID = false ;
			}
		} else
		if ( isset( $_REQUEST[ 'spec' ] ) ) {
			$specIDL = getIDList( $_REQUEST[ 'spec' ] );
		}
	}

	MainHead_L2( 'Админка' , '<a href="main.php">Админка</a> - аккаунты' , array( '../%UT/buttons.css' , '%UT/accounts.css' ) , array( 'files/accounts.js.php?ut=$UserThemeLoc' ) , 'hlp/accounts.html' );

	$deps = $portalDB->query( "select `t1`.* , count( `t2`.`dep` ) as `cnt` from `departments` as `t1` , `workers-no-spec` as `t2` where ( `t1`.`id` = `t2`.`dep` ) and ( `t2`.`actual` <=> 1 ) group by `t2`.`dep` having `cnt` > 0 order by `t1`.`name`" );

	echo '<form action="accounts.php" method="post">
		<center>
			<a href="add-user.php" class="btn3">Добавить</a> | <select id="dep" name="dep" rows="1"><option value="all">все</option>' ;
			foreach( $deps as $d ) {
				echo '<option value="'.$d[ 'id' ].'" '.( $targetDep !== false && $d[ 'id' ] == $targetDep ? 'selected' : '' ).'>'.$d[ 'name' ].' / '.$d[ 'ind' ].' ( '.$d[ 'cnt' ].' чел )</option>' ;
			}
		echo '</select>
			<input name="selectDep" type="submit" value="Выбрать"> <input name="listUsers" type="button" value="Вывести список" onclick="doListUsers();">
		</center>
	</form>' ;

	$tabDepartments = $portalDB->table( 'departments' , 'id' );
	$tabPosts = $portalDB->table( 'posts' , 'id' );
	$tabSpecialities = $portalDB->query( "select `t1`.* , `t2`.`index` , `t2`.`name` , `t1`.`comment` from `specialities` as `t1` , `specialities-groups` as `t2` where ( `t1`.`group` = `t2`.`id` )" , 'id' );
	foreach ( $tabSpecialities as &$spec ) {
		if ( $spec[ 'use_in_stat' ] != 1 ) {
			$spec[ 'fullName' ] = '<span class="spec-no-stat" title="'.$spec[ 'desc' ].'">'.$spec[ 'index' ].'.'.$spec[ 'num' ].( !is_null( $spec[ 'comment' ] ) ? ' ('.$spec[ 'comment' ].')' : '' ).'</span>' ;
		} else {
			$spec[ 'fullName' ] = '<span class="spec-in-stat" title="'.$spec[ 'desc' ].'">'.$spec[ 'index' ].'.'.$spec[ 'num' ].( !is_null( $spec[ 'comment' ] ) ? ' ('.$spec[ 'comment' ].')' : '' ).'</span>' ;
		}
	} unset( $spec );

	$wlMap = array( 'used' => array() , 'unused' => array() );
	if ( $targetDep !== false ) {
		$wla = $portalDB->query( "select * from `workers` where ( `id` in ( select max( `id` ) from `workers` group by `first_id` ) ) and ( `actual` <=> 1 ) and ( `dep` = ? ) order by `name` asc" , 'id' , 'i' , $targetDep );
		$wli = $portalDB->query( "select * from `workers` where ( `id` in ( select max( `id` ) from `workers` group by `first_id` ) ) and ( `actual` != 1 ) and ( `dep` = ? ) order by `name` asc" , 'id' , 'i' , $targetDep );
	} else
	if ( $wrkrID !== false ) {
		$wla = $portalDB->query( "select * from `workers` where ( `id` in ( select max( `id` ) from `workers` group by `first_id` ) ) and ( `actual` <=> 1 ) and ( `first_id` in ( ?* ) ) order by `name` asc" , 'id' , '*i' , $wrkrID );
		$wli = $portalDB->query( "select * from `workers` where ( `id` in ( select max( `id` ) from `workers` group by `first_id` ) ) and ( `actual` != 1 ) and ( `first_id` in ( ?* ) ) order by `name` asc" , 'id' , '*i' , $wrkrID );
	} else
	if ( $specIDL !== false ) {
		$wla = $portalDB->query( "select `t1`.* from `workers` as `t1` , `workers-spec` as `t2` where ( `t1`.`id` in ( select max( `t1.0`.`id` ) from `workers` as `t1.0` group by `t1.0`.`first_id` ) ) and ( `t1`.`actual` <=> 1 ) and ( `t2`.`spec_id` in ( ?* ) ) and ( `t2`.`worker_id` = `t1`.`id` ) order by `t1`.`name` asc" , 'id' , '*i' , $specIDL );
		$wli = $portalDB->query( "select `t1`.* from `workers` as `t1` , `workers-spec` as `t2` where ( `t1`.`id` in ( select max( `t1.0`.`id` ) from `workers` as `t1.0` group by `t1.0`.`first_id` ) ) and ( `t1`.`actual` != 1 )  and ( `t2`.`spec_id` in ( ?* ) ) and ( `t2`.`worker_id` = `t1`.`id` ) order by `t1`.`name` asc" , 'id' , '*i' , $specIDL );
	} else {
		$wla = $portalDB->query( "select * from `workers` where ( `id` in ( select max( `id` ) from `workers` group by `first_id` ) ) and ( `actual` <=> 1 ) order by `name` asc" , 'id' );
		$wli = $portalDB->query( "select * from `workers` where ( `id` in ( select max( `id` ) from `workers` group by `first_id` ) ) and ( `actual` != 1 ) order by `name` asc" , 'id' );
	}

	foreach ( $wla as &$w ) {
		$w[ 'name' ] = NAMES_Format( NAMES_parse( $w[ 'name' ] ) , '%F1 %I1 %O1' );
		$wlMap[ 'used' ][ $w[ 'id' ] ] = array();
	} unset( $w );

	foreach ( $wli as &$w ) {
		$w[ 'name' ] = NAMES_Format( NAMES_parse( $w[ 'name' ] ) , '%F1 %I1 %O1' );
		$wlMap[ 'used' ][ $w[ 'id' ] ] = array();
	} unset( $w );

	$al = $portalDB->query( "select * from `accounts` where `worker_id` is not null" , 'id' );
	foreach ( $al as & $a ) {
		if ( isset( $wlMap[ 'used' ][ $a[ 'worker_id' ] ] ) ) {
			$wlMap[ 'used' ][ $a[ 'worker_id' ] ][]= & $a ;
		} else {
			$wlMap[ 'unused' ][]= & $a ;
		}
	} unset( $a );

	$f = makeSimpleTable_init_filter();
	$f[ 'btn-e' ] = function( &$r , $c , $v ) {
		global $accountsEDIT , $accountsWORKER_EDIT ;
		return ( $accountsWORKER_EDIT ? '<a class="btn-edit" href="worker.php?edit='.$v.'" title="просмотр и редактирование данных о сотруднике" target="_blank"></a>' : '' );
	};
	$f[ 'btn-ae' ] = function( &$r , $c , $v ) {
		global $accountsEDIT , $accountsWORKER_EDIT ;
		return ( $accountsWORKER_EDIT ? '<a class="btn-edit" href="worker.php?edit='.$v.'" title="просмотр и редактирование данных о сотруднике"></a>' : '' );
	};
	$f[ 'dep' ] = function( &$r , $c , $v ) {
		global $tabDepartments ;
		if ( isset( $tabDepartments[ $v ] ) ) {
			return '<span title="'.htmlspecialchars( $tabDepartments[ $v ][ 'name' ] ).'">'.htmlspecialchars( !is_null( $tabDepartments[ $v ][ 'short_name' ] ) ? $tabDepartments[ $v ][ 'short_name' ] : $tabDepartments[ $v ][ 'name' ] ).'</span>' ;
		} else {
			return '<span class="dep-error" title="Информация об отделе повреждена">Ошибка</span>' ;
		}
	};
	$f[ 'post' ] = function( &$r , $c , $v ) {
		global $tabPosts ;
		$p1 = $r[ 'post_1_id' ];
		$p2 = $r[ 'post_2_id' ];
		if ( isset( $tabPosts[ $p1 ] ) ) {
			$res = $tabPosts[ $p1 ][ 'name' ];
		} else {
			$res = '<span class="post-error" title="Информация о должности повреждена">Ошибка</span>' ;
		}

		if ( $p1 != $p2 ) {
			$res.= ', ' ;
			if ( isset( $tabPosts[ $p2 ] ) ) {
				$res.= $tabPosts[ $p2 ][ 'name' ];
			} else {
				$res.= '<span class="post-error" title="Информация о должности повреждена">Ошибка</span>' ;
			}
		}

		return $res ;
	};
	$f[ 'spec' ] = function( &$r , $c , $v ) {
		global $tabSpecialities ;
		$v = trim( trim( $v , ';' ) );
		if ( strlen( $v ) > 0 ) {
			$v = explode( ';' , $v );
		} else {
			$v = array();
		}

		foreach( $v as &$s ) {
			if ( isset( $tabSpecialities[ $s ] ) ) {
				$s = $tabSpecialities[ $s ][ 'fullName' ];
			} else {
				$s = '<span class="spec-error" title="Информация о специальности повреждена">Ошибка</span>' ;
			}
		} unset( $s );

		return implode( ' ' , $v );
	};
	$f[ 'acc-data' ] = function( &$r , $c , $v ) use ( $currentDateTime ) {
		global $wlMap , $accountsEDIT , $accountsACCESS_EDIT ;

		$res = '' ;
		if ( isset( $wlMap[ 'used' ][ $v ] ) ) {
			$m = & $wlMap[ 'used' ][ $v ];
			foreach( $m as $ad ) {
				$res.= '<tr><td>'.
					( $accountsEDIT ?
						'<a class="btn-edit" href="account.php?edit='.$ad[ 'id' ].'" title="просмотр и редактирование данных об учетной записи" target="_blank"></a>'
						:
						''
					).' '.
					( $accountsACCESS_EDIT ?
						'<a class="rights-edit" href="rights.php?edit='.$ad[ 'id' ].'" title="просмотр и редактирование доступа к разделам и прав на них" target="_blank">права</a>'
						:
						''
					).' '.$ad[ 'login' ].'</td><td></td>' ;

					$td = date( 'd-m-Y' , $currentDateTime );
					$yd = date( 'd-m-Y' , $currentDateTime - 86400 );

					$ud = strtotime( $ad[ 'last_visit_date' ] );

					$UserLastVisitDate = date( 'd-m-Y' , $ud );
					if ( $UserLastVisitDate == $td ) {
						$UserLastVisitDate = 'сегодня' ;
					} else
						if ( $UserLastVisitDate == $yd ) {
							$UserLastVisitDate = 'вчера' ;
						}
					$UserLastVisitTime = date( 'H:i' , strtotime( $ad[ 'last_visit_time' ] ) );
					$res.= '<td class="time-data'.( ( ( $r[ 'actual' ] != 1 ) xor ( $ud < $currentDateTime - 86400 * 30 ) ) ? ' time-alert' : '' ).'"><span class="date">'.$UserLastVisitDate.'</span> в <span class="time">'.$UserLastVisitTime.'</span></td>' ;

				$res.= '</tr>' ;
			}
			return '<table class="acc-data-table">'.$res.'</table>' ;
		} else {
			return '' ;
		}
	};


	$t = '[]' ;

	echo '<div class="tab-cap">Активные сотрудники</div>' ;
	echo makeSimpleTable(
		$t , '[ { "t" : 1 } ]' ,
		'[ { "n" : "id"         , "t" : "n"  , "h" : [ { "d" : "" } ] , "f" : "btn-e" , "s" : "btn-e" } ,'.
		'  { "n" : "name"       , "t" : "s320" , "h" : [ { "d" : "Ф.И.О." } ] } ,'.
		'  { "n" : "dep"        , "t" : "S320" , "h" : [ { "d" : "отдел"  } ] , "f" : "dep" } ,'.
		'  { "n" : "post_1_id"  , "t" : "S320" , "h" : [ { "d" : "должность"  } ] , "f" : "post" } ,'.
		'  { "n" : "spec"       , "t" : "S160" , "h" : [ { "d" : "специальности"  } ] , "f" : "spec" } ,'.
		'  { "n" : "id"         , "t" : "s384" , "h" : [ { "d" : "аккаунт" } ] , "f" : "acc-data" , "s" : "acc-data" } ,'.
		'  { "n" : "ad-login"   , "t" : "ss" , "h" : [ { "d" : "аккаунт AD" } ] }'.
		']' ,
		$wla , array( 'dr' => 'dr-d' ) , $f );

	echo '<div class="tab-cap">Неактивные сотрудники ( уволенные / удаленные )</div>' ;
	echo makeSimpleTable(
		$t , '[ { "t" : 1 } ]' ,
		'[ { "n" : "id" , "t" : "n" , "h" : [ { "d" : "" } ] , "f" : "btn-e" , "s" : "btn-e" } ,'.
		'  { "n" : "name" , "t" : "sm" , "h" : [ { "d" : "Ф.И.О." } ] } ,'.
		'  { "n" : "dep"  , "t" : "sm" , "h" : [ { "d" : "отдел"  } ] , "f" : "dep" } ,'.
		'  { "n" : "post_1_id"  , "t" : "Sm" , "h" : [ { "d" : "должность"  } ] , "f" : "post" } ,'.
		'  { "n" : "spec"  , "t" : "Sm" , "h" : [ { "d" : "специальности"  } ] , "f" : "spec" } ,'.
		'  { "n" : "id" , "t" : "n" , "h" : [ { "d" : "аккаунт" } ] , "f" : "acc-data" , "s" : "acc-data" }'.
		']' ,
		$wli , array( 'dr' => 'dr-d' ) , $f );

	closeHtml();
