{strip}
    <h1>{translate text="Campaigns" isPublicFacing=true}</h1>
    {if empty($campaignList)}
        <div class="alert alert-info">
            {translate text="There are no available campaigns at the moment" isPublicFacing=true}
        </div>
    {else}
        <h2>Your Campaigns</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Campaign Name:</th>
                    <th>Milestones Completed</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$campaignList item="campaign" key="resultIndex"}
                {if $campaign->enrolled}
                    <tr>
                        <td>{$campaign->name}</td>
                        <td>{$campaign->numCompletedMilestones} / {$campaign->numCampaignMilestones}</td>
                        <td>
                            <button onclick="AspenDiscovery.Account.unenroll({$campaign->id}, {$userId});">{translate text="Unenroll" isPublicFacing=true}</button>
                        </td>
                        <td>
                            <button onclick="toggleCampaignInfo({$resultIndex});">{translate text="Campaign Information" isPublicFacing=true}</button>
                        </td>
                    </tr>
                    <tr id="campaignInfo_{$resultIndex}" style="display:none;">
                            <td colspan="4">
                                {* <h4>{translate text="Milestones"}</h4> *}
                                <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{translate text="Start Date" isPublicFacing=true}</th>
                                        <th>{translate text="End Date" isPublicFacing=true}</th>
                                        <th>{translate text="Milestone" isPublicFacing=true}</th>
                                        <th>{translate text="Progress Towards Milestone" isPublicFacing=true}</th>
                                        <th>{translate text="Progess Percentage" isPublicFacing=true}</th>
                                    </tr>
                                </thead>
                                    <tbody>
                                    {foreach from=$campaign->milestones item="milestone"}
                                        <tr>
                                            <td>{$campaign->startDate}</td>
                                            <td>{$campaign->endDate}</td>
                                            <td>{$milestone->name}</td>
                                            <td>
                                                {$milestone->completedGoals}/ {$milestone->totalGoals}
                                                {foreach from=$milestone->progressData item="progressData"}
                                                <div style="padding:10px;">
                                                    {$progressData['title']}
                                                </div>
                                                {/foreach}
                                            </td>
                                            <td>
                                                <div class="progress" style="width:100%; border:1px solid black; border-radius:4px;height:20px;">
                                                    <div class="progress-bar" role="progressbar" aria-valuenow="{$milestone->progress}" aria-valuemin="0"
                                                     aria-valuemax="100" style="width: {$milestone->progress}%; line-height: 20px; text-align: center; color: #fff;">
                                                        {$milestone->progress}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>                                 
                                    {/foreach}
                                    </tbody>
                                </table>
                            </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
        <h2>Active Campaigns</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Campaign Name</th>
                    <th>End Date</th>
                    <th>Enrollment</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$campaignList item="campaign" key="resultIndex"}
                {if $campaign->isActive}
                    <tr>
                        <td>{$campaign->name}</td>
                        <td>{$campaign->endDate}</td>
                        {if $campaign->enrolled}
                            <td>{translate text="Enrolled" isPublicFacing=true}</td>
                        {else}
                            <td>{translate text="Not Enrolled" isPublicFacing=true}</td>
                        {/if}
                        {if $campaign->enrolled}
                        <td>
                            <button onclick="AspenDiscovery.Account.unenroll({$campaign->id}, {$userId});">{translate text="Unenroll" isPublicFacing=true}</button>
                        </td>
                        {else}
                            <td>
                                <button onclick="AspenDiscovery.Account.enroll({$campaign->id}, {$userId});">{translate text="Enroll" isPublicFacing=true}</button>
                            </td>
                        {/if}
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
        <h2>Upcoming Campaigns</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Campaign Name</th>
                    <th>Start Date</th>
                    <th>Enrollment</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            {foreach from=$campaignList item="campaign" key="resultIndex"}
                {if $campaign->isUpcoming}
                    <tr>
                        <td>{$campaign->name}</td>
                        <td>{$campaign->startDate}</td>
                        {if $campaign->enrolled}
                            <td>{translate text="Enrolled" isPublicFacing=true}</td>
                        {else}
                            <td>{translate text="Not Enrolled" isPublicFacing=true}</td>
                        {/if}
                        {if $campaign->enrolled}
                            <td>
                                <button onclick="AspenDiscovery.Account.unenroll({$campaign->id}, {$userId});">{translate text="Unenroll" isPublicFacing=true}</button>
                            </td>
                            {else}
                                <td>
                                    <button onclick="AspenDiscovery.Account.enroll({$campaign->id}, {$userId});">{translate text="Enroll" isPublicFacing=true}</button>
                                </td>
                            {/if}
                    </tr>
                {/if}
            {/foreach}
        </table>
        <h2>Past Campaigns</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Campaign Name</th>
                    <th>End Date</th>
                    <th>Campaign Milestones</th>
                    <th>Campaign Reward</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$pastCampaigns item="campaign" key="resultIndex"}
                <tr>
                    <td>{$campaign->name}</td>
                    <td>{$campaign->endDate}</td>
                    {foreach from=$campaign->milestones item="milestone"}
                       <td>{$milestone->name}</td>
                    {/foreach}
                    <td>{$campaign->rewardName}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/if}
{/strip}
{literal}
    <script type="text/javascript">
           function toggleCampaignInfo(index) {
            var campaignInfoDiv = document.getElementById('campaignInfo_' + index);
            if (campaignInfoDiv.style.display === 'none') {
                campaignInfoDiv.style.display = 'block';
            } else {
                campaignInfoDiv.style.display = 'none';
            }
        }       
    </script>
{/literal}