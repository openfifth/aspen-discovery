{strip}
{if !empty($loggedIn)}
	<div id="eventRegistrationUserDetails" class="well well-sm" style="margin-top: 10px;">
		<div><strong>{translate text="Email" isPublicFacing=true}:</strong> <span id="eventUserEmail">{$userEmail|escape}</span> <a id="eventUserEmailChangeLink" href="/MyAccount/ContactInformation" class="btn btn-xs btn-warning">{translate text="change" isPublicFacing=true}</a></div>
		<div><strong>{translate text="Home Branch" isPublicFacing=true}:</strong> <span id="eventUserLocation">{$userHomeLocation|escape}</span></div>
	</div>
{/if}
{/strip}
