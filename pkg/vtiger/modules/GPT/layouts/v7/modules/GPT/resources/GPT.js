/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

var Vtiger_GPT_Js = {

	ckEditorInstance : false,


	addGPTIcon: function() {
		var globalNav = jQuery('.global-nav').find('.navbar-nav');
		globalNav.prepend('<li><div><a href="#" class="fa fa-commenting global-gpt" aria-hidden="true"></a></div></li>');
    },

	registerEventForGlobalGPT: function() {
		var thisInstance = this;
		var gptAction = jQuery('.global-nav');
		gptAction.off("click");
		gptAction.on("click", ".global-gpt", function(e) {
			var params = {
				'module' : 'GPT',
				'view' : 'AskGPT',
				'mode' : 'AskGPTView',
				'type' : 'Global',
			}
			app.helper.showProgress();
			app.request.post({"data":params}).then(function(err,data) {
				if(err == null) {
					app.helper.showModal(data);
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
		var gptModal = jQuery('.globalgptcontainer');
		gptModal.off("click", "#getGlobalGPTResponse");
		gptModal.on("click", "#getGlobalGPTResponse", function(e) {
			e.preventDefault();
			var query = gptModal.find("#AskGPTInput").val();
			var params = {
				'module' : 'GPT',
				'view' : 'AskGPT',
				'mode' : 'requestGPT',
				'type' : 'Global',
				'query' : query,
			}
			app.helper.showProgress();
			app.request.post({"data":params}).then(function(err,data) {
				app.helper.hideProgress();
				if(err == null) {
					gptModal.find('.cancelLink').trigger('click');
					jQuery('.globalgptcontainer').remove();
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
					thisInstance.postCloseModal();
				} else {
					app.helper.showErrorNotification({message : err.message});
				}
			});
		});
	},

	postCloseModal: function() {
		var responseModal = jQuery('.gptResponseContainer');
		responseModal.on("click", "#gptmodalCancelLink", function(e) {
			location.reload();
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

    registerEvents : function(){
		var thisInstance = this;
		thisInstance.addGPTIcon();
		thisInstance.registerEventForGlobalGPT();
	}
}

//On Page Load register GPT Js Events
jQuery(window).on("load", function() {
	Vtiger_GPT_Js.registerEvents();
});