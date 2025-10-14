{strip}
<div class="result row hyperhold-group" id="hyperhold_{$record.visual_hold_id}">
	<div class="col-xs-12">
		<span class="result-index">{$resultIndex})</span>&nbsp;
		<strong>{translate text="Hyperhold Group" isPublicFacing=true}:</strong> {$record.visual_hold_id}
		&nbsp;|&nbsp;
		<strong>{translate text="Number of Holds in Group" isPublicFacing=true}:</strong> {$record.holdCount}
		
		<a href="#" onclick="$('#hyperhold_details_{$record.visual_hold_id}').toggle(); return false;"
		class="btn btn-sm btn-default" style="margin-left: 15px;">
			<i class="fas fa-list"></i> {translate text="View All Records" isPublicFacing=true} ({$record.holdCount})
		</a>
	</div>
</div>

<div id="hyperhold_details_{$record.visual_hold_id}" class="holdDetails" style="display:none; margin-top:15px;">
	{foreach from=$record.holds item=hold name="holdLoop"}
		{include file="MyAccount/ilsHold.tpl" record=$hold resultIndex=$smarty.foreach.holdLoop.iteration showCovers=$showCovers}
	{/foreach}
</div>
{/strip}