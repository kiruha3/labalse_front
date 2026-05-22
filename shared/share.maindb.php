<?php
	function getPaymentsAddr() {
		global $UserOptions ;
		if ( isset( $UserOptions[ 'payments.redirect' ] ) ) {
			return $UserOptions[ 'payments.redirect' ][ 'op_value' ];
		} else {
			return 'payments.php' ;
		}
	}
