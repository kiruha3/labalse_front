/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	/**
	 * @var incDate
	 */

	let blinkTimer = null ;
	let blinkFase = 0 ;
	
	const EXPERTIZE_AJAX_ADDR = 'expertize.ajax.php' ;
	
	function blinkElement( f ) {
		blinkTimer = setInterval( function( x ){
			blinkFase = 0 ;
			return function() {
				if ( isArray( x ) ) {
					for( var i = 0 ; i < x.length ; i++ ) {
						if ( blinkFase < 20 ) {
							if ( blinkFase % 2 === 0 ) {
								x[ i ].style.backgroundColor = "" ;
							} else {
								x[ i ].style.backgroundColor = "#ff0000" ;
							}
						} else {
							x[ i ].style.backgroundColor = "" ;
							clearInterval( blinkTimer );
							blinkTimer = null ;
						}
					}
					
					blinkFase++ ;
				} else {
					if ( blinkFase++ < 20 ) {
						if ( blinkFase % 2 === 0 ) {
							x.style.backgroundColor = "" ;
						} else {
							x.style.backgroundColor = "#ff0000" ;
						}
					} else {
						x.style.backgroundColor = "" ;
						clearInterval( blinkTimer );
						blinkTimer = null ;
					}
				}
			};
		}( f ) , 100 );
	}
	
	function doCheckForm() {
		
		const iStateCBList = document.getElementsByName( 'i_state' );
		let iStateSelected = 0 ;
		for( const stateCB of iStateCBList  ) {
			if ( stateCB.checked ) {
				iStateSelected = parseInt( stateCB.value , 10 );
			}
		}
		
		const form = document.getElementById( 'PostForm' );
		
		if ( typeof expClosedNoCorrect == 'undefined' || !expClosedNoCorrect ) {
			const ifd = document.getElementById( 'i_fin_date' );
			const finDateCheckResult = finDateCheck();
			if ( !finDateCheckResult.ok ) {
				ifd.focus();
				blinkElement( [ ifd.parentNode.parentNode , ifd ] );
				return ;
			}
			
			if ( iStateSelected === 1 ) {
				if ( finDateCheckResult.wDaysGT30 ) {
					const ir = document.getElementsByName( 'i_reason_1[]' );
					let tc = false ;
					const ira = [];
					for( let i = 0 ; i < ir.length ; i++ ) {
						tc = tc || ir[ i ].checked ;
						ira[ 'v_' + ir[ i ].value ] = ir[ i ];
					}
					
					if ( !tc ) {
						alert( 'Ошибка : отсутствует причина превышения 30 дневного срока' );
						ira[ 'v_1' ].scrollIntoView();
						blinkElement( ira[ 'v_1' ].parentNode.parentNode );
						return ;
					}
					
					if ( ira[ 'v_4' ].checked ) {
						const ir4c = document.getElementById( 'i_reason_1_comment' );
						if ( ir4c.value.length < 10 ) {
							alert( 'Ошибка : слишком короткий комментарий (мин. 10 символов)' );
							ir4c.focus();
							blinkElement( ir4c );
							return ;
						}
					}
				}
				
				const ic = [];
				ic[ 'total' ] = document.getElementById( 'i_conclusion' );
				
				const icv = [];
				icv[ 'total' ] = parseInt( ic[ 'total' ].value , 10 ) || 0 ;
				
				const ica = strexp( '_{1,2{,_{1,2,3}},3}' );
				for( let i = 0 ; i < ica.length ; i++ ) {
					ic[ ica[ i ] ] = document.getElementById( 'i_conclusion' + ica[ i ] );
					icv[ ica[ i ] ] = parseInt( ic[ ica[ i ] ].value , 10 ) || 0 ;
				}
				
				if ( icv[ 'total' ] !== icv[ '_1' ] + icv[ '_2' ] + icv[ '_3' ] ) {
					alert( 'Ошибка : количество поставленных вопросов не совпадает с количеством выводов' );
					ic[ 'total' ].scrollIntoView();
					blinkElement( [
						ic[ 'total' ].parentNode.parentNode ,
						ic[ '_1' ].parentNode.parentNode ,
						ic[ '_2' ].parentNode.parentNode ,
						ic[ '_3' ].parentNode.parentNode
					] );
					return ;
				}
				
				if ( icv[ 'total' ] === 0 ) {
					alert( 'Ошибка : количество поставленных вопросов должно быть больше 0' );
					ic[ 'total' ].scrollIntoView();
					blinkElement( [
						ic[ 'total' ].parentNode.parentNode ,
						ic[ '_1' ].parentNode.parentNode ,
						ic[ '_2' ].parentNode.parentNode ,
						ic[ '_3' ].parentNode.parentNode
					] );
					return ;
				}
				
				if ( icv[ '_2_1' ] + icv[ '_2_2' ] + icv[ '_2_3' ] !== icv[ '_2' ] ) {
					alert( 'Ошибка : число причин не соответствует числу нерешенных вопросов' );
					ic[ '_2' ].scrollIntoView();
					blinkElement( [
						ic[ '_2' ].parentNode.parentNode ,
						ic[ '_2_1' ].parentNode.parentNode ,
						ic[ '_2_2' ].parentNode.parentNode ,
						ic[ '_2_3' ].parentNode.parentNode
					] );
					return ;
				}
			}
			
			if ( iStateSelected === 2 ) {
				const ir2ns = document.getElementById( 'i_reason_2_ns' );
				if ( ir2ns && ir2ns.checked ) {
					alert( 'Не указана причина возвращения без производства!' );
					ir2ns.scrollIntoView();
					blinkElement( ir2ns.parentNode.parentNode );
					return ;
				}
				
				const ir2CBList = document.getElementsByName( 'i_reason_2' );
				let ir2CBSelected = null ;
				for( let ir2CCB of ir2CBList ) {
					if ( ir2CCB.checked ) {
						ir2CBSelected = ir2CCB ;
					}
				}
				
				if ( ir2CBSelected == null ) {
					alert( 'Не указана причина возвращения без производства!' );
					const rowReason2 = document.getElementById( 'row--reasons-2' );
					blinkElement( rowReason2 );
					return ;
				}
				
				if ( ir2CBSelected.dataset.commentRequired > 0 ) {
					const ir2c = document.getElementById( 'i_reason_2_comment' );
					if ( ir2c.value.length < ir2CBSelected.dataset.commentRequired ) {
						alert( 'Слишком короткий комментарий (мин. ' + ir2CBSelected.dataset.commentRequired + ' символов)' );
						ir2c.focus();
						blinkElement( ir2c );
						return ;
					}
				}
			}
		}
		
		if ( !isCCGZ && !isMarkNoPay && iStateSelected != 2 && iStateSelected != 0 ) {
			const ip = document.getElementById( 'i_price' );
			if ( ip ) {
				const n = ip.value.match( /^\s*(\d{1,10}(?:[,.]\d{2})?)\s*$/ );
				if ( n == null || n.length !== 2 ) {
					alert( 'Ошибка : стоимость экспертизы' );
					ip.focus();
					blinkElement( ip.parentNode.parentNode );
					return ;
				} else {
					ip.value = n[ 1 ];
				}
			}
			
			const iafiy = document.getElementById( 'i_afi_yes' );
			const iafin = document.getElementById( 'i_afi_no' );
			
			if ( !iafiy.checked && !iafin.checked ) {
				alert( 'Ошибка : не указано, прикладывалось ли заявление о выдаче исполнительного листа.' );
				iafin.scrollIntoView();
				blinkElement( iafin.parentNode.parentNode );
				return ;
			}
		}
		
		form.submit();
	}
	
	var dialogs = {	};
	var dialogsList = [ "eul" , "ml" , "pl" , "tl" ];
	var dialogsElementsNames = {
		eul : "ds,ts,de,te,el,ta" ,
		ml : "ds,ts,ml,dd2l,dd2ta,dc,dn,dt" ,
		pl : "sl,pr,pa,cmt" ,
		tl : ""
	};
	
	function finDateCheck() {
		const ifd = document.getElementById( 'i_fin_date' );
		let ifdUT ;
		if ( typeof expClosedNoCorrect != 'undefined' && expClosedNoCorrect ) {
			ifdUT = expClosedNoCorrect.date * 1000 ;
		} else {
			ifd.value = ifd.value.replaceAll( /[-,.]/g , '-' );
			ifd.value = ifd.value.replace( /^(\d{2}-\d{2}-)([^2]\d)$/ , '$120$2' );
			let n = ifd.value.match( /^\d{2}-\d{2}-\d{4}$/ );
			if ( n == null || n.length !== 1 ) {
				return { message : 'не верный формат даты' };
			}
			
			const ifdC = ifd.value.split( '-' );
			ifdUT = ( new Date( parseInt( ifdC[ 2 ] , 10 ) , parseInt( ifdC[ 1 ] , 10 ) - 1 , parseInt( ifdC[ 0 ] , 10 ) ) ).getTime();
		}
		
		if ( ifdUT < incDate * 1000 ) {
			return { message : 'раньше даты регистрации' };
		}
		
		if ( typeof expClosedNoCorrect == 'undefined' || !expClosedNoCorrect ) {
			const serverCurrentDate = getDateWOTime( $.PageGeneratedDateTime * 1000 ).getTime();
			
			if ( typeof finDateDeltaLowLimit == 'undefined' || !finDateDeltaLowLimit || ( finDateDeltaLowLimit && finDateDeltaLowLimit !== 'no-check' ) ) {
				if ( ifdUT < serverCurrentDate ) {
					if ( typeof finDateDeltaLowLimit == 'undefined' || !finDateDeltaLowLimit ) {
						finDateDeltaLowLimit = 0 ;
					}
					
					if ( serverCurrentDate - ifdUT > finDateDeltaLowLimit * 86400 * 1000 ) {
						if ( finDateDeltaLowLimit === 0 ) {
							return { message : 'установка даты в прошлом не разрешена' };
						} else {
							return { message : 'отклонение от текущей даты может быть не более ' + finDateDeltaLowLimit + ' дней' };
						}
					}
				}
			}
			
			if ( typeof finDateDeltaHighLimit == 'undefined' || !finDateDeltaHighLimit || ( finDateDeltaHighLimit && finDateDeltaHighLimit !== 'no-check' ) ) {
				if ( ifdUT > serverCurrentDate ) {
					if ( typeof finDateDeltaHighLimit == 'undefined' || !finDateDeltaHighLimit ) {
						finDateDeltaHighLimit = 0 ;
					}
					
					if ( ifdUT - serverCurrentDate > finDateDeltaHighLimit * 86400 * 1000 ) {
						if ( finDateDeltaHighLimit === 0 ) {
							return { message : 'установка даты в будущем не разрешена' };
						} else {
							return { message : 'отклонение от текущей даты может быть не более ' + finDateDeltaHighLimit + ' дней' };
						}
					}
				}
			}
		}
		const wDays = Math.ceil( ( ifdUT - ( incDate * 1000 ) ) / ( 86400 * 1000 ) ) + 1 ;
		return {
			ok : true ,
			message : wDays + ' дней с даты регистрации ' + formatDate( incDate * 1000 , '{d}-{m}-{Y}' ) ,
			wDaysGT30 : wDays > 30
		};
	}
	
	function finDateInput() {
		const finDateCheckResult = finDateCheck();
		const ifd = document.getElementById( 'i_fin_date' );
		const ifdCom = document.getElementById( 'i_fin_date_comment' );
		const ifds = ifd.style ;
		
		if ( !finDateCheckResult.ok ) {
			ifds.textDecorationStyle = 'wavy' ;
			ifds.textDecorationLine = 'underline' ;
			ifds.textDecorationColor = 'red' ;
		} else {
			ifds.textDecorationStyle = '' ;
			ifds.textDecorationLine = '' ;
			ifds.textDecorationColor = '' ;
		}
		
		if ( ifdCom ) {
			setText( ifdCom , finDateCheckResult.message );
		}
		const rfd = document.getElementById( 'row--reasons-1' );
		rfd.dataset.showFinDateReason = finDateCheckResult.wDaysGT30 ? '1' : '0' ;
	}
	
	function selectReason2() {
		const ir2c = document.getElementById( 'i_reason_2_comment' );
		const ir2ns = document.getElementById( 'i_reason_2_ns' );
		if ( ir2ns.checked ) {
			ir2c.placeholder = '' ;
			return ;
		}
		
		const ir2CBList = document.getElementsByName( 'i_reason_2' );
		let ir2CBSelected = null ;
		for( let ir2CCB of ir2CBList ) {
			if ( ir2CCB.checked ) {
				ir2CBSelected = ir2CCB ;
			}
		}
		
		if ( ir2CBSelected != null ) {
			ir2c.placeholder = ir2CBSelected.dataset.commentPlaceholder ;
		}
	}
		
	window.onload = function() {
		if ( typeof expClosedNoCorrect == 'undefined' || !expClosedNoCorrect ) {
			finDateInput();
		}
		
		for( var i = 0 ; i < dialogsList.length ; i++ ) {
			var cdlgn = dialogsList[ i ];
			var cdlg = {
				area : document.getElementById( cdlgn + "-dlg" ),
				NRR : document.getElementById( cdlgn + "-new-record-row" ),
				ER : document.getElementById( cdlgn + "-row-empty" ),
				Tab : document.getElementById( cdlgn + "-list-tab" ),
				addBtn : document.getElementById( cdlgn + "-add-btn" ),
				applyBtn : document.getElementById( cdlgn + "-apply-btn" ),
				cancelBtn : document.getElementById( cdlgn + "-cancel-btn" ),
				elements : []
			};
			
			var re = dialogsElementsNames[ cdlgn ].split( "," );
			for( var j = 0 ; j < re.length ; j++ ) {
				var tmpEl = document.getElementById( cdlgn + "-nrr-" + re[ j ] );
				if ( tmpEl !== null ) {
					cdlg.elements.push( { name : re[ j ] , el : tmpEl } );
				}
			}
			
			cdlg.NRR.parentNode.removeChild( cdlg.NRR );
			cdlg.TabBody = cdlg.Tab.tBodies[ 0 ];
			dialogs[ cdlgn ] = cdlg ;
		}
	};
	
	function getNewRowElements( dlg , t , dv ) {
		const drel = dlg.elements ;
		switch ( t ) {
			case 'array' :
				if ( dv ) {
					for( let i = 0 ; i < drel.length ; i++ ) {
						drel[ i ].el.value = drel[ i ].el.defaultValue ;
					}
				}
				return drel ;
				break ;
				
			case 'object' :
				const rel = {};
				for( let i = 0 ; i < drel.length ; i++ ) {
					const cel = drel[ i ].el ;
					rel[ drel[ i ].name ] = cel ;
					if ( dv ) {
						cel.value = cel.defaultValue ;
					}
				}
				return rel ;
				break ;
		}
	}
	
	function doAddPosition( dlgName ) {
		var dlg = dialogs[ dlgName ];
		dlg.addBtn.style.display = "none" ;
		dlg.applyBtn.style.display = "" ;
		dlg.cancelBtn.style.display = "" ;
		
		if ( dlg.ER === null ) {
		} else {
			dlg.ER.parentNode.removeChild( dlg.ER );
		}
		
		dlg.TabBody.appendChild( dlg.NRR );
		var rel = getNewRowElements( dlg , "object" , true );
		
		switch ( dlgName ) {
			case "eul" :
				rel.el.selectedIndex = 0 ;
				break ;
			case "ml" :
				rel.ml.selectedIndex = 0 ;
				dlg.normUsed = false ;
				setMLNRRData( false , false , false , false , false );
				break ;
		}
	}
	
	function checkInput( el , t , v , mt ) {
		var m = true ;
		var msg = "" ;
		switch ( t ) {
			case "i" :
				m = el.value.match( /^\s*(\d+)\s*$/ );
				m = ( m == null || m.length !== 2 );
				msg = "Неверный формат числа" ;
				break ;
			case "f" :
				m = el.value.match( /^\s*(\d+(?:[.,]\d+)?)\s*$/ );
				m = ( m == null || m.length !== 2 );
				msg = "Неверный формат числа" ;
				break ;
			case "p" :
				m = el.value.match( /^\s*(\d+(?:[.,]\d{2})?)\s*$/ );
				m = ( m == null || m.length !== 2 );
				msg = "Неверный формат числа" ;
				break ;
			case "d" :
				m = el.value.match( /^\s*([0-2]\d|3[0-1])[-.,](0\d|1[0-2])[-.,](?:20)?(\d{2})\s*$/ );
				m = ( m == null || m.length !== 4 );
				msg = "Неверный формат даты" ;
				break ;
			case "t" :
				m = el.value.match( /^\s*([0-1]\d|2[0-3])[-.,:]([0-5]\d)\s*$/ );
				m = ( m == null || m.length !== 3 );
				msg = "Неверный формат времени" ;
				break ;
			case "v" :
				if ( v instanceof RegExp ) {
					m = el.value.match( v );
					m = ( m == null );
				} else {
					m = !( el.value == v );
				}
				msg = mt ;
				break ;
			case "V" :
				if ( v instanceof RegExp ) {
					m = el.value.match( v );
					m = !( m == null );
				} else {
					m = ( el.value == v );
				}
				msg = mt ;
				break ;
		}
		
		if ( m ) {
			alert( msg );
			el.scrollIntoView();
			blinkElement( [ el ] );
			el.focus();
			return false ;
		} else {
			if ( t == "i" || t == "d" || t == "t" ) {
				el.value = el.value.trim();
			}
			return true ;
		}
	}
	
	function doAddPositionApplyEUL( eid ) {
		const dlg = dialogs.eul ;
		const rel = getNewRowElements( dlg , 'object' , false );
		const ds = rel.ds ;
		const ts = rel.ts ;
		const de = rel.de ;
		const te = rel.te ;
		const el = rel.el ;
		const ta = rel.ta ;
		
		const ca = [ [ ds , 'd' ] , [ ts , 't' ] , [ de , 'd' ] , [ te , 't' ] , [ el , 'V' , '' , 'Не указан прибор' ] ];
		let cr = true ;
		for( let cai of ca ) {
			cr = cr && checkInput.apply( null , cai );
			if ( !cr ) {
				return ;
			}
		}
		
		
		const doc = sendXML( '<add-equipment extid="' + eid + '" eqid="' + el.value + '" ds="' + ds.value + '" ts="' + ts.value + '" de="' + de.value + '" te="' + te.value + '">' + toCDATA( ta.value.trim() ) + '</add-equipment>' , false , EXPERTIZE_AJAX_ADDR );
		if ( doc.getAttribute( 'state' ) !== 'ok' ) {
			alert( doc.nodeValue );
			return ;
		}
		
		const daDS = doc.getAttribute( 'ds' );
		const daTS = doc.getAttribute( 'ts' );
		const daDE = doc.getAttribute( 'de' );
		const daTE = doc.getAttribute( 'te' );
		const daTA = getXMLNodeValue( doc );
		const rowID = parseInt( doc.getAttribute( 'row-id' ) , 10 );
		
		let c , tmp ;
		
		const nrr = document.createElement( 'tr' );
		nrr.className = 'dlg-list-row' ;
			c = nrr.insertCell( -1 );
			//<a class="dlg-row-delete" onclick="deleteRow( 'eul' , 20579 )" title="Удалить"></a>
			c.className = 'dlg-list-d eul-col-dt' ;
				tmp = document.createElement( 'a' );
				tmp.className = 'dlg-row-delete' ;
				tmp.onclick = deleteRow.bind( 'eul' , rowID );
				tmp.title = 'удалить' ;
			c.appendChild( tmp );
			
			c = nrr.insertCell( -1 );
			c.className = 'dlg-list-d eul-col-dp' ;
				tmp = document.createElement( 'span' );
				tmp.className = 'dlg-list-d-v' ;
				tmp.appendChild( document.createTextNode( daDS ) );
			c.appendChild( tmp );
				tmp = document.createElement( 'span' );
				tmp.className = 'dlg-list-t-v' ;
				tmp.appendChild( document.createTextNode( daTS ) );
			c.appendChild( tmp );
			c.appendChild( document.createElement( 'br' ) );
				tmp = document.createElement( 'span' );
				tmp.className = 'dlg-list-d-v' ;
				tmp.appendChild( document.createTextNode( daDE ) );
			c.appendChild( tmp );
				tmp = document.createElement( 'span' );
				tmp.className = 'dlg-list-t-v' ;
				tmp.appendChild( document.createTextNode( daTE ) );
			c.appendChild( tmp );
			
			c = nrr.insertCell( -1 );
			c.className = 'dlg-list-d eul-col-dn' ;
			c.appendChild( document.createTextNode( el.options[ el.selectedIndex ].text ) );
		
			c = nrr.insertCell( -1 );
			c.className = 'dlg-list-d eul-col-dc' ;
			c.appendChild( document.createTextNode( daTA ) );
		
		dlg.TabBody.appendChild( nrr );
		
		dlg.NRR.parentNode.removeChild( dlg.NRR );
		
		dlg.addBtn.style.display = '' ;
		dlg.applyBtn.style.display = 'none' ;
		dlg.cancelBtn.style.display = 'none' ;
		
		if ( dlg.ER !== null ) {
			dlg.ER = null ;
		}
	}
	
	function doAddPositionApplyML( eid ) {
		const dlg = dialogs.ml ;
		const rel = getNewRowElements( dlg , 'object' , false );
		let ds = rel.ds ;
		let ts = rel.ts ;
		const ml = rel.ml ;
		const dd2l = rel.dd2l ;
		let dd2ta = rel.dd2ta ;
		const dc = rel.dc ;
		const dn = rel.dn ;
		const dt = rel.dt ;
		
		const ca = [ [ ds , 'd' ] , [ ts , 't' ] , [ ml , 'V' , '' , 'Не указан материал' ] , [ dd2l , 'V' , '' , 'Не указана норма или описание' ] ];
		let cr = true ;
		for( let i = 0 ; i < ca.length ; i++ ) {
			cr = cr && checkInput.apply( null , ca[ i ] );
			if ( !cr ) {
				return ;
			}
		}
		
		if ( dlg.normUsed ) {
			cr = cr && checkInput( dc , 'i' );
		} else {
			cr = cr && checkInput( dt , 'i' );
		}
		
		if ( !cr ) {
			return ;
		}
		
		const nID = dd2l.value ;
		
		const doc = sendXML( '<add-substance extid="' + eid + '" ds="' + ds.value + '" ts="' + ts.value + '" sid="' + ml.value + '" nid="' + nID + '" c="' + ( dlg.normUsed ? dc.value : dt.value ) + '">' + toCDATA( dlg.normUsed ? '' : dd2ta.value.trim() ) + '</add-substance>' , false , EXPERTIZE_AJAX_ADDR );
		//doc = doc.documentElement ;
		if ( doc.getAttribute( 'state' ) !== 'ok' ) {
			alert( doc.nodeValue );
			return ;
		}
		
		
		ds = doc.getAttribute( 'ds' );
		ts = doc.getAttribute( 'ts' );
		dd2ta = getXMLNodeValue( doc );
		const un = doc.getAttribute( 'u' );
		const rowID = parseInt( doc.getAttribute( 'row-id' ) , 10 );
		
		let c , tmp ;
		
		const nrr = document.createElement( 'tr' );
		nrr.className = 'dlg-list-row' ;
			c = nrr.insertCell( -1 );
			//<a className="dlg-row-delete" onClick="deleteRow( 'ml' , 3394 )" title="Удалить"></a>
			c.className = 'dlg-list-d ml-col-dtb' ;
			tmp = document.createElement( 'a' );
			tmp.className = 'dlg-row-delete' ;
			tmp.onclick = deleteRow.bind( 'ml' , rowID );
			tmp.title = 'удалить' ;
			c.appendChild( tmp );
			
			c = nrr.insertCell( -1 );
			c.className = 'dlg-list-d ml-col-dd' ;
				tmp = document.createElement( 'span' );
				tmp.className = 'dlg-list-d-v' ;
				tmp.appendChild( document.createTextNode( ds ) );
			c.appendChild( tmp );
				tmp = document.createElement( 'span' );
				tmp.className = 'dlg-list-t-v' ;
				tmp.appendChild( document.createTextNode( ts ) );
			c.appendChild( tmp );
			
			c = nrr.insertCell( -1 );
			c.className = 'dlg-list-d ml-col-dm' ;
			c.appendChild( document.createTextNode( ml.options[ ml.selectedIndex ].text ) );
		
			c = nrr.insertCell( -1 );
			c.className = 'dlg-list-d ml-col-dd2' ;
			c.appendChild( document.createTextNode( dlg.normUsed ? dd2l.options[ dd2l.selectedIndex ].text : dd2ta ) );
			
			c = nrr.insertCell( -1 );
			c.className = 'dlg-list-d ml-col-dc' ;
			c.appendChild( document.createTextNode( dc.value ) );
			
			c = nrr.insertCell( -1 );
			let nd ;
			if ( dlg.normUsed ) {
				c.className = 'dlg-list-d ml-col-dn' ;
				nd = substancesNorms[ 'nd_' + nID ];
				c.innerHTML = nd.v + ' ' + nd.u ;
			} else {
				c.className = 'dlg-list-d ml-col-dna' ;
				c.innerHTML = '&mdash;' ;
			}
			
			c = nrr.insertCell( -1 );
			c.className = 'dlg-list-d ml-col-dt' ;
			if ( dlg.normUsed ) {
				c.appendChild( document.createTextNode( ( parseInt( dc.value , 10 ) * nd.v ) + ' ' + nd.u ) );
			} else {
				c.appendChild( document.createTextNode( dt.value + ' ' + un ) );
			}
			
		dlg.TabBody.appendChild( nrr );
		
		dlg.NRR.parentNode.removeChild( dlg.NRR );
		
		dlg.addBtn.style.display = '' ;
		dlg.applyBtn.style.display = 'none' ;
		dlg.cancelBtn.style.display = 'none' ;
		
		if ( dlg.ER !== null ) {
			dlg.ER = null ;
		}
	}
	
	function doAddPositionApplyPL( eid ) {
		var dlg = dialogs.pl ;
		var rel = getNewRowElements( dlg , "object" , false );
		var sl = rel.sl ;
		var pr = rel.pr ;
		var pa = rel.pa ;
		var cmt = rel.cmt ;
		
		var ca = [ [ sl , "V" , "" , "Не указана повестка" ] , [ pr , "p" ] , [ pa , "V" , /^(\s|[^a-zA-ZА-Яа-я0-9])*$/ , "Не указан плательщик" ] ];
		var cr = true ;
		for( var i = 0 ; i < ca.length ; i++ ) {
			cr = cr && checkInput.apply( null , ca[ i ] );
			if ( !cr ) {
				return ;
			}
		}
		
		if ( !cr ) {
			return ;
		}
		
		sID = sl.value ;
		
		var doc = sendXML( "<add-subpoena-addressee extid=\"" + eid + "\" s=\"" + sID + "\" pr=\"" + pr.value + "\"><pa>" + toCDATA( pa.value ) + "</pa><cmt>" + toCDATA( cmt.value ) + "</cmt></add-subpoena-addressee>" , false , EXPERTIZE_AJAX_ADDR );
		//doc = doc.documentElement ;
		if ( doc.getAttribute( "state" ) != "ok" ) {
			alert( doc.nodeValue );
			return ;
		}
		
		pr = doc.getAttribute( "pr" );
		
		var nrr = document.createElement( "tr" );
		nrr.className = "dlg-list-row" ;
			var c = nrr.insertCell( -1 );
			c.className = "dlg-list-d pl-col-t" ;
				var dBtn = document.createElement( "a" );
				dBtn.className = "dlg-row-delete" ;
				dBtn.title = "Удалить" ;
				dBtn.onclick = function( x ) {
					return function() {
						deleteRow( 'pl' , x );
					};
				}( eid );
			c.appendChild( dBtn );
			
			var c = nrr.insertCell( -1 );
			c.className = "dlg-list-d pl-col-s" ;
			c.appendChild( document.createTextNode( sl.options[ sl.selectedIndex ].text ) );
			
			var c = nrr.insertCell( -1 );
			c.className = "dlg-list-d pl-col-pr" ;
			c.appendChild( document.createTextNode( pr ) );
		
			var c = nrr.insertCell( -1 );
			c.className = "dlg-list-d pl-col-pa" ;
			c.appendChild( document.createTextNode( pa.value ) );
			
			var c = nrr.insertCell( -1 );
			c.className = "dlg-list-d pl-col-cmt" ;
			c.appendChild( document.createTextNode( cmt.value ) );
			
			var c = nrr.insertCell( -1 );
			c.className = "dlg-list-d pl-col-st" ;
			
		dlg.TabBody.appendChild( nrr );
		
		dlg.NRR.parentNode.removeChild( dlg.NRR );
		
		dlg.addBtn.style.display = "" ;
		dlg.applyBtn.style.display = "none" ;
		dlg.cancelBtn.style.display = "none" ;
		
		if ( dlg.ER !== null ) {
			dlg.ER = null ;
		}
	}
	
	function doAddPositionCancel( dlgName ) {
		var dlg = dialogs[ dlgName ];
		dlg.NRR.parentNode.removeChild( dlg.NRR );
		
		dlg.addBtn.style.display = "" ;
		dlg.applyBtn.style.display = "none" ;
		dlg.cancelBtn.style.display = "none" ;
		
		if ( dlg.ER !== null ) {
			dlg.TabBody.appendChild( dlg.ER );
		}
	}
	
	var substancesNorms = null ;
	
	function setMLNRRData( dd2l , dd2ta , dc , dn , dt ) {
		var dlg = dialogs[ "ml" ];
		var rel = getNewRowElements( dlg , "object" , false );
		
		if ( dd2l === false ) {
			rel.dd2l.disabled = true ;
		} else {
			rel.dd2l.disabled = false ;
		}
		
		if ( dd2ta === false ) {
			rel.dd2ta.style.display = "none" ;
		} else {
			rel.dd2ta.style.display = "" ;
			rel.dd2ta.value = dd2ta ;
		}
		
		if ( dc === false ) {
			rel.dc.disabled = true ;
			rel.dc.value = 1 ;
		} else {
			rel.dc.disabled = false ;
		}
		
		if ( dn === false || dn == "-" ) {
			rel.dn.parentNode.className = "dlg-list-d ml-col-dna" ;
			if ( dn === false ) {
				rel.dn.innerHTML = "" ;
			} else {
				rel.dn.innerHTML = "&mdash;" ;
			}
			dlg.normUsed = false ;
		} else {
			rel.dn.parentNode.className = "dlg-list-d ml-col-dn" ;
			setText( rel.dn , dn );
			dlg.normUsed = true ;
		}
		
		if ( dt === false ) {
			rel.dt.disabled = true ;
		} else {
			rel.dt.disabled = false ;
		}
	}
	
	function doSubstanceSelect() {		
		var rel = getNewRowElements( dialogs[ "ml" ] , "object" , false );
		
		var sisID = rel.ml.value ;
		if ( sisID == "" ) {
			setMLNRRData( false , false , false , false , false );
			return ;
		}
		
		if ( substancesNorms === null ) {
			substancesNorms = {};
			var res = sendXML( "<substances-norms />" , false , EXPERTIZE_AJAX_ADDR );
			//res = res.documentElement ;
			for( var i = 0 ; i < res.childNodes.length ; i++ ) {
				var cn = res.childNodes[ i ];
				cnid = parseInt( cn.getAttribute( "id" ) , 10 );
				cnsi = parseInt( cn.getAttribute( "si" ) , 10 );
				cnv = parseInt( cn.getAttribute( "v" ) , 10 );
				cnn = getXMLNodeValue( cn );
				cnu = cn.getAttribute( "u" );
				if ( typeof substancesNorms[ "nl_" + cnsi ] === "undefined" ) {
					substancesNorms[ "nl_" + cnsi ] = [];
				}
				substancesNorms[ "nl_" + cnsi ].push( {
					id : cnid ,
					n : cnn
				} );
				substancesNorms[ "nd_" + cnid ] = {
					v : cnv ,
					u : cnu
				};
			}
		}
		
		if ( typeof substancesNorms[ "nl_" + sisID ] !== "undefined" ) {
			var csn = substancesNorms[ "nl_" + sisID ];
			rel.dd2l.innerHTML = "<option value=\"\"></option>" ;
			for( var i = 0 ; i < csn.length ; i++ ) {
				var no = document.createElement( "option" );
				no.value = csn[ i ].id ;
				no.text = csn[ i ].n ;
				rel.dd2l.add( no , null );
			}
			var no = document.createElement( "option" );
			no.value = "-1" ;
			no.text = "Другое" ;
			rel.dd2l.add( no , null );
			setMLNRRData( true , false , false , false , false );
		} else {
			rel.dd2l.innerHTML = "<option value=\"-1\">Другое</option>" ;
			setMLNRRData( false , "" , false , "-" , 0 );
		}
	}
	
	function doSubstanceNormSelect () {
		var rel = getNewRowElements( dialogs[ "ml" ] , "object" , false );
		var nID = rel.dd2l.value ;
		if ( nID == "" ) {
			setMLNRRData( true , false , false , false , false );
			return ;
		}
		
		if ( nID != "-1" && typeof substancesNorms[ "nd_" + nID ] !== "undefined" ) {
			var nd = substancesNorms[ "nd_" + nID ];
			setMLNRRData( !rel.dd2l.disabled , false , 1 , nd.v + " " + nd.u , false );
		} else {
			setMLNRRData( !rel.dd2l.disabled , "" , false , "-" , 0 );
		}
	}
	
	function doChangeCount() {
		var dlg = dialogs[ "ml" ];
		var rel = getNewRowElements( dlg , "object" , false );
		
		var m = rel.dc.value.match( /^\s*(\d+)\s*$/ );
		if ( m != null && m.length == 2 ) {
			var nID = rel.dd2l.value ;
			if ( nID != "" && nID != "-1" && typeof substancesNorms[ "nd_" + nID ] !== "undefined" && dlg.normUsed ) {
				var nd = substancesNorms[ "nd_" + nID ];
				rel.dt.value = ( nd.v * parseInt( m[ 1 ] , 10 ) ) + " " + nd.u ;
			}
		} else {
			rel.dt.value = "" ;
		}
		
	}
	
	function deleteRow( dlgName , rid ) {
		const dlg = dialogs[ dlgName ];
		
		const res = prompt( 'Для подтверждения удаления карточки напечатайте в нижней строке слово "П О Д Т В Е Р Ж Д А Ю" без пробелов' , '' );
		if ( res === 'ПОДТВЕРЖДАЮ' ) {
			const doc = sendXML( '<delete-item dlgName="' + dlgName + '" id="' + rid + '" />' , false , 'expertize.ajax.php' , EXPERTIZE_AJAX_ADDR );
			if ( doc.getAttribute( 'state' ) === "ok" ) {
				const ri = document.getElementById( dlgName + '-tab-row-' + rid );
				ri.parentNode.removeChild( ri );
			} else {
				alert( 'При удалении произошла ошибка' );
			}
		} else {
			alert( 'Удаление не подтверждено' );
		}
	}
	
	
	
	
	function mkBill( id ) {
		var ip = document.getElementById( "i_price" );
		var n = ip.value.match( /^\s*(\d{1,10}(?:[,.]\d{2})?)\s*$/ );
		if ( n == null || n.length != 2 ) {
			alert( "Ошибка : стоимость экспертизы" );
			ip.focus();
			blinkElement( ip.parentNode.parentNode );
			return ;
		} else {
			ip.value = n[ 1 ];
		}
		
		window.open( "/bills/bill.php?invoice&exp=" + id + "&price=" + ip.value , "_blank" );
	}
	
	function showLoadTmplDlg() {
		const form = new TDLGForm();
		Object.assign( form , {
			caption : 'Загрузка шаблона документа' ,
			width : 880 ,
			height : 320 ,
			maxHeight : 640 ,
			flowDirection : TDLGComponent.DIRECTION_TOP_BOTTOM
		} );
		const area = form.dom.clientArea ;
			const inpForm = document.createElement( 'form' );
				inpForm.method = 'post' ;
				inpForm.enctype = 'multipart/form-data' ;
				inpForm.action = '/doc-generator/template-import.template.php' ;
				inpForm.target = '_blank' ;
			area.appendChild( inpForm );
			
			const tmplFile = document.createElement( 'input' );
				tmplFile.type = 'file' ;
				tmplFile.name = 'ais-tmpl-pack-file' ;
				tmplFile.accept = '.ais-tmpl' ;
			inpForm.appendChild( tmplFile );
			
			const sendBtn = document.createElement( 'button' );
				sendBtn.type = 'submit' ;
				sendBtn.appendChild( document.createTextNode( 'Загрузить' ) );
			inpForm.appendChild( sendBtn );
			
		form.show();
		form.height = Math.max( area.clientHeight , 96 );
	}
	
	function createNewTmpl() {
		let tn = '' ;
		while ( 1 ) {
			tn = prompt( 'Название нового шаблона (не менее 10 символов, не включая пробелы)' , tn );
			if ( tn === null ) {
				break ;
			}
			const ttn = tn.replaceAll( /\s+/g , '' );
			if ( ttn.length >= 10 ) {
				break ;
			}
		}
		
		if ( tn === null ) {
			return ;
		}
		
		const res = sendXML( '<create-template><name>' + toCDATA( tn ) + '</name></create-template>' , false , '/doc-generator/template-create.php' );
		
		const cpUrlParams = new URLSearchParams( window.location.search );
		const rootID = cpUrlParams.get( 'edit' );
		
		const npUrlParams = new URLSearchParams();
		npUrlParams.append( 'mode'    , 'edit-template' );
		npUrlParams.append( 'id'      , res.getAttribute( 'new-tmpl-id' ) );
		npUrlParams.append( 'root-id' , rootID );
		
		window.location.assign( '/doc-generator/preview.php?' + npUrlParams.toString() );
		
		console.log( res );
	}