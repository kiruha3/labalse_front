<?php

	class TSimpleList {
		//protected $con = false ;
		//public $dbgMode = false ;

		protected $tabName ;
		protected $db ;
		protected $key ;

		//public $

		function __construct( $tabName , $key = "id" , $db = false ) {
			if ( $db === false ) {
				global $portalDB ;
				$this->db = $portalDB ;
			} else {
				$this->db = $db ;
			}

			$this->tabName = $tabName ;
			$this->key = $key ;
		}

		function __destruct() {
		}

		function ajax( $data ) {

		}

		function mkEditDlg() {
			var_dump( $this->db->dbgMode );
			echo $this->tabName."<br>" ;
			echo $this->key."<br>" ;
		}

		function mkSelectList(  ) {

		}

	}
?>