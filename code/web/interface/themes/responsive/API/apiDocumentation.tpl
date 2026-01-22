<div id="main-content" class="col-xs-12">
	<h1>{translate text="Aspen API Documentation" isAdminFacing=true}</h1>
	<hr>
	<rapi-doc spec-url="{$apiFile}" render-style="view"
	 allow-spec-file-load="false"
	 default-schema-tab="schema"
	 allow-try="false"
	 allow-authentication="false"
	 bg-color="{$bodyBackgroundColor}"
	 header-color="{$bodyBackgroundColor}"
	 regular-font="{$bodyFont}"
	 mono-font="'Consolas', monospace"
	 text-color="{$bodyTextColor}"
	 primary-color="{$linkColor}"
	 nav-bg-color="{$secondaryBackgroundColor}"
	 {if $isDarkColorScheme}theme="dark"{else}theme="light"{/if}>
		<img slot="logo" src="" alt="" />
	</rapi-doc>
</div>
