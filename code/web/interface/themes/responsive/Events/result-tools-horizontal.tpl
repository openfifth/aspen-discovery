{strip}
	{if $showComments || $showFavorites || !empty($showEmailThis) || !empty($showShareOnX) || !empty($showShareOnFacebook) || !empty($showShareOnPinterest) || !empty($showShareOnLink)}
		<div class="result-tools-horizontal btn-toolbar" role="toolbar">
			{* More Info Link, only if we are showing other data *}
			{if $showMoreInfo || $showComments || $showFavorites}
				{if $showMoreInfo !== false}
					<div class="btn-group btn-group-sm">
						{if $bypassEventPage}
							<a href="{$recordDriver->getExternalUrl()}" class="btn btn-sm btn-tools" target="_blank" aria-label="{translate text="More Info" isPublicFacing=true inAttribute=true} ({translate text="opens in a new window" isPublicFacing=true inAttribute=true})"><i class="fas fa-external-link-alt" role="presentation"></i> {translate text="More Info" isPublicFacing=true}</a>
						{else}
							<a href="{if !empty($eventUrl)}{$eventUrl}{else}{$recordDriver->getExternalUrl()}{/if}" class="btn btn-sm btn-tools">{translate text="More Info" isPublicFacing=true}</a>
						{/if}
						{if $isStaff && $eventsInLists == 1 || $eventsInLists == 2}
							<button onclick="return AspenDiscovery.Account.showSaveToListForm(this, 'Events', '{$recordDriver->getUniqueID()|escape}');" class="btn btn-sm btn-tools addToListBtn">{translate text="Add to List" isPublicFacing=true}</button>
						{/if}
						{if $recordDriver->getIntegration() == 'event_aspenEvent'}
							{if $upcomingInstanceCount > 1}
								<div class="btn-group">
									<button data-toggle="dropdown" class="btn btn-sm btn-tools btn-default dropdown-toggle" aria-haspopup="true" aria-expanded="false" id="export_{$recordDriver->getUniqueID()|escape}">
										{translate text="Export" isPublicFacing=true}&nbsp;
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="export_{$recordDriver->getUniqueID()|escape}">
										<li><a href="#" onclick="return AspenDiscovery.Events.iCalendarExport('{$recordDriver->getUniqueID()|escape}', 'event_aspenEvent', 0);">{translate text="Only this event" isPublicFacing="true"}</a></li>
										<li><a onclick="return AspenDiscovery.Events.iCalendarExport('{$recordDriver->getUniqueID()|escape}', 'event_aspenEvent', 1);" href="#">{translate text="All upcoming events in this series" isPublicFacing="true"}</a></li>
									</ul>
								</div>
							{else}
								<button class="btn btn-sm btn-tools btn-default" onclick="return AspenDiscovery.Events.iCalendarExport('{$recordDriver->getUniqueID()|escape}', 'event_aspenEvent', 0);">
									{translate text="Export" isPublicFacing=true}
								</button>
							{/if}
							{else}
							<button class="btn btn-sm btn-tools btn-default" onclick="return AspenDiscovery.Events.iCalendarExport('{$recordDriver->getUniqueID()|escape}', '{$recordDriver->getIntegration()|escape}', 0);">
								{translate text="Export" isPublicFacing=true}
							</button>
						{/if}
					</div>
				{/if}
			{else}
				{if $isStaff && $eventsInLists == 1 || $eventsInLists == 2}
					<div class="btn-group btn-group-sm">
						<button onclick="return AspenDiscovery.Account.showSaveToListForm(this, 'Events', '{$recordDriver->getUniqueID()|escape}');" class="btn btn-sm btn-tools addToListBtn">{translate text="Add to List" isPublicFacing=true}</button>
					</div>
				{/if}
			{/if}

			<div class="btn-group btn-group-sm">
				{include file="Events/share-tools.tpl"}
			</div>
		</div>
	{/if}
{/strip}
