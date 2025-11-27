{strip}
	<section>
		<table id="aspen-events-registration-button-{$event.sourceId}" class="table table-striped">
				<thead>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
				</thead>
				<tbody>
					<tr>
						<td class="myAccountCell">
							{include file='AspenEvents/registrationUserSelector.tpl'}
						</td>
						<td class="myAccountCell">
							<label for="eventUserSelector" class="control-label"></label>
							{include file='AspenEvents/registrationUserDetails.tpl'}
						</td>
						<td class="myAccountCell">
							{include file='AspenEvents/registrationToggleButton.tpl'}
						</td>
					</tr>
					<tr>
						<td class="myAccountCell" colspan="3">
							{include file='AspenEvents/seats.tpl'}
						</td>
					</tr>
				</tbody>
		</table>
	</section>
{/strip}
{* TODO: figure out *}
