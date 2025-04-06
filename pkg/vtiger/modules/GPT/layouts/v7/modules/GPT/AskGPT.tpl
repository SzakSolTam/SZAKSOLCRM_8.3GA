{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
    {if $TYPE eq 'Global'}
        <div class="globalgptcontainer">
    {else}
        <div class="mailgptcontainer">
    {/if}
        <div class="modal-md modal-dialog modelContainer">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right " >
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true" class='fa fa-close'></span>
                        </button>
                    </div>
                    <h4 class="pull-left" id="gptHeaderLabel">
                        {vtranslate('LBL_ASK_GPT', $MODULE)}
                    </h4>
                </div>
            </div>
            <div class="modal-content">
                <form id="gptEditForm" autocomplete="off">
                    <div class="modal-body">
                        <div class="form-group">
                            {if $TYPE eq 'Global'}
                                <div class="gpt-global-container" style="border: 1px solid black; height: 32px;">
                                    <textarea rows="5" id="AskGPTInput" class="inputElement textAreaElement col-lg-12 " data-rule-required="true" aria-required="true" placeholder="{vtranslate('LBL_GPT_PLACEHOLDER', $MODULE)}" style="resize: none; max-width:95%; border: none;"></textarea>
                                    <button id="getGlobalGPTResponse" style="border: none; background: none;">
                                        <i class="fa fa-paper-plane" style=" margin-top: 50%;"></i>
                                    </button>
                                </div>
                            {elseif $TYPE eq 'MailBody'}
                                <div class="gpt-mail-container" style="border: 1px solid black; height: 32px;">
                                    <textarea rows="5" id="AskGPTInputMail" class="inputElement textAreaElement col-lg-12 " data-rule-required="true" aria-required="true" placeholder="{vtranslate('LBL_GPT_PLACEHOLDER', $MODULE)}" style="resize: none; max-width:95%; border: none;"></textarea>
                                    <button id="getMailGPTResponse" style="border: none; background: none;">
                                        <i class="fa fa-paper-plane" style=" margin-top: 50%;"></i>
                                    </button>
                                </div>
                            {/if}
                        </div>
                    </div>
                    <div class="textAlignCenter" style="background-color: lightblue;padding-top: 10px; padding-bottom: 10px; font-weight: 500;">
                        <small>{vtranslate('LBL_GPT_INFO', $MODULE)}</small>
                    </div>
                    <div class="modal-footer ">
                        <center>
                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/strip}}