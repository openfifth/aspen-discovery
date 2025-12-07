<!DOCTYPE html>
<html lang="{$userLang->code}" class="hero-slider-signage">
{strip}
<head>
	<title>{$location->name}</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

	{include file="cssAndJsIncludes.tpl" includeAutoLogoutCode=false}
	<script src="/interface/themes/responsive/js/aspen/hero-slider.js"></script>
</head>

<body class="hero-slider-signage">
	<div class="digital-signage-container">
		{foreach from=$slides item=slide name=slideLoop}
			<div class="signage-slide"
				 data-duration="{$slide.duration}">
				<img src="/WebBuilder/ViewImage?id={$slide.image->id}&size=full"
					 alt="{$slide.image->altText|escape}" />
			</div>
		{/foreach}
	</div>

	<script>
		$(() => {
			AspenDiscovery.HeroSlider.initDigitalSignage({
				autoRotate: {if $location->autoRotate}true{else}false{/if},
				locationId: {$location->id}{if $reload},
				reload: true{/if}
			});
		});
	</script>
</body>
{/strip}
</html>
