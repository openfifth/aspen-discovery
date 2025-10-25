{strip}
	<h1>{translate text="Your Lists" isPublicFacing=true}</h1>
	{if !empty($listGroups)}
		<div class="row" style="padding-bottom:1em;">
			<div class="form-group">
				<div class="col-xs-12">
					<select id="listGroupSelect" name="listGroupSelect" class="form-control" onchange="return AspenDiscovery.Account.loadListGroupData();">
	                    {foreach from=$listGroups item=listGroup}
							<option value="{$listGroup->id}" {if $inListGroup && $listGroup->id == $userList->listGroupId}selected{/if}>{$listGroup->title|escape:"html"}</option>
	                    {/foreach}
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
                <div id="listGroupListData">
	                {foreach from=$listGroupLastViewed item=list key="resultIndex"}
                        {include file='MyAccount/listDetails.tpl' list=$list}
                    {/foreach}
                </div>
			</div>
		</div>
	{else}
        {if empty($lists)}
			<div class="alert alert-info">
                {translate text="You have not created any lists yet." isPublicFacing=true}
			</div>
			<div class="row">
				<div class="col-xs-12">
					<div class="btn-toolbar">
						<button class="btn btn-sm btn-default" onclick="return AspenDiscovery.Account.showCreateListForm()">{translate text="Create a New List" isPublicFacing=true}</button>
                        {if !empty($showConvertListsFromClassic)}
							<a href="/MyAccount/ImportListsFromClassic" class="btn btn-sm btn-default">{translate text="Import From Old Catalog" isPublicFacing=true}</a>
                        {/if}
					</div>
				</div>
			</div>

        {else}
			<div class="row">
				<select id="results-sort" name="sort" aria-label="{translate text='Sort' isPublicFacing=true}" onchange="document.location.href = this.options[this.selectedIndex].value;" class="input-medium">
					<option value="?sort=title"{if $sortedBy == "title"} selected="selected"{/if}>{translate text='Sort by Title' isPublicFacing=true}</option>
					<option value="?sort=created"{if $sortedBy == "created"} selected="selected"{/if}>{translate text='Sort by Most Recently Created' isPublicFacing=true}</option>
					<option value="?sort=dateUpdated"{if $sortedBy == "dateUpdated"} selected="selected"{/if}>{translate text='Sort by Most Recently Updated' isPublicFacing=true}</option>
				</select>

				<div id="selected-browse-label">
					<div class="btn-group" id="hideSearchCoversSwitch"{if $displayMode != 'list'} style="display: none;"{/if}>
						<label for="hideCovers" class="checkbox{* control-label*}"> {translate text='Hide Covers' isPublicFacing=true}
							<input id="hideCovers" type="checkbox" onclick="AspenDiscovery.Account.toggleShowCovers(!$(this).is(':checked'))" {if $showCovers == false}checked="checked"{/if}>
						</label>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom: 20px;">
				<div class="col-xs-12">
					<div class="btn-toolbar">
						<button class="btn btn-sm btn-default" onclick="return AspenDiscovery.Account.showCreateListForm()">{translate text="Create a New List" isPublicFacing=true}</button>
                        {if count($lists) > 0}
							<button id="deleteSelectedListsBtn" onclick="return AspenDiscovery.Account.deleteSelectedLists()" class="btn btn-sm btn-danger" disabled>{translate text="Delete Selected Lists" isPublicFacing=true}</button>
                        {/if}
                        {if !empty($showConvertListsFromClassic)}
							<a href="/MyAccount/ImportListsFromClassic" class="btn btn-sm btn-default">{translate text="Import From Old Catalog" isPublicFacing=true}</a>
                        {/if}
					</div>
				</div>
			</div>

            {foreach from=$lists item="list" key="resultIndex"}
				<div class="row">
					<div class="selectList col-xs-12 col-sm-1">
						<input type="checkbox" name="selected[{$list->id}]" class="listSelect" id="selected{$list->id}" onchange="$('#deleteSelectedListsBtn').prop('disabled', $('.listSelect:checked').length === 0);">
					</div>
                    {include file='MyAccount/listDetails.tpl' list=$list}
				</div>
            {/foreach}
            {if !empty($pageLinks.all)}<div class="pagination">{$pageLinks.all}</div>{/if}
        {/if}
	{/if}
{/strip}