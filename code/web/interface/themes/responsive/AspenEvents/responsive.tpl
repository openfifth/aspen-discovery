{strip}
    {literal}
        <script>
            function toggleManageEvent(eventSourceId) {
                let savedEventDetailsModalWrapper = document.getElementById(`aspen-events-registration-button-${eventSourceId}-wrapper`)
                savedEventDetailsModalWrapper.hidden = !savedEventDetailsModalWrapper.hidden;
            }
        </script>
    {/literal}
{/strip}