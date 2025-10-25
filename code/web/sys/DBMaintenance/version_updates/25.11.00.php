<?php

/** @noinspection PhpUnused */
function getUpdates25_11_00(): array {
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
		'add_user_list_group_table' => [
			'title' => 'Add list group table',
			'description' => 'Add table to support grouping of lists',
			'continueOnError' => false,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS user_list_group (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					title VARCHAR(255) NOT NULL,
					parentGroupId INT(11) DEFAULT NULL,
					userId INT(11) NOT NULL
				)'
			],
		],
		//add_user_list_group_table
		'add_last_used_group_list_view_to_user' => [
			'title' => 'Add last viewed list group to user',
			'description' => 'Add column to track last viewed list group for users',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE user ADD COLUMN lastGroupListViewed INT(11) DEFAULT NULL',
			]
		],
		//add_last_used_group_list_view_to_user
		'add_last_used_group_list_added_to_user' => [
			'title' => 'Add last added used group list to user',
			'description' => 'Add column to track last added list group for users',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE user ADD COLUMN lastGroupListAdded INT(11) DEFAULT NULL',
			]
		],
		//add_last_used_group_list_added_to_user
		'add_group_list_id_to_user_list' => [
			'title' => 'Add group list id to user list',
			'description' => 'Add column to track group list id for user lists',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE user_list ADD COLUMN groupListId INT(11) DEFAULT -1',
			]
		],
		//add_group_list_id_to_user_list

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - Open Fifth

		//chloe - Open Fifth


		//Jacob - Open Fifth

		//Pedro - Open Fifth


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions
		'forceDebugLog' => [
			'title' => 'Enable Forced Logging of Debugging Information for WorldPay Payments',
			'description' => 'Enable to show debugging information about WorldPay payments regardless of whether the user IP is authorized or not',
			'sql' => [
				'ALTER TABLE worldpay_settings ADD COLUMN forceDebugLog TINYINT(1) DEFAULT 0',
			]
		], //enable_worldpay_debug_logging

		//other

		//Talpa Search

		// Brendan Lawlor

	];
}
