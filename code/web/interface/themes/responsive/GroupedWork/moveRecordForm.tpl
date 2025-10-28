{strip}
<form method="post" action="" name="popupForm" class="form-horizontal" id="moveRecordForm">
	<div class="alert alert-info">
		{translate text="This will move only this specific record to another grouped work. The record will stay in the target work even after reindexing." isAdminFacing=true}
	</div>
	<div class="alert alert-info">
		<div class="row">
			<div class="col-tn-12">
				{translate text="You are moving %1%:%2%" 1=$source 2=$identifier isAdminFacing=true}
			</div>
		</div>
		<div class="row">
			<div class="col-tn-3">
				{translate text="Currently in" isAdminFacing=true}
			</div>
			<div class="col-tn-9">
				<strong>{$currentWork->full_title}</strong> {translate text="by" isPublicFacing=true} <strong>{$currentWork->author}</strong>
			</div>
		</div>
	</div>

	{if !empty($existingOverride)}
	<div class="alert alert-warning">
		{translate text="This record already has a grouping override. Moving it will update the existing override." isAdminFacing=true}
	</div>
	{/if}

	<input type="hidden" name="recordId" id="recordId" value="{$recordId}"/>
	<div class="form-group">
		<label for="targetWorkId" class="col-sm-3">{translate text="Target Work" isAdminFacing=true} </label>
		<div class="col-sm-9">
			<input type="text" name="targetWorkId" id="targetWorkId" class="form-control" onkeyup="AspenDiscovery.GroupedWork.getMoveRecordInfo();" placeholder="{translate text='Enter grouped work permanent ID...' isAdminFacing=true inAttribute=true}">
		</div>
	</div>
	<div id="moveRecordInfo">
	</div>
</form>
{/strip}
