{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<input type="hidden" name="is_record_creation_allowed" id="is_record_creation_allowed" value="{$IS_CREATE_PERMITTED}">
	<div class="col-sm-11 col-xs-10 padding0 module-action-bar clearfix">
		<div class="module-action-content clearfix coloredBorderTop">
			<div class="col-lg-7 col-md-6 col-sm-5 col-xs-11 padding0 module-breadcrumb module-breadcrumb-List transitionsAllHalfSecond"">
				<span>
					{assign var="VIEW_HEADER_LABEL" value="LBL_CALENDAR_VIEW"}
					{if $VIEW === 'SharedCalendar'}
						{assign var="VIEW_HEADER_LABEL" value="LBL_SHARED_CALENDAR"}
					{/if}
					<a href='javascript:void(0)'><h4 class="module-title pull-left"><span style="cursor: default;"> {strtoupper(vtranslate($VIEW_HEADER_LABEL, $MODULE))} </span></h4></a>
				</span>
			</div>
			<div class="col-lg-5 col-md-6 col-sm-7 col-xs-1 padding0 pull-right">
				<div id="appnav" class="navbar-right">
					<nav class="navbar navbar-inverse border0 margin0">
						<div class="container-fluid">
							<div class="navbar-header bg-white marginTop5px">
								<button type="button" class="navbar-toggle collapsed margin0" data-toggle="collapse" data-target="#appnavcontent" aria-expanded="false">
								<i class="fa fa-ellipsis-v"></i>
								</button>
							</div>
							<div class="navbar-collapse collapse" id="appnavcontent" aria-expanded="false" style="height: 1px;">
								<ul class="nav navbar-nav">
									{if $IS_CREATE_PERMITTED}
									<li>
										<button id="calendarview_basicaction_addevent" type="button" 
										class="btn addButton btn-default module-buttons cursorPointer" 
										onclick='Calendar_Calendar_Js.showCreateEventModal();'>
											<div class="fa fa-plus" aria-hidden="true"></div>&nbsp;&nbsp;
											{vtranslate('LBL_ADD_EVENT', $MODULE)}
									</li>
									<li>
										<button id="calendarview_basicaction_addtask" type="button" 
										class="btn addButton btn-default module-buttons cursorPointer" 
										onclick='Calendar_Calendar_Js.showCreateTaskModal();'>
											<div class="fa fa-plus" aria-hidden="true"></div>&nbsp;&nbsp;
											{vtranslate('LBL_ADD_TASK', $MODULE)}
										</button>
									</li>
									{/if}
									<li>
										<div class="settingsIcon">
											<button type="button" class="btn btn-default module-buttons dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
												<span class="fa fa-wrench" aria-hidden="true" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></span>&nbsp;&nbsp;{vtranslate('LBL_CUSTOMIZE', 'Reports')}&nbsp; <span class="caret"></span>
											</button>
											<ul class="detailViewSetting dropdown-menu">
												{foreach item=SETTING from=$MODULE_SETTING_ACTIONS}
													{if $SETTING->getLabel() eq 'LBL_EDIT_FIELDS'}
														<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}_Events"><a href="{$SETTING->getUrl()}&sourceModule=Events">{vtranslate($SETTING->getLabel(), $MODULE_NAME,vtranslate('LBL_EVENTS',$MODULE_NAME))}</a></li>
														<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}_Calendar"><a href="{$SETTING->getUrl()}&sourceModule=Calendar">{vtranslate($SETTING->getLabel(), $MODULE_NAME,vtranslate('LBL_TASKS','Calendar'))}</a></li>
													{else if $SETTING->getLabel() eq 'LBL_EDIT_WORKFLOWS'}
														<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}_WORKFLOWS"><a href="{$SETTING->getUrl()}&sourceModule=Events">{vtranslate('LBL_EVENTS', $MODULE_NAME)} {vtranslate('LBL_WORKFLOWS',$MODULE_NAME)}</a></li>	
														<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}_WORKFLOWS"><a href="{$SETTING->getUrl()}&sourceModule=Calendar">{vtranslate('LBL_TASKS', 'Calendar')} {vtranslate('LBL_WORKFLOWS',$MODULE_NAME)}</a></li>
													{else}
														<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}"><a href={$SETTING->getUrl()}>{vtranslate($SETTING->getLabel(), $MODULE_NAME, vtranslate($MODULE_NAME, $MODULE_NAME))}</a></li>
													{/if}
												{/foreach}
												<li>
													<a>
														<span id="calendarview_basicaction_calendarsetting" onclick='Calendar_Calendar_Js.showCalendarSettings();' class="cursorPointer">
															{vtranslate('LBL_CALENDAR_SETTINGS', 'Calendar')}
														</span>
													</a>
												</li>
											</ul>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</nav>
				</div>
			</div>
		</div>
		{if $FIELDS_INFO neq null}
			<script type="text/javascript">
				var uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					return {
						field: {
							get: function (name, property) {
								if (name && property === undefined) {
									return fieldInfo[name];
								}
								if (name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].type
								}
								return false;
							}
						},
					};
				})();
			</script>
		{/if}
	</div>
{/strip}