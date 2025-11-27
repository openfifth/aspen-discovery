{strip}
{if !empty($loggedIn)}
	<input type="hidden" id="eventRegistrationUserId" value="{$userId}">
	{include file='AspenEvents/seats.tpl'}
	<section class="well">
		{include file='AspenEvents/registrationUserSelector.tpl'}
    	{include file="AspenEvents/registrationUserDetails.tpl"}
		{include file='AspenEvents/registrationToggleButton.tpl'}
	</section>
{/if}
{/strip}