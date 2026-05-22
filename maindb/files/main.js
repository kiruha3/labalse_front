/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	function tc( id ) {
		var ttl = document.getElementById( "mit" + id );
		var tti = document.getElementById( "tcimg" + id );
		if ( ttl.style.display == "none" ) {
			ttl.style.display = "" ;
			tti.src = "themes/" + UserThemeLoc + "/col.bmp" ;
		} else {
			ttl.style.display = "none" ;
			tti.src = "themes/" + UserThemeLoc + "/exp.bmp" ;
		}
	}

	function tc2( rid ) {
		var row = document.getElementById( "lvl1cRow" + rid );
		var subRow = document.getElementById( "mit" + rid );
		var img = document.getElementById( "tcimg" + rid );
		
		if ( !subRow ) {
			var nextRow = row.nextSibling ;
			
			var doc = sendXML( "<get-l2-e-cards rid=\"" + rid + "\"/>" , false , "main.php" );
			
			if ( doc.childNodes.length == 0 ) {
				return ;
			}
			
			var subRow = document.createElement( "tr" );
			subRow.className = "l2l3-row" ;
			subRow.id = "mit" + rid ;
			
			var subTableContainerWrapper = subRow.insertCell( -1 );
			subTableContainerWrapper.colSpan = 13 ;
			subTableContainerWrapper.className = "l2l3-cont-wrapper" ;
			
			var subTableContainer = document.createElement( "div" );
			subTableContainer.className = "l2l3-cont" ;
			
			var subTable = document.createElement( "table" );
			subTable.align = "center" ;
			subTable.style.borderCollapse = "collapse" ;
			var dcn = doc.childNodes ;
			for( var i = 0 ; i < dcn.length ; i++ ) {
				mkL2L3Rows( subTable , dcn[ i ] , doc );
			}
			
			subTableContainer.appendChild( subTable );
			
			var subTableDecor = document.createElement( "div" );
			subTableDecor.className = "l2l3-cont-decor" ;
			subTableContainer.appendChild( subTableDecor );
			
			subTableContainerWrapper.appendChild( subTableContainer );
			
			if ( nextRow ) {
				row.parentNode.insertBefore( subRow , nextRow );
			} else {
				row.parentNode.appendChild( subRow );
			}
			
			img.style.backgroundPosition = "-13px -132px" ;
		} else {
			if ( subRow.style.display == "none" ) {
				subRow.style.display = "" ;
				img.style.backgroundPosition = "-13px -132px" ;
			} else {
				subRow.style.display = "none" ;
				img.style.backgroundPosition = "" ;
			}
		}		
	}

	var misa = [];
	misa[ "miaw" ] = "miaws" ;
	misa[ "miaws" ] = "miaw" ;
	misa[ "miawh" ] = "miawhs" ;
	misa[ "miawhs" ] = "miawh" ;
	misa[ "micw" ] = "micws" ;
	misa[ "micws" ] = "micw" ;
	misa[ "micwh" ] = "micwhs" ;
	misa[ "micwhs" ] = "micwh" ;
	misa[ "mig" ] = "mig" ;

	function ts( id ) {
		row = document.getElementById( "lvl1cRow" + id );
		row.className = misa[ row.className ];
	}

	function delete_req( cid ) {
		res = prompt( "Для подтверждения удаления карточки напечатайте в нижней строке слово \"П О Д Т В Е Р Ж Д А Ю\" без пробелов" , "" );
		if ( res == "ПОДТВЕРЖДАЮ" ) {
			url = "processor.php?lvl2cdelete=" + cid ;
			if ( window.XMLHttpRequest ) {
				req = new XMLHttpRequest();
				req.onreadystatechange = delete_req_processReqChange ;
				req.open( 'GET' , url , true );
				req.send( null );
			} else
			if ( window.ActiveXObject ) {
				req = new ActiveXObject('Microsoft.XMLHTTP');
				if ( req ) {
					req.onreadystatechange = delete_req_processReqChange ;
					req.open( 'GET' , url , true );
					req.send();
				}
			}
		} else {
			alert( "Удаление не подтверждено" );
		}
	}

	function delete_req_processReqChange() {
		if ( req.readyState == 4 ) {
			if ( req.status == 200 ) {
				alert( "Карточка удалена." );
				window.location.reload( true );
			} else {
				alert( "Облом:\r\n" + req.statusText );
			}
		}
	}

	function exMatCheck01( kc ) {
		if ( kc != 13 ) {
			return ;
		}
		var n = document.getElementById( "iMatChecker01" ).value ;
		if ( n.match( /^\d+$/ ) )
		for ( var i = 0 ; i < n.length ; i++ ) {
			if ( "0123456789".indexOf( n.charAt( i ) ) < 0  ) {
				alert( "Недопустимый номер дела. Номер должен состоять только из цифр." );
				return ;
			}
		}

		var y = parseInt( document.getElementById( "sMatChecker01" ).value , 10 );
		var url = "main.check-01.php?n=" + n + "&y=" + y ;
		if ( window.XMLHttpRequest ) {
			req = new XMLHttpRequest();
			req.onreadystatechange = exMatCheck01_processReqChange ;
			req.open( 'GET' , url , true );
			req.send( null );
		} else
		if ( window.ActiveXObject ) {
			req = new ActiveXObject('Microsoft.XMLHTTP');
			if ( req ) {
				req.onreadystatechange = exMatCheck01_processReqChange ;
				req.open( 'GET' , url , true );
				req.send();
			}
		}
	}


	function exMatCheck01_processReqChange() {
		if ( req.readyState == 4 ) {
			if ( req.status == 200 ) {
				res_text = req.responseText ;
				res_text = res_text.split( "\r\n" );
				mc01r = document.getElementById( "rMatChecker01" );
				if ( res_text[ 0 ] == "FINISHED" ) {
					mc01r.className = "mc01-rf" ;
					mc01r.innerHTML = "завершено : " + res_text[ 1 ] + "<br>" + res_text[ 2 ];
				} else
				if ( res_text[ 0 ] == "UNFINISHED" ) {
					mc01r.className = "mc01-ruf" ;
					mc01r.innerHTML = "незавершено : " + res_text[ 1 ] + "<br>" + res_text[ 2 ];
				} else {
					mc01r.className = "mc01-ru" ;
					mc01r.innerHTML = "неопределено<br>" + res_text[ 1 ];
				}
			} else {
				alert( "Облом:\r\n" + req.statusText );
			}
		}
	}

	function showsinglerow() {
		n = document.getElementById( "iMatChecker01" ).value ;
		y = parseInt( document.getElementById( "sMatChecker01" ).value , 10 );
		window.location.replace( "main.php?singlerow&n=" + n + "&y=" + y );
	}

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

	var letter_dlg__selected_mat_id = null ;
	
	function normalizeAddress( s ) {
		return s
			.replaceAll( /\s+,/g , ',' )
			.replaceAll( /,(\S)/g , ', $1' )
			.replaceAll( /область\s*,/g , 'обл.,' )
			.replaceAll( /,\s*д\.\s*(\d+)/g , ', д. $1' )
			.replaceAll( /,\s*(ул|г|с)\.\s*(\S)/g , ', $1. $2' )
			.trim();
	}

	function showLetterDlg( event , id ) {
		event = window.event ? window.event : event ;
		event.target = event.target ? event.target : event.srcElement ;
		if( event.stopPropagation ) {
			event.stopPropagation();
		} else {
			event.cancelBubble = true ;
		}

		letter_dlg__selected_mat_id = id ;

		const lf = getCookie( 'labelFormat' );
		const labelFormat = document.getElementById( 'labelFormat' );
		labelFormat.value = lf ;

		const letterDlg = document.getElementById( 'letter_dlg' );
		letterDlg.style.display = 'none' ;

		const ldt2 = document.getElementById( 'letter_dlg_tab_2' );
		while ( ldt2.rows.length > 0 ) {
			ldt2.deleteRow( 0 );
		}

		let r , c ;
		r = ldt2.insertRow( -1 );
			addTabCell( r , 'ldt2-cap' );
			addTabCell( r , 'ldt2-cap' , 'Ф.И.О. сотрудника' );
			
		let res ;
		res = sendXML( '<get-workers-list id="' + id + '" />' , false , 'main.letters.php' );

		const resCount = parseInt( res.getAttribute( 'count' ) , 10 );

		if ( resCount == 0 ) {
			alert( 'Экспертиза не распределена или нет сотрудников, ее выполняющих.' );
			return ;
		}

		for( let i = 0 ; i < res.childNodes.length ; i++ ) {
			const lData = res.childNodes[ i ];

			r = ldt2.insertRow( -1 );
				c = addTabCell( r , 'ldt2-radio-btn' );
					const inp = document.createElement( 'input' );
					inp.type = 'radio' ;
					inp.name = 'letter_dlg__worker_id' ;
					inp.value = lData.getAttribute( 'id' );
					inp.checked = ( resCount == 1 );
				c.appendChild( inp );
			addTabCell( r , 'ldt2-worker' , getXMLNodeValue( lData ) );
		}


		const ldt = document.getElementById( 'letter_dlg_tab' );
		while ( ldt.rows.length > 0 ) {
			ldt.deleteRow( 0 );
		}

		r = ldt.insertRow( -1 );
			addTabCell( r , 'ldt-cap' );
			addTabCell( r , 'ldt-cap' , 'Кому' );
			addTabCell( r , 'ldt-cap' , 'Куда' );

		res = sendXML( '<get-address-list id="' + id + '" />' , false , 'main.letters.php' );
		
		const addrDataMap = {};

		for( let i = 0 ; i < res.childNodes.length ; i++ ) {
			const lData = res.childNodes[ i ];
			
			const addrData = {
				addressee : '' ,
				destination : ''
			};
			
			for( const cn of lData.childNodes ) {
				switch ( cn.nodeName ) {
					case 'addressee' :
					case 'destination' :
						addrData[ cn.nodeName ] = getXMLNodeValue( cn )
							.replaceAll( /\s+/g , ' ' )
							.trim();
						break ;
				}
			}
			
			addrData.destination = normalizeAddress( addrData.destination );
			if ( !addrDataMap[ addrData.addressee.toUpperCase() ] ) {
				addrDataMap[ addrData.addressee.toUpperCase() ] = {};
			}
			const cadm = addrDataMap[ addrData.addressee.toUpperCase() ];
			if ( !cadm[ addrData.destination.toUpperCase() ] ) {
				cadm[ addrData.destination.toUpperCase() ] = addrData ;
			}
		}
		
		for( const addressee in addrDataMap ) {
			const cad = addrDataMap[ addressee ];
			const destinationKeys = Object.keys( cad );
			let first = true ;
			
			for( const dk of destinationKeys ) {
				if ( dk != '' || destinationKeys.length == 1 ) {
					const addrData = cad[ dk ];
					r = ldt.insertRow( -1 );
						c = addTabCell( r , 'ldt-prna-btn' );
							const inp = document.createElement( 'a' );
							inp.onclick = function( mid , ad ) {
								return function() {
									printLetterLabel( mid , ad );
								}
							} ( id , addrData );
							const img = document.createElement( 'div' );
							img.className = 'ldt-prna-img' ;
							inp.appendChild( img );
						c.appendChild( inp );
						
					if ( first ) {
						const atc = addTabCell( r , 'ldt-addressee' , addrData.addressee );
						if ( destinationKeys.length > 1 ) {
							atc.rowSpan = destinationKeys.length ;
						}
					}
					addTabCell( r , 'ldt-destination' , addrData.destination );
					first = false ;
				}
			}
		}
		
		/*for( let i = 0 ; i < res.childNodes.length ; i++ ) {
			const lData = res.childNodes[ i ];
			
			const addrData = {
				addressee : '' ,
				destination : ''
			};
			
			for( const cn of lData.childNodes ) {
				switch ( cn.nodeName ) {
					case 'addressee' :
					case 'destination' :
						addrData[ cn.nodeName ] = getXMLNodeValue( cn ).trim();
						break ;
				}
			}
			
			addrData.destination = normalizeAddress( addrData.destination );
			if ( !addrDataMap[ addrData.addressee ] ) {
				addrDataMap[ addrData.addressee ] = {};
			}
			const cadm = addrDataMap[ addrData.addressee ];
			if ( !cadm[ addrData.destination.toUpperCase() ] ) {
				cadm[ addrData.destination.toUpperCase() ] = addrData ;
			}
			
			r = ldt.insertRow( -1 );
			c = addTabCell( r , 'ldt-prna-btn' );
			const inp = document.createElement( 'a' );
			inp.onclick = function( mid , ad ) {
				return function() {
					printLetterLabel( mid , ad );
				}
			} ( id , addrData );
			const img = document.createElement( 'div' );
			img.className = 'ldt-prna-img' ;
			inp.appendChild( img );
			c.appendChild( inp );
			addTabCell( r , 'ldt-addressee' , addrData.addressee );
			addTabCell( r , 'ldt-destination' , addrData.destination );
		}*/
		
		const w = document.getElementById( 'new-weight' );
		w.value = '' + ( wbp1 / 1000 ).toFixed( 3 );
		const p = document.getElementById( 'new-price' );
		p.value = bp1.toFixed( 2 ) + ' + 0.00' ;
		
		letterDlg.style.display = '' ;
	}

	function hideLetterDlg() {
		var ld = document.getElementById( "letter_dlg" );
		ld.style.display = "none" ;
	}

	function showAddressesFillDlg( event , id ) {
		event = window.event ? window.event : event ;
		event.target = event.target ? event.target : event.srcElement ;
		if( event.stopPropagation ) {
			event.stopPropagation();
		} else {
			event.cancelBubble = true ;
		}

		var addressesFillDlg = document.getElementById( "addresses_fill_dlg" );
		addressesFillDlg.style.display = "none" ;

		var addressesFillDlgFrame = document.getElementById( "addresses_fill_dlg_frame" );
		addressesFillDlgFrame.src = "letter.addresses.frame.php?mid=" + id ;
		addressesFillDlg.style.display = "" ;
	}

	function hideAddressesFillDlg() {
		var afd = document.getElementById( "addresses_fill_dlg" );
		afd.style.display = "none" ;
	}

	function sendMessage() {
		var su = document.getElementsByName( "letter_dlg__worker_id" );
		var wid = null ;
		for ( var i = 0 ; i < su.length ; i++ ) {
			if ( su[ i ].checked == true ) {
				wid = su[ i ].value ;
			}
		}

		if ( wid == null ) {
			alert( "Выбирите сотрудника для извещения." );
			return ;
		}

		sendXML( "<msg uwid=\"" + wid + "\"><title>" + toCDATA( "По экспертизе № " + ( letter_dlg__selected_mat_id % 1000000 ) ) + "</title><text>" + toCDATA( "Для отправки заключения/уведомления/запроса по экспертизе № " + ( letter_dlg__selected_mat_id % 1000000 ) + " необходимо указать все адреса[br][frame \"/maindb/letter.addresses.frame.php?mid=" + letter_dlg__selected_mat_id + "\"]" ) + "</text></msg>" , false , "/messages.php" , "simple_send_xml=simple_send_xml" );

		var letterDlg = document.getElementById( "letter_dlg" );
		letterDlg.style.display = "none" ;

		alert( "Уведомление отправлено" );
	}

	function labelFormatChange() {
		var lf = document.getElementById( "labelFormat" );
		setCookie( "labelFormat" , lf.value , 1000  );
	}

	function printLetterLabel( matID , addrData ) {
		const w = document.getElementById( 'new-weight' );
		const lt = document.getElementById( 'new-letter-type' );
		const pr = getPrices( w.value , lt.checked );

		const req = '<register-letter mat-id="' + matID + '" p1="' + pr.p1.toFixed( 2 ) + '" p2="' + pr.p2.toFixed( 2 ) + '" w="' + pr.w + '">' +
			'<addressee>' + toCDATA( addrData.addressee ) + '</addressee>' +
			'<destination>' + toCDATA( addrData.destination ) + '</destination>' +
		'</register-letter>' ;
		const lData = sendXML( req , false , 'main.letters.php' );

		DoPrintLetterLabel(
			addrData.addressee ,
			addrData.destination ,
			lData.getAttribute( 'index' )
		);
	}

	function doAll() {
		var res = sendXML( "<all db=\"maindb\" dateStart=\"1326142800\" dateEnd=\"1364504400\" />" , false , "all.php" );
		var WordApp = new ActiveXObject( "Word.Application" );
		WordApp.Visible = true ;
		var WordDoc = WordApp.Documents.Add( window.location.protocol + "//" + window.location.host + "/maindb/files/all.dot" );
		WordDoc.Activate();
		var tab = WordDoc.Tables.Item( 1 );
		for( var i = 0 ; i < res.childNodes.length ; i++ ) {
			var rNode = res.childNodes[ i ];
			if ( rNode.nodeName == "row1" ) {
				var row1_ay = "" ;
				var row1_at = "" ;
				var row1_e3 = "" ;
				var row1_e4 = "" ;
				var row1_e6 = "" ;
				var row1_e7 = "" ;
				var row1_e8 = "" ;
				var row1_e9 = "" ;
				for( var j = 0 ; j < rNode.childNodes.length ; j++ ) {
					switch( rNode.childNodes[ j ].nodeName ) {
						case "ay" : row1_ay = getXMLNodeValue( rNode.childNodes[ j ] ); break ;
						case "at" : row1_at = getXMLNodeValue( rNode.childNodes[ j ] ); break ;
						case "e3" : row1_e3 = getXMLNodeValue( rNode.childNodes[ j ] ); break ;
						case "e4" : row1_e4 = getXMLNodeValue( rNode.childNodes[ j ] ); break ;
						case "e6" : row1_e6 = getXMLNodeValue( rNode.childNodes[ j ] ); break ;
						case "e7" : row1_e7 = getXMLNodeValue( rNode.childNodes[ j ] ); break ;
						case "e8" : row1_e8 = getXMLNodeValue( rNode.childNodes[ j ] ); break ;
						case "e9" : row1_e9 = getXMLNodeValue( rNode.childNodes[ j ] ); break ;
					}
				}
				var r = tab.Rows.Add();
				r.HeightRule = 0 ;
				r.HeadingFormat = false ;
				var c = r.Cells.Item( 1 );
				c.Range.Text = rNode.getAttribute( "n" ) + " / * - " + rNode.getAttribute( "t" );
				c.Range.ParagraphFormat.Alignment = 2 ;
				var c = r.Cells.Item( 2 );
				c.Range.Text = rNode.getAttribute( "d" );
				c.Range.ParagraphFormat.Alignment = 1 ;
				var c = r.Cells.Item( 3 );
				c.Range.Text = row1_ay + ", " + row1_at + ", " + row1_e3 ;
				c.Range.ParagraphFormat.Alignment = 0 ;
				var c = r.Cells.Item( 4 );
				c.Range.Text = row1_e4 ;
				c.Range.ParagraphFormat.Alignment = 0 ;
				var c = r.Cells.Item( 5 );
				c.Range.Text = row1_e6 ;
				c.Range.ParagraphFormat.Alignment = 0 ;
				var c = r.Cells.Item( 6 );
				c.Range.Text = row1_e7 ;
				c.Range.ParagraphFormat.Alignment = 0 ;
				var c = r.Cells.Item( 7 );
				c.Range.Text = row1_e8 ;
				c.Range.ParagraphFormat.Alignment = 0 ;
				var c = r.Cells.Item( 8 );
				c.Range.Text = row1_e9 ;
				c.Range.ParagraphFormat.Alignment = 0 ;
			}
		}
	}

	var bp1 = 17.00 ;
	var bp2 = [ [ 17.00 , 37.00 ] , [ 35.00 , 50.00 ] ];

	var ws = 20 ;
	var wbp1 = 20 ;
	var wlt = 100 ;
	
	var ps = 2.0 ;

	function getPrices( w , t ) {
		w = "" + w ;
		var wm = w.match( /^\d*(?:[.,]\d{0,3})?$/ );
		if ( wm == null ) {
			return {
				s : 1
			};
		}
		
		wm = w.match( /^\d*[.,]$/ );
		if ( wm != null ) {
			w = w + "0" ;
		}
		
		wm = w.match( /^[.,]\d{0,3}$/ );
		if ( wm != null ) {
			w = "0" + w ;
		}
		
		w = Math.round( parseFloat( w.replace( "," , "." ) ) * 1000 );
		var wSave = w ;
		if ( w % 20 > 0 ) {
			w = w - w % 20 + 20 ;
		}
		
		w = Math.max( 1 , w );
		if ( isNaN( w ) ) {
			return {
				s : 1
			};
		}

		var p1 = 0.00 ;
		var p2 = 0.00 ;

		if ( !t && w <= wbp1 ) {
			p1 = bp1 ;
		} else {
			if ( w <= wlt ) {
				p2 = bp2[ t ? 1 : 0 ][ 0 ] + Math.round( ( w - ws ) / ws ) * ps ;
			} else {
				p2 = bp2[ t ? 1 : 0 ][ 1 ] + Math.round( ( w - wlt - ws ) / ws ) * ps ;
			}
		}

		return {
			w : wSave / 1000 ,
			p1 : p1 ,
			p2 : p2
		};
	}

	function changeWeight() {
		var w = document.getElementById( "new-weight" );
		var lt = document.getElementById( "new-letter-type" );
		var p = document.getElementById( "new-price" );

		var pr = getPrices( w.value , lt.checked );
		if ( isNaN( pr.w ) ) {
			alert( "Вес указан не правильно" );
			return ;
		}
		p.value = pr.p1.toFixed( 2 ) + " + " + pr.p2.toFixed( 2 );
	}
	
	function showGroup( gid , rid ) {
		var row = document.getElementById( "lvl1cRow" + rid );
		var doc = sendXML( "<get-group-cards gid=\"" + gid + "\" rid=\"" + rid + "\" />" , false , "main.php" );
		
		var nextRow = row.nextSibling ;
		while ( nextRow && nextRow.className == "l2l3-row" ) {
			nextRow = nextRow.nextSibling ;
		}
		
		var dcn = doc.childNodes ;
		var gel = [];
		var ges = {};
		var gesSub = {};
		for( var i = 0 ; i < dcn.length ; i++  ) {
			var geid = dcn[ i ].getAttribute( "id" );
			var ge = document.getElementById( "lvl1cRow" + geid );
			var geSub = document.getElementById( "mit" + geid );
			if ( ge && ge != nextRow ) {
				gel.push( { id : geid , dom : ge } );
				if ( geSub ) {
					gesSub[ "id" + geid ] = geSub ;
				}
			}
						
			if ( ge ) {
				ges[ "id" + geid ] = true ;
			}
			
			if ( nextRow && ge == nextRow ) {
				nextRow.className = "mig" ;
			}
		}
		
		//alert( JSON.stringify( dcn ) );
		
		for( var i = 0 ; i < gel.length ; i++ ) {
			gel[ i ].dom.parentNode.removeChild( gel[ i ].dom );
			if ( gesSub[ "id" + gel[ i ].id ] ) {
				gesSub[ "id" + gel[ i ].id ].parentNode.removeChild( gesSub[ "id" + gel[ i ].id ] );
			}
		}
		
		
		for( var i = 0 ; i < gel.length ; i++ ) {
			if ( gesSub[ "id" + gel[ i ].id ] ) {
				row.parentNode.insertBefore( gesSub[ "id" + gel[ i ].id ] , nextRow );
			}
			gel[ i ].dom.className = "mig" ;
			gel[ i ].dom.style.display = "" ;
			row.parentNode.insertBefore( gel[ i ].dom , nextRow );
		}
		
		
		for( var i = 0 ; i < dcn.length ; i++  ) {
			var geid = dcn[ i ].getAttribute( "id" );
			if ( !ges[ "id" + geid ] ) {
				var newRow = document.createElement( "tr" );
				mkL1Row( newRow , dcn[ i ] );
				if ( nextRow ) {
					row.parentNode.insertBefore( newRow , nextRow );
				} else {
					row.parentNode.appendChild( newRow );
				}
			}
		}
		
		row.className = "mig" ;
	}
	
	state1Map = [];
	state1Map[ "s-2" ] = {
		img : "e" ,
		descr : "ошибочно зарегистрировано"
	};
	state1Map[ "s-1" ] = {
		img : "w" ,
		descr : "ожидает выполнения другой экспертизы"
	};
	state1Map[ "s1" ] = {
		img : "r" ,
		descr : "готово к выдаче"
	};
	state1Map[ "s2" ] = {
		img : "f" ,
		descr : "выдано"
	};
	
	function mkCell( r , cn , txt , t , scn , a , ti ) {
		if ( typeof ti === "undefined" ) {
			ti = "" ;
		}
		
		if ( typeof a === "undefined" ) {
			a = false ;
		}
		
		if ( typeof scn === "undefined" ) {
			scn = "" ;
		}
		
		if ( typeof t === "undefined" ) {
			t = false ;
		}
		
		if ( typeof txt === "undefined" ) {
			txt = "" ;
		}
		
		if ( typeof cn === "undefined" ) {
			cn = "" ;
		}

		if ( typeof r === "undefined" ) {
			return null ;
		}
		
		var c = r.insertCell( -1 );
		c.className = cn ;
		
		if ( txt != "" ) {
			txt = txt.replace( "\r\n" , "\n" );
			txt = txt.replace( "\n\n" , "\n" );
			txt = txt.split( "\n" );
			for( var i = 0 ; i < txt.length ; i++ ) {
				if ( i > 0 ) {
					c.appendChild( document.createElement( "br" ) );
				}
				c.appendChild( document.createTextNode( txt[ i ] ) );
			}
		}
		
		if ( t !== false ) {
			var tmp = document.createElement( t );
			tmp.title = ti ;
			tmp.className = scn ;
			if ( a !== false ) {
				if ( typeof a === "function" ) {
					tmp.onclick = a ;
				} else {
					tmp.href = a ;
				}
			}
			c.appendChild( tmp );
		}
		
		return c ;
	}
	
	function mkBtn( parent , id , className , href , onclick , title , tag ) {
		if ( !tag ) {
			tag = 'a' ;
		}
		const tmp = document.createElement( tag );
		if ( id ) {
			tmp.id = id ;
		}
		if ( title ) {
			tmp.title = title ;
		}
		tmp.className = className ;
		if ( href ) {
			if ( typeof href === 'function' ) {
				tmp.href = href();
			} else {
				tmp.href = href ;
			}
		}
		if ( onclick ) {
			tmp.onclick = onclick ;
		}
		if ( parent ) {
			parent.appendChild( tmp );
		}
		return tmp ;
	}

	function mkBtnHREF( parent , id , className , href , title , tag ) {
		return mkBtn( parent , id , className , href , null , title , tag );
	}
	function mkBtnClick( parent , id , className , onclick , title , tag ) {
		return mkBtn( parent , id , className , null , onclick , title , tag );
	}
	
	function mkTextEl( parent , text , element ) {
		if ( !element ) {
			element = 'span' ;
		}
		const tmp = document.createElement( element );
		tmp.appendChild( document.createTextNode( text ) );
		if ( parent ) {
			parent.appendChild( tmp );
		}
		return tmp ;
	}
	
	function mkL1Row( r , rd , opt ) {
		const defOpt = {
			showSubLVLBtn : 1 ,
			showActionBtn : 1 ,
			showStateIcon : 1 ,
			showGroupLength : 1
		};
		if ( isUndefined( opt ) ) {
			opt = {};
		}
		
		opt = Object.assign( {} , defOpt , opt );
		
		const r1id = rd.getAttribute( 'id' );
		const l2c = parseInt( rd.getAttribute( 'l2c' ) , 10 );
		const inWork = rd.getAttribute( 'inWork' ) === '1' ;
		const l1gid = rd.getAttribute( 'gid' );
		const l1gc = parseInt( rd.getAttribute( 'gc' ) , 10 );
		const state1 = rd.getAttribute( 'state' );
		
		const mkCells = function( r , ci ) {
			const res = {};
			for( const i in ci ) {
				const c = r.insertCell( -1 );
				c.className = i ;
				res[ i ] = c ;
				if ( ci[ i ] ) {
					const va = ( ci[ i ] instanceof Array ) ? ci[ i ] : [ ci[ i ] ];
					for( const v of va ) {
						if ( v instanceof HTMLElement ) {
							c.appendChild( v );
						} else {
							c.appendChild( document.createTextNode( v ) );
						}
					}
				}
			}
			return res ;
		};
		
		const subNodes = {};
		for( const ccn of rd.childNodes ) {
			switch ( ccn.nodeName ) {
				case 'egl' :
					const egl = [];
					for( const egn of ccn.childNodes ) {
						const tmp = document.createElement( 'div' );
						mkTextEl( tmp , getXMLNodeValue( egn ) );
						const tmp2 = mkTextEl( tmp , egn.getAttribute( 'spec' ) );
						tmp2.className = 'exp-genus' ;
						egl.push( tmp );
					}
					subNodes.egl = egl ;
					break ;
					
				case 'fl' :
					const fl = [];
					for( const fdn of ccn.childNodes ) {
						const fp = [];
						for( const fdcn of fdn.childNodes ) {
							fp[ fdcn.nodeName ] = getXMLNodeValue( fdcn );
						}
						const lnk = mkBtnHREF( null , null , 'docs-lnk' + fdn.getAttribute( 'style' ) , '/documents.php?download=' + fdn.getAttribute( 'id' ) , fp.descr );
						lnk.target = '_blank' ;
						lnk.appendChild( document.createTextNode( fp.name ) );
						fl.push( lnk );
					}
					subNodes.fl = fl ;
					break ;
					
				case 'marks' :
					// TODO : marks
					break ;
				
				default :
					subNodes[ ccn.nodeName ] = getXMLNodeValue( ccn );
					break ;
			}
		}
		
		r.id = 'lvl1cRow' + r1id ;
		r.className = 'mig' ;
		r.vAlign = 'middle' ;

		const cells = mkCells( r , {
			f0M : null ,
			f01 : null ,
			f02 : null ,
			f031 : null ,
			f04 : null ,
			f05 : null ,
			f1  : null ,
			f2  : rd.getAttribute( 'date' ) ,
			f3  : subNodes.ay + ', ' + subNodes.at + ', ' + subNodes.ed3 ,
			f4  : subNodes.ed4 ,
			f5  : subNodes.egl ,
			f6  : subNodes.ed6 ,
			f7  : [].concat( subNodes.fl , subNodes.ed7 ) ,
			f8  : subNodes.ed8 ,
			f9  : subNodes.ed9 ,
		} );
	
		if ( l2c > 0 && opt.showSubLVLBtn ) {
			mkBtnClick( cells.f01 , 'tcimg' + r1id , 'l1-c-e' , tc2.bind( null , r1id ) , 'Развернуть' , 'div' );
		}
		
		if ( opt.showActionBtn ) {
			const c = cells.f02 ;
			if ( userRights.l1edit ) {
				mkBtnHREF( c , null , 'l1-a-e' , 'level1card.php?edit=' + r1id , 'Редактировать карточку' );
			}
			if ( userRights.l1add ) {
				mkBtnHREF( c , null , 'l1-a-a' , 'level1card.php?add&assign=' + r1id , 'Создать карточку 1 уровня и связать с этой' );
			}
			if ( userRights.l2add ) {
				mkBtnHREF( c , null , 'l1-a-a2' , 'level2card.php?add=' + r1id , 'Добавить карточку 2 уровня' );
			}
			if ( userRights.mayPrintAddressLabel ) {
				const tmp = mkBtnClick( c , null , 'l1-a-l l1-at' , function( x ){
					return function( event ) {
						showLetterDlg( event , x );
					};
				}( r1id ) , 'Этикетка адресная' );
				mkTextEl( tmp , 'Э' );
			}
			
			const tmp2 = mkBtnClick( c , null , 'l1-a-l l1-at' , function( x ){
				return function( event ) {
					showAddressesFillDlg( event , x );
				};
			}( r1id ) , 'Указать адреса' );
			mkTextEl( tmp2 , 'У' );
			
			if ( l2c > 0 && !inWork ) {
				const tmp = mkBtnHREF( c , null , 'l1-a-c l1-at' , 'main.cover.php?id=' + r1id , 'Наблюдательное производство' );
				tmp.target = '_blank' ;
			}
			
			if ( userRights.mayEnvForPayment ) {
				const tmp = mkBtnHREF( c , null , 'l1-a-l l1-at' , 'payment-details.php?id=' + r1id , 'Конверт для оплаты' );
				mkTextEl( tmp , 'О' );
			}
			
			if ( userRights.mayOrders ) {
				let tmp ;
				tmp = mkBtnHREF( c , null , 'l1-a-c l1-at' , 'order.php?id=' + r1id , 'Поручение' );
				mkTextEl( tmp , 'п' );
				
				tmp = mkBtnHREF( c , null , 'l1-a-c l1-at' , 'order-2-dmtx.php?id=' + r1id , 'Поручение новое' );
				mkTextEl( tmp , 'N' );
				
				tmp = mkBtnHREF( c , null , 'l1-a-c l1-at' , 'order-2-side-2.php?id=' + r1id , 'Выдача' );
				mkTextEl( tmp , 'S' );
				
				tmp = mkBtnHREF( c , null , 'l1-a-c l1-at' , 'order-3.php?id=' + r1id , 'Поручение' );
				mkTextEl( tmp , '3' );
				
				tmp = mkBtnHREF( c , null , 'l1-a-c l1-at' , 'order-4-dmtx.php?id=' + r1id , 'Поручение' );
				mkTextEl( tmp , '4' );
				
				tmp = mkBtnHREF( c , null , 'l1-a-c l1-at' , 'order-5-dmtx.php?id=' + r1id , 'Поручение' );
				mkTextEl( tmp , '5' );
			}
		}
		
		if ( opt.showStateIcon ) {
			const tmp = document.createElement( 'div' );
			tmp.title = ( l2c > 0 ? ( inWork ? 'В работе' : 'Завершено' ) : 'Ожидает' );
			tmp.className = 'l3-s-' + ( l2c > 0 ? ( inWork ? 'r' : 'g' ) : 'm1' ) + ( l1gc > 1 ? ' l1-gc' : '' );
			
			if ( l1gc > 0 && opt.showGroupLength ) {
				const tmp2 = mkTextEl( tmp , '' + l1gc );
				tmp2.onclick = showGroup.bind( null , l1gid , r1id );
			}
			cells.f031.appendChild( tmp );
		}
		
		if ( state1Map[ 's' + state1 ] ) {
			const tmp = document.createElement( 'div' );
			tmp.title = state1Map[ 's' + state1 ].descr ;
			tmp.className = 'l1-s-' + state1Map[ 's' + state1 ].img ;
			cells.f04.appendChild( tmp );
		}
	
		const tmpF1 = mkTextEl( cells.f1 , rd.getAttribute( 'matNumberFull' ) , 'a' );
		tmpF1.id = 'row' + r1id ;
		
		return r ;
	}
	
	function mkL2L3Rows( t , rd , dd ) {
		
		var expNumber = rd.getAttribute( "full-num" );
		var expType = dd.getAttribute( "type" );
		var r2id = rd.getAttribute( "l2id" );
		var r3id = rd.getAttribute( "eid" );
		var ordered = rd.getAttribute( "ordered" );
		var dep = rd.getAttribute( "dep" );
		var date = rd.getAttribute( "date" );
		var cat = rd.getAttribute( "cat" );
		var mayDelete = rd.getAttribute( "del" ) == "1" ? true : false ;
		var state = rd.getAttribute( "state" );
		var spec = rd.getAttribute( "spec" );
		var accTime = rd.getAttribute( "accTime" );
		var expFinished = state == "1" ;
		var expWOExecute = state == "2" ;
		var finDate = rd.getAttribute( "finDate" );
		
		var subNodes = [];
		for( var i = 0 ; i < rd.childNodes.length ; i++ ) {
			var ccn = rd.childNodes[ i ];
			switch ( ccn.nodeName ) {
				case 'comments' :
					var cw = document.createElement( 'div' );
					cw.className = 'uc-area' ;
					subNodes[ ccn.nodeName ] = cw ;
					for( var j = 0 ; j < ccn.childNodes.length ; j++ ) {
						var cce = ccn.childNodes[ j ];
						var ccw = document.createElement( 'div' );
						ccw.className = 'uc-comment' ;
						var cca = document.createElement( 'span' );
						cca.className = 'uc-author' ;
						cca.appendChild( document.createTextNode( cce.getAttribute( 'author' ) ) );
						ccw.appendChild( cca );
						var cct = document.createElement( 'span' );
						cct.className = 'uc-text' ;
						cct.appendChild( document.createTextNode( getXMLNodeValue( cce ) ) );
						ccw.appendChild( cct );
						cw.appendChild( ccw );
					}
					break ;
				default :
					subNodes[ ccn.nodeName ] = getXMLNodeValue( ccn );
					break ;
			}
		}

		var payments = JSON.parse( subNodes[ "payment" ] );
		payments = payments.join( "," );
		
		var r2 = t.insertRow( -1 );
		r2.className = "miTreeItem1" ;
		if ( mayDelete ) {
			var c = mkCell( r2 , "mitif00" , "" , "div" , "l2-a-d" , function( x ) { return function() { delete_req( x ); }; }( r2id ) , "Удалить карточки 2 и 3 уровней!!! Осторожно!!!" );
			c.rowSpan = 2 ;
		}
		
		if ( userRights.l2edit ) {
			var c = mkCell( r2 , "mitif01" , "" , "a" , "l2-a-e" , "level2card.php?edit=" + r2id , "Редактировать карточку" );
		} else {
			var c = mkCell( r2 , "mitif01" );
		}
		
		var c = mkCell( r2 , "mitif02" , "" , "div" , "l3-s-" + ( expFinished || expWOExecute ? "g" : "r" ) , false , expFinished || expWOExecute ? "Завершено" : "В производстве" );
		var c = mkCell( r2 , "mitif1" , expNumber + "\nкат.: " + cat );
		var c = mkCell( r2 , "mitif2" , date );
		var c = mkCell( r2 , "mitif3" , subNodes.materials );
		var c = mkCell( r2 , "mitif4" , subNodes.ed6 );
		var c = mkCell( r2 , "mitif6" , subNodes.ed7 );
		var c = mkCell( r2 , "mitif7" , subNodes.ed8 );
		var c = mkCell( r2 , "mitif8" , subNodes.ed9 );
		var c = mkCell( r2 , "mitif9" , spec + "\n" + subNodes.ed10 );
		var c = mkCell( r2 , "mitif10" , finDate );
		var c = mkCell( r2 , "mitif11" , subNodes.ed12 );		
			
		var r3 = t.insertRow( -1 );
		r3.className = "lvl3card" ;
		
		if ( ordered == 'none'  ) {
			if ( userRights.l3order ) {
				//debugger ;
				var orderFunc = function( id , norm , price ) {
					return function() {
						var v = '' ;
						if ( $.dbConfig[ 'matincoming.order.input.acc-time' ] == 1 ) {
							v = norm ;
							while( true ) {
								v = prompt( 'Введите учетное время' , v );
								if ( v == null ) {
									break ;
								} else {
									if ( v.match( /^\d+$/ ) ) {
										break ;
									}
								}
							}
							if ( v == null ) {
								return ;
							}

							v = '&acctime=' + parseInt( v.replace( /^\s*(\d+)\s*$/ , '$1' ) , 10 );
						}

						var p = '' ;
						if ( $.dbConfig[ 'matincoming.order.input.price' ] == 1 ) {
							p = price ;
							while( true ) {
								p = prompt( 'Введите стоимость' , p );
								if ( p == null ) {
									break ;
								} else {
									if ( p.match( /^\s*\d+(?:[\.,-]\d{2})?\s*$/ ) ) {
										break ;
									}
								}
							}
							if ( p == null ) {
								return ;
							}

							p = '&price=' + p.replace( /^\s*(\d+)[,-](\d{2})\s*$/ , '$1.$2' );
						}

						window.location = 'processor.php?expertizeorder=' + id + v + p ;
					};
				} ( r3id , accTime , subNodes[ 'price-raw' ] );
				var c = mkCell( r3 , 'lvl3c_order' , '' , 'a' , 'l3-a-o' , orderFunc , 'Поручить производство экспертизы эксперту' );
				var detailsFunc = function( id ) {
					return function() {
						var statPanel = document.getElementById( 'stat-panel' );
						var spsa = document.getElementById( 'stat-panel.spec-area' );
						spsa.innerHTML = '' ;
						var doc = sendXML( '<get-spec-stat rid="' + id + '"/>' , false , 'main.php' );
						var sr = document.createElement( 'div' );
						sr.className = 'spec-row' ;
						var specNum = doc.getAttribute( 'spec' );
						sr.appendChild( document.createTextNode( specNum ) );
						spsa.appendChild( sr );
						for( var i = 0 ; i < doc.childNodes.length ; i++ ) {
							var csr = doc.childNodes[ i ];
							var uis = csr.getAttribute( 'uis' );
							var wName = getXMLNodeValue( csr );
							var wID = csr.getAttribute( 'wid' );
							var vs = csr.getAttribute( 'vs' );
							var sp = csr.getAttribute( 'sp' );
							var vss = csr.getAttribute( 'vss' );
							var sps = csr.getAttribute( 'sps' );

							var domStatRow = document.createElement( 'div' );
							domStatRow.className = 'stat-row' + ( uis == 1 ? '' : ' no-stat' );
								var domStatRowName = document.createElement( 'div' );
								domStatRowName.className = 'stat-row-name' ;
								domStatRowName.appendChild( document.createTextNode( wName ) );
									var statLnk = document.createElement( 'a' );
									statLnk.className = 'stat-row-name-details' ;
									statLnk.target = '_blank' ;
									statLnk.href = '/maindb/expertize.report.in-the-pipeline.php?i_worker=' + wID ;
									statLnk.appendChild( document.createTextNode( '?' ) );
								domStatRowName.appendChild( statLnk );
							domStatRow.appendChild( domStatRowName );

								var domStatRowData = document.createElement( 'div' );
								domStatRowData.className = 'stat-row-data' ;
									var domStatRowDataBar = document.createElement( 'div' );
									domStatRowDataBar.className = 'stat-row-data-bar' ;
									domStatRowDataBar.style.width = sp.replace( /,/ , '.' ) + '%' ;
										var domStatRowDataLabel = document.createElement( 'div' );
										domStatRowDataLabel.className = 'stat-row-data-label left' ;
										domStatRowDataLabel.appendChild( document.createTextNode( vss ) );
									domStatRowDataBar.appendChild( domStatRowDataLabel );
										domStatRowDataLabel = document.createElement( 'div' );
										domStatRowDataLabel.className = 'stat-row-data-label right' ;
										domStatRowDataLabel.appendChild( document.createTextNode( vs ) );
									domStatRowDataBar.appendChild( domStatRowDataLabel );
										var domStatRowDataBarSub = document.createElement( 'div' );
										domStatRowDataBarSub.className = 'stat-row-data-bar-sub' ;
										domStatRowDataBarSub.style.width = sps.replace( /,/ , '.' ) + '%' ;
									domStatRowDataBar.appendChild( domStatRowDataBarSub );
								domStatRowData.appendChild( domStatRowDataBar );
							domStatRow.appendChild( domStatRowData );

							spsa.appendChild( domStatRow );
						}
						statPanel.style.display = '' ;
					};
				} ( r3id );
				var c = mkCell( r3 , "lvl3c_spec_detail" , "" , "a" , "l3-a-d" , detailsFunc , "Подсказка о загруженности" );
			} else {
				var c = mkCell( r3 , "lvl3c_edit" );
				var c = mkCell( r3 , "lvl3c_state" , "" , "div" , "l3-s-" + ( expFinished ? "g" : ( expWOExecute ? "y" : "r" ) ) , false , expFinished ? "Завершено" : ( expWOExecute ? "Без производства" : "В производстве" ) );
			}
		} else {
			if ( userRights.l3edit ) {
				var c = mkCell( r3 , "lvl3c_edit" , "" , "a" , "l3-a-e" , "expertize.php?edit=" + r3id , "Редактировать карточку" );			
			} else {
				var c = mkCell( r3 , "lvl3c_edit" );
			}

			var c = mkCell( r3 , "lvl3c_state" , "" , "div" , "l3-s-" + ( expFinished ? "g" : ( expWOExecute ? "y" : "r" ) ) , false , expFinished ? "Завершено" : ( expWOExecute ? "Без производства" : "В производстве" ) );
		}
		
		var c = mkCell( r3 , "lvl3c_name" , subNodes.exp );
		var c = mkCell( r3 , "lvl3c_price" , subNodes.price + " руб." );
		var c = mkCell( r3 , "lvl3c_" + ( expFinished || expWOExecute ? "" : "un" ) + "finished" , ( expFinished || expWOExecute ? "окончено: " + finDate : "в производстве" ) );
				
		var c = mkCell( r3 , "lvl3c_spec" , spec );
		
		var c = mkCell( r3 , "lvl3c_payments" );
		if ( payments != "" ) {
			var tmp = document.createElement( "a" );
			tmp.href = UserOptions.paymentsAddress + "?idlist=" + payments ;
			tmp.appendChild( document.createTextNode( "Оплата" ) );
			tmp.target = "_blank" ;
			c.appendChild( tmp );			
		}

		var c = mkCell( r3 , "lvl3c_comments" );
		if ( subNodes.comments ) {
			c.appendChild( subNodes.comments );
		}
		
		c.colSpan = 6 ;
	}
	
	function addL1Buttons() {
		
	}
	
	function doBodyScroll() {
		if ( hiddenRowsIDList.length == 0 ) {
			return ;
		}
		
		var scrolled = window.pageYOffset || document.documentElement.scrollTop ;
		var vh = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight ;
		var sh = document.documentElement.scrollHeight - vh ;
		
		var vec = 50 ;
		
		if ( scrolled + 500 >= sh ) {
			var tel = hiddenRowsIDList.slice( 0 , vec );
			hiddenRowsIDList.splice( 0 , vec );
			for( var i = 0 ; i < tel.length ; i++ ) {
				var te = document.getElementById( "lvl1cRow" + tel[ i ] );
				te.style.display = "" ;
			}
			visibleRowsIDList = visibleRowsIDList.concat( tel );
		}
	}

	function hideStatPanel() {
		var statPanel = document.getElementById( 'stat-panel' );
		statPanel.style.display = 'none' ;
	}
	
	var MainTable = null ;
	$.windowOnLoad.push( function() {
		if ( typeof visibleRowsIDList === "undefined" || visibleRowsIDList == "" ) {
			visibleRowsIDList = [];
		} else {
			visibleRowsIDList = visibleRowsIDList.split( "," );
		}
		
		if ( typeof hiddenRowsIDList === "undefined" || hiddenRowsIDList == "" ) {
			hiddenRowsIDList = [];
		} else {
			hiddenRowsIDList = hiddenRowsIDList.split( "," );
		}
		
		if ( typeof expandRowsIDList === "undefined" || expandRowsIDList == "" ) {
			expandRowsIDList = [];
		} else {
			expandRowsIDList = expandRowsIDList.split( "," );
		}
		
		for( var i = 0 ; i < Math.min( 1 , expandRowsIDList.length ) ; i++ ) {
			tc2( expandRowsIDList[ i ] );
		}

		
		MainTable = document.getElementById( "MainTable" );
		
		window.onscroll = doBodyScroll ;
	} );
