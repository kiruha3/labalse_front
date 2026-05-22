<?php
	namespace Security ;

	function generateQueryData( $objectType , $placeID ) {
		global $user ;

		$rights = ParseRightsToFlat( $placeID );
		//print_r_html( $rights , 1 );

		if ( !isset( $rights[ "DB-ACCESS-TYPE" ] ) ) {
			$rights[ "DB-ACCESS-TYPE" ] = "NONE" ;
		}

		switch( $objectType ) {
			case "lvl1card" :
				$res = generateQueryData_lvl1card( $rights[ "DB-ACCESS-TYPE" ] );
				break ;

			case "lvl1cardWGroups" :
				$res = generateQueryData_lvl1card( $rights[ "DB-ACCESS-TYPE" ] , true );
				break ;
		}

		return $res ;
	}

	function generateQueryData_lvl1card( $accessType , $withGroups = false ) {
		global $user ;
		$res = array();
		switch ( $accessType ) {

			case "EXPERT" :
				$res[ "tables" ] = array( "`matincoming` as `ts1`" , "`matincominglvl2` as `ts2`" , "`expertize` as `ts3`" );
				$res[ "conditions" ] = array(
					"( `t1`.`id` = `ts1`.`id` )" ,
					"( `ts2`.`mat_id` = `ts1`.`id` )" ,
					"( `ts3`.`ext_id` = `ts2`.`id` )" ,
					"( `ts3`.`exp_id` in ( ".implode( "," , $user->allWorkersID )." ) )"
				);

				if ( $withGroups ) {
					$res[ "union" ] = array( "type-all" => false , "conditions" => array( array(
						"( `t1`.`group_id` = `ts1`.`group_id` ) and ( `ts1`.`group_id` is not null ) and ( `ts1`.`group_id` <> 0 )" ,
						"( `ts2`.`mat_id` = `ts1`.`id` )" ,
						"( `ts3`.`ext_id` = `ts2`.`id` )" ,
						"( `ts3`.`exp_id` in ( ".implode( "," , $user->allWorkersID )." ) )"
					) ) );
				}
				break ;

			default :
				$res[ "tables" ] = array();
				$res[ "conditions" ] = array( "( 0 )" );
				break ;
		}

		return $res ;
	}

	function checkAccess( $object ) {
		global $user ;
	}
?>