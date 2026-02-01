<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_02_00(): array {
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

		//chloe

		//mark j
		'offer_immediate_hold_freeze' => [
			'title' => 'Library - Add the Ability to Freeze Holds Immediately',
			'description' => 'Within Library Settings, libraries can choose to offer patrons the ability to freeze their holds immediately.',
			'sql' => [
				"ALTER TABLE library ADD COLUMN offerImmediateHoldFreeze tinyint(1) NOT NULL DEFAULT 0",
			]
		],
		'prompt_to_freeze_holds_immediately' => [
			'title' => 'User - Add the Choice to Have a Prompt to Freeze Holds Immediately',
			'description' => 'Patrons will gain the choice within their Account Settings to have the system prompt them to freeze their holds immediately. (Requires that the library first offers this setting.)',
			'sql' => [
				"ALTER TABLE user ADD COLUMN promptToFreezeHoldsImmediately tinyint(1) NOT NULL DEFAULT 0",
			]
		], //offer_immediate_hold_freeze
		'share_tools_add_granularity' => [
			'title' => 'Library and Locations - Add more granularity to the sharing tools (Facebook, X, etc.)',
			'description' => 'Within Library Settings (and location settings), libraries can now choose specifically which social platforms they allow their customers to share on.',
			'sql' => [
				"ALTER TABLE library CHANGE COLUMN showShareOnExternalSites showShareOnX TINYINT DEFAULT 1",
				"ALTER TABLE location CHANGE COLUMN showShareOnExternalSites showShareOnX TINYINT DEFAULT 1",
				"ALTER TABLE library ADD showShareOnFacebook TINYINT DEFAULT 1",
				"UPDATE library SET showShareOnFacebook = IF(showShareOnX = 1, 1, 0)",
				"ALTER TABLE location ADD showShareOnFacebook TINYINT DEFAULT 1",
				"UPDATE location SET showShareOnFacebook = IF(showShareOnX = 1, 1, 0)",
				"ALTER TABLE library ADD showShareOnPinterest TINYINT DEFAULT 1",
				"UPDATE library SET showShareOnPinterest = IF(showShareOnX = 1, 1, 0)",
				"ALTER TABLE location ADD showShareOnPinterest TINYINT DEFAULT 1",
				"UPDATE location SET showShareOnPinterest = IF(showShareOnX = 1, 1, 0)",
				"ALTER TABLE library ADD showShareOnLink TINYINT DEFAULT 1",
				"UPDATE library SET showShareOnLink = IF(showShareOnX = 1, 1, 0)",
				"ALTER TABLE location ADD showShareOnLink TINYINT DEFAULT 1",
				"UPDATE location SET showShareOnLink = IF(showShareOnX = 1, 1, 0)",
			]
		],  //share_tools_add_granularity

		//lucas


		//tomas

		//other


	];
}
