{strip}
	<button id="aspen-events-toggle-registration-button-{$eventSourceId}" type="button" class="btn btn-primary" onclick="return AspenDiscovery.Account.toggleUserEventRegistration('{$eventSourceId}');">
		{if $isRegistered}
			{translate text="Unregister" isPublicFacing=true}
		{else}
			{if $isEventFull}
				{if $userCanRegister}
					{translate text="Register" isPublicFacing=true}
				{else}
					{translate text="Cannot Register - Event Full" isPublicFacing=true}
				{/if}
			{else}
				{translate text="Register" isPublicFacing=true}
			{/if}
		{/if}
	</button>
{/strip}