{strip}
	<h1>{translate text="Permissions by Role" isAdminFacing=true}</h1>

	<div class='adminTableRegion fixed-height-table'>
		<table id="permissionsByRole" class="adminTable table table-condensed table-hover smallText table-sticky">
			<thead>
				<tr>
					<th></th>
					{foreach from=$roles item=$role}
						<th>{$role->name}</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach from=$permissionsBySection item=sectionPermissions key=sectionName}
					<tr>
						<td colspan="{count($roles)+1}" class="text-center" style="background-color: #dddddd; font-size: small; font-weight: bold">{$sectionName}</td>
					</tr>
					{foreach from=$sectionPermissions item=permission}
						<tr>
							<td>{$permission}</td>
							{foreach from=$roles item=$role}
								<td class="text-center" style="border: 1px solid #dddddd">{if $role->hasPermission($permission)}X{/if}</td>
							{/foreach}
						</tr>
					{/foreach}
				{/foreach}
			</tbody>
		</table>
	</div>

	<br/>
	<a href="/Admin/PermissionsReport?exportToCSV" class="btn btn-default">{translate text="Export To CSV" isAdminFacing=true}</a>

{/strip}
