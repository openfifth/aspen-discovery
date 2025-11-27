{strip}
	<section>
		<table id="aspen-events-registration-button-{$event.sourceId}" class="table table-striped">
				<thead>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
				</thead>
				<tbody>
					<tr>
						<td>
							{include file='AspenEvents/registrationUserSelector.tpl'}
						</td>
						<td>
							{include file='AspenEvents/registrationToggleButton.tpl'}
						</td>
					</tr>
					<tr>
						<td colspan="2">
							{include file='AspenEvents/seats.tpl'}
						</td>
					</tr>
				</tbody>
		</table>
	</section>
{/strip}
