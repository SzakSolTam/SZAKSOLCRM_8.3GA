<?php 

require_once 'modules/SalesOrder/RecurringInvoice.php';

class SalesOrder_CreateRecurringInvoices_Action extends Vtiger_Action_Controller
{
    public function requiresPermission(\Vtiger_Request $request) {
        $permissions = parent::requiresPermission($request);
        $request->set('create_module', 'Invoice');
        $permissions[] = array('module_parameter' => 'create_module', 'action' => 'CreateView');
        return $permissions;
    }
    
    public function process(Vtiger_Request $request)
    {
        $soId = (int) $request->get('record');
        $recurringInvoice = new RecurringInvoice($soId);
        $recurringInvoice->createAll();
        $this->emitResponse();
    }
    
    private function emitResponse()
    {
        $response = new Vtiger_Response();
        $response->setResult(array(
            'created' => 'created',
        ));
        $response->emit();
    }
}
