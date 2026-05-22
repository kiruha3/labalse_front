
	class TBCEChartPanel extends HTMLElement {
		static #__template = false ;
		static get TEMPLATE() {
			if ( !this.#__template ) {
				this.#__template = document.getElementById( 'template--ChartPanel' );
			}
			return this.#__template ;
		}
		
		static get TRANSFORMATION_GROUP() { return 'group' };
		static get TRANSFORMATION_FLOATING_GROUP() { return 'floating-group' };
		
		static get VIEWSTYLE_BAR_SORTED() { return 'bar-sorted' };
		static get VIEWSTYLE_BAR_ALONGSIDE() { return 'bar-alongside' };
		
		#__dom ;
		#__shadowRoot ;
		#__created ;
		#__internals ;
		
		#__data ;
		#__transformedData ;
		#__transformations ;
		#__currentTransformation ;
		#__context2d ;
		
		#__contentOldWidth ;
		#__contentOldHeight ;
		
		#__canvasCurrentWidth ;
		#__canvasCurrentHeight ;
		
		onTransformSelect ;
		
		constructor() {
			super();
			
			this.#__dom = {};
			this.#__shadowRoot = false ;
			this.#__created = false ;
			this.#__internals = false ;
			
			this.#__data = false ;
			this.#__transformedData = false ;
			this.#__transformations = [];
			this.#__currentTransformation = false ;
			
			this.#__context2d = false ;
			
			this.#__canvasCurrentWidth = false ;
			this.#__canvasCurrentHeight = false ;
			
			this.onTransformSelect = null ;
		}
		
		addTransformation( label , type , viewStyle , options ) {
			const id = 'transformation--' + generateGUID();
			this.#__transformations[ id ] = {
				label ,
				type ,
				viewStyle ,
				options
			};
			const dom = this.#__dom ;
			const htmlOption = new Option( label , id );
			dom.transformationsSelect.add( htmlOption );
			if ( !this.#__currentTransformation ) {
				this.#__currentTransformation = id ;
				this.diagramRedraw();
			}
			return id ;
		}
		
		get data() {
			return this.#__data ;
		}
		
		set data( v ) {
			if ( v instanceof Array ) {
				this.#__data = v ;
				this.dataset.loaded = 'loaded' ;
				this.#__transformedData = false ;
				this.diagramRedraw();
				console.log( 'data loaded' );
			} else {
				this.#__data = false ;
				this.dataset.loaded = '' ;
				this.diagramRedraw();
			}
		}
		
		dataTransform() {
			if ( !this.#__data ) {
				return ;
			}
			const data = this.#__data ;
			
			if ( !this.#__currentTransformation ) {
				return ;
			}
			if ( !this.#__transformations[ this.#__currentTransformation ] ) {
				return ;
			}
			
			this.#__transformedData = false ;
			
			const tr = this.#__transformations[ this.#__currentTransformation ];
			const tro = tr.options ;
			const result = Object.assign( {} , {
				x_value_min : false ,
				x_value_max : false ,
				y_value_min : false ,
				y_value_max : false ,
				
				x_grid_step : 1 ,
				y_grid_step : 1 ,
				
				x_axis_name : '' ,
				y_axis_name : '' ,
				
				x_value_labels : function( v ) { return v ; } ,
				y_value_labels : function( v ) { return v ; } ,
				
				
				dataLines : [ {
					lineID : 'default'
				} ] ,
				dataLinesLegend : {} ,
				
				transformation : tr
			} , tro );
			
			this.#__transformedData = result ;
			switch( tr.type ) {
				case TBCEChartPanel.TRANSFORMATION_GROUP :
					const dataLines = result.dataLines ;
					const defDataLine = 'default' ;
					const vm = result.points = {};
					for( const l of dataLines ) {
						vm[ 'le:' + l.lineID ] = {};
					}
					if ( tro.x_groups === 'auto' ) {
						const xgr = {};
						const xvf = tro.x_value ;
						for( const p of data ) {
							const lx = xvf( p );
							if ( lx === false ) {
								continue ;
							}
							
							let lList ;
							if ( typeof lx.l === 'undefined' ) {
								lList = [ defDataLine ];
							} else
							if ( typeof lx.l === 'string' || typeof lx.l === 'number' || typeof lx.l === 'symbol' ) {
								lList = [ lx.l ];
							} else
							if ( typeof lx.l === 'object' && lx.l instanceof Array ) {
								lList = lx.l ;
							} else {
								lList = [];
							}
							
							for( const l of lList ) {
								const lid = 'le:' + l ;
								if ( typeof vm[ lid ] === 'undefined' ) {
									continue ;
								}
								const lp = vm[ lid ];
								const gid = 'ge:' + lx.v ;
								xgr[ gid ] = true ;
								if ( typeof lp[ gid ] === 'undefined' ) {
									lp[ gid ] = { x : lx.v , y : 0 };
								}
								lp[ gid ].y++ ;
							}
						}
						result.x_groups_real = Object.keys( xgr ).map( k => k.substring( 3 ) );
					} else {
						result.x_groups_real = tro.x_groups.slice();
						for( const l of dataLines ) {
							const lid = 'le:' + l.lineID ;
							const lp = vm[ lid ];
							for( const ge of result.x_groups_real ) {
								const gid = 'ge:' + ge ;
								lp[ gid ] = { x : ge , y : 0 };
							}
						}
						
						const xvf = tro.x_value ;
						for( const p of data ) {
							const lx = xvf( p );
							if ( lx === false ) {
								continue ;
							}
							
							let lList ;
							if ( typeof lx.l === 'undefined' ) {
								lList = [ defDataLine ];
							} else
							if ( typeof lx.l === 'string' || typeof lx.l === 'number' || typeof lx.l === 'symbol' ) {
								lList = [ lx.l ];
							} else
							if ( typeof lx.l === 'object' && lx.l instanceof Array ) {
								lList = lx.l ;
							} else {
								lList = [];
							}
							
							for( const l of lList ) {
								const lid = 'le:' + l ;
								if ( typeof vm[ lid ] === 'undefined' ) {
									continue ;
								}
								const lp = vm[ lid ];
								const gid = 'ge:' + lx.v ;
								if ( typeof lp[ gid ] !== 'undefined' ) {
									lp[ gid ].y++ ;
								}
							}
						}
					}
					console.log( vm );
					break ;
			}
		}
		
		#__dataTransform_Assort() {
		
		}
		
		#__diagramRedraw_XAxis_Prepare( data , dataRect , aX ) {
			aX.pos = 'bottom' ;
			aX.labelsSide = 'bottom' ;
			if ( dataRect.yMax <= 0 ) {
				aX.pos = 'top' ;
				aX.labelsSide = 'top' ;
			} else
			if ( dataRect.yMin < 0 ) {
				aX.pos = 'center' ;
				if ( Math.abs( dataRect.yMin ) > dataRect.yMax ) {
					aX.labelsSide = 'top' ;
				}
			}
			const labels = [];
			let tmpF = data.x_value_labels ?? function( v ) {
				return v ;
			};
			
			if ( data.transformation.type === TBCEChartPanel.TRANSFORMATION_GROUP ) {
				const gr = data.x_groups_real ;
				aX.vMin = dataRect.xMin ;
				aX.vMax = dataRect.xMax ;
				aX.vLength = gr.length ;
				for( let gri = 0 ; gri < gr.length ; gri++ ) {
					const grID = gr[ gri ];
					labels.push( {
						text : tmpF( grID ) ,
						v_pos : gri + 0.5
					} );
				}
			} else {
				const vMin = aX.vMin = dataRect.xMin ;
				const vMax = aX.vMax = dataRect.xMax ;
				const gridStep = data.x_grid_step ;
				labels.push( {
					text : tmpF( vMin ) ,
					v_pos : vMin
				} );
				let tmp = vMin + gridStep ;
				tmp = tmp - tmp % gridStep ;
				while ( tmp <= vMax ) {
					labels.push( {
						text : tmpF( tmp ) ,
						v_pos : tmp
					} );
					tmp+= gridStep ;
				}
			}
			console.log( labels );
			aX.labels = labels ;
		}
		
		#__diagramRedraw_YAxis_Prepare( data , dataRect , aY ) {
			const vMin = aY.vMin = dataRect.yMin ;
			const vMax = aY.vMax = dataRect.yMax ;
			let gridStep ;
			if ( data.y_grid_step === 'auto' ) {
				const tmpS = ( vMax - vMin )  / 10 ;
				if ( Math.abs( tmpS ) <= 1 ) {
					let tmpK = 1 ;
					let cTmpS = tmpS ;
					while ( Math.abs( tmpS * tmpK ) < 1 ) {
						tmpK *= 10 ;
					}
					gridStep = Math.round( tmpS * tmpK * 10 ) / ( tmpK * 10 );
				} else {
					let tmpK = 1 ;
					let cTmpS = tmpS ;
					while ( Math.abs( tmpS / tmpK ) > 1 ) {
						tmpK *= 10 ;
					}
					gridStep = Math.ceil( tmpS / tmpK ) * tmpK ;
				}
			} else {
				gridStep = data.y_grid_step ;
			}
			aY.pos = 'left' ;
			aY.labelsSide = 'left' ;
			if ( dataRect.xMax <= 0 ) {
				aY.pos = 'right' ;
				aY.labelsSide = 'right' ;
			} else
			if ( dataRect.xMin < 0 ) {
				aY.pos = 'center' ;
				if ( Math.abs( dataRect.xMin ) > dataRect.xMax ) {
					aY.labelsSide = 'right' ;
				}
			}
			
			const labels = [];
			let tmpF = data.y_value_labels ?? function( v ) {
				return v ;
			};
			labels.push( {
				text : tmpF( vMin ) ,
				r_pos : vMin
			} );
			let tmp = vMin + gridStep ;
			tmp = tmp - tmp % gridStep ;
			while ( tmp <= vMax ) {
				labels.push( {
					text : tmpF( tmp ) ,
					v_pos : tmp
				} );
				tmp+= gridStep ;
			}
			console.log( labels );
			aY.labels = labels ;
		}
		
		#__diagramRedraw_XAxis_GetHeight( aX , ctx , pals ) {
			ctx.font = pals.font ;
			let xLabelsMaxHeight = 0 ;
			aX.labels.forEach( l => {
				const r = ctx.measureText( l.text );
				l.width = r.width ;
				l.height = r.actualBoundingBoxAscent + r.actualBoundingBoxDescent ;
				l.mtr = r ;
				xLabelsMaxHeight = Math.max( xLabelsMaxHeight , l.height );
			} );
			
			aX.labelsHeight = xLabelsMaxHeight ;
			console.log( aX );
			return xLabelsMaxHeight ;
		}
		
		#__diagramRedraw_YAxis_GetWidth( aY , ctx , pals ) {
			ctx.font = pals.font ;
			let yLabelsMaxWidth = 0 ;
			aY.labels.forEach( l => {
				const r = ctx.measureText( l.text );
				l.width = r.width ;
				l.height = r.actualBoundingBoxAscent + r.actualBoundingBoxDescent ;
				l.mtr = r ;
				yLabelsMaxWidth = Math.max( yLabelsMaxWidth , l.width );
			} );
			
			aY.labelsWidth = yLabelsMaxWidth ;
			console.log( aY );
			return yLabelsMaxWidth ;
		}
		
		#__diagramRedraw_Legend_Prepare( data , legend ) {
			const style = legend.style = getComputedStyle( this.#__dom.partDataLinesLegend );
			legend.spacer               = readCssNumberProp( style.getPropertyValue( '--line-to-line-space' )      , 'px' , 10 );
			legend.markToTextSpace      = readCssNumberProp( style.getPropertyValue( '--mark-to-text-space' )      , 'px' , 10 );
			legend.rowToRowSpace        = readCssNumberProp( style.getPropertyValue( '--row-to-row-space' )        , 'px' , 0 );
			legend.legendToDiagramSpace = readCssNumberProp( style.getPropertyValue( '--legend-to-diagram-space' ) , 'px' , 10 );
			legend.position = style.getPropertyValue( '--position' ).trim();
			
			/*const lineComputedStyles = {};
			for( const l of dataLines ) {
				lineComputedStyles[ 'le:' + l.index ] = getComputedStyle( dom.partDataLines[ l.index ] );
			}*/
			
			const dataLines = data.dataLines ;
			legend.lines = [];
			for( let li = 0 ; li < dataLines.length ; li++ ) {
				const l = dataLines[ li ];
				legend.lines.push( {
					index : li ,
					lineID : l.lineID ,
					text : typeof l.name === 'undefined' ? l.lineID : l.name
				} );
			}
		}
		
		#__diagramRedraw_Legend_Size( legend , drawingArea , ctx ) {
			//console.log( legend );
			const size = legend.size = {
				width : 0 ,
				height : 0
			};
			if ( legend.lines.length === 1 && legend.lines[ 0 ].lineID === 'default' ) {
				return size ;
			}
			
			ctx.font = legend.style.font ;
			const cftm = ctx.measureText( '0' );
			const lineHeight = legend.textLineHeight = cftm.fontBoundingBoxAscent + cftm.fontBoundingBoxDescent ;
			let sizeLimit = false ;
			const dataLines = legend.lines ;
			dataLines.forEach( l => {
				const r = ctx.measureText( l.text );
				l.textWidth = Math.round( r.width );
				l.textHeight = Math.round( r.actualBoundingBoxAscent + r.actualBoundingBoxDescent );
				l.textMtr = r ;
			} );
			switch( legend.position ) {
				case 'top' :
				case 'bottom' :
					sizeLimit = drawingArea.width ;
					let cRow = {
						l : [] ,
						w : 0
					};
					const rows = legend.textRows = [];
					rows.push( cRow );
					for( let li = 0 ; li < dataLines.length ; ) {
						const l = dataLines[ li ];
						const lfw = lineHeight + legend.markToTextSpace + l.textWidth ;
						if ( cRow.w === 0 ) {
							cRow.l.push( l );
							cRow.w += lfw ;
							li++ ;
						} else {
							if ( cRow.w + legend.spacer + lfw <= sizeLimit ) {
								cRow.l.push( l );
								cRow.w += legend.spacer + lfw ;
								li++ ;
							} else {
								cRow = {
									l : [] ,
									w : 0
								};
								rows.push( cRow );
							}
						}
					}
					size.width = Math.min( Math.max.apply( null , rows.map( i => i.w ) ) , sizeLimit );
					size.height = lineHeight * rows.length + legend.rowToRowSpace * ( rows.length - 1 ) + legend.legendToDiagramSpace ;
					break ;
					
				case 'left' :
				case 'right' :
					sizeLimit = drawingArea.height ;
					break ;
			}
			return size ;
		}
		
		#__diagramRedraw_Legend( legend , drawingArea , constatns , ctx ) {
			//console.log( legend );
			if ( legend.lines.length === 1 && legend.lines[ 0 ].lineID === 'default' ) {
				return ;
			}
			
			/*
			// dbg
			
			ctx.strokeStyle = '#000' ;
			ctx.lineWidth = 1 ;
			ctx.strokeRect( legend.rLeft , legend.rTop , legend.size.width , legend.size.height );
			 */
			
			
			
			ctx.font = legend.style.font ;
			const lineHeight = legend.textLineHeight ;
			let sizeLimit = false ;
			
			const dom = this.#__dom ;
			const dataLines = legend.lines ;
			const lineComputedStyles = {};
			for( const l of dataLines ) {
				lineComputedStyles[ 'le:' + l.index ] = getComputedStyle( dom.partDataLines[ l.index ] );
			}
			
			switch( legend.position ) {
				case 'top' :
				case 'bottom' :
					const rows = legend.textRows ;
					const yStep = lineHeight + legend.rowToRowSpace ;
					for( let ri = 0 ; ri < rows.length ; ri++ ) {
						const cRow = rows[ ri ];
						const cy = ri * yStep + legend.rTop ;
						const cyt = cy + lineHeight ;
						let cx = legend.rLeft + Math.round( ( legend.size.width - cRow.w ) / 2 ) ;
						for( const l of cRow.l ) {
							const cs = lineComputedStyles[ 'le:' + l.index ];
							ctx.strokeStyle = cs.borderColor ;
							ctx.fillStyle = cs.backgroundColor ;
							ctx.lineWidth = readCssNumberProp( cs.borderWidth , 'px' , 1 );
							ctx.fillRect( cx , cy , lineHeight , lineHeight );
							ctx.strokeRect( cx , cy , lineHeight , lineHeight );
							cx += lineHeight + legend.markToTextSpace ;
							ctx.fillStyle = legend.style.color ;
							ctx.fillText( l.text , cx , cyt - Math.round( ( lineHeight - l.textHeight ) / 2 ) );
							cx += l.textWidth + legend.spacer ;
						}
					}
					break ;
				
				case 'left' :
				case 'right' :
					sizeLimit = drawingArea.height ;
					break ;
			}
		}
		
		#__diagramRedraw_Grid( aX , aY , drawingArea , constatns , ctx , pals ) {
			const diagramArea = drawingArea.diagramRect ;
			
			ctx.lineWidth = 1 ;
			ctx.strokeStyle = '#ccc' ;
			let tmp1 = diagramArea.bottom ;
			let tmp2 = ( diagramArea.height - 2 * constatns.AXIS_ARROW_LENGTH ) / ( aY.vMax - aY.vMin );
			ctx.beginPath();
			aY.labels.forEach( l => {
				const gridHLineYPos = Math.round( tmp1 - ( ( l.v_pos - aY.vMin ) * tmp2 ) );
				ctx.moveTo( diagramArea.left , gridHLineYPos );
				ctx.lineTo( diagramArea.right , gridHLineYPos );
			} );
			ctx.stroke();
		}
		
		#__diagramRedraw_Data( data , dataRect , aX , aY , diagramRect , constatns , ctx ) {
			const tr = this.#__transformations[ this.#__currentTransformation ];
			
			/*const aXvL = aX.vMax - aX.vMin ;
			const aYvL = aY.vMax - aY.vMin ;
			
			const points = data.points ;*/
			
			switch( tr.viewStyle ) {
				case TBCEChartPanel.VIEWSTYLE_BAR_SORTED :
					this.#__diagramRedraw_Data_BAR_SORTED.apply( this , arguments );
					break ;
					
				case TBCEChartPanel.VIEWSTYLE_BAR_ALONGSIDE :
					this.#__diagramRedraw_Data_BAR_ALONGSIDE.apply( this , arguments );
					break ;
			}
		}
		
		#__diagramRedraw_Data_BAR_SORTED( data , dataRect , aX , aY , diagramRect , constatns , ctx ) {
			const aYvL = aY.vMax - aY.vMin ;
			
			const points = data.points ;
			const dom = this.#__dom ;
			const groupsReal = data.x_groups_real ;
			const dataLines = data.dataLines ;
			const barStyles = {};
			for( let li = 0 ; li < dataLines.length ; li++ ) {
				const l = dataLines[ li ];
				barStyles[ 'le:' + l.lineID ] = getComputedStyle( dom.partDataLines[ li ] );
			}
			const barAreaWidth = Math.floor( diagramRect.width / groupsReal.length );
			const barSpaceHalfWidth = Math.max( Math.floor( barAreaWidth * 0.05 ) , 1 );
			const barWidth = Math.round( barAreaWidth - 2 * barSpaceHalfWidth );
			//debugger ;
			const tmp1 = diagramRect.width / groupsReal.length ;
			const tmp2 = diagramRect.height / aYvL ;
			const xOffset = diagramRect.left + barSpaceHalfWidth ;
			for( let gri = 0 ; gri < groupsReal.length ; gri++ ) {
				const grID = 'ge:' + groupsReal[ gri ];
				const cx = Math.round( gri * tmp1 );
				const bar = [];
				for( const l of dataLines ) {
					const lid = 'le:' + l.lineID ;
					const lp = points[ lid ];
					const p = lp[ grID ];
					const cy = Math.round( p.y * tmp2 );
					bar.push( {
						lid ,
						cy ,
						v : p.y
					} );
				}
				
				bar.sort( function( a , b ) {
					return b.cy - a.cy ;
				} );
				
				for( const b of bar ) {
					const ls = barStyles[ b.lid ];
					ctx.strokeStyle = ls.borderColor ;
					ctx.fillStyle = ls.backgroundColor ;
					ctx.lineWidth = readCssNumberProp( ls.borderWidth , 'px' , 1 );
					ctx.fillRect( cx + xOffset , diagramRect.bottom - b.cy , barWidth , b.cy );
					ctx.strokeRect( cx + xOffset , diagramRect.bottom - b.cy , barWidth , b.cy );
					ctx.font = ls.font ;
					//const dataLabel = b.v ;
					//const tr = ctx.measureText( dataLabel );
				}
			}
		}
		
		#__diagramRedraw_Data_BAR_ALONGSIDE( data , dataRect , aX , aY , diagramRect , constatns , ctx ) {
			const aYvL = aY.vMax - aY.vMin ;
			const points = data.points ;
			const dom = this.#__dom ;
			const groupsReal = data.x_groups_real ;
			const dataLines = data.dataLines ;
			const barStyles = {};
			for( let li = 0 ; li < dataLines.length ; li++ ) {
				const l = dataLines[ li ];
				barStyles[ 'le:' + l.lineID ] = getComputedStyle( dom.partDataLines[ li ] );
			}
			const barAreaWidth = Math.floor( diagramRect.width / groupsReal.length );
			const barSpaceHalfWidth = Math.max( Math.floor( barAreaWidth * 0.05 ) , 1 );
			const barGroupWidth = barAreaWidth - 2 * barSpaceHalfWidth ;
			const barWidth = Math.floor( barGroupWidth / dataLines.length );
			//debugger ;
			const tmp1 = ( diagramRect.width - 2 * constatns.AXIS_ARROW_LENGTH ) / groupsReal.length ;
			const tmp2 = ( diagramRect.height - 2 * constatns.AXIS_ARROW_LENGTH ) / aYvL ;
			const xOffset = diagramRect.left + barSpaceHalfWidth /*+ Math.round( 10 * Math.sin( ( ( new Date() ).getTime() % 1000 ) * 2 * Math.PI / 200 ) )*/;
			for( let gri = 0 ; gri < groupsReal.length ; gri++ ) {
				const grID = 'ge:' + groupsReal[ gri ];
				const cx = Math.round( gri * tmp1 );
				let dlxo = 0 ;
				for( const l of dataLines ) {
					const lid = 'le:' + l.lineID ;
					const lp = points[ lid ];
					const p = lp[ grID ];
					const cy = Math.round( p.y * tmp2 );
					
					const ls = barStyles[ lid ];
					ctx.strokeStyle = ls.borderColor ;
					ctx.fillStyle = ls.backgroundColor ;
					ctx.lineWidth = readCssNumberProp( ls.borderWidth , 'px' , 1 );
					ctx.fillRect( cx + xOffset + dlxo , diagramRect.bottom - cy , barWidth , cy );
					ctx.strokeRect( cx + xOffset + dlxo , diagramRect.bottom - cy , barWidth , cy );
					//ctx.font = ls.font ;
					dlxo += barWidth ;
				}
			}
		}
		
		#__diagramRedraw_XAxis( aX , drawingArea , constatns , ctx , pals ) {
			let ap ;
			let lp ;
			const diagramArea = drawingArea.diagramRect ;
			if ( aX.labelsSide === 'bottom' ) {
				ap = aX.rBottom - Math.round( aX.labelsHeight ) - constatns.DiA_X_LABELS_TO_AXIS_SPACE ;
				lp = ap + aX.labelsHeight + constatns.DiA_X_LABELS_TO_AXIS_SPACE ;
			} else {
				ap = aX.rBottom ;
				lp = aX.rBottom ;
			}
			
			ctx.font = pals.font ;
			ctx.lineWidth = 1 ;
			ctx.fillStyle = pals.color ;
			ctx.font = pals.font ;
			let tmp1 = diagramArea.left ;
			let tmp2 = ( diagramArea.width - 2 * constatns.AXIS_ARROW_LENGTH ) / aX.vLength ;
			aX.labels.forEach( l => {
				const gridVLineXPos = Math.round( tmp1 + ( ( l.v_pos - aX.vMin ) * tmp2 ) );
				ctx.fillText( l.text , Math.round( gridVLineXPos - l.width / 2 ) , lp );
			} );
			
			//—ама ось
			ctx.strokeStyle = '#000' ;
			ctx.beginPath();
			ctx.moveTo( diagramArea.left                                , ap );
			ctx.lineTo( diagramArea.right                               , ap );
			ctx.moveTo( diagramArea.right                               , ap );
			ctx.lineTo( diagramArea.right - constatns.AXIS_ARROW_LENGTH , ap - constatns.AXIS_ARROW_HALF_WIDTH );
			ctx.moveTo( diagramArea.right                               , ap );
			ctx.lineTo( diagramArea.right - constatns.AXIS_ARROW_LENGTH , ap + constatns.AXIS_ARROW_HALF_WIDTH );
			ctx.stroke();
		}
		
		#__diagramRedraw_YAxis( aY , drawingArea , constatns , ctx , pals ) {
			let ap ;
			let lp ;
			const diagramArea = drawingArea.diagramRect ;
			if ( aY.labelsSide === 'left' ) {
				ap = aY.rLeft + Math.round( aY.labelsWidth ) + constatns.DiA_Y_LABELS_TO_AXIS_SPACE ;
				lp = aY.rLeft ;
			} else {
				ap = aY.rLeft ;
				lp = ap + constatns.DiA_Y_LABELS_TO_AXIS_SPACE ;
			}
			
			ctx.font = pals.font ;
			ctx.lineWidth = 1 ;
			ctx.fillStyle = pals.color ;
			ctx.font = pals.font ;
			let tmp1 = diagramArea.bottom ;
			let tmp2 = ( diagramArea.height - 2 * constatns.AXIS_ARROW_LENGTH ) / ( aY.vMax - aY.vMin );
			aY.labels.forEach( l => {
				const gridHLineYPos = Math.round( tmp1 - ( ( l.v_pos - aY.vMin ) * tmp2 ) );
				ctx.fillText( l.text , lp + aY.labelsWidth - l.width , Math.round( gridHLineYPos + l.height / 2 ) );
			} );
			
			//—ама ось
			ctx.strokeStyle = '#000' ;
			ctx.beginPath();
			ctx.moveTo( ap                                   , diagramArea.bottom );
			ctx.lineTo( ap                                   , diagramArea.top );
			ctx.moveTo( ap                                   , diagramArea.top );
			ctx.lineTo( ap - constatns.AXIS_ARROW_HALF_WIDTH , diagramArea.top + constatns.AXIS_ARROW_LENGTH );
			ctx.moveTo( ap                                   , diagramArea.top );
			ctx.lineTo( ap + constatns.AXIS_ARROW_HALF_WIDTH , diagramArea.top + constatns.AXIS_ARROW_LENGTH );
			ctx.stroke();
		}
		
		#__diagramRedraw_getDataRect( data ) {
			const r = {};
			for( const v of strexp('{x,y}M{in,ax}') ) {
				r[ v ] = [];
			}
			console.log( data );
			for( const l of data.dataLines ) {
				const points = data.points[ 'le:' + l.lineID ];
				let pointsX ;
				let pointsY ;
				if ( data.transformation.type === TBCEChartPanel.TRANSFORMATION_GROUP ) {
					pointsX = [ 0 , data.x_groups_real.length - 1 ];
					pointsY = [];
					for( const k in points ) {
						pointsY.push( points[ k ].y );
					}
				} else {
					pointsX = points.map( p => p.x );
					pointsY = points.map( p => p.y );
				}
				r.xMin.push( Math.min( ...pointsX ) );
				r.xMax.push( Math.max( ...pointsX ) );
				r.yMin.push( Math.min( ...pointsY ) );
				r.yMax.push( Math.max( ...pointsY ) );
			}
			
			r.xMin = Math.min( ...r.xMin );
			r.xMax = Math.max( ...r.xMax );
			r.yMin = Math.min( ...r.yMin );
			r.yMax = Math.max( ...r.yMax );
			
			if ( data.x_value_min !== false ) {
				r.xMin = data.x_value_min ;
			}
			if ( data.x_value_max !== false ) {
				r.xMax = data.x_value_max ;
			}
			if ( data.y_value_min !== false ) {
				r.yMin = data.y_value_min ;
			}
			if ( data.y_value_max !== false ) {
				r.yMax = data.y_value_max ;
			}
			
			r.width  = r.xMax - r.xMin ;
			r.height = r.yMax - r.yMin ;
			
			return r ;
		}
		
		diagramRedraw() {
			if ( !this.#__created ) {
				return ;
			}
			
			if ( !this.#__data ) {
				return ;
			}
			
			if ( !this.#__canvasCurrentWidth || !this.#__canvasCurrentHeight ) {
				return ;
			}
			
			if ( !this.#__transformedData ) {
				this.dataTransform();
			}
			
			if ( !this.#__transformedData ) {
				return ;
			}
			
			const dom = this.#__dom ;
			if ( !dom.partDataLines ) {
				this.dataLineStylesRefresh();
			}
			
			const constants = {
				DA_X_MARGING : 10 ,
				DA_Y_MARGING : 10 ,
				DiA_X_MARGING_RIGHT : 10 ,
				DiA_X_MARGING_LEFT : 10 ,
				DiA_Y_MARGING_TOP : 10 ,
				DiA_Y_MARGING_BOTTOM : 10 ,
				DiA_X_LABELS_TO_AXIS_SPACE : 5 ,
				DiA_Y_LABELS_TO_AXIS_SPACE : 5 ,
				GRID_H_LINE_AXIS_OVERFLOW : 2 ,
				GRID_V_LINE_AXIS_OVERFLOW : 2 ,
				AXIS_ARROW_LENGTH : 10 ,
				AXIS_ARROW_HALF_WIDTH : 3
			};
			
			const drawingArea = {
				canvasWidth  : this.#__canvasCurrentWidth ,
				canvasHeight : this.#__canvasCurrentHeight ,
			};
			drawingArea.left   = constants.DA_X_MARGING ;
			drawingArea.top    = constants.DA_Y_MARGING ;
			drawingArea.right  = drawingArea.canvasWidth  - constants.DA_X_MARGING ;
			drawingArea.bottom = drawingArea.canvasHeight - constants.DA_Y_MARGING ;
			drawingArea.width  = drawingArea.right  - drawingArea.left ;
			drawingArea.height = drawingArea.bottom  - drawingArea.top ;
			
			const pals = getComputedStyle( dom.partAxisLabels );
			const dc = this.#__context2d ;
			
			const data = this.#__transformedData ;
			let dataRect = this.#__diagramRedraw_getDataRect( data );
			const axisData = {};
			const aX = {};
			const aY = {};
			axisData.x = aX ;
			axisData.y = aY ;
			
			const legend = drawingArea.legend = {};
			
			this.#__diagramRedraw_XAxis_Prepare( data , dataRect , aX );
			this.#__diagramRedraw_YAxis_Prepare( data , dataRect , aY );
			this.#__diagramRedraw_Legend_Prepare( data , legend );
			
			
			dc.translate( 0.5 , 0.5 );
			
			dc.fillStyle = '#fff' ;
			dc.fillRect( 0 , 0 , drawingArea.canvasWidth , drawingArea.canvasHeight );
			
			// dbg
			//dc.strokeStyle = '#880' ;
			//dc.strokeRect( drawingArea.left , drawingArea.top , drawingArea.width , drawingArea.height );
			//
			
			this.#__diagramRedraw_Legend_Size( legend , drawingArea , dc );
			
			const diagramRect = drawingArea.diagramRect = {
				left   : drawingArea.left ,
				top    : drawingArea.top ,
				right  : drawingArea.right ,
				bottom : drawingArea.bottom
			};
			
			switch ( legend.position ) {
				case 'top' :
					legend.rTop = diagramRect.top ;
					diagramRect.top += legend.size.height ;
					break ;
				case 'bottom' :
					diagramRect.bottom -= legend.size.height ;
					legend.rTop = diagramRect.bottom ;
					break ;
				case 'left' :
					legend.rLeft = diagramRect.left ;
					diagramRect.left += legend.size.width ;
					break ;
				case 'right' :
					diagramRect.right -= legend.size.width ;
					legend.rLeft = diagramRect.right ;
					break ;
			}
			
			switch ( legend.position ) {
				case 'top' :
				case 'bottom' :
					legend.rLeft = Math.round( ( drawingArea.left + drawingArea.right - legend.size.width ) / 2 );
					break ;
				case 'left' :
				case 'right' :
					legend.rTop = Math.round( ( drawingArea.top + drawingArea.bottom - legend.size.height ) / 2 );
					break ;
			}
			
			this.#__diagramRedraw_YAxis_GetWidth( aY , dc , pals );
			if ( aY.pos === 'center' ) {
				aY.rLeft = Math.round( Math.abs( dataRect.xMin ) * drawingArea.width / ( dataRect.xMax - dataRect.xMin ) ) + drawingArea.left ;
			} else {
				const yAxisCWidth = Math.ceil( aY.labelsWidth ) + constants.DiA_Y_LABELS_TO_AXIS_SPACE ;
				if ( aY.pos === 'left' ) {
					aY.rLeft = drawingArea.left ;
					diagramRect.left = drawingArea.left + yAxisCWidth ;
					diagramRect.right = drawingArea.right - 2 * constants.AXIS_ARROW_LENGTH ;
				} else {
					diagramRect.right = drawingArea.right - yAxisCWidth - 2 * constants.AXIS_ARROW_LENGTH ;
					aY.rLeft = diagramRect.right ;
				}
			}
			this.#__diagramRedraw_XAxis_GetHeight( aX , dc , pals );
			if ( aX.pos === 'center' ) {
				aX.rBottom = drawingArea.bottom - Math.round( Math.abs( dataRect.yMin ) * drawingArea.height / ( dataRect.yMax - dataRect.yMin ) );
			} else {
				const xAxisCHeight = Math.ceil( aX.labelsHeight ) + constants.DiA_X_LABELS_TO_AXIS_SPACE ;
				if ( aX.pos === 'bottom' ) {
					diagramRect.bottom = drawingArea.bottom - xAxisCHeight ;
					aX.rBottom = drawingArea.bottom ;
				} else {
					aX.rBottom = drawingArea.top + xAxisCHeight ;
					diagramRect.top = aX.rBottom ;
				}
			}
			
			diagramRect.width  = diagramRect.right - diagramRect.left ;
			diagramRect.height = diagramRect.bottom - diagramRect.top ;
			
			this.#__diagramRedraw_Legend( legend , drawingArea , constants , dc );
			
			this.#__diagramRedraw_Grid( aX , aY , drawingArea , constants , dc , pals );
			
			dc.save();
			let clipRect = new Path2D();
			clipRect.rect( diagramRect.left , diagramRect.top , diagramRect.width , diagramRect.height );
			dc.clip( clipRect );
			this.#__diagramRedraw_Data( data , dataRect , aX , aY , diagramRect , constants , dc );
			dc.restore();
			
			this.#__diagramRedraw_XAxis( aX , drawingArea , constants , dc , pals );
			this.#__diagramRedraw_YAxis( aY , drawingArea , constants , dc , pals );
			
			
			dc.translate( -0.5 , -0.5 );
		}
		
		render() {
			const dom = this.#__dom ;
			
			if ( !this.#__shadowRoot ) {
				this.#__shadowRoot = this.attachShadow( { mode : 'closed' } );
			}
			const shadowRoot = this.#__shadowRoot ;
			
			if ( !this.#__created ) {
				shadowRoot.appendChild( TBCEChartPanel.TEMPLATE.content.cloneNode( true ) );
				this.#__internals = this.attachInternals();
				
				dom.transformationsSelect = shadowRoot.querySelector( '#transformations-select' );
				dom.transformationsSelect.addEventListener( 'change' , this.#__onTransformationSelect.bind( this ) );
				dom.diagram = shadowRoot.querySelector( '#diagram' );
				dom.area = shadowRoot.querySelector( '#area' );
				
				dom.scrollerShr = shadowRoot.querySelector( '#scroller-shr' );
				this.#__setScrollInitial( dom.scrollerShr );
				dom.scrollerShr.addEventListener( 'scroll' , this.#__onContentResize.bind( this ) );
				
				dom.scrollerExp = shadowRoot.querySelector( '#scroller-exp' );
				this.#__setScrollInitial( dom.scrollerExp );
				dom.scrollerExp.addEventListener( 'scroll' , this.#__onContentResize.bind( this ) );
				
				dom.variables = shadowRoot.querySelector( '#variables' );
				dom.partAxisLabels = shadowRoot.querySelector( '#element-style #axis-labels' );
				dom.partDataLinesTop = shadowRoot.querySelector( '#element-style #data-lines' );
				dom.partDataLines = false ;
				dom.partDataLinesLegend = shadowRoot.querySelector( '#element-style #data-lines-legend' );
				dom.refreshBtn = shadowRoot.querySelector( '#refresh-btn' );
				dom.refreshBtn.addEventListener( 'click' , this.#__onRefrechBtnClick.bind( this ) );
				
				this.#__context2d = dom.diagram.getContext( '2d' );
				
				this.#__created = true ;
				
				this.diagramRedraw();
			}
		}
		
		dataLineStylesRefresh() {
			if ( !this.#__transformedData ) {
				return false ;
			}
			
			const td = this.#__transformedData ;
			const dom = this.#__dom ;
			const shadowRoot = this.#__shadowRoot ;
			if ( !dom.partDataLines ) {
				dom.partDataLines = [];
			}
			for( let li = 0 ; li < td.dataLines.length ; li++ ) {
				let ePDLi = shadowRoot.querySelector( '#element-style #data-lines #data-line-' + li );
				if ( !ePDLi ) {
					ePDLi = document.createElement( 'div' );
					ePDLi.id = 'data-line-' + li ;
					ePDLi.className = 'data-line' ;
					ePDLi.part = 'data-line-' + li ;
					dom.partDataLinesTop.appendChild( ePDLi );
				}
				dom.partDataLines[ li ] = ePDLi ;
			}
		}
		
		connectedCallback() {
			if ( !this.rendered ) {
				this.render();
				this.rendered = true ;
			}
		}
		
		disconnectedCallback() {
		}
		
		static get observedAttributes() {
			return [ 'data' ];
		}
		
		attributeChangedCallback( name , oldValue , newValue ) {
			switch ( name ) {
				case 'data' :
					this.data = newValue ;
					break ;
			}
		}
		
		adoptedCallback() {
		}
		
		#__onContentResize() {
			if ( !this.#__dom ) {
				return ;
			}
			
			const dom = this.#__dom ;
			if ( dom.area && dom.variables ) {
				dom.variables.innerHTML = ':host {' +
					'--client-width : ' + dom.area.clientWidth + 'px ;' +
					'transition : all 0.5s ;' +
				'}' ;
				
				//console.log( 'width x height : ' + dom.area.clientWidth + ' x ' + dom.area.clientHeight );
				this.#__canvasCurrentWidth  = dom.area.clientWidth ;
				this.#__canvasCurrentHeight = dom.area.clientHeight ;
				dom.diagram.width = this.#__canvasCurrentWidth ;
				dom.diagram.height = this.#__canvasCurrentHeight ;
				this.diagramRedraw();
			}
			
			this.#__setScrollInitial( dom.scrollerShr );
			this.#__setScrollInitial( dom.scrollerExp );
		}
		
		#__setScrollInitial( e ) {
			if ( e ) {
				e.scrollTop = 10000000 ;
				e.scrollLeft = 10000000 ;
			}
		}
		
		#__onRefrechBtnClick() {
			this.diagramRedraw();
		}
		
		#__onTransformationSelect() {
			this.#__transformedData = false ;
			this.#__dom.partDataLines = false ;
			const st = this.#__dom.transformationsSelect.value ;
			if ( !this.#__transformations[ st ] ) {
				return false ;
			}
			
			this.#__currentTransformation = st ;
			this.diagramRedraw();
			if ( typeof this.onTransformSelect === 'function' ) {
				this.onTransformSelect( st );
			}
		}
	}
	
	customElements.define( 'vrcse-chart-panel' , TBCEChartPanel );