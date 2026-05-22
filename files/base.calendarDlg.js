
	$.calendarDlg = new function() {

		this.dlg = null ;
		this.monthName = null ;
		this.table = null ;
		this.currentDate = null ;

		this.months = strexp( '{Январь,Февраль,Март,Апрель,Май,Ию{н,л}ь,Август,{Сентя,Октя,Ноя,Дека}брь}' );

		this.selectedMonth = {
			m : 0 ,
			y : 0 ,
			el : null ,
			ss : 0 ,
			se : 0
		};

		this.dateFormat = function( d , m , y ) {
			return ( parseInt( d , 10 ) < 10 ? '0' : '' ) + d + "." + ( m < 10 ? 0 : '' ) + m + "." + y ;
		};

		this.dlgCalendar = function( f , v1 , v2 , v3 ) {
			var sm = this.selectedMonth ;
			switch ( f ) {
				case 0 :
					var mn = this.monthName ;
					setText( mn , this.months[ sm.m ] + " " + sm.y );
					var dc = ( new Date( sm.y , sm.m + 1 , 0 ) ).getDate();
					var fd = this.dlgCalendar( 4 , ( new Date( sm.y , sm.m , 1 ) ).getDay() );
					var t = this.table ;
					while ( t.rows.length > 1 ) {
						t.deleteRow( 1 );
					}

					var r = t.insertRow( -1 );
					for( var i = 0 ; i < fd ; i++ ) {
						var c = r.insertCell( -1 );
						c.className = 'dlg-calendar-empty' ;
					}

					var cwd = fd ;
					var cd = 1 ;
					var today = new Date();
					while ( cd <= dc ) {
						if ( cwd == 7 ) {
							var r = t.insertRow( -1 );
							cwd = 0 ;
						}
						var c = r.insertCell( -1 );
						c.className = 'dlg-calendar-date-' + ( cwd++ < 5 ? 0 : 1 ) + ( cd == today.getDate() && sm.m == today.getMonth() && sm.y == today.getFullYear() ? " dlg-calendar-today" : '' );
						c.onclick = function( o , d , m , y ) {
							return function() {
								o.dlgCalendar( 5 , d , m , y );
							};
						}( this , cd , sm.m + 1 , sm.y );
						setText( c , cd++ );
					}

					for( var i = cwd ; i < 7 ; i++ ) {
						var c = r.insertCell( -1 );
						c.className = 'dlg-calendar-empty';
					}

					break ;

				case 1 :
					if ( typeof v1 === 'undefined' ) {
						var v1 = new Date();
						sm.m = v1.getMonth();
						sm.y = v1.getFullYear();
					} else {
						sm.m = v1 - 1 ;
						sm.y = v2 ;
					}
					break ;

				case 2 :
					sm.m-- ;
					if ( sm.m < 0 ) {
						sm.m = 11 ;
						sm.y-- ;
					}
					this.dlgCalendar( 0 );
					break ;

				case 3 :
					sm.m++ ;
					if ( sm.m > 11 ) {
						sm.m = 0 ;
						sm.y++ ;
					}
					this.dlgCalendar( 0 );
					break ;

				case 4 :
					if ( v1 == 0 ) {
						v1 = 7 ;
					}
					v1-- ;
					return v1 ;
					break ;

				case 5 :
					var dlg = this.dlg ;
					var tgt = sm.el ;
					var ss = sm.ss ;
					var se = sm.se ;
					tgt.value = tgt.value.substring( 0 , ss ) + this.dateFormat( v1 , v2 , v3 ) + tgt.value.substring( se , tgt.value.length );
					dlg.style.display = 'none' ;
					break ;

				case 6 :
					sm.y-- ;
					this.dlgCalendar( 0 );
					break ;

				case 7 :
					sm.y++ ;
					this.dlgCalendar( 0 );
					break ;
			}

		};

		this.show = function( event , id ) {
			if ( this.dlg == null ) {
				this.init();
			}
			event = event || window.event ;
			var b = getChar( event );
			if ( b == "*" ) {
				var dlg = this.dlg ;
				var tgt = document.getElementById( id );
				this.selectedMonth.el = tgt ;
				this.selectedMonth.ss = tgt.selectionStart ;
				this.selectedMonth.se = tgt.selectionEnd ;
				dlg.style.display = '' ;
				return false ;
			}
		};

		this.init = function() {
			this.dlg = document.createElement( 'div' );
			var $ = this.dlg ;
			$.className = 'dlg-calendar' ;
			$.style.display = 'none' ;

			var ma = document.createElement( 'div' );
			ma.className = 'dlg-calendar-month-area' ;

			var lnk = document.createElement( 'a' );
			lnk.className = 'dlg-calendar-year-prev' ;
			lnk.onclick = function( o , x ) { return function() { o.dlgCalendar( x ); }; }( this , 6 );
			ma.appendChild( lnk );

			var lnk = document.createElement( 'a' );
			lnk.className = 'dlg-calendar-month-prev' ;
			lnk.onclick = function( o , x ) { return function() { o.dlgCalendar( x ); }; }( this , 2 );
			ma.appendChild( lnk );

			var tmpDiv = document.createElement( 'div' );
			tmpDiv.className = 'dlg-calendar-month-name' ;
			tmpDiv.onclick = function( o , x ) { return function() { o.dlgCalendar( x ); }; }( this , 2 );
			ma.appendChild( tmpDiv );
			this.monthName = tmpDiv ;

			var lnk = document.createElement( 'a' );
			lnk.className = 'dlg-calendar-month-next' ;
			lnk.onclick = function( o , x ) { return function() { o.dlgCalendar( x ); }; }( this , 3 );
			ma.appendChild( lnk );

			var lnk = document.createElement( 'a' );
			lnk.className = 'dlg-calendar-year-next' ;
			lnk.onclick = function( o , x ) { return function() { o.dlgCalendar( x ); }; }( this , 7 );
			ma.appendChild( lnk );

			$.appendChild( ma );

			var tab = document.createElement( 'table' );
			this.table = tab ;
			tab.className = 'dlg-calendar-table' ;

			var r = tab.insertRow( -1 );
			var tmp = strexp( "{Пн,Вт,Ср,Чт,Пт,Сб,Вс}" );
			for( var i = 0 ; i < tmp.length ; i++ ) {
				var c = r.insertCell( -1 );
				c.className = 'dlg-calendar-week-day-0' ;
				c.appendChild( document.createTextNode( tmp[ i ] ) );
			}

			/*var c = r.insertCell( -1 );
			c.className = 'dlg-calendar-week-day-0' ;
			c.appendChild( document.createTextNode( "Вт" ) );

			var c = r.insertCell( -1 );
			c.className = 'dlg-calendar-week-day-0' ;
			c.appendChild( document.createTextNode( "Ср" ) );

			var c = r.insertCell( -1 );
			c.className = 'dlg-calendar-week-day-0' ;
			c.appendChild( document.createTextNode( "Чт" ) );

			var c = r.insertCell( -1 );
			c.className = 'dlg-calendar-week-day-0' ;
			c.appendChild( document.createTextNode( "Пт" ) );

			var c = r.insertCell( -1 );
			c.className = 'dlg-calendar-week-day-0' ;
			c.appendChild( document.createTextNode( "Сб" ) );

			var c = r.insertCell( -1 );
			c.className = 'dlg-calendar-week-day-0' ;
			c.appendChild( document.createTextNode( "Вс" ) );*/

			$.appendChild( tab );

			var cDate = document.createElement( 'div' );
			this.currentDate = cDate ;
			cDate.className = 'dlg-calendar-current-date' ;
			cDate.onclick = function(  ){  };

			var tmpDiv = document.createElement( 'div' );
			tmpDiv.className = '' ;

			cDate.appendChild( tmpDiv );
			cDate.appendChild( document.createTextNode( '' ) );

			$.appendChild( cDate );

			document.body.appendChild( $ );

			this.dlgCalendar( 1 );
			this.dlgCalendar( 0 );
		};
	}();
