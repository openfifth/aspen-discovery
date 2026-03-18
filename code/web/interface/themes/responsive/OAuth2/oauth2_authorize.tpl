{extends file="layout.tpl"}

{block name="breadcrumbs"}{/block}

{block name="content"}
<div class="row">
	<div class="col-md-12">
		<h1>Authorize Application</h1>

		<div class="well">
			<p><strong>{$client->name}</strong> is requesting access to your account.</p>

			{if !empty($scopes)}
			<h3>Requested Permissions:</h3>
			<ul>
				{foreach from=$scopes item=scope}
					{if $scope == 'user:read'}
						<li>Read your basic profile information</li>
					{elseif $scope == 'user:write'}
						<li>Modify your profile information</li>
					{elseif $scope == 'catalog:read'}
						<li>View catalog items and your reading history</li>
					{elseif $scope == 'catalog:write'}
						<li>Place holds and manage your library account</li>
					{elseif $scope == 'admin:read'}
						<li>Read administrative data (admin only)</li>
					{elseif $scope == 'admin:write'}
						<li>Modify administrative settings (admin only)</li>
					{else}
						<li>{$scope}</li>
					{/if}
				{/foreach}
			</ul>
			{/if}

			<div class="alert alert-info">
				<strong>Logged in as:</strong> {$user->displayName} ({$user->id})
			</div>
		</div>

		<form method="POST" action="{$authorizationUrl}">
			<div class="btn-group btn-group-justified">
				<div class="btn-group">
					<button type="submit" name="approve" value="yes" class="btn btn-success btn-lg">
						<i class="fas fa-check"></i> Authorize
					</button>
				</div>
				<div class="btn-group">
					<button type="submit" name="approve" value="no" class="btn btn-danger btn-lg">
						<i class="fas fa-times"></i> Cancel
					</button>
				</div>
			</div>
		</form>

		<div class="alert alert-warning" style="margin-top: 20px;">
			<strong>Security Notice:</strong> Only authorize applications you trust. Once authorized, this application will be able to access your account according to the permissions listed above.
		</div>
	</div>
</div>
{/block}
