{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
    <div class="gptResponseContainer">
        <div class="modal-md modal-dialog modelContainer">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right " >
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true" class='fa fa-close'></span>
                        </button>
                    </div>
                    <h4 class="pull-left" id="gptHeaderLabel">
                        {vtranslate('GPT_RESPONSE', $MODULE)}
                    </h4>
                </div>
            </div>
            <div class="modal-content">
                <form autocomplete="off">
                    <div class="modal-body">
                        <div class="form-group" style="margin-bottom: 35px">
                            <textarea rows="5" class="inputElement textAreaElement col-lg-12"  aria-required="true" style="resize: none;" readonly>{$QUERY}</textarea>
                        </div>
                        <div class="form-group">
                            <textarea rows="5" id="gptResponseField" class="inputElement textAreaElement col-lg-12 " data-rule-required="true" aria-required="true">{$RESPONSE}</textarea>
                        </div>
                    </div>
                    <div class="textAlignCenter" style="background-color: lightblue;padding-top: 10px; padding-bottom: 10px; font-weight: 500;">
                        <small>{vtranslate('LBL_GPT_INFO', $MODULE)}</small>
                    </div>
                    <div class="modal-footer ">
                        <center>
                            <div id="gptmodalCancelLink">
                                <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/strip}