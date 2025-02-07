{strip}
<div id="record{if !empty($summShortId)}{$summShortId}{else}{$summId|escape}{/if}" class="resultsList row">
	<div class="col-tn-9 col-sm-9{if empty($viewingCombinedResults)} col-md-9 col-lg-10{/if}">
		<div class="row">
			<div class="col-xs-12">
				<span class="result-index">{$resultIndex})</span>&nbsp;
				<a href="{$summUrl}" class="result-title notranslate" onclick="AspenDiscovery.Summon.trackSummonUsage('{$summId}')" target="_blank">
					{if !$summTitle|removeTrailingPunctuation} {translate text='Title not available' isPublicFacing=true}{else}{$summTitle|removeTrailingPunctuation|truncate:180:"..."|highlight}{/if}
				</a>
			</div>
		</div>
		{if !empty($summDescription)}
			{* Standard Description *}
			<div class="row visible-xs">
				<div class="result-label col-tn-3">{translate text='Description' isPublicFacing=true}</div>
				<div class="result-value col-tn-8"><a id="descriptionLink{$summId|escape}" href="#" onclick="$('#descriptionValue{$summId|escape},#descriptionLink{$summId|escape}').toggleClass('hidden-xs');return false;">{translate text="Click to view" isPublicFacing=true}</a></div>
			</div>
			{* Mobile Description *}
			<div class="row">
				{* Hide in mobile view *}
				<div class="hidden-xs result-value col-sm-12" id="descriptionValue{$summId|escape}">
					{$summDescription|highlight|truncate_html:450:"..."}
				</div>
			</div>
		{/if}
	</div>
</div>
{/strip}