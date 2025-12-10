{strip}
	<div class='form-group'>
		<label for='timeframe'>{translate text='Report Usage per' isAdminFacing=true}</label>
		<select name='timeframe' id='timeframe' class='form-control' onchange=toggleCustomPeriodInputFieldDisplay()>
			<option value='days'>{translate text='Day' isAdminFacing=true}</option>
			<option value='weeks'>{translate text='Week' isAdminFacing=true}</option>
			<option value='months'>{translate text='Month' isAdminFacing=true}</option> 
			<option value='years'>{translate text='Year' isAdminFacing=true}</option>
			<option value='custom'>{translate text='Custom period' isAdminFacing=true}</option>
		</select>
		<div id='custom-usage-period-wrapper' hidden>
			<label for='custom-usage-period-field'>{translate text='Customer period duration (days)' isAdminFacing=true}</label>
			<input type='number' name='custom-usage-period' id='custom-usage-period-field' min='1' class='form-control duration-input' hidden>
		</div>
	</div>
{/strip}
{literal}
<script>
	function toggleCustomPeriodInputFieldDisplay() {
		const selectedTimeFrame = document.getElementById('timeframe').value;
		const customPeriodInputWrapper = document.getElementById('custom-usage-period-wrapper');

		if (selectedTimeFrame === 'custom') {
			customPeriodInputWrapper.removeAttribute('hidden');
			return;
		}

		if (customPeriodInputWrapper.hidden) {
			return;
		}

		customPeriodInputWrapper.setAttribute('hidden', true);
	}
</script>
{/literal}