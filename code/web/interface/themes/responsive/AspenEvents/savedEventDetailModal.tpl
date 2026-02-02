{strip}
{if !empty($loggedIn)}
	<input type="hidden" id="eventRegistrationUserId-{$event.sourceId}" value="{$userId}">
	{include file='AspenEvents/seats.tpl'  numberOfSeats="{$event.numberOfSeats}" availableSeats="{$event.availableSeats}" isEventFull="{$event.isEventFull}" userOnWaitingList="{$event.userOnWaitingList}" userWaitingListPosition="{$event.userWaitingListPosition}" userIsRegistered="{$event.isRegistered}" isWaitingListFull="{$event.waitingListFull}" userCanRegister="{$event.userCanRegister}"}
	<section class="well">
		{include file='AspenEvents/registrationUserSelector.tpl' eventSourceId="{$event.sourceId}"}
    	{include file='AspenEvents/registrationUserDetails.tpl' eventSourceId="{$event.sourceId}"}
		{include file='AspenEvents/registrationToggleButton.tpl' eventSourceId="{$event.sourceId}" isRegistered="{$event.isRegistered}" isEventFull="{$event.isEventFull}" userCanRegister="{$event.userCanRegister}"}
	</section>
{/if}
{/strip}