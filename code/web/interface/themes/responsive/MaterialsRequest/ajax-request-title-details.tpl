{strip}
{if !empty($error)}
	<div class="alert alert-danger">{$error}</div>
{else}
	<div>
		<table id="requestedMaterials" class="table tablesorter table-striped table-hover table-sticky">
			<thead>
			<tr>
				{foreach from=$columnsToDisplay item=label}
					<th>{translate text=$label isAdminFacing=true}</th>
				{/foreach}
			</tr>
			</thead>
			{foreach from=$materialsRequests item=materialsRequest}
				<tr>
					{foreach name="columnLoop" from=$columnsToDisplay item=label key=column}
						{if $column == 'dateCreated' || $column == 'dateUpdated'}
							{* Date Columns*}
							<td>{$materialsRequest->$column|date_format}</td>
						{else}
							{* All columns that can be displayed with out special handling *}
							<td>{$materialsRequest->$column}</td>
						{/if}
					{/foreach}
				</tr>
			{/foreach}
		</table>
	</div>
{/if}
{/strip}
