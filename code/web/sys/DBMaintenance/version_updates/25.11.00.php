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
		'library_allow_automatic_faceting' => [
			'title' => 'Library - Allow Automatic Faceting',
			'description' => 'Add a setting of whether applying automatic facets to search term is allowed.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library add COLUMN allowAutomaticFaceting TINYINT DEFAULT 0'
			]
		], //library_allow_automatic_faceting
		'google_translate_api_key' => [
			'title' => 'Add Google Translate API Key',
			'description' => 'Add the ability to store a Google Translate API Key.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE google_api_settings ADD COLUMN googleTranslateKey varchar(60) DEFAULT NULL'
			]
		], //google_translate_api_key
		'translator_indicate_google_translations' => [
			'title' => 'Translator - Indicate Google Translations',
			'description' => 'Track which translations were loaded using Google Translate.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE translations ADD COLUMN googleTranslated TINYINT DEFAULT 0',
			]
		], //translator_indicate_google_translations

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove
		'events_search_setting' => [
			'title' => 'Events Search Setting',
			'description' => 'Add column to events_indexing_settings for search scope settings.',
			'sql' => [
				'ALTER TABLE events_indexing_settings ADD COLUMN eventsSearchSetting INT DEFAULT 1'
			]
		],
		'event_field_calendar_options' => [
			'title' => 'Event Field Calendar Options',
			'description' => 'Create event_field_calendar_options table.',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS event_field_calendar_options (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					calendarDisplaySettingId INT NOT NULL,
					eventFieldId INT NOT NULL,
					weight INT DEFAULT 0,
					displayedOnline TINYINT(1) DEFAULT 0,
					printedCalendar TINYINT(1) DEFAULT 0,
					printedAgenda TINYINT(1) DEFAULT 0
					)',
			]
		], //event_field_calendar_options
		'calendary_display_setting_library' => [
			'title' => 'Calendar Settings by Library',
			'description' => 'Create calendar_display_setting_library_table.',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS calendar_display_setting_library (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					calendarDisplaySettingId INT NOT NULL,
					libraryId INT NOT NULL
				)',
			]
		], //calendary_display_setting_library

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

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
