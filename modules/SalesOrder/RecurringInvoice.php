<?php 

require_once 'include/Webservices/Retrieve.php';
require_once('include/utils/utils.php');
require_once('modules/SalesOrder/SalesOrder.php');
require_once('modules/Invoice/Invoice.php');
require_once('modules/Users/Users.php');

class RecurringInvoice
{
    private $soId;
    private $so;
    private $recurringEndDate;
    private $nextInvoiceDate;
    
    public static function getRecurringSalesOrderIds()
    {
        $db = PearDatabase::getInstance();
        $sql = "
            SELECT vtiger_salesorder.salesorderid
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
            INNER JOIN vtiger_invoice_recurring_info ON vtiger_salesorder.salesorderid = vtiger_invoice_recurring_info.salesorderid
            WHERE start_period <= ? AND end_period >= ? AND (last_recurring_date < end_period OR last_recurring_date IS NULL OR last_recurring_date = '')
        ";
        $now = new DateTime();
        $result = $db->pquery($sql, array($now->format('Y-m-d'), $now->format('Y-m-d')));
        
        $ids = [];
        while ($row = $db->fetchByAssoc($result)) {
            $ids[] = $row['salesorderid'];
        }
        return $ids;
    }
    
    public static function processRecurrence(array $salesOrdersIds)
    {
        foreach ($salesOrdersIds as $soId) {
            $recurringInvoice = new self($soId);
            $recurringInvoice->createUpToToday();
        }
    }
    
    public function __construct($soId)
    {
        $this->soId = $soId;
        $soWsId = vtws_getWebserviceEntityId('SalesOrder', $soId);
        $this->so = vtws_retrieve($soWsId, Users::getActiveAdminUser());
        $this->recurringEndDate = new DateTime($this->so['end_period']);
        $this->nextInvoiceDate = $this->getBaseNextInvoiceDate();
    }
    
    public function createUpToToday()
    {
        $today = new DateTime();
        $this->create($today);
    }
    
    public function createAll()
    {
        $this->create($this->recurringEndDate);
    }
    
    public function getRemainingDates()
    {
        return $this->getDates($this->recurringEndDate);
    }
    
    private function getDates($createUntilDate)
    {
        $creditDays = $this->getDaysNumberFromString($this->so['payment_duration']);
        $dates = [];
        while ($this->nextInvoiceDate < $createUntilDate && $this->nextInvoiceDate < $this->recurringEndDate) {
            
            $dueDate = clone $this->nextInvoiceDate;
            $dueDate->modify("+{$creditDays} days");
            
            $dates[] = array(
                'invoicedate' => $this->nextInvoiceDate->format('Y-m-d'),
                'duedate' => $dueDate->format('Y-m-d'),
            );
            $this->advanceNextInvoiceDate();
        }
        return $dates;
    }
    
    private function create(DateTime $createUntilDate)
    {
        $dates = $this->getDates($createUntilDate);
        
        foreach ($dates as $date) {
            $this->createInvoice(new DateTime($date['invoicedate']), new DateTime($date['duedate']));
        }
        $this->saveNextInvoiceCreationDate();
    }
    
    private function getBaseNextInvoiceDate()
    {
        $nextInvoiceDate = new DateTime($this->so['last_recurring_date']);
        $recurringStrartDate = new DateTime($this->so['start_period']);
        if ($recurringStrartDate > $nextInvoiceDate) {
            $nextInvoiceDate = $recurringStrartDate;
        }
        return $nextInvoiceDate;
    }
    
    private function saveNextInvoiceCreationDate()
    {
        $db = PearDatabase::getInstance();
        $db->pquery('UPDATE vtiger_invoice_recurring_info SET last_recurring_date = ? WHERE salesorderid = ?', array($this->nextInvoiceDate->format('Y-m-d'), $this->soId));
    }
    
