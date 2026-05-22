<?php
	/*
	    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
	    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
	    copyright (c) Пекшев Петр Александрович, 2008
	*/

	$PlaceID = 3 ;
	//$dbLocalDatabase = "maindb-test" ;
	$defRedirect = "main.php" ;
	$elementSize = 4 * 1024 ;

	/*
	 * matincoming id  - 19 знаков : 1 - версия ( = 1 ) , 1 - подверсия ( = 0 ) , 2 - регион ( наш 36 ) , 1 - версия ( = 0 ) , 4 - тип документа , 4 - год , 6 - порядковый номер
	 *                             : пример 10.360.0110.2017.001234 => 1036001102017001234
	 */

	$paymentsStyles = array(
		"unchecked" => array(
			"simple" => array( "chk_btn" , "exp_number" , "worker" , "price" , "sndz" , "pay_date" , "pay_details" , "comment" , "marks" , "application_for_issuance" ) ,
			"extended" => array( "exp_number" , "worker" , "price" , "from" , "pay_date" , "pay_details" , "comment" )
		) ,
		"checked" => array(
			"simple" => array( "check_date" , "chk_btn" , "exp_number" , "worker" , "price" , "pay_date" , "pay_details" , "comment" , "marks" , "application_for_issuance" ) ,
			"extended" => array( "exp_number" , "worker" , "price" , "from" , "pay_date" , "pay_details" , "comment" )
		)
	);
