{if count($pickupLocations) > 0}
	<div class="form-group">
		<label class="control-label" for="pickupBranch">{translate text="Pickup Location" isPublicFacing=true}</label>
		<select name="pickupBranch" id="pickupBranch" class="form-control required" required>
			{foreach from=$pickupLocations item=location}
				<option value="{$location->code}" {if !empty($preSelectedPickupBranch)}{if $location->code == $preSelectedPickupBranch}selected{/if}{else}{if $location->code == $user->getPickupLocationCode()}selected{/if}{/if}>{$location->displayName|escape}</option>
			{/foreach}
		</select>
	</div>
{/if}
