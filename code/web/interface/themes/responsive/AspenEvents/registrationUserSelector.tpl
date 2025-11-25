{strip}
{if !empty($loggedIn)}
	<input type="hidden" id="eventRegistrationUserId" value="{$userId}">
	{if !empty($linkedUsers)}
		<div class="form-group">
			<label for="eventUserSelector" class="control-label">{translate text="Register user" isPublicFacing=true}</label>
			<select id="eventUserSelector" name="selectedUser" class="form-control" onchange="AspenDiscovery.Account.updateEventRegistrationUser(this);">
				<option value="{$userId}" data-email="{$userEmail|escape}" data-location="{$userHomeLocation|escape}" selected>{$userDisplayName|escape}</option>
				{foreach from=$linkedUsers item=linkedUser}
					<option value="{$linkedUser->id}" data-email="{$linkedUser->email|escape}" data-location="{$linkedUser->getHomeLocationName()|escape}">{$linkedUser->getDisplayName()|escape}</option>
				{/foreach}
			</select>
		</div>
	{/if}
	<div id="eventRegistrationUserDetails" class="well well-sm" style="margin-top: 10px;">
		<div><strong>{translate text="Email" isPublicFacing=true}:</strong> <span id="eventUserEmail">{$userEmail|escape}</span></div>
		<div><strong>{translate text="Home Branch" isPublicFacing=true}:</strong> <span id="eventUserLocation">{$userHomeLocation|escape}</span></div>
	</div>
{/if}
{/strip}
