
	$.windowOnLoad.push( function() {
		setInterval( checkMessages , 1000 );
	} );
	
	function sleep( ms ) {
		ms+= ( new Date() ).getTime();
		while ( ( new Date() ).getTime() < ms ) {
		}
	}
	
	function checkMessages() {
		sendXML( "<get-messages />" , true , "" , "" , false , checkMessagesCB );
	}
	
	function checkMessagesCB( req ) {
		var doc = req.responseXML.documentElement ;
		var dc = doc.childNodes ;
		for( var i = 0 ; i < dc.length ; i++ ) {
			var cdc = dc[ i ];
			switch( cdc.getAttribute( "type" ) ) {
				case "1" :
					var msg = getXMLNodeValue( cdc );
					msg = msg.split( ";" );
					var matId = msg[ 0 ];
					var expType = msg[ 1 ];
					var matDate = new Date( parseInt( msg[ 2 ] ) * 1000 , 10 );
					
					var tab = document.getElementById( "tab-" + parseInt( cdc.getAttribute( "cell" ) , 10 ) );
					var ci = tab.insertRow( 0 ).insertCell( -1 );
					ci.className = "mat-item" ;
					ci.id = "mat-" + matId ;
					var matIDS = getCharIDStructure( matId );
					setText( ci , matIDS.n + " / * - " + expType );
					if ( matDate.getFullYear()  < ( new Date() ).getFullYear() ) {
						ci.style.color = "#ff0000" ;
					}
					startSignaling( parseInt( cdc.getAttribute( "cell" ) , 10 ) , matId );
					break ;
				case "2" :
					switch( cdc.getAttribute( "ct" ) ) {
						case "1" :
							startSignaling( parseInt( cdc.getAttribute( "cell" ) , 10 ) , getXMLNodeValue( cdc ) );
							break ;
						
						case "2" :
							alert( "В архиве: № " + cdc.getAttribute( "cell" ) + "\r\n" + cdc.getAttribute( "workers" ) );
							break ;
					}
					break ;
			}
		}		
	}
	
	function doSearch() {
		var ene = document.getElementById( "search-exp-number" );
		ene = parseInt( ( "" + ene.value ).trim() , 10 );
		var ede = document.getElementById( "search-exp-date" );
		ede = parseInt( ( "" + ede.value ).trim() , 10 );
		var doc = sendXML( "<search n=\"" + ene + "\" d=\"" + ede + "\" />" );
		switch ( doc.getAttribute( "status" ) ) {
			case "-3" :
				alert( "Сведения о месте хранения удалены!" );
				break ;
			case "-2" :
				alert( "Место хранения не задано." );
				break ;
			case "-1" :
				alert( "Экпертизы с указанным номером не существует!" );
				break ;
			case "0" :
				break ;
		}
	}

	function doAdd() {
		var ene = document.getElementById( "search-exp-number" );
		ene = parseInt( ( "" + ene.value ).trim() , 10 );
		var ede = document.getElementById( "search-exp-date" );
		ede = parseInt( ( "" + ede.value ).trim() , 10 );
		var doc = sendXML( "<add-exp n=\"" + ene + "\" d=\"" + ede + "\" />" );
		switch ( doc.getAttribute( "status" ) ) {
			case "-4" :
				alert( "Уже добавлялась ранее!" );
				break ;
			case "-3" :
				alert( "Нет места!" );
				break ;
			case "-2" :
				alert( "Экспертиза не распределена" );
				break ;
			case "-1" :
				alert( "Экпертизы с указанным номером не существует!" );
				break ;
			case "0" :
				/*var objDoc = new ActiveXObject( "bpac.Document" );
				var lf = getCookie( "labelFormat" );
				if ( objDoc.Open( "c:\\labeling\\barcodeLabel-" + lf + ".lbx" ) != false ) {
					objDoc.GetObject( "bc1" ).Text = doc.getAttribute( "id" );

					objDoc.StartPrint( "" , 0 );
					objDoc.PrintOut( 1 , 0 );
					objDoc.Close();
					objDoc.EndPrint();
				}*/
				break ;
		}
	}
	
	function doAddCell() {
		var cx = document.getElementById( "cell-x" );
		cx = parseInt( ( "" + cx.value ).trim() , 10 );
		var cy = document.getElementById( "cell-y" );
		cy = parseInt( ( "" + cy.value ).trim() , 10 );
		
		var wid = document.getElementById( "worker-id" );
		wid = parseInt( ( "" + wid.value ).trim() , 10 );
		
		var cl = document.getElementById( "cell-label" );
		cl = cl.value ;
		
		var m = cl.match( /["'<>&]/ );
		if ( m != null ) {
			alert( "Поле метки содержит недопустимые знаки : \" ' < > &" );
			return ;
		}
		
		var doc = sendXML( "<add-cell x=\"" + cx + "\" y=\"" + cy + "\" wid=\"" + wid + "\" l=\"" + cl + "\" />" );
		location.reload( true );
	}

	var signalingCell = null ;
	
	function startSignaling( id , row ) {
		if ( signalingCell != null ) {
			stopSignaling();
		}
		
		var se = document.getElementById( "signal-" + id );
		var item = row !== null ? document.getElementById( "mat-" + row ) : null ;
		var pe = document.getElementById( "pos-" + id );
		
		if ( item !== null ) {
			item.scrollIntoView();
		
			var pi = item.parentNode.parentNode.rows ;
			
			var pt = 0 ;
			var pb = 0 ;
			var tr = pi.length ;
			var posMsg = "" ;
			
			for( var i = 0 ; i < tr ; i++ ) {
				var tc = pi[ i ].cells[ 0 ];
				if ( tc.id == "mat-" + row ) {
					pt = i + 1 ;
					pb = tr - i ;
					if ( pt < pb ) {
						posMsg = pt + " В" ;
					} else {
						posMsg = pb + " Н" ;
					}
					break ;
				}
			}
			
			setText( pe , posMsg );
			
			pe.style.display = "block" ;
			
		}
			
		signalingCell = {
			id : id ,
			tid : setInterval( doSignal , 125 ) ,
			el : se ,
			it : item ,
			st : ( new Date() ).getTime() ,
			pe : pe
		};
	}

	function startSignaling2( x , y ) {
		if ( signalingCell != null ) {
			stopSignaling();
		}
		
		var se = document.getElementById( "signal2-" + x + "-" + y );
		
		signalingCell = {
			id : null ,
			tid : setInterval( doSignal , 125 ) ,
			el : se ,
			it : null ,
			st : ( new Date() ).getTime() ,
			pe : null
		};
	}

	function stopSignaling() {
		if ( signalingCell == null ) {
			return ;
		}
		
		clearInterval( signalingCell.tid );
		signalingCell.el.style.display = "" ;
		
		if ( signalingCell.it !== null ) {
			signalingCell.it.style.backgroundColor = "" ;
		}
		
		if ( signalingCell.pe !== null ) {
			signalingCell.pe.style.display = "" ;
		}
	}
	
	function doSignal() {
		if ( signalingCell == null ) {
			return ;
		}
		
		if ( signalingCell.it !== null ) {
			var es = signalingCell.el.style ;
			var it = signalingCell.it.style ;
			if ( es.display == "" ) {
				es.display = "block" ;
				it.backgroundColor = "" ;
			} else {
				es.display = "" ;
				it.backgroundColor = "#ff0000" ;
			}
		} else {
			var es = signalingCell.el.style ;
			if ( es.display == "" ) {
				es.display = "block" ;
			} else {
				es.display = "" ;
			}
		}
			
		var et = ( new Date() ).getTime();
		//console.log( et - signalingCell.st );
		if ( et - signalingCell.st >= 7500 ) {
			stopSignaling();
		}
	}
	
	function highlightCell( x , y , s ) {
		if ( signalingCell != null ) {
			stopSignaling();
		}
		
		var se = document.getElementById( "signal2-" + x + "-" + y );
		var es = se.style ;
		if ( s == "on" ) {
			es.display = "block" ;
		} else {
			es.display = "" ;
		}
	}
	
	function doCut( cid , fid , name ) {
		var coord = prompt( "Укажите координаты X и Y (разделенные пробелом) новой ячейки:" );
		if ( coord == null ) {
			return ;
		}
		
		var mr = coord.match( /^\s*[0-8]\s+[0-6]\s*$/ );
		
		if ( mr == null ) {
			alert( "Координаты указаны неправильно!" );
			return ;
		}
		
		mr = mr[ 0 ].trim().split( " " );
		alert( "Сейчас будет подсвечена указанная вами ячейка ( " + mr[ 0 ] + " , " + mr[ 1 ] + " )" );
		highlightCell( mr[ 0 ] , mr[ 1 ] , "on" );
		if ( confirm( "Переместить экспертизы " + name + " в эту ячейку?" ) ) {
			var doc = sendXML( "<wcut cid=\"" + cid + "\" fid=\"" + fid + "\" nx=\"" + mr[ 0 ] + "\" ny=\"" + mr[ 1 ] + "\" />" );
			switch ( doc.getAttribute( "status" ) ) {
				case "-1" :
					alert( "Такого эксперта нет!" );
					break ;
				case "-2" :
					alert( "Нет экспертиз для перемещения!" );
					break ;
				case "0" :
					highlightCell( mr[ 0 ] , mr[ 1 ] , "off" );
					alert( "Перемещено!" );
					location.reload( true );
					break ;
			}
			
		}
		
		//alert( cid + " : " + fid );
	}
	
	function doOffload( id ) {
		var res = prompt( "Чтобы продолжить напечатайте слово \"В Ы Г Р У З И Т Ь\" без пробелов" , "" );
		if ( res != "ВЫГРУЗИТЬ" ) {
			alert( "Выгрузка не подтверждена" );
			return ;
		}
		
		var doc = sendXML( "<offload id=\"" + id + "\" />" );
		location.reload( true );
	}
	
	function doDeleteFromCell( id ) {
		var res = prompt( "Чтобы продолжить напечатайте слово \"У Д А Л И Т Ь ( " + id + " )\" без скобок и пробелов" , "" );
		if ( res != "УДАЛИТЬ" + id ) {
			alert( "Удаление не подтверждено" );
			return ;
		}
		
		var doc = sendXML( "<delete-exp id=\"" + id + "\" />" );
		location.reload( true );
	}
	