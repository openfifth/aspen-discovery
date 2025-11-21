{strip}
	<div>
		{if $recordDriver->getPrimaryAuthor()}
			<div class="row">
				<div class="result-label col-md-3">{translate text="Author" isPublicFacing=true} </div>
				<div class="col-md-9 result-value notranslate">
					<a href='/Author/Home?author="{$recordDriver->getPrimaryAuthor()|escape:"url"}"'>{$recordDriver->getPrimaryAuthor()|highlight}</a>
				</div>
			</div>
		{/if}
		{if $recordDriver->hasCachedSeries()}
			{assign var=summSeries value=$recordDriver->getSeries(false)}
			{if !empty($summSeries) && empty($summSeries.allHidden)}
				<div class="series row">
					<div class="result-label col-md-3">{translate text="Series" isPublicFacing=true} </div>
					<div class="col-md-9 result-value">
						{assign var=seriesLimit value=$numSeriesToShowBeforeMore+1}
						{assign var=totalSeriesShown value=0}
						{if !empty($summSeries.fromNovelist)}
							{assign var=totalSeriesShown value=$totalSeriesShown+1}
							<a href="/GroupedWork/{$recordDriver->getPermanentId()}/Series">{$summSeries.seriesTitle}</a>{if !empty($summSeries.volume)}<strong> {translate text="volume %1%" 1=$summSeries.volume|format_float_with_min_decimals isPublicFacing=true}</strong>{/if}<br>
							{if !empty($summSeries.additionalSeries)}
								{foreach from=$summSeries.additionalSeries item=additional}
									{assign var=totalSeriesShown value=$totalSeriesShown+1}
									{if $totalSeriesShown == $seriesLimit}
										<a onclick="$('#moreSeries').show();$('#moreSeriesLink').hide();" id="moreSeriesLink">{translate text='More Series...' isPublicFacing=true}</a>
										<div id="moreSeries" style="display:none">
									{/if}
									<a href="/Search/Results?searchIndex=Series&lookfor={$additional.seriesTitle}&sort=year+asc%2Ctitle+asc">{$additional.seriesTitle}</a>{if !empty($additional.volume)}<strong> {translate text="volume %1%" 1=$additional.volume|format_float_with_min_decimals isPublicFacing=true}</strong>{/if}<br>
								{/foreach}
								{if $totalSeriesShown >= $seriesLimit}
									</div>
								{/if}
							{/if}
						{elseif !empty($summSeries.fromSeriesIndex)}
							{if !$summSeries.hidden}
								{assign var=totalSeriesShown value=$totalSeriesShown+1}
								<a href="/Series/{$summSeries.seriesId}">{$summSeries.seriesTitle}</a>{if !empty($summSeries.volume)}<strong> {translate text="volume %1%" 1=$summSeries.volume|format_float_with_min_decimals isPublicFacing=true}</strong>{/if}<br>
							{/if}
							{if !empty($summSeries.additionalSeries)}
								{foreach from=$summSeries.additionalSeries item=additional}
									{if !$additional.hidden}
										{assign var=totalSeriesShown value=$totalSeriesShown+1}
										{if $totalSeriesShown == $seriesLimit}
											<a onclick="$('#moreSeries').show();$('#moreSeriesLink').hide();" id="moreSeriesLink">{translate text='More Series...' isPublicFacing=true}</a>
											<div id="moreSeries" style="display:none">
										{/if}
										<a href="/Series/{$additional.seriesId}">{$additional.seriesTitle}</a>{if !empty($additional.volume)}<strong> {translate text="volume %1%" 1=$additional.volume|format_float_with_min_decimals isPublicFacing=true}</strong>{/if}<br>
									{/if}
								{/foreach}
								{if $totalSeriesShown >= $seriesLimit}
									</div>
								{/if}
							{/if}
						{elseif !empty($summSeries.seriesTitle)}
							{assign var=totalSeriesShown value=$totalSeriesShown+1}
							<a href="/Search/Results?searchIndex=Series&lookfor={$summSeries.seriesTitle}&sort=year+asc%2Ctitle+asc">{$summSeries.seriesTitle}</a>{if !empty($summSeries.volume)}<strong> {translate text="volume %1%" 1=$summSeries.volume|format_float_with_min_decimals isPublicFacing=true}</strong>{/if}<br>
							{if !empty($summSeries.additionalSeries)}
								{foreach from=$summSeries.additionalSeries item=additional}
									{assign var=totalSeriesShown value=$totalSeriesShown+1}
									{if $totalSeriesShown == $seriesLimit}
										<a onclick="$('#moreSeries').show();$('#moreSeriesLink').hide();" id="moreSeriesLink">{translate text='More Series...' isPublicFacing=true}</a>
										<div id="moreSeries" style="display:none">
									{/if}
									<a href="/Search/Results?searchIndex=Series&lookfor={$additional.seriesTitle}&sort=year+asc%2Ctitle+asc">{$additional.seriesTitle}</a>{if !empty($additional.volume)}<strong> {translate text="volume %1%" 1=$additional.volume|format_float_with_min_decimals isPublicFacing=true}</strong>{/if}<br>
								{/foreach}
								{if $totalSeriesShown >= $seriesLimit}
									</div>
								{/if}
							{/if}
						{/if}
					</div>
				</div>
			{/if}
		{/if}
		{if $recordDriver->getDescriptionFast()}
			<div class="row">
				<div class="col-sm-12">
					<span class="result-label">{translate text="Description" isPublicFacing=true} </span>
				</div>
				<div class="col-sm-12">
					{$recordDriver->getDescriptionFast()|stripTags:'<b><p><i><em><strong><ul><li><ol>'}{*Leave unescaped because some syndetics reviews have html in them *}
				</div>
			</div>
		{/if}
		{include file="GroupedWork/allManifestations.tpl" relatedManifestations=$recordDriver->getRelatedManifestations() inPopUp=true summId=$recordDriver->getPermanentId() id=$recordDriver->getPermanentId() summTitle=$recordDriver->getTitle() isSearchResults=true}
	</div>
{/strip}
