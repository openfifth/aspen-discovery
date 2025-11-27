{strip}
{* $event here refers to an event instance. *}
{if !empty($loggedIn)}
	<input type="hidden" id="eventRegistrationUserId-{$event.sourceId}" value="{$userId}">
	{include file='AspenEvents/seats.tpl'}
	<section class="well">
		{include file='AspenEvents/registrationUserSelector.tpl' eventSourceId="{$event.sourceId}"}
    	{include file='AspenEvents/registrationUserDetails.tpl' eventSourceId="{$event.sourceId}"}
		{include file='AspenEvents/registrationToggleButton.tpl' eventSourceId="{$event.sourceId}" isRegistered="{$event.isRegistered}"}
	</section>
{/if}
{/strip}