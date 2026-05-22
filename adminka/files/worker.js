/**
 * 
 */

	function deactivate( id ) {
		var res = prompt( "Для подтверждения операции введите слово П О Д Т В Е Р Ж Д А Ю без пробелов" );
		if ( res == "ПОДТВЕРЖДАЮ" ) {
			window.location = "worker.php?edit=" + id + "&deactivate" ;
		}
	}
	
	function activate( id ) {
		var res = prompt( "Для подтверждения операции введите слово П О Д Т В Е Р Ж Д А Ю без пробелов" );
		if ( res == "ПОДТВЕРЖДАЮ" ) {
			window.location = "worker.php?edit=" + id + "&activate" ;
		}
	}