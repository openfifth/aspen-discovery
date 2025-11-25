{strip}
	<button id="aspen-events-manage-button-{$event.sourceId}" type="button" class="btn btn-xs btn-primary" onclick="return responsiveActions.toggleManageEvent('{$event.sourceId|escape}');">
		{translate text = 'Manage' isPublicFacing=true}
	</button>	
	<script>
        console.log('loaded')
        const responsiveActions = {
        	toggleManageEvent: function (eventSourceId) {
                let savedEventDetailsModalWrapper = document.getElementById('aspen-events-registration-button-{$event.sourceId}-wrapper')
                savedEventDetailsModalWrapper.hidden = !savedEventDetailsModalWrapper.hidden;
            },  
        }
    </script>
{/strip}
