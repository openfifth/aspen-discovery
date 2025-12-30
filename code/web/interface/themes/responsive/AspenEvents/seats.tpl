{strip}
	{if $numberOfSeats !== null}
		<div class="alert {if $availableSeats > 0}alert-info{else}alert-danger{/if}" style="margin-bottom: 10px;">
			{if $availableSeats > 0}
				{translate text="Available Seats" isPublicFacing=true}: {$availableSeats} / {$numberOfSeats}
			{else}
				{translate text="This event is full. No seats available." isPublicFacing=true}
				{if $userOnWaitingList}
					{translate text="You are number %1% on the waiting list." 1=$userWaitingListPosition isPublicFacing=true}
				{else}
					{translate text="Join the waiting list." isPublicFacing=true}
				{/if}
			{/if}
		</div>
	{/if}
{/strip}
