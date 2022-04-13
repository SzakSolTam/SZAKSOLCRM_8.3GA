<?php

require_once 'modules/SalesOrder/RecurringInvoice.php';

class SalesOrder_CreateRecurringInvoices_View extends Vtiger_Index_View 
{
    public function requiresPermission(\Vtiger_Request $request) {
        $permissions = parent::requiresPermission($request);
        $request->set('create_module', 'Invoice');
        $permissions[] = array('module_parameter' => 'create_module', 'action' => 'CreateView');
        return $permissions;
    }
    
    public function process (Vtiger_Request $request)
    {
        $soId = (int) $request->get('record');
        $recurringInvoice = new RecurringInvoice($soId);
        $dates = $recurringInvoice->getRemainingDates();
        
        $viewer = $this->getViewer($request);
        $viewer->assign('DATES', $dates);
        $viewer->view('CreateRecurringInvoices.tpl', 'SalesOrder');
    }
}
