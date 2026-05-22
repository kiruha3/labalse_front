<?php
	$timerData = array();
	function fixTimerData( $n ) {
		global $timerData ;
		if ( !isset( $timerData[ $n ] ) ) {
			$timerData[ $n ] = array();
		}
		$timerData[ $n ][]= microtime( true );
	}
	
	function var_dump_html( $a , $f = false , $asLine = false ) {
		global $UserID ;
		if ( $UserID == 1 || $f || $_SERVER[ "REMOTE_ADDR" ] == "10.0.0.253" ) {
			ob_start();
			var_dump( $a );
			$res = ob_get_contents();
			ob_end_clean();
			
			$res = str_replace( "\n" , "<br>" , $res );
			$res = str_replace( "\t" , "    " , $res );
			$res = str_replace( " " , "&nbsp;" , $res );
			if ( !$asLine ) {
				$res.= "<br>" ;
			}
			echo $res ;
		}
	}
	
	function print_r_html( &$a , $f = false , $asLine = false ) {
		global $UserID ;
		if ( $UserID == 1 || $f || $_SERVER[ "REMOTE_ADDR" ] == "10.0.0.253" ) {
			$res = print_r( $a , true );
			$res = preg_replace( '/Array\s+\(\s/' , "Array (\n" , $res );
			$res = str_replace( "&" , "&amp;" , $res );
			$res = str_replace( "\"" , "&quot;" , $res );
			$res = str_replace( "<" , "&lt;" , $res );
			$res = str_replace( ">" , "&gt;" , $res );
			$res = str_replace( "\r\n" , "\n" , $res );
			$res = str_replace( "\n" , "<br>" , $res );
			$res = str_replace( "\t" , "    " , $res );
			$res = str_replace( " " , "&nbsp;" , $res );
			if ( !$asLine ) {
				$res.= "<br>" ;
			}
			echo $res ;
		}
	}
	
	function print_r_html_2( &$a , $f = false , $asLine = false ) {
		global $UserID ;
		if ( $UserID == 1 || $f || $_SERVER[ "REMOTE_ADDR" ] == "10.0.0.253" ) {
			$res = print_r( $a , true );
			$res = str_replace( "\n" , "<br>" , $res );
			$res = str_replace( "\t" , "    " , $res );
			$res = str_replace( " " , "&nbsp;" , $res );
			if ( !$asLine ) {
				$res.= "<br>" ;
			}
			return $res ;
		}
	}
	
	function var_dump_line( $v ) {
		ob_start();
		var_dump( $v );
		return ob_get_clean();
	}
	