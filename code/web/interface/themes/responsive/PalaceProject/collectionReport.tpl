<div id="main-content" class="col-md-12">
	<h1>{translate text="Collection Report" isAdminFacing=true}</h1>
	{foreach from=$allLibraries item="tmpLibrary"}
		<h2>{$tmpLibrary.displayName}</h2>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>{translate text="Palace Project Name" isAdminFacing=true}</th>
					<th>{translate text="Display Name" isAdminFacing=true}</th>
					<th style="text-align: right">{translate text="Active titles" isAdminFacing=true}</th>
					<th style="text-align: right">{translate text="Deleted titles" isAdminFacing=true}</th>
					<th style="text-align: right">{translate text="Titles Needing Holds" isAdminFacing=true}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$tmpLibrary.collections item="collection"}
					<tr>
						<td>{$collection.palaceProjectName}</td>
						<td>{$collection.displayName}</td>
						<td style="text-align: right">{$collection.numTitles|number_format}</td>
						<td style="text-align: right">{$collection.numDeletedTitles|number_format}</td>
						<td style="text-align: right">{$collection.numNeedingHolds|number_format}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	{foreachelse}
		<h2>{translate text="No libraries are active for Palace Project" isAdminFacing=true}</h2>
	{/foreach}
</div>
