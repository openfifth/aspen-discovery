{strip}
    {literal}
        <script>
            const responsiveActions = {
            	toggleManageEvent: function (eventSourceId) {
                    let savedEventDetailsModalWrapper = document.getElementById(`aspen-events-registration-button-${eventSourceId}-wrapper`)
                    savedEventDetailsModalWrapper.hidden = !savedEventDetailsModalWrapper.hidden;
                },  
            }
        </script>
    {/literal}
{/strip}