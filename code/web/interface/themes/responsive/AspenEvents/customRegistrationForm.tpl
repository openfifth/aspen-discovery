<div id="eventRegistrationFormContainer">
    {foreach from=$registrationFormStructure item=property}
		{if is_array($property) && isset($property.property) && isset($property.type)}
			{include file="DataObjectUtil/property.tpl"}
		{/if}
	{/foreach}
</div>