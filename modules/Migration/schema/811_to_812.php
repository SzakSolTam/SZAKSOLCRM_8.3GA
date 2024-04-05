<?php
if (defined('VTIGER_UPGRADE')) {
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
ini_set('display_errors', 'On');
include_once 'include/utils/utils.php';
include_once 'includes/runtime/Viewer.php';
include_once 'includes/runtime/LanguageHandler.php';
global $adb, $current_user;
    $db = PearDatabase::getInstance();

    $db->pquery("ALTER TABLE vtiger_emailtemplates MODIFY COLUMN body LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;");
}