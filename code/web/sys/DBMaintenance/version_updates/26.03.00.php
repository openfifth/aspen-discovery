<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_03_00(): array {
	$now = time();

	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark n

		//kirstien

		//kodi

		//yanjun

		//imani

		//galen

		//alexander
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

		//chloe

		//mark j

		//lucas


		//tomas

		//other


	];
}
