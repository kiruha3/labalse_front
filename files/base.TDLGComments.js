
	$.TDLGComments = function() {
		this.links = [];
		this.linksMap = {};

		var mkElem = mk_makeElement( 'std-comments-' );
		var mkDiv = mkElem ;

		var mkElemI = mk_makeElement( "std-comments " );
		var mkDivI = mkElemI ;

		this.saveLink = function( i ) {
			var links = this.links ;
			var linksMap = this.linksMap ;

			links.push( i );

			if ( !linksMap[ i.etype ] ) {
				linksMap[ i.etype ] = {};
			}

			var clma = linksMap[ i.etype ];

			if ( !clma[ "e:" + i.eid ] ) {
				clma[ "e:" + i.eid ] = [];
			}
			clma[ "e:" + i.eid ].push( i );
		};

		this.saveCLink = function( i , c ) {
			var cl = i.comments ;
			var cm = i.commentsMap ;
			cl.push( c );

			if ( !cm[ "c:" + c.id ] ) {
				cm[ "c:" + c.id ] = c ;
			}
		};

		this.linkAll = function() {
			var elem = document.querySelectorAll( "[data-comment-style]" );
			var ql = '' ;
			for( var i = 0 ; i < elem.length ; i++ ) {
				var clds = elem[ i ].dataset ;
				ql += "<item type=\"" + clds.commentExtType + "\" id=\"" + clds.commentExtId + "\" auto-edit=\"" + !!clds.commentAutoEdit + "\" />" ;
				var cci = {
					etype : clds.commentExtType ,
					eid : clds.commentExtId ,
					style : clds.commentStyle ,
					vsp : clds.commentVStylePref ,
					el : elem[ i ] ,
					comments : [] ,
					commentsMap : {} ,
					lvl : 0 ,
					editor : null ,
					container : null
				};
				this.saveLink( cci );
			}

			sendXML( "<get-comments-for>" + ql + "</get-comments-for>" , true , "/comments.core.php" , '' , false , function( x ){
				return function( req ) {
					var d = req.responseXML.documentElement ;
					x.createCommentsFromXML( d );
					//x.links ;
				};
			}( this ) );
		};

		this.createCommentsFromXML = function( xml ) {
			var links = this.links ;
			var linksMap = this.linksMap ;
			var cil = xml.childNodes ;
			var may = true ;
			while ( may ) {
				may = false ;
				var cilc = cil.length ;
				for( var i = 0 ; i < cilc ; i++ ) {
					var cci = cil[ i ];
					var cci = {
						id       : cci.getAttribute( 'id' ) ,
						etype    : cci.getAttribute( 'etype' ) ,
						eid      : cci.getAttribute( 'eid' ) ,
						date     : cci.getAttribute( 'date' ) ,
						date_s   : cci.getAttribute( 'date_s' ) ,
						exp      : cci.getAttribute( 'exp' ) ,
						exp_s    : cci.getAttribute( 'exp_s' ) ,
						auto_edit: attrToBool( cci.getAttribute( 'auto_edit' ) ) ,
						rights   : JSON.parse( cci.getAttribute( 'rights' ) ) ,
						comment  : getXMLNodeValue( cci ) ,
						dom      : {
							area   : null ,
							text   : null ,
							author : null ,
							editor : null
						}
					};
					console.log( cci );
					if ( !linksMap[ cci.etype ] ) {
						continue ;
					}
					var clma = linksMap[ cci.etype ];
					if ( !clma[ "e:" + cci.eid ] ) {
						continue ;
					}
					var clmal = clma[ "e:" + cci.eid ];
					for( var j = 0 ; j < clmal.length ; j++ ) {
						var lcci = clmal[ j ];
						if ( !lcci.commentsMap[ "c:" + cci.id ] ) {
							this.createComment( lcci , cci );
							may = true ;
						}
					}
				}
			}
		};

		this.makeCTRLButtons = function ( l , c , a , o ) {
			if ( c.rights ) {
				var r = c.rights ;
				if ( r.add || r.edit || r.del ) {
					var tb = o( l.vsp + " menu" );
					if ( r.add ) {
						var btn = o( l.vsp + " menu-btn add" );
						btn.onclick = function() {
							return function() {

							};
						}();
						tb.appendChild( btn );
					}
					if ( r.edit ) {
						var btn = o( l.vsp + " menu-btn edit" );
						btn.onclick = function() {
							return function() {

							};
						}();
						tb.appendChild( btn );
					}
					if ( r.del ) {
						var btn = o( l.vsp + " menu-btn del" );
						btn.onclick = function() {
							return function() {

							};
						}();
						tb.appendChild( btn );
					}
					return tb ;
				} else {
					return null ;
				}
			} else {
				return null ;
			}

		};

		this.createComment = function( l , c ) {
			var cew ;
			if ( !l.container ) {
				l.container = mkDivI( l.vsp + " container" );
				l.el.appendChild( l.container );
			}
			switch( l.style ) {
				case 'inline' :
					cew = this.createCommentInline( l , c );
					break ;

				default :
					cew = this.createCommentInline( l , c );
					break ;
			}

			var cci = {
				etype : 'comments' ,
				eid : c.id ,
				style : 'outline' ,
				vsp : 'std-comment-outline' ,
				el : cew ,
				comments : [] ,
				commentsMap : {} ,
				lvl : l.lvl + 1 ,
				editor : null
			};

			cew.dataset.commentStyle = cci.style ;
			cew.dataset.commentExtType = cci.etype ;
			cew.dataset.commentExtId = cci.eid ;
			//cew.dataset.commentId = cci.id ;

			this.saveLink( cci );
			this.saveCLink( l , c );
		};

		this.createCommentInline = function( l , c ) {
			if ( c.auto_edit ) {
				if ( l.editor != null ) {
				} else {
					var ceWrapper = mkDivI( l.vsp + " editor-wrapper" );

					var cEditor = mkElemI( 'textarea' , l.vsp + " editor" );
					cEditor.value = c.comment ;
					l.editor = {
						comment : c ,
						dom : cEditor
					};
					c.dom.editor = cEditor ;

					ceWrapper.appendChild( cEditor );
					l.container.appendChild( ceWrapper );
					return ceWrapper ;
				}
			} else {
				var cArea = mkDivI( l.vsp + " area" );
				var cText = mkDivI( l.vsp + " text" );
				cText.appendChild( document.createTextNode( c.comment ) );
				cArea.appendChild( cText );

				var cAuthor = mkDivI( l.vsp + " author" );
				cAuthor.appendChild( document.createTextNode( c.exp_s + ", " + c.date_s ) );
				cArea.appendChild( cAuthor );

				var cMenu = this.makeCTRLButtons( l , c , cArea , mkDivI );
				if ( cMenu !== null ) {
					cArea.appendChild( cMenu );
				}
				l.container.appendChild( cArea );
				c.dom.area   = cArea ;
				c.dom.text   = cText ;
				c.dom.author = cAuthor ;
			}

			return cArea ;
		};
	};

