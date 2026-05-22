<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	$PlaceID = 2;



	// Network Monitor

	$subNetAddr = "10.1.0" ;
	$subNetRange = Array( 1 , 254 );
	$subNetAddrStyle = "st-subnet-addr" ;
	$hostAddrStyle = "st-host-addr" ;
	$unknownAddrStyle = "st-unknown-addr" ;
	$topDomain = Array( "local" , "st-top" );
	$subDomains = Array();
	$subDomains[]= Array( "srv" , "st-srv" );
	$subDomains[]= Array( "comp" , "st-comp" );
	$subDomains[]= Array( "prn" , "st-prn" );
	$subDomains[]= Array( "dev" , "st-dev" );
	$subDomains[]= Array( "vrcse" , "st-vrcse" );
	$host = Array( "%" , "st-host" );
	$unknownHosts = Array( "%" , "st-unknown" );
?>