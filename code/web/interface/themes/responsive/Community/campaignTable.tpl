<div id="campaignDetails">
hi
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
            </tr>
        </thead>
        <tbody>
                {foreach from=$users item=user}
                        <tr>
                            <td>{$user->id}</td>
                            <td>{$user->username}</td>
                            {foreach from=$milestones item=milestone}
                                <td>
                                {if $userCampaigns[$campaign->id][$user->id]['milestones'][$milestone->id]['milestoneComplete']}
                                    <div style="display: flex; justify-content: space-between;">
                                        <div style="margin-right: 20px;">
                                            {translate text="Complete" isAdminFacing=true}
                                        </div>
                                    <div>
                                        {if $userCampaigns[$camapign->id][$user->id]['milestones'][$milestone->id]['rewardGiven'] == 0}
                                            <button class="set-reward-btn-milestone" data-user-id="{$user->id}" data-campaign-id="{$campaign->id}" data-milestone-id="{$milestone->id}" onclick="AspenDiscovery.CommunityEngagement.milestoneRewardGiven({$user->id}, {$campaign->id}, {$milestone->id});">
                                                {translate text="Set Reward as Given" isAdminFacing=true}
                                            </button>
                                        {else}
                                            {translate text="Reward Given" isAdminFacing=true}
                                        {/if}
                                    </div>
                                </div>
                                {else}
                                    <div>
                                        {translate text="Incomplete" isAdminFacing=true}
                                    </div>
                                {/if}
                            </td>
                            {/foreach}
                            <td>
                            {if $userCampaigns[$camapign->id][$user->id]['isCampaignComplete']}
                                {translate text="Campaign Complete" isAdminFacing=true}
                            {else}
                                {translate text="Campaign Inomplete" isAdminFacing=true}
                            {/if}
                            </td>
                            <td>
                                {if $userCampaigns[$campaign->id][$user->id]['rewardGiven'] == 0}
                                    <button class="set-reward-btn" data-user-id="{$user->id}" data-campaign-id="{$campaign->id}" onclick="AspenDiscovery.CommunityEngagement.campaignRewardGiven({$user->id}, {$campaign->id});">
                                        {translate text="Set Reward as Given" isAdminFacing=true}
                                    </button>
                                {else}
                                    {translate text="Reward Given" isAdminFacing=true}
                                {/if}
                            </td>
                        </tr>
                {/foreach}
        </tbody>
    </table>
</div>