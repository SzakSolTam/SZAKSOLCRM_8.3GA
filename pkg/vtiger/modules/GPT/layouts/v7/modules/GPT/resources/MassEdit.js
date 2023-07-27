/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Emails_MassEdit_Js("GPT_MassEdit_Js", {}, {
	
	ckEditorInstance : false,

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
		var thisInstance = this;
		var gptAction = jQuery('#composeEmailContainer');
		gptAction.off("click", "#askGPTMailContent");
		gptAction.on("click", "#askGPTMailContent", function(e) {
			var params = {
				'module' : 'GPT',
				'view' : 'AskGPT',
				'mode' : 'AskGPTView',
				'type' : 'MailBody',
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
					thisInstance.registerSendGlobalGPTRequest();
				} else {
					app.helper.showErrorNotification({message: err.message});
					app.helper.hideProgress();
				}
			});
		});
	},

	registerSendGlobalGPTRequest: function() {
		var thisInstance = this;
		var gptModal = jQuery('.mailgptcontainer');
		gptModal.off("click", "#getMailGPTResponse");
		gptModal.on("click", "#getMailGPTResponse", function(e) {
			e.preventDefault();
			var query = gptModal.find("#AskGPTInputMail").val();
			var params = {
				'module' : 'GPT',
				'view' : 'AskGPT',
				'mode' : 'requestGPT',
				'type' : 'MailBody',
				'query' : query,
			}
			app.helper.showProgress();
			app.request.post({"data":params}).then(function(err,data) {
				app.helper.hideProgress();
				if(err == null) {
					gptModal.find('.cancelLink').trigger('click');
					jQuery('.mailgptcontainer').remove();
					var ele = jQuery('<div class="modal popupModal"></div>');
					ele.append(data);
					jQuery('body').append(ele);
					var emailEditInstance = new Emails_MassEdit_Js();
					emailEditInstance.showpopupModal();
					var isCkeditorApplied = jQuery('#gptResponseField').data('isCkeditorApplied');
					if(isCkeditorApplied != true && jQuery('#gptResponseField').length > 0){
						thisInstance.loadCkEditor(jQuery('#gptResponseField').data('isCkeditorApplied',true));
					}
					var ckEditorInstance = thisInstance.getckEditorInstance();
					var fullResponse = jQuery(data);
					var ckValue = fullResponse.find('#gptResponseField').val();
					ckEditorInstance.loadContentsInCkeditor(ckValue);
				} else {
					app.helper.showErrorNotification({message : err.message});
				}
			});
		});
	},

	loadCkEditor : function(textAreaElement){
		var ckEditorInstance = this.getckEditorInstance();
		ckEditorInstance.loadCkEditor(textAreaElement);
	},

	getckEditorInstance : function(){
		if(this.ckEditorInstance == false){
			this.ckEditorInstance = new Vtiger_CkEditor_Js();
		}
		return this.ckEditorInstance;
	},

    registerEvents: function () {
        thisInstance = this;
		thisInstance.gptMailSubject();
		thisInstance.registerEventForGPTMailContent();
	}
});
