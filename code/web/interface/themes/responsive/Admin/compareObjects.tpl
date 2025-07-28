<div class="col-xs-12">
	<div class="row">
		<div class="col-xs-12 col-md-9">
			<h1 id="pageTitle">{translate text="Compare Objects" isAdminFacing=true}</h1>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div class="btn-group">
				{if !empty($showReturnToList)}
					<a class="btn btn-default" href='/{$module}/{$toolName}?objectAction=list'><i class="fas fa-arrow-alt-circle-left" role="presentation"></i> {translate text="Return to List" isAdminFacing=true}</a>
				{/if}
			</div>
		</div>
	</div>
</div>

{if !empty($error)}
	<div class="alert alert-danger">
		{$error}
	</div>
{else}
	<table class="adminTable table table-responsive table-condensed smallText">
		<thead>
			<tr>
				<th>{translate text="Property Name" isAdminFacing=true}</th>
				<th>{translate text="Value 1" isAdminFacing=true} <a href="{$object1EditUrl}" class="btn btn-sm btn-default">{translate text="Edit" isAdminFacing=true}</a></th>
				<th>{translate text="Value 2" isAdminFacing=true}  <a href="{$object2EditUrl}" class="btn btn-sm btn-default">{translate text="Edit" isAdminFacing=true}</a></th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$properties item=property}
			<tr>
				<td><strong>{translate text=$property.name isAdminFacing=true}</strong></td>
				<td class="{if !empty($property.uniqueProperty)}unique compareSame{elseif $property.value1 == $property.value2}compareSame{else}compareChanged{/if}">{$property.value1}</td>
				<td class="{if !empty($property.uniqueProperty)}unique compareSame{elseif $property.value1 == $property.value2}compareSame{else}compareChanged{/if}">{$property.value2}</td>
			</tr>
		{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<th></th>
				<th><a href="{$object1EditUrl}" class="btn btn-sm btn-default">{translate text="Edit" isAdminFacing=true}</a></th>
				<th><a href="{$object2EditUrl}" class="btn btn-sm btn-default">{translate text="Edit" isAdminFacing=true}</a></th>
			</tr>
		</tfoot>
	</table>
{/if}