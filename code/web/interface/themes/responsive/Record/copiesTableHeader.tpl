{strip}
<thead>
	<tr>
		{if !empty($showVolume)}
			<th>
				<strong><u>{translate text="Volume" isPublicFacing=true}</u></strong>
			</th>
		{/if}
		<th>
			<strong><u>{translate text="Location" isPublicFacing=true}</u></strong>
		</th>
		{if $showFormatInHoldings}
			<th>
				<strong><u>{translate text="Format" isPublicFacing=true}</u></strong>
			</th>
		{/if}
		<th>
			<strong><u>{translate text="Call Number" isPublicFacing=true}</u></strong>
		</th>
		{if !empty($hasBarcode) && $showItemBarcodes}
			<th>
				<strong><u>{translate text="Barcode" isPublicFacing=true}</u></strong>
			</th>
		{/if}
		{if !empty($hasNote) && $showItemNotes}
			<th>
				<strong><u>{translate text="Note" isPublicFacing=true}</u></strong>
			</th>
		{/if}
		<th>
			<strong><u>{translate text="Status" isPublicFacing=true}</u></strong>
		</th>
		{if !empty($hasDueDate) && $showItemDueDates}
			<th>
				<strong><u>{translate text="Due Date" isPublicFacing=true}</u></strong>
			</th>
		{/if}
		{if !empty($showLastCheckIn)}
			<th>
				<strong><u>{translate text="Last Check-In" isPublicFacing=true}</u></strong>
			</th>
		{/if}

		{if $holdingsHaveUrls}
			<th>
				<strong><u>{translate text="Link" isPublicFacing=true}</u></strong>
			</th>
		{/if}
	</tr>
</thead>
{/strip}