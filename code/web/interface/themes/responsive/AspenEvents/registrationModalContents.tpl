{strip}
{if !empty($loggedIn)}
	<input type="hidden" id="eventRegistrationUserId-{$eventSourceId}" value="{$userId}">
    {include file="AspenEvents/seats.tpl"}
    {include file="AspenEvents/registrationUserSelector.tpl"}
    {include file="AspenEvents/registrationUserDetails.tpl"}
    {include file="AspenEvents/customRegistrationForm.tpl"}
{/if}
{/strip}
