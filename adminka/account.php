<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	include_once( '../core.php' );
	/**
	 * @var $dbConfig
	 * @var $LoginOk
	 * @var $UserRights
	 * @var $UserID
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
	
	$GoOut = true ;
	$accountsEDIT = false ;
	
	$tgt_UserID = intval( $_REQUEST[ 'edit' ] , 10 );
	if ( $UserID == $tgt_UserID ) {
		$accountsEDIT = true ;
		$GoOut = false ;
	} else
	if ( count( $UserRights ) == 1 ) {
		$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );

		if ( array_key_exists( 'ACCOUNTS' , $Rights ) ) {
			$accountsEDIT = in_array( 'EDIT' , $Rights[ 'ACCOUNTS' ] );
			$GoOut = !( $accountsEDIT && isset( $_REQUEST[ 'edit' ] ) );
		}
	}

	if ( $GoOut ) {
		MainHead_L2( '' , '' , array( '../%UT/buttons.css' , '../%UT/forms.css' ), array() , 'hlp/no_access.html' );
		echo '<br><br><br><br><br>' ;
		MessageForm();
		closeHtml();
		exit ;
	}
	
	$tabPlaces = $portalDB->table( 'places' );
	$user_worker = $portalDB->row( "select `t1`.* from `workers` as `t1`, `accounts` as `t2` where ( `t1`.`id` = `t2`.`worker_id` ) and ( `t2`.`id` = ? )" , 'i' , $tgt_UserID );
	$user_name = NAMES_Format( NAMES_parse( $user_worker[ 'name' ] ) , '%F1 %I1 %O1' );
	$user_account = $portalDB->row( "select * from `accounts` where `id` = ?" , 'i' , $tgt_UserID );

	$v_login = $user_account[ 'login' ];
	$v_passwd = '' ;
	$v_passwd_code = $user_account[ 'pass' ];
	$v_ip = long2ip( $user_account[ 'ip' ] );
	$v_any_ip = $user_account[ 'any_ip' ] == 1 ;
	$v_mac_addr = $user_account[ 'mac_addr' ];
	$v_guid = $user_account[ 'guid' ];

	$credChanged = false ;

 	if ( isset( $_REQUEST[ 'set' ] ) ) {
		$v_login = $_REQUEST[ 'i_login' ];
		$v_passwd = $_REQUEST[ 'i_passwd' ];

		$row = $portalDB->row( "select count( * ) as `count` from `accounts` where `login` = ? and `id`<> ?" , 'si' , strtolower( $v_login ) , $tgt_UserID );
		if ( $row[ 'count' ] != 0 ) {
			$err_msg = '' ;
		} else {
			if ( $v_passwd != '' ) {
				$v_passwd_code = base64_encode( sha1( $v_passwd ) ) ;
 			}

 			$portalDB->noResult(
 				"update `accounts` set `login`= ? ".( $v_passwd != "" ? " , `pass`= ".Str2SQL( $v_passwd_code ) : "" )." , `otp-core` = null , `any_ip`= 1 , `mac_addr` = ? , `guid` = ? where `id` = ? limit 1" ,
 				'sssi' , strtolower( $v_login ) , $v_mac_addr , $v_guid , $tgt_UserID
			);

			$credChanged = true ;

 			if ( isset( $_REQUEST[ 'btnChAc' ] ) ) {
 				$cookieDomain = $dbConfig[ 'engine.addresses.cookieDomain' ];
				setcookie( 'uLogin' , $v_login , time() + 60 * 60 * 24 * 1024 , '/' , $cookieDomain , '0' );
				setcookie( 'uPassword' , $v_passwd_code , time() + 60 * 60 * 24 * 1024 , '/' , $cookieDomain , '0' );
 				Redirect( '/' );
 			}
 		}
 	}

 	MainHead_L2('Админка', '<a href="main.php">Админка</a> - <a href="accounts.php">аккаунты</a> - права доступа' , array( '../%UT/buttons.css' , '%UT/account.css' ) , array( 'files/account.js' ) , 'hlp/rights.html' );

 		echo '<center>
 			<div class="worker-div">
 				'.$user_name.'
 			</div>
 		</center>' ;

 		if ( isset( $_REQUEST[ 'otp' ] ) ) {
			$sk = openssl_random_pseudo_bytes( 32 , $cs );
			$pass = bin2hex( openssl_random_pseudo_bytes( 32 , $cs ) );

			$portalDB->updateRow( 'accounts' , array( 'id' => $tgt_UserID , 'otp-core' => base64_encode( $sk ) , 'pass' => $pass ) );
			$portalDB->noResult( "delete from `options` where ( `op_name` = 'kuvk.pass' ) and ( `user_id` = ? )" , 'i' , $tgt_UserID );
			$barCodeData = 'otpauth://totp/'.$v_login.'@'.$dbConfig[ 'engine.addresses.base' ].'?secret='.base32encode( $sk );
			echo '<center><img src="/barcode.php?timernd='.( date( 'YmdHis' ).mt_rand() ).'&dbg=1&src='.urlencode( $barCodeData ).
				'&type=QR&opt='.urlencode( json_encode( array( 'EL' => 'L' , 'qrcode_mode' => 'byte' , 'pix_size' => 4 ) ) ).
				'" class="qr-code"></center>' ;
		} else {
			echo '<form action="account.php?edit='.$tgt_UserID.'&set" method="post">
				<table align="center" class="account-datas-table">
					<tr>
						<td class="account-data">
							<table class="account-data-table">
								<tr>
									<td class="data-name" colspan=2>
										Параметры аккаунта
									</td>
								</tr>
								<tr>
									<td class="param-name">
										Логин
									</td>
									<td class="param-value">
										<input id="i_login" name="i_login" type="text" value="'.$v_login.'" class="i_login" oninput="evtCredChanged()"> <input name="genLogin" type="button" value="Сгенерировать" onclick="doGenLogin()" class="genBtn">
										<input type="checkbox" id="i_login_cb" class=""> <div id="login_bc" style="background-image: url( \'/barcode.php?timernd='.time().'--'.rand().'&dbg=1&src='.$v_login.'&type=QR&opt='.urlencode( '{ "EL" : "L" , "qrcode_mode" : "byte" , "pix_size" : 4 }' ).'\' );"/>
									</td>
								</tr>
								<tr>
									<td class="param-name">
										Пароль
									</td>
									<td class="param-value">
										<input id="i_passwd" name="i_passwd" type="text" value="'.$v_passwd.'" class="i_passwd" oninput="evtCredChanged()"> <input name="genPasswd" type="button" value="Сгенерировать" onclick="doGenPWD()" class="genBtn">
										<input type="checkbox" id="i_passwd_cb" class=""> <div id="passwd_bc" style="background-image: url( \'/barcode.php?timernd='.time().'--'.rand().'&dbg=1&src='.$v_passwd.'&type=QR&opt='.urlencode( '{ "EL" : "L" , "qrcode_mode" : "byte" , "pix_size" : 4 }' ).'\' );"></div> <br/>
										<button type="submit" name="otp" value="otp">привязать генератор OTP</button>
									</td>
								</tr>
								<tr>
									<td class="param-name">
										Сохранить и перейти на этот аккаунт
									</td>
									<td class="param-value">
										<input name="btnChAc" type="submit" value="Переход" class="btnChAc">
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td class="formBtn">
							<input type="submit" value="Сохранить изменения"> <input type="button" id="copy-cred-to-clipboard-btn" value="копировать в буфер обмена" onclick="copyCredToClipboard()" '.( $credChanged ? '' : ' disabled="disabled"' ).'/>
						</td>
					</tr>
				</table>
			</form>' ;
		}

  closeHtml();
