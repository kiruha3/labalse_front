<template id="template--ChartPanel">
    <style id="variables">
        :host {
            --client-width : 10000px ;
		}
    </style>
    <style>
		:host {
            display : flex ;
            flex-direction : column ;
        }

        :host(:not([data-loaded="loaded"])) {
			background : linear-gradient( 105deg , #ccc 50% , #fff 51% , #ccc 52% );
			background-size: 300% 300% ;
			animation: chart-loading-data 3s ease infinite ;
		}

		@keyframes chart-loading-data {
			0% {
				background-position: 0 0 ;
			}
			100% {
				background-position: -200% 0 ;
			}
		}

		#control-panel {
			flex : 0 0 auto ;
			position : relative ;
        }

		:host(:not([data-loaded="loaded"])) #diagram {
            visibility : hidden ;
        }

        #area {
			flex : 1 1 auto ;
            position : relative ;
        }

        #scroller-shr , #scroller-exp {
            position : absolute ;
            top : 0 ;
            right : 0 ;
            bottom : 0 ;
			left : 0 ;
            overflow : hidden ;
            visibility : hidden ;
        }

		#scroller-shr-filler {
            position: absolute ;
            left : 0 ;
            top : 0 ;
            width : 10000000px ;
            height : 10000000px ;
        }
		#scroller-exp-filler {
			position: absolute ;
			left : 0 ;
			top : 0 ;
			width : 200% ;
			height : 200% ;
		}

		#diagram {
			position : absolute ;
			left : 0 ;
			top : 0 ;
		}

        #refresh-btn {
            cursor : pointer ;
        }

		#refresh-btn:hover {
            box-shadow : 1px 1px 1px #000 , -1px -1px 1px #fff ;
        }

        .data-line {
            border : inherit ;
        }

		#data-lines-legend-default {
			--position : top ;
            --line-to-line-space : 24px ;
            --row-to-row-space   : 4px ;
            --mark-to-text-space : 8px ;
            --legend-to-diagram-space : 10px ;
        }

    </style>
    <div id="element-style" style="display: none">
        <div id="axis-labels" part="axis-labels"></div>
        <div id="data-lines" part="data-lines"></div>
        <div id="data-lines-legend-default"><div id="data-lines-legend" part="data-lines-legend"></div></div>
    </div>
    <div id="control-panel">
        <select id="transformations-select"></select>
        <a id="refresh-btn">обновить</a>
    </div>
    <div id="area">
        <div id="scroller-shr">
            <div id="scroller-shr-filler"></div>
        </div>
        <div id="scroller-exp">
            <div id="scroller-exp-filler"></div>
        </div>
        <div part="area">
            <div id="drawing-area" part="drawing-area" style="position : relative !important ; visibility : hidden !important ;"></div>
            <canvas id="diagram"></canvas>
        </div>
    </div>
</template>