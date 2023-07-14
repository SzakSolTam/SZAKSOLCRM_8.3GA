<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once('modules/GPT/connectors/GPTConnector.php');

class Settings_GPT_SaveAjax_Action extends Vtiger_SaveAjax_Action {

    public function process(Vtiger_Request $request) {
        $id = $request->get('id');
        $provider = 'openAI';
        
        $recordModel = Settings_GPT_Record_Model::getCleanInstance();
        $recordModel->set('provider',$provider);
        if($id) {
            $recordModel->set('id',$id);
        }
        
        foreach (GPT_GPT_Connector::getSettingsParameters() as $field => $type) {
            $recordModel->set($field, $request->get($field));
        }
        
        $response = new Vtiger_Response();
        try {
                $authorize = GPT_GPT_Connector::checkCredentials($request->get('org_id'), $request->get('api_key'));
                if($authorize['data']) {
                    $recordModel->save();
                    $response->setResult(true);
                } else {
                    $response->setError($authorize['error']['message']);
                }
        } catch (Exception $e) {
                $response->setError($e->getMessage());
        }
        $response->emit();
    }
}
