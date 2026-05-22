<?php
	/**
	 * @var $DaysOfWeek
	 * @var $MonthNames
	 * @var $DaysOfWeekShort
	 * @var $TAB_CASECATEGORY
	 */

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



	function normalizeTemplate( $xmlTmpl ) {
		$xmlTmplXPATH = new DOMXPath( $xmlTmpl );
		$docPackNodePresent = $xmlTmplXPATH->query( '/doc-pack' )->length > 0 ;
		if ( !$docPackNodePresent ) {
			$templateNode = $xmlTmplXPATH->query( '/template' )->item( 0 );
			$docPackNode = $xmlTmpl->createElement( 'doc-pack' );
			$xmlTmpl->appendChild( $docPackNode );
			$docPackNode->appendChild( $templateNode );
		}
	}

	function extractCaseData( $t1 ) {
		global $extractCaseData__map , $extractCaseData__pa , $TAB_CASECATEGORY ;
		$map = $extractCaseData__map ;

		$res2 = array();

		$ccGroup = getCCGroup( $t1[ 'exp_type' ] );
		$cp = $extractCaseData__pa[ $ccGroup ];
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
				//array( 'm' => '/(?:ко|ич)$/' , 'r' => null  ) ,

				array( 'm' => '/(?:ова|ева|ёва|ина)$/' , 'r' => '/а$/' , 'e' => array(  "а" , "ой" , "ой" , "у" , "ой" , "ой"  )  ) ,
				array( 'm' => '/(?:вская)$/' , 'r' => '/ая$/' , 'e' => array(  "ая" , "ой" , "ой" , "ую" , "ой" , "ой"  )  ) ,

				//array( 'm' => '/(?:сь|ик|их|им)$/' , 'r' => null  ) ,
			) ,

			'i' => array(
				array( 'm' => '/(?:нна|ина|дра|ста|ора|ата|ида|сса|ена|ада|аза|жда)$/i'  , 'r' => '/а$/i' , 'e' => array(  "а" , "ы" , "е" , "у" , "ой" , "е"  )  ) ,
				array( 'm' => '/(?:га)$/i'  , 'r' => '/а$/i' , 'e' => array(  "а" , "и" , "е" , "у" , "ой" , "е"  )  ) ,
				array( 'm' => '/(?:ия|ья|ая|йя)$/i' , 'r' => '/я$/i' , 'e' => array(  "я" , "и" , "и" , "ю" , "ей" , "и"  )  ) ,
				//array( 'm' => '/(?:су|ль)$/i' , 'r' => null  ) ,

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
				$isChanged = false ;
				for( $i = 0 ; $i < count( $nm ) ; ++$i ) {
					$m = array();
					$mc = preg_match( $nm[ $i ][ 'm' ] , $fen , $m );
					//$m =  fen.match( nm[ i ].m );
					if ( $mc == 1 ) {
						if ( $nm[ $i ][ 'r' ] != null ) {
							for( $j = 1 ; $j < 6 ; ++$j ) {
								$elt[ $j ] = preg_replace( $nm[ $i ][ 'r' ] , $nm[ $i ][ 'e' ][ $j ] , $fen );
								$isChanged = true ;
							}
						}
						break ;
					}
				}

				if ( !$isChanged ) {
					return $fen.' '.$matches[ 2 ];
					//$elt = array_fill( 1 , 6 , $fen );
				} else {
					return packFormsES( $elt ).' '.$matches[ 2 ];
				}
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
	
	class TDGVariable {
		const LAYOUT_LIST         = 'list'         ; // Просто набор div'ов по одному для каждого элемента
		const LAYOUT_ALIGNED_LIST = 'aligned-list' ; // Таблица 2 столбца, слева названия, справа поле ввода, по 1 строке на элемент
		const LAYOUT_COLUMNS      = 'columns'      ; // Таблица 1 строка, по 1 столбцу отдельно на каждое название и поле ввода на каждый элемент
		const LAYOUT_LINE         = 'line'         ; // 1 строка для всех элементов
		const LAYOUT_ONE_LINE     = 'line'         ; // 1 строка для заголовка и всех элементов

		private $_uid ;
		private $_parent ;
		private $_exData ;
		private $_name ;
		private $_type ;
		private $_description ;
		private $_definition ;
		private $_value ;
		private $_path ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			$this->_uid = 'var--'.generateGUID();
			$this->_parent = $parent ;
			$this->_exData = $exData ;
			$this->_name = $name ;
			$this->_type = $type ;
			$this->_description = $description ;
			$this->_definition = $definition ;
			$this->_value = null ;
			$this->_path = '['.$name.']' ;
			if ( !is_null( $parent ) ) {
				$this->_path = $parent->path.( isset( $exData[ 'path-prefix' ] ) ? $exData[ 'path-prefix' ] : '' ).$this->_path ;
			}
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'uid'         : return $this->_uid ;
				case 'parent'      : return $this->_parent ;
				case 'exData'      : return $this->_exData ;
				case 'name'        : return $this->_name ;
				case 'type'        : return $this->_type ;
				case 'description' : return $this->_description ;
				case 'definition'  : return $this->_definition ;
				case 'value'       : return $this->_value ;
				case 'path'        : return $this->_path ;
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					break ;
			}
		}

		public function read( $src ) {
		}

		public function write() {
			return null ;
		}
		
		public function export( DOMElement $xml ) {
			$xml->setAttribute( 'type' , $this->_type );
			$xml->setAttribute( 'path' , $this->_path );
		}
		
		public static function fromDef( $parent , $def , $exData ) {
			$MAP = array(
				'string'    => 'TDGVariableString' ,
				'number'    => 'TDGVariableNumber' ,
				'price'     => 'TDGVariablePrice' ,
				'date-time' => 'TDGVariableDateTime' ,
				'options'   => 'TDGVariableOptions' ,
				'variant'   => 'TDGVariableVariant' ,
				'array'     => 'TDGVariableArray' ,
				'structure' => 'TDGVariableStructure' ,
				'class'     => 'TDGVariableClass' ,
				'image'     => 'TDGVariableImage' ,
				'address'   => 'TDGVariableAddress'
			);

			if ( isset( $def[ 'type' ] ) && isset( $MAP[ $def[ 'type' ] ] ) ) {
				$td = $MAP[ $def[ 'type' ] ];
				return new $td( $parent , $def[ 'name' ] , $def[ 'type' ] , $def[ 'descr' ] , $def , $exData );
			} else {
				error_log_ml( print_r( $def , 1 ) );
				return new TDGVariable( $parent , $def[ 'name' ] , $def[ 'type' ] , $def[ 'descr' ] , $def , $exData );
			}
		}
	}

	class TDGVariableString extends TDGVariable {
		private $_value ;
		private $_lines ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			$this->_value = '' ;
			$this->_lines = isset( $definition[ 'lines' ] ) ? $definition[ 'lines' ] : 1 ;
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->_value ;
				case 'lines' : return $this->_lines ;
				default :
					return parent::__get( $name );
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}

		public function read( $src ) {
			$this->value = ''.$src ;
		}

		public function write() {
			return $this->_value ;
		}
		
		public function export( DOMElement $xml ) {
			parent::export( $xml );
			$doc = $xml->ownerDocument ;
			$xml->appendChild( $doc->createTextNode( $this->_value ) );
		}
	}

	class TDGVariableAddress extends TDGVariableString {
	}

	class TDGVariableNumber extends TDGVariable {
		private $_value ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			$this->_value = 0 ;
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->_value ;
				default :
					return parent::__get( $name );
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}

		public function read( $src ) {
			$this->value = 0 + $src ;
		}

		public function write() {
			return $this->_value ;
		}
		
		public function export( DOMElement $xml ) {
			parent::export( $xml );
			$doc = $xml->ownerDocument ;
			$xml->appendChild( $doc->createTextNode( ''.$this->_value ) );
		}
	}

	class TDGVariablePrice extends TDGVariableNumber {
	}



	class TDGVariableDateTime extends TDGVariable {
		private $_value ;
		private $_format ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			$this->_value = null ;
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->_value ;
				default :
					return parent::__get( $name );
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}

		public function read( $src ) {
			$this->value = $src + 0 ;
		}

		public function write() {
			return $this->_value ;
		}
		
		public function export( DOMElement $xml ) {
			parent::export( $xml );
			/*$doc = $xml->ownerDocument ;
			$v = $this->_value - $this->_value % 1000 ;
			$t = date( 'd-m-Y' , $v / 1000 );
			$xml->appendChild( $doc->createTextNode( $t ) );*/

			$doc = $xml->ownerDocument ;
			$xml->appendChild( $doc->createTextNode( $this->_value ) );
		}
	}

	class TDGVariableOptions extends TDGVariable {
		private $_options ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			$this->_options = array();
			$od = $definition[ 'options' ];
			foreach( $od as $id => $odd ) {
				$option = array(
					'id' => $id ,
					'descr' => $odd ,
					'selected' => false
				);
				$this->_options[ $id ] = $option ;
			}
		}

		private function getValue() {
			$selected = array();
			foreach( $this->_options as $id => $optV ) {
				if ( $optV[ 'selected' ] ) {
					$selected[]= $id ;
				}
			}
			return $selected ;
		}

		private function setValue( $v ) {
			foreach( $this->_options as &$optV ) {
				$optV[ 'selected' ] = false ;
			} unset( $optV );

			foreach( $v as $id ) {
				$this->_options[ $id ][ 'selected' ] = true ;
			}
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->getValue();
				default :
					return parent::__get( $name );
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->setValue( $newValue ) ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}

		public function read( $src ) {
			$this->value = $src ;
		}

		public function write() {
			return $this->_value ;
		}

		public function export( DOMElement $xml ) {
			parent::export( $xml );
			$doc = $xml->ownerDocument ;
			foreach( $this->_options as $id => $optV ) {
				if ( $optV[ 'selected' ] ) {
					$optNode = $doc->createElement( 'option' );
					$optNode->setAttribute( 'id' , $id );
					$xml->appendChild( $optNode );
				}
			}
		}
	}

	class TDGVariableVariant extends TDGVariable {
		private $_items ;
		private $_value ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			$this->_items = array();
			$items = $definition[ 'items' ];
			foreach( $items as $cItem ) {
				$this->_items[ $cItem[ 'id' ] ]= $cItem[ 'descr' ];
			}
			$this->_value = null ;
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->_value ;
				default :
					return parent::__get( $name );
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}

		public function read( $src ) {
			$this->value = ''.$src ;
		}

		public function write() {
			return $this->_value ;
		}

		public function export( DOMElement $xml ) {
			parent::export( $xml );
			$doc = $xml->ownerDocument ;
			$optNode = $doc->createElement( 'option' );
			$optNode->setAttribute( 'id' , $this->_value );
			$optNode->appendChild( $doc->createTextNode( isset( $this->_items[ $this->_value ] ) ? $this->_items[ $this->_value ] : '' ) );
			$xml->appendChild( $optNode );
		}
	}

	class TDGVariableArray extends TDGVariable {
		private $_value ;
		private $_elementDefinition ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			$this->_value = array();
			$this->_elementDefinition = array();
			foreach( $definition[ 'array-element-definition' ] as $ed ) {
				$name = $ed[ 'name' ];
				$this->_elementDefinition[ $name ] = $ed ;
			}
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->_value ;
				default :
					return parent::__get( $name );
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}

		public function read( $src ) {
			$res = array();
			$elDef = &$this->_elementDefinition ;
			for( $i = 0 ; $i < count( $src ) ; $i++ ) {
				$v = $src[ $i ];
				reset( $v );
				$k = key( $v );
				$cDef = $elDef[ $k ];
				$cRes = TDGVariable::fromDef( $this , $cDef , array_merge( $this->exData , array( 'path-prefix' => '['.$i.']' ) ) );
				//print_r_html( $v[ $k ] , 1 );
				$cRes->read( $v[ $k ] );
				$res[]= $cRes ;
			}
			$this->_value = $res ;
		}

		public function write() {
			$res = array();
			$v = $this->_value ;
			for( $i = 0 ; $i < count( $v ) ; $i++ ) {
				$e = $v[ $i ];
				$ri = array();
				$ri[ $e->name ] = $e->write();
				$res[ $i ] = $ri ;
			}
			return $res ;
		}
		
		public function export( DOMElement $xml ) {
			parent::export( $xml );
			$doc = $xml->ownerDocument ;
			$v = $this->_value ;
			for( $i = 0 ; $i < count( $v ) ; $i++ ) {
				$e = $v[ $i ];
				$ri = $doc->createElement( $e->name );
				$xml->appendChild( $ri );
				$e->export( $ri );
			}
		}
	}

	class TDGVariableStructure extends TDGVariable {
		private $_value ;
		private $_elementDefinition ;
		private $_layout ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			$this->_value = array();
			$value = &$this->_value ;

			$this->_elementDefinition = array();
			$elDef = &$this->_elementDefinition ;

			$this->_layout = isset( $definition[ 'layout' ] ) ? $definition[ 'layout' ] : TDGVariable::LAYOUT_LIST ;

			$childExData = array_merge( array() , $exData );
			if ( isset( $childExData[ 'path-prefix' ] ) ) {
				unset( $childExData[ 'path-prefix' ] );
			}
			foreach( $definition[ 'structure-definition' ] as $ed ) {
				$name = $ed[ 'name' ];
				$value[ $name ] = TDGVariable::fromDef( $this , $ed , $childExData );
				$elDef[ $name ]= $ed ;
			}
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->_value ;
				default :
					return parent::__get( $name );
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}

		public function read( $src ) {
			$value = &$this->_value ;
			foreach( $src as $k => $v ) {
				$value[ $k ]->read( $v );
			}
		}

		public function write() {
			$res = array();
			$value = &$this->_value ;
			$elDef = &$this->_elementDefinition ;
			foreach( $elDef as $ed ) {
				$name = $ed[ 'name' ];
				$res[ $name ] = $value[ $name ]->write();
			}
			return $res ;
		}
		
		public function export( DOMElement $xml ) {
			parent::export( $xml );
			$doc = $xml->ownerDocument ;
			$value = &$this->_value ;
			$elDef = &$this->_elementDefinition ;
			foreach( $elDef as $ed ) {
				$name = $ed[ 'name' ];
				$ri = $doc->createElement( $name );
				$xml->appendChild( $ri );
				$value[ $name ]->export( $ri );
			}
		}
		
	}

	class TDGVariableClass extends TDGVariable {
		private $_value ;
		private $_classID ;
		private $_classDefinition ;

		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			$this->_value = array();
			$value = &$this->_value ;

			if ( isset( $definition[ 'class-id' ] ) ) {
				$this->_classID = $definition[ 'class-id' ];
			} else {
				return ;
			}

			$classID = $this->_classID ;
			if ( !isset( $exData[ 'classes' ] ) || !isset( $exData[ 'classes' ][ $classID ] ) ) {
				return ;
			}

			//console.log( classID );
			//console.log( exData.classes[ classID ] );

			$childExData = array_merge( array() , $exData );
			if ( isset( $childExData[ 'path-prefix' ] ) ) {
				unset( $childExData[ 'path-prefix' ] );
			}
			$this->_classDefinition = $exData[ 'classes' ][ $classID ];
			$currentClass = &$this->_classDefinition ;
			foreach( $currentClass->fields as $fID => $f ) {
				$vd = $f->getVariableDeinition();
				$value[ $f->id ] = TDGVariable::fromDef( $this , $vd , $childExData );
			}
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->_value ;
				default :
					return parent::__get( $name );
			}
		}

		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}

		public function read( $src ) {
			/*echo '------------------------------------' ;
			print_r_html( $src , 1 );
			echo '------------------------------------' ;*/
			$value = &$this->_value ;
			foreach( $src as $k => $v ) {
				$value[ $k ]->read( $v );
			}
		}

		public function write() {
			$res = array();
			$value = &$this->_value ;
			$fDef = &$this->_classDefinition->fields ;
			foreach( $fDef as $cfDef ) {
				$fID = $cfDef->id ;
				$res[ $fID ] = $value[ $fID ]->write();
			}
			return $res ;
		}

		public function export( DOMElement $xml ) {
			parent::export( $xml );
			$doc = $xml->ownerDocument ;
			$value = &$this->_value ;
			$fDef = &$this->_classDefinition->fields ;
			foreach( $fDef as $cfDef ) {
				$fID = $cfDef->id ;
				$e = $value[ $fID ];
				$ri = $doc->createElement( 'field' );
				$ri->setAttribute( 'id' , $fID );
				$xml->appendChild( $ri );
				$e->export( $ri );
			}
		}
	}
	
	class TDGVariableImage extends TDGVariable {
		private $_imageData ;
		private $_type ;
		private $_transforms ;
		private $_cache ;
		private $_originalSize ;
		private $_DOC_IMAGE_ID ;

		public static $ImagesIDMap ;
		
		public function __construct( $parent , $name , $type , $description , $definition , $exData ) {
			parent::__construct( $parent , $name , $type , $description , $definition , $exData );
			
			$this->_transforms = array();
			$this->_originalSize = null ;
			
		}
		
		public function __get( $name ) {
			switch ( $name ) {
				case 'value' : return $this->_imageData ;
				default :
					return parent::__get( $name );
			}
		}
		
		public function __set( $name , $newValue ) {
			switch ( $name ) {
				case 'value' : $this->_value = $newValue ; break ;
				default :
					parent::__set( $name , $newValue );
					break ;
			}
		}
		
		public function read( $src ) {
			if ( !isset( $src ) || !isset( $src[ 'data' ] ) ) {
				return ;
			}
			
			$this->_transforms = array();
			if ( isset( $src[ 'transforms' ] ) ) {
				$st = $src [ 'transforms' ];
				for( $i = 0 ; $i < count( $st ) ; $i++ ) {
					$this->_transforms[ $i ] = $st[ $i ];
				}
			}

			$this->LoadImageFromURL( $src[ 'data' ] );
		}
		
		public function write() {
		
		}
		
		public function export( DOMElement $xml ) {
			parent::export( $xml );
			$this->_DOC_IMAGE_ID = generateGUID(); //.'.'.$this->_type ;
			$xml->setAttribute( 'id' , $this->_DOC_IMAGE_ID );
			error_log( 'DBG : DG images id : '.$this->_DOC_IMAGE_ID );
			TDGVariableImage::$ImagesIDMap[ $this->_DOC_IMAGE_ID ] = $this ;
		}
		
		public function LoadImageFromURL( $data ) {
			$ch = substr( $data , 0 , 50 );
			$m = array();
			$n = preg_match( '/^data\:image\/(gif|png|jpeg|bmp);base64,/' , $ch , $m );
			if ( $n == 1 ) {
				error_log( 'DBG : DG images / type : '.$m[ 1 ] );
				$this->_type = $m[ 1 ];
				$this->_imageData = base64_decode( substr( $data , strlen( $m[ 0 ] ) ) );
			}
		}
	}

	class TDGClassField {
		public $id ;
		public $name ;
		public $type ;
		public $description ;
		public $unit ;

		public function __construct( $def ) {
			if ( $def instanceof DOMElement ) {
				foreach( array( 'id' , 'type' ) as $p ) {
					if ( $def->hasAttribute( $p ) ) {
						$this->$p = $def->getAttribute( $p );
					} else {
						throw new Exception( 'field '.strtoupper( $p ).' not set' );
					}
				}

				$elements = array();
				foreach( $def->childNodes as $cn ) {
					if ( $cn->nodeType == XML_ELEMENT_NODE ) {
						$elements[ $cn->nodeName ] = $cn ;
					}
				}

				if ( isset( $elements[ 'name' ] ) ) {
					$this->name = $elements[ 'name' ]->nodeValue ;
				} else {
					throw new Exception( 'field NAME not set' );
				}
			} else {
				foreach( $this as $prop => $val  ) {
					$this->$prop = $def[ $prop ];
					//print_r_html( $prop );
				}
			}
		}

		public static function fromDef( $def ) {
			$MAP = array(
				'variant' => 'TDGClassFieldVariant' ,
				'array'   => 'TDGClassFieldArray'   ,
			);

			$type = null ;

			if ( $def instanceof DOMElement ) {
				if ( $def->hasAttribute( 'type' ) ) {
					$type = $def->getAttribute( 'type' );
				} else {
					throw new Exception( 'field TYPE not set' );
					return ;
				}
			} else {
				$type = $def->type ;
			}

			if ( isset( $MAP[ $type ] ) ) {
				$cd = $MAP[ $type ];
				return new $cd( $def );
			} else {
				return new TDGClassField( $def );
			}
		}

		public function getVariableDeinition() {
			return array(
				'name'  => $this->id ,
				'type'  => $this->type ,
				'descr' => $this->name
			);
		}
	}

	class TDGClassFieldVariant extends TDGClassField {
		public $items ;

		public function __construct( $def ) {
			parent::__construct( $def );
			$this->items = array();

			if ( $def instanceof DOMElement ) {
				$elements = array();
				foreach( $def->childNodes as $cn ) {
					if ( $cn->nodeType == XML_ELEMENT_NODE ) {
						$elements[ $cn->nodeName ] = $cn ;
					}
				}

				if ( isset( $elements[ 'items' ] ) ) {
					foreach( $elements[ 'items' ]->childNodes as $cn ) {
						if ( $cn->nodeType == XML_ELEMENT_NODE && $cn->nodeName == 'item' ) {
							$cItem = array(
								'id'    => $cn->hasAttribute( 'id' ) ? $cn->getAttribute( 'id' ) : null ,
								'descr' => $cn->nodeValue
							);
							$this->items[]= $cItem ;
						}
					}
				} else {
					throw new Exception( 'items not found' );
				}
			} else {
				$items = isset( $def[ 'items' ] ) && is_array( $def[ 'items' ] ) ? $def[ 'items' ] : array();
				foreach( $items as $cItem ) {
					$this->items[]= $cItem ;
				}
			}
		}

		public function getVariableDeinition() {
			$res = parent::getVariableDeinition();
			$res[ 'items' ] = $this->items ;
			return $res ;
		}
	}

	class TDGClassFieldArray extends TDGClassField {
		public $elementsDefinition ;

		public function __construct( $def ) {
			parent::__construct( $def );
			$this->elementsDefinition = array();

			if ( $def instanceof DOMElement ) {
				$elements = array();
				foreach( $def->childNodes as $cn ) {
					if ( $cn->nodeType == XML_ELEMENT_NODE ) {
						$elements[ $cn->nodeName ] = $cn ;
					}
				}

				if ( isset( $elements[ 'elements' ] ) ) {
					foreach( $elements[ 'elements' ]->childNodes as $cn ) {
						if ( $cn->nodeType == XML_ELEMENT_NODE && $cn->nodeName == 'element' ) {
							$cItem = TDGClassField::fromDef( $cn );
							$this->elementsDefinition[]= $cItem ;
						}
					}
				} else {
					throw new Exception( 'items not found' );
				}
			} else {
				error_log( 'DBG: DG CORE TDGClassFieldArray from json' );
				$elements = isset( $def[ 'elementsDefinition' ] ) && is_array( $def[ 'elementsDefinition' ] ) ? $def[ 'elementsDefinition' ] : array();
				foreach( $elements as $cDef ) {
					$cItem = TDGClassField::fromDef( $cDef );
					$this->elementsDefinition[]= $cItem ;
				}
			}
		}

		public function getVariableDeinition() {
			$res = parent::getVariableDeinition();
			$res[ 'array-element-definition' ] = array();
			foreach( $this->elementsDefinition as $cDef ) {
				error_log_ml( print_r( $cDef , 1 ) );
				$res[ 'array-element-definition' ][]= $cDef->getVariableDeinition();
			}
			error_log( 'DBG: DG CORE TDGClassFieldArray getVariableDeinition' );
			error_log_ml( print_r( $res , 1 ) );
			return $res ;
		}
	}

	class TDGClass {
		public $id ;
		public $parentID ;
		public $name ;
		public $fields ;
		public function __construct( $def ) {
			$this->fields = array();

			if ( $def instanceof DOMElement ) {
				if ( $def->hasAttribute( 'id' ) ) {
					$this->id = $def->getAttribute( 'id' );
				} else {
					throw new Exception( 'Class ID not set' );
				}

				$this->parentID = $def->hasAttribute( 'parent-class-id' ) ? $def->getAttribute( 'parent-class-id' ) : null ;

				$elements = array();
				foreach( $def->childNodes as $cn ) {
					if ( $cn->nodeType == XML_ELEMENT_NODE ) {
						$elements[ $cn->nodeName ] = $cn ;
					}
				}

				if ( isset( $elements[ 'name' ] ) ) {
					$this->name = $elements[ 'name' ]->nodeValue ;
				} else {
					throw new Exception( 'Class NAME not set' );
				}

				if ( !isset( $elements[ 'fields' ] ) ) {
					throw new Exception( 'Class NAME not set' );
				}

				foreach( $elements[ 'fields' ]->childNodes as $cn ) {
					if ( $cn->nodeType == XML_ELEMENT_NODE && $cn->nodeName == 'field' ) {
						$tmpField = TDGClassField::fromDef( $cn );
						$this->fields[ $tmpField->id ] = $tmpField ;
					}
				}
			} else {
				foreach( $this as $prop => $propV ) {
					if ( $prop == 'fields' ) {
						$fields = $def[ 'fields' ];
						foreach( $fields as $fid => $fV ) {
							$this->fields[ $fid ] = TDGClassField::fromDef( $fields[ $fid ] );
						}
					} else {
						$this->$prop = $def[ $prop ];
					}
				}
			}
		}
	}

	function readClasses( $xml ) {
		$classes = array();
		foreach( $xml->childNodes as $cn ) {
			if ( $cn->nodeType == XML_ELEMENT_NODE && $cn->nodeName == 'class' ) {
				$tmpClass = new TDGClass( $cn );
				$classes[ $tmpClass->id ] = $tmpClass ;
			}
		}
		return $classes ;
	}

	function tmpl2Doc_formatDate( $value , $format , $encoding = false ) {
		global $MonthNames , $DaysOfWeek , $DaysOfWeekShort ;
		if ( !isset( $format ) || $format == '' ) {
			$format = '{d}.{m}.{Y}' ;
		}
		$d = $value / 1000 ;
		$rep = array();
		foreach( str_split( 'jwnYGNztLgUisdmyHhv' ) as $v ) {
			$rep[]= str_replace( '$' , $v , '"\$":"$"' );
		}
		$json = '{'.date( implode( ',' , $rep ) , $d ).'}' ;
		$el = json_decode( $json , 1 );
		$el[ 'F' ]  = inForm( $MonthNames[ $el[ 'n' ] - 1 ] , 2 );
		$el[ 'F1' ] = inForm( $MonthNames[ $el[ 'n' ] - 1 ] , 1 );
		$el[ 'F2' ] = inForm( $MonthNames[ $el[ 'n' ] - 1 ] , 2 );
		$el[ 'l' ]  = inForm( $DaysOfWeek[ $el[ 'N' ] ] , 1 );
		$el[ 'D' ]  = $DaysOfWeekShort[ $el[ 'N' ] ];

		if ( $encoding !== false ) {
			$el[ 'F' ]  = iconv( DEF_CODEPAGE , $encoding , $el[ 'F' ] );
			$el[ 'F1' ] = iconv( DEF_CODEPAGE , $encoding , $el[ 'F1' ] );
			$el[ 'F2' ] = iconv( DEF_CODEPAGE , $encoding , $el[ 'F2' ] );
			$el[ 'l' ]  = iconv( DEF_CODEPAGE , $encoding , $el[ 'l' ] );
			$el[ 'D' ]  = iconv( DEF_CODEPAGE , $encoding , $el[ 'D' ] );
		}

		$result = preg_replace_callback( '/(?<!\\\\)\\{([a-z]\d?)\\}/i' , function( $m ) use ( $el ) {
			$m1 = $m[ 1 ];
			return isset( $el[ $m1 ] ) ? $el[ $m1 ] : $m[ 0 ];
		} , $format );
		$result = preg_replace( '/(?<!\\\\)\\\\{/i' , '{' , $result );
		return $result ;
	}

	function tmpl2Doc_formatPrice( $value , $format , $encoding = false ) {
		$res = $value ;
		switch ( $format ) {
			case 'price-w-text' :
				$res = number_format( $value , 0 , '.' , chr( 160 ) ).' '.preg_replace( '/^(.+)\s+(руб)\S+\s+(\d{2})\s+(коп)\S+$/' , '($1) $2. $3 $4.' , price2word( $value ) );
				break ;

			case 'price' :
			default :
				$res = number_format( $value , 2 , '.' , chr( 160 ) );
				break ;
		}
		if ( $encoding !== false ) {
			$res = iconv( DEF_CODEPAGE , $encoding , $res );
		}
		return $res ;
	}

	function tmpl2Doc_moreData( $nodes ) {
		ob_start();
		var_dump( $nodes );
		$Data = ob_get_contents();
		ob_end_clean();
		error_log_ml( 'DBG : tmpl2Doc_moreData : '.$Data );
		return 'ODIN ODIN' ;
	}

	$dgCalculationResults = array();

	function tmpl2Doc_calc( $stackNode ) {
		/*ob_start();
		var_dump( $stackNode );
		$Data = ob_get_contents();
		ob_end_clean();
		error_log_ml( 'DBG : tmpl2Doc_calc : '.$Data );*/
		$res = array();
		if ( is_array( $stackNode ) ) {
			$stackNode = $stackNode[ 0 ];
		}
		//error_log( 'DBG : tmpl2Doc_calc : '.( $stackNode instanceof DOMNode ? 'INSTANCE' : 'NOT INSTANCE!!!' ) );
		foreach( $stackNode->childNodes as $cn ) {
			if ( $cn->nodeType == XML_ELEMENT_NODE ) {
				switch( $cn->nodeName ) {
					case 'load' :
						if ( $cn->hasAttribute( 'type' ) ) {
							$loadType = $cn->getAttribute( 'type' );
						} else {
							$loadType = 'number' ;
						}
						switch( $loadType ) {
							case 'number' :
								$loadVal = trim( $cn->textContent );
								$tm = preg_match( '/^\d+$/' , $loadVal );
								if ( $tm == 1 ) {
									$loadVal = intval( $loadVal , 10 );
								} else {
									$tm = preg_match( '/^\d+[,.]\d+$/' , $loadVal );
									if ( $tm == 1 ) {
										$loadVal = floatval( str_replace( ',' , '.' ,$loadVal ) );
									}
								}
								break ;
							default :
								$loadVal = $cn->textContent ;
								break ;
						}

						$res[]= $loadVal ;
						error_log( 'DBG : tmpl2Doc_calc : push '.$loadVal );
						break ;

					case 'sum' :
						$op2 = array_pop( $res );
						$op1 = array_pop( $res );
						$res[]= $op1 + $op2 ;
						error_log( 'DBG : tmpl2Doc_calc : '.$op1.' + '.$op2.' = '.( $op1 + $op2 ) );
						break ;
					case 'mul' :
						$op2 = array_pop( $res );
						$op1 = array_pop( $res );
						$res[]= $op1 * $op2 ;
						error_log( 'DBG : tmpl2Doc_calc : '.$op1.' * '.$op2.' = '.( $op1 * $op2 ) );
						break ;
					case 'div' :
						$op2 = array_pop( $res );
						$op1 = array_pop( $res );
						$res[]= $op1 / $op2 ;
						error_log( 'DBG : tmpl2Doc_calc : '.$op1.' / '.$op2.' = '.( $op1 / $op2 ) );
						break ;
					case 'abs' :
						$op1 = array_pop( $res );
						$res[]= abs( $op1 );
						error_log( 'DBG : tmpl2Doc_calc : abs( '.$op1.' ) = '.abs( $op1 ) );
						break ;
					case 'round' :
						$op1 = array_pop( $res );
						$rd = $cn->hasAttribute( 'digits' ) ? intval( $cn->getAttribute( 'digits' ) , 10 ) : 0 ;
						$res[]= round( $op1 , $rd );
						error_log( 'DBG : tmpl2Doc_calc : round ('.$op1.' , '.$rd.') = '.( round( $op1 , $rd ) ) );
						break ;
				}
			}

		}
		return array_pop( $res );
	}

	function tmpl2Doc_storeCalcResultWOID( $name , $value ) {
		global $dgCalculationResults ;
		$dgCalculationResults[ $name ] = $value ;
		return '' ;
	}

	function tmpl2Doc_storeCalcResultWID( $name , $id , $value ) {
		global $dgCalculationResults ;
		if ( !isset( $dgCalculationResults[ $name ] ) ) {
			$dgCalculationResults[ $name ] = array();
		}
		$dgCalculationResults[ $name ][ $id ] = $value ;
		return '' ;
	}

	function tmpl2Doc_restoreCalcResultWOID( $name ) {
		global $dgCalculationResults ;
		return $dgCalculationResults[ $name ];
	}

	function tmpl2Doc_restoreCalcResultWID( $name , $id ) {
		global $dgCalculationResults ;

		ob_start();
		var_dump( $name );
		var_dump( $id );
		var_dump( $dgCalculationResults );
		$Data = ob_get_contents();
		ob_end_clean();
		error_log_ml( 'DBG : tmpl2Doc_calc : '.$Data );

		return $dgCalculationResults[ $name ][ $id ];
	}

	function dgRowToXML( $srcArr , $dstNode , $extAttrMap = array() , $intAttrMap = array() ) {
		$dstDoc = $dstNode->ownerDocument ;
		foreach( $extAttrMap as $sRa => $cRa ) {
			$dstAttrNode = $dstDoc->createElement( $cRa );
			$dstAttrNode->appendChild( $dstDoc->createTextNode( iconv( 'cp1251' , 'utf8' , $srcArr[ $sRa ] ) ) );
			$dstNode->appendChild( $dstAttrNode );
		}

		foreach( $intAttrMap as $sRa => $cRa ) {
			$dstNode->setAttribute( $cRa , $srcArr[ $sRa ] );
		}
	}


