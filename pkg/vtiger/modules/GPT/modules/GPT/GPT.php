<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

include_once 'data/CRMEntity.php';
require_once 'vtlib/Vtiger/Link.php';
include_once 'vtlib/Vtiger/Module.php';
include_once('vtlib/Vtiger/Menu.php');
require 'include/events/include.inc';
require_once 'include/utils/utils.php';

class GPT extends CRMEntity {

    function vtlib_handler($moduleName, $eventName) {
        if ($eventName == 'module.postinstall') {
            $this->addLinksForGPT();
            $this->addSettingsLinks();
        } else if ($eventName == 'module.preuninstall') {
            $this->removeLinksForGPT();
            $this->removeSettingsLinks();
        } else if ($eventName == 'module.enabled') {
            $this->addLinksForGPT();
            $this->addSettingsLinks();
        } else if ($eventName == 'module.disabled') {
            $this->removeLinksForGPT();
            $this->removeSettingsLinks();
        }
    }


    /**
     * To add a link in vtiger_links which is to load our GPT.js 
     */
    function addLinksForGPT() {
        global $log;
        Vtiger_Link::addLink(0, 'HEADERSCRIPT', 'GPT', 'layouts/v7/modules/GPT/resources/GPT.js', '', '');
        $log->fatal('Links added');
    }

    /**
     * To remove link for GPT.js from vtiger_links
     */
    function removeLinksForGPT() {
        global $log;
        Vtiger_Link::deleteLink('HEADERSCRIPT', 'GPT', 'layouts/v7/modules/GPT/resources/GPT.js');
        $log->fatal('Links Removed');
    }


    /**
     * To add Integration->GPT block in Settings page
     */
    function addSettingsLinks()
    {
        global $log;
        $adb = PearDatabase::getInstance();
        $integrationBlock = $adb->pquery('SELECT * FROM vtiger_settings_blocks WHERE label=?', array('LBL_INTEGRATION'));
        $integrationBlockCount = $adb->num_rows($integrationBlock);

        // To add Block
        if ($integrationBlockCount > 0) {
            $blockid = $adb->query_result($integrationBlock, 0, 'blockid');
        } else {
            $blockid = $adb->getUniqueID('vtiger_settings_blocks');
            $sequenceResult = $adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_blocks", array());
            if ($adb->num_rows($sequenceResult)) {
                $sequence = $adb->query_result($sequenceResult, 0, 'sequence');
            }
            $adb->pquery("INSERT INTO vtiger_settings_blocks(blockid, label, sequence) VALUES(?,?,?)", array($blockid, 'LBL_INTEGRATION', ++$sequence));
        }

        // To add a Field
        $fieldid = $adb->getUniqueID('vtiger_settings_field');
        $adb->pquery("INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence, active)
            VALUES(?,?,?,?,?,?,?,?)", array($fieldid, $blockid, 'LBL_GPT', '', 'GPT module Configuration', 'index.php?module=GPT&parent=Settings&view=Index', 2, 0));
        $log->fatal('Settings Block and Field added');
    }

    /**
     * To delete Integration->GPT block in Settings page
     */
    function removeSettingsLinks()
    {
        global $log;
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_settings_field WHERE name=?', array('LBL_GPT'));
        $log->fatal('Settings Field Removed');
    }
}