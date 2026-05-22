<?php
	/**
	 * @var $portalDB
	 */

	$TAB_DEPARTMENTS = $portalDB->table( 'departments' , 'id' );
	$TAB_CASECATEGORY = $portalDB->query( 'select * from `casecategory` order by `index` , `name` asc' , 'id' );

	$TAB_CC_GROUPS = array();

	foreach( $TAB_CASECATEGORY as $cc ) {
		$ccID = $cc[ 'id' ];
		$ccg = $cc[ 'cc_group' ];
		if ( !isset( $TAB_CC_GROUPS[ $ccg ] ) ) {
			$TAB_CC_GROUPS[ $ccg ] = array();
		}
		$TAB_CC_GROUPS[ $ccg ][]= $ccID ;
	}

	define( 'PORTAL_TIME_ZONE' , date_default_timezone_get() );
	define( 'PORTAL_TIME_ZONE_OFFSET' , intval( date( 'Z' ) , 10 ) );

	define( 'CC_TYPE_EXP' , 0 );
	define( 'CC_TYPE_RES' , 1 );
