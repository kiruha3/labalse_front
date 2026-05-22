<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( "core.php" );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $portalDB
	 * @var $MonthNames
	 * @var $UserThemeLoc
	 */

	TryLoginFromCookie();
	$modeAJAX = isset( $_REQUEST[ "mode" ] );
	if ( !$LoginOk ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			Redirect( "../auth.php" );
		}
	}

	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

		if ( array_key_exists( "REGISTER-CORRESPONDENCE" , $Rights ) ) {
			$mayAdd = in_array( "ADD" , $Rights[ "REGISTER-CORRESPONDENCE" ] );
			$mayEdit = in_array( "EDIT" , $Rights[ "REGISTER-CORRESPONDENCE" ] );
			$mayOutput = in_array( "OUTPUT" , $Rights[ "REGISTER-CORRESPONDENCE" ] );
			$mayDelete = in_array( "DELETE" , $Rights[ "REGISTER-CORRESPONDENCE" ] );
			$GoOut = isset( $_REQUEST[ "edit" ] ) ? !$mayEdit : false ;
		} else {
			$GoOut = true ;
		}
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		if ( $modeAJAX ) {
			exit ;
		} else {
			MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
			echo "<br><br><br><br><br>" ;
			MessageForm();
			closeHtml();
			exit ;
		}
	}

	function processAddress( $addr , $splited = false ) {
		$sdi = array();
		if ( preg_match( "/(?:[,. ]+(\\d{6})[,. ]*)/" , $addr , $sdi ) == 1 ) {
			if ( $splited ) {
				return array( "index" => $sdi[ 1 ] , "address" => preg_replace( "/(?:[,. ]+(\\d{6})[,. ]*)/" , "" , $addr ) );
			} else {
				return $sdi[ 1 ].", ".preg_replace( "/(?:[,. ]+(\\d{6})[,. ]*)/" , "" , $addr );
			}
		} else
		if ( preg_match( "/(?:[,. ]*(\\d{6})[,. ]+)/" , $addr , $sdi ) == 1 ) {
			if ( $splited ) {
				return array( "index" => $sdi[ 1 ] , "address" => preg_replace( "/(?:[,. ]*(\\d{6})[,. ]+)/" , "" , $addr ) );
			} else {
				return $sdi[ 1 ].", ".preg_replace( "/(?:[,. ]*(\\d{6})[,. ]+)/" , "" , $addr );
			}
		} else {
			if ( $splited ) {
				return array( "index" => "" , "address" => $addr );
			} else {
				return $addr ;
			}
		}
	}

	function xmlChars( $r ) {
		$r = str_replace( "&" , "&amp;" , $r );
		$r = str_replace( "\"" , "&quot;" , $r );
		$r = str_replace( ">" , "&gt;" , $r );
		$r = str_replace( "<" , "&lt;" , $r );
		return $r ;
	}

	if ( isset( $_REQUEST[ "mode" ] ) ) {

		header( "Content-Type: text/xml" );
		header( "Pragma: no-cache" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Expires: ".date( "r" ) );
		header( "Expires: -1" , false );

		echo "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>" ;

		$DD = new DomDocument();
		$DD->loadXML( $_REQUEST[ "data" ] );

		$data = $DD->documentElement ;

		switch ( $data->nodeName ) {
			case "price" :
				$rid = intval( $data->getAttribute( "id" ) );
				$ta = explode( "+" , $data->nodeValue );
				if ( count( $ta ) == 1 ) {
					$ta[ 1 ] = $ta[ 0 ];
					$ta[ 0 ] = 0.0 ;
				}

				foreach( $ta as &$t ) {
					$t = str_replace( "\xC2\xA0" , " " , $t );
					$t = str_replace( " " , "" , $t );
					$t = str_replace( "," , "." , $t );
					$t = floatval( $t );
				}
				unset( $t );

				$price = $ta[ 0 ];
				$addPrice = $ta[ 1 ];
				$portalDB->noResult( "update `register-correspondence` set `price` = ? , `add_price` = ? where `id` = ?" , "ssi" , str_replace( "," , "." , $price ) , str_replace( "," , "." , $addPrice ) , $rid );
				echo "<result>".money_format( "%!i" , $price )." + ".money_format( "%!i" , $addPrice )."</result>" ;
				break ;

			case "weight" :
				$rid = intval( $data->getAttribute( "id" ) );
				$t = $data->nodeValue ;
				$t = str_replace( "\xC2\xA0" , " " , $t );
				$t = str_replace( " " , "" , $t );
				$t = str_replace( "," , "." , $t );
				$weight = intval( round( floatval( $t ) * 1000 , 0 ) );
				$portalDB->noResult( "update `register-correspondence` set `weight` = ? where `id` = ?" , "ii" , $weight , $rid );
				echo "<result>".( $weight / 1000.0 )."</result>" ;
				break ;

			case "add-label" :
				$comment = "" ;
				$addressee = "" ;
				$destination = "" ;
				$index = "" ;
				foreach( $data->childNodes as $n ) {
					switch ( $n->nodeName ) {
						case "comment" :
							$comment = iconv( "utf8" , "cp1251" , $n->nodeValue );
							break ;
						case "addressee" :
							$addressee = iconv( "utf8" , "cp1251" , $n->nodeValue );
						case "destination" :
							$destination = iconv( "utf8" , "cp1251" , $n->nodeValue );
							break ;
					}
				}

				$p1 = floatval( $data->getAttribute( "p1" ) );
				$p2 = floatval( $data->getAttribute( "p2" ) );
				$w = intval( round( $data->getAttribute( "w" ) * 1000.0 ) );

				$r = processAddress( $destination , true );
				$destination = $r[ "address" ];
				$index = $r[ "index" ];

				$portalDB->noResult(
					"insert into `register-correspondence` ( `comment` , `date` , `destination` , `addressee` , `price` , `add_price` , `weight` ) values ( ? , ? , ? , ? , ? , ? , ? )" ,
					"sissddi" , $comment , time() , $index.", ".$destination , $addressee , $p1 , $p2 , $w );

				echo "<result status=\"ok\"/>" ;

				break ;

			case "set-mark" :
				$ida = explode( "," , $data->getAttribute( "id" ) );
				$mark = $data->getAttribute( "mark" );
				$idl = array();
				foreach( $ida as $id ) {
					if ( preg_match( "/^\\d+$/" , trim( $id ) ) == 1 ) {
						$idl[]= $id ;
					}
				}

				if ( count( $idl ) > 0 ) {
					$portalDB->noResult( "update `register-correspondence` set `mark` = ? where ( `id` in ( ?* ) )" , "s*i" , $mark , $idl );
				}

				echo "<result status=\"ok\" count=\"0\" />" ;

				break ;

			case "delete" :
				$idl = array();
				foreach( $data->childNodes as $n ) {
					if ( $n->nodeName == "e" ) {
						$id = $n->getAttribute( "id" );
						if ( preg_match( "/^\\d+$/" , trim( $id ) ) == 1 ) {
							$idl[]= $id ;
						}
					}
				}

				if ( count( $idl ) > 0 ) {
					$portalDB->noResult( "delete from `register-correspondence` where ( `id` in ( ?* ) )" , "*i" , $idl );
				}
				echo "<result status=\"ok\"/>" ;
				break ;
		}

		exit();
	}

	$labelColors = array( "c00000" , "008000" , "0080ff" );
	$markColors = array();

	//$ml = QueryAsArray( $con , "select `mark` from `register_correspondence` where ( `mark` is not null ) group by `mark`" );
	$ml = array();
	$ml[]= array( "mark" => "mark1" );
	$ml[]= array( "mark" => "mark2" );
	$ml[]= array( "mark" => "mark3" );
	$mci = 0 ;
	$js_mca = "var markColors = [];" ;
	foreach( $ml as $m ) {
		$m = $m[ "mark" ];
		$markColors[ $m ] = $labelColors[ $mci++ ];
		$js_mca.= "markColors[ \"".$m."\" ] = \"".$markColors[ $m ]."\" ;" ;
	}

	$dataRange = $portalDB->row( "select min( `date` ) as `mid` , max( `date` ) as `mad` from `register-correspondence`" );
	$minY = intval( date( "Y" , $dataRange[ "mid" ] ) );
	$minM = intval( date( "m" , $dataRange[ "mid" ] ) );
	$maxY = intval( date( "Y" , $dataRange[ "mad" ] ) );
	$maxM = intval( date( "m" , $dataRange[ "mad" ] ) );

	if ( isset( $_REQUEST[ "y" ] ) ) {
		$cY = intval( $_REQUEST[ "y" ] );
	} else {
		$cY = intval( date( "Y" , time() ) );
	}

	if ( $cY >= $maxY ) {
		$cY = $maxY ;
		$minM = 1 ;
	} else
	if ( $cY <= $minY ) {
		$cY = $minY ;
		$maxM = 12 ;
	} else {
		$minM = 1 ;
		$maxM = 12 ;
	}

	if ( isset( $_REQUEST[ "m" ] ) ) {
		$cM = intval( $_REQUEST[ "m" ] );
	} else {
		$cM = $maxM ;
	}

	if ( $cM > $maxM ) {
		$cM = $maxM ;
	} else
	if ( $cM < $minM ) {
		$cM = $minM ;
	}

	$minD = mktime( 0 , 0 , 0 , $cM , 1 , $cY );
	$maxD = mktime( 23 , 59 , 59 , $cM , date( "t" , $minD ) , $cY );

	MainHead_L2( "" , "" , array( "%UT/buttons.css" , "%UT/register-correspondence.css" ) , array( '@/files/labeling/brother--ql-570/main.js' , 'files/register-correspondence.js' , "#var minDate = ".$minD." ; var maxDate = ".$maxD." ;".$js_mca ) , "hlp/register-correspondence.html" , "" );


	echo "<div class=\"date-selector-panel\">" ;

	for( $m = $maxM ; $m >= $minM ; $m-- ) {
		if ( $m == $cM ) {
			echo "<a class=\"dsp-selected\">".inForm( $MonthNames[ $m - 1 ] , 1 )."</a>" ;
		} else {
			echo "<a href=\"?m=".$m.( isset( $_REQUEST[ "y" ] ) ? "&y=".$cY : "" )."\" class=\"dsp-unselected\">".inForm( $MonthNames[ $m - 1 ] , 1 )."</a>" ;
		}
	}

	echo " | " ;

	for( $y = $maxY ; $y >= $minY ; $y-- ) {
		if ( $y == $cY ) {
			echo "<a class=\"dsp-selected\">".$y."</a>" ;
		} else {
			echo "<a href=\"?y=".$y."\" class=\"dsp-unselected\">".$y."</a>" ;
		}
	}

	echo "</div>" ;


	echo ( $mayAdd ? "<a onclick=\"showAddressesFillDlg( event )\" class=\"btn3\">Добавить</a>" : "" )."
	".( $mayDelete ? "<a onclick=\"doDelete();\" class=\"btn3\">Удалить</a>" : "" )."
	|
	".( $mayEdit ? "<a onclick=\"selectRows();\" class=\"btn3\">Выделить все</a>" : "" )."
	".( $mayEdit ? "<a onclick=\"selectRows( 'mark1' );\" class=\"btn3\">Выделить mark1</a>" : "" )."
	".( $mayEdit ? "<a onclick=\"selectRows( 'mark2' );\" class=\"btn3\">Выделить mark2</a>" : "" )."
	".( $mayEdit ? "<a onclick=\"selectRows( 'mark3' );\" class=\"btn3\">Выделить mark3</a>" : "" )."
	".( $mayEdit ? "<a onclick=\"selectRows( '' );\" class=\"btn3\">без отметки</a>" : "" )."
	".( $mayEdit ? "<a onclick=\"deselectRows();\" class=\"btn3\">Снять выделение</a>" : "" )."
	|
	".( $mayEdit ? "<a onclick=\"setMark( 'mark1' );\" class=\"btn3\">поставить mark1</a>" : "" )."
	".( $mayEdit ? "<a onclick=\"setMark( 'mark2' );\" class=\"btn3\">поставить mark2</a>" : "" )."
	".( $mayEdit ? "<a onclick=\"setMark( 'mark3' );\" class=\"btn3\">поставить mark3</a>" : "" )."
	".( $mayEdit ? "<a onclick=\"setMark( '' );\" class=\"btn3\">Убрать отметки</a>" : "" )."
	".( $mayOutput ? "<a onclick=\"generateRegistry2( 'register-correspondence.report-registered.php?selected=' );\" class=\"btn3\">Вывести 2</a>" : "" )."
	".( $mayOutput ? "<a onclick=\"generateRegistry2( 'register-correspondence.report-simple-1.php?selected=' );\" class=\"btn3\">Вывести 3</a>" : "" )."
	".( $mayOutput ? "<a onclick=\"generateRegistry2( 'register-correspondence.a4-labels.php?selected=' );\" class=\"btn3\">Этикетки на А4</a>" : "" )."
	<a onclick=\"calcControlDigit();\" class=\"btn3\">Контрольная цифра</a>

	<br><table align=\"center\" class=\"main-tab\">
		<tr>
			<td class=\"mt-c-1\">
			</td>
			<td class=\"mt-c-1-2\">
			</td>
			<td class=\"mt-c-2\">
				Номер корр.
			</td>
			<td class=\"mt-c-3\">
				Дата отправки
			</td>
			<td class=\"mt-c-4\">
				Адресат
			</td>
			<td class=\"mt-c-5\">
				Цена
			</td>
			<td class=\"mt-c-6\">
				Вес
			</td>
		</tr>" ;

	$rcd = $portalDB->query( "select * from `register-correspondence` where ( `date` >= ? ) and ( `date` <= ? )" , false , "ii" , $minD , $maxD );

	foreach( $rcd as $row ) {
		$rid = $row[ "id" ];
		$mark = $row[ "mark" ];
		$rc = $row[ "comment" ];
		switch( $row[ "ext_type" ] ) {
			case "matincoming" :
				$rc = '<a href="/maindb/main.php?idlist='.$row[ 'ext_id' ].'" target="_blank" class="comment-lnk">'.htmlentities( $rc ).'</a>' ;
				break ;

			case "bills" :
				$rc = '<a href="/bills/bill.print.php?id='.$row[ 'ext_id' ].'" target="_blank" class="comment-lnk">'.htmlentities( $rc ).'</a>' ;
				break ;
			case 'correspondence' :
				$rc = '<a href="/maindb/correspondence.php?view=any&idlist='.$row[ 'ext_id' ].'" target="_blank" class="comment-lnk">'.htmlentities( $rc ).'</a>' ;
				break ;
			case 'subpoena' :
				$rc = '<a href="/maindb/subpoenas.php?id='.$row[ 'ext_id' ].'" target="_blank" class="comment-lnk">'.htmlentities( $rc ).'</a>' ;
				break ;
		}
		echo "<tr>
			<td class=\"mt-d-1\">
				<input id=\"i_row_".$rid."\" name=\"i_row[]\" type=\"checkbox\" value=\"".$rid."\" data-mark=\"".$mark."\">
			</td>
			<td class=\"mt-d-1-2\" id=\"i_mark_".$rid."\" ".( $mark != "" ? "style=\"background-color : #".$markColors[ $mark ]."\"" : "" ).">
				".$mark."
			</td>
			<td class=\"mt-d-2\">
				".$rc."
			</td>
			<td class=\"mt-d-3\">
				".date( "d.m.Y" , $row[ "date" ] )."
			</td>
			<td class=\"mt-d-4\">
				".$row[ "destination" ].', '.$row[ "addressee" ]."
			</td>
			<td id=\"rc_p_".$rid."\" class=\"mt-d-5\" onclick=\"editField( 'price' , ".$rid." )\">
				<span id=\"rc_p_s_".$rid."\">".money_format( "%!i" , $row[ "price" ] )." + ".money_format( "%!i" , $row[ "add_price" ] )."</span>
			</td>
			<td id=\"rc_w_".$rid."\" class=\"mt-d-6\" onclick=\"editField( 'weight' , ".$rid." )\">
				<span id=\"rc_w_s_".$rid."\">".number_format( $row[ "weight" ] / 1000.0 , 3 )."</span>
			</td>
		</tr>" ;
	}

	echo "</table>" ;

	echo "Всего записей: <font color=\"#ff0000\">".count( $rcd )."</font><br><br>" ;

	echo "<input id=\"i_action\" name=\"i_action\" type=\"hidden\" value=\"\">" ;
	echo ( $mayDelete ? "<a onclick=\"doDelete();\" class=\"btn3\">Удалить</a>" : "" );
	echo ( $mayOutput ? "<a onclick=\"generateRegistry2( 'register-correspondence.report-registered.php?selected=' );\" class=\"btn3\">Вывести 2</a>" : "" );
	echo ( $mayOutput ? "<a onclick=\"generateRegistry2( 'register-correspondence.report-simple-1.php?selected=' );\" class=\"btn3\">Вывести 3</a>" : "" );
	echo ( $mayOutput ? "<a onclick=\"generateRegistry2( 'register-correspondence.a4-labels.php?selected=' );\" class=\"btn3\">Этикетки на А4</a>" : "" );

	echo "<div id=\"addresses_fill_dlg\" class=\"addresses-fill-dlg\" style=\"display : none ;\">
		<div class=\"addresses-fill-dlg-close-box\"><img src=\"themes/".$UserThemeLoc."/btn_close.bmp\" border=\"0\" onclick=\"hideAddressesFillDlg();\" title=\"Закрыть\"></div>
		<div class=\"addresses-fill-dlg-content\">
			<table id=\"letter_dlg_tab\" class=\"letter-dlg-tab\">
				<tr>
					<td class=\"ldt-cap\">Описание</td>
					<td class=\"ldt-cap\">Кому</td>
					<td class=\"ldt-cap\">Куда</td>
				</tr>
				<tr>
					<td class=\"ldt-comment\"><input id=\"comment\" name=\"comment\" type=\"text\" value=\"\" class=\"comment-inp\"></td>
					<td class=\"ldt-addressee\"><input id=\"addressee\" name=\"addressee\" type=\"text\" value=\"\" class=\"addressee-inp\"></td>
					<td class=\"ldt-destination\">
						<table class=\"destination-panel-top\">
							<tr>
								<td class=\"destination-panel-inp\">
									<input id=\"destination\" name=\"destination\" type=\"text\" value=\"\" class=\"destination-inp\">
								</td>
								<td class=\"destination-panel-btn\">
									<a onclick=\"editAddress()\" class=\"address-lnk\"><img src=\"themes/".$UserThemeLoc."/search.png\"></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan=\"3\" class=\"ldt-weight\">Вес <input id=\"new-weight\" type=\"text\" class=\"weight-inp\" onkeyup=\"changeWeight()\"> заказное <input id=\"new-letter-type\" type=\"checkbox\" onchange=\"changeWeight()\"> =&gt; <input id=\"new-price\" type=\"text\" class=\"price-inp\"></td>
				</tr>
			</table>
			<div class=\"buttons-panel\">
				<a onclick=\"printLabelAndSave();\" class=\"btn3\">Напечатать</a>
				<select id=\"labelFormat\" onchange=\"labelFormatChange();\">
					<option value=\"29x90\">29 x 90</option>
					<option value=\"38x90\">38 x 90</option>
					<option value=\"62x100\">62 x 100</option>
					<option value=\"62\">62 -></option>
				</select>
			</div>
		</dev>
	</div>" ;

	closeHtml();
