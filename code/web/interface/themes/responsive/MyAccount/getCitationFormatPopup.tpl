<div align="left">
	{if !empty($message)}<div class="error">{translate text=$message isPublicFacing=true}</div>{/if}

	<form action="/MyAccount/CiteList" method="get" class="form" id="citeListForm">
		<input type="hidden" name="listId" value="{$listId|escape}">
		<input type="hidden" name="selectedResourceTypes" id="selectedResourceTypes" value="{$selectedResourceTypes|escape:"html"}">
		<input type="hidden" name="activeFilters" id="activeFilters" value="{$activeFilters|escape:"html"}">

		<div class="form-group">
			<label for="citationFormat">{translate text='Citation Format' isPublicFacing=true}</label>
			<select name="citationFormat" id="citationFormat" class="form-control">
				{foreach from=$citationFormats item=formatName key=format}
					<option value="{$format}">{translate text=$formatName isPublicFacing=true}</option>
				{/foreach}
			</select>
		</div>

	</form>
</div>
