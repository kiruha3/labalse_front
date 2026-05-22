

	function processTriggerChain( triggerChain , triggerIndex , DGData ) {
		console.log( triggerIndex );
		if ( triggerIndex < triggerChain.length ) {
			let pr = new Promise( function( trg , dgd ) {
				return function( resolve , reject ) {
					processTrigger( resolve , reject , trg , dgd );
				};
			} ( triggerChain[ triggerIndex ] , DGData ) );
			return pr.then( processTriggerChain( triggerChain , triggerIndex + 1 , DGData ) , function() { return Promise.reject(); } );
		} else {
			return Promise.resolve();
		}
	}

	function processTrigger( resolve , reject , trigger , DGData ) {
		switch( trigger.type ) {
			case 'ask-user' :
				console.log( 'RESOLVED BY TRIGGER' );
				resolve();
				break ;

			case 'internal' :
				console.log( 'RESOLVED BY TRIGGER' );
				resolve();
				break ;

			case 'exteral-vriables-form' :
				processTrigger_ExternalVriablesForm.apply( this , arguments );
				break ;

			default :
				console.log( 'rejected BY TRIGGER' );
				reject();
				break ;
		}
	}

	function externalVriablesForm( DGData , resolve , reject ) {
		const tmplData = DGData.tmplData ;
		const extVarDef = tmplData.extVar ;
		const collectedData = DGData.collectedData ;
		const exData = {
			classes : tmplData.classes
		};

		let formDataSrc = {};
		const formDataDefault = {
			caption : 'Параметры' ,
			width : 1200 ,
			height : 800 ,
			maxHeight : 960 ,
			flowDirection : TDLGComponent.DIRECTION_TOP_BOTTOM
		};
		for( const def of extVarDef ) {
			if ( def && def.type && def.type == TDGVariable.SPECIAL_TYPE_FORM_DATA ) {
				formDataSrc = def ;
			}
		}
		const formData = new TDLGFormData( formDataSrc , formDataDefault , exData );
		const form = new TDLGForm( formData );

		form.show();


		const extVar = {};
		const area = form.dom.clientArea ;

		for( const cv of extVarDef ) {
			const v = Object.assign( {} , cv );
			v.name = 'ext:' + v.name ;
			if ( v.type && v.type != TDGVariable.SPECIAL_TYPE_FORM_DATA ) {
				const dgv = TDGVariable.fromDef( null , v , exData );
				if ( collectedData[ v.name ] ) {
					dgv.read( collectedData[ v.name ] );
				}
				dgv.reset();
				dgv.generateDom( area );
				extVar[ v.name ] = dgv ;
			}
		}

		const btnPanel = document.createElement( 'div' );
		btnPanel.className = 'dg--dlg-form----btn-panel' ;
		form.dom.dlgArea.appendChild( btnPanel );

		const btnApply = document.createElement( 'a' );
		btnApply.className = 'btn3' ;
		btnApply.appendChild( document.createTextNode( 'Сохранить и продолжить' ) );
		btnApply.onclick = function( res , f, data , ev ) {
			return function() {
				f.hide();
				for( let n in ev ) {
					ev[ n ].applyChanges();
					data[ n ] = ev[ n ].write();
				}
				console.log( data );
				res();
			};
		}( resolve , form , DGData.collectedData , extVar );
		btnPanel.appendChild( btnApply );

		form.onCLose = function( rej ) {
			return function() {
				let res = confirm( 'Все несохраненные данные будут потеряны. Продолжить ?' );
				if ( res ) {
					rej();
					return true ;
				} else {
					return false ;
				}
			};
		}( reject );

		console.log( extVar );

		$.TMPLForm = form ;
		$.TMPLextVar = extVar ;
	}

	function processTrigger_ExternalVriablesForm( resolve , reject , trigger , DGData ) {
		externalVriablesForm( DGData , resolve , reject );
	}

	function saveCollectedData( DGData ) {
		let pr = new Promise( function( dgd ) {
			return function( resolve , reject ) {
				sendDataPOST( '/doc-generator/save.php?tmpl=' + dgd.tmplID + '&id=' + dgd.rootID + '&doc=' + dgd.docID , dgd.collectedData , function( a ) {
					return function() {
						a();
					};
				}( resolve ) );
			};
		} ( DGData ) );
		return pr ;
	}

	function showPreview( docID ) {
		console.log( ( new Date() ).getTime() );
		window.open( '/doc-generator/preview.php?id=' + docID , '_blank' );
		console.log( ( new Date() ).getTime() );
	}

	function downloadDocument( docID , dnldType ) {
		window.open( '/doc-generator/download.php?type=' + dnldType + '&id=' + docID , '_blank' );
	}

	function doDownload( DGData ) {
		const tmplData = DGData.tmplData ;
		const triggers = tmplData.triggers ;
		if ( tmplData.downloadMode == 1 ) {
			let tgp ;
			if ( triggers.beforeDownload && triggers.beforeDownload.length > 0  ) {
				tgp = processTriggerChain( triggers.beforeDownload , 0 , DGData );
			} else {
				tgp = Promise.resolve();
			}
			console.log( tgp );
			tgp.then( function( dgd ) {
				return function() {
					var pr = saveCollectedData( dgd );
					pr.then( function( id , t ) {
						return function() {
							downloadDocument( id , t );
						};
					}( dgd.docID , dgd.dnldType ) );
				};
			} ( DGData ) );
		}
	}

	function loadGeneratedDoc( DGData ) {
		const docVar = sendXML( '<get-tmpl-vars id="' + DGData.docID + '"/>' , false , '/doc-generator/template-data.php' );
		if ( docVar && typeof docVar == 'object' && docVar instanceof Node ) {
			if ( docVar.nodeType !== Node.ELEMENT_NODE || docVar.nodeName !== 'result' || docVar.hasAttribute( 'error' ) ) {
				alert( 'Ошибка загрузки данных. Обратитесь к администратору' );
				return ;
			}
			for( const v of docVar.childNodes ) {
				if ( v.nodeType === Node.ELEMENT_NODE && v.nodeName === 'ext-var' ) {
					const data = JSON.parse( getXMLNodeValue( v ) );
					Object.assign( DGData.collectedData , data );
				}
			}
			console.log( DGData.collectedData );
		}

		externalVriablesForm( DGData , function( dgd ) {
			return function(){
				let pr = saveCollectedData( dgd );
				pr.then( function( id ) {
					return function() {
						showPreview( id );
					};
				} ( dgd.docID ) );
			}
		} ( DGData ) , function(){} );
	}

	function genDoc( tmplID , rootID , dnldType ) {
		let tmplData = getTmplData( tmplID );
		const DGData = {
			tmplID ,
			tmplData ,
			rootID ,
			dnldType ,
			docID         : null ,
			collectedData : {}
		};
		if ( tmplData.proceeding == 2 ) {
			const generatedList = sendXML( '<get-generated-docs-list tmpl="' + tmplID + '" root="' + rootID + '"/>' , false , '/doc-generator/template-data.php' );
			
			if ( generatedList.childNodes.length > 0 ) {
				const form = new TDLGForm();
				Object.assign( form , {
					caption : 'Список сгенерированных документов' ,
					width : 880 ,
					height : 320 ,
					maxHeight : 640 ,
					flowDirection : TDLGComponent.DIRECTION_TOP_BOTTOM
				} );
				const area = form.dom.clientArea ;

				const panel = document.createElement( 'div' );
				panel.className = 'dg--generated-list--panel' ;
				
				
					const btnCreate = document.createElement( 'div' );
					btnCreate.className = 'btn3' ;
					btnCreate.appendChild( document.createTextNode( 'Создать новый' ) );
					btnCreate.onclick = function( DGD , f ) {
						return function() {
							doGenDoc( DGD );
							f.close();
						};
					} ( DGData , form );
					
					panel.appendChild( btnCreate );
					
					const btnDnldTmpl = document.createElement( 'a' );
					btnDnldTmpl.className = 'btn3' ;
					btnDnldTmpl.appendChild( document.createTextNode( 'экспорт шаблона' ) );
					btnDnldTmpl.href = '/doc-generator/export.php?tmpl=' + tmplID ;
					btnDnldTmpl.target = '_blank' ;
					panel.appendChild( btnDnldTmpl );
					
				area.appendChild( panel );
				
				for( const dgd of generatedList.childNodes ) {
					const cDGData = Object.assign( {} , DGData , {
						docID : dgd.getAttribute( 'doc-id' )
					} );
					const item = document.createElement( 'div' );
					item.classList.add( 'dg--generated-list--item' );
					item.classList.add( 'state-' + dgd.getAttribute( 'state' ) );
					let label ;
					let value ;
					const tvList = { created : 'создан' , edited  : 'изменен' , downloaded : 'скачан' };
					for( let tv in tvList ) {
						label = document.createElement( 'span' );
						label.appendChild( document.createTextNode( tvList[ tv ] ) );
						item.appendChild( label );
						value = document.createElement( 'span' );
						let text ;
						if ( dgd.hasAttribute( 'time-' + tv ) && dgd.getAttribute( 'time-' + tv ) ) {
							text = formatDate( parseInt( dgd.getAttribute( 'time-' + tv ) ) * 1000 , '{d}-{m}-{Y} {H}:{i}:{s}' );
						} else {
							text = '-' ;
						}
						value.appendChild( document.createTextNode( text ) );
						item.appendChild( value );
					}
					
					item.onclick = function( dgd , f ) {
						return function() {
							loadGeneratedDoc( dgd );
							f.close();
						};
					} ( cDGData , form );
					area.appendChild( item );
				}
				
				form.show();
				form.height = Math.max( area.clientHeight , 96 );
				return ;
			} else {
				doGenDoc( DGData );
			}
		} else {
			doGenDoc( DGData );
		}
	}

	function doGenDoc( origDGData ) {
		console.log( ( new Date() ).getTime() );

		const DGData = Object.assign( {} , origDGData , {
			docID : generateReqID( origDGData ) ,
			collectedData : {}
		} );

		const tmplData = DGData.tmplData ;
		const triggers = tmplData.triggers ;

		if ( tmplData.previewMode == 1 ) {
			let tgp ;
			if ( triggers.beforeView && triggers.beforeView.length > 0  ) {
				tgp = processTriggerChain( triggers.beforeView , 0 , DGData );
			} else {
				tgp = Promise.resolve();
			}
			console.log( tgp );
			tgp.then( function( dgd ) {
				return function() {
					let pr = saveCollectedData( dgd );
					pr.then( function( id ) {
						return function() {
							showPreview( id );
						};
					} ( dgd.docID ) );
				};
			}( DGData ) );
		} else {
			doDownload( DGData );
		}
	}