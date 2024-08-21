{strip}
    <h1>{translate text="Your Campaigns" isPublicFacing=true}</h1>

    {if empty($campaignList)}
        <div class="alert alert-info">
            {translate text="There are no available campaign at the moment" isPublicFacing=true}
        </div>
    {else}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Campaign Name:</th>
                    <th>Enrollment</th>
                    <th>Milestones Completed</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$campaignList item="campaign" key="resultIndex"}
                    <tr>
                        <td>{$campaign->name}</td>
                        <td>
                            {if $campaign->enrolled}
                                {translate text="Enrolled" isPublicFacing=true}
                            {else}
                                {translate text="Unenrolled" isPublicFacing=true}
                            {/if}
                        </td>
                        <td>
                        {if $campaign->enrolled}
                            {* <div class="progess" style="width:100%; border:1px solid black; border-radius:4px;height:20px;">
                                <div class="progress-bar" role="progressbar" aria-valuenow="{$campaing->progress}" aria-valuemin="0"
                                    aria-valuemax="100" style="width: {$campaign->progress}%;">
                                    {$campaign->progress}%
                                </div>
                            </div> *}
                            <div>
                                {$campaign->numCompletedMilestones} / {$campaign->numCampaignMilestones}
                            </div>
                        {/if}
                        </td>
                        <td>
                        {if $campaign->enrolled}
                            <button onclick="AspenDiscovery.Account.unenroll({$campaign->id}, {$userId});">{translate text="Unenroll" isPublicFacing=true}</button>
                        {else}
                            <button onclick="AspenDiscovery.Account.enroll({$campaign->id}, {$userId});">{translate text="Enroll" isPublicFacing=true}</button>
                        {/if}
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
                                            <td> {$campaign->milestoneCompletedGoals[$milestone->milestoneId]} / {$campaign->milestoneGoalCount[$milestone->milestoneId]}</td>
                                            <td>
                                                <div class="progress" style="width:100%; border:1px solid black; border-radius:4px;height:20px;">
                                                    <div class="progress-bar" role="progressbar" aria-valuenow="{$campaign->milestoneProgress[$milestone->milestoneId]}" aria-valuemin="0"
                                                     aria-valuemax="100" style="width: {$campaign->milestoneProgress[$milestone->milestoneId]}%; line-height: 20px; text-align: center; color: #fff;">
                                                        {$campaign->milestoneProgress[$milestone->milestoneId]}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>                                    
                                    {/foreach}
                                    </tbody>
                                </table>
                            </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
{/strip}
{literal}
    <script tupe="text/javascript">
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