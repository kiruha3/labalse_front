<template id="template--SlidingPanel">
    <style id="variables">
        :host {
            --client-width : 10000px ;
		}
    </style>
    <style>
        :host {
			border : 1px solid #000 ;
			background-color : #fff ;
            box-shadow: 6px 6px 12px #000 ;
            display : flex ;
		}

        :host(:not([side])) , :host([side="left"]) {
			flex-direction : row-reverse ;
			left : 0 !important ;
			transform : translateX( calc( 0px - var( --client-width ) ) );
        }

		:host([side="right"]) {
			flex-direction : row ;
			right : 0 !important ;
			transform : translateX( var( --client-width ) );
		}

        :host(:not([side])) , :host([side="left"]) , :host([side="right"]) {
			min-height : 256px ;
        }

		:host([data-opened="opened"]:not([side])) ,
		:host([data-opened="opened"][side="left"]) ,
        :host([data-opened="1"]:not([side])) ,
		:host([data-opened="1"][side="left"]) {
			transform : translateX( 1cm );
		}
		:host([data-opened="opened"][side="right"]) ,
		:host([data-opened="1"][side="right"]) {
			transform : translateX( -1cm );
		}

		#label {
            flex : 0 0 auto ;
            padding : 4px ;
            font-size : 18pt ;
            user-select : none ;
            cursor : pointer ;
            text-align : center ;
        }
        :host(:not([side])) #label , :host([side="left"]) #label {
			writing-mode : vertical-lr ;
			border-left : 1px dotted #000 ;
        }
		:host([side="right"]) #label {
			writing-mode : vertical-lr ;
			transform : rotate( 180deg );
			border-left : 1px dotted #000 ;
		}
        #label:hover {
            background-color : #ffe0c0 ;
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

    </style>
    <div id="label" part="label"></div>
    <div id="area">
        <div id="scroller-shr">
            <div id="scroller-shr-filler"></div>
        </div>
        <div id="scroller-exp">
            <div id="scroller-exp-filler"></div>
        </div>
        <div part="area"><slot></slot></div>
    </div>
</template>