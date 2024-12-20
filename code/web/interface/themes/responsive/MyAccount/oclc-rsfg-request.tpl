{strip}
<div class="result row" id="oclcRSFGHold_{$record->sourceId|escapeCSS}_{$record->cancelId|escapeCSS}">
	{if !empty($showCovers)}
		<div class="{if $section == 'available'}col-xs-4 col-sm-3{else}col-xs-3 col-sm-2{/if}">
			<div class="text-center">
				{if !empty($record->getCoverUrl())}
					{if !empty($record->getLinkUrl())}
						<a href="{$record->getLinkUrl()}" id="descriptionTrigger{$record->recordId|escape:"url"}" aria-hidden="true">
							<img src="{$record->getCoverUrl()}" class="listResultImage img-thumbnail img-responsive {$coverStyle}" alt="{translate text='Cover Image' inAttribute=true isPublicFacing=true}">
						</a>
					{else}
						<img src="{$record->getCoverUrl()}" class="listResultImage img-thumbnail img-responsive {$coverStyle}" alt="{translate text='Cover Image' inAttribute=true isPublicFacing=true}" aria-hidden="true">
					{/if}
				{/if}

			</div>
		</div>
	{/if}
	<div class="{if !empty($showCovers)}col-xs-8 col-sm-9{else}{if $section != 'available'}col-xs-11{else}col-xs-12{/if}{/if}">
		<div class="row">
			<div class="col-xs-12">
				<span class="result-index">{$resultIndex}</span>&nbsp;
					<a href="/OCLCRSFG/OCLCRSFGRequestDetails?requestId={$record->cancelId}" class="result-title notranslate">
						{if !$record->getTitle()|removeTrailingPunctuation} {translate text='Title not available' isPublicFacing=true}{else}{$record->getTitle()|removeTrailingPunctuation|truncate:180:"..."|highlight}{/if}
					</a>
			</div>
		</div>
		<div class="row">
			<div class="resultDetails col-xs-12 col-md-8 col-lg-9">
				{if !empty($record->getAuthor())}
					<div class="row">
						<div class="result-label col-tn-4">{translate text='Author' isPublicFacing=true}</div>
						<div class="col-tn-8 result-value">
							{if is_array($record->getAuthor())}
								{foreach from=$record->getAuthor() item=author}
									<a href='/Author/Home?"author={$author|escape:"url"}"'>{$author|highlight}</a>
								{/foreach}
							{else}
								<a href='/Author/Home?author="{$record->getAuthor()|escape:"url"}"'>{$record->getAuthor()|highlight}</a>
							{/if}
						</div>
					</div>
				{/if}
				{if !empty($hasLinkedUsers)}
				<div class="row">
					<div class="result-label col-tn-4">{translate text='On Hold For' isPublicFacing=true}</div>
					<div class="col-tn-8 result-value">
						{$record->getUserName()|escape}
					</div>
				</div>
				{/if}
				<div class="row">
					<div class="result-label col-tn-4">{translate text='Pickup Location' isPublicFacing=true}</div>
					<div class="col-tn-8 result-value">
						{$record->pickupLocationName|escape}
					</div>
				</div>
				{if !empty($showPlacedColumn) && $record->createDate}
					<div class="row">
						<div class="result-label col-tn-4">{translate text='Date Placed' isPublicFacing=true}</div>
						<div class="col-tn-8 result-value">
							{$record->createDate|date_format:"%b %d, %Y"}
						</div>
					</div>
				{/if}
				<div class="row">
					<div class="result-label col-tn-4">{translate text='Status' isPublicFacing=true}</div>
					<div class="col-tn-8 result-value">
						{if $record->frozen}
							<span class="frozenHold label label-warning">
						{/if}
						{translate text=$record->status isPublicFacing=true}
					</div>
				</div>
				<div class="row">
					<div class="col-tn-8 result-value">
						<span class="label label-primary">
						{translate text='This is an interlibrary loan request' isPublicFacing=true}
						</span>
					</div>
				</div>
			</div>
			<div class="col-xs-9 col-sm-8 col-md-4 col-lg-3">
				{if !empty($showWhileYouWait)}
					<div class="btn-group btn-group-vertical btn-block">
						{if !empty($record->getGroupedWorkId())}
							<button onclick="return AspenDiscovery.GroupedWork.getWhileYouWait('{$record->getGroupedWorkId()}');" class="btn btn-sm btn-default btn-wrap">{translate text="While You Wait" isPublicFacing=true}</button>
						{/if}
					</div>
				{/if}
			</div>
		</div>
	</div>
</div>
{/strip}