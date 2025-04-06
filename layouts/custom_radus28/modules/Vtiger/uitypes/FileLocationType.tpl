{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{assign var=FIELD_VALUES value=$FIELD_MODEL->getFileLocationType()}
{* The options displayed based on the file location type received on request *}
<select class="select2" name="{$FIELD_MODEL->getFieldName()}" {if $FILE_LOCATION_TYPE eq 'I' OR $FILE_LOCATION_TYPE eq 'E'} disabled {/if}>
{foreach item=TYPE key=KEY from=$FIELD_VALUES}
    {if $FILE_LOCATION_TYPE eq 'I'}
        {assign var=SELECTED value='I'}
    {elseif $FILE_LOCATION_TYPE eq 'E'}
        {assign var=SELECTED value='E'}
    {else}
        {assign var=SELECTED value=$FIELD_MODEL->get('fieldvalue')}
    {/if}
    <option value="{$KEY}" {if $SELECTED eq $KEY} selected {/if}>{vtranslate($TYPE, $MODULE)}</option>
{/foreach}
</select>
{/strip}