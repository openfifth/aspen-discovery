{strip}
    {if !empty($listError)}<p class="error">{translate text=$listError isPublicFacing=true}</p>{/if}
	<form method="post" action="" name="createListGroupForm" class="form form-horizontal" id="createListGroupForm">
		<div class="form-group">
			<label for="newListGroupName" class="col-sm-3 control-label">{translate text='New List Group Name' isPublicFacing=true}</label>
			<div class="col-sm-9">
				<input type="text" name="newListGroupName" id="newListGroupName" class="form-control form-control-sm"/>
			</div>
		</div>

	    {if !empty($userListGroups)}
			<div class="form-group">
				<label for="newListGroupNesting" class="col-sm-3 control-label">{translate text='Nest within another group?' isPublicFacing=true}</label>
				<div class="col-sm-9">
					<select name="nestedWithinGroup" id="newListGroupNesting" class="form-control form-control-sm">
						<option value="none">{translate text='No, do not nest within another group' isPublicFacing=true}</option>
	                    {foreach from=$userListGroups item="listGroup"}
							<option value="{$listGroup->id}" {if $groupId == $listGroup->id}selected{/if}>{$listGroup->title|escape:"html"}</option>
	                    {/foreach}
					</select>
				</div>
			</div>
	    {/if}
	</form>

{/strip}