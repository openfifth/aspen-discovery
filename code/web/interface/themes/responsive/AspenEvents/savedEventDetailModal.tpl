{strip}
{* $event here refers to an event instance. *}
{if !empty($loggedIn)}
	<input type="hidden" id="eventRegistrationUserId-{$event.sourceId}" value="{$userId}">
	{include file='AspenEvents/seats.tpl'  numberOfSeats="{$event.numberOfSeats}" availableSeats="{$event.availableSeats}" isEventFull="{$event.isEventFull}" userOnWaitingList="{$event.userOnWaitingList}" userWaitingListPosition="{$event.userWaitingListPosition}" userIsRegistered="{$event.isRegistered}" isWaitingListFull="{$event.waitingListFull}" userCanRegister="{$event.userCanRegister}"}
	<section class="well">
		{include file='AspenEvents/registrationUserSelector.tpl' eventSourceId="{$event.sourceId}"}
    	{include file='AspenEvents/registrationUserDetails.tpl' eventSourceId="{$event.sourceId}"}
		{include file='AspenEvents/registrationToggleButton.tpl' eventSourceId="{$event.sourceId}" isRegistered="{$event.isRegistered}" isEventFull="{$event.isEventFull}" userCanRegister="{$event.userCanRegister}"}
	    {include file='AspenEvents/customRegistrationForm.tpl'  eventSourceId="{$event.sourceId}" isRegistered="{$event.isRegistered}"}
	</section>
{/if}

<script type="text/javascript">
	$(function () {
		// Clear form data when navigating back so user info is not retained.
		window.addEventListener('pageshow', function () {
			document.querySelectorAll('form[id^="objectEditor"]').forEach(form => form.reset());
		});
		const $borrowPass2 = $("#borrower_password2");
		if ($borrowPass2.length) {
			$borrowPass2.attr('data-rule-equalTo', "#borrower_password");
			$borrowPass2.attr('data-msg-equalTo', '{translate text="Passwords must match." isPublicFacing=true inAttribute=true}');
		}
	});
</script>
{/strip}