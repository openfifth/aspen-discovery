{strip}
	<section>
		<table id="aspen-events-registration-button-{$event.sourceId}" class="table table-striped">
				<thead>
					<th>&nbsp;</th>
				</thead>
				<tbody>
					<tr>
						<td>
							{include file='AspenEvents/registrationToggleButton.tpl' eventSourceId="{$event.sourceId}" userId="{$userId}"}
						</td>
					</tr>
				</tbody>
		</table>
	</section>
{/strip}
