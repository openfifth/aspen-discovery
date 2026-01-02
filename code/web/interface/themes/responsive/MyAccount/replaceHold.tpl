{strip}
	{if !empty($placeHoldResults.title)}
		{* for single item results *}
		<p><strong>{$placeHoldResults.title|removeTrailingPunctuation}</strong></p>
	{/if}
	<div class="contents">
		{if !empty($placeHoldResults.success)}
			<div class="alert alert-success">{$placeHoldResults.message}</div>
		{else}
			<div class="alert alert-danger">{$placeHoldResults.message}</div>
		{/if}
	</div>
{/strip}
