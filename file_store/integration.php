<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "lconfig.php" );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	/*
	 * "multilist-sep" - разделитель для случая множественного списка
	 * 		: "none" - без разделителей
	 * 		: "header" - заголовок
	 * 		: "user-defined" - определяется пользователем , в "multilist-sep-data" соответствующая строка
	 * "header" : 1 - показывать заголовок
	 */

	function integrate( $id , $opt = false , $overrideViewStyle = false , $overrideTableStyle = false ) {
		global $portalDB , $PlaceID , $groups ;

		$defViewStyle = array(
			"style" => "table" ,
			"order" => "name" ,
			"param" => "asc"
		);

		$defOpt = array(
			"file-name-style" => "def-file-store-file-name" ,
			"header" => 1 ,
			"icon-style-prefix" => "def-file-icon-" ,
			"field-name-file" => "file_id[]" ,
			"field-name-dir" => "dir_id[]" ,
			"may-select" => 1 ,
			"multilist-sep" => "header" ,
			"no-rights-message" => "Нет доступа" ,

			"path-at-top-simple" => false ,
			"path-at-top-style" => "" ,

			"important-files-mark" => false ,
			"deleted-files-mark" => false ,

			"show-dirs" => 1 ,
			"show-files" => 1 ,
			"show-icons" => 0 ,
			"show-path-at-top" => 1 ,
			"show-deleted" => 0 ,
			"open-in" => false ,
			"name-preprocess" => false ,

			"db-connection" => false ,

			"_fnc" => "" // file-name class from file-name-style
		);


		if ( !is_array( $id ) ) {
			$id = array( $id );
		}


		if ( $opt !== false ) {
			if ( is_string( $opt ) ) {
				$opt = json_decode( $opt , true );
			}
			$opt = array_merge( $defOpt , $opt );
		} else {
			$opt = $defOpt ;
		}
		if ( $opt[ "file-name-style" ] === false ) {
			$opt[ "_fnc" ] = "" ;
		} else {
			$opt[ "_fnc" ] = " class=\"".$opt[ "file-name-style" ]."\"" ;
		}

		$buildPath = function( $db , $cl , $simple = true ) use ( $opt ) {
			$cndtn = $opt[ "_cndtn" ];
			$pathNodes = array( $cl );
			$cpn = $cl ;
			while ( $cpn[ "ext_id" ] > 0 ) {
				$cpn = $db->row( "select * from `".$cndtn."` where `id` = ?" , "i" , $cpn[ "ext_id" ] );
				$pathNodes[]= $cpn ;
			}

			$clPath = " / " ;
			$pni = 0 ;
			foreach( $pathNodes as $pn ) {
				if ( $pn[ "id" ] > 0 ) {
					if ( $simple ) {
						$clPath = " / ".$pn[ "name" ].$clPath ;
					} else {
						$clPath = " / <a href=\"/file_store/main.php?id=".$pn[ "id" ]."\" target=\"_blank\">".$pn[ "name" ]."</a>".$clPath ;
					}
				}
				$pni++ ;
			}

			return $clPath ;
		};

		$flt = makeSimpleTable_init_filter();
		$flt[ "c" ] = function( &$r , $c , $v ) use( $opt ) {
			if ( $r[ "obj-type" ] != 0 ) {
				$fnp = $opt[ "field-name-file" ];
			} else {
				$fnp = $opt[ "field-name-dir" ];
			}
			return "<input type=\"checkbox\" name=\"".$fnp."\" value=\"".$v."\">" ;
		};
		$flt[ "fs" ] = function( $r , $c , $v ) {
			if ( $r[ "obj-type" ] != 0 ) {
				$default_filter = makeSimpleTable_init_filter();
				return $default_filter[ "fs" ]( $r , $c , $v );
			} else {
				return "" ;
			}
		};
		$flt[ "type" ] = function( $r , $c , $v ) {
			if ( $r[ "obj-type" ] != 0 ) {
				return $v ;
			} else {
				return "Папка" ;
			}
		};

		$flt[ "name" ] = function( $r , $c , $v ) use ( &$opt ) {
			if ( $r[ "obj-type" ] != 0 ) {
				if ( $r[ "type" ] == "url" ) {
					$url = $r[ "src_name" ];
					$postfix = "url" ;
				} else {
					$url = "/file_store/download.php?id=".$r[ "id" ];
					$postfix = $r[ "type" ];
				}
			} else {
				$url = "/file_store/main.php?id=".$r[ "id" ];
				$postfix = "folder" ;
			}

			$icon = ( $opt[ "show-icons" ] ? "<div class=\"".$opt[ "icon-style-prefix" ].$postfix."\"></div>" : "" );
			if ( isset( $r[ "important" ] ) && $r[ "important" ] == 1 && $opt[ "important-files-mark" ] !== false ) {
				$imp = $opt[ "important-files-mark" ];
			} else {
				$imp = "" ;
			}

			if ( $r[ "deleted" ] == 1 && $opt[ "deleted-files-mark" ] !== false ) {
				$del = $opt[ "deleted-files-mark" ];
			} else {
				$del = "" ;
			}

			if ( $opt[ "open-in" ] !== false ) {
				$tgt = "target=\"".$opt[ "open-in" ]."\"" ;
			} else {
				$tgt = "" ;
			}

			if ( $opt[ "name-preprocess" ] !== false ) {
				$npf = $opt[ "name-preprocess" ];
				$v = $npf( $v );
			}

			return "<a href=\"".$url."\" ".$tgt." title=\"".$r[ "description" ]."\"".$opt[ "_fnc" ].">".$del.$imp.$icon."<span>".$v."</span></a>" ;
		};

		$Rights = getRights( $PlaceID );
		if ( isset( $Rights[ "GROUP" ] ) ) {
			$groups = explode( "," , trim( strtolower( $Rights[ "GROUP" ][ 0 ] ) ) );
		} else {
			$groups = array();
		}

		if ( $opt[ "db-connection" ] === false ) {
			$localDB = $portalDB ;
		} else {
			$localDB = $opt[ "db-connection" ];
		}

		$idIndex = 0 ;
		$totalRes = "" ;
		foreach( $id as $cnid ) {
			if ( $cnid === "recycled" ) {
				$cnid = -1 ;
				$cndtn = "dir" ;
				$cnftn = "files" ;
			} else
			if ( $cnid === "list" ) {
				$cnid = 0 ;
				$cndtn = "list-dir" ;
				$cnftn = "list-files" ;
			} else {
				$cndtn = "dir" ;
				$cnftn = "files" ;
			}

			$opt[ "_cndtn" ] = $cndtn ;
			$opt[ "_cnftn" ] = $cnftn ;

			$cl = $localDB->row( "select * from `".$cndtn."` where `id` = ?" , "i" , $cnid );
			if ( $cl === false ) {
				continue ;
			}

			if ( $overrideViewStyle === false ) {
				if ( is_null( $cl[ "view-style" ] ) ) {
					$cvs = $defViewStyle ;
				} else {
					$cvs = $cl[ "view-style" ];
				}
			} else {
				$cvs = $overrideViewStyle ;
			}

			$colDef = array();
			if ( $opt[ "may-select" ] ) {
				$colDef[]= '{ "id" : "id" , "n" : "id" , "t" : "c" , "h" : [ { "d" : "" } ] }' ;
			}
			switch ( $cvs[ "style" ] ) {
				case "table" :
					$colDef[]= '{ "n" : "name" , "t" : "sl" , "h" : [ { "d" : "Название" } ] , "f" : "name" }' ;
					$colDef[]= '{ "n" : "size" , "t" : "fs" , "h" : [ { "d" : "Размер"   } ] }' ;
					$colDef[]= '{ "n" : "type" , "t" : "ss" , "h" : [ { "d" : "Тип"      } ] , "f" : "type" , "s" : "def-file-store-file-type" }' ;
					$colDef[]= '{ "n" : "date" , "t" : "d"  , "h" : [ { "d" : "Дата"     } ] }' ;
					break ;

				case "list" ;
					$colDef[]= '{ "n" : "name" , "t" : "sm" , "h" : [ { "d" : "Название" } ] , "f" : "name" }' ;
					break ;
			}
			$colDef = "[ ".implode( " , " , $colDef )." ]" ;

			if ( in_array( strtolower( $cl[ "group" ] ) , $groups ) ) {
				$access_mask = $cl[ "group_access" ] ;
			} else {
				$access_mask = $cl[ "others_access" ] ;
			}

			if ( preg_match( "/l/" , $access_mask ) != 1 ) {
				$totalRes.= "НЕТ ДОСТУПА" ;
				continue ;
			}

			if ( $opt[ "show-deleted" ] ) {
				$showDeleted = "" ;
			} else {
				$showDeleted = " and ( not ( `deleted` <=> 1 ) )" ;
			}

			if ( $cvs[ "order" ] !== false ) {
				if ( $opt[ "show-dirs" ] ) {
					$clContentD = $localDB->query( "select * from `".$cndtn."` where ( `ext_id` = ? )".$showDeleted." order by `".$cvs[ "order" ]."` ".$cvs[ "param" ] , false , "s" , $cnid );
				} else {
					$clContentD = array();
				}
				if ( $opt[ "show-files" ] ) {
					$clContentF = $localDB->query( "select * from `".$cnftn."` where ( `ext_id` = ? )".$showDeleted." order by `".$cvs[ "order" ]."` ".$cvs[ "param" ] , false , "s" , $cnid );
				} else {
					$clContentF = array();
				}
			} else {
			if ( $opt[ "show-dirs" ] ) {
					$clContentD = $localDB->query( "select * from `".$cndtn."` where ( `ext_id` = ? )".$showDeleted , "id" , "s" , $cnid );
				} else {
					$clContentD = array();
				}
				if ( $opt[ "show-files" ] ) {
					$clContentF = $localDB->query( "select * from `".$cnftn."` where ( `ext_id` = ? )".$showDeleted , "id" , "s" , $cnid );
				} else {
					$clContentF = array();
				}
			}

			foreach( $clContentD as &$clc ) {
				$clc[ "size" ] = 0 ;
				$clc[ "type" ] = false ;
				$clc[ "obj-type" ] = 0 ;
			} unset( $clc );

			foreach( $clContentF as &$clc ) {
				$clc[ "obj-type" ] = 1 ;
			} unset( $clc );

			if ( $cvs[ "order" ] !== false ) {
				$clContent = array_merge( $clContentD , $clContentF );
			} else {
				$clContent = array();
				foreach ( explode( "," , $cvs[ "param" ] ) as $param ) {
					switch( $param[ 0 ] ) {
						case "d" :
							$tmp = &$clContentD ;
							break ;
						case "f" :
							$tmp = &$clContentF ;
							break ;
					}
					$param = substr( $param , 1 );
					if ( isset( $tmp[ $param ] ) ) {
						$clContent[]= &$tmp[ $param ];
						unset( $tmp[ $param ] );
					}
				}

				foreach ( $clContentD as &$tmp ) {
					$clContent[]= &$tmp ;
				} unset( $tmp );
				foreach ( $clContentF as &$tmp ) {
					$clContent[]= &$tmp ;
				} unset( $tmp );
			}

			$tabDef = array( "no-table-close-tag" => 1 );
			if ( $idIndex == 0 ) {
				if ( $opt[ "header" ] ) {
					$h = '[ { "t" : 2 } ]' ;
				} else {
					$h = '[]' ;
				}
			} else
			if ( $idIndex > 0 ) {
				$tabDef[ "no-table-open-tag" ] = 1 ;
				switch ( $opt[ "multilist-sep" ] ) {
					case "none" :
						$h = '[]' ;
						break ;
					case "header" :
						$h = '[ { "t" : 2 } ]' ;
						break ;
					case "user-defined" :
						$h = '[]' ;
						$totalRes.= $opt[ "multilist-sep-data" ];
						break ;
				}
			}

			$tmp = array();
			$totalRes.= makeSimpleTable( $tabDef , $h , $colDef , $tmp , $overrideTableStyle , $flt );
			if ( $opt[ "show-path-at-top" ] ) {
				$totalRes.= "<td colspan=\"".count( $colDef )."\" class=\"".$opt[ "path-at-top-style" ]."\">".$buildPath( $localDB , $cl , $opt[ "path-at-top-simple" ] )."</td>" ;
			}
			$tabDef[ "no-table-open-tag" ] = 1 ;
			$totalRes.= makeSimpleTable( $tabDef , "[]" , $colDef , $clContent , $overrideTableStyle , $flt );

			$idIndex++ ;
		}

		$tabDef[ "no-table-open-tag" ] = 1 ;
		$tabDef[ "no-table-close-tag" ] = 0 ;
		$tmp = array();
		$h = '[]' ;
		$totalRes.= makeSimpleTable( $tabDef , $h , $colDef , $tmp , $overrideTableStyle , $flt );
		return $totalRes ;
	}
?>