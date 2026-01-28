<form id="eventRegistrationForm-{$eventSourceId}" method="post">
<form method="post" id="eventRegistrationForm" name="eventRegistrationForm" class="form-horizontal" role="form" onsubmit="return AspenDiscovery.Account.saveEventRegistrationInformation()">
    {foreach from=$registrationFormStructure item=property}
    	{if is_array($property) && isset($property.property) && isset($property.type)}
			{$property.readOnly = $isRegistered}
    		{include file="DataObjectUtil/property.tpl"}
    	{/if}
    {/foreach}
</form>
