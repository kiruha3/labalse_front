<?php
	function extractTotalIDListForPart( $partData ) {
		$totalRes = array();
		$specGroups = array_keys( $partData );

		foreach( $specGroups as $cSpecGroupID ) {
			if ( $cSpecGroupID == 'complex' ) {
				continue ;
			}

			if ( count( $partData[ $cSpecGroupID ][ 'spec-data' ] ) == 0 ) {
				continue ;
			}
			$sd = $partData[ $cSpecGroupID ][ 'spec-data' ];
			foreach( $sd as $csd ) {
				foreach( $csd as $kcsdp => $vcsdp ) {
					if ( substr( $kcsdp , 0 , 4 ) == 'res-' && count( $vcsdp ) > 0 ) {
						if ( !isset( $totalRes[ $kcsdp ] ) ) {
							$totalRes[ $kcsdp ] = array();
						}
						$totalRes[ $kcsdp ] = array_merge( $totalRes[ $kcsdp ] , $vcsdp );
					}
				}
			}
		}

		return $totalRes ;
	}