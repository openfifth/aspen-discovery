{strip}
<div id="main-content" class="col-md-12">
	<h1>{translate text="Manage Materials Requests by Title" isAdminFacing=true}</h1>
	{if !empty($error)}
		<div class="alert alert-danger">{$error}</div>
	{/if}
	{if !empty($updateMessage)}
		<div class="alert {if !empty($updateMessageIsError)}alert-danger{else}alert-success{/if}">
			{$updateMessage}
		</div>
	{/if}
	{if !empty($loggedIn)}
		{if count($allRequests) > 0}
			<form id="updateRequests" method="post" action="/MaterialsRequest/ManageRequests" class="form form-horizontal">
				<div class="form-group col-xs-4">
					<label for="pageSize" class="control-label">{translate text="Entries Per Page" isAdminFacing=true}&nbsp;</label>
					<select id="pageSize" name="pageSize" class="pageSize form-control input-sm" onchange="AspenDiscovery.changePageSize()">
						<option value="30"{if $materialsRequestsPerPage == 30} selected="selected"{/if}>30</option>
						<option value="50"{if $materialsRequestsPerPage == 50} selected="selected"{/if}>50</option>
						<option value="75"{if $materialsRequestsPerPage == 75} selected="selected"{/if}>75</option>
						<option value="100"{if $materialsRequestsPerPage == 100} selected="selected"{/if}>100</option>
						<option value="250"{if $materialsRequestsPerPage == 250} selected="selected"{/if}>250</option>
						<option value="500"{if $materialsRequestsPerPage == 500} selected="selected"{/if}>500</option>
						<option value="all"{if $showingAllRequests} selected="selected"{/if}>{translate text="All" isAdminFacing=true inAttribute=true}</option>
					</select>
				</div>
				<table id="requestedMaterials" class="table tablesorter table-striped table-hover table-sticky">
					<thead>
						<tr>
							<th><input type="checkbox" name="selectAll" id="selectAll" aria-label="{translate text="Select All" isAdminFacing=true inAttribute=true}" onchange="AspenDiscovery.toggleCheckboxes('.select', '#selectAll');"></th>
							{foreach from=$columnsToDisplay item=label}
								<th>{translate text=$label isAdminFacing=true}</th>
							{/foreach}
							<th>&nbsp;</th> {* Action Buttons Column *}
						</tr>
					</thead>
					{foreach from=$allRequests item=request}
						<tr>
							<td><input type="checkbox" name="select[{$request->id}]" class="select" aria-label="{translate text="Select Row" isAdminFacing=true inAttribute=true}"></td>
							{foreach name="columnLoop" from=$columnsToDisplay item=label key=column}
								{if $column == 'dateFirstRequested' || $column == 'dateLastRequested'}
									{* Date Columns*}
									<td>{$request->$column|date_format}</td>
								{else}
									{* All columns that can be displayed with out special handling *}
									<td>{$request->$column}</td>
								{/if}
							{/foreach}
							<td>
								<div class="btn-group btn-group-vertical btn-group-sm">
									{if $showExistingTitleInformation && !$request->hasExistingRecord}
										<button type="button" onclick="AspenDiscovery.MaterialsRequest.checkRequestForExistingRecord('{$request->id}')" class="btn btn-sm btn-info btn-wrap">{translate text="Check for Existing Title" isAdminFacing=true}</button>
									{/if}
									<button type="button" onclick="AspenDiscovery.MaterialsRequest.manageMaterialsTitleRequest('{$request->id}')" class="btn btn-sm btn-info btn-wrap">{translate text="Manage Requests" isAdminFacing=true}</button>
								</div>
							</td>
						</tr>
					{/foreach}
				</table>
				{if in_array('Manage Library Materials Requests', $userPermissions)}
					<div id="materialsRequestActions">
						<div class="row">
							<div class="col-xs-12">
								{if !empty($page)}
									<input type="hidden" name="page" value="{$page}">
								{/if}
								<input class="btn btn-default" type="submit" name="exportSelected" value="{translate text="Export Selected To CSV" inAttribute=true isAdminFacing=true}" onclick="return AspenDiscovery.MaterialsRequest.exportSelectedRequests();">
								<input class="btn btn-default" type="submit" name="exportAll" value="{translate text="Export All To CSV" inAttribute=true isAdminFacing=true}">
								<span class="btn btn-default" onclick="return AspenDiscovery.MaterialsRequest.updateSelectedRequests();">{translate text="Update Selected Requests" isAdminFacing=true}</span>
							</div>
						</div>
					</div>
				{/if}
				{if !empty($pageLinks.all)}
					<div class="text-center">{$pageLinks.all}</div>
				{/if}
			</form>
		{else}
			<div class="alert alert-info">{translate text="There are no materials requests that meet your criteria." isAdminFacing=true}</div>
		{/if}
	{/if}
</div>
{/strip}

<script type="text/javascript">
	$(function () {ldelim}
		$("#requestedMaterials").tablesorter({ldelim}
			cssAsc: 'sortAscHeader',
			cssDesc: 'sortDescHeader',
			cssHeader: 'unsortedHeader',
			widgets: ['zebra', 'filter'],
			headers: {ldelim}
				0: {ldelim}sorter: false{rdelim},
				{foreach name=config from=$dateColumns item=columnNumber}
				{$columnNumber+1}: {ldelim}sorter : 'date'{rdelim}{if empty($smarty.foreach.config.last)}, {/if}
				{/foreach}

			}
		});
	});
</script>
