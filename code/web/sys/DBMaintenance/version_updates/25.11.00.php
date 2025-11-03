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
		'event_search_options' => [
			'title' => 'Event Search Options',
			'description' => 'Event Search Options (for Aspen Events)',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN aspenEventsToInclude INT DEFAULT 2',
				'UPDATE library SET aspenEventsToInclude = 1 WHERE isConsortialCatalog = 1'
			]
		], //event_search_options
		'remove_location_event_settings' => [
			'title' => 'Remove Location Event Settings',
			'description' => 'Remove Location Event Settings',
			'continueOnError' => false,
			'sql' => [
				'DROP TABLE location_events_setting'
			]
		], //event_search_options

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
				) ENGINE = InnoDB',
			]
		], //event_field_calendar_options
		'calendar_display_setting_library' => [
			'title' => 'Calendar Settings by Library',
			'description' => 'Create calendar_display_setting_library_table.',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS calendar_display_setting_library (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					calendarDisplaySettingId INT NOT NULL,
					libraryId INT NOT NULL
				) ENGINE = InnoDB',
			]
		], //calendar_display_setting_library

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'grouped_work_display_settings_showItemBarcodes' => [
			'title' => 'Grouped Work Display Settings - Show Item Barcodes',
			'description' => 'Add option to show item barcodes in copy details.',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS showItemBarcodes TINYINT(1) DEFAULT 0",
			],
		], //grouped_work_display_settings_showItemBarcodes
		'add_theme_soft_delete_columns' => [
			'title' => 'Add Soft Delete Columns to Themes',
			'description' => 'Add metadata needed to support Object Restorations for Themes.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE themes ADD COLUMN IF NOT EXISTS deleted TINYINT(1) DEFAULT 0",
				"ALTER TABLE themes ADD COLUMN IF NOT EXISTS dateDeleted INT(11) DEFAULT 0",
				"ALTER TABLE themes ADD COLUMN IF NOT EXISTS deletedBy INT(11) DEFAULT NULL",
			],
		], //add_theme_soft_delete_columns
		'update_log4j_patterns' => [
			'title' => 'Update Log4j Pattern Layouts',
			'description' => 'Add stack trace output to log4j pattern layouts for better exception debugging in Java processes.',
			'continueOnError' => true,
			'sql' => [
				'updateLog4jPatterns'
			]
		], //update_log4j_patterns

		//alexander - Open Fifth
		'add_use_library_name_for_maps' => [
			'title' => 'Add Use Library Name For Maps',
			'description' => 'Allow libraries to use library name for google maps',
			'sql' => [
				"ALTER TABLE location ADD COLUMN useLocationNameForMaps TINYINT(1) DEFAULT 0",
			]
		], //add_use_library_name_for_maps
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

function updateLog4jPatterns(&$update): void {
	global $serverName;

	$confPath = '/usr/local/aspen-discovery/sites/' . $serverName . '/conf/';

	if (!is_dir($confPath)) {
		$update['status'] = "Configuration directory not found at $confPath.";
		$update['success'] = true; // Still success, just nothing to update.
		return;
	}

	$log4jFiles = glob($confPath . 'log4j*.properties');

	if (empty($log4jFiles)) {
		$update['status'] = "No log4j properties files found in $confPath";
		$update['success'] = true;
		return;
	}

	$updatedFiles = 0;
	$errorMessages = [];

	foreach ($log4jFiles as $log4jFile) {
		try {
			$content = file_get_contents($log4jFile);
			$originalContent = $content;

			// Update console pattern: %m%n -> %m%n%throwable{short}%n.
			$content = preg_replace(
				'/^(appender\.console\.layout\.pattern\s*=.*%m%n)(?!%throwable)(.*)$/m',
				'$1%throwable{short}%n$2',
				$content
			);

			// Update rolling file pattern: %m%n -> %m%n%throwable{short}%n.
			$content = preg_replace(
				'/^(appender\.rolling\.layout\.pattern\s*=.*%m%n)(?!%throwable)(.*)$/m',
				'$1%throwable{short}%n$2',
				$content
			);

			if ($content !== $originalContent) {
				if (file_put_contents($log4jFile, $content) !== false) {
					$updatedFiles++;
				} else {
					$errorMessages[] = basename($log4jFile);
				}
			}
		} catch (Exception $e) {
			$errorMessages[] = basename($log4jFile) . ': ' . $e->getMessage();
		}
	}

	$statusMessage = "Updated $updatedFiles log4j properties file(s) for $serverName.";
	if (!empty($errorMessages)) {
		$statusMessage .= ". Errors: " . implode(', ', $errorMessages);
	}

	$update['status'] = $statusMessage;
	$update['success'] = true;
}
