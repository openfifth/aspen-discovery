{strip}
	{if $registrationAction == 'registered'}
		<button id="aspen-events-toggle-registration-button-{$eventSourceId}" type="button" class="btn btn-primary" onclick="return AspenDiscovery.Account.toggleUserEventRegistration('{$eventSourceId}');">
			{translate text='Unregister' isPublicFacing=true}
		</button>
	{elseif $registrationAction == 'completeRegistration' || $registrationAction == 'registrationAvailable'}
		<button id="aspen-events-toggle-registration-button-{$eventSourceId}" type="button" class="btn btn-primary" onclick="return AspenDiscovery.Account.toggleUserEventRegistration('{$eventSourceId}');">
			{if $registrationAction == 'completeRegistration'}
				{translate text='Complete Your Registration' isPublicFacing=true}
			{else}
				{translate text='Register' isPublicFacing=true}
			{/if}
		</button>
	{elseif $registrationAction == 'eventFull'}
		<button id="aspen-events-toggle-registration-button-{$eventSourceId}" type="button" class="btn btn-primary" disabled>
			{translate text='Cannot Register - Event Full' isPublicFacing=true}
		</button>
	{/if}
{/strip}