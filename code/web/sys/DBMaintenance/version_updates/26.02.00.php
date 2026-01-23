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
		'create_cloudsource_table' => [
			'title' => 'Create CloudSource OA Table',
			'description' => 'Create DB table for CloudSource OA',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS cloudsource_setting (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(255) NOT NULL,
					baseUrl VARCHAR(255) NOT NULL,
					accessToken VARCHAR(255),
					profileKey VARCHAR(255)
				) ENGINE=INNODB",
			]
		], //create_cloudsource_table
		'add_cloudsource_permissions' => [
			'title' => 'Add Cloud Source Permissions',
			'description' => 'Add Cloud Source Permissions',
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('', 'Administer CloudSource OA', 'CloudSource', 40, 'Allows users to administer CloudSource OA settings.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer CloudSource OA'))",
			]
		], //add_cloudsource_permissions
		'add_cloudsource_module' => [
			'title' => 'Create CloudSource OA module',
			'description' => 'Setup module for CloudSource OA',
			'sql' => [
				"INSERT INTO modules (name) VALUES ('CloudSource')",
			],
		], //add_cloudsource_module
		'library_location_cloudsource_settings' => [
			'title' => 'Library Location CloudSource Settings',
			'description' => 'Create tables for library and location CloudSource OA settings',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS library_cloudsource_setting (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					libraryId INT(11),
					cloudsourceSettingId INT(11)
				) ENGINE=INNODB",
				"CREATE TABLE IF NOT EXISTS location_cloudsource_setting (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					locationId INT(11),
					cloudsourceSettingId INT(11)
				) ENGINE=INNODB",
			]
		], //library_location_cloudsource_settings

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

		//lucas


		//tomas

		//other


	];
}
