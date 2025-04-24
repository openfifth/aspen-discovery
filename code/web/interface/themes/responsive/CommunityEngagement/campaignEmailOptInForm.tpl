{strip}
    <h3 id="campaignNotificationModalTitle">{translate text="Campaign Notification Options" isPublicFacing=true}</h3>

    <div id="campaignEmailOptIn" class="custom-control custom-switch mb-5">
        <input type="checkbox" class="custom-control-input" id="emailOptInSlider" {if $isOptedIn}checked{/if}>
        <label id="emailOptInLabel" class="custom-control-label" for="emailOptInSlider">{translate text="Opt in to campaign email updates for %1%" 1=$campaignName isPublicFacing=true}</label>&nbsp;
    </div>


    
    {if !empty($user->email)}
        <p id="addresToSendEmails" class="mt-5">{translate text="Emails will be sent to %1%" 1=$user->email isPublicFacing=true}</p>
    {else}
        <p id="noAddressToSendEmails" class="mt-5">{translate text="Please update your email address in your contact information" isPublicFacing=true}</p>
    {/if}
    

{/strip}