    private function createInvoice(DateTime $invoiceDate, DateTime $dueDate)
    {
        global $current_user;
        if(!$current_user) {
            $current_user = Users::getActiveAdminUser();
        }
        
        $soFocus = CRMEntity::getInstance('SalesOrder');
        $soFocus->id = $this->soId;
        $soFocus->retrieve_entity_info($this->soId,"SalesOrder");
        foreach($soFocus->column_fields as $fieldname=>$value) {
            $soFocus->column_fields[$fieldname] = decode_html($value);
        }
        
        $invoiceFocus = new Invoice();
        $invoiceFocus = getConvertSoToInvoice($invoiceFocus,$soFocus,$this->soId);
        $invoiceFocus->id = '';
        $invoiceFocus->mode = '';
        $invoiceFocus->column_fields['invoicestatus'] = $soFocus->column_fields['invoicestatus'];
        $invoiceFocus->column_fields['invoicedate'] = $invoiceDate->format('Y-m-d');
        $invoiceFocus->column_fields['duedate'] = $dueDate->format('Y-m-d');
        
        // Additional SO fields to copy -> Invoice field name mapped to equivalent SO field name
        $invoice2SoFields = Array (
            'txtAdjustment' => 'txtAdjustment',
            'hdnSubTotal' => 'hdnSubTotal',
            'hdnGrandTotal' => 'hdnGrandTotal',
            'hdnTaxType' => 'hdnTaxType',
            'hdnDiscountPercent' => 'hdnDiscountPercent',
            'hdnDiscountAmount' => 'hdnDiscountAmount',
            'hdnS_H_Amount' => 'hdnS_H_Amount',
            'assigned_user_id' => 'assigned_user_id',
            'currency_id' => 'currency_id',
            'conversion_rate' => 'conversion_rate',
            'balance' => 'hdnGrandTotal'
        );
        
        foreach($invoice2SoFields as $invoice_field => $so_field) {
            $invoiceFocus->column_fields[$invoice_field] = $soFocus->column_fields[$so_field];
        }
        $invoiceFocus->_salesorderid = $this->soId;
        $invoiceFocus->_recurring_mode = 'recurringinvoice_from_so';
        
        try {
            $invoiceFocus->save("Invoice");
        } catch (Exception $e) {
            //TODO - Review
        }
    }
    
    /*
     * removes non numeric characters and casts result to int.
     * E.g. With the argument 'net 07 days', this function returns (int) 7
     */
    private function getDaysNumberFromString($creditDays)
    {
        return (int) preg_replace("/[^0-9]/", '', $creditDays);
    }
    
    private function advanceNextInvoiceDate()
    {
        $frequency = $this->so['recurring_frequency'];
        $compansateDay = false;
        switch(strtolower($frequency)) {
            case 'daily' :
                $interval = '+1 day';
                break;
            case 'weekly':
                $interval = '+1 week';
                break;
            case 'monthly' :
                $interval = '+1 month';
                $compansateDay = true;
                break;
            case 'quarterly' :
                $interval = '+3 month';
                $compansateDay = true;
                break;
            case 'every 4 months' :
                $interval = '+4 month';
                $compansateDay = true;
                break;
            case 'half-yearly' :
                $interval = '+6 month';
                $compansateDay = true;
                break;
            case 'yearly' :
                $interval = '+1 year';
                $compansateDay = true;
                break;
            default :
                $interval = '';
        }
        
        //compensation is needed to handle cases when day of the month > 28 and the frequency is n months or yearly.
        //For example 31 january +1 month = 3 march. After compensation, instead of getting 3 march, we get 28 february.
        if ($compansateDay) {
            $dayOfMonthBeforeAddingPeriod = $this->nextInvoiceDate->format('j');
        }
        
        $this->nextInvoiceDate->modify($interval);
        
        if ($compansateDay) {
            $dayOfMonthAfterAddingPeriod =  $this->nextInvoiceDate->format('j');
            if ($dayOfMonthAfterAddingPeriod < $dayOfMonthBeforeAddingPeriod) {
                $this->nextInvoiceDate->modify('last day of previous month');
            }
        }
    }
}
