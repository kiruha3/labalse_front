<?php
	require_once ( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $portalDB
	 */
	include_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */
	
	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}
	
	
	
	
	if ( isset( $_REQUEST[ 'do-merge' ] ) ) {
		//print_r_html( $_FILES );
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment;filename="Объединенные XLSX '.date( 'Y.m.d H-i' , mktime() ).'.xlsx"' );
		
		$WD = array(
			'Общие сведения 1.1' => array(
				'rows' => range( 10 , 260 ) ,
				'cols' => range( 'D' , 'G' )
			) ,
			'Общие сведения 1.2' => array(
				'rows' => range( 10 , 260 ) ,
				'cols' => range( 'D' , 'G' )
			) ,
			'Детальные сведения 2.1' => array(
				'rows' => range( 11 , 7103 ) ,
				'cols' => range( 'C' , 'D' )
			) ,
			'Детальные сведения 2.2' => array(
				'rows' => range( 9 , 1786 ) ,
				'cols' => range( 'E' , 'H' )
			)
		);
		
		$TB = microtime( 1 );
		$xlsx_ctrl = new TSimpleXLSXTemplate( $_FILES[ 'ctrl_file' ][ 'tmp_name' ] );
		$xlsx_add  = new TSimpleXLSXTemplate( $_FILES[ 'add_file'  ][ 'tmp_name' ] );
		$xlsx_dst  = new TSimpleXLSXTemplate( $_FILES[ 'dst_file'  ][ 'tmp_name' ] );
		
		foreach( $WD as $p => $d ) {
			$xlsx_ctrl->selectSheet( $p );
			$xlsx_add->selectSheet( $p );
			$xlsx_dst->selectSheet( $p );
			
			$res = array();
			
			foreach( $d[ 'rows' ] as $row ) {
				$resRow = array();
				foreach( $d[ 'cols' ] as $col ) {
					$vCtrl = $xlsx_ctrl->getCellValueEx( $col.$row );
					$vAdd  = $xlsx_add->getCellValueEx( $col.$row );
					$vDst  = $xlsx_dst->getCellValueEx( $col.$row );
					
					if ( $vCtrl[ 'formula' ] === false ) {
						if ( ( $vCtrl[ 'value' ] != $vAdd[ 'value' ] ) && ( $vCtrl[ 'value' ] == $vDst[ 'value' ] ) ) {
							$resRow[ $col ] = $vAdd[ 'value' ];
							$xlsx_dst->setCellValue( $col.$row , trim( $vAdd[ 'value' ] ) );
						}
					}
				}
				if ( count( $resRow ) > 0 ) {
					$res[ $row ]= $resRow ;
				}
			}
			
			/*echo '<div>'.$p.'</div>' ;
			echo makeSimpleTable( $res );*/
		}
		
		
		
		//for( range( 'D' , 'U' ); )
		
		$TE = microtime( 1 );
		//var_dump( $TE - $TB );
		$xlsx_dst->write();
		exit();
	}
	
	MainHead_L2( 'Инструменты - объединить XLSX' , 'Инструменты - объединить XLSX' , array( '../%UT/buttons.css' ) , array() , 'hlp/main.html' );
	
	echo '<div>
			<form action="?do-merge" method="post" enctype="multipart/form-data">
				<label>Контрольный файл <input type="file" name="ctrl_file" /></label><br/>
				<label>Добавочный файл <input type="file" name="add_file" /></label><br/>
				<label>Заполняемый файл <input type="file" name="dst_file" /></label><br/>
				<input type="submit" value="Отправить">
			</form>
		</div>' ;
	
	closeHtml();
	
	//$xlsx = new TSimpleXLSXTemplate( './files/tmpl-151.xlsx' );
	
