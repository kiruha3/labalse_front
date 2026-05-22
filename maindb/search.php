<?php
/*
    Этот скрипт (исходный код) является интелектуальной собственностью Пекшева Петра Александровича.
    Публикация, воспроизведение, распространение и использование без письменного разрешение автора запрещено.
    copyright (c) Пекшев Петр Александрович, 2008
*/

	require_once( '../core.php' );
	/**
	 * @var $LoginOk
	 * @var $UserRights
	 */
	require_once( 'lconfig.php' );
	/**
	 * @var $PlaceID
	 */

	TryLoginFromCookie( $PlaceID );
	if ( !$LoginOk ) {
		Redirect( '../auth.php' );
	}

	if ( count( $UserRights ) == 1 ) {
      	$Rights = ParseRights( strtoupper( $UserRights[ 0 ] ) );
		if ( array_key_exists( 'EXTENTIONS', $Rights ) ) {
			$maySEARCH = in_array( 'SEARCH' , $Rights[ 'EXTENTIONS' ] );
		} else {
			$maySEARCH = false ;
		}

		$GoOut = !$maySEARCH ;
	} else {
		$GoOut = true ;
	}

	if ( $GoOut ) {
		ErrorPageAndExit();
	}

	$func = 'main.php' ;

	MainHead_L2( 'База - Поиск' , '<a href="main.php">База</a> - Поиск' , array( '../%UT/buttons.css', '%UT/search.css' ) , array( 'files/search.js' ), 'hlp/search.html' );

	echo '<form id="main-form" method="get" action="'.$func.'">
		<input type="hidden" name="search" value="1">
		<table align="center" class="ST">
			<tr>
				<td class="D">
					Строка поиска
					<div class="hint">Укажите слова для поиска, разделяя их пробелами</div>
				</td>
				<td class="I">
					<textarea name="i_search_string" class="i_search_string"></textarea><br/>
					<span class="ctrl-enter-comment">нажмите Ctrl + Enter для отправки формы, или нажмите одну из кнопок ниже</span>
				</td>
			</tr>

			<tr>
				<td class="D">
					Искать
				</td>
				<td class="I">
					<input name="i_operation" type=radio class="i_operation_or"  value="OR" checked> любое из указанных слов &nbsp;&nbsp;&nbsp;
					<input name="i_operation" type=radio class="i_operation_and" value="AND"> все слова<br>
					<input type="submit" value="Искать">
				</td>
			</tr>

			<tr>
				<td class="D">
					Дополнительно
				</td>
				<td class="I">
					<input name="i_ext_mydep" type=checkbox class="i_ext_mydep"  value="OR"> Только свой отдел &nbsp;&nbsp;&nbsp;
					<input name="i_ext_onlyme" type=checkbox class="i_ext_onlyme" value="AND"> Только мои<br>
				</td>
			</tr>

			<tr>
				<td class="D">
					Искать в столбцах:
				</td>
				<td class="sif_params">
					<input name="i_sif_date" type=checkbox checked>Дата поступления материалов<br><br>
					<input name="i_sif_from" type=checkbox checked>От кого поступили материалы<br><br>
					<input name="i_sif_ex_data_3" type=checkbox checked>Постановление и др.<br><br>
					<input name="i_sif_ex_data_4" type=checkbox checked>Номер дела; Количество томов, страниц, приложений; Ф.И.О. лиц, привлекаемых к ответственности, сторон по делу<br><br>
					<input name="i_sif_ex_data_6" type=checkbox checked>Ф.И.О. и подпись работника подразделения, получившего материалы, дата получения<br><br>
					<input name="i_sif_ex_data_7" type=checkbox checked>Сведения о приостановлении срока производства экспертизы (причина, даты приостановления и возобновления производства, результат рассмотрения или ходатайства)<br><br>
					<input name="i_sif_ex_data_8" type=checkbox checked>Дата сдачи заключения, акта, сообщения, письма о возврате без исполнения и материалов для отправки<br><br>
					<input name="i_sif_ex_data_9" type=checkbox checked>Дата и способ отправки заключения, акта, сообщения, письма о возврате без исполнения и материалов<br><br>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="btnTB">
					<input type="submit" value="Искать">
				</td>
			</tr>
		</table>
	</form>';

	closeHtml();
