<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/com_vtiger_workflow/VTSimpleTemplate.inc';

class VTWebhookTask extends VTTask {
    public $executeImmediately = true;

    public function getFieldNames() {
        return array('url', 'method', 'field_value_mapping');
    }

    protected function sendRequest($url, $method, $params) {
        $ch = curl_init();
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
        } else {
            if (!empty($params)) {
                if (strpos($url, '?') === false) {
                    $url .= '?';
                } else {
                    $url .= '&';
                }
                $url .= http_build_query($params, '', '&');
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function doTask($entity) {
        $util = new VTWorkflowUtils();
        $admin = $util->adminUser();
        $ws_id = $entity->getId();
        $entityCache = new VTEntityCache($admin);

        $urlTemplate = new VTSimpleTemplate($this->url);
        $url = $urlTemplate->render($entityCache, $ws_id);
        $method = strtoupper($this->method);

        $mapping = array();
        if (!empty($this->field_value_mapping)) {
            $mapping = Zend_Json::decode($this->field_value_mapping);
        }

        $params = array();
        foreach ($mapping as $fieldInfo) {
            $param = $fieldInfo['fieldname'];
            $template = new VTSimpleTemplate($fieldInfo['value']);
            $params[$param] = $template->render($entityCache, $ws_id);
        }

        $this->sendRequest($url, $method, $params);
        $util->revertUser();
    }
}
?>
