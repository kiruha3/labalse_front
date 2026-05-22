
	function mk_xValueFunc( lpList ) {
		return function( item ) {
			const v = item.fin_date * 1000 ;
			const D = new Date( v );
			const l = [];
			const Y = D.getFullYear();
			for( const lp of lpList ) {
				let inLine = ( lp.y === Y );
				if ( !inLine ) {
					continue ;
				}
				if ( lp.t !== 'all' ) {
					for( const f of lp.t ) {
						inLine = inLine & f.fltFunc( item );
						if ( !inLine ) {
							break ;
						}
					}
				}
				
				if ( inLine ) {
					l.push( lp.ID );
				}
			}
			
			return {
				v : D.getMonth() ,
				l
			}
		};
	}
	
	function getLineID( lp ) {
		let r = [ 'y:' + lp.y ];
		if ( lp.t === 'all' ) {
			r.push( 'all' );
		} else {
			for( const f of lp.t ) {
				const kn = f.fltName ;
				if ( f.value instanceof Array ) {
					r.push( kn + ':[' + f.value.join( ',' ) + ']' );
				} else {
					r.push( kn + ':' + f.value );
				}
			}
		}
		return r.join( '@' );
	}
	
	function mkFunc_mkDataLine( workData ) {
		return function( lp ) {
			let name = lp.y + ': ';
			if ( lp.t === 'all' ) {
				name += 'Все';
			} else {
				const rf = [];
				for( const f of lp.t ) {
					switch ( f.fltName ) {
						case 'spec' :
							const sl = [];
							for( const v of f.value ) {
								sl.push( workData.specList[ v ].index + '.' + workData.specList[ v ].num );
							}
							rf.push( 'спец. ' + sl.join( ', ' ) );
							break;
					}
				}
				name += rf.join( ' и ' );
			}
			
			return {
				lineID : generateGUID() , //getLineID( lp ) ,
				name
			};
		}
	}
	
	function mkFunc_tr1opt_MkDataLines( workData ) {
		return function ( lpList , dstOpt ) {
			const filters = workData.filters ;
			const dataLines = dstOpt.dataLines = [];
			const mkDataLine = mkFunc_mkDataLine( workData );
			for( const lp of lpList ) {
				if ( lp.t !== 'all' ) {
					for( const f of lp.t ) {
						f.fltFunc = filters[ f.fltName ]( f.value );
					}
				}
				const cDataLine = mkDataLine( lp );
				dataLines.push( cDataLine );
				lp.ID = cDataLine.lineID ;
			}
			dstOpt.x_value = mk_xValueFunc( lpList );
		}
	}
	
	$.windowOnLoad.push( function() {
		const stdListsRaw = sendGET( false , '/maindb/gp-info.page.diagram-data.ajax.php?mode=ajax&data=get-lists' + '&random=' + ( new Date() ).getTime() + ( Math.random() * 1000 ) );
		const stdLists = JSON.parse( stdListsRaw );
		const workData = $.GP_INFO_PAGE_DATA = {
			dom : {} ,
			specList : stdLists[ 'spec-list' ]
		};
		
		const CCPP = workData.ccpp = {};
		
		const filters = workData.filters = {};
		filters[ 'year' ] = function( year ) {
			return function( item ) {
				const v = item.fin_date * 1000 ;
				const D = new Date( v );
				const Y = D.getFullYear();
				
				return Y === year ;
			}
		};
		filters[ 'spec' ] = function( specIDList ) {
			return function( item ) {
				return specIDList.indexOf( item.spec_id ) >= 0 ;
			}
		};
		filters[ 'dep' ] = function( depIDList ) {
			return function( item ) {
				return depIDList.indexOf( item.dep_id ) >= 0 ;
			}
		};
		
		
		
		/*const tr1LineParams = [
			{
				y : 2020 ,
				t : 'all'
			} , {
				y : 2020 ,
				t : [ { fltName : 'spec' , value : [ 35 , 36 , 37 , 38 , 128 , 153 ] } ] ,
			} , {
				y : 2020 ,
				t : [ { fltName : 'spec' , value : [ 41 , 136 , 127 , 138 ] } ] ,
			}
		];*/
		
		const tr1LineParams = [];
		for( let i = ( new Date() ).getFullYear() - 2 ; i <= ( new Date() ).getFullYear() ; i++ ) {
			tr1LineParams.push( {
				y : i , t : 'all'
			} );
		}
		
		const chartYearStat = workData.dom.chartYearStat = document.getElementById( 'year-stat' );
		chartYearStat.onTransformSelect = function( ccpp ) {
			return function( trID ) {
				controlPanelReload( ccpp[ trID ] );
			}
		} ( CCPP );
		const tr1opt = {
			//x_value : mk_xValueFunc( tr1LineParams ) ,
			x_groups : function() {
				const res = [];
				for( let i = 0 ; i < 12 ; i++ ) {
					res.push( i );
				}
				return res ;
			} () ,
			x_value_labels : function( value ) {
				const m = value ;
				const tmp = new Date( 2000 , m , 1 );
				return formatDate( tmp , '{F1}' );
			} ,
			x_axis_name : '' ,
			y_value_min : 0 ,
			y_grid_step : 50 ,
			y_axis_name : '' ,
			//dataLines :  ,
			dataLinesLegend : {}
		};
		
		const tr1opt_MkDataLines = mkFunc_tr1opt_MkDataLines( workData );
		tr1opt_MkDataLines( tr1LineParams , tr1opt );
		const tr = chartYearStat.addTransformation( 'По месяцам' , TBCEChartPanel.TRANSFORMATION_GROUP , TBCEChartPanel.VIEWSTYLE_BAR_ALONGSIDE , tr1opt );
		
		CCPP[ tr ] = {
			opt : tr1opt ,
			lines : tr1LineParams ,
			mkDataLines : tr1opt_MkDataLines
		}
		
		
		const tr2 = chartYearStat.addTransformation( 'По дням' , TBCEChartPanel.TRANSFORMATION_GROUP , TBCEChartPanel.VIEWSTYLE_BAR_SORTED , {
			x_value : function( item ) {
				const v = item.fin_date ;
				return { v :  v - v % 86400 };
			} ,
			x_value_labels : function( value ) {
				return formatDate( value * 1000 , '{d}.{m}' );
			} ,
			x_groups : 'auto' ,
			x_axis_name : '' ,
			y_value_min : 0 ,
			y_grid_step : 50 ,
			y_axis_name : ''
		} );
		
		const tr3 = chartYearStat.addTransformation( 'По отделам' , TBCEChartPanel.TRANSFORMATION_GROUP , TBCEChartPanel.VIEWSTYLE_BAR_SORTED , {
			x_value : function( item ) {
				return { v : item.dep_id };
			} ,
			x_groups : /*function() {
				const res = [];
				for( let i = 0 ; i < 30 ; i++ ) {
					res.push( i );
				}
				return res ;
			} ()*/ 'auto' ,
			x_value_labels : function( value ) {
				return value ;
			} ,
			x_axis_name : '' ,
			y_value_min : 0 ,
			y_grid_step : 500 ,
			y_axis_name : ''
		} );
		
		fetch( '/maindb/gp-info.page.diagram-data.ajax.php?mode=ajax&data=get-data&ts1=' + Math.round( ( new Date( 2020 , 0 , 1 ) ).getTime() / 1000 ) + '&ts2=' + Math.round( ( new Date( 2024 , 11 , 31 ) ).getTime() / 1000 ) ).then( function( r ){
			//debugger ;
			const dataPr = r.json();
			dataPr.then( res => {
				const data = [];
				const v = res[ 'diagram-data' ];
				for( const k in v ) {
					data.push( v[ k ] );
				}
				chartYearStat.data = data ;
			} );
		} );
		
		controlPanelReload( CCPP[ tr ] );
	} );

	function controlPanelClear() {
		const cp = document.getElementById( 'cart-panel-control' );
		cp.innerHTML = '' ;
	}
	
	function controlPanelMkLine( lineParams , lineParamsIndex , chart , cccpp ) {
		const result = document.createElement( 'div' );
		result.className = 'chart-tr1-line-params-area' ;
		
		const markArea = document.createElement( 'div' );
		markArea.className = 'control-panel-lines-mark-area' ;
			const mark = document.createElement( 'div' );
			mark.className = 'control-panel-lines-def-mark control-panel-line-' + lineParamsIndex + '-mark' ;
			markArea.appendChild( mark );
		result.appendChild( markArea );
		
		const yearSelect = document.createElement( 'select' );
		yearSelect.className = 'chart-tr1-line-params-year' ;
		for( let i = 2024 ; i >= 2020 ; i-- ) {
			const opt = new Option( '' + i , '' + i , false , i === lineParams.y );
			yearSelect.options.add( opt );
		}
		yearSelect.onchange = function( lp , y , chart , ccpp ) {
			return function(){
				lp.y = parseInt( y.value , 10 );
				ccpp.mkDataLines( ccpp.lines , ccpp.opt );
				chart.dataTransform();
				chart.diagramRedraw();
			};
		}( lineParams , yearSelect , chart , cccpp );
		result.appendChild( yearSelect );
		
		let selectedSpecs = [];
		if ( lineParams.t !== 'all' ) {
			for( const f of lineParams.t ) {
				switch( f.fltName ) {
					case 'spec' :
						selectedSpecs = [].concat( f.value );
						break ;
				}
			}
		}
		
		const specSelect = document.createElement( 'select' );
		specSelect.className = '' ;
		specSelect.multiple = true ;
		result.appendChild( specSelect );
		const specList = $.GP_INFO_PAGE_DATA.specList ;
		
		const specArr = [];
		for( const sid in specList ) {
			const s = specList[ sid ];
			specArr.push( s );
		}
		specArr.sort( function( a , b ) {
			if ( a.index == b.index ) {
				return a.num - b.num ;
			} else {
				return a.index - b.index ;
			}
		} );
		
		for( const s of specArr ) {
			const txt = s.index + '.' + s.num ;
			const opt = new Option( txt , s.id , false , selectedSpecs.indexOf( parseInt( s.id ) ) >= 0 );
			specSelect.options.add( opt );
		}
		specSelect.onchange = function( lp , ss , chart , ccpp ) {
			return function() {
				const selectedSpecs = [];
				for( const opt of ss.options ) {
					if ( opt.selected ) {
						selectedSpecs.push( parseInt( opt.value , 10 ) );
					}
				}
				
				const filters = [];
				
				if ( selectedSpecs.length > 0 ) {
					filters.push( {
						fltName : 'spec' ,
						value : selectedSpecs
					} );
				}
				
				if ( lp.t !== 'all' ) {
					for( const f of lp.t ) {
						if ( f.fltName !== 'spec' ) {
							filters.push( f );
						}
					}
				}
				
				if ( filters.length > 0 ) {
					lp.t = filters ;
				} else {
					lp.t = 'all' ;
				}
				
				ccpp.mkDataLines( ccpp.lines , ccpp.opt );
				chart.dataTransform();
				chart.diagramRedraw();
			}
		}( lineParams , specSelect , chart , cccpp );
		return result ;
	}

	function controlPanelReload( cccpp ) {
		const chartYearStat = document.getElementById( 'year-stat' );
		const cp = document.getElementById( 'cart-panel-control' );
		
		controlPanelClear();
		
		for( let lpi = 0 ; lpi < cccpp.lines.length ; lpi++ ) {
			const lineParams = cccpp.lines[ lpi ];
			const lpDom = controlPanelMkLine( lineParams , lpi , chartYearStat , cccpp );
			cp.appendChild( lpDom );
		}
		
		const tmp = document.createElement( 'div' );
		tmp.className = 'btn3' ;
		tmp.appendChild( document.createTextNode( 'Добавить' ) );
		tmp.onclick = function( p , b , l , c , cp ) {
			return function() {
				const np = {
					y : ( new Date() ).getFullYear() ,
					t : 'all'
				};
				l.push( np );
				const lpDom = controlPanelMkLine( np , l.length - 1 , c , cp );
				p.insertBefore( lpDom , b );
				
				cp.mkDataLines( cp.lines , cp.opt );
				c.dataTransform();
				c.dataLineStylesRefresh();
				c.diagramRedraw();
			};
		} ( cp , tmp , cccpp.lines , chartYearStat , cccpp );
		cp.appendChild( tmp );
	}
