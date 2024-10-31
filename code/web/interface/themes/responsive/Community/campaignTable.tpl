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
                {foreach from=$campaign->getUsersForCampaign() item=user}
                        <tr>
                            <td>{$user->id}</td>
                            <td>{$user->username}</td>
                            {foreach from=$milestones item=milestone}
                                <td>
                                {if}
                                </td>
                            {/foreach}
                        </tr>
                {/foreach}
        </tbody>
    </table>
</div>