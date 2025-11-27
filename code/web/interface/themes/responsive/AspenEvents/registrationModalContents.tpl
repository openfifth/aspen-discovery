{strip}
{if !empty($loggedIn)}
	<input type="hidden" id="eventRegistrationUserId" value="{$userId}">
    {include file="AspenEvents/seats.tpl"}
    {include file="AspenEvents/registrationUserSelector.tpl"}
    {include file="AspenEvents/registrationUserDetails.tpl"}
{/if}
{/strip}
