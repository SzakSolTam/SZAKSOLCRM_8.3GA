<div class="modal-dialog modal-md">
	<div class="modal-content">
		<div class="quickCreateContent">
			{include file="ModalHeader.tpl"|vtemplate_path:Vtiger TITLE="<b><i class='fa fa-clone' aria-hidden='true'></i> {vtranslate('LBL_CREATE_ALL_INVOICES_FROM_SO', 'SalesOrder')}</b>"}
			
			<div class="modal-body">
				
				{if $DATES}
					<table class="massEditTable table">
						
						<tr>
							<td><b>#</b></td>
							<td class='fieldLabel col-lg-6'><b>{vtranslate('Invoice Date', 'Invoice')}</b></td>
							<td class='fieldLabel col-lg-6'><b>{vtranslate('Due Date', 'Invoice')}</b></td>
						</tr>
						{foreach item=date key=i from=$DATES}
							<tr>
								<td>{$i+1}</td>
								<td class='col-lg-6'>
									{DateTimeField::convertToUserFormat($date['invoicedate'])}
								</td>
								<td class='col-lg-6'>
									{DateTimeField::convertToUserFormat($date['duedate'])}
								</td>
							</tr>
						{/foreach}
						
					</table>
				{else}
					<div class="alert alert-danger">
						<p>{vtranslate('LBL_NO_INVICES_CAN_BE_CREATED', 'SalesOrder')}</p>
					</div>
				{/if}
			</div>
				
			<div class="modal-footer">
				<center>
					{if $DATES}
						<button class="btn btn-success" onclick="SalesOrder_Detail_Js.createAllInvoices()">{vtranslate('LBL_CREATE', 'Vtiger')}</button>
					{/if}
					<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
				</center>
			</div>
		</div>
	</div>
</div>