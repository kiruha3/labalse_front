<?php
	namespace Marks ;

	/**
	 * режим управлени€ отметками
	 */
	define( 'MARKS__MODE__EDIT' , 'edit' );

	/**
	 * режим просмотра отметок (в карточке)
	 */
	define( 'MARKS__MODE__SHOW' , 'show' );

	/**
	 * режим €рлыка в строке (лева€ колонка)
	 */
	define( 'MARKS__MODE__LABEL' , 'label' );

	/**
	 * режим €рлыка в строке (как документы)
	 */
	define( 'MARKS__MODE__SIMPLE_INLINE' , 'simple-inline' );

	function updateMarks( $oID , $oType , $newMarks ) {
		global $portalDB ;
		$oldMarks = array_column( $portalDB->simpleQuery( "marks-objects" , array( "ext_type" => $oType , "ext_id" => $oID ) ) , "mark_id" );
		$oldMarks = array_combine( $oldMarks , $oldMarks );
		$newMarks = array_combine( $newMarks , $newMarks );
		$mtd = array_diff_key( $oldMarks , $newMarks );
		$mta = array_diff_key( $newMarks , $oldMarks );
		if ( count( $mtd ) > 0 ) {
			//print_r_html( $mtd );
			$portalDB->noResult( "delete from `marks-objects` where ( `ext_id` = ? ) and ( `ext_type` = ? ) and ( `mark_id` in ( ?* ) )" , "ss*i" , $oID , $oType , $mtd );
		}

		$mtaV = array( "ext_id" => $oID , "ext_type" => $oType , "date" => time() );
		foreach( $mta as $i ) {
			$mtaV[ "mark_id" ] = $i ;
			$portalDB->insertRow( "marks-objects" , $mtaV );
		}
	}

	function getMarks( $oID , $oType , $idOnly = true ) {
		global $portalDB ;
		if ( $idOnly ) {
			return array_column( $portalDB->simpleQuery( "marks-objects" , array( "ext_type" => $oType , "ext_id" => $oID ) ) , "mark_id" );
		} else {
			return $portalDB->query( "select `t1`.* , `t2`.`date` , `t2`.`id` as `link_id` from `marks-catalog` as `t1` , `marks-objects` as `t2` where ( `t1`.`id` = `t2`.`mark_id` ) and ( `t2`.`ext_type` = ? ) and ( `t2`.`ext_id` = ? )" , false , "ss" , $oType , $oID );
		}
	}

	function getObjectsIDAny( $mID , $oType ) {
		global $portalDB ;
		if ( !is_array( $mID ) ) {
			$mID = array( $mID );
		}
		if ( count( $mID ) == 0 ) {
			return array();
		}

		foreach( $mID as &$cmID ) {
			$cmID = explode( ":" , $cmID );
			if ( count( $cmID ) == 1 ) {
				$cmID = $cmID[ 0 ];
			} else {
				$tmp = explode( "-" , $cmID[ 1 ] );
				$cmID[ 1 ] = array(
					min( intval( $tmp[ 0 ] ) , intval( $tmp[ 1 ] ) ) ,
					max( intval( $tmp[ 0 ] ) , intval( $tmp[ 1 ] ) )
				);
			}
		} unset( $cmID );


		$w = array();
		foreach( $mID as $id ) {
			if ( is_array( $id ) ) {
				$w[]= "( ( `t1`.`ext_type` = ".Str2SQL( $oType )." ) and ( `t1`.`mark_id` = ".Int2SQL( $id[ 0 ] )." ) and ( `t1`.`date` >= ".Int2SQL( $id[ 1 ][ 0 ] )." ) and ( `t1`.`date` <= ".Int2SQL( $id[ 1 ][ 1 ] )." ) )" ;
			} else {
				$w[]= "( ( `t1`.`ext_type` = ".Str2SQL( $oType )." ) and ( `t1`.`mark_id` = ".Int2SQL( $id )." ) )" ;
			}
		}

		return array_column( $portalDB->query( "select `t1`.`ext_id` from `marks-objects` as `t1` where ".implode( " or " , $w ) , false ) , "ext_id" );

		//return array_column( $portalDB->query( "select `t1`.`ext_id` from ".implode(  " , " , $f )." where ".implode( " and " , $w ) , false ) , "ext_id" );
		//return array_column( $portalDB->simpleQuery( "marks-objects" , array( "ext_type" => $oType , "mark_id" => $mID ) ) , "ext_id" );
	}

	function getObjectsIDAll( $mID , $oType ) {
		global $portalDB ;
		if ( !is_array( $mID ) ) {
			$mID = array( $mID );
		}

		if ( count( $mID ) == 0 ) {
			return array();
		}

		foreach( $mID as &$cmID ) {
			$cmID = explode( ":" , $cmID );
			if ( count( $cmID ) == 1 ) {
				$cmID = $cmID[ 0 ];
			} else {
				$tmp = explode( "-" , $cmID[ 1 ] );
				$cmID[ 1 ] = array(
					min( intval( $tmp[ 0 ] ) , intval( $tmp[ 1 ] ) ) ,
					max( intval( $tmp[ 0 ] ) , intval( $tmp[ 1 ] ) )
				);
			}
		} unset( $cmID );

		$f = array();
		$w = array();
		$i = 1 ;
		foreach( $mID as $id ) {
			$f[]= "`marks-objects` as `t".$i."`" ;
			if ( is_array( $id ) ) {
				$w[]= "( `t".$i."`.`ext_type` = ".Str2SQL( $oType )." ) and ( `t".$i."`.`mark_id` = ".Int2SQL( $id[ 0 ] )." ) and ( `t".$i."`.`date` >= ".Int2SQL( $id[ 1 ][ 0 ] )." ) and ( `t".$i."`.`date` <= ".Int2SQL( $id[ 1 ][ 1 ] )." )" ;
			} else {
				$w[]= "( `t".$i."`.`ext_type` = ".Str2SQL( $oType )." ) and ( `t".$i."`.`mark_id` = ".Int2SQL( $id )." )" ;
			}
			if ( $i > 1 ) {
				$w[]= "( `t".$i."`.`ext_id` = `t1`.`ext_id` )" ;
			}
			$i++ ;
		}

		return array_column( $portalDB->query( "select `t1`.`ext_id` from ".implode(  " , " , $f )." where ".implode( " and " , $w ) , false ) , "ext_id" );
	}

	const DEFAULT_OPTIONS = array(
		'mode' => MARKS__MODE__EDIT ,
		'id-mark' => false , // id €вл€етс€ marks-catalog.id вместо marks-groups.id
		'mark-id-attr-prefix' => 'marks-' ,
		'mark-name-attr' => 'marks' ,
		'mark-class-attr-prefix' => 'std-marks-' ,
		'group-id-attr-prefix' => 'marks-group-' ,
		'group-name-attr' => 'marks-group' ,
		'group-class' => 'std-marks-group' ,
		'offset-step' => 24 ,
		'offset-unit' => 'px' ,
		'checked-id-only' => true , // checked только id или объекты
		'checked-area' => true ,
		'checked-area-duplicate' => false ,
		'checked-area-dilim' => '<hr>' ,
		'user-defined-group-name' => '—вои' ,
		'mark-row' => 'std-marks-row' ,
		'add-date-range-editor' => false ,
		'integrate-date-range-w-id' => false ,
		'show-timestamp' => false ,
		'timestamp-field' => 'id=catalog-id:timestamp' ,
		'description-as-title' => false ,
		'actions' => array()
	);

	function integrate( $id , $opt = false , $checked = array() ) {
		if ( !is_array( $id ) ) {
			$id = array( $id );
		}

		if ( $opt !== false ) {
			if ( is_string( $opt ) ) {
				$opt = json_decode( $opt , true );
			}
			$opt = array_merge( DEFAULT_OPTIONS , $opt );
		} else {
			$opt = DEFAULT_OPTIONS ;
		}

		//$optModeEdit = $opt[ "mode" ] == "edit" ? true : false ;
		//$optModeShow = $opt[ "mode" ] == "show" ? true : false ;
		//$optModeLabel = $opt[ "mode" ] == "label" ? true : false ;
		//$modeDLG = $optModeShow || $optModeEdit ;
		//$modeROW = $optModeLabel ;

		switch ( $opt[ "mode" ] ) {
			case 'edit' :
			case 'show' :
				$result = integrateDLG( $id , $opt , $checked );
				break ;

			case 'label' :
				$result = integrateROW( $id , $opt , $checked );
				break ;

			case 'simple-inline' :
				$result = integrateSimple( $id , $opt , $checked );
				break ;
				
			case 'text-quoted' :
				$result = integrateTextQuoted( $id , $opt , $checked );
				break ;

			default :
				$result = '' ;
				break ;
		}

		return $result ;
	}

	function integrateSimple( $id , $opt , $catalog ) {
		$optMarkClassAttrPrefix = $opt[ 'mark-class-attr-prefix' ];
		$optShowTimestamp = $opt[ 'show-timestamp' ];
		if ( $optShowTimestamp ) {
			switch ( $opt[ 'timestamp-field' ] ) {
				case 'id=catalog-id:timestamp' :
					$getTimestamp = function( $v ) {
						list( , $t ) = explode( ':' , $v );
						return date( 'Y-m-d H:i:s' , $t );
					};
					break ;
				default :
					$getTimestamp = function(  ) {
						return '' ;
					};
					break ;
			}
		} else {
			$getTimestamp = function() {
				return '' ;
			};
		}
		$res = '' ;
		$i = 0 ;
		$titleField = $opt[ 'description-as-title' ] ? 'description' : 'name' ;

		$actions = '' ;
		if ( isset( $opt[ 'actions' ] ) && is_array( $opt[ 'actions' ] ) ) {
			foreach( $opt[ 'actions' ] as $cActionEvent => $cActionHandler ) {
				$actions.= ' '.$cActionEvent.'="'.$cActionHandler.'(event)"' ;
			}
		}

		foreach ( $id as $coid ) {
			$idList = explode( ':' , $coid );
			if ( count( $idList ) >= 1 ) {
				$cid = $idList[ 0 ];
			}
			if ( count( $idList ) >= 2 ) {
				$mID = $idList[ 1 ];
			} else {
				$mID = '' ;
			}

			$titleText = isset( $catalog[ $cid ][ $titleField ] ) ? $catalog[ $cid ][ $titleField ] : '' ;
			$res.= '<div
				class="std-marks-'.$catalog[ $cid ][ 'style' ].'"
				style="display : inline-block"
				title="'.$titleText.'"
				data-mark-coid="'.$coid.'"
				data-mark-style="'.$catalog[ $cid ][ 'style' ].'"
				data-mark-actual="'.$catalog[ $cid ][ 'actual' ].'"
				data-mark-element="mark"
				data-comment-style="inline"
				data-comment-ext-type="marks"
				data-comment-ext-id="'.$mID.'"
				data-comment-substyle="c-list"
				data-comment-v-style-pref="std-marks-comment"
				'.$actions.'
			><div class="'.$optMarkClassAttrPrefix.'text-container" data-mark-element="text-container"><span data-mark-element="text">'.htmlentities( $catalog[ $cid ][ 'name' ] ).'</span>'.( $optShowTimestamp ? '<span data-mark-element="timestamp">[ '.$getTimestamp( $coid ).' ]</span>' : '' ).'</div></div>' ;
			$i++ ;
		}
		return $res ;
	}
	
	function integrateTextQuoted( $id , $opt , $catalog ) {
		$optShowTimestamp = $opt[ 'show-timestamp' ];
		if ( $optShowTimestamp ) {
			switch ( $opt[ 'timestamp-field' ] ) {
				case 'id=catalog-id:timestamp' :
					$getTimestamp = function( $v ) {
						list( , $t ) = explode( ':' , $v );
						return date( 'Y-m-d H:i:s' , $t );
					};
					break ;
				default :
					$getTimestamp = function(  ) {
						return '' ;
					};
					break ;
			}
		} else {
			$getTimestamp = function() {
				return '' ;
			};
		}
		
		$qo = isset( $opt[ 'q-open'  ]    ) ? $opt[ 'q-open' ] : '"' ;
		$qc = isset( $opt[ 'q-close' ]    ) ? $opt[ 'q-close' ] : '"' ;
		$sep = isset( $opt[ 'separator' ] ) ? $opt[ 'separator' ] : ',' ;
		
		$res = array();
		foreach ( $id as $coid ) {
			list( $cid , $mID ) = explode( ':' , $coid );
			$res[] = $qo.$catalog[ $cid ][ 'name' ].( $optShowTimestamp ? ' [ '.$getTimestamp( $coid ).' ]' : '' ).$qc ;
		}
		return implode( $sep , $res );
	}
	
	function integrateROW( $id , $opt , $catalog ) {
		$markLnkIDMap = array();
		$checked = $catalog ;
		$optMarkClassAttrPrefix = $opt[ "mark-class-attr-prefix" ];
		$optShowTimestamp = $opt[ 'show-timestamp' ];
		if ( $optShowTimestamp ) {
			switch ( $opt[ 'timestamp-field' ] ) {
				case 'id=catalog-id:timestamp' :
					$getTimestamp = function( $v ) {
						list( , $t ) = explode( ":" , $v );
						return date( "Y-m-d H:i:s" , $t );
					};
					break ;
				default :
					$getTimestamp = function(  ) {
						return '' ;
					};
					break ;
			}
		} else {
			$getTimestamp = function() {
				return '' ;
			};
		}
		$res = '<div class="std-marks-area" style="height : '.str_replace( "," , "." , ( count( $id ) * 1.8 ) )."em\">" ;
		$i = 0 ;
		foreach ( $id as $coid ) {
			list( $cid , $mID ) = explode( ":" , $coid );
			$res.= "<div
				class=\"std-marks-".$catalog[ $cid ][ "style" ]."\"
				style=\"top : ".str_replace( "," , "." , ( $i * 1.8 + 0.9 ) )."em\"
				title=\"".$catalog[ $cid ][ "name" ]."\"
				data-comment-style=\"inline\" data-comment-ext-type=\"marks\" data-comment-ext-id=\"".$mID."\" data-comment-substyle=\"c-list\" data-comment-v-style-pref=\"std-marks-comment\"
			><div class=\"".$optMarkClassAttrPrefix."text-container\">".htmlentities( $catalog[ $cid ][ "name" ] ).( $optShowTimestamp ? ' [ '.$getTimestamp( $coid ).' ]' : '' )."</div></div>" ;
			$i++ ;
		}
		return $res."</div>" ;
	}

	function integrateDLG( $id , $opt , $checked ) {
		global $portalDB ;

		$optModeEdit = $opt[ "mode" ] == "edit" ? true : false ;
		$optModeShow = $opt[ "mode" ] == "show" ? true : false ;

		$optIDMarks = $opt[ "id-mark" ] == true ;
		$optMarkIDPrefix = $opt[ "mark-id-attr-prefix" ];
		$optMarkName = $opt[ "mark-name-attr" ];
		$optMarkClassAttrPrefix = $opt[ "mark-class-attr-prefix" ];
		$optGroupIDPrefix = $opt[ "group-id-attr-prefix" ];
		$optGroupName = $opt[ "group-name-attr" ];
		$optGroupClass = $opt[ "group-class" ];
		$optOffsetStep = $opt[ "offset-step" ];
		$optOffsetUnit = $opt[ "offset-unit" ];
		$optMarkRow = $opt[ "mark-row" ];

		$optAddDateRangeEditor = $opt[ "add-date-range-editor" ];
		$optIntegrateDateRangeWID = $opt[ "integrate-date-range-w-id" ];

		$optCheckedArea = $opt[ "checked-area" ];
		$optCheckedAreaDup = $opt[ "checked-area-duplicate" ];

		$markLnkIDMap = array();
		if ( $opt[ "checked-id-only" ] ) {
		} else {
			error_log( print_r( $checked , 1 ) );
			foreach( $checked as $ccm ) {
				$markLnkIDMap[ $ccm[ "id" ] ] = $ccm[ "link_id" ];
			}
			error_log( print_r( $markLnkIDMap , 1 ) );
			$checkedMarks = array_column( $checked , null , "id" );
			$checked = array_column( $checked , "id" );
		}

		//

		$checked = array_combine( $checked , $checked );

		$catalog = $portalDB->table( "marks-catalog" , "id" );
		$groups = $portalDB->table( "marks-groups" , "id" );
		$markGroup = $portalDB->table( "marks-mark-group" );
		$groupGroup = $portalDB->table( "marks-group-group" );

		$result = array();
		if ( $optCheckedArea ) {
			$checkedCount = 0 ;
			$checkedEx = array();
			foreach( $checked as $rmID ) {
				//$mID = $cmInfo[ "id" ];
				$tmp = explode( ":" , $rmID );
				$mID = $tmp[ 0 ];
				if ( count( $tmp ) > 1 ) {
					$tmp = explode( "-" , $tmp[ 1 ] );
					$dr = array(
						min( intval( $tmp[ 0 ] ) , intval( $tmp[ 1 ] ) ) ,
						max( intval( $tmp[ 0 ] ) , intval( $tmp[ 1 ] ) )
					);
					$checkedEx[ $mID ] = $mID ;
				} else {
					$dr = false ;
				}

				if ( !isset( $catalog[ $mID ] ) ) {
					continue ;
				}
				$mi = $catalog[ $mID ];
				if ( $optModeEdit ) {
					$result[]= "<div class=\"".$optMarkRow."\" data-mid=\"".$mID."\" data-id-prefix=\"".$optMarkIDPrefix."\" data-class-prefix=".$optMarkClassAttrPrefix." data-is-ca=\"1\">".
						"<input type=\"checkbox\" id=\"".$optMarkIDPrefix.$mID."-ca\" name=\"".$optMarkName."[]\" value=\"".$mID.( $optAddDateRangeEditor && $optIntegrateDateRangeWID && ( $dr !== false ) ? ":".$dr[ 0 ]."-".$dr[ 1 ] : "" )."\" checked=\"checked\">".
						"<label
							for=\"".$optMarkIDPrefix.$mID."-ca\"
							class=\"".$optMarkClassAttrPrefix.$mi[ "style" ]."\"
							".( isset( $checkedMarks[ $mID ] ) ? " title=\"".date( "d-m-Y H:i:s" , $checkedMarks[ $mID ][ "date" ] )."\"" : "" )."
							".( isset( $markLnkIDMap[ $mID ] ) ? " data-comment-style=\"inline\" data-comment-ext-type=\"marks\" data-comment-ext-id=\"".$markLnkIDMap[ $mID ]."\" data-comment-substyle=\"c-list\" data-comment-v-style-pref=\"std-marks-comment\"" : "" )."
						><div class=\"".$optMarkClassAttrPrefix."text-container\">".htmlentities( $mi[ "name" ] )."</div></label>".
						(
							$optAddDateRangeEditor ?
								"<div class=\"".$optMarkClassAttrPrefix."date-range-area\">".(
									$dr !== false ?
										"с <input type=\"text\" id=\"".$optMarkIDPrefix.$mID."-drl\" value=\"".date( "d.m.Y" , $dr[ 0 ] )."\" oninput=\"$.CORES.marks.dateRageChange()\" onpropertychange=\"$.CORES.marks.dateRageChange()\">".
										" по <input type=\"text\" id=\"".$optMarkIDPrefix.$mID."-drh\" value=\"".date( "d.m.Y" , $dr[ 1 ] )."\" oninput=\"$.CORES.marks.dateRageChange()\" onpropertychange=\"$.CORES.marks.dateRageChange()\">".
										"<div class=\"".$optMarkClassAttrPrefix."dra-delete-btn\" onclick=\"$.CORES.marks.dateRageDelete()\"></div>"
									: "<div class=\"".$optMarkClassAttrPrefix."dra-add-btn\" onclick=\"$.CORES.marks.dateRageAdd()\"></div>"
								)."</div>"
							: "" ).
					"</div>" ;
				} else {
					$result[]= "<div class=\"".$optMarkRow."\">".
						"<label class=\"".$optMarkClassAttrPrefix.$mi[ "style" ]."\"".( isset( $checkedMarks[ $mID ] ) ? " title=\"".date( "d-m-Y H:i:s" , $checkedMarks[ $mID ][ "date" ] )."\"" : "" ).">".$mi[ "name" ]."</label>".
					"</div>" ;
				}
				$checkedCount++ ;
			}
			$checked = array_merge( $checked , $checkedEx );
			if ( $checkedCount > 0 && $optModeEdit ) {
				$result[]= $opt[ "checked-area-dilim" ];
			}
		}

		if ( $optModeEdit ) {
			$ggMap = array();
			foreach( $groupGroup as $cgg ) {
				$pgID = $cgg[ "parent_id" ];
				if ( !isset( $ggMap[ $pgID ] ) ) {
					$ggMap[ $pgID ] = array();
				}
				$ggMap[ $pgID ][]= $cgg[ "group_id" ];
			}

			$mgMap = array();
			foreach( $markGroup as $cmg ) {
				$pgID = $cmg[ "group_id" ];
				if ( !isset( $mgMap[ $pgID ] ) ) {
					$mgMap[ $pgID ] = array();
				}
				$mgMap[ $pgID ][]= $cmg[ "mark_id" ];
			}

			$toProcess = array();
			foreach ( $id as $cid ) {
				if ( $optIDMarks ) {
					$toProcess[]= array( "t" => "m" , "l" => 0 , "r" => false , "id" => $cid );
				} else {
					$toProcess[]= array( "t" => "g" , "l" => 0 , "r" => false , "id" => $cid );
				}
			}

			$toProcess = array_reverse( $toProcess );
			$processed = array();

			do {
				$ce = array_pop( $toProcess );
				if ( $ce[ "t" ] == "g" ) {
					$pgID = $ce[ "id" ];
					if ( isset( $groups[ $pgID ] ) ) {
						if ( isset( $ggMap[ $pgID ] ) ) {
							foreach( array_reverse( $ggMap[ $pgID ] ) as $cgg ) {
								$toProcess[]= array( "t" => "g" , "l" => $ce[ "l" ] + 1 , "r" => $pgID , "id" => $cgg );
							}
						}
						if ( isset( $mgMap[ $pgID ] ) ) {
							foreach( array_reverse( $mgMap[ $pgID ] ) as $cgg ) {
								$toProcess[]= array( "t" => "m" , "l" => $ce[ "l" ] + 1 , "r" => $pgID , "id" => $cgg );
							}
						}
					} else {
						continue ;
					}
				} else
				if ( $ce[ "t" ] == "m" ) {
					$pgID = $ce[ "id" ];
					if ( !isset( $catalog[ $pgID ] ) ) {
						continue ;
					}
				}
				array_push( $processed , $ce );
			} while ( count( $toProcess ) > 0 );

			foreach( $processed as $ei ) {
				$ceID = $ei[ "id" ];
				if ( $ei[ "t" ] == "m" ) {
					$mi = $catalog[ $ceID ];
					$miv = isset( $checked[ $ceID ] ) && $optCheckedArea ? $optCheckedAreaDup : true ;
					if ( $miv ) {
						$result[]= "<div class=\"".$optMarkRow."\" data-mid=\"".$ceID."\" data-id-prefix=\"".$optMarkIDPrefix."\" data-class-prefix=".$optMarkClassAttrPrefix." data-is-ca=\"0\">".
							"<input type=\"checkbox\" id=\"".$optMarkIDPrefix.$ceID."\" name=\"".$optMarkName."[]\" value=\"".$ceID."\" style=\"margin-left : ".( $optOffsetStep * $ei[ "l" ] ).$optOffsetUnit."\" ".( isset( $checked[ $ceID ] ) ? " checked" : "" ).">".
							"<label for=\"".$optMarkIDPrefix.$ceID."\" class=\"".$optMarkClassAttrPrefix.$mi[ "style" ]."\">".$mi[ "name" ]."</label>".
							(
								$optAddDateRangeEditor ?
									"<div class=\"".$optMarkClassAttrPrefix."date-range-area\">".
										"<div class=\"".$optMarkClassAttrPrefix."dra-add-btn\" onclick=\"$.CORES.marks.dateRageAdd()\"></div>"
									."</div>"
							: "" )."</div>" ;
					}
				} else {
					$gi = $groups[ $ceID ];
					if ( !is_null( $gi[ "user_id" ] ) ) {
						$gi[ "name" ] = $opt[ "user-defined-group-name" ];
					}
					$result[]= "<div class=\"".$optMarkRow."\"><input type=\"checkbox\" id=\"".$optGroupIDPrefix.$ceID."\" name=\"".$optGroupName."[]\" value=\"".$ceID."\" style=\"margin-left : ".( $optOffsetStep * $ei[ "l" ] ).$optOffsetUnit."\"><label for=\"".$optGroupIDPrefix.$ceID."\" class=\"".$optGroupClass."\">".$gi[ "name" ]."</label></div>" ;
				}
			}
		}
		return implode( $result );
	}
?>