<?php
if (defined('VTIGER_UPGRADE')) {
    global $adb, $current_user;
    $db = PearDatabase::getInstance();

    // Increase column length to hold longer JSONified value.
    $db->pquery('ALTER TABLE com_vtiger_workflows MODIFY COLUMN schannualdates VARCHAR(500)', array());

    // Trim the space in value.
    $db->pquery('UPDATE vtiger_projecttaskstatus set projecttaskstatus = "Canceled" where projecttaskstatus = "Canceled "', array());
}
