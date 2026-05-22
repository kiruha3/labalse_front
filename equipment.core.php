<?php

	function loadVariables2_post_init_equipment( &$param , &$docVar ) {
		if ( isset( $param[ 'equipment-list-name' ] ) ) {
			$docVar = array_merge( $docVar , array(
				"env:equipment-list-name" => array( "value" => $param[ 'equipment-list-name' ] , "desc" => "Вид списка оборудования" , "mf" => false ) ,
			) );
		}
	}
