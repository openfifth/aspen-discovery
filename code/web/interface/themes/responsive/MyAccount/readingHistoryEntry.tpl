{strip}<div class="row result reading-history-entry" id="readingHistoryEntry{$record.id}">
	{* Checkbox Column *}
	<div class="selectTitle" style="display: none;">
		<input type="checkbox" name="selected[{$record.id}]" class="titleSelect" id="selected{$record.id}">
	</div>

	{* Cover Column *}
	{if !empty($showCovers)}
		<div class="coverColumn col-xs-3 col-sm-4 col-md-2 text-center">
			{if !empty($record.coverUrl)}
				{if !empty($record.recordId) && $record.linkUrl}
					<a href="{$record.linkUrl}" id="descriptionTrigger{$record.recordId|escape:"url"}" aria-hidden="true">
						<img src="{$record.coverUrl}" class="listResultImage img-thumbnail{if $useOriginalCoverUrls} use-original-covers{/if} img-responsive {$coverStyle}" alt="{translate text='Cover Image' inAttribute=true isPublicFacing=true}">
					</a>
				{else} {* Cover Image but no Record-View link *}
					<img src="{$record.coverUrl}" class="listResultImage img-thumbnail{if $useOriginalCoverUrls} use-original-covers{/if} img-responsive {$coverStyle}" alt="{translate text='Cover Image' inAttribute=true isPublicFacing=true}" aria-hidden="true">
				{/if}
			{/if}
		</div>
	{/if}

	{* Title Details Column *}
	<div class="titleColumn {if !empty($showCovers)}col-xs-9 col-sm-8 col-md-10{else}col-xs-12{/if}">
		<div class="row">
			<div class="col-xs-12 result-title notranslate">
				{$record.index})&nbsp;
				{if !empty($record.linkUrl)}
					<a href="{$record.linkUrl}" class="title">{if !$record.title|removeTrailingPunctuation} {translate text='Title not available' isPublicFacing=true}{else}{$record.title|removeTrailingPunctuation|truncate:180:"..."|highlight}{/if}</a>
				{else}
					{if !$record.title|removeTrailingPunctuation} {translate text='Title not available' isPublicFacing=true}{else}{$record.title|removeTrailingPunctuation}{/if}
				{/if}
				{if !empty($record.title2)}
					<div class="searchResultSectionInfo">
						{$record.title2|removeTrailingPunctuation|truncate:180:"..."|highlight}
					</div>
				{/if}
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 col-md-9">

				{if !empty($record.author)}
					<div class="row">
						<div class="result-label col-tn-3"> {translate text='Author' isPublicFacing=true}</div>
						<div class="result-value col-tn-9">
							{if is_array($record.author)}
								{foreach from=$summAuthor item=author}
									<a href='/Author/Home?author="{$author|escape:"url"}"'>{$author|highlight}</a>
								{/foreach}
							{else}
								<a href='/Author/Home?author="{$record.author|escape:"url"}"'>{$record.author|highlight}</a>
							{/if}
						</div>
					</div>
				{/if}

				{if !empty($record.format)}
					<div class="row">
						<div class="result-label col-tn-3">{translate text='Format' isPublicFacing=true}</div>
						<div class="result-value col-tn-9 reading-history-formats">
							{if is_array($record.format)}
								{assign var="uniqueFormats" value=array()}
								{foreach from=$record.format item=formatItem}
									{if !in_array($formatItem, $uniqueFormats)}
										{append var="uniqueFormats" value=$formatItem}
									{/if}
								{/foreach}
								{foreach from=$uniqueFormats item=formatItem name=formatLoop}
									<span class="format-chip">{translate text=$formatItem isPublicFacing=true}</span>{if !$smarty.foreach.formatLoop.last} {/if}
								{/foreach}
							{else}
								{if !empty($record.format)}
									{assign var="formatArray" value=","|explode:$record.format}
									{assign var="uniqueFormats" value=array()}
									{foreach from=$formatArray item=formatItem}
										{assign var="trimmedFormat" value=$formatItem|trim}
										{if !in_array($trimmedFormat, $uniqueFormats)}
											{append var="uniqueFormats" value=$trimmedFormat}
										{/if}
									{/foreach}
									{foreach from=$uniqueFormats item=formatItem name=formatLoop}
										<span class="format-chip">{translate text=$formatItem isPublicFacing=true}</span>{if !$smarty.foreach.formatLoop.last} {/if}
									{/foreach}
								{/if}
							{/if}
						</div>
					</div>
				{/if}

				<div class="row">
					<div class="result-label col-tn-3">{translate text='Last Used' isPublicFacing=true}</div>
					<div class="result-value col-tn-9">
						{if !empty($record.checkedOut)}
							{translate text="In Use" isPublicFacing=true}
						{else}
							{if is_numeric($record.checkout)}
								{$record.checkout|date_format:"%b %Y"}
							{else}
								{$record.checkout|escape}
							{/if}
						{/if}
					</div>
				</div>

				{if !empty($showRatings)}
					{if !empty($record.existsInCatalog) && !empty($record.ratingData)}
						<div class="row">
							<div class="result-label col-tn-3">{translate text="Rating" isPublicFacing=true}</div>
							<div class="result-value col-tn-9">
								{include file="GroupedWork/title-rating.tpl" id=$record.permanentId summId=$record.permanentId ratingData=$record.ratingData showNotInterested=false}
							</div>
						</div>
					{/if}
				{/if}

				{* Show checkout count badge and details toggle if multiple checkouts *}
				{if !empty($record.timesUsed) && $record.timesUsed > 1}
					<div class="row reading-history-meta">
						<div class="col-xs-12">
							<span class="reading-history-count-text">{translate text="Checked out %1% times" 1=$record.timesUsed isPublicFacing=true}</span>
							<a href="#" class="reading-history-toggle-details" data-target="readingHistoryDetails{$record.id}" aria-expanded="false">
								{translate text="Show Details" isPublicFacing=true} <i class="fa fa-chevron-down"></i>
							</a>
						</div>
					</div>
				{/if}
			</div>

			<div class="col-xs-12 col-md-3">
				<div class="btn-group btn-group-vertical btn-block">
					<a href="#" onclick='return AspenDiscovery.Account.ReadingHistory.deleteGroupedEntry("{$selectedUser}", "{$record.permanentId}", "{$record.title|escape:"javascript"}", "{$record.author|escape:"javascript"}", "{$record.id}");' class="btn btn-sm btn-primary">{translate text='Delete' isPublicFacing=true}</a>
				</div>
				{if !empty($showYouMightAlsoLike)}
					{if !$record.isIll}
						<div class="btn-group btn-group-vertical btn-block">
							{if !empty($record.existsInCatalog)}
								<button onclick="return AspenDiscovery.GroupedWork.getYouMightAlsoLike('{$record.permanentId}', '{$record.format}');" class="btn btn-sm btn-default btn-wrap">{translate text="You Might Also Like" isPublicFacing=true}</button>
							{/if}
						</div>
					{/if}
				{/if}
			</div>
		</div>

		{* Accordion section for multiple checkouts *}
		{if !empty($record.detailRecords) && $record.timesUsed > 1}
			<div class="row reading-history-details collapse" id="readingHistoryDetails{$record.id}">
				<div class="col-xs-12">
					<div class="panel panel-default">
						<div class="panel-body">
							<h4>{translate text="Checkout History" isPublicFacing=true}</h4>
							<div class="table-responsive">
								<table class="table table-striped table-condensed reading-history-detail-table">
									<thead>
										<tr>
											<th>{translate text="Checkout Date" isPublicFacing=true}</th>
											<th>{translate text="Return Date" isPublicFacing=true}</th>
											<th>{translate text="Format" isPublicFacing=true}</th>
											<th>{translate text="Source ID" isPublicFacing=true}</th>
											<th class="text-center">{translate text="Actions" isPublicFacing=true}</th>
										</tr>
									</thead>
									<tbody>
										{foreach from=$record.detailRecords item=detail}
											<tr id="readingHistoryDetailEntry{$detail.id}">
												<td>
													{if is_numeric($detail.checkOutDate)}
														{$detail.checkOutDate|date_format:"%b %d, %Y"}
													{else}
														{$detail.checkOutDate|escape}
													{/if}
												</td>
												<td>
													{if empty($detail.checkInDate)}
														<span class="label label-success">{translate text="Currently Checked Out" isPublicFacing=true}</span>
													{else}
														{if is_numeric($detail.checkInDate)}
															{$detail.checkInDate|date_format:"%b %d, %Y"}
														{else}
															{$detail.checkInDate|escape}
														{/if}
													{/if}
												</td>
												<td>{$detail.format|replace:',':', '}</td>
												<td>
													{if $detail.source != 'ils'}
														<span class="text-muted">{$detail.source}:</span>
													{/if}
													{$detail.sourceId}
												</td>
												<td class="text-center">
													<a href="#" onclick='return AspenDiscovery.Account.ReadingHistory.deleteIndividualEntry("{$selectedUser}", "{$detail.id}", "{$record.id}");' class="btn btn-xs btn-danger" title="{translate text='Delete this checkout' isPublicFacing=true inAttribute=true}">
														<i class="fa fa-trash"></i>
													</a>
												</td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}

		{if !empty($record.existsInCatalog)}
			<div class="row">
				<div class="col-xs-12">
					{include file='GroupedWork/result-tools-horizontal.tpl' recordDriver=$record.recordDriver summTitle=$record.title ratingData=$record.ratingData recordUrl=$record.linkUrl showMoreInfo=true showNotInterested=false}
				</div>
			</div>
		{/if}
	</div>

</div>
{/strip}
