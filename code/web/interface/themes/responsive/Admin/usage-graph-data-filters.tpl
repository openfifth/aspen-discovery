{strip}
	<form>
		<div class='form-group'>
			<label for='timeframe'>{translate text='Report Usage per' isAdminFacing=true}</label>
			<select name='timeframe' id='timeframe' class='form-control' onchange=toggleCustomPeriodInputFieldDisplay()>
				<option {if $timeframe == 'day'}selected{/if} value='day'>{translate text='Day' isAdminFacing=true}</option>
				<option {if $timeframe == 'month'}selected{/if} value='month'>{translate text='Month' isAdminFacing=true}</option> 
				<option {if $timeframe == 'year'}selected{/if} value='year'>{translate text='Year' isAdminFacing=true}</option>
				<option {if $timeframe == 'custom'}selected{/if} value='custom'>{translate text='Custom period' isAdminFacing=true}</option>
			</select>
			<div id='custom-usage-period-wrapper' hidden>
				<label for='custom-usage-period-duration-field'>{translate text='Custom period duration (days)' isAdminFacing=true}</label>
				<input type='number' name='custom-usage-period-duration' id='custom-usage-period-duration-field' min='1' class='form-control duration-input' hidden>
				<label for='custom-usage-period-start-field'>{translate text='Custom period start (date)' isAdminFacing=true}</label>
				<input type='number' name='custom-usage-period-start' id='custom-usage-period-start-field' min='1' class='form-control duration-input' hidden>
			</div>
			<input type="hidden" value="{$stat}" name="stat"/>
		</div>
		<div class="form-group">
			<input type="submit" value="{translate text="Update Report" isAdminFacing=true inAttribute=true}" class="form-control btn btn-primary"/>
		</div>
	</form>
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