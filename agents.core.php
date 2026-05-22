<?php
	namespace Agents ;

	function integrate( $opt = false , $sel = false , $style = false ) {
		global $portalDB ;

		$defOpt = array(
			'type-fixed' => false ,
			'target-elements' => strexp( '{type,agen{cy,t}{,-label},org-address{,-alt-editor,-label},contacts{,-controls,-label},addressee{,-label}}' ) ,
			'embed' => false ,
			'id-prefix' => '' ,
			'name-prefix' => '' ,
			'org-address-func' => false
		);

		$defSel = array(
			'type' => 1 ,
			'agency' => false ,
			'agent' => false
		);

		$defStyle = array(
			'table'                      => 'std-agents-table' ,
			'type-td'                    => 'std-agents-type-td' ,
			'type'                       => 'std-agents-type' ,
			'agency-label'               => 'std-agents-agency-label' ,
			'agency-tr'                  => 'std-agents-agency-tr' ,
			'agency-td'                  => 'std-agents-agency-td' ,
			'agency'                     => 'std-agents-agency' ,
			'agency-sel-tr'              => 'std-agents-agency-sel-tr' ,
			'agency-sel-td'              => 'std-agents-agency-sel-td' ,
			'agency-sel-c1'              => 'std-agents-agency-sel-c1' ,
			'agency-sel-c2'              => 'std-agents-agency-sel-c2' ,
			'agency-sel'                 => 'std-agents-agency-sel' ,
			'agent-label'                => 'std-agents-agent-label' ,
			'agent-td'                   => 'std-agents-agent-td' ,
			'agent'                      => 'std-agents-agent' ,
			'agent-sel-td'               => 'std-agents-agent-sel-td' ,
			'agent-sel-c1'               => 'std-agents-agent-sel-c1' ,
			'agent-sel-c2'               => 'std-agents-agent-sel-c2' ,
			'agent-sel'                  => 'std-agents-agent-sel' ,
			'org-address-label'          => 'std-agents-org-address-label' ,
			'org-address-td'             => 'std-agents-org-address-td' ,
			'org-address-editor'         => 'std-agents-org-address-editor' ,
			'org-address-link'           => 'std-agents-org-address-link' ,
			'contacts-td'                => 'std-agents-contacts-td' ,
			'contacts-label'             => 'std-agents-contacts-label' ,
			'contacts-label-agent-name'  => 'std-agents-contacts-label-agent-name' ,
			'show-hidden-contacts'       => 'std-agents-show-hidden-contacts' ,
			'show-hidden-contacts-label' => 'std-agents-show-hidden-contacts-label' ,
			'contacts-area'              => 'std-agents-contacts-area' ,
			'contacts-table'             => 'std-agents-contacts-table' ,
			'contacts-btn-panel'         => 'std-agents-contacts-btn-panel' ,
			'contacts-table-ctrl'        => 'std-agents-contacts-table-ctrl' ,
			'contacts-table-ctrl-ico'    => 'std-agents-contacts-table-ctrl-ico' ,

			'addressee-td'               => 'std-agents-addressee-td' ,
			'addressee-label'            => 'std-agents-addressee-label' ,
			'addressee-list-area'        => 'std-agents-addressee-list-area' ,
			'addressee-list-tab'         => 'std-agents-addressee-list-tab' ,

		);

		if ( $opt !== false ) {
			if ( is_string( $opt ) ) {
				$opt = json_decode( $opt , true );
			}
			$opt = array_merge( $defOpt , $opt );
		} else {
			$opt = $defOpt ;
		}

		$optEmbed = $opt[ 'embed' ];
		$optTgtElements = array_fill_keys( $opt[ 'target-elements' ] , true );
		foreach( $defOpt[ 'target-elements' ] as $cte ) {
			if ( !isset( $optTgtElements[ $cte ] ) ) {
				$optTgtElements[ $cte ] = false ;
			}
		}
		$optIDPrefix = $opt[ 'id-prefix' ];
		$optNamePrefix = $opt[ 'name-prefix' ];

		if ( $sel !== false ) {
			if ( is_string( $sel ) ) {
				$sel = json_decode( $sel , true );
			}
			$sel = array_merge( $defSel , $sel );
		} else {
			$sel = $defSel ;
		}

		if ( $style !== false ) {
			if ( is_string( $style ) ) {
				$style = json_decode( $style , true );
			}
			$style = array_merge( $defStyle , $style );
		} else {
			$style = $defStyle ;
		}

		//var_dump_html( $style , true );

		$result = array();
		$js = array( 'link' => array() );

		if ( !$optEmbed ) {
			$result[ 'tab-open' ] = '<table class="'.$style[ 'table' ].'">' ;
		}

		if ( $optTgtElements[ 'type' ] ) {
			$listType = $portalDB->table( 'type-of-agency' );
			$result[ 'type' ] = '<select id="'.$optIDPrefix.'type" name="'.$optNamePrefix.'type" size="1" class="'.$style[ 'type' ].'">'.
				makeSimpleSelectTagOptions( $listType , 'name' , 'id' , $sel[ 'type' ] , function( $s ){
					return inForm( $s );
				} ).
			'</select>' ;
			$js[ 'link' ][ 'type' ] = $optIDPrefix.'type' ;
			if ( !$optEmbed ) {
				$result[ 'type' ] = '<tr>
					<td class="'.$style[ 'type-td' ].'">
						'.$result[ 'type' ].'
					</td>
				</tr>' ;
			}
		} else {
			$js[ 'link' ][ 'type' ] = $optIDPrefix.'type' ;
			$result[ 'type' ] = '<input id="'.$optIDPrefix.'type" name="'.$optNamePrefix.'type" type="hidden" value="'.$sel[ 'type' ].'">' ;
		}

		if ( $optTgtElements[ 'agency' ] ) {
			if ( !$optEmbed ) {
				$result[ 'agency' ] = '<tr class="'.$style[ 'agency-tr' ].'">
					<td class="'.$style[ 'agency-td' ].'">
						'.( $optTgtElements[ 'agency-label' ] ? '<div id="'.$optIDPrefix.'agency-label" class="'.$style[ 'agency-label' ].'">Орган / Фирма</div>' : '' ).'
						<textarea id="'.$optIDPrefix.'agency" name="'.$optNamePrefix.'agency" class="'.$style[ 'agency' ].'"></textarea>
					</td>
				</tr>
				<tr class="'.$style[ 'agency-sel-tr' ].'">
					<td class="'.$style[ 'agency-sel-td' ].'">
						<div class="'.$style[ 'agency-sel-c1' ].'">
							<div class="'.$style[ 'agency-sel-c2' ].'">
								<select id="'.$optIDPrefix.'agency-sel" name="'.$optNamePrefix.'agency_sel" size="2" class="'.$style[ 'agency-sel' ].'"></select>
							</div>
						</div>
					</td>
				</tr>' ;
			} else {
				$result[ 'agency' ] =
					'<textarea id="'.$optIDPrefix.'agency" name="'.$optNamePrefix.'agency" class="'.$style[ 'agency' ].'"></textarea>
					<select id="'.$optIDPrefix.'agency-sel" name="'.$optNamePrefix.'agency_sel" size="2" class="'.$style[ 'agency-sel' ].'"></select>' ;
			}

			if ( $optTgtElements[ 'agency-label' ] ) {
				$js[ 'link' ][ 'agencyLabel' ] = $optIDPrefix.'agency-label' ;
			}
			$js[ 'link' ][ 'agency' ] = $optIDPrefix.'agency' ;
			$js[ 'link' ][ 'agencyList' ] = $optIDPrefix.'agency-sel' ;
		} else {
			$result[ 'agency' ] = '<input id="'.$optIDPrefix.'agency" name="'.$optNamePrefix.'agency" type="hidden" value="'.$sel[ 'agency' ].'">' ;
		}

		if ( $optTgtElements[ 'org-address-alt-editor' ] ) {
			if ( !$optEmbed ) {
				$result[ 'org-address' ] = '<tr>
					<td class="'.$style[ 'org-address-td' ].'">
						'.( $optTgtElements[ 'org-address-label' ] ? '<div id="'.$optIDPrefix.'org-address-label" class="'.$style[ 'org-address-label' ].'">Адрес</div>' : '' ).'
						<textarea id="'.$optIDPrefix.'org-address-alt-editor" name="'.$optNamePrefix.'org_address" class="'.$style[ 'org-address-editor' ].'"></textarea>
						<a id="org-address" class="'.$style[ 'org-address-link' ].'" '.( $opt[ 'org-address-func' ] !== false ? ' onclick="'.$opt[ 'org-address-func' ].'"' : '' ).'></a>
					</td>
				</tr>' ;
			} else {
				$result[ 'org-address' ] =
					'<textarea id="'.$optIDPrefix.'org-address-alt-editor" name="'.$optNamePrefix.'org_address" class="'.$style[ 'org-address-editor' ].'"></textarea>
					<a id="org-address" class="'.$style[ 'org-address-link' ].'" '.( $opt[ 'org-address-func' ] !== false ? ' onclick="'.$opt[ 'org-address-func' ].'"' : '' ).'></a>' ;
			}
			$js[ 'link' ][ 'agencyAddress' ] = $optIDPrefix.'org-address' ;
		} else
		if ( $optTgtElements[ 'org-address' ] ) {
			if ( !$optEmbed ) {
				$result[ 'org-address' ] = '<tr>
					<td class="'.$style[ 'org-address-td' ].'">
						<textarea id="'.$optIDPrefix.'org-address" name="'.$optNamePrefix.'org_address" class="'.$style[ 'org-address-editor' ].'"></textarea>
					</td>
				</tr>' ;
			} else {
				$result[ 'org-address' ] =
					'<textarea id="'.$optIDPrefix.'org-address" name="'.$optNamePrefix.'org_address" class="'.$style[ 'org-address-editor' ].'"></textarea>' ;
			}

			$js[ 'link' ][ 'agencyAddress' ] = $optIDPrefix.'org-address' ;
		}


		if ( $optTgtElements[ 'agent' ] ) {
			if ( !$optEmbed ) {
				$result[ 'agent' ] = '<tr>
					<td class="'.$style[ 'agent-td' ].'">
						'.( $optTgtElements[ 'agent-label' ] ? '<div id="'.$optIDPrefix.'agent-label" class="'.$style[ 'agent-label' ].'">Лицо / Представитель</div>' : '' ).'
						<textarea id="'.$optIDPrefix.'agent" name="'.$optNamePrefix.'agent" class="'.$style[ 'agent' ].'"></textarea>
					</td>
				</tr>
				<tr>
					<td class="'.$style[ 'agent-sel-td' ].'">
						<div class="'.$style[ 'agent-sel-c1' ].'">
							<div class="'.$style[ 'agent-sel-c2' ].'">
								<select id="'.$optIDPrefix.'agent-sel" name="'.$optNamePrefix.'agent-sel" size="2" class="'.$style[ 'agent-sel' ].'"></select>
							</div>
						</div>
					</td>
				</tr>' ;
			} else {
				$result[ 'agent' ] =
					'<textarea id="'.$optIDPrefix.'agent" name="'.$optNamePrefix.'agent" class="'.$style[ 'agent' ].'"></textarea>
					<select id="'.$optIDPrefix.'agent-sel" name="'.$optNamePrefix.'agent-sel" size="2" class="'.$style[ 'agent-sel' ].'"></select>' ;
			}

			if ( $optTgtElements[ 'agency-label' ] ) {
				$js[ 'link' ][ 'agencyLabel' ] = $optIDPrefix.'agency-label' ;
			}
			$js[ 'link' ][ 'agent' ] = $optIDPrefix.'agent' ;
			$js[ 'link' ][ 'agentList' ] = $optIDPrefix.'agent-sel' ;
		} else {
			$result[ 'agent' ] = '<input id="'.$optIDPrefix.'agent" name="'.$optNamePrefix.'agent" type="hidden" value="'.$sel[ 'agent' ].'">' ;
		}

		if ( $optTgtElements[ 'contacts' ] ) {
			if ( !$optEmbed ) {
				$result[ 'contacts' ] = '<tr>
					<td class="'.$style[ 'contacts-td' ].'">
						<input type="checkbox" id="'.$optIDPrefix.'show-hidden-contacts" class="'.$style[ 'show-hidden-contacts' ].'" style="display : none ;">
						'.( $optTgtElements[ 'contacts-label' ] ? '<div class="'.$style[ 'contacts-label' ].'">Контакты'.( $optTgtElements[ 'contacts-label-agent-name' ] ? ': <div id="'.$optIDPrefix.'contacts-label-agent-name" class="'.$style[ 'contacts-label-agent-name' ].'"></div>' : '' ).'<label for="'.$optIDPrefix.'show-hidden-contacts" class="'.$style[ 'show-hidden-contacts-label' ].'">скрытые<div class="std-slider"></div></label></div>' : '' ).'
						<div class="'.$style[ 'contacts-area' ].'">
							<table id="'.$optIDPrefix.'contacts-table" class="'.$style[ 'contacts-table' ].'"></table>
						</div>' ;

				$js[ 'link' ][ 'contactsList' ] =  $optIDPrefix.'contacts-table' ;
				$js[ 'link' ][ 'contactsLabelAgentName' ] = $optIDPrefix.'contacts-label-agent-name' ;

				if ( $optTgtElements[ 'contacts-controls' ] ) {
					if ( $optTgtElements[ 'addressee' ] ) {
						$js[ 'link' ][ 'contactsListStore' ] =  $optIDPrefix.'contacts-tab-store' ;
						$addresseeStoreBtn = '<a id="'.$optIDPrefix.'contacts-tab-store" class="btn1 '.$style[ 'contacts-table-ctrl' ].'">Сохранить адресата</a>' ;
					} else {
						$addresseeStoreBtn = '' ;
					}
					$result[ 'contacts' ].= '<div class="'.$style[ 'contacts-btn-panel' ].'">
						<a id="'.$optIDPrefix.'contacts-tab-ctrl1" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> адрес</a>
						<a id="'.$optIDPrefix.'contacts-tab-ctrl2" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> e-mail</a>
						<a id="'.$optIDPrefix.'contacts-tab-ctrl3" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> тел./факс</a>
						<a id="'.$optIDPrefix.'contacts-tab-ctrl4" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> мобильный</a>
						<a id="'.$optIDPrefix.'contacts-tab-ctrl5" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> на руки</a>
						'.$addresseeStoreBtn.'
					</div>' ;
					foreach ( range( 1 , 5 ) as $i ) {
						$js[ 'link' ][ 'contactsListCtrl'.$i ] =  $optIDPrefix.'contacts-tab-ctrl'.$i ;
					}
				}

				$result[ 'contacts' ].= '</td></tr>' ;
			} else {
				$result[ 'contacts' ] = '<div class="'.$style[ 'contacts-area' ].'">
					<table id="'.$optIDPrefix.'contacts-table" class="'.$style[ 'contacts-table' ].'"></table>
				</div>' ;
				if ( $optTgtElements[ 'contacts-controls' ] ) {
					if ( $optTgtElements[ 'addressee' ] ) {
						$addresseeStoreBtn = '<a id="'.$optIDPrefix.'contacts-tab-store" class="btn1 '.$style[ 'contacts-table-ctrl' ].'">Сохранить адресата</a>' ;
					} else {
						$addresseeStoreBtn = '' ;
					}
					$result[ 'contacts-controls' ] =  '<div class="'.$style[ 'contacts-btn-panel' ].'">
						<a id="'.$optIDPrefix.'contacts-tab-ctrl1" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> адрес</a>
						<a id="'.$optIDPrefix.'contacts-tab-ctrl2" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> e-mail</a>
						<a id="'.$optIDPrefix.'contacts-tab-ctrl3" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> тел./факс</a>
						<a id="'.$optIDPrefix.'contacts-tab-ctrl4" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> мобильный</a>
						<a id="'.$optIDPrefix.'contacts-tab-ctrl5" class="btn1 '.$style[ 'contacts-table-ctrl' ].'"><div class="'.$style[ 'contacts-table-ctrl-ico' ].'"></div> на руки</a>
						'.$addresseeStoreBtn.'
					</div>' ;
				}
			}
		}

		if ( $optTgtElements[ 'addressee' ] ) {
			if ( !$optEmbed ) {
				$result[ 'addressee' ] = '<tr>
					<td class="'.$style[ 'addressee-td' ].'">
						'.( $optTgtElements[ 'addressee-label' ] ? '<div class="'.$style[ 'addressee-label' ].'">Адресаты</div>' : '' ).'
						<div class="'.$style[ 'addressee-list-area' ].'">
							<table id="'.$optIDPrefix.'addressee-list-tab" class="'.$style[ 'addressee-list-tab' ].'"></table>
						</div>
					</td>
				</tr>' ;
			} else {
				//
			}

			$js[ 'link' ][ 'addresseeList' ] = $optIDPrefix.'addressee-list-tab' ;
		}

		if ( !$optEmbed ) {
			$result[ 'tab-close' ] = '</table>' ;
		}

		return array(
			'html' => $result ,
			'js' => $js ,
			'selected' => $sel
		);
	}
