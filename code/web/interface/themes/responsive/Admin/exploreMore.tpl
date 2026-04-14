{strip}
    <div class="col-xs-12">
		<div class="row">
			<div class="col-xs-12 col-md-9">
                <h1>{translate text="Explore More" isAdminFacing=true}</h1>
                <p>Coming soon!</p>
			</div>
			<div class="col-xs-12 col-md-3 help-link">
				{if !empty($instructions)}<a href="{$instructions}" target="_blank"><i class="fas fa-question-circle" role="presentation"></i>&nbsp;{translate text="Documentation" isAdminFacing=true}</a>{/if}
			</div>
		</div>
    </div>
{/strip}
