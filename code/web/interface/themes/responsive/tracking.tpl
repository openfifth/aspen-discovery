{* Add Google Analytics version 4 code *}
{if !empty($googleAnalyticsId)}
	<script async src="https://www.googletagmanager.com/gtag/js?id={$googleAnalyticsId}"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){ldelim}dataLayer.push(arguments);{rdelim}
		gtag('js', new Date());
		gtag('config', '{$googleAnalyticsId}');
	</script>
{/if}
