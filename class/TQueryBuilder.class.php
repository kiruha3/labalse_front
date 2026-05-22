<?php
	//namespace TQueryBuilder ;

	const QUERY_TYPE_SELECT = "select" ;

	class TQueryBuilder {
		//private

		private $secData = array();

		private $fields = array();
		private $tables = array();
		private $conditions = array();
		public $orderBy = false ;
		public $groupBy = false ;
		public $limit = false ;

		public function build( $type = QUERY_TYPE_SELECT ) {
			switch( $type ) {
				case QUERY_TYPE_SELECT :
					print_r_html( $this->secData );
					$sc = array(
						$this->secData[ "conditions" ]
					);
					$uta = false ; // union all ?
					if ( isset( $this->secData[ "union" ] ) ) {
						$sc = array_merge( $sc , $this->secData[ "union" ][ "conditions" ] );
						$uta = $this->secData[ "union" ][ "type-all" ];
					}
					$t = array_merge( $this->secData[ "tables" ] , $this->tables );
					$q = array();
					foreach ( $sc as $csc ) {
						$c = array_merge( $csc , $this->conditions );
						$q[]= "select ".implode( " , " , $this->fields )
							." from  ".implode( " , " , $t )
							." where ".implode( " and " , $c )
							.( $this->groupBy !== false ? " group by ".$this->groupBy : "" );
					}

					$q = "( ".implode( " ) union ".( $uta ? " all" : "" )." ( " , $q )." )"
						.( $this->orderBy !== false ? " order by ".$this->orderBy : "" )
						.( $this->limit   !== false ? " limit ".$this->limit : "" );

					return $q ;
					break ;
			}
		}

		public function addSecData( $data ) {
			$this->secData = $data ;
		}

		public function addFields( $f ) {
			if ( !is_array( $f ) ) {
				$f = array( $f );
			}

			$this->fields = array_merge( $this->fields , $f );
		}

		public function addTables( $t ) {
			if ( !is_array( $t ) ) {
				$t = array( $t );
			}

			$this->tables = array_merge( $this->tables , $t );
		}

		public function addConditions( $c ) {
			if ( !is_array( $c ) ) {
				$c = array( $c );
			}

			$this->conditions = array_merge( $this->conditions , $c );
		}
	}
?>