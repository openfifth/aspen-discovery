<select name='{$propName}' id='{$propName}Select' {if !empty($property.accessibleLabel)}aria-label="{$property.accessibleLabel}"{/if} class="form-control" {if !empty($property.onchange)}onchange="{$property.onchange}"{/if} {if !empty($property.readOnly)}readonly disabled{/if} {if !empty($property.required) && $property.required == true}required{/if}>
	{foreach from=$property.values item=propertyName key=propertyValue}
		{if $property.type == 'enumFromNestedSection'}
			<option value='{$propertyValue}' {if isset($propValue) && ($propValue.$propName == $propertyValue)} selected='selected'{/if} {if !empty($property.onchange)} onchange="{$property.onchange}"{/if}>{if !empty($property.translateValues)}{translate text=$propertyName|escape inAttribute=true isPublicFacing=$property.isPublicFacing isAdminFacing=$property.isAdminFacing}{else}{$propertyName|escape}{/if}</option>
		{else}
			<option value='{$propertyValue}'{if isset($propValue) && ($propValue == $propertyValue)} selected='selected'{/if}{if !empty($property.onchange)} onchange="{$property.onchange}"{/if}>{if !empty($property.translateValues)}{translate text=$propertyName|escape inAttribute=true isPublicFacing=$property.isPublicFacing isAdminFacing=$property.isAdminFacing}{else}{$propertyName|escape}{/if}</option>
		{/if}
	{/foreach}
</select>