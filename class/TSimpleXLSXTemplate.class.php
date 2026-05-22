<?php
	
	class TSimpleXLSXTemplate {
		const STR__TEMP_FILE_PREFIX = 'XLSX' ;
		const STR__CONTENT_TYPE_XML = '[Content_Types].xml' ;
		const FIELD__ContentType = 'ContentType' ;
		const FIELD__Extension = 'Extension' ;
		const FIELD__Type = 'Type' ;
		const FIELD__Target = 'Target' ;
		const FIELD__Id = 'Id' ;
		const FIELD__id = 'id' ;
		const FIELD__name = 'name' ;
		const FIELD__zipFileName = 'zipFileName' ;
		const FIELD__xml = 'xml' ;
		const FIELD__changed = 'changed' ;
		const FIELD__rowsMap = 'rowsMap' ;
		const FIELD__cellsMap = 'cellsMap' ;
		const FIELD__colsStyle = 'colsStyle' ;
		const FIELD__mergeCells = 'mergeCells' ;
		const FIELD__maxRowIndex = 'maxRowIndex' ;
		const FIELD__maxColIndex = 'maxColIndex' ;
		const FIELD__stylesData = 'stylesData' ;
		const CONTENT_TYPE__relationships_xml = 'application/vnd.openxmlformats-package.relationships+xml' ;
		
		const REL_TYPE__officeDocument = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument' ;
		const REL_TYPE__worksheet = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet' ;
		const REL_TYPE__sharedStrings = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings' ;
		const REL_TYPE__styles = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles' ;
		
		private $tempFileName = false ;
		private $zip = false ;
		
		private $zipNameContentType = self::STR__CONTENT_TYPE_XML ;
		private $xmlContentType = false ;
		private $xmlContentTypeChanged = false ;
		
		private $defExtensionMap = false ;
		private $relsExtension = false ;
		
		private $zipNameWorkbook = false ;
		private $xmlWorkbook = false ;
		private $xmlWorkbookChanged = false ;
		
		private $zipNameWorksheets = false ;
		private $sheets = false ;
		
		private $zipNameSharedStrings = false ;
		private $xmlSharedStrings = false ;
		private $xmlSharedStringsChanged = false ;
		private $sharedStringsMap = false ;

		private $zipNameStyles = false ;
		private $xmlStyles = false ;
		private $stylesData = false ;

		private $currentSheetExData = false ;
		private $currentSheetRowMap = false ;

		public $colIndexes = array();
		public $colNames = array();
		
		
		private function ldXML( $name ) {
			$index = $this->zip->locateName( $name );
			$data = $this->zip->getFromIndex( $index );
			return simplexml_load_string( $data );
		}
		
		private function ldDOM( $name ) {
			$index = $this->zip->locateName( $name );
			$data = $this->zip->getFromIndex( $index );
			$res = new DOMDocument();
			$res->loadXML( $data );
			return $res ;
		}
		
		private function ldXMLi( $index ) {
			$data = $this->zip->getFromIndex( $index );
			return simplexml_load_string( $data );
		}
		private function ldDOMi( $index ) {
			$data = $this->zip->getFromIndex( $index );
			$res = new DOMDocument();
			$res->loadXML( $data );
			return $res ;
		}
		
		private function strEndsWith( $str , $ends ) {
			return substr( $str , -strlen( $ends ) ) == $ends ;
		}
		
		private function xmlFormat( $str ) {
			$d = new DOMDocument();
			$d->preserveWhiteSpace = false ;
			$d->formatOutput = true ;
			$d->loadXML( $str );
			return $d->saveXML();
		}
		
		function __construct( $templatePath ) {
			
			$this->tempFileName = tempnam( sys_get_temp_dir() , self::STR__TEMP_FILE_PREFIX );
			copy( $templatePath , $this->tempFileName );
			
			$this->zip = new ZipArchive ;
			if ( $this->zip->open( $this->tempFileName ) === true ) {
				$this->xmlContentType = $this->ldXML( $this->zipNameContentType );
				
				$this->defExtensionMap = array();
				foreach( $this->xmlContentType->Default as $dData ) {
					$ct = (string)$dData[ self::FIELD__ContentType ];
					$this->defExtensionMap[ $ct ] = (string)$dData[ self::FIELD__Extension ];
				}
				$this->relsExtension = '.'.$this->defExtensionMap[ self::CONTENT_TYPE__relationships_xml ];
				
				$allRelsFiles = array();
				$filesCount = $this->zip->numFiles ;
				for( $fileIndex = 0 ; $fileIndex < $filesCount ; $fileIndex++ ) {
					$name = $this->zip->getNameIndex( $fileIndex );
					if ( $this->strEndsWith( $name , $this->relsExtension ) ) {
						$allRelsFiles[ $name ] = $this->ldXMLi( $fileIndex );
					}
				}
				//error_log_ml( print_r( $allRelsFiles , 1 ) );
				$this->zipNameWorksheets = array();
				
				foreach( $allRelsFiles as $crfn => &$crf ) {
					foreach( $crf->Relationship as $cr ) {
						switch( (string)$cr[ self::FIELD__Type ] ) {
							case self::REL_TYPE__officeDocument :
								$this->zipNameWorkbook = (string)$cr[ self::FIELD__Target ];
								break ;
							
							case self::REL_TYPE__worksheet :
								$this->zipNameWorksheets[ (string)$cr[ self::FIELD__Id ] ]= (string)$cr[ self::FIELD__Target ];
								break ;
							
							case self::REL_TYPE__sharedStrings :
								$this->zipNameSharedStrings = (string)$cr[ self::FIELD__Target ];
								break ;
							case self::REL_TYPE__styles :
								$this->zipNameStyles = (string)$cr[ self::FIELD__Target ];
								break ;
						}
						
					}
				} unset( $crf );
				
				for( $fileIndex = 0 ; $fileIndex < $filesCount ; $fileIndex++ ) {
					$name = $this->zip->getNameIndex( $fileIndex );
					if ( $this->strEndsWith( $name , $this->zipNameWorkbook ) ) {
						$this->xmlWorkbook = $this->ldXMLi( $fileIndex );
						$this->zipNameWorkbook = $name ;
					} else
					if ( $this->strEndsWith( $name , $this->zipNameSharedStrings ) ) {
						$this->xmlSharedStrings = $this->ldDOMi( $fileIndex );
						$this->zipNameSharedStrings = $name ;
					} else
					if ( $this->strEndsWith( $name , $this->zipNameStyles ) ) {
						$this->xmlStyles = $this->ldDOMi( $fileIndex );
						$this->zipNameStyles = $name ;
					} else {
						foreach( $this->zipNameWorksheets as &$cwn ) {
							if ( $this->strEndsWith( $name , $cwn ) ) {
								$cwn = $name ;
							}
						} unset( $cwn );
					}
				}
				
				$ssIndex = 0 ;
				$this->sharedStringsMap = array();
				foreach( $this->xmlSharedStrings->documentElement->childNodes as $cn1 ) {
					if ( $cn1->nodeType == XML_ELEMENT_NODE && $cn1->nodeName == 'si' ) {
						$txt = array();
						foreach( $cn1->childNodes as $cn2 ) {
							if ( $cn2->nodeType == XML_ELEMENT_NODE ) {
								switch( $cn2->nodeName ) {
									case 't' :
										$txt[]= $cn2->nodeValue ;
										break ;
										
									case 'r' :
										foreach( $cn2->childNodes as $cn3 ) {
											if ( $cn3->nodeType == XML_ELEMENT_NODE && $cn3->nodeName == 't' ) {
												$txt[]= $cn3->nodeValue ;
											}
										}
										break ;
								}
							}
						}
						$this->sharedStringsMap[ $ssIndex ] = array(
							'node' => $cn1 ,
							'links' => array() ,
							'index' => $ssIndex ,
							'txt' => iconv( 'utf8' , 'cp1251' , implode( $txt ) )
						);
						$ssIndex++ ;
					}
				}

				$this->stylesData = array_fill_keys(
					array(
						'numFmts' , 'fonts' , 'fills' ,
						'borders' , 'cellStyleXfs' , 'cellXfs' ,
						'cellStyle' , 'dxfs' , 'tableStyles' ,
						'extLst'
					) ,
					array()
				);
				foreach( $this->xmlStyles->documentElement->childNodes as $cn1 ) {
					if ( $cn1->nodeType == XML_ELEMENT_NODE ) {
						switch ( $cn1->nodeName ) {
							case 'numFmts' :
								break ;

							case 'fonts' :
								$sdFonts = array();
								foreach( $cn1->childNodes as $cn2 ) {
									if ( $cn2->nodeType == XML_ELEMENT_NODE && $cn2->nodeName == 'font' ) {
										$cFont = array();
										foreach( $cn2->childNodes as $cn3 ) {
											if ( $cn3->nodeType == XML_ELEMENT_NODE ) {
												switch ( $cn3->nodeName ) {
													case 'sz' :
														$cFont[ 'size' ] = $cn3->getAttribute( 'val' );
														break ;
													case 'name' :
														$cFont[ 'name' ] = $cn3->getAttribute( 'val' );
														break ;
													case 'b' :
														$cFont[ 'bold' ] = true ;
														break ;
													case 'u' :
														$cFont[ 'underline' ] = true ;
														break ;
													case 'i' :
														$cFont[ 'italic' ] = true ;
														break ;
												}
											}
										}
										$sdFonts[]= $cFont ;
									}
								}
								$this->stylesData[ 'fonts' ] = $sdFonts ;
								break ;

							case 'fills' :
								break ;
							case 'borders' :
								$sdBorders = array();
								foreach( $cn1->childNodes as $cn2 ) {
									if ( $cn2->nodeType == XML_ELEMENT_NODE && $cn2->nodeName == 'border' ) {
										$cBrdr = array();
										foreach( $cn2->childNodes as $cn3 ) {
											if ( $cn3->nodeType == XML_ELEMENT_NODE ) {
												switch ( $cn3->nodeName ) {
													case 'left' :
													case 'top' :
													case 'right' :
													case 'bottom' :
														$cBrdr[ $cn3->nodeName ] = $cn3->hasAttribute( 'style' ) ? $cn3->getAttribute( 'style' ) : false ;
														break ;
												}
											}
										}
										$sdBorders[]= $cBrdr ;
									}
								}
								$this->stylesData[ 'borders' ] = $sdBorders ;
								break ;
							case 'cellStyleXfs' :
								break ;
							case 'cellXfs' :
								$sdCellXfs = array();
								foreach( $cn1->childNodes as $cn2 ) {
									if ( $cn2->nodeType == XML_ELEMENT_NODE && $cn2->nodeName == 'xf' ) {
										$cCellXfs = array(
											'numFmtId' => $cn2->getAttribute( 'numFmtId' ) ,
											'fontId'   => $cn2->getAttribute( 'fontId' ) ,
											'borderId'   => $cn2->getAttribute( 'borderId' ) ,
										);
										foreach( $cn2->childNodes as $cn3 ) {
											if ( $cn3->nodeType == XML_ELEMENT_NODE ) {
												switch( $cn3->nodeName ) {
													case 'alignment' :
														$cCellXfs[ 'alignment' ] = array(
															'hor'  => $cn3->hasAttribute( 'horizontal' ) ? $cn3->getAttribute( 'horizontal' ) : 'left' ,
															'ver'  => $cn3->hasAttribute( 'vertical' ) ? $cn3->getAttribute( 'vertical' ) : 'top' ,
															'wrap' => $cn3->hasAttribute( 'wrapText' ) && $cn3->hasAttribute( 'wrapText' ) == 1 ? 1 : 0 ,
															'rot'  => $cn3->hasAttribute( 'textRotation' ) ? $cn3->hasAttribute( 'textRotation' ) : 0 ,
														);
														break ;
												}
											}
										}
										$sdCellXfs[]= $cCellXfs ;
									}
								}
								$this->stylesData[ 'cellXfs' ] = $sdCellXfs ;

								break ;
							case 'cellStyle' :
								break ;
							case 'dxfs' :
								break ;
							case 'tableStyles' :
								break ;
							case 'extLst' :
								break ;
						}
					}
				}

				error_log_ml( print_r( $this->stylesData , 1 ) );
				
				$this->sheets = array();
				
				$sheetsCount = count( $this->xmlWorkbook->sheets->sheet );
				for( $sheetIndex = 0 ; $sheetIndex < $sheetsCount ; $sheetIndex++ ) {
					$cs = $this->xmlWorkbook->sheets->sheet[ $sheetIndex ];
					$tabName = iconv( 'utf8' , 'cp1251' , $cs[ self::FIELD__name ] );
					$tabRID = (string)$cs->attributes( 'r' , true )[ self::FIELD__id ];
					$tabZipFileName = $this->zipNameWorksheets[ $tabRID ];
					$xmlCurrentSheet = $this->ldDOM( $tabZipFileName );
					
					$nodeSheetData = null ;
					$rowsMap = array();
					$cellsMap = array();
					$colsStyle = array();
					$maxColIndex = 'A' ;
					$maxRowIndex = 1 ;
					$mergeCells = array();
					$this->sheets[ $tabName ] = array(
						'tabName' => $tabName ,
						'workbook--index' => $sheetIndex ,
						self::FIELD__zipFileName => $tabZipFileName ,
						self::FIELD__xml => $xmlCurrentSheet ,
						self::FIELD__changed => false
					);
					
					$nodeWorksheet = $xmlCurrentSheet->documentElement ;
					foreach( $nodeWorksheet->childNodes as $cn1 ) {
						if ( $cn1->nodeType == XML_ELEMENT_NODE ) {
							switch ( $cn1->nodeName ) {
								case 'sheetData' :
									$nodeSheetData = $cn1 ;
									foreach( $nodeSheetData->childNodes as $cn2 ) {
										if ( $cn2->nodeType == XML_ELEMENT_NODE && $cn2->nodeName == 'row' ) {
											$cRow = $cn2 ;
											$cRowIndex = ( (string)$cRow->getAttribute( 'r' ) );
											$cRowIndexLength = strlen( $cRowIndex );
											if ( $maxRowIndex < $cRowIndex ) {
												$maxRowIndex = $cRowIndex ;
											}
											$rowsMap[ 'row-'.$cRowIndex ] = $cRow ;

											foreach( $cRow->childNodes as $cn3 ) {
												if ( $cn3->nodeType == XML_ELEMENT_NODE && $cn3->nodeName == 'c' ) {
													$cCell = $cn3 ;
													$cellAddress = (string)$cCell->getAttribute( 'r' );
													if ( substr( $cellAddress , -$cRowIndexLength ) == $cRowIndex ) {
														$cColName = substr( $cellAddress , 0 , -$cRowIndexLength );
														if ( $maxColIndex < $cColName ) {
															$maxColIndex = $cColName ;
														}
													}
													$cellFullAddress = $tabName.'!'.$cellAddress ;
													$cellsMap[ $cellAddress ] = $cCell ;

													if ( $cCell->hasAttribute( 't' ) && $cCell->getAttribute( 't' ) == 's' ) {
														foreach( $cCell->childNodes as $cn4 ) {
															if ( $cn4->nodeType == XML_ELEMENT_NODE && $cn4->nodeName == 'v' ) {
																$ssi = $cn4->nodeValue ;
																$this->sharedStringsMap[ $ssi ][ 'links' ][ $cellFullAddress ] = array(
																	'cell' => $cCell ,
																	'cell-v' => $cn4 ,
																	'tabName' => $tabName ,
																);
															}
														}
													}
												}
											}
										}
									}
									break ;

								case 'cols' :
									$nodeCols = $cn1 ;
									foreach( $nodeCols->childNodes as $cn2 ) {
										if ( $cn2->nodeType == XML_ELEMENT_NODE && $cn2->nodeName == 'col' ) {
											//$colsStyle
											$cMinI = intval( $cn2->getAttribute( 'min' ) , 10 );
											$cMaxI = intval( $cn2->getAttribute( 'max' ) , 10 );
											if ( $cn2->hasAttribute( 'customWidth' ) && $cn2->getAttribute( 'customWidth' ) == '1' ) {
												$cWidth = floatval( $cn2->getAttribute( 'width' ) );
											} else {
												$cWidth = 64/7.5 ;
											}
											if ( $cn2->hasAttribute( 'style' ) ) {
												$cStyle = $cn2->getAttribute( 'style' );
											} else {
												$cStyle = 1 ;
											}
											for( $cI = $cMinI ; $cI <= $cMaxI ; $cI++ ) {
												$colsStyle[ $cI ] = array(
													'width' => $cWidth ,
													'style' => $cStyle
												);
											}
										}
									}
									break ;

								case 'mergeCells' :
									$nodeMergeCells = $cn1 ;
									foreach( $nodeMergeCells->childNodes as $cn2 ) {
										if ( $cn2->nodeType == XML_ELEMENT_NODE && $cn2->nodeName == 'mergeCell' ) {
											$mcRef = $cn2->getAttribute( 'ref' );
											if ( preg_match( '/([A-Z]+)(\d+):([A-Z]+)(\d+)/' , $mcRef , $mcRefM ) == 1 ) {
												$mergeCells[]= array(
													'left' => $mcRefM[ 1 ] ,
													'top' => $mcRefM[ 2 ] ,
													'right' => $mcRefM[ 3 ] ,
													'bottom' => $mcRefM[ 4 ]
												);
											}
										}
									}
									break ;
							}
						}
					}
					
					$this->sheets[ $tabName ][ self::FIELD__rowsMap ] = $rowsMap ;
					$this->sheets[ $tabName ][ self::FIELD__cellsMap ] = $cellsMap ;
					$this->sheets[ $tabName ][ self::FIELD__maxRowIndex ] = $maxRowIndex ;
					$this->sheets[ $tabName ][ self::FIELD__maxColIndex ] = $maxColIndex ;
					$this->sheets[ $tabName ][ self::FIELD__colsStyle ] = $colsStyle ;
					$this->sheets[ $tabName ][ self::FIELD__mergeCells ] = $mergeCells ;
					//error_log_ml( print_r( $colsStyle , 1 ) );
				}
			}

			$colIndex = 1 ;
			$conLvl23NameRange = array_merge( array( '' ) , range( 'A' , 'Z' ) );
			$conLvl1NameRange = range( 'A' , 'Z' );
			foreach( $conLvl23NameRange as $l3 ) {
				foreach( $conLvl23NameRange as $l2 ) {
					foreach( $conLvl1NameRange as $l1 ) {
						$colName = $l3.$l2.$l1 ;
						$this->colNames[ $colIndex ]= $colName ;
						$this->colIndexes[ $colName ]= $colIndex ;
						$colIndex++ ;
					}
				}
			}
		}

		function __destruct() {
			unlink( $this->tempFileName );
		}

		function getSheetsNames() {
			$res = array();
			foreach( $this->sheets as $cs ) {
				$res[]= $cs[ 'tabName'];
			}
			return $res ;
		}

		function getColsWidth() {
			$csd = &$this->currentSheetExData ;
			$colsStye = &$csd[ self::FIELD__colsStyle ];
			$res = array();
			foreach( $colsStye as $cci => $ccs ) {
				$res[ $cci ] = $ccs[ 'width' ];
			}
			return $res ;
		}

		function getRowsHeights() {
			$csd = &$this->currentSheetExData ;
			$rowsMap = &$csd[ self::FIELD__rowsMap ];
			$res = array();
			foreach( $rowsMap as $rn ) {
				$ri = $rn->getAttribute( 'r' );
				$rh = $rn->hasAttribute( 'ht' ) ? $rn->getAttribute( 'ht' ) : 15.0 ;
				$res[ $ri ] = $rh ;
			}
			return $res ;
		}

		function getCellsMerges() {
			$csd = &$this->currentSheetExData ;
			return $csd[ self::FIELD__mergeCells ];
		}

		function getSheetArea() {
			return array(
				'maxRowIndex' => $this->currentSheetExData[ self::FIELD__maxRowIndex ] ,
				'maxColIndex' => $this->currentSheetExData[ self::FIELD__maxColIndex ]
			);
		}

		function selectSheet( $sheetName ) {
			$this->currentSheetExData = &$this->sheets[ $sheetName ];
			return $this->currentSheetExData[ self::FIELD__xml ];
		}
		
		private function getSharedStringIndex( $str ) {
			$ssm = $this->sharedStringsMap ;
			
			foreach( $ssm as $ssmi ) {
				if ( $str == $ssmi[ 'txt' ] ) {
					return $ssmi[ 'index' ];
				}
			} unset( $ssmi );
			
			return false ;
		}

		private function getStyle( $sid ) {
			$sd = &$this->stylesData ;
			$sdFonts = &$sd[ 'fonts' ];
			$sdBorders = &$sd[ 'borders' ];
			$sdCellXfs = &$sd[ 'cellXfs' ];
			$res = array();

			if ( !isset( $sdCellXfs[ $sid ] ) ) {
				return $res ;
			}

			$csd = $sdCellXfs[ $sid ];

			$csdFontID =  $csd[ 'fontId' ];
			if ( isset( $sdFonts[ $csdFontID ] ) ) {
				$res[ 'font' ] = $sdFonts[ $csdFontID ];
			}

			$csdBorderID =  $csd[ 'borderId' ];
			if ( isset( $sdBorders[ $csdBorderID ] ) ) {
				$res[ 'borders' ] = $sdBorders[ $csdBorderID ];
			}

			if ( isset( $csd[ 'alignment' ] ) ) {
				$res[ 'alignment' ] = $csd[ 'alignment' ];
			}

			return $res ;
		}
		
		private function newSharedString( $str ) {
			$si = $this->xmlSharedStrings->createElement( 'si' );
			$sit = $this->xmlSharedStrings->createElement( 't' );
			if ( preg_match( '/^\s+/' , $str ) == 1 || preg_match( '/\s+$/' , $str ) == 1 ) {
				//$sit->setAttributeNS( 'xml' , 'space' , 'preserve' );
			}
			$sit->nodeValue = htmlspecialchars( iconv( 'cp1251' , 'utf8' , $str ) , ENT_XML1 );
			$si->appendChild( $sit );
			
			$this->xmlSharedStrings->documentElement->appendChild( $si );
			
			$ssmSize = count( $this->sharedStringsMap );
			$this->sharedStringsMap[ $ssmSize ] = array(
				'node' => $si ,
				'links' => array() ,
				'index' => $ssmSize ,
				'txt' => $str
			);
			
			$this->xmlSharedStringsChanged = true ;
			
			return $ssmSize ;
		}
		
		private function linkSharedString( $strIndex , $sheetData , $cell ) {
			$cellV = false ;
			foreach( $cell->childNodes as $cn1 ) {
				if ( $cn1->nodeType == XML_ELEMENT_NODE && $cn1->nodeName == 'v' ) {
					$cellV = $cn1 ;
				}
			}
			if ( $cellV === false ) {
				$cellV = $sheetData[ self::FIELD__xml ]->createElement( 'v' );
				$cell->appendChild( $cellV );
			}
			
			$tabName = $sheetData[ 'tabName' ];

			$ssmi = &$this->sharedStringsMap[ $strIndex ];
			$cellAddress = (string)$cell->getAttribute( 'r' );
			$cellFullAddress = $tabName.'!'.$cellAddress ;
			$ssmi[ 'links' ][ $cellFullAddress ] = array(
				'cell' => $cell ,
				'cell-v' => $cellV ,
				'tabName' => $tabName ,
			);
		}
		
		private function unlinkSharedString( $strIndex , $tabName , $cellAddr ) {
			$ssmi = &$this->sharedStringsMap[ $strIndex ];
			$cellFullAddress = $tabName.'!'.$cellAddr ;
			unset( $ssmi[ 'links' ][ $cellFullAddress ] );
			if ( count( $ssmi[ 'links' ] ) == 0 ) {
				$sst = $this->xmlSharedStrings->documentElement ;
				$ssmSize = count( $this->sharedStringsMap );
				$ssmiLast = &$this->sharedStringsMap[ $ssmSize - 1 ];
				
				foreach( $ssmiLast[ 'links' ] as &$cld ) {
					$cld[ 'cell-v' ]->nodeValue = $strIndex ;
					$this->sheets[ $cld[ 'tabName' ] ][ self::FIELD__changed ] = true ;
				} unset( $cld );

				$ssmiLast[ 'node' ] = $sst->insertBefore( $ssmiLast[ 'node' ] , $ssmi[ 'node' ] );
				$sst->removeChild( $ssmi[ 'node' ] );
				
				unset( $this->sharedStringsMap[ $ssmSize - 1 ] );
				$ssmiLast[ 'index' ] = $strIndex ;
				$this->sharedStringsMap[ $strIndex ] = $ssmiLast ;
				$this->xmlSharedStringsChanged = true ;
			}
		}
		
		function setCellValue( $addr , $value , $forceString = false ) {
			$n = preg_match( '/^([A-Z]+)(\d+)$/' , $addr , $m );
			if ( $n != 1 ) {
				return false ;
			}
			$csd = &$this->currentSheetExData ;
			$cellsMap = &$csd[ self::FIELD__cellsMap ];
			
			if ( !isset( $cellsMap[ $addr ] ) ) {
				error_log( 'DBG XLSX : '.$addr.' - NO MAP' );
			}
			
			$cCell = $cellsMap[ $addr ];
			
			if ( $cCell->hasAttribute( 't' ) && $cCell->getAttribute( 't' ) == 's' ) {
				$ntc = false ;
				$forceSet = false ;
				foreach( $cCell->childNodes as $cn1 ) {
					if ( $cn1->nodeType == XML_ELEMENT_NODE && $cn1->nodeName == 'v' ) {
						$ntc = $cn1 ;
					}
				}
				
				if ( $ntc === false ) {
					$ntc = $csd[ self::FIELD__xml ]->createElement( 'v' );
					$cCell->appendChild( $ntc );
					$forceSet = true ;
				}
				
				if ( is_numeric( $value ) && !$forceString ) {
					$this->unlinkSharedString( (int)$ntc->nodeValue , $csd[ 'tabName' ] , $addr );
					$ntc->nodeValue = is_float( $value ) ? str_replace( ',' , '.' , ''.$value ) : $value ;
					$cCell->removeAttribute( 't' );
					$csd[ self::FIELD__changed ] = true ;
				} else {
					$this->unlinkSharedString( (int)$ntc->nodeValue , $csd[ 'tabName' ] , $addr );
					$ssi = $this->getSharedStringIndex( $value );
					if ( $ssi === false ) {
						$ssi = $this->newSharedString( $value );
					}
					$ntc->nodeValue = $ssi ;
					$this->linkSharedString( $ssi , $csd , $cCell );
					$csd[ self::FIELD__changed ] = true ;
				}
			} else {
				$ntc = false ;
				$forceSet = false ;
				foreach( $cCell->childNodes as $cn1 ) {
					if ( $cn1->nodeType == XML_ELEMENT_NODE && $cn1->nodeName == 'v' ) {
						$ntc = $cn1 ;
					}
				}
				
				if ( $ntc === false ) {
					$ntc = $csd[ self::FIELD__xml ]->createElement( 'v' );
					$cCell->appendChild( $ntc );
					$forceSet = true ;
				}
				
				if ( is_numeric( $value ) && !$forceString ) {
					if ( ( $value != $ntc->nodeValue ) || $forceSet ) {
						$ntc->nodeValue = is_float( $value ) ? str_replace( ',' , '.' , ''.$value ) : $value ;
						$csd[ self::FIELD__changed ] = true ;
					}
				} else {
					$cCell->setAttribute( 't' , 's' );
					$ssi = $this->getSharedStringIndex( $value );
					if ( $ssi === false ) {
						$ssi = $this->newSharedString( $value );
					}
					$ntc->nodeValue = $ssi ;
					$this->linkSharedString( $ssi , $csd , $cCell );
					$csd[ self::FIELD__changed ] = true ;
				}
			}
		}
		
		function getCellValue( $addr ) {
			$n = preg_match( '/^([A-Z]+)(\d+)$/' , $addr , $m );
			if ( $n != 1 ) {
				return null ;
			}
			$csd = &$this->currentSheetExData ;
			$cellsMap = &$csd[ self::FIELD__cellsMap ];
			
			if ( !isset( $cellsMap[ $addr ] ) ) {
				error_log( 'DBG XLSX : '.$addr.' - NO MAP' );
				return '' ;
			}
			
			$cCell = $cellsMap[ $addr ];
			
			if ( $cCell->hasAttribute( 't' ) && $cCell->getAttribute( 't' ) == 's' ) {
				$ntc = false ;
				foreach( $cCell->childNodes as $cn1 ) {
					if ( $cn1->nodeType == XML_ELEMENT_NODE && $cn1->nodeName == 'v' ) {
						$ntc = $cn1 ;
					}
				}
				
				if ( $ntc === false ) {
					return '' ;
				} else {
					$ssi = (int)$ntc->nodeValue ;
					return $this->sharedStringsMap[ $ssi ][ 'txt' ];
				}
			} else
			if ( $cCell->hasAttribute( 't' ) && $cCell->getAttribute( 't' ) == 'str' ) {
				$ntc = false ;
				foreach( $cCell->childNodes as $cn1 ) {
					if ( $cn1->nodeType == XML_ELEMENT_NODE && $cn1->nodeName == 'v' ) {
						$ntc = $cn1 ;
					}
				}
				
				if ( $ntc === false ) {
					return '' ;
				} else {
					return iconv( 'utf8' , 'cp1251' , $ntc->nodeValue );
				}
			} else {
				$ntc = false ;
				foreach( $cCell->childNodes as $cn1 ) {
					if ( $cn1->nodeType == XML_ELEMENT_NODE && $cn1->nodeName == 'v' ) {
						$ntc = $cn1 ;
					}
				}
				
				if ( $ntc === false ) {
					return '' ;
				} else {
					return $ntc->nodeValue ;
				}
			}
		}
		
		function getCellValueEx( $addr ) {
			$n = preg_match( '/^([A-Z]+)(\d+)$/' , $addr , $m );
			if ( $n != 1 ) {
				return null ;
			}
			$cellColName = $m[ 1 ];
			$cellColIndex = $this->colIndexes[ $cellColName ];
			$cellRowIndex = $m[ 2 ];
			$csd = &$this->currentSheetExData ;
			$rowsMap = &$csd[ self::FIELD__rowsMap ];
			$cellsMap = &$csd[ self::FIELD__cellsMap ];
			$colsStye = &$csd[ self::FIELD__colsStyle ];
			
			if ( !isset( $cellsMap[ $addr ] ) ) {
				error_log( 'DBG XLSX : '.$addr.' - NO MAP' );
				return array(
					'formula' => false ,
					'type'  => 'empty' ,
					'value' => '' ,
					'style' => array()
				);
			}
			
			$cCell = $cellsMap[ $addr ];
			
			$formulaNode = false ;
			$valueNode = false ;
			foreach( $cCell->childNodes as $cn1 ) {
				if ( $cn1->nodeType == XML_ELEMENT_NODE ) {
					switch( $cn1->nodeName ) {
						case 'v' :
							$valueNode = $cn1 ;
							break ;
							
						case 'f' :
							$formulaNode = $cn1 ;
							break ;
					}
				}
			}
			
			$res = array(
				'formula' => ( $formulaNode === false ? false : $formulaNode->nodeValue ) ,
				'value'   => '' ,
				'style'   => array(
					'width' => 8.43
				)
			);

			if ( isset( $colsStye[ $cellColIndex ] ) ) {
				$res[ 'style' ][ 'width' ] = $colsStye[ $cellColIndex ][ 'width' ];
			}

			if ( $cCell->hasAttribute( 's' ) ) {
				$res[ 'style' ] = array_merge( $res[ 'style' ] , $this->getStyle( $cCell->getAttribute( 's' ) ) );
			}
			
			if ( $cCell->hasAttribute( 't' ) && $cCell->getAttribute( 't' ) == 's' ) {
				$res [ 'type' ]  = 'string' ;
				if ( $valueNode !== false ) {
					$ssi = (int)$valueNode->nodeValue ;
					$res[ 'value' ] = $this->sharedStringsMap[ $ssi ][ 'txt' ];
				}
				return $res ;
			} else
			if ( $cCell->hasAttribute( 't' ) && $cCell->getAttribute( 't' ) == 'str' ) {
				$res[ 'type' ] = 'string' ;
				if ( $valueNode !== false ) {
					$res[ 'value' ] = iconv( 'utf8' , 'cp1251' , $valueNode->nodeValue );
				}
				return $res ;
			} else {
				$res[ 'type' ] = 'number' ;
				if ( $valueNode !== false ) {
					$res[ 'value' ] = $valueNode->nodeValue ;
				}
				return $res ;
			}
		}
		
		private function writePart( $zipFileName , $PartData ) {
			$this->zip->deleteName( $zipFileName );
			if ( $PartData instanceof SimpleXMLElement ) {
				$this->zip->addFromString( $zipFileName , $PartData->asXML() );
			} else
			if ( $PartData instanceof DOMDocument ) {
				$this->zip->addFromString( $zipFileName , $PartData->saveXML() );
			} else {
				$this->zip->addFromString( $zipFileName , ''.$PartData );
			}
		}
		
		function write() {
			if ( $this->xmlContentTypeChanged ) {
				$this->writePart( $this->zipNameContentType , $this->xmlContentType );
			}
			
			//if ( $this->xmlWorkbookChanged ) {
			$this->xmlWorkbook->calcPr[ 'fullCalcOnLoad' ] = 1 ;
			$this->writePart( $this->zipNameWorkbook , $this->xmlWorkbook );
			//}
			
			foreach( $this->sheets as &$cs ) {
				if ( $cs[ self::FIELD__changed ] ) {
					$this->writePart( $cs[ self::FIELD__zipFileName ] , $cs[ self::FIELD__xml ] );
				}
			} unset( $cs );
			
			if ( $this->xmlSharedStringsChanged ) {
				$this->writePart( $this->zipNameSharedStrings , $this->xmlSharedStrings );
			}
			
			$this->zip->close();
			readfile( $this->tempFileName );
		}

		function makeSimpleTable( $t , $c = '' , &$d = null , $f = false ) {
			if ( is_string( $t ) ) {
				$t = json_decode( $t , true );
			}

			if ( is_string( $c ) ) {
				$c = json_decode( iconv( "cp1251" , "utf8" , $c ) , true );
			}

			if ( $f === false  ) {
				$f = makeSimpleTable_init_filter();
			}
			foreach ( $c as &$cc ) {
				$fn = isset( $cc[ "f" ] ) ? $cc[ "f" ] : ( isset( $cc[ "t" ] ) ? ( isset( $t[ "fp" ] ) ? $t[ "fp" ] : "" ).$cc[ "t" ] : false );
				$cc[ "f" ] = $fn === false || !isset( $f[ $fn ] ) ? $f[ "raw" ] : $f[ $fn ];
			} unset( $cc );

			if ( isset( $t[ "first-result-row" ] ) ) {
				$rowIndex = intval( $t[ "first-result-row" ] , 10 );
			} else {
				$rowIndex = 1 ;
			}

			error_log( 'DBG: XLSX makeSimpleTable rows total count : '.count( $d ) );
			error_log( 'DBG: XLSX makeSimpleTable row index : '.$rowIndex );

			foreach ( $d as $drk => &$drv ) {
				//$r.= "<tr".( isset( $t[ "drid" ] ) ? " id=\"".$t[ "drid-pref" ]."dr".$drk."\"" : "" )." class=\"".$rs."\"".( isset( $t[ "dra" ] ) ? " onclick=\"".$t[ "dra" ]."( event , ".$drk." )\"" : "" ).">" ;
				foreach ( $c as &$cc ) {
					$ro = array();
					if ( isset( $cc[ "n" ] ) ) {
						$tr = $cc[ "f" ]( $drv , $cc[ "n" ] , $drv[ $cc[ "n" ] ] , $ro );
					} else {
						$tr = $cc[ "f" ]( $drv , "" , "" , $ro );
					}

					if ( isset( $ro[ "skip" ] ) && $ro[ "skip" ] ) {
					} else {
						$this->setCellValue( $cc[ "c" ].$rowIndex , $tr );
						/*$r.= "<td".( isset( $cc[ "id" ] ) ? " id=\"".$t[ "drid-pref" ]."dr".$drk."c-".$cc[ "id" ]."\"" : "" )." class=\"".$cc[ "s" ]."\"" ;
						if ( isset( $ro[ "colspan" ] ) ) {
							$r.= " colspan=\"".$ro[ "colspan" ]."\"" ;
						}
						if ( isset( $ro[ "rowspan" ] ) ) {
							$r.= " rowspan=\"".$ro[ "rowspan" ]."\"" ;
						}
						$r.= ">".$tr."</td>" ;*/
					}
				} unset( $cc );
				if ( $rowIndex % 100 == 0 ) {
					error_log( 'DBG: XLSX makeSimpleTable row index : '.$rowIndex );
				}
				$rowIndex++ ;
			} unset( $drv );

		}
	}
