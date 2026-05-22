<?php
	include_once( '../core.php' );
	/**
	 * @var TDB $portalDB
	 * @var boolean $LoginOk
	 * @var array $UserRights
	 * @var string $dbDatabase
	 * @var string $UserLogin
	 * @var string $UserOrgIndex
	 */

	include_once( '../maindb/lconfig.php' );

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( !isset( $_SERVER[ 'alterDB' ] ) || trim( $_SERVER[ 'alterDB' ] ) == '' ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm( 'рабочая база `'.$dbDatabase.'`' );
		closeHtml();
		exit();
	}

	if ( isset( $dbConfig[ 'engine.mayWipe' ] ) && $dbConfig[ 'engine.mayWipe' ] == 1 ) {
		if ( isset( $_REQUEST[ 'do-wipe' ] ) && $_REQUEST[ 'do-wipe' ] == 1 ) {
			MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
			echo '<br><br><br><br><br>Стираемая база: `'.$_SERVER[ 'alterDB' ].'`<br><br>' ;
		} else {
			MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
			echo '<br><br><br><br><br>' ;
			MessageForm( 'Стирание `'.$_SERVER[ 'alterDB' ].'` не подтверждено' );
			closeHtml();
			exit();
		}
	} else {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo "<br><br><br><br><br>" ;
		MessageForm( 'Разрешение на стирание `'.$_SERVER[ 'alterDB' ].'` не получено' );
		closeHtml();
		exit();
	}

	$adminList = explode( ';' , strtolower( $dbConfig[ 'engine.admin' ] ) );
	if ( !in_array( strtolower( $UserLogin ) , $adminList ) ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ) , array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit();
	}

	//exit();

	$noWipeTab = array(
		'access-rights' ,
		'accounts' ,
		'bank-set' ,
		'bank-set-items' ,
		'casecategory' ,
		'char-id-types' ,
		'config' ,
		'correspondence-types' ,
		'dir' ,
		'doc-templates' ,
		'event-types' ,
		'exp-equipment-test-types' ,
		'files' ,
		'kuvk-params' ,
		'marks-catalog' ,
		'marks-groups' ,
		'marks-mark-group' ,
		'news-categories' ,
		'places' ,
		'plan--groups' ,
		'plan--plan-types' ,
		'posts' ,
		'reasons' ,
		'simple-lists' ,
		'specialities' ,
		'specialities-groups' ,
		'stat-points' ,
		'substances-norms' ,
		'themes' ,
		'type-of-agency' ,
	);

	$insertEmpty = array(
		'bills' ,
	);

	$portalDB->noResult( 'SET FOREIGN_KEY_CHECKS = 0;' );
	$tabList = $portalDB->query( 'show full tables where Table_Type <> "VIEW"' , false );
	foreach ( $tabList as $tabData ) {
		$tabName = $tabData[ 'Tables_in_'.$dbDatabase ];
		if ( !in_array( $tabName , $noWipeTab ) ) {
			echo 'truncate: '.$tabName.'<br>' ;
			$portalDB->noResult( 'truncate table `'.$tabName.'`' );
		}

		if ( in_array( $tabName , $insertEmpty ) ) {
			echo 'insert empty : '.$tabName.'<br>' ;
			$portalDB->noResult( 'insert into `'.$tabName.'` (`id`) values ( null );' );
		}
	}
	$portalDB->noResult( 'SET FOREIGN_KEY_CHECKS = 1;' );


	/////////////////////////
	$portalDB->insertRow( 'departments' , array(
		'ind' => -1 ,
		'name' => 'Нет отдела' ,
		'short_name' => 'Н/О' ,
		'actual' => 1
	) );
	$dep = $portalDB->lastInsertID();

	//$portalDB->dbgMode = true ;

	$portalDB->insertRow( 'workers-no-spec' , array(
		'name' => 'i=Администратор{|а|у|а|ом|е}' ,
		'post_1_id' => 0 ,
		'post_2_id' => 0 ,
		'dep' => $dep ,
		'actual' => 1
	) );
	$workerID = $portalDB->lastInsertID();
	$portalDB->updateRow( 'workers-no-spec' , array(
		'id' => $workerID ,
		'first_id' => $workerID
	) );

	$portalDB->noResult( 'delete from `access-rights` where `user_id` not in ( ?* )' , '*i' , array( 1 ) );
	$portalDB->noResult( 'delete from `access-rights` where `place` in ( ?* )' , '*i' , array( 5 , 10 , 11 , 12 ) );
	$portalDB->noResult( 'delete from `accounts` where `id` not in ( ?* )' , '*i' , array( 1 ) );
	$portalDB->noResult( 'update `accounts` set `worker_id` = ? where `id` in ( ?* )' , 'i*i' , $workerID , array( 1 ) );
	$portalDB->noResult( 'delete from `places` where `id` in ( ?* )' , '*i' , array( 5 , 10 , 11 , 12 ) );
	$portalDB->noResult( 'update `config` set `value` = ? where `name` = ?' , 'ss' , '1' , 'org.boss' );
	$portalDB->noResult( 'update `config` set `value` = ? where `name` = ?' , 'ss' , '1' , 'org.accountantGeneral' );
	$portalDB->noResult( 'update `config` set `value` = ? where `name` = ?' , 'ss' , '1' , 'org.payments.worker' );
	$portalDB->noResult( 'update `config` set `value` = ? where `name` = ?' , 'ss' , '1' , 'report.order-3.boss' );

	$portalDB->noResult( 'insert into `indexes` ( `_index_prefix` , `_id` ) values ( ? , ? )' , 'ss' , VERSION_CHAR_ID.'.'.$UserOrgIndex.'.'.DOCTYPE_MATINCOMING.'.20' , date( 'y' , time() ).'000000' );


	$lst = $portalDB->query( 'select * from `dir` where `id` not in ( -1 , 4 , 6 , 11 , 12 , 13 , 14 , 15 , 16 , 17 , 18 , 19 , 20 , 22 , 23 , 24 , 25 , 42 , 43 , 46 , 64 , 66 )' );
	$lst = array_column( $lst , 'id' );
	echo 'dir save list ' ;
	print_r_html( $lst );
	$portalDB->noResult( 'delete from `files` where `ext_id` not in ( ?* )' , '*i' , $lst );
	$portalDB->noResult( 'delete from `dir` where `id` not in ( ?* )' , '*i' , $lst );

	closeHtml();
