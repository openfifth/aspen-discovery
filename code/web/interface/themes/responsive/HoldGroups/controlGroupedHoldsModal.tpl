{strip}
<div>
	<div id="selectHoldGroupComments">
		{if $holdGroupMap|@count == 0}
			<p class="alert alert-info">
				{translate text="You have no hold groups." isAdminFacing=true}
			</p>
		{else}
			<p class="alert alert-info">
				{translate text="Please select a hold group from the list below." isAdminFacing=true}
			</p>
		{/if}
	</div>
	{if $holdGroupMap|@count > 0}
		<form method="post" name="selectHoldGroupForm" id="selectHoldGroupForm" action="MyAccount/AJAX" class="form">
			<input type="hidden" name="method" value="processSelectedHoldGroup">
			<div class="form-group">
				<label for="holdGroupSelect" class="control-label">
					{translate text="Hold Group" isAdminFacing=true}
					<a style="margin-right: .5em; margin-left: .25em" id="holdGroupTooltip" class="text-info" role="tooltip" tabindex="0" data-toggle="tooltip" data-placement="right" data-title="{translate text='Select the hold group you want to manage.' isAdminFacing=true inAttribute=true}">
						<i class="fas fa-question-circle"></i>
					</a>
					<span class="label label-danger" style="margin-right: .5em;">{translate text="Required" isAdminFacing=true}</span>
				</label>
				<select id="holdGroupSelect" name="holdGroupId" class="form-control required">
					<option value="">{translate text="-- Select Hold Group --" isAdminFacing=true}</option>
					{foreach from=$holdGroupMap key=visualHoldGroupId item=holdGroupId}
						<option value="{$holdGroupId}">{translate text="Hold Group %1%" 1=$visualHoldGroupId isAdminFacing=true}</option>
					{/foreach}
				</select>
			</div>
		</form>
	{/if}
</div>
{/strip}