<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( "../core.php" );
	/**
	 * @var $portalDB
	 * @var $LoginOk
	 * @var $UserRights
	 */
	require_once( "lconfig.php" );
	/**
	 * @var $PlaceID
	 */
	require_once( '../cores/core.maindb.php' );

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( "../auth.php" );
	}

	if ( count( $UserRights ) != 1 ) {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}

	if ( isset( $_REQUEST[ "id" ] ) ) {
		$cid = $_REQUEST[ "id" ];
	} else {
		MainHead_L2( "" , "" , array( "../%UT/buttons.css" , "../%UT/forms.css" ) , array() , "hlp/no_access.html" );
		echo "<br><br><br><br><br>" ;
		MessageForm();
		closeHtml();
		exit();
	}

	MainHead_Print( "База" , array( "%UT/paper-storage.mkList.css" ) , array( "files/paper-storage.js" ) , "" );

	$lst = $portalDB->query( "select `t1`.`id` as `id` , concat( matincomingNumber( `t1`.`id` ) , \" / *\" , IF( `t1`.`exp_type` > 0 , concat( \" - \" , `t1`.`exp_type` ) , \" - д\" ) ) as `v` from `matincoming` as `t1` where ( `t1`.`paper_storage_cell_id` = ? ) order by `id` asc" , false , "i" , $cid );
	$wks = $portalDB->query( "select `t2`.`name` from `paper-storage` as `t1` , `workers` as `t2` where ( `t1`.`id` = ? ) and ( concat( \",\" , `t1`.`workers` , \",\" ) like concat( \"%,\" , `t2`.`first_id` , \",%\" ) ) group by `t2`.`first_id` order by `name`" , false , "i" , $cid );

	echo "<div class=\"label-area\">" ;
		echo "<div class=\"year\">202<input type=\"text\" id=\"i_year\" maxlength=\"1\" class=\"i-year\"> г.<span class=\"uid\">№ ".$cid."</span></div>" ;

		foreach( $wks as $w ) {
			echo "<div class=\"name\">".NAMES_Format( NAMES_parse( $w[ "name" ] ) )."</div>" ;
		}

		echo "<div class=\"num-area\">" ;

		$ly = -1 ;

		foreach( $lst as $l ) {
			$ny = matincomingYear( $l[ "id" ] );
			if ( $ny != $ly ) {
				echo "<div class=\"num-year\">".$ny."</div>" ;
				$ly = $ny ;
			}
			echo "<div class=\"num\">".$l[ "v" ]."</div>" ;
		}

		echo "<div class=\"num-count\"> Итого : ".count( $lst )." шт.</div>" ;

		echo "</div>" ;

	echo "</div>" ;

	closeHtml_Print();
