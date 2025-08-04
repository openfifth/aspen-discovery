{strip}
	<div id="more-details-accordion" class="panel-group">
		{foreach from=$moreDetailsOptions key="moreDetailsKey" item="moreDetailsOption"}
			<div class="panel {if !empty($moreDetailsOption.openByDefault)}active{/if}" id="{$moreDetailsKey}Panel" {if !empty($moreDetailsOption.hideByDefault)}style="display:none"{/if}>
				<a data-toggle="collapse" href="#{$moreDetailsKey}PanelBody">
					<div class="panel-heading">
						<div class="panel-title">
							<h2>{translate text=$moreDetailsOption.label isPublicFacing=true}</h2>
						</div>
					</div>
				</a>
				<div id="{$moreDetailsKey}PanelBody" class="panel-collapse collapse {if !empty($moreDetailsOption.openByDefault)}in{/if}">
					<div class="panel-body">
						{if $moreDetailsKey == 'description'}
							{* make text-full items easier to read by placing an empty line where linebreaks exist *}
							{$moreDetailsOption.body|replace:"\n":"<br>\n"}
						{else}
							{$moreDetailsOption.body}
						{/if}
					</div>
					{if !empty($moreDetailsOption.onShow)}
						<script type="text/javascript">
							{literal}
							$('#{/literal}{$moreDetailsKey}Panel'){literal}.on('shown.bs.collapse', function () {
								{/literal}{$moreDetailsOption.onShow}{literal}
							});
							{/literal}
						</script>
					{/if}
				</div>
			</div>
		{/foreach}
	</div> {* End of tabs*}
{/strip}
<script type="text/javascript">
	{literal}
	$(() => {
		$('#excerptPanel').on('show.bs.collapse', () => {
			AspenDiscovery.GroupedWork.getGoDeeperData({/literal}'{$recordDriver->getPermanentId()}'{literal}, 'excerpt');
		});
		$('#tableOfContentsPanel').on('show.bs.collapse', () => {
			AspenDiscovery.GroupedWork.getGoDeeperData({/literal}'{$recordDriver->getPermanentId()}'{literal}, 'tableOfContents');
		});
		$('#authornotesPanel').on('show.bs.collapse', () => {
			AspenDiscovery.GroupedWork.getGoDeeperData({/literal}'{$recordDriver->getPermanentId()}'{literal}, 'authornotes');
		});
	})
	{/literal}

	{foreach from=$moreDetailsOptions key="moreDetailsKey" item="moreDetailsOption"}
		{if !empty($moreDetailsOption.openByDefault) && !empty($moreDetailsOption.onShow)}
			{$moreDetailsOption.onShow}
		{/if}
	{/foreach}
</script>
