<?php
	/**
	 * @var $dbConfig
	 * @var $portalDB
	 * @var $UserDepartment
	 */

	$depData = $portalDB->row( "select * from `departments` where `id` = ?" , "i" , $UserDepartment );
	if ( $depData !== false && $depData[ 'remote' ] == 1 ) {
		$docs = $dbConfig[ 'engine.addresses.docs.remote' ];
	} else {
		$docs = $dbConfig[ 'engine.addresses.docs.local' ];
	}
	
	$dbConfigJS = iconvRecursion( DB_CODEPAGE , 'utf8' , $dbConfig );

	$portalDataVersions = $portalDB->table( 'versions' );
	$portalDataVersions = array_column( $portalDataVersions , 'version' , 'name' );
	
	
	echo '<script type="text/javascript">
	const $ = {
		VERSION_CHAR_ID : 10 ,
		ORG_INDEX_PATTERN : \'\\\\w{3}\' ,
		ORG_INDEX_ANY : \'*\' ,
		ORG_INDEX_TEST : \'Te0\' ,
		ORG_INDEX_VRCSE : \''.$dbConfig[ 'engine.orgIndex' ].'\' ,
		ORG_INDEX_TRAINING : \'Tr0\' ,
		DOCTYPE_PATTERN : \'\\\\w{4}\' ,
		OBJ_YEAR_PATTERN : \'20\\\\d{2}\' ,
		OBJ_L_NUMBER_PATTERN : \'\\\\w{6,8}\' ,
		windowOnLoad : [] ,
		thisPageObjects : {} ,
		VERSIONS : '.json_encode( $portalDataVersions ).'
	}
	
	$.OBJ_G_NUMBER_PATTERN = $.OBJ_YEAR_PATTERN + $.OBJ_L_NUMBER_PATTERN ;
	$.CHARID_STRUCTURE_PATTERN = \'(\' + $.VERSION_CHAR_ID + \')\\\\.(\' + $.ORG_INDEX_PATTERN + \')\\\\.(\' + $.DOCTYPE_PATTERN + \')\\\\.(\' + $.OBJ_YEAR_PATTERN + \')(\' + $.OBJ_L_NUMBER_PATTERN + \')\' ;
	$.docsURL = \'https://'.$docs.'\' ;
	
	$.PageGeneratedDateTime = '.time().' ;
	$.serverTimezoneOffset = '.PORTAL_TIME_ZONE_OFFSET.' ;
	
	$.dbConfig = JSON.parse( atob( "'.base64_encode( json_encode( $dbConfigJS ) ).'" ) );
</script>' ;
