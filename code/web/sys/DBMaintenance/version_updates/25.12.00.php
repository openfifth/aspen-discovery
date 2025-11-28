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
		'reading_history_add_barcode' => [
			'title' => 'Reading History - Add Barcode Field',
			'description' => 'Add barcode column to user_reading_history_work table to store item barcode for historical checkouts.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user_reading_history_work ADD COLUMN IF NOT EXISTS barcode VARCHAR(50) DEFAULT NULL AFTER sourceId",
			]
		],
		'reading_history_add_edited_checkin_date' => [
			'title' => 'Reading History - Add Edited Check-In Date Field',
			'description' => 'Add editedCheckInDate column to user_reading_history_work table to store user-edited check-in dates while preserving original checkInDate.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user_reading_history_work ADD COLUMN IF NOT EXISTS editedCheckInDate BIGINT(20) DEFAULT NULL AFTER checkInDate",
			]
		],

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
