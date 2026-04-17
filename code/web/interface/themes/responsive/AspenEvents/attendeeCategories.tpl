{if !empty($attendeeCategories)}
	<div class="attendee-categories">
		<label>{translate text="Attendees" isPublicFacing=true}</label>
		{foreach from=$attendeeCategories item=category}
			<div class="form-group well well-sm">
				<label for="attendeeCategory-{$category->attendeeCategoryId}">{translate text="{$category->getCategory()->name}" isPublicFacing=true} : <span style="font-weight:normal">{translate text="{$category->getCategory()->publicDescription}" isPublicFacing=true}</span></label>
				<div class="input-group">
					<input type="number"
						id="attendeeCategory-{$category->attendeeCategoryId}"
						name="attendeeCategory[{$category->attendeeCategoryId}]"
						class="form-control"
						min="0"
						max="{$category->maxAttendees}"
						value="0">
					<span class="input-group-addon">/ {$category->maxAttendees}</span>
				</div>
			</div>
		{/foreach}
	</div>
{/if}
