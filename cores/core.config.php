<?php

	/**
	 * @var $RelRootDir
	 */
	
	include( $RelRootDir.'config.php' );

	$dbConfigFull = array();
	$dbConfig = $dbConfigFull ;

	define( 'CFG_DATABANK_DEFAULT_EXPERTIZE'          , 'databank.default-id.expertize' );

	define( 'CFG__ENGINE__AUTH__REALM'                   , 'engine.auth.realm' );
	define( 'CFG_ENGINE_PORTAL_ALTER_TITLE'           , 'engine.portal.alter-title' );
	define( 'CFG_ENGINE_PORTAL_STYLE_MOD'             , 'engine.portal.style-mod' );


	define( 'CFG_EXPERTIZE_PRICE_EDITABLE'            , 'expertize.price.editable' );

	define( 'CFG_MARK_MATINCOMING_UNAUTHORIZED_CONSTRUCTION' , 'mark.matincoming.unauthorized-construction' );
	define( 'CFG_MARK_MATINCOMING_CADASTRAL_VALUE'           , 'mark.matincoming.cadastral-value' );
	define( 'CFG_MARK_GROUP_CORRESPONDENCE_PREFX'            , 'mark-group.correspondence' );
	define( 'CFG_MARK_GROUP_MATINCOMING'                     , 'mark-group.matincoming' );
	define( 'CFG_MARK_GROUP_WOE'                             , 'mark-group.woe' );

	define( 'CFG_MATINCOMING_FINDATE_DELTALIMIT_LOW'  , 'matincoming.finDate.deltaLimit.Low' );
	define( 'CFG_MATINCOMING_FINDATE_DELTALIMIT_HIGH' , 'matincoming.finDate.deltaLimit.High' );
	define( 'CFG_MATINCOMING_MARK_NOPAY'              , 'matincoming.markNoPay' );
	define( 'CFG_MATINCOMING_MARK_RP3214R'            , 'matincoming.markRP3214r' );

	define( 'CFG_ORG_NAME_SHORT'                      , 'org.name.short' );
	define( 'CFG_ORG_NAME_SIMPLE'                     , 'org.name.simple' );


	define( 'CFG__ORG__ADDRESS'                       , 'org.address' );
	define( 'CFG__ORG__PHONE__CODE'                   , 'org.phone.code' );
	define( 'CFG__ORG__PHONE'                         , 'org.phone' );
	define( 'CFG__ORG__FAX'                           , 'org.fax' );
	define( 'CFG__ORG__EMAIL'                         , 'org.email' );
	define( 'CFG__REPORT__151__EMAIL'                 , 'report.151.email' );




	define( 'CFG__REPORT__151__ed_88__CONFIG' , 'report.151.ed-88.config' );
	define( 'CFG__REPORT__151__ed_129__SPEC_SUM' , 'matincoming.report-151-129-246.spec-sum' );
	define( 'CFG__REPORT__151__RP3214R__CONFIG' , 'report.151.RP-3214r.config' );

	define( 'CFG__MATINCOMING_CARDS_FORMAT_EVIDENCE_DMTX'   , 'matincoming.cards.format.evidence-dmtx'   );
	define( 'CFG__MATINCOMING_CARDS_FORMAT_EVIDENCE_SIDE_2' , 'matincoming.cards.format.evidence-side-2' );
	define( 'CFG__MATINCOMING_CARDS_FORMAT_ORDER_2_DMTX'    , 'matincoming.cards.format.order-2-dmtx'    );
	define( 'CFG__MATINCOMING_CARDS_FORMAT_ORDER_2_SIDE_2'  , 'matincoming.cards.format.order-2-side-2'  );

	define( 'OPTION__PORTAL__STYLE_MOD'  , 'portal.style-mod' );

	function loadConfig() {
		global $dbConfig , $dbConfigFull , $portalDB ;
		
		function extractDBTree( $c , &$a ) {
			global $portalDB ;
			//print_r_html( $c );
			$b = $c[ 'name' ];
			$id = $c[ 'value' ];
			$ced = json_decode( $c[ 'e-data' ] , true );
			$t = $ced[ 'typeData' ];
			$dbi = array(
				"workers-no-spec" => array(
					"key" => "id" ,
					"desc" => "Сотрудник" ,
					"fields" => array(
						"id" => array( "type" => "key" , "desc" => "ID" ),
						"name" => array( "type" => "name" , "form" => "%F0 %I0 %O0" , "desc" => "Фамилия Имя Отчество" ),
						"name1" => array( "type" => "name" , "form" => "%F0 %i.%o." , "desc" => "Фамилия И.О." , "real" => "name" ),
						"name2" => array( "type" => "name" , "form" => "%i.%o. %F0" , "desc" => "И.О. Фамилия" , "real" => "name" ),
						"post" => array( "type" => "dbTree" , "typeData" => "posts" , "desc" => "Должность" , "real" => "post_1_id" ),
						"dep" => array( "type" => "dbTree" , "typeData" => "departments" , "desc" => "Отдел" ),
					)
				) ,
				'posts' => array(
					'key' => 'id' ,
					'desc' => 'Должность' ,
					'fields' => array(
						'id' => array( 'type' => 'key' , 'desc' => 'ID' ),
						'name' => array( 'type' => 'string' , 'desc' => 'Название' )
					)
				) ,
				'departments' => array(
					'key' => 'id' ,
					'desc' => 'Отдел' ,
					'fields' => array(
						'id' => array( 'type' => 'key' , 'desc' => 'ID' ),
						'name' => array( 'type' => 'string' , 'desc' => 'Название' )
					)
				) ,
			);
			
			$cdb = &$dbi[ $t ];
			/** @noinspection SqlResolve */
			$q = "select * from `".$t."` where `".$cdb[ "key" ]."` = ".$id ;
			$row = $portalDB->row( $q );
			$a[ $b ] = array(
				'name' => $b ,
				'value' => $row ,
				'description' => $cdb[ 'desc' ],
				'e-data' => '{"d-tmpl":0}'
			);
			$cdbf = $cdb[ 'fields' ];
			foreach ( $cdbf as $fn => &$fd ) {
				$ned = array( 'type' => $fd[ 'type' ] );
				$nn = $b.'.'.$fn ;
				if ( isset( $row[ $fn ] ) ) {
					$nv = $row[ $fn ];
				} else
					if ( isset( $fd[ 'real' ] ) && isset( $row[ $fd[ 'real' ] ] ) ) {
						$nv = $row[ $fd[ 'real' ] ];
					} else {
						$nv = '' ;
					}
				$nd = $c[ "description" ]." > ".$fd[ "desc" ];
				$nra = true ;
				switch( $fd[ "type" ] ) {
					case "dbTree" :
						$ned[ "typeData" ] = $fd[ "typeData" ];
						$na = array(
							"name" => $nn ,
							"value" => $nv ,
							"description" => $nd ,
							"e-data" => json_encode( $ned )
						);
						extractDBTree( $na , $a );
						$nra = false ;
						break ;
					
					case "key" :
						$ned[ "d-tmpl" ] = 0 ;
						break ;
					
					case "name" :
						$nv = NAMES_Format( NAMES_parse( $nv ) , $fd[ "form" ] );
						$ned[ "mf" ] = 1 ;
						break ;
				}
				if ( $nra ) {
					$a[ $nn ] = array(
						"name" => $nn ,
						"value" => $nv ,
						"description" => $nd ,
						"e-data" => json_encode( $ned )
					);
				}
			} unset( $fd );
			//$
		};
		
		function getVal( $name ) {
			global $dbConfigFull ;
			
			if ( !isset( $dbConfigFull[ $name ] ) ) {
				return null ;
			}
			
			$cc = $dbConfigFull[ $name ];
			
			if ( !is_null( $cc[ "e-data" ] ) ) {
				$cced = json_decode( $cc[ "e-data" ] , true );
			} else {
				$cced = array();
			}
			
			if ( isset( $cced[ 'valueType' ] ) ) {
				switch ( $cced[ 'valueType' ] ) {
					case 'ref' :
						return getVal( $cc[ 'value' ] );
						break ;
					
					default :
						return $cc[ 'value' ];
						break ;
				}
			} else {
				return $cc[ 'value' ];
			}
		}
		
		$dbConfigFull = $portalDB->query( "select * from `config` order by `description`" , "name" );
		$aa = array();
		foreach ( $dbConfigFull as &$cc ) {
			if ( !is_null( $cc[ "e-data" ] ) ) {
				$cced = json_decode( $cc[ "e-data" ] , true );
			} else {
				$cced = array();
			}
			
			//$cc[ "e-data" ] = $cced ;
			
			if ( isset( $cced[ "type" ] ) ) {
				switch( $cced[ "type" ] ) {
					case "dbTree" :
						extractDBTree( $cc , $aa );
						break ;
				}
			}
		} unset( $cc );
		
		$dbConfigFull = array_merge( $dbConfigFull , $aa );
		
		foreach ( $dbConfigFull as &$cc ) {
			$cc[ 'value' ] = getVal( $cc[ 'name' ] );
		} unset( $cc );
		
		$dbConfig = $dbConfigFull ;
		foreach( $dbConfig as &$dbc ) {
			$dbc = $dbc[ "value" ];
		} unset( $dbc );
	}
	
	
