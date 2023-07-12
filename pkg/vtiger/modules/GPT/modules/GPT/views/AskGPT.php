<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class GPT_AskGPT_View extends Vtiger_Index_View {

	function __construct() {
		$this->exposeMethod('AskGPTView');
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
}
