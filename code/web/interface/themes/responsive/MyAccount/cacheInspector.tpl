{strip}
	{if !empty($loggedIn)}
		<h1>{translate text='Cache Inspector' isAdminFacing=true}</h1>
		<h2>{translate text="Cached Account Summaries" isAdminFacing=true}</h2>
		<table class="adminTable table table-responsive table-condensed smallText">
			<thead>
				<tr>
					<th>{translate text="Source" isAdminFacing=true}</th>
					<th>{translate text="Data Stale" isAdminFacing=true}</th>
					<th>{translate text="Checkouts Stale" isAdminFacing=true}</th>
					<th>{translate text="Checked Out" isAdminFacing=true}</th>
					<th>{translate text="Checkouts Remaining" isAdminFacing=true}</th>
					<th>{translate text="Overdue" isAdminFacing=true}</th>
					<th>{translate text="Holds Stale" isAdminFacing=true}</th>
					<th>{translate text="Available Holds" isAdminFacing=true}</th>
					<th>{translate text="Unavailable Holds" isAdminFacing=true}</th>
					<th>{translate text="Last Loaded" isAdminFacing=true}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$accountSummaries item=$accountSummary}
					<tr>
						<td>{$accountSummary->source}</td>
						<td>{if $accountSummary->dataIsStale}Y{else}N{/if}</td>
						<td>{if $accountSummary->checkoutsAreStale}Y{else}N{/if}</td>
						<td>{$accountSummary->numCheckedOut}</td>
						<td>{$accountSummary->numCheckoutsRemaining}</td>
						<td>{$accountSummary->numOverdue}</td>
						<td>{if $accountSummary->holdsAreStale}Y{else}N{/if}</td>
						<td>{$accountSummary->numAvailableHolds}</td>
						<td>{$accountSummary->numUnavailableHolds}</td>
						<td>{$accountSummary->lastLoaded}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	{else}
		{translate text="You must sign in to view this information." isPublicFacing=true}<a href='/MyAccount/Login' class="btn btn-primary">{translate text="Sign In" isPublicFacing=true}</a>
	{/if}
{/strip}
