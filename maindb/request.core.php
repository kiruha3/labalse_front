<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	if ( isset( $_REQUEST[ 'extractCaseData' ] ) && $_REQUEST[ 'extractCaseData' ] == 'print' ) {
		require_once( "../core.php" );
	} else {
		require_once( "../ext-lib/rtf-gen.php" );
	}

	$extractCaseData__map = strexp( "{case-{num,size,pers{,-1,-2}},doc-{num,type,date}}" );
	$extractCaseData__map = array_combine( $extractCaseData__map , $extractCaseData__map );
	foreach( $extractCaseData__map as &$extractCaseData__cmap ) {
		$extractCaseData__cmap = preg_replace_callback( '/-([a-z])/i' , function( $m ){ return strtoupper( $m[ 1 ] ); } , $extractCaseData__cmap );
	} unset( $extractCaseData__cmap );
	//$extractCaseData__rmap = array_flip( $extractCaseData__map );

	$extractCaseData__pa = array(
		0 => array(
		) ,
		1 => array(
			'^%C ?№ ?%N ?(?:в отношении |в копиях,) ?(?:электронный носитель)? ?(?<casePers>.*)$' ,
			'^%C ?№ ?%N ' ,

			'в отношении (?<casePers>[а-я]+ [а-я]\.[а-я]\.(?:\s*(,\s*)?[а-я]+ [а-я]\.[а-я]\.)*)' ,
		) ,
		2 => array(
			'%C ?№ ?%N' ,
			'(?<caseSize>в \d+(?:-х|-ти|-ми)?\s*(?:т|томах))' ,
			'(?:иск|по иску) (?<casePers>%T(?:\s*[,и]\s*%T)* к %T(?:\s*[,и]\s*%T)*)' ,


			//'(?:иск|по иску) (?<casePers>%F(?:\s*,\s*%F)* к %O(?:\s*,\s*%O)*)' ,
			//'^(?:гр\.д\.|гражданское дело) ?\№(?<caseNum>\d+\-\d+\/\d+(?:\-\d+)?) ?(?<caseSize>в \d+(?:-х)? ?т).*иск (?<casePers>.*)$' ,
			//'^(?:гр\.д\.|гражданское дело) ?\№(?<caseNum>\d+\-\d+\/\d+(?:\-\d+)?) ?(?<caseSize>в \d+(?:-х)? ?т).*иск (?<casePers>.*)$' ,
			//'^(?:гр\.д\.|гражданское дело) ?\№(?<caseNum>\d+\-\d+\-\d+) ?(?<caseSize>в \d+(?:-х)? ?т).*иск (?<casePers>.*)$' ,
		) ,
		3 => array(
			'(?:арб\.д\.|д\.)?\s*№\s*(?<caseNum>А\d+-(?:к)?\d+(?:-\d+)?/\d+(?:-\d+)?)' ,
			'(?<caseSize>в \d+(?:-х|-ти)?\s*(?:т|томах))' ,
			'(?:иск|по иску) (?<casePers>%T(?:\s*[,и]\s*%T)* к %T(?:\s*[,и]\s*%T)*)' ,
		) ,
		4 => array(
			'^(?:%C ?)?\№ ?%N ?(?:мат. ?д.)? ?(?<caseSize>в \d+(?:-х)? ?т).*иск (?<casePers>.*)$' ,
			'^%C ?\№ ?%N (?:мат. ?д.)? ?(?<caseSize>в \d+(?:-х)? ?т).*по жалобе (?<casePers>.*)$'
		),
		5 => array(
			'^К[УР]СП\s*№(?<caseNum>\d+)\s*(?: (?<caseSize>в \d+\s*т)|в копиях(?:\+диск)?)?,?\s*(?<casePers>.*)$' ,
			'^К[УР]СП\s*№(?<caseNum>\d+)\s*,\s*(?<casePers>.*) исслед\.объект согл\.постановления$' ,
			'^К[УР]СП №(?<caseNum>\d+) .*$' ,

			'в отношении (?<casePers>[а-я]+ [а-я]\.[а-я]\.)' ,
			'по заявлению (?<casePers>[а-я]+ [а-я]\.[а-я]\.)' ,

			//№45/4-18 в отношении Ищенко В.А. исслед.док.согл.направления
			'№(?<caseNum>\d+\/\d+\-\d+)\s*в отношении' ,
		) ,
		6 => array(
			'^%C ?№ ?%N ?(?: (?<caseSize>в \d+\s*т)|в копиях(?:\+диск)?)?,?\s*(?<casePers>.*)$' ,
			'^%C ?№ ?%N б/мат+ [а-я]+ [а-я]\.[а-я]\.$' ,
			'^%C ?№ ?%N ?,\s*(?<casePers>.*) исслед\.объект согл\.постановления$' ,
			'^%C ?№ ?%N .*$' ,

			'^%C ?№ ?%N ' ,

			'в отношении (?<casePers>[а-я]+ [а-я]\.[а-я]\.)' ,
			'по заявлению (?<casePers>[а-я]+ [а-я]\.[а-я]\.)' ,

			//№45/4-18 в отношении Ищенко В.А. исслед.док.согл.направления
			'№(?<caseNum>\d+\/\d+\-\d+)\s*в отношении' ,
		)
	);

	$extractCaseData__pa_dt = array(
		0 => array(
			'^(?<docType>Договор) от %D$'
		) ,
		1 => array(
			'^(?<docType>постановление) от %D$',
			'^%D$'
		) ,
		2 => array(
			'(?<docType>определение) от %D'
		) ,
		3 => array(
			'(?<docType>определение) от %D'
		) ,
		4 => array(
			'^(?<docType>определение) от %D$',
			'^(?<docType>постановление) №(?<docNum>\d+) от %D$'
		),
		5 => array(
			'(?<docType>постановление|направление) от %D'
		) ,
		6 => array(
			'(?<docType>постановление|направление) от %D'
		)
	);

	$extractCaseData__pa_ctm = array(
		1 => '(?:уг.д.|уголовное дело)' ,
		2 => '(?:гр\.д\.|гражданское дело|гр\. дело)' ,
		4 => '(?:админ\.д\.|административное дело)' ,
		6 => '(?:К[УР]СП|(?:по )?мат. ?пр.|(?:по )?мат. ?проверки КУСП)'
	);
	$extractCaseData__pa_cn = array(
		1 => '(?<caseNum>\d+)' ,
		2 => '(?<caseNum>\d+-(?:к)?\d+/\d+(?:-\d+)?)' ,
		4 => '(?<caseNum>\d+[а-я]?-\d+/\d+)' ,
		6 => '(?<caseNum>\d+)'
	);

	foreach( $extractCaseData__pa as $extractCaseData__ci => &$extractCaseData__cp ) {
		$extractCaseData__cp = array_merge( $extractCaseData__cp , $extractCaseData__pa_dt[ $extractCaseData__ci ] );
		//print_r_html( $extractCaseData__cp );
		foreach( $extractCaseData__cp as &$extractCaseData__p ) {
			if ( substr( $extractCaseData__p , 0 , 1 ) != "^" ) {
				$extractCaseData__p = '(?:^| |,|\()'.$extractCaseData__p ;
			}

			if ( substr( $extractCaseData__p , -1 ) != "$" ) {
				$extractCaseData__p = $extractCaseData__p.'(?:$| |,|\))' ;
			}

			if ( isset( $extractCaseData__pa_ctm[ $extractCaseData__ci ] ) ) {
				$extractCaseData__p = str_replace( '%C' , $extractCaseData__pa_ctm[ $extractCaseData__ci ] , $extractCaseData__p );
			}
			if ( isset( $extractCaseData__pa_cn[ $extractCaseData__ci ] ) ) {
				$extractCaseData__p = str_replace( '%N' , $extractCaseData__pa_cn[ $extractCaseData__ci ] , $extractCaseData__p );
			}

			$extractCaseData__p = str_replace( '%T' , '(?:%F|%O)' , $extractCaseData__p );
			$extractCaseData__p = str_replace( '%F' , '(?:(?:ИП )?\w+ \w\.\w\.)' , $extractCaseData__p );
			$extractCaseData__p = str_replace( '%O' , '(?:(?:(?:ООО|ОАО|ЗАО|ПАО|АО|САО) )?[-"\w]{2,}(?: [-"\w]{2,})*)' , $extractCaseData__p );
			$extractCaseData__p = str_replace( '%D' , '(?<docDate>\d{2}\.\d{2}\.\d{2,4}|\d{2}\-\d{2}\-\d{2,4})' , $extractCaseData__p );
			$extractCaseData__p = str_replace( ' ?' , '\s*' , $extractCaseData__p );
			$extractCaseData__p = str_replace( ' ' , '\s+' , $extractCaseData__p );
		} unset( $extractCaseData__p );
	} unset( $extractCaseData__cp );

	if ( isset( $_REQUEST[ 'extractCaseData' ] ) && $_REQUEST[ 'extractCaseData' ] == 'print' ) {
		print_r_html( $extractCaseData__pa , 1 );
		exit();
	}

	function extractCaseData( $t1 ) {
		global $extractCaseData__map , $extractCaseData__pa ;
		$map = $extractCaseData__map ;

		$res2 = array();

		$cp = $extractCaseData__pa[ getCCGroup( $t1[ 'exp_type' ] ) ];
		foreach( array( $t1[ "ex_data_3" ] , $t1[ "ex_data_4" ] ) as $t ) {
			$t = preg_replace( '/\s+/m' , " " , trim( $t ) );
			$m = array();
			foreach ( $cp as $p ) {
				$n = preg_match( '@'.$p.'@im' , $t , $m );
				if ( $n == 1 ) {
					$res2 = array_merge( $res2 , $m );
				}
			}
		}

		$res = array();
		foreach ( $map as $resKey => $resVal ) {
			if ( isset( $res2[ $resVal ] ) ) {
				$res[ $resKey ] = $res2[ $resVal ];
			} else {
				$res[ $resKey ] = "" ;
			}
		}

		return $res ;
	}

	function normalizeAgency( $a , $t1 ) {
		$repl = array(
			0 => array() ,
			1 => array() ,
			2 => array(
				array( '/^\s*мир\.суд\.уч\.\s*\№?\s*(\d+)/i' , 'Миров{ой|ого|ому|ой|ым|ом} судебн{ый|ого|ому|ый|ым|ом} участ{ок|ка|ку|ок|ком|ке} №${1}' ) ,
			),
			3 => array() ,
			4 => array() ,
			5 => array() ,
			6 => array() ,
			'all' => array(
				array( '/ский(\W)/i' , 'ск{ий|ого|ому|ий|им|ом}${1}' ) ,
				array( '/ский$/i' , 'ск{ий|ого|ому|ий|им|ом}' ) ,
				array( '/ской(\W)/i' , 'ск{ой|ого|ому|ой|им|ом}${1}' ) ,
				array( '/ской$/i' , 'ск{ой|ого|ому|ой|им|ом}' ) ,
				array( '/(Арбитражн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,

				array( '/(суд)(\W)/i' , '${1}{|а|у||ом|е}${2}' ) ,
				array( '/(суд)$/i' , '${1}{|а|у||ом|е}' ) ,
				array( '/(районн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,
				array( '/(областн)ой(\W)/i' , '${1}{ой|ого|ому|ой|ым|ом}${2}' ) ,
				array( '/(Центральн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,
				array( '/(\W)(городск)ой(\W)/i' , '${1}${2}{ой|ого|ому|ой|им|ом}${3}' ) ,
				array( '/^(городск)ой(\W)/i' , '${1}{ой|ого|ому|ой|им|ом}${2}' ) ,

				array( '/(Белгородск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Воронежск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Выгоничск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Грибановск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Грязинск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Железнодорожн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,
				array( '/(Ивнянск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Калачеевск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Каширск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Коминтерновск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Курск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Левобережн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,
				array( '/(Ленинградск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Ленинск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Лискинск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Липецк)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Мичуринск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Нижнедевицк)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Нововоронежск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Новоусманск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Октябрьск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Павловск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Правобережн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,
				array( '/(Рамонск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Россошанск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Санкт-Петербургск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Семилукск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Советск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Старооскольск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Сыктывкарск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Таловск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Тербунск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Хохольск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				array( '/(Яковлевск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				//array( '/(Калачеевск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				//array( '/(Калачеевск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				//array( '/(Калачеевск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				//array( '/(Калачеевск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,
				//array( '/(Калачеевск)ий(\W)/i' , '${1}{ий|ого|ому|ий|им|ом}${2}' ) ,





				//array( '/(районн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,
				//array( '/(районн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,
				//array( '/(районн)ый(\W)/i' , '${1}{ый|ого|ому|ый|ым|ом}${2}' ) ,

				//array( '/(областн)ой(\W)/i' , '${1}{ой|ого|ому|ой|ым|ом}${2}' ) ,
				//array( '/(областн)ой(\W)/i' , '${1}{ой|ого|ому|ой|ым|ом}${2}' ) ,
				//array( '/(областн)ой(\W)/i' , '${1}{ой|ого|ому|ой|ым|ом}${2}' ) ,
				//array( '/(областн)ой(\W)/i' , '${1}{ой|ого|ому|ой|ым|ом}${2}' ) ,

			)
		);
		$rl = array_merge( $repl[ getCCGroup( $t1[ 'exp_type' ] ) ] , $repl[ 'all' ] );
		foreach ( $rl as $r ) {
			/*$vvv = array( $r[ 0 ] , $r[ 1 ] , $a );
			print_r_html( $vvv );*/
			$a = preg_replace( $r[ 0 ] , $r[ 1 ] , $a );
		}
		return $a ;
	}

	function normalizeAgent( $a , $t1 ) {
		$repl = array(
			0 => array(),
			1 => array(),
			2 => array(),
			3 => array(),
			4 => array(),
			5 => array() ,
			6 => array() ,
			'all' => array(
				array( "m" => '/судья ([а-я]+ [a-я]\.[a-я]\.)/i' , "p" => 'судь{я|и|е|ю|ей|е}' , "n" => 1 )
			)
		);

		$changes = array (
			'f' => array(
				array( 'm' => '/(?:ов|ев|ёв)$/' , 'r' => '/в$/' , 'e' => array(  "в" , "ва" , "ву" , "ва" , "вым" , "ве"  ) ) ,
				array( 'm' => '/ин$/' , 'r' => '/н$/' , 'e' => array(  "н" , "на" , "ну" , "на" , "ным" , "не"  )  ) ,
				array( 'm' => '/(?:ко|ич)$/' , 'r' => null  ) ,

				array( 'm' => '/(?:ова|ева|ёва|ина)$/' , 'r' => '/а$/' , 'e' => array(  "а" , "ой" , "ой" , "у" , "ой" , "ой"  )  ) ,
				array( 'm' => '/(?:вская)$/' , 'r' => '/ая$/' , 'e' => array(  "ая" , "ой" , "ой" , "ую" , "ой" , "ой"  )  ) ,

				array( 'm' => '/(?:сь|ик|их|им)$/' , 'r' => null  ) ,
			) ,

			'i' => array(
				array( 'm' => '/(?:нна|ина|дра|ста|ора|ата|ида|сса|ена|ада|аза|жда)$/i'  , 'r' => '/а$/i' , 'e' => array(  "а" , "ы" , "е" , "у" , "ой" , "е"  )  ) ,
				array( 'm' => '/(?:га)$/i'  , 'r' => '/а$/i' , 'e' => array(  "а" , "и" , "е" , "у" , "ой" , "е"  )  ) ,
				array( 'm' => '/(?:ия|ья|ая|йя)$/i' , 'r' => '/я$/i' , 'e' => array(  "я" , "и" , "и" , "ю" , "ей" , "и"  )  ) ,
				array( 'm' => '/(?:су|ль)$/i' , 'r' => null  ) ,

				array( 'm' => '/(?:ий)$/i' , 'r' => '/й$/i' , 'e' => array(  "й" , "я" , "ю" , "я" , "ем" , "и"  )  ) ,
				array( 'm' => '/(?:ей)$/i' , 'r' => '/й$/i' , 'e' => array(  "й" , "я" , "ю" , "я" , "ем" , "е"  )  ) ,
				array( 'm' => '/(?:тр|др|дор)$/i' , 'r' => '/р$/i' , 'e' => array(  "р" , "ра" , "ру" , "ра" , "ром" , "ре"  )  ) ,
				array( 'm' => '/(?:ан|ен|ён)$/i' , 'r' => '/н$/i' , 'e' => array(  "н" , "на" , "ну" , "на" , "ном" , "не"  )  ) ,
				array( 'm' => '/(?:ем|ём)$/i' , 'r' => '/м$/i' , 'e' => array(  "м" , "ма" , "му" , "ма" , "мом" , "ме"  )  ) ,
				array( 'm' => '/(?:ат)$/i' , 'r' => '/т$/i' , 'e' => array(  "т" , "та" , "ту" , "та" , "том" , "те"  )  ) ,
			) ,

			'o' => array(
				array( 'm' => '/вич$/' , 'r' => '/ч$/' , 'e' => array(  "ч" , "ча" , "чу" , "ча" , "чем" , "че"  )  ) ,

				array( 'm' => '/вна$/' , 'r' => '/а$/' , 'e' => array(  "а" , "ы" , "е" , "у" , "ой" , "е"  )  ) ,
			)
		);



		$rl = array_merge( $repl[ getCCGroup( $t1[ 'exp_type' ] ) ] , $repl[ 'all' ] );
		$res = array( "a" => "" , "p" => "" , "n" => "" );
		foreach ( $rl as $r ) {
			$m = array();
			$n = preg_match( $r[ "m" ] , $a , $m );
			if ( $n == 1 ) {
				$res[ "p" ] = $r[ "p" ];
				if ( is_numeric( $r[ "n" ] ) ) {
					$res[ "n" ] = $m[ $r[ "n" ] ];
				}
			}
		}


		$res[ "n" ] = preg_replace_callback(
			'/([а-я]+) ([a-я]\.[a-я]\.)/i' ,
			function( $matches ) use ( $changes ) {
				$nm = $changes[ 'f' ];
				$fen = $matches[ 1 ];
				$elt = array_fill( 1 , 6 , '' );
				$elt[ 0 ] = $fen ;
				for( $i = 0 ; $i < count( $nm ) ; ++$i ) {
					$m = array();
					$mc = preg_match( $nm[ $i ][ 'm' ] , $fen , $m );
					//$m =  fen.match( nm[ i ].m );
					if ( $mc == 1 ) {
						for( $j = 1 ; $j < 6 ; ++$j ) {
							if ( $nm[ $i ][ 'r' ] != null ) {
								$elt[ $j ] = preg_replace( $nm[ $i ][ 'r' ] , $nm[ $i ][ 'e' ][ $j ] , $fen );
							} else {
								$elt[ $j ] = $fen ;
							}
						}
						break ;
					}
				}

				return packFormsES( $elt ).' '.$matches[ 2 ];
			} ,
			$res[ "n" ]
		);

		if ( $res[ "p" ] != "" ) {
			$res[ "a" ] = $res[ "p" ]." ".$res[ "n" ];
		} else {
			$res[ "a" ] = $a ;
		}

		return array( $res[ "a" ] , $res[ "p" ] , $res[ "n" ] );
	}

	function readTextHTML( DOMNode $n , $id , $opt = null ) {
		global $docVar , $exClasses , $listsData ;

		$hlcm = array(
			"g" => "#0f0" ,
			"r" => "#f00" ,
			"n" => "#f8f" ,
			"-none" => "none"
		);

		$taMap = array(
			"justified" => "justify"
		);

		$read_align = function ( &$style , $cn ) use ( $taMap ) {
			if ( $cn->hasAttribute( "align" ) ) {
				$av = $cn->getAttribute( "align" );
				$avm = strexp( "{left,center,right,justified}" );
				$avm = array_combine( $avm , $avm );
				$av = isset( $avm[ $av ] ) ? $av : "justified" ;
			} else {
				$av = TEXT_ALIGN_JUSTIFIED ;
			}

			if ( isset( $taMap[ $av ] ) ) {
				$av = $taMap[ $av ];
			}

			$style[ "text-align" ] = $av ;
		};

		$read_border = function ( &$style , $cn ) {
			if ( $cn->hasAttribute( "border" ) ) {
				$bv = $cn->getAttribute( "border" );
				if ( $bv == "none" ) {
					$style[ "border" ] = "none" ;
				}
			} else {
			}
		};


		$read_simple_flag = function ( &$style , $cn , $flagName , $propName , $valTrue = true , $valFalse = false ) {
			if ( $cn->hasAttribute( $flagName ) && $cn->getAttribute( $flagName ) == true ) {
				$set = true ;
			} else {
				$set = false ;
			}

			if ( $set ) {
				$style[ $propName ] = $valTrue ;
			} else
			if ( $valFalse !== false ) {
				$style[ $propName ] = $valFalse ;
			}
		};

		$read_param_func = function ( &$style , $cn , $paramName , $funcName , $correctFunc = null , $default = null ) {
			if ( is_array( $funcName ) ) {
				$do = function( $v ) use ( $funcName ) {
					$obj = $funcName[ "obj" ];
					$func = $funcName[ "func" ];
					$obj->$func( $v );
				};
			} else {
				$do = function( $v ) use ( $doc , $funcName ) {
					$doc->$funcName( $v );
				};
			}

			if ( $cn->hasAttribute( $paramName ) ) {
				$v = $cn->getAttribute( $paramName );
				if ( $correctFunc !== null ) {
					$v = $correctFunc( $v );
				}
				$do( $v );
			} else {
				if ( $default !== null ) {
					$do( $default );
				}
			}
		};

		$read_param_prop = function ( &$style , $cn , $paramName , $propName , $correctFunc = null , $default = null ) {
			if ( $cn->hasAttribute( $paramName ) ) {
				$v = $cn->getAttribute( $paramName );
				if ( $correctFunc !== null ) {
					$v = $correctFunc( $v );
				}
				$style[ $propName ] = $v ;
			} else {
				if ( $default !== null ) {
					$style[ $propName ] = $default ;
				}
			}
		};

		$read_list_info = function ( &$style , &$class , &$data , $cn ) use ( $read_param_prop ) {
			global $exClasses , $listsData ;
			$read_param_prop( $style , $cn , "list" , "counter-reset" );
			$read_param_prop( $style , $cn , "list-item" , "counter-increment" );
			if ( $cn->hasAttribute( "list" ) ) {
				$ldef = $cn->getAttribute( "list" );
				$m = array();
				$ldc = preg_match_all( '/(?<=\s|^)[-a-zA-Z][-a-zA-Z\d]*(?:\s+\d+)?(?=\s|$)/' , $ldef , $m );
				error_log( print_r( $m , 1 ) );
				if ( $ldc === false && $ldc < 1 ) {
					return ;
				} else {
					foreach ( $m[ 0 ] as $cld ) {
						$m2 = array();
						preg_match( '/(?<name>\S+)(?:\s+(?<count>\d+))?/' , $cld , $m2 );
						$listItemName = $m2[ "name" ];
						$listItemCount = isset( $m2[ "count" ] ) ? $m2[ "count" ] : 0 ;
						$listData[ $listItemName ] = array( "formula" => "counter(".$listItemName.") \".\"" );
						if ( $cn->hasAttribute( "list-item-formula" ) ) {
							$listData[ $listItemName ][ "formula" ] = str_replace( "'" , "\"", $cn->getAttribute( "list-item-formula" ) );
						}
						$exClasses[ "*[data-list-item~=\"".$listItemName."\"]:before" ] = "content : ".iconv( "utf8" , "cp1251" , $listData[ $listItemName ][ "formula" ] ).";padding-right: 0.25cm" ;
					}
				}
			}

			if ( $cn->hasAttribute( "list-item" ) ) {
				$data[ "list-item" ] = $cn->getAttribute( "list-item" );
			}
		};

		$buildStyle = function ( $style ) {
			$res = array();
			foreach( $style as $spn => $spv ) {
				$res[]= $spn.":".$spv ;
			}
			if ( count( $res ) > 0 ) {
				return "style=\"".implode( ";" , $res )."\"" ;
			} else {
				return "" ;
			}
		};

		$buildClass = function ( $classes ) {
			if ( count( $classes ) > 0 ) {
				return "class=\"".implode( " " , $classes )."\"" ;
			} else {
				return "" ;
			}
		};

		$buildDataset = function ( $data ) {
			if ( count( $data ) > 0 ) {
				$res = array();
				foreach( $data as $name => $value ) {
					$res[]= "data-".$name."=\"".$value."\"" ;
				}
				return implode( " " , $res );
			} else {
				return "" ;
			}
		};


		$res = "" ;
		foreach ( $n->childNodes as $cn ) {
			if ( $cn->nodeType == XML_TEXT_NODE ) {
				$res.= iconv( "utf8" , "cp1251" , $cn->wholeText );
			} else {
				switch ( $cn->nodeName ) {
					case "p" :
						$cStyle = array();
						$cClass = array( "doc-p" );
						$cData = array();
						$read_align( $cStyle , $cn );
						$read_simple_flag( $cStyle , $cn , "bold" , "font-weight" , "bold" , "normal" );
						$read_simple_flag( $cStyle , $cn , "italic" , "font-style" , "italic" , "normal" );
						$read_param_prop( $cStyle , $cn , "indent" , "text-indent" , function( $v ) { return $v == false ? "0" : $v ; } , "1cm" );
						$read_list_info( $cStyle , $cClass , $cData , $cn );
						$res.= "<div ".$buildClass( $cClass )." ".$buildStyle( $cStyle )." ".$buildDataset( $cData ).">".readTextHTML( $cn , $id )."</div>" ;
						break ;

					case "table" :
						if ( $opt === null ) {
							$opt = array();
						}
						$opt[]= array( "type" => "tag.table" );
						$res.= "<div class=\"doc-table\">".readTextHTML( $cn , $id , $opt )."</div>" ;
						array_pop( $opt );
						break ;

					case "tr" :
						$le = end( $opt );
						$opt[]= array( "type" => "tag.row" );
						$res.= "<div class=\"doc-tr-wrapper\"><div class=\"doc-tr\">".readTextHTML( $cn , $id , $opt )."</div></div>" ;
						array_pop( $opt );
						break ;

					case "td" :
						$le = end( $opt );

						$cStyle = array();
						$read_border( $cStyle , $cn );
						$read_param_prop( $cStyle , $cn , "width" , "width" );
						$read_align( $cStyle , $cn );
						$read_simple_flag( $cStyle , $cn , "bold" , "font-weight" , "bold" , "normal" );
						$read_simple_flag( $cStyle , $cn , "italic" , "font-style" , "italic" , "normal" );
						$read_param_prop( $cStyle , $cn , "indent" , "text-indent" , function( $v ) { return $v == false ? "0" : $v ; } , "0cm" );
						$res.= "<div class=\"doc-td\" ".$buildStyle( $cStyle ).">".readTextHTML( $cn , $id , $opt )."</div>" ;

						break ;

					case "var" :
						$vn = $cn->getAttribute( "name" );
						$vf = $cn->getAttribute( "form" );
						$vh = $cn->getAttribute( "hl" );
						$br = ( $cn->hasAttribute( "break-rule" ) ? $cn->getAttribute( "break-rule" ) : false );
						if ( $vh == "" ) {
							$vhs = "-none" ;
						} else {
							$vhs = $vh ;
						}
						if ( $id !== false ) {
							if ( isset( $docVar[ $vn ] ) ) {
								$vv = $docVar[ $vn ][ "value" ];
								if ( $vf != "" ) {
									$vv = inForm( $vv , intval( $vf ) );
								}
							}
						} else {
							if ( isset( $docVar[ $vn ] ) ) {
								$vv = $docVar[ $vn ][ "desc" ];
								if ( $vf != "" ) {
									$vv.= " [ ".inForm( "{И|Р|Д|В|Т|О}" , intval( $vf ) )." ]" ;
								}
							}
						}

						if ( isset( $docVar[ $vn ] ) ) {
							if ( $br !== false ) {
								$vv = breakLineByRule( $vv , $br );
							}
							$res.= "<span data-var-name=\"".$vn."\" data-form=\"".$vf."\" data-var-hl=\"".$vh."\" class=\"thl".$vhs."\" title=\"".$docVar[ $vn ][ "desc" ]."\">".$vv."</span>" ;
						} else {
							$res.= "<span data-var-name=\"".$vn."\" data-form=\"".$vf."\" data-var-hl=\"".$vh."\" class=\"thle\" title=\"".$vn."\">ОШИБКА</span>" ;
						}

						break ;

					case "image" :
						$it = $cn->getAttribute( "type" );
						$vn = $cn->getAttribute( "src" );
						$cStyle = array();
						$read_param_prop( $cStyle , $cn , "width" , "width" );
						$read_param_prop( $cStyle , $cn , "height" , "height" );

						switch( $it ) {
							case "barcode" :
								$vf = $cn->getAttribute( "form" );
								$br = ( $cn->hasAttribute( "break-rule" ) ? $cn->getAttribute( "break-rule" ) : false );

								if ( $id !== false ) {
									if ( isset( $docVar[ $vn ] ) ) {
										$vv = $docVar[ $vn ][ "value" ];
										if ( $vf != "" ) {
											$vv = inForm( $vv , intval( $vf ) );
										}
									}
								} else {
									if ( isset( $docVar[ $vn ] ) ) {
										$vv = $docVar[ $vn ][ "desc" ];
										if ( $vf != "" ) {
											$vv.= " [ ".inForm( "{И|Р|Д|В|Т|О}" , intval( $vf ) )." ]" ;
										}
									}
								}

								if ( isset( $docVar[ $vn ] ) ) {
									if ( $br !== false ) {
										$vv = breakLineByRule( $vv , $br );
									}
									$res.= "<img data-var-name=\"".$vn."\" data-form=\"".$vf."\" src=\"/barcode.php?src=".urlencode( $vv )."\" ".$buildStyle( $cStyle )." title=\"".$docVar[ $vn ][ "desc" ]."\">" ;
								} else {
									$res.= "<img data-var-name=\"".$vn."\" data-form=\"".$vf."\" src=\"/barcode.php?src=ОШИБКА\" ".$buildStyle( $cStyle )." title=\"".$vn."\">" ;
								}

								break ;

							case "local" :
							case "remote" :
								$res.= "<img src=\"\">" ;
								break ;
						}

						break ;

					case "block" :
						$pbStyle = array();
						$cStyle = array();
						$read_param_prop( $cStyle , $cn , "list" , "counter-reset" );
						if ( $cn->hasAttribute( "pagebreak" ) ) {
							$avPB = $cn->getAttribute( "pagebreak" );
						} else {
							$avPB = "none" ;
						}
						if ( $avPB == "before" || $avPB == "both" ) {
							$pbStyle[]= "pb-before" ;
						}
						//readTextRTF( $cn , $id , $doc );
						if ( $avPB == "after" || $avPB == "both" ) {
							$pbStyle[]= "pb-after" ;
						}

						$res.= "<div class=\"".( count( $pbStyle ) > 0 ? implode( " " , $pbStyle ) : "" )."\" ".$buildStyle( $cStyle ).">".readTextHTML( $cn , $id )."</div>" ;
						break ;
					default :
						$res.= "<".$cn->nodeName.">".readTextHTML( $cn , $id )."</".$cn->nodeName.">" ;
						break ;
				}
			}
		}
		return $res ;
	}

	function readTextRTF( DOMNode $n , $id , RTFDocument $doc , $opt = null ) {
		global $docVar ;

		$hlcm = array(
			"g" => "#0f0" ,
			"r" => "#f00" ,
			"n" => "#f8f" ,
			"-none" => "none"
		);

		$taMap = array(
			"justified" => "justify"
		);

		$read_align = function ( $cn ) use ( $doc ) {
			if ( $cn->hasAttribute( "align" ) ) {
				$av = $cn->getAttribute( "align" );
				$avm = strexp( "{left,center,right,justified}" );
				$avm = array_combine( $avm , $avm );
				$av = isset( $avm[ $av ] ) ? $av : "justified" ;
				$doc->setTextAlign( $av );
			} else {
				$doc->setTextAlign( TEXT_ALIGN_JUSTIFIED );
			}
		};

		$read_border = function ( $cn , $cell ) use ( $doc ) {
			if ( $cn->hasAttribute( "border" ) ) {
				$bv = $cn->getAttribute( "border" );
				if ( $bv == "none" ) {
					$cell->setBorders( "ltrb" , "none" );
				}
			} else {
			}
		};

		$read_simple_flag = function ( $cn , $flagName , $tagName ) use ( $doc ) {
			if ( $cn->hasAttribute( $flagName ) && $cn->getAttribute( $flagName ) == true ) {
				$doc->addTag( $tagName );
				$set = true ;
			} else {
				$doc->addTag( $tagName."0" );
				$set = false ;
			}

			return array( "tag" => $tagName , "set" => $set );
		};

		$read_param_func = function ( $cn , $paramName , $funcName , $correctFunc = null , $default = null ) use ( $doc ) {
			if ( is_array( $funcName ) ) {
				$do = function( $v ) use ( $funcName ) {
					$obj = $funcName[ "obj" ];
					$func = $funcName[ "func" ];
					$obj->$func( $v );
				};
			} else {
				$do = function( $v ) use ( $doc , $funcName ) {
					$doc->$funcName( $v );
				};
			}

			if ( $cn->hasAttribute( $paramName ) ) {
				$v = $cn->getAttribute( $paramName );
				if ( $correctFunc !== null ) {
					$v = $correctFunc( $v );
				}
				$do( $v );
			} else {
				if ( $default !== null ) {
					$do( $default );
				}
			}
		};

		$read_param_prop = function ( $cn , $paramName , $propName , $correctFunc = null , $default = null ) use ( $doc ) {
			if ( is_array( $propName ) ) {
				$set = function( $v ) use ( $propName ) {
					$obj = $propName[ "obj" ];
					$prop = $propName[ "prop" ];
					$obj->$prop =  $v ;
				};
			} else {
				$set = function( $v ) use ( $doc , $propName ) {
					$doc->$propName( $v );
				};
			}


			if ( $cn->hasAttribute( $paramName ) ) {
				$v = $cn->getAttribute( $paramName );
				if ( $correctFunc !== null ) {
					$v = $correctFunc( $v );
				}
				$set( $v );
			} else {
				if ( $default !== null ) {
					$set( $default );
				}
			}
		};

		$listModStyleMap = array(
			"decimal" => 0 ,
			"decimal-leading-zero" => 22 ,
			"upper-roman" => 1 ,
			"lower-roman" => 2 ,
			"upper-latin" => 3 ,
			"lower-latin" => 4
		);

		$read_list_info = function ( $cn ) use ( $doc , $read_param_prop , $listModStyleMap ) {
			global $listsData ;
			if ( $cn->hasAttribute( "list" ) ) {
				$ldef = $cn->getAttribute( "list" );
				$m = array();
				$ldc = preg_match_all( '/(?<=\s|^)[-a-zA-Z][-a-zA-Z\d]*(?:\s+\d+)?(?=\s|$)/' , $ldef , $m );
				if ( $ldc === false && $ldc < 1 ) {
					return ;
				} else {
					foreach ( $m[ 0 ] as $cld ) {
						$m2 = array();
						preg_match( '/(?<name>\S+)(?:\s+(?<count>\d+))?/' , $cld , $m2 );
						$listItemName = $m2[ "name" ];
						$listItemCount = isset( $m2[ "count" ] ) ? $m2[ "count" ] : 0 ;

						$clif = "counter(".$listItemName.") \".\"" ;
						if ( $cn->hasAttribute( "list-item-formula" ) ) {
							$clif = str_replace( "'" , "\"", $cn->getAttribute( "list-item-formula" ) );
						}
						preg_match_all( '/(?:"(?:[^\\"]|\\.)*")|(?:counter\(\s*[-a-zA-Z][-a-zA-Z\d]*\s*(?:,\s*[-a-z]+\s*)?\))/' , $clif , $m3 );

						$tli = array();
						$clif = array();

						foreach( $m3[ 0 ] as $cfp ) {
							$cc = preg_match( '/(?:counter\(\s*(?<name>[-a-zA-Z][-a-zA-Z\d]*)\s*(?:,\s*(?<mod>[-a-z]+)\s*)?\))/' , $cfp , $m4 );
							if ( $cc == 1 ) {
								if ( !isset( $m4[ "mod" ] ) ) {
									$m4[ "mod" ] = "decimal" ;
								}
								$cci = array(
									"name" => $m4[ "name" ] ,
									"mod" => ( isset( $listModStyleMap[ $m4[ "mod" ] ] ) ? $listModStyleMap[ $m4[ "mod" ] ] : intval( $m4[ "mod" ] ) ) ,
									"listIndex" => $doc->getListIndex( $m4[ "name" ] , false ) ,
									"index" => false
								);
								$tli[]= &$cci ;
								$clif[]= &$cci[ "index" ];
								unset( $cci );
							} else {
								$clif[]= iconv( "utf8" , "cp1251" , trim( $cfp , "\"" ) ) ;
							}
						}

						if ( count( $tli ) == 1 ) {
							$cc = &$tli[ 0 ];
							if ( isset( $listsData[ $listItemName ] ) ) {
								$d = &$listsData[ $listItemName ];
								$ovid = $doc->getListOverrideIndex( $d[ "id" ] );
								$d[ "ovid" ] = $ovid ;
								error_log_ml( print_r( $listsData , 1 ) );
							} else {
								$clID = $doc->getListIndex( $listItemName );
								$cc[ "index" ] = chr( 0 );
								$doc->mkListLevel( $clID , implode( $clif ) , $listItemCount + 1 , $cc[ "mod" ] );
								$ovid = $doc->getListOverrideIndex( $clID );
								$listsData[ $listItemName ] = array(
									"name" => $listItemName ,
									"id" => $clID ,
									"root" => $listItemName ,
									"lvlid" => 1 ,
									"ovid" => $ovid
								);
								error_log_ml( print_r( $listsData , 1 ) );
							}
							unset( $cc );
						} else {

						}
					}
				}
			}
			if ( $cn->hasAttribute( "list-item" ) ) {
				$lin = $cn->getAttribute( "list-item" );
				if ( isset( $listsData[ $lin ] ) ) {
					$cld = $listsData[ $lin ];
					$doc->addTag( "ls".$cld[ "ovid" ] );
					if ( $cld[ "lvlid" ] > 1 ) {
						$doc->addTag( "ilvl" + ( $cld[ "lvlid" ] - 1 ) );
					}
				} else {
				}
			} else {
				$doc->addTag( "ls0" );
			}
		};

		$clear_simple_flag = function ( $fd ) use ( $doc ) {
			if ( $fd[ "set" ] ) {
				$doc->addTag( $fd[ "tag" ]."0" );
			}
		};

		foreach ( $n->childNodes as $cn ) {
			if ( $cn->nodeType == XML_TEXT_NODE ) {
				$t = iconv( "utf8" , "cp1251" , $cn->wholeText );
				//$t = preg_replace( '/\s+/' , ' ' , $t );
				$doc->addText( $t );
			} else {
				switch ( $cn->nodeName ) {
					case "p" :
						$read_align( $cn , $doc );
						$sfBold = $read_simple_flag( $cn , "bold" , "b" );
						$sfItalic = $read_simple_flag( $cn , "italic" , "i" );
						$read_param_func( $cn , "indent" , "setFirstLineIndent" , function( $v ) { return $v == false ? "0" : $v ; } , "1cm" );
						$read_list_info( $cn );
						readTextRTF( $cn , $id , $doc );
						$clear_simple_flag( $sfItalic );
						$clear_simple_flag( $sfBold );
						$doc->addTextLine();
						break ;
					case "b" :
						$doc->addTag( "b" );
						readTextRTF( $cn , $id , $doc );
						$doc->addTag( "b0" );
						break ;
					case "i" :
						$doc->addTag( "i" );
						readTextRTF( $cn , $id , $doc );
						$doc->addTag( "i0" );
						break ;
					case "mark" :
						$color = $cn->getAttribute( 'color' );
						error_log( 'DBG: color "'.$color.'"' );
						if ( $color == '' ) {
							$color = '#f00' ;
						}
						$doc->setHighlight( $color );
						readTextRTF( $cn , $id , $doc );
						$doc->setHighlight();
						break ;
					case "table" :
						$tab = $doc->addTable();
						if ( $opt === null ) {
							$opt = array();
						}
						$opt[]= array( "type" => "tag.table" , "data" => $tab );
						$read_list_info( $cn );
						readTextRTF( $cn , $id , $doc , $opt );
						$doc->setMainContext();
						array_pop( $opt );
						break ;
					case "tr" :
						$le = end( $opt );
						$tab = $le[ "data" ];
						$row = $tab->insertRow();
						$opt[]= array( "type" => "tag.row" , "data" => $row );
						$read_list_info( $cn );
						readTextRTF( $cn , $id , $doc , $opt );
						array_pop( $opt );
						break ;
					case "td" :
						$le = end( $opt );
						$row = $le[ "data" ];
						$cell = $row->insertCell();
						$read_border( $cn , $cell );
						//$opt[]= array( "type" => "tag.cell" , "data" => $cell );
						$doc->setTableCellContext( $cell );
						$read_param_prop( $cn , "width" , array( "obj" => $cell , "prop" => "width" ) );
						$read_align( $cn , $doc );
						$sfBold = $read_simple_flag( $cn , "bold" , "b" );
						$sfItalic = $read_simple_flag( $cn , "italic" , "i" );
						$read_param_func( $cn , "indent" , "setFirstLineIndent" , function( $v ) { return $v == false ? "0" : $v ; } , "0cm" );
						$read_list_info( $cn );
						readTextRTF( $cn , $id , $doc , $opt );
						$clear_simple_flag( $sfItalic );
						$clear_simple_flag( $sfBold );
						break ;
					case "var" :
						$vn = $cn->getAttribute( "name" );
						$vf = $cn->getAttribute( "form" );
						$vh = $cn->getAttribute( "hl" );
						$br = ( $cn->hasAttribute( "break-rule" ) ? $cn->getAttribute( "break-rule" ) : false );
						if ( $vh == "" ) {
							$vhs = "-none" ;
						} else {
							$vhs = $vh ;
						}

						if ( $id !== false ) {
							if ( isset( $docVar[ $vn ] ) ) {
								$vv = $docVar[ $vn ][ "value" ];
								if ( $vf != "" ) {
									$vv = inForm( $vv , intval( $vf ) );
								}
								if ( $br !== false ) {
									$vv = breakLineByRule( $vv , $br , "\r\n" );
								}

								if ( $vv == "" ) {
									$doc->setHighlight( $hlcm[ $vhs ] )->addText( $docVar[ $vn ][ "desc" ] )->setHighlight();
								} else {
									$doc->setHighlight( $hlcm[ $vhs ] )->addText( $vv )->setHighlight();
								}
							} else {
								$doc->setHighlight( $hlcm[ $vhs ] )->addText( "ОШИБКА" )->setHighlight();
							}
						}

						break ;

					case "image" :
						$it = $cn->getAttribute( "type" );
						switch( $it ) {
							case "barcode" :
								break ;
						}
						break ;

					case "block" :
						if ( $cn->hasAttribute( "pagebreak" ) ) {
							$avPB = $cn->getAttribute( "pagebreak" );
						} else {
							$avPB = "none" ;
						}
						if ( $avPB == "before" || $avPB == "both" ) {
							$doc->addTag( "page" );
						}
						$read_list_info( $cn );
						readTextRTF( $cn , $id , $doc );
						if ( $avPB == "after" || $avPB == "both" ) {
							$doc->addTag( "page" );
						}
						break ;

					default :
						$read_list_info( $cn );
						readTextRTF( $cn , $id , $doc );
						//$doc->addTextLine();
						break ;
				}
			}
		}
		return $doc ;
	}

	function fillDataBank( $tmplData , $type , $id ) {
		$data = loadVariablesInit( $tmplData );

		switch ( $type ) {
			case "expertize" :
				$dataEx = loadVariables_Expertize( $tmplData , $id );
				break ;

			default :
				$dataEx = array();
				break ;
		}

		$data = array_merge( $dataEx , $data );
		return $data ;
	}

	function loadVariablesInit( $tmplData ) {
		global $portalDB , $dbConfigFull , $tabDepartments , $tabWorkers , $tabPosts , $MonthNames ;
		global $UserOrgIndex , $UserName ;

		$tabDepartments = $portalDB->table( "departments" , "id" );
		$tabWorkers = $portalDB->query( "select `t1`.* , `t2`.`phone` from `workers` as `t1` left join `cabinet` as `t2` on `t1`.`cab` = `t2`.`id`" , "id" );
		$tabPosts = $portalDB->table( "posts" , "id" );

		$tmplExtVar = json_decode( iconv( "cp1251" , "utf8" , $tmplData[ "ext-var" ] ) , true );

		$docVar = array();

		foreach ( $tmplExtVar as $ev ) {
			$docVar[ "ext:".$ev[ "name" ] ] = array(
				"value" => "" ,
				"desc" => iconv( "utf8" , "cp1251" , $ev[ "desc" ] ),
				"mf" => false ,
				"editable" => $ev
			);
		}

		$ct = time();
		$ct_dmonthY = "\xAB".date( "d" , $ct )."\xBB ".$MonthNames[ intval( date( "m" , $ct ) ) ]." ".date( "Y" , $ct );

		$docVar = array_merge( $docVar , array(
			"env:date-d.m.Y" => array( "value" => date( "d.m.Y" , $ct ) , "desc" => "Окружение > Текущая дата" , "mf" => false ) ,
			"env:date-d month Y" => array( "value" => $ct_dmonthY , "desc" => "Окружение > Текущая дата" , "mf" => true ) ,
			"env:user-name" => array( "value" => NAMES_Format( NAMES_parse( $UserName ) , "%F0 %I0 %O0" ) , "desc" => "Пользователь > Фамилия Имя Отчество" , "mf" => true ),
			"env:user-name1" => array( "value" => NAMES_Format( NAMES_parse( $UserName ) , "%F0 %i.%o." ) , "desc" => "Пользователь > Фамилия И.О." , "mf" => true ),
			"env:user-name2" => array( "value" => NAMES_Format( NAMES_parse( $UserName ) , "%i.%o. %F0" ) , "desc" => "Пользователь > И.О. Фамилия" , "mf" => true ),
		) );

		foreach( $dbConfigFull as $c ) {
			if ( !is_null( $c[ "e-data" ] ) ) {
				$ced = json_decode( $c[ "e-data" ] , true );
			} else {
				$ced = array();
			}

			if ( isset( $ced[ "d-tmpl" ] ) && $ced[ "d-tmpl" ] == 0 ) {
				continue ;
			}

			$ccmf = isset( $ced[ "mf" ] ) && $ced[ "mf" ] == 1 ;

			$docVar[ "cfg:".$c[ "name" ] ] = array(
				"value" => $c[ "value" ] ,
				"desc" => $c[ "description" ] ,
				"mf" => $ccmf
			);
		}

		$docVar = array_merge( $docVar , array(
			"tmpl:name"       => array( "value" => $tmplData[ "name" ]       , "desc" => "шаблон : Название документа полное" , "mf" => false ) ,
			"tmpl:name-short" => array( "value" => $tmplData[ "short_name" ] , "desc" => "шаблон : Название документа краткое" , "mf" => false )
		) );

		return $docVar ;
	}

	function loadVariables2_post_init_tmpl( &$param , &$docVar ) {
		global $dbConfigFull ;
		$tmplData = $param[ 'tmpl-data' ];
		if ( $tmplData !== false ) {
			$docVar = array_merge( $docVar , array(
				"tmpl:name"       => array( "value" => $tmplData[ "name" ]       , "desc" => "шаблон : Название документа полное" , "mf" => false ) ,
				"tmpl:name-short" => array( "value" => $tmplData[ "short_name" ] , "desc" => "шаблон : Название документа краткое" , "mf" => false )
			) );
			$tmplExtVar = json_decode( iconv( "cp1251" , "utf8" , $tmplData[ "ext-var" ] ) , true );
			foreach ( $tmplExtVar as $ev ) {
				$docVar[ "ext:".$ev[ "name" ] ] = array(
					"value" => "" ,
					"desc" => iconv( "utf8" , "cp1251" , $ev[ "desc" ] ),
					"mf" => false ,
					"editable" => $ev
				);
			}
		}

		if ( isset( $param[ 'tmpl-list-name' ] ) ) {
			$docVar = array_merge( $docVar , array(
				"env:tmpl-list-name" => array( "value" => $param[ 'tmpl-list-name' ] , "desc" => "Вид списка шаблонов" , "mf" => false ) ,
			) );
		}

		foreach( $dbConfigFull as $c ) {
			if ( !is_null( $c[ "e-data" ] ) ) {
				$ced = json_decode( $c[ "e-data" ] , true );
			} else {
				$ced = array();
			}

			if ( isset( $ced[ "d-tmpl" ] ) && $ced[ "d-tmpl" ] == 0 ) {
				unset( $docVar[ "cfg:".$c[ "name" ] ] );
			}
		}

	}

	function loadVariables_Expertize( $tmplData , $expertize_id ) {
		global $portalDB , $dbConfig , $dbConfigFull , $tabDepartments , $tabWorkers , $tabPosts , $tabSpecGroups ;
		global $TAB_CASECATEGORY , $tabTypeOfAgency ;
		global $UserOrgIndex , $UserName ;

		$tabDepartments = $portalDB->table( "departments" , "id" );
		$tabWorkers = $portalDB->query( "select `t1`.* , `t2`.`phone` from `workers` as `t1` left join `cabinet` as `t2` on `t1`.`cab` = `t2`.`id`" , "id" );
		$tabPosts = $portalDB->table( "posts" , "id" );
		$tabSpecGroups = $portalDB->query( "select `t2`.`id` , `t1`.`name` from `specialities-groups` as `t1` , `specialities` as `t2` where `t2`.`group` = `t1`.`id`" , "id" );
		foreach ( $tabSpecGroups as &$sp ) {
			$sp = $sp[ "name" ];
		} unset( $sp );

		$tabCaseCategory = array_column( $TAB_CASECATEGORY , 'name' , 'id' );
		$tabCaseCategoryIndexes = array_column( $TAB_CASECATEGORY , 'index' , 'id' );
		$tabTypeOfAgency = $portalDB->table( "type-of-agency" , "id" );

		$t1 = $portalDB->row( "select `t1`.* , ifnull( `t1`.`group_id` , 0 ) as `group_id_norm` , `t4`.`ext_id` as `agency_type` , `t4`.`name` as `agency` , `t4`.`destination` as `agency_address` , `t5`.`name` as `agent` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` ,  `agency` as `t4` , `agent` as `t5` where ( `t3`.`id` = ? ) and ( `t4`.`id` = `t1`.`from_agency` ) and ( `t5`.`id` = `t1`.`from_agent` ) and ( `t2`.`mat_id` = `t1`.`id` ) and ( `t3`.`ext_id` = `t2`.`id` )" , "i" , $expertize_id );
		if ( $t1 === false ) {
			return false ;
		}

		if ( $t1[ "group_id_norm" ] != 0 ) {
			$t1All = $portalDB->query( "select `t1`.* , `t2`.`dep_id` , `t3`.`price` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `group_id` = ? )" , false , "i" , $t1[ "group_id_norm" ] );
		} else {
			$t1All = $portalDB->query( "select `t1`.* , `t2`.`dep_id` , `t3`.`price` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `t1`.`id` = ? )" , false , "s" , $t1[ "id" ] );
		}
		$nums = array();
		$totalPrice = 0 ;
		foreach( $t1All as $ct1 ) {
			$nums[]= matincomingNumberFull( $ct1[ "id" ] , $ct1[ "dep_id" ] , $ct1[ "exp_type" ] );
			$totalPrice+= $ct1[ "price" ];
		}
		$nums = array_unique( $nums );

		$t1[ "agency" ] = normalizeAgency( $t1[ "agency" ] , $t1 );
		list( $t1[ "agent" ] , $agentPost , $agentName ) = normalizeAgent( $t1[ "agent" ] , $t1 );

		$row = $portalDB->row( "select `t2`.`mat_id` , `t2`.`dep_id` , `t3`.* from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t3`.`id` = ? ) and ( `t3`.`ext_id` = `t2`.`id` )" , "i" , $expertize_id );
		$worker = $tabWorkers[ $row[ "exp_id" ] ];
		$name = NAMES_parse( $worker[ "name" ] );

		$caseData = extractCaseData( $t1 );

		$bcs = getCharIDStructure( $row[ "mat_id" ] );
		$bcs[ "t" ] = $tmplData[ "code" ];

		$docVar = array(
			"expert-name" => array( "value" => NAMES_Format( $name , "%F0 %I0 %O0" ) , "desc" => "Эксперт > Фамилия Имя Отчество" , "mf" => true ),
			"expert-name1" => array( "value" => NAMES_Format( $name , "%F0 %i.%o." ) , "desc" => "Эксперт > Фамилия И.О." , "mf" => true ),
			"expert-name2" => array( "value" => NAMES_Format( $name , "%i.%o. %F0" ) , "desc" => "Эксперт > И.О. Фамилия" , "mf" => true ),
			"expert-post" => array( "value" => $tabPosts[ $worker[ "post_1_id" ] ][ "name" ] , "desc" => "Эксперт > Должность > Название" , "mf" => false ),
			"expert-post-simple" => array( "value" => $tabPosts[ $worker[ "post_1_id" ] ][ "simple_name" ] , "desc" => "Эксперт > Должность > Название (упрощенное)" , "mf" => false ),
			"expert-department" => array( "value" => $tabDepartments[ $worker[ "dep" ] ][ "name" ] , "desc" => "Эксперт > Отдел > Название" , "mf" => false ),
			"expert-department-short" => array( "value" => $tabDepartments[ $worker[ "dep" ] ][ "short_name" ] , "desc" => "Эксперт > Отдел > Название (Краткое)" , "mf" => false ),
			"expert-phone" => array( "value" => $worker[ "phone" ] , "desc" => "Эксперт > Отдел > Номер телефона" , "mf" => false ),
		//// № ".matincomingNumber( $t1[ "id" ] )." / ".$tabDepartments[ $row[ "dep_id" ] ][ "ind" ]." - ".$t1[ "exp_type" ]." от ".date( "d.m.Y" , time() )." г.
			"exp-number" => array( "value" => matincomingNumber( $t1[ "id" ] ) , "desc" => "порядковый номер экспертизы" , "mf" => false ),
			"exp-number-full" => array( "value" => matincomingNumberFull( $t1[ "id" ] , $row[ "dep_id" ] , $t1[ "exp_type" ] ) , "desc" => "номер экспертизы" , "mf" => false ),
			"exp-number-all" => array( "value" => implode( ", " , $nums ) , "desc" => "порядковый номер экспертизы (комплексной)" , "mf" => false ),
			"matincoming-date" => array( "value" => date( "d.m.Y" , strtotime( $t1[ "date" ] ) ) , "desc" => "дата поступления материалов дела" , "mf" => false ),
			"matincoming-id" => array( "value" => $t1[ "id" ] , "desc" => "ID материалов дела" , "mf" => false ),
			"spec-group" => array( "value" => $tabSpecGroups[ $row[ "spec_id" ] ] , "desc" => "вид экспертизы" , "mf" => true ),
			"case-category" => array( "value" => $tabCaseCategory[ $t1[ "exp_type" ] ] , "desc" => "категория дела" , "mf" => true ),
			"case-category-index" => array( "value" => $tabCaseCategoryIndexes[ $t1[ "exp_type" ] ] , "desc" => "категория дела (индекс)" , "mf" => false ),
			"case-num" => array( "value" => $caseData[ "case-num" ] , "desc" => "номер дела" , "mf" => false ),
			"case-size" => array( "value" => $caseData[ "case-size" ] , "desc" => "Количество томов/страниц" , "mf" => false ),
			"case-pers" => array( "value" => $caseData[ "case-pers" ] , "desc" => "иск кого к кому" , "mf" => false ),
			"doc-type" => array( "value" => strtolower( $caseData[ "doc-type" ] ) , "desc" => "тип документа" , "mf" => false ),
			"doc-date" => array( "value" => $caseData[ "doc-date" ] , "desc" => "дата документа" , "mf" => false ),
			"agency" => array( "value" => $t1[ "agency" ] , "desc" => "организация-заказчик" , "mf" => true ),
			"agency-type" => array( "value" => $tabTypeOfAgency[ $t1[ "agency_type" ] ][ "name" ] , "desc" => "тип организации-заказчика" , "mf" => true ),
			"agency-address" => array( "value" => $t1[ "agency_address" ] , "desc" => "адрес организации-заказчика" , "mf" => false ),
			"agent" => array( "value" => $t1[ "agent" ] , "desc" => "представитель организации-заказчика" , "mf" => true ),
			"agent-name" => array( "value" => $agentName , "desc" => "представитель организации-заказчика > Имя" , "mf" => true ),
			"agent-post" => array( "value" => $agentPost , "desc" => "представитель организации-заказчика > Должность" , "mf" => true ),
			"page-code" => array( "value" => mkCharID( $bcs ) , "desc" => "код документа" , "mf" => false ),
			"exp-price" => array( "value" => money_format( "%!i" , $row[ "price" ] ) , "desc" => "Стоимость" , "mf" => false ),
			"exp-price-all" => array( "value" => money_format( "%!i" , $totalPrice ) , "desc" => "Стоимость (комплексной)" , "mf" => false ),
			"exp-price-text" => array( "value" => preg_replace( '/\s+руб\w+\s+\d{2}\s+коп\w+/i' , '' , price2word( $row[ "price" ] ) ) , "desc" => "Стоимость" , "mf" => false ),
			"exp-price-all-text" => array( "value" => preg_replace( '/\s+руб\w+\s+\d{2}\s+коп\w+/i' , '' , price2word( $totalPrice ) ) , "desc" => "Стоимость (комплексной)" , "mf" => false ),
			"exp-fin-date" => array( "value" => date( "d.m.Y" , strtotime( $row[ "fin_date" ] ) ) , "desc" => "Дата завершения экспертизы" , "mf" => false ),
		);

		return $docVar ;
	}





	function loadVariables( $tmplData , $expertize_id ) {
		global $portalDB , $dbConfig , $dbConfigFull , $tabDepartments , $tabWorkers , $tabPosts , $tabSpecGroups ;
		global $TAB_CASECATEGORY , $tabTypeOfAgency ;
		global $UserOrgIndex , $UserName ;

		$tabDepartments = $portalDB->table( "departments" , "id" );
		$tabWorkers = $portalDB->query( "select `t1`.* , `t2`.`phone` from `workers` as `t1` left join `cabinet` as `t2` on `t1`.`cab` = `t2`.`id`" , "id" );
		$tabPosts = $portalDB->table( "posts" , "id" );
		$tabSpecGroups = $portalDB->query( "select `t2`.`id` , `t1`.`name` from `specialities-groups` as `t1` , `specialities` as `t2` where `t2`.`group` = `t1`.`id`" , "id" );
		foreach ( $tabSpecGroups as &$sp ) {
			$sp = $sp[ "name" ];
		} unset( $sp );

		$tabCaseCategory = array_column( $TAB_CASECATEGORY , 'name' , 'id' );
		$tabCaseCategoryIndexes = array_column( $TAB_CASECATEGORY , 'index' , 'id' );
		$tabTypeOfAgency = $portalDB->table( "type-of-agency" , "id" );

		$t1 = $portalDB->row( "select `t1`.* , ifnull( `t1`.`group_id` , 0 ) as `group_id_norm` , `t4`.`ext_id` as `agency_type` , `t4`.`name` as `agency` , `t4`.`destination` as `agency_address` , `t5`.`name` as `agent` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` ,  `agency` as `t4` , `agent` as `t5` where ( `t3`.`id` = ? ) and ( `t4`.`id` = `t1`.`from_agency` ) and ( `t5`.`id` = `t1`.`from_agent` ) and ( `t2`.`mat_id` = `t1`.`id` ) and ( `t3`.`ext_id` = `t2`.`id` )" , "i" , $expertize_id );
		if ( $t1 === false ) {
			return false ;
		}

		if ( $t1[ "group_id_norm" ] != 0 ) {
			$t1All = $portalDB->query( "select `t1`.* , `t2`.`dep_id` , `t3`.`price` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `group_id` = ? )" , false , "i" , $t1[ "group_id_norm" ] );
		} else {
			$t1All = $portalDB->query( "select `t1`.* , `t2`.`dep_id` , `t3`.`price` from `matincoming` as `t1` , `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t1`.`id` = `t2`.`mat_id` ) and ( `t2`.`id` = `t3`.`ext_id` ) and ( `t1`.`id` = ? )" , false , "s" , $t1[ "id" ] );
		}
		$nums = array();
		$totalPrice = 0 ;
		foreach( $t1All as $ct1 ) {
			$nums[]= matincomingNumberFull( $ct1[ "id" ] , $ct1[ "dep_id" ] , $ct1[ "exp_type" ] );
			$totalPrice+= $ct1[ "price" ];
		}
		$nums = array_unique( $nums );

		$t1[ "agency" ] = normalizeAgency( $t1[ "agency" ] , $t1 );
		list( $t1[ "agent" ] , $agentPost , $agentName ) = normalizeAgent( $t1[ "agent" ] , $t1 );

		$row = $portalDB->row( "select `t2`.`mat_id` , `t2`.`dep_id` , `t3`.* from `matincominglvl2` as `t2` , `expertize` as `t3` where ( `t3`.`id` = ? ) and ( `t3`.`ext_id` = `t2`.`id` )" , "i" , $expertize_id );
		$worker = $tabWorkers[ $row[ "exp_id" ] ];
		$name = NAMES_parse( $worker[ "name" ] );

		$caseData = extractCaseData( $t1 );

		$tmplExtVar = json_decode( iconv( "cp1251" , "utf8" , $tmplData[ "ext-var" ] ) , true );

		$docVar = array();

		foreach ( $tmplExtVar as $ev ) {
			$docVar[ "ext:".$ev[ "name" ] ] = array(
				"value" => "" ,
				"desc" => iconv( "utf8" , "cp1251" , $ev[ "desc" ] ),
				"mf" => false ,
				"editable" => $ev
			);
		}

		$bcs = getCharIDStructure( $row[ "mat_id" ] );
		$bcs[ "t" ] = $tmplData[ "code" ];

		$docVar = array_merge( $docVar , array(
			"expert-name" => array( "value" => NAMES_Format( $name , "%F0 %I0 %O0" ) , "desc" => "Эксперт > Фамилия Имя Отчество" , "mf" => true ),
			"expert-name1" => array( "value" => NAMES_Format( $name , "%F0 %i.%o." ) , "desc" => "Эксперт > Фамилия И.О." , "mf" => true ),
			"expert-name2" => array( "value" => NAMES_Format( $name , "%i.%o. %F0" ) , "desc" => "Эксперт > И.О. Фамилия" , "mf" => true ),
			"expert-post" => array( "value" => $tabPosts[ $worker[ "post_1_id" ] ][ "name" ] , "desc" => "Эксперт > Должность > Название" , "mf" => false ),
			"expert-post-simple" => array( "value" => $tabPosts[ $worker[ "post_1_id" ] ][ "simple_name" ] , "desc" => "Эксперт > Должность > Название (упрощенное)" , "mf" => false ),
			"expert-department" => array( "value" => $tabDepartments[ $worker[ "dep" ] ][ "name" ] , "desc" => "Эксперт > Отдел > Название" , "mf" => false ),
			"expert-department-short" => array( "value" => $tabDepartments[ $worker[ "dep" ] ][ "short_name" ] , "desc" => "Эксперт > Отдел > Название (Краткое)" , "mf" => false ),
			"expert-phone" => array( "value" => $worker[ "phone" ] , "desc" => "Эксперт > Отдел > Номер телефона" , "mf" => false ),
		//// № ".matincomingNumber( $t1[ "id" ] )." / ".$tabDepartments[ $row[ "dep_id" ] ][ "ind" ]." - ".$t1[ "exp_type" ]." от ".date( "d.m.Y" , time() )." г.
			"exp-number" => array( "value" => matincomingNumber( $t1[ "id" ] ) , "desc" => "порядковый номер экспертизы" , "mf" => false ),
			"exp-number-full" => array( "value" => matincomingNumberFull( $t1[ "id" ] , $row[ "dep_id" ] , $t1[ "exp_type" ] ) , "desc" => "номер экспертизы" , "mf" => false ),
			"exp-number-all" => array( "value" => implode( ", " , $nums ) , "desc" => "порядковый номер экспертизы (комплексной)" , "mf" => false ),
			"matincoming-date" => array( "value" => date( "d.m.Y" , strtotime( $t1[ "date" ] ) ) , "desc" => "дата поступления материалов дела" , "mf" => false ),
			"matincoming-id" => array( "value" => $t1[ "id" ] , "desc" => "ID материалов дела" , "mf" => false ),
			"spec-group" => array( "value" => $tabSpecGroups[ $row[ "spec_id" ] ] , "desc" => "вид экспертизы" , "mf" => true ),
			"case-category" => array( "value" => $tabCaseCategory[ $t1[ "exp_type" ] ] , "desc" => "категория дела" , "mf" => true ),
			"case-num" => array( "value" => $caseData[ "case-num" ] , "desc" => "номер дела" , "mf" => false ),
			"case-size" => array( "value" => $caseData[ "case-size" ] , "desc" => "Количество томов/страниц" , "mf" => false ),
			"case-pers" => array( "value" => $caseData[ "case-pers" ] , "desc" => "иск кого к кому" , "mf" => false ),
			"doc-type" => array( "value" => strtolower( $caseData[ "doc-type" ] ) , "desc" => "тип документа" , "mf" => false ),
			"doc-date" => array( "value" => $caseData[ "doc-date" ] , "desc" => "дата документа" , "mf" => false ),
			"agency" => array( "value" => $t1[ "agency" ] , "desc" => "организация-заказчик" , "mf" => true ),
			"agency-type" => array( "value" => $tabTypeOfAgency[ $t1[ "agency_type" ] ][ "name" ] , "desc" => "тип организации-заказчика" , "mf" => true ),
			"agency-address" => array( "value" => $t1[ "agency_address" ] , "desc" => "адрес организации-заказчика" , "mf" => false ),
			"agent" => array( "value" => $t1[ "agent" ] , "desc" => "представитель организации-заказчика" , "mf" => true ),
			"agent-name" => array( "value" => $agentName , "desc" => "представитель организации-заказчика > Имя" , "mf" => true ),
			"agent-post" => array( "value" => $agentPost , "desc" => "представитель организации-заказчика > Должность" , "mf" => true ),
			"page-code" => array( "value" => mkCharID( $bcs ) , "desc" => "код документа" , "mf" => false ),
			"exp-price" => array( "value" => money_format( "%!i" , $row[ "price" ] ) , "desc" => "Стоимость" , "mf" => false ),
			"exp-price-all" => array( "value" => money_format( "%!i" , $totalPrice ) , "desc" => "Стоимость (комплексной)" , "mf" => false ),
			"exp-price-text" => array( "value" => preg_replace( '/\s+руб\w+\s+\d{2}\s+коп\w+/i' , '' , price2word( $row[ "price" ] ) ) , "desc" => "Стоимость" , "mf" => false ),
			"exp-price-all-text" => array( "value" => preg_replace( '/\s+руб\w+\s+\d{2}\s+коп\w+/i' , '' , price2word( $totalPrice ) ) , "desc" => "Стоимость (комплексной)" , "mf" => false ),
			"exp-fin-date" => array( "value" => date( "d.m.Y" , strtotime( $row[ "fin_date" ] ) ) , "desc" => "Дата завершения экспертизы" , "mf" => false ),
			"user-name" => array( "value" => NAMES_Format( NAMES_parse( $UserName ) , "%F0 %I0 %O0" ) , "desc" => "Пользователь > Фамилия Имя Отчество" , "mf" => true ),
			"user-name1" => array( "value" => NAMES_Format( NAMES_parse( $UserName ) , "%F0 %i.%o." ) , "desc" => "Пользователь > Фамилия И.О." , "mf" => true ),
			"user-name2" => array( "value" => NAMES_Format( NAMES_parse( $UserName ) , "%i.%o. %F0" ) , "desc" => "Пользователь > И.О. Фамилия" , "mf" => true ),
		) );

		foreach( $dbConfigFull as $c ) {

			if ( !is_null( $c[ "e-data" ] ) ) {
				$ced = json_decode( $c[ "e-data" ] , true );
			} else {
				$ced = array();
			}

			if ( isset( $ced[ "d-tmpl" ] ) && $ced[ "d-tmpl" ] == 0 ) {
				continue ;
			}

			$ccmf = isset( $ced[ "mf" ] ) && $ced[ "mf" ] == 1 ;

			$docVar[ "cfg:".$c[ "name" ] ] = array(
				"value" => $c[ "value" ] ,
				"desc" => $c[ "description" ] ,
				"mf" => $ccmf
			);
		}

		$docVar = array_merge( $docVar , array(
			"tmpl:name"       => array( "value" => $tmplData[ "name" ]       , "desc" => "шаблон : Название документа полное" , "mf" => false ) ,
			"tmpl:name-short" => array( "value" => $tmplData[ "short_name" ] , "desc" => "шаблон : Название документа краткое" , "mf" => false )
		) );



		return $docVar ;
	}

	function varCategory( $name ) {
		$n = preg_match( '/^([^:]+)\:/' , $name , $m );
		if ( $n == 1 ) {
			return $m[ 1 ];
		} else {
			return '' ;
		}
	}

	class TDocTemplate extends baseExt {
		public $version = false ;
		public $pageFormat = PAPER_SIZE_A4_PORTRAIT ;
		public $header = false ;
		public $prependText = false ;
		public $title = false ;
		public $mainText = false ;

		public $origDOM = false ;

		function __construct( $txt ) {
			$domDoc = new DOMDocument();
			$txt = preg_replace( '/\s+/' , ' ' , $txt );
			do {
				$ltl = strlen( $txt );
				//$txt = preg_replace( '=</([^>]+)>\s+</([^>]+)>=' , '</\1></\2>' , $txt );
				$txt = preg_replace( '=\s+<\/([^>]+)>=' , '</\1>' , $txt );
				$txt = preg_replace( '=<([^>/]+)>\s+=' , '<\1>' , $txt );
			} while ( $ltl > strlen( $txt ) );
			$txt = str_replace( '</p> <p' , '</p><p' , $txt );
			$txt = str_replace( '</block> <block' , '</block><block' , $txt );
			$txt = str_replace( '</td> <td' , '</td><td' , $txt );
			$txt = str_replace( '</tr> <tr' , '</tr><tr' , $txt );
			if ( isset( $_REQUEST[ "dbg" ] ) && $_REQUEST[ "dbg" ] == 1 ) {
				echo "<div>".$txt."</div>" ;
			}
			$domDoc->loadXML( iconv( "cp1251" , "utf8" , $txt ) );
			$this->origDOM = $data = $domDoc->documentElement ;
			$this->version = $data->getAttribute( "version" );
			switch ( $this->version ) {
				case "100" :
					break ;
				default :
					exit();
					break ;
			}

			$this->pageFormat = $data->getAttribute( "format" );

			$tnm = array(
				"header" => "header" ,
				"prepend-text" => "prependText" ,
				"title" => "title" ,
				"main-text" => "mainText"
			);

			$topNodes = array();
			foreach( $data->childNodes as $ttn ) {
				if ( isset( $tnm[ $ttn->nodeName ] ) ) {
					$this->$tnm[ $ttn->nodeName ] = $ttn ;
				}
			}
		}

		public function extractVars( $data = null ) {
			if ( $data == null ) {
				$data = $this->origDOM ;
			}
			$varArr = array();
			if ( $data->nodeType == 1 ) {
				foreach( $data->childNodes as $ttn ) {
					if ( $ttn->nodeName == 'var' ) {
						$cvn = $ttn->getAttribute( 'name' );
						$varArr[ $cvn ] = $cvn ;
					} else {
						$cv = $this->extractVars( $ttn );
						$varArr = array_merge( $varArr , $cv );
					}
				}
			}
			return $varArr ;
		}
	}

	function getFilteredList( $type ) {

	}

