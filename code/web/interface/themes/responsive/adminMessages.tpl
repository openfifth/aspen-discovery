{if !empty($hasSqlUpdates)}
	<div id="admin-message-header" style="margin: 1em">
		<div class="alert alert-danger" id="admin-message" role="alert" aria-live="polite">
			<div class="admin-message-text">
				<strong><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {translate text='Something broken? It looks like %1%database maintenance%2% needs to be completed' isAdminFacing=true 1='<a href="/Admin/DBMaintenance" class="alert-link">' 2='</a>'}</strong>
			</div>
		</div>
	</div>
{/if}
{if !empty($hasOptionalUpdates)}
	<div id="admin-message-header" style="margin: 1em">
		<div class="alert alert-warning" id="admin-message" role="alert" aria-live="polite">
			<div class="admin-message-text">
				<strong><a href="/Admin/OptionalUpdates"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {translate text='Recommended updates are available for Aspen Administrators' isAdminFacing=true}</a> </strong>
			</div>
		</div>
	</div>
{/if}
