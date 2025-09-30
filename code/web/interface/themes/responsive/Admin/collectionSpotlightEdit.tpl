{css filename="collectionSpotlight.css"}
{strip}
	<div id="main-content">
		<h1>{translate text="Edit Collection Spotlight" isAdminFacing=true}</h1>
		<div class="btn-group">
			<a class="btn btn-default" href="{$returnToListUrl|default:'/Admin/CollectionSpotlights'}"><i class="fas fa-arrow-alt-circle-left" role="presentation"></i> {translate text="Return to List" isAdminFacing=true}</a>
		</div>
		<div class="btn-group">
			<a class="btn btn-default" href="/Admin/CollectionSpotlights?objectAction=view&id={$object->id}"><i class="fas fa-eye" role="presentation"></i> {translate text="View" isAdminFacing=true}</a>
			<a class="btn btn-default" href="/API/SearchAPI?method=getCollectionSpotlight&id={$object->id}" target="_blank"><i class="fas fa-external-link-alt" role="presentation"></i> {translate text="Preview" isAdminFacing=true}</a>
		</div>
		{if !empty($canDelete)}
		<div class="btn-group">
			<a class="btn btn-danger" href="/Admin/CollectionSpotlights?objectAction=delete&id={$object->id}" onclick="return confirm('{translate text="Are you sure you want to delete %1%?" 1=$object->name inAttribute=true isAdminFacing=true}');"><i class="fas fa-trash" role="presentation"></i> {translate text="Delete" isAdminFacing=true}</a>
		</div>
		{/if}

		{$editForm}
	</div>
{/strip}

<script type="text/javascript">
	{literal}
	$(() => {
		AspenDiscovery.Admin.initializeScrollPositioning();
	});
	{/literal}
</script>