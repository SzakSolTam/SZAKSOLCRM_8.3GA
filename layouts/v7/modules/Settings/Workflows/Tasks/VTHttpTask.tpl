{strip}
<div class="row">
  <div class="col-sm-12">
    <div class="row form-group">
      <div class="col-sm-3">{vtranslate('LBL_ACTION_TITLE',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
      <div class="col-sm-9"><input name="title" data-rule-required="true" class="fields form-control" type="text" value="{$TASK_OBJECT->title}" /></div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</div>
      <div class="col-sm-9"><textarea name="description" class="fields form-control">{$TASK_OBJECT->description}</textarea></div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">{vtranslate('LBL_URL_TO_NOTIFY',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
      <div class="col-sm-9"><input name="url" data-rule-required="true" class="fields form-control" type="text" value="{$TASK_OBJECT->url}" /></div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">{vtranslate('LBL_METHOD',$QUALIFIED_MODULE)}</div>
      <div class="col-sm-9">
        <select name="method" class="select2">
          <option value="GET" {if $TASK_OBJECT->method eq 'GET'}selected{/if}>GET</option>
          <option value="POST" {if $TASK_OBJECT->method eq 'POST'}selected{/if}>POST</option>
          <option value="PUT" {if $TASK_OBJECT->method eq 'PUT'}selected{/if}>PUT</option>
          <option value="PATCH" {if $TASK_OBJECT->method eq 'PATCH'}selected{/if}>PATCH</option>
          <option value="DELETE" {if $TASK_OBJECT->method eq 'DELETE'}selected{/if}>DELETE</option>
          <option value="HEAD" {if $TASK_OBJECT->method eq 'HEAD'}selected{/if}>HEAD</option>
        </select>
      </div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">{vtranslate('LBL_CONTENT_TYPE',$QUALIFIED_MODULE)}</div>
      <div class="col-sm-9">
        <select name="content_type" class="select2">
          <option value="application/json" {if $TASK_OBJECT->content_type eq 'application/json'}selected{/if}>application/json</option>
          <option value="application/x-www-form-urlencoded" {if $TASK_OBJECT->content_type eq 'application/x-www-form-urlencoded'}selected{/if}>application/x-www-form-urlencoded</option>
          <option value="text/xml" {if $TASK_OBJECT->content_type eq 'text/xml'}selected{/if}>text/xml</option>
        </select>
      </div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">{vtranslate('LBL_AUTH_TYPE',$QUALIFIED_MODULE)}</div>
      <div class="col-sm-9">
        <select id="http_auth_type" name="auth_type" class="select2">
          <option value="none" {if $TASK_OBJECT->auth_type eq 'none'}selected{/if}>{vtranslate('LBL_NO_AUTH',$QUALIFIED_MODULE)}</option>
          <option value="basic" {if $TASK_OBJECT->auth_type eq 'basic'}selected{/if}>{vtranslate('LBL_BASIC_AUTH',$QUALIFIED_MODULE)}</option>
        </select>
      </div>
    </div>
    <div id="basic_auth_fields" class="row form-group {if $TASK_OBJECT->auth_type neq 'basic'}hide{/if}">
      <div class="col-sm-3">{vtranslate('LBL_USERNAME',$QUALIFIED_MODULE)}</div>
      <div class="col-sm-9">
        <input name="username" class="fields form-control" type="text" value="{$TASK_OBJECT->username}" />
        <input style="margin-top:5px" name="password" class="fields form-control" type="password" value="{$TASK_OBJECT->password}" />
      </div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">{vtranslate('LBL_CUSTOM_HEADERS',$QUALIFIED_MODULE)}</div>
      <div class="col-sm-9"><textarea name="headers" class="fields form-control">{$TASK_OBJECT->headers}</textarea></div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">{vtranslate('LBL_PARAMETERS',$QUALIFIED_MODULE)}</div>
      <div class="col-sm-9"><textarea name="parameters" class="fields form-control">{$TASK_OBJECT->parameters}</textarea></div>
    </div>
  </div>
</div>
<script type="text/javascript">
jQuery(function(){
  jQuery('#http_auth_type').change(function(){
    if(jQuery(this).val() == 'basic') jQuery('#basic_auth_fields').removeClass('hide');
    else jQuery('#basic_auth_fields').addClass('hide');
  });
});
</script>
{/strip}

