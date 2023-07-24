<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class GPT_AskGPT_Action extends Vtiger_BasicAjax_Action {

    public function __construct() {
        parent::__construct();
        $this->exposeMethod('requestGPT');
    }

    public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
    }

    public static function requestGPT(Vtiger_Request $request) {
        $query = $request->get('query');
        $type  =$request->get('type');
        $connector = new GPT_GPT_Connector;
        if($type == 'Global') {
            $formattedQuery = array(
                array('role'=>'system','content'=>'You are a helpful assistant.'),
                array('role'=>'user','content'=>$query),
            );
            $response = $connector->AskGPT($formattedQuery);
            $formattedResponse = self::formatResponse('Global', $response);
        }
        return $formattedResponse;
    }

    public function formatResponse($type, $response){
        if($type == 'Global') {
            $response = json_decode($response, true);
            if(!$response['error']) {
                $content['data'] = $response['choices'][0]['message']['content'];
            } else {
                $content['error'] = (string)$response['error']['message'];
            }
        }
        return $content;
    }
}
