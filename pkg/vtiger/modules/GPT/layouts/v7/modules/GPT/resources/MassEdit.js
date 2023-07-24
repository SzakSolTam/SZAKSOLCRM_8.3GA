/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Emails_MassEdit_Js("GPT_MassEdit_Js", {}, {
	
    gptMailSubject: function() {
		composeEmailContainer = jQuery("#composeEmailContainer");
		mailComposeGPTIcon = composeEmailContainer.find(".mail-subject-gpt");
		mailComposeSubject = composeEmailContainer.find("#subject");
		mailComposeGPTIcon.hide();
		mailComposeSubject.keyup( function(e) {
			if(mailComposeSubject.val()){
				mailComposeGPTIcon.show();
			} else {
				mailComposeGPTIcon.hide();
			}
		});
	},

	registerEventForGPTMailContent: function() {
		var gptAction = jQuery('#composeEmailContainer');
		gptAction.off("click");
		gptAction.on("click", "#askGPTMailContent", function(e) {
			var params = {
				'module' : 'GPT',
				'view' : 'AskGPT',
				'mode' : 'AskGPTView',
			}
			app.helper.showProgress();
			app.request.post({"data":params}).then(function(err,data) {
				if(err == null) {
					jQuery('.popupModal').remove();
					var ele = jQuery('<div class="modal popupModal"></div>');
					ele.append(data);
					jQuery('body').append(ele);
					var emailEditInstance = new Emails_MassEdit_Js();
					emailEditInstance.showpopupModal();
					app.helper.hideProgress();
				} else {
					app.helper.showErrorNotification({message: err.message});
					app.helper.hideProgress();
				}
			});
		});
	},

    registerEvents: function () {
        thisInstance = this;
		thisInstance.gptMailSubject();
		thisInstance.registerEventForGPTMailContent();
	}
});
