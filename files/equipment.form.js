/**
 * 
 */

	function addTestResult( id , did ) {
		var uploadDlg = document.getElementById( "uploadDlg" );
		
		var reqID = generateGUID();
		
		var res = sendXML( "<add-test-result-req reqid=\"" + reqID + "\" />" );
		
		var uploadDlgFrame = document.getElementById( "uploadDlgFrame" );
		uploadDlgFrame.src = "file_store/upload_form.php?id=" + did + "&req=" + reqID + "&frame" ;
		
		$.waitFileUpload = {
			timer : setInterval( waitFileUploadFunc , 2000 ) ,
			reqID : reqID ,
			id : id
		};
		
		uploadDlg.style.display = "" ;
	}
	
	function waitFileUploadFunc() {
		if ( typeof $ !== "undefined" && typeof $.waitFileUpload !== "undefined" ) {
			var reqID = $.waitFileUpload.reqID ;
			var res = sendXML( "<get-test-result-req reqid=\"" + reqID + "\" />" );
			if ( res.getAttribute( "result" ) == "ok" ) {
				clearInterval( $.waitFileUpload.timer );
				
				var fileId = res.getAttribute( "id" );
				
				var ntiTestResult = document.getElementById( "ntiTestResult_" + $.waitFileUpload.id );
				var ntiTestResultSee = document.getElementById( "ntiTestResult_" + $.waitFileUpload.id + "_see" );
				var ntiTestResultAdd = document.getElementById( "ntiTestResult_" + $.waitFileUpload.id + "_add" );
				var ntiTestResultDel = document.getElementById( "ntiTestResult_" + $.waitFileUpload.id + "_del" );
				
				ntiTestResult.value = fileId ;
				ntiTestResultSee.style.display = "" ;
				ntiTestResultSee.href = "/file_store/download.php?id=" + fileId ;
				ntiTestResultAdd.style.display = "none" ;
				ntiTestResultDel.style.display = "" ;

				var uploadDlg = document.getElementById( "uploadDlg" );
				uploadDlg.style.display = "none" ;
			}
		}
	}
	
	function delTestResult( id , did ) {
		var res = confirm( "Вы действительно хотите удалить результат?" );
		if ( !res ) {
			return ;
		}
		
		var ntiTestResult = document.getElementById( "ntiTestResult_" + id );
		var ntiTestResultSee = document.getElementById( "ntiTestResult_" + id + "_see" );
		var ntiTestResultAdd = document.getElementById( "ntiTestResult_" + id + "_add" );
		var ntiTestResultDel = document.getElementById( "ntiTestResult_" + id + "_del" );
		
		ntiTestResult.value = "" ;
		ntiTestResultSee.style.display = "none" ;
		ntiTestResultSee.href = "" ;
		ntiTestResultAdd.style.display = "" ;
		ntiTestResultDel.style.display = "none" ;
	}
	
	function uploadDlgClose() {
		var uploadDlg = document.getElementById( "uploadDlg" );
		uploadDlg.style.display = "none" ;
		if ( typeof $ !== "undefined" && typeof $.waitFileUpload !== "undefined" ) {
			clearInterval( $.waitFileUpload.timer );
		}
	}

	function toggleUseInAnyExp() {
		var inp = document.getElementById( 'i_useInAnyExp' );
		var lst = document.getElementById( 'spec-list-area' );
		lst.dataset.specAll = inp.checked ? '1' : '0' ;
	}

	function sendAsync( url , opts , callback ) {
		var tmpf = function( url , opts , callback ) {
			fetch( url , opts )
				.then(
					function( resp ) {
						resp.json().then(
							function( j ) {
								if ( j.status == 200 ) {
									callback( j );
								}
							}
						);
					}
				);
		} ( url , opts , callback );
	}

	var lastMiTypeNumber = '' ;
	function doMiTypeNumberCange() {
		var mtn = document.getElementById( 'i_miTypeNumber' );
		if ( mtn ) {
			mtn = mtn.value ;
		} else {
			return ;
		}

		var mtTitle = document.getElementById( 'i_miTypeTitle' );
		var mtType = document.getElementById( 'i_miTypeType' );
		var mtInfoLink = document.getElementById( 'miTypeInfoLink' );


		if ( mtn != lastMiTypeNumber ) {
			mtInfoLink.href = '' ;
			mtInfoLink.style.display = '' ;
			mtTitle.value = '' ;
			mtType.value = '' ;
		}
	}

	/*var miTypeData = {
		__value : ''
	};

	function getTypeDataValue() {
		return this.__value ;
	}
	function setTypeDataValue( newValue ) {
		this.__value = newValue ;
	}

	Object.defineProperty( miTypeData , 'value' , {
		configurable : true ,
		get : getTypeDataValue ,
		set : setTypeDataValue
	} );*/

	function checkARSHIN() {

		var levels = [ {
			style : 'big-wo-num' ,
			max : 10
		} , {
			style : 'big-wo-num' ,
			max : 10
		} , {
			style : 'big-wo-num' ,
			max : 10
		} ];
		var smd = new $.TDLGProgressBar( levels );

		var res = smd.show();

		setInterval( function() {
			var i = 0 ;
			return function() {
				levels[ 0 ].setProgress( i % 101 );
				levels[ 1 ].setProgress( ( ( i - ( i % 5 ) ) / 5 ) % 101 );
				levels[ 2 ].setProgress( ( ( i - ( i % 25 ) ) / 25 ) % 101 );
				i++ ;
			};
		}() , 25 );

		return ;
		//
		var firstYear = ( new Date() ).getFullYear();
		var lastYear = 2010 ;

		var mn = document.getElementById( 'i_manufactureNumber' );
		if ( mn ) {
			mn = mn.value ;
		} else {
			return ;
		}

		var mtn = document.getElementById( 'i_miTypeNumber' );
		if ( mtn ) {
			mtn = mtn.value ;
		} else {
			return ;
		}

		var mtTitle = document.getElementById( 'i_miTypeTitle' );
		var mtType = document.getElementById( 'i_miTypeType' );
		var mtInfoLink = document.getElementById( 'miTypeInfoLink' );

		if ( mtn == '' ) {
			mtn = '*' ;
		} else {
			sendAsync(
				'https://base.vrcse.ru/tools/proxy/arshin/type/data?orgID=CURRENT_ORG&filterBy=foei:NumberSI&filterValues=' + mtn ,
				{} ,
				function( json ) {
					var map = {
						'foei:NameSI' : mtTitle ,
						'foei:DesignationSI' : mtType ,
					};
					var item ;
					switch ( json.result.items.length ) {
						case 0 :
							alert( 'Не найдено сведений о типе СИ по номеру ' + mtn );
							return ;
							break ;

						case 1 :
							item =  json.result.items[ 0 ];
							break ;

						default :
							alert( 'По номеру ' + mtn + ' найдено несколько записей о типе СИ' );
							return ;
							break ;
					}

					for( var i = 0 ; i < item.properties.length ; i++ ) {
						var prop = item.properties[ i ];
						if ( map[ prop.name ] ) {
							map[ prop.name ].value = prop.value ;
						}
					}
					var fgisLink = 'https://fgis.gost.ru/fundmetrology/registry/4/items/' + item.id ;
					mtInfoLink.href = fgisLink ;
					mtInfoLink.innerText = '>>' ;
					mtInfoLink.style.display = 'inline' ;
				}
			);
		}

		var allReq = {
			prF : [] ,
			prR : []
		};
		for( var y = firstYear , i = 0  ; y >= lastYear ; y-- , i++ ) {
			var tmpPr = new Promise( function( year , n , tn , timeout ) {
				return function( resolve , reject ) {
					setTimeout( function() {
						var tmpf = fetch( 'https://base.vrcse.ru/tools/proxy/arshin/verification/select?fq=verification_year:' + year + '&fq=mi.number:' + encodeURIComponent( n ) + '&fq=mi.mitnumber:' + encodeURIComponent( tn ) + '&fl=vri_id,org_title,mi.mitnumber,mi.mititle,mi.mitype,mi.modification,mi.number,verification_date,valid_date,applicability,result_docnum&q=*&rows=1000' );
						tmpf.then( function( req ){ resolve( req ); } );
					} , timeout );
				}
			} ( y , mn , mtn , 1000 * i ) );

			allReq.prF.push( tmpPr );

			//var tmpf = fetch( 'https://fgis.gost.ru/fundmetrology/cm/xcdb/vri/select?fq=verification_year:' + y + '&fq=mi.number:' + encodeURIComponent( mn ) + '&fq=mi.mitnumber:' + encodeURIComponent( mtn ) + '&fl=vri_id,org_title,mi.mitnumber,mi.mititle,mi.mitype,mi.modification,mi.number,verification_date,valid_date,applicability,result_docnum&q=*&rows=1000' );
			//38321-08
		}

		Promise.allSettled( allReq.prF ).then( function( ar ) {
			return function ( requests ) {
				for( var i = 0 ; i < requests.length ; i++ ) {
					var tmpj = requests[ i ].value.json();
					ar.prR.push( tmpj );
				}

				Promise.allSettled( ar.prR ).then(
					function( results ) {
						var f = mk_makeElement( "equipment--" );
						console.log( results );
						var list = [];
						var map = {};
						for( var i = 0 ; i < results.length ; i++ ) {
							if ( results[ i ].status == 'fulfilled' ) {
								var tmpList = results[ i ].value.response.docs;
								//list = list.concat(  );
								for( var j = 0 ; j < tmpList.length ; j++ ) {
									var doc = tmpList[ j ];
									var mitNumber = doc[ 'mi.mitnumber' ];
									if ( !map[ mitNumber ] ) {
										map[ mitNumber ] = {
											'mi.mitnumber' : mitNumber ,
											docs : []
										};
										list.push( map[ mitNumber ] );
									}
									map[ mitNumber ].docs.push( doc );
								}
							}
						}

						list.sort( function( a , b ) {
							return b.docs.length - a.docs.length ;
						} );

						var smd = new $.TDLGSimpleMenu( list , { title : 'test' , itemMkFunc : function( mkElem , mkDiv ) {
								return function( item ) {
									/*var res = mkDiv( "item" );
									var n = mkDiv( "name" );
									n.appendChild( document.createTextNode( item[ 'mi.mititle' ] ) );
									res.appendChild( n );
									var ed = mkDiv( "ex-data" );
									var inn = mkDiv( "inn" );
									inn.appendChild( document.createTextNode( item[ 'mi.mitnumber' ] ) );
									ed.appendChild( inn );
									var addr = mkDiv( "addr" );
									addr.appendChild( document.createTextNode( item[ 'org_title' ] ) );
									ed.appendChild( addr );
									res.appendChild( ed );*/


									var res = mkDiv( "item" );
									var n = mkDiv( "name" );
									n.appendChild( document.createTextNode( item[ 'mi.mitnumber' ] ) );
									res.appendChild( n );
									var ed = mkDiv( "ex-data" );
									var inn = mkDiv( "inn" );
									inn.appendChild( document.createTextNode( item.docs.length ) );
									ed.appendChild( inn );
									var addr = mkDiv( "addr" );
									//addr.appendChild( document.createTextNode( item[ 'org_title' ] ) );
									ed.appendChild( addr );
									res.appendChild( ed );

									return res ;
								};
							}( f , f ) } );

						var res = smd.show().then( function ( item ) { console.log( item ); } );
						//dom.appendChild( document.createTextNode( j.response.docs.length > 0 ? j.response.docs.length + ' : ' + JSON.stringify( j.response.docs[ 0 ] ) : '-' ) );*/
					}
				);
			};
		}( allReq ) );


	/*.then(
			function( resp ) {
				reqArr.pr.push( resp.json() );
			}
		);*/

		/*var res = smd.show().then( function( om ) {
			return function ( item ) {
				console.log( item );
				return {
					om : om ,
					value : item.value
				};
			};
		}( origMatch ) );*/


	}

	