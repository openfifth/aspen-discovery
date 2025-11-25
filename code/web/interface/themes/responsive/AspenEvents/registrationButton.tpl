{strip}
{if !empty($loggedIn)}
	<button id="native-events-registration-button" type="button" class="btn btn-primary" onclick="return AspenDiscovery.Account.registerUserToEvent('{$eventSourceId}');">
		{translate text="Register" isPublicFacing=true}
	</button>
{else}
	<a id="native-events-login-redirect" href="{$path}/MyAccount/Login" class="btn btn-primary">
		{translate text="Login To Register" isPublicFacing=true}
	</a>
{/if}
{/strip}