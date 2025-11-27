{strip}
	{if !empty($loggedIn)}
		<button id="aspen-events-toggle-registration-button-{$eventSourceId}" type="button" class="btn btn-primary" onclick="return AspenDiscovery.Account.toggleUserEventRegistration('{$eventSourceId}');">
			{if $isRegistered}
				{translate text="Unregister" isPublicFacing=true}
			{else}
				{translate text="Register" isPublicFacing=true}
			{/if}
		</button>
	{else}
		<a id="aspen-events-login-redirect" href="{$path}/MyAccount/Login" class="btn btn-primary">
			{translate text="Login To Register" isPublicFacing=true}
		</a>
	{/if}	
{/strip}