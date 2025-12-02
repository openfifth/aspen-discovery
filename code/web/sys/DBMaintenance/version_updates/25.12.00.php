<?php

/** @noinspection PhpUnused */
function getUpdates25_12_00(): array {
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
		'library_options_to_disable_pickup_locations' => [
			'title' => 'Library Options to Disable Pickup Locations',
			'description' => 'Add options to library settings to allow disabling pickup locations',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN hidePickupLocationPrompt TINYINT(1) DEFAULT 0',
				'ALTER TABLE library ADD COLUMN allowChangingPickupLocationForUnavailableHolds TINYINT(1) DEFAULT 1',
			]
		], //library_options_to_disable_pickup_locations
		'increase_hoopla_status_field' => [
			'title' => 'Increase Hoopla Status Field Length',
			'description' => 'Increase Hooopla Status Field Length',
			'sql' => [
				'ALTER TABLE hoopla_flex_availability CHANGE COLUMN status status VARCHAR(20) NOT NULL'
			]
		], //increase_hoopla_status_field
		'search_interpreter_remove_custom_facets' => [
			'title' => 'Remove Custom Facets from Search Interpreter',
			'description' => 'Remove Custom Facets from Search Interpreter in favor of special terms',
			'sql' => [
				'ALTER TABLE search_interpreter_settings DROP COLUMN audienceFacet',
				'ALTER TABLE search_interpreter_settings DROP COLUMN fictionNonFictionFacet'
			]
		], //search_interpreter_remove_custom_facets
		'library_enable_website_search' => [
			'title' => 'Library - Enable Website Search',
			'description' => 'Add Enable Website Search to Library Settings',
			'sql' => [
				'ALTER TABLE library ADD COLUMN showWebsiteSearch TINYINT default 1'
			]
		], //library_enable_website_search
		'prioritized_shelf_locations' => [
			'title' => 'Prioritized Shelf Locations',
			'description' => 'Add Prioritized Shelf Locations Table',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS prioritized_shelf_locations (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					groupedWorkSettingsId INT NOT NULL,
					shelfLocation varchar(250) DEFAULT '',
					weight INT(11) DEFAULT 0,
					INDEX (groupedWorkSettingsId, weight)
				) ENGINE = InnoDB"
			]
		], //prioritized_shelf_locations
		'message_bee_settings' => [
			'title' => 'Message Bee Settings',
			'description' => 'Message Bee Settings table and link to library',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS message_bee_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL,
					customerToken VARCHAR(50) NOT NULL 
				) ENGINE = InnoDB",
				"ALTER TABLE library ADD COLUMN messageBeeSettingId INT DEFAULT -1",
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Third Party Enrichment', 'Administer MessageBee Keys', '', 70, 'Allows users to administer Message Bee Keys.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer MessageBee Keys'))",
			]
		], //message_bee_settings
		'limit_access_to_shared_records' => [
			'title' => 'Limit Access To Shared Records',
			'description' => 'Limit Access To Shared Administration Records',
			'sql' => [
				"CREATE TABLE administration_record_lock (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					module varchar(30) NOT NULL,
					toolName varchar(100) NOT NULL,
					recordId INT NOT NULL,
					UNIQUE (module, toolName, recordId)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci",
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('System Administration', 'Lock Administration Records', '', 70, 'Allows the user to lock administration records and change locked records.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Lock Administration Records'))",
			]
		], //limit_access_to_shared_records
		'library_google_analytics' => [
			'title' => 'Library Google Analytics',
			'description' => 'Add Library Google Analytics Table',
			'sql' => [
				"DROP TABLE library_google_analytics",
				"CREATE TABLE IF NOT EXISTS library_google_analytics (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					libraryId int(11) NOT NULL,
					googleApiSettingId INT(11) NOT NULL,
					googleAnalyticsTrackingId varchar(50) DEFAULT NULL,
					UNIQUE (libraryId, googleApiSettingId)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci"
			]
		], //library_google_analytics

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'library_self_reg_form_message_translations' => [
			'title' => 'Library - Translate Self Registration Form Message',
			'description' => 'Copy existing self registration form messages into the text block translation table for all available languages.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO text_block_translation (objectType, objectId, fieldName, languageId, translation)
				SELECT 'Library', l.libraryId, 'selfRegistrationFormMessage', lang.id, l.selfRegistrationFormMessage
				FROM library l
				JOIN languages lang ON 1=1
				LEFT JOIN text_block_translation existing ON existing.objectType = 'Library' AND existing.objectId = l.libraryId AND existing.fieldName = 'selfRegistrationFormMessage' AND existing.languageId = lang.id
				WHERE l.selfRegistrationFormMessage IS NOT NULL AND l.selfRegistrationFormMessage <> '' AND existing.id IS NULL AND lang.code NOT IN ('ubb','pig')"
			]
		], //library_self_reg_form_message_translations
		'library_self_reg_success_message_translations' => [
			'title' => 'Library - Translate Self Registration Success Message',
			'description' => 'Copy existing self registration success messages into the text block translation table for all available languages.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO text_block_translation (objectType, objectId, fieldName, languageId, translation)
				SELECT 'Library', l.libraryId, 'selfRegistrationSuccessMessage', lang.id, l.selfRegistrationSuccessMessage
				FROM library l
				JOIN languages lang ON 1=1
				LEFT JOIN text_block_translation existing ON existing.objectType = 'Library' AND existing.objectId = l.libraryId AND existing.fieldName = 'selfRegistrationSuccessMessage' AND existing.languageId = lang.id
				WHERE l.selfRegistrationSuccessMessage IS NOT NULL AND l.selfRegistrationSuccessMessage <> '' AND existing.id IS NULL AND lang.code NOT IN ('ubb','pig')"
			]
		], //library_self_reg_success_message_translations

		//alexander - Open Fifth

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		// Galen Charlton - Equinox
		'chilifresh_cover_art_settings' => [
			'title' => 'ChiliFresh covert art settings',
			'description' => 'ChiliFresh covert art settings',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS chilifresh_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					enabled TINYINT(1) NOT NULL DEFAULT 1,
					genericArtCode TINYTEXT
				) ENGINE = InnoDB",
			]
		]
		//other

	];
}
