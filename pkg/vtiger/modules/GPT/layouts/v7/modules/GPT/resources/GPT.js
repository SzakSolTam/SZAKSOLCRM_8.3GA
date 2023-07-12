/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

var Vtiger_GPT_Js = {
	addGPTIcon: function() {
		var globalNav = jQuery('.global-nav').find('.navbar-nav');
		globalNav.prepend('<li><div><a href="#" class="fa fa-commenting global-gpt" aria-hidden="true"></a></div></li>');
    },

	registerEventForGPT: function() {
		var gptAction = jQuery('.global-nav');
		gptAction.on("click", ".global-gpt", function(e) {
			var params = {
				'module' : 'GPT',
				'view' : 'AskGPT',
				'mode' : 'AskGPTView',
			}
			app.helper.showProgress();
			app.request.post({"data":params}).then(function(err,data) {
				if(err == null) {
					app.helper.showModal(data);
					app.helper.hideProgress();
				} else {
					app.helper.showErrorNotification({message: err.message});
					app.helper.hideProgress();
				}
			});
		});
	},

    registerEvents : function(){
		var thisInstance = this;
		thisInstance.addGPTIcon();
		thisInstance.registerEventForGPT();
	}
}

//On Page Load
jQuery(window).on("load", function() {
	Vtiger_GPT_Js.registerEvents();
});