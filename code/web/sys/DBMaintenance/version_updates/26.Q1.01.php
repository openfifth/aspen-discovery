<?php

/** @noinspection PhpUnused */
function getUpdates26_Q1_01(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/
		'sierra_phone_fields' => [
			'title' => 'Sierra Phone Fields',
			'description' => 'Add configurable phone fields for Sierra Phone and Work Phone',
			'sql' => [
				"ALTER TABLE library ADD COLUMN phoneField CHAR(1) DEFAULT 't'",
				"ALTER TABLE library ADD COLUMN workPhoneField CHAR(1) DEFAULT 'p'"
			]
		], //sierra_phone_fields

		//pedro
		'update_community_engagement_schema_v2' => [
			'title' => 'Normalize Community Engagement to allow multiple milestone/extra credit instances',
			'description' => 'Shifts progress tracking from (Campaign+Item) ID pairs to a single Junction ID (Instance) to allow duplicate items within one campaign.',
			'sql' => [
				// --- 1. MILESTONES: Add the new instance-based foreign keys ---
				"ALTER TABLE `ce_campaign_milestone_users_progress` ADD COLUMN `ce_campaign_milestone_id` int(11) DEFAULT NULL AFTER `ce_milestone_id`",
				"ALTER TABLE `ce_campaign_milestone_progress_entries` ADD COLUMN `ce_campaign_milestone_id` int(11) DEFAULT NULL AFTER `ce_milestone_id`",
				"ALTER TABLE `ce_user_completed_milestones` ADD COLUMN `ce_campaign_milestone_id` int(11) DEFAULT NULL AFTER `milestoneId`",

				// --- 2. MILESTONES: Data Migration (Map coordinates to unique instance IDs) ---
				"UPDATE `ce_campaign_milestone_users_progress` up
					JOIN `ce_campaign_milestones` cm ON up.ce_campaign_id = cm.campaignId AND up.ce_milestone_id = cm.milestoneId
					SET up.ce_campaign_milestone_id = cm.id",
				"UPDATE `ce_campaign_milestone_progress_entries` pe
					JOIN `ce_campaign_milestones` cm ON pe.ce_campaign_id = cm.campaignId AND pe.ce_milestone_id = cm.milestoneId
					SET pe.ce_campaign_milestone_id = cm.id",
				"UPDATE `ce_user_completed_milestones` ucm
					JOIN `ce_campaign_milestones` cm ON ucm.campaignId = cm.campaignId AND ucm.milestoneId = cm.milestoneId
					SET ucm.ce_campaign_milestone_id = cm.id",

				// --- 3. MILESTONES: Cleanup and Constraint Enforcement ---
				"ALTER TABLE `ce_campaign_milestone_users_progress`
					MODIFY COLUMN `ce_campaign_milestone_id` int(11) NOT NULL,
					DROP COLUMN `ce_campaign_id`,
					DROP COLUMN `ce_milestone_id`",
				"ALTER TABLE `ce_campaign_milestone_progress_entries`
					MODIFY COLUMN `ce_campaign_milestone_id` int(11) NOT NULL,
					DROP COLUMN `ce_campaign_id`,
					DROP COLUMN `ce_milestone_id`",
				"ALTER TABLE `ce_user_completed_milestones`
					MODIFY COLUMN `ce_campaign_milestone_id` int(11) NOT NULL,
					DROP COLUMN `campaignId`,
					DROP COLUMN `milestoneId`",
				"ALTER TABLE `ce_milestone` DROP COLUMN `campaignId`",

			]
		],
	];
}
