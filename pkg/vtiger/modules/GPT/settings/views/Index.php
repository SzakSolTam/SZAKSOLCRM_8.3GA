<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
require_once('modules/GPT/connectors/GPTConnector.php');
class Settings_GPT_Index_View extends Settings_Vtiger_Index_View{
    
    function __construct() {
        $this->exposeMethod('gptconfig');
        $this->exposeMethod('gptlogs');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

        $modulename = $request->getModule();
        $qualifiedModule = $request->getModule(false); 

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $modulename);
        $viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModule);
        $configFields = GPT_GPT_Connector::getSettingsParameters();
        $viewer->assign('CONFIG_FIELDS', $configFields);
        $viewer->view('index.tpl', $qualifiedModule);
    }
    
    public function gptconfig(Vtiger_Request $request){
        $recordModel = Settings_GPT_Record_Model::getInstance();
        $moduleModel = Settings_GPT_Module_Model::getCleanInstance();
        $configFields = GPT_GPT_Connector::getSettingsParameters();

        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIG_FIELDS', $configFields);
        $viewer->assign('RECORD_ID', $recordModel->get('id'));
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('MODULE', $request->getModule(false));
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->view('gptconfig.tpl', $request->getModule(false));
    }

    public function gptlogs(Vtiger_Request $request){
        $recordModel = Settings_GPT_Record_Model::getInstance();
        $moduleModel = Settings_GPT_Module_Model::getCleanInstance();
        $viewer = $this->getViewer($request);
        
        $viewer->assign('RECORD_ID', $recordModel->get('id'));
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('MODULE', $request->getModule(false));
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->view('gptlogs.tpl', $request->getModule(false));
    }
}
