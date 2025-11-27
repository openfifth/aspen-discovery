{strip}
	{if $numberOfSeats !== null}
		<div class="alert {if $availableSeats > 0}alert-info{else}alert-danger{/if}" style="margin-bottom: 10px;">
			{if $availableSeats > 0}
				{translate text="Available Seats" isPublicFacing=true}: {$availableSeats} / {$numberOfSeats}
			{else}
				{translate text="This event is full. No seats available." isPublicFacing=true}
			{/if}
		</div>
	{/if}
{/strip}
