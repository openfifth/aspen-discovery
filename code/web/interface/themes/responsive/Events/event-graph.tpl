{strip}
	<div id="main-content" class="col-sm-12">
		<h1>{translate text=$graphTitle isAdminFacing=true}</h1>

		<form>

			<div class="form-group">
				<label for="timeframe">{translate text="Report Event hours per" isAdminFacing=true}</label>
				<select name="timeframe" id="timeframe" class="form-control">
					<option {if $timeframe == 'days'}selected{/if} value="days">{translate text="Day" isAdminFacing=true}</option>
					<option {if $timeframe == 'weeks'}selected{/if} value="weeks">{translate text="Week" isAdminFacing=true}</option>
					<option {if $timeframe == 'months'}selected{/if} value="months">{translate text="Month" isAdminFacing=true}</option>
					<option {if $timeframe == 'years'}selected{/if} value="years">{translate text="Year" isAdminFacing=true}</option>
				</select>
			</div>

			{* Date Range *}
			<div class="form-group">
				<label for="fromDate">{translate text="Start date" isAdminFacing=true}</label>
				<input class="form-control" type="date" {if !empty($fromDate)}value="{$fromDate}"{/if} id="fromDate" name="fromDate">
			</div>
			<div class="form-group">
				<label for="toDate">{translate text="End date" isAdminFacing=true}</label>
				<input class="form-control" type="date" {if !empty($toDate)}value="{$toDate}"{/if} id="toDate" name="toDate">
			</div>

			{* Event Grouping *}
			<div class="form-group property-row">
				<label for="graphOption">{translate text="Group Events By" isAdminFacing=true}</label>
				<select name="graphOption" id="graphOption" class="form-control">
					<option {if $graphOption == '0'}selected{/if} value="0">{translate text="Show total event hours" isAdminFacing=true}</option>
					<option {if $graphOption == '1'}selected{/if} value="1">{translate text="Group hours by event type" isAdminFacing=true}</option>
					<option {if $graphOption == '2'}selected{/if} value="2">{translate text="Group hours by location" isAdminFacing=true}</option>
					<option {if $graphOption == '3'}selected{/if} value="3">{translate text="Group hours by event type at each location (no graph)" isAdminFacing=true}</option>
				</select>
				<span class="help-block" style="margin-top:0"><small class="text-info"><i class="fas fa-info-circle"></i> {translate text="Groupings with no events will not show" isAdminFacing=true}</small></span>
			</div>

			{* Other Filters *}
			<div class="panel-group" id="filterReportAccordion">
				<div class='panel panel-default' id='filterReportPanel'>
					<a data-toggle='collapse' data-parent='#filterReportAccordion' href='#filterReportPanelBody' class="collapsed">
						<div class='panel-heading'>
							<div class='panel-title'>
								{translate text="Report Filtering Options" isAdminFacing=true}
							</div>
						</div>
					</a>
					<div id='filterReportPanelBody' class='panel-collapse collapse'>
						<div class='panel-body'>
							{* Filter by standard fields (event type, location) *}
							<div class="form-group">
								<label for="type">{translate text="Event type" isAdminFacing=true}</label>
								<select name="type" id="type" class="form-control">
									<option {if $eventTypeValue == ''}selected{/if} value="">{translate text="All Types" isAdminFacing=true}</option>
									{foreach $eventTypes as $id => $type}
										<option {if $eventTypeValue == $id}selected{/if} value="{$id}">{$type}</option>
									{/foreach}
								</select>
							</div>
							<div class="form-group">
								<label for="location">{translate text="Location" isAdminFacing=true}</label>
								<select name="location" id="location" class="form-control">
									<option {if $locationValue == ''}selected{/if} value="">{translate text="All Locations{$libraryRestriction}" isAdminFacing=true}</option>
									{foreach $locations as $id => $location}
										<option {if $locationValue == $id}selected{/if} value="{$id}">{$location}</option>
									{/foreach}
								</select>
							</div>
							<div class="form-group" {if empty($sublocations)}style="display:none"{/if}>
								<label for="sublocation">{translate text="Sublocation" isAdminFacing=true}</label>
								<select name="sublocation" id="sublocation" class="form-control" >
									<option {if $sublocations && $sublocationValue == ''}selected{/if} value="">All Sublocations</option>
									{if $sublocations}
										{foreach $sublocations as $id => $sublocation}
											<option {if $sublocationValue == $id}selected{/if} value="{$id}">{$sublocation}</option>
										{/foreach}
									{/if}
								</select>
							</div>

							{* Filtering by custom fields *}
							<div class="panel-group" id="customFieldFilteringAccordion">
								<div class='panel panel-default active'>
									<a data-toggle='collapse' data-parent='#customFieldFilteringAccordion' href='#customFieldFilteringPanelBody' class="expanded">
										<div class='panel-heading'>
											<div class='panel-title'>
												{translate text="Filter By Event Fields" isAdminFacing=true}
											</div>
										</div>
									</a>
									<div id='customFieldFilteringPanelBody' class='panel-collapse in'>
										<div class='panel-body'>
											{* Checkbox fields *}
											<div class="form-inline">
												{foreach $checkboxFields as $id => $checkbox}
													<div class="form-group">
														<label for="field_{$id}">
															<input type="checkbox" {if array_key_exists("field_{$id}", $fields) && $fields["field_{$id}"] == 1}checked{/if}  value="1" id="field_{$id}" name="field_{$id}" class="form-control">
															{$checkbox->name}
														</label>
													</div>
												{/foreach}
											</div>
											{* Select Fields *}
											{foreach $selectFields as $id => $select}
												<div class="form-group">
													<label for="field_{$id}">{$select->name}</label>
													<select id="field_{$id}" name="field_{$id}" class="form-control">
														<option value="">No selection</option>
														{foreach explode("\n", $select->allowableValues) as $index => $option}
															<option {if array_key_exists("field_{$id}", $fields) && $fields["field_{$id}"] == $index}selected{/if} value="{$index}">{$option}</option>
														{/foreach}
													</select>
												</div>
											{/foreach}
										</div>
									</div>
								</div>
							</div>

							{* Event Search *}
							<div class="form-group">
								<label for="query">{translate text="Include Events Matching" isAdminFacing=true}</label><input type="text" id="query" name="query" class="form-control" value="{$query}"/>
								<span class="help-block" style="margin-top:0"><small class="text-info"><i class="fas fa-info-circle"></i> {translate text="Searches all text fields (title, description, custom text fields)" isAdminFacing=true}</small></span>
							</div>
						</div>
					</div>

				</div>
			</div>

			<br/>

			<div class="form-group">
				<input type="submit" value="{translate text="Update Report" isAdminFacing=true inAttribute=true}" class="form-control btn btn-primary"/>
			</div>
		</form>
		<hr>

		{if $graphOption != '3'}
			<div class="chart-container" style="position: relative; height:50%; width:100%">
				<canvas id="chart"></canvas>
			</div>
		{/if}

		<h2>{translate text="Raw Data" isAdminFacing=true}</h2>
		<div class="adminTableRegion fixed-height-table">
			<table class="adminTable table table-responsive table-striped table-bordered table-condensed smallText table-sticky">
				<thead>
				<tr>
					<th>{translate text="Date" isAdminFacing=true}</th>
					{foreach from=$dataSeries key=seriesLabel item=seriesData}
						<th>{if !empty($translateDataSeries)}{translate text=$seriesLabel isAdminFacing=true}{else}{$seriesLabel}{/if}</th>
					{/foreach}
				</tr>
				</thead>
				<tbody>
				{foreach from=$columnLabels item=label}
					<tr>
						<td>{if !empty($translateColumnLabels)}{translate text=$label isAdminFacing=true}{else}{$label}{/if}</td>
						{foreach from=$dataSeries item=seriesData}
							<td>{if (empty($seriesData.data.$label))}0{else}{$seriesData.data.$label|number_format}{/if}</td>
						{/foreach}
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
		{if !empty($showCSVExportButton)}
			<div>
				<a id="UsageGraphExport" class="btn btn-sm btn-default" href="/{$section}/AJAX?{$urlParameters}&method=exportUsageData">{translate text='Export To CSV' isAdminFacing=true}</a>
				<div id="exportToCSVHelpBlock" class="help-block" style="margin-top:0"><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> {translate text="Exporting will retrieve the latest data. To see it on screen, refresh this page." isAdminFacing=true}</small></div>
			</div>
		{/if}
	</div>
{/strip}
{literal}
<script>
	{/literal}
	{if $graphOption != "3"}
	{literal}
	var ctx = document.getElementById('chart');
	var myChart = new Chart(ctx, {
		type: 'line',
		data: {
			labels: [
				{/literal}
				{foreach from=$columnLabels item=columnLabel}
				'{$columnLabel}',
				{/foreach}
				{literal}
			],
			datasets: [
				{/literal}
				{foreach from=$dataSeries key=seriesLabel item=seriesData}
				{ldelim}
					label: "{translate text=$seriesLabel isAdminFacing=true}",
					data: [
						{foreach from=$seriesData.data item=curValue}
						{$curValue},
						{/foreach}
					],
					borderWidth: 1,
					borderColor: '{$seriesData.borderColor}',
					backgroundColor: '{$seriesData.backgroundColor}',
					{rdelim},
				{/foreach}
				{literal}
			]
		},
		options: {
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}],
				xAxes: [{
					type: 'category',
					labels: [
						{/literal}
						{foreach from=$columnLabels item=columnLabel}
						'{$columnLabel}',
						{/foreach}
						{literal}
					]
				}]
			}
		}
	});
	{/literal}
	{/if}
	{literal}
	$("#location").on('change', function () {
		var url = Globals.path + '/Events/AJAX';
		var params = {
			method: 'getEventTypesAndSublocationsForLocation',
			locationId: $(this).val()
		};

		$.getJSON(url, params, function (data) {
			if (data.success) {
				var sublocationSelect = $("#sublocation");
				if (data.sublocations && data.sublocations.length > 0) {
					var sublocations = JSON.parse(data.sublocations);
					sublocationSelect.html("");
					$("<option/>", {
						value: "",
						text: "All Sublocations"
					}).appendTo("#sublocation");
					Object.keys(sublocations).forEach(function (key) {
						$("<option/>", {
							value: key,
							text: sublocations[key]
						}).appendTo("#sublocation");
					});
					if (sublocations.length === 0) {
						sublocationSelect.parent().hide();
					} else {
						sublocationSelect.parent().show();
					}

				} else {
					sublocationSelect.html("");
					sublocationSelect.parent().hide();
				}
			}
		});
	});
</script>
{/literal}
