<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'include/utils/utils.php';
require_once 'vtlib/Vtiger/Net/Client.php';

class GPT_GPT_Connector {

    const webappurl = 'https://api.openai.com/v1/chat/completions';
    const modelURL = 'https://api.openai.com/v1/models';
    const gptModel = 'gpt-3.5-turbo';
    const maxTokens = 20;
    private static $SETTINGS_REQUIRED_PARAMETERS = array('org_id' => 'text' , 'api_key' => 'text');

    /**
     * Function to get Settings edit view params
     * returns <array>
     */
    public static function getSettingsParameters() {
        return self::$SETTINGS_REQUIRED_PARAMETERS;
    }

    public function getServiceURL($type = false) {
		return self::webappurl;
	}

    public function getModelURL() {
        return self::modelURL;
    }

    public function getGPTModel() {
        return self::gptModel;
    }

    public function getMaxTokens() {
        return self::maxTokens;
    }

    /**
     * Function to get response from openAI GPT
     */
    public function getApiKey() {
        $recordModel = Settings_GPT_Record_Model::getInstance();
        $api_key = $recordModel->get('api_key');
        return $api_key;
    }

    /**
     * Function to ask openAI.
     */
    public static function AskGPT($query) {
        $serviceURL = self::getServiceURL();
        $accessKey = self::getApiKey();
        $gptModel = self::getGPTModel();
        $maxTokens = self::getMaxTokens();
        $body = array('model' => $gptModel, 'temperature' => 0.7, 'max_tokens'=> $maxTokens, 'messages' => $query);
        $body = json_encode($body);

        $httpClient = new Vtiger_Net_Client($serviceURL);
        $httpClient->setHeaders(array('Content-type' => 'application/json', 'Authorization' => 'Bearer '.$accessKey));

        $result = $httpClient->doPost($body);
        
        return $result;
    }

    /**
     * Function to check openAI credentials
     * We are verifying by sending request to openAI before saving to DB.
     */
    public static function checkCredentials($org_id, $api_key) {
        $modelURL = self::getModelURL();
        $httpClient = new Vtiger_Net_Client($modelURL);
        $httpClient->setHeaders(array('Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$api_key, 'OpenAI-Organization' => $org_id));
        
        $result = $httpClient->doGet(array());

        return json_decode($result, true);
    }
}