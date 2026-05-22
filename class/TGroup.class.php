<?php
	class TGroup {
		public static function load( $groupID ) {
			try{
				return new TGroup( $groupID );
			} catch ( ObjectNotFoundException $e ) {
				return null ;
			}
		}

		protected static $groupCache = false ;
		protected static $groupLinksCache = false ;
		protected static $groupsMap = false ;
		protected static $groupsRevMap = false ;
		private static function loadGroups() {
			global $portalDB ;
			$g = $portalDB->simpleQuery( 'accounts' , array( 'type' => ACCOUNT_TYPE_GROUP ) );
			if ( $g === false ) {
				TGroup::$groupCache = array();
				throw new Exception( 'groups read error' );
			}
			TGroup::$groupCache = $g ;
		}
		private static function loadGroupLinks() {
			global $portalDB ;
			if ( TGroup::$groupCache === false ) {
				try{
					TGroup::loadGroups();
				} catch ( Exception $e ) {
					return ;
				}
			}
			$gl = $portalDB->table( 'group-group' );
			if ( $gl === false ) {
				TGroup::$groupLinksCache = array();
				throw new Exception( 'group links loading error' );
			}
			TGroup::$groupLinksCache = $gl ;

			$g = &TGroup::$groupCache ;
			$gm = array();
			$grm = array();
			foreach( $g as $cg ) {
				$gID = $cg[ 'id' ];
				$gm[ $gID ] = array();
			}
			foreach( $gl as $cgl ) {
				$rID = $cgl[ 'root' ];
				$cID = $cgl[ 'child' ];
				if ( !isset( $g[ $rID ] ) || !isset( $g[ $cID ] ) ) {
					throw new Exception( 'group links processing error : group not found' );
				}
				$p = array( $rID );
				$gm[ $rID ][ $cID ] = true ;

			}
		}

		public $ID = false ;
		public $name = false ;
		public $rights = false ;
		public $options = false ;
		public $childGroups = false ;
		public $rootGroups = false ;

		protected function __construct( $accountID ) {
			if ( TGroup::$groupCache === false ) {
				TGroup::loadGroups();
			}
			$groups = &TGroup::$groupCache ;

			if ( !isset( $groups[ $accountID ] ) ) {
				throw new ObjectNotFoundException();
				return ;
			}

			$groupData = $groups[ $accountID ];

			if ( TGroup::$groupLinksCache === false ) {
				TGroup::loadGroupLinks();
			}
			$groupLinks = &TGroup::$groupLinksCache ;

			$this->ID = $accountID ;
			$this->name  = $groupData[ 'login' ];
			$this->childGroups = array();
			$this->rootGroups = array();
		}
	}