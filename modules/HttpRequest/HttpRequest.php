<?php
class HttpRequest {
    function vtlib_handler($moduleName, $eventType) {
        if ($eventType == 'module.postinstall') {
            self::registerTask();
        } elseif ($eventType == 'module.preuninstall') {
            self::deregisterTask();
        }
    }

    public static function registerTask() {
        $db = PearDatabase::getInstance();
        $res = $db->pquery('SELECT id FROM com_vtiger_workflow_tasktypes WHERE tasktypename=?', array('VTHttpTask'));
        if ($db->num_rows($res) == 0) {
            $taskId = $db->getUniqueID('com_vtiger_workflow_tasktypes');
            $modules = Zend_Json::encode(array('include'=>array(),'exclude'=>array()));
            $db->pquery('INSERT INTO com_vtiger_workflow_tasktypes(id, tasktypename, label, classname, classpath, templatepath, modules, sourcemodule) VALUES (?,?,?,?,?,?,?,?)',
                array($taskId, 'VTHttpTask', 'HTTP Request', 'VTHttpTask', 'modules/HttpRequest/tasks/VTHttpTask.php', 'layouts/v7/modules/Settings/Workflows/Tasks/VTHttpTask.tpl', $modules, ''));
        }
    }

    public static function deregisterTask() {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM com_vtiger_workflow_tasktypes WHERE tasktypename=?', array('VTHttpTask'));
    }
}

