/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Inventory_Detail_Js("SalesOrder_Detail_Js",{
	displayCreateAllInvoicesModal: function(){
		var listInstance = app.controller();
		app.helper.showProgress();
		
		var params = {
			'module': 'SalesOrder',
			'view': 'CreateRecurringInvoices',
			'record': app.getRecordId(),
		}
		
		app.request.post({data : params}).then(function(error, data) {
			app.helper.hideProgress();
			app.helper.showModal(data);
		});
	},
	
	createAllInvoices: function(){
		app.helper.showProgress();
		
		var params = {
			'module': 'SalesOrder',
			'action': 'CreateRecurringInvoices',
			'record': app.getRecordId(),
		}
		
		app.request.post({data : params}).then(function(error, result) {
			if (error === null) {
				if (typeof result === 'object' && result !== null) {
					console.log(result);
					app.helper.showSuccessNotification({title: app.vtranslate('JS_SUCCESS'), message: app.vtranslate('JS_LBL_INVOICES_CREATED')});
				} else {
					app.helper.showErrorNotification({title: app.vtranslate('JS_ERROR'), message: app.vtranslate('Unexpected error')},  {'delay' : 0});
				}
			} else {
				console.log(error);
				app.helper.showErrorNotification({title: app.vtranslate('JS_ERROR'), message: error.message},  {'delay' : 0});
			}
			app.helper.hideModal();
			app.helper.hideProgress();
		});
    },
    
},{});