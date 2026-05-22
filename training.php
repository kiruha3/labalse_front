<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/
	require_once( "core.php" );

	TryLoginFromCookie();
	if ( !$LoginOk ) {
		Redirect( "auth.php" );
	}

	MainHead_L2( "" , "" , array( "%UT/training.css" ) , array() , "hlp/index.html" , "" );

	include_once 'file_store/integration.php' ;

	$opt = array(
		"header" => 1 ,
		"show-path-at-top" => 0 ,
		"path-at-top-style" => "document-path-at-top" ,
		"path-at-top-simple" => 1 ,
		"multilist-sep" => "none" ,
		"may-select" => 0 ,
		"show-icons" => 1 ,
		"show-dirs" => 0
	);
	$viewStyle = array(
		"style" => "list" ,
		"order" => "name" ,
		"param" => "asc"
	);
	$tableStyle = array(
		"tsm" => "tsf"
	);

	$tpc = $dbConfig[ "training.parts.count" ];
	for( $i = 1 ; $i <= $tpc ; $i++ ) {
		$tpi = $dbConfigFull[ "training.parts.".$i.".lnk.id" ];
		$tpi[ "e-data" ] = json_decode( $tpi[ "e-data" ] , true );
		$opt[ "show-dirs" ] = $tpi[ "e-data" ][ "show-dirs" ];
		echo "<div class=\"doc-section-2\">".integrate( $tpi[ "value" ] , $opt , $viewStyle , $tableStyle )."</div>" ;
	}

	closeHtml();
?>