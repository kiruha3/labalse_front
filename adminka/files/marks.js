
	

	function doMarkCreate() {
		const markEditForm = $.thisPageObjects.markEditForm ;
		const groupEditForm = $.thisPageObjects.groupEditForm ;
		if ( markEditForm.visible || groupEditForm.visible || $.thisPageObjects.markDragMode ) {
			return ;
		}
		
		markEditForm.markStyle = 'yellow' ;
		markEditForm.markName = '' ;
		markEditForm.markDescr = '' ;
		markEditForm.markActual = 1 ;
		markEditForm.onApply = function( f ) {
			return function() {
				const res = sendXML( '<mark-create actual="' + ( f.markActual ? 1 : 0 ) + '"><name>' + toCDATA( f.markName ) + '</name><descr>' + toCDATA( f.markDescr ) + '</descr><style>' + f.markStyle + '</style></mark-create>' , false , 'marks.ajax.php' );
				if ( res ) {
					window.location.reload();
				}
			}
		}( markEditForm );
		markEditForm.show();
	}
	
	function doMarkEdit( evt ) {
		const markEditForm = $.thisPageObjects.markEditForm ;
		const groupEditForm = $.thisPageObjects.groupEditForm ;
		if ( markEditForm.visible || groupEditForm.visible || $.thisPageObjects.markDragMode ) {
			return ;
		}
		
		const e = evt || window.event ;
		const markEl = e.currentTarget ;
		
		const mID = markEl.dataset.markCoid ;
		
		const markText = document.querySelector( '[data-mark-element="mark"][data-mark-coid="' + mID + '"] [data-mark-element="text"]' );
		const markContainer = document.querySelector( '[data-mark-element="mark"][data-mark-coid="' + mID + '"]' );
		
		markEditForm.markStyle = markContainer.dataset.markStyle ;
		markEditForm.markName = markText.textContent ;
		markEditForm.markDescr = markContainer.title ;
		markEditForm.markActual = markContainer.dataset.markActual == 1 ;
		markEditForm.onApply = function( id , f ) {
			return function() {
				const res = sendXML( '<mark-change id="' + id + '" actual="' + ( f.markActual ? 1 : 0 ) + '"><name>' + toCDATA( f.markName ) + '</name><descr>' + toCDATA( f.markDescr ) + '</descr><style>' + f.markStyle + '</style></mark-change>' , false , 'marks.ajax.php' );
				if ( res ) {
					window.location.reload();
				}
			}
		}( mID , markEditForm );
		markEditForm.show();
	}
	
	class ltDLGMarkEdit extends TDLGForm {
	
		#__markName ;
		#__markDescr ;
		#__markStyle ;
		#__markActual ;
		#__demoMark ;
		#__samples ;
		#__samplesMap ;
		#__onApply ;
	
		constructor() {
			super();
			
			Object.assign( this , {
				caption : 'Параметры отметки' ,
				width : 640 ,
				height : 640 ,
				maxHeight : 800 ,
				flowDirection : TDLGComponent.DIRECTION_TOP_BOTTOM
			} );
			
			const area = this.dom.clientArea ;
			area.classList.add( 'marks--dlg-client-area' );
			
			const mkEl = function( cp ) {
				return function( tag , parent , style ) {
					const el = document.createElement( tag );
					if ( style ) {
						el.classList.add( cp + style );
					}
					if ( parent ) {
						parent.appendChild( el );
					}
					return el ;
				};
			}( 'marks--mark-dlg--' );
			
			const markElements = {};
			
		/* -------------------------------------------------------- */
			const nameFieldID = generateGUID();
			const nameFieldTitle = mkEl( 'label' , area , 'name-label' );
			nameFieldTitle.appendChild( document.createTextNode( 'Название отметки' ) );
			nameFieldTitle.htmlFor = nameFieldID ;
			const nameFieldInputWrapper = mkEl( 'div' , area , 'name-input-wrapper' );
			const nameFieldInput = mkEl( 'input' , nameFieldInputWrapper , 'name-input' );
			nameFieldInput.type = 'text' ;
			nameFieldInput.id = nameFieldID ;
			nameFieldInput.oninput = function( o , e ) {
				return function() {
					o.markName = e.value ;
				}
			} ( this , nameFieldInput );
			
			markElements.name = nameFieldInput ;
			
		/* -------------------------------------------------------- */
			const descrFieldID = generateGUID();
			const descrFieldTitle = mkEl( 'label' , area , 'descr-label' );
			descrFieldTitle.appendChild( document.createTextNode( 'Описание отметки' ) );
			descrFieldTitle.htmlFor = descrFieldID ;
			const descrFieldAreaWrapper = mkEl( 'div' , area , 'descr-area-wrapper' );
			const descrFieldArea = mkEl( 'textarea' , descrFieldAreaWrapper , 'descr-area' );
			descrFieldArea.id = descrFieldID ;
			descrFieldArea.oninput = function( o , e ) {
				return function() {
					o.markDescr = e.value ;
				}
			} ( this , descrFieldArea );
			
			markElements.descr = descrFieldArea ;
			
			
		/* -------------------------------------------------------- */
			const templateMarksSamplesArea = document.getElementById( 'marks-samples-area' );
			const samples = this.#__samples = templateMarksSamplesArea.content.children ;
			const samplesMap = this.#__samplesMap = {};
			
		/* -------------------------------------------------------- */
			const tab = mkEl( 'table' , area , 'param-columns-table' );
			let paramsRow ;
			paramsRow = tab.insertRow( -1 );
			let paramsCol1 , paramsCol2 ;
			
		/* -------------------------------------------------------- */
			paramsCol1 = paramsRow.insertCell( -1 );
			const styleFieldID = generateGUID();
			const styleFieldTitle = mkEl( 'label' , paramsCol1 , 'style-label' );
			styleFieldTitle.appendChild( document.createTextNode( 'Стиль отметки' ) );
			styleFieldTitle.htmlFor = styleFieldID ;
			const styleFieldSelectWrapper = mkEl( 'div' , paramsCol1 , 'style-select-wrapper' );
			const styleFieldSelect = mkEl( 'select' , styleFieldSelectWrapper , 'style-select' );
			styleFieldSelect.id = styleFieldID ;
			
			for( const cse of samples ) {
				const opt = new Option();
				const mID = cse.dataset.markStyle ;
				const text = cse.querySelector( '[data-mark-element="mark"][data-mark-style="' + mID + '"] [data-mark-element="text"]' );
				opt.text = text.textContent ;
				opt.value = mID ;
				samplesMap[ mID ] = cse ;
				styleFieldSelect.options.add( opt );
			}
			
			styleFieldSelect.onchange = function( o , e ) {
				return function() {
					o.markStyle = e.value ;
				};
			} ( this , styleFieldSelect );
			
			markElements.style = styleFieldSelect ;
			
			
		/* -------------------------------------------------------- */
			paramsCol2 = paramsRow.insertCell( -1 );
			const stateFieldID = generateGUID();
			const stateFieldTitle = mkEl( 'label' , paramsCol2 , 'state-label' );
			stateFieldTitle.appendChild( document.createTextNode( 'Состояние отметки' ) );
			stateFieldTitle.htmlFor = stateFieldID ;
			const stateFieldSelectWrapper = mkEl( 'div' , paramsCol2 , 'state-select-wrapper' );
			const stateFieldSelect = mkEl( 'select' , stateFieldSelectWrapper , 'state-select' );
			stateFieldSelect.id = stateFieldID ;
			
			for( const css of [ 1 , 0 ] ) {
				const opt = new Option();
				const text = css ? 'Активна' : 'Отключена' ;
				opt.text = text ;
				opt.value = css ;
				stateFieldSelect.options.add( opt );
			}
			
			stateFieldSelect.onchange = function( o , e ) {
				return function() {
					o.markActual = e.value ;
				};
			} ( this , stateFieldSelect );
			
			markElements.state = stateFieldSelect ;
			
			
		/* -------------------------------------------------------- */
			const demoPanelTitle = mkEl( 'label' , area , 'demo-panel-label' );
			demoPanelTitle.appendChild( document.createTextNode( 'Образец' ) );
			const demoPanel = mkEl( 'div' , area , 'demo-panel' );
			markElements.demoArea = demoPanel ;
			
		/* -------------------------------------------------------- */
			const buttonsPanel = mkEl( 'div' , area , 'buttons-panel' );
			
			
		/* -------------------------------------------------------- */
			const applyBtn = mkEl( 'a' , buttonsPanel , 'apply-btn' );
			applyBtn.classList.add( 'btn3' );
			applyBtn.appendChild( document.createTextNode( 'Принять' ) );
			applyBtn.onclick = function( o ) {
				return function() {
					o.doApply();
				}
			} ( this );
			
		/* -------------------------------------------------------- */
			this.elements.mark = markElements ;
		}
		
		selectStyle() {
			const m = this.#__samplesMap ;
			const ns = this.elements.mark.style.value ;
			if ( !m || !m[ ns ] ) {
				return ;
			}
			this.#__markStyle = ns ;
			this.refreshDemo( { body : 1 } );
		}
		
		get markName() {
			return this.#__markName ;
		}
		
		set markName( v ) {
			const lv = v + '' ;
			if ( lv != this.#__markName ) {
				this.#__markName = this.elements.mark.name.value = lv ;
				this.refreshDemo( { name : 1 } );
			}
		}
		
		get markDescr() {
			return this.#__markDescr ;
		}
		
		set markDescr( v ) {
			const lv = v + '' ;
			if ( lv != this.#__markDescr ) {
				this.#__markDescr = this.elements.mark.descr.value = lv ;
				this.refreshDemo( { descr : 1 } );
			}
		}
		
		get markStyle() {
			return this.#__markStyle ;
		}
		
		set markStyle( v ) {
			const lv = v + '' ;
			if ( lv != this.#__markStyle ) {
				this.#__markStyle = this.elements.mark.style.value = lv ;
				this.refreshDemo( { body : 1 } );
			}
		}
		
		get markActual() {
			return this.#__markActual ;
		}
		
		set markActual( v ) {
			const lv = v == 1 ;
			if ( lv != this.#__markActual ) {
				this.elements.mark.state.value = lv ? 1 : 0 ;
				this.#__markActual = lv ;
			}
		}
		
		refreshDemo( opt ) {
			let tgtMark ;
			if ( opt && opt.body ) {
				const samplesMap = this.#__samplesMap ;
				const markStyle = this.#__markStyle ;
				if ( !samplesMap[ markStyle ] ) {
					return ;
				}
				
				const se = samplesMap[ markStyle ];
				tgtMark = se.cloneNode( true );
				tgtMark.dataset.markCoid = 'demo-mark' ;
				this.elements.mark.demoArea.replaceChildren( tgtMark );
				this.#__demoMark = tgtMark ;
			} else {
				tgtMark = this.#__demoMark ;
			}
			
			if ( !tgtMark ) {
				return ;
			}
			
			if ( opt && ( opt.name || opt.body ) ) {
				const mn = this.markName ;
				const st = tgtMark.querySelector( '[data-mark-element="mark"] [data-mark-element="text"]' );
				st.replaceChildren( document.createTextNode( mn ) );
			}
			
			if ( opt && ( opt.descr || opt.body ) ) {
				const md = this.markDescr ;
				tgtMark.title = md ;
			}
		}
		
		get demoMark() {
			return this.#__demoMark ;
		}
		
		set onApply( v ) {
			if ( typeof v !== 'function' ) {
				return ;
			}
			
			this.#__onApply = v ;
		}
		
		doApply() {
			if ( this.#__onApply && typeof this.#__onApply === 'function' ) {
				this.#__onApply();
			}
		}
	}
	
	function doGroupCreate() {
		const markEditForm = $.thisPageObjects.markEditForm ;
		const groupEditForm = $.thisPageObjects.groupEditForm ;
		if ( markEditForm.visible || groupEditForm.visible || $.thisPageObjects.markDragMode ) {
			return ;
		}
		
		const membersLists = {
			childMarks : [] ,
			childGroups : [] ,
			parentGroups : []
		};
		
		groupEditForm.groupID = null ;
		groupEditForm.groupName = '' ;
		groupEditForm.groupDescr = '' ;
		groupEditForm.groupActual = 1 ;
		
		Object.assign( groupEditForm , membersLists );
		
		groupEditForm.onApply = function( f ) {
			return function() {
				const marksLinks = [];
				for( const id of f.childMarks ) {
					marksLinks.push( '<link mark-id="' + id + '"/>' );
				}
				const groupsLinks = [];
				for( const id of f.childGroups ) {
					groupsLinks.push( '<link group-id="' + id + '"/>' );
				}
				for( const id of f.parentGroups ) {
					groupsLinks.push( '<link parent-id="' + id + '"/>' );
				}
				const res = sendXML( '<group-create actual="' + ( f.groupActual ? 1 : 0 ) + '"><name>' + toCDATA( f.groupName ) + '</name><descr>' + toCDATA( f.groupDescr ) + '</descr><links><marks>' + marksLinks.join( '' ) + '</marks><groups>' + groupsLinks.join( '' ) + '</groups></links></group-create>' , false , 'marks.ajax.php' );
				if ( res ) {
					window.location.reload();
				}
			}
		}( groupEditForm );
		groupEditForm.show();
	}
	
	function doGroupEdit( evt ) {
		const markEditForm = $.thisPageObjects.markEditForm ;
		const groupEditForm = $.thisPageObjects.groupEditForm ;
		if ( markEditForm.visible || groupEditForm.visible || $.thisPageObjects.markDragMode ) {
			return ;
		}
		
		const e = evt || window.event ;
		const groupEl = e.currentTarget ;
		
		const gID = groupEl.dataset.groupId ;
		
		const groupName  = document.querySelector( '[data-mark-element="group"][data-group-id="' + gID + '"]' );
		const groupDescr = document.querySelector( '[data-mark-element="group-descr"][data-group-id="' + gID + '"]' );
		
		const linksDoc = sendXML( '<get-group-links id="' + gID + '"/>' , false , 'marks.ajax.php' );
		const membersLists = {
			childMarks : [] ,
			childGroups : [] ,
			parentGroups : []
		};
		for( const lgn of linksDoc.childNodes ) {
			if ( lgn.nodeType === Node.ELEMENT_NODE ) {
				switch( lgn.nodeName ) {
					case 'marks' :
						for( const ml of lgn.childNodes ) {
							if ( ml.nodeType === Node.ELEMENT_NODE && ml.nodeName === 'link' && ml.hasAttribute( 'mark-id' ) ) {
								membersLists.childMarks.push( ml.getAttribute( 'mark-id' ) );
							}
						}
						break ;
						
					case 'groups' :
						for( const gl of lgn.childNodes ) {
							if ( gl.nodeType === Node.ELEMENT_NODE && gl.nodeName === 'link' && gl.hasAttribute( 'group-id' ) ) {
								membersLists.childGroups.push( gl.getAttribute( 'group-id' ) );
							}
							if ( gl.nodeType === Node.ELEMENT_NODE && gl.nodeName === 'link' && gl.hasAttribute( 'parent-id' ) ) {
								membersLists.parentGroups.push( gl.getAttribute( 'parent-id' ) );
							}
						}
						break ;
				}
			}
		}
		
		
		groupEditForm.groupID = gID ;
		groupEditForm.groupName = groupName.textContent ;
		groupEditForm.groupDescr = groupDescr.textContent ;
		groupEditForm.groupActual = groupName.dataset.groupActual == 1 ;
		
		Object.assign( groupEditForm , membersLists );
		
		groupEditForm.onApply = function( id , f ) {
			return function() {
				const marksLinks = [];
				for( const id of f.childMarks ) {
					marksLinks.push( '<link mark-id="' + id + '"/>' );
				}
				const groupsLinks = [];
				for( const id of f.childGroups ) {
					groupsLinks.push( '<link group-id="' + id + '"/>' );
				}
				for( const id of f.parentGroups ) {
					groupsLinks.push( '<link parent-id="' + id + '"/>' );
				}
				const res = sendXML( '<group-change id="' + id + '" actual="' + ( f.groupActual ? 1 : 0 ) + '"><name>' + toCDATA( f.groupName ) + '</name><descr>' + toCDATA( f.groupDescr ) + '</descr><links><marks>' + marksLinks.join( '' ) + '</marks><groups>' + groupsLinks.join( '' ) + '</groups></links></group-change>' , false , 'marks.ajax.php' );
				if ( res ) {
					window.location.reload();
				}
			}
		}( gID , groupEditForm );
		groupEditForm.show();
	}
	
	class ltDLGGroupEdit extends TDLGForm {
	
		static get CHILD_MARKS() { return 'childMarks' };
		static get CHILD_GROUPS() { return 'childGroups' };
		static get PARENT_GROUPS() { return 'parentGroups' };
		static get TGT_MARK() { return 'mark' };
		static get TGT_GROUP() { return 'group' };
	
		#___mkEl ;
		
		#__groupID ;
		#__groupName ;
		#__groupDescr ;
		#__groupActual ;
		#__onApply ;
		
		#__membersListHandlers ;
		
		#__members ;
		#__tgtElementsData ;
		
		get mkEl() {
			return this.#___mkEl ;
		}
		
		constructor() {
			super();
			
			Object.assign( this , {
				caption : 'Параметры группы' ,
				width : 640 ,
				height : 640 ,
				maxHeight : 800 ,
				flowDirection : TDLGComponent.DIRECTION_TOP_BOTTOM
			} );
			
			const area = this.dom.clientArea ;
			area.classList.add( 'marks--dlg-client-area' );
			
			const mkEl = this.#___mkEl = function( cp ) {
				return function( tag , parent , style ) {
					const el = document.createElement( tag );
					if ( style ) {
						el.classList.add( cp + style );
					}
					if ( parent ) {
						parent.appendChild( el );
					}
					return el ;
				};
			} ( 'marks--group-dlg--' );
			
			const groupElements = {};
			
		/* -------------------------------------------------------- */
			const nameFieldID = generateGUID();
			const nameFieldTitle = mkEl( 'label' , area , 'name-label' );
			nameFieldTitle.appendChild( document.createTextNode( 'Название группы' ) );
			nameFieldTitle.htmlFor = nameFieldID ;
			const nameFieldInputWrapper = mkEl( 'div' , area , 'name-input-wrapper' );
			const nameFieldInput = mkEl( 'input' , nameFieldInputWrapper , 'name-input' );
			nameFieldInput.type = 'text' ;
			nameFieldInput.id = nameFieldID ;
			nameFieldInput.oninput = function( o , e ) {
				return function() {
					o.groupName = e.value ;
				}
			} ( this , nameFieldInput );
			
			groupElements.name = nameFieldInput ;
			
		/* -------------------------------------------------------- */
			const descrFieldID = generateGUID();
			const descrFieldTitle = mkEl( 'label' , area , 'descr-label' );
			descrFieldTitle.appendChild( document.createTextNode( 'Описание группы' ) );
			descrFieldTitle.htmlFor = descrFieldID ;
			const descrFieldAreaWrapper = mkEl( 'div' , area , 'descr-area-wrapper' );
			const descrFieldArea = mkEl( 'textarea' , descrFieldAreaWrapper , 'descr-area' );
			descrFieldArea.id = descrFieldID ;
			descrFieldArea.oninput = function( o , e ) {
				return function() {
					o.groupDescr = e.value ;
				}
			} ( this , descrFieldArea );
			
			groupElements.descr = descrFieldArea ;
			
			
		/* -------------------------------------------------------- */
			const stateFieldID = generateGUID();
			const stateFieldTitle = mkEl( 'label' , area , 'state-label' );
			stateFieldTitle.appendChild( document.createTextNode( 'Состояние группы' ) );
			stateFieldTitle.htmlFor = stateFieldID ;
			const stateFieldSelect = mkEl( 'select' , stateFieldTitle , 'state-select' );
			stateFieldSelect.id = stateFieldID ;
			
			for( const css of [ 1 , 0 ] ) {
				const opt = new Option();
				const text = css ? 'Активна' : 'Отключена' ;
				opt.text = text ;
				opt.value = css ;
				stateFieldSelect.options.add( opt );
			}
			
			stateFieldSelect.onchange = function( o , e ) {
				return function() {
					o.groupActual = e.value ;
				};
			} ( this , stateFieldSelect );
			
			groupElements.state = stateFieldSelect ;
			
			
		/* -------------------------------------------------------- */
			
			const tab = mkEl( 'div' , area , 'members-table' );
			
		/* -------------------------------------------------------- */
			const membersListCol1 = mkEl( 'div' , tab , 'members-col-area' );
			const membersListTitle = mkEl( 'label' , membersListCol1 , 'members-list-label' );
			membersListTitle.appendChild( document.createTextNode( 'Содержит' ) );
			const membersList = mkEl( 'div' , membersListCol1 , 'members-list' );
			
			const membersListGroups = mkEl( 'div' , membersList , 'members-sub-list' );
			membersListGroups.dataset.subListName = 'Группы' ;
			const membersListMarks = mkEl( 'div' , membersList , 'members-sub-list' );
			membersListMarks.dataset.subListName = 'Отметки' ;
			
			groupElements.membersList       = membersList ;
			groupElements.membersListMarks  = membersListMarks ;
			groupElements.membersListGroups = membersListGroups ;
			
			
		/* -------------------------------------------------------- */
			const membersListCol2 = mkEl( 'div' , tab , 'members-col-area' );
			const belongsToListTitle = mkEl( 'label' , membersListCol2 , 'members-list-label' );
			belongsToListTitle.appendChild( document.createTextNode( 'Состоит в группах' ) );
			const belongsToListWrapper = mkEl( 'div' , membersListCol2 , 'members-list-wrapper' );
			const belongsToList = mkEl( 'div' , belongsToListWrapper , 'members-list' );
			
			groupElements.belongsToList = belongsToList ;
			
			/* -------------------------------------------------------- */
			const buttonsPanel = mkEl( 'div' , area , 'buttons-panel' );
			
			
		/* -------------------------------------------------------- */
			const applyBtn = mkEl( 'a' , buttonsPanel , 'apply-btn' );
			applyBtn.classList.add( 'btn3' );
			applyBtn.appendChild( document.createTextNode( 'Принять' ) );
			applyBtn.onclick = this.doApply.bind( this );
			
		/* -------------------------------------------------------- */
			this.elements.group = groupElements ;
			
			this.onShow = this.handlerOnShow.bind( this );
			this.onHide = this.handlerOnHide.bind( this );
			this.#__membersListHandlers = {};
			
			this.#__members = {
				[ltDLGGroupEdit.CHILD_MARKS] : {
					list : {} ,
					elementType : ltDLGGroupEdit.TGT_MARK ,
					listAreaDom : membersListMarks ,
					dragAreaDom : membersList
				} ,
				[ltDLGGroupEdit.CHILD_GROUPS] : {
					list : {} ,
					elementType : ltDLGGroupEdit.TGT_GROUP ,
					listAreaDom : membersListGroups ,
					dragAreaDom : membersList
				} ,
				[ltDLGGroupEdit.PARENT_GROUPS] : {
					list : {} ,
					elementType : ltDLGGroupEdit.TGT_GROUP ,
					listAreaDom : belongsToList ,
					dragAreaDom : belongsToList
				}
			};
			
			this.#__tgtElementsData = {
				[ ltDLGGroupEdit.TGT_MARK ] : {
					list : null ,
					globalListName : 'marksList' ,
					idField : 'markCoid' ,
					rmFields : [ 'markActual' , 'commentStyle' , 'commentExtType' , 'commentExtId' , 'commentSubstyle' , 'commentVStylePref' ]
				},
				[ ltDLGGroupEdit.TGT_GROUP ] : {
					list :null ,
					globalListName : 'groupsList' ,
					idField : 'groupId' ,
					rmFields : [ 'groupActual' ]
				}
			};
		}
		
		get groupID() {
			return this.#__groupID ;
		}
		
		set groupID( v ) {
			const lv = v + '' ;
			if ( lv != this.#__groupID ) {
				this.#__groupID = lv ;
			}
		}
		
		get groupName() {
			return this.#__groupName ;
		}
		
		set groupName( v ) {
			const lv = v + '' ;
			if ( lv != this.#__groupName ) {
				this.#__groupName = this.elements.group.name.value = lv ;
			}
		}
		
		get groupDescr() {
			return this.#__groupDescr ;
		}
		
		set groupDescr( v ) {
			const lv = v + '' ;
			if ( lv != this.#__groupDescr ) {
				this.#__groupDescr = this.elements.group.descr.value = lv ;
			}
		}
		
		get groupActual() {
			return this.#__groupActual ;
		}
		
		set groupActual( v ) {
			const lv = v == 1 ;
			if ( lv != this.#__groupActual ) {
				this.elements.group.state.value = lv ? 1 : 0 ;
				this.#__groupActual = lv ;
			}
		}
		
		setMembersList( memberType , newVal ) {
			//debugger ;
			
			const newUniqueVal = [ ...new Set( newVal ) ];
			
			if ( !this.#__members[ memberType ] ) {
				return ;
			}
			const ctMembersData = this.#__members[ memberType ];
			const members = ctMembersData.list ;
			
			const newList = ( typeof newUniqueVal === 'string' || typeof newUniqueVal === 'number' ) ? [ newUniqueVal ] : newUniqueVal ;
			if ( !( newList instanceof Array ) ) {
				return ;
			}
			
			const remList = {};
			for( const i in members ) {
				if ( newUniqueVal.indexOf( i ) === -1 ) {
					remList[ i ] = members[ i ];
				}
			}
			
			const addList = [];
			for( const i of newList ) {
				if ( !members[ i ] ) {
					if ( !( ctMembersData.elementType === ltDLGGroupEdit.TGT_GROUP && i == this.groupID ) ) {
						addList.push( i );
					}
				}
			}
			
			for( const i in remList ) {
				const memberData = remList[ i ];
				memberData.dom.remove();
				delete members[ i ];
			}
			
			const tgtElementsData = ctMembersData.elementType === ltDLGGroupEdit.TGT_MARK ? $.thisPageObjects.marksList : $.thisPageObjects.groupsList ;
			for( const i of addList ) {
				if ( tgtElementsData[ i ] ) {
					const cElementData = tgtElementsData[ i ];
					const cNewElementDom = this.addMemberToList( memberType , cElementData.dom );
					members[ i ] = {
						id : i ,
						dom : cNewElementDom
					};
				}
			}
		}
		
		get childMarks() {
			return Object.keys( this.#__members[ ltDLGGroupEdit.CHILD_MARKS ].list );
		}
		
		set childMarks( v ) {
			//debugger ;
			this.setMembersList( ltDLGGroupEdit.CHILD_MARKS , v );
		}
		
		get childGroups() {
			return Object.keys( this.#__members[ ltDLGGroupEdit.CHILD_GROUPS ].list );
		}
		
		set childGroups( v ) {
			//debugger ;
			this.setMembersList( ltDLGGroupEdit.CHILD_GROUPS , v );
		}
		
		get parentGroups() {
			return Object.keys( this.#__members[ ltDLGGroupEdit.PARENT_GROUPS ].list );
		}
		
		set parentGroups( v ) {
			//debugger ;
			this.setMembersList( ltDLGGroupEdit.PARENT_GROUPS , v );
		}
		
		set onApply( v ) {
			if ( typeof v !== 'function' ) {
				return ;
			}
			
			this.#__onApply = v ;
		}
		
		doApply() {
			if ( this.#__onApply && typeof this.#__onApply === 'function' ) {
				this.#__onApply();
			}
		}
		
		handlerOnShow() {
			//debugger ;
			for( const elementType in this.#__tgtElementsData ) {
				const elementsData = this.#__tgtElementsData[ elementType ];
				if ( !elementsData.list ) {
					elementsData.list = $.thisPageObjects[ elementsData.globalListName ];
					for( const cElementID in elementsData.list ) {
						const cElementData = elementsData.list[ cElementID ];
						const cElement = cElementData.dom ;
						cElementData.handlers = {
							dragStart : function( o , t , e ) {
								return function( event ) {
									o.handlerElementDragStart( event , t , e );
								}
							} ( this , elementType , cElement ) ,
							dragEnd : function( o , t , e ) {
								return function( event ) {
									o.handlerElementDragEnd( event , t , e );
								}
							} ( this , elementType , cElement )
						};
					}
				}
				
				const elementsDataList = elementsData.list ;
				const allID = {};
				for( const memberType in this.#__members ) {
					const cMemberData = this.#__members[ memberType ];
					if ( cMemberData.elementType === elementType ) {
						Object.assign( allID , cMemberData.list );
					}
				}
				
				for( const cElementID in elementsDataList ) {
					if ( !allID[ cElementID ] ) {
						this.makeTgtElementDraggable( elementType , cElementID );
					} else {
						this.cancelTgtElementDraggable( elementType , cElementID , true );
					}
				}
				
			}
			
			$.thisPageObjects.markDragMode = true ;
			return true ;
		}
		
		makeTgtElementDraggable( elementType , elementID ) {
			if ( !this.#__tgtElementsData[ elementType ] ) {
				return ;
			}
			
			if ( elementType === ltDLGGroupEdit.TGT_GROUP && elementID == this.groupID ) {
				this.cancelTgtElementDraggable( elementType , elementID , true );
				return ;
			}
			
			const tgtElementsData = this.#__tgtElementsData[ elementType ];
			const cElementData = tgtElementsData.list[ elementID ];
			const cElement = cElementData.dom ;
			const cHandlers = cElementData.handlers ;
			
			cElement.draggable = true ;
			cElement.addEventListener( 'dragstart' , cHandlers.dragStart );
			cElement.addEventListener( 'dragend'   , cHandlers.dragEnd );
			cElement.classList.add( 'drag-mode' );
			cElement.classList.add( 'element-draggable' );
			cElement.classList.remove( 'element-non-draggable' );
		}
		
		cancelTgtElementDraggable( elementType , elementID , editMode ) {
			if ( !this.#__tgtElementsData[ elementType ] ) {
				return ;
			}
			
			const tgtElementsData = this.#__tgtElementsData[ elementType ];
			const cElementData = tgtElementsData.list[ elementID ];
			const cElement = cElementData.dom ;
			const cHandlers = cElementData.handlers ;
			
			cElement.draggable = cElementData.defDraggable ;
			cElement.removeEventListener( 'dragstart' , cHandlers.dragStart );
			cElement.removeEventListener( 'dragend'   , cHandlers.dragEnd );
			
			if ( editMode ) {
				cElement.classList.add( 'drag-mode' );
				cElement.classList.remove( 'element-draggable' );
				cElement.classList.add( 'element-non-draggable' );
			} else {
				cElement.classList.remove( 'drag-mode' );
				cElement.classList.remove( 'element-draggable' );
				cElement.classList.remove( 'element-non-draggable' );
			}
		}
		
		clearDropHandlers() {
			const mlh = this.#__membersListHandlers ;
			for( const o of mlh.dragOver ) {
				o.ml.removeEventListener( 'dragover' , o.doel );
				o.ml.removeEventListener( 'drop' , o.del );
			}
			mlh.dragOver = [];
		}
		
		handlerOnHide() {
			for( const type in this.#__tgtElementsData ) {
				const elementsData = this.#__tgtElementsData[ type ];
				for( const elementID in elementsData.list ) {
					this.cancelTgtElementDraggable( type , elementID , false );
				}
			}
			
			$.thisPageObjects.markDragMode = false ;
			return true ;
		}
		
		handlerElementDragStart( event , elementType , element ) {
			const mlh = this.#__membersListHandlers ;
			mlh.dragOver = [];
			for( const memberType in this.#__members ) {
				const cMemberData = this.#__members[ memberType ];
				if ( cMemberData.elementType === elementType ) {
					const ml = cMemberData.dragAreaDom ;
					const doel = function( o ) {
						return function( event ) {
							o.handlerElementDragOver( event );
						}
					} ( this );
					const del = function( o , e , m ) {
						return function( event ) {
							o.handlerElementDrop( event , e , m );
						}
					} ( this , element , memberType );
					mlh.dragOver.push( { ml , doel , del } );
					ml.addEventListener( 'dragover' , doel );
					ml.addEventListener( 'drop' , del );
				}
			}
		}
		
		handlerElementDragOver( event ) {
			event.preventDefault();
		}
		
		handlerElementDrop( event , element , memberType ) {
			console.log( event , element , memberType );
			console.log( 'dropped OK : ' , event );
			
			if ( !this.#__members[ memberType ] ) {
				return ;
			}
			
			const membersData = this.#__members[ memberType ];
			const elementType = membersData.elementType ;
			
			const elementsData = this.#__tgtElementsData[ elementType ];
			const newElement = this.addMemberToList( memberType , element );
			const elementID = element.dataset[ elementsData.idField ];
			membersData.list[ elementID ] = {
				id : elementID ,
				dom : newElement
			};
			this.cancelTgtElementDraggable( elementType , elementID , true );
			
			event.preventDefault();
			this.clearDropHandlers();
		}
		
		handlerElementDragEnd( event , elementType , element ) {
			console.log( 'DRAG END' );
			this.clearDropHandlers();
			event.preventDefault();
		}
		
		addMemberToList( memberType , element ) {
			if ( !this.#__members[ memberType ] ) {
				return ;
			}
			
			const mkEl = this.mkEl ;
			const membersData = this.#__members[ memberType ];
			const elementType = membersData.elementType ;
			const elementsData = this.#__tgtElementsData[ elementType ];
			const elementID = element.dataset[ elementsData.idField ];
			
			const listAreaDom = membersData.listAreaDom ;
			const ma = mkEl( 'div' , listAreaDom , 'members-area' );
			const db = mkEl( 'a' , ma , 'members-delete-item-btn' );
			db.onclick = this.removeMemberFromList.bind( this , memberType , element );
			const idl = mkEl( 'span' , ma , 'members-id' );
			idl.appendChild( document.createTextNode( elementID ) );
			
			const ne = element.cloneNode( true );
			ne.draggable = false ;
			for( const fld of elementsData.rmFields ) {
				delete ne.dataset[ fld ];
			}
			ne.setAttribute( 'onclick'  , '' );
			
			ma.appendChild( ne );
			
			return ma ;
		}
		
		removeMemberFromList( memberType , element ) {
			if ( !this.#__members[ memberType ] ) {
				return ;
			}
			
			const membersData = this.#__members[ memberType ];
			const elementType = membersData.elementType ;
			const elementsData = this.#__tgtElementsData[ elementType ];
			
			const elementID = element.dataset[ elementsData.idField ];
			const membersList = membersData.list ;
			const elementData = membersList[ elementID ];
			
			elementData.dom.remove();
			
			delete membersList[ elementID ];
			this.makeTgtElementDraggable( elementType , elementID );
		}
	}
	
	
	
	$.windowOnLoad.push( function() {
		const markEditForm = new ltDLGMarkEdit();
		const groupEditForm = new ltDLGGroupEdit();
		
		const marksList = {};
		const marksNodesList = document.querySelectorAll( '.marks--marks-table [data-mark-element="mark"]' );
		
		for( const mark of marksNodesList ) {
			const markID = mark.dataset.markCoid ;
			marksList[ markID ] = {
				id : markID ,
				dom : mark ,
				defDraggable : mark.draggable
			};
		}
		
		const groupsList = {};
		const groupsNodesList = document.querySelectorAll( '.marks--groups-table [data-mark-element="group"]' );
		
		for( const group of groupsNodesList ) {
			const groupID = group.dataset.groupId ;
			groupsList[ groupID ] = {
				id : groupID ,
				dom : group ,
				defDraggable : group.draggable
			};
		}
		
		Object.assign( $.thisPageObjects , {
			markEditForm ,
			groupEditForm ,
			marksList ,
			groupsList ,
			markDragMode : false
		} );
	} );