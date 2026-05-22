<?php
	/**
	 * @var $DaysOfWeek
	 * @var $MonthNames
	 * @var $DaysOfWeekShort
	 */

	echo '<script type="text/javascript">
	$.CONSTANTS = {
		monthNames : JSON.parse( atob( "'.base64_encode( json_encode( iconvRecursion( 'cp1251' , 'utf8' , $MonthNames ) ) ).'" ) ) ,
		daysOfWeek : JSON.parse( atob( "'.base64_encode( json_encode( iconvRecursion( 'cp1251' , 'utf8' , $DaysOfWeek ) ) ).'" ) ) ,
		daysOfWeekShort : JSON.parse( atob( "'.base64_encode( json_encode( iconvRecursion( 'cp1251' , 'utf8' , $DaysOfWeekShort ) ) ).'" ) ) ,
	};
</script>' ;
