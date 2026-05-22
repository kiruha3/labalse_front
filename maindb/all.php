<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once ( "../core.php" );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $portalDB
	 */
	
	require_once ( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );
	require_once ( "../marks.core.php" );


	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	$mayAll = false ;
	if ( count( $UserRights ) == 1 ) {
		$Rights= ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( "ALL-ALL" , $Rights ) ) {
			$mayAll = in_array( "ALL" , $Rights[ "ALL-ALL" ] );
		}
	}

	if ( !$mayAll ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit ;
	}

	$fKey = ( isset( $UserOptions[ "payments.full" ] ) ) && ( $UserOptions[ "payments.full" ][ "op_value" ] == 1 );


	$cache = array();

	$maindb_matincoming_state_list = array(
		-2 => "ошибочно зарегистрировано" ,
		-1 => "ожидает выполнения другой экспертизы" ,
		 0 => "в производстве" ,
		 1 => "готово к выдаче" ,
		 2 => "выдано"
	);

	$maindb_expertize_state_list = array(
		0 => "В производстве" ,
		1 => "Окончена" ,
		2 => "Без производства"
	);

	$maindb_payments_state_list = array(
		0 => "Не оплачено" ,
		1 => "Оплачено"
	);

	$maindb_writ_of_execution_state_list = array(
		0 => "Открыт" ,
		1 => "Закрыт"
	);

	$maindb_mark_object_type_list = array(
		'correspondence' => 'Журналы корреспонденции' ,
		'matincoming'    => 'Экспертизы' ,
		'payments'       => 'Оплата' ,
		'woe'            => 'Исполнительные листы'
	);

	function lf__simple_list_int( $action , $data , $param1 = null , $param2 = null , $param3 = null ) {
		global $fltDepDesc , $allLists ;
		switch ( $action ) {
			case "check" :
				$data = implode( "|" , array_keys( $allLists[ $data ] ) );
				$data = preg_match( "/^(?:".$data.")(?:,(?:".$data."))*$/" , $param1 );
				return ( $data == 1 );
				break ;

			case "getItems" :
				return $allLists[ $data ];
				break ;

			case "getText" :
				$data = $allLists[ $data ];
				$param1 = explode( "," , trim( $param1 ) );
				foreach( $param1 as &$i ) {
					$i = trim( $i );
				}
				unset( $i );

				$txt = array();
				foreach( $data as $i => $v ) {
					if ( in_array( $i , $param1 ) ) {
						$txt[]= $v ;
					}
				}

				$txt = implode( ", " , $txt );
				if ( strlen( $txt ) > 30 ) {
					$txt2 = substr( $txt , 0 , 27 )."..." ;
				} else {
					$txt2 = $txt ;
				}
				return array( "full" => $txt , "short" => $txt2 );
				break ;

			case "getCondition" :
				return array( "(".$param1." in ( ".$param2." ) )" );
				break ;

			case "formatResult" :
				$param2 = $param2[ "data" ];
				$param2 = $allLists[ $param2 ];
				if ( isset( $param2[ $param1 ] ) ) {
					return $param2[ $param1 ];
				} else {
					return "[НЕОПРЕДЕЛЕНО]" ;
				}
				break ;
		}
	}

	function lf__simple_list_text( $action , $data , $param1 = null , $param2 = null , $param3 = null ) {
		global $fltDepDesc , $allLists ;
		switch ( $action ) {
			case "check" :
				$data = implode( "|" , array_keys( $allLists[ $data ] ) );
				$data = preg_match( "/^(?:".$data.")(?:,(?:".$data."))*$/" , $param1 );
				return ( $data == 1 );
				break ;

			case "getItems" :
				return $allLists[ $data ];
				break ;

			case "getText" :
				$data = $allLists[ $data ];
				$param1 = explode( "," , trim( $param1 ) );
				foreach( $param1 as &$i ) {
					$i = trim( $i );
				}
				unset( $i );

				$txt = array();
				foreach( $data as $i => $v ) {
					if ( in_array( $i , $param1 ) ) {
						$txt[]= $v ;
					}
				}

				$txt = implode( ", " , $txt );
				if ( strlen( $txt ) > 30 ) {
					$txt2 = substr( $txt , 0 , 27 )."..." ;
				} else {
					$txt2 = $txt ;
				}
				return array( "full" => $txt , "short" => $txt2 );
				break ;

			case "getCondition" :
				$param2 = explode( "," , trim( $param2 ) );
				$param2 = "'".implode( "','" , $param2 )."'" ;
				return array( "(".$param1." in ( ".$param2." ) )" );
				break ;

			case "formatResult" :
				$param2 = $param2[ "data" ];
				$param2 = $allLists[ $param2 ];
				if ( isset( $param2[ $param1 ] ) ) {
					return $param2[ $param1 ];
				} else {
					return "[НЕОПРЕДЕЛЕНО]" ;
				}
				break ;
		}
	}

	function lf__simple_ref_list( $action , $data , $param1 = null , $param2 = null , $param3 = null ) {
		global $portalDB , $cache ;
		switch ( $action ) {
			case "check" :
				$res = $portalDB->row( "select GROUP_CONCAT( CAST( `id` AS CHAR ) separator \"|\" ) as `lst` from `".implode( "`.`" , explode( "." , $data[ "tabn" ] ) )."` ;" );

				$data = $res[ "lst" ];
				$data = preg_match( "/^(?:".$data.")(?:,(?:".$data."))*$/" , $param1 );
				return ( $data == 1 );
				break ;

			case "getItems" :
				$res = $portalDB->query( "select * from `".implode( "`.`" , explode( "." , $data[ "tabn" ] ) )."`" , "id" );
				if ( $data[ "inForm" ] ) {
					foreach( $res as &$row ) {
						$row = inForm( $row[ "name" ] );
					}
				} else {
					foreach( $res as &$row ) {
						$row = $row[ "name" ];
					}
				}
				unset( $row );
				return $res ;
				break ;

			case "getText" :
				$res = $portalDB->row( "select GROUP_CONCAT( `name` separator \", \" ) as `lst` from `".implode( "`.`" , explode( "." , $data[ "tabn" ] ) )."` where `id` in ( ".$param1." )" );

				if ( $data[ "inForm" ] ) {
					$txt = inForm( $res[ "lst" ] );
				} else {
					$txt = $res[ "lst" ];
				}

				if ( strlen( $txt ) > 30 ) {
					$txt2 = substr( $txt , 0 , 27 )."..." ;
				} else {
					$txt2 = $txt ;
				}
				return array( "full" => $txt , "short" => $txt2 );
				break ;

			case "getCondition" :
				return array( "(".$param1." in ( ".$param2." ) )" );
				break ;

			case "formatResult" :
				if( isset( $cache[ "formatResult_table__".$data[ "tabn" ] ] ) ) {
					$lst = $cache[ "formatResult_table__".$data[ "tabn" ] ];
				} else {
					$lst = $portalDB->query( "select * from `".implode( "`.`" , explode( "." , $data[ "tabn" ] ) )."` ;" , "id" );
					if ( $data[ "inForm" ] ) {
						foreach( $lst as &$row ) {
							$row = inForm( $row[ "name" ] );
						}
						unset( $row );
					} else {
						foreach( $lst as &$row ) {
							$row = $row[ "name" ];
						}
						unset( $row );
					}

					$cache[ "formatResult_table__".$data[ "tabn" ] ] = $lst ;
				}

				if ( isset( $lst[ $param1 ] ) ) {
					return $lst[ $param1 ];
				} else {
					return "[НЕОПРЕДЕЛЕНО]" ;
				}
				break ;
		}
	}

	function lf__portal_specialities( $action , $data , $param1 = null , $param2 = null , $param3 = null ) {
		global $portalDB , $cache ;
		switch ( $action ) {
			case "check" :
				$res = $portalDB->row( "select GROUP_CONCAT( CAST( `id` AS CHAR ) separator \"|\" ) as `lst` from `specialities`" );

				$data = $res[ "lst" ];
				$data = preg_match( "/^(?:".$data.")(?:,(?:".$data."))*$/" , $param1 );
				return ( $data == 1 );
				break ;

			case "getItems" :
				$res = $portalDB->query( "select `t1`.`id` as `id` , concat( `t2`.`index` , \".\" , `t1`.`num` , \" \" , `t1`.`desc` ) as `name` from `specialities` as `t1` , `specialities-groups` as `t2` where ( `t1`.`group` = `t2`.`id` ) order by `t1`.`use_in_stat` desc , `t2`.`index` , `t1`.`num`" , "id" );
				foreach( $res as &$row ) {
					//$row = mb_convert_encoding( $row[ "name" ] , "CP1251" , "UTF-8" );
					$row = $row[ "name" ];
					/*$ENC = mb_detect_encoding( $row[ "name" ] );
					echo $ENC ;
					if ( $ENC !== false ) {
						$row = iconv( $ENC , "CP1251" , $row[ "name" ] );
					} else {
						$row = $row[ "name" ];
					}*/
					//
				}
				unset( $row );
				return $res ;
				break ;

			case "getText" :
				$res = $portalDB->query( "select concat( `t2`.`index` , \".\" , `t1`.`num` , \" \" , `t1`.`desc` ) as `name` from `specialities` as `t1` , `specialities-groups` as `t2` where ( `t1`.`group` = `t2`.`id` ) and ( `t1`.`id` in ( ?* ) ) order by `t2`.`index` , `t1`.`num`" , false , "*i" , explode( "," , $param1 ) );

				$txt = array();
				foreach( $res as &$row ) {
					$txt[]= iconv( "utf8" , "cp1251" , $row[ "name" ] );
				}
				unset( $row );
				$txt = implode( ", " , $txt );

				if ( strlen( $txt ) > 30 ) {
					$txt2 = substr( $txt , 0 , 27 )."..." ;
				} else {
					$txt2 = $txt ;
				}
				return array( "full" => $txt , "short" => $txt2 );
				break ;

			case "getCondition" :
				return array( "(".$param1." in ( ".$param2." ) )" );
				break ;

			case "formatResult" :
				if( isset( $cache[ "formatResult_table__specialities" ] ) ) {
					$lst = $cache[ "formatResult_table__specialities" ];
				} else {
					$lst = $portalDB->query( "select `t1`.`id` as `id` , concat( `t2`.`index` , \".\" , `t1`.`num` , \" \" , `t1`.`desc` ) as `name` from `specialities` as `t1` , `specialities-groups` as `t2` where ( `t1`.`group` = `t2`.`id` );" , "id" );
					foreach( $lst as &$row ) {
						//$row = iconv( "utf8" , "cp1251" , $row[ "name" ] );
						$row = $row[ "name" ];
					}
					unset( $row );

					$cache[ "formatResult_table__specialities" ] = $lst ;
				}

				if ( isset( $lst[ $param1 ] ) ) {
					return $lst[ $param1 ];
				} else {
					return "[НЕОПРЕДЕЛЕНО]" ;
				}
				break ;
		}
	}

	function lf__portal_workers( $action , $data , $param1 = null , $param2 = null , $param3 = null ) {
		global $portalDB , $cache ;

		switch ( $action ) {
			case "check" :
				$res = $portalDB->row( "select GROUP_CONCAT( DISTINCT CAST( `first_id` AS CHAR ) separator \"|\" ) as `lst` from `workers`" );

				$data = $res[ "lst" ];
				$data = preg_match( "/^(?:".$data.")(?:,(?:".$data."))*$/" , $param1 );
				return ( $data == 1 );
				break ;

			case "getItems" :
				$res = $portalDB->query( "select * from `workers` group by `first_id` order by `name`" , "id" );

				foreach( $res as &$row ) {
					$row = NAMES_Format( NAMES_parse( $row[ "name" ] ) );
				}

				unset( $row );
				return $res ;
				break ;

			case "getText" :
				$res = $portalDB->query( "select max( `id` ) as `mid` from `workers` where `first_id` in ( ?* ) group by `first_id`" , false , "*i" , explode( "," , $param1 ) );
				$ids = array();
				foreach( $res as $row ) {
					$ids[]= $row[ "mid" ];
				}

				$res = $portalDB->query( "select `name` from `workers` where `id` in ( ?* ) order by `name`" , false , "*i" , $ids );
				foreach( $res as &$row ) {
					$row = NAMES_Format( NAMES_parse( $row[ "name" ] ) );
				}
				unset( $row );

				$txt = implode( ", " , $res );

				if ( strlen( $txt ) > 30 ) {
					$txt2 = substr( $txt , 0 , 27 )."..." ;
				} else {
					$txt2 = $txt ;
				}
				return array( "full" => $txt , "short" => $txt2 );
				break ;

			case "getCondition" :
				$res = $portalDB->query( "select `id` from `workers` where `first_id` in ( ?* )" , false , "*i" , explode( "," , $param2 ) );
				$ids = array();
				foreach( $res as $row ) {
					$ids[]= $row[ "id" ];
				}

				return array( "(".$param1." in ( ".implode( "," , $ids )." ) )" );
				break ;

			case "formatResult" :
				if( isset( $cache[ "formatResult_table__portal_workers" ] ) ) {
					$lst = $cache[ "formatResult_table__portal_workers" ];
				} else {
					$lst = $portalDB->query( "select * from `workers`" , "id" );
					foreach( $lst as &$row ) {
						$row = NAMES_Format( NAMES_parse( $row[ "name" ] ) );
					}
					unset( $row );

					$cache[ "formatResult_table__portal_workers" ] = $lst ;
				}

				if ( isset( $lst[ $param1 ] ) ) {
					return $lst[ $param1 ];
				} else {
					return "[НЕОПРЕДЕЛЕНО]" ;
				}
				break ;
		}
	}

	function lf__sndz( $action , $data , $param1 = null , $param2 = null , $param3 = null ) {
		global $portalDB , $cache ;
		$LIST = array(
			"0" => "Нет" ,
			"1" => "Да" ,
		);
		switch ( $action ) {
			case "check" :
				$res = $portalDB->row( "select GROUP_CONCAT( CAST( `id` AS CHAR ) separator \"|\" ) as `lst` from `specialities`" );

				$data = $res[ "lst" ];
				$data = preg_match( "/^(?:".$data.")(?:,(?:".$data."))*$/" , $param1 );
				return ( $data == 1 );
				break ;

			case "getItems" :
				$res = $LIST ;
				return $res ;
				break ;

			case "getText" :
				$res = explode( "," , $param1 );
				$res = array_combine( $res , $res );

				$txt = array_intersect_key( $LIST , $res);
				$txt = implode( ", " , $txt );

				if ( strlen( $txt ) > 30 ) {
					$txt2 = substr( $txt , 0 , 27 )."..." ;
				} else {
					$txt2 = $txt ;
				}
				return array( "full" => $txt , "short" => $txt2 );
				break ;

			case "getCondition" :
				$r = explode( "," , $param2 );
				$r = array_combine( $r , $r );

				$cond = array();
				if ( isset( $r[ 0 ] ) ) {
					$cond[]= "( ".$param1." <=> 0 )" ;
					$cond[]= "( ".$param1." is null )" ;
				}
				if ( isset( $r[ 1 ] ) ) {
					$cond[]= "( ".$param1." <=> 1 )" ;
				}

				return array( "(".implode( " or " , $cond ).")" );
				break ;

			case "formatResult" :
				if ( is_null( $param1 ) ) {
					$param1 = 0 ;
				}

				if ( isset( $LIST[ $param1 ] ) ) {
					return $LIST[ $param1 ];
				} else {
					return "[НЕОПРЕДЕЛЕНО]" ;
				}
				break ;
		}

	}

	function lf__use_in_stat( $action , $data , $param1 = null , $param2 = null , $param3 = null ) {
		global $portalDB , $cache ;
		$LIST = array(
			"0" => "Да" ,
			"1" => "Нет" ,
		);
		switch ( $action ) {
			case "check" :
				$res = $portalDB->row( "select GROUP_CONCAT( CAST( `id` AS CHAR ) separator \"|\" ) as `lst` from `specialities`" );

				$data = $res[ "lst" ];
				$data = preg_match( "/^(?:".$data.")(?:,(?:".$data."))*$/" , $param1 );
				return ( $data == 1 );
				break ;

			case "getItems" :
				$res = $LIST ;
				return $res ;
				break ;

			case "getText" :
				$res = explode( "," , $param1 );
				$res = array_combine( $res , $res );

				$txt = array_intersect_key( $LIST , $res);
				$txt = implode( ", " , $txt );

				if ( strlen( $txt ) > 30 ) {
					$txt2 = substr( $txt , 0 , 27 )."..." ;
				} else {
					$txt2 = $txt ;
				}
				return array( "full" => $txt , "short" => $txt2 );
				break ;

			case "getCondition" :
				$r = explode( "," , $param2 );
				$r = array_combine( $r , $r );

				$cond = array();
				if ( isset( $r[ 0 ] ) ) {
					$cond[]= "( ".$param1." <=> 0 )" ;
					$cond[]= "( ".$param1." is null )" ;
				}
				if ( isset( $r[ 1 ] ) ) {
					$cond[]= "( ".$param1." <=> 1 )" ;
				}

				return array( "(".implode( " or " , $cond ).")" );
				break ;

			case "formatResult" :
				if ( is_null( $param1 ) ) {
					$param1 = 0 ;
				}

				if ( isset( $LIST[ $param1 ] ) ) {
					return $LIST[ $param1 ];
				} else {
					return "[НЕОПРЕДЕЛЕНО]" ;
				}
				break ;
		}

	}

	function lf__marks_list( $action , $data , $param1 = null , $param2 = null , $param3 = null ) {
		global $portalDB , $cache , $marksList ;

		switch ( $action ) {
			case "check" :
				$data = implode( "|" , array_keys( $marksList ) );
				$data = preg_match( "/^(?:".$data.")(?:,(?:".$data."))*$/" , $param1 );
				return ( $data == 1 );
				break ;

			case "getItems" :
				$l = array();
				foreach( $marksList as $row ) {
					$l[ $row[ "id" ] ] = $row[ "name" ]." [".$row[ "id" ]."]" ;
				}
				return $l ;
				break ;

			case "getText" :
				$midl = explode( "," , $param1 );
				$midl = array_combine( $midl , $midl );
				$l = array_intersect_key( $marksList , $midl );
				foreach( $l as &$row ) {
					$row = $row[ "name" ];
				} unset( $row );

				$txt = implode( ", " , $l );

				if ( strlen( $txt ) > 30 ) {
					$txt2 = substr( $txt , 0 , 27 )."..." ;
				} else {
					$txt2 = $txt ;
				}
				return array( "full" => $txt , "short" => $txt2 );
				break ;

			case "getCondition" :
				if ( $param2 == "" ) {
					return array( "( 1 )" );
				} else {
					/*$res = $portalDB->query( "select `ext_id` from `marks-objects` where ( `ext_type` = ? ) and ( `mark_id` in ( ?* ) )" , false , "s*i" , "woe" , explode( "," , $param2 ) );
					$ids = array();
					foreach( $res as $row ) {
						$ids[]= $row[ "ext_id" ];
					}

					//var_dump( $param3 );

					exit();

					return array( "(".$param1." in ( ".implode( "," , $ids )." ) )" );*/

					$midl = explode( "," , $param2 );
					$idflt = array();
					foreach( $midl as $row ) {
						$idflt[]= "( locate( ',".$row.",' , concat( ',' , ".$param1." , ',' ) ) )" ;
					}

					return array( "( ".implode( " or " , $idflt )." )" );
				}
				break ;

			case "formatResult" :
				if ( is_null( $param1 ) || $param1 == "" ) {
					return "" ;
				}

				$lst = array();
				$midl = explode( "," , $param1 );
				$lst = array_intersect_key( $marksList , array_combine( $midl , $midl ) );
				foreach( $lst as &$row ) {
					$row = $row[ "name" ];
				} unset( $row );
				return implode( ", " , $lst );
				//return $param1 ;
				break ;
		}
	}



	$allLists = array(
		"maindb_matincoming_state_list" => &$maindb_matincoming_state_list ,
		"maindb_expertize_state_list" => &$maindb_expertize_state_list ,
		"maindb_payments_state_list" => &$maindb_payments_state_list ,
		"maindb_writ_of_execution_state_list" => &$maindb_writ_of_execution_state_list ,
		"maindb_mark_object_type_list" => &$maindb_mark_object_type_list
	);

	$marksList = $portalDB->table( "marks-catalog" , "id" );

	$fltDepDesc = array(
		"matincoming" => array(
			"id"          => array( "type" => "key"  , "desc"     => "Идентификатор" ) ,
			"num"         => array( "type" => "func" , "funcName" => "matincomingNumber" , "src" => "id" , "desc" => "Номер экспертизы" , "restype" => "charID"  ) ,
			"inc_year"    => array( "type" => "calc" , "calc"     => "YEAR( $.`date` )" , "desc" => "Год поступления" , "restype" => "int" ) ,
			"date"        => array( "type" => "date" , "desc"     => "Дата поступления" ) ,
			"from_agency" => array( "type" => "ref"  , "ref"      => "agency" , "desc" => "Назначивший орган" ) ,
			"from_agent"  => array( "type" => "ref"  , "ref"      => "agent" , "desc" => "Назначившее лицо" ) ,
			"ex_data_3"   => array( "type" => "text" , "desc"     => "Основание (постоновление, определение и др.)" ) ,
			"ex_data_4"   => array( "type" => "text" , "desc"     => "Номер дела, количество томов и т.д." ) ,
			"exp_type"    => array( "type" => "list" , "funcName" => "simple_ref_list" , "data" => array( "tabn" => "casecategory" , "inForm" => true ) , "desc" => "Категория дела" ) ,
			"ex_data_6"   => array( "type" => "text" , "desc"     => "Кому передано" ) ,
			"ex_data_7"   => array( "type" => "text" , "desc"     => "Сведения о приостоновлении срока" ) ,
			"ex_data_8"   => array( "type" => "text" , "desc"     => "Дата сдачи заключения" ) ,
			"ex_data_9"   => array( "type" => "text" , "desc"     => "Дата и способ отправки" ) ,
			"state"       => array( "type" => "list" , "funcName" => "simple_list_int" , "data" => "maindb_matincoming_state_list" , "desc" => "состояние" ) ,
		) ,
		"matincominglvl2" => array(
			"id"        => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"mat_id"    => array( "type" => "ref"  , "ref"  => "matincoming" , "desc" => "Карточка 1-го уровня" ) ,
			"dep_id"    => array( "type" => "ref"  , "ref"  => "departments" , "desc" => "Отдел" ) ,
			"date"      => array( "type" => "date" , "desc" => "Дата передачи в подразделение" ) ,
			"materials" => array( "type" => "text" , "desc" => "Переданные материалы" ) ,
			"kat_slognost" => array( "type" => "int" , "desc" => "Категория сложности" )
		) ,
		"expertize" => array(
			"id"       => array( "type" => "key"   , "desc"     => "Идентификатор" ) ,
			"ext_id"   => array( "type" => "ref"   , "ref"      => "matincominglvl2" , "desc" => "Карточка 2-го уровня" ) ,
			"exp_id"   => array( "type" => "list"  , "funcName" => "portal_workers" , "data" => "" , "desc" => "Эксперт" ) ,
			"spec_id"  => array( "type" => "list"  , "funcName" => "portal_specialities" , "data" => "" , "desc" => "Специальность" ) ,
			"state"    => array( "type" => "list"  , "funcName" => "simple_list_int" , "data" => "maindb_expertize_state_list" , "desc" => "Состояние" ) ,
			"fin_date" => array( "type" => "date"  , "desc"     => "Дата завершения" ) ,
			"fin_year" => array( "type" => "calc"  , "calc"     => "YEAR( $.`fin_date` )" , "desc" => "Год завершения" , "restype" => "int" ) ,
		//	"price"    => array( "type" => "float" , "desc"     => "Стоимость" ) ,
		//	"pay_info" => array( "type" => "calc"  , "calc"     => "CONCAT( $.`pay_date` , \" \" , $.`pay_details` )" , "desc" => "Информация об оплате (выставленный счет, на кого возложено и пр.)" , "restype" => "text" ) ,
			"application_for_issuance" => array( "type" => "int"  , "desc" => "Заявление о выдаче исп. листа" ) ,
			"sndz"     => array( "type" => "list"  , "funcName" => "sndz" , "data" => "text" , "desc" => "СНДЗ" ) ,
			"v_srok"   => array( "type" => "calc"  , "calc"     => "IF( $.`reason_1` <=> 0 , 1 , 0 )" , "desc" => "Выполнено в срок" , "restype" => "int" ) ,
			"srok_narushen"   => array( "type" => "calc"  , "calc"     => "IF( $.`reason_1` > 0 , 1 , 0 )" , "desc" => "Срок нарушен" , "restype" => "int" ) ,
			"use_in_stat"     => array( "type" => "list"  , "funcName" => "use_in_stat" , "data" => "" , "desc" => "Экспертоучастие" ) ,
			"document" => array( "type" => "virtual" , "src" => "id" , "funcName" => "documentName" , "desc" => "Документ" ) ,
			"document_w_dn"  => array( "type" => "virtual" , "src" => "id" , "funcName" => "documentNameWDN" , "desc" => "Документ с номером и датой" ) ,
		) ,
		"agency" => array(
			"id"     => array( "type" => "key"  , "desc"     => "Идентификатор" ) ,
			"ext_id" => array( "type" => "list" , "funcName" => "simple_ref_list" , "data" => array( "tabn" => "type-of-agency" , "inForm" => true ) , "desc" => "Тип органа" ) ,
			"name"   => array( "type" => "text" , "desc"     => "название" )
		) ,
		"agent" => array(
			"id"   => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"name" => array( "type" => "text" , "desc" => "Название" )
		) ,
		"casecategory" => array(
			"id"   => array( "type" => "key"     , "desc" => "Идентификатор" ) ,
			"index"=> array( "type" => "int"     , "desc" => "Индекс" ) ,
			"name" => array( "type" => "cString" , "desc" => "Название" )
		) ,
		"departments" => array(
			"id"   => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"ind"  => array( "type" => "int"  , "desc" => "Индекс отдела" ) ,
			"name" => array( "type" => "text" , "desc" => "Название отдела" )
		) ,
		"workers" => array(
			"id"        => array( "type" => "key"     , "desc"     => "Идентификатор" ) ,
			"name"      => array( "type" => "nString" , "desc"     => "Имя сотрудника" ) ,
			"post_1_id" => array( "type" => "ref"     , "ref"      => "posts" , "desc" => "Должность основная" ) ,
			"dep"       => array( "type" => "ref"     , "ref"      => "departments" , "desc" => "Отдел" ) ,
			"spec"      => array( "type" => "list"    , "funcName" => "portal_specialities" , "data" => "" , "desc" => "Специальности" ) ,
		) ,

		"specialities" => array(
			"id"    => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"group" => array( "type" => "ref"  , "ref" => "specialities_groups" , "desc" => "Группа специальности" ) ,
			"num"   => array( "type" => "int"  , "desc" => "Номер" ) ,
			"desc"  => array( "type" => "text" , "desc" => "Название" ) ,
		) ,

		"specialities-groups" => array(
			"id"    => array( "type" => "key"     , "desc" => "Идентификатор" ) ,
			"index" => array( "type" => "int"     , "desc" => "Номер группы" ) ,
			"name"  => array( "type" => "cString" , "desc" => "Название группы" )
		) ,
		"departments" => array(
			"id"   => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"ind"  => array( "type" => "int"  , "desc" => "Индекс" ) ,
			"name" => array( "type" => "text" , "desc" => "Название" )
		) ,

		"posts" => array(
			"id"   => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"name" => array( "type" => "text" , "desc" => "Должность" ) ,
		) ,

		"type-of-agency" => array(
			"id"   => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"name" => array( "type" => "text" , "desc" => "Название" ) ,
		) ,

		'marks-objects' => array(
			"id"       => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"mark_id"  => array( "type" => "list" , "funcName" => "marks_list" , "data" => array( "type" => "woe" , "id" => array( 7 , 6 ) ) , "desc" => "Отметка" ) ,
			"ext_type" => array( "type" => "list" , "funcName" => "simple_list_text" , "data" => "maindb_mark_object_type_list" , "desc" => "тип объекта" ) ,
			"ext_id"   => array( "type" => "text" , "desc" => "идентификатор объекта" ) ,
			"date"     => array( "type" => "calc" , "calc" => "FROM_UNIXTIME( $.`date` )" , "desc" => "Дата установки" , "restype" => "date" ) ,
		)
	);

	if ( true /*$fKey  && false*/ ) {
		$fltDepDesc[ "payments" ] = array(
			"id"            => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"expertize_id"  => array( "type" => "ref"  , "ref" => "expertize" , "desc" => "Карточка 3-го уровня" ) ,
			"state"         => array( "type" => "list" , "funcName" => "simple_list_int" , "data" => "maindb_payments_state_list" , "desc" => "Состояние" ) ,
			"create_date_c" => array( "type" => "calc" , "calc" => "FROM_UNIXTIME( $.`create_date` )" , "desc" => "Дата создания"  , "restype" => "date" ) ,
			"create_date_y" => array( "type" => "calc" , "calc" => "YEAR( FROM_UNIXTIME( $.`create_date` ) )" , "desc" => "Год создания"  , "restype" => "int" ) ,
			"check_date_c"  => array( "type" => "calc" , "calc" => "FROM_UNIXTIME( $.`check_date` )" , "desc" => "Дата отметки"  , "restype" => "date" ) ,
			"check_date_y"  => array( "type" => "calc" , "calc" => "YEAR( FROM_UNIXTIME( $.`check_date` ) )" , "desc" => "Год отметки"  , "restype" => "int" ) ,
			/*"comment"       => array( "type" => "text" , "desc" => "Комментарий" ) ,*/
		//""             => array( "type" => "" , "desc" => "" ) ,*/
		);
		$fltDepDesc[ "writ-of-execution" ] = array(
			"id"     => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			'date_с'   => array( "type" => "calc" , "calc" => "DATE( FROM_UNIXTIME( $.`date` ) )" , "desc" => "Дата составления"  , "restype" => "date" ) ,
			'issue_date_с' => array( "type" => "calc" , "calc" => "DATE( FROM_UNIXTIME( $.`issue_date` ) )" , "desc" => "Дата выдачи"  , "restype" => "date" ) ,
			'date_force_c'   => array( "type" => "calc" , "calc" => "DATE( FROM_UNIXTIME( $.`date_force` ) )" , "desc" => "Дата вступления в законную силу"  , "restype" => "date" ) ,
			'incoming_date_c'   => array( "type" => "calc" , "calc" => "DATE( FROM_UNIXTIME( $.`incoming_date` ) )" , "desc" => "Дата поступления в центр"  , "restype" => "date" ) ,
			"num"    => array( "type" => "text" , "desc" => "Номер исполнительного листа" ) ,
			"state"  => array( "type" => "list" , "funcName" => "simple_list_int" , "data" => "maindb_writ_of_execution_state_list" , "desc" => "Состояние" ) ,
			"ext_id" => array( "type" => "ref"  , "ref" => "matincoming" , "desc" => "Карточка 1-го уровня" ) ,
		);
		$fltDepDesc[ "writ-of-execution-payers" ] = array(
			"id"     => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"ext_id" => array( "type" => "ref"  , "ref" => "writ-of-execution" , "desc" => "Исполнительные листы" ) ,
			"payer"  => array( "type" => "text" , "desc" => "Плательщик" ) ,
			"price"  => array( "type" => "float" , "desc" => "Возложенная часть оплаты" ) ,
		);
		$fltDepDesc[ "writ-of-execution-list-w-prices" ] = array(
			"id"     => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"ext_id" => array( "type" => "ref"  , "ref" => "matincoming" , "desc" => "Карточка 1-го уровня" ) ,
			"num"    => array( "type" => "text" , "desc" => "Номер исполнительного листа" ) ,
			"state"  => array( "type" => "list" , "funcName" => "simple_list_int" , "data" => "maindb_writ_of_execution_state_list" , "desc" => "Состояние" ) ,
			"marks"  => array( "type" => "list" , "funcName" => "marks_list" , "data" => array( "type" => "woe" , "id" => array( 7 , 6 ) ) , "desc" => "Отметки" ) ,
			"total_price"    => array( "type" => "virtual" , "src" => "total_price"   , "funcName" => "price_no_dup" , "restype" => "float" , "desc" => "Общая стоимость" ) ,
			"partial_price"  => array( "type" => "virtual" , "src" => "partial_price" , "funcName" => "price_no_dup" , "restype" => "float" , "desc" => "Частичная оплата" ) ,
			//"partial_price"    => array( "type" => "float" , "desc"     => "Частичная оплата" ) ,
		);

		/*$fltDepDesc[ "" => array(
			"id" => array( "type" => "key" , "desc" => "" ) ,
			"" => array( "type" => "calc" , "calc" => "$.`id` % 1000000" , "desc" => ""  , "restype" => "" ) ,
			"" => array( "type" => "ref" , "ref" => "" , "desc" => "" ) ,
			"" => array( "type" => "" , "desc" => "" ) ,
		)*/

	} else {
		$fltDepDesc[ "payments" ] = array(
			"id"            => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"rexpertize_id"  => array( "type" => "ref"  , "ref" => "expertize" , "desc" => "Карточка 3-го уровня" ) ,
			/*"state"         => array( "type" => "list" , "funcName" => "simple_list_int" , "data" => "maindb_payments_state_list" , "desc" => "Состояние" ) ,
			"create_date_c" => array( "type" => "calc" , "calc" => "FROM_UNIXTIME( $.`create_date` )" , "desc" => "Дата создания"  , "restype" => "date" ) ,
			"create_date_y" => array( "type" => "calc" , "calc" => "YEAR( FROM_UNIXTIME( $.`create_date` ) )" , "desc" => "Год создания"  , "restype" => "int" ) ,
			"check_date_c"  => array( "type" => "calc" , "calc" => "FROM_UNIXTIME( $.`check_date` )" , "desc" => "Дата отметки"  , "restype" => "date" ) ,
			"check_date_y"  => array( "type" => "calc" , "calc" => "YEAR( FROM_UNIXTIME( $.`check_date` ) )" , "desc" => "Год отметки"  , "restype" => "int" ) ,
			"comment"       => array( "type" => "text" , "desc" => "Комментарий" ) ,
		//""             => array( "type" => "" , "desc" => "" ) ,*/
		);
		/*$fltDepDesc[ "writ-of-execution" ] = array(
			"id"     => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"ext_id" => array( "type" => "ref"  , "ref" => "matincoming" , "desc" => "Карточка 1-го уровня" ) ,
			"num"    => array( "type" => "text" , "desc" => "Номер исполнительного листа" ) ,
			"state"  => array( "type" => "list" , "funcName" => "simple_list_int" , "data" => "maindb_writ_of_execution_state_list" , "desc" => "Состояние" ) ,
		);
		$fltDepDesc[ "writ-of-execution-payers" ] = array(
			"id"     => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"ext_id" => array( "type" => "ref"  , "ref" => "writ-of-execution" , "desc" => "Исполнительные листы" ) ,
			"payer"  => array( "type" => "text" , "desc" => "Плательщик" ) ,
			"price"  => array( "type" => "float" , "desc" => "Возложенная часть оплаты" ) ,
		);
		$fltDepDesc[ "writ-of-execution-list-w-prices" ] = array(
			"id"     => array( "type" => "key"  , "desc" => "Идентификатор" ) ,
			"ext_id" => array( "type" => "ref"  , "ref" => "matincoming" , "desc" => "Карточка 1-го уровня" ) ,
			"num"    => array( "type" => "text" , "desc" => "Номер исполнительного листа" ) ,
			"state"  => array( "type" => "list" , "funcName" => "simple_list_int" , "data" => "maindb_writ_of_execution_state_list" , "desc" => "Состояние" ) ,
			"marks"  => array( "type" => "list" , "funcName" => "marks_list" , "data" => array( "type" => "woe" , "id" => array( 7 , 6 ) ) , "desc" => "Отметки" ) ,
			"total_price"    => array( "type" => "float" , "desc"     => "Общая стоимость" ) ,
			"partial_price"    => array( "type" => "float" , "desc"     => "Частичная оплата" ) ,
		);*/
	}

	$fltTabName = array(
		"matincoming" => "Карточка 1-го уровня" ,
		"matincominglvl2" => "Карточка 2-го уровня" ,
		"expertize" => "Карточка 3-го уровня" ,
		"agency" => "От кого (орган)" ,
		"agent" => "От кого (лицо)" ,
		"casecategory" => "Категория дела" ,
		"departments" => "Отдел" ,
		"workers" => "Сотрудники" ,
		"specialities" => "Специальность" ,
		"specialities-groups" => "Группа специальностей" ,
		"posts" => "Должность" ,
		"type-of-agency" => "Тип органа" ,
		"payments" => "Оплата" ,
		"writ-of-execution" => "Исполнительные листы" ,
		"writ-of-execution-payers" => "Плательщики по исполнительным листам" ,
		"writ-of-execution-list-w-prices" => "Исполнительные листы с отметками" ,
		'marks-objects' => 'Отметки к объектам' ,
		//"" => "" ,
	);

	$sti = 0 ;
	function mkLists( $tn , $lvl , $gi ) {
		global $sti , $fltDepDesc , $fltTabName ;

		$selectFieldsList = array();
		$fromTablesList = array();
		$whereConditions = array();
		$tree = array(
			array( "t" => "g" , "c" => $fltTabName[ $tn ] , "lvl" => $lvl )
		);

		if ( $lvl == 0 ) {
			$sti = 0 ;
		} else {
			$sti++ ;
		}

		$fi = 0 ;

		$sta = "g".$gi."s".$sti ; // select table alias

		$fromTablesList[ $sta ] = array( "tabn" => $tn , "lvl" => $lvl , "gi" => $gi , "expr" => "`".$tn."` as `".$sta."`" );

		if ( $lvl == 0 && $gi == 0 ) {
		} else
		if ( $lvl == 0 && $gi > 0 ) {
		} else
		if ( $lvl > 0 ) {
		}

		$kfa = false ;
		$key = false ;
		foreach( $fltDepDesc[ $tn ] as $fn => $fd ) {
			$fa = $sta."f".( $fi++ ); // field alias
			if ( $fd[ "type" ] == "ref" ) {
				$res = mkLists( $fd[ "ref" ] , $lvl + 1 , $gi );
				$selectFieldsList = array_merge( $selectFieldsList , $res[ "sfl" ] );
				$fromTablesList = array_merge( $fromTablesList , $res[ "ftl" ] );
				$whereConditions = array_merge( $whereConditions , $res[ "wc" ] );
				$whereConditions[]= "`".$sta."`.`".$fn."` = ".$res[ "key" ]."" ;
				$tree = array_merge( $tree , $res[ "tree" ] );
			} else {
				switch ( $fd[ "type" ] ) {
					case "calc" :
						$fldc = "( ".str_replace( "$" , "`".$sta."`" , $fd[ "calc" ] )." )" ;
						break ;
					case "func" :
					case "virtual" :
						$fldc = "`".$sta."`.`".$fd[ "src" ]."`" ;
						break ;
					default :
						$fldc = "`".$sta."`.`".$fn."`" ;
						break ;
				}
				$selectFieldsList[ $fa ] = array (
					"fld" => $fn ,
					"tabn" => $tn ,
					"fldc" => $fldc ,
					"taba" => $sta ,
					"vis" => $fd[ "type" ] != "key"
				);
				$tree[]= array(
					"t" => "f" ,
					"f" => $fa ,
					"c" => $fd[ "desc" ] ,
					"lvl" => $lvl + 1 ,
				);
			}

			if ( $fd[ "type" ] == "key" ) {
				$kfa = $fa ;
				$key = "`".$sta."`.`".$fn."`" ;
			}
		}

		return array(
			"sfl" => $selectFieldsList ,
			"ftl" => $fromTablesList ,
			"wc" => $whereConditions ,
			"kfa" => $kfa ,
			"key" => $key ,
			"tree" => $tree
		);
	}

	function flt_default( $action , $fa = null , $param1 = null , $param2 = null ) {
		switch ( $action ) {
			case "mkEditor" :
				/* $fa - field alias : g0s1f4
				 $param1 - lists :array(
									"sfl" => $selectFieldsList ,
									"ftl" => $fromTablesList ,
									"wc" => $whereConditions ,
									"kfa" => $kfa ,
									"key" => $key ,
									"tree" => $tree
								);
				$param2 - filter / input value
				*/
				break ;

			case "check" :
				return false ;
				break ;

			case "getCondition" :
				/*
					$fa - calc formula for field : `maindb`.`matincoming`.`id`
					$param1 - filter / input value
				*/
				break ;

			case "formatResult" :
				/*
					$fa - value
					$param1 - descr
				*/
				return $fa ;
				break ;

			case "needFormatResult" :
				return false ;
				break ;
		}
	}
				/*$sfl = $param1[ "sfl" ];
				$fd = $sfl[ $fa ];
				$fDesc = $fltDepDesc[ $fd[ "tabn" ] ][ $fd[ "fld" ] ];*/

	function flt_int( $action , $fa = null , $param1 = null , $param2 = null ) {
		global $fltDepDesc ;
		switch ( $action ) {
			case "mkEditor" :
				if ( $param2 !== null ) {
					if ( flt_int( "check" , $param2 ) ) {
						echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\" class=\"i_int\">" ;
					} else {
						echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\" class=\"i_int i_err\">" ;
					}
				} else {
					echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"\" class=\"i_int\">" ;
				}

				break ;

			case "check" :
				$n = preg_match( "/^\s*\d+\s*(?:-\s*\d+\s*)?(?:,\s*\d+\s*(?:-\s*\d+\s*)?)*$/" , $fa );
				return ( $n == 1 );
				break ;

			case "getCondition" :
				$param1 = explode( "," , trim( $param1 ) );
				if ( count( $param1 ) < 1 ) {
					return false ;
				}
				$cIN = array();
				$res = array();
				foreach( $param1 as &$v ) {
					$v = explode( "-" , $v );
					if ( count( $v ) == 1 ) {
						$cIN[] = intVal( trim( $v[ 0 ] ) );
					} else {
						$v[ 0 ] = intVal( trim( $v[ 0 ] ) );
						$v[ 1 ] = intVal( trim( $v[ 1 ] ) );
						if ( $v[ 1 ] - $v[ 0 ] == 1 || $v[ 0 ] - $v[ 1 ] == 1 ) {
							$cIN[] = $v[ 0 ];
							$cIN[] = $v[ 1 ];
						} else
						if ( $v[ 0 ] > $v[ 1 ] ) {
							$res[]= $fa." between ".$v[ 1 ]." and ".$v[ 0 ];
						} else {
							$res[]= $fa." between ".$v[ 0 ]." and ".$v[ 1 ];
						}
					}
				}

				if ( count( $cIN ) > 0 ) {
					$res[]= $fa." in (". implode( "," , $cIN ).")" ;
				}

				$res = array( "(".implode( ") or (" , $res ).")" );
				return $res ;
				break ;

			case "formatResult" :
				return $fa ;
				break ;

			case "needFormatResult" :
				return false ;
				break ;
		}
	}

	function flt_float( $action , $fa = null , $param1 = null , $param2 = null ) {
		global $fltDepDesc ;
		switch ( $action ) {
			case "mkEditor" :
				if ( $param2 !== null ) {
					if ( flt_float( "check" , $param2 ) ) {
						echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\" class=\"i_float\">" ;
					} else {
						echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\" class=\"i_float i_err\">" ;
					}
				} else {
					echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"\" class=\"i_float\">" ;
				}

				break ;

			case "check" :
				$n = preg_match( "/^\s*\d+(?:[.]\d+)?\s*(?:-\s*\d+(?:[.]\d+)?\s*)?(?:,\s*\d+(?:[.]\d+)?\s*(?:-\s*\d+(?:[.]\d+)?\s*)?)*$/" , $fa );
				return ( $n == 1 );
				break ;

			case "getCondition" :
				$param1 = explode( "," , trim( $param1 ) );
				if ( count( $param1 ) < 1 ) {
					return false ;
				}
				$cIN = array();
				$res = array();
				foreach( $param1 as &$v ) {
					$v = explode( "-" , $v );
					if ( count( $v ) == 1 ) {
						$cIN[] = str_replace( "," , "." , floatval( trim( $v[ 0 ] ) ) );
					} else {
						$v[ 0 ] = floatval( trim( $v[ 0 ] ) );
						$v[ 1 ] = floatval( trim( $v[ 1 ] ) );
						if ( $v[ 0 ] > $v[ 1 ] ) {
							$res[]= $fa." between ".str_replace( "," , "." , $v[ 1 ] )." and ".str_replace( "," , "." , $v[ 0 ] );
						} else {
							$res[]= $fa." between ".str_replace( "," , "." , $v[ 0 ] )." and ".str_replace( "," , "." , $v[ 1 ] );
						}
					}
				}

				if ( count( $cIN ) > 0 ) {
					$res[]= $fa." in (". implode( "," , $cIN ).")" ;
				}

				$res = array( "(".implode( ") or (" , $res ).")" );
				return $res ;
				break ;

			case "formatResult" :
				return str_replace( "." , "," , $fa );
				break ;

			case "needFormatResult" :
				return true ;
				break ;
		}
	}

	function cvtDate( $s ) {
		list( $d , $m , $y ) = explode( "." , $s );
		return date( "Y-m-d" , mktime( 0 , 0 , 0 , $m , $d , $y ) );
	}

	function flt_date( $action , $fa = null , $param1 = null , $param2 = null ) {
		global $fltDepDesc ;
		switch ( $action ) {
			case "mkEditor" :
				if ( $param2 !== null ) {
					if ( flt_date( "check" , $param2 ) ) {
						echo "<input id=\"flt_".$fa."\" name=\"flt_".$fa."\" type=\"text\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\" class=\"i_date\" onkeypress=\"return dlgCalendarShow( event , '".$fa."' )\">" ;
					} else {
						echo "<input id=\"flt_".$fa."\" name=\"flt_".$fa."\" type=\"text\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\" class=\"i_date i_err\" onkeypress=\"return dlgCalendarShow( event , '".$fa."' )\">" ;
					}
				} else {
					echo "<input id=\"flt_".$fa."\" name=\"flt_".$fa."\" type=\"text\" value=\"\" class=\"i_date\" onkeypress=\"return dlgCalendarShow( event , '".$fa."' )\">" ;
				}

				break ;

			case "check" :
				$n = preg_match( "/^\s*\d{2}\.\d{2}\.\d{4}\s*(?:-\s*\d{2}\.\d{2}\.\d{4}\s*)?(?:,\s*\d{2}\.\d{2}\.\d{4}\s*(?:-\s*\\d{2}\.\d{2}\.\d{4}\s*)?)*$/" , $fa );
				return ( $n == 1 );
				break ;

			case "getCondition" :
				$param1 = explode( "," , trim( $param1 ) );
				if ( count( $param1 ) < 1 ) {
					return false ;
				}
				$cIN = array();
				$res = array();
				foreach( $param1 as &$v ) {
					$v = explode( "-" , $v );
					if ( count( $v ) == 1 ) {
						$cIN[] = cvtDate( trim( $v[ 0 ] ) );
					} else {
						$v[ 0 ] = cvtDate( trim( $v[ 0 ] ) );
						$v[ 1 ] = cvtDate( trim( $v[ 1 ] ) );
						$res[]= $fa." between \"".$v[ 0 ]."\" and \"".$v[ 1 ]."\"" ;
					}
				}

				if ( count( $cIN ) > 0 ) {
					$res[]= $fa." in (". implode( "," , $cIN ).")" ;
				}

				$res = array( "(".implode( ") or (" , $res ).")" );
				return $res ;
				break ;

			case "formatResult" :
				return $fa ;
				break ;

			case "needFormatResult" :
				return false ;
				break ;
		}
	}

	function flt_text( $action , $fa = null , $param1 = null , $param2 = null ) {
		global $fltDepDesc ;
		switch ( $action ) {
			case "mkEditor" :
				if ( $param2 !== null ) {
					if ( flt_text( "check" , $param2 ) ) {
						echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\" class=\"i_text\">" ;
					} else {
						echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\" class=\"i_text i_err\">" ;
					}
				} else {
					echo "<input name=\"flt_".$fa."\" type=\"text\" value=\"\" class=\"i_text\">" ;
				}

				break ;

			case "check" :
				return true ;
				break ;

			case "getCondition" :
				return array( "(".$fa." like concat( \"%\" , ".Str2SQL( $param1 )." , \"%\" ) )" );
				break ;

			case "formatResult" :
				return $fa ;
				break ;

			case "needFormatResult" :
				return false ;
				break ;
		}
	}

	function flt_list( $action , $fa = null , $param1 = null , $param2 = null ) {
		global $fltDepDesc , $allLists ;
		switch( $action ) {
			case "mkEditor" :
				$a = $param1[ "sfl" ][ $fa ];
				$lfn = "lf__".$fltDepDesc[ $a[ "tabn" ] ][ $a[ "fld" ] ][ "funcName" ];
				$lfd = isset( $fltDepDesc[ $a[ "tabn" ] ][ $a[ "fld" ] ][ "data" ] ) ? $fltDepDesc[ $a[ "tabn" ] ][ $a[ "fld" ] ][ "data" ] : null ;
				if ( $param2 !== null ) {
					if ( $lfn( "check" , $lfd , $param2 ) ) {
						$lfn = $lfn( "getText" , $lfd , $param2 );
						echo "<input id=\"flt_".$fa."\" name=\"flt_".$fa."\" type=\"hidden\" value=\"".htmlentities( $param2 , ENT_COMPAT , "cp1251" )."\"><a id=\"flt_a_".$fa."\" onclick=\"dlgListShow( event , '".$fa."' , '".$a[ "tabn" ]."' , '".$a[ "fld" ]."' );\" class=\"i_list\" title=\"".$lfn[ "full" ]."\">".$lfn[ "short" ]."</a>" ;
					} else {
						echo "<input id=\"flt_".$fa."\" name=\"flt_".$fa."\" type=\"hidden\" value=\"\"><a id=\"flt_a_".$fa."\" onclick=\"dlgListShow( event , '".$fa."' , '".$a[ "tabn" ]."' , '".$a[ "fld" ]."' );\" class=\"i_list i_err\">Ошибка</a>" ;
					}
				} else {
					echo "<input id=\"flt_".$fa."\" name=\"flt_".$fa."\" type=\"hidden\" value=\"\" class=\"i_text\"><a id=\"flt_a_".$fa."\" onclick=\"dlgListShow( event , '".$fa."' , '".$a[ "tabn" ]."' , '".$a[ "fld" ]."' );\" class=\"i_list\">&lt; выбрать &gt;</a>" ;
				}

				break ;

			case "getCondition" :
				$lfn = "lf__".$param2[ "funcName" ];
				$lfd = $param2[ "data" ];
				return $lfn( "getCondition" , $lfd , $fa , $param1 );
				break ;

			case "formatResult" :
				$lfn = "lf__".$param1[ "funcName" ];
				$lfd = $param1[ "data" ];
				return $lfn( "formatResult" , $lfd , $fa , $param1 );
				break ;

			case "needFormatResult" :
				return true ;
				break ;
		}
	}

	function flt_charID( $action , $fa = null , $param1 = null , $param2 = null ) {
		global $fltDepDesc ;
		switch ( $action ) {
			case "mkEditor" :
				break ;

			case "check" :
				return false ;
				break ;

			case "getCondition" :
				return false ;
				break ;

			case "formatResult" :
				if ( $param1[ "type" ] == "func" ) {
					$fn = $param1[ "funcName" ];
					return ( $fn( $fa ) );
				} else {
					return $fa ;
				}
				//return print_r( $param1 , true );
				break ;

			case "needFormatResult" :
				return true ;
				break ;
		}
	}

	function price_no_dup( $action , $fa = null , $param1 = null , $param2 = null ) {
		global $fltDepDesc ;
		switch ( $action ) {
			case "mkEditor" :
				break ;

			case "check" :
				return false ;
				break ;

			case "getCondition" :
				return false ;
				break ;

			case "formatResult" :
				$fa = explode( ";" , trim( trim( $fa , ";" ) ) );
				$fa = array_unique( $fa );
				$res = 0 ;
				foreach( $fa as $cfa ) {
					$cfa = explode( ":" , $cfa );
					$res+= intval( $cfa[ 0 ] );
				}
				return str_replace( "." , "," , $res / 100 );
				break ;

			case "needFormatResult" :
				return true ;
				break ;
		}
	}

	function documentName( $action , $fa = null , $param1 = null , $param2 = null , $map = array() ) {
		global $fltDepDesc , $TAB_CASECATEGORY ;
		switch ( $action ) {
			case "mkEditor" :
				break ;

			case "check" :
				return false ;
				break ;

			case "getCondition" :
				return false ;
				break ;

			case "formatResult" :
				$fldn_EXP_STATE = $map[ 'expertize/state' ];
				$expStateCode = $param2[ $fldn_EXP_STATE.':orig' ];
				if ( $expStateCode != 1 ) {
					return '------' ;
				}

				$fldn_EXP_TYPE = $map[ 'matincoming/exp_type' ];
				$expType = $param2[ $fldn_EXP_TYPE.':orig' ];

				$fldn_SNDZ = $map[ 'expertize/sndz' ];
				$sndz = $param2[ $fldn_SNDZ.':orig' ];
				if ( is_null( $sndz ) ) {
					$sndz = 0 ;
				}
				if ( isset( $TAB_CASECATEGORY[ $expType ] ) ) {
					$ccGroup = $TAB_CASECATEGORY[ $expType ][ 'cc_group' ];
					switch ( $ccGroup ) {
						case 0 :
						case 5 :
							return $sndz == 1 ? 'Сообщение о невозможности дать заключение' : 'Акт экспертного исследования' ;
						case 1 :
						case 2 :
						case 3 :
						case 4 :
						case 6 :
							return $sndz == 1 ? 'Сообщение о невозможности дать заключение' : 'Заключение эксперта' ;
						default :
							return '<ОШИБКА>' ;
					}
				} else {
					return '<ОШИБКА>' ;
				}

				break ;

			case "needFormatResult" :
				return true ;
				break ;
		}
	}


	function documentNameWDN( $action , $fa = null , $param1 = null , $param2 = null , $map = array() ) {
		global $fltDepDesc , $TAB_CASECATEGORY ;
		switch ( $action ) {
			case "mkEditor" :
				break ;

			case "check" :
				return false ;
				break ;

			case "getCondition" :
				return false ;
				break ;

			case "formatResult" :
				$fldn_EXP_STATE = $map[ 'expertize/state' ];
				$expStateCode = $param2[ $fldn_EXP_STATE.':orig' ];
				if ( $expStateCode != 1 ) {
					return '------' ;
				}

				$fldn_EXP_TYPE = $map[ 'matincoming/exp_type' ];
				$expType = $param2[ $fldn_EXP_TYPE.':orig' ];

				$fldn_SNDZ = $map[ 'expertize/sndz' ];
				$sndz = $param2[ $fldn_SNDZ.':orig' ];
				if ( is_null( $sndz ) ) {
					$sndz = 0 ;
				}
				if ( isset( $TAB_CASECATEGORY[ $expType ] ) ) {
					$ccGroup = $TAB_CASECATEGORY[ $expType ][ 'cc_group' ];
					$fldn_MAT_ID = $map[ 'matincoming/id' ];
					$matID = $param2[ $fldn_MAT_ID ];
					$fldn_DEP_ID = $map[ 'departments/id' ];
					$depID = $param2[ $fldn_DEP_ID ];
					$matNumber = matincomingNumberFull( $matID , $depID , $expType );
					$fldn_FIN_DATE = $map[ 'expertize/fin_date' ];
					$finDate = date( 'd.m.Y' , strtotime( $param2[ $fldn_FIN_DATE.':orig' ] ) );
					$docNamePart = '' ;
					switch ( $ccGroup ) {
						case 0 :
						case 5 :
							$docNamePart = $sndz == 1 ? 'Сообщение о невозможности дать заключение' : 'Акт экспертного исследования' ;
							break ;
						case 1 :
						case 2 :
						case 3 :
						case 4 :
						case 6 :
							$docNamePart = $sndz == 1 ? 'Сообщение о невозможности дать заключение' : 'Заключение эксперта' ;
							break ;
						default :
							return '<ОШИБКА>' ;
					}

					return $docNamePart.' от '.$finDate.' №'.$matNumber ;
				} else {
					return '<ОШИБКА>' ;
				}

				break ;

			case "needFormatResult" :
				return true ;
				break ;
		}
	}





	if ( isset( $_REQUEST[ "mode" ] ) && $_REQUEST[ "mode" ] == "ajax" ) {
		header( "Content-Type: application/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ "data" ] );

		$data = $DD->documentElement ;

		switch ( $data->nodeName ) {
			case "get-list-items" :
				$tabn = $data->getAttribute( "tabn" );
				$fld = $data->getAttribute( "fld" );
				$lfn = "lf__".$fltDepDesc[ $tabn ][ $fld ][ "funcName" ];
				$lfd = $fltDepDesc[ $tabn ][ $fld ][ "data" ];
				$lst = $lfn( "getItems" , $lfd );
				echo "<result>" ;
				foreach( $lst as $id => $desc ) {
					echo "<i id=\"".$id."\">".toCDATA( $desc )."</i>" ;
				}
				echo "</result>" ;
				break ;
		}

		exit();
	}

	//$portalDB->dbgMode = true ;

	if ( isset( $_REQUEST[ "flt" ] ) ) {
		$flt = json_decode( gzuncompress( base64_decode( $_REQUEST[ "flt" ] ) ) , true );
	} else {
		$flt = array();
	}

	if ( isset( $_REQUEST[ "fop" ] ) ) {
		if ( isset( $_REQUEST[ "btn_add_table" ] ) ) {
			$flt[]= array( "tabn" => $_REQUEST[ "add_table_name" ] , "group_1" => "g0s0f0" , "order_1" => "g0s0f0" );
		}
	}

	$ti = 0 ;
	$fltTabLists = array(
		"sfl" => array() , // select field list
		"ftl" => array() , // from tab list
		"wc" => array() , // where conditions
		"tree" => array() //
	);

	foreach( $flt as $ftd ) {
		$lists = mkLists( $ftd[ "tabn" ] , 0 , $ti++ );
		$fltTabLists[ "sfl" ] = array_merge( $fltTabLists[ "sfl" ] , $lists[ "sfl" ] );
		$fltTabLists[ "ftl" ] = array_merge( $fltTabLists[ "ftl" ] , $lists[ "ftl" ] );
		$fltTabLists[ "wc" ] = array_merge( $fltTabLists[ "wc" ] , $lists[ "wc" ] );
		$fltTabLists[ "tree" ] = array_merge( $fltTabLists[ "tree" ] , $lists[ "tree" ] );
	}

	if ( isset( $_REQUEST[ "btn_apply_flt" ] ) ) {
		foreach( $fltTabLists[ "sfl" ] as $fa => $fd ) {
			$ta = $fltTabLists[ "sfl" ][ $fa ][ "taba" ];
			$gi = $fltTabLists[ "ftl" ][ $ta ][ "gi" ];
			if ( isset( $_REQUEST[ "flt_".$fa ] ) && trim( $_REQUEST[ "flt_".$fa ] ) != "" ) {
				$flt[ $gi ][ "fltd" ][ $fa ] = iconv( "cp1251" , "utf8" , $_REQUEST[ "flt_".$fa ] );
			} else {
				unset( $flt[ $gi ][ "fltd" ][ $fa ] );
			}

			$fltTabLists[ "sfl" ][ $fa ][ "vis" ] = isset( $_REQUEST[ "vis_cols" ] ) && in_array( "".$fa , $_REQUEST[ "vis_cols" ] );
			if ( $fltTabLists[ "sfl" ][ $fa ][ "vis" ] ) {
				if ( !isset( $flt[ $gi ][ "vis" ] ) ) {
					$flt[ $gi ][ "vis" ] = array();
				}
				$flt[ $gi ][ "vis" ][] = $fa ;
			}
		}

		if ( isset( $_REQUEST[ "group_1" ] ) ) {
			$fa = $_REQUEST[ "group_1" ];
			$flt[ $gi ][ "group_1" ] = $fa ;
			foreach( $flt as &$f ) {
				$f[ "group_1" ] = $fa ;
			}
			unset( $f );
		}

		if ( isset( $_REQUEST[ "order_1" ] ) ) {
			$fa = $_REQUEST[ "order_1" ];
			$flt[ $gi ][ "order_1" ] = $fa ;
			foreach( $flt as &$f ) {
				$f[ "order_1" ] = $fa ;
			}
			unset( $f );
		}
	}

	//print_r_html( $_REQUEST );

	function generateResult( $rc = null ) {
		global $portalDB ,
		$fltTabLists , $fltDepDesc , $flt ;

		$fCaps = array(); // Заголовки
		$faMap = array();

		$sfl = array(); // select fields list
		$ftl = array(); // from tab list
		$ewc = array(); // where condition

		/*echo "generateResult / fltTabLists\r\n" ;
		print_r( $fltTabLists );*/

		foreach( $fltTabLists[ "sfl" ] as $fa => $fd ) { // fa - field alias , fd - field desc
			$ta = $fd[ "taba" ]; // tab alias
			$gi = $fltTabLists[ "ftl" ][ $ta ][ "gi" ]; //

			$fdd = $fltDepDesc[ $fd[ "tabn" ] ][ $fd[ "fld" ] ]; // $field description data

			$sfl[]= $fd[ "fldc" ]." as `".$fa."`" ;
			if ( in_array( $fa , $flt[ $gi ][ "vis" ] ) ) {
				$fCaps[ $fa ] = array( "data" => $fdd );
			}

			if ( $fdd[ "type" ] == "calc" || $fdd[ "type" ] == "func" ) {
				$func = "flt_".$fdd[ "restype" ];
			} else
			if ( $fdd[ "type" ] == "virtual" ) {
				$func = $fdd[ "funcName" ];
			} else {
				$func = "flt_".$fdd[ "type" ];
			}
			if ( !function_exists( $func ) ) {
				$func = "flt_default" ;
			}

			if ( isset( $flt[ $gi ][ "fltd" ][ $fa ] ) ) {
				$ewc = array_merge( $ewc , $func( "getCondition" , $fd[ "fldc" ] , iconv( "utf8" , "cp1251" , $flt[ $gi ][ "fltd" ][ $fa ] ) , $fdd ) );
			}

			$faMap[ $fd[ 'tabn' ].'/'.$fd[ 'fld' ] ] = $fa ;
		}
		foreach( $fltTabLists[ "ftl" ] as $td ) {
			$ftl[]= $td[ "expr" ];
		}

		$gbf = $flt[ 0 ][ "group_1" ];
		$gbf = $fltTabLists[ "sfl" ][ $gbf ][ "fldc" ];

		$wc = array_merge( $ewc , $fltTabLists[ "wc" ] );
		$q = array();
		$q[]= "select " ;
		$q[]= implode( "," , $sfl );
		$q[]= " from " ;
		$q[]= implode( "," , $ftl );
		$q[]= " where ((" ;
		$q[]= implode( ") and (" , $wc );
		$q[]= ")) ".( $rc === null ? "" : "limit ".$rc." " )." " ;
		$q[]= " group by ".$gbf." " ;
		//$q[]= " group by `".$flt[ 0 ][ "group_1" ]."` " ;
		$q[]= " order by `".$flt[ 0 ][ "order_1" ]."` asc" ;
		$q = implode( $q );

		//echo $q."\r\n" ;
		$reqData = $portalDB->query( $q );

		foreach( $fCaps as $fa => $fDesc ) {
			if ( $fDesc[ "data" ][ "type" ] == "calc" || $fDesc[ "data" ][ "type" ] == "func" ) {
				$func = "flt_".$fDesc[ "data" ][ "restype" ];
			} else
			if ( $fDesc[ "data" ][ "type" ] == "virtual" ) {
				$func = $fDesc[ "data" ][ "funcName" ];
			} else {
				$func = "flt_".$fDesc[ "data" ][ "type" ];
			}

			if ( !function_exists( $func ) ) {
				$func = "flt_default" ;
			}

			foreach( $reqData as &$row ) {
				$row[ $fa.':orig' ] = $row[ $fa ];
			} unset( $row );
		}

		foreach( $fCaps as $fa => $fDesc ) {
			//print_r( $fDesc );
			if ( $fDesc[ "data" ][ "type" ] == "calc" || $fDesc[ "data" ][ "type" ] == "func" ) {
				$func = "flt_".$fDesc[ "data" ][ "restype" ];
			} else
			if ( $fDesc[ "data" ][ "type" ] == "virtual" ) {
				$func = $fDesc[ "data" ][ "funcName" ];
			} else {
				$func = "flt_".$fDesc[ "data" ][ "type" ];
			}

			if ( !function_exists( $func ) ) {
				$func = "flt_default" ;
			}

			$needFormatResult = $func( "needFormatResult" );
			if ( $needFormatResult ) {
				foreach( $reqData as &$row ) {
					$row[ $fa ] = $func( "formatResult" , $row[ $fa ] , $fDesc[ "data" ] , $row , $faMap );
				} unset( $row );
			}
		}

		$res = array( "colData" => &$fCaps , "result" => &$reqData );

		return $res ;
	}

	if ( isset( $_REQUEST[ "getCSVFile" ] ) ) {

		function prepCSVField( $s ) {
			$s = str_replace( "\r" , " " , $s );
			$s = str_replace( "\n" , " " , $s );
			$s = str_replace( "\t" , " " , $s );
			$s = str_replace( "  " , " " , $s );
			return trim( $s );
		}

		header( "Content-Type: text/csv" );
		header( "Content-Disposition: attachment;filename=all.csv" );

		$fp = fopen( "php://output" , "w" );

		$req = generateResult();
		$fCaps = $req[ "colData" ];

		$resRow = array();
		foreach( $fCaps as $fa => $fDesc ) {
			$resRow[]= prepCSVField( $fDesc[ "data" ][ "desc" ] );
		}
		fputcsv( $fp , $resRow , ";" );

		$sfl = &$fltTabLists[ "sfl" ];
		foreach( $req[ "result" ] as &$row ) {
			$resRow = array();
			foreach( $fCaps as $fa => $fDesc ) {
				$resRow[]= prepCSVField( $row[ $fa ] );
			}
			fputcsv( $fp , $resRow , ";" );
		}
		unset( $row );

		fputcsv( $fp , array() );
		fputcsv( $fp , array( "Количество : ".count( $req[ "result" ] ) ) , ";" );
		fputcsv( $fp , array( "Дата составления : ".date( "d.m.Y H:i" , time() ) ) , ";" );

		fclose( $fp );
		exit();
	}


	$ert = array();
	if ( count( $flt ) < 1 ) {
		foreach( $fltDepDesc as $tn => $td ) {
			$ert[]= array( "tabn" => $tn );
		}
	} else {
		foreach( $fltDepDesc as $tn => $td ) {
			foreach( $td as $fn => $fd ) {
				if ( $fd[ "type" ] == "ref" ) {
					foreach( $flt as $d ) {
						if ( $fd[ "ref" ] == $d[ "tabn" ] ) {
							$ert[]= array( "tabn" => $tn , "fld" => $fn , "ttabn" => $d[ "tabn" ] );
						}
					}
				}
			}
		}
	}


	MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" , "%UT/all.css" ) , array( "files/all.js" ) , "hlp/no_access.html" );
	/*$ss1 = json_encode( $flt );
	for( $i = 0 ; $i <= 9 ; $i++ ) {
		$ss2 = urlencode( base64_encode( gzcompress( json_encode( $flt ) , $i ) ) );
		echo $i." : ".strlen( $ss1 )." / ".strlen( $ss2 )." , ".( strlen( $ss1 ) / strlen( $ss2 ) )."<br>" ;
	}*/
	echo "<form action=\"all.php?fop&flt=".urlencode( base64_encode( gzcompress( json_encode( $flt ) ) ) )."\" method=\"post\">
	<table align=\"center\" class=\"mft\"><tr>" ;

	$ti = 0 ;
	//print_r_html( $flt , true );
	foreach( $flt as $ftd ) {
		$ftn = $ftd[ "tabn" ];
		echo "<td class=\"tf-area\" valign=\"top\">
			<table class=\"fp-area\">" ;

		$lists = mkLists( $ftn , 0 , $ti++ );
		//print_r_html( $lists , true );
		$sfl = &$lists[ "sfl" ];
		//print_r_html( $lists[ "tree" ] );
		foreach( $lists[ "tree" ] as $te ) {
			$sep = str_pad( "" , $te[ "lvl" ] * 24 , "&nbsp;" );
			if ( $te[ "t" ] == "g" ) {
				echo "<tr><td colspan=\"5\" class=\"flt-gn\">".$sep.$te[ "c" ]."</td></tr>" ;
			} else {
				$fa = $te[ "f" ];
				$fd = $sfl[ $fa ];
				$fDesc = $fltDepDesc[ $fd[ "tabn" ] ][ $fd[ "fld" ] ];
				$fltD = ( isset( $ftd[ "fltd" ][ $fa ] ) ? iconv( "utf8" , "cp1251" , $ftd[ "fltd" ][ $fa ] ) : null );
				if ( isset( $_REQUEST[ "btn_apply_flt" ] ) ) {
					$fd[ "vis" ] = isset( $_REQUEST[ "vis_cols" ] ) && in_array( "".$fa , $_REQUEST[ "vis_cols" ] );
				}

					echo "<tr>
						<td class=\"flt-pn\">
							".$sep.$te[ "c" ]."
						</td>
						<td class=\"flt-v\">
							<input name=\"vis_cols[]\" type=\"checkbox\" value=\"".$fa."\" ".( $fd[ "vis" ] ? "checked" : "" ).">
						</td>
						<td class=\"flt-d\">" ;
							if ( $fDesc[ "type" ] == "calc" || $fDesc[ "type" ] == "func" ) {
								$funcName = "flt_".$fDesc[ "restype" ];
							} else
							if ( $fDesc[ "type" ] == "virtual" ) {
								$funcName = $fDesc[ "funcName" ];
							} else {
								$funcName = "flt_".$fDesc[ "type" ];
							}

							if ( !function_exists( $funcName ) ) {
								$funcName = "flt_default" ;
							}

							$funcName( 'mkEditor' , $fa , $lists , $fltD );
						echo '</td>
						<td class="flt-s">
							<input name="group_1" type="radio" value="'.$fa.'"'.( isset( $flt[ 0 ][ 'group_1' ] ) && $flt[ 0 ][ 'group_1' ] == $fa ? ' checked' : '' ).'>
						</td>
						<td class="flt-s">
							<input name="order_1" type="radio" value="'.$fa.'"'.( isset( $flt[ 0 ][ 'order_1' ] ) && $flt[ 0 ][ 'order_1' ] == $fa ? ' checked' : '' ).'>
						</td>
					</tr>' ;

			}
		}
		echo '</table>
		</td>' ;
	}

	echo '<td>
	</td>' ;

	echo '</tr></table>
		<div id="controls-panel" class="controls-panel">
			<select name="add_table_name">' ;
				foreach( $ert as $td ) {
					$tn = $td[ 'tabn' ];
					echo '<option value="'.$tn.'">'.$fltTabName[ $tn ].'</option>' ;
				}
			echo '</select>
			<input name="btn_add_table" type="submit" value="Выбрать таблицу" />
			<hr>
			<input name="btn_apply_flt" type="submit" value="Применить изменения" />
			<hr>
			<a href="?getCSVFile&flt='.urlencode( base64_encode( gzcompress( json_encode( $flt ) ) ) ).'" target="_blank">Скачать результат в формате CSV</a>
		</div>
	</from>' ;

	$today = explode( '-' , date( 'd-m-Y' , time() ) );

	echo '<div id="blockator" style="display : none ;">
	</div>' ;


	echo '<div id="dlgCalendar" class="dlg-calendar" style="display : none ;">
		<div class="dlg-calendar-month-area">
			<a onclick="dlgCalendar( 6 );" class="dlg-calendar-year-prev"></a>
			<a onclick="dlgCalendar( 2 );" class="dlg-calendar-month-prev"></a>
			<div id="dlgCalendarMonthName" class="dlg-calendar-month-name"></div>
			<a onclick="dlgCalendar( 3 );" class="dlg-calendar-month-next"></a>
			<a onclick="dlgCalendar( 7 );" class="dlg-calendar-year-next"></a>
		</div>
		<table id="dlgCalendarTable" class="dlg-calendar-table">
			<tr>
				<td class="dlg-calendar-week-day-0">Пн</td>
				<td class="dlg-calendar-week-day-0">Вт</td>
				<td class="dlg-calendar-week-day-0">Ср</td>
				<td class="dlg-calendar-week-day-0">Чт</td>
				<td class="dlg-calendar-week-day-0">Пт</td>
				<td class="dlg-calendar-week-day-1">Сб</td>
				<td class="dlg-calendar-week-day-1">Вс</td>
			</tr>
		</table>
		<div id="dlgCalendarCurrentDate" class="dlg-calendar-current-date" onclick="dlgCalendar( 5 , '.$today[ 0 ].' , '.$today[ 1 ].' , '.$today[ 2 ].' )">
			<div class="dlg-calendar-current-date-marker dlg-calendar-today"></div><span id="dlgCalendarCurrentDateValue"> - текущая дата '.date( 'd-m-Y' , time() ).'</span>
		</div>
	</div>' ;

	echo '<div id="dlgList" class="dlg-list" style="display : none ;">
		<div class="dlg-list-area">
			<table id="dlg_list_table" align="center" class="dlg-list-table">
			</table>
		</div>
		<a id="dlg_list_apply" onclick="dlgListApply()" class="dlg-list-apply">Принять</a> <a id="dlg_list_clear" onclick="dlgListClear()" class="dlg-list-clear">Очистить</a> <a id="dlg_list_apply" onclick="dlgListCancel()" class="dlg-list-cancel">Отмена</a>
	</div>' ;

	closeHtml();
	