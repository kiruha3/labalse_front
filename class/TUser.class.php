<?php
	class TUser {
		public $login = false ;
		public $name = false ;
		public $ID = false ;
		public $workerID = false ;
		public $workerFirstID = false ;
		public $allWorkersID = false ;
		public $lastVisit = false ;
		public $departmentID = false ;
		public $allDepsID = false ;
		public $depAllWorkersID = false ;
		public $postID = false ;
		public $specialityNumber = false ;
		public $rights = false ;
		public $options = false ;
		public $theme = false ;
		public $groups = false ;
		public $orgIndex = false ;

		function __construct( $accountID ) {

		}

		function assign() {
			global
				$UserLogin , $UserName , $UserID ,
				$UserWorkerID , $UserWorkerFirstID , $UserAllWorkers ,
				$UserLastVisitDate , $UserLastVisitTime ,
				$UserDepartment , $UserAllDeps , $DepAllWorkers ,
				$UserPost ,
				$UserSpecialityNumber ,
				$UserRights ,
				$UserOptions ,
				$UserThemeDir , $UserThemeLoc ,
				$UserGroups ,
				$UserOrgIndex ;

			$this->login = &$UserLogin ;
			$this->name = &$UserName ;
			$this->ID = &$UserID ;
			$this->workerID = &$UserWorkerID ;
			$this->workerFirstID = &$UserWorkerFirstID ;
			$this->allWorkersID = &$UserAllWorkers ;
			$this->lastVisit = &$UserlastVisitDate ;
			$this->departmentID = &$UserDepartment ;
			$this->allDepsID = &$UserAllDeps ;
			$this->depAllWorkersID = &$DepAllWorkers ;
			$this->postID = &$UserPost ;
			$this->specialityNumber = &$UserSpecialityNumber ;
			$this->rights = &$UserRights ;
			$this->options = &$UserOptions ;
			$this->theme = array( "dir" => &$UserThemeDir , "loc" => &$UserThemeLoc );
			$this->groups = &$UserGroups ;
			$this->orgIndex = &$UserOrgIndex ;
		}
	}
?>