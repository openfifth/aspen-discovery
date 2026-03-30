{strip}
<div id="main-content" class="col-tn-12 col-xs-12">
	<h1>{translate text="Collection Reports" isAdminFacing=true}</h1>

	<h2>{translate text="Record Counts by Source" isAdminFacing=true}</h2>

	<table id="sourceCount" class="table table-bordered" aria-label="Record Counds by Source">
		<thead>
		<tr>
			<th>{translate text="Records Indexed" isAdminFacing=true}</th>
			<th>{translate text="Number Active" isAdminFacing=true}</th>
			<th>{translate text="Number Deleted" isAdminFacing=true}</th>
			<th>{translate text="Number Suppressed" isAdminFacing=true}</th>
		</tr>
		</thead>
		<tbody>
		{foreach $tableData as $row}
			<tr>
				<td>{$row.rowName}</td>
				<td>{$row.activeCount|default:'n/a'}</td>
				<td>{$row.deletedCount|default:'n/a'}</td>
				<td>{$row.suppressedCount|default:'n/a'}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>

	<h2>{translate text="Record Counts by Format" isAdminFacing=true}</h2>
	<table id="formatCount" class="table table-bordered" aria-label="Record Counts by Format">
		<thead>
		<tr>
			<th>{translate text="Format" isAdminFacing=true}</th>
			<th>{translate text="Source" isAdminFacing=true}</th>
			<th>{translate text="Number of Records" isAdminFacing=true}</th>
		</tr>
		</thead>
		<tbody>
		{foreach $formatTableData as $row}
			<tr>
				<td>{$row.format}</td>
				<td>{$row.source|default:'-'}</td>
				<td>{$row.numRecords|default:'-'}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
{/strip}
