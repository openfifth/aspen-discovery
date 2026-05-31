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
		'permissions_create_events_localhop' => [
			'title' => 'Alters permissions for Events',
			'description' => 'Create permissions for LocalHop',
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Events', 'Administer LocalHop Settings', 'Events', 20, 'Allows the user to administer integration with LocalHop for all libraries.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer LocalHop Settings'))",
			],
		],
		// permissions_create_events_localhop
		'localhop_settings' => [
			'title' => 'Define events settings for LocalHop integration',
			'description' => 'Initial setup of the LocalHop integration',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS localhop_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL UNIQUE,
					baseUrl VARCHAR(255) NOT NULL,
					eventsInLists tinyint(1) default 1,
					bypassAspenEventPages tinyint(1) default 0,
					registrationModalBody mediumtext,
					registrationModalBodyApp varchar(500),
					numberOfDaysToIndex INT DEFAULT 365
				) ENGINE INNODB',
			],
		], // localhop_settings
		'localhop_events' => [
			'title' => 'LocalHop Event Data',
			'description' => 'Set up table to store events data for LocalHop',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS localhop_events (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					settingsId INT NOT NULL,
					externalId varchar(150) NOT NULL,
					title varchar(255) NOT NULL,
					rawChecksum BIGINT,
					rawResponse MEDIUMTEXT,
					deleted TINYINT default 0,
					UNIQUE (settingsId, externalId)
				)',
			],
		], // localhop_events
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
		'allow_to_renew_ill_items' => [
			'title' => 'Allow Renewing ILL Items',
			'description' => 'Add allowToRenewILL to the library table to control whether patrons can renew ILL items.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN allowToRenewILL TINYINT(1) DEFAULT 1'
			]
		], //allow_to_renew_ill_items



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
