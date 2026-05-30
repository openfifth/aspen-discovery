<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_06_00(): array {
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
		'addForceReadingHistoryOptIn' => [
			'title' => 'Add option force patrons to opt-in to reading history',
			'description' => 'Add option to ignore Koha/ILS settings and force new patrons to opt-in to reading history',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN forceReadingHistoryOptIn TINYINT(1) DEFAULT 0',
			]
		],
		//addForceReadingHistoryOptIn

		//kodi
		'scheduled_offline_mode' => [
			'title' => 'Scheduled Offline Mode',
			'description' => 'Add columns to system variables table for scheduling offline mode.',
			'sql' => [
				'ALTER TABLE system_variables ADD COLUMN scheduledOfflineStart int(11) DEFAULT NULL',
				'ALTER TABLE system_variables ADD COLUMN scheduledOfflineEnd int(11) NULL DEFAULT NULL',
				'ALTER TABLE system_variables ADD COLUMN scheduledEcontentAccess TINYINT(1) NOT NULL DEFAULT 0',
			]
		], //scheduled_offline_mode
		'scoped_more_like_this' => [
			'title' => 'Scoped More Like This',
			'description' => 'Add setting for scoping options for More Like This feature.',
			'sql' => [
				'ALTER TABLE library ADD COLUMN moreLikeThisSettings tinyint(1) DEFAULT 1',
			]
		], //scoped_more_like_this

		//yanjun

		//imani

		//galen

		//chloe

		//pedro

		//mark j

		//lucas
		'language_add_is_default' => [
			'title' => 'Add Default Language Flag',
			'description' => 'Adds an isDefault column to the languages table to allow admins to designate a default language for unauthenticated users. English is set as the initial default to preserve existing behavior.',
			'sql' => [
				'ALTER TABLE languages ADD COLUMN isDefault TINYINT(1) NOT NULL DEFAULT 0',
				"UPDATE languages SET isDefault = 1 WHERE code = 'en' LIMIT 1",
			],
		], //language_add_is_default

		//tomas

		// stephen

		'user_payments_receipt_url_rename' => [
		'title' => 'Rename Receipt URL Column',
		'description' => 'Rename column from stripeReceiptUrl to receiptUrl.',
		'continueOnError' => false,
		'sql' => [
			'ALTER TABLE user_payments CHANGE stripeReceiptUrl receiptUrl VARCHAR(255) DEFAULT NULL'
		]
	], //user_payments_receipt_url_rename

		//other

	];
}
