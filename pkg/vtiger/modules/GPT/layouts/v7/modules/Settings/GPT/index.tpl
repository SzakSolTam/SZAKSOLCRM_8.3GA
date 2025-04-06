{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<div class="row margin0">
		<div class="col-lg-12" style="padding: 16px;">
			<div class="related-tabs">
				<div class="collapse navbar-collapse" id="GPTNavTabs"  style="border-bottom: 1px solid #ccc;">
					<ul class="nav nav-tabs">
						<li  class="tab-item active cursorPointer" data-tabname="gptconfig" style="margin-right: 16px;"><a href="#gptconfig">Configuration</a></li>
						<li  class="tab-item cursorPointer" data-tabname="gptlogs" style="margin-right: 16px;"><a href="#gptlogs">GPT logs</a></li>	
					</ul>
				</div>

				<div class="navbar-contents">
					<div class="tab-contents gptconfigcontainer" id="gptconfig" style="padding: 16px;">
						<div class="row">
							<div class="col-lg-12">
								{include file="gptconfig.tpl"|vtemplate_path:$QUALIFIED_MODULE CONFIG_FIELDS=$CONFIG_FIELDS}
							</div>
						</div>
					</div>
					<div class="tab-contents hide" id="gptlogs" style="padding: 16px;">
						<div class="row">
							<div class="col-lg-12" id="gptlogscontainer">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
