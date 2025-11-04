{strip}
	<form enctype="multipart/form-data" method="post" id="moveListGroupForm" action="/MyAccount/AJAX" class="form-horizontal">
		<input type="hidden" name="groupId" value="{$groupId}">
		<div>
			<div class="form-group">
				<label for="listGroupMove" class="col-sm-3">{translate text='Move to List Group' isPublicFacing=true} </label>
				<div class="col-sm-9">
					<select class="form-control" name="listGroupMove">
						<option value="null"></option>
                        {foreach from=$listGroups item="listGroup" key="resultIndex"}
                            {if ($listGroup->id != $groupId && $listGroup->id != $parentId)}
								<option value="{$listGroup->id}">{$listGroup->title}</option>
                            {/if}
                        {/foreach}
					</select>
				</div>
			</div>
		</div>
	</form>
	<script type="application/javascript">
        {literal}
		$("#moveListGroupForm").validate({
			submitHandler: function(){
				AspenDiscovery.Account.editListGroupParentForm()
			}
		});
        {/literal}
	</script>
{/strip}