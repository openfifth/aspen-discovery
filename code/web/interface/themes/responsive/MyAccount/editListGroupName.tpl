{strip}
	<form enctype="multipart/form-data" method="post" id="renameListGroupForm" action="/MyAccount/AJAX" class="form-horizontal">
		<input type="hidden" name="groupId" value="{$groupId}">
		<div>
			<div class="form-group">
				<label for="listGroupNameNew" class="col-sm-3">{translate text='Rename Group To' isPublicFacing=true} </label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="listGroupNameNew" name="listGroupNameNew">
				</div>
			</div>
		</div>
	</form>
	<script type="application/javascript">
        {literal}
		$("#renameListGroupForm").validate({
			submitHandler: function(){
				AspenDiscovery.Account.editListGroupNameForm()
			}
		});
        {/literal}
	</script>
{/strip}