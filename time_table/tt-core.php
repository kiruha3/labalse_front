<?php

	$ttDescr = array(
		0 => array(
			'placeID' => 1 ,
			'ruleSet' => 'RECORDS' ,
			'labelText' => 'График выездов' ,
		) ,
		1 => array(
			'placeID' => 14 ,
			'ruleSet' => 'ONLINE-EVENTS-RECORDS' ,
			'labelText' => 'График online-мероприятий' ,
		) ,
		2 => array(
			'placeID' => 15 ,
			'ruleSet' => 'ONLINE-EVENTS-RECORDS' ,
			'labelText' => 'График online-мероприятий' ,
		) ,
	);

	if ( isset( $_REQUEST[ 'type' ] ) ) {
		$ttType = intval( $_REQUEST[ 'type' ] , 10 );
	} else {
		$ttType = 0 ;
	}

?>