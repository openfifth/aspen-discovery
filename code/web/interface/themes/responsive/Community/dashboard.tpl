{strip}
    <div id="main-content" class="col-sm-12">
        <h1>{translate text="Dashboard" isAdminFacing=true}</h1>
        <button id="toggleOverview" onClick="toggleCampaignOverview()">Camapign Overview</button>
        <button id="togglePatronStatusBtn" onClick="togglePatronStatus()">Patron Status</button>
        {*Overview of campaigns*}
        <div id="campaignOverview" style="display:none;">
            {*All Campaigns*}
            <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding:0 10px 10px 10px; margin-bottom: 10px;">
                <div class="col-sm-12">
                    <h2 class="dashboardCategoryLabel">{translate text="All Campaigns" isAdminFacing=true}</h2>
                    {foreach from=$campaigns item=campaign}
                        <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                        <h5 style="font-weight:bold;">{translate text=$campaign->name isAdminFacing=true}</h5>

                            <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                            <div class="dashboardValue">{translate text=$campaign->currentEnrollments isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Enrollments:</div>
                            <div class="dashboardValue">{translate text=$campaign->enrollmentCounter isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Unenrollments:</div>
                            <div class="dashboardValue">{translate text=$campaign->unenrollmentCounter isAdminFacing=true}</div>
                        </div>
                    {/foreach}
                </div>
            </div>
            {*Active Campaigns*}
            <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding: 0 10px 10px 10px; margin-bottom: 10px;">
                <div class="col-sm-12">
                    <h2 class="dashboardCategoryLabel">{translate text="Active Campaigns" isAdminFacing=true}</h2>
                    {foreach from=$activeCampaigns item=activeCampaign}
                        <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                            <h5 style="font-weight:bold;">{translate text=$activeCampaign->name isAdminFacing=true}</h5>

                            <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                            <div class="dashboardValue">{translate text=$activeCampaign->currentEnrollments isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Enrollments:</div>
                            <div class="dashboardValue">{translate text=$activeCampaign->enrollmentCounter isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Unenrollments:</div>
                            <div class="dashboardValue">{translate text=$activeCampaign->unenrollmentCounter isAdminFacing=true}</div>
                        </div>
                    {/foreach}
                </div>
            </div>
            {*Upcoming Campaigns*}
            <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding: 0 10px 10px 10px; margin-bottom: 10px;">
                <div class="col-sm-12">
                    <h2 class="dashboardCategoryLabel">{translate text="Upcoming Campaigns" isAdminFacing=true}</h2>
                    {foreach from=$upcomingCampaigns item=upcomingCampaign}
                        <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                            <h5 style="font-weight:bold;">{translate text=$campaign->name isAdminFacing=true}</h5>

                            <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                            <div class="dashboardValue">{translate text=$upcomingCampaign->currentEnrollments isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Enrollments:</div>
                            <div class="dashboardValue">{translate text=$upcomingCampaign->enrollmentCounter isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Unenrollments:</div>
                            <div class="dashboardValue">{translate text=$upcomimgCampaign->unenrollmentCounter isAdminFacing=true}</div>
                        </div>
                    {/foreach}
                </div>
            </div>
            {*Campaigns that end this month*}
            <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding: 0 10px 10px 10px; margin-bottom: 10px;">
                <div class="col-sm-12">
                    <h2 class="dashboardCategoryLabel">{translate text="Campaigns Ending This Month" isAdminFacing=true}</h2>
                    {foreach from=$campaignsEndingThisMonth item=campaignEnding}
                        <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">
                            <h5 style="font-weight:bold;">{translate text=$campaignEnding->name isAdminFacing=true}</h5>

                            <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                            <div class="dashboardValue">{translate text=$campaignEnding->currentEnrollments isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Enrollments:</div>
                            <div class="dashboardValue">{translate text=$campaignEnding->enrollmentCounter isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Unenrollments:</div>
                            <div class="dashboardValue">{translate text=$campaignEnding->unenrollmentCounter isAdminFacing=true}</div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
        {*Show patrons that are enrolled in each campaign*}
        <div id="patronStatus" style="display:none;">
                    {foreach from=$campaigns item=campaign}
                        <div class="campaign-patron-status">
                            <h2>{translate text=$campaign->name isAdminFacing=true}</h2>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{translate text="User ID" isAdminFacing=true}</th>
                                        <th>{translate text="Username" isAdminFacing=true}</th>
                                        <th>{translate text="Campaign Complete" isAdminFacing=true}</th>
                                        <th>{translate text="Reward Given" isAdminFacing=true}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$campaign->getUsersForCampaign() item=user}
                                        <tr>
                                            <td>{$user->id}</td>
                                            <td>{$user->username}</td>
                                            <td>
                                            </td>
                                            <td>
                                                {if isset ($userCampaigns[$campaign->id][$user->id]) && $userCampaigns[$campaign->id][$user->id] == 0}
                                                <button class="set-reward-btn" data-user-id="{$user->id}" data-campaign-id="{$campaign->id}" onclick="AspenDiscovery.CommunityEngagement.campaignRewardGiven({$user->id}, {$campaign->id});">
                                                    {translate text="Set Reward as Given" isPublicFacing=true}
                                                </button>
                                                {else}
                                                    Reward Given
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {/foreach}
        </div>
    </div>
{/strip}
<script type="text/javascript">
    function toggleCampaignOverview() {
        var overviewDiv = document.getElementById("campaignOverview");
        var campaignOverviewBtn = document.getElementById("toggleOverview");
        if (overviewDiv.style.display === "none") {
            overviewDiv.style.display = "block";
            campaignOverviewBtn.textContent = "Close Campaign Overview";
        } else {
            overviewDiv.style.display = "none";
            campaignOverviewBtn.textContent = "Campaign Overview";
        }
    }

    function togglePatronStatus() {
        var patronStatusDiv = document.getElementById("patronStatus");
        var patronStatusBtn = document.getElementById("togglePatronStatusBtn");
        if (patronStatusDiv.style.display === "none") {
            patronStatusDiv.style.display = "block";
            patronStatusBtn.textContent = "Close Patron Status Overview";
        } else {
            patronStatusDiv.style.display = "none";
            patronStatusBtn.textContent = "Patron Status";
        }
    }

</script>