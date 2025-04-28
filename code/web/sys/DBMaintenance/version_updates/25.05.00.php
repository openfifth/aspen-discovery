<?php

function getUpdates25_05_00(): array {
	$curTime = time();
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark - Grove

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - Open Fifth
		'allow_filtering_of_linked_users_in_checkouts' => [
			'title' => 'Allow Filtering of Linked Users in Checkouts',
			'description' => 'Allow libraries the option of allowing users to filter their checkouts by linked user',
			'sql' => [
				'ALTER TABLE library ADD COLUMN allowFilteringOfLinkedAccountsInCheckouts TINYINT(1) DEFAULT 0',
			],
		], //allow_filtering_of_linked_users_in_checkouts
		'allow_selecting_checkouts_to_export' => [
			'title' => 'Allow Selecting Checkouts to Export',
			'description' => 'Allow libraries the option of allowing users to export only selected checkouts',
			'sql' => [
				'ALTER TABLE library ADD COLUMN allowSelectingCheckoutsToExport TINYINT(1) DEFAULT 0'
			],
		], //allow_selecting_checkouts_to_export
		'add_weight_to_campaign_milestones' => [
			'title' => 'Add Weight To Campaign Milestones',
			'description' => 'Add a weight column to campaign milestones to allow ordering',
			'sql' => [
				"ALTER TABLE ce_campaign_milestones ADD COLUMN weight int(11) NOT NULL DEFAULT 0",
			],
		], 
		'add_ability_to_highlight_campaigns_in_account_area' => [
			'title' => 'Add Ability to Highlight Campaigns In Account Area',
			'description' => 'Allow libraries to choose whether to display a block highlighting campaigns on the account page',
			'sql' => [
				"ALTER TABLE library ADD COLUMN highlightCommunityEngagement TINYINT(1) DEFAULT 0",
			],
		],

		//chloe - Open Fifth
		'permanentUrl_allows_longer_strings' => [
			'title' => 'PermanentUrl Allows For Longer Strings',
			'description' => 'Allow for longer permanent URLs so that Open Archive records can be indexed without clashing with the length constraint',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE open_archives_record MODIFY COLUMN permanentUrl VARCHAR(2048) NOT NULL",
			]
		], // permanentUrl_allows_longer_strings

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
