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

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove
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

		]

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - Open Fifth

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
