/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	/**
	* @var lastOpenedYear
	*/
	/**
	* @var typeIDs
	*/
	/**
	* @var UserThemeLoc
	*/
	/**
	* @var openedBlocks
	*/

	$.windowOnLoad.push( function() {
		var yearTabsDOM = {};
		var labels = document.querySelectorAll( '.tt-tabs > input[name="tt-tabs"]' );
		for( var i = 0 ; i < labels.length ; i++ ) {
			var ci = labels[ i ];

			if ( "onpropertychange" in ci ) {
				// старый IE
				ci.onpropertychange = function( x , y ) {
					return function() {
						// проверим имя изменённого свойства
						if (event.propertyName == "checked") {
							pageSelected( x.dataset[ 'ttt' ] , y );
						}
					};
				} ( ci , yearTabsDOM );
			} else {
				// остальные браузеры
				ci.onchange = function( x , y ) {
					return function () {
						pageSelected( x.dataset[ 'ttt' ] , y );
					};
				} ( ci , yearTabsDOM );
			}
		}

		var re1 = '' ;
		for( var i = 0 ; i < labels.length - 1 ; i++ ) {
			re1 += ' + * ' ;
		}
		var re2 = ' + * + * ' + re1 + re1 ;

		var tmp = document.createElement( 'style' );
		document.head.appendChild( tmp );
		var sheet = tmp.sheet ;
		var rules = sheet.cssRules || sheet.rules;
		var numRules = rules.length ;
		sheet.insertRule( '.tt-tabs :checked' + re1 + ' + label { background-color : inherit ; padding : 0.25em 1em ; border-top : 0.125em solid #444 ; color : #444 ; }' , numRules );
		numRules = rules.length ;
		sheet.insertRule( '.tt-tabs :checked' + re1 + ' + label:hover { border-color : #000 ; color : #000 ; }' , numRules );
		numRules = rules.length ;
		sheet.insertRule( '.tt-tabs :checked' + re2 + ' + div { display : block ; }' , numRules );

		for( var i = 0 ; i < typeIDs.length ; i++ ) {
			var cttt = typeIDs[ i ];
			var ti = 'type-' + cttt ;
			yearTabsDOM[ ti ] = {};
			var yearTabs = document.querySelectorAll( 'div#year-area--' + cttt + ' > a[data-type="' + cttt + '"]' );
			var tab = document.getElementById( 'tt-' + cttt );
			for( var j = 0 ; j < yearTabs.length ; j++ ) {
				var cyt = yearTabs[ j ];
				var cy = cyt.dataset[ 'year' ];
				yearTabsDOM[ ti ][ 'y-' + cy ] = cyt ;
				cyt.onclick = function ( w , x , y , z ) {
					return function () {
						yearSelected( w , x , y , z );
					};
				} ( tab , cttt , cy , yearTabsDOM );
			}
		}
	} );

	function setOpenedBlock( ttType , m , y , v ) {
		if ( !openedBlocks[ 'type-' + ttType ] ) {
			openedBlocks[ 'type-' + ttType ] = {};
		}
		if ( !openedBlocks[ 'type-' + ttType ][ 'y-' + y ] ) {
			openedBlocks[ 'type-' + ttType ][ 'y-' + y ] = {};
		}
		openedBlocks[ 'type-' + ttType ][ 'y-' + y ][ 'm-' + m ] = v ;
	}

	function hlYearTab( ttType , year , ytd ) {
		var ti = 'type-' + ttType ;
		for( var yi in ytd[ ti ] ) {
			var cyt = ytd[ ti ][ yi ];
			cyt.className = 'year-link' ;
		}
		var yi = 'y-' + year ;
		var cyt = ytd[ ti ][ yi ];
		cyt.className = 'c-year-link' ;
	}

	function pageSelected( ttType , ytd ) {
		var tab = document.getElementById( 'tt-' + ttType );
		var ti = 'type-' + ttType ;
		var loy ;
		if ( !lastOpenedYear[ ti ] ) {
			loy = ( new Date() ).getFullYear();
		} else {
			loy = lastOpenedYear[ ti ];
		}

		hlYearTab( ttType , loy , ytd );

		var yi = 'y-' + loy ;

		if ( !openedBlocks[ ti ] ) {
			openedBlocks[ ti ] = {};
		}
		if ( !openedBlocks[ ti ][ yi ] ) {
			openedBlocks[ ti ][ yi ] = {};
		}
		yearSelectedFill( tab , ttType , loy );
	}

	function yearSelected( tab , ttType , year , ytd ) {
		var ti = 'type-' + ttType ;
		if ( lastOpenedYear[ ti ] && lastOpenedYear[ ti ] == year ) {
			return ;
		}

		hlYearTab( ttType , year , ytd );

		var yi = 'y-' + year ;

		while ( tab.rows.length > 0 ) {
			tab.deleteRow( tab.rows.length - 1 );
		}

		if ( !openedBlocks[ ti ] ) {
			openedBlocks[ ti ] = {};
		}
		if ( !openedBlocks[ ti ][ yi ] ) {
			openedBlocks[ ti ][ yi ] = {};
		}
		yearSelectedFill( tab , ttType , year );
	}

	function yearSelectedFill( tab , ttType , year ) {
		if ( typeof tab === 'string' ) {
			tab = document.getElementById( 'tt-' + ttType );
		}
		var monthList = sendXML( '<get-month-list year="' + year + '" />' , false , 'main.ajax.php' , 'type=' + ttType );

		for( var i = 0 ; i < monthList.childNodes.length ; i++ ) {
			var cmie = monthList.childNodes[ i ];
			var com = openedBlocks[ 'type-' + ttType ][ 'y-' + year ];
			var cm = parseInt( cmie.getAttribute( 'index' ) , 10 );
			var tmyID = '' + ttType + '_' + cm + '_' + year ;
			var cr = document.getElementById( 'ttLIST_cap_' + tmyID );
			if ( !cr ) {
				cr = tab.insertRow( -1 );
				cr.id = 'ttLIST_cap_' + tmyID ;
				var crc = addTabCell( cr , 'ttMonth' );
				crc.unselectable = 'on' ;
				crc.onclick = function ( a , b , c ) {
					return function () {
						tc( a , b , c );
					};
				} ( cm , year , ttType );
				var tmp = document.createElement( 'img' );
				tmp.id = 'cttbimg_' + tmyID ;
				tmp.border = 0 ;
				tmp.src = 'themes/' + UserThemeLoc + '/' + ( com[ 'm-' + cm ] ? 'col' : 'exp' ) + '.bmp' ;
				crc.appendChild( tmp );
				crc.appendChild( document.createTextNode( getXMLNodeValue( cmie ) ) );
			}

			var cr2 = document.getElementById( 'ttLIST_' + tmyID );
			if ( !cr2 ) {
				cr2 = tab.insertRow( -1 );
				cr2.id = 'ttLIST_' + tmyID ;
				cr2.style.display = 'none' ;
				var crc = addTabCell( cr2 , 'ttl' );
				var tmp = document.createElement( 'div' );
				crc.appendChild( tmp );
			}

			var tmp2 = document.getElementById( 'ttLIST_t_' + tmyID );
			if ( !tmp2 ) {
				tmp2 = document.createElement( 'table' );
				tmp2.id = 'ttLIST_t_' + tmyID ;
				tmp2.className = 'ttlt' ;
				tmp.appendChild( tmp2 );
			}

			if ( com[ 'm-' + cm ] ) {
				while ( tmp2.rows.length > 0 ) {
					tmp2.deleteRow( 0 );
				}
				fill( tmp2 , cm , year , ttType );
				cr2.style.display = '' ;
			}
		}

		lastOpenedYear[ 'type-' + ttType ] = year ;
	}

	function fill( t , m , y , ttType ) {
		var r = t.insertRow( -1 );
			addTabCell( r , "ttcapt_c1" );
			addTabCell( r , "ttcapt_c2" , "дата" );
			addTabCell( r , "ttcapt_c3" , "куда (адрес)" );
			addTabCell( r , "ttcapt_c4" , "цель выезда" );
			addTabCell( r , "ttcapt_c5" , "эксперт" );

		var lst = sendXML( '<get-list m="' + m + '" y="' + y + '" />' , false , 'main.ajax.php' , 'type=' + ttType );
		for( var i = 0 ; i < lst.childNodes.length ; i++ ) {
			var rec = lst.childNodes[ i ];
			var destNode = null ;
			var purposeNode = null ;
			var expertNode = null ;
			for( var j = 0 ; j < rec.childNodes.length ; j++ ) {
				switch ( rec.childNodes[ j ].nodeName ) {
					case "destination" :
						destNode = rec.childNodes[ j ];
						break ;
					case "purpose" :
						purposeNode = rec.childNodes[ j ];
						break ;
					case "expert" :
						expertNode = rec.childNodes[ j ];
						break ;
				}
			}
			r = t.insertRow( -1 );
				r.className = "ttDoW" + rec.getAttribute( "w" );
				var c = addTabCell( r , "ttdc_1" );
				var rid = rec.getAttribute( "del" )
				if ( rid != null ) {
					var inp = document.createElement( "input" );
					inp.type = "checkbox" ;
					inp.name = "ttli[]" ;
					inp.value = rid ;
					inp.checked = false ;
					c.appendChild( inp );
				}
				rid = rec.getAttribute( "edit" )
				if ( rid != null ) {
					var img = document.createElement( "img" );
					img.onclick = function( x , v ) {
						return function() {
							showEditRecordDlg( x , v );
						}
					}( rid , ttType );
					img.src = "themes/" + UserThemeLoc + "/edit.gif" ;
					c.appendChild( img );
				}

				addTabCell( r , "ttdc_2" , rec.getAttribute( "d" ) );
				addTabCell( r , "ttdc_3" , getXMLNodeValue( destNode ) );
				addTabCell( r , "ttdc_4" , getXMLNodeValue( purposeNode ) );
				addTabCell( r , "ttdc_5" , getXMLNodeValue( expertNode ) );
		}
	}

	function tc( m , y , ttType ) {
		var tmyID = ttType + '_' + m + "_" + y ;
		var ttl = document.getElementById( "ttLIST_" + tmyID );
		var tti = document.getElementById( "cttbimg_" + tmyID );
		var ttlt = document.getElementById( "ttLIST_t_" + tmyID );
		if ( ttl.style.display == "none" ) {
			setOpenedBlock( ttType , m , y , 1 );
			if ( ttlt.rows.length == 0 ) {
				fill( ttlt , m , y , ttType );
			}

			ttl.style.display = "" ;
			tti.src = "themes/" + UserThemeLoc + "/col.bmp" ;
		} else {
			setOpenedBlock( ttType , m , y , 0 );
			ttl.style.display = "none" ;
			tti.src = "themes/" + UserThemeLoc + "/exp.bmp" ;
		}
	}

	/**
	 * @param {HTMLTableRowElement} tr
	 * @param {string} cn
	 * @param {string} [text]
	 * @returns {HTMLTableDataCellElement}
	 */
	function addTabCell( tr , cn , text ) {
		if ( typeof( text ) == "undefined" ) {
			text = "" ;
		}

		var c = document.createElement( "td" );
		c.className = cn ;
		setText( c , text );
		tr.appendChild( c );
		return c ;
	}

	function showAddRecordDlg( ttType ) {
		var addRecordDlg = document.getElementById( "add_record_dlg" );
		addRecordDlg.style.display = "none" ;

		var addRecordDlgBtn = document.getElementById( "add_record_dlg_btn" );
		addRecordDlgBtn.onclick = function( v ) {
			return function() {
				addRecordDlg_Add( v );
			};
		} ( ttType );
		setText( addRecordDlgBtn , "Добавить" );

		var i_date = document.getElementById( "i_date" );
		i_date.disabled = false ;
		i_date.readOnly = false ;


		addRecordDlg.style.display = "" ;
	}

	function hideAddRecordDlg() {
		var ard = document.getElementById( "add_record_dlg" );
		ard.style.display = "none" ;
	}

	function addRecordDlg_Add( ttType ) {
		var iDate = document.getElementById( "i_date" );
		var iPurpose = document.getElementById( "i_purpose" );
		var iDestination = document.getElementById( "i_destination" );
		var iExperts = document.getElementById( "i_experts" );

		var res = sendXML( "<add-record><date>" + toCDATA( iDate.value ) + "</date><purpose>" + toCDATA( iPurpose.value ) + "</purpose><destination>" + toCDATA( iDestination.value ) + "</destination><experts>" + toCDATA( iExperts.value ) + "</experts></add-record>" , false , 'main.ajax.php' , 'type=' + ttType );
		var resStatus = res.getAttribute( "status" );
		if ( resStatus == "date-error" ) {
			alert( "Дата должна быть указана в формате дд.мм.гггг\nГод должен быть в пределах 2000-2100" );
			return ;
		}

		if ( resStatus == "ok" ) {
			hideAddRecordDlg();
			var m = res.getAttribute( "m" );
			var y = res.getAttribute( "y" );
			var tmyID = ttType + '_' + m + "_" + y ;
			var ttlt = document.getElementById( "ttLIST_t_" + tmyID );
			if ( ttlt != null && ttlt.rows.length != 0 ) {
				while ( ttlt.rows.length > 0 ) {
					ttlt.deleteRow( 0 );
				}
				fill( ttlt , m , y , ttType );
			}
		}
	}

	function showEditRecordDlg( id , ttType ) {
		var addRecordDlg = document.getElementById( "add_record_dlg" );
		addRecordDlg.style.display = "none" ;

		var addRecordDlgBtn = document.getElementById( "add_record_dlg_btn" );
		addRecordDlgBtn.onclick = function ( x , y ){
			return function () {
				editRecordDlg_Edit( x , y );
			}
		}( id , ttType );
		setText( addRecordDlgBtn , "Заменить" );


		var res = sendXML( "<get-record id=\"" + id + "\"/>" , false , 'main.ajax.php' , 'type=' + ttType );

		var iDate = document.getElementById( "i_date" );
		iDate.value = "" ;
		var iPurpose = document.getElementById( "i_purpose" );
		iPurpose.value = "" ;
		var iDestination = document.getElementById( "i_destination" );
		iDestination.value = "" ;
		var iExperts = document.getElementById( "i_experts" );
		iExperts.value = "" ;

		if ( res != null ) {
			iDate.value = res.getAttribute( "d" );
			for ( var i = 0 ; i < res.childNodes.length ; i++ ) {
				switch ( res.childNodes[ i ].nodeName ) {
					case "destination" :
						iDestination.value = getXMLNodeValue( res.childNodes[ i ] );
						break ;
					case "purpose" :
						iPurpose.value = getXMLNodeValue( res.childNodes[ i ] );
						break ;
					case "expert" :
						iExperts.value = getXMLNodeValue( res.childNodes[ i ] );
						break ;
				}

			}
		}

		var i_date = document.getElementById( "i_date" );
		i_date.disabled = true ;
		i_date.readOnly = true ;

		addRecordDlg.style.display = "" ;
	}

	function editRecordDlg_Edit( id , ttType ) {
		var iDate = document.getElementById( "i_date" );
		var iPurpose = document.getElementById( "i_purpose" );
		var iDestination = document.getElementById( "i_destination" );
		var iExperts = document.getElementById( "i_experts" );

		var res = sendXML( "<edit-record id=\"" + id + "\"><date>" + toCDATA( iDate.value ) + "</date><purpose>" + toCDATA( iPurpose.value ) + "</purpose><destination>" + toCDATA( iDestination.value ) + "</destination><experts>" + toCDATA( iExperts.value ) + "</experts></edit-record>" , false , 'main.ajax.php' , 'type=' + ttType );
		var resStatus = res.getAttribute( "status" );
		if ( resStatus == "date-error" ) {
			alert( "Дата должна быть указана в формате дд.мм.гггг\nГод должен быть в пределах 2000-2100" );
			return ;
		}

		if ( resStatus == "ok" ) {
			hideAddRecordDlg();
			var m = res.getAttribute( "m" );
			var y = res.getAttribute( "y" );
			var tmyID = ttType + '_' + m + "_" + y ;
			var ttlt = document.getElementById( "ttLIST_t_" + tmyID );
			if ( ttlt != null && ttlt.rows.length != 0 ) {
				while ( ttlt.rows.length > 0 ) {
					ttlt.deleteRow( 0 );
				}
				fill( ttlt , m , y , ttType );
			}
		}
	}

	function deleteRecords( ttType ) {
		var ia = document.getElementsByName( "ttli[]" );
		var dr = "<delete-records>" ;
		for ( var i = 0 ; i < ia.length ; i++ ) {
			if ( ia[ i ].checked ) {
				dr+= "<r id=\"" + ia[ i ].value + "\"/>" ;
			}
		}

		dr+= "</delete-records>" ;
		var res = sendXML( dr , false , 'main.ajax.php' , 'type=' + ttType );
		if ( res != null && res.getAttribute( "status" ) == "ok" ) {
			for( var i = 0 ; i < res.childNodes.length ; i++ ) {
				var m = res.childNodes[ i ].getAttribute( "m" );
				var y = res.childNodes[ i ].getAttribute( "y" );
				var tmyID = ttType + '_' + m + "_" + y ;
				var ttlt = document.getElementById( "ttLIST_t_" + tmyID );
				if ( ttlt != null && ttlt.rows.length != 0 ) {
					while ( ttlt.rows.length > 0 ) {
						ttlt.deleteRow( 0 );
					}
					fill( ttlt , m , y , ttType );
				}
			}
		}
	}

	var fill_dlg_data = null ;

	function fill2( ttType ) {
		var cy = fill_dlg_data.y ;
		var cm = fill_dlg_data.m ;
		var ct = new Date( cy , cm + 1 , 0 );
		var dc = ct.getDate();
		var monthNames = "Январь,Февраль,Март,Апрель,Май,Июнь,Июль,Август,Сентябрь,Октябрь,Ноябрь,Декабрь".split( "," );
		var fdcm = document.getElementById( "fill_dlg_cont_month" );
		setText( fdcm , monthNames[ ct.getMonth() ] + " " + ct.getFullYear() );

		var fdct = document.getElementById( "fill_dlg_cont_tab" );
		while ( fdct.rows.length > 0 ) {
			fdct.deleteRow( 0 );
		}

		var res = sendXML( "<get-exp-list/>" , false , 'main.ajax.php' , 'type=' + ttType );
		var expList = [];
		for ( var i = 0 ; i < res.childNodes.length ; i++ ) {
			expList.push( [ res.childNodes[ i ].getAttribute( "id" ) , getXMLNodeValue( res.childNodes[ i ] ) ] );
		}

		var r = fdct.insertRow( -1 );
			addTabCell( r , "fdc-t-cap1" , "Дата" );
			addTabCell( r , "fdc-t-cap2" );
			for ( var i = 0 ; i < expList.length ; i++ ) {
				var c = addTabCell( r , "fdc-t-cap3" );
					var img = document.createElement( "img" );
					img.src = "main.img.php?t=" + encodeURIComponent( expList[ i ][ 1 ] );
					c.appendChild( img );
			}

		var weekDayNames = "вс,пн,вт,ср,чт,пт,сб".split( "," );

		for ( var i = 1 ; i <= dc ; i++ ) {
			ct.setFullYear( cy , cm , i );
			var cwd = ct.getDay();
			if ( cwd == 0 ) {
				cwd = 7 ;
			}

			var r = fdct.insertRow( -1 );
			r.className = "ttDoW" + cwd ;

				addTabCell( r , "fdc-t-d1" , i + " " + weekDayNames[ ct.getDay() ] );

				var c = addTabCell( r , "fdc-t-d2" );
					var inp = document.createElement( "input" );
					inp.type = "radio" ;
					inp.name = "fdcsi[" + i + "]" ;
					inp.value = "" ;
					inp.checked = true ;
					inp.title = "не назначать" ;
					c.appendChild( inp );

				for ( var j = 0 ; j < expList.length ; j++ ) {
					var c = addTabCell( r , "fdc-t-d3" );
						var inp = document.createElement( "input" );
						inp.type = "radio" ;
						inp.name = "fdcsi[" + i + "]" ;
						inp.value = expList[ j ][ 0 ];
						inp.checked = false ;
						inp.title = expList[ j ][ 1 ];
						c.appendChild( inp );
				}
		}
	}

	function showFillDlg( ttType ) {
		var fillDlg = document.getElementById( "fill_dlg" );
		fillDlg.style.display = "none" ;

		var ct = new Date();

		var cm = ct.getMonth();
		var cy = ct.getFullYear();

		fill_dlg_data = {
			y : cy ,
			m : cm ,
			type : ttType
		};

		fill2( ttType );

		fillDlg.style.display = "" ;
	}

	function hideFillDlg() {
		var fd = document.getElementById( "fill_dlg" );
		fd.style.display = "none" ;
	}

	function fillDlg_ChangeMonth( v ) {
		var m = fill_dlg_data.m ;
		var y = fill_dlg_data.y ;
		if ( m + v < 1 ) {
			fill_dlg_data.y-- ;
			fill_dlg_data.m = 12 ;
		} else
		if ( m + v > 12 ) {
			fill_dlg_data.y++ ;
			fill_dlg_data.m = 1 ;
		} else {
			fill_dlg_data.m = m + v ;
		}

		fill2();
	}

	function fillDlg_Fill( ttType ) {
		var cy = fill_dlg_data.y ;
		var cm = fill_dlg_data.m ;
		var ct = new Date( cy , cm + 1 , 0 );
		var dc = ct.getDate();
		var s = '<fill m="' + ( cm + 1 ) + '" y="' + cy + '">' ;
		for ( var i = 1 ; i <= dc ; i++ ) {
			var fd = document.getElementsByName( 'fdcsi[' + i + ']' );
			var id = "" ;
			for ( var j = 0 ; j < fd.length ; j++ ) {
				if ( fd[ j ].checked ) {
					id = fd[ j ].value ;
				}
			}

			if ( id != "" ) {
				s+= '<r d="' + i + '" e="' + id + '"/>' ;
			}
		}
		s+= "</fill>" ;
		var res = sendXML( s , false , 'main.ajax.php' , 'type=' + ttType );
		if ( res != null && res.getAttribute( 'status' ) == 'ok' ) {
			hideFillDlg();
			var ttlt = document.getElementById( "ttLIST_t_" + ( cm + 1 ) + "_" + cy );
			if ( ttlt != null && ttlt.rows.length != 0 ) {
				while ( ttlt.rows.length > 0 ) {
					ttlt.deleteRow( 0 );
				}
				fill( ttlt , ( cm + 1 ) , cy );
			}
		}
	}


