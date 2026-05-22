<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	const PAPER_SIZE_A3_PORTRAIT = "A3p" ;
	const PAPER_SIZE_A4_PORTRAIT = "A4p" ;
	const PAPER_SIZE_A5_PORTRAIT = "A5p" ;

	const PAPER_SIZE_A3_LANDSCAPE = "A3l" ;
	const PAPER_SIZE_A4_LANDSCAPE = "A4l" ;
	const PAPER_SIZE_A5_LANDSCAPE = "A5l" ;

	const PAPER_SIZE_C3_PORTRAIT = "C3p" ;
	const PAPER_SIZE_C4_PORTRAIT = "C4p" ;
	const PAPER_SIZE_C5_PORTRAIT = "C5p" ;

	const FONT_CALIBRI = "Calibri" ;
	const FONT_SANS_SERIF = "Microsoft Sans Serif" ;
	const FONT_TIMES_NEW_ROMAN = "Times New Roman" ;

	const TEXT_ALIGN_LEFT		= "left" ;
	const TEXT_ALIGN_CENTER		= "center" ;
	const TEXT_ALIGN_RIGHT		= "right" ;
	const TEXT_ALIGN_JUSTIFIED	= "justified" ;

	const CELL_ALIGN_TOP		= "top" ;
	const CELL_ALIGN_CENTER		= "center" ;
	const CELL_ALIGN_BOTTOM		= "bottom" ;

	const CELL_MERGE_NONE       = "none" ;
	const CELL_MERGE_FIRST      = "first" ;
	const CELL_MERGE_PRECEDING  = "preceding" ;

	const CELL_DIRECTION_LRTB   = "lrtb" ;
	const CELL_DIRECTION_BTLR   = "btlr" ;

	const LANGUAGE_CODE_RUSSIAN = 1049 ;

	function unitConvert( $src , $tu ) {
		$m = array();
		$n = preg_match( "/^(-?\\d+(?:\\.\\d+)?)(mm|cm|in|pt|tw|px)$/" , trim( $src ) , $m );
		if ( $n != 1 ) {
			return false ;
		}

		$cvt = array(
			"mm" => array( "mm" =>         1.0 , "cm" =>         0.1 , "in" =>   1.0/25.4 , "pt" => 72.0/25.4 , "tw" => 1440.0/25.4 , "px" =>   96.0/25.4 ),
			"cm" => array( "mm" =>        10.0 , "cm" =>         1.0 , "in" =>   1.0/2.54 , "pt" => 72.0/2.54 , "tw" => 1440.0/2.54 , "px" =>   96.0/2.54 ),
			"in" => array( "mm" =>        25.4 , "cm" =>        2.54 , "in" =>        1.0 , "pt" =>      72.0 , "tw" =>      1440.0 , "px" =>        96.0 ),
			"pt" => array( "mm" =>   25.4/72.0 , "cm" =>   2.54/72.0 , "in" =>   1.0/72.0 , "pt" =>       1.0 , "tw" =>        20.0 , "px" =>   96.0/72.0 ),
			"tw" => array( "mm" => 25.4/1440.0 , "cm" => 2.54/1440.0 , "in" => 1.0/1440.0 , "pt" =>  1.0/20.0 , "tw" =>         1.0 , "px" => 96.0/1440.0 ),
			"px" => array( "mm" =>   25.4/96.0 , "cm" =>   2.54/96.0 , "in" =>   1.0/96.0 , "pt" => 72.0/96.0 , "tw" => 1440.0/96.0 , "px" =>         1.0 )
		);

		if ( !isset( $cvt[ $tu ] ) ) {
			return false ;
		}

		if ( !isset( $cvt[ $m[ 2 ] ] ) ) {
			return false ;
		}

		return floatval( $m[ 1 ] ) * $cvt[ $m[ 2 ] ][ $tu ];
	}

	function numUnitCheck( $v ) {
		$n = preg_match( "/^\\d+(\\.\\d+)?(mm|cm|in|pt)$/" , trim( $v ) );
		return $n == 1 ;
	}

	class RTFDocumentTableCell extends baseExt {
		public $parentRow = null ;
		function __construct( $parent ) {
			$this->parentRow = $parent ;
		}

		public $docElements = array();
		public $width = "7.5cm" ;
		public $textDirection = CELL_DIRECTION_LRTB ;
		public $verticalAlign = CELL_ALIGN_CENTER ;
		public $hMerge = CELL_MERGE_NONE ;
		public $vMerge = CELL_MERGE_NONE ;

		//private $currentBorders ;
		public $borders = array(
			"l" => array( "t" => "s" , "c" => 1 ),
			"t" => array( "t" => "s" , "c" => 1 ),
			"r" => array( "t" => "s" , "c" => 1 ),
			"b" => array( "t" => "s" , "c" => 1 )
		);

		//private $currentPaddings ;
		public $paddings = array();

		public function setBorders( $borders , $type = "s" , $colorIndex = 1 , $width = false , $spacing = false ) {
			foreach ( str_split( $borders ) as $bi ) {
				if ( $type == "none" ) {
					unset( $this->borders[ $bi ] );
				} else {
					$this->borders[ $bi ][ "t" ] = $type ;
					$this->borders[ $bi ][ "c" ] = $colorIndex ;
					if ( $width !== false ) {
						$this->borders[ $bi ][ "w" ] = ceil( unitConvert( $width , "tw" ) );
					}
					if ( $spacing !== false ) {
						$this->borders[ $bi ][ "s" ] = ceil( unitConvert( $spacing , "tw" ) );
					}
				}
			}
			return $this ;
		}

		public function setPaddings( $paddings ) {
			$v = preg_replace( "(\\s+)" , " " , trim( $paddings ) );
			$n = preg_match( "/^\\d+(\\.\\d+)?(mm|cm|in|pt)(\\s\\d+(\\.\\d+)?(mm|cm|in|pt)){0,3}$/" , $v );
			if ( $n != 1 ) {
				$this->showFormatErrorNotice( "paddings" );
				return $this ;
			}

			$v = explode( " " , $v );
			switch ( count( $v ) ) {
				case 1 :
					$this->paddings = array( "l" => $v[ 0 ] , "t" => $v[ 0 ] , "r" => $v[ 0 ] , "b" => $v[ 0 ] );
					break ;
				case 2 :
					$this->paddings = array( "l" => $v[ 1 ] , "t" => $v[ 0 ] , "r" => $v[ 1 ] , "b" => $v[ 0 ] );
					break ;
				case 3 :
					$this->paddings = array( "l" => $v[ 1 ] , "t" => $v[ 0 ] , "r" => $v[ 1 ] , "b" => $v[ 2 ] );
					break ;
				case 4 :
					$this->paddings = array( "l" => $v[ 3 ] , "t" => $v[ 0 ] , "r" => $v[ 1 ] , "b" => $v[ 2 ] );
					break ;
			}

			return $this ;
		}

		private $currentBgColorIndex = "none" ;
		public function setBgColor( $v = "none" ) {
			$v = trim( $v );

			if ( $v == "none" ) {
				$this->currentBgColorIndex = $v ;
			} else {
				$parentDoc = $this->parentRow->parentTable->parentDoc ;
				$n = preg_match( "/^#?(([0-9a-f]{3}){1,2})$/i" , $v );
				if ( $n != 1 ) {
					$parentDoc->showFormatErrorNotice( "bgColor" );
					return $this ;
				}

				if ( $v[ 0 ] == "#" ) {
					$v = substr( $v , 1 );
				}

				if ( strlen( $v ) == 3 ) {
					$v = $v[ 0 ].$v[ 0 ].$v[ 1 ].$v[ 1 ].$v[ 2 ].$v[ 2 ];
				}

				$ci = $parentDoc->getColorIndex( $v );
				$this->currentBgColorIndex = $ci ;
			}

			//$this->docElements[]= $de ;
			return $this ;
		}
		public function getBgColor() {
			if ( $this->currentBgColorIndex == "none" ) {
				return $this->currentBgColorIndex ;
			} else {
				return $this->colorTab[ $this->currentBgColorIndex - 1 ];
			}
		}



		private function processDocElements() {
			$res = "" ;
			foreach ( $this->docElements as & $de ) {
				$pf = "processDocElements_".$de[ "pf" ];
				$res.= $this->$pf( $de );
			} unset( $de );

			return $res ;
		}
		private function processDocElements_simple( $e ) {
			return "\\".$e[ "eName" ].( isset( $e[ "param" ] ) ? $e[ "param" ] : "" );
		}

		private function processDocElements_text( $e ) {
			return $e[ "param" ];
		}

		private function processDocElements_picture( $e ) {
			switch( $e[ "param" ][ "type" ] ) {
				case "pngblip" :
					$wd = ( $e[ "param" ][ "wd" ] === false ? ceil( unitConvert( $e[ "param" ][ "wp" ]."px" , "tw" ) ) : ceil( unitConvert( $e[ "param" ][ "wd" ] , "tw" ) ) );
					$hd = ( $e[ "param" ][ "hd" ] === false ? ceil( unitConvert( $e[ "param" ][ "hp" ]."px" , "tw" ) ) : ceil( unitConvert( $e[ "param" ][ "hd" ] , "tw" ) ) );
					$wp = ceil( unitConvert( $e[ "param" ][ "wp" ]."px" , "mm" ) * 100 );
					$hp = ceil( unitConvert( $e[ "param" ][ "hp" ]."px" , "mm" ) * 100 );
					break ;
			}
			return "{\\pict\\".$e[ "param" ][ "type" ]."\\picw".$wp."\\pich".$hp."\\picwgoal".$wd."\\pichgoal".$hd." ".bin2hex( $e[ "param" ][ "raw" ] )."}" ;
		}

		private function processDocElements_table( $e ) {
			return $e[ "param" ]->write()."" ;
		}





		public function write( & $pos ) {
			$pos+= round( unitConvert( $this->width , "tw" ) );
			$res = "" ;
			switch ( $this->hMerge ) {
				case CELL_MERGE_FIRST :
					$res.= "\\clmgf" ;
					break ;

				case CELL_MERGE_PRECEDING :
					$res.= "\\clmrg" ;
					break ;

				default :
					break ;
			}

			switch ( $this->vMerge ) {
				case CELL_MERGE_FIRST :
					$res.= "\\clvmgf" ;
					break ;

				case CELL_MERGE_PRECEDING :
					$res.= "\\clvmrg" ;
					break ;

				default :
					break ;
			}

			foreach ( $this->borders as $bi => $bd ) {
				$res.= "\\clbrdr".$bi."\\brdr".$bd[ "t" ].( isset( $bd[ "w" ] ) ? "\\brdrw".$bd[ "w" ] : "" ).( isset( $bd[ "s" ] ) ? "\\brsp".$bd[ "s" ] : "" )."\\brdrcf".$bd[ "c" ];
			}

			$cellPaddingsPatch = array(
				"l" => "t" ,
				"t" => "l" ,
				"r" => "r" ,
				"b" => "b" ,
			);

			foreach( str_split( "ltbr" ) as $pi ) {
				if ( isset( $this->paddings[ $pi ] ) ) {
					$ci = $cellPaddingsPatch[ $pi ];
					$pv = $this->paddings[ $pi ];
					$res.= "\\clpad".$ci.ceil( unitConvert( $pv , "tw" ) )."\\clpadf".$ci."3" ;
				}
			}

			$res.= $this->currentBgColorIndex == "none" ? "\\clshdrawnil" : "\\clcbpat".$this->currentBgColorIndex ;
			$res.= "\\clvertal".$this->verticalAlign[ 0 ]."\\cltx".$this->textDirection."\\cellx".$pos ;


			$nl = $this->parentRow->parentTable->nestingLevel ;
			$lr = $this->parentRow->index + 1 == $this->parentRow->parentTable->getRowsCount();
			$res.= "\\intbl\\itap".$nl.$this->processDocElements().( ( $nl > 1 ) && $lr ? "\\nestcell" : "\\cell" );
			return $res ;
		}
	}

	class RTFDocumentTableRow extends baseExt {
		public $parentTable ;
		function __construct( $parent ) {
			$this->parentTable = $parent ;
		}

		public $index = false ;
		public $height = "0tw" ;
		public $isHeader = false ;

		private $cells = array();
		public function insertCell( $i = -1 ) {
			$c = new RTFDocumentTableCell( $this );
			if ( $i == -1 ) {
				$this->cells[]= $c ;
			} else {
				array_splice( $this->cells , $i , 0 , array( $c ) );
			}
			return $c ;
		}
		public function write() {
			$res = "\\trowd\\trgaph36".( $this->isHeader ? "\\trhdr" : "" );
			$pos = 0 ;
			foreach ( $this->cells as $cell ) {
				$res.= $cell->write( $pos );
			}
			$nl = $this->parentTable->nestingLevel ;
			$lr = $this->index + 1 == $this->parentTable->getRowsCount();
			$res.= "\\trkeep\\trrh".round( unitConvert( $this->height , "tw" ) ).( ( $nl > 1 ) && $lr ? "\\nestrow" : "\\row" );

			return $res ;
		}
	}

	class RTFDocumentTable extends baseExt {
		public $parentDoc ;
		public $nestingLevel ;
		function __construct( $parent , $level = 1 ) {
			$this->parentDoc = $parent ;
			$this->nestingLevel = $level ;
		}


		private $rows = array();

		/**
		 *
		 * Вставляет сроку в указанной позиции
		 * @param integer $i позиция
		 */
		public function insertRow( $i = -1 ) {
			$r = new RTFDocumentTableRow( $this );
			if ( $i == -1 ) {
				$r->index = count( $this->rows );
				$this->rows[]= $r ;
			} else {
				array_splice( $this->rows , $i , 0 , array( $r ) );
				for( $j = $i ; $j < count( $this->rows ) ; $j++ ) {
					$this->rows[ $j ]->index = $j ;
				}
			}

			if ( ( count( $this->rows ) > 1 ) && ( $r->index > 0 ) ) {
				$pr = $this->rows[ $r->index - 1 ];

			}


			return $r ;
		}

		/**
		 * Удаляет строку
		 * @param integer $i № удаляемой строки
		 */
		public function deleteRow( $i ) {
			if ( $i == -1 ) {
				array_pop( $this->rows );
			} else {
				array_splice( $this->rows , $i , 1 );
			}
		}

		public function getRowsCount() {
			return count( $this->rows );
		}

		public function write() {
			//$res = "" ;
			$res = "{" ;
			foreach ( $this->rows as $row ) {
				$res.= $row->write();
			}

			//return $res."\\pard" ;
			return $res."\\pard}" ;
		}
	}

	class RTFDocument extends baseExt {

		function __construct() {
			$this->context = array( "type" => "main" , "lnk" => & $this->mainDocElements );
		}

		private function showErrorNotice( $s ) {
			$bt = debug_backtrace();
			echo sprintf( "<b>Notice</b>: %s in <b>%s</b> on line <b>%s</b><br>" , $s , $bt[ 1 ][ "file" ] , $bt[ 1 ][ "line" ] ) ;
		}
		private function showFormatErrorNotice( $s ) {
			$bt = debug_backtrace();
			echo sprintf( "<b>Notice</b>: invalid %s format in <b>%s</b> on line <b>%s</b><br>" , $s , $bt[ 1 ][ "file" ] , $bt[ 1 ][ "line" ] ) ;
		}

		public $charSet = "cp1251" ;
		private $charSetMap = array ( "cp1251" => "ansicpg1251" );

		public $defFont = 0 ;
		public $defLang = LANGUAGE_CODE_RUSSIAN ;

		private $fontTab = array( FONT_CALIBRI );
		private $fontData = array(
			FONT_CALIBRI 			=> array( "family" => "fswiss" ),
			FONT_TIMES_NEW_ROMAN	=> array( "family" => "froman" )
		);

		private $colorTab = array( "000000" , "ffffff" );

		function getColorIndex( $v , $add = true ) {
			if ( in_array( $v , $this->colorTab ) ) {
				$ci = array_search( $v , $this->colorTab ) + 1 ;
			} else {
				if ( $add ) {
					$ci = count( $this->colorTab ) + 1 ;
					$this->colorTab[]= $v ;
				} else {
					$ci = false ;
				}
			}
			return $ci ;
		}

		private $listTab = array();
		private $listTabMap = array();
		private $listOverrideTab = array();

		//private $listTab = array( array( "id" => 10000 , "name" => "list2" , "levels" => array( array( "format" => "Глава \x01." , "startat" => 1 , "nfc" => 0 ) ) ) );
		private $lastListID = 10001 ;
		private $lastListOverrideID = 1 ;

		public function getListIndex( $name , $add = true ) {
			$name = trim( $name );
			if ( !isset( $this->listTab[ $name ] ) ) {
				if ( $add ) {
					$l = array( "id" => ( $this->lastListID ++ ) , "name" => $name , "levels" => array() );
					$this->listTab[ $name ] = &$l ;
					$this->listTabMap[ $l[ "id" ] ] = &$l ;
				} else {
					return false ;
				}
			} else {
				$l = &$this->listTab[ $name ];
			}

			return $l[ "id" ];
		}

		public function mkListLevel( $listID , $format , $startat = 1 , $nfc = 0 ) {
			if ( isset( $this->listTabMap[ $listID ] ) ) {
				$cl = &$this->listTabMap[ $listID ];
				$cll = array( "format" => $format , "startat" => $startat , "nfc" => $nfc );
				$cl[ "levels" ][]= &$cll ;
			} else {
				return false ;
			}
		}

		public function getListOverrideIndex( $listID ) {
			if ( isset( $this->listTabMap[ $listID ] ) ) {
				$l = $this->listTabMap[ $listID ];
				$ol = array(
					"list" => &$l ,
					"ovid" => ( $this->lastListOverrideID++ )
				);

				$this->listOverrideTab[ $ol[ "ovid" ] ]= &$ol ;
				return $ol[ "ovid" ];
			} else {
				return false ;
			}
		}




		public function setListItem( $listName ) {
			/*if ( in_array( $value , $this->fontTab ) ) {
				$fi = array_search( $value , $this->fontTab );
			} else {
				$fi = count( $this->fontTab );
				$this->fontTab[]= $value ;
			}

			$this->currentFontIndex = $fi ;
			$de = array(
				"eName" => "f" ,
				"pf" => "simple" ,
				"param" => $fi
			);
			$this->context[ "lnk" ][]= $de ;

			return $this ;*/
		}

		//private $whrLnk = null ;
		//private function inc

		private function writeHeader() {
			$res = "\\rtf1" ;

			if ( isset( $this->charSetMap[ $this->charSet ] ) ) {
				$res.= "\\".$this->charSetMap[ $this->charSet ];
			}

			$res.= "\\deff".$this->defFont ;
			$res.= "\\deflang".$this->defLang ;

			$res.= "\r\n" ;

			$res.= "{\\fonttbl" ;
			foreach ( $this->fontTab as $fi => $fn ) {
				$res.= "{\\f".$fi ;
				if ( isset( $this->fontData[ $fn ] ) ) {
					$res.= "\\".$this->fontData[ $fn ][ "family" ];
				} else {
					$res.= "\\fnil" ;
				}
				$res.= "\\fcharset204 ".$fn.";}" ;
			}
			$res.= "}" ;
			$res.= "\r\n" ;

			$res.= "{\\colortbl;" ;
			foreach ( $this->colorTab as $color ) {
				$rgb = unpack( "C1r/C1g/C1b" , pack( "H*" , $color ) );
				$res.= "\\red".$rgb[ "r" ]."\\green".$rgb[ "g" ]."\\blue".$rgb[ "b" ].";" ;
			}
			$res.= "}" ;
			$res.= "\r\n" ;

			//var_dump( $this->listTab );

			$res.= "{\\*\\listtable" ;
			foreach ( $this->listTab as $list ) {
				$res.="\r\n" ;
				$res.="{\\list\\listtemplateid-1\\listsimple".count( $list[ "levels" ] );
				foreach( $list[ "levels" ] as $listLevel ) {
					$fs = $listLevel[ "format" ];
					$fsc = "\\'".sprintf( "%02x" , strlen( $fs ) );
					$fsm = $fs ;
					$fsp = array();
					for( $i = 0 ; $i < 10 ; $i++ ) {
						$fsm = str_replace( chr( $i ) , "\\'".sprintf( "%02x" , $i ) , $fsm );
						$lp = strpos( $fs , chr( $i ) );
						while( $lp !== false ) {
							$fsp[]= "\\'".sprintf( "%02x" , $lp + 1 );
							$lp = strpos( $fs , chr( $i ) , $lp + 1 );
						}
					}

					$res.="\r\n" ;
					$res.= "{\\listlevel\\levelnfc".$listLevel[ "nfc" ]."\\leveljc0\\levelfollow1\\levelstartat".$listLevel[ "startat" ].
						"\r\n"."{\\leveltext ".$fsc.$fsm.";}".
						"\r\n"."{\levelnumbers ".implode( $fsp ).";}".
						"\r\n"."}" ;
				}
				$res.="\r\n" ;
				$res.= "\\listrestarthdn0\\listid".$list[ "id" ]."{\\listname ".$list[ "name" ]."}}" ;
			}
			$res.= "\r\n" ;
			$res.= "}" ;
			$res.= "\r\n" ;

			$res.= "{\\*\\listoverridetable" ;
			foreach( $this->listOverrideTab as $ovList ) {
				$res.= "{\\listoverride\\listid".$ovList[ "list" ][ "id" ]."\\listoverridecount0\\ls".$ovList[ "ovid" ]."}" ;
			}
			$res.= "}" ;
			$res.= "\r\n" ;

			return $res ;
		}

		private $headerFPDocElemnts = array();
		private $headerDocElemnts = array();
		private $footerFPDocElemnts = array();
		private $footerDocElemnts = array();
		private $mainDocElements = array();

		private $context ;

		private $headerMargin = "12.5mm" ;
		private $footerMargin = "12.5mm" ;

		private $storedContext = false ;
		private function saveContext() {
			$this->storedContext = array( "type" => $this->context[ "type" ] , "lnk" => &$this->context[ "lnk" ] );
		}
		private function restoreContext() {
			$this->context[ "type" ] = $this->storedContext[ "type" ];
			unset( $this->context[ "lnk" ] );
			$this->context[ "lnk" ] = & $this->storedContext[ "lnk" ];
			unset( $this->storedContext[ "lnk" ] );
			$this->storedContext = false ;
		}

		public function setHeaderFPContext() {
			$this->context[ "type" ] = "headerFP" ;
			unset( $this->context[ "lnk" ] );
			$this->context[ "lnk" ] = & $this->headerFPDocElemnts ;
			return $this ;
		}
		public function setHeaderContext() {
			$this->context[ "type" ] = "header" ;
			unset( $this->context[ "lnk" ] );
			$this->context[ "lnk" ] = & $this->headerDocElemnts ;
			return $this ;
		}
		public function setHeaderMargin( $v ) {
			if ( !numUnitCheck( $v ) ) {
				$this->showFormatErrorNotice( "header margin" );
				return $this ;
			}
			$this->headerMargin = $v ;
			return $this ;
		}
		public function setFooterFPContext() {
			$this->context[ "type" ] = "footerFP" ;
			unset( $this->context[ "lnk" ] );
			$this->context[ "lnk" ] = & $this->footerFPDocElemnts ;
			return $this ;
		}
		public function setFooterContext() {
			$this->context[ "type" ] = "footer" ;
			unset( $this->context[ "lnk" ] );
			$this->context[ "lnk" ] = & $this->footerDocElemnts ;
			return $this ;
		}
		public function setFooterMargin( $v ) {
			if ( !numUnitCheck( $v ) ) {
				$this->showFormatErrorNotice( "footer margin" );
				return $this ;
			}
			$this->footerMargin = $v ;
			return $this ;
		}
		public function setMainContext() {
			$this->context[ "type" ] = "main" ;
			unset( $this->context[ "lnk" ] );
			$this->context[ "lnk" ] = & $this->mainDocElements ;
			return $this ;
		}

		/**
		 *
		 * Enter description here ...
		 * @param RTFDocumentTableCell $cell
		 */
		public function setTableCellContext( $cell ) {
			$this->context[ "type" ] = "table" ;
			unset( $this->context[ "lnk" ] );
			$this->context[ "lnk" ] = & $cell->docElements ;
			$this->context[ "obj" ] = & $cell ;
			return $this ;
		}

		public $paperFormat = PAPER_SIZE_A4_PORTRAIT ;
		private $paperFormatsTab = array (
			PAPER_SIZE_A3_PORTRAIT	=> array( "w" => "297mm" , "h" => "420mm" , "t" =>  "8" ) ,
			PAPER_SIZE_A4_PORTRAIT	=> array( "w" => "210mm" , "h" => "297mm" , "t" =>  "9" ) ,
			PAPER_SIZE_A5_PORTRAIT	=> array( "w" => "148mm" , "h" => "210mm" , "t" =>  "11" ) ,

			PAPER_SIZE_A3_LANDSCAPE => array( "w" => "420mm" , "h" => "297mm" , "t" =>  "8" , "l" => true ) ,
			PAPER_SIZE_A4_LANDSCAPE	=> array( "w" => "297mm" , "h" => "210mm" , "t" =>  "9" , "l" => true ) ,
			PAPER_SIZE_A5_LANDSCAPE	=> array( "w" => "210mm" , "h" => "148mm" , "t" =>  "11" , "l" => true ) ,

			PAPER_SIZE_C3_PORTRAIT  => array( "w" => "324mm" , "h" => "458mm" , "t" => "29" ) ,
			PAPER_SIZE_C4_PORTRAIT  => array( "w" => "229mm" , "h" => "324mm" , "t" => "30" ) ,
			PAPER_SIZE_C5_PORTRAIT  => array( "w" => "162mm" , "h" => "229mm" , "t" => "28" ) ,
		);


		private $margins = array( "l" => "30mm" , "t" => "20mm" , "r" => "15mm" , "b" => "20mm" );
		public function setMargins( $v ) {
			$v = preg_replace( "(\\s+)" , " " , trim( $v ) );
			$n = preg_match( "/^\\d+(\\.\\d+)?(mm|cm|in|pt)(\\s\\d+(\\.\\d+)?(mm|cm|in|pt)){0,3}$/" , $v );
			if ( $n != 1 ) {
				$this->showFormatErrorNotice( "margins" );
				return $this ;
			}

			$v = explode( " " , $v );
			switch ( count( $v ) ) {
				case 1 :
					$this->margins = array( "l" => $v[ 0 ] , "t" => $v[ 0 ] , "r" => $v[ 0 ] , "b" => $v[ 0 ] );
					break ;
				case 2 :
					$this->margins = array( "l" => $v[ 1 ] , "t" => $v[ 0 ] , "r" => $v[ 1 ] , "b" => $v[ 0 ] );
					break ;
				case 3 :
					$this->margins = array( "l" => $v[ 1 ] , "t" => $v[ 0 ] , "r" => $v[ 1 ] , "b" => $v[ 2 ] );
					break ;
				case 4 :
					$this->margins = array( "l" => $v[ 3 ] , "t" => $v[ 0 ] , "r" => $v[ 1 ] , "b" => $v[ 2 ] );
					break ;
			}

			return $this ;
		}
		public function getMargins() {
			return implode( " " , $this->margins );
		}

		private $currentColorIndex = 1 ;

		public function addColor( $v ) {
			$v = trim( $v );
			$n = preg_match( "/^#?(([0-9a-f]{3}){1,2})$/i" , $v );
			if ( $n != 1 ) {
				$this->showFormatErrorNotice( "color" );
				return $this ;
			}

			if ( $v[ 0 ] == "#" ) {
				$v = substr( $v , 1 );
			}

			if ( strlen( $v ) == 3 ) {
				$v = $v[ 0 ].$v[ 0 ].$v[ 1 ].$v[ 1 ].$v[ 2 ].$v[ 2 ] ;
			}

			if ( in_array( $v , $this->colorTab ) ) {
				$ci = array_search( $v , $this->colorTab ) + 1 ;
			} else {
				$this->colorTab[]= $v ;
				$ci = count( $this->colorTab );
			}

			return $ci ;
		}

		public function setTextColor( $v ) {
			$v = trim( $v );
			$n = preg_match( "/^#?(([0-9a-f]{3}){1,2})$/i" , $v );
			if ( $n != 1 ) {
				$this->showFormatErrorNotice( "color" );
				return $this ;
			}

			if ( $v[ 0 ] == "#" ) {
				$v = substr( $v , 1 );
			}

			if ( strlen( $v ) == 3 ) {
				$v = $v[ 0 ].$v[ 0 ].$v[ 1 ].$v[ 1 ].$v[ 2 ].$v[ 2 ];
			}

			if ( in_array( $v , $this->colorTab ) ) {
				$ci = array_search( $v , $this->colorTab ) + 1 ;
			} else {
				$ci = count( $this->colorTab ) + 1 ;
				$this->colorTab[]= $v ;
			}

			$this->currentColorIndex = $ci ;
			$de = array(
				"eName" => "cf" ,
				"pf" => "simple" ,
				"param" => $ci
			);
			$this->context[ "lnk" ][]= $de ;

			return $this ;
		}
		public function getTextColor() {
			return $this->colorTab[ $this->currentColorIndex - 1 ];
		}

		private $currentBgColorIndex = "none" ;
		public function setBgColor( $v = "none" ) {
			$v = trim( $v );

			if ( $v == "none" ) {
				$this->currentBgColorIndex = "none" ;
				$de = array(
					"eName" => "chcbpat" ,
					"pf" => "simple" ,
					"param" => 0
				);
			} else {
				$n = preg_match( "/^#?(([0-9a-f]{3}){1,2})$/i" , $v );
				if ( $n != 1 ) {
					$this->showFormatErrorNotice( "bgColor" );
					return $this ;
				}

				if ( $v[ 0 ] == "#" ) {
					$v = substr( $v , 1 );
				}

				if ( strlen( $v ) == 3 ) {
					$v = $v[ 0 ].$v[ 0 ].$v[ 1 ].$v[ 1 ].$v[ 2 ].$v[ 2 ];
				}

				if ( in_array( $v , $this->colorTab ) ) {
					$ci = array_search( $v , $this->colorTab ) + 1 ;
				} else {
					$ci = count( $this->colorTab ) + 1 ;
					$this->colorTab[]= $v ;
				}

				$this->currentBgColorIndex = $ci ;
				$de = array(
					"eName" => "chcbpat" ,
					"pf" => "simple" ,
					"param" => $ci
				);
			}

			$this->context[ "lnk" ][]= $de ;
			return $this ;
		}
		public function getBgColor() {
			if ( $this->currentBgColorIndex == "none" ) {
				return $this->currentBgColorIndex ;
			} else {
				return $this->colorTab[ $this->currentBgColorIndex - 1 ];
			}
		}

		private $currentHighlightColorIndex = "none" ;
		public function setHighlight( $v = "none" ) {
			$v = trim( $v );

			if ( $v == "none" ) {
				$this->currentHighlightColorIndex = "none" ;
				$de = array(
					"eName" => "highlight" ,
					"pf" => "simple" ,
					"param" => 0
				);
			} else {
				$n = preg_match( "/^#?(([0-9a-f]{3}){1,2})$/i" , $v );
				if ( $n != 1 ) {
					$this->showFormatErrorNotice( "highlight" );
					return $this ;
				}

				if ( $v[ 0 ] == "#" ) {
					$v = substr( $v , 1 );
				}

				if ( strlen( $v ) == 3 ) {
					$v = $v[ 0 ].$v[ 0 ].$v[ 1 ].$v[ 1 ].$v[ 2 ].$v[ 2 ];
				}

				if ( in_array( $v , $this->colorTab ) ) {
					$ci = array_search( $v , $this->colorTab ) + 1 ;
				} else {
					$ci = count( $this->colorTab ) + 1 ;
					$this->colorTab[]= $v ;
				}

				$this->currentHighlightColorIndex = $ci ;
				$de = array(
					"eName" => "highlight" ,
					"pf" => "simple" ,
					"param" => $ci
				);
			}

			$this->context[ "lnk" ][]= $de ;
			return $this ;
		}
		public function getHighlight() {
			if ( $this->currentHighlightColorIndex == "none" ) {
				return $this->currentHighlightColorIndex ;
			} else {
				return $this->colorTab[ $this->currentHighlightColorIndex - 1 ];
			}
		}

		private $currentFontSize = "12pt" ;
		public function setFontSize( $fs ) {
			if ( !numUnitCheck( $fs ) ) {
				$this->showFormatErrorNotice( "font size" );
				return $this ;
			}

			$this->currentFontSize = $fs ;
			$fs = round( 2 * unitConvert( $fs ,	"pt" ) );

			$de = array(
				"eName" => "fs" ,
				"pf" => "simple" ,
				"param" => $fs
			);
			$this->context[ "lnk" ][]= $de ;

			return $this ;
		}
		public function getFontSize() {
			return $this->currentFontSize ;
		}


		private $currentFontIndex = 0 ;
		public function getFontName() {
			return $this->fontTab[ $this->currentFontIndex ];
		}
		public function setFontName( $value ) {
			if ( in_array( $value , $this->fontTab ) ) {
				$fi = array_search( $value , $this->fontTab );
			} else {
				$fi = count( $this->fontTab );
				$this->fontTab[]= $value ;
			}

			$this->currentFontIndex = $fi ;
			$de = array(
				"eName" => "f" ,
				"pf" => "simple" ,
				"param" => $fi
			);
			$this->context[ "lnk" ][]= $de ;

			return $this ;
		}

		private $currentFirstLineIndent = "0cm" ;
		public function getFirstLineIndent() {
			return $this->currentFirstLineIndent ;
		}
		public function setFirstLineIndent( $v = 0 ) {
			if ( $v !== 0 ) {
				$this->currentFirstLineIndent = $v ;
				$v = ceil( unitConvert( $v , "tw" ) );
			} else {
				$this->currentFirstLineIndent = "0cm" ;
			}
			$de = array(
				"eName" => "fi" ,
				"pf" => "simple" ,
				"param" => $v
			);

			$this->context[ "lnk" ][]= $de ;

			return $this ;
		}

		/* paragraph space before and after  */
		private $currentParSpace = array( "b" => "0cm" , "a" => "0cm" );
		public function getParSpace() {
			return $this->$currentParSpace ;
		}
		public function setParSpace( $v = "0cm" ) {
			$v = preg_replace( "(\\s+)" , " " , trim( $v ) );
			$n = preg_match( '/^\d+(\.\d+)?(mm|cm|in|pt)(\s+\d+(\.\d+)?(mm|cm|in|pt))?$/i' , $v );
			if ( $n != 1 ) {
				$this->showFormatErrorNotice( "parSpace" );
				return $this ;
			}

			$v = explode( " " , $v );

			switch ( count( $v ) ) {
				case 1 :
					$v[ 1 ] = $v[ 0 ];
					break ;
				case 2 :
					break ;
				default :
					$this->showFormatErrorNotice( "parSpace" );
					break ;
			}

			$this->currentParSpace = array( "b" => $v[ 0 ] , "a" => $v[ 1 ] );
			foreach ( $v as &$cv ) {
				$cv = unitConvert( $cv , "tw" );
			} unset( $cv );

			$de = array(
				"eName" => "sb" ,
				"pf" => "simple" ,
				"param" => $v[ 0 ]
			);
			$this->context[ "lnk" ][]= $de ;

			$de = array(
				"eName" => "sa" ,
				"pf" => "simple" ,
				"param" => $v[ 1 ]
			);
			$this->context[ "lnk" ][]= $de ;

			return $this ;
		}

		private $currentTextAlign = TEXT_ALIGN_LEFT ;
		public function getTextAlign() {
			return $this->currentTextAlign ;
		}
		public function setTextAlign( $value ) {
			$de = array(
				"eName" => "q" ,
				"pf" => "simple"
			);

			switch( $value ) {
				case TEXT_ALIGN_LEFT :
				case TEXT_ALIGN_RIGHT :
				case TEXT_ALIGN_CENTER :
				case TEXT_ALIGN_JUSTIFIED :
					$de[ "param" ] = $value[ 0 ];
					break ;

				default :
					$this->showErrorNotice( "invalid text alignment" );
					return $this ;
			}

			$this->currentTextAlign = $value ;
			$this->context[ "lnk" ][]= $de ;

			return $this ;
		}

		public function addTextLine( $s = "" , $par = "\r\n" ) {
			$this->addText( $s.$par , $par );
			return $this ;
		}
		public function addText( $s , $par = "\r\n" ) {
			if ( $this->context[ "type" ] == "table" ) {
				$npar = "\\par\\intbl\\itap".$this->context[ "obj" ]->parentRow->parentTable->nestingLevel." " ;
			} else {
				$npar = "\\par " ;
			}

			$s = str_replace( "\\" , "\\\\" , $s );
			$s = str_replace( "{" , "\\{" , $s );
			$s = str_replace( "}" , "\\}" , $s );
			$s = str_replace( $par , $npar , $s );
			$s = str_replace( "\t" , "\\tab" , $s );

			$sc = str_split( "№" );
			$scc = $sc ;
			foreach ( $scc as &$cc ) {
				$cc = "\\'".bin2hex( $cc );
			} unset( $cc );

			$s = str_replace( $sc , $scc , $s );

			$le = end( $this->context[ "lnk" ] );
			if ( $le[ "eName" ] != "text" ) {
				$de = array(
					"eName" => "text" ,
					"pf" => "text" ,
					"param" => " ".$s
				);
				$this->context[ "lnk" ][]= $de ;
			} else {
				$lei = count( $this->context[ "lnk" ] ) - 1 ;
				$this->context[ "lnk" ][ $lei ][ "param" ].= $s ;
			}

			return $this ;
		}

		public function addTag( $tag , $dim = null , $unit = "tw" ) {
			if ( $dim === null ) {
				$de = array(
					"eName" => $tag ,
					"pf" => "simple"
				);
			} else {
				$de = array(
					"eName" => $tag ,
					"pf" => "simple" ,
					"param" => ceil( unitConvert( $dim , $unit ) )
				);
			}
			$this->context[ "lnk" ][]= $de ;
			return $this ;
		}

		public function addRaw( $data ) {
			$de = array(
				"eName" => "raw" ,
				"pf" => "raw" ,
				"param" => $data
			);
			$this->context[ "lnk" ][]= $de ;
			return $this ;
		}

		public function addTable() {
			if ( $this->context[ "type" ] == "table" ) {
				//$this->showErrorNotice( "invalid context" );
				//return false ;
				//print_r_html( $this->context , 1 );
				$nl = $this->context[ "obj" ]->parentRow->parentTable->nestingLevel ;
				//print_r_html( $this->context[ "obj" ]->parentRow->parentTable , 1 );
			} else {
				$nl = 0 ;
			}


			$de = array(
				"eName" => "table" ,
				"pf" => "table" ,
				"param" => new RTFDocumentTable( $this ,$nl + 1 )
			);

			$this->context[ "lnk" ][]= $de ;
			return $de[ "param" ];
		}

		public function addImagePNG( $raw , $wp , $hp , $wd = false , $hd = false ) {
			$de = array(
				"eName" => "picture" ,
				"pf" => "picture" ,
				"param" => array(
					"type" => "pngblip" ,
					"wp" => $wp ,
					"hp" => $hp ,
					"wd" => $wd ,
					"hd" => $hd ,
					"raw" => $raw
				)
			);
			$this->context[ "lnk" ][]= $de ;
			return $this ;
		}

		public function addParagraphBorders( $borders , $type = "s" , $color = "#000000" , $width = false , $spacing = false ) {
			foreach ( str_split( $borders ) as $bi ) {
				$de = array(
					"eName" => "brdr".$bi ,
					"pf" => "simple"
				);
				$this->context[ "lnk" ][]= $de ;
				$de = array(
					"eName" => "brdr" ,
					"pf" => "simple" ,
					"param" => $type
				);
				$this->context[ "lnk" ][]= $de ;

				if ( $type !== "none" ) {
					$de = array(
						"eName" => "brdrcf" ,
						"pf" => "simple" ,
						"param" => $this->addColor( $color )
					);
					$this->context[ "lnk" ][]= $de ;
				}

				if ( $width !== false ) {
					$de = array(
						"eName" => "brdrw" ,
						"pf" => "simple" ,
						"param" => min( array( ceil( unitConvert( $width , "tw" ) ) , 255 ) )
					);
					$this->context[ "lnk" ][]= $de ;
				}

				if ( $spacing !== false ) {
					$de = array(
						"eName" => "brsp" ,
						"pf" => "simple" ,
						"param" => ceil( unitConvert( $spacing , "tw" ) )
					);
					$this->context[ "lnk" ][]= $de ;
				}
			}
			return $this ;
		}

		private function processDocElements() {
			$res = "" ;
			foreach ( $this->context[ "lnk" ] as & $de ) {
				$pf = "processDocElements_".$de[ "pf" ];
				$res.= $this->$pf( $de );
			} unset( $de );

			return $res ;
		}

		private function processDocElements_raw( $e ) {
			return $e[ "param" ];
		}

		private function processDocElements_simple( $e ) {
			return "\\".$e[ "eName" ].( isset( $e[ "param" ] ) ? $e[ "param" ] : "" );
		}

		private function processDocElements_text( $e ) {
			return $e[ "param" ];
		}

		private function processDocElements_table( $e ) {
			return $e[ "param" ]->write()."" ;
		}

		private function processDocElements_picture( $e ) {
			switch( $e[ "param" ][ "type" ] ) {
				case "pngblip" :
					$wd = ( $e[ "param" ][ "wd" ] === false ? ceil( unitConvert( $e[ "param" ][ "wp" ]."px" , "tw" ) ) : ceil( unitConvert( $e[ "param" ][ "wd" ] , "tw" ) ) );
					$hd = ( $e[ "param" ][ "hd" ] === false ? ceil( unitConvert( $e[ "param" ][ "hp" ]."px" , "tw" ) ) : ceil( unitConvert( $e[ "param" ][ "hd" ] , "tw" ) ) );
					$wp = ceil( unitConvert( $e[ "param" ][ "wp" ]."px" , "mm" ) * 100 );
					$hp = ceil( unitConvert( $e[ "param" ][ "hp" ]."px" , "mm" ) * 100 );
					break ;
			}
			return "{\\pict\\".$e[ "param" ][ "type" ]."\\picw".$wp."\\pich".$hp."\\picwgoal".$wd."\\pichgoal".$hd." ".bin2hex( $e[ "param" ][ "raw" ] )."}" ;  //$e[ "param" ]->write()."" ;
		}

		public function write() {
			$res = "{".$this->writeHeader();
			$res.= "\\doctype0" ;
			if ( isset( $this->paperFormatsTab[ $this->paperFormat ] ) ) {
				$res.= "\\paperw".ceil( unitConvert( $this->paperFormatsTab[ $this->paperFormat ][ "w" ] , "tw" ) )."\\paperh".ceil( unitConvert( $this->paperFormatsTab[ $this->paperFormat ][ "h" ] , "tw" ) )."\\psz".$this->paperFormatsTab[ $this->paperFormat ][ "t" ].( isset( $this->paperFormatsTab[ $this->paperFormat ][ "l" ] ) && $this->paperFormatsTab[ $this->paperFormat ][ "l" ] ? "\\landscape" : '' );
			}

			foreach ( $this->margins as $n => $v ) {
				 $res.= "\\marg".$n.ceil( unitConvert( $v , "tw" ) );
			}

			$res.= "\\headery".ceil( unitConvert( $this->headerMargin , "tw" ) )."\\footery".ceil( unitConvert( $this->footerMargin , "tw" ) );

			if ( count( $this->headerFPDocElemnts ) > 0 || count( $this->footerFPDocElemnts ) > 0 ) {
				$res.= "\\titlepg" ;
			}

			$this->saveContext();
			if ( count( $this->headerFPDocElemnts ) > 0 ) {
				$this->setHeaderFPContext();
				$res.= "{\\headerf".$this->processDocElements()."}" ;
			}
			if ( count( $this->headerDocElemnts ) > 0 ) {
				$this->setHeaderContext();
				$res.= "{\\header".$this->processDocElements()."}" ;
			}
			if ( count( $this->footerFPDocElemnts ) > 0 ) {
				$this->setFooterFPContext();
				$res.= "{\\footerf".$this->processDocElements()."}" ;
			}
			if ( count( $this->footerDocElemnts ) > 0 ) {
				$this->setFooterContext();
				$res.= "{\\footer".$this->processDocElements()."}" ;
			}
			$this->setMainContext();
			$res.= $this->processDocElements()."}" ;
			$this->restoreContext();

			echo $res ;

			return ;
		}

	}
?>