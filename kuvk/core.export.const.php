<?php
    define( 'KUVK_LINK_AGENT' , 'agent' );
	define( 'KUVK_LINK_AGENCY' , 'agency' );
	define( 'KUVK_LINK_MATINCOMING' , 'matincoming' );
	define( 'KUVK_LINK_MATINCOMING_C23' , 'matincominglvl2-expertize' );
	define( 'KUVK_LINK_SPECIALITIES' , 'specialities' );
	define( 'KUVK_LINK_SPECIALITIES_GROUPS' , 'specialities-groups' );
	define( 'KUVK_LINK_WORKERS' , 'workers' );
	define( 'KUVK_LINK_DEPARTMENTS' , 'departments' );
	define( 'KUVK_LINK_POSTS' , 'posts' );
	define( 'KUVK_LINK_STAFFING' , 'staffing' );
	define( 'KUVK_LINK_STAFFING_WORKERS' , 'staffing-workers' );
	define( 'KUVK_LINK_WORKERS_SPEC' , 'workers-spec' );
	define( 'KUVK_LINK_CORRESPONDENCE' , 'correspondence' );
	define( 'KUVK_LINK_SUBPOENA' , 'subpoena' );
	define( 'KUVK_LINK_EQUIPMENT' , 'equipment' );
	define( 'KUVK_LINK_BILL' , 'bill' );
	define( 'KUVK_LINK_EVIDENCE' , 'evidence' );

	$clearArea = array(
		'clear-corr'         => KUVK_LINK_CORRESPONDENCE ,
		'clear-matincoming'  => array(
			KUVK_LINK_MATINCOMING ,
			KUVK_LINK_MATINCOMING_C23
		) ,
		'clear-agents'       => array(
			KUVK_LINK_AGENT ,
			KUVK_LINK_AGENCY
		) ,
		'clear-workers-info' => array(
			KUVK_LINK_SPECIALITIES ,
			KUVK_LINK_SPECIALITIES_GROUPS ,
			KUVK_LINK_WORKERS ,
			KUVK_LINK_DEPARTMENTS ,
			KUVK_LINK_POSTS ,
			KUVK_LINK_STAFFING ,
			KUVK_LINK_STAFFING_WORKERS ,
			KUVK_LINK_WORKERS_SPEC
		) ,
		'clear-subpoenas'    => KUVK_LINK_SUBPOENA ,
		'clear-equipment'    => KUVK_LINK_EQUIPMENT
	);

