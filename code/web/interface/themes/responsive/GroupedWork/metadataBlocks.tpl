{if !empty($summAuthor)}
	<div class="result-label col-sm-4 col-xs-12">{translate text="Author" isPublicFacing=true} </div>
	<div class="result-value col-sm-8 col-xs-12 notranslate">
		{if is_array($summAuthor)}
			{foreach from=$summAuthor item=author}
				<a href='/Author/Home?author="{$author|escape:"url"}"'>{$author|highlight}</a>
			{/foreach}
		{else}
			<a href='/Author/Home?author="{$summAuthor|escape:"url"}"'>{$summAuthor|highlight}</a>
		{/if}
	</div>
{/if}
{if !empty($showSeries)}
	{if $summSeries && empty($summSeries.allHidden)}
		<div class="series{$summISBN}">
			<div class="result-label col-sm-4 col-xs-12">{translate text="Series" isPublicFacing=true} </div>
			<div class="result-value col-sm-8 col-xs-12">
				{assign var=seriesLimit value=$numSeriesToShowBeforeMore+1}
				{assign var=totalSeriesShown value=0}
				{if !empty($summSeries)}
					{if !empty($summSeries.fromNovelist)}
						{assign var=totalSeriesShown value=$totalSeriesShown+1}
						<a href="/GroupedWork/{$summId}/Series">{$summSeries.seriesTitle}</a>{if !empty($summSeries.volume)} <strong>{translate text=volume isPublicFacing=true} {$summSeries.volume|format_float_with_min_decimals}</strong>{/if}<br>
						{if !empty($summSeries.additionalSeries)}
							{foreach from=$summSeries.additionalSeries item=additional}
								{assign var=totalSeriesShown value=$totalSeriesShown+1}
								{if $totalSeriesShown == $seriesLimit}
									<a onclick="$('#moreSeries_{$summId}').show();$('#moreSeriesLink_{$summId}').hide();" id="moreSeriesLink_{$summId}">{translate text='More Series...' isPublicFacing=true}</a>
									<div id="moreSeries_{$summId}" style="display:none">
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
										<a onclick="$('#moreSeries_{$summId}').show();$('#moreSeriesLink_{$summId}').hide();" id="moreSeriesLink_{$summId}">{translate text='More Series...' isPublicFacing=true}</a>
										<div id="moreSeries_{$summId}" style="display:none">
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
									<a onclick="$('#moreSeries_{$summId}').show();$('#moreSeriesLink_{$summId}').hide();" id="moreSeriesLink_{$summId}">{translate text='More Series...' isPublicFacing=true}</a>
									<div id="moreSeries_{$summId}" style="display:none">
								{/if}
								<a href="/Search/Results?searchIndex=Series&lookfor={$additional.seriesTitle}&sort=year+asc%2Ctitle+asc">{$additional.seriesTitle}</a>{if !empty($additional.volume)}<strong> {translate text="volume %1%" 1=$additional.volume|format_float_with_min_decimals isPublicFacing=true}</strong>{/if}<br>
							{/foreach}
							{if $totalSeriesShown >= $seriesLimit}
								</div>
							{/if}
						{/if}
					{/if}
				{/if}
			</div>
		</div>
	{/if}
{/if}
{if !empty($showPublisher) && $showPublisher}
	{if $alwaysShowSearchResultsMainDetails || $summPublisher}
		<div class="result-label col-sm-4 col-xs-12">{translate text="Publisher" isPublicFacing=true} </div>
		<div class="result-value col-sm-8 col-xs-12">
			{if !empty($summPublisher)}
				{$summPublisher}
			{elseif $alwaysShowSearchResultsMainDetails}
				{translate text="Not Supplied" isPublicFacing=true}
			{/if}
		</div>
	{/if}
{/if}
{if !empty($showPublicationDate) && $showPublicationDate}
	{if $alwaysShowSearchResultsMainDetails || $summPubDate}
		<div class="result-label col-sm-4 col-xs-12">{translate text="Publication Date" isPublicFacing=true} </div>
		<div class="result-value col-sm-8 col-xs-12">
			{if !empty($summPubDate)}
				{$summPubDate|escape}
			{elseif $alwaysShowSearchResultsMainDetails}
				{translate text="Not Supplied" isPublicFacing=true}
			{/if}
		</div>
	{/if}
{/if}
{if !empty($showPlaceOfPublication) && $showPlaceOfPublication}
	{if $alwaysShowSearchResultsMainDetails || $summPlaceOfPublication}
		<div class="result-label col-sm-4 col-xs-12">{translate text="Publication Places" isPublicFacing=true} </div>
		<div class="result-value col-sm-8 col-xs-12">
			{if !empty($summPlaceOfPublication)}
				{$summPlaceOfPublication|escape}
			{elseif $alwaysShowSearchResultsMainDetails}
				{translate text="Not Supplied" isPublicFacing=true}
			{/if}
		</div>
	{/if}
{/if}
{if !empty($showEditions)}
	{if $alwaysShowSearchResultsMainDetails || $summEdition}
		<div class="result-label col-sm-4 col-xs-12">{translate text="Edition" isPublicFacing=true} </div>
		<div class="result-value col-sm-8 col-xs-12">
			{if !empty($summEdition)}
				{$summEdition}
			{elseif $alwaysShowSearchResultsMainDetails}
				{translate text="Not Supplied" isPublicFacing=true}
			{/if}
		</div>
	{/if}
{/if}
{if !empty($showAudience)}
	{if $alwaysShowSearchResultsMainDetails || $summAudience}
		{assign var=formats value=$recordDriver->getFormats()}
		{assign var=formats value=array_map('strstr', $formats, array_fill(0, count($formats), "#"))}
		<div class="result-audience result-{join(" result-", array_unique($formats))|replace:"#":""|replace:" ":"-"|lower}">
			<div class="result-label col-sm-4 col-xs-12">{translate text='Audience' isPublicFacing=true} </div>
			<div class="result-value col-sm-8 col-xs-12">
			{if !empty($summAudience)}
				{$summAudience}
			{elseif $alwaysShowSearchResultsMainDetails}
				{translate text="Not Supplied" isPublicFacing=true}
			{/if}
			</div>
		</div>
	{/if}
{/if}
{if !empty($showArInfo) && $summArInfo}
	<div class="result-label col-sm-4 col-xs-12">{translate text='Accelerated Reader' isPublicFacing=true} </div>
	<div class="result-value col-sm-8 col-xs-12">
		{$summArInfo}
	</div>
{/if}
{if !empty($showLexileInfo) && $summLexileInfo}
	<div class="result-label col-sm-4 col-xs-12">{translate text='Lexile measure' isPublicFacing=true} </div>
	<div class="result-value col-sm-8 col-xs-12">
		{$summLexileInfo}
	</div>
{/if}
{if !empty($showFountasPinnell) && $summFountasPinnell}
	<div class="result-label col-sm-4 col-xs-12">{translate text='Fountas &amp; Pinnell' isPublicFacing=true} </div>
	<div class="result-value col-sm-8 col-xs-12">
		{$summFountasPinnell}
	</div>
{/if}
{if !empty($showPhysicalDescriptions)}
	{if $alwaysShowSearchResultsMainDetails || $summPhysicalDesc}
		<div class="result-label col-sm-4 col-xs-12">{translate text='Physical Desc' isPublicFacing=true} </div>
		<div class="result-value col-sm-8 col-xs-12">
			{if !empty($summPhysicalDesc)}
				{$summPhysicalDesc}
			{elseif $alwaysShowSearchResultsMainDetails}
				{translate text="Not Supplied" isPublicFacing=true}
			{/if}
		</div>
	{/if}
{/if}
{if !empty($showLanguages) && $summLanguage}
	<div class="result-label col-sm-4 col-xs-12">{translate text="Language" isPublicFacing=true} </div>
	<div class="result-value col-sm-8 col-xs-12">
		{if is_array($summLanguage)}
			{implode subject=$summLanguage glue=', ' translate=true isPublicFacing=true isMetadata=true}
		{else}
			{translate text=$summLanguage isPublicFacing=true isMetadata=true}
		{/if}
	</div>
{/if}