
<h3>{$ruleActionHeader}</h3>

<div class="crm-block crm-form-block crm-civirule-rule_action-block-contact_subtype">

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}
    {if $descriptions.$elementName}
      <div class="description">{$descriptions.$elementName}</div>
    {/if}
    </div>
    <div class="clear"></div>
  </div>
{/foreach}
</div>
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{literal}
<script type="text/javascript">
(function($) {
 var opElem = $("input[name=update_operation]"),
   statusElem = $("select[name=membership_status]"),
   statusSection = statusElem.parents('.crm-section'),
   unsetOverrideElem = $("input[name=unset_status_override]");
   console.log(unsetOverrideElem);
   opElem.change(function() {
    var val = opElem.filter(":checked").val();
    statusSection.toggle(val === 'fixed_value');
    statusElem.val(val === 'fixed_value' ? statusElem.val() : 0);
  }).trigger('change');
}(CRM.$))
</script>
{/literal}
