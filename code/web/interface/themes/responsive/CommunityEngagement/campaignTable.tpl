{strip}
<div id="campaignDetails">
    <h2>{translate text=$campaign->name isAdminFacing=true}</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{translate text="User ID" isAdminFacing=true}</th>
                <th>{translate text="Username" isAdminFacing=true}</th>
                {foreach from=$milestones item=milestone}
                    <th>{translate text="Milestone: {$milestone->name}" isAdminFacing=true}</th>
                {/foreach}
                <th>{translate text="Campaign Complete" isAdminFacing=true}</th>
                <th>{translate text="Reward Given" isAdminFacing=true}</th>
                <th>{translate text="Extra Credit" isAdminFacing=true}</th>
            </tr>
        </thead>
        <tbody>
                {foreach from=$users item=user}
                        <tr>
                            <td>{$user->id}</td>
                            <td>{$user->displayName}</td>
                            {foreach from=$milestones item=milestone}
                                <td>
                                {if $userCampaigns[$campaign->id][$user->id]['milestones'][$milestone->id]['milestoneComplete']}
                                    <div style="display: flex; justify-content: space-between;">
                                        <div style="margin-right: 20px;">
                                            {translate text="Complete" isAdminFacing=true}
                                        </div>
                                    <div>
                                        {if $milestone->rewardType == 0 || $milestone->rewardType == 1 && $milestone->awardAutomatically == 0}
                                            {if $userCampaigns[$campaign->id][$user->id]['milestones'][$milestone->id]['milestoneRewardGiven'] == 0}
                                                <button class="btn btn-primary set-reward-btn-milestone" data-user-id="{$user->id}" data-campaign-id="{$campaign->id}" data-milestone-id="{$milestone->id}" onclick="AspenDiscovery.CommunityEngagement.milestoneRewardGiven({$user->id}, {$campaign->id}, {$milestone->id});">
                                                    {translate text="Give Reward" isAdminFacing=true}
                                                </button>
                                            {else}
                                                {translate text="Reward Given" isAdminFacing=true}
                                            {/if}
                                        {/if}
                                    </div>
                                </div>
                                {else}
                                    <div>
                                        {translate text="Incomplete" isAdminFacing=true}<br>
                                        <div class="progress" style="width:100%; border:1px solid black; border-radius:4px;height:20px;">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="{$userCampaigns[$campaign->id][$user->id]['milestones'][$milestone->id]['percentageProgress']}" aria-valuemin="0"
                                            aria-valuemax="100" style="width: {$userCampaigns[$campaign->id][$user->id]['milestones'][$milestone->id]['percentageProgress']}%; line-height: 20px; text-align: center; color: #fff;background-color:blue;">
                                                {$userCampaigns[$campaign->id][$user->id]['milestones'][$milestone->id]['percentageProgress']}%
                                            </div>
                                       </div>
                                        <button class="btn btn-primary set-reward-btn-milestone" data-user-id="{$user->id}" data-campaign-id="{$campaign->id}" data-milestone-id="{$milestone->id}" onclick="AspenDiscovery.CommunityEngagement.manuallyProgressMilestone({$milestone->id}, {$user->id}, {$campaign->id});">
                                            {translate text="Add Progress" isAdminFacing=true}
                                        </button>
                                    </div>
                                {/if}
                            </td>
                            {/foreach}
                            <td>
                            {if $userCampaigns[$campaign->id][$user->id]['isCampaignComplete']}
                                {translate text="Campaign Complete" isAdminFacing=true}
                            {else}
                                {translate text="Campaign Incomplete" isAdminFacing=true}
                            {/if}
                            </td>
                            <td>
                                {if $campaign->rewardType == 1 && $campaign->awardAutomatically == 1 && $userCampaigns[$campaign->id][$user->id]['isCampaignComplete']}
                                    {translate text="Rewarded Automatically" isAdminFacing=true}
                                {elseif $userCampaigns[$campaign->id][$user->id]['rewardGiven'] == 0}
                                    <button class="btn btn-primary set-reward-btn" data-user-id="{$user->id}" data-campaign-id="{$campaign->id}" onclick="AspenDiscovery.CommunityEngagement.campaignRewardGiven({$user->id}, {$campaign->id});">
                                        {translate text="Give Reward" isAdminFacing=true}
                                    </button>
                                {else}
                                    {translate text="Reward Given" isAdminFacing=true}
                                {/if}
                            </td>
                           <td>
                                <button class="btn btn-primary btn-sm" onclick="toggleExtraCreditDetails({$campaign->id}, {$user->id})">
                                    {translate text="Extra Credit" isAdminFacing=true}
                                </button>
                           </td>

                           <tr id="extraCreditDetails_{$campaign->id}_{$user->id}" class="extra-credit-details" style="display:none;">
                                <td colspan="6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{translate text="Extra Credit Activity" isAdminFacing=true}</th>
                                                <th>{translate text="Progress" isAdminFacing=true}</th>
                                                <th>{translate text="Reward" isAdminFacing=true}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach from=$extraCreditActivities item="extraCreditActivity"}
                                            <tr>
                                                <td>{$extraCreditActivity->name}</td>
                                                <td>
                                                    {$userCampaigns[$campaign->id][$user->id]['extraCreditActivities'][$extraCreditActivity->id]['percentageProgress']}%
                                                     <button class="btn btn-primary set-reward-btn-extra-credit" data-user-id="{$user->id}" data-campaign-id="{$campaign->id}" data-extraCreditActivity-id="{$extraCreditActivity->id}" onclick="AspenDiscovery.CommunityEngagement.addProgressToExtraCreditActivity({$extraCreditActivity->id}, {$user->id}, {$campaign->id});">
                                                        {translate text="Add Progress" isAdminFacing=true}
                                                    </button>
                                                </td>
                                                <td>
                                                   {if $userCampaigns[$campaign->id][$user->id]['extraCreditActivities'][$extraCreditActivity->id]['extraCreditRewardGiven'] == 0}
                                                        {if $extraCreditActivity->rewardType == 1 && $extraCreditActivity->awardAutomatically == 1 && $userCampaigns[$campaign->id][$user->id]['extraCreditActivities'][$extraCreditActivity->id]['extraCreditComplete'] == 1}
                                                            {translate text="Awarded Automatically" isAdminFacing=true}
                                                        {else}
                                                            <button class="btn btn-primary set-reward-btn-milestone" data-user-id="{$user->id}" data-campaign-id="{$campaign->id}" data-extraCreditActivity-id="{$extraCreditActivity->id}" onclick="AspenDiscovery.CommunityEngagement.extraCreditRewardGiven({$user->id}, {$campaign->id}, {$extraCreditActivity->id});">
                                                                {translate text="Give Reward" isAdminFacing=true}
                                                            </button>
                                                        {/if}
                                                    {elseif $userCampaigns[$campaign->id][$user->id]['extraCreditActivities'][$extraCreditActivity->id]['extraCreditRewardGiven'] == 1}
                                                        {translate text="Reward Given" isAdminFacing=true}
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </td>
                           </tr>
                        </tr>
                {/foreach}
        </tbody>
    </table>
</div>
{/strip}
{literal}
<script type="text/javascript">
    function toggleExtraCreditDetails(campaignId, userId) {
        var tableRow = document.getElementById('extraCreditDetails_' + campaignId + '_' + userId);

        if (tableRow) {
            if (tableRow.style.display === 'none' || tableRow.style.display == '') {
                tableRow.style.display = 'table-row';
            } else {
                tableRow.style.display = 'none';
            }
        }
    }
</script>
{/literal}