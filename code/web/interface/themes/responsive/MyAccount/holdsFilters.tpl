{if is_array($filterOptions)}
	<div class="row" id="holdsFilterRow">
        {foreach from=$filterOptions item=filterOption key=filterKey}
			<div class="col-sm-2">
				<div class="form-group">
					<fieldset>
						<legend>{$filterOption.label}</legend>
						<select
								name="HoldFilter_{$filterKey}[]"
								id="HoldFilter_{$filterKey}"
								class="multipleSelect"
								aria-label="{translate text='Filter by %1%' 1=$filterOption.label isPublicFacing=true inAttribute=true}"
                                {if $filterOption.type === "multiselect"}multiple="multiple"{/if}
						>
                            {foreach from=$filterOption.options item=optionVal key=optionKey}
								<option value="{$optionKey}"{if isset($filterOption.selected) && in_array($optionKey, $filterOption.selected)} selected="selected"{/if}>{$optionVal}</option>
                            {/foreach}
						</select>
					</fieldset>
				</div>
			</div>
        {/foreach}
		<div class="col-sm-3">
			<button type="button" style="margin-top: .5em;" class="btn btn-default" id="applyHoldsFilters">Apply Filters</button>
			<button type="button" style="margin-top: .5em;" class="btn btn-default" id="clearHoldsFilters">Clear Filters</button>
		</div>
	</div>
	<script type="text/javascript">
        {literal}
		$(document).ready(function() {
			$('.multipleSelect').multipleSelect({});
			$('#applyHoldsFilters').on('click', function() {
				let filters = {};
				$('#holdsFilterRow select').each(function() {
					let key = $(this).attr('id').replace('HoldFilter_', '');
					filters[key] = $(this).val() || [];
				});
				AspenDiscovery.Account.loadHolds('{/literal}{$source}{literal}', $('#availableHoldSort_{/literal}{$source}{literal} option:selected').val(), $('#unavailableHoldSort_{/literal}{$source}{literal} option:selected').val(), null, null, filters);
			});
			$('#clearHoldsFilters').on('click', function() {
				AspenDiscovery.Account.loadHolds('{/literal}{$source}{literal}', $('#availableHoldSort_{/literal}{$source}{literal} option:selected').val(), $('#unavailableHoldSort_{/literal}{$source}{literal} option:selected').val());
			});
		});
        {/literal}
	</script>
{/if}