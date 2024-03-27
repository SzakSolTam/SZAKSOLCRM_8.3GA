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
$db->pquery("ALTER TABLE vtiger_crmentity ADD INDEX {setype}_idx (setype)", array());
}