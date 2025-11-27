{strip}
	<div class="alert {if $isEventFull}alert-danger{else}alert-info{/if}" style="margin-bottom: 10px;">
		{if $isEventFull}
			{translate text="This event is full. No seats available." isPublicFacing=true}
		{else}
			{translate text="Available Seats" isPublicFacing=true}: {$availableSeats} / {$numberOfSeats}
		{/if}
	</div>
{/strip}
