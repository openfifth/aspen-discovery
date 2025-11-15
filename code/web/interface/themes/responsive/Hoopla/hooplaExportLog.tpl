{strip}
	<div id="main-content" class="col-md-12">
		<h1>{translate text="Hoopla Export Log" isAdminFacing=true}</h1>

		{include file='Admin/exportLogFilters.tpl'}
		<div class="adminTableRegion fixed-height-table">
			<table class="adminTable table table-condensed table-hover table-condensed smallText table-sticky">
				<thead>
					<tr>
						<th>{translate text="Id" isAdminFacing=true}</th>
						<th>{translate text="Started" isAdminFacing=true}</th>
						<th>{translate text="Last Update" isAdminFacing=true}</th>
						<th>{translate text="Finished" isAdminFacing=true}</th>
						<th>{translate text="Elapsed" isAdminFacing=true}</th>
						<th>{translate text="Products Regrouped" isAdminFacing=true}</th>
						<th>{translate text="Products Changed After Grouping" isAdminFacing=true}</th>
						<th>{translate text="Total Products" isAdminFacing=true}</th>
						<th>{translate text="Num Errors" isAdminFacing=true}</th>
						<th>{translate text="Products Added" isAdminFacing=true}</th>
						<th>{translate text="Products Deleted" isAdminFacing=true}</th>
						<th>{translate text="Products Updated" isAdminFacing=true}</th>
						{if !$hooplaVersion2}
							<th>{translate text="Products Skipped" isAdminFacing=true}</th>
						{else}
							<th>{translate text="Entitlements Updated" isAdminFacing=true}</th>
							<th>{translate text="Entitlements Deleted" isAdminFacing=true}</th>
						{/if}
						<th>{translate text="Num Availability Changes" isAdminFacing=true}</th>
						<th>{translate text="Notes" isAdminFacing=true}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$logEntries item=logEntry}
						<tr>
							<td>{$logEntry->id}</td>
							<td>{$logEntry->startTime|date_format:"%D %T"}</td>
							<td>{$logEntry->lastUpdate|date_format:"%D %T"}</td>
							<td>{$logEntry->endTime|date_format:"%D %T"}</td>
							<td>{$logEntry->getElapsedTime()}</td>
							<td>{$logEntry->numRegrouped}</td>
							<td>{$logEntry->numChangedAfterGrouping}</td>
							<td>{$logEntry->numProducts}</td>
							<td>{$logEntry->numErrors}</td>
							<td>{$logEntry->numAdded}</td>
							<td>{$logEntry->numDeleted}</td>
							<td>{$logEntry->numUpdated}</td>
							{if !$hooplaVersion2}
								<td>{$logEntry->numSkipped}</td>
							{else}
								<td>{$logEntry->numEntitlementsUpdated}</td>
								<td>{$logEntry->numEntitlementsDeleted}</td>
							{/if}
							<td>{$logEntry->numAvailabilityChanges}</td>
							<td><a href="#" onclick="return AspenDiscovery.Admin.showExtractNotes('{$logEntry->id}', 'hoopla');">{translate text="Show Notes" isAdminFacing=true}</a></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>

		{if !empty($pageLinks.all)}<div class="text-center">{$pageLinks.all}</div>{/if}
	</div>
{/strip}
