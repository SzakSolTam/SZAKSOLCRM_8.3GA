<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_GPT_Record_Model extends Settings_Vtiger_Record_Model {

    const tableName = 'vtiger_gpt_config';

    public function getId() {
        return $this->get('id');
    }

    public function getName() {
    }

    public function getModule(){
        return new Settings_GPT_Module_Model;
    }

    static function getCleanInstance(){
        return new self;
    }

    public static function getInstance(){
        $serverModel = new self();
        $db = PearDatabase::getInstance();
        $query = 'SELECT * FROM '.self::tableName;
        $gatewatResult = $db->pquery($query, array());
        $gatewatResultCount = $db->num_rows($gatewatResult);

        if($gatewatResultCount > 0) {
            $rowData = $db->query_result_rowdata($gatewatResult, 0);
            $serverModel->set('id',$rowData['id']);
            $serverModel->set('org_id',$rowData['org_id']);
            $serverModel->set('api_key',$rowData['api_key']);
            return $serverModel;
        }
        return $serverModel;
    }

    public static function getInstanceById($recordId, $qualifiedModuleName) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM '.self::tableName.' WHERE id = ?', array($recordId));

		if ($db->num_rows($result)) {
			$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
			$rowData = $db->query_result_rowdata($result, 0);

			$recordModel = new self();
			$recordModel->setData($rowData);
			return $recordModel;
		}
		return false;
	}

    public function save() {
		$db = PearDatabase::getInstance();
		$id = $this->getId();
        $params = array($this->get('org_id'), $this->get('api_key'), $this->get('provider'));

		if ($id) {
			$query = 'UPDATE '.self::tableName.' SET org_id = ?, api_key = ? WHERE provider=? AND id = ?';
			array_push($params, $id);
		} else {
			$query = 'INSERT INTO '.self::tableName.'(org_id, api_key, provider) VALUES(?, ?, ?)';
		}
		$db->pquery($query, $params);
	}
}
