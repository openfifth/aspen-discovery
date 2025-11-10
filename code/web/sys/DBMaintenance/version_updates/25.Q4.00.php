<?php

/** @noinspection PhpUnused */
function getUpdates25_Q4_00(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		 //alexander - Open Fifth
		 'change_data_types_for_grapes_js_columns' => [
			'title' => 'Change Data Types For Grapes JS Columns',
			'description' => 'Update column types to allow for longer pages',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grapes_web_builder MODIFY templateContent LONGTEXT",
				"ALTER TABLE grapes_web_builder MODIFY htmlData LONGTEXT",
				"ALTER TABLE grapes_web_builder MODIFY cssData LONGTEXT",
			]
		], //change_data_types_for_grapes_js_columns
		'add_user_removed_campaigns_table' =>[
			'title' => 'Add User Removed Campaigns Table',
			'description' => 'Add the ability for user to remove campaigns from their account area',
			'continueOnError' => false,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS user_removed_campaigns (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					userId INT NOT NULL, 
					campaignId INT NOT NULL, 
					UNIQUE KEY user_campaign (userid, campaignId),
					INDEX (userId),
					INDEX (campaignId)
				) ENGINE = InnoDB'
			]
		],// add_user_removed_campaigns_table
	];
}