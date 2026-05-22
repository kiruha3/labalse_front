<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "../core.php" );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserID
	 * @var TDB $portalDB
	 * @var $UserThemeLoc
	 * @var $TAB_CASECATEGORY
	 */

	require_once( "../barcode.php" );
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */

	require_once( '../cores/core.maindb.php' );
	require_once( "../documents.core.php" );
	require_once '../agents.core.php' ;


	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		$Rights2 = ParseRightsToFlat( $PlaceID );
		if ( isset( $Rights[ "LVL1CARD" ] ) ) {
			$lvl1cardADD = in_array( "ADD" , $Rights[ "LVL1CARD" ] );
			$lvl1cardEDIT = in_array( "EDIT" , $Rights[ "LVL1CARD" ] );

			$optLVL1CARD_ADD_REP = isset( $_REQUEST[ "o_ADD_REP" ] );
			if ( $optLVL1CARD_ADD_REP ) {
				$REP = json_decode( base64_decode( $_REQUEST[ "o_ADD_REP" ] ) , true );
				$optLVL1CARD_ADD_SAMEDATE = isset( $REP[ "o_ADD_SAMEDATE" ] );
				$optLVL1CARD_ADD_SAMEALL = isset( $REP[ "o_ADD_SAMEALL" ] );
				if ( $optLVL1CARD_ADD_SAMEALL ) {
					foreach ( $REP[ "o_ADD_SAMEALL" ] as &$tmpstr ) {
						if ( is_string( $tmpstr ) ) {
							$tmpstr = iconv(  "utf8" , "cp1251" , $tmpstr );
						}
					} unset( $tmpstr );
				}
			} else {
				$optLVL1CARD_ADD_SAMEDATE = $optLVL1CARD_ADD_SAMEALL = false ;
			}
			$optLVL1CARD_EDIT_NEXT = isset( $_REQUEST[ "o_EDIT_NEXT" ] );
		} else {
			$lvl1cardADD = $lvl1cardEDIT = false ;
		}

		$lvl1cardVIEW = $Rights2[ "lvl1card.view" ] = checkAccess( $Rights2 , "{LVL{1,2}CARD.{ADD,EDIT},EXPERTIZE.EDIT}" , RIGHTS_OR );

		$GoOut = isset( $_REQUEST[ "add" ] ) ? !$lvl1cardADD : ( isset( $_REQUEST[ "edit" ] ) ? !$lvl1cardEDIT : ( isset( $_REQUEST[ "view" ] ) ? !$lvl1cardVIEW : true ) );

		//$GoOut = $GoOut && !$lvl1cardVIEW ;
	} else {
		$Rights2 = array();
		$lvl1cardADD = $lvl1cardEDIT = false ;
		$GoOut = true ;
	}

	/*$lvl1cardADD = checkAccess( $Rights2 , "LVL1CARD.ADD" );
	$lvl1cardEDIT = checkAccess( $Rights2 , "LVL1CARD.EDIT" );
	$lvl1cardVIEW = $Rights2[ "lvl1card.view" ] = checkAccess( $Rights2 , "{LVL{1,2}CARD.{ADD,EDIT},EXPERTIZE.EDIT}" , RIGHTS_OR );*/

	$modeVIEW = false ;
	if ( isset( $_REQUEST[ "edit" ] ) && $lvl1cardEDIT ) {
		$v_id = getCharID( $_REQUEST[ "edit" ] , DOCTYPE_MATINCOMING );
		if ( $v_id === false ) {
			$GoOut = true ;
		}
	} else
	if ( isset( $_REQUEST[ "view" ] ) && $lvl1cardVIEW ) {
		$v_id = getCharID( $_REQUEST[ "view" ] , DOCTYPE_MATINCOMING );
		if ( $v_id === false ) {
			$GoOut = true ;
		}
		$modeVIEW = true ;
	}

	if ( $GoOut ) {
		MainHead_L2( "UID=".$UserID , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}

	if ( isset( $_REQUEST[ "edit" ] ) ) {
		//$row = RowAsObject( $con , "select * from `matincoming` where `id`=".Str2SQL( $_REQUEST[ "edit" ] ) );
		$row = $portalDB->row( "select * from `matincoming` where `id` = ?" , "s" , $v_id );
		if ( $row !== false ) {
			extract( array_rekey( $row , '/(.+)/' , 'v_${1}' , "{id,exp_type,ex_data_{3,4,6,7,8,9},state}" ) , EXTR_OVERWRITE );
			$v_date = date( "d-m-Y" , strtotime( $row[ "date" ] ) );

			$v_from_agency_id = is_null( $row[ "from_agency" ] ) || ( $row[ "from_agency" ] == 0 ) ? -1 : $row[ "from_agency" ];
			//$row2 = RowAsArray( $con , "select * from `agency` where `id`=".$v_from_agency_id." order by `_fr` desc limit 1;" );
			$row2 = $portalDB->row( "select * from `agency` where `id` = ? order by `_fr` desc limit 1;" , "i" , $v_from_agency_id );
			if ( $row2 !== false ) {
				$v_from_type_of_agency = $row2[ "ext_id" ];
				$v_from_agency_name = $row2[ "name" ];
			} else {
				$v_from_type_of_agency = 1 ;
				$v_from_agency_name = "" ;
			}

			$v_from_agent_id = is_null( $row[ "from_agent" ] ) || ( $row[ "from_agent" ] == 0 ) ? -1 : $row[ "from_agent" ];
			//$row2 = RowAsArray( $con , "select * from `agent` where `id`=".$v_from_agent_id." order by `_fr` desc limit 1;" );
			$row2 = $portalDB->row( "select * from `agent` where `id` = ? order by `_fr` desc limit 1;" , "i" , $v_from_agent_id );
			if ( $row2 !== false ) {
				$v_from_agent_name = $row2[ "name" ];
			} else {
				$v_from_agent_name = "" ;
			}

			$v_linked = ( !is_null( $row[ "group_id" ] ) ? $row[ "group_id" ] > 0 : false );

			$state = 2 ;

			$selectMarks = Marks\getMarks( $v_id , "matincoming" );

			$func = "processor.php?lvl1cedit=".$v_id ;
			$useForm = true ;
			$btnName = "Заменить" ;
		} else {
		}
	} else
	if ( isset( $_REQUEST[ "view" ] ) ) {
		$row = $portalDB->row( "select * from `matincoming` where `id` = ?" , "s" , $v_id );
		if ( $row !== false ) {
			extract( array_rekey( $row , '/(.+)/' , 'v_${1}' , "{id,exp_type,ex_data_{3,4,6,7,8,9},state}" ) , EXTR_OVERWRITE );
			$v_date = date( "d-m-Y" , strtotime( $row[ "date" ] ) );

			$v_from_agency_id = is_null( $row[ "from_agency" ] ) || ( $row[ "from_agency" ] == 0 ) ? -1 : $row[ "from_agency" ];
			$row2 = $portalDB->row( "select * from `agency` where `id` = ? order by `_fr` desc limit 1;" , "i" , $v_from_agency_id );
			if ( $row2 !== false ) {
				$v_from_type_of_agency = $row2[ "ext_id" ];
				$v_from_agency_name = $row2[ "name" ];
			} else {
				$v_from_type_of_agency = 1 ;
				$v_from_agency_name = "" ;
			}

			$v_from_agent_id = is_null( $row[ "from_agent" ] ) || ( $row[ "from_agent" ] == 0 ) ? -1 : $row[ "from_agent" ];
			$row2 = $portalDB->row( "select * from `agent` where `id` = ? order by `_fr` desc limit 1;" , "i" , $v_from_agent_id );
			if ( $row2 !== false ) {
				$v_from_agent_name = $row2[ "name" ];
			} else {
				$v_from_agent_name = "" ;
			}

			$v_linked = ( !is_null( $row[ "group_id" ] ) ? $row[ "group_id" ] > 0 : false );

			$state = 2 ;

			$selectMarks = Marks\getMarks( $v_id , "matincoming" );
		} else {
		}

		$state = 0 ;
		$func = "main.php" ;
		$useForm = false ;
	} else
	if ( isset( $_REQUEST[ "add" ] ) ) {
		$v_assign = false ;
		$row = $portalDB->row( "select `id` from `matincoming` where ( `date` is null );" );
		if ( $optLVL1CARD_ADD_SAMEALL ) {
			$v_id = false ;
			$vv = array_rekey( $REP[ "o_ADD_SAMEALL" ] , '/^i_/' , 'v_' );
			$vv = array_rekey( $vv , '/(from_agen(?:cy|t))/' , '$1_name' );
			$vv = array_rekey( $vv , '/(agen(?:cy|t))\.id/' , 'v_from_$1_id' );
			$vv = array_intersect_key( $vv , array_flip( strexp(
				"v_{date,case_category,from_{type_of_agency,agen{cy,t}_{name,id}},ex_data_{3,4,6,7,8,9},state,marks}"
			) ) );
			extract( $vv , EXTR_OVERWRITE );
			/** @var $v_case_category */
			/** @var $v_date */
			/** @var $v_from_type_of_Agency */
			/** @var $v_ex_data_3 */
			/** @var $v_ex_data_4 */
			/** @var $v_ex_data_7 */
			/** @var $v_ex_data_8 */
			/** @var $v_ex_data_9 */
			$v_exp_type = $v_case_category ;
			$v_date = date( "d-m-Y" , $v_date );
			if ( isset( $v_marks ) ) {
				$selectMarks = $v_marks ;
			}
		} else
		if ( isset( $_REQUEST[ "assign" ] ) ) {

			$v_id = false ;
			$v_assign = getCharID( $_REQUEST[ "assign" ] , DOCTYPE_MATINCOMING );
			if ( $v_assign === false ) {

			}

			$acd = $portalDB->row( "select `t1`.* , `t2`.`name` as `agency_name` , `t3`.`name` as `agent_name` , `t2`.`ext_id` as `type_of_agency` from `matincoming` as `t1` , `agency` as `t2` , `agent` as `t3` where ( `t2`.`id` = `t1`.`from_agency` ) and ( `t3`.`id` = `t1`.`from_agent` ) and ( `t1`.`id` = ? )" , "s" , $v_assign );
			$selectMarks = Marks\getMarks( $v_assign , "matincoming" );

			$v_from_type_of_agency = $acd[ "type_of_agency" ];

			extract( array_rekey( $acd , '/(.+)/' , 'v_${1}' , "{exp_type,ex_data_{3,4,7,8,9}}" ) , EXTR_OVERWRITE );

			$v_date = date( "d-m-Y" , time() );
			$v_from_agency_id = $acd[ "from_agency" ];
			$v_from_agency_name = $acd[ "agency_name" ];
			$v_from_agent_id = $acd[ "from_agent" ];
			$v_from_agent_name = $acd[ "agent_name" ];
			$v_ex_data_6 = $v_date.", " ;
			$v_state = 0 ;
		} else {
			$v_id = false ;

			if ( isset( $UserOptions[ "maindb.lvl1.card.default.typeOfAgency" ] ) ) {
				$v_from_type_of_agency = $UserOptions[ "maindb.lvl1.card.default.typeOfAgency" ][ "op_value" ];
			} else {
				$tmp = $portalDB->row( "select min( `id` ) as `mid` from `type-of-agency`" );
				$v_from_type_of_agency = $tmp[ "mid" ];
			}

			extract( array_fill_keys( strexp( "v_{exp_type,from_agen{cy,t}_name,ex_data_{3,4,7,8,9}}" ) , "" ) , EXTR_OVERWRITE );

			$v_date = $optLVL1CARD_ADD_SAMEDATE ? date( "d-m-Y" , $_REQUEST[ "o_ADD_SAMEDATE" ] ) : date( "d-m-Y" , time() );
			$v_from_agency_id = -1 ;
			$v_from_agent_id = -1 ;
			$v_ex_data_6 = $v_date.", " ;
			$v_state = 0 ;
			$selectMarks = array();
		}

		$state = 1 ;
		if ( $v_assign !== false ) {
			$func = "processor.php?lvl1cadd&assign=".$v_assign ;
			$btnName = "Добавить и связать" ;
		} else {
			$func = "processor.php?lvl1cadd" ;
			$btnName = "Добавить" ;
		}
		$useForm = true ;

	} else {
		$state = 0 ;
		$func = "main.php" ;
		$useForm = false ;
		Redirect( $func );
	}


  /*  Тип органа: суд, прокуротура и т.д.  */

	if ( $modeVIEW ) {
		$tabTypeOfAgency = $portalDB->simpleRow( "type-of-agency" , $v_from_type_of_agency );
		$from_type_of_agency = "<div id=\"i_from_type_of_agency\">".inForm( $tabTypeOfAgency[ "name" ] , 1 , false )."</div>" ;
	} else {
		$tabTypeOfAgency = $portalDB->table( "type-of-agency" );
		$from_type_of_agency = "<select id=\"i_from_type_of_agency\" name=\"i_from_type_of_agency\" size=\"1\" class=\"i_from_type_of_agency\">" ;
		foreach( $tabTypeOfAgency as $i ) {
			$from_type_of_agency.= "<option value=\"".$i[ "id" ]."\"".( $i[ "id" ] == $v_from_type_of_agency ? " selected" : "" ).">".inForm( $i[ "name" ] , 1 , false )."</option>" ;
		}
		$from_type_of_agency.= "</select>" ;
	}

	/* ------------------------------------- */

	$tabAgency = $portalDB->query( "select * from `agency` where `ext_id` = ?" , false , "i" , $v_from_type_of_agency );
	$from_agency = "" ;
	/*foreach( $tabAgency as $i ) {
		$from_agency.= "<option value=\"".$i[ "id" ]."\"".( $i[ "id" ] == $v_from_agency_id ? " selected" : "").">".$i[ "name" ]."</option>" ;
	}*/

	$tabAgent = $portalDB->query( "select * from `agent` where `ext_id` = ?" , false , "i" , $v_from_agency_id );
	$from_agent = "" ;
	/*foreach( $tabAgent as $i ) {
		$from_agent.= "<option value=\"".$i["id"]."\"".( $i["id"] == $v_from_agent_id ? " selected" : "").">".$i[ "name" ]."</option>" ;
	}*/

	if ( $modeVIEW ) {
		if ( isset( $v_exp_type ) && isset( $TAB_CASECATEGORY[ $v_exp_type ] ) ) {
			$currentCaseCategory = $TAB_CASECATEGORY[ $v_exp_type ];
			$CaseCat = "<div id=\"i_case_category\">".inForm( $currentCaseCategory[ "name" ] , 1 , false )."</div>" ;
		}
	} else {
		$CaseCat = "<select id=\"i_case_category\" name=\"i_case_category\" size=\"1\"><option value=\"\"></option>" ;
		if ( isset( $v_exp_type ) && isset( $TAB_CASECATEGORY[ $v_exp_type ] ) ) {
			$currentCaseCategory = $TAB_CASECATEGORY[ $v_exp_type ];
			$ccGroup = $currentCaseCategory[ 'group' ];
		} else {
			$ccGroup = false ;
		}
		foreach( $TAB_CASECATEGORY as $i ) {
			if ( $ccGroup !== false ) {
				if ( $ccGroup != $i[ 'group' ] ) {
					continue ;
				}
			} else {
				if ( $i[ 'actual' ] != 1 ) {
					continue ;
				}
			}
			$CaseCat.= "<option value=\"".$i[ "id" ]."\"".( $i[ "id" ] == $v_exp_type ? " selected" : "" ).">".$i[ "index" ]." ( ". inForm( $i[ "name" ] , 1 , false )." )</option>" ;
		}
		$CaseCat.= "</select>" ;
	}

	$expStates = array(
		-2 => "Ошибочно зарегистрировано" ,
		-1 => "Ожидает выполнения другой экспертизы" ,
		 0 => "В производстве" ,
		 1 => "Готово к выдаче" ,
		 2 => "Выдано"
	);

	if ( $modeVIEW ) {
		$expStateSel = "<div>".$expStates[ $v_state ]."</div>" ;
	} else {
		$expStateSel = "<select name=\"i_state\" class=\"i_state\">" ;
		foreach( $expStates as $esi => $est ) {
			$expStateSel.= "<option value=\"".$esi."\"".( $v_state == $esi ? " selected" : "" ).">".$est."</option>" ;
		}
		$expStateSel.= "</select>" ;
	}

	if ( $v_id !== false ) {
		$evidence = $portalDB->query( "select * from `evidence` where ( `ext_id` = ? ) order by `inc_date` asc" , "id" , "s" , $v_id );
	} else {
		$evidence = array();
	}

	if ( $modeVIEW ) {
		$agents = Agents\integrate( array( "target-elements" => strexp( "{org-address1{,-alt-editor,-label},contacts{,-label}}" ) , "name-prefix" => "i_" ) );
	} else {
		$agents = Agents\integrate( array( "target-elements" => strexp( "{org-address1{,-alt-editor,-label},contacts{,-controls,-label{,-agent-name}}}" ) , "name-prefix" => "i_" ) );
	}
	unset( $agents[ "html" ][ "type" ] );
	unset( $agents[ "html" ][ "agency" ] );
	unset( $agents[ "html" ][ "agent" ] );
	unset( $agents[ "js" ][ "link" ][ "type" ] );
	$o = $agents[ "js" ];
	$o[ "selected" ] = $agents[ "selected" ];


	MainHead_L2(
		"База" ,
		"<a href=\"main.php\">База</a> - карточка 1" ,
		array( "../%UT/buttons.css", "%UT/level1card.css" ),
		array( "# $.agentDLG_Data = ".json_encode( $o )."" , '#
			$.userThemeLoc = "'.$UserThemeLoc.'" ;
			$.tmpl = '.( isset( $UserOptions[ "maindb.lvl1.card.tmpl" ] ) && isValidJSON( $UserOptions[ "maindb.lvl1.card.tmpl" ][ "op_value" ] ) ? $UserOptions[ "maindb.lvl1.card.tmpl" ][ "op_value" ] : "[]" ).' ;
			$.tmplVar = [ { k : "cd" , v : "'.date( "d-m-Y" ).'" , d : "Текущая дата" } , { k : "cdT" , v : "'.date( "d-m-Y" , time() + 86400 ).'" , d : "Завтрашняя дата" } , { k : "cdY" , v : "'.date( "d-m-Y" , time() - 86400 ).'" , d : "Вчерашняя дата" } , { k : "ay" , v : "'.str_replace( "\"" , "\\\"" , str_replace( "\\" , "\\\\" , $v_from_agency_name ) ).'" , d : "Назначивший орган" } , { k : "at" , v : "'.str_replace( "\"" , "\\\"" , str_replace( "\\" , "\\\\" , $v_from_agent_name ) ).'" , d : "Назначившее лицо" } ];
			$.tmplTargets = strexp( "i_{date,ex_data_{3,4,6,7,8,9},from_agen{cy,t}}" );
			$.typeOfAgency =  '.$v_from_type_of_agency.' ;
			$.agencyId = '.$v_from_agency_id.' ;
			$.agentId = '.$v_from_agent_id.' ;
			$.userId = '.$UserID.' ;
		' , 'files/level1card.3.js' ,
		'/ext-lib/pdf.js/build/pdf.js' ,
		'/ext-lib/pdf.js/build/pdf.worker.js' ),
		'hlp/level1card.html'
	);


	if ( $UserID == 145 ) {
		print_r_html( $Rights2 , 1 );
	}

	//print_r_html( $UserOptions );
	//$vvv = isValidJSON( $UserOptions[ "maindb.lvl1.card.tmpl" ][ "op_value" ] );
	//$vvv = json_decode( iconv( "cp1251" , "utf8" , $UserOptions[ "maindb.lvl1.card.tmpl" ][ "op_value" ] ) );
	//echo json_last_error();
	//print_r_html( $vvv );

	if ( $v_id !== false ) {
		$matNumber = matincomingNumberFullParts( $v_id , null , $v_exp_type );
		$matNumber[ 'casecategory' ] = $CaseCat ;
		$matNumber = implode( ' ' , $matNumber );
	} else {
		$matNumber = $CaseCat ;
	}

	echo ( $useForm ? "<form id=\"lvl1CardForm\" action=\"".$func."\" method=\"post\">" : "" )."
		<table align=\"center\" class=\"PT\">

			<!-- Порядковый номер экспертизы, категория дела -->
			<tr>
				<td class=\"D\">
					Порядковый номер экспертизы, категория дела
				</td>
				<td class=\"I\">
					".$matNumber." ".( isset( $_REQUEST[ "edit" ] ) && isset( $v_linked ) && $v_linked === true ? "<a onclick=\"doUnlink( '".$v_id."' );\" class=\"unlink-lnk\">Развязать</a>" : ( isset( $_REQUEST[ "edit" ] ) ? "<a onclick=\"mkNewLink( '".$v_id."' );\" class=\"new-link-lnk\">Связать с другой</a>" : "" ) )."
				</td>
			</tr>

      <!-- Дата поступления материалов -->
			<tr>
				<td class=\"D\">
					Дата поступления материалов
				</td>
				<td class=\"I\">
					".( $modeVIEW ? $v_date : "<input name=\"i_date\" id=\"i_date\" type=\"text\" value=\"".$v_date."\" class=\"i_date\">" )."
				</td>
			</tr>

      <!-- От кого поступили материалы -->
			<tr>
				<td class=\"D\" colspan=\"2\">
					От кого поступили материалы
				</td>
			</tr>
			<tr>
				<td class=\"D\">
					тип органа
				</td>
				<td class=\"I\">
					".$from_type_of_agency."
				</td>
			</tr>
			<tr>
				<td class=\"D\">
					название органа
				</td>
				<td class=\"I\">
					".( $modeVIEW ? "<div id=\"i_from_agency\">".$v_from_agency_name."</div>" : "<textarea id=\"i_from_agency\" name=\"i_from_agency\" class=\"i_from_agency\">".$v_from_agency_name."</textarea>
					<img id=\"tcimg1\" src=\"themes/".$UserThemeLoc."/col.bmp\" border=\"0\" onclick=\"tc( 1 )\">
					<br>
					<div id=\"tcel1\">
						<select id=\"i_from_agency_list\" name=\"i_from_agency_list\" class=\"i_from_agency_list\" size=\"20\">".$from_agency."</select>
					</div>" )."
				</td>
			</tr>
			<tr>
				<td class=\"D\">
					Назначивший
				</td>
				<td class=\"I\">
					".( $modeVIEW ? "<div id=\"i_from_agent\">".$v_from_agent_name."</div>" : "<input id=\"i_from_agent\" name=\"i_from_agent\" type=\"text\" class=\"i_from_agent\" value=\"".$v_from_agent_name."\" onkeyup=\"srch2()\">
					<img id=\"tcimg2\" src=\"themes/".$UserThemeLoc."/col.bmp\" border=\"0\" onclick=\"tc( 2 )\">
					<br>
					<div id=\"tcel2\">
						<select id=\"i_from_agent_list\" name=\"i_from_agent_list\" class=\"i_from_agent_list\" size=\"20\">".$from_agent."</select>
					</div>" )."
				</td>
			</tr>

			<tr>
				<td class=\"D\">
					Постановление, др.
				</td>
				<td class=\"I\">
					".( $modeVIEW ? $v_ex_data_3 : "<textarea name=\"i_ex_data_3\" id=\"i_ex_data_3\" class=\"i_ex_data_3\" onkeypress=\"return ex_data_3_fill_2(event);\">".$v_ex_data_3."</textarea>" )."
				</td>
			</tr>

			<!-- Номер дела; количество томов, страниц, приложений; Ф.И.О. лиц, привлекаемых к ответственности, сторон по делу -->
			<tr>
				<td class=\"D\">
					Номер дела;
					количество томов, страниц, приложений;
					Ф.И.О. лиц, привлекаемых к ответственности, сторон по делу
				</td>
				<td class=\"I\">
					".( $modeVIEW ? $v_ex_data_4 : "<textarea name=\"i_ex_data_4\" id=\"i_ex_data_4\" class=\"i_ex_data_4\">".$v_ex_data_4."</textarea>" )."
				</td>
			</tr>
			<tr>
				<td colspan=\"2\"><div class=\"et-label\">Вещественные доказательства и(или) объекты исследования</div>" ;

					$flt = makeSimpleTable_init_filter();
					$flt[ "btns" ] = function ( &$r , $c , $v ) {
						return "<a class=\"et-btn-cover\" href=\"evidence-dmtx.php?id=".$v."\" target=\"_blank\" title=\"Карточка вещ.дока\"><span>N</span></a>".
							"<a class=\"et-btn-cover\" href=\"evidence-side-2.php?id=".$v."\" target=\"_blank\" title=\"Карточка вещ.дока выдача\"><span>S</span></a>" ;
					};
					$flt[ "states" ] = function ( &$r , $c , $v ) use( $modeVIEW ) {
						if ( is_null( $r[ "state" ] ) ) {
							$r[ "state" ] = 0 ;
						}
						switch( $r[ "state" ] ) {
							case -2 :
								$sb = "e" ;
								break ;
							case -1 :
								$sb = "w0" ;
								break ;
							case 0 :
								$sb = "w1" ;
								break ;
							case 1 :
								$sb = "r" ;
								break ;
							case 2 :
								$sb = "f" ;
								break ;
						}
						if ( $modeVIEW ) {
							return "<a id=\"et-state-icon-".$v."\" class=\"et-state-".$sb."\"></a>" ;
						} else {
							return '<a id="et-state-icon-'.$v.'" class="et-state-'.$sb.'" onclick="doChangeState( '.$v.' , '.$r[ 'state' ].' )"></a>' ;
						}
					};
					$flt[ "out" ] = function ( &$r , $c , $v , &$ro ) {
						$ro[ "skip" ] = $r[ "state" ] != 2 ;
						switch( $r[ "state" ] ) {
							case 2 :
								return "<span class=\"et-state-date\">[ ".date( "d-m-Y" , $r[ "state_date" ] )." ]</span>".$r[ "out_comment" ];
								break ;

							default :
								return "" ;
								break ;
						}
					};
					echo makeSimpleTable(
						'{ "id" : "evtab" , "drid" : 1 , "drid-pref" : "evtab-" }' ,
						'[ { "t" : 1 } ]' ,
						'[ { "n" : "id"       , "t" : "s48" , "h" : [ { "d" : "" } ] , "f" : "btns" , "s" : "et-states" } ,'
						.' { "n" : "id"       , "t" : "s48" , "h" : [ { "d" : "" } ] , "f" : "states" , "s" : "et-states" } ,'
						.' { "n" : "inc_date" , "t" : "d" , "h" : [ { "d" : "Дата поступления" } ] , "id" : "date" , "e" : "r" } ,'
						.' { "n" : "descr"    , "t" : "Sf" , "h" : [ { "d" : "Описание" } ] , "id" : "descr" , "e" : "r" } ,'
						.' { "n" : "id"       , "t" : "Sl" , "h" : [ { "d" : "" , "s" : "eth-hidden" } ] , "f" : "out" , "id" : "descr" , "e" : "r" }'
						.']' ,
						$evidence , array( "dr" => "dr-d" ) , $flt
					);

					echo "<div class=\"et-panel\">".( $modeVIEW ? "" : "<a id=\"evtab-add-btn\" class=\"btn1\" onclick=\"doAddRow()\">Добавить</a></div>" );
				echo "</td>
			</tr>
			<tr>
				<td class=\"D\">
					Ф.И.О. и подпись работника подразделения, получившего материалы, дата получения
				</td>
				<td class=\"I\">
					".( $modeVIEW ? $v_ex_data_6 : "<input id=\"i_ex_data_6_hidden\" type=\"hidden\" value=\"".$v_ex_data_6."\">
					<textarea id=\"i_ex_data_6\" name=\"i_ex_data_6\" class=\"i_ex_data_6\">".$v_ex_data_6."</textarea>" )."
				</td>
			</tr>
			<tr>
				<td class=\"D\">
					Сведения о приостановлении срока производства экспертизы
					(причина, даты приостановления и возобновления производства, результат рассмотрения или ходатайства)
				</td>
				<td class=\"I\">
					".( $modeVIEW ? $v_ex_data_7 : "<textarea name=\"i_ex_data_7\" id=\"i_ex_data_7\" class=\"i_ex_data_7\">".$v_ex_data_7."</textarea>" )."
				</td>
			</tr>
			<tr>
				<td class=\"D\">
					Дата сдачи заключения, акта, сообщения, письма о возврате без исполнения и материалов для отправки
				</td>
				<td class=\"I\">
					".( $modeVIEW ? $v_ex_data_8 : "<textarea name=\"i_ex_data_8\" id=\"i_ex_data_8\" class=\"i_ex_data_8\">".$v_ex_data_8."</textarea>" )."
				</td>
			</tr>
			<tr>
				<td class=\"D\">
					Дата и способ отправки заключения, акта, сообщения, письма о возврате без исполнения и материалов
				</td>
				<td class=\"I\">
					".( $modeVIEW ? $v_ex_data_9 : "<textarea name=\"i_ex_data_9\" id=\"i_ex_data_9\" class=\"i_ex_data_9\">".$v_ex_data_9."</textarea>" )."
				</td>
			</tr>
			<tr>
				<td class=\"D\">
					Состояние экспертизы
				</td>
				<td class=\"I\">
					".$expStateSel."
				</td>
			</tr>
			<tr>
				<td colspan=\"2\" class=\"optTB\">" ;
					switch ( $state ) {
						case 0 :
							echo "" ;
							break ;
						case 1 :
							echo "<br>
							<input name=\"o_ADD_REP\" type=\"checkbox\"".( $optLVL1CARD_ADD_REP ? " checked" : "" ).">вернуться для добавления новой карточки<br>
							<input name=\"o_ADD_SAMEDATE\" type=\"checkbox\"".( $optLVL1CARD_ADD_SAMEDATE ? " checked" : "" ).">использовать ту же дату поступления материалов, что и в текущей карточке<br>
							<input name=\"o_ADD_SAMEALL\" type=\"checkbox\"".( $optLVL1CARD_ADD_SAMEALL ? " checked" : "" ).">использовать те же данные, что и в текущей карточке<br>" ;
							break ;
						case 2 :
							echo "<br>
							<input name=\"o_EDIT_NEXT\" type=\"checkbox\"".( $optLVL1CARD_EDIT_NEXT ? " checked" : "" ).">вернуться для изменения следующей карточки<br>" ;
							break ;
					}
				echo "</td>
			</tr>
			<tr>
				<td colspan=\"2\" class=\"btnTB\">
					".( $useForm ? "<input type=\"button\" value=\"".$btnName."\" class=\"btn\" onclick=\"lvl1CardSubmit()\">" : "<a href=\"".$func."\" class=\"btn3\">К базе</a>" )."
				</td>
			</tr>

		</table>
		<div id=\"marks-panel\">
			".Marks\integrate( array( $dbConfig[ CFG_MARK_GROUP_MATINCOMING ] ) , array( "mark-name-attr" => "i_marks" , "mode" => ( $modeVIEW ? "show" : "edit" ) ) , $selectMarks )."
		</div>
		<div id=\"contacts-panel\">
			".implode( $agents[ "html" ] )."
		</div>
	".( $useForm ? "</form>" : "" );

	if ( $v_id !== false ) {
		echo "<div id=\"docs-panel\">" ;
			$mIDg = getMatincomingIDGroup( $v_id );
			$docsIntegrateOpt = array(
				"docs" => "ref" ,
				"ex-style-calc" => function( $fi ) use ( $v_id ) {
					return $fi[ "ext_id" ] != $v_id ? " docs-lnk-alter-border" : "" ;
				} ,
				"show-icons" => true ,
			);

			if ( !$modeVIEW ) {
				$docsIntegrateOpt[ "upload" ] = array( "ext_id" => $v_id , "ext_type" => "docs" );
			}

			echo Documents\integrate(
				array( "ext_id" => $mIDg , "ext_type" => "docs" ) ,
				$docsIntegrateOpt
			);
		echo "</div>" ;
	}

	closeHtml();
