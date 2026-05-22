	/**
	 * @var $
	 * @var wrkr
	 * @var specs
	 */
	
	/**
	 * @param ei
	 * @param si
	 */
	
	function init( ei , si ) {
		const sel1 = document.getElementById( 'i_worker' );
		if ( sel1 && sel1 instanceof HTMLSelectElement ) {
			sel1.value = ei ;
			let j = -1 ;
			for( let i = 0 ; i < sel1.options.length ; i++ ) {
				if ( sel1.options[ i ].value == ei ) {
					j = i ;
				}
			}
			
			sel1.selectedIndex = j ;
			
			if ( j >= 0 ) {
				upd( si );
			}
		}
	}

	$.windowOnLoad.push( function() {
		init( $.LVLC2init.expID , $.LVLC2init.specID );
		$.LVL2C = {
			exNumbersList : {} ,
			form           : document.getElementById( 'PostForm' ) ,
			exNumAreaDiv   : document.getElementById( 'add-ex-num-area' ) ,
			materialsArea  : document.getElementById( 'i_materials' ) ,
			fieldDate      : document.getElementById( 'i_date' ) ,
			fieldExData6   : document.getElementById( 'i_ex_data_6' ) ,
			cbDateFromLvl1 : document.getElementById( 'i_date_from_lvl1' )
		};

		var urlParams = new URLSearchParams( window.location.search );
		$.LVL2C.currentMatID = urlParams.get( 'add' );
		$.LVL2C.exNumbersList[ $.LVL2C.currentMatID ] = 1 ;
	} );

	function upd( si ) {
		const sel1 = document.getElementById( 'i_worker' );
		const sel2 = document.getElementById( 'i_spec' );
		const wid = 'w' + sel1.options[ sel1.selectedIndex ].value ;
		sel2.options.length = 0 ;
		sel2.appendChild( document.createElement( 'option' ) );
		
		if ( wid in wrkr ) {
			const ws = wrkr[ wid ];
			for ( const sid of ws ) {
				const nel = document.createElement( 'option' );
				sel2.appendChild( nel );
				nel.value = sid ;
				nel.text = specs[ 's' + sid ].c ;
			}
		}

		if ( si != undefined ) {
			let j = -1 ;
			for ( let i = 0 ; i < sel2.options.length ; i++ ) {
				if ( sel2.options[ i ].value == si ) {
					j = i ;
				}
			}

			if ( j > -1 ) {
				sel2.selectedIndex = j ;
			}
		}
	}

	function upd2() {
		const kat = document.getElementById( "i_kat_slognost" );
		const sel2 = document.getElementById( "i_spec" );
		const accTime = document.getElementById( "i_accounting_time" );
		/*if ( !accTime ) {
			return ;
		}*/
		
		const cb = document.getElementById( 'i-no-use-in-stat' );
		const sid = sel2.value ;
		let forceNoUseInStat = false ;
		if ( !specFilter ) {
			specFilter = {};
		}
		if ( Object.keys( specFilter ).length > 0 && !specFilter[ sid ] && specs[ 's' + sid ] && specs[ 's' + sid ].uis == 1 ) {
			forceNoUseInStat = true ;
		}
		if ( forceNoUseInStat ) {
			alert( 'Данную специальность можно указать только как экспертоучастие!' );
		}
		if ( cb ) {
			cb.checked = forceNoUseInStat == 1 ;
		}
	}

	function doSelectWorker( event , ei , si , forceNoUseInStat ) {
		/*var cb = document.getElementById( 'i-no-use-in-stat' );
		if ( forceNoUseInStat ) {
			alert( 'Данную специальность можно указать только как экспертоучастие!' );
		}
		if ( cb ) {
			cb.checked = forceNoUseInStat == 1 ;
		}*/

		event = event || window.event ;
		if ( event.target.nodeName == 'A' ) {
			return ;
		}
		init( ei , si );
		upd2();
	}

	var exNumbersList = {};
	function addExNumber() {
		var exNumbersList = $.LVL2C.exNumbersList ;
		var exNumAreaDiv = $.LVL2C.exNumAreaDiv ;
		var exNumField = document.getElementById( 'i-ex-num' );
		if ( !exNumField ) {
			exNumField = document.createElement( 'input' );
			exNumField.type = 'hidden' ;
			exNumField.id = 'i-ex-num' ;
			exNumField.name = 'i-ex-num' ;
			exNumField.value = '' ;
			$.LVL2C.form.appendChild( exNumField );
		}

		var inp = '' ;
		var matData = [];
		while ( true ) {
			inp = prompt( 'Укажите только порядковый номер экспертизы' , inp );
			if ( !inp ) {
				return ;
			}

			if ( !inp.match( /^\s*\d{1,5}\s*(,\s*\d{1,5}\s*)*$/ ) ) {
				alert( 'Введите только номер(а) экспертиз(ы) через запятую без отдела, категории и др. знаков' );
				continue ;
			}

			var nums = inp.split( ',' ).map( i => ( i + '' ).trim() );
			var doc = sendXML( '<add-ex-num op="check" id="' + $.LVL2C.currentMatID + '" ex-num="' + nums.join( ',' ) + '"/>' , false , 'main.php' );
			if ( !doc || !doc.childNodes || doc.childNodes.length != nums.length ) {
				alert( 'Данные не получены или получены частично! Попробуйте еще раз позже или укажите другой номер.' );
				continue ;
			}

			matData = [ ...doc.childNodes ];

			var pr = [];
			matData.forEach(
				function( cn ) {
					var id = cn.getAttribute( 'id' );
					if ( exNumbersList[ id ] ) {
						pr.push( cn.getAttribute( 'matNumber' ) );
					}
				}
			);

			if ( pr.length == 1 ) {
				alert( 'Номер ' + pr[ 0 ] + ' уже присутствует в списке!' );
				continue ;
			} else
			if ( pr.length > 1 ) {
				alert( 'Номера ' + pr.join( ', ' ) + ' уже присутствуют в списке!' );
				continue ;
			}

			console.log( doc );
			break ;
		}

		var styles = {
			matNumber : 'number' ,
			ay  : 'agency' ,
			at  : 'agent' ,
			ed4 : 'ed4'
		};

		matData.forEach(
			function( mat ) {
				var cmd = {};

				[ ...mat.attributes , ...mat.childNodes ].forEach(
					function( sn ) {
						switch( sn.nodeName ) {
							case 'id' :
							case 'matNumber' :
							case 'ay' :
							case 'at' :
							case 'ed4' :
								cmd[ sn.nodeName ] = getXMLNodeValue( sn );
								break ;
						}
					}
				);

				var aenData = {
					matID : cmd.id
				};

				var aenRow = document.createElement( 'div' );
				aenRow.className = 'aen-row' ;

					var aenBtnDel = document.createElement( 'a' );
					aenBtnDel.className = 'aen-btn-del' ;
					aenBtnDel.title = 'Удалить' ;
					aenBtnDel.onclick = function( data ){
						return function() {
							$.LVL2C.exNumAreaDiv.removeChild( data.domRow );
							delete( $.LVL2C.exNumbersList[ data.matID ] );
							updexNumField();
						};
					}( aenData );

					aenData.domBtnDel = aenBtnDel ;

				aenRow.appendChild( aenBtnDel );


				strexp( '{matNumber,ay,at,ed4}' ).forEach(
					function( en ) {
						var domEl = document.createElement( 'span' );
						domEl.className = 'aen-' + styles[ en ];
						domEl.appendChild( document.createTextNode( cmd[ en ] ) );

						aenData[ 'dom-' + en ] = domEl ;

						aenRow.appendChild( domEl );
					}
				);

				aenData.domRow = aenRow ;

				exNumAreaDiv.appendChild( aenRow );

				exNumbersList[ cmd.id ] = aenData ;
			}
		);

		updexNumField();
	}

	function updexNumField() {
		var exNumField = document.getElementById( 'i-ex-num' );
		var exNumbersList = $.LVL2C.exNumbersList ;
		var idList = Object.keys( exNumbersList );
		exNumField.value = idList.filter( i => typeof exNumbersList[ i ] != 'number' ).map( i => exNumbersList[ i ].matID ).join( ',' );
		$.LVL2C.materialsArea.disabled = idList.length > 1 ;
	}

	var oldDateValue = null ;
	function beforeDateChange() {
		oldDateValue = '' + $.LVL2C.fieldDate.value ;
	}

	function doDateChange() {
		var vDate = '' + $.LVL2C.fieldDate.value ;
		var vExData6 = $.LVL2C.fieldExData6.value ;
		if ( vExData6 == oldDateValue + ', ' ) {
			$.LVL2C.fieldExData6.value = vDate + ', ' ;
		}
	}

	function doCheckDateFromLvl1() {
		$.LVL2C.fieldDate.disabled = $.LVL2C.cbDateFromLvl1.checked ;
		$.LVL2C.fieldExData6.disabled = $.LVL2C.cbDateFromLvl1.checked ;
	}

	function doCheckForm() {
		const form = document.getElementById( 'PostForm' );
		const sel2 = document.getElementById( 'i_spec' );
		if ( sel2 && sel2 instanceof HTMLSelectElement ) {
			const cb = document.getElementById( 'i-no-use-in-stat' );
			const sid = sel2.value ;
			if ( sid == '' ) {
				alert( 'Специальность не указана!' );
				return ;
			}
			let forceNoUseInStat = false ;
			if ( !specFilter ) {
				specFilter = {};
			}
			if ( Object.keys( specFilter ).length > 0 && !specFilter[ sid ] && specs[ 's' + sid ] && specs[ 's' + sid ].uis == 1 ) {
				forceNoUseInStat = true ;
			}
			if ( forceNoUseInStat && !cb.checked ) {
				alert( 'Данную специальность можно указать только как экспертоучастие!' );
				return ;
			}
		}

		form.submit();
	}
