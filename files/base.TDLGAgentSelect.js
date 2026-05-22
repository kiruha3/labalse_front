
	$.TDLGAgentSelect = function( opt , sel ) {
		
		this.contactData = [ {
			label : 'адрес' ,
			tag : 'textarea' ,
			placeHolder : '394000, г.Воронеж, ...'
		} , {
			label : 'e-mail' ,
			tag : 'input' ,
			tagType : 'text' ,
			placeHolder : 'vasya_pupkin@mail.ru'
		} , {
			label : 'факс' ,
			tag : 'input' ,
			tagType : 'text' ,
			placeHolder : '(473) 200-00-00 или 200-00-00'
		} , {
			label : 'мобильный' ,
			tag : 'input' ,
			tagType : 'text' ,
			placeHolder : '8-920-200-00-00'
		} , {
			label : 'на руки' ,
			tag : 'input' ,
			tagType : 'text' ,
			placeHolder : 'Иванов Иван Иванович ...'
		} ];
		
		
		
		const dom = this.dom = {};

		this.options = {
			'new-contacts-name-prefix' : '' ,
			'contacts-name-prefix' : '' ,
			'html-form-post' : false
		};
		for( const o in opt ) {
			this.options[ o ] = opt[ o ];
		}
		if ( sel ) {
			this.selected = sel ;
		} else {
			this.selected = sel = {};
		}
		this.cache = {};
		this.nextList = {
			type : 'agencyList' ,
			agencyList : 'agentList' ,
			agentList : null
		};
		this.nextInput = {
			type : 'agency' ,
			agencyList : 'agent' ,
			agentList : null
		};
		this.linkedEditors = {
			type : null ,
			agencyList : 'agency' ,
			agentList : 'agent'
		};
		this.agencyAddressLnkFunc = null ;
		this.srchTimer = null ;

		this.addresseeList = [];

		if ( opt.link ) {
			strexp( "{type,agen{cy,t}{,List,Label},agencyAddress,contacts{List{,Ctrl{1,2,3,4,5},Store,Panel},LabelAgentName},addressee{List}}" ).forEach( function( v ) {
				const opt = this.options ;
				if ( opt.link[ v ] ) {
					this.dom[ v ] = document.getElementById( opt.link[ v ] );
				}
			} , this );
		} else {
			var dlg = document.createElement( 'div' );
			dlg.className = 'tdlg-agent-select-wrapper' ;

			var tmp = document.createElement( 'div' );
			tmp.className = 'tdlg-agent-select' ;
			var tab = document.createElement( 'table' );
			tab.className = 'tdlg-agent-select-table' ;
			tmp.appendChild( tab );

			var cap = document.createElement( 'div' );
			cap.className = 'tdlg-agent-select-cap' ;
			cap.appendChild( document.createTextNode( "Выберите шаблон" ) );
			var closeBtn = document.createElement( 'div' );
			closeBtn.className = 'tdlg-agent-select-close-btn' ;
			closeBtn.onclick = function ( dlg ) {
				return function () {
					if ( dlg.mode && dlg.mode == 'config' ) {
						dlg.configCancel();
						dlg.cfgModeClose();
					} else {
						dlg.close();
					}
				};
			}( this );
			cap.appendChild( closeBtn );
			tmp.appendChild( cap );

			dlg.appendChild( tmp );



			var r = tab.insertRow( -1 );
			var c = r.insertCell( -1 );
			c.className = 'tdlg-agent-select-t-v' ;
			var tmp = dom.type = document.createElement( 'select' );
			tmp.className = 'tdlg-agent-select-toa' ;
			c.appendChild( tmp );

			var r = tab.insertRow( -1 );
			var c = r.insertCell( -1 );
			c.className = 'tdlg-agent-select-t-v' ;
			var tmp = document.createElement( 'textarea' );
			tmp.className = 'tdlg-agent-select-from-agency' ;
			c.appendChild( tmp );

			var r = tab.insertRow( -1 );
			var c = r.insertCell( -1 );
			c.className = 'tdlg-agent-select-a-c' ;
			var tmp1 = document.createElement( 'div' );
			tmp1.className = 'tdlg-agent-select-a-c1' ;
			c.appendChild( tmp1 );

			var tmp2 = document.createElement( 'div' );
			tmp2.className = 'tdlg-agent-select-a-c2' ;
			tmp1.appendChild( tmp2 );

			var tmp = document.createElement( 'select' );
			tmp.size = 2 ;
			tmp.className = 'tdlg-agent-select-agency-sel' ;
			tmp2.appendChild( tmp );

			var r = tab.insertRow( -1 );
			var c = r.insertCell( -1 );
			c.className = 'tdlg-agent-select-t-v' ;
			var tmp = document.createElement( 'textarea' );
			tmp.className = 'tdlg-agent-select-from-agency' ;
			c.appendChild( tmp );

			var r = tab.insertRow( -1 );
			var c = r.insertCell( -1 );
			c.className = 'tdlg-agent-select-a-c' ;
			var tmp1 = document.createElement( 'div' );
			tmp1.className = 'tdlg-agent-select-a-c1' ;
			c.appendChild( tmp1 );

			var tmp2 = document.createElement( 'div' );
			tmp2.className = 'tdlg-agent-select-a-c2' ;
			tmp1.appendChild( tmp2 );

			var tmp = document.createElement( 'select' );
			tmp.size = 2 ;
			tmp.className = 'tdlg-agent-select-agency-sel' ;
			tmp2.appendChild( tmp );

			var r = tab.insertRow( -1 );
			var c = r.insertCell( -1 );
			c.className = 'tdlg-agent-select-t-v' ;
			var tmp = document.createElement( 'div' );
			tmp.className = 'tdlg-agent-select-a-c1' ;
			c.appendChild( tmp );

			var r = tab.insertRow( -1 );
			var c = r.insertCell( -1 );
			c.className = 'tdlg-agent-select-t-v' ;
			var tmp = document.createElement( 'a' );
			tmp.className = 'btn3' ;
			setText( tmp , 'Ok' );
			c.appendChild( tmp );

			document.body.appendChild( dlg );
		}

		strexp( "{type,agen{cy,t}List}" ).forEach( function( v ) {
			const dom = this.dom ;
			if ( dom[ v ] ) {
				const elt = dom[ v ];
				if ( elt ) {
					elt.onchange = function( o , t ) {
						return function( evt ) {
							const e = window.event || evt ;
							console.log( e );
							o.listSelect( null , t );
						};
					}( this , v );
				}
			}
		} , this );

		strexp( "{agen{cy,t}}" ).forEach( function( v ) {
			const dom = this.dom ;
			if ( dom[ v ] && dom[ v + 'List' ] ) {
				const elt = dom[ v ];
				if ( elt ) {
					elt.oninput = function( o , t ) {
						return function() {
							o.srch( t );
						};
					}( this , v );
				}
			}
		} , this );

		for( var i = 1 ; i <= 5 ; i++ ) {
			if ( dom[ 'contactsListCtrl' + i ] ) {
				dom[ 'contactsListCtrl' + i ].onclick = function( o , btni ) {
					return function() {
						o.addContact( btni );
					};
				}( this , i );
			}
		}

		if ( dom[ 'contactsListStore' ] ) {
			dom[ 'contactsListStore' ].onclick = function( o ) {
				return function() {
					o.doAddAddressee();
				};
			}( this );
		}

		if ( dom[ 'agencyAddress' ] && opt.agencyAddressFunc ) {
			this.agencyAddressFunc = opt.agencyAddressFunc ;
		}

		//if ( dom[ 'addresseeList' ] )

		this.loadList = function( extID , listType , cbFunc ) {
			const updFunc = function( o , eid , lt , fn ){
				return function( req ) {
					const cid = 'id' + eid ;
					const lc = o.cache ;
					let dcn ;
					if ( req == null ) {
						dcn = lc[ lt ][ cid ];
					} else {
						dcn = JSON.parse( req.response );
						if ( lc[ lt ] ) {
							lc[ lt ][ cid ] = dcn ;
						} else {
							lc[ lt ] = {};
							lc[ lt ][ cid ] = dcn ;
						}
					}
					lc[ lt ].current = dcn ;
					lc[ lt ].currentIndex = eid ;
					lc[ lt ].currentRM = {};
					const rm = lc[ lt ].currentRM ;
					let cv ;
					if ( o.selected && o.selected[ listType ] ) {
						cv = o.selected[ listType ];
					} else {
						cv = null ;
					}
					for( let i = 0 ; i < dcn.length ; i++ ) {
						const cn = dcn[ i ];
						cn.nameUC = cn.name.toUpperCase();
						rm[ 'id' + cn.id ] = cn ;
					}

					if ( o.dom[ lt ] ) {
						const sel = o.dom[ lt ];
						const newSel = sel.cloneNode( false );
						let liSelected = false;
						for( let i = 0 ; i < dcn.length ; i++ ) {
							const cn = dcn[ i ];
							const tmp = new Option( cn.name , cn.id );
							cn.opt = tmp;
							if ( cv == cn.id ) {
								tmp.selected = true;
								liSelected = cn.name;
							}
							newSel.appendChild( tmp );
							//cn.nameUC = cn.name.toUpperCase();
							//rm[ 'id' + cn.id ] = cn ;
						}
						sel.parentNode.replaceChild( newSel , sel );
						o.dom[ lt ] = newSel;
						newSel.onchange = sel.onchange;
						
						if ( o.linkedEditors[ lt ] ) {
							const len = o.linkedEditors[ lt ];
							o.dom[ len ].value = liSelected !== false ? liSelected : '';
						}
					}

					fn();
				};
			}( this , extID , listType , cbFunc );



			if ( this.cache[ listType ] && this.cache[ listType ][ 'id' + extID ] ) {
				updFunc( null );
			} else {
				switch( listType ) {
					case 'agencyList' :
						sendXML( '<agency-list toa="' + extID + '"/>' , true , '/agents.ajax.php' , '' , false , updFunc );
						break ;

					case 'agentList' :
						sendXML( '<agent-list agency="' + extID + '" ' + ( this.dom.contactsList ? ' contacts="1"' : '' ) + ' />' , true , '/agents.ajax.php' , '' , false , updFunc );
						break ;
				}
			}
		};

		this.listSelect = function ( elID , listType ) {
			const dom = this.dom ;
			const cache = this.cache ;
			
			if ( elID == null ) {
				const sel = dom[ listType ];
				if ( sel && sel.selectedIndex > -1 ) {
					elID = sel.options[ sel.selectedIndex ].value ;
					if ( this.linkedEditors[ listType ] ) {
						const len = this.linkedEditors[ listType ];
						if ( dom[ len ] ) {
							dom[ len ].value = sel.options[ sel.selectedIndex ].text ;
						}
					}
				} else
				if ( this.selected && this.selected[ listType ] ) {
					elID = this.selected[ listType ];
				} else {
					elID = 1 ;
				}
			}

			if ( listType === 'agencyList' && dom.agencyAddress && cache[ listType ] ) {
				const aa = dom.agencyAddress ;
				const rm = cache[ listType ].currentRM ;
				const cn = rm[ 'id' + elID ];
				/*console.log( 'agenCY LIST' );
				console.log( 'cn: ' , cn );
				console.log( 'id: ' , extID );
				console.log( 'cache: ' , this.cache );*/
				
				if ( cn ) {
					if ( aa.tagName === 'INPUT' || aa.tagName === 'TEXTAREA' ) {
						aa.value = cn.destination ;
					} else {
						while ( aa.lastChild ) {
							aa.removeChild( aa.lastChild );
						}
						if ( cn.destination ) {
							aa.appendChild( document.createTextNode( cn.destination.replace( /,/g , ", " ).replace( /\s+/ , " " ) ) );
							if ( this.agencyAddressFunc !== null ) {
								aa.onclick = function( o , x ) {
									return function() {
										o.agencyAddressFunc( x );
									};
								}( this , cn.destination );
							}
						}
					}
				}
			}

			if ( listType === 'agentList' && dom.contactsList && cache[ listType ] ) {
				const cl = dom.contactsList ;
				
				while( cl.rows.length > 0 ) {
					cl.deleteRow( 0 );
				}
				
				const rm = cache[ listType ].currentRM ;
				
				const CLAN = dom.contactsLabelAgentName ?? null ;
				if ( CLAN ) {
					CLAN.innerHTML = '' ;
				}
				
				if ( elID && ( elID !== 'none' ) && rm[ 'id' + elID ] ) {
					const cn = rm[ 'id' + elID ];
					/*console.log( 'agenT LIST' );
					console.log( 'cn: ' , cn );
					console.log( 'id: ' , extID );
					console.log( 'cache: ' , this.cache );*/
					
					if ( CLAN ) {
						CLAN.appendChild( document.createTextNode( cn.name ) );
					}
					
					if ( cn && cn.contacts ) {
						for( let i = 0 ; i < cn.contacts.length ; i++ ) {
							const cc = cn.contacts[ i ];
							if ( cc.actual ) {
								this.addContact( cc.type , cc );
							}
						}
						for( let i = 0 ; i < cn.contacts.length ; i++ ) {
							const cc = cn.contacts[ i ];
							if ( !cc.actual ) {
								this.addContact( cc.type , cc );
							}
						}
					}
				}
			}

			const nln = this.nextList[ listType ];
			const nin = this.nextInput[ listType ];
			if ( ( nln && dom[ nln ] ) || ( nin && dom[ nin ] ) ) {
				this.loadList( elID , nln , function( o , x ) {
					return function() {
						if ( o.selected[ x ] ) {
							o.listSelect( o.selected[ x ] , x );
						}
					};
				}( this , nln ) );
			}

			this.selected[ listType ] = elID ;
		};

		this.addContact = function( ct , cd , cc ) {
			const contactData = this.contactData ;
			if ( typeof cc == 'undefined' ) {
				cc = false ;
			}

			const justCreated = ( typeof cd == 'undefined' );
			let ncnp ;
			if ( typeof cd == 'undefined' ) {
				cd = {
					uid : generateGUID() ,
					value : ''
				};

				ncnp = this.options[ 'new-contacts-name-prefix' ] ? this.options[ 'new-contacts-name-prefix' ] : this.options[ 'contacts-name-prefix' ];
			} else {
				ncnp = this.options[ 'contacts-name-prefix' ];
			}

			const inpID = 'std-agent-contact-' + generateGUID();

			let r , c , tmp ;
			const ctab = this.dom.contactsList ;
			r = ctab.insertRow( -1 );
			const copyAnimObj = {
				cd ,
				r
			};
			r.classList.add( 'std-agents-act-row' );
			if ( !justCreated && !cd.actual ) {
				r.classList.add( 'std-agents-act-row-non-actual' );
			}
				c = r.insertCell( -1 );
				c.className = 'std-agents-act-name' ;
					tmp = document.createElement( 'label' );
					tmp.className = 'std-agents-inline-label' ;
					setText( tmp , contactData[ ct - 1 ].label );
					tmp.htmlFor = inpID ;
						const copyBtn = document.createElement( 'div' );
						copyBtn.className = 'std-agents-inline-label-copy' ;
						copyBtn.addEventListener( 'click' , function( obj ){
							return function() {
								let d = obj.r.querySelector( '.std-agents-contact-animation' );
								if ( d ) {
									d.parentNode.removeChild( d );
								} else {
									d = document.createElement( 'div' );
									d.className = 'std-agents-contact-animation' ;
								}
								navigator.clipboard.writeText( obj.cd.value );
								obj.tgtTD.insertBefore( d , obj.inp );
								setTimeout( function( e ){
									return function() {
										d.parentNode.removeChild( d );
									};
								}( d ) , 1500 );
							};
						}( copyAnimObj ) );
					tmp.appendChild( copyBtn );
				c.appendChild( tmp );

				c = copyAnimObj.tgtTD = r.insertCell( -1 );
				c.className = 'std-agents-act-value' ;
				
				if ( justCreated || cd.actual ) {
					tmp = copyAnimObj.inp = document.createElement( contactData[ ct - 1 ].tag );
					if ( contactData[ ct - 1 ].tagType ) {
						tmp.type = contactData[ ct - 1 ].tagType ;
					}
					tmp.name = ncnp + 'contacts[' + cd.uid + ']' ;
					tmp.placeholder = contactData[ ct - 1 ].placeHolder ;
					tmp.value = cd.value ;
					c.appendChild( tmp );
					
					if ( this.options[ 'html-form-post' ] ) {
						const tmp = document.createElement( 'input' );
						tmp.type = 'hidden' ;
						tmp.name = ncnp + 'contacts-type[' + cd.uid + ']' ;
						tmp.value = ct ;
						c.appendChild( tmp );
					}
				} else {
					tmp = copyAnimObj.inp = document.createElement( 'span' );
					tmp.appendChild( document.createTextNode( cd.value ) );
				}
				tmp.id = inpID ;
				tmp.setAttribute( 'data-contact-type' , ct );
				tmp.classList.add( 'std-agents-contact' );
				c.appendChild( tmp );
				
				const inpTag = tmp ;
			
				if ( !this.options[ 'no-select-checkbox' ] ) {
					c = r.insertCell( -1 );
					c.className = 'std-agents-act-repl' ;
					tmp = document.createElement( 'input' );
					tmp.type = 'checkbox' ;
					tmp.name = ncnp + 'contacts-cb[' + cd.uid + ']' ;
					tmp.value = 'checked' ;
					tmp.setAttribute( 'data-for-contact' , inpID );
					if ( cc ) {
						tmp.checked = true ;
					}
					c.appendChild( tmp );
				}
			
				if ( !this.options[ 'no-delete-btn' ] ) {
					c = r.insertCell( -1 );
					c.className = 'std-agents-act-btns' ;
					tmp = document.createElement( 'div' );
					tmp.className = 'std-agents-act-dcb' ;
					tmp.onclick = function( o , t , d , r ){
						return function() {
							if ( d.actual ) {
								o.hideContact( d );
							} else {
								o.showContact( d );
							}
							r.remove();
							o.addContact( t , d );
						};
					} ( this , ct , cd , r );
					c.appendChild( tmp );
				}
				
				//$.dlgTmpl.create( inpTag , { temporary : true , listLoader : contactsLoader } );
			return inpTag ;
		};
		
		this.hideContact = function( params ) {
			const contactData = this.contactData ;
			
			const atID = 'id' + params.ext_id ;
			const atData = this.cache[ 'agentList' ].currentRM[ atID ];
			
			const res = confirm( 'Хотите удалить контакт: ' + "\n    [" + contactData[ params.type - 1 ].label + '] ' + params.value + "\n" + 'для :' + "\n    " + atData.name + ' ?' );
			if ( res ) {
				const res2 = confirm( 'Вместо удаления контакта он буде скрыт. Продолжить ?' );
				if ( res2 ) {
					sendXML( '<hide-contact id="' + params.id + '"/>' , true , '/agents.ajax.php' );
					params.actual = 0 ;
				}
			}
		}
		
		this.showContact = function( params ) {
			const contactData = this.contactData ;
			
			const atID = 'id' + params.ext_id ;
			const atData = this.cache[ 'agentList' ].currentRM[ atID ];
			
			const res = confirm( 'Хотите возобновить отображение контакта: ' + "\n    [" + contactData[ params.type - 1 ].label + '] ' + params.value + "\n" + 'для :' + "\n    " + atData.name + ' ?' );
			if ( res ) {
				sendXML( '<show-contact id="' + params.id + '"/>' , true , '/agents.ajax.php' );
				params.actual = 1 ;
			}
		}
		
		this.srch = function( type ) {
			const dom = this.dom ;
			const ed = dom[ type ];
			const st = ed.value.trim().toUpperCase();
			const cd = this.cache[ type + 'List' ].current ;
			const count = cd.length ;
			const sel = dom[ type + 'List' ];
			if ( st ) {
				if ( type === 'agency' ) {
					if ( this.srchTimer !== null ) {
						clearTimeout( this.srchTimer );
					}

					this.srchTimer = setTimeout( function( o , x ) {
						return function() {
							//o.exInfoReq( x );
							clearTimeout( o.srchTimer );
						};
					}( this , type ) , 1000 );
				}
				
				let j = -1 ;
				for ( let i = 0 ; i < count ; i++ ) {
					if ( cd[ i ].nameUC.indexOf( st ) >= 0 ) {
						if ( ( cd[ i ].nameUC == st ) && ( j < 0 ) ) {
							j = i ;
						}
						cd[ i ].opt.hidden = false ;
					} else {
						cd[ i ].opt.hidden = true ;
					}
				}

				sel.selectedIndex = j ;
			} else {
				for ( let i = 0 ; i < count ; i++ ) {
					cd[ i ].opt.hidden = false ;
				}
				sel.selectedIndex = -1 ;
			}
			
			if ( sel.selectedIndex == -1 ) {
				this.listSelect( 'none' , type + 'List' );
				console.log( type + '  is  NONE' );
			}
		};

		this.doAddAddressee = function() {
			const addresseeData = {
				uid : generateGUID() ,
				agencyType : 1 ,
				orgName : '' ,
				name : '' ,
				contacts : [] ,
				contactsMap : {}
			};
			const tcl = [];
			const ncnp = this.options[ 'new-contacts-name-prefix' ];
			const contactsList = document.getElementsByName( ncnp + 'contacts[]' );
			for( let i = 0 ; i < contactsList.length ; i++ ) {
				const cli = contactsList[ i ];
				const cc = {
					id : cli.id ,
					type : cli.getAttribute( 'data-contact-type' ),
					value : cli.value.trim() ,
					useForReply : 0 ,
					state : 0 ,
					stateDate : ''
				};

				addresseeData.contacts.push( cc );
				addresseeData.contactsMap[ cc.id ] = cc ;
			}

			const contactsCbList = document.getElementsByName( ncnp + "contacts-cb[]" );
			for( let i = 0 ; i < contactsCbList.length ; i++ ) {
				const ccbli = contactsCbList[ i ];
				if ( ccbli.checked ) {
					const ccid = ccbli.getAttribute( 'data-for-contact' );
					addresseeData.contactsMap[ ccid ].useForReply = 1 ;
					tcl.push( addresseeData.contactsMap[ ccid ].value );
				}
			}

			const dom = this.dom ;
			addresseeData.agencyType = dom.type.value ;
			addresseeData.orgName = dom.agency.value ;
			addresseeData.name = dom.agent.value ;

			this.mkAddresseeRow( addresseeData , tcl );

			this.addresseeList.push( addresseeData );
		};

		this.mkAddresseeRow = function( addresseeData ) {
			const al = this.dom.addresseeList ;
			const r = al.insertRow( -1 );
			r.className = 'std-agents-alt-row' ;
			let c ;
			c = r.insertCell( -1 );
			c.className = 'std-agents-alt-name' ;
			addText( c , addresseeData.orgName + ', ' + addresseeData.name );

			c = r.insertCell( -1 );
			c.className = 'std-agents-alt-cnt' ;
			const tcl = addresseeData.contacts ;
			for( let i = 0 ; i < tcl.length ; i++ ) {
				if ( tcl[ i ].useForReply == 1 ) {
					const tmp = document.createElement( 'div' );
					const tmp2 = document.createElement( 'div' );
					tmp2.className = tcl[ i ].state == 1 ? 'std-agents-alt-ss-ok' : 'std-agents-alt-ss-wait' ;
					tmp2.onclick = function ( o , x , y ) {
						return function() {
							o.changeSendState( x , y );
						};
					}( this , tmp2 , tcl[ i ] );
					tmp.appendChild( tmp2 );
					addText( tmp , tcl[ i ].value );
					tmp.title = tcl[ i ].value ;
					c.appendChild( tmp );
				}
			}

			c = r.insertCell( -1 );
			c.className = 'std-agents-alt-btns' ;
			const tmp = document.createElement( 'div' );
			tmp.className = 'std-agents-alt-dab' ;
			tmp.onclick = function ( o , x ) {
				return function () {
					o.deleteAddresseeRow( x );
				};
			}( this , addresseeData );
			c.appendChild( tmp );

			addresseeData.row = r ;

			return r ;
		};

		this.deleteAddresseeRow = function( ad ) {
			let adi = -1 ;
			const addresseeList = this.addresseeList ;
			for( let i = 0 ; i < addresseeList.length ; i++ ) {
				if ( addresseeList[ i ].uid == ad.uid ) {
					adi = i ;
					break ;
				}
			}

			if ( adi == -1 ) {
				return ;
			}

			addresseeList.splice( adi , 1 );
			ad.row.parentNode.removeChild( ad.row );
		};



		/*this.exInfoReq = function( type ) {
			clearTimeout( this.srchTimer );
			if ( type == 'agency' && this.dom[ type ] ) {

			}
		};*/

		if ( sel.type ) {
			this.listSelect( sel.type , 'type' );
		} else {
			this.listSelect( 1 , 'type' );
		}
	};