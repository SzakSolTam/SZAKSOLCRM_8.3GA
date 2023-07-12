{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
    <div class="unmaskedcontainer">
        <div class="modal-md modal-dialog modelContainer residentVerificationContainer">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right " >
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true" class='fa fa-close'></span>
                        </button>
                    </div>
                    <h4 class="pull-left" id="unmaskedHeaderLabel">
                        {vtranslate('LBL_ASK_GPT', $MODULE)}
                    </h4>
                </div>
            </div>
            <div class="modal-content">
                <form id="unmaskedEditForm" autocomplete="off">
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="gpt-chat-container" style="border: 1px solid black; height: 32px;">
                                <textarea rows="5" id="AskGPTInput" class="inputElement textAreaElement col-lg-12 " name="gpt_prompt" data-rule-required="true" aria-required="true" placeholder="{vtranslate('LBL_GPT_PLACEHOLDER', $MODULE)}" style="resize: none; max-width:95%; border: none;"></textarea>
                                <button style="border: none; background: none;">
                                    <i class="fa fa-paper-plane" style=" margin-top: 50%;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="textAlignCenter" style="background-color: lightblue;padding-top: 10px; padding-bottom: 10px; font-weight: 500;">
                        <small>{vtranslate('LBL_GPT_INFO', $MODULE)}</small>
                    </div>
                    <div class="modal-footer ">
                        <center>
                            <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/strip}}