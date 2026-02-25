<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_03_00(): array {
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

		//kodi

		//yanjun
		'require_pin_for_palace_project' => [
			'title' => 'Add Require PIN for Palace Project Setting',
			'description' => 'Add Require PIN for Palace Project Setting',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE palace_project_settings ADD COLUMN requirePin TINYINT(1) DEFAULT 1',
			]
		], //allow_require_pin_for_palace_project
		'clean_up_event_fields_allowable_values' => [
			'title' => 'Clean up Event Fields Allowable Values when type is not select lists',
			'description' => 'Clean up Event Fields Allowable Values when type is not select lists',
			'continueOnError' => false,
			'sql' => [
				"UPDATE event_field SET allowableValues = '' WHERE type <> 3",
			]
		], //clean_up_event_fields_allowable_values

		//imani

		//galen

		//alexander
		'add_default_event_calendar_display_dropdown' => [
			'title' => 'Add Default Event Calendar Display Dropdown',
			'description' => 'Add the option of selecting the default display for the native events calendar',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN eventsDefaultCalendarView TINYINT(1) NOT NULL DEFAULT 0",
			],
		], //add_default_event_calendar_display_dropdown

		//chloe

		//mark j
		'notify_saved_searches' => [
			'title' => 'User - Allow patrons to choose if they want email notifications when saved searches are updated.',
			'description' => 'Patrons will gain the choice within Your Preferences to have Aspen notify them via email when updates to their saved searches occur.',
			'sql' => [
				"ALTER TABLE user ADD COLUMN notifySavedSearches tinyint(1) NOT NULL DEFAULT 1",
			]
		], //notify_saved_searches

		//lucas


		//tomas

		// stephen
		'add_success_button_to_theme' => [
			'title' => 'Theme - Add Success button customization',
			'description' => 'In Themes, libraries can now customize the Bootstrap Success button.',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE themes ADD COLUMN successButtonBackgroundColor char(7) DEFAULT '#5cb85c'",
				"ALTER TABLE themes ADD COLUMN successButtonBackgroundColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonForegroundColor char(7) DEFAULT '#000000'",
				"ALTER TABLE themes ADD COLUMN successButtonForegroundColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonBorderColor char(7) DEFAULT '#4cae4c'",
				"ALTER TABLE themes ADD COLUMN successButtonBorderColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonHoverBackgroundColor char(7) DEFAULT '#449d44'",
				"ALTER TABLE themes ADD COLUMN successButtonHoverBackgroundColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonHoverForegroundColor char(7) DEFAULT '#000000'",
				"ALTER TABLE themes ADD COLUMN successButtonHoverForegroundColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonHoverBorderColor char(7) DEFAULT '#398439'",
				"ALTER TABLE themes ADD COLUMN successButtonHoverBorderColorDefault tinyint(1) DEFAULT 1",
			]
		],

		//other


	];
}
