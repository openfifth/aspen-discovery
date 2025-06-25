<?php
/**@noinspection SqlResolve*/
function getCampaignMilestoneProgressEntriesUpdates() {
	return [
        'add_timestamp_to_ce_campaign_milestone_progress_entries' => [
            'title' => 'Update ce_campaign_milestone_progress_entries',
            'description' => 'Add timestamp to ce_campaign_milestone_progress_entries',
            'sql' => [
                "ALTER TABLE ce_campaign_milestone_progress_entries ADD COLUMN `timestamp` datetime NOT NULL DEFAULT (CURRENT_TIME)",
            ],
        ],
    ];
}