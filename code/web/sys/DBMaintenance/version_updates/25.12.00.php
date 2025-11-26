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
		'grouped_work_display_settings_showIndexedSeriesWithNoveList' => [
			'title' => 'Grouped Work Display Settings - Add Show Indexed Series with NoveList',
			'description' => 'Add showIndexedSeriesWithNoveList field to grouped_work_display_settings table to control whether indexed series are displayed alongside NoveList or manual override series.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS showIndexedSeriesWithNoveList TINYINT(1) DEFAULT 0",
			]
		],
		'grouped_work_display_settings_numSeriesToShowBeforeMore' => [
			'title' => 'Grouped Work Display Settings - Add Number of Series to Show Before "More Series" Link',
			'description' => 'Add numSeriesToShowBeforeMore field to grouped_work_display_settings table to configure the number of series entries displayed before showing "More Series..." link.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS numSeriesToShowBeforeMore INT(11) DEFAULT 3",
			]
		],
		'grouped_work_display_settings_hideIndexedEContentSeries' => [
			'title' => 'Grouped Work Display Settings - Add Hide Indexed E-Content Series',
			'description' => 'Add hideIndexedEContentSeries field to grouped_work_display_settings table to control whether indexed series from eContent sources (OverDrive, Hoopla) are hidden from display.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS hideIndexedEContentSeries TINYINT(1) DEFAULT 0",
			]
		],
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
