<h1 class="hiddenTitle">{translate text='Articles & Databases Search Results'}</h1>
<div id="searchInfo">
	{if !empty($subpage)}
		{include file=$subpage}
	{else}
		{$pageContent}
	{/if}
	{if !empty($pageLinks.all)}<div class="text-center">{$pageLinks.all}</div>{/if}
</div>
