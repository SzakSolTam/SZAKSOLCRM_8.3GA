<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once('modules/GPT/connectors/GPTConnector.php');
class GPT_AskGPT_View extends Vtiger_Index_View {

	function __construct() {
		$this->exposeMethod('AskGPTView');
		$this->exposeMethod('requestGPT');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

    public function AskGPTView(Vtiger_Request $request) {
        $module = $request->get('module');
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->view('AskGPT.tpl', $module);
    }

	public function requestGPT(Vtiger_Request $request) {
		$module = $request->get('module');
		$query = $request->get('query');
		$viewer = $this->getViewer($request);
		$responseField = 'gpt_response';
		$response = new Vtiger_Response();

		try {
			$GPTResponse = GPT_AskGPT_Action::requestGPT($request);
			if($GPTResponse['error']) {
				$message = $GPTResponse['error'];
				$response->setError($message);
				$response->emit();
			} else {
				$moduleModel = Vtiger_Module_Model::getInstance('GPT');
				$fieldModel = Vtiger_Field_Model::getInstance($responseField, $moduleModel);
				$fieldModel->set('fieldvalue', $GPTResponse['data']);

				$viewer->assign('MODULE', $module);
				$viewer->assign('FIELD_MODEL', $fieldModel);
				$viewer->assign('QUERY', $query);
				$viewer->assign('RESPONSE', $GPTResponse['data']);
				$viewer->view('GPTResponse.tpl', $module);
			}
		} catch (Exception $e) {
			$response->setError($e->getMessage());
			$response->emit();
		}
	}
}
