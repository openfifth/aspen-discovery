{strip}
{include file='Record/pickup-location-select.tpl'}
{include 
    file="Record/date-range-picker.tpl"
    rangeId="booking-calendar"
    startName="startDate"
    endName="endDate"
    startValue=$startDate|default:''
    endValue=$endDate|default:''
    months=1 statusText="Loading availability…"
    clampNotice="Your booking was shortened: for this start date, the latest end date is %1%."
}

<script>AspenDiscovery.Record.initBookingForm({if !empty($availability)}{json_encode($availability)}{/if});</script>
{/strip}
