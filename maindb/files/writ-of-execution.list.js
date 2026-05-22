
	function doFilter() {
		const f = document.getElementById( 'search-form' );
		const u = new URL( f.action );
		const qsp = u.searchParams ;
		qsp.delete( 'xlsx' );
		f.action = u.href ;
		f.submit();
	}

	function doXLSXTable() {
		const f = document.getElementById( 'search-form' );
		const u = new URL( f.action );
		const qsp = u.searchParams ;
		qsp.append( 'xlsx' , '1' );
		f.action = u.href ;
		f.submit();
	}