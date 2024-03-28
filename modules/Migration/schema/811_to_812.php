<?php
if (defined('VTIGER_UPGRADE')) {
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
ini_set('display_errors', 'On');
include_once 'include/utils/utils.php';
include_once 'includes/runtime/Viewer.php';
include_once 'includes/runtime/LanguageHandler.php';
$db = PearDatabase::getInstance();
global $current_user;
$current_user = Users::getActiveAdminUser();
$db->pquery('DELETE detail FROM vtiger_modtracker_detail AS detail 
                LEFT JOIN vtiger_modtracker_basic AS basic ON detail.id = basic.id WHERE basic.id IS NULL');

$db->pquery('ALTER TABLE vtiger_modtracker_detail ADD CONSTRAINT fk_modtracker_basic_id FOREIGN KEY (id) REFERENCES vtiger_modtracker_basic(id) ON DELETE CASCADE');

